<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'МОЙ.БЛОГ') }}</title>

    <!-- Тема применяется до первой отрисовки, чтобы не было вспышки светлой темы -->
    <script>
        (function () {
            try {
                var lightThemes = ['light', 'cupcake', 'corporate', 'lofi', 'garden', 'retro', 'winter'];
                var knownThemes = ['light', 'cupcake', 'corporate', 'lofi', 'garden', 'retro', 'winter', 'dark', 'dracula', 'business', 'night', 'forest', 'coffee', 'dim'];

                var saved = localStorage.getItem('theme');
                if (!saved || knownThemes.indexOf(saved) === -1) {
                    saved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }

                document.documentElement.setAttribute('data-theme', saved);
                document.documentElement.setAttribute(
                    'data-mode',
                    lightThemes.indexOf(saved) === -1 ? 'dark' : 'light'
                );
                document.documentElement.style.colorScheme =
                    lightThemes.indexOf(saved) === -1 ? 'dark' : 'light';
            } catch (e) {}
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style"
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap">
    <link rel="stylesheet" media="print" onload="this.media='all'"
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap">
    <noscript>
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap">
    </noscript>

    <!-- Scripts -->

    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="antialiased bg-page text-content">
    {{-- Скелетон первой отрисовки: виден сразу, пока грузится и выполняется JS --}}
    <div id="boot-skeleton" aria-hidden="true">
        <div class="bs-nav">
            <div class="bs-logo"></div>
            <div class="bs-search"></div>
            <div class="bs-actions">
                <div class="bs-dot"></div>
                <div class="bs-pill"></div>
            </div>
        </div>
        <div class="bs-layout">
            <div class="bs-col bs-col-side">
                <div class="bs-block" style="height: 220px"></div>
            </div>
            <div class="bs-col bs-col-main">
                <div class="bs-line bs-line-title"></div>
                <div class="bs-card"></div>
                <div class="bs-card"></div>
                <div class="bs-card"></div>
            </div>
            <div class="bs-col bs-col-side">
                <div class="bs-block" style="height: 260px"></div>
                <div class="bs-block" style="height: 180px"></div>
            </div>
        </div>
    </div>

    @inertia

    <style>
        #boot-skeleton {
            padding-top: 96px;
            max-width: 1500px;
            margin: 0 auto;
        }

        #boot-skeleton .bs-nav {
            position: fixed;
            top: 0;
            inset-inline: 0;
            height: 64px;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 0 16px;
            background: rgb(var(--c-surface, 255 255 255) / .85);
            border-bottom: 1px solid rgb(var(--c-line, 228 221 209));
        }

        #boot-skeleton .bs-logo {
            width: 120px;
            height: 28px;
            border-radius: 6px;
        }

        #boot-skeleton .bs-search {
            margin: 0 auto;
            width: min(360px, 50%);
            height: 34px;
            border-radius: 6px;
        }

        #boot-skeleton .bs-actions {
            display: flex;
            gap: 10px;
        }

        #boot-skeleton .bs-dot {
            width: 34px;
            height: 34px;
            border-radius: 50%;
        }

        #boot-skeleton .bs-pill {
            width: 96px;
            height: 34px;
            border-radius: 6px;
        }

        #boot-skeleton .bs-layout {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr) 300px;
            gap: 32px;
            padding: 32px 24px;
        }

        #boot-skeleton .bs-col {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        #boot-skeleton .bs-line-title {
            width: 180px;
            height: 24px;
            border-radius: 6px;
        }

        #boot-skeleton .bs-card {
            height: 320px;
            border-radius: 8px;
        }

        #boot-skeleton .bs-block {
            border-radius: 8px;
        }

        #boot-skeleton .bs-logo,
        #boot-skeleton .bs-search,
        #boot-skeleton .bs-dot,
        #boot-skeleton .bs-pill,
        #boot-skeleton .bs-line,
        #boot-skeleton .bs-card,
        #boot-skeleton .bs-block {
            background: linear-gradient(
                90deg,
                rgb(var(--c-surface-muted, 240 235 226)) 25%,
                rgb(var(--c-line, 228 221 209) / 0.45) 37%,
                rgb(var(--c-surface-muted, 240 235 226)) 63%
            );
            background-size: 400% 100%;
            animation: bs-shimmer 1.4s ease infinite;
        }

                        @keyframes bs-shimmer {
            0% { background-position: 100% 0; }
            100% { background-position: 0 0; }
        }

        @media (max-width: 1023px) {
            #boot-skeleton .bs-layout {
                grid-template-columns: 1fr;
                padding: 24px 16px;
            }

            #boot-skeleton .bs-col-side {
                display: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            #boot-skeleton * {
                animation: none !important;
            }
        }
    </style>
</body>

</html>