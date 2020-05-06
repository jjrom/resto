#!/bin/bash

ENV_FILE=../config.env
# Force script to exit on error
RED='\033[0;31m'
NC='\033[0m'

set -e
err_report() {
    echo -e "${RED}[ERROR] Error on line $1 - see errors.log file ${NC}"
}
trap 'err_report $LINENO' ERR

#
# Help function
#
function showUsage {
    echo -e ""
    echo -e "Install database tables and functions for RESTo application"
    echo -e ""
    echo -e "   Usage $0 [options]"
    echo -e ""
    echo -e "   Options:"
    echo -e ""
    echo -e "      -e | --envfile Environnement file (default is ${GREEN}config.env${NC})"
    echo -e "      -h | --help show this help"
    echo -e ""
}
TARGET=""
while (( "$#" ));
do
	case "$1" in
        -e|--envfile)
            if [[ "$2" == "" || ${2:0:1} == "-" ]]; then
                showUsage
                echo -e "${RED}[ERROR] Missing config file name${NC}"
                echo -e ""
                exit 1
            fi
            ENV_FILE="$2"
            shift 2 # past argument
            ;;
        -h|--help)
            showUsage
            shift # past argument
            exit 0
            ;;
        --) # end argument parsing
            shift
            break
            ;;
        -*|--*=) # unsupported flags
            showUsage
            echo -e "${RED}[ERROR] Unsupported flag $1${NC}"
            echo -e ""
            exit 1
            ;;
        *) # preserve positional arguments
            TARGET="$1"
            shift
            ;;
	esac
done

# The environement file is mandatory
# It contains all configuration to build and run resto images
#
if [[ ! -f "${ENV_FILE}" ]]; then
    showUsage
    echo -e "${RED}[ERROR] The \"${ENV_FILE}\" file does not exist!${NC}"
    echo ""
    exit 1
fi

DATABASE_HOST=$(grep ^DATABASE_HOST= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')
DATABASE_PORT=$(grep ^DATABASE_PORT= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')
DATABASE_NAME=$(grep ^DATABASE_NAME= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')
DATABASE_USER_NAME=$(grep ^DATABASE_USER_NAME= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')
DATABASE_USER_PASSWORD=$(grep ^DATABASE_USER_PASSWORD= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')

PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f build/resto-database/sql/01_resto_functions.sql > /dev/null 2>> errors.log
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f build/resto-database/sql/01_tamn.sql > /dev/null 2>> errors.log
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f build/resto-database/sql/02_resto_model.sql > /dev/null 2> errors.log
# [IMPORTANT] Deactivate geometry_part split - should be completely removed in next version ?
#PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f build/resto-database/sql/03_resto_triggers.sql > /dev/null 2>> errors.log
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f build/resto-database/sql/04_resto_inserts.sql > /dev/null 2>> errors.log
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f build/resto-database/sql/05_resto_indexes.sql > /dev/null 2>> errors.log

# Addons sql files if any
if [ -d "../build/resto-database/sql/addons" ]; then
  for sql in $(find build/resto-database/sql/addons -name "*.sql" | sort); do
      echo "[PROCESS] " . $sql
      PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$POSTGRES_HOST" -p "$POSTGRES_PORT" -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f $sql > /dev/null 2>> errors.log
  done
fi
