# Быстрые команды для docker-стенда: см. docs/docker.md

up:
	docker compose up -d --build

seed:
	DB_SEED=true docker compose up -d --build

down:
	docker compose down

reset:
	docker compose down -v

logs:
	docker compose logs -f app

shell:
	docker compose exec app sh

db:
	docker compose exec db psql -U postgres -d blog

restart:
	docker compose restart app

test:
	docker compose --profile test run --rm tests

# Локальный dev-сервер. Хостовый PHP собран без intl (нужен Filament)
# и без pdo_sqlite (нужен тестам) — подключаем расширения через php-ini/.
serve:
	PHP_INI_SCAN_DIR="$(CURDIR)/php-ini" php artisan serve --port=8001

# То же, но слушает все интерфейсы: сайт открывается с телефона по LAN-адресу
# (Vite тоже ходит по локалке — см. vite.config.js).
serve-lan:
	PHP_INI_SCAN_DIR="$(CURDIR)/php-ini" php artisan serve --host=0.0.0.0 --port=8001
