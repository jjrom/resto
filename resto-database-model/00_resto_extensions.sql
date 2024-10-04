--
-- resto extensions
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
-- LTREE extension to support for path search
--
CREATE EXTENSION IF NOT EXISTS ltree;

-- 
-- PostGIS extension to support geometrical searches
--
CREATE EXTENSION IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS postgis_topology;
