<?php
/*
 * Copyright 2013 Jérôme Gasperi
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

abstract class Tagger {
    
    /*
     * Data references description
     */
    public $references;
    
    /*
     * Database Handler reference
     */
    protected $dbh;
    
    /*
     * Configuration array
     */
    protected $config;
    
    /*
     * Footprint area
     */
    protected $area;
    
    /**
     * Constructor
     * 
     * @param DatabaseHandler $dbh
     * @param array $config
     */
    public function __construct($dbh, $config) {
        $this->dbh = $dbh;
        $this->config = $config;
    }
    
    /**
     * Tag metadata
     * 
     * metadata array should contains at least the following properties:
     * 
     *      'footprint' => // WKT footprint
     * 
     * @param array $metadata
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function tag($metadata, $options = array()) {
        $this->area = isset($metadata['area']) ? $metadata['area'] : -1;
    }

    /**
     * Return true if area is lower than maximum limit
     * 
     * @param float $area (in square kilometers)
     */
    protected function isValidArea($area) {
        return $area > $this->config['areaLimit'] ? false : true;
    }
    
    /**
     * Return percentage of $part regarding $total
     * 
     * @param <float> $part
     * @param <float> $total
     * @return <float>
     */
    protected function percentage($part, $total) {
        if (!isset($total) || $total == 0) {
            return 100;
        }
        return min(array(100, floor(10000 * ($part / $total)) / 100));
    }

    /**
     * Return results database from query
     * 
     * @param string $query
     */
    protected function query($query) {
        $results = pg_query($this->dbh, $query);
        if (!isset($results)) {
            throw new Exception('Database Connection Error', 500);
        }
        return $results;
    }
    
    /**
     * Return postgis area function
     * @param string $geometry
     */
    protected function postgisArea($geometry) {
        return 'st_area(geography(' . $geometry . '))';
    }
    
    /**
     * Return postgis WKT function
     * 
     * @param string $geom
     * 
     */
    protected function postgisAsWKT($geom) {
        return 'st_astext(' . $geom . ')';
    }
    
    /**
     * Return postgis intersection function
     * 
     * @param string $geomA
     * @param string $geomB
     * 
     */
    protected function postgisIntersection($geomA, $geomB) {
        return 'st_intersection(' . $geomA . ',' . $geomB . ')';
    }
    
    /**
     * Return postgis intersection function
     * 
     * @param string $geom
     * @param boolean $preserveTopology
     * 
     */
    protected function postgisSimplify($geom, $preserveTopology = false) {
        return $this->config['geometryTolerance'] > 0 ? 'ST_Simplify' . ($preserveTopology ? 'PreserveTopology' : '') . '(' . $geom . ',' . $this->config['geometryTolerance'] . ')' : $geom;
    }
    
    /**
     * Return postgis intersection function
     * 
     * @param string $footprint
     * @param string $srid
     * 
     */
    protected function postgisGeomFromText($footprint, $srid = '4326') {
        return 'ST_GeomFromText(\'' . $footprint . '\', ' . $srid . ')';
    }
    
    /**
     * Return area in square kilometers
     * 
     * @param string $areaInSquareMeters
     */
    protected function toSquareKm($areaInSquareMeters) {
        return floatval($areaInSquareMeters) / 1000000;
    }
    
    /**
     * 
     * Return true if input date string is a valid timestamp,
     * i.e. an ISO 8601 formatted date with at least YYYY-MM-DD
     * Accepted forms are :
     * 
     *      YYYY-MM-DD
     *      YYYY-MM-DDTHH:MM:SS
     *      YYYY-MM-DDTHH:MM:SSZ
     *      YYYY-MM-DDTHH:MM:SS.sssss
     *      YYYY-MM-DDTHH:MM:SS.sssssZ
     *      YYYY-MM-DDTHH:MM:SS+HHMM
     *      YYYY-MM-DDTHH:MM:SS-HHMM
     *      YYYY-MM-DDTHH:MM:SS.sssss+HHMM
     *      YYYY-MM-DDTHH:MM:SS.sssss-HHMM
     * 
     * @param {String} $dateStr
     *    
     */
    protected function isValidTimeStamp($dateStr) {

        /**
         * Construct the regex to match all ISO 8601 format date case
         * The regex is constructed as a combination of all pattern       
         */
        return preg_match('/^' . join('$|^', array(
                    '\d{4}-\d{2}-\d{2}', // YYYY-MM-DD
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}', // YYYY-MM-DDTHH:MM:SS
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}Z', // YYYY-MM-DDTHH:MM:SSZ
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}' . '' . '[\+|\-]\d{2}\:\d{2}', // YYYY-MM-DDTHH:MM:SS +HH:MM or -HH:MM
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}' . '' . '[,|\.]\d+', // YYYY-MM-DDTHH:MM:SS(. or ,)n
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}' . '' . '[,|\.]\d+' . 'Z', // YYYY-MM-DDTHH:MM:SS(. or ,)nZ
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}' . '' . '[,|\.]\d+' . '' . '[\+|\-]\d{2}\:\d{2}', // // YYYY-MM-DDTHH:MM:SS(. or ,)n +HH:MM or -HH:MM
                    '\d{4}\d{2}\d{2}', // YYYYMMDD
                    '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}', // YYYYMMDDTHHMMSS
                    '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}' . 'Z', // YYYYMMDDTHHMMSSZ
                    '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}' . '' . '[\+|\-]\d{2}\d{2}', // YYYYMMDDTHHMMSSZ +HHMM or -HHMM
                    '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}' . '' . '[\+|\-]\d{2}\d{2}' . 'Z', // // YYYYMMDDTHHMMSSZ(. or ,)nZ
                    '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}' . '' . '[,|\.]\d+' . '' . '[\+|\-]\d{2}\d{2}' // YYYYMMDDTHHMMSSZ(. or ,)n +HHMM or -HHMM
                )) . '$/i', $dateStr);
    }

}
