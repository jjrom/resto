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
    abstract public function tag($metadata, $options = array());
       
    /**
     * Throws exception
     * 
     * @param string $message
     * @param integer $code
     * @throws Exception
     */
    protected function error($message = 'Database Connection Error', $code = 500) {
        throw new Exception($message, $code);
    }

    /**
     * Return true if either W-E or N-S length of the footprint
     * is greater than $degrees 
     *  
     * @param string $footprint
     */
    protected function isValidArea($footprint) {
        return $this->getArea($footprint) > $this->config['areaLimit'] ? false : true;
    }
    
    /**
     * Return percentage of $part regarding $total
     * @param <float> $part
     * @param <float> $total
     * @return <float>
     */
    protected function percentage($part, $total) {
        if (!isset($total) || $total === 0) {
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
            $this->error();
        }
        return $results;
    }
    
    /**
     * Return the area of a WKT footprint
     * 
     * @param string $footprint
     */
    protected function getArea($footprint) {
        
        $coordinates = $this->wktToCoordinates($footprint);
        $count = count($coordinates[0]);
        $xs = array();
        $ys = array();

        //export to $xs and $ys
        for ($i = 0; $i < $count; $i++) {
            array_push($xs, $coordinates[0][$i][0]);
            array_push($ys, $coordinates[0][$i][1]);
        }

        if (count($xs) != count($ys)) {
            return -1;
        }

        if (count($xs) < 3) {
            return -1;
        }

        return abs((($this->subCalculation($xs, $ys)) - ($this->subCalculation($ys, $xs))) / 2);
    }
    
    /**
     * Return postgis area function
     * @param string $geometry
     */
    protected function postgisArea($geometry) {
        return 'st_area(geography(' . $geometry . '))';
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
     * Subcomputation of area 
     * 
     * @param array $a
     * @param array $b
     * @return float
     */
    private function subCalculation($a, $b) {
        $answer = 0;

        for ($i = 0; $i < (count($a) - 1); $i++) {
            $answer += ($a[$i] * $b[$i + 1]);
        }

        $answer += $a[count($a) - 1] * $b[0];
        return $answer;
    }
    
    /**
     * Return GeoJSON coordinates from wkt
     * 
     * @param string $footprint
     * @return array
     */
    private function wktToCoordinates($footprint) {
        $strcoordinates = str_replace(array('polygon((', '))'), '', trim(strtolower($footprint)));
        $pairs = explode(',', $strcoordinates);
        $lonlats = array();
        for ($i = 0, $ii = count($pairs); $i < $ii; $i++) {
            $lonlats[] = explode(' ', trim($pairs[$i]));
        }
        return array($lonlats);
    }
}
