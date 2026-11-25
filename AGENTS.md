# Restaurant Gardylafquen — Reglas de arquitectura del proyecto

Este archivo es la fuente de verdad de la arquitectura. Toda contribución (humana o de agente) debe cumplirlo **sin excepciones**. Si una regla estorba, se propone cambiarla aquí primero; no se la viola en el código.

## 1. Contexto y stack

Aplicación para gestión de productos, categorías, órdenes/mesas, meseros y caja (ventas) de un restaurante.

| Capa | Tecnología |
|---|---|
| Backend | **Objetivo:** Laravel última versión estable + PHP 8.3+. **Hoy:** Laravel 5.7 + PHP 7.4 (ver §13). **API REST pura** (JSON) |
| Base de datos | MySQL 8 |
| Autenticación | **Objetivo:** Laravel Sanctum (tokens). **Hoy:** no disponible en 5.7 (ver §13) |
| Frontend | Astro + TypeScript estricto + Tailwind CSS, islas interactivas solo donde haga falta |
| Infra local | Docker Compose (`backend`, `frontend`, `db`) |

> **Estado actual:** el código en la raíz (`app/`, `resources/`, `routes/`, ...) es el **legado** Laravel 5.7 con vistas Blade. Se migra por fases a `backend/` y `frontend/`. No se agregan funcionalidades nuevas al legado.
>
> **Escalado previsto:** la arquitectura de este documento es la definitiva. Se implementa **primero sobre Laravel 5.7 / PHP 7.4** y se sube después a la última versión de Laravel. Todo código nuevo debe escribirse **listo para esa actualización** (§13).

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
2. **Fase 1** — Scaffolding: `backend/` (Laravel 5.7 actual movido tal cual + estructura de capas), `frontend/` (Astro + Tailwind), Docker Compose del monorepo, CI de lint/tests.
3. **Fase 2** — Migrar por dominio, uno por PR: Categorías → Productos → Meseros/Mesas → Órdenes → Ventas/Tickets. Cada uno con migración, DTOs, Repository, Service, Controller, Resource, tests y pantalla Astro.
4. **Fase 3** — Autenticación: solución provisional compatible con 5.7 (§13) y roles/permisos.
5. **Fase 4** — Retirar el código legado de la raíz.
6. **Fase 5** — **Actualizar Laravel a la última versión** (y PHP 8.3+), adoptar la sintaxis objetivo, cambiar a Sanctum, Pint y Larastan. Se hace en un PR propio, sin mezclar funcionalidades.

## 12. Instrucciones para agentes de IA

- Leer este archivo antes de cualquier cambio y **citar la sección** cuando una decisión dependa de ella.
- Respetar el flujo `Controller → Service → Repository`; si falta una capa para la funcionalidad pedida, crearla en lugar de saltársela.
- Antes de crear un archivo, comprobar que no existe un equivalente. Mantener cambios mínimos y enfocados.
- Verificar con lint, análisis estático y tests antes de dar una tarea por terminada.
- Ante ambigüedad de negocio, **preguntar** en lugar de asumir.

## 13. Compatibilidad actual (Laravel 5.7 / PHP 7.4) y camino de actualización

El objetivo es actualizar a la última versión de Laravel. Hasta entonces el código nuevo debe **funcionar en 5.7/PHP 7.4 y migrar sin reescribirse**.

### 13.1 Reglas para que la actualización sea trivial

- La arquitectura por capas (§3), la API REST (§4) y las convenciones de BD (§5) **no cambian** con la actualización; se aplican desde ya.
- **Prohibido** usar APIs de 5.7 que se eliminaron o cambiaron: helpers `array_*`/`str_*` globales (usar `Arr::`/`Str::`), `Route::get('x', 'Controller@method')` (usar `[Controller::class, 'method']`), `$dates` en modelos (usar `$casts`), `App\Model` en la raíz de `app/` (los modelos van en `app/Models/`), `mix`/webpack en el backend.
- Inyección de dependencias por constructor y bindings en `RepositoryServiceProvider`; sin facades en Services/Repositories (excepto `DB::transaction`).
- Lógica de negocio solo en Services: el framework queda en los bordes y se sustituye al actualizar sin tocar el dominio.
- Dependencias del `composer.json` con versiones acotadas; no añadir paquetes que no soporten la última versión de Laravel.
- Tests de Feature/Unit desde la Fase 1: son la red de seguridad de la actualización.

### 13.2 Equivalencias de sintaxis (hoy → objetivo)

| Tema | Hoy (PHP 7.4) | Objetivo (PHP 8.3) |
|---|---|---|
| DTO | clase `final` con propiedades tipadas `private` + getters, constructor explícito | `final readonly` con constructor promovido |
| Enums | clase `final` con constantes (`OrderStatus::WAITING`) y método `values()` | `enum OrderStatus: string` |
| Tipos | `?string`, tipos de retorno siempre; sin union types | union types, `never`, `new` en inicializadores |
| `match` / `str_contains` | no disponibles: usar `switch` / `Str::contains` | `match`, funciones nativas |
| Strict types | `declare(strict_types=1);` siempre | igual |

Al actualizar se hace una pasada mecánica (Rector) de la columna «Hoy» a «Objetivo».

### 13.3 Autenticación

- Sanctum **no existe** en Laravel 5.7. Hasta la Fase 5 se usa el guard `api` con `token` (`api_token` hasheado en `users`) o Passport, el que sea más simple de reemplazar.
- El acceso al token se encapsula en un `AuthService` + `AuthRepositoryInterface`; los controllers no conocen el mecanismo. Al actualizar solo cambia la implementación (a Sanctum) y el middleware `auth:sanctum`.
- El contrato de la API (`POST /api/v1/auth/login` → `{ "data": { "token": "..." } }`, `POST /api/v1/auth/logout`) **no cambia** con la actualización.

### 13.4 Herramientas de calidad hoy

- Pint y Larastan nivel 8 requieren PHP/Laravel modernos: hasta la Fase 5 se usa **PHP‑CS‑Fixer (PSR‑12)** y **PHPStan** al nivel más alto que soporte el proyecto, subiéndolo gradualmente.
- Las reglas de §8 (lint + análisis estático + tests en verde) se mantienen igualmente.
- `Model::preventLazyLoading()` (§5) no existe en 5.7: se evita el N+1 con `with()` en los repositorios y se revisa en code review.

### 13.5 Docker

- La imagen de `backend` usa PHP 7.4 hoy; la actualización a PHP 8.3 es un cambio de la imagen base en la Fase 5. No se acopla código a la versión de PHP del contenedor.
