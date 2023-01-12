<?php
/*
 * Copyright 2018 Jérôme Gasperi
 *
 * Licensed under the Apache License, version 2.0 (the "License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/**
 * RESTo Geometry utilities functions
 */
class RestoGeometryUtil
{


    /**
     * Check if input object is a valid GeoJSON object
     *
     * Valid GeoJSON geometry
     *
     *      array(
     *          'type' =>,
     *          'coordinates' => array(...)
     *      )
     *
     * @param array $object : json object
     * @return array
     */
    public static function checkGeoJSONGeometry($object)
    {

        $allowedGeometryTypes = array(
            'Point',
            'Polygon',
            'LineString',
            'MultiPoint',
            'MultiPolygon',
            'MultiLineString'
        );

        if (!$object || !is_array($object)) {
            return false;
        }

        if (!isset($object['coordinates']) || !is_array($object['coordinates'])) {
            return false;
        }

        if (!isset($object['type']) || !in_array($object['type'], $allowedGeometryTypes)) {
            return false;
        }
        
        return true;

    }

    /**
     * Check if input object is a valid GeoJSON object
     *
     * Valid GeoJSON Feature
     *
     *      array(
     *          'type' => 'Feature',
     *          'geometry' => array(
     *              'type' =>,
     *              'coordinates' => array(...)
     *          ),
     *          'properties' => array(...)
     *      )
     *
     * @param array $object : json object
     * @return array
     */
    public static function checkGeoJSONFeature($object)
    {

        // Default is nice
        $error = 'Invalid GeoJSON feature';

        if (!$object || !is_array($object)) {
            return array(
                'isValid' => false,
                'error' => $error
            );
        }

        if (!isset($object['type']) || $object['type'] !== 'Feature') {
            return array(
                'isValid' => false,
                'error' => $error . ' - only type *Feature* is supported'
            );
        }

        /* 
         * Empty geometry are allowed in GeoJSON specification
         * 
         * "The value of the geometry member SHALL be either a Geometry object as
         *  defined above or, in the case that the Feature is unlocated, a JSON null value"
         * 
         * (See https://tools.ietf.org/html/rfc7946#section-1.4)
         */
        if ( isset($object['geometry']) ) {
            if (!is_array($object['geometry']) || ! RestoGeometryUtil::checkGeoJSONGeometry($object['geometry'])) {
                return array(
                    'isValid' => false,
                    'error' => $error . ' - invalid geometry'
                );
            }
        }

        if (!isset($object['properties']) || !is_array($object['properties'])) {
            return array(
                'isValid' => false,
                'error' => $error . ' - invalid properties'
            );
        }

        return array(
            'isValid' => true
        );

    }

    /**
     * Check if input object is a valid WKT object
     * 
     * [TODO] Does not support mutligeometries and/or (multi)geometries with holes
     * [TODO] This function does not validates the coordinates validity
     *
     * @param string $wktstring
     * @return boolean
     */
    public static function isValidWKT($wktstring)
    {
        if (! isset($wktstring) ) {
            return false;
        }
        if ( substr(strtolower($wktstring), 0, 6) === 'point(' && substr($wktstring, -1) === ')') {
            return true;
        }
        if ( substr(strtolower($wktstring), 0, 9) === 'polygon((' && substr($wktstring, -2) === '))') {
            return true;
        }
        if ( substr(strtolower($wktstring), 0, 12) === 'linestring((' && substr($wktstring, -2) === '))') {
            return true;
        }
        return true;
    }

    /**
     * Check if input object is a valid WKT polygon
     *
     * @param string $wktPolygon
     * @return boolean
     */
    public static function isValidWKTPolygon($wktPolygon)
    {
        if (! isset($wktPolygon) || substr(strtolower($wktPolygon), 0, 9) !== 'polygon((' || substr($wktPolygon, -2) !== '))') {
            return false;
        }
        return true;
    }

    /**
     * Return radius length in degrees for a radius in meters
     * at a given latitude
     *
     * @param float $radius
     * @param float $lat
     * @return float
     */
    public static function radiusInDegrees($radius, $lat)
    {
        return ($radius * cos(deg2rad($lat))) / 52520.0;
    }

    /**
     * Transform EPSG:3857 coordinate into EPSG:4326
     *
     * @param {array} $xy : array(x, y)
     */
    public static function inverseMercator($xy)
    {
        if (!is_array($xy) || count($xy) !== 2) {
            return null;
        }

        return array(
            180.0 * $xy[0] / 20037508.34,
            180.0 / M_PI * (2.0 * atan(exp(($xy[1] / 20037508.34) * M_PI)) - M_PI / 2.0)
        );
    }

    /**
     * Transform EPSG:4326 coordinate into EPSG:3857
     *
     * @param {array} $lonlat : array(lon, lat)
     */
    public static function forwardMercator($lonlat)
    {
        if (!is_array($lonlat) || count($lonlat) !== 2) {
            return null;
        }

        /*
         * Constrain latitude limits between -85/+85 degrees
         */
        if ($lonlat[1] > 85) {
            $lonlat[1] = 85;
        }
        else if ($lonlat[1] < -85) {
            $lonlat[1] = -85;
        }

        return array(
            $lonlat[0] * 20037508.34 / 180.0,
            max(-20037508.34, min(log(tan((90.0 + $lonlat[1]) * M_PI / 360.0)) / M_PI * 20037508.34, 20037508.34))
        );

    }

    /**
     * Return a PostGIS BOX2D to a bbox array
     * 
     * @param string $box2d
     * @return array
     */
    public static function box2dTobbox($box2d)
    {
        return isset($box2d) ? array_map('floatval', explode(',', str_replace(' ', ',', substr(substr($box2d, 0, strlen($box2d) - 1), 4)))) : null;
    }

    /**
     * Transform EPSG:4326 BBOX to EPSG:3857 bbox
     *
     * @param array $bbox : bbox in EPSG:4326 (i.e. [lonmin,latmin,lonmax,latmax])
     */
    public static function bboxToMercator($bbox)
    {
        if (!isset($bbox) || count($bbox) !== 4) {
            return null;
        }
      
        /*
         * Lower left coordinate
         */
        $lowerLeft = RestoGeometryUtil::forwardMercator(array(floatval($bbox[0]), floatval($bbox[1])));
        if (!$lowerLeft) {
            return null;
        }

        /*
         * Upper right coordinate
         */
        $upperRight = RestoGeometryUtil::forwardMercator(array(floatval($bbox[2]), floatval($bbox[3])));
        if (!$upperRight) {
            return null;
        }

        return join(',', $lowerLeft) . ',' . join(',', $upperRight);
    }

    /**
     * Return WKT from geometry
     * @param array $geometry - GeoJSON geometry
     */
    public static function geoJSONGeometryToWKT($geometry)
    {
        
        if (!isset($geometry)) {
            return null;
        }
        
        $type = strtoupper($geometry['type']);
        $epsgCode = RestoGeometryUtil::geoJSONGeometryToSRID($geometry);
        $srid = $epsgCode === 4326 ? '' : 'SRID=' . $epsgCode . ';';
        switch ($type) {
            case 'POINT':
                $wkt = $srid . $type . RestoGeometryUtil::toPoint($geometry['coordinates']);
                break;
                
            case 'MULTIPOINT':
                $wkt = $srid . $type . RestoGeometryUtil::coordinatesToString($geometry['coordinates'], 'toPoint');
                break;
            
            case 'LINESTRING':
                $wkt = $srid . $type . RestoGeometryUtil::coordinatesToString($geometry['coordinates']);
                break;

            case 'MULTILINESTRING':
            case 'POLYGON':
                $wkt = $srid . $type . RestoGeometryUtil::coordinatesToString($geometry['coordinates'], 'toLineString');
                break;

            case 'MULTIPOLYGON':
                $wkt = $srid . $type . RestoGeometryUtil::coordinatesToString($geometry['coordinates'], 'toPolygon');
                break;
            
            case 'GEOMETRYCOLLECTION':
                $wkts = array();
                for ($i = count($geometry['geometries']); $i--;) {
                    $wkts[] = RestoGeometryUtil::geoJSONGeometryToWKT($geometry['geometries'][$i]);
                }
                $wkt = $srid . $type . '(' . join(',', $wkts). ')';
                break;

            default:
                $wkt = null;
                
        }

        return $wkt;
    }

    /**
     * Return SRID from GeoJSON geometry
     * @param array $geometry - GeoJSON geometry
     */
    public static function geoJSONGeometryToSRID($geometry)
    {
        if (isset($geometry) && isset($geometry['crs']) && isset($geometry['crs']['properties']) && isset($geometry['crs']['properties']['name'])) {
            // Get code from EPSG string (e.g. urn:ogc:def:crs:EPSG:8.8.1:32610)
            $exploded = explode(':', $geometry['crs']['properties']['name']);
            $epsgCode = $exploded[count($exploded) - 1];
            return (integer) $epsgCode;
            
        }
        return 4326;
    }

    /**
     * Convert input GeoJSON string geometry into WKT or leave it untouched if already a WKT
     * 
     * @param string $geostring
     */
    public static function forceWKT($geostring)
    {

        // Issue #329 - pystac_client GeoJSON string is within double quotes (so not valid GeoJSON)
        // e.g. "intersects":"\"{\\\"type\\\": \\\"Polygon\\\", \\\"coordinates\\\": [[[100.0, 0.0], [101.0, 0.0], [101.0, 1.0], [100.0, 1.0], [100.0, 0.0]]]}\""
        $geostring = stripslashes(trim($geostring, '\'"'));

        if (isset($geostring) && isset($geostring[0]) && $geostring[0] === '{') {
            $geostring = RestoGeometryUtil::geoJSONGeometryToWKT(json_decode($geostring, true));
        }
        
        if ( !isset($geostring) || !RestoGeometryUtil::isValidWKT($geostring) ) {
            return RestoLogUtil::httpError(400, 'Invalid input geometry for intersects - should be a valid GeoJSON or Well Known Text standard (WKT)');
        }
        
        return $geostring;

    }

    /**
     * Returns point array from WKT point.
     *
     * @param string $wktPoint WKT point
     * @return array
     */
    public static function WKTPointToArray($wktPoint)
    {
        $coordsAsString = explode(' ', substr($wktPoint, 6, -1));
        return array((float) $coordsAsString[0], (float) $coordsAsString[1]);
    }

    /**
     * Returns polygon array from WKT polygon.
     * 
     * @param string $wktPolygon WKT polygon
     * @throws Exception
     * @return array
     */
    public static function WKTPolygonToArray($wktPolygon) {
        
        /*
         * Result
         */
        $coordinates = array ();
        
        /*
         * Patterns
         */
        $lon = $lat = '[-]?[0-9]{1,3}\.?[0-9]*';
        $values = "($lon $lat)(\s*,\s*$lon $lat)*";
        $pattern = "/^POLYGON\s*\(\s*\(\s*($values)\s*\)\s*\)$/i";
        
        /*
         * Checks input parameter (WKT String)
         */
        if (preg_match($pattern, $wktPolygon, $matches)) {
            
            if (count($matches) >= 1) {
                
                /*
                 * Explodes coordinates string
                 */
                $coordinates = explode(',', $matches[1]);
                
                /*
                 * For each coordinate, stores lon/lat
                 */
                for($i = 0, $ii = count($coordinates); $i < $ii; $i++) {
                    $coordinates[$i] = array_map('floatval', explode(' ', $coordinates[$i]));
                }
            }
        }
        else {
            throw new Exception(__method__ . ': Invalid input WKT');
        }

        /*
         * Returns result
         */
        return $coordinates;
        
    }

    /**
     * Return a WKT Polygon from centroid and radius
     *
     * @param float $lon lon
     * @param float $lat Lat
     * @param float $radius (in meters)
     * @throws Exception
     * @return string
     */
    public static function WKTPolygonFromLonLat($lon, $lat, $radius)
    {
        $radius = RestoGeometryUtil::radiusInDegrees($radius, $lat);
        $lonmin = $lon - $radius;
        $latmin =  $lat - $radius;
        $lonmax = $lon + $radius;
        $latmax =  $lat  + $radius;
        return 'POLYGON((' . $lonmin . ' ' . $latmin . ',' . $lonmin . ' ' . $latmax . ',' . $lonmax . ' ' . $latmax . ',' . $lonmax . ' ' . $latmin . ',' . $lonmin . ' ' . $latmin . '))';
    }

    /**
     * Return POINT WKT from coordinates (without WKT type)
     *
     * @param array $coordinates - GeoJSON geometry
     */
    private static function toPoint($coordinates)
    {
        return '(' . join(' ', $coordinates) . ')';
    }

    /**
     * Generic code to transform input coordinates array to WKT string
     *
     * @param array $coordinates
     * @param function $functionName
     * @return string
     */
    private static function coordinatesToString($coordinates, $functionName = null)
    {
        $output = array();
        for ($i = 0, $l = count($coordinates); $i < $l; $i++) {
            switch ($functionName) {
                case 'toPoint':
                    $output[] = RestoGeometryUtil::toPoint($coordinates[$i]);
                    break;
                case 'toLineString':
                    $output[] = RestoGeometryUtil::coordinatesToString($coordinates[$i]);
                    break;
                case 'toPolygon':
                    $output[] = RestoGeometryUtil::coordinatesToString($coordinates[$i], 'toLineString');
                    break;
                default:
                    $output[] = join(' ', $coordinates[$i]);
            }
        }
        return '(' . join(',', $output) . ')';
    }

}
