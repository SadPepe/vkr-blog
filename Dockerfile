# syntax=docker/dockerfile:1.7

############################################################
# 1. PHP-зависимости
#    COMPOSER_NO_DEV=0 собирает образ с phpunit/pint для тестов
############################################################
FROM composer:2 AS vendor

ARG COMPOSER_NO_DEV=1

WORKDIR /build

COPY composer.json composer.lock ./
# ext-intl ставится на этапе runtime; в образе composer:2 его нет,
# а Filament 4 без него не разрешается — на время сборки vendor игнорируем.
# --prefer-source: на домашнем сервере api.github.com/codeload.github.com отдают
# подменённый сертификат, dist-архивы не скачиваются, а Composer не откатывается
# на git сам. Клонирование с github.com работает, поэтому ставим из исходников.
RUN if [ "$COMPOSER_NO_DEV" = "1" ]; then \
        composer install \
            --no-dev \
            --no-scripts \
            --no-interaction \
            --no-progress \
            --prefer-source \
            --optimize-autoloader \
            --ignore-platform-req=ext-intl; \
    else \
        composer install \
            --no-scripts \
            --no-interaction \
            --no-progress \
            --prefer-source \
            --optimize-autoloader \
            --ignore-platform-req=ext-intl; \
    fi

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
        libpng \
        libjpeg-turbo \
        freetype \
        libwebp \
        tzdata \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        oniguruma-dev \
        postgresql-dev \
        sqlite-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libwebp-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pdo_sqlite \
        mbstring \
        intl \
        bcmath \
        pcntl \
        opcache \
        gd \
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
