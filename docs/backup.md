# Бэкапы: база и загруженные файлы

Скрипты бэкапа:

- `scripts/backup.ps1` — Windows-сервер с Docker Desktop (этот проект, см.
  [home-hosting.md](home-hosting.md));
- `scripts/backup.sh` — Linux-сервер, WSL или cron.

Оба снимают одно и то же:

- дамп PostgreSQL (`pg_dump` из контейнера `db`);
- архив тома `storage` (аватары, обложки, картинки статей) из контейнера `app`.

Сертификаты Caddy не архивируются: они выпускаются заново, а `caddy_data` можно
сохранить отдельно, если не хочется повторной выдачи.

## Запуск

Стек должен быть поднят (`docker compose up -d`), скрипт запускается из любой директории.

Windows:

```powershell
.\scripts\backup.ps1
.\scripts\backup.ps1 -BackupDir D:\vkr-backups -RetentionDays 30
```

Linux:

```bash
sh scripts/backup.sh
```

Параметры и переменные:

| PowerShell | sh | По умолчанию | Назначение |
|---|---|---|---|
| `-BackupDir` | `BACKUP_DIR` | `backups` | куда складывать архивы (относительно корня проекта) |
| `-RetentionDays` | `RETENTION_DAYS` | `14` | сколько дней хранить архивы; старые удаляются |

Пример с отдельным диском:

```bash
BACKUP_DIR=/var/backups/vkr-blog RETENTION_DAYS=14 sh scripts/backup.sh
```

Почему PowerShell-версия не ходит через WSL: `wsl.exe` искажает в аргументах обратные
слэши и кириллицу, а если в дистрибе стоит свой `dockerd`, `docker compose` там видит
чужие контейнеры («service "db" is not running»). Поэтому контейнер пакует данные в
свой `/tmp`, а `docker compose cp` забирает готовый файл — бинарные данные вообще не
проходят через консоль.

Оба скрипта проверяют результат: архив меньше 64 байт или без gzip-заголовка считается
ошибкой. Раньше `pg_dump | gzip` при падении `pg_dump` оставлял пустой, но «валидный»
архив — то есть бэкапа не было, а релиз считался успешным.

## По расписанию

Windows — Планировщик заданий и `scripts/backup.ps1` (пример в [home-hosting.md](home-hosting.md) §7).
Linux — cron на сервере (не в контейнере):

```cron
15 3 * * * cd /srv/vkr-blog && BACKUP_DIR=/var/backups/vkr-blog sh scripts/backup.sh >> /var/log/vkr-backup.log 2>&1
```

Проверьте, что диск не переполняется: `RETENTION_DAYS` и размер архивов.

## Куда складывать

- только в РФ (152-ФЗ): другой российский сервер, объектное хранилище российского
  провайдера или шифрованный архив на отдельном диске;
- не в зарубежные облака (Google Drive, Dropbox, S3 вне РФ);
- копию стоит держать вне того же сервера: при потере VPS локальные бэкапы исчезнут.

## Восстановление

База:

```bash
gunzip -c backups/db-20261105-031500.sql.gz \
  | docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"'
```

Файлы:

```bash
docker compose exec -T app sh -c 'tar xzf - -C /var/www/storage' \
  < backups/storage-20261105-031500.tar.gz
```

На Windows у PowerShell нет перенаправления `<`, поэтому файл сначала копируется в контейнер
(`docker compose cp` безопасен для бинарных данных), а распаковка идёт уже внутри:

```powershell
docker compose cp backups/db-20261105-031500.sql.gz db:/tmp/db.sql.gz
docker compose exec -T db sh -c 'gunzip -c /tmp/db.sql.gz | psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"'

docker compose cp backups/storage-20261105-031500.tar.gz app:/tmp/storage.tar.gz
docker compose exec -T app sh -c 'tar xzf /tmp/storage.tar.gz -C /var/www/storage'
```

После восстановления проверьте вход, картинки в статье и `php artisan migrate --force`
(если дамп старее кода).

## Проверка

Раз в месяц прогоняйте restore на тестовом стенде — бэкап, который ни разу не
восстанавливали, считается несуществующим. Минимум: `gunzip -t` для архива,
`pg_restore --list`/`psql` на пустой базе.

Политика обработки ПД обещает хранение резервных копий 30 дней; скрипт по умолчанию
держит 14 — это в пределах обещанного, но при изменении политики поправьте
`RETENTION_DAYS`.
