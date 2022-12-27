#!/command/with-contenv bash

# Force script to exit on error
RED='\033[0;31m'
set -e
err_report() {
    echo -e "${RED}[ERROR] Error on line $1 ${NC}"
}
trap 'err_report $LINENO' ERR

PGPASSWORD=${POSTGRES_PASSWORD} psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /sql/00_resto_extensions.sql > /dev/null 2>&1
PGPASSWORD=${POSTGRES_PASSWORD} psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /sql/01_resto_functions.sql > /dev/null 2>&1
PGPASSWORD=${POSTGRES_PASSWORD} psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /sql/01_tamn.sql > /dev/null 2>&1

if [[ "${DATABASE_COMMON_SCHEMA}" == "" ]]; then
    DATABASE_COMMON_SCHEMA=resto
fi

if [[ "${DATABASE_TARGET_SCHEMA}" == "" ]]; then
    DATABASE_TARGET_SCHEMA=resto
fi

cat /sql/02_resto_common_model.sql | sed "s/__DATABASE_COMMON_SCHEMA__\./${DATABASE_COMMON_SCHEMA}\./g" | PGPASSWORD=${POSTGRES_PASSWORD} psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB"
cat /sql/03_resto_target_model.sql | sed "s/__DATABASE_COMMON_SCHEMA__\./${DATABASE_TARGET_SCHEMA}\./g" | PGPASSWORD=${POSTGRES_PASSWORD} psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB"
# [TODO] To be discarded ?
#cat /sql/04_resto_triggers.sql | sed "s/__DATABASE_COMMON_SCHEMA__\./${DATABASE_TARGET_SCHEMA}\./g" | PGPASSWORD=${POSTGRES_PASSWORD} psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB"

# Addons sql files
for sql in $(find /sql/addons -name "*.sql" | sort); do
    echo "[PROCESS] " . $sql
    cat $sql | sed "s/__DATABASE_COMMON_SCHEMA__\./${DATABASE_COMMON_SCHEMA}\./g" | sed "s/__DATABASE_TARGET_SCHEMA__\./${DATABASE_TARGET_SCHEMA}\./g" | PGPASSWORD=${POSTGRES_PASSWORD} psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB"> /dev/null 2>&1
done
