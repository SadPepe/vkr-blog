#!/bin/sh
set -eu

cd /var/www

log() { printf '[entrypoint] %s\n' "$*"; }

# --- 1. Writable-каталоги (важно, если storage смонтирован томом) ---------
mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# --- 2. APP_KEY ----------------------------------------------------------
# Нужен для cookie/сессий. В docker-compose.yml ключ пробрасывается из .env
# (переменная APP_KEY). Если его нет — генерируем на время жизни контейнера.
if [ -z "${APP_KEY:-}" ]; then
    if [ -f .env ] && grep -q '^APP_KEY=base64:' .env; then
        log "APP_KEY найден в .env"
    else
        APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
        export APP_KEY
        log "APP_KEY не задан — сгенерирован временный ключ (сессии сбросятся при перезапуске)"
    fi
fi

# --- 3. Ожидание PostgreSQL ---------------------------------------------
if [ "${DB_CONNECTION:-pgsql}" = "pgsql" ]; then
    log "Ожидаю PostgreSQL ${DB_HOST:-db}:${DB_PORT:-5432}/${DB_DATABASE:-blog} ..."
    attempt=0
    until php -r '
        $h = getenv("DB_HOST") ?: "db";
        $p = getenv("DB_PORT") ?: "5432";
        $d = getenv("DB_DATABASE") ?: "blog";
        $u = getenv("DB_USERNAME") ?: "blog";
        $w = getenv("DB_PASSWORD");
        try {
            new PDO("pgsql:host={$h};port={$p};dbname={$d}", $u, $w ?: "");
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    '; do
        attempt=$((attempt + 1))
        if [ "$attempt" -ge 60 ]; then
            log "PostgreSQL недоступен за 2 минуты. Проверьте значения DB_* в .env / docker-compose.yml"
            exit 1
        fi
        sleep 2
    done
    log "PostgreSQL доступен"
fi

# --- 4. Симлинк на публичные файлы + миграции ---------------------------
if [ ! -L public/storage ]; then
    php artisan storage:link --no-interaction || true
fi

php artisan migrate --force --no-interaction

# Демо-данные: только при первом запуске и только по флагу DB_SEED=true
if [ "${DB_SEED:-false}" = "true" ] && [ ! -f storage/app/.seeded ]; then
    log "Наполняю базу демо-данными (DB_SEED=true)"
    php artisan db:seed --force --no-interaction
    if [ "${DB_SEED_ARTICLES:-true}" = "true" ]; then
        # 40 статей с текстом и картинками из database/seeders/images.
        # Свою папку с фото можно задать через SEEDER_IMAGES_DIR.
        php artisan db:seed --class=ArticleSeeder --force --no-interaction || \
            log "warn: ArticleSeeder завершился с ошибкой"
    fi
    touch storage/app/.seeded
fi

# --- 5. Кэш конфига/шаблонов и запуск процессов -------------------------
php artisan config:cache --no-interaction || log "warn: config:cache пропущен"
php artisan view:cache --no-interaction || log "warn: view:cache пропущен"

log "Готово, запускаю php-fpm и nginx"

exec "$@"
