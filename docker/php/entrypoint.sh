#!/bin/sh
set -e

# ---------------------------------------------------------------------------
# Ensure Laravel directories exist and are writable by php-fpm (www-data)
# ---------------------------------------------------------------------------
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# ---------------------------------------------------------------------------
# Generate an application key when none is provided (e.g. via compose env)
# ---------------------------------------------------------------------------
if [ -z "$APP_KEY" ]; then
    APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    export APP_KEY
    echo "[entrypoint] Generated a new APP_KEY for this container."
fi

# ---------------------------------------------------------------------------
# Wait for PostgreSQL to accept connections
# ---------------------------------------------------------------------------
until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-laravel}" -d "${DB_DATABASE:-laravel}" > /dev/null 2>&1; do
    echo "[entrypoint] Waiting for PostgreSQL at ${DB_HOST:-db}:${DB_PORT:-5432} ..."
    sleep 2
done

# ---------------------------------------------------------------------------
# Wait for Redis (cache / session / queue driver)
# ---------------------------------------------------------------------------
until php -r '$s = @fsockopen(getenv("REDIS_HOST") ?: "redis", (int) (getenv("REDIS_PORT") ?: 6379), $e, $es, 2); exit($s ? 0 : 1);' > /dev/null 2>&1; do
    echo "[entrypoint] Waiting for Redis at ${REDIS_HOST:-redis}:${REDIS_PORT:-6379} ..."
    sleep 2
done

# ---------------------------------------------------------------------------
# Run database migrations
# ---------------------------------------------------------------------------
php artisan migrate --force

# ---------------------------------------------------------------------------
# Cache config / routes / views in production only
# ---------------------------------------------------------------------------
if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec docker-php-entrypoint "$@"
