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
    psql -d $DB -U $SUPERUSER -f $postgis -h $HOSTNAME
    psql -d $DB -U $SUPERUSER -f $projections -h $HOSTNAME
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
-- resto schema contains collections descriptions tables
--
CREATE SCHEMA resto;

--
-- collections table list all RESTo collections
--
CREATE TABLE resto.collections (
    collection          VARCHAR(50) PRIMARY KEY,
    creationdate        TIMESTAMP,
    model               VARCHAR(50) DEFAULT 'RestoModel_default',
    status              VARCHAR(10) DEFAULT 'public',
    license             TEXT,
    licenseurl          VARCHAR(255),
    mapping             TEXT
);
CREATE INDEX idx_status_collections ON resto.collections (status);
CREATE INDEX idx_creationdate_collections ON resto.collections (creationdate);

--
-- osdescriptions table describe all RESTo collections
--
CREATE TABLE resto.osdescriptions (
    collection          VARCHAR(50),
    lang                VARCHAR(2),
    shortname           VARCHAR(50),
    longname            VARCHAR(255),
    description         TEXT,
    tags                TEXT,
    developper          VARCHAR(50),
    contact             VARCHAR(50),
    query               VARCHAR(255),
    attribution         VARCHAR(255)
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
    name                VARCHAR(255), -- keyword name in given language code
    type                VARCHAR(50), -- type of keyword (i.e. region, state, location, etc.)
    lang                VARCHAR(2), -- ISO A2 language code in lowercase
    value               VARCHAR(255) -- keyword as stored in features keywords columns
);
CREATE INDEX idx_name_keywords ON resto.keywords (lower(unaccent(name)));
CREATE INDEX idx_type_keywords ON resto.keywords (type);
CREATE INDEX idx_lang_keywords ON resto.keywords (lang);

--
-- Facets table - store statistics for keywords appearance
--
CREATE TABLE resto.facets (
    gid                 SERIAL PRIMARY KEY, -- unique id
    value               VARCHAR(255), -- keyword as stored in features keywords columns
    type                VARCHAR(50), -- type of keyword (i.e. region, state, location, etc.)
    parent              VARCHAR(255), -- keyword parent (i.e. 'europe' for keyword 'france')
    parenttype          VARCHAR(50), -- keyword parent type (i.e. 'continent' for 'country')
    collection          VARCHAR(50), -- collection name
    counter             INTEGER -- number of appearance of this keyword within the collection
);
CREATE INDEX idx_value_facets ON resto.facets (value);
CREATE INDEX idx_parent_facets ON resto.facets (parent);
CREATE INDEX idx_collection_facets ON resto.facets (collection);


--
-- tags table list all tags attached to data within collection
--
CREATE TABLE resto.tags (
    tag                 VARCHAR(50) PRIMARY KEY,
    creationdate        TIMESTAMP,
    updateddate         TIMESTAMP,
    occurence           INTEGER
);
CREATE INDEX idx_updated_tags ON resto.tags (updateddate);

--
-- features TABLE MUST BE EMPTY (inheritance)
--

CREATE TABLE resto.features (
    identifier          CHAR(36) UNIQUE,
    parentidentifier    VARCHAR(250),
    collection          VARCHAR(50),
    productidentifier   VARCHAR(250),
    title               VARCHAR(250),
    description         TEXT,
    authority           VARCHAR(50),
    startdate           TIMESTAMP,
    completiondate      TIMESTAMP,
    producttype         VARCHAR(50),
    processinglevel     VARCHAR(50),
    platform            VARCHAR(50),
    instrument          VARCHAR(50),
    resolution          NUMERIC(8,2),
    sensormode          VARCHAR(20),
    orbitnumber         INTEGER,
    quicklook           VARCHAR(250),
    thumbnail           VARCHAR(250),
    metadata            VARCHAR(250),
    metadata_mimetype   VARCHAR(250),
    resource            VARCHAR(250),
    resource_mimetype   VARCHAR(250),
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
    lo_continents       TEXT[],
    lo_countries        TEXT[],
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
    email               VARCHAR(255) UNIQUE,  -- should be an email adress
    groupname           VARCHAR(20), -- group name
    username            VARCHAR(50),
    givenname           VARCHAR(255),
    lastname            VARCHAR(255),
    password            CHAR(40) NOT NULL, -- stored as sha1
    registrationdate    TIMESTAMP NOT NULL,
    activationcode      CHAR(40) NOT NULL UNIQUE, -- activation code store as sha1
    activated           BOOLEAN NOT NULL DEFAULT FALSE,              
    lastsessionid       VARCHAR(255)
);
CREATE INDEX idx_email_users ON usermanagement.users (email);
CREATE INDEX idx_groupname_users ON usermanagement.users (groupname);

--
-- rights table list user rights on collection
--
CREATE TABLE usermanagement.rights (
    gid                 SERIAL PRIMARY KEY, -- unique id
    collection          VARCHAR(50), -- same as collection in resto.collections
    featureid           CHAR(36), -- same as collection in resto.collections
    emailorgroup        VARCHAR(255) NOT NULL,  -- email or group name (from usermanagement.users)
    search              BOOLEAN DEFAULT FALSE,
    visualize           BOOLEAN DEFAULT FALSE,
    download            BOOLEAN DEFAULT FALSE,
    canpost             BOOLEAN DEFAULT FALSE,
    canput              BOOLEAN DEFAULT FALSE,
    candelete           BOOLEAN DEFAULT FALSE,
    filters             TEXT -- serialized json representation of services rights
);
CREATE INDEX idx_emailorgroup_rights ON usermanagement.rights (emailorgroup);

--
-- list licenses signed by users
--
CREATE TABLE usermanagement.signatures (
    email               VARCHAR(50), -- email from usermanagement.users
    collection          VARCHAR(50), -- collection from resto.collections
    signdate            TIMESTAMP NOT NULL
);
CREATE INDEX idx_email_signatures ON usermanagement.signatures (email);

--
-- history table stores all user requests
--
CREATE TABLE usermanagement.history (
    gid                 SERIAL PRIMARY KEY,
    userid              INTEGER DEFAULT -1,
    method              VARCHAR(6),
    service             VARCHAR(10),
    collection          VARCHAR(50),
    resourceid          CHAR(36),
    query               TEXT DEFAULT NULL,
    querytime           TIMESTAMP,
    url                 TEXT DEFAULT NULL,
    ip                  VARCHAR(15)
);
CREATE INDEX idx_service_history ON usermanagement.history (service);
CREATE INDEX idx_userid_history ON usermanagement.history (userid);

--
-- cart table stores user download request
--
CREATE TABLE usermanagement.cart (
    gid                 SERIAL PRIMARY KEY,
    email               VARCHAR(255),
    itemid              CHAR(40) NOT NULL,
    querytime           TIMESTAMP,
    item                TEXT NOT NULL -- item as JSON
);
CREATE INDEX idx_email_cart ON usermanagement.cart (email);
CREATE INDEX idx_itemid_cart ON usermanagement.cart (itemid);

--
-- temporary download table
--
CREATE TABLE usermanagement.sharedlinks (
    gid                 SERIAL PRIMARY KEY,
    token               CHAR(40) UNIQUE NOT NULL,
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
GRANT SELECT,INSERT,UPDATE,DELETE ON usermanagement.sharedlinks TO $USER;
GRANT SELECT,INSERT,UPDATE ON usermanagement.history TO $USER;

GRANT SELECT,UPDATE ON usermanagement.users_userid_seq TO $USER;
GRANT SELECT,UPDATE ON usermanagement.history_gid_seq TO $USER;
GRANT SELECT,UPDATE ON usermanagement.sharedlinks_gid_seq TO $USER;
GRANT SELECT,UPDATE ON usermanagement.cart_gid_seq TO $USER;

EOF



