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
# Apply the current migrations explicitly because the production database was imported without legacy migration history.
php artisan migrate --path=database/migrations/2026_07_12_000023_create_provinces_and_add_delivery_fields.php --force
php artisan migrate --path=database/migrations/2026_07_12_000024_add_image_path_to_shop_order_items.php --force
# Apply missing subscription schema on each deploy without rerunning legacy migrations.
php artisan migrate --path=database/migrations/2026_09_14_000001_create_subscription_schema.php --force
# Repair payment columns required by course and subscription KHQR checkout.
php artisan migrate --path=database/migrations/2026_09_15_000002_repair_payment_checkout_schema.php --force
# Create the payment history audit table required by checkout event logging.
php artisan migrate --path=database/migrations/2026_09_15_000003_repair_payment_histories_schema.php --force

php-fpm -D
exec nginx -g "daemon off;"
