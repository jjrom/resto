
-- -------------------- TRIGGERS --------------------------
--
-- Subdivide input geometry to geometry_part table
--
CREATE OR REPLACE FUNCTION __DATABASE_TARGET_SCHEMA__.store_geometry_part(id UUID, collection TEXT, geom GEOMETRY)
RETURNS VOID AS $$
DECLARE
    geo_type TEXT;
BEGIN

    -- Do nothing if geom is not set
    IF geom IS NULL THEN
        RETURN;
    END IF;

    -- First get geometry type
    geo_type := GeometryType(geom);
    
    -- Do not touch (MULTI)POINT
    IF geo_type = 'POINT' OR geo_type = 'MULTIPOINT' THEN
        INSERT INTO __DATABASE_TARGET_SCHEMA__.geometry_part (id, collection, part_num, geom) VALUES (id, collection, 1, geom);
    ELSE
        INSERT INTO __DATABASE_TARGET_SCHEMA__.geometry_part
            SELECT id, collection, ordinality AS part_num, sub AS geom
            FROM ST_SubDivide(geom, 32) WITH ordinality AS sub;
    END IF;

    RETURN;

END
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION __DATABASE_TARGET_SCHEMA__.trigger_store_geometry_part()
RETURNS TRIGGER AS $$
DECLARE
    geo_type TEXT;
BEGIN

    -- Do nothing if geom is not set
    IF NEW.geom IS NULL THEN
        RETURN NEW;
    END IF;

    -- First get geometry type
    geo_type := GeometryType(NEW.geom);
    
    -- Do not touch (MULTI)POINT
    IF geo_type = 'POINT' OR geo_type = 'MULTIPOINT' THEN
        INSERT INTO __DATABASE_TARGET_SCHEMA__.geometry_part (id, collection, part_num, geom) VALUES (NEW.id, NEW.collection, 1, NEW.geom);
    ELSE
        INSERT INTO __DATABASE_TARGET_SCHEMA__.geometry_part
            SELECT NEW.id, NEW.collection, ordinality AS part_num, sub AS geom
            FROM ST_SubDivide(NEW.geom, 32) WITH ordinality AS sub;
    END IF;

    RETURN NEW;

END
$$ LANGUAGE plpgsql;

-- 
-- On INSERT on __DATABASE_TARGET_SCHEMA__.feature THEN subdivide the input feature geometry and store it in geometry_part table
--
DROP TRIGGER IF EXISTS update_geometry_part ON __DATABASE_TARGET_SCHEMA__.feature;
CREATE TRIGGER update_geometry_part AFTER INSERT ON __DATABASE_TARGET_SCHEMA__.feature FOR EACH ROW EXECUTE PROCEDURE __DATABASE_TARGET_SCHEMA__.trigger_store_geometry_part();
