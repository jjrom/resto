--
-- resto extensions and functions
--

--------------------------------  EXTENSION -----------------------------------------------

--
-- Unaccent extension to support text normalization
--
CREATE EXTENSION IF NOT EXISTS unaccent SCHEMA public;

--
-- UUID extension
--
CREATE EXTENSION IF NOT EXISTS "uuid-ossp" SCHEMA public;

--
-- Trigram extension to support full text search
--
CREATE EXTENSION IF NOT EXISTS pg_trgm SCHEMA public;

-- 
-- PostGIS extension to support geometrical searches
--
CREATE EXTENSION IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS postgis_topology;


--------------------------------  FUNCTIONS -----------------------------------------------

--
-- SYNOPSYS:
--   ST_SplitDateLine(polygon, radius)
--
-- DESCRIPTION:
--
--   [WARNING] This is a dummy replacement that does nothing
--
--   Use [TAMN] plugin to get the right function (this is not OpenSource)
--
-- USAGE:
--   SELECT ST_SplitDateLine(geom_in geometry, radius integer DEFAULT 10000);
--
CREATE OR REPLACE FUNCTION ST_SplitDateLine(geom_in geometry, radius integer default 10000)
    RETURNS geometry AS $$
BEGIN
    RETURN geom_in;
END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

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
CREATE OR REPLACE FUNCTION immutable_concat(text, text)
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
CREATE OR REPLACE FUNCTION normalize(input text, separator text DEFAULT '') 
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
CREATE OR REPLACE FUNCTION normalize_array(input text[], separator text DEFAULT '') 
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
$$ LANGUAGE PLPGSQL;

CREATE OR REPLACE FUNCTION to_iso8601(ts timestamp)
RETURNS TEXT AS $$
BEGIN
    RETURN to_char(ts, 'YYYY-MM-DD"T"HH24:MI:SS.US"Z"');
END;
$$ LANGUAGE PLPGSQL;
