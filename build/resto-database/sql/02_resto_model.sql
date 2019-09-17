--
-- resto core model
--

--
-- resto core model is sored under resto schema
--
CREATE SCHEMA IF NOT EXISTS resto;

-- 
-- Sequence used to generate time based unique identifier (see public.timestamp_to_id() function)
--
CREATE SEQUENCE IF NOT EXISTS resto.table_id_seq;

--
-- collections table list all resto collections
--
CREATE TABLE IF NOT EXISTS resto.collection (

    -- Unique name for collection.
    -- It cannot start with a digit and cannot contains special characters
    name                TEXT PRIMARY KEY,

    -- Model used to ingest collection metadata
    model               TEXT,

    -- Model lineage
    lineage             TEXT[],

    -- A default license attached to this collection.
    -- Every feature within this collection will inherit from the collection's license
    licenseid           TEXT,

    -- Mapping for "on the fly" transformation of properties values 
    mapping             JSON,

    -- Visibility - group visibility (only user within this group can see collection)
    visibility          INTEGER,

    -- Owner of the collection. References resto.user.id
    owner               BIGINT,

    -- Timestamp of collection creation
    created             TIMESTAMP,

    -- Start time of the collection 
    startdate           TIMESTAMP,

    -- Completion time of the collection 
    completiondate      TIMESTAMP,

    -- Spatial extent of the collection
    bbox                GEOMETRY(GEOMETRY, 4326),

    -- [STAC] Providers
    providers           JSON,

    -- [STAC] Additional properties
    properties          JSON

);

--
-- osdescriptions table describe all RESTo collections
--
CREATE TABLE IF NOT EXISTS resto.osdescription (

    -- Reference resto.collection.name
    collection          TEXT REFERENCES resto.collection (name) ON DELETE CASCADE, 

    -- OpenSearch description lang
    lang                TEXT,

    -- Contains a brief human-readable title that identifies this search engine.
    shortname           TEXT,
    
    -- Contains an extended human-readable title that identifies this search engine.
    longname            TEXT,
    
    -- Contains a human-readable text description of the search engine.
    description         TEXT,

    -- Contains a set of words that are used as keywords to identify and categorize this search content. Tags must be a single word and are delimited by the space character
    tags                TEXT,

    -- Contains the human-readable name or identifier of the creator or maintainer of the description document.
    Developer           TEXT,

    -- Contains an email address at which the maintainer of the description document can be reached.
    contact             TEXT,
    
    -- Defines a search query that can be performed by search clients. Please see the OpenSearch Query element specification for more information.
    query               TEXT,
    
    -- Contains a list of all sources or entities that should be credited for the content contained in the search feed
    attribution         TEXT

);

--
-- Features table - handle every metadata
--
CREATE TABLE IF NOT EXISTS resto.feature (

    -- [INDEXED] Unique identifier based on published date
    -- "id"                BIGINT PRIMARY KEY DEFAULT public.timestamp_to_id(clock_timestamp(), 1, nextval('resto.table_id_seq')),

    -- [INDEXED] UUID v5 based on productidentifier
    "id"                UUID PRIMARY KEY DEFAULT public.uuid_generate_v4(),

    -- [INDEXED] Reference resto.collection.name - A feature is within one and only one collection
    collection          TEXT NOT NULL, 

    -- Vendor identifier (for migration)
    productidentifier   TEXT,

    -- A title for this feature
    title               TEXT,

    -- A description for this feature
    description         TEXT,

    -- Start of validity timestamp for this feature 
    startdate           TIMESTAMP,

    -- End of validity timestamp for this feature. If equal to started then feature is "instantaneous"
    completiondate      TIMESTAMP,

    -- [INDEXED] Visibility - only user within a group with the same name as visibility can see feature
    visibility          INTEGER,

    -- [INDEXED] Owner of the feature. Reference resto.user.id
    owner               BIGINT,

    -- [INDEXED] Open value
    status              INTEGER,

    -- Number of likes for this feature. Used by Social add-on
    likes               INTEGER,

    -- Number of comments for this feature. Used by Social add-on
    comments            INTEGER, 

    -- Url to a preview for this feature
    quicklook           TEXT,

    -- Url to a thumbnails for this feature
    thumbnail           TEXT,

    -- Metadata. You can put whatever you want.
    -- Field indexation is based on collection type
    --  
    --  Example for a satellite imagery
    --      {
    --          authority:
    --          productType:
    --          processingLevel:
    --          platform:
    --          instrument:
    --          resolution:
    --          sensorMode:
    --          orbitNumber:
    --      }
    metadata            JSON,

    -- Assets array contains download, metadata original file, etc.
    --  
    --      {
    --          download:{
    --              "title":"Download link",
    --              "href":"http://localhost/image123.tif",
    --              "realPath":"file://data/images/image123.tif",
    --              "size": 1234567,
    --              "integrity": "sha384-oqVuAfXRKap...7f86",
    --              "type":"application/tif"
    --          },
    --          metadata:{
    --              "rel":"alternate",
    --              "title":"Metadata link",
    --              "href":"http://localhost/image123.xml",
    --              "realPath":"file://data/images/image123.xml",
    --              "type":"application/xml"
    --          }
    --      }
    --
    assets               JSON,

    -- Links array contains related url
    --  
    --      [
    --          {"rel": "acquisition", "href": "http://cool-sat.com/catalog/acquisitions/20160503_56"}
    --          {"rel": "something", "title":"SIVolcano", "href": "http://volcano.si.edu/volcano.cfm?vn=233020"}
    --      ]
    --
    links               JSON,

    -- Timestamp of publication for this feature
    published           TIMESTAMP,

    -- Timestamp of update for this feature
    updated             TIMESTAMP,

    -- Keywords computed by Tag add-on
    keywords            JSON,

    -- 
    -- List of hashtags (without prefix # !)
    --
    -- Two kind of hashtags:
    --   * hashtags without {Resto::TAG_SEPARATOR} hashtags *provided* by user from description
    --   * hashtags with {Resto::TAG_SEPARATOR} hashtags *computed* by Tag add-on (depend on collection)
    hashtags            TEXT[],

    -- Original geometry as provided during feature insertion
    -- It is set to NULL if equals to _geometry (see below) 
    geometry            GEOMETRY(GEOMETRY, 4326),
    
    -- Centroid computed from geometry
    centroid            GEOMETRY(POINT, 4326),

    -- Result of ST_SplitDateLine(geometry)
    -- Guarantee a valid geometry in database even if input geometry crosses -180/180 meridian of crosses North or South pole
    -- If input geometry does not cross one of this case, then input geometry is not
    -- modified and _geometry equals geomety.
    _geometry           GEOMETRY(GEOMETRY, 4326),

    -- [INDEXED] Hashtags
    -- 
    -- List of normalized hashtags (without prefix # !)
    --
    normalized_hashtags TEXT[],

    -- [INDEXED] Start date unique iterator
    startdate_idx       BIGINT,
    
    -- [INDEXED] Published date unique iterator
    published_idx       BIGINT

);

--
-- Feature geometry is splitted into smaller part and indexed
--
CREATE TABLE IF NOT EXISTS resto.geometry_part (

    -- Feature identifier
    "id"                UUID REFERENCES resto.feature (id) ON DELETE CASCADE,

    -- Feature's collection
    collection          TEXT NOT NULL, 

    -- Part iterator
    part_num            INTEGER,

    -- [INDEXED] Geometry part
    geom                GEOMETRY(GEOMETRY, 4326),

    -- Primary key based on unique identifier
    PRIMARY KEY (id, part_num)

);

--
-- Features content common to all features belonging to LandCoverModel (based on itag)
--
CREATE TABLE IF NOT EXISTS resto.feature_landcover (

    -- [INDEXED] Reference resto.feature.id
    "id"                UUID PRIMARY KEY REFERENCES resto.feature (id) ON DELETE CASCADE,

    -- Collection name
    collection          TEXT NOT NULL, 

    -- Percentage of cultivated area
    cultivated          NUMERIC,

    -- Percentage of desert area
    desert              NUMERIC,

    -- Percentage of flooded area
    flooded             NUMERIC,

    -- Percentage of forest area
    forest              NUMERIC,

    -- Percentage of herbaceous area
    herbaceous          NUMERIC,

    -- Percentage of ice area
    ice                 NUMERIC,

    -- Percentage of urban area
    urban               NUMERIC,

    -- Percentage of water area
    water               NUMERIC,

    -- Population estimation (in number of people)
    population          NUMERIC,

    -- Population estimation density (in peopler per square kilometers)
    population_density  NUMERIC

);

--
-- Features specific properties based on SatelliteModel
--
CREATE TABLE IF NOT EXISTS resto.feature_satellite (

    -- [INDEXED] Reference resto.feature.id
    "id"                UUID PRIMARY KEY REFERENCES resto.feature (id) ON DELETE CASCADE,

    -- Collection name
    collection          TEXT NOT NULL,

    -- Image resolution in meters
    resolution          NUMERIC
    
);

--
-- Features specific properties based on OpticalModel
--
CREATE TABLE IF NOT EXISTS resto.feature_optical (

    -- [INDEXED] Reference resto.feature.id
    "id"                UUID PRIMARY KEY REFERENCES resto.feature (id) ON DELETE CASCADE,

    -- Collection name
    collection          TEXT NOT NULL,
    
    -- Percentage of snow in area
    snowcover           NUMERIC,

    -- Percentage of cloud area
    cloudcover          NUMERIC
    
);

--
-- users table list user informations
--
CREATE TABLE IF NOT EXISTS resto.user (

    -- Unique identifier based on resto serial (timestamp)
    "id"                BIGINT PRIMARY KEY DEFAULT public.timestamp_to_id(clock_timestamp(), 1, nextval('resto.table_id_seq')),

    -- Email adress
    email               TEXT NOT NULL UNIQUE,

    -- By default concatenation of firstname lastname
    name                TEXT,

    -- First name
    firstname           TEXT,

    -- Last name
    lastname            TEXT,

    -- User description
    bio                 TEXT,

    -- Array of groups referenced by resto.group.id
    groups              INTEGER[],

    -- User lang
    lang                TEXT,

    -- User country
    country             TEXT,

    -- User organization
    organization        TEXT,

    -- User organization country
    organizationcountry TEXT,

    -- Comm separated list of additionnal properties
    flags               TEXT,

    -- Array of topics of interest name. Reference resto.topic.name
    topics              TEXT[],

    -- Password stored as sha1 with salt
    password            TEXT NOT NULL,

    -- Url to user's picture
    picture             TEXT,

    -- Registration timestamp of the user
    registrationdate    TIMESTAMP,

    -- Token used for password reset
    resettoken          TEXT,

    -- Timestamp until which the reset token is valid
    resetexpire         TIMESTAMP,

    -- User is activated (1) once registration is completed (i.e. email adress verified)
    activated           INTEGER,

    -- Number of followers. Used by Social add-on
    followers           INTEGER,

    -- Number of followings. Used by Social add-on
    followings          INTEGER,

    -- Who validated the user (usually admin)
    validatedby         TEXT, 

    -- Date of validation
    validationdate      TIMESTAMP, -- Validation date

    -- External Identity provider (Facebook, google, etc.)
    externalidp         JSON, 

    -- Free application settings
    settings            JSON

);

--
-- Followers table
--
CREATE TABLE IF NOT EXISTS resto.follower (

    -- Reference resto.user.id
    userid              BIGINT NOT NULL REFERENCES resto.user (id) ON DELETE CASCADE,

    -- Reference resto.user.id
    followerid          BIGINT NOT NULL REFERENCES resto.user (id) ON DELETE CASCADE,

    -- Timestamp of relationship creation
    created             TIMESTAMP,

    PRIMARY KEY (userid, followerid)

);

--
-- groups table list
--
CREATE SEQUENCE IF NOT EXISTS resto.group_id_seq START 100 INCREMENT 1;
CREATE TABLE IF NOT EXISTS resto.group (

    -- Group identifier is a serial
    "id"                INTEGER PRIMARY KEY DEFAULT nextval('resto.group_id_seq'),

    -- Name of the group
    name                TEXT NOT NULL,

    -- Description
    description         TEXT,

    -- Owner of the group
    owner               BIGINT,

    -- Timestamp of group creation
    created             TIMESTAMP

);

--
-- topics table
--
CREATE TABLE IF NOT EXISTS resto.topic (

    -- Topic of interest name
    name                TEXT PRIMARY KEY,

    -- Description
    description         TEXT

);

--
-- rights table list user rights on collection
--
CREATE SEQUENCE IF NOT EXISTS resto.right_id_seq START 100 INCREMENT 1;
CREATE TABLE IF NOT EXISTS resto.right (

    -- Unique id
    gid                 INTEGER PRIMARY KEY DEFAULT nextval('resto.right_id_seq'), 

    -- Reference to resto.user.id
    userid              BIGINT,

    -- Reference to resto.group.groupid
    groupid             BIGINT,

    -- Reference resto.collection.name
    collection          TEXT,

    -- Reference resto.feature.id
    featureid           UUID,

    -- Has right to download = 1. Otherwise 0
    download            INTEGER DEFAULT 0,

    -- Has right to visualize = 1. Otherwise 0
    visualize           INTEGER DEFAULT 0,

    -- Has right to create a collection = 1. Otherwise 0
    createcollection    INTEGER DEFAULT 0

);

--
-- Shared links are temporaty links available when you know the url
--
CREATE SEQUENCE IF NOT EXISTS resto.sharedlink_id_seq START 100 INCREMENT 1;
CREATE TABLE IF NOT EXISTS resto.sharedlink (

    -- Not used
    gid                 INTEGER PRIMARY KEY DEFAULT nextval('resto.sharedlink_id_seq'),

    -- Token
    token               TEXT UNIQUE NOT NULL,

    -- Url that can be requested with this token
    url                 TEXT NOT NULL,

    -- Validity in the future - if request time is greater than validity then 403
    validity            TIMESTAMP,

    -- Original requester of this link. Reference to resto.user.id
    userid              BIGINT

);

--
-- Revoked tokens table
-- On insert trigger delete entries older than 48 hours
--
CREATE SEQUENCE IF NOT EXISTS resto.revokedtoken_id_seq START 100 INCREMENT 1;
CREATE TABLE IF NOT EXISTS resto.revokedtoken (

    -- Unique identifier (not used)
    gid                 INTEGER PRIMARY KEY DEFAULT nextval('resto.revokedtoken_id_seq'),

    -- Token either JWT or RJWT
    token               TEXT UNIQUE NOT NULL,

    -- Date of token creation
    created             TIMESTAMP NOT NULL DEFAULT now(),

    -- Usually the exp time of the token - after this date the token is no more valid and can be removed from this table
    validuntil          TIMESTAMP

);

--
-- Facets table
--
CREATE TABLE IF NOT EXISTS resto.facet (

    -- Identifier for the facet (unique in combination with collection name)
    id                  TEXT NOT NULL,

    -- Collection name attached to the facet
    collection          TEXT NOT NULL,
    
    -- Facet value
    value               TEXT,

    -- Facet type (i.e. hashtag, region, state, location, etc.)
    type                TEXT,

    -- Parent identifier (i.e. 'europe' for facet 'france')
    pid                 TEXT,

    -- Set to 1 if facet is a terminal leaf, 0 otherwise (used for STAC)
    isleaf              INTEGER,

    -- Number of appearance of this facet within the collection
    counter             INTEGER,

    -- Facet date of creation
    created             TIMESTAMP DEFAULT now(),

    -- Creator of the facet
    creator             BIGINT,

    -- The id,collection pair should be unique
    PRIMARY KEY (id, collection)

);

--
-- Logs table stores all user requests
--
CREATE TABLE IF NOT EXISTS resto.log (

    gid                 SERIAL PRIMARY KEY,

    -- Reference resto.user id
    userid              BIGINT,

    -- Http method (i.e. GET,PUT,POST,DELETE)
    method              TEXT,

    -- Time of query
    querytime           TIMESTAMP,

    -- Query path
    path                TEXT,

    -- Query params
    query               TEXT,

    -- Query initiator IP address
    ip                  TEXT

);

