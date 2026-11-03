# МОЙ.БЛОГ

Адаптивный сайт-блог: статьи с редактором, категории, комментарии с ответами и лайками,
профили авторов, поиск и 14 тем оформления.

Дипломный проект (ВКР). Монолит на Laravel + Inertia: сервер отдаёт Vue-страницы с props,
отдельного REST API нет.

[![CI](https://github.com/SadPepe/vkr-blog/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/SadPepe/vkr-blog/actions/workflows/ci.yml)

## Возможности

- **Статьи** — создание, редактирование и удаление через редактор TipTap: обложка,
  категория, автоматический slug, вставка изображений, очистка HTML перед сохранением
  (`App\Support\HtmlSanitizer`), удаление картинок, убранных из текста.
- **Лента** — сортировки (новые, старые, по просмотрам, лайкам, комментариям), фильтр по
  категориям, пагинация, блоки «Рекомендации» и «Похожие статьи».
- **Поиск** — по заголовку, описанию и тексту, с пагинацией; для кириллицы используется
  `ilike` PostgreSQL, поэтому поиск работает без учёта регистра.
- **Страница статьи** — прогресс чтения, метаданные, обложка с лайтбоксом, блок автора,
  «Поделиться» (копирование ссылки, Telegram, ВК), кнопка «Наверх», лайк статьи.
- **Комментарии** — ответы на комментарии, лайки, редактирование и удаление своих;
  счётчики пересчитываются только по комментариям верхнего уровня.
- **Профили** — общая двухколоночная вёрстка для своего профиля и страницы автора:
  обложка, аватар-инициалы, статистика, медиасетка из обложек статей, лента карточек.
  Настройки: данные профиля, обложка и фон, смена пароля, удаление аккаунта.
- **Авторизация** — Breeze: вход, регистрация, сброс пароля, подтверждение почты
  (маршруты есть, `MustVerifyEmail` отключён).
- **Оформление** — 14 палитр и светлый/тёмный режим, переключатель в навбаре,
  выбор сохраняется в `localStorage` и применяется до первой отрисовки.
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

Демо-пользователь после `--seed`: **test@example.com / password**.

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

## Тесты

```bash
php artisan test        # 48 тестов, 1 пропуск на sqlite (см. ниже)
make test               # то же в контейнере, если на хосте нет pdo_sqlite
```

Покрыто: вход/регистрация/сброс пароля/профиль (Breeze), локализация, статьи
(создание, права, slug, санитайзер HTML), комментарии и ответы со счётчиками, лайки,
поиск. Тест регистронезависимого поиска по кириллице пропускается на sqlite —
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

- Аватар не загружается (только инициалы); обложка и фон профиля — загружаются.
- Профиль выводит все статьи автора без пагинации; комментарии без пагинации.
- Черновиков, статусов публикации, тегов, RSS и уведомлений нет.
- Rate limit только на авторизацию и загрузку изображений; лайки и комментарии без лимита.
- Страница 404 автономная (Blade) и не следует выбранной теме.

## Документация

- [docs/state.md](docs/state.md) — что реализовано, дизайн-система, принятые решения, долги.
- [docs/roadmap.md](docs/roadmap.md) — бэклог с приоритетами и оценками.
- [docs/docker.md](docs/docker.md) — запуск в контейнерах, переменные, тома, тесты.

## Лицензия

MIT — как у Laravel skeleton, на котором построен проект.
