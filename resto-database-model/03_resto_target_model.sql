--
-- resto dedicated target model
--

--
-- Target tables are sored under {DATABASE_TARGET_SCHEMA} schema (default is resto)
--
CREATE SCHEMA IF NOT EXISTS __DATABASE_TARGET_SCHEMA__;

--
-- collections table list all resto collections
--
CREATE TABLE IF NOT EXISTS __DATABASE_TARGET_SCHEMA__.collection (

    -- Unique id for collection
    "id"                TEXT PRIMARY KEY,

    -- [STAC] Title
    title               TEXT,

    -- [STAC] Description
    description         TEXT,

    -- Collection version
    version             TEXT,

    -- Model used to ingest collection metadata
    model               TEXT,

    -- Model lineage
    lineage             TEXT[],

    -- A default license attached to this collection.
    -- Every feature within this collection will inherit from the collection's license
    licenseid           TEXT,

    -- Visibility - group visibility (only user within this group can see collection)
    visibility          BIGINT[],

    -- Owner of the collection. References __DATABASE_COMMON_SCHEMA__.user.id
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
    properties          JSON,

    -- [STAC] Static links
    links               JSON,

    -- [STAC] Collection assets
    assets              JSON,

    -- [STAC] Keywords
    keywords            TEXT[]

);

--
-- Collection aliases
--
CREATE TABLE IF NOT EXISTS __DATABASE_TARGET_SCHEMA__.collection_alias (

    -- Alternate name to this collection
    alias               TEXT PRIMARY KEY,

    -- [INDEXED] Reference __DATABASE_TARGET_SCHEMA__.collection.id
    collection          TEXT REFERENCES __DATABASE_TARGET_SCHEMA__.collection (id) ON DELETE CASCADE

);

--
-- Features table - handle every metadata
--
CREATE TABLE IF NOT EXISTS __DATABASE_TARGET_SCHEMA__.feature (

    -- [INDEXED] UUID v5 based on productidentifier
    "id"                UUID PRIMARY KEY DEFAULT public.uuid_generate_v4(),

    -- [INDEXED] Reference __DATABASE_TARGET_SCHEMA__.collection.id - A feature is within one and only one collection
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
    visibility          BIGINT[],

    -- [INDEXED] Owner of the feature. Reference __DATABASE_COMMON_SCHEMA__.user.id
    owner               BIGINT,

    -- [INDEXED] Open value
    status              INTEGER,

    -- Number of likes for this feature. Used by Social add-on
    likes               INTEGER,

    -- Number of comments for this feature. Used by Social add-on
    comments            INTEGER, 

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

    -- Timestamp of creation for this feature metadata
    created           TIMESTAMP,

    -- Timestamp of update for this feature metadata
    updated             TIMESTAMP,

    -- Original geometry as provided during feature insertion
    -- It is set to NULL if equals to geom (see below) 
    geometry            GEOMETRY(GEOMETRY, 4326),
    
    -- Centroid computed from geometry
    centroid            GEOMETRY(POINT, 4326),

    -- Result of Antimeridian function applied to original geometry
    -- Guarantee a valid geometry in database even if input geometry crosses -180/180 meridian of crosses North or South pole
    -- If input geometry does not cross one of this case, then input geometry is not
    -- modified and geom equals geomety.
    geom           GEOMETRY(GEOMETRY, 4326),

    -- [INDEXED] Start date unique iterator
    startdate_idx       BIGINT,
    
    -- [INDEXED] Created date unique iterator
    created_idx       BIGINT

);

--
-- Feature geometry is splitted into smaller part and indexed
--
CREATE TABLE IF NOT EXISTS __DATABASE_TARGET_SCHEMA__.geometry_part (

    -- Feature identifier
    "id"                UUID REFERENCES __DATABASE_TARGET_SCHEMA__.feature (id) ON DELETE CASCADE,

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
-- Relation between features
--
CREATE TABLE IF NOT EXISTS __DATABASE_TARGET_SCHEMA__.relation (

    -- Reference feature id master
    id1                 UUID REFERENCES __DATABASE_TARGET_SCHEMA__.feature (id) ON DELETE CASCADE,

    -- Reference feature id slave
    id2                 UUID REFERENCES __DATABASE_TARGET_SCHEMA__.feature (id) ON DELETE CASCADE,

    -- Relation type: -1 (id1 is the parent of id2 - "hasSample"), 1 (id1 is the child of id1 - "isSampleOf")
    relation            INTEGER NOT NULL,

    -- Relation type
    type                TEXT,

    -- Relation creation date
    created             TIMESTAMP,

    -- Primary key based on unique identifier
    PRIMARY KEY (id1, id2)

);


--
-- Features content common to all features belonging to LandCoverModel (based on itag)
--
CREATE TABLE IF NOT EXISTS __DATABASE_TARGET_SCHEMA__.feature_landcover (

    -- [INDEXED] Reference __DATABASE_TARGET_SCHEMA__.feature.id
    "id"                UUID PRIMARY KEY REFERENCES __DATABASE_TARGET_SCHEMA__.feature (id) ON DELETE CASCADE,

    -- Collection id
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
CREATE TABLE IF NOT EXISTS __DATABASE_TARGET_SCHEMA__.feature_satellite (

    -- [INDEXED] Reference __DATABASE_TARGET_SCHEMA__.feature.id
    "id"                UUID PRIMARY KEY REFERENCES __DATABASE_TARGET_SCHEMA__.feature (id) ON DELETE CASCADE,

    -- Collection id
    collection          TEXT NOT NULL,

    -- Image resolution in meters
    resolution          NUMERIC
    
);

--
-- Features specific properties based on OpticalModel
--
CREATE TABLE IF NOT EXISTS __DATABASE_TARGET_SCHEMA__.feature_optical (

    -- [INDEXED] Reference __DATABASE_TARGET_SCHEMA__.feature.id
    "id"                UUID PRIMARY KEY REFERENCES __DATABASE_TARGET_SCHEMA__.feature (id) ON DELETE CASCADE,

    -- Collection id
    collection          TEXT NOT NULL,
    
    -- Percentage of snow in area
    snowcover           NUMERIC,

    -- Percentage of cloud area
    cloudcover          NUMERIC
    
);

--
-- Catalog table
--
CREATE TABLE IF NOT EXISTS __DATABASE_TARGET_SCHEMA__.catalog (

    --
    -- The id must be a path starting with '/' (e.g. '/geographical/Europe/France/Haute-Garonne/Toulouse)
    -- Note: only the last part is displayed in JSON output (e.g. Toulouse)
    --
    id                  TEXT PRIMARY KEY,
    
    -- A short descriptive one-line title for the Catalog.
    title               TEXT,

    -- Detailed multi-line description to fully explain the Catalog. CommonMark 0.29 syntax MAY be used for rich text representation.
    description         TEXT,

    -- Number of levels for this catalog
    level              INTEGER,

    -- Number of items within this catalog (total and per collection)
    counters            JSON,

    -- Owner of the catalog (i.e. the one who creates it)
    owner               BIGINT,

    -- Catalog date of creation
    created             TIMESTAMP DEFAULT now(),

    -- A list of references to other documents.
    links               JSON,

    -- Visibility - group visibility (only user within this group can see collection)
    visibility          BIGINT[],

    -- resto type 
    rtype               TEXT,

    -- Free object to store info
    properties          JSON,

    -- [STAC PROXY] This catalog is a proxy to a STAC catalog
    stac_url            TEXT,

    -- True to have this catalog "pinned" i.e. appears at the catalog root level whatever its real level
    pinned              BOOLEAN

);

--
-- Feature/Catalog association 
--
CREATE TABLE IF NOT EXISTS __DATABASE_TARGET_SCHEMA__.catalog_feature (

    -- Reference __DATABASE_TARGET_SCHEMA__.feature.id
    featureid               UUID NOT NULL REFERENCES __DATABASE_TARGET_SCHEMA__.feature (id) ON DELETE CASCADE,

    -- Leaf catalog path with . instead of / as delimiter
    path                    LTREE,

    -- This is a duplicate from feature title to avoid JOIN
    title                   TEXT,

    -- This is a duplicate from catalog id without constraint to avoid JOIN
    catalogid               TEXT,

    -- Feature collection
    collection              TEXT,

    -- Feature ingestion date
    created                 TIMESTAMP DEFAULT now(),

    PRIMARY KEY (featureid, path)
   
);

-- --------------------- INDEXES ---------------------------

-- [TABLE __DATABASE_TARGET_SCHEMA__.collection]
CREATE INDEX IF NOT EXISTS idx_lineage_collection ON __DATABASE_TARGET_SCHEMA__.collection USING GIN (lineage);
CREATE INDEX IF NOT EXISTS idx_visibility_collection ON  __DATABASE_TARGET_SCHEMA__.collection USING GIN (visibility);
CREATE INDEX IF NOT EXISTS idx_created_collection ON __DATABASE_TARGET_SCHEMA__.collection (created);
CREATE INDEX IF NOT EXISTS idx_keywords_collection ON __DATABASE_TARGET_SCHEMA__.collection USING GIN (keywords);

-- [TABLE __DATABASE_TARGET_SCHEMA__.catalog]
CREATE INDEX IF NOT EXISTS idx_description_catalog ON __DATABASE_TARGET_SCHEMA__.catalog USING GIN (public.normalize(description) gin_trgm_ops);
CREATE INDEX IF NOT EXISTS idx_level_catalog ON __DATABASE_TARGET_SCHEMA__.catalog USING btree (level);
CREATE INDEX IF NOT EXISTS idx_visibility_catalog ON  __DATABASE_TARGET_SCHEMA__.catalog USING GIN (visibility);

-- [TABLE __DATABASE_TARGET_SCHEMA__.catalog_feature]
CREATE INDEX IF NOT EXISTS idx_path_catalog_feature ON __DATABASE_TARGET_SCHEMA__.catalog_feature USING GIST (path);

-- [TABLE __DATABASE_TARGET_SCHEMA__.feature]
CREATE INDEX IF NOT EXISTS idx_collection_feature ON __DATABASE_TARGET_SCHEMA__.feature USING btree (collection);
CREATE INDEX IF NOT EXISTS idx_startdateidx_feature ON __DATABASE_TARGET_SCHEMA__.feature USING btree (startdate_idx);
CREATE INDEX IF NOT EXISTS idx_createdidx_feature ON __DATABASE_TARGET_SCHEMA__.feature USING btree (created_idx);
CREATE INDEX IF NOT EXISTS idx_owner_feature ON __DATABASE_TARGET_SCHEMA__.feature USING btree (owner) WHERE owner IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_visibility_feature ON __DATABASE_TARGET_SCHEMA__.feature USING GIN (visibility);
CREATE INDEX IF NOT EXISTS idx_status_feature ON __DATABASE_TARGET_SCHEMA__.feature USING btree (status);
CREATE INDEX IF NOT EXISTS idx_centroid_feature ON __DATABASE_TARGET_SCHEMA__.feature USING GIST (centroid);
CREATE INDEX IF NOT EXISTS idx_geom_feature ON __DATABASE_TARGET_SCHEMA__.feature USING GIST (geom);
CREATE INDEX IF NOT EXISTS idx_description_feature ON __DATABASE_TARGET_SCHEMA__.feature USING GIN (public.normalize(description) gin_trgm_ops);

-- [TABLE __DATABASE_TARGET_SCHEMA__.geometry_part]
CREATE INDEX IF NOT EXISTS idx_id_geometry_part ON __DATABASE_TARGET_SCHEMA__.geometry_part USING HASH (id);
CREATE INDEX IF NOT EXISTS idx_geom_geometry_part ON __DATABASE_TARGET_SCHEMA__.geometry_part USING GIST (geom);

-- [TABLE __DATABASE_TARGET_SCHEMA__.feature_landcover]
CREATE INDEX IF NOT EXISTS idx_cultivated_m_landcover ON __DATABASE_TARGET_SCHEMA__.feature_landcover USING btree (cultivated);
CREATE INDEX IF NOT EXISTS idx_desert_m_landcover ON __DATABASE_TARGET_SCHEMA__.feature_landcover USING btree (desert);
CREATE INDEX IF NOT EXISTS idx_flooded_m_landcover ON __DATABASE_TARGET_SCHEMA__.feature_landcover USING btree (flooded);
CREATE INDEX IF NOT EXISTS idx_forest_m_landcover ON __DATABASE_TARGET_SCHEMA__.feature_landcover USING btree (forest);
CREATE INDEX IF NOT EXISTS idx_herbaceous_m_landcover ON __DATABASE_TARGET_SCHEMA__.feature_landcover USING btree (herbaceous);
CREATE INDEX IF NOT EXISTS idx_ice_m_landcover ON __DATABASE_TARGET_SCHEMA__.feature_landcover USING btree (ice);
CREATE INDEX IF NOT EXISTS idx_urban_m_landcover ON __DATABASE_TARGET_SCHEMA__.feature_landcover USING btree (urban);
CREATE INDEX IF NOT EXISTS idx_water_m_landcover ON __DATABASE_TARGET_SCHEMA__.feature_landcover USING btree (water);

-- [TABLE __DATABASE_TARGET_SCHEMA__.feature_optical]
CREATE INDEX IF NOT EXISTS idx_cloudcover_m_optical ON __DATABASE_TARGET_SCHEMA__.feature_optical USING btree (cloudcover);


