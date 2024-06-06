--
-- resto functions
--

--------------------------------  FUNCTIONS -----------------------------------------------

--
-- 
-- SYNOPSYS:
--   ST_SimplifyPreserveTopologyWhenTooBig(geom_in, tolerance, maxpoints)
--
-- DESCRIPTION:
--   Apply a ST_SimplifyPreserveTopologyWhenTooBig on input geometry if the number of points of input geometry
--   is greater than maxpoints
--
-- USAGE:
--   SELECT ST_SimplifyPreserveTopologyWhenTooBig(geom_in geometry, tolerance float, maxpoints integer DEFAULT 1000);
--
CREATE OR REPLACE FUNCTION ST_SimplifyPreserveTopologyWhenTooBig(geom_in geometry, tolerance float, maxpoints integer default 1000)
    RETURNS geometry AS $$
BEGIN
    IF ST_NPoints(geom_in) > maxpoints THEN
        geom_in = ST_SimplifyPreserveTopology(geom_in, tolerance);
    END IF;
    RETURN geom_in;
END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- 
-- SYNOPSYS:
--   ST_ExtentAsString(geom_in)
--
-- DESCRIPTION:
--   Return a string of geometry extent i.e. [lonMin,latMin,lonMax,latMax]
--
-- USAGE:
--   SELECT ST_ExtentAsString(geom_in);
--
CREATE OR REPLACE FUNCTION ST_ExtentAsString(geom_in geometry)
    RETURNS text AS $$
BEGIN
    IF geom_in IS NULL THEN
        RETURN NULL;
    END IF;
    RETURN  translate(substr(st_extent(geom_in)::text, 4), '() ', '[],');
END
$$ LANGUAGE 'plpgsql' IMMUTABLE;


--
-- Create IMMUTABLE unaccent function 
--
CREATE OR REPLACE FUNCTION public.f_unaccent(text)
RETURNS text AS $$
SELECT public.unaccent('public.unaccent', $1)  -- schema-qualify function and dictionary
$$  LANGUAGE sql IMMUTABLE;

--
-- An immutable concat that works with NULL values
-- (From https://blog.ropardo.ro/2010/05/04/extending-postgresql-a-better-concat-operator/)
--
CREATE OR REPLACE FUNCTION public.immutable_concat(text, text)
RETURNS text AS
    'SELECT
        CASE WHEN $1 IS NULL THEN $2
        WHEN $2 IS NULL THEN $1
        ELSE $1 || '' '' || $2 END;'
LANGUAGE sql IMMUTABLE;

--
-- Create function normalize
-- 
-- This function will return input text
-- in lower case, without accents and with space, and characaters ",:-`´‘’_" replaced by separator
--
CREATE OR REPLACE FUNCTION public.normalize(input text, separator text DEFAULT '') 
RETURNS text AS $$
BEGIN
    RETURN translate(lower(public.f_unaccent(input)), ' '',:-`´‘’_' , separator);
END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- Normalize array
-- 
-- This function return input text[] array in lower case, without accents and with space,
-- and characaters ",:-`´‘’_" replaced by empty string
--
--
CREATE OR REPLACE FUNCTION public.normalize_array(input text[], separator text DEFAULT '') 
RETURNS text[] AS $$
BEGIN
    RETURN array_agg(public.normalize(value)) FROM unnest(input) value;
END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- Create function count_estimate to speed up table count
-- (see https://wiki.postgresql.org/wiki/Count_estimate)
--
CREATE OR REPLACE FUNCTION public.count_estimate(query text)
RETURNS INTEGER AS $$
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
$$ LANGUAGE plpgsql;


--
-- Get UTM EPSG code from longitude/latitude
--
CREATE OR REPLACE FUNCTION public.get_utmzone(input_geom geometry) RETURNS integer AS $$
DECLARE
   zone int;
   pref int;
BEGIN
   IF GeometryType(input_geom) != 'POINT' THEN
     RAISE EXCEPTION 'Input geom must be a point. Currently is: %', GeometryType(input_geom);
   END IF;
   IF ST_Y(input_geom) > 0 THEN
      pref:=32600;
   ELSE
      pref:=32700;
   END IF;
   zone = floor((ST_X(input_geom)+180)/6)+1;
   RETURN zone+pref;
END
$$ LANGUAGE plpgsql IMMUTABLE;


--
-- New feature trigger notification
--
CREATE OR REPLACE FUNCTION public.add_feature() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    
    IF NEW.owner IS NOT NULL THEN
        PERFORM pg_notify('add_feature',
            json_build_object(
                'userid', NEW.owner::TEXT,
                'featureid', NEW.id::TEXT,
                'productidentifier', NEW.productidentifier,
                'status', NEW.status,
                'title', NEW.title
            )::text
        );
    END IF;
    
    RETURN NEW;
    
END;
$$;

--
-- Function to return time in utc
--
CREATE OR REPLACE FUNCTION public.now_utc()
RETURNS timestamp AS $$
SELECT now() AT TIME ZONE 'utc';
$$ LANGUAGE sql;


--
-- SYNOPSYS:
--   timestamp_to_id(ts)
--
-- DESCRIPTION:
--   Generate a 64 bits time sortable id
--   (Inspired by http://instagram-engineering.tumblr.com/post/10853187575/sharding-ids-at-instagram)
--
--   [IMPORTANT] Input timestamp MUST BE within the range [0260-01-01T00:00:00 BC, 4199-11-24T00:00:00]
--
-- USAGE:
--   SELECT timestamp_to_id(ts timestampz);
--

CREATE SEQUENCE IF NOT EXISTS public.resto_id_seq;
CREATE OR REPLACE FUNCTION public.timestamp_to_id(ts TIMESTAMP WITH TIME ZONE)
RETURNS BIGINT AS $$
DECLARE
    result bigint;
    now_millis bigint;
    seq_id bigint;
BEGIN
    SELECT FLOOR(EXTRACT(EPOCH FROM ts::TIMESTAMP) * 1000) INTO now_millis;
    result := now_millis << 17;
    seq_id := nextval('public.resto_id_seq');
    --result := result | ((shard_id % 64) << 10);
    --result := result | (seq_id % 1024);
    result := result | (seq_id % 131072);
    RETURN result;
END;
$$ LANGUAGE PLPGSQL;

--
-- SYNOPSYS:
--   timestamp_to_firstid(ts)
--
-- DESCRIPTION:
--   Generate a 64 bits time sortable id
--   (Inspired by http://instagram-engineering.tumblr.com/post/10853187575/sharding-ids-at-instagram)
--
-- USAGE:
--   SELECT timestamp_to_id(ts timestampz);
--
CREATE OR REPLACE FUNCTION public.timestamp_to_firstid(ts TIMESTAMP WITH TIME ZONE)
RETURNS BIGINT AS $$
DECLARE
    result bigint;
    now_millis bigint;
BEGIN
    SELECT FLOOR(EXTRACT(EPOCH FROM ts::TIMESTAMP) * 1000) INTO now_millis;
    result := now_millis << 17;
    RETURN result;
END;
$$ LANGUAGE PLPGSQL IMMUTABLE;

--
-- SYNOPSIS:
--   id_to_timestamp(restoid)
--
-- DESCRIPTION:
--   Return timestamp from restoid created with timestamp_to_id function
--
-- USAGE:
--   SELECT id_to_timestamp(restoid bigint);
--
CREATE OR REPLACE FUNCTION public.id_to_timestamp(restoid bigint)
RETURNS TIMESTAMP AS $$
BEGIN
    RETURN to_timestamp((restoid >> 17) / 1000.0);
END;
$$ LANGUAGE PLPGSQL IMMUTABLE;

CREATE OR REPLACE FUNCTION to_iso8601(ts timestamp)
RETURNS TEXT AS $$
BEGIN
    RETURN to_char(ts, 'YYYY-MM-DD"T"HH24:MI:SS.US"Z"');
END;
$$ LANGUAGE PLPGSQL IMMUTABLE;

--
-- 
-- SYNOPSYS:
--   increment_counters(counters JSON, value INTEGER, collection_id TEXT)
--
-- DESCRIPTION:
--   Increment JSON counters column
--     counters column:
--      {
--         "total": 12,
--         "collections":{
--              "S2":11,
--              "L8":1
--         }
--      }
--     value : 1,
--     collection_id: 'S2'
--
--  will return
--
--      {
--         "total": 13,
--         "collections":{
--              "S2":12,
--              "L8":1
--         }
--      }
--
-- USAGE:
--   SELECT increment_counters('{}', 1, 'S2');
--
CREATE OR REPLACE FUNCTION public.increment_counters(
    counters JSON,
    increment INTEGER,
    collection_id TEXT
)
RETURNS JSON AS $$
DECLARE
    -- Variable to store the total property from JSON
    total INTEGER;
    -- Variable to store individual keys and values from the collections array
    collection_key TEXT;
    collection_value INTEGER;
    -- Variable to store the updated collections array
    updated_collections JSONB;
    -- Variable to store the updated JSON object
    updated_counters JSONB;
    -- Boolean to check if collection_id exists
    collection_exists BOOLEAN := FALSE;
BEGIN
    -- Extract the total property from the JSON object
    total := (counters->>'total')::INTEGER;
    
    -- Increment the total by the value
    total := total + increment;

    -- Initialize the updated collections as the original collections
    updated_collections := counters->'collections';

    -- Loop through the collections array and update the collection_id value
    FOR collection_key, collection_value IN 
        SELECT collection_key, collection_value
        FROM json_each_text(counters->'collections')
    LOOP
        IF collection_key = collection_id THEN
            -- Increment the collection value by the input value
            collection_value := collection_value + increment;
            -- Update the collections JSONB with the new value
            updated_collections := jsonb_set(updated_collections, ARRAY[collection_key], to_jsonb(collection_value));
            collection_exists := TRUE;
        END IF;
    END LOOP;

    -- If the collection_id does not exist, add it with the input value
    IF NOT collection_exists THEN
        updated_collections := jsonb_set(updated_collections, ARRAY[collection_id], to_jsonb(increment));
    END IF;

    -- Update the JSON object with the new total and collections
    updated_counters := jsonb_set(counters::jsonb, '{total}', to_jsonb(total));
    updated_counters := jsonb_set(updated_counters, '{collections}', updated_collections);

    -- Example operation: Print the updated JSON object for debugging
    RAISE NOTICE 'Updated JSON: %', updated_counters::TEXT;

    -- Return the updated JSON object
    RETURN updated_counters::JSON;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION public.increment_counters(
    counters JSON,
    increment INTEGER,
    collection_id TEXT
)
RETURNS JSON AS $$
DECLARE
    -- Variable to store the total property from JSON
    total INTEGER;
    -- Variable to store individual keys and values from the collections array
    collection_key TEXT;
    collection_value INTEGER;
    -- Variable to store the updated collections array
    updated_collections JSONB;
    -- Variable to store the updated JSON object
    updated_counters JSONB;
    -- Boolean to check if collection_id exists
    collection_exists BOOLEAN := FALSE;
BEGIN
    -- Extract the total property from the JSON object
    total := (counters->>'total')::INTEGER;
    
    -- Increment the total by the value
    total := total + increment;

    -- Initialize the updated collections as the original collections
    updated_collections := counters->'collections';

    -- Check if collection_id is NULL
    IF collection_id IS NOT NULL THEN
        -- Loop through the collections array and update the collection_id value
        FOR collection_key, collection_value IN 
            SELECT key, value::INTEGER
            FROM json_each_text(counters->'collections')
        LOOP
            IF collection_key = collection_id THEN
                -- Increment the collection value by the input value
                collection_value := collection_value + increment;
                -- Update the collections JSONB with the new value
                updated_collections := jsonb_set(updated_collections, ARRAY[collection_key], to_jsonb(collection_value));
                collection_exists := TRUE;
            END IF;
        END LOOP;

        -- If the collection_id does not exist, add it with the input value
        IF NOT collection_exists THEN
            updated_collections := jsonb_set(updated_collections, ARRAY[collection_id], to_jsonb(increment));
        END IF;
    END IF;

    -- Update the JSON object with the new total and collections
    updated_counters := jsonb_set(counters::jsonb, '{total}', to_jsonb(total));
    updated_counters := jsonb_set(updated_counters, '{collections}', updated_collections);

    -- Return the updated JSON object
    RETURN updated_counters::JSON;
END;
$$ LANGUAGE plpgsql;
