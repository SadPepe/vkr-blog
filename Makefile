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
