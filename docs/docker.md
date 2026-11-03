# Docker: запуск сайта в контейнерах

Стек в контейнерах: `app` (PHP 8.4 FPM + nginx + supervisor в одном образе) и `db` (PostgreSQL 16).
Фронтенд (`npm run build`) и `composer install --no-dev` выполняются **при сборке образа**,
поэтому в рантайме Node и Composer не нужны.

## Требования

- Docker Engine 24+ и Docker Compose v2.24+ (флаг `env_file.required`)
- Свободный порт `8080` (или задайте другой через `APP_PORT`)
- Локальный `.env` (он в `.gitignore`, но нужен как источник `APP_KEY` и настроек)

## Быстрый старт

```bash
cp .env.example .env        # если .env ещё нет
php artisan key:generate    # локально, чтобы APP_KEY попал в .env

docker compose up -d --build
# http://localhost:8080
```

При первом запуске entrypoint сам: дожидается PostgreSQL, применяет миграции,
создаёт симлинк `public/storage`, кэширует конфиг и шаблоны, затем стартует php-fpm и nginx.

Демо-данные (категории, тестовый пользователь и 40 статей):

```bash
DB_SEED=true docker compose up -d --build
```

Повторно сиды не запускаются — после первого успеха создаётся маркер `storage/app/.seeded`.

## Переменные окружения

`docker-compose.yml` читает локальный `.env` (секция `env_file`) и переопределяет ключи,
специфичные для контейнеров (переменные из `environment` приоритетнее `.env`):

| Переменная | По умолчанию | Назначение |
|---|---|---|
| `APP_PORT` | `8080` | порт сайта на хосте |
| `APP_KEY` | из `.env` | если пусто — генерируется временный ключ |
| `APP_ENV` / `APP_DEBUG` | из `.env` (`local`/`true`) | для демонстрации можно `production`/`false` |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | из `.env`, иначе `blog`/`blog`/`blog` | одинаково для `app` и `db` |
| `DB_SEED` | `false` | наполнить БД демо-данными при первом старте |
| `SEEDER_IMAGES_DIR` | `/home/anna/Изображения/Wallpapers/Best` | папка с фото для `ArticleSeeder` |

`APP_URL` внутри контейнера выставляется в `http://localhost:${APP_PORT}` (переопределяется
переменной `APP_DOCKER_URL`). Если меняете порт — поменяйте и её, чтобы ссылки на `/storage`
в письмах/`Storage::url()` были корректными (Зигги берёт хост из запроса и работает с любым портом).

## Данные и тома

- `db` → `db:/var/lib/postgresql/data` — база;
- `app` → `storage:/var/www/storage` — загруженные обложки и вставки редактора.

Полный сброс (удалит БД и файлы):

```bash
docker compose down -v
```

## Полезные команды

```bash
docker compose logs -f app                 # логи php-fpm/nginx (LOG_CHANNEL=stderr)
docker compose exec app php artisan tinker # консоль Laravel
docker compose exec app sh                 # shell в контейнере
docker compose exec db psql -U postgres -d blog
docker compose restart app                 # после изменения .env
docker compose down                        # остановить (без удаления данных)
```

## Демо-картинки для статей

`ArticleSeeder` ищет фотографии в `SEEDER_IMAGES_DIR` и копирует их на диск `public`.
В образ эта папка не попадает. Если нужны обложки в контейнерной сборке, смонтируйте её:

```yaml
# docker-compose.yml → services.app.volumes
- /путь/к/фото:/images:ro
```

и задайте `SEEDER_IMAGES_DIR=/images` (или `/images/Подпапка`) при запуске с `DB_SEED=true`.
Без этого статьи создадутся без изображений — сайт работает, но карточки будут без обложек.

## Тесты

Образ приложения собирается без dev-пакетов, поэтому phpunit в нём нет. Для тестов — отдельный
образ (`COMPOSER_NO_DEV=0`) и профиль compose:

```bash
make test                                                   # или:
docker compose --profile test run --rm tests                # весь набор
docker compose --profile test run --rm tests --filter=ProfileTest
```

Тесты идут на sqlite `:memory:` (`.env.testing` + `phpunit.xml`), PostgreSQL и `.env` для них
не нужны. В образе есть `pdo_sqlite` (и `dom` для превью постов), поэтому `php artisan test`
работает так же, как в CI: 57 тестов, 1 пропуск.

## Разработка

Для активной разработки удобнее по-прежнему запускать на хосте:

```bash
php artisan serve     # :8000
npm run dev           # :5173, HMR
```

Контейнер собирает статику один раз при сборке образа; для правок Vue нужен
`docker compose up -d --build` (пересборка идёт из кеша слоёв, обычно 30–60 с).

## Ограничения и известные места

- Ключ `APP_KEY` передаётся из локального `.env`. Если `.env` нет, ключ генерируется при каждом
  старте контейнера: пользователи разлогинятся после перезапуска.
- PHP-настройки (лимиты загрузки, opcache) — `docker/php.ini`; конфиг nginx — `docker/nginx-app.conf`.
