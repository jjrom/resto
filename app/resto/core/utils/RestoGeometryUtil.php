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

        $allowedGeometryTypes = array(
            'Point',
            'Polygon',
            'LineString',
            'MultiPoint',
            'MultiPolygon',
            'MultiLineString'
        );

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
        if (!isset($object['geometry']) || !is_array($object['geometry'])) {
            return array(
                'isValid' => false,
                'error' => $error . ' - invalid geometry'
            );
        }
        if (!isset($object['geometry']['coordinates']) || !is_array($object['geometry']['coordinates'])) {
            return array(
                'isValid' => false,
                'error' => $error . ' - invalid coordinates'
            );
        }
        if (!isset($object['geometry']['type']) || !in_array($object['geometry']['type'], $allowedGeometryTypes)) {
            return array(
                'isValid' => false,
                'error' => $error . ' - type should be one of ' . join(', ', $allowedGeometryTypes)
            );
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
    public static function bboxToMercator($bbox)
    {
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
    private static function toPoint($coordinates)
    {
        return '(' . join(' ', $coordinates) . ')';
    }

    /**
     * Return WKT from geometry
     * @param array $geometry - GeoJSON geometry
     */
    public static function geoJSONGeometryToWKT($geometry)
    {
        $type = strtoupper($geometry['type']);
        switch ($type) {

            case 'POINT':
                return $type . RestoGeometryUtil::toPoint($geometry['coordinates']);
            
            case 'MULTIPOINT':
                return $type . RestoGeometryUtil::coordinatesToString($geometry['coordinates'], 'toPoint');
            
            case 'LINESTRING':
                return $type . RestoGeometryUtil::coordinatesToString($geometry['coordinates']);
            
            case 'MULTILINESTRING':
            case 'POLYGON':
                return $type . RestoGeometryUtil::coordinatesToString($geometry['coordinates'], 'toLineString');
            
            case 'MULTIPOLYGON':
                return $type . RestoGeometryUtil::coordinatesToString($geometry['coordinates'], 'toPolygon');
            
            default:
                return null;
        }
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

    /**
     * Returns point array from WKT point.
     *
     * @param string $wktPoint WKT point
     * @throws Exception
     * @return multitype:NULL polygon array
     */
    public static function WKTPointToArray($wktPoint)
    {
        $coordsAsString = explode(' ', substr($wktPoint, 6, -1));
        return array((float) $coordsAsString[0], (float) $coordsAsString[1]);
    }

    /**
     * Return a WKT Polygon from centroid and radius
     *
     * @param float $lon lon
     * @param float $lat Lat
     * @param float $radius (in meters)
     * @throws Exception
     * @return multitype:NULL polygon array
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

}
