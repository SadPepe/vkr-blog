# AGENTS.md — vkr-blog

Дипломный проект (ВКР): адаптивный сайт-блог. Монолит **Laravel + Inertia**, отдельного REST API нет:
маршрут → контроллер → `Inertia::render()` → Vue-страница с props.

Публичная часть: лента постов, статьи, категории, комментарии с ответами, лайки, просмотры, профили,
поиск, 16 тем оформления. Служебная: админка и модерация — **Filament 4** на `/admin`.

Читай этот файл первым. Детали — в `docs/` (таблица в конце).

## Стек

| Слой | Технологии |
|---|---|
| Backend | Laravel 12, PHP ^8.2, PostgreSQL (база `blog`, см. `.env`) |
| Frontend | Vue 3 `<script setup>`, Inertia 2, Vite 6, Ziggy (`route()`), Tailwind 3 |
| UI | PrimeVue 4, `vue-material-design-icons`, кастомные компоненты |
| Редактор | Tiptap 2 (`Components/TipTapEditor.vue`), `browser-image-compression` |
| Админка | Filament 4 (`app/Filament`, `app/Providers/Filament/AdminPanelProvider.php`) |
| Тесты | PHPUnit на sqlite `:memory:` (`phpunit.xml`, `.env.testing`); на 2026-11-12 — 153 passed, 2 skipped |
| Деплой | Docker Compose: `app` (php-fpm + nginx), `db`, `caddy`, профиль `tests` |

## Команды

```bash
make serve                     # php artisan serve :8001 с расширениями из php-ini/
npm run dev                    # Vite :5173 (наличие public/hot = dev-режим)
npm run build                  # проверка сборки Vue

PHP_INI_SCAN_DIR="$PWD/php-ini" php artisan test   # тесты на хосте (нужен pdo_sqlite)
make test                      # те же тесты в контейнере

php artisan migrate --seed     # схема + демо-данные (осторожно с рабочей БД)
php artisan filament:assets    # если /admin без стилей (ассеты не в git)
php artisan route:list --path=articles
node_modules/.bin/prettier --write "resources/js/**/*.vue"
```

Локальный PHP собран **без `intl`, `pdo_sqlite` и `exif`** — расширения подключаются каталогом
`php-ini/` (см. `make serve`). Без него тесты на хосте падают, Filament не стартует, а `ImageStore`
не применяет EXIF-ориентацию (повёрнутые фото с телефона сохраняются боком).

## Карта репозитория

```
routes/web.php            публичные и auth-маршруты; routes/auth.php — Breeze
app/Http/Controllers/     Article, Author, Comment, Like, Image, PopularArticle, Report, Profile
app/Http/Middleware/      HandleInertiaRequests (общие props: auth.user, categories), EnsureNotBlocked
app/Models/               Article, Comment, User, Category, Like, CommentLike, ArticleView, Report, Announcement
app/Traits/HandlesArticles.php    slug, сортировка, удаление картинок контента
app/Support/HtmlSanitizer.php    белый список HTML для content
app/Support/PostPreview.php      превью поста для ленты
app/Policies/             Article, Comment, Category, User, Report, Announcement
app/Filament/Resources/   статьи, комментарии, пользователи, категории, жалобы, объявления
resources/js/Pages/       Inertia-страницы (Home, Articles/*, Authors/Show, Profile/*, Legal/*, Auth/*)
resources/js/Components/  FeedPost (лента), ArticleGridCard, Navbar, ImageLightbox, ReportDialog, ...
resources/js/Layouts/     Layout.vue — Navbar + <main> + Footer
resources/css/app.css     палитры тем (CSS-переменные), типографика, скроллбары
docs/                     состояние проекта, бэклог, docker, хостинг, юр. документы
docker/entrypoint.sh      миграции, ассеты Filament, кэши при старте контейнера
```

## Инварианты (не нарушать)

1. **Валидация — в контроллере** через `$request->validate([...])` (FormRequest только у Profile/Auth).
   Пост: `title` nullable ≤ 255, `content` required ≤ 200 000, `excerpt` не принимается (генерируется),
   комментарий ≤ 1000. Пустой пост (ни текста, ни `<img>`) отклоняется `ValidationException`.
2. **HTML поста** сохранять только после `HtmlSanitizer::sanitize()`; выводить через `v-html`.
   Новые теги/атрибуты — сначала в белый список санитайзера.
3. **Slug** — `generatePostSlug()`: из заголовка, иначе из текста, иначе `post-xxxxxxxx`;
   в URL биндинг `{article:slug}`.
4. **Счётчики** `views_count`, `likes_count`, `comments_count` денормализованы — только
   `increment`/`decrement`/`sync*Count`, не считать на лету в лентах. Комментарии — только верхнего уровня.
5. **Статусы статьи**: `draft` / `published` / `hidden` (`Article::STATUS_*`). В публичных запросах —
   только `published` (scope `published`). Комментарии скрываются полем `hidden_at`.
6. **Картинки** — диск `public`: вставки редактора в `images/`, `card_image_path` — производная от
   первой картинки контента. Удалять файлы вместе с записью.
7. **Права**: `Gate::authorize('update'|'delete', ...)` + политики (правит только владелец);
   в админку пускает `User::canAccessPanel()` — роль `moderator`/`admin` и не заблокирован.
8. **Зигги**: ссылки только `route('name', params)`, без хардкода путей.
9. **Поиск на pgsql** — `ilike`, иначе ломается регистр кириллицы.
10. **Цвета — только токены**: `bg-page`, `bg-surface(-muted)`, `border-line(-strong)`, `text-content`,
    `text-muted`, `bg-accent*`, `text-accent*` + `dark:`-варианты. Никаких `stone-*`, `bg-white`, `#hex`:
    от этого зависят все 16 тем.
11. **Заголовок страницы** — `<Head :title="...">`, без `document.title`.
12. **Фидбэк** — через `flash.success` / `flash.error` из общих props; никаких `alert()`/`confirm()`.
13. **Лента не получает `content`**: `PostPreview::attachPaginator()` подмешивает `preview`
    и скрывает полный HTML (он грузится лениво через `articles.excerpt`).

## Темы

На `<html>` два атрибута: `data-theme` (палитра, 16 штук) и `data-mode` (`light`/`dark`, включает
`dark:` через `darkMode: ['class', '[data-mode="dark"]']`). Переключатель — `Navbar.vue` (меню в 2 колонки,
`localStorage.theme`); до отрисовки тему ставит инлайн-скрипт в `app.blade.php` — там же список известных id
и фолбэк. Новая тема = блок переменных в `app.css` + строка в `themes` (`Navbar.vue`) + id в `app.blade.php`.
Контраст каждой палитры проверять: текст ≥ 4.5:1 к `page` и `surface`.

## Ветки, CI и деплой

| Ветка | Роль |
|---|---|
| `redesign/underground` | прод: её тянет сервер, руками не коммитим |
| `redesign/underground-dev` | рабочая: здесь правки и коммиты |
| `main` | легаси, не деплоится |

- Выпуск: `git checkout redesign/underground && git merge --ff-only redesign/underground-dev && git push`.
  `--ff-only` — предохранитель: прямой коммит в прод сразу видно.
- CI (`.github/workflows/ci.yml`) гоняет `composer install`, `npm ci`, `npm run build`, `php artisan test`
  на пушах в обе ветки и на всех PR. Бейдж в README — на прод-ветке.
- Сервер — домашний ПК (Windows + WSL + Docker), `.env` там **свой и в git не входит**. Секреты и сетевые
  настройки (`APP_URL`, `APP_DOMAIN`, `ACME_EMAIL`, `DB_*`, `SESSION_SECURE_COOKIE`) не коммитить.
- Выкладка на сервере:
  `sh scripts/backup.sh` → `php artisan down` → `git pull --ff-only` → `docker compose build app`
  → `docker compose up -d` → `php artisan up`. Entrypoint сам делает `storage:link`, `migrate --force`,
  `filament:assets`, `config:cache`, `view:cache`. `restart` вместо `build`/`up` — недостаточно.
- После первой выкладки у существующих аккаунтов `role = 'user'`; чтобы войти в `/admin`, выдать роль:
  `update users set role='admin' where email='...';`
- `APP_TIMEZONE` по умолчанию `Europe/Moscow` (`config/app.php`).

## Проверка перед коммитом

1. `PHP_INI_SCAN_DIR="$PWD/php-ini" php artisan test` (или `make test`) — должно быть зелёно.
2. `npm run build` — если правил `.vue`/`.js`/`.css`.
3. Для UI — проверить 375 / 768 / 1024 / 1440 px, горизонтальный скролл запрещён (`overflow-x: clip`).
4. Секреты и абсолютные пути (`/home/...`) в диффе не допускаются.

## Грабли

- `public/build`, `public/{css,js,fonts}/filament`, `public/hot`, `storage/.tmp-config` — генерируемые,
  в git не входят. `public/hot` означает, что страница собирается Vite-сервером, а не из `public/build`.
- После `docker compose --profile test` каталоги `storage/framework/testing/disks/public/*` могут стать
  `nobody:nobody 700` — хост-тесты падают с `Permission denied`. Лечится удалением этих каталогов
  (например, из контейнера), не правкой тестов.
- Классы, которые создаются в JS (`document.createElement`, `classList.add`), Tailwind не находит в
  `.vue` и вырезает из `@layer components` — их надо перечислять в `safelist` (`tailwind.config.js`).
  Пример: фасад видео `video-embed__*`.
- `composer.lock` / `package-lock.json` не править руками; `vendor/`, `node_modules/`, `public/build` — не трогать.
- Миграции на рабочей БД — только после `scripts/backup.sh`; rollback удаляет данные (`reports`, `announcements`).

## Завершение задачи: память для новой сессии

Чтобы новая сессия не восстанавливала контекст по диффу, перед финальным ответом
агент обязан оставить короткую пометку:

1. **UI/UX, компоненты, стили** — обновить `docs/redesign-notes.md`: что изменено,
   какие решения приняты, какие файлы/маршруты затронуты. 3–8 пунктов, без пересказа кода.
2. **Backend, маршруты, данные, тесты** — при необходимости добавить краткий блок
   в `docs/state.md` или сослаться оттуда на `docs/redesign-notes.md`.
3. В конце пометки указать **дату, hash последнего коммита и результат проверок**
   (`php artisan test`, `npm run build`).
4. Не дублировать длинные диффы и не писать «всё готово» без конкретики.

Последняя зафиксированная итерация: **2026-11-13, `028d6b3`** — закрепление поста
автора (`is_pinned`/`pinned_at`), черновики с фото в IndexedDB, шрифты Golos Text
700/800 и кегли заголовков, `BackToTop`/`ArticleActionsMenu`, LAN-хост Vite для
проверки с телефона. Проверки: `npm run build` — ok, `php artisan test` —
173 passed, 2 skipped. Детали — `docs/redesign-notes.md`.

## Документация

| Файл | О чём |
|---|---|
| `docs/state.md` | что реализовано, дизайн-система, решения, долги |
| `docs/roadmap.md` | бэклог с приоритетами и оценками |
| `docs/management.md` | админка, модерация, роли, жалобы, объявления |
| `docs/docker.md` | контейнеры, переменные, тома, тесты |
| `docs/home-hosting.md` | домашний сервер: белый IP, Caddy, HTTPS, обновления |
| `docs/https.md`, `docs/backup.md`, `docs/legal.md` | TLS, бэкапы, юридические страницы |
| `docs/redesign-notes.md`, `docs/redesign-prompt.md` | дизайн-направление и его правила |
