#!/bin/bash

# Force script to exit on error
RED='\033[0;31m'
set -e
err_report() {
    echo -e "${RED}[ERROR] Error on line $1 ${NC}"
}
trap 'err_report $LINENO' ERR

psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /sql/00_resto_extensions.sql > /dev/null 2>&1
psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /sql/01_resto_functions.sql > /dev/null 2>&1
psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /sql/01_tamn.sql > /dev/null 2>&1
psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /sql/02_resto_model.sql > /dev/null 2>&1
# [IMPORTANT] Deactivate geometry_part split - should be completely removed in next version ?
#psql -X -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /sql/03_resto_triggers.sql > /dev/null 2> errors.log
psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /sql/04_resto_inserts.sql > /dev/null 2>&1
psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /sql/05_resto_indexes.sql > /dev/null 2>&1

# Addons sql files
for sql in $(find /sql/addons -name "*.sql" | sort); do
    echo "[PROCESS] " . $sql
    psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f $sql > /dev/null 2>&1
done
