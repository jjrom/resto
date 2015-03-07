<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2013 Jérôme Gasperi <https://github.com/jjrom>
 * 
 * jerome[dot]gasperi[at]gmail[dot]com
 * 
 * 
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
 * 
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
}
