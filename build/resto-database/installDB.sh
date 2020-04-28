#!/bin/bash

# Force script to exit on error
RED='\033[0;31m'
set -e
err_report() {
    echo -e "${RED}[ERROR] Error on line $1 ${NC}"
}
trap 'err_report $LINENO' ERR

# nico ribot add: if sql files not found at root /sql (not deployed in the container), use script path to find sql files
REL_PATH=""
if [[ ! -f /sql/01_resto_functions.sql ]]; then
  REL_PATH=$(dirname $0)
fi

psql -X -v ON_ERROR_STOP=1 -h "$POSTGRES_HOST" -p "$POSTGRES_PORT" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f ${REL_PATH}/sql/00_resto_extensions.sql > /dev/null 2>&1
psql -X -v ON_ERROR_STOP=1 -h "$POSTGRES_HOST" -p "$POSTGRES_PORT" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f ${REL_PATH}/sql/01_resto_functions.sql > /dev/null 2>&1
psql -X -v ON_ERROR_STOP=1 -h "$POSTGRES_HOST" -p "$POSTGRES_PORT" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f ${REL_PATH}/sql/02_resto_model.sql > /dev/null 2>&1
psql -X -v ON_ERROR_STOP=1 -h "$POSTGRES_HOST" -p "$POSTGRES_PORT" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f ${REL_PATH}/sql/03_resto_triggers.sql > /dev/null 2>&1
psql -X -v ON_ERROR_STOP=1 -h "$POSTGRES_HOST" -p "$POSTGRES_PORT" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f ${REL_PATH}/sql/04_resto_inserts.sql > /dev/null 2>&1
psql -X -v ON_ERROR_STOP=1 -h "$POSTGRES_HOST" -p "$POSTGRES_PORT" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f ${REL_PATH}/sql/05_resto_indexes.sql > /dev/null 2>&1

# Addons sql files
for sql in $(find ${REL_PATH}/sql/addons -name "*.sql" | sort); do
    echo "[PROCESS] " . $sql
    psql -X -v ON_ERROR_STOP=1 -h "$POSTGRES_HOST" -p "$POSTGRES_PORT" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f $sql > /dev/null 2>&1
done
