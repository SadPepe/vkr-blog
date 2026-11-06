# Docker: запуск сайта в контейнерах

Стек в контейнерах: `app` (PHP 8.4 FPM + nginx + supervisor в одном образе) и `db` (PostgreSQL 16).
Для HTTPS добавляется `caddy` (профиль `https`), который терминирует TLS и проксирует
запросы в `app`. Фронтенд (`npm run build`) и `composer install --no-dev` выполняются
**при сборке образа**, поэтому в рантайме Node и Composer не нужны.

## Требования

- Docker Engine 24+ и Docker Compose v2.24+ (флаг `env_file.required`)
- Свободный порт `8080` на localhost (или задайте другой через `APP_PORT`)
- Для HTTPS: домен, свободные `80`/`443` и A-запись — см. [docs/https.md](https.md)
- Локальный `.env` (он в `.gitignore`, но нужен как источник `APP_KEY` и настроек)

## Быстрый старт

```bash
cp .env.example .env        # если .env ещё нет
php artisan key:generate    # локально, чтобы APP_KEY попал в .env

docker compose up -d --build
# http://localhost:8080 — локальная отладка
```

Для публичного HTTPS — `docker compose --profile https up -d --build` после настройки
домена и переменных; пошагово — [docs/https.md](https.md).

При первом запуске entrypoint сам: дожидается PostgreSQL, применяет миграции,
создаёт симлинк `public/storage`, кэширует конфиг и шаблоны, затем стартует php-fpm и nginx.

Демо-данные (категории, тестовый пользователь и 40 статей):

```bash
DB_SEED=true docker compose up -d --build
```

Фотографии для статей уже лежат в образе — в `database/seeders/images`. `ArticleSeeder`
сжимает их в WebP и уменьшает без обрезки, поэтому отдельная папка с фото не нужна.
Повторно сиды не запускаются — после первого успеха создаётся маркер `storage/app/.seeded`.

## Переменные окружения

`docker-compose.yml` читает локальный `.env` (секция `env_file`) и переопределяет ключи,
специфичные для контейнеров (переменные из `environment` приоритетнее `.env`):

| Переменная | По умолчанию | Назначение |
|---|---|---|
| `APP_PORT` | `8080` | порт приложения на localhost (наружу не открывается) |
| `APP_BIND` | `127.0.0.1` | `0.0.0.0` — доступ по локальной сети/с ноутбука |
| `APP_URL` | `http://localhost:8080` | публичный адрес; для HTTPS — `https://домен` |
| `APP_DOMAIN` | — | домен для Caddy (профиль `https`) |
| `ACME_EMAIL` | — | email для Let's Encrypt |
| `SESSION_SECURE_COOKIE` | `false` | `true` при HTTPS |
| `PRIVATE_BETA` | `false` | `true` — noindex в `<head>` для закрытой беты |
| `REGISTRATION_INVITE_CODE` | — | код приглашения; пусто — регистрация открыта |
| `APP_KEY` | из `.env` | если пусто — генерируется временный ключ |
| `APP_ENV` / `APP_DEBUG` | из `.env` (`local`/`true`) | для продакшена — `production`/`false` |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | из `.env`, иначе `blog`/`blog`/`blog` | одинаково для `app` и `db` |
| `DB_SEED` | `false` | наполнить БД демо-данными при первом старте |
| `SEEDER_IMAGES_DIR` | `database/seeders/images` | своя папка с фото для `ArticleSeeder` (необязательно) |

`APP_URL` должен совпадать с реальным адресом сайта: от него зависят абсолютные ссылки
и `Storage::url()` для новых картинок. После изменения `.env` — `docker compose restart app`.

Логи контейнеров ограничены `10m × 3` через `logging` в `docker-compose.yml`.
Бэкапы БД и файлов — `sh scripts/backup.sh` (см. [docs/backup.md](backup.md)).

## Доступ по локальной сети

По умолчанию порт приложения слушает только `127.0.0.1`. Чтобы открыть сайт с телефона
или другого ноутбука в той же Wi-Fi:

```bash
# .env
APP_BIND=0.0.0.0
APP_URL=http://192.168.1.50:8080   # ваш LAN-IP

docker compose up -d --build
```

Разрешите входящий TCP `8080` в фаерволе ноутбука. Это HTTP без TLS: подходит для
демонстрации в доверенной сети, но не для интернета. Для удалённого доступа без
публикации портов подойдёт WireGuard/Tailscale; для публичного HTTPS — `docs/https.md`
(VPS) или [docs/home-hosting.md](home-hosting.md) (домашний ПК с белым IP).

## HTTPS

Публичный запуск — через профиль `https`: Caddy получает сертификат Let's Encrypt,
редиректит HTTP и проксирует в `app`. Нужны домен, свободные 80/443 и переменные
`APP_DOMAIN`, `ACME_EMAIL`, `APP_URL=https://…`, `SESSION_SECURE_COOKIE=true`.
Домашний сервер с белым IP, пробросом портов и требованиями 152-ФЗ —
[docs/home-hosting.md](home-hosting.md).
Пошагово, с проверками и типовыми ошибками — [docs/https.md](https.md).

## Данные и тома

- `db` → `db:/var/lib/postgresql/data` — база;
- `app` → `storage:/var/www/storage` — загруженные обложки и вставки редактора;
- `caddy` → `caddy_data:/data` — сертификаты и аккаунт ACME (не удалять при обновлениях).

Полный сброс (удалит БД, файлы и сертификаты):

```bash
docker compose down -v
```

## Полезные команды

```bash
docker compose logs -f app                 # логи php-fpm/nginx (LOG_CHANNEL=stderr)
docker compose logs -f caddy               # HTTPS/ACME, только с профилем https
docker compose exec app php artisan tinker # консоль Laravel
docker compose exec app sh                 # shell в контейнере
docker compose exec db psql -U postgres -d blog
docker compose restart app                 # после изменения .env
docker compose --profile https up -d       # поднять сайт с HTTPS
docker compose down                        # остановить (без удаления данных)
```

## Демо-картинки для статей

По умолчанию `ArticleSeeder` берёт фото из `database/seeders/images` — эта папка
уже входит в образ, поэтому демо-обложки появляются сразу после `DB_SEED=true`.
Каждое фото конвертируется в WebP и уменьшается до 1600 px по большей стороне
(без обрезки), затем копируется на диск `public` в `article-cards/` и `images/`.

Свои фотографии: положите их в папку и задайте `SEEDER_IMAGES_DIR`. В Docker
удобнее смонтировать её томом:

```yaml
# docker-compose.yml → services.app.volumes
- /путь/к/фото:/images:ro
```

и задать `SEEDER_IMAGES_DIR=/images` (или `/images/Подпапка`) при запуске с `DB_SEED=true`.
Допустимы JPG/JPEG/PNG/WebP. Если папки нет, статьи создадутся без картинок —
сайт продолжит работать.

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
работает так же, как в CI: 90 passed, 1 пропуск.

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
