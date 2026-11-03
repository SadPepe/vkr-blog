# Состояние проекта vkr-blog

Актуально на: 2026-11-02. Документ для передачи контекста в новую сессию.

## Что это

Дипломный проект: адаптивный сайт-блог (статьи, категории, комментарии с ответами,
лайки, просмотры, профили авторов, поиск, темы оформления). Монолит
Laravel 12 + Inertia 2 + Vue 3 + Tailwind 3 + PostgreSQL.

## Функциональность (реализовано)

- Посты: текст с картинками через TipTap, заголовок необязателен (пустой → в ленте
  показывается текст), отдельной обложки нет (превью = первая картинка текста),
  `excerpt` генерируется из текста (200 символов), slug — из заголовка/текста,
  пустой пост отклоняется валидацией. HTML очищается (`HtmlSanitizer`), удалённые
  из текста картинки удаляются с диска.
- Лента: сортировки (новые/старые/просмотры/лайки/комментарии), категории, пагинация,
  «Рекомендации» и «Похожие статьи».
- Поиск: по заголовку/описанию/тексту, `ilike` для кириллицы, пагинация.
- Статья: прогресс чтения, метаданные, обложка с лайтбоксом, блок автора, «Поделиться»
  (копирование ссылки, Telegram, ВК), кнопка «Наверх», комментарии с ответами, лайками,
  редактированием и удалением, лайк статьи.
- Профили: двухколоночная сетка (`ProfileLayout`) — шапка-обложка во всю ширину с
  наезжающим аватаром, левая колонка (имя, «на блоге с», описание, медиасетка 3×2 из
  обложек статей), правая (панель статистики + лента карточек как на главной). Общая для
  своего профиля и страницы автора; фон страницы на весь экран, без затемнения.
  Настройки: данные, загрузка обложки и фона, смена пароля, удаление аккаунта.
- Авторизация: Breeze (вход, регистрация, сброс пароля, подтверждение почты в маршрутах,
  но `MustVerifyEmail` отключён).
- Оформление: 14 тем, переключатель в навбаре, сохранение в `localStorage`,
  применение до первой отрисовки.
- Ошибки: брендовая 404 (standalone Blade), тосты для flash-сообщений.
- Локализация: `lang/ru` (валидация, пагинация, auth, passwords), `APP_LOCALE=ru`,
  `APP_NAME="МОЙ.БЛОГ"`.

## Дизайн-система

### Семантические токены (Tailwind)

Определены в `tailwind.config.js`, значения — CSS-переменные из `resources/css/app.css`.

| Класс | Переменная | Роль |
|---|---|---|
| `bg-page` | `--c-page` | фон страницы |
| `bg-surface` | `--c-surface` | карточки, панели, навбар |
| `bg-surface-muted` | `--c-surface-muted` | «утопленные» поля, hover, плейсхолдеры |
| `border-line` / `border-line-strong` | `--c-line` / `--c-line-strong` | границы, разделители |
| `text-content` | `--c-content` | основной текст |
| `text-muted` | `--c-muted` | вторичный текст, метаданные |
| `bg-accent` / `hover:bg-accent-hover` | `--c-accent` / `--c-accent-hover` | кнопки, активные состояния |
| `text-accent` | `--c-accent` | ссылки, акценты |
| `bg-accent-soft` | `--c-accent-soft` | мягкие плашки, бейджи |
| `text-accent-contrast` | `--c-accent-contrast` | текст на акцентной заливке |
| `*-accent-2` | `--c-accent-2` | второй цвет (градиент логотипа, `from-accent to-accent-2`) |

Правило: **новые цвета не хардкодить**. Только токены; для состояний — `dark:`-утилиты
(они работают во всех тёмных темах).

### Темы

- `data-theme` на `<html>` — палитра, `data-mode` (`light`/`dark`) — режим для `dark:`-вариантов.
- `tailwind.config.js`: `darkMode: ['class', '[data-mode="dark"]']`.
- Темы: базовые авторские `light` (крем + emerald) и `dark` (графит), плюс палитры DaisyUI:
  `cupcake`, `corporate`, `lofi`, `garden`, `retro`, `winter`, `dracula`, `business`,
  `night`, `forest`, `coffee`, `dim`.
- Палитры DaisyUI взяты из их CSS (OKLCH → sRGB) и разложены по токенам:
  `page = base-200`, `surface = base-100`, `surface-muted = base-300`,
  `content = base-content`, `accent = primary`, `accent-contrast = primary-content`,
  `muted` — смесь `base-content` с фоном с автоподгонкой до AA.
- Все темы проверены на контраст ≥ 4.5:1 (текст, вторичный текст, акцент, текст на акценте).
- Переключатель: `Navbar.vue` (меню в 2 колонки, светлые/тёмные), `utils` нет —
  логика в компоненте, id тем должны совпадать со списком в `app.blade.php`
  (там же фолбэк на светлую, если в `localStorage` неизвестный id).

## Ключевые технические решения

- **Превью в ленте** — `App\Support\PostPreview::make()`: берёт первые блоки `content`
  в авторском порядке (до 600 символов текста и 2 картинок), закрывает HTML и убирает
  полный `content` из props. Поэтому в ленте фото и текст идут так же, как в посте.
- **Изображение на странице поста** — отдельный hero-блок показывается только для старых
  обложек (`article-cards/`); если `card_image_path` уже есть в тексте, картинка не дублируется.

- **Заголовки вкладок** — только через `<Head>` на каждой странице (без ручного `document.title`).
- **Flash-сообщения** — `HandleInertiaRequests` шарит `flash.success/error`,
  `Components/FlashMessage.vue` показывает тосты (автоскрытие 5 c).
- **Вес props**: в лентах/сайдбарах используется `Article::CARD_COLUMNS`
  (id, title, slug, excerpt, card_image_path, category_slug, created_at, счётчики, user_id),
  полный `content` не передаётся; для автора — `with('user:id,name,avatar')`.
- **Права и валидация** — политики + `$request->validate` в контроллерах.
- **Категории** — `Category::articles()` исправлена на связь по `category_slug`/`slug`,
  счётчики через `withCount('articles')`.
- **Карточки**: `ArticleCard` (лента, ссылка-оверлей на всю карточку, `prefetch="hover"`),
  `ArticleGridCard` (сетки: поиск, профиль автора), `PopularArticles`, `RelatedArticles`.
- **Категории-сайдбар**: `CategoriesSidebar` — чипсы на мобиле, список со счётчиками на десктопе.
- **Поля форм** — `bg-surface-muted`, чтобы не сливаться с белыми карточками.
- **Футер** — прижат к низу: `Layout.vue` — flex-колонка `min-h-[calc(100vh-6rem)]`, `main.flex-1`.
- **Пагинация** — без `preserve-scroll`: после клика страница поднимается наверх.
- **Лайтбокс** — модальное окно: картинка `max-h-[78vh]`, `max-w-[min(1100px,92vw)]`,
  закрытие по Esc/клику вне, подсказка.
- **Контент статьи** — картинки по центру (`display:block; margin:1.5rem auto`),
  `max-height:70vh`, `loading="lazy"` проставляется при монтировании;
  вложенные списки имеют свой шаг отступа и маркеры (`circle`/`square`, `lower-alpha`/`lower-roman`).
- **Редактор** — те же правила списков/картинок в `.prose`, чтобы WYSIWYG совпадал со статьёй.
- **Производительность** — скелетон первой отрисовки в `app.blade.php` (удаляется при
  монтировании), скелетоны картинок, `prefetch="hover"` у ссылок, неблокирующие шрифты,
  payload ленты сокращён с 195 КБ до 21 КБ JSON.

## Компоненты (resources/js/Components)

`ArticleCard`, `ArticleGridCard`, `ArticleStats`, `PopularArticles`, `RelatedArticles`,
`CategoriesSidebar`, `Pagination`, `Navbar`, `Footer`, `FlashMessage`, `ConfirmDialog`,
`Modal`, `UserAvatar`, `LikeButton`, `TipTapEditor`, `Comments/*`, UI-кит
(`TextInput`, `TextArea`, `InputLabel`, `InputError`, `PrimaryButton`, `SecondaryButton`,
`DangerButton`, `Checkbox`, `Dropdown*`).

## Проверки

```bash
npm run build                                  # синтаксис Vue + сборка
php artisan test                               # 57 тестов, sqlite :memory: (1 пропуск на sqlite)
make test                                      # то же в контейнере: docker compose --profile test run --rm tests
php -l app/Http/Controllers/File.php           # синтаксис PHP
node_modules/.bin/prettier --write "resources/js/**/*.vue"   # форматирование (.prettierrc.json)
```

`php artisan test` работает без PostgreSQL: `phpunit.xml` + `.env.testing` переводят тесты
на sqlite `:memory:`. На хосте нужен `pdo_sqlite` (иначе — `make test` в контейнере,
там расширение есть).

## Известные ограничения / долги

- Поиск по кириллице: регистронезависимость проверяется только на PostgreSQL — на sqlite тест пропускается.
- Аватары: аксессор `avatar_url` есть, загрузить аватар нельзя.
- Обложка и фон профиля: поля `users.cover_image` / `users.background_image`, файлы
  в `profile/covers`, `profile/backgrounds` (диск `public`), до 5 МБ, JPG/PNG/WebP;
  выводятся в `Profile/Show` и `Authors/Show`.
- Комментарии без пагинации: все верхнеуровневые и ответы приходят одним списком.
- Нет черновиков/статусов публикации, тегов, RSS, sitemap, уведомлений.
- `MustVerifyEmail` отключён, почта в `log`.
- Rate limit: 10 комментариев/мин, 20 правок и удалений комментариев/мин,
  30 лайков (статьи и комментарии)/мин.
- Нет `srcset`/ресайза обложек; 500/403 — стандартные страницы Laravel.
- `PopularArticleController::index()` рендерит несуществующий `Pages/Popular/Index.vue`
  и не подключён в маршрутах (используется только `getPopularArticles()`).
- 404-страница автономная (Blade, свой CSS) — не следует выбранной теме.

## Окружение

- Dev: `php artisan serve` (порт может отличаться, проверяй фактический), `npm run dev`
  (Vite, `public/hot`).
- БД: PostgreSQL `blog` (см. `.env`), демо-данные — `php artisan migrate --seed`.
- Docker: `docker compose up -d --build` → http://localhost:8080, демо-данные —
  `DB_SEED=true docker compose up -d --build`; подробности в `docs/docker.md`.
- Симлинк картинок: `php artisan storage:link` (уже создан).
