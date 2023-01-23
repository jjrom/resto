

-- Clean old functions
DROP FUNCTION IF EXISTS public.ST_DistanceToNorthPole(geometry);
DROP FUNCTION IF EXISTS public.ST_DistanceToSouthPole(geometry);

DROP FUNCTION IF EXISTS public.ST_SplitNorthPole(geometry, integer);
DROP FUNCTION IF EXISTS public.ST_SplitNorthPole(geometry, integer, integer);
DROP FUNCTION IF EXISTS public.ST_SplitNorthPole(geometry, integer, integer, integer);

DROP FUNCTION IF EXISTS public.ST_SplitSouthPole(geometry, integer);
DROP FUNCTION IF EXISTS public.ST_SplitSouthPole(geometry, integer, integer);
DROP FUNCTION IF EXISTS public.ST_SplitSouthPole(geometry, integer, integer, integer);

DROP FUNCTION IF EXISTS public.ST_SplitAntimeridian(geometry);
DROP FUNCTION IF EXISTS public.ST_IntersectsAntimeridian(geometry);

DROP FUNCTION IF EXISTS public.ST_SplitDateLine(geometry, integer);
DROP FUNCTION IF EXISTS public.ST_SplitDateLine(geometry, integer, integer);

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

    -- If any of above failed, raise error and return -1
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
--   ST_SplitSouthPole(polygon, radius, pole_distance, trans_buffer)
--
-- DESCRIPTION:
--   Splits input geometry that crosses the South Pole. If radius is specified, then remove a circle of radius meters around the South Pole from input geometry
--
--   [WARNING] Only work for EPSG:4326 geometry that CROSS the South Pole with latitude length STRICTLY LOWER than 90 degrees
--
-- USAGE:
--   SELECT ST_SplitSouthPole(geom_in geometry);
--   or
--   SELECT ST_SplitSouthPole(geom_in geometry, radius integer, pole_distance integer, trans_buffer);
--
--
CREATE OR REPLACE FUNCTION ST_SplitSouthPole(geom_in geometry, radius integer default NULL, pole_distance integer DEFAULT 500000, trans_buffer integer DEFAULT -1)
    RETURNS geometry AS $$
DECLARE
    pole_split          geometry;
    pole_geom           geometry;
    pole_blade          geometry;
    epsg_code           integer;
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
        --RAISE NOTICE 'ST_SplitSouthPole : Segmentize and simplify topology';
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
        pole_split := ST_SimplifyPreserveTopology(ST_Buffer(ST_Transform(ST_Buffer(ST_Split(pole_split, pole_blade), trans_buffer), 4326), 0), 0.01);
    ELSE
        pole_split := ST_Buffer(ST_Transform(ST_Buffer(ST_Split(pole_split, pole_blade), trans_buffer), 4326), 0);
    END IF;

    RETURN pole_split;

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
--   ST_SplitNorthPole(polygon, radius integer default NULL, pole_distance integer DEFAULT 500000, trans_buffer DEFAULT -1)
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
--   SELECT ST_SplitNorthPole(geom_in geometry, radius integer, pole_distance integer, trans_buffer integer);
--
CREATE OR REPLACE FUNCTION ST_SplitNorthPole(geom_in geometry, radius integer default NULL, pole_distance integer DEFAULT 500000, trans_buffer integer DEFAULT -1)
    RETURNS geometry AS $$
DECLARE
    pole_geom           geometry;
    pole_blade          geometry;
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
        pole_blade := ST_SetSrid(ST_MakeLine(ARRAY[ST_MakePoint(-2353926.81, 2345724.36), ST_MakePoint(0, 0), ST_MakePoint(0, 0), ST_MakePoint(-2349829.16, 2349829.16)]), epsg_code);
        
    -- Latitudes are below 60 degrees =>  input geometry is converted to North Pole Azimuthal Equidistant
    ELSE
        --RAISE NOTICE 'Using EPSG:102016';
        epsg_code := 102016;
        -- (-180, 90)deg -> (0, 90)deg -> (-180, -89.9)deg
        --pole_blade := ST_SetSrid(ST_MakeLine(ARRAY[ST_MakePoint(2.4497750631170925E-9, 2.0003931458627265E7), ST_MakePoint(1.2248875315585463E-9, 1.0001965729313632E7), ST_MakePoint(0, 0), ST_MakePoint(0, 0), ST_MakePoint(0, -10001965.72931363), ST_MakePoint(0, -20003931.45862726)]), epsg_code);
        pole_blade := ST_SetSrid(ST_MakeLine(ARRAY[ST_MakePoint(0, 20003931.458627265), ST_MakePoint(0, 10001965.729313632), ST_MakePoint(0, 0), ST_MakePoint(0, 0), ST_MakePoint(0, -10001965.72931363), ST_MakePoint(0, -20003931.45862726)]), epsg_code);
    END IF;

    --
    -- Always apply a Buffer(0) to make output geometry valid
    --
    -- [NOTE] Densify over pole to avoid issue in ST_Difference
    --
    distance_to_south := ST_DistanceToSouthPole(geom_in);
    IF  distance_to_south > -1 AND distance_to_south <= pole_distance THEN
        --RAISE NOTICE 'ST_SplitNorthPole : Segmentize';
        pole_geom := ST_Buffer(ST_Transform(ST_Segmentize(geom_in::geography, 50000)::geometry, epsg_code), 0.0);
    ELSE
        pole_geom := ST_Buffer(ST_Transform(geom_in, epsg_code), 0.0);
    END IF;
    
    -- Convert polar geometry to epsg_code and optionaly remove a radius hole centered on North Pole.
    IF radius IS NOT NULL THEN
        pole_geom:= ST_Difference(pole_geom, ST_Buffer(ST_SetSrid(ST_MakePoint(0, 0), epsg_code), radius));
    END IF;

    -- Split polygon to avoid -180/180 crossing issue.
    -- Note: applying negative buffer ensure valid multipolygons that don't share a common edge
    IF distance_to_south > -1 AND distance_to_south <= pole_distance THEN
        --RAISE NOTICE 'ST_SplitNorthPole : Simplify topology';
        pole_geom := ST_SimplifyPreserveTopology(ST_Buffer(ST_Transform(ST_Buffer( ST_ForcePolygonCCW(ST_Split(pole_geom, pole_blade)) , trans_buffer), 4326), 0.0), 0.01);
    ELSE
        pole_geom := ST_Buffer(ST_Transform(ST_Buffer(ST_Split(pole_geom, pole_blade), trans_buffer), 4326), 0.0);
    END IF;

    RETURN pole_geom;

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
-- Copyright (C) 2021 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_CutPoles(polygon)
--
-- DESCRIPTION:
--   Cut both poles for input polygon geometry that crosses both poles
--
-- USAGE:
--   SELECT ST_CutPoles(geom_in geometry);
--
CREATE OR REPLACE FUNCTION ST_CutPoles(geom_in geometry)
    RETURNS geometry AS $$
DECLARE
    m                   geometry_dump;
    cutted              geometry := ST_GeomFromText('GEOMETRYCOLLECTION EMPTY', 4326);
    text_var1           text := '';
    text_var2           text := '';
    text_var3           text := '';
BEGIN

    FOR m IN SELECT (ST_Dump(ST_Split(geom_in, ST_SetSrid(ST_MakeLine(ARRAY[ST_MakePoint(-180, 0), ST_MakePoint(0, 0), ST_MakePoint(180, 0)]), 4326)))).* LOOP
        
        -- Keep only geometries that are not only on the poles
        IF ST_YMin(m.geom) < 60 AND ST_YMax(m.geom)  > -60 THEN
            cutted := ST_Union(cutted, m.geom);
        END IF;

    END LOOP;
    
    RETURN cutted;

    -- If any of above failed, revert to use original polygon
    -- This prevents ingestion error, but may potentially lead to incorrect spatial query.
    EXCEPTION WHEN OTHERS THEN
        GET STACKED DIAGNOSTICS text_var1 = MESSAGE_TEXT,
                                text_var2 = PG_EXCEPTION_DETAIL,
                                text_var3 = PG_EXCEPTION_HINT;
        raise WARNING 'ST_CutPoles: exception occured: Msg: %, detail: %, hint: %', text_var1, text_var1, text_var3;
        RETURN geom_in;

END
$$ LANGUAGE 'plpgsql' IMMUTABLE;


--
-- Copyright (C) 2018 Jerome Gasperi <jerome.gasperi@gmail.com>
--
-- SYNOPSYS:
--   ST_SplitDateLine(geom_in geometry, radius integer DEFAULT 50000, pole_distance integer DEFAULT 500000)
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
--   SELECT ST_SplitDateLine(geom_in, radius, pole_distance);
--
CREATE OR REPLACE FUNCTION ST_SplitDateLine(geom_in geometry, radius integer default 50000, pole_distance integer DEFAULT 500000)
    RETURNS geometry AS $$
DECLARE
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

    -- Poles crosses twice => split geometry at the equator
    IF np_distance = 0 AND sp_distance = 0 THEN
        geom_in := ST_CutPoles(geom_in);
        -- (Re)compute distances to North and South poles
        np_distance := ST_DistanceToNorthPole(geom_in);
        sp_distance := ST_DistanceToSouthPole(geom_in);
    END IF;

    -- Input geometry crosses North Pole
    IF np_distance > -1 AND np_distance <= pole_distance THEN

        -- Input geometry is even closer to South Pole
        IF sp_distance > -1 AND sp_distance < np_distance THEN
            RETURN ST_SplitSouthPole(geom_in, radius, pole_distance);
        ELSE
            RETURN ST_SplitNorthPole(geom_in, radius, pole_distance);
        END IF;

    END IF;

    -- Input geometry crosses South Pole
    IF sp_distance > -1 AND sp_distance <= pole_distance THEN

         -- Input geometry is even closer to North Pole
        IF np_distance > -1 AND np_distance < sp_distance THEN
            RETURN ST_SplitNorthPole(geom_in, radius, pole_distance);
        ELSE
            RETURN ST_SplitSouthPole(geom_in, radius, pole_distance);
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
