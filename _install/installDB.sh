#!/bin/bash
#
#  RESTo
# 
#  RESTo - REstful Semantic search Tool for geOspatial 
# 
#  Copyright 2014 Jérôme Gasperi <https://github.com/jjrom>
# 
#  jerome[dot]gasperi[at]gmail[dot]com
#  
#  
#  This software is governed by the CeCILL-B license under French law and
#  abiding by the rules of distribution of free software.  You can  use,
#  modify and/ or redistribute the software under the terms of the CeCILL-B
#  license as circulated by CEA, CNRS and INRIA at the following URL
#  "http://www.cecill.info".
# 
#  As a counterpart to the access to the source code and  rights to copy,
#  modify and redistribute granted by the license, users are provided only
#  with a limited warranty  and the software's author,  the holder of the
#  economic rights,  and the successive licensors  have only  limited
#  liability.
# 
#  In this respect, the user's attention is drawn to the risks associated
#  with loading,  using,  modifying and/or developing or reproducing the
#  software by the user in light of its specific status of free software,
#  that may mean  that it is complicated to manipulate,  and  that  also
#  therefore means  that it is reserved for developers  and  experienced
#  professionals having in-depth computer knowledge. Users are therefore
#  encouraged to load and test the software's suitability as regards their
#  requirements in conditions enabling the security of their systems and/or
#  data to be ensured and,  more generally, to use and operate it in the
#  same conditions as regards security.
# 
#  The fact that you are presently reading this means that you have had
#  knowledge of the CeCILL-B license and that you accept its terms.
#  

# Paths are mandatory from command line
SUPERUSER=postgres
DROPFIRST=NO
DB=resto2
USER=resto
DATADIR=`dirname $0`/data
usage="## RESTo database installation\n\n  Usage $0 -p <resto (Read+Write database) user password> [-d <PostGIS directory> -s <database SUPERUSER> -F]\n\n  -d : absolute path to the directory containing postgis.sql - If not set EXTENSION mechanism will be used\n  -s : dabase SUPERUSER (default "postgres")\n  -F : WARNING - suppress existing resto schema within resto database\n"
while getopts "d:s:p:hF" options; do
    case $options in
        d ) ROOTDIR=`echo $OPTARG`;;
        s ) SUPERUSER=`echo $OPTARG`;;
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
DROP SCHEMA IF EXISTS resto CASCADE;
DROP SCHEMA IF EXISTS usermanagement CASCADE;
EOF
fi
#############CREATE DB ##############
psql -d $DB -U $SUPERUSER << EOF

-- hstore is used for collection datasources
CREATE EXTENSION hstore;

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
SELECT replace(lower(unaccent(\$1)),' ','-') 
\$\$ LANGUAGE sql;
EOF

-- 
-- resto schema contains collections descriptions tables
--
CREATE SCHEMA resto;

--
-- collections table list all RESTo collections
--
CREATE TABLE resto.collections (
    collection          TEXT PRIMARY KEY,
    creationdate        TIMESTAMP,
    model               TEXT DEFAULT 'RestoModel_default',
    status              TEXT DEFAULT 'public',
    license             TEXT,
    mapping             TEXT
);
CREATE INDEX idx_status_collections ON resto.collections (status);
CREATE INDEX idx_creationdate_collections ON resto.collections (creationdate);

--
-- osdescriptions table describe all RESTo collections
--
CREATE TABLE resto.osdescriptions (
    collection          TEXT,
    lang                TEXT,
    shortname           TEXT,
    longname            TEXT,
    description         TEXT,
    tags                TEXT,
    developper          TEXT,
    contact             TEXT,
    query               TEXT,
    attribution         TEXT
);
ALTER TABLE ONLY resto.osdescriptions ADD CONSTRAINT fk_collection FOREIGN KEY (collection) REFERENCES resto.collections(collection);
ALTER TABLE ONLY resto.osdescriptions ADD CONSTRAINT cl_collection UNIQUE(collection, lang);
CREATE INDEX idx_collection_osdescriptions ON resto.osdescriptions (collection);
CREATE INDEX idx_lang_osdescriptions ON resto.osdescriptions (lang);


--
-- Keywords table
--
CREATE TABLE resto.keywords (
    gid                 SERIAL PRIMARY KEY, -- unique id
    name                TEXT, -- keyword name in given language code
    type                TEXT, -- type of keyword (i.e. region, state, location, etc.)
    lang                TEXT, -- ISO A2 language code in lowercase
    value               TEXT, -- keyword as stored in features keywords columns
    location            TEXT DEFAULT NULL -- 'country code:bounding box'
);
CREATE INDEX idx_name_keywords ON resto.keywords (normalize(name));
CREATE INDEX idx_type_keywords ON resto.keywords (type);
CREATE INDEX idx_lang_keywords ON resto.keywords (lang);

--
-- Facets table - store statistics for keywords appearance
--
CREATE TABLE resto.facets (
    gid                 SERIAL PRIMARY KEY, -- unique id
    uid                 TEXT,
    value               TEXT, -- keyword value (without type)
    type                TEXT, -- type of keyword (i.e. region, state, location, etc.)
    pid                 TEXT, -- parent hash (i.e. 'europe' for keyword 'france')
    pvalue              TEXT, -- keyword parent (without type)
    ptype               TEXT, -- keyword parent type (i.e. 'continent' for 'country')
    collection          TEXT, -- collection name
    counter             INTEGER -- number of appearance of this keyword within the collection
);
CREATE INDEX idx_type_facets ON resto.facets (type);
CREATE INDEX idx_uid_facets ON resto.facets (uid);
CREATE INDEX idx_pid_facets ON resto.facets (pid);
CREATE INDEX idx_collection_facets ON resto.facets (collection);


--
-- tags table list all tags attached to data within collection
--
CREATE TABLE resto.tags (
    tag                 TEXT PRIMARY KEY,
    creationdate        TIMESTAMP,
    updateddate         TIMESTAMP,
    occurence           INTEGER
);
CREATE INDEX idx_updated_tags ON resto.tags (updateddate);

--
-- features TABLE MUST BE EMPTY (inheritance)
--

CREATE TABLE resto.features (
    identifier          TEXT UNIQUE,
    parentidentifier    TEXT,
    collection          TEXT,
    visible             INTEGER DEFAULT 1,
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
    resource_size       INTEGER,
    resource_checksum   TEXT, -- Checksum should be on the form checksumtype=checksum (e.g. SHA1=.....)
    wms                 TEXT,
    updated             TIMESTAMP,
    published           TIMESTAMP,
    keywords            hstore DEFAULT '',
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
SELECT AddGeometryColumn('resto', 'features', 'geometry', '4326', 'GEOMETRY', 2);

-- 
-- users schema contains users descriptions tables
--
CREATE SCHEMA usermanagement;

--
-- users table list user informations
--
CREATE TABLE usermanagement.users (
    userid              SERIAL PRIMARY KEY,
    email               TEXT UNIQUE,  -- should be an email adress
    groupname           TEXT, -- group name
    username            TEXT,
    givenname           TEXT,
    lastname            TEXT,
    password            TEXT NOT NULL, -- stored as sha1
    registrationdate    TIMESTAMP NOT NULL,
    activationcode      TEXT NOT NULL UNIQUE, -- activation code store as sha1
    activated           INTEGER DEFAULT 0,              
    connected           INTEGER DEFAULT 0
);
CREATE INDEX idx_email_users ON usermanagement.users (email);
CREATE INDEX idx_groupname_users ON usermanagement.users (groupname);

--
-- rights table list user rights on collection
--
CREATE TABLE usermanagement.rights (
    gid                 SERIAL PRIMARY KEY, -- unique id
    collection          TEXT, -- same as collection in resto.collections
    featureid           TEXT, -- same as collection in resto.collections
    emailorgroup        TEXT NOT NULL,  -- email or group name (from usermanagement.users)
    search              INTEGER DEFAULT 0,
    visualize           INTEGER DEFAULT 0,
    download            INTEGER DEFAULT 0,
    canpost             INTEGER DEFAULT 0,
    canput              INTEGER DEFAULT 0,
    candelete           INTEGER DEFAULT 0,
    filters             TEXT -- serialized json representation of services rights
);
CREATE INDEX idx_emailorgroup_rights ON usermanagement.rights (emailorgroup);

--
-- list licenses signed by users
--
CREATE TABLE usermanagement.signatures (
    email               TEXT, -- email from usermanagement.users
    collection          TEXT, -- collection from resto.collections
    signdate            TIMESTAMP NOT NULL
);
CREATE INDEX idx_email_signatures ON usermanagement.signatures (email);

--
-- history table stores all user requests
--
CREATE TABLE usermanagement.history (
    gid                 SERIAL PRIMARY KEY,
    userid              INTEGER DEFAULT -1,
    method              TEXT,
    service             TEXT,
    collection          TEXT,
    resourceid          TEXT,
    query               TEXT DEFAULT NULL,
    querytime           TIMESTAMP,
    url                 TEXT DEFAULT NULL,
    ip                  TEXT
);
CREATE INDEX idx_service_history ON usermanagement.history (service);
CREATE INDEX idx_userid_history ON usermanagement.history (userid);

--
-- cart table stores user download request
--
CREATE TABLE usermanagement.cart (
    gid                 SERIAL PRIMARY KEY,
    email               TEXT,
    itemid              TEXT NOT NULL,
    querytime           TIMESTAMP,
    item                TEXT NOT NULL -- item as JSON
);
CREATE INDEX idx_email_cart ON usermanagement.cart (email);
CREATE INDEX idx_itemid_cart ON usermanagement.cart (itemid);

--
-- orders table stores user orders
--
CREATE TABLE usermanagement.orders (
    gid                 SERIAL PRIMARY KEY,
    email               TEXT,
    orderid             TEXT NOT NULL,
    querytime           TIMESTAMP,
    items               TEXT NOT NULL -- items as an array of JSON cart item
);
CREATE INDEX idx_email_orders ON usermanagement.orders (email);
CREATE INDEX idx_orderid_orders ON usermanagement.orders (orderid);

--
-- temporary download table
--
CREATE TABLE usermanagement.sharedlinks (
    gid                 SERIAL PRIMARY KEY,
    token               TEXT UNIQUE NOT NULL,
    url                 TEXT NOT NULL,
    validity            TIMESTAMP
);
CREATE INDEX idx_token_sharedlinks ON usermanagement.sharedlinks (token);
EOF

# Data
psql -U $SUPERUSER -d $DB -f $DATADIR/platformsAndInstruments.sql
psql -U $SUPERUSER -d $DB -f $DATADIR/landuses.sql
psql -U $SUPERUSER -d $DB -f $DATADIR/continentsAndCountries.sql
psql -U $SUPERUSER -d $DB -f $DATADIR/regionsAndStates.sql

# Rights
psql -U $SUPERUSER -d $DB << EOF

-- CHANGE OWNER
ALTER SCHEMA public OWNER TO $USER;
ALTER SCHEMA resto OWNER TO $USER;
ALTER SCHEMA usermanagement OWNER TO $USER;
ALTER TABLE public.geometry_columns OWNER TO $USER;
ALTER TABLE public.geography_columns OWNER TO $USER;
ALTER TABLE public.spatial_ref_sys OWNER TO $USER;
ALTER TABLE resto.features OWNER TO $USER;
ALTER DATABASE $DB OWNER TO $USER;

-- REVOKE rights on public schema
REVOKE CREATE ON SCHEMA public FROM PUBLIC;

-- SET user RIGHTS
GRANT ALL ON geometry_columns TO $USER;
GRANT ALL ON geography_columns TO $USER;
GRANT SELECT ON spatial_ref_sys TO $USER;
GRANT CREATE ON DATABASE $DB TO $USER;

GRANT ALL ON SCHEMA resto TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON resto.collections TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON resto.osdescriptions TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON resto.keywords TO $USER;
GRANT SELECT,INSERT,UPDATE ON resto.features TO $USER;
GRANT SELECT,INSERT,UPDATE ON resto.facets TO $USER;
GRANT ALL ON resto.keywords_gid_seq TO $USER;
GRANT ALL ON resto.facets_gid_seq TO $USER;

GRANT ALL ON SCHEMA usermanagement TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON usermanagement.users TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON usermanagement.rights TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON usermanagement.signatures TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON usermanagement.cart TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON usermanagement.orders TO $USER;
GRANT SELECT,INSERT,UPDATE,DELETE ON usermanagement.sharedlinks TO $USER;
GRANT SELECT,INSERT,UPDATE ON usermanagement.history TO $USER;
GRANT ALL ON usermanagement.rights_gid_seq TO $USER;

GRANT SELECT,UPDATE ON usermanagement.users_userid_seq TO $USER;
GRANT SELECT,UPDATE ON usermanagement.history_gid_seq TO $USER;
GRANT SELECT,UPDATE ON usermanagement.sharedlinks_gid_seq TO $USER;
GRANT SELECT,UPDATE ON usermanagement.cart_gid_seq TO $USER;
GRANT SELECT,UPDATE ON usermanagement.orders_gid_seq TO $USER;


EOF



