#!/bin/sh
set -e
cd /var/www/html

[ -f .env ] || cp .env.example .env

if [ -z "$APP_KEY" ] && ! grep -Eq '^APP_KEY=.+' .env; then
    php artisan key:generate --force
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
