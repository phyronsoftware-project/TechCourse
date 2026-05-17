#!/usr/bin/env sh
set -e

cd /var/www/html

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod 755 public/logo || true
chmod 644 public/logo/* || true
chmod -R a+rX storage/app/public || true

rm -f public/storage
php artisan storage:link || true

rm -f bootstrap/cache/*.php
php artisan package:discover --ansi || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

php-fpm -D
exec nginx -g "daemon off;"
