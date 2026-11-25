# Restaurant Gardylafquen — Reglas de arquitectura del proyecto

Este archivo es la fuente de verdad de la arquitectura. Toda contribución (humana o de agente) debe cumplirlo **sin excepciones**. Si una regla estorba, se propone cambiarla aquí primero; no se la viola en el código.

## 1. Contexto y stack

Aplicación para gestión de productos, categorías, órdenes/mesas, meseros y caja (ventas) de un restaurante.

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 + PHP 8.3+ — **API REST pura** (JSON) |
| Base de datos | MySQL 8 |
| Autenticación | Laravel Sanctum (tokens Bearer) |
| Frontend | Astro + TypeScript estricto + Tailwind CSS, islas interactivas solo donde haga falta |
| Infra local | Docker Compose (`backend`, `frontend`, `db`) |

> **Estado actual:** migración completada al monorepo (`backend/` sobre Laravel 12 / PHP 8.3 con Sanctum + `frontend/` sobre Astro SSR + Tailwind CSS). El código legado de Laravel 5.7 fue retirado en la Fase 4.

## 2. Estructura del monorepo

```
/
├── AGENTS.md                 # este archivo
├── docker-compose.yml
├── backend/                  # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/
│   │   │   ├── Requests/          # FormRequest (validación)
│   │   │   ├── Resources/         # API Resources (salida JSON)
│   │   │   └── Middleware/
│   │   ├── DTOs/                  # Data Transfer Objects (entrada a Services)
│   │   ├── Services/              # Lógica de negocio
│   │   ├── Repositories/
│   │   │   ├── Contracts/         # Interfaces
│   │   │   └── Eloquent/          # Implementaciones
│   │   ├── Models/
│   │   ├── Enums/
│   │   ├── Policies/
│   │   ├── Exceptions/            # Excepciones de dominio
│   │   └── Providers/             # Binding interfaz → implementación
│   ├── routes/api.php
│   └── tests/{Unit,Feature}/
└── frontend/                 # Astro + Tailwind
    └── src/
        ├── pages/            # rutas (solo composición)
        ├── layouts/
        ├── components/       # UI reutilizable (.astro / islas)
        ├── features/<dominio>/   # componentes + lógica por dominio
        ├── lib/api/          # cliente HTTP tipado
        ├── types/            # tipos de la API
        └── styles/
```

## 3. Backend — arquitectura por capas

Flujo obligatorio de una petición:

```
Route → Controller → (FormRequest → DTO) → Service → Repository (interfaz) → Model/DB
                  ←  API Resource  ←  Service  ←  Repository
```

### 3.1 Reglas por capa

- **Controller**: delgado. Solo recibe el `FormRequest`, construye el DTO, llama **a un único Service** y devuelve un `Resource`/respuesta. Prohibido: consultas Eloquent, lógica de negocio, `DB::`, validación manual.
- **FormRequest**: toda validación y autorización de entrada. Expone `toDto()`.
- **DTO**: clases `final readonly` con propiedades tipadas y constructor promovido. Sin lógica, sin Eloquent, sin `array` asociativos sueltos entre capas. Se crean con `fromRequest()`/`fromArray()`.
- **Service**: contiene **toda** la lógica de negocio, orquesta repositorios y abre transacciones (`DB::transaction`) cuando se escriben varias entidades. Depende **solo de interfaces** de Repository. No conoce `Request`, `Response` ni HTTP.
- **Repository**: único lugar con acceso a datos. Una interfaz en `Repositories/Contracts` por agregado (`ProductRepositoryInterface`) y su implementación en `Repositories/Eloquent`. Recibe/retorna modelos, colecciones o paginadores; **no** contiene reglas de negocio.
- **Model**: relaciones, casts, scopes y `$fillable`. Sin lógica de negocio ni consultas complejas.
- **API Resource**: única forma de serializar modelos. Nunca devolver un modelo ni un array crudo desde un controller.
- **Policy**: autorización por recurso. Se aplica en el controller (`authorize`) o en el FormRequest.
- **Enum**: para estados y tipos cerrados (p. ej. `OrderStatus`: `waiting`, `served`, `paid`). Prohibidos los strings mágicos.

### 3.2 Reglas de inyección y bindings

- Inyección por constructor. Prohibido `app()`, `resolve()`, `new Service()` y facades dentro de Services/Repositories (excepto `DB::transaction`).
- Cada interfaz de Repository se enlaza en un `RepositoryServiceProvider`.
- Los Services son `final` y se prueban con repositorios mockeados.

### 3.3 Ejemplo mínimo (referencia de estilo)

> Sintaxis **objetivo** (PHP 8.3). Mientras el proyecto siga en PHP 7.4 se aplica la equivalencia de §13 (propiedades tipadas `private` + constructor explícito, sin `readonly` ni promoción).

```php
// DTO
final readonly class CreateProductDto
{
    public function __construct(
        public string $name,
        public int $categoryId,
        public int $priceCents,
        public ?string $description = null,
    ) {}
}

// Service
final class ProductService
{
    public function __construct(private readonly ProductRepositoryInterface $products) {}

    public function create(CreateProductDto $dto): Product
    {
        return $this->products->create($dto);
    }
}

// Controller
final class ProductController extends Controller
{
    public function __construct(private readonly ProductService $service) {}

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->service->create($request->toDto());

        return ProductResource::make($product)->response()->setStatusCode(201);
    }
}
```

## 4. Convenciones de la API REST

- Prefijo y versionado: `/api/v1/...`. Cambios incompatibles ⇒ `/api/v2`.
- Recursos en **plural, minúscula, kebab-case, en inglés**: `/products`, `/categories`, `/orders`, `/waiters`, `/tables`, `/sales`.
- Verbos HTTP correctos: `GET` (leer), `POST` (crear), `PUT/PATCH` (actualizar), `DELETE` (borrar). Prohibido usar `POST` para borrar/actualizar (como en el legado).
- Códigos: `200`, `201` (con `Location`), `204` (sin cuerpo), `401`, `403`, `404`, `409`, `422` (validación), `500`. Nunca `200` con un error dentro.
- Respuesta de éxito: `{ "data": ... }` (+ `links`/`meta` si hay paginación). Todas las listas se **paginan**.
- Respuesta de error uniforme (manejada en el `Handler` global):
  ```json
  { "message": "Descripción legible", "code": "validation_error", "errors": { "campo": ["detalle"] } }
  ```
- Filtros, orden y paginación por query string: `?filter[status]=waiting&sort=-created_at&page=2&per_page=20`.
- Dinero en **centavos (entero)**; fechas en ISO‑8601 UTC; claves JSON en `snake_case`.
- Rutas protegidas con `auth:sanctum`; cada endpoint documentado en OpenAPI (`backend/docs/openapi.yaml`) y actualizado en el mismo PR.
- Subida de imágenes: endpoint dedicado `multipart/form-data`, validar tipo/tamaño, guardar en `storage` (disco configurable), devolver URL.

## 5. Base de datos

- Cambios solo mediante **migraciones** (nunca editar una ya aplicada/commiteada). Nombres en inglés, tablas en plural `snake_case`.
- Claves foráneas con `constrained()` y política `onDelete` explícita. Índices en columnas de filtro/búsqueda.
- `SoftDeletes` en entidades con historial (productos, órdenes, meseros).
- Dinero: columnas `unsignedBigInteger` en centavos.
- Seeders y factories para desarrollo y tests; **nunca** datos reales en seeders.
- Prevenir N+1: usar `with()` en los repositorios y habilitar `Model::preventLazyLoading()` fuera de producción.

## 6. Frontend — Astro + Tailwind

- **TypeScript estricto** (`astro/tsconfigs/strictest`). Prohibido `any`.
- `pages/` solo compone layouts y componentes; la lógica vive en `features/<dominio>/` y `lib/`.
- **Todo acceso a la API pasa por `src/lib/api/`** (cliente tipado con `fetch`, manejo central de errores y del token). Prohibido `fetch` suelto en componentes.
- Los tipos de `src/types/` reflejan exactamente los Resources de la API.
- Preferir componentes `.astro` (HTML estático). Usar islas (`client:*`) solo para interactividad real (carrito de orden, formularios dinámicos) y con la directiva más perezosa posible (`client:visible`/`client:idle`).
- **Tailwind**: solo utilidades; sin CSS ad‑hoc salvo `@layer components` justificado. Tokens (colores, tipografía, espaciado) definidos en la configuración del tema, no valores arbitrarios repetidos. Extraer componentes en vez de copiar clases. Diseño *mobile‑first* y accesible (HTML semántico, contraste, foco visible, `aria-*`).
- El token de Sanctum **no** se guarda en `localStorage`: se mantiene en cookie `httpOnly` gestionada por endpoints del servidor de Astro.
- Variables de entorno públicas con prefijo `PUBLIC_`; nunca secretos en el cliente.

## 7. Buenas prácticas obligatorias (todo el código)

1. **SOLID**, DRY y funciones/métodos pequeños con una sola responsabilidad. Preferir composición sobre herencia.
2. **Tipado estricto**: `declare(strict_types=1);` en todo PHP; tipos de parámetros y retorno siempre; TypeScript sin `any`.
3. **Nombres en inglés** en código, rutas, tablas y columnas. Los textos visibles al usuario (UI/mensajes) en español, vía archivos de traducción, no hardcodeados.
4. **Sin lógica duplicada** entre capas ni entre frontend y backend: el backend es la autoridad de reglas de negocio y validación; el frontend solo valida para UX.
5. **Seguridad**: validar toda entrada, autorizar con Policies, `$fillable` explícito (nunca `$guarded = []`), proteger contra mass‑assignment/IDOR, rate limiting en login, CORS restringido al origen del frontend, secretos solo en variables de entorno.
6. **Manejo de errores**: excepciones de dominio en `app/Exceptions` mapeadas a respuestas HTTP en el `Handler`; nunca `catch` vacío; logs con contexto, sin datos sensibles.
7. **Sin comentarios obvios**; comentar solo el *porqué*. PHPDoc/TSDoc en contratos públicos.
8. **Commits** pequeños con Conventional Commits (`feat:`, `fix:`, `refactor:`, `docs:`, `chore:`, `test:`).
9. **No dejar código muerto** ni archivos temporales; respetar el `.gitignore`. Nunca commitear `.env` ni secretos.

## 8. Calidad y pruebas

- **Backend**: Pest o PHPUnit. Services con tests unitarios (repositorios mockeados); endpoints con tests de feature (BD de prueba). Cobertura mínima objetivo: 80 % en Services.
- **Estilo/análisis estático**: Laravel Pint (PSR‑12) + Larastan nivel 8 — deben pasar sin errores.
- **Frontend**: Vitest para `lib/` y lógica; ESLint + Prettier (plugin Tailwind); `astro check` sin errores.
- Un cambio no está terminado hasta que **lint, análisis estático y tests pasen** y el endpoint nuevo esté en OpenAPI.

## 9. Docker

- `docker compose up` levanta `db` (MySQL 8), `backend` (PHP‑FPM/Nginx o `php artisan serve` solo en dev) y `frontend` (Astro dev server).
- Configuración por variables de entorno; `.env.example` siempre actualizado en `backend/` y `frontend/`.
- Las migraciones se ejecutan al iniciar solo en desarrollo; en producción de forma explícita.

## 10. Dominio y glosario (legado ES → código EN)

| Legado (ES) | Código (EN) |
|---|---|
| Productos | `Product` |
| Categorías | `Category` |
| Orden / Mesa (state) | `Order`, `Table` |
| Meseros (User) | `Waiter` (`User` queda para autenticación) |
| Ticket / Ventas (Result) | `Ticket` / `Sale` |

## 11. Plan de migración (por fases)

1. **Fase 0** — Esta regla aprobada. ✅ *(este documento)*
2. **Fase 1** — Scaffolding: `backend/`, `frontend/`, Docker Compose del monorepo y CI de lint/tests. ✅
3. **Fase 2** — Migración por dominio (Categorías → Productos → Meseros/Mesas → Órdenes → Ventas/Tickets) con migraciones, DTOs, Repository, Service, Controller, Resource, tests, OpenAPI y pantallas Astro. ✅
4. **Fase 3** — Autenticación, roles (`admin`, `cashier`, `waiter`), Policies y sesión Astro mediante cookies `httpOnly` + proxy BFF. ✅
5. **Fase 4** — Retiro completo del código legado de la raíz. ✅
6. **Fase 5** — Actualización a Laravel 12 + PHP 8.3+, sintaxis objetivo (`final readonly` DTOs, `enum`, `match`, constructor promovido), Sanctum, Pint y Larastan nivel 8. ✅

## 12. Instrucciones para agentes de IA

- Leer este archivo antes de cualquier cambio y **citar la sección** cuando una decisión dependa de ella.
- Respetar el flujo `Controller → Service → Repository`; si falta una capa para la funcionalidad pedida, crearla en lugar de saltársela.
- Antes de crear un archivo, comprobar que no existe un equivalente. Mantener cambios mínimos y enfocados.
- Verificar con lint, análisis estático y tests antes de dar una tarea por terminada.
- Ante ambigüedad de negocio, **preguntar** en lugar de asumir.

## 13. Convenciones activas del stack actualizado (Laravel 12 / PHP 8.3+)

Las equivalencias temporales de Laravel 5.7 / PHP 7.4 fueron completadas en la Fase 5. Todo código nuevo debe usar directamente la sintaxis y herramientas objetivo:

- **DTOs:** `final readonly class` con propiedades públicas promovidas en `__construct` y normalización de entrada en `fromArray()`.
- **Enums:** enums nativos respaldados por `string` (`enum OrderStatus: string`, `TableStatus`, `UserRole`) con `values(): array`.
- **Services, Controllers y Repositories:** `final` / `final readonly` con promoción de constructor `private readonly`, `declare(strict_types=1);`, `match` y funciones nativas (`str_contains`, `str_starts_with`).
- **Autenticación:** Laravel Sanctum (`HasApiTokens` en `User`, middleware `auth:sanctum`, encapsulado en `AuthService` + `AuthRepositoryInterface`).
- **Calidad y BD:** `./vendor/bin/pint --test` (preset PSR-12 en `pint.json`), `./vendor/bin/phpstan analyse` (Larastan nivel 8), `./vendor/bin/phpunit` (PHPUnit 11 sobre SQLite `:memory:` con `<server>` + `<env force="true">` en `phpunit.xml`) y `Model::preventLazyLoading(! $this->app->isProduction())` activo en `AppServiceProvider`.
- **Docker:** imagen `backend` sobre `php:8.3-apache`.
