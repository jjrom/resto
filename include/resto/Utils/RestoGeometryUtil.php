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
        if ($type === 'POINT') {
            $wkt = $type . RestoUtil::toPoint($geometry['coordinates']);
        }
        else if ($type === 'MULTIPOINT') {
            $points = array();
            for ($i = 0, $l = count($geometry['coordinates']); $i < $l; $i++) {
                $points[] = RestoUtil::toPoint($geometry['coordinates'][$i]);
            }
            $wkt = $type . '(' . join(',', $points) . ')';
        }
        else if ($type === 'LINESTRING') {
            $wkt = $type . RestoUtil::toLineString($geometry['coordinates']);
        }
        else if ($type === 'MULTILINESTRING') {
            $lineStrings = array();
            for ($i = 0, $l = count($geometry['coordinates']); $i < $l; $i++) {
                $lineStrings[] = RestoUtil::toLineString($geometry['coordinates'][$i]);
            }
            $wkt = $type . '(' . join(',', $lineStrings) . ')';
        }
        else if ($type === 'POLYGON') {
            $wkt = $type . RestoUtil::toPolygon($geometry['coordinates']);
        }
        else if ($type === 'MULTIPOLYGON') {
            $polygons = array();
            for ($i = 0, $l = count($geometry['coordinates']); $i < $l; $i++) {
                $polygons[] = RestoUtil::toPolygon($geometry['coordinates'][$i]);
            }
            $wkt = $type . '(' . join(',', $polygons) . ')';
        }
        return $wkt;
    }
    
    /**
     * Return POINT WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    public static function toPoint($coordinates) {
        return '(' . join(' ', $coordinates) . ')';
    }
    
    /**
     * Return LINESTRING WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    public static function toLineString($coordinates) {
        $pairs = array();
        for ($i = 0, $l = count($coordinates); $i < $l; $i++) {
            $pairs[] = join(' ', $coordinates[$i]);
        }
        return '(' . join(',', $pairs) . ')';
    }
    
    /**
     * Return POLYGON WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    public static function toPolygon($coordinates) {
        $rings = array();
        for ($i = 0, $l = count($coordinates); $i < $l; $i++) {
            $rings[] = RestoUtil::toLineString($coordinates[$i]);
        }
        return '(' . join(',', $rings) . ')';
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
        $ll = RestoGeometryUtil::forwardMercator(array(floatval($coords[0]), floatval($coords[1])));
        if (!$ll) {
            return null;
        }

        /*
         * Upper right coordinate
         */
        $ur = RestoGeometryUtil::forwardMercator(array(floatval($coords[2]), floatval($coords[3])));
        if (!$ur) {
            return null;
        }

        return join(',', $ll) . ',' . join(',', $ur);
    }
    
}
