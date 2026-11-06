<!DOCTYPE html>
<html lang="ru" data-theme="toner" data-mode="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#121110">
    <title>@yield('title', 'Ошибка') — Челны подземка</title>

    <link rel="preload" href="/fonts/golos-text-400-cyrillic.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/unbounded-600-cyrillic.woff2" as="font" type="font/woff2" crossorigin>

    <style>
        /* Автономная страница ошибки: без Vite, без БД и без JS.
           Палитра синхронизирована с [data-theme="toner"] в resources/css/app.css. */
        @font-face {
            font-family: 'Golos Text';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('/fonts/golos-text-400-cyrillic.woff2') format('woff2');
            unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
        }

        @font-face {
            font-family: 'Golos Text';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('/fonts/golos-text-400-latin.woff2') format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        @font-face {
            font-family: 'Golos Text';
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url('/fonts/golos-text-600-cyrillic.woff2') format('woff2');
            unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
        }

        @font-face {
            font-family: 'Golos Text';
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url('/fonts/golos-text-600-latin.woff2') format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        @font-face {
            font-family: 'Unbounded';
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url('/fonts/unbounded-600-cyrillic.woff2') format('woff2');
            unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
        }

        @font-face {
            font-family: 'Unbounded';
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url('/fonts/unbounded-600-latin.woff2') format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }

        :root {
            color-scheme: dark;

            --c-page: 18 17 16;
            --c-surface: 26 25 23;
            --c-surface-muted: 35 33 32;
            --c-line: 53 50 46;
            --c-line-strong: 86 81 74;
            --c-content: 231 224 212;
            --c-muted: 176 167 155;
            --c-accent: 255 90 54;
            --c-accent-hover: 255 122 92;
            --c-accent-contrast: 20 18 16;
            --c-accent-2: 201 191 175;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100svh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 28px;
            padding: clamp(20px, 4vw, 48px);
            background:
                radial-gradient(1100px 620px at 50% -12%, rgb(var(--c-accent) / 0.10), transparent 62%),
                rgb(var(--c-page));
            color: rgb(var(--c-content));
            font-family: 'Golos Text', -apple-system, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        ::selection {
            background: rgb(var(--c-accent) / 0.28);
            color: rgb(var(--c-content));
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: rgb(var(--c-content));
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        .brand svg {
            width: 26px;
            height: 26px;
            color: rgb(var(--c-accent));
            flex: none;
        }

        .brand .accent {
            color: rgb(var(--c-accent));
        }

        .brand:hover .accent,
        .brand:focus-visible .accent {
            color: rgb(var(--c-accent-hover));
        }

        .card {
            position: relative;
            width: 100%;
            max-width: 620px;
            overflow: hidden;
            border: 1px solid rgb(var(--c-line));
            border-radius: 8px;
            background: rgb(var(--c-surface));
            padding: clamp(28px, 6vw, 56px);
            box-shadow: 0 24px 60px rgb(0 0 0 / 0.35);
        }

        .card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 2px;
            background: linear-gradient(90deg, rgb(var(--c-accent)), rgb(var(--c-accent-2)));
        }

        .eyebrow {
            margin: 0 0 14px;
            color: rgb(var(--c-muted));
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .code {
            margin: 0 0 10px;
            color: rgb(var(--c-accent));
            font-family: 'Unbounded', 'Golos Text', sans-serif;
            font-size: clamp(64px, 17vw, 128px);
            font-weight: 600;
            line-height: 0.85;
            letter-spacing: -0.05em;
        }

        h1 {
            margin: 0 0 12px;
            color: rgb(var(--c-content));
            font-size: clamp(22px, 4vw, 30px);
            font-weight: 600;
            line-height: 1.15;
            letter-spacing: -0.025em;
            text-wrap: balance;
        }

        .message {
            max-width: 52ch;
            margin: 0;
            color: rgb(var(--c-muted));
            font-size: clamp(15px, 2vw, 16px);
            line-height: 1.65;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 20px;
            border: 1px solid transparent;
            border-radius: 6px;
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        .btn-primary {
            background: rgb(var(--c-accent));
            color: rgb(var(--c-accent-contrast));
        }

        .btn-primary:hover {
            background: rgb(var(--c-accent-hover));
        }

        .btn-secondary {
            border-color: rgb(var(--c-line-strong));
            background: rgb(var(--c-surface-muted));
            color: rgb(var(--c-content));
        }

        .btn-secondary:hover {
            border-color: rgb(var(--c-accent));
            color: rgb(var(--c-accent));
        }

        .btn:focus-visible,
        .brand:focus-visible {
            outline: 2px solid rgb(var(--c-accent));
            outline-offset: 3px;
        }

        .foot {
            margin: 0;
            color: rgb(var(--c-muted));
            font-size: 12px;
            line-height: 1.5;
            text-align: center;
        }

        .foot a {
            color: rgb(var(--c-muted));
            text-underline-offset: 3px;
            transition: color 0.15s ease;
        }

        .foot a:hover {
            color: rgb(var(--c-accent));
        }

        @media (max-width: 460px) {
            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .btn,
            .foot a,
            .brand .accent {
                transition: none;
            }
        }
    </style>
</head>
<body>
    <a class="brand" href="{{ url('/') }}" aria-label="Челны подземка — на главную">
        <svg fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
            viewBox="0 0 24 24" aria-hidden="true">
            <path d="M4 21V10.5a8 8 0 0 1 16 0V21" />
            <path d="M8 21v-4.5h4V12h4" />
        </svg>
        <span>Челны <span class="accent">подземка</span></span>
    </a>

    <main class="card" aria-labelledby="error-heading">
        <p class="eyebrow">@yield('eyebrow', 'Ошибка')</p>
        <p class="code" aria-hidden="true">@yield('code', '')</p>
        <h1 id="error-heading">@yield('heading', 'Что-то пошло не так')</h1>
        <p class="message">@yield('message')</p>

        <div class="actions">
            @yield('actions')
        </div>
    </main>

    <p class="foot">
        Челны подземка · {{ date('Y') }} · <a href="{{ url('/') }}">на главную</a>
    </p>
</body>
</html>
