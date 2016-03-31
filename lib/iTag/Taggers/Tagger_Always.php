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

class Tagger_Always extends Tagger {

    /*
     * Data references
     */
    public $references = array(
        array(
            'dataset' => 'Coastline',
            'author' => 'Natural Earth',
            'license' => 'Free of Charge',
            'url' => 'http://www.naturalearthdata.com/downloads/10m-physical-vectors/10m-coastline/'
        )
    );
    
    /*
     * Well known areas
     */
    private $areas = array(
        'equatorial' => array(
            'operator' => 'ST_Crosses',
            'geometry' => 'ST_GeomFromText(\'LINESTRING(-180 0,180 0)\', 4326)'
        ),
        'tropical' => array(
            'operator' => 'ST_Contains',
            'geometry' => 'ST_GeomFromText(\'POLYGON((-180 -23.43731,-180 23.43731,180 23.43731,180 -23.43731,-180 -23.43731))\', 4326)'
        ),
        'southern' => array(
            'operator' => 'ST_Contains',
            'geometry' => 'ST_GeomFromText(\'POLYGON((-180 0,-180 -90,180 -90,180 0,-180 0))\', 4326)'
        ),
        'northern' => array(
            'operator' => 'ST_Contains',
            'geometry' => 'ST_GeomFromText(\'POLYGON((-180 0,-180 90,180 90,180 0,-180 0))\', 4326)'
        )
    );
    
    /**
     * Constructor
     * 
     * @param DatabaseHandler $dbh
     * @param array $config
     */
    public function __construct($dbh, $config) {
        parent::__construct($dbh, $config);
    }
    
    /**
     * TODO Tag metadata
     * 
     * @param array $metadata
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function tag($metadata, $options = array()) {
        
        /*
         * Relative location on earth
         */
        $locations = $this->getLocations($metadata['footprint']);
        $keywords = $locations;
        
        /*
         * Coastal status
         */
        if ($this->isCoastal($metadata['footprint'])) {
            $keywords[] = 'location:coastal';
        }
       
        /*
         * Season
         */
        if (isset($metadata['timestamp']) && $this->isValidTimeStamp($metadata['timestamp']) ) {
            $keywords[] = $this->getSeason($metadata['timestamp'], in_array('location:southern', $locations));
        }
        
        return array(
            'area' => $this->getArea($metadata['footprint']),
            'keywords' => $keywords
        );
        
    }
    
    /**
     * Return footprint area in square meters
     * 
     * @param string $footprint
     */
    private function getArea($footprint) {
        $query = 'SELECT ' . $this->postgisArea($this->postgisGeomFromText($footprint)) . ' as area';
        $result = pg_fetch_assoc($this->query($query));
        return $this->toSquareKm($result['area']);
    }
    
    /**
     * Return locations of footprint i.e.
     *  - location:equatorial
     *  - location:tropical
     *  - location:northern
     *  - location:southern
     * 
     * @param string $footprint
     */
    private function getLocations($footprint) {
        $locations = array();
        foreach ($this->areas as $key => $value) {
            if ($this->isETNS($footprint, $value)) {
                $locations[] = 'location:' . $key;
            }
        }
        return $locations;
    }
    
    /**
     * Return true if footprint overlaps a coastline
     * 
     * @param string $footprint
     */
    private function isCoastal($footprint) {
        $geom = $this->postgisGeomFromText($footprint);
        $query = 'SELECT gid FROM datasources.coastlines WHERE ST_Crosses(' . $geom . ', geom) OR ST_Contains(' . $geom . ', geom)';
        return $this->hasResults($query);
    }
    
    /**
     * Return true if footprint overlaps Equatorial, Tropical, Southern or Northern areas
     * 
     * @param string $footprint
     * @param array $what
     */
    private function isETNS($footprint, $what) {
        $query = 'SELECT 1 WHERE ' . $what['operator'] . '(' . $what['geometry'] . ',' . $this->postgisGeomFromText($footprint) . ') LIMIT 1';
        return $this->hasResults($query);
    }
    
    /**
     * Return season keyword
     * 
     * @param string $timestamp
     * @param boolean $southern
     */
    private function getSeason($timestamp, $southern = false) {
        
        /*
         * Get month and day
         */
        $month = intval(substr($timestamp, 5, 2));
        $day = intval(substr($timestamp, 8, 2));
        
        if ($this->isSpring($month, $day)) {
            return $southern ? 'season:autumn' : 'season:spring';
        }
        
        else if ($this->isSummer($month, $day)) {
            return $southern ? 'season:winter' : 'season:summer';
        }
        
        else if ($this->isAutumn($month, $day)) {
            return $southern ? 'season:spring' : 'season:autumn';
        }
        
        else {
            return $southern ? 'season:summer' : 'season:winter';
        }
        
    }
    
    /**
     * Return true if season is winter
     * 
     * @param integer $month
     * @param integer $day
     * @return type
     */
    private function isSpring($month, $day) {
        return $this->isSeason($month, $day, array(3, 6));
    }
    
    /**
     * Return true if season is winter
     * 
     * @param integer $month
     * @param integer $day
     * @return type
     */
    private function isSummer($month, $day) {
        return $this->isSeason($month, $day, array(6, 9));
    }
    
    /**
     * Return true if season is winter
     * 
     * @param integer $month
     * @param integer $day
     * @return type
     */
    private function isAutumn($month, $day) {
        return $this->isSeason($month, $day, array(9, 12));
    }
    
    /**
     * Return true if month/day are inside magics bounds 
     * 
     * @param integer $month
     * @param integer $day
     * @return type
     */
    private function isSeason($month, $day, $magics) {
        if ($month > $magics[0] && $month < $magics[1]) {
            return true;
        }
        if ($month === $magics[0] && $day > 20) {
            return true;
        }
        if ($month === $magics[1] && $day < 21) {
            return true;
        }
        return false;
    }
    
    /**
     * Return true is query returns result.
     * 
     * @param string $query
     * @return boolean
     */
    private function hasResults($query) {
        $results = pg_fetch_all($this->query($query));
        if (empty($results)) {
            return false;
        }
        return true;
    }
}
