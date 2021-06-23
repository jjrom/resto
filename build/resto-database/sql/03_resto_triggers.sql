--
-- Subdivide input geometry to geometry_part table
--
CREATE OR REPLACE FUNCTION resto.store_geometry_part(id UUID, collection TEXT, geom GEOMETRY)
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
        INSERT INTO resto.geometry_part (id, collection, part_num, geom) VALUES (id, collection, 1, geom);
    ELSE
        INSERT INTO resto.geometry_part
            SELECT id, collection, ordinality AS part_num, sub AS geom
            FROM ST_SubDivide(geom, 32) WITH ordinality AS sub;
    END IF;

    RETURN;

END
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION resto.trigger_store_geometry_part()
RETURNS TRIGGER AS $$
BEGIN
    RETURN resto.store_geometry_part(NEW.id, NEW.collection, NEW.geom);
END
$$ LANGUAGE plpgsql;

-- 
-- On INSERT on resto.feature THEN subdivide the input feature geometry and store it in geometry_part table
--
DROP TRIGGER IF EXISTS update_geometry_part ON resto.feature;
CREATE TRIGGER update_geometry_part AFTER INSERT ON resto.feature FOR EACH ROW EXECUTE PROCEDURE resto.trigger_store_geometry_part();
