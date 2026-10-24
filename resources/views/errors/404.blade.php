<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Страница не найдена</title>
    <style>
        :root {
            --bg: #f8fafc;
            --fg: #1f2937;
            --muted: #6b7280;
            --card: #ffffff;
            --border: #e5e7eb;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #1c1c1c;
                --fg: #d6d3d1;
                --muted: #a8a29e;
                --card: #252525;
                --border: #3f3f46;
            }
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            color: var(--fg);
            font-family: Figtree, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            padding: 24px;
        }

        .card {
            max-width: 440px;
            width: 100%;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
        }

        .code {
            font-size: 84px;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(90deg, #4f46e5, #9333ea);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        h1 {
            margin: 16px 0 8px;
            font-size: 22px;
        }

        p {
            margin: 0 0 24px;
            color: var(--muted);
            line-height: 1.6;
        }

        a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(90deg, #4f46e5, #9333ea);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }

        a.secondary {
            background: transparent;
            color: var(--fg);
            border: 1px solid var(--border);
            box-shadow: none;
        }

        a.secondary:hover {
            box-shadow: none;
            background: rgba(79, 70, 229, 0.06);
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="code">404</div>
        <h1>Страница не найдена</h1>
        <p>Возможно, статья была удалена или ссылка устарела.</p>
        <div class="actions">
            <a href="/">На главную</a>
            <a class="secondary" href="javascript:history.back()">Назад</a>
        </div>
    </main>
</body>
</html>