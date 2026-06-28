FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress --no-scripts

FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    bash \
    ca-certificates \
    fcgi \
    freetype-dev \
    icu-dev \
    jpeg-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    zip \
    unzip \
    git \
    mysql-client \
    nginx \
    ffmpeg

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring intl gd zip bcmath

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/app/entrypoint.sh /usr/local/bin/app-entrypoint
COPY docker/app/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

RUN chmod +x /usr/local/bin/app-entrypoint \
    && rm -f bootstrap/cache/*.php \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && php artisan vendor:publish --tag=laravel-assets --force || true

EXPOSE 80

ENTRYPOINT ["app-entrypoint"]
CMD []
