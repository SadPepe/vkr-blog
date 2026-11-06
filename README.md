# МОЙ.БЛОГ

Адаптивный сайт-блог: статьи с редактором, категории, комментарии с ответами и лайками,
профили авторов, поиск и 14 тем оформления.

Дипломный проект (ВКР). Монолит на Laravel + Inertia: сервер отдаёт Vue-страницы с props,
отдельного REST API нет.

[![CI](https://github.com/SadPepe/vkr-blog/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/SadPepe/vkr-blog/actions/workflows/ci.yml)

## Возможности

- **Посты и статьи** — один формат: текст с картинками в редакторе TipTap, заголовок
  необязателен (без него лента показывает текст как есть), отдельной обложки нет —
  превью карточки берётся из первой картинки текста, анонс собирается из текста
  автоматически. Пустой пост (ни текста, ни картинки) не публикуется. HTML очищается
  перед сохранением (`App\Support\HtmlSanitizer`), убранные из текста картинки удаляются.
- **Лента** — посты «как есть»: первые блоки текста и картинок в авторском порядке
  (превью до 600 символов и двух изображений, дальше «Читать полностью»); сортировки
  (новые, старые, по просмотрам, лайкам, комментариям), фильтр по категориям, пагинация,
  блоки «Рекомендации» и «Похожие статьи».
- **Поиск** — по заголовку, описанию и тексту, с пагинацией; для кириллицы используется
  `ilike` PostgreSQL, поэтому поиск работает без учёта регистра.
- **Страница статьи** — прогресс чтения, метаданные, обложка с лайтбоксом, блок автора,
  «Поделиться» (копирование ссылки, Telegram, ВК), кнопка «Наверх», лайк статьи.
- **Комментарии** — ответы на комментарии, лайки, редактирование и удаление своих;
  счётчики пересчитываются только по комментариям верхнего уровня.
- **Профили** — общая двухколоночная вёрстка для своего профиля и страницы автора:
  обложка, аватар (или инициалы), статистика, медиасетка из обложек статей, лента карточек.
  Настройки: данные профиля, аватар/обложка/фон, смена пароля, удаление аккаунта.
- **Авторизация** — Breeze: вход по логину, регистрация (email необязателен, код
  приглашения при закрытой бете), сброс пароля по email, подтверждение почты
  (маршруты есть, `MustVerifyEmail` отключён).
- **Юридические документы** — политика обработки ПД, согласия на обработку и
  распространение, пользовательское соглашение и «Правообладателям»; согласия
  фиксируются при регистрации. Подробности — [docs/legal.md](docs/legal.md).
- **Оформление** — 16 палитр, включая светлый и тёмный режимы, переключатель в навбаре,
  выбор сохраняется в `localStorage` и применяется до первой отрисовки.
- **Страницы ошибок** — брендовые 403/404/419/500/503 в тёмной теме toner;
  автономный Blade без Vite и БД, поэтому работают даже при сбое приложения.
- **Локализация** — русский интерфейс и сообщения валидации (`lang/ru`), `APP_LOCALE=ru`.
- **Адаптивность** — mobile-first, проверено на 375 / 768 / 1024 / 1440 px.

## Стек

| Слой | Технологии |
|---|---|
| Backend | PHP 8.2+ (проверено на 8.4/8.5), Laravel 12 |
| Frontend | Vue 3 `<script setup>`, Inertia 2, Vite 6, Ziggy |
| Стили | Tailwind CSS 3 + `@tailwindcss/forms`, семантические токены тем |
| UI | PrimeVue 4, `vue-material-design-icons`, TipTap 2 |
| БД | PostgreSQL (обязательна для поиска по кириллице) |
| Тесты | PHPUnit 11 на sqlite `:memory:` |
| Инфраструктура | Docker (php-fpm + nginx + PostgreSQL), GitHub Actions |

## Требования

- PHP 8.2+ с расширениями `mbstring`, `intl`, `pdo_pgsql`, `pdo_sqlite` (для тестов)
- Composer 2
- Node.js 20+ и npm
- PostgreSQL 14+

## Установка

```bash
git clone git@github.com:SadPepe/vkr-blog.git
cd vkr-blog

composer install
cp .env.example .env
php artisan key:generate

# в .env: DB_CONNECTION=pgsql, DB_DATABASE=blog, DB_USERNAME, DB_PASSWORD
php artisan migrate --seed
php artisan storage:link

npm ci
npm run build        # или npm run dev — Vite с HMR

php artisan serve    # http://127.0.0.1:8000
```

Демо-пользователь после `--seed`: логин **test**, пароль **password** (email `test@example.com`).

Демонстрационные статьи (40 штук с текстом и рубриками) добавляются отдельно:

```bash
php artisan db:seed --class=ArticleSeeder
```

`ArticleSeeder` копирует фотографии из папки `SEEDER_IMAGES_DIR` (по умолчанию
`/home/anna/Изображения/Wallpapers/Best`) на диск `public`. Если папки нет, статьи
создадутся без обложек — сайт при этом полностью работоспособен.

## Запуск в Docker

Docker нужен, когда не хочется ставить PHP/PostgreSQL локально или нужно показать проект
на другой машине. Внутри — php-fpm + nginx, рядом контейнер PostgreSQL.

```bash
docker compose up -d --build                        # http://localhost:8080

# с демо-данными (категории, пользователь, 40 статей):
DB_SEED=true docker compose up -d --build
```

Локальный `.env` не обязателен: если он есть, из него берутся `APP_KEY` и остальные
настройки; если нет — entrypoint сгенерирует временный ключ и запустит миграции сам.

Полезные команды: `docker compose ps`, `docker compose logs -f app`,
`docker compose exec app sh`, `docker compose down`, `make test`.
Подробности и переменные окружения — в [docs/docker.md](docs/docker.md).

Для HTTPS: домен, A-запись на сервер и `docker compose --profile https up -d --build`;
подробности — [docs/https.md](docs/https.md).

Локально: `php artisan views:prune` (удаляет детальные просмотры старше 365 дней),
`php artisan schedule:list` — запланированные задачи (чистка идёт через `schedule:work`
в контейнере). Бэкап БД и файлов — `sh scripts/backup.sh`, подробности в
[docs/backup.md](docs/backup.md).

Закрытая бета: `PRIVATE_BETA=true` (noindex) и `REGISTRATION_INVITE_CODE` — без кода
аккаунт не создать. Перед публичным запуском: `PRIVATE_BETA=false`, код пустой,
`public/robots.txt` снова разрешить.

## Тесты

```bash
php artisan test        # 90 passed, 1 skipped на sqlite (см. ниже)
make test               # то же в контейнере, если на хосте нет pdo_sqlite
```

Покрыто: вход/регистрация/сброс пароля/профиль (Breeze), локализация, посты
(создание без заголовка, права, slug, санитайзер HTML, пустой пост), комментарии
и ответы со счётчиками, лайки, поиск, лимиты запросов, превью ленты
(порядок блоков, лимит картинок, обрезка длинного текста). Тест регистронезависимого поиска по кириллице пропускается на sqlite —
он проверяет поведение PostgreSQL (`ilike`).

Тесты не трогают боевую базу: `.env.testing` и `phpunit.xml` переводят их на sqlite
`:memory:`, PostgreSQL для прогона не нужен. CI прогоняет тот же набор на каждый push.

## CI

[GitHub Actions](.github/workflows/ci.yml) на каждый push в `main`/`develop` и на pull request:
`composer install` → `npm ci` → `npm run build` → `php artisan test`.
Результаты — во вкладке **Actions** репозитория.

## Структура репозитория

```
app/Http/Controllers/     Article, Author, Comment, Like, Image, Profile + Auth/*
app/Models/               Article, Comment, User, Category, Like, CommentLike, ArticleView
app/Traits/               HandlesArticles: slug, сортировка, удаление картинок контента
app/Support/              HtmlSanitizer — белый список HTML для статей
resources/js/Pages/       Inertia-страницы (Home, Articles/*, Authors/Show, Profile/*, ...)
resources/js/Components/  ArticleCard, Navbar, Footer, Comment*, TipTapEditor, Pagination, ...
resources/css/app.css     палитры всех тем (CSS-переменные), базовые стили
database/seeders/         CategorySeeder, ArticleSeeder (40 статей)
lang/ru/                  валидация, пагинация, auth, passwords
tests/Feature/            Feature-тесты (Auth, Profile, локализация)
docker/                   nginx, php.ini, supervisor, entrypoint
docs/                     state.md, roadmap.md, docker.md
```

## Как устроено

Запрос идёт по цепочке **маршрут → контроллер → `Inertia::render('Page', props)` → Vue-страница**
(`resources/js/Pages`); общие props (`auth.user`, `categories`) приходят из
`HandleInertiaRequests::share()`. Навигация — `<Link :href="route('...')">`, формы — `useForm`
из `@inertiajs/vue3`.

Решения, которые стоит знать:

- **Счётчики денормализованы**: `views_count`, `likes_count`, `comments_count` обновляются
  `increment`/`decrement`, а не считаются на лету в лентах.
- **Темы — только CSS-переменные**: на `<html>` два атрибута, `data-theme` (палитра) и
  `data-mode` (`light`/`dark`); компоненты используют токены `bg-surface`, `text-content`,
  `text-muted`, `bg-accent` и т. п. Хардкод цветов ломает 14 тем.
- **HTML статей санитизируется** на сервере (`HtmlSanitizer`), на клиенте выводится через `v-html`.
- **Права** — политики `ArticlePolicy` / `CommentPolicy`: редактировать и удалять может только владелец.
- **Slug** генерируется `HandlesArticles::generateUniqueSlug()` и меняется при смене заголовка.

## Известные ограничения

- Комментарии выводятся без пагинации (все сразу, вместе с ответами).
- Черновиков, статусов публикации, тегов, RSS и уведомлений нет.
- Комментарий на статью выводится целиком до лайтбокса: очень длинные тексты
  грузятся в props страницы.

## Документация

- [docs/state.md](docs/state.md) — что реализовано, дизайн-система, принятые решения, долги.
- [docs/roadmap.md](docs/roadmap.md) — бэклог с приоритетами и оценками.
- [docs/docker.md](docs/docker.md) — запуск в контейнерах, переменные, тома, тесты.
- [docs/backup.md](docs/backup.md) — бэкапы БД и файлов, восстановление.
- [docs/https.md](docs/https.md) — Caddy, Let's Encrypt, первый запуск.
- [docs/legal.md](docs/legal.md) — юридический каркас и чек-лист перед запуском.

## Лицензия

MIT — как у Laravel skeleton, на котором построен проект.
