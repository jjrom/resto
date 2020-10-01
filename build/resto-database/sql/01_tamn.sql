
--
-- TAMN - The AntiMeridian Nightmare
-- https:://github.com/jjrom/tamn
--
-- Copyright (C) 2018 Jerome Gasperi <jerome.gasperi@gmail.com>
-- 
-- TAMN provides a set of PostGIS based PSQL functions to deal with antimeridian crossing geometries.
-- 

--
-- Insert North_Pole_Azimuthal_Equidistant and South_Pole_Azimuthal_Equidistant special projections
--
INSERT INTO spatial_ref_sys (srid, auth_name, auth_srid, proj4text, srtext) VALUES ( 102016, 'ESRI', 102016, '+proj=aeqd +lat_0=90 +lon_0=0 +x_0=0 +y_0=0 +datum=WGS84 +units=m +no_defs ', 'PROJCS["North_Pole_Azimuthal_Equidistant",GEOGCS["GCS_WGS_1984",DATUM["WGS_1984",SPHEROID["WGS_1984",6378137,298.257223563]],PRIMEM["Greenwich",0],UNIT["Degree",0.017453292519943295]],PROJECTION["Azimuthal_Equidistant"],PARAMETER["False_Easting",0],PARAMETER["False_Northing",0],PARAMETER["Central_Meridian",0],PARAMETER["Latitude_Of_Origin",90],UNIT["Meter",1],AUTHORITY["EPSG","102016"]]') ON CONFLICT DO NOTHING;
INSERT INTO spatial_ref_sys (srid, auth_name, auth_srid, proj4text, srtext) VALUES ( 102019, 'ESRI', 102019, '+proj=aeqd +lat_0=-90 +lon_0=0 +x_0=0 +y_0=0 +datum=WGS84 +units=m +no_defs ', 'PROJCS["South_Pole_Azimuthal_Equidistant",GEOGCS["GCS_WGS_1984",DATUM["WGS_1984",SPHEROID["WGS_1984",6378137,298.257223563]],PRIMEM["Greenwich",0],UNIT["Degree",0.017453292519943295]],PROJECTION["Azimuthal_Equidistant"],PARAMETER["False_Easting",0],PARAMETER["False_Northing",0],PARAMETER["Central_Meridian",0],PARAMETER["Latitude_Of_Origin",-90],UNIT["Meter",1],AUTHORITY["EPSG","102019"]]') ON CONFLICT DO NOTHING;

--
-- Copyright (C) 2018 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_DistanceToNorthPole(geom_in)
--
-- DESCRIPTION:
--   Returns distance in meters to South Pole - if error occurs returns -1
--
-- USAGE:
--   SELECT ST_DistanceToNorthPole(geom_in geometry);
--
CREATE OR REPLACE FUNCTION ST_DistanceToNorthPole(geom_in geometry)
    RETURNS numeric AS $$
DECLARE
    north_pole  geography := ST_SetSrid(ST_MakePoint(0, 90), 4326)::geography;
    text_var1   text := '';
    text_var2   text := '';
    text_var3   text := '';
BEGIN

    -- [IMPORTANT] Computation is done in geographical coordinates
    RETURN ST_Distance(geom_in::geography, north_pole, true); 

    -- If any of above failed, revert to use original polygon
    -- This prevents ingestion error, but may potentially lead to incorrect spatial query.
    EXCEPTION WHEN OTHERS THEN
        GET STACKED DIAGNOSTICS text_var1 = MESSAGE_TEXT,
                                text_var2 = PG_EXCEPTION_DETAIL,
                                text_var3 = PG_EXCEPTION_HINT;
        raise WARNING 'ST_DistanceToNorthPole: exception occured: Msg: %, detail: %, hint: %', text_var1, text_var1, text_var3;
        RETURN -1;

END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- Copyright (C) 2018 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_DistanceToSouthPole(geom_in)
--
-- DESCRIPTION:
--   Returns distance in meters to South Pole - if error occurs returns -1
--
-- USAGE:
--   SELECT ST_DistanceToSouthPole(geom_in geometry);
--
CREATE OR REPLACE FUNCTION ST_DistanceToSouthPole(geom_in geometry)
    RETURNS numeric AS $$
DECLARE
    south_pole  geography := ST_SetSrid(ST_MakePoint(0, -90), 4326)::geography;
    text_var1   text := '';
    text_var2   text := '';
    text_var3   text := '';
BEGIN

    -- [IMPORTANT] Computation is done in geographical coordinates
    RETURN ST_Distance(geom_in::geography, south_pole, true); 

    -- If any of above failed, revert to use original polygon
    -- This prevents ingestion error, but may potentially lead to incorrect spatial query.
    EXCEPTION WHEN OTHERS THEN
        GET STACKED DIAGNOSTICS text_var1 = MESSAGE_TEXT,
                                text_var2 = PG_EXCEPTION_DETAIL,
                                text_var3 = PG_EXCEPTION_HINT;
        raise WARNING 'ST_DistanceToSouthPole: exception occured: Msg: %, detail: %, hint: %', text_var1, text_var1, text_var3;
        RETURN -1;

END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- Copyright (C) 2019 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_IntersectsAntimeridian(polygon)
--
-- DESCRIPTION:
--   This function returns TRUE if input geometry intersects the antimeridian, FALSE otherwise
--
-- USAGE:
--   SELECT ST_IntersectsAntimeridian(geom_in geometry);
--
CREATE OR REPLACE FUNCTION ST_IntersectsAntimeridian(geom_in geometry)
    RETURNS INTEGER AS $$
DECLARE
    part        RECORD;
    blade       geography := ST_SetSrid(ST_MakeLine(ARRAY[ST_MakePoint(180, -90), ST_MakePoint(180, 0), ST_MakePoint(180, 90)]), 4326)::geography;
    text_var1   text := '';
    text_var2   text := '';
    text_var3   text := '';
BEGIN
    
     -- Detect MultiPolygon
    IF GeometryType(geom_in) = 'MULTIPOLYGON' THEN

        FOR part IN SELECT (ST_Dump(geom_in)).geom LOOP

            -- AntiMeridian is crossed for sure
            IF ST_IsPolygonCW(part.geom) AND abs(ST_XMax(part.geom) - ST_XMin(part.geom)) > 360 THEN
                --RAISE NOTICE 'Polygon is CW and abs > 360';
                RETURN 1;
            -- AntiMeridian is perhaps crossed
            ELSIF ST_Intersects(part.geom::geography, blade) THEN
                --RAISE NOTICE 'Polygon intersects blade';
                RETURN 1;
            END IF;

        END LOOP;

    ELSE

        -- AntiMeridian is crossed
        IF ST_IsPolygonCW(geom_in) AND abs(ST_XMax(geom_in) - ST_XMin(geom_in)) > 360 THEN
            --RAISE NOTICE 'Polygon is CW and abs > 360';
            RETURN 1;
        ELSIF ST_Intersects(geom_in::geography, blade) THEN
            --RAISE NOTICE 'Polygon intersects blade';
            RETURN 1;
        END IF;

    END IF;
    
    RETURN 0; 

    -- If any of above failed, revert to use original polygon
    -- This prevents ingestion error, but may potentially lead to incorrect spatial query.
    EXCEPTION WHEN OTHERS THEN
        GET STACKED DIAGNOSTICS text_var1 = MESSAGE_TEXT,
                                text_var2 = PG_EXCEPTION_DETAIL,
                                text_var3 = PG_EXCEPTION_HINT;
        raise WARNING 'ST_IntersectsAntimeridian: exception occured: Msg: %, detail: %, hint: %', text_var1, text_var1, text_var3;
        RETURN -1;

END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- Copyright (C) 2018 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_IntersectsNorthPole(polygon, distance)
--
-- DESCRIPTION:
--   Returns TRUE if input geometry intersects the North Pole, FALSE otherwise
--   Intersection is based on ST_Distance computation
--
-- USAGE:
--   SELECT ST_IntersectsNorthPole(geom_in geometry, distance integer default 0);
--
CREATE OR REPLACE FUNCTION ST_IntersectsNorthPole(geom_in geometry, distance integer default 0)
    RETURNS boolean AS $$
DECLARE
    north_pole  geography := ST_SetSrid(ST_MakePoint(0, 90), 4326)::geography;
    text_var1   text := '';
    text_var2   text := '';
    text_var3   text := '';
BEGIN

    -- [IMPORTANT] Computation is done in geographical coordinates
    IF ST_Distance(geom_in::geography, north_pole, true) <= distance THEN
        RETURN TRUE;
    END IF;    
    
    RETURN FALSE; 

    -- If any of above failed, revert to use original polygon
    -- This prevents ingestion error, but may potentially lead to incorrect spatial query.
    EXCEPTION WHEN OTHERS THEN
        GET STACKED DIAGNOSTICS text_var1 = MESSAGE_TEXT,
                                text_var2 = PG_EXCEPTION_DETAIL,
                                text_var3 = PG_EXCEPTION_HINT;
        raise WARNING 'ST_IntersectsNorthPole: exception occured: Msg: %, detail: %, hint: %', text_var1, text_var1, text_var3;
        RETURN FALSE;

END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- Copyright (C) 2018 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_IntersectsSouthPole(polygon, distance)
--
-- DESCRIPTION:
--   Returns TRUE if input geometry intersects the South Pole, FALSE otherwise
--   Intersection is based on ST_Distance computation
--
-- USAGE:
--   SELECT ST_IntersectsSouthPole(geom_in geometry, distance integer default 0);
--
CREATE OR REPLACE FUNCTION ST_IntersectsSouthPole(geom_in geometry, distance integer default 0)
    RETURNS boolean AS $$
DECLARE
    south_pole  geography := ST_SetSrid(ST_MakePoint(0, -90), 4326)::geography;
    text_var1   text := '';
    text_var2   text := '';
    text_var3   text := '';
BEGIN

    -- [IMPORTANT] Computation is done in geographical coordinates
    IF ST_Distance(geom_in::geography, south_pole, true) <= distance THEN
        RETURN TRUE;
    END IF;    
    
    RETURN FALSE; 

    -- If any of above failed, revert to use original polygon
    -- This prevents ingestion error, but may potentially lead to incorrect spatial query.
    EXCEPTION WHEN OTHERS THEN
        GET STACKED DIAGNOSTICS text_var1 = MESSAGE_TEXT,
                                text_var2 = PG_EXCEPTION_DETAIL,
                                text_var3 = PG_EXCEPTION_HINT;
        raise WARNING 'ST_IntersectsSouthPole: exception occured: Msg: %, detail: %, hint: %', text_var1, text_var1, text_var3;
        RETURN FALSE;

END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- Copyright (C) 2018 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_SplitSouthPole(polygon, radius)
--
-- DESCRIPTION:
--   Splits input geometry that crosses the South Pole. If radius is specified, then remove a circle of radius meters around the South Pole from input geometry
--
--   [WARNING] Only work for EPSG:4326 geometry that CROSS the South Pole with latitude length STRICTLY LOWER than 90 degrees
--
-- USAGE:
--   SELECT ST_SplitSouthPole(geom_in geometry);
--   or
--   SELECT ST_SplitSouthPole(geom_in geometry, radius integer);
--
--
CREATE OR REPLACE FUNCTION ST_SplitSouthPole(geom_in geometry, radius integer default NULL)
    RETURNS geometry AS $$
DECLARE
    pole_split          geometry;
    pole_geom           geometry;
    pole_blade          geometry;
    epsg_code           integer;
    pole_distance       integer := 500000;
    distance_to_north   numeric;
    force_epsg3031      boolean := FALSE;
    text_var1           text := '';
    text_var2           text := '';
    text_var3           text := '';
BEGIN

    -- Convert geometry to WGS 84 / Arctic Polar Stereographic
    if force_epsg3031 IS TRUE OR ST_Ymax(geom_in) < -60 THEN
        --RAISE NOTICE 'Using EPSG:3031 - latitudes are lower than 60 degrees';
        epsg_code := 3031;
        -- (0, -89)deg -> (0, -90)deg -> (-180, 89.9)deg
        pole_blade := ST_SetSrid(ST_MakeLine(ARRAY[ST_MakePoint(0, 108655.09), ST_MakePoint(0, 0), ST_MakePoint(0, -14077221718.36)]), epsg_code);
        
    -- Convert geometry to North Pole Azimuthal Equidistant
    ELSE
        --RAISE NOTICE 'Using EPSG:102019';
        epsg_code := 102019;
        -- (0, 90)deg -> (0, -90)deg -> (180, 90)deg
        pole_blade := ST_SetSrid(ST_MakeLine(ARRAY[ST_MakePoint(0, 20003931.45862726), ST_MakePoint(0, 0), ST_MakePoint(0, -20003931.45862726)]), epsg_code);
    END IF;

    --
    -- Always apply a Buffer(0) to make output geometry valid
    --
    -- [NOTE] Densify over pole to avoid issue in ST_Difference
    distance_to_north := ST_DistanceTonorthPole(geom_in);
    IF  distance_to_north > -1 AND distance_to_north <= pole_distance THEN
        pole_geom := ST_Buffer(ST_Transform(ST_Segmentize(geom_in::geography, 50000)::geometry, epsg_code), 0);
    ELSE
        pole_geom := ST_Buffer(ST_Transform(geom_in, epsg_code), 0);
    END IF;
    
    -- Convert polar geometry to epsg_code and optionaly remove a radius hole centered on North Pole.
    IF radius IS NOT NULL THEN
        pole_split:= ST_Difference(pole_geom, ST_Buffer(ST_SetSrid(ST_MakePoint(0, 0), epsg_code), radius));
    ELSE
        pole_split := pole_geom;
    END IF;
    
    -- Split polygon to avoid -180/180 crossing issue.
    -- Note: applying negative buffer ensure valid multipolygons that don't share a common edge
    IF distance_to_north > -1 AND distance_to_north <= pole_distance THEN
        RETURN ST_SimplifyPreserveTopology(ST_Buffer(ST_Transform(ST_Buffer(ST_Split(pole_split, pole_blade), -1), 4326), 0), 0.01);
    ELSE
        RETURN ST_Buffer(ST_Transform(ST_Buffer(ST_Split(pole_split, pole_blade), -1), 4326), 0);
    END IF;

    -- If any of above failed, revert to use original polygon
    -- This prevents ingestion error, but may potentially lead to incorrect spatial query.
    EXCEPTION WHEN OTHERS THEN
        GET STACKED DIAGNOSTICS text_var1 = MESSAGE_TEXT,
                                text_var2 = PG_EXCEPTION_DETAIL,
                                text_var3 = PG_EXCEPTION_HINT;
        raise WARNING 'ST_SplitSouthPole: exception occured: Msg: %, detail: %, hint: %', text_var1, text_var1, text_var3;
        RETURN geom_in;

END
$$ LANGUAGE 'plpgsql' IMMUTABLE;


--
-- Copyright (C) 2018 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_SplitNorthPole(polygon, radius integer default NULL)
--
-- DESCRIPTION:
--   Splits input geometry that crosses the North Pole. If radius is specified, then remove a circle of radius meters around the North Pole from input geometry
--
--
--   [WARNING] Only work for EPSG:4326 geometry that CROSS the North Pole with latitude length STRICTLY LOWER than 90 degrees
--
-- USAGE:
--   SELECT ST_SplitNorthPole(geom_in geometry);
--   or
--   SELECT ST_SplitNorthPole(geom_in geometry, radius integer);
--
CREATE OR REPLACE FUNCTION ST_SplitNorthPole(geom_in geometry, radius integer default NULL)
    RETURNS geometry AS $$
DECLARE
    pole_split          geometry;
    pole_geom           geometry;
    pole_blade          geometry;
    pole_distance       integer := 500000;
    distance_to_south   numeric;
    epsg_code           integer;
    force_epsg3413      boolean := FALSE;
    text_var1           text := '';
    text_var2           text := '';
    text_var3           text := '';
BEGIN
    
    -- Latitudes are above 60 degrees => input geometry is converted to WGS 84 / Antarctic Polar Stereographic
    IF force_epsg3413 IS TRUE OR ST_Ymin(geom_in) > 60 THEN
        --RAISE NOTICE 'Using EPSG:3413 - latitudes are greater than 60 degrees';
        epsg_code := 3413;
        -- (0, 90)deg -> (-180, -89.9)deg
        --pole_blade := ST_SetSrid(ST_MakeLine(ARRAY[ST_MakePoint(0, 0), ST_MakePoint(-9924313227.23, 9924313227.23)]), epsg_code);
        pole_blade := ST_SetSrid(ST_MakeLine(ARRAY[ST_MakePoint(-2353926.81, 2345724.36), ST_MakePoint(0, 0), ST_MakePoint(0, 0), ST_MakePoint(-2349829.16, 2349829.16)]), epsg_code);
        
    -- Latitudes are below 60 degrees =>  input geometry is converted to North Pole Azimuthal Equidistant
    ELSE
        --RAISE NOTICE 'Using EPSG:102016';
        epsg_code := 102016;
        -- (-180, 90)deg -> (0, 90)deg -> (-180, -89.9)deg
        --pole_blade := ST_SetSrid(ST_MakeLine(ARRAY[ST_MakePoint(0, -19892237.59371344), ST_MakePoint(0, 0), ST_MakePoint(0, 19892237.59371344)]), epsg_code);
        pole_blade := ST_SetSrid(ST_MakeLine(ARRAY[ST_MakePoint(2.4497750631170925E-9, 2.0003931458627265E7), ST_MakePoint(1.2248875315585463E-9, 1.0001965729313632E7), ST_MakePoint(0, 0), ST_MakePoint(0, 0), ST_MakePoint(0, -10001965.72931363), ST_MakePoint(0, -20003931.45862726)]), epsg_code);
    END IF;

    --
    -- Always apply a Buffer(0) to make output geometry valid
    --
    -- [NOTE] Densify over pole to avoid issue in ST_Difference
    --
    distance_to_south := ST_DistanceToSouthPole(geom_in);
    IF  distance_to_south > -1 AND distance_to_south <= pole_distance THEN
        pole_geom := ST_Buffer(ST_Transform(ST_Segmentize(geom_in::geography, 50000)::geometry, epsg_code), 0);
    ELSE
        pole_geom := ST_Buffer(ST_Transform(geom_in, epsg_code), 0);
    END IF;
    
    -- Convert polar geometry to epsg_code and optionaly remove a radius hole centered on North Pole.
    IF radius IS NOT NULL THEN
        pole_split:= ST_Difference(pole_geom, ST_Buffer(ST_SetSrid(ST_MakePoint(0, 0), epsg_code), radius));
    ELSE
        pole_split := pole_geom;
    END IF;
    
    -- Split polygon to avoid -180/180 crossing issue.
    -- Note: applying negative buffer ensure valid multipolygons that don't share a common edge
    IF distance_to_south > -1 AND distance_to_south <= pole_distance THEN
        RETURN ST_SimplifyPreserveTopology(ST_Buffer(ST_Transform(ST_Buffer(ST_Split(pole_split, pole_blade), -1), 4326), 0), 0.01);
    ELSE
        RETURN ST_Buffer(ST_Transform(ST_Buffer(ST_Split(pole_split, pole_blade), -1), 4326), 0);
    END IF;

    -- If any of above failed, revert to use original polygon
    -- This prevents ingestion error, but may potentially lead to incorrect spatial query.
    EXCEPTION WHEN OTHERS THEN
        GET STACKED DIAGNOSTICS text_var1 = MESSAGE_TEXT,
                                text_var2 = PG_EXCEPTION_DETAIL,
                                text_var3 = PG_EXCEPTION_HINT;
        raise WARNING 'ST_SplitNorthPole: exception occured: Msg: %, detail: %, hint: %', text_var1, text_var1, text_var3;
        RETURN geom_in;

END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- Copyright (C) 2019 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_SplitAntimeridian(polygon)
--
-- DESCRIPTION:
--   Splits the input polygon geometry against the -180/180 date line
--
--   [WARNING] Only work for EPSG:4326 geometry that DO NOT CROSS the North Pole or the South Pole
--
-- USAGE:
--   SELECT ST_SplitAntimeridian(geom_in geometry);
--
CREATE OR REPLACE FUNCTION ST_SplitAntimeridian(geom_in geometry)
    RETURNS geometry AS $$
DECLARE
    geom_out    geometry;
    is_valid    boolean;
    text_var1   text := '';
    text_var2   text := '';
    text_var3   text := '';
BEGIN

    -- Shift polygon to 0-360 and cut at 180
    geom_out := ST_Buffer(ST_WrapX(ST_ShiftLongitude(geom_in), 180, -360), 0);    

    -- See case test id=S2A_OPER_PRD_MSIL1C_PDMC_20160720T163945_R116_V20160714T235631_20160714T235631
    -- If output geometry still crosses antimeridian - split it again
    -- This case arises if input geometry longitude is outside -180/180 bounds
    IF ST_IntersectsAntimeridian(geom_out) = 1 THEN
        geom_out := ST_Buffer(ST_WrapX(ST_ShiftLongitude(geom_out), 180, -360), 0);     
    END IF;

    RETURN geom_out;

    -- If any of above failed, revert to use original polygon
    -- This prevents ingestion error, but may potentially lead to incorrect spatial query.
    EXCEPTION WHEN OTHERS THEN
        GET STACKED DIAGNOSTICS text_var1 = MESSAGE_TEXT,
                                text_var2 = PG_EXCEPTION_DETAIL,
                                text_var3 = PG_EXCEPTION_HINT;
        raise WARNING 'ST_SplitAntimeridian: exception occured: Msg: %, detail: %, hint: %', text_var1, text_var1, text_var3;
        RETURN geom_in;

END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- Copyright (C) 2019 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_CorrectLatitude(geometry)
--
-- DESCRIPTION:
--   Constraint input polygon latitude between -90 and 90 degrees
--
-- USAGE:
--   SELECT ST_CorrectLatitude(geom_in geometry);
--
CREATE OR REPLACE FUNCTION ST_CorrectLatitude(geom_in geometry)
RETURNS geometry AS $$
DECLARE
	geom_out geometry;
BEGIN

    -- Delta longitude is greater than 180 then return splitted geometry
	IF (ST_YMin(geom_in) < -90 OR ST_YMax(geom_in) > 90) THEN

        WITH
            tmp0 AS (
                SELECT geom_in AS geom
            ),
            tmp1 AS (
                SELECT ST_DumpPoints(geom) AS d FROM tmp0
            ),
            tmp2 AS (
                SELECT (d).path[1] AS objid, (d).path[2] AS ringid, (d).path[3] AS ptid,
                    CASE
                        WHEN ST_Y((d).geom) < -90 THEN ST_setSRID(ST_MakePoint(ST_X((d).geom), abs(90 + ST_Y((d).geom)) - 90), 4326)
                        WHEN ST_Y((d).geom) > 90 THEN ST_setSRID(ST_MakePoint(ST_X((d).geom), 90 - abs(ST_Y((d).geom)) + 90), 4326)
                    ELSE (d).geom
                END AS geom FROM tmp1
            ),
            tmp3 AS (
                SELECT objid, ringid, st_makeLine(geom ORDER BY ptid) AS geom_lines FROM tmp2
                GROUP BY objid, ringid
            ),
            tmp4 AS (
                SELECT objid, array_agg(geom_lines ORDER BY ringid) as arr FROM tmp3
                GROUP BY objid
            ),
            tmp5 AS (
                SELECT objid, st_makePolygon(arr[1], array_remove(arr, arr[1])) AS geom FROM tmp4
            ),
            tmp6 AS (
                SELECT ST_Collect(geom ORDER BY objid) AS gout FROM tmp5
            )
            SELECT gout AS geom_out FROM tmp6;

        RETURN geom_out;

    END IF;
    
    RETURN geom_in;
	
END
$$ LANGUAGE 'plpgsql' IMMUTABLE;

--
-- Copyright (C) 2018 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_SplitDateLine(polygon, radius)
--
-- DESCRIPTION:
--   Splits input geometry if it intersects the antimeridian longitude (i.e. -180/180) 
--   It supports poles crossing (i.e. geometry that crosses the North Pole or the South Pole)
--   
--   Non crossing input geometry are returned without modification
--
--   [WARNING] Input geometry that crosses one of the pole MUST have a length in latitude strictly lower than 90 degrees
--
-- USAGE:
--   SELECT ST_SplitDateLine(geom_in geometry, radius integer DEFAULT 50000);
--
CREATE OR REPLACE FUNCTION ST_SplitDateLine(geom_in geometry, radius integer default 50000)
    RETURNS geometry AS $$
DECLARE
    pole_distance   integer := 500000;
    np_distance     numeric;
    sp_distance     numeric;
    geo_type        text;
    text_var1       text := '';
    text_var2       text := '';
    text_var3       text := '';
BEGIN

    -- Geometry must be a (Multi)Polygon
    geo_type := GeometryType(geom_in);
    
    IF geo_type <> 'POLYGON' AND geo_type <> 'MULTIPOLYGON' THEN

        RAISE NOTICE 'ST_SplitDateLine: input geometry must be a POLYGON or a MULTIPOLYGON';
        RETURN geom_in;

    END IF;

    -- Compute distances to North and South poles
    np_distance := ST_DistanceToNorthPole(geom_in);
    sp_distance := ST_DistanceToSouthPole(geom_in);

    -- Input geometry crosses North Pole
    IF np_distance > -1 AND np_distance <= pole_distance THEN

        -- Input geometry is even closer to South Pole
        IF sp_distance > -1 AND sp_distance < np_distance THEN
            RETURN ST_SplitSouthPole(geom_in, radius);
        ELSE
            RETURN ST_SplitNorthPole(geom_in, radius);
        END IF;

    END IF;

    -- Input geometry crosses South Pole
    IF sp_distance > -1 AND sp_distance <= pole_distance THEN

         -- Input geometry is even closer to North Pole
        IF np_distance > -1 AND np_distance < sp_distance THEN
            RETURN ST_SplitNorthPole(geom_in, radius);
        ELSE
            RETURN ST_SplitSouthPole(geom_in, radius);
        END IF;
        
    END IF;

    -- Input geometry crosses -180/180 but not the poles
    IF ST_IntersectsAntimeridian(geom_in) = 1 THEN
        RETURN ST_SplitAntimeridian(geom_in);
    END IF;

    -- Non crossing geometry is not modified unless invalid
    IF ST_isValid(geom_in, 0) IS FALSE THEN
        RETURN ST_Buffer(ST_MakeValid(geom_in), 0);
    END IF;
    
    RETURN geom_in;
    
    -- If any of above failed, revert to use original polygon
    -- This prevents ingestion error, but may potentially lead to incorrect spatial query.
    EXCEPTION WHEN OTHERS THEN
        GET STACKED DIAGNOSTICS text_var1 = MESSAGE_TEXT,
                                text_var2 = PG_EXCEPTION_DETAIL,
                                text_var3 = PG_EXCEPTION_HINT;
        raise WARNING 'ST_SplitDateLine: exception occured: Msg: %, detail: %, hint: %', text_var1, text_var1, text_var3;
        RETURN geom_in;

END
$$ LANGUAGE 'plpgsql' IMMUTABLE;
