--
-- resto triggers
--

-- 
-- On INSERT resto.feature THEN
--
--     1. Split resto.feature._geometry into part
--     2. Store parts within resto.geometry_part
--
CREATE OR REPLACE FUNCTION resto.store_geometry_part()
RETURNS TRIGGER AS $$
DECLARE
    geo_type TEXT;
BEGIN

    -- First get geometry type
    geo_type := GeometryType(NEW._geometry);
    
    -- Do not touch (MULTI)POINT
    IF geo_type = 'POINT' OR geo_type = 'MULTIPOINT' THEN
        INSERT INTO resto.geometry_part (id, collection, part_num, geom) VALUES (NEW.id, NEW.collection, 1, NEW._geometry);
    ELSE
        INSERT INTO resto.geometry_part
            SELECT NEW.id, NEW.collection, ordinality AS part_num, sub AS geom
            FROM ST_SubDivide(NEW._geometry, 32) WITH ordinality AS sub;
    END IF;

    RETURN NEW;

END
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_geometry_part AFTER INSERT ON resto.feature FOR EACH ROW EXECUTE PROCEDURE resto.store_geometry_part();
