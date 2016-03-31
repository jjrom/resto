-- 
-- Migration script from resto v2.1 to resto v2.2
--
-- 

--
-- Copyright (C) 2016 Jerome Gasperi <jerome.gasperi@gmail.com>
-- With priceless contribution from Nicolas Ribot <nicky666@gmail.com>
-- 
-- This work is placed into the public domain.
--
-- SYNOPSYS:
--   ST_SplitDateLine(polygon)
--
-- DESCRIPTION:
--
--   This function split the input polygon geometry against the -180/180 date line
--   Returns the original geometry otherwise
--
--   WARNING ! Only work for SRID 4326
--
-- USAGE:
--
CREATE OR REPLACE FUNCTION ST_SplitDateLine(geom_in geometry)
RETURNS geometry AS $$
DECLARE
	geom_out geometry;
	blade geometry;
BEGIN

        blade := ST_SetSrid(ST_MakeLine(ST_MakePoint(180, -90), ST_MakePoint(180, 90)), 4326);

	-- Delta longitude is greater than 180 then return splitted geometry
	IF ST_XMin(geom_in) < -90 AND ST_XMax(geom_in) > 90 THEN

            -- Add 360 to all negative longitudes
            WITH tmp0 AS (
                SELECT geom_in AS geom
            ), tmp AS (
                SELECT st_dumppoints(geom) AS dmp FROM tmp0
            ), tmp1 AS (
                SELECT (dmp).path,
                CASE WHEN st_X((dmp).geom) < 0 THEN st_setSRID(st_MakePoint(st_X((dmp).geom) + 360, st_Y((dmp).geom)), 4326) 
                ELSE (dmp).geom END AS geom
                FROM tmp
                ORDER BY (dmp).path[2]
            ), tmp2 AS (
                SELECT st_dump(st_split(st_makePolygon(st_makeline(geom)), blade)) AS d
                FROM tmp1
            )
            SELECT ST_Union(
                (
                    CASE WHEN ST_Xmax((d).geom) > 180 THEN ST_Translate((d).geom, -360, 0, 0)
                    ELSE (d).geom END
                )
            )
            INTO geom_out
            FROM tmp2;
            
        -- Delta longitude < 180 degrees then return untouched input geometry
        ELSE
            RETURN geom_in;
	END IF;

	RETURN geom_out;
END
$$ LANGUAGE 'plpgsql';

-- Add indexed geometry column
SELECT AddGeometryColumn('resto', 'features', '_geometry', '4326', 'GEOMETRY', 2);
-- Could be quite long !!
UPDATE resto.features SET _geometry=ST_SplitDateLine(geometry);

DROP INDEX _s1._features_geometry_idx;
DROP INDEX _s2._features_geometry_idx;
DROP INDEX _spot._features_geometry_idx;
DROP INDEX _pleiades._features_geometry_idx;
DROP INDEX _landsat._features_geometry_idx;
CREATE INDEX _features__geometry_idx ON _s1.features USING gist(_geometry);
CREATE INDEX _features__geometry_idx ON _s2.features USING gist(_geometry);
CREATE INDEX _features__geometry_idx ON _spot.features USING gist(_geometry);
CREATE INDEX _features__geometry_idx ON _pleiades.features USING gist(_geometry);
CREATE INDEX _features__geometry_idx ON _landsat.features USING gist(_geometry);


