# HTTPS: Caddy + Let's Encrypt

Схема:

```
интернет → Caddy :80/:443 → app (nginx + php-fpm) :80 (внутри compose)
```

Caddy сам получает сертификат Let's Encrypt, перенаправляет HTTP на HTTPS и продлевает
сертификат. Приложение наружу не смотрит: порт `8080` привязан к `127.0.0.1` и нужен
только для локальной отладки.

## Что нужно заранее

- **Домен** и A-запись (при IPv6 — AAAA) на IP сервера. Проверить: `dig +short ваш-домен.ру`.
- **Открытые порты** `80/tcp` и `443/tcp` (для HTTP/3 — ещё `443/udp`). Порт 80 нужен
  для проверки домена и редиректа; закрывать его нельзя.
- **Свободные 80/443 на хосте**: если там уже висит другой nginx/apache — остановить
  или настроить его как прокси на Caddy.
- **`.env`**: `APP_KEY`, `APP_URL=https://ваш-домен.ру`, `APP_DOMAIN=ваш-домен.ру`,
  `ACME_EMAIL=ваш@email`, `SESSION_SECURE_COOKIE=true`, `APP_ENV=production`,
  `APP_DEBUG=false`.

## Запуск

```bash
# 1. Домен уже указывает на сервер, порты открыты
dig +short ваш-домен.ру

# 2. В .env
# APP_URL=https://ваш-домен.ру
# APP_DOMAIN=ваш-домен.ру
# ACME_EMAIL=ваш@email
# SESSION_SECURE_COOKIE=true
# APP_ENV=production
# APP_DEBUG=false

# 3. Поднять приложение и Caddy
docker compose --profile https up -d --build

# 4. Логи выдачи сертификата
docker compose logs -f caddy
```

Проверка:

```bash
curl -I https://ваш-домен.ру/up          # 200
curl -I http://ваш-домен.ру             # 301/308 на https
curl -s https://ваш-домен.ру/ | head    # страница отдаётся
```

Снаружи `http://IP:8080` больше не отвечает — это ожидаемо: порт слушает только localhost.
Если нужен доступ к приложению в обход Caddy (диагностика), используйте SSH-туннель:
`ssh -L 8080:127.0.0.1:8080 user@server`.

## Как это работает

- `docker/Caddyfile` — один сайт `{$APP_DOMAIN}`, `reverse_proxy app:80`.
- Caddy хранит сертификаты в томе `caddy_data`; продление автоматическое, обычно за
  30 дней до истечения.
- `bootstrap/app.php` доверяет `X-Forwarded-*` (`trustProxies`), поэтому Laravel видит
  `https`, строит правильные абсолютные ссылки и ставит secure-cookie.
- HSTS (`Strict-Transport-Security`) уже отдаётся. **Preload не включаем**: это
  необратимо для домена. Если боитесь — уменьшите `max-age` в `Caddyfile` на первое время.

## Обслуживание

```bash
docker compose logs -f caddy        # выпуск/продление, ошибки ACME
docker compose restart caddy        # после правки Caddyfile
docker compose exec caddy caddy validate --config /etc/caddy/Caddyfile --adapter caddyfile
```

Тома `caddy_data` и `caddy_config` **не удаляйте** при обновлениях: там сертификаты и
аккаунт ACME. `docker compose down -v` удалит и их, и сертификат будет выпускаться заново
(с риском упереться в лимиты Let's Encrypt).

## Если что-то не так

- **Сертификат не выпускается.** Проверьте DNS, что 80/443 доступны снаружи
  (`curl -I http://ваш-домен.ру` должен доходить до Caddy), что домен не за прокси
  Cloudflare в режиме «Proxied» на первом запуске. Логи — `docker compose logs caddy`.
- **Лимиты Let's Encrypt.** Не больше 5 неудачных проверок на домен в час; при отладке
  используйте staging-ACME (добавить в глобальный блок Caddyfile:
  `acme_ca https://acme-staging-v02.api.letsencrypt.org/directory`), потом убрать.
- **`mixed content` / ссылки на http.** Проверьте `APP_URL` и что `trustProxies` на месте;
  после правки `.env` — `docker compose restart app` (entrypoint перекэширует конфиг).
- **Логин не работает.** Если `SESSION_SECURE_COOKIE=true`, сайт должен открываться
  по HTTPS; по HTTP cookie не отправится — это правильное поведение.
- **Другой прокси/Cloudflare.** Не терминируйте TLS дважды: либо Caddy, либо внешний
  прокси. Для 152-ФЗ предпочтительнее прямая схема «домен → российский сервер → Caddy»:
  зарубежный прокси видит IP посетителей.

## Локально

`docker compose up -d --build` без профиля `https` поднимает только app+db:
`http://localhost:8080`. Caddy и сертификаты для локальной разработки не нужны.
<<<<<<< HEAD
=======

## Домашний сервер

Если сайт хостится на домашнем ПК с белым IP: проброс 80/443, CGNAT, Windows,
бэкапы и требования 152-ФЗ — [home-hosting.md](home-hosting.md).
>>>>>>> my-saved-changes
