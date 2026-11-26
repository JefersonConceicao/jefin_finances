#!/bin/sh
# Dump diário do banco de produção, guardando os últimos 14 dias.
# Cron (root): 0 3 * * * /opt/jefin_finances/docker/prod/backup.sh
set -e
cd "$(dirname "$0")/../.."

DIR=/opt/backups/jefin_finances
mkdir -p "$DIR"
ARQ="$DIR/jefin_finances-$(date +%Y%m%d%H%M).sql.gz"

docker exec jefin_finances_db sh -c \
  'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --triggers "$MYSQL_DATABASE"' \
  | gzip > "$ARQ"

find "$DIR" -name 'jefin_finances-*.sql.gz' -mtime +14 -delete
