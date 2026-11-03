# syntax=docker/dockerfile:1.7

############################################################
# 1. PHP-зависимости: composer install без dev-пакетов
############################################################
FROM composer:2 AS vendor

WORKDIR /build

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

############################################################
# 2. Фронтенд: собираем resources/js + resources/css -> public/build
#    (app.js импортирует ../../vendor/tightenco/ziggy — нужен vendor)
############################################################
FROM node:22-alpine AS assets

WORKDIR /build

COPY --from=vendor /build/vendor ./vendor

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources

RUN npm run build

############################################################
# 3. Runtime: php-fpm + nginx + supervisor в одном контейнере
############################################################
FROM php:8.4-fpm-alpine AS runtime

RUN apk add --no-cache \
        nginx \
        supervisor \
        icu-libs \
        oniguruma \
        postgresql-libs \
        tzdata \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        oniguruma-dev \
        postgresql-dev \
        sqlite-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pdo_sqlite \
        mbstring \
        intl \
        bcmath \
        pcntl \
        opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/*

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/nginx-app.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

WORKDIR /var/www

COPY --from=vendor /build/vendor ./vendor
COPY . .
COPY --from=assets /build/public/build ./public/build

RUN set -eux; \
    mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache; \
    ln -sfn /var/www/storage/app/public public/storage; \
    chmod +x /usr/local/bin/entrypoint.sh; \
    php artisan package:discover --ansi; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R a+rX /var/www

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
