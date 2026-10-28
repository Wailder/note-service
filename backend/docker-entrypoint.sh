#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

chown -R www-data:www-data database storage bootstrap/cache
chmod -R ug+rwX database storage bootstrap/cache

php artisan migrate --force

exec php-fpm