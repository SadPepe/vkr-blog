# Шрифты

Сайт использует два шрифта, оба распространяются по SIL Open Font License 1.1:

- **Golos Text** — основной интерфейс, веса 400/500/600, плюс 700 и 800 для жирного
  текста и заголовков (в тёмных темах жирный набирается 800: светлые штрихи на тёмном
  фоне «расплываются» и разница с обычным текстом теряется);
- **Unbounded** — акцидентный/дисплейный, веса 600/800.

Файлы `.woff2` скачаны с Google Fonts и разложены по сабсетам (cyrillic, cyrillic-ext,
latin, latin-ext). `@font-face` — в `resources/css/fonts.css`; внешних запросов к
`fonts.googleapis.com` больше нет. Это важно и для 152-ФЗ: IP посетителя не уходит
в Google при загрузке страницы.

Лицензии:

- `OFL-GolosText.txt` — https://github.com/googlefonts/golos-text
- `OFL-Unbounded.txt` — https://github.com/googlefonts/unbounded

При обновлении шрифтов скачайте новые `.woff2` и обновите `unicode-range` в
`resources/css/fonts.css` (он скопирован из CSS Google Fonts).
