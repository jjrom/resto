#!/bin/bash
#
# Copyright 2014 Jérôme Gasperi
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

USER=admin
SUPERUSER=postgres
SCHEMA=resto
DB=resto
BCRYPT=NO
usage="## resto - Create administrator user account\n\n  Usage $0 -u <admin user name (default 'admin')> -p <admin user password> [-B <use bcrypt hashing (needs PHP >= 5.5.0)> -d <databasename (default resto)> -S <schemaname (default resto)> -s <superuser (default postgres)>]\n"
while getopts "d:u:p:s:S:Bh" options; do
    case $options in
        d ) DB=`echo $OPTARG`;;
        u ) USER=`echo $OPTARG`;;
        p ) PASSWORD=`echo $OPTARG`;;
        s ) SUPERUSER=`echo $OPTARG`;;
        S ) SCHEMA=`echo $OPTARG`;;
        B ) BCRYPT=YES;;
        h ) echo -e $usage;;
        \? ) echo -e $usage
            exit 1;;
        * ) echo -e $usage
            exit 1;;
    esac
done
if [ "$PASSWORD" = "" ]
then
    echo -e $usage
    exit 1
fi
# Change password !!!
if [ "$BCRYPT" = "NO"]
then
    HASH=`php -r "echo sha1('$PASSWORD');"`
else
    HASH=`php -r "echo password_hash('$PASSWORD', PASSWORD_BCRYPT);"`
fi
ACTIVATIONCODE=`php -r "echo sha1(mt_rand() . microtime());"`
psql -d $DB -U $SUPERUSER << EOF
INSERT INTO ${SCHEMA}.users (email,groups,username,password,activationcode,activated,registrationdate) VALUES ('$USER','{"admin"}','$USER','$HASH','$ACTIVATIONCODE', 1, now());
EOF
