# Restaurant Gardylafquen

Aplicación para la gestión de productos, categorías, órdenes/mesas, meseros y caja (ventas) de un restaurante.

## Arquitectura (monorepo)

- [`backend/`](backend/): API REST pura en Laravel 13 (PHP 8.6, `/api/v1/...`), arquitectura por capas (`Controller → FormRequest → DTO → Service → Repository → Model`), documentada en [`backend/docs/openapi.yaml`](backend/docs/openapi.yaml).
- [`frontend/`](frontend/): Astro + TypeScript estricto + Tailwind CSS (sesión mediante cookie `httpOnly` en BFF de Astro).
- [`AGENTS.md`](AGENTS.md): fuente de verdad de arquitectura, convenciones y flujo de desarrollo.

---

## Instalación con Docker (recomendado)

### Requisitos

- Docker y Docker Compose.

### Levantar los servicios

```bash
docker compose up -d --build
```

Al iniciar el contenedor `backend`, las migraciones de Laravel se ejecutan automáticamente. Para cargar los datos de ejemplo y usuarios demo (si aún no se ejecutó el seeder):

```bash
docker compose exec backend php artisan db:seed
```

Servicios disponibles:

| Servicio | URL / Puerto | Notas |
|---|---|---|
| Frontend (Astro) | `http://localhost:4321` | Interfaz web y endpoints BFF (`/api/auth/*`, `/api/proxy/*`) |
| Backend API (PHP 8.6 + Apache) | `http://localhost:8000/api/v1/health` | API REST `/api/v1/...` |
| MySQL 8.0 | `localhost:3307` | BD `restaurant`, usuario `restaurant`, clave `secret` |

### Usuarios demo (seeder)

| Rol | Email | Contraseña |
|---|---|---|
| `admin` | `admin@gardylafquen.test` | `Admin123!` |
| `cashier` | `caja@gardylafquen.test` | `Caja1234!` |
| `waiter` | `mesero@gardylafquen.test` | `Mesero123!` |

### Variables de entorno (Docker)

Variables opcionales en el archivo `.env` de la raíz o en el entorno:

- `APP_PORT` (default `8000`)
- `FRONTEND_PORT` (default `4321`)
- `DB_FORWARD_PORT` (default `3307`)
- `DOCKER_DB_DATABASE`, `DOCKER_DB_USERNAME`, `DOCKER_DB_PASSWORD`, `DOCKER_DB_ROOT_PASSWORD`

Comandos útiles de Docker:

```bash
docker compose down            # detener contenedores (conserva la base de datos)
docker compose down -v         # detener y borrar volúmenes/base de datos
```

---

## Instalación local (sin Docker)

### Backend (`backend/`)

Requisitos: PHP 8.5+ (probado con PHP 8.6; extensiones `pdo_mysql`, `gd`, `mbstring`, `xml`), Composer y MySQL 8.

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

Configura la conexión `DB_*` y `CORS_ALLOWED_ORIGINS=http://localhost:4321` en `backend/.env`, luego ejecuta migraciones y seeders:

```bash
php artisan migrate --seed
php artisan serve --port=8000
```

### Frontend (`frontend/`)

Requisitos: Node.js 22+ y npm.

```bash
cd frontend
cp .env.example .env
npm ci
npm run dev
```

Variables de `frontend/.env.example`:

- `PUBLIC_API_URL=http://localhost:8000/api/v1`
- `API_INTERNAL_URL=http://backend/api/v1` (en ejecución local sin Docker, apuntar a `http://127.0.0.1:8000/api/v1`)

---

## Calidad y pruebas

### Backend (dentro de Docker o en `backend/`)

```bash
docker compose exec backend ./vendor/bin/pint --test
docker compose exec backend ./vendor/bin/phpstan analyse --no-progress --memory-limit=512M
docker compose exec backend ./vendor/bin/phpunit
```

> `backend/phpunit.xml` usa SQLite en memoria (`:memory:`), por lo que ejecutar PHPUnit no modifica la base de datos MySQL de desarrollo.

### Frontend (dentro de Docker o en `frontend/`)

```bash
docker compose exec frontend npm run format:check
docker compose exec frontend npm run lint
docker compose exec frontend npm run check
docker compose exec frontend npm run test
docker compose exec frontend npm run build
```

---

## Contratos de la API REST y sesión

La especificación completa se mantiene en [`backend/docs/openapi.yaml`](backend/docs/openapi.yaml).

### Convenciones generales

- **Prefijo**: `/api/v1` (`Content-Type: application/json`, `Accept: application/json`).
- **Formato de datos**: claves JSON en `snake_case`, importes monetarios en **centavos enteros** (`price_cents`, `subtotal_cents`, `tip_cents`, `total_cents`) y fechas en **ISO-8601 UTC**.
- **Éxito y paginación**:
  - Recurso individual: `{ "data": { ... } }` (`200` o `201` con header `Location`).
  - Listados paginados: `{ "data": [...], "links": { ... }, "meta": { ... } }` con filtros/orden/paginación por query string (`?filter[...]=...&sort=...&page=...&per_page=...`).
  - Borrado / logout sin cuerpo: `204`.
- **Errores uniformes**:
  ```json
  {
    "message": "Descripción legible",
    "code": "validation_error",
    "errors": { "campo": ["detalle"] }
  }
  ```
  Códigos HTTP: `401` (`unauthenticated` / `invalid_credentials`), `403` (`forbidden`), `404` (`not_found`), `409` (`conflict`), `422` (`validation_error`), `500` (`server_error`).

### Autenticación y BFF en Astro

- **Backend (Sanctum)**: `POST /api/v1/auth/login` (limitado con `throttle:10,1`) devuelve `{ "data": { "token": "...", "token_type": "Bearer", "user": { ... } } }`. Las rutas protegidas requieren `Authorization: Bearer <token>`.
- **Frontend (Astro BFF)**: el token **no** se almacena en `localStorage`. Los endpoints del servidor de Astro (`/api/auth/login`, `/api/auth/logout`) gestionan la cookie `httpOnly` `gardylafquen_token`, y las mutaciones desde islas cliente pasan por `/api/proxy/*`, que inyecta el header `Authorization` y reenvía respuestas (incluido `Location`).

### Endpoints principales y permisos por rol

| Recurso | Rutas | Acceso |
|---|---|---|
| Salud | `GET /api/v1/health` | Público (`200` / `503`) |
| Autenticación | `POST /api/v1/auth/login` | Público |
| Sesión actual | `GET /api/v1/auth/me`, `POST /api/v1/auth/logout` | Autenticado (`admin`, `waiter`, `cashier`) |
| Categorías | `GET /api/v1/categories`, `GET /api/v1/categories/{category}` | Autenticado (todos los roles) |
| | `POST`, `PUT/PATCH`, `DELETE /api/v1/categories...` | `admin` |
| Productos | `GET /api/v1/products`, `GET /api/v1/products/{product}` | Autenticado (todos los roles) |
| | `POST`, `PUT/PATCH`, `DELETE /api/v1/products...`, `POST /api/v1/products/{product}/image` (`multipart/form-data`, campo `image`, máx. 2048 KB) | `admin` |
| Meseros | `GET /api/v1/waiters`, `GET /api/v1/waiters/{waiter}` | Autenticado (todos los roles) |
| | `POST`, `PUT/PATCH`, `DELETE /api/v1/waiters...` | `admin` |
| Mesas | `GET /api/v1/tables`, `GET /api/v1/tables/{table}` | Autenticado (todos los roles) |
| | `POST`, `PUT/PATCH`, `DELETE /api/v1/tables...` | `admin` |
| Órdenes | `GET /api/v1/orders`, `GET /api/v1/orders/{order}` | Autenticado (todos los roles) |
| | `POST`, `PUT/PATCH`, `DELETE /api/v1/orders...`, `PATCH /api/v1/orders/{order}/status` | `admin`, `waiter` |
| Ventas | `GET /api/v1/sales`, `GET /api/v1/sales/summary`, `GET /api/v1/sales/{sale}`, `POST /api/v1/sales` | `admin`, `cashier` |
