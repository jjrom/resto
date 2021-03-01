#!/bin/bash
#
# Copyright 2018 Jérôme Gasperi
#
# Licensed under the Apache License, version 2.0 (the "License");
# You may not use this file except in compliance with the License.
# You may obtain a copy of the License at:
#
#   http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
# WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
# License for the specific language governing permissions and limitations
# under the License.

# Force script to exit on error
RED='\033[0;31m'
NC='\033[0m'

set -e
err_report() {
    echo -e "${RED}[ERROR] Error on line $1 - see errors.log file ${NC}"
}
trap 'err_report $LINENO' ERR

# Compute absolute path
ABS_ROOT_PATH=$(cd -P -- "$(dirname -- "$0")" && printf '%s\n' "$(pwd -P)")
ENV_FILE=${ABS_ROOT_PATH}/../config.env

#
# Help function
#
function showUsage {
    echo -e ""
    echo -e "Install database tables and functions for resto application"
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

DATABASE_HOST=$(grep ^DATABASE_HOST= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_PORT=$(grep ^DATABASE_PORT= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_NAME=$(grep ^DATABASE_NAME= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_USER_NAME=$(grep ^DATABASE_USER_NAME= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_USER_PASSWORD=$(grep ^DATABASE_USER_PASSWORD= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)

PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f ${ABS_ROOT_PATH}/../build/resto-database/sql/01_resto_functions.sql > /dev/null 2>> errors.log
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f ${ABS_ROOT_PATH}/../build/resto-database/sql/01_tamn.sql > /dev/null 2>> errors.log
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f ${ABS_ROOT_PATH}/../build/resto-database/sql/02_resto_model.sql > /dev/null 2> errors.log
# [IMPORTANT] Deactivate geometry_part split - should be completely removed in next version ?
#PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f build/resto-database/sql/03_resto_triggers.sql > /dev/null 2>> errors.log
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f ${ABS_ROOT_PATH}/../build/resto-database/sql/04_resto_inserts.sql > /dev/null 2>> errors.log
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f ${ABS_ROOT_PATH}/../build/resto-database/sql/05_resto_indexes.sql > /dev/null 2>> errors.log

# Addons sql files if any
if [ -d "${ABS_ROOT_PATH}/../addons" ]; then
  for sql in $(find ${ABS_ROOT_PATH}/../addons -name "*.sql" | sort); do
      echo "[PROCESS] " . $sql
      PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST" -p "$DATABASE_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f $sql > /dev/null 2>> errors.log
  done
fi
