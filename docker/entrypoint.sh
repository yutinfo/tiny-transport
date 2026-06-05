#!/bin/sh
set -e

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

exec "$@"
