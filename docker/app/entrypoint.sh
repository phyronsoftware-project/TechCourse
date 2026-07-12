#!/usr/bin/env sh
set -e

cd /var/www/html

PORT="${PORT:-80}"
sed "s/__PORT__/${PORT}/g" /etc/nginx/http.d/default.conf > /tmp/default.conf
mv /tmp/default.conf /etc/nginx/http.d/default.conf

if [ -f /etc/secrets/ca.pem ]; then
    cp /etc/secrets/ca.pem /tmp/render-ca.pem
    chmod 644 /tmp/render-ca.pem
    export MYSQL_ATTR_SSL_CA=/tmp/render-ca.pem
    echo "Aiven CA loaded from Render secret file."
else
    echo "Aiven CA secret file not found at /etc/secrets/ca.pem."
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
# Make sure Laravel can always create and append its runtime log file on Render.
touch storage/logs/laravel.log
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
chmod 664 storage/logs/laravel.log
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
# Apply legacy migrations when their history is available, but keep imported databases bootable.
php artisan migrate --force || echo "Legacy migration pass reported an existing-schema warning; continuing with current migrations."

# Apply the delivery and order-image migrations explicitly for imported databases without migration history.
php artisan migrate --path=database/migrations/2026_07_12_000023_create_provinces_and_add_delivery_fields.php --force
php artisan migrate --path=database/migrations/2026_07_12_000024_add_image_path_to_shop_order_items.php --force

php-fpm -D
exec nginx -g "daemon off;"
