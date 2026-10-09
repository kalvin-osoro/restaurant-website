#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is required. Set a persistent key in .env.docker before starting." >&2
    exit 1
fi

# Named volumes also need these directories when mounted over image contents.
mkdir -p storage/app/public storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    if [ ! -f "$DB_DATABASE" ]; then
        touch "$DB_DATABASE"
    fi
    chown www-data:www-data "$(dirname "$DB_DATABASE")" "$DB_DATABASE"
fi

chown -R www-data:www-data storage bootstrap/cache

# CLI services run with the same permissions as Apache's workers.
if [ "$1" = "php" ]; then
    exec su -s /bin/sh www-data -c 'exec "$@"' -- sh "$@"
fi

exec docker-php-entrypoint "$@"
