--
-- resto geometry_part split
--

-- 
-- On INSERT resto.feature THEN
--
--     1. Split resto.feature.geom into part
--     2. Store parts within resto.geometry_part
--
CREATE OR REPLACE FUNCTION resto.store_geometry_part()
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
        INSERT INTO resto.geometry_part (id, collection, part_num, geom) VALUES (NEW.id, NEW.collection, 1, NEW.geom);
    ELSE
        INSERT INTO resto.geometry_part
            SELECT NEW.id, NEW.collection, ordinality AS part_num, sub AS geom
            FROM ST_SubDivide(NEW.geom, 32) WITH ordinality AS sub;
    END IF;

    RETURN NEW;

END
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS update_geometry_part ON resto.feature;
CREATE TRIGGER update_geometry_part AFTER INSERT ON resto.feature FOR EACH ROW EXECUTE PROCEDURE resto.store_geometry_part();
