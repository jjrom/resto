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

RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

# Force script to exit on error
set -e
err_report() {
    echo -e "${RED}[ERROR] Error - user not created${NC}"
}
trap 'err_report' ERR

ENV_FILE=__NULL__

function showUsage {
    echo ""
    echo "   Create admin user for resto instance"
    echo ""
    echo "   Usage $0 -e config.env"
    echo ""
    echo "      -e | --envfile Environnement file (see config.env example)"
    echo "      -h | --help show this help"
    echo ""
    echo "      !!! This script requires docker and docker-compose !!!"
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
DATABASE_EXPOSED_PORT=$(grep ^DATABASE_EXPOSED_PORT= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')
if [ "${DATABASE_EXPOSED_PORT}" == "" ]; then
    DATABASE_EXPOSED_PORT=5253
fi

DATABASE_USER_PASSWORD=$(grep ^DATABASE_USER_PASSWORD= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')
if [ "${DATABASE_USER_PASSWORD}" == "" ]; then
    DATABASE_USER_PASSWORD=resto
fi

DATABASE_USER_NAME=$(grep ^DATABASE_USER_NAME= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')
if [ "${DATABASE_USER_NAME}" == "" ]; then
    DATABASE_USER_NAME=resto
fi

DATABASE_NAME=$(grep ^DATABASE_NAME= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')
if [ "${DATABASE_NAME}" == "" ]; then
    DATABASE_NAME=resto
fi

DATABASE_HOST=$(grep ^DATABASE_HOST= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')
if [ "${DATABASE_HOST}" == "" ]; then
    DATABASE_HOST=restodb
fi

ADMIN_USER_NAME=$(grep ^ADMIN_USER_NAME= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')
if [ "${ADMIN_USER_NAME}" == "" ]; then
    ADMIN_USER_NAME=admin
fi

ADMIN_USER_ID=$(grep ^ADMIN_USER_ID= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')

ADMIN_USER_PASSWORD=$(grep ^ADMIN_USER_PASSWORD= ${ENV_FILE} | awk -F= '{print $2}' | sed 's/^"//g' | sed 's/"$//g')
if [ "${ADMIN_USER_PASSWORD}" == "" ]; then
    showUsage
    echo -e "${RED}[ERROR]${NC} ADMIN_USER_PASSWORD cannot be empty (see ${ENV_FILE})"
    echo ""
    exit 0
fi

# Change password !!!
HASH=`docker run --rm php:7.2-alpine -r "echo password_hash('${ADMIN_USER_PASSWORD}', PASSWORD_BCRYPT);"`

if [ "${DATABASE_HOST}" == "restodb" ] || [ "${DATABASE_HOST}" == "host.docker.internal" ]; then
    DATABASE_HOST_SEEN_FROM_DOCKERHOST=localhost
else
    DATABASE_HOST_SEEN_FROM_DOCKERHOST=${DATABASE_HOST}
fi

if [ "${ADMIN_USER_ID}" != "" ]; then
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -d ${DATABASE_NAME} -U ${DATABASE_USER_NAME} -h ${DATABASE_HOST_SEEN_FROM_DOCKERHOST} -p ${DATABASE_EXPOSED_PORT} > /dev/null 2> errors.log << EOF
INSERT INTO resto.user (id,email,groups,firstname,password,activated,registrationdate) VALUES (${ADMIN_USER_ID}, '${ADMIN_USER_NAME}','{0}','${ADMIN_USER_NAME}','${HASH}', 1, now_utc()) ON CONFLICT (id) DO UPDATE SET password='${HASH}';
EOF
else
PGPASSWORD=${DATABASE_USER_PASSWORD} psql -d ${DATABASE_NAME} -U ${DATABASE_USER_NAME} -h ${DATABASE_HOST_SEEN_FROM_DOCKERHOST} -p ${DATABASE_EXPOSED_PORT} > /dev/null 2> errors.log << EOF
INSERT INTO resto.user (email,groups,firstname,password,activated,registrationdate) VALUES ('${ADMIN_USER_NAME}','{0}','${ADMIN_USER_NAME}','${HASH}', 1, now_utc()) ON CONFLICT (email) DO UPDATE SET password='${HASH}';
EOF
fi
echo -e "[INFO] User ${GREEN}${ADMIN_USER_NAME}${NC} created/updated"