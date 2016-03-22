<?php
/*
 * Copyright 2014 Jérôme Gasperi
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
class RestoGeometryUtil {

    /**
     * Check if input object is a valid GeoJSON object
     * 
     * Valid GeoJSON Feature 
     * 
     *      array(
     *          'type' => 'Feature',
     *          'geometry' => array(...),
     *          'properties' => array(...)
     *      )
     *       
     * @param Array $object : json object
     */
    public static function isValidGeoJSONFeature($object) {
        if (!$object || !is_array($object)) {
            return false;
        }
        if (!isset($object['type']) || $object['type'] !== 'Feature') {
            return false;
        }
        if (!isset($object['geometry']) || !is_array($object['geometry'])) {
            return false;
        }
        if (!isset($object['properties']) || !is_array($object['properties'])) {
            return false;
        }
        return true;
    }
    
    /**
     * Return WKT from geometry
     * @param array $geometry - GeoJSON geometry
     */
    public static function geoJSONGeometryToWKT($geometry) {
        $type = strtoupper($geometry['type']);
        switch($type) {
            case 'POINT':
                return $type . RestoGeometryUtil::toPoint($geometry['coordinates']);
            case 'MULTIPOINT':
                return $type . RestoGeometryUtil::toMultiPoint($geometry['coordinates']);
            case 'LINESTRING':
                return $type . RestoGeometryUtil::toLineString($geometry['coordinates']);
            case 'MULTILINESTRING':
                return $type . RestoGeometryUtil::toMultiLineString($geometry['coordinates']);
            case 'POLYGON':
                return $type . RestoGeometryUtil::toPolygon($geometry['coordinates']);
            case 'MULTIPOLYGON':
                return $type . RestoGeometryUtil::toMultiPolygon($geometry['coordinates']);
            default:
                return null;
        }
    }
    
    /**
     * Return BBOX from WKT
     * 
     * @param array $polygon - WKT Polygon
     */
    public static function getExtent($polygon) {
        $extent = array(360, 180, -360, -180);
        $stringPairs = explode(',', str_replace('POLYGON((', '', str_replace('))', '', $polygon)));
        for ($i = 0, $ii = count($stringPairs); $i < $ii; $i++) {
            $coordinates = explode(' ', trim($stringPairs[$i]));
            if (floatval($coordinates[0]) > $extent[2]) {
                $extent[2] = $coordinates[0];
            }
            if (floatval($coordinates[0]) < $extent[0]) {
                $extent[0] = $coordinates[0];
            }
            if (floatval($coordinates[1]) > $extent[3]) {
                $extent[3] = $coordinates[1];
            }
            if (floatval($coordinates[1]) < $extent[1]) {
                $extent[1] = $coordinates[1];
            }
        }
        return $extent;
    }
    
    /**
     * Return radius length in degrees for a radius in meters
     * at a given latitude
     * 
     * @param float $radius
     * @param float $lat
     */
    public static function radiusInDegrees($radius, $lat) {
        return ($radius * cos(deg2rad($lat))) / 111110.0;
    }

    /**
     * Transform EPSG:3857 coordinate into EPSG:4326
     * 
     * @param {array} $xy : array(x, y) 
     */
    public static function inverseMercator($xy) {

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
    public static function forwardMercator($lonlat) {

        if (!is_array($lonlat) || count($lonlat) !== 2) {
            return null;
        }

        /*
         * Latitude limits are -85/+85 degrees
         */
        if ($lonlat[1] > 85 || $lonlat[1] < -85) {
            return null;
        }

        return array(
            $lonlat[0] * 20037508.34 / 180.0,
            max(-20037508.34, min(log(tan((90.0 + $lonlat[1]) * M_PI / 360.0)) / M_PI * 20037508.34, 20037508.34))
        );
    }

    /**
     * Transform EPSG:4326 BBOX to EPSG:3857 bbox
     * 
     * @param {String} $bbox : bbox in EPSG:4326 (i.e. lonmin,latmin,lonmax,latmax) 
     */
    public static function bboxToMercator($bbox) {

        if (!$bbox) {
            return null;
        }
        $coords = explode(',', $bbox);
        if (count($coords) !== 4) {
            return null;
        }

        /*
         * Lower left coordinate
         */
        $lowerLeft = RestoGeometryUtil::forwardMercator(array(floatval($coords[0]), floatval($coords[1])));
        if (!$lowerLeft) {
            return null;
        }

        /*
         * Upper right coordinate
         */
        $upperRight = RestoGeometryUtil::forwardMercator(array(floatval($coords[2]), floatval($coords[3])));
        if (!$upperRight) {
            return null;
        }

        return join(',', $lowerLeft) . ',' . join(',', $upperRight);
    }
    
    /**
     * Return POINT WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    private static function toPoint($coordinates) {
        return '(' . join(' ', $coordinates) . ')';
    }
    
    /**
     * Return MULTIPOINT WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    private static function toMultiPoint($coordinates) {
        return RestoGeometryUtil::coordinatesToString($coordinates, 'toPoint');
    }
    
    /**
     * Return LINESTRING WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    private static function toLineString($coordinates) {
        return RestoGeometryUtil::coordinatesToString($coordinates);
    }
    
    /**
     * Return MULTILINESTRING WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    private static function toMultiLineString($coordinates) {
        return RestoGeometryUtil::toPolygon($coordinates);
    }
    
    /**
     * Return POLYGON WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    private static function toPolygon($coordinates) {
        return RestoGeometryUtil::coordinatesToString($coordinates, 'toLineString');
    }
    
    /**
     * Return MULTIPOLYGON WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    private static function toMultiPolygon($coordinates) {
        return RestoGeometryUtil::coordinatesToString($coordinates, 'toPolygon');
    }
    
    /**
     * Generic code to transform input coordinates array to WKT string
     * 
     * @param array $coordinates
     * @param function $functionName
     * @return type
     */
    private static function coordinatesToString($coordinates, $functionName = null) {
        $output = array();
        for ($i = 0, $l = count($coordinates); $i < $l; $i++) {
            switch ($functionName) {
                case 'toPoint':
                    $output[] = RestoGeometryUtil::toPoint($coordinates[$i]);
                    break;
                case 'toLineString':
                    $output[] = RestoGeometryUtil::toLineString($coordinates[$i]);
                    break;
                case 'toPolygon':
                    $output[] = RestoGeometryUtil::toPolygon($coordinates[$i]);
                    break;
                default:
                    $output[] = join(' ', $coordinates[$i]);
            }
        }
        return '(' . join(',', $output) . ')';
    }
    
    /**
     * Returns polygon array from WKT polygon.
     * 
     * @param unknown $wktPolygon WKT polygon
     * @throws Exception
     * @return multitype:NULL polygon array
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
                for($i = 0; $i < count($coordinates); $i++) {
                    $coordinates[$i] = explode(' ', $coordinates[$i]);
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
}
