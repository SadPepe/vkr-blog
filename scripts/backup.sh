#!/usr/bin/env sh
# Бэкап БД и загруженных файлов vkr-blog (Linux-сервер, WSL или cron).
# На Windows-сервере с Docker Desktop используйте scripts/backup.ps1.
# Запуск из любой директории: sh scripts/backup.sh
# Переменные: BACKUP_DIR (по умолчанию backups), RETENTION_DAYS (14).

set -eu

cd "$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

BACKUP_DIR="${BACKUP_DIR:-backups}"
RETENTION_DAYS="${RETENTION_DAYS:-14}"
STAMP="$(date +%Y%m%d-%H%M%S)"

mkdir -p "$BACKUP_DIR"
umask 077

# Дамп пишется сначала в файл и только после успешного pg_dump сжимается: в sh нет
# pipefail, поэтому `pg_dump | gzip > файл` при ошибке pg_dump оставляет
# «валидный» пустой архив, и бэкапа фактически нет.
TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT

echo "БД → $BACKUP_DIR/db-$STAMP.sql.gz"
docker compose exec -T db sh -c 'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB"' > "$TMP_DIR/db.sql"
if [ ! -s "$TMP_DIR/db.sql" ]; then
    echo "ОШИБКА: pg_dump вернул пустой дамп, бэкап не сделан." >&2
    exit 1
fi
gzip -c "$TMP_DIR/db.sql" > "$BACKUP_DIR/db-$STAMP.sql.gz"

echo "Файлы → $BACKUP_DIR/storage-$STAMP.tar.gz"
docker compose exec -T app tar czf - -C /var/www/storage . > "$TMP_DIR/storage.tar.gz"
if ! gzip -t "$TMP_DIR/storage.tar.gz"; then
    echo "ОШИБКА: архив storage повреждён или пуст, бэкап не сделан." >&2
    exit 1
fi
mv "$TMP_DIR/storage.tar.gz" "$BACKUP_DIR/storage-$STAMP.tar.gz"

find "$BACKUP_DIR" -type f -mtime +"$RETENTION_DAYS" -delete

echo "Готово. Файлов в $BACKUP_DIR: $(find "$BACKUP_DIR" -type f | wc -l | tr -d ' ')"
