# Restaurant Gardylafquen

Aplicación para la gestión de productos, categorías, órdenes/mesas, meseros y ventas.

## Arquitectura (monorepo)

- `backend/`: API REST en Laravel (`/api/v1/...`), arquitectura por capas (`Controller → FormRequest → DTO → Service → Repository → Model`), documentada en `backend/docs/openapi.yaml`.
- `frontend/`: Astro + TypeScript estricto + Tailwind CSS.
- La raíz conserva el código legado Laravel 5.7 con vistas Blade mientras se completan las fases de migración definidas en [AGENTS.md](AGENTS.md).

## Docker

Requiere Docker y Docker Compose.

```bash
docker compose up -d --build
```

- Frontend (Astro): http://localhost:4321
- Backend API (PHP 7.4 + Apache): http://localhost:8000/api/v1/health
- MySQL 8: puerto `3307` del host (db `restaurant`, usuario `restaurant`, clave `secret`)
- Las migraciones se ejecutan automáticamente al iniciar `backend`.

Comandos útiles:

```bash
docker compose exec backend php artisan db:seed
docker compose exec backend ./vendor/bin/php-cs-fixer fix --dry-run --diff
docker compose exec backend ./vendor/bin/phpstan analyse
docker compose exec backend ./vendor/bin/phpunit
docker compose down            # detener (conserva la base de datos)
docker compose down -v         # detener y borrar la base de datos
```

Variables opcionales (en `.env` o en el entorno): `APP_PORT`, `FRONTEND_PORT`, `DB_FORWARD_PORT`, `DOCKER_DB_DATABASE`, `DOCKER_DB_USERNAME`, `DOCKER_DB_PASSWORD`, `DOCKER_DB_ROOT_PASSWORD`.
