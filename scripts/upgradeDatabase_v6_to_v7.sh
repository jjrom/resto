#!/bin/bash
#
# Copyright 2022 Jérôme Gasperi
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

RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

###### DO NOT TOUCH DEFAULT VALUES ########
DATABASE_NAME=resto
DATABASE_USER_NAME=resto
DATABASE_USER_PASSWORD=resto
DATABASE_EXPOSED_PORT=5253
USERNAME=
PASSWORD=
GROUP=100
ID=
###########################################

# Force script to exit on error
set -e
err_report() {
    echo -e "${RED}[ERROR] Error - user not created${NC}"
}
trap 'err_report' ERR

ENV_FILE=__NULL__
function showUsage {
    echo ""
    echo "   Upgrade existing v6.* resto database to resto v7 database"  
    echo ""
    echo "   Usage $0 -e config.env"
    echo ""
    echo "      -e | --envfile Environnement file (see config.env example)"
    echo "      -h | --help show this help"
    echo ""
    echo "      !!! This script requires docker !!!"
    echo ""
}

# Parsing arguments
while [[ $# > 0 ]]
do
	key="$1"
	case $key in
        -e|--envfile)
            ENV_FILE="$2"
            shift # past argument
            ;;
        -h|--help)
            showUsage
            exit 0
            shift # past argument
            ;;
            *)
        shift # past argument
        # unknown option
        ;;
	esac
done

if [ ! -f ${ENV_FILE} ]; then
    showUsage
    echo -e "${RED}[ERROR]${NC} Missing or invalid config file!"
    echo ""
    exit 0
fi

# Read environment from ENV_FILE
DATABASE_EXPOSED_PORT=$(grep ^DATABASE_EXPOSED_PORT= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_USER_PASSWORD=$(grep ^DATABASE_USER_PASSWORD= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_USER_NAME=$(grep ^DATABASE_USER_NAME= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_NAME=$(grep ^DATABASE_NAME= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_SCHEMA=$(grep ^DATABASE_SCHEMA= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)
DATABASE_HOST=$(grep ^DATABASE_HOST= ${ENV_FILE} | awk -F= '{for (i=2; i<=NF; i++) print $i}'| xargs echo -n)

if [ "${DATABASE_HOST}" == "restodb" ] || [ "${DATABASE_HOST}" == "host.docker.internal" ]; then
    DATABASE_HOST_SEEN_FROM_DOCKERHOST=localhost
else
    DATABASE_HOST_SEEN_FROM_DOCKERHOST=${DATABASE_HOST}
fi

PGPASSWORD=${DATABASE_USER_PASSWORD} psql -d ${DATABASE_NAME} -U ${DATABASE_USER_NAME} -h ${DATABASE_HOST_SEEN_FROM_DOCKERHOST} -p ${DATABASE_EXPOSED_PORT} << EOF
ALTER TABLE resto.right ADD COLUMN target TEXT;
EOF
fi
echo -e "[INFO] Upgrade done"
