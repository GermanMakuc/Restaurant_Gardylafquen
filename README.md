# Restaurant Gardylafquen

Aplicación web local a medida, para la gestión de productos, control de caja, empleados y mesas.

Herramientas: PHP Laravel frameworks 5.7 / MDBootstrap / Bootstrap / JavaScript 


## Docker

Requiere Docker y Docker Compose.

```bash
docker compose up -d --build
```

- App: http://localhost:8000 (PHP 7.4 + Apache)
- MySQL 8: puerto `3307` del host (db `restaurant`, usuario `restaurant`, clave `secret`)
- Las migraciones se ejecutan automáticamente al iniciar.

Comandos útiles:

```bash
docker compose exec app php artisan db:seed      # ejecutar seeders
docker compose exec app php artisan tinker
docker compose logs -f app
docker compose down            # detener (conserva la base de datos)
docker compose down -v         # detener y borrar la base de datos
```

Variables opcionales (en `.env` o en el entorno): `APP_PORT`, `DB_FORWARD_PORT`, `DOCKER_DB_DATABASE`, `DOCKER_DB_USERNAME`, `DOCKER_DB_PASSWORD`, `DOCKER_DB_ROOT_PASSWORD`.
