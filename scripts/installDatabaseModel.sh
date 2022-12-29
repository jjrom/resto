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
    echo -e "${RED}[ERROR] Error on line $1${NC}"
}
trap 'err_report $LINENO' ERR

# Compute absolute path
ABS_ROOT_PATH=$(cd -P -- "$(dirname -- "$0")" && printf '%s\n' "$(pwd -P)")
ENV_FILE=${ABS_ROOT_PATH}/../config.env

#
# Convert relative path to absolute
#
function absolutePath {
    local target="$1"

    if [ "$target" == "." ]; then
        echo "$(pwd)"
    elif [ "$target" == ".." ]; then
        echo "$(dirname "$(pwd)")"
    else
        echo "$(cd "$(dirname "$1")"; pwd)/$(basename "$1")"
    fi
}

#
# Help function
#
function showUsage {
    echo -e ""
    echo -e "Install resto database model on a running resto-database instance"
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

# Check mandatory tools
if ! command -v curl &> /dev/null
then
    echo -e "${RED}[ERROR]${NC} The required \"curl\" command was not found. Please install curl package before running this script."
    echo ""
    exit 1
fi
if ! command -v psql &> /dev/null
then
    echo -e "${RED}[ERROR]${NC} The required \"psql\" command was not found. Please install postgresql-client package before running this script."
    echo ""
    exit 1
fi

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
DATABASE_EXPOSED_PORT=$(grep ^DATABASE_EXPOSED_PORT= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_NAME=$(grep ^DATABASE_NAME= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_COMMON_SCHEMA=$(grep ^DATABASE_COMMON_SCHEMA= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_TARGET_SCHEMA=$(grep ^DATABASE_TARGET_SCHEMA= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_USER_NAME=$(grep ^DATABASE_USER_NAME= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_USER_PASSWORD=$(grep ^DATABASE_USER_PASSWORD= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
ADDONS_DIR=$(grep ^ADDONS_DIR= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)

# Compute ADDONS_DIR absolute directory
if [[ "${ADDONS_DIR}" != "" && $(dirname ${ADDONS_DIR}) == "." ]]; then
    ADDONS_DIR=$(dirname $(absolutePath ${ENV_FILE}))/${ADDONS_DIR}
fi

# Hack to translate container host to localhost
if [ "${DATABASE_HOST}" == "restodb" ] || [ "${DATABASE_HOST}" == "host.docker.internal" ]; then
    DATABASE_HOST_SEEN_FROM_DOCKERHOST=localhost
else
    DATABASE_HOST_SEEN_FROM_DOCKERHOST=${DATABASE_HOST}
fi

echo -e "[INFO] Retrieving resto model from https://github.com/jjrom/resto-database to ${ABS_ROOT_PATH}/../resto-model"
mkdir -p ${ABS_ROOT_PATH}/../resto-model
curl -o ${ABS_ROOT_PATH}/../resto-model/00_resto_extensions.sql https://raw.githubusercontent.com/jjrom/resto-database/main/build/resto-database/sql/00_resto_extensions.sql  > /dev/null 2>&1
curl -o ${ABS_ROOT_PATH}/../resto-model/01_resto_functions.sql https://raw.githubusercontent.com/jjrom/resto-database/main/build/resto-database/sql/01_resto_functions.sql > /dev/null 2>&1
curl -o ${ABS_ROOT_PATH}/../resto-model/01_tamn.sql https://raw.githubusercontent.com/jjrom/resto-database/main/build/resto-database/sql/01_tamn.sql > /dev/null 2>&1
curl -o ${ABS_ROOT_PATH}/../resto-model/02_resto_common_model.sql https://raw.githubusercontent.com/jjrom/resto-database/main/build/resto-database/sql/02_resto_common_model.sql > /dev/null 2>&1
curl -o ${ABS_ROOT_PATH}/../resto-model/03_resto_target_model.sql https://raw.githubusercontent.com/jjrom/resto-database/main/build/resto-database/sql/03_resto_target_model.sql > /dev/null 2>&1

echo -e "[INFO][DATABASE] Create resto functions"
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST_SEEN_FROM_DOCKERHOST" -p "$DATABASE_EXPOSED_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f ${ABS_ROOT_PATH}/../resto-model/01_resto_functions.sql
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST_SEEN_FROM_DOCKERHOST" -p "$DATABASE_EXPOSED_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME" -f ${ABS_ROOT_PATH}/../resto-model/01_tamn.sql

if [[ "${DATABASE_COMMON_SCHEMA}" == "" ]]; then
    DATABASE_COMMON_SCHEMA=resto
fi

if [[ "${DATABASE_TARGET_SCHEMA}" == "" ]]; then
    DATABASE_TARGET_SCHEMA=resto
fi

echo -e "[INFO][DATABASE] Create common model tables"
cat ${ABS_ROOT_PATH}/../resto-model/02_resto_common_model.sql | sed "s/__DATABASE_COMMON_SCHEMA__/${DATABASE_COMMON_SCHEMA}/g" | PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST_SEEN_FROM_DOCKERHOST" -p "$DATABASE_EXPOSED_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME"

echo -e "[INFO][DATABASE] Create target model tables"
cat ${ABS_ROOT_PATH}/../resto-model/03_resto_target_model.sql | sed "s/__DATABASE_TARGET_SCHEMA__/${DATABASE_TARGET_SCHEMA}/g" | PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST_SEEN_FROM_DOCKERHOST" -p "$DATABASE_EXPOSED_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME"

# Addons sql files if any
if [ -d "${ADDONS_DIR}" ]; then
    echo -e "[INFO][DATABASE] Create addons tables"
    # [HACK] Sort on filename without dirname to be sure that SQL files are run in the right order (i.e. O1_*.sql, then 02_*.sql, etc.)
    for sql in $(find ${ADDONS_DIR} -name "*.sql" | sed 's:\(.*/\)\(.*\):\2 \1\2:' | sort | sed 's:.* ::'); do
        echo "[INFO][DATABASE] Process addons " . $sql
        cat $sql | sed "s/__DATABASE_COMMON_SCHEMA__/${DATABASE_COMMON_SCHEMA}/g" | sed "s/__DATABASE_TARGET_SCHEMA__/${DATABASE_TARGET_SCHEMA}/g" | PGPASSWORD=${DATABASE_USER_PASSWORD} psql -X -v ON_ERROR_STOP=1 -h "$DATABASE_HOST_SEEN_FROM_DOCKERHOST" -p "$DATABASE_EXPOSED_PORT" -U "$DATABASE_USER_NAME" -d "$DATABASE_NAME"
    done
fi
