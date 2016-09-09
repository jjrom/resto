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

# Paths are mandatory from command line
SUPERUSER=postgres
DROPFIRST=NO
DB=resto
USER=resto
SCHEMA=resto
DATADIR=`dirname $0`/data
usage="## resto database installation\n\n  Usage $0 -p <resto (Read+Write database) user password> [-d <databasename> -S <schemaname> -f <PostGIS directory> -s <database SUPERUSER> -F]\n\n  -d : database name (default resto)\n  -S : schema name (default resto)\n  -f : absolute path to the directory containing postgis.sql - If not set EXTENSION mechanism will be used\n  -s : dabase SUPERUSER (default "postgres")\n  -F : WARNING - suppress existing resto schema within resto database\n"
while getopts "f:d:s:u:p:hF" options; do
    case $options in
        f ) ROOTDIR=`echo $OPTARG`;;
        d ) DB=`echo $OPTARG`;;
        s ) SUPERUSER=`echo $OPTARG`;;
        S ) SCHEMA=`echo $OPTARG`;;
        u ) USER=`echo $OPTARG`;;
        p ) USERPASSWORD=`echo $OPTARG`;;
        F ) DROPFIRST=YES;;
        h ) echo -e $usage;;
        \? ) echo -e $usage
            exit 1;;
        * ) echo -e $usage
            exit 1;;
    esac
done
if [ "$DATADIR" = "" ]
then
    echo -e $usage
    exit 1
fi
if [ "$USERPASSWORD" = "" ]
then
    echo -e $usage
    exit 1
fi

# Create DB
createdb $DB -U $SUPERUSER -E UTF8
createlang -U $SUPERUSER plpgsql $DB

# Make db POSTGIS compliant
if [ "$ROOTDIR" = "" ]
then
    psql -d $DB -U $SUPERUSER -c "CREATE EXTENSION postgis; CREATE EXTENSION postgis_topology;"
else
    # Example : $ROOTDIR = /usr/local/pgsql/share/contrib/postgis-1.5/
    postgis=`echo $ROOTDIR/postgis.sql`
    projections=`echo $ROOTDIR/spatial_ref_sys.sql`
    psql -d $DB -U $SUPERUSER -f $postgis
    psql -d $DB -U $SUPERUSER -f $projections
fi


###### ADMIN ACCOUNT CREATION ######
psql -U $SUPERUSER -d $DB << EOF
CREATE USER $USER WITH PASSWORD '$USERPASSWORD' NOCREATEDB;
EOF

##### DROP SCHEMA FIRST ######
if [ "$DROPFIRST" = "YES" ]
then
psql -d $DB -U $SUPERUSER << EOF
DROP SCHEMA IF EXISTS $SCHEMA CASCADE;
EOF
fi
#############CREATE DB ##############
psql -d $DB -U $SUPERUSER << EOF

--
-- Use unaccent function from postgresql >= 9
-- Set it as IMMUTABLE to use it in index
--
CREATE EXTENSION unaccent;
ALTER FUNCTION unaccent(text) IMMUTABLE;

--
-- Create function normalize
-- This function will return input text
-- in lower case, without accents and with spaces replaced as '-'
--
CREATE OR REPLACE FUNCTION normalize(text)
RETURNS text AS \$\$
SELECT replace(replace(lower(unaccent(\$1)),' ','-'), '''', '-')
\$\$ LANGUAGE sql;

--
-- Create function count_estimate to speed up table count
-- (see https://wiki.postgresql.org/wiki/Count_estimate)
--
CREATE FUNCTION count_estimate(query text) RETURNS INTEGER AS
\$func\$
DECLARE
    rec   record;
    ROWS  INTEGER;
BEGIN
    FOR rec IN EXECUTE 'EXPLAIN ' || query LOOP
        ROWS := SUBSTRING(rec."QUERY PLAN" FROM ' rows=([[:digit:]]+)');
        EXIT WHEN ROWS IS NOT NULL;
    END LOOP;
    RETURN ROWS;
END
\$func\$ LANGUAGE plpgsql;

--
-- Copyright (C) 2016 Jerome Gasperi <jerome.gasperi@gmail.com>
-- With priceless contribution from Nicolas Ribot <nicky666@gmail.com>
--
-- This work is placed into the public domain.
--
-- SYNOPSYS:
--   ST_SplitDateLine(polygon)
--
-- DESCRIPTION:
--
--   This function split the input polygon geometry against the -180/180 date line
--   Returns the original geometry otherwise
--
--   WARNING ! Only work for SRID 4326
--
-- USAGE:
--
CREATE OR REPLACE FUNCTION ST_SplitDateLine(geom_in geometry)
RETURNS geometry AS \$\$
DECLARE
	geom_out geometry;
	blade geometry;
BEGIN

        blade := ST_SetSrid(ST_MakeLine(ST_MakePoint(180, -90), ST_MakePoint(180, 90)), 4326);

	-- Delta longitude is greater than 180 then return splitted geometry
	IF (ST_XMin(geom_in) < -90 AND ST_XMax(geom_in) > 90) OR ST_XMax(geom_in) > 180 OR ST_XMax(geom_in) < -180 THEN

            -- Add 360 to all negative longitudes
            WITH tmp0 AS (
                SELECT geom_in AS geom
            ), tmp AS (
                SELECT st_dumppoints(geom) AS dmp FROM tmp0
            ), tmp1 AS (
                SELECT (dmp).path,
                CASE WHEN st_X((dmp).geom) < 0 THEN st_setSRID(st_MakePoint(st_X((dmp).geom) + 360, st_Y((dmp).geom)), 4326)
                ELSE (dmp).geom END AS geom
                FROM tmp
                ORDER BY (dmp).path[2]
            ), tmp2 AS (
                SELECT st_dump(st_split(st_makePolygon(st_makeline(geom)), blade)) AS d
                FROM tmp1
            )
            SELECT ST_Union(
                (
                    CASE WHEN ST_Xmax((d).geom) > 180 THEN ST_Translate((d).geom, -360, 0, 0)
                    ELSE (d).geom END
                )
            )
            INTO geom_out
            FROM tmp2;

        -- Delta longitude < 180 degrees then return untouched input geometry
        ELSE
            RETURN geom_in;
	END IF;

	RETURN geom_out;
END
\$\$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- resto schema contains collections descriptions tables
--
CREATE SCHEMA ${SCHEMA};

--
-- collections table list all RESTo collections
--
CREATE TABLE ${SCHEMA}.collections (
    collection          TEXT PRIMARY KEY,
    creationdate        TIMESTAMP,
    model               TEXT DEFAULT 'RestoModel_default',
    licenseid           TEXT DEFAULT 'unlicensed', -- This should be linked to an existing license
    mapping             TEXT,
    status              TEXT DEFAULT 'public',
    owner               TEXT
);
CREATE INDEX idx_status_collections ON ${SCHEMA}.collections (status);
CREATE INDEX idx_creationdate_collections ON ${SCHEMA}.collections (creationdate);

--
-- osdescriptions table describe all RESTo collections
--
CREATE TABLE ${SCHEMA}.osdescriptions (
    collection          TEXT,
    lang                TEXT,
    shortname           TEXT,
    longname            TEXT,
    description         TEXT,
    tags                TEXT,
    Developer           TEXT,
    contact             TEXT,
    query               TEXT,
    attribution         TEXT
);
ALTER TABLE ONLY ${SCHEMA}.osdescriptions ADD CONSTRAINT fk_collection FOREIGN KEY (collection) REFERENCES ${SCHEMA}.collections(collection);
ALTER TABLE ONLY ${SCHEMA}.osdescriptions ADD CONSTRAINT cl_collection UNIQUE(collection, lang);
CREATE INDEX idx_collection_osdescriptions ON ${SCHEMA}.osdescriptions (collection);
CREATE INDEX idx_lang_osdescriptions ON ${SCHEMA}.osdescriptions (lang);

--
-- licenses table describe all resto licences
--
CREATE TABLE ${SCHEMA}.licenses (
    licenseid                       TEXT NOT NULL,
    grantedcountries                TEXT, -- Comma separated list of isoa2 list of allowed user countries
    grantedorganizationcountries    TEXT, -- Comma separated list of isoa2 list of allowed user's organization countries
    grantedflags                    TEXT, -- Comma separated list of flags allowed
    viewservice                     TEXT DEFAULT 'public', -- Enumeration : 'public', 'private'
    hastobesigned                   TEXT NOT NULL, -- Enumeration : 'never', 'once', 'always'
    signaturequota                  INTEGER DEFAULT -1, -- Maximum of signatures allowed if hastobesigned = 'always' (-1 means unlimited)
    description                     TEXT NOT NULL -- JSON object with one entry per language
);
-- INSERT licenses
INSERT INTO ${SCHEMA}.licenses (licenseid, viewservice, hastobesigned, description) VALUES ('unlicensed', 'public', 'never', '{"en":{"shortName":"No license"}}');
INSERT INTO ${SCHEMA}.licenses (licenseid, viewservice, hastobesigned, grantedflags, description) VALUES ('unlicensedwithregistration', 'public', 'never', 'REGISTERED', '{"en":{"shortName":"No license with mandatory registration"}}');

--
-- Keywords table
--
CREATE TABLE ${SCHEMA}.keywords (
    gid                 SERIAL PRIMARY KEY, -- unique id
    name                TEXT, -- keyword name in given language code
    type                TEXT, -- type of keyword (i.e. region, state, location, etc.)
    lang                TEXT, -- ISO A2 language code in lowercase
    value               TEXT, -- keyword as stored in features keywords columns
    location            TEXT DEFAULT NULL -- 'country code:bounding box'
);
CREATE INDEX idx_name_keywords ON ${SCHEMA}.keywords (normalize(name));
CREATE INDEX idx_type_keywords ON ${SCHEMA}.keywords (type);
CREATE INDEX idx_lang_keywords ON ${SCHEMA}.keywords (lang);

--
-- Facets table - store statistics for keywords appearance
--
CREATE TABLE ${SCHEMA}.facets (
    gid                 SERIAL PRIMARY KEY, -- unique id
    uid                 TEXT,
    value               TEXT, -- keyword value (without type)
    type                TEXT, -- type of keyword (i.e. region, state, location, etc.)
    pid                 TEXT, -- parent hash (i.e. 'europe' for keyword 'france')
    collection          TEXT, -- collection name
    counter             INTEGER -- number of appearance of this keyword within the collection
);
CREATE INDEX idx_type_facets ON ${SCHEMA}.facets (type);
CREATE INDEX idx_uid_facets ON ${SCHEMA}.facets (uid);
CREATE INDEX idx_pid_facets ON ${SCHEMA}.facets (pid);
CREATE INDEX idx_collection_facets ON ${SCHEMA}.facets (collection);

--
-- features TABLE MUST BE EMPTY (inheritance)
--

CREATE TABLE ${SCHEMA}.features (
    identifier          TEXT UNIQUE,
    parentidentifier    TEXT,
    collection          TEXT,
    visibility          TEXT DEFAULT 'public'::text,
    licenseid           TEXT,
    productidentifier   TEXT,
    title               TEXT,
    description         TEXT,
    authority           TEXT,
    startdate           TIMESTAMP,
    completiondate      TIMESTAMP,
    producttype         TEXT,
    processinglevel     TEXT,
    platform            TEXT,
    instrument          TEXT,
    resolution          NUMERIC(8,2),
    sensormode          TEXT,
    orbitnumber         INTEGER,
    quicklook           TEXT,
    thumbnail           TEXT,
    metadata            TEXT,
    metadata_mimetype   TEXT,
    resource            TEXT,
    resource_mimetype   TEXT,
    resource_size       NUMERIC,
    resource_checksum   TEXT, -- Checksum should be on the form checksumtype=checksum (e.g. SHA1=.....)
    wms                 TEXT,
    updated             TIMESTAMP,
    published           TIMESTAMP,
    keywords            TEXT,
    lu_cultivated       NUMERIC DEFAULT 0,
    lu_desert           NUMERIC DEFAULT 0,
    lu_flooded          NUMERIC DEFAULT 0,
    lu_forest           NUMERIC DEFAULT 0,
    lu_herbaceous       NUMERIC DEFAULT 0,
    lu_ice              NUMERIC DEFAULT 0,
    lu_urban            NUMERIC DEFAULT 0,
    lu_water            NUMERIC DEFAULT 0,
    hashes              TEXT[],
    snowcover           NUMERIC,
    cloudcover          NUMERIC
);
SELECT AddGeometryColumn('${SCHEMA}', 'features', 'geometry', '4326', 'GEOMETRY', 2);
SELECT AddGeometryColumn('${SCHEMA}', 'features', '_geometry', '4326', 'GEOMETRY', 2);
SELECT AddGeometryColumn('${SCHEMA}', 'features', 'centroid', '4326', 'POINT', 2);

--
-- users table list user informations
--
CREATE TABLE ${SCHEMA}.users (
    userid              SERIAL PRIMARY KEY,
    email               TEXT UNIQUE,  -- should be an email adress
    groups              TEXT[], -- array of groupid
    username            TEXT,
    givenname           TEXT,
    lastname            TEXT,
    country             TEXT,
    organization        TEXT,
    organizationcountry TEXT,
    flags               TEXT, -- Additionnal properties (comma separated)
    topics              TEXT,
    password            TEXT NOT NULL, -- stored as sha1
    registrationdate    TIMESTAMP NOT NULL,
    activationcode      TEXT NOT NULL UNIQUE, -- activation code store as sha1
    activated           INTEGER DEFAULT 0,
    validatedby         TEXT, -- Who validated the user (usually admin)
    validationdate      TIMESTAMP -- Validation date
);
CREATE INDEX idx_email_users ON ${SCHEMA}.users (email);

--
-- groups table list
--
CREATE TABLE ${SCHEMA}.groups (
    groupid             TEXT NOT NULL UNIQUE,
    childrens           TEXT -- groupids that are childrens of this groupid (comma separated)
);
CREATE INDEX idx_groups_groupid ON ${SCHEMA}.groups (groupid);

-- INSERT base groups
INSERT INTO ${SCHEMA}.groups (groupid) VALUES ('admin');
INSERT INTO ${SCHEMA}.groups (groupid) VALUES ('default');

--
-- rights table list user rights on collection
--
CREATE TABLE ${SCHEMA}.rights (
    gid                 SERIAL PRIMARY KEY, -- unique id
    ownertype           TEXT NOT NULL, -- 'user' or 'group'
    owner               TEXT NOT NULL, -- email from ${SCHEMA}.users or groupid from ${SCHEMA}.groups
    targettype          TEXT NOT NULL, -- 'collection' or 'feature'
    target              TEXT NOT NULL, -- collection from ${SCHEMA}.collection or featureid from ${SCHEMA}.features
    download            INTEGER DEFAULT 0,
    visualize           INTEGER DEFAULT 0,
    createcollection    INTEGER DEFAULT 0,
    productidentifier   TEXT -- same as productidentifier in ${SCHEMA}.features
);
CREATE INDEX idx_owner_rights ON ${SCHEMA}.rights (owner);
CREATE INDEX idx_ownertype_rights ON ${SCHEMA}.rights (ownertype);

-- INSERT admin rights
INSERT INTO ${SCHEMA}.rights (ownertype, owner, targettype, target, download, visualize, createcollection) VALUES ('group','admin','collection','*',1,1,1);

--
-- list licenses signed by users
--
CREATE TABLE ${SCHEMA}.signatures (
    email               TEXT, -- email from ${SCHEMA}.users
    licenseid           TEXT, -- licenseid from ${SCHEMA}.licenses
    signdate            TIMESTAMP NOT NULL,
    counter             INTEGER -- number of time the user sign the license
);
CREATE INDEX idx_email_signatures ON ${SCHEMA}.signatures (email);

--
-- history table stores all user requests
--
CREATE TABLE ${SCHEMA}.history (
    gid                 SERIAL PRIMARY KEY,
    email               TEXT DEFAULT 'unregistered',
    method              TEXT,
    service             TEXT,
    collection          TEXT,
    resourceid          TEXT,
    query               TEXT DEFAULT NULL,
    querytime           TIMESTAMP,
    url                 TEXT DEFAULT NULL,
    ip                  TEXT
);
CREATE INDEX idx_service_history ON ${SCHEMA}.history (service);
CREATE INDEX idx_email_history ON ${SCHEMA}.history (email);
CREATE INDEX idx_querytime_history ON ${SCHEMA}.history (querytime);

--
-- cart table stores user download request
--
CREATE TABLE ${SCHEMA}.cart (
    gid                 SERIAL PRIMARY KEY,
    email               TEXT,
    itemid              TEXT NOT NULL,
    querytime           TIMESTAMP,
    item                TEXT NOT NULL -- item as JSON
);
CREATE INDEX idx_email_cart ON ${SCHEMA}.cart (email);
CREATE INDEX idx_itemid_cart ON ${SCHEMA}.cart (itemid);

--
-- orders table stores user orders
--
CREATE TABLE ${SCHEMA}.orders (
    gid                 SERIAL PRIMARY KEY,
    email               TEXT,
    orderid             TEXT NOT NULL,
    querytime           TIMESTAMP,
    items               TEXT NOT NULL -- items as an array of JSON cart item
);
CREATE INDEX idx_email_orders ON ${SCHEMA}.orders (email);
CREATE INDEX idx_orderid_orders ON ${SCHEMA}.orders (orderid);
CREATE INDEX idx_querytime_orders ON ${SCHEMA}.orders (querytime);

--
-- temporary download table
--
CREATE TABLE ${SCHEMA}.sharedlinks (
    gid                 SERIAL PRIMARY KEY,
    token               TEXT UNIQUE NOT NULL,
    url                 TEXT NOT NULL,
    validity            TIMESTAMP,
    email               TEXT
);
CREATE INDEX idx_token_sharedlinks ON ${SCHEMA}.sharedlinks (token);

--
-- Revoked tokens table
-- On insert trigger delete entries older than 48 hours
--
CREATE TABLE ${SCHEMA}.revokedtokens (
    gid                 SERIAL PRIMARY KEY,
    token               TEXT UNIQUE NOT NULL,
    creationdate        TIMESTAMP NOT NULL DEFAULT now()
);
CREATE INDEX idx_token_revokedtokens ON ${SCHEMA}.revokedtokens (token);
CREATE FUNCTION delete_old_tokens() RETURNS trigger
    LANGUAGE plpgsql
    AS \$\$
BEGIN
  DELETE FROM ${SCHEMA}.revokedtokens WHERE creationdate < now() - INTERVAL '2 days';
  RETURN NEW;
END;
\$\$;
CREATE TRIGGER old_tokens_gc AFTER INSERT ON ${SCHEMA}.revokedtokens EXECUTE PROCEDURE delete_old_tokens();
EOF

# Data
psql -U $SUPERUSER -d $DB -f $DATADIR/platformsAndInstruments.sql
psql -U $SUPERUSER -d $DB -f $DATADIR/regionsAndStates.sql
psql -U $SUPERUSER -d $DB -f $DATADIR/en/landuses.sql
psql -U $SUPERUSER -d $DB -f $DATADIR/en/continentsAndCountries.sql
psql -U $SUPERUSER -d $DB -f $DATADIR/en/generalKeywords.sql
psql -U $SUPERUSER -d $DB -f $DATADIR/fr/landuses.sql
psql -U $SUPERUSER -d $DB -f $DATADIR/fr/continentsAndCountries.sql
psql -U $SUPERUSER -d $DB -f $DATADIR/fr/generalKeywords.sql

# Normalize values
psql -U $SUPERUSER -d $DB << EOF
UPDATE ${SCHEMA}.keywords SET value=normalize(value) WHERE TYPE IN ('continent', 'country', 'region', 'state', 'landuse');
EOF

# Rights
psql -U $SUPERUSER -d $DB << EOF

-- CHANGE OWNER
ALTER SCHEMA public OWNER TO $USER;
ALTER SCHEMA ${SCHEMA} OWNER TO $USER;
ALTER TABLE public.geometry_columns OWNER TO $USER;
ALTER TABLE public.geography_columns OWNER TO $USER;
ALTER TABLE public.spatial_ref_sys OWNER TO $USER;
ALTER TABLE ${SCHEMA}.features OWNER TO $USER;
ALTER DATABASE $DB OWNER TO $USER;

-- REVOKE rights on public schema
REVOKE CREATE ON SCHEMA public FROM PUBLIC;

-- SET user RIGHTS
GRANT ALL ON geometry_columns TO $USER;
GRANT ALL ON geography_columns TO $USER;
GRANT SELECT ON spatial_ref_sys TO $USER;
GRANT CREATE ON DATABASE $DB TO $USER;

GRANT ALL ON SCHEMA ${SCHEMA} TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.collections TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.osdescriptions TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.licenses TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.keywords TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.features TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.facets TO $USER;

GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.users TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.revokedtokens TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.rights TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.groups TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.signatures TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.cart TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.orders TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON ${SCHEMA}.sharedlinks TO $USER;
GRANT SELECT,INSERT,UPDATE ON ${SCHEMA}.history TO $USER;

GRANT SELECT,UPDATE ON ${SCHEMA}.users_userid_seq TO $USER;
GRANT SELECT,UPDATE ON ${SCHEMA}.revokedtokens_gid_seq TO $USER;
GRANT SELECT,UPDATE ON ${SCHEMA}.history_gid_seq TO $USER;
GRANT SELECT,UPDATE ON ${SCHEMA}.sharedlinks_gid_seq TO $USER;
GRANT SELECT,UPDATE ON ${SCHEMA}.cart_gid_seq TO $USER;
GRANT SELECT,UPDATE ON ${SCHEMA}.orders_gid_seq TO $USER;
GRANT SELECT,UPDATE ON ${SCHEMA}.rights_gid_seq TO $USER;
GRANT SELECT,UPDATE ON ${SCHEMA}.keywords_gid_seq TO $USER;
GRANT SELECT,UPDATE ON ${SCHEMA}.facets_gid_seq TO $USER;

EOF
