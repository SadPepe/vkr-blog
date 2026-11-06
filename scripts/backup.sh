#!/usr/bin/env sh
# Бэкап БД и загруженных файлов vkr-blog.
# Запуск из любой директории: sh scripts/backup.sh
# Переменные: BACKUP_DIR (по умолчанию backups), RETENTION_DAYS (14).

set -eu

cd "$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

BACKUP_DIR="${BACKUP_DIR:-backups}"
RETENTION_DAYS="${RETENTION_DAYS:-14}"
STAMP="$(date +%Y%m%d-%H%M%S)"

mkdir -p "$BACKUP_DIR"
umask 077

echo "БД → $BACKUP_DIR/db-$STAMP.sql.gz"
docker compose exec -T db sh -c 'pg_dump -U "$POSTGRES_USER" -d "$POSTGRES_DB"' \
    | gzip > "$BACKUP_DIR/db-$STAMP.sql.gz"

echo "Файлы → $BACKUP_DIR/storage-$STAMP.tar.gz"
docker compose exec -T app tar czf - -C /var/www/storage . \
    > "$BACKUP_DIR/storage-$STAMP.tar.gz"

find "$BACKUP_DIR" -type f -mtime +"$RETENTION_DAYS" -delete

echo "Готово. Файлов в $BACKUP_DIR: $(find "$BACKUP_DIR" -type f | wc -l | tr -d ' ')"
