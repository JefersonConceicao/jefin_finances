#!/bin/sh
# Gera os caches do Laravel com o .env montado em tempo de execução e sobe o PHP-FPM.
set -e

cd /var/www

# O volume de storage pode vir vazio num servidor novo
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
         storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
