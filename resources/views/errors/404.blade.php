<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Страница не найдена — МОЙ.БЛОГ</title>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #F5F1E8;
            color: #1c1917;
            font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, sans-serif;
        }

        .card {
            max-width: 520px;
            width: 100%;
            text-align: center;
            border: 1px solid rgba(17, 24, 39, .08);
            border-radius: 12px;
            background: #fff;
            padding: 40px 28px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: -.01em;
            text-decoration: none;
            color: inherit;
            margin-bottom: 24px;
        }

        .logo span.mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: linear-gradient(135deg, #059669, #0d9488);
            color: #fff;
        }

        .logo em {
            font-style: normal;
            background: linear-gradient(90deg, #059669, #0d9488);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 56px;
            line-height: 1;
            color: #059669;
        }

        h2 {
            margin: 0 0 12px;
            font-size: 20px;
        }

        p {
            margin: 0 0 28px;
            color: #6b7280;
            font-size: 15px;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            font: inherit;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: box-shadow .2s, transform .2s;
        }

        .btn-primary {
            background: linear-gradient(90deg, #059669, #0d9488);
            color: #fff;
            box-shadow: 0 4px 12px rgba(5, 150, 105, .25);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(5, 150, 105, .3);
        }

        .btn-secondary {
            border: 1px solid #d1d5db;
            color: #374151;
            background: #ffffff;
        }

        .btn-secondary:hover {
            border-color: #6ee7b7;
            color: #047857;
        }

        .btn:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, .35);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
            }

            body {
                background: #151515;
                color: #e7e5e4;
            }

            .card {
                background: #252525;
                border-color: rgba(255, 255, 255, .06);
            }

            h2 {
                color: #e7e5e4;
            }

            p {
                color: #a8a29e;
            }

            .btn-secondary {
                background: rgba(255, 255, 255, .05);
                border-color: rgba(255, 255, 255, .12);
                color: #e7e5e4;
            }
        }
    </style>
</head>

<body>
    <main class="card">
        <a class="logo" href="/">
            <span class="mark">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" />
                </svg>
            </span>
            МОЙ.<em>БЛОГ</em>
        </a>

        <h1>404</h1>
        <h2>Страница не найдена</h2>
        <p>
            Возможно, статья была удалена или адрес набран с ошибкой.
            Вернитесь в ленту — там точно есть что почитать.
        </p>

        <div class="actions">
            <a class="btn btn-primary" href="/">На главную</a>
            <a class="btn btn-secondary" href="/search">Поиск по блогу</a>
            <button class="btn btn-secondary" type="button" onclick="history.back()">Назад</button>
        </div>
    </main>
</body>

</html>
