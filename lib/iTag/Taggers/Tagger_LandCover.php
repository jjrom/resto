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

class Tagger_LandCover extends Tagger {

    /*
     * Data references
     */
    public $references = array(
        array(
            'dataset' => 'Global Land Cover 2000',
            'author' => 'JRC',
            'license' => 'Free of Charge for non-commercial use',
            'url' => 'http://bioval.jrc.ec.europa.eu/products/glc2000/data_access.php'
        )
    );
    
    /*
     * Corine Land Cover
     */
    private $clcClassNames = array(
        100 => 'Urban',
        200 => 'Cultivated',
        310 => 'Forest',
        320 => 'Herbaceous',
        330 => 'Desert',
        335 => 'Ice',
        400 => 'Flooded',
        500 => 'Water'
    );
    
    /*
     * Global Land Cover class names
     */
    private $glcClassNames = array(
        1 => 'Tree Cover, broadleaved, evergreen',
        2 => 'Tree Cover, broadleaved, deciduous, closed',
        3 => 'Tree Cover, broadleaved, deciduous, open',
        4 => 'Tree Cover, needle-leaved, evergreen',
        5 => 'Tree Cover, needle-leaved, deciduous',
        6 => 'Tree Cover, mixed leaf type',
        7 => 'Tree Cover, regularly flooded, fresh  water',
        8 => 'Tree Cover, regularly flooded, saline water',
        9 => 'Mosaic - Tree cover / Other natural vegetation',
        10 => 'Tree Cover, burnt',
        11 => 'Shrub Cover, closed-open, evergreen',
        12 => 'Shrub Cover, closed-open, deciduous',
        13 => 'Herbaceous Cover, closed-open',
        14 => 'Sparse Herbaceous or sparse Shrub Cover',
        15 => 'Regularly flooded Shrub and/or Herbaceous Cover',
        16 => 'Cultivated and managed areas',
        17 => 'Mosaic - Cropland / Tree Cover / Other natural vegetation',
        18 => 'Mosaic - Cropland / Shrub or Grass Cover',
        19 => 'Bare Areas',
        20 => 'Water Bodies',
        21 => 'Snow and Ice',
        22 => 'Artificial surfaces and associated areas'
    );
    
    /*
     * Corine Land Cover - Global Land Cover linkage
     */
    private $linkage = array(
        100 => array(22), // Urban
        200 => array(15, 16, 17, 18), // Cultivated
        310 => array(1, 2, 3, 4, 5, 6), // Forest
        320 => array(9, 11, 12, 13), // Herbaceous
        330 => array(10, 14, 19), // Desert
        335 => array(21), // Ice
        400 => array(7, 8), // Flooded
        500 => array(20) // Water
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
     * Tag metadata
     * 
     * @param array $metadata
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function tag($metadata, $options = array()) {
        return $this->process($metadata['footprint']);
    }
    
    /**
     * 
     * Compute land cover from input WKT footprint
     * 
     * @param string $footprint
     * 
     */
    private function process($footprint) {
        
        /*
         * Do not process if footprint area is greater
         * than the maximum area allowed
         */
        if (!$this->isValidArea($footprint)) {
            return array(
                'landCover' => array()
            );
        }

        /*
         * Get raw landcover
         */
        $rawLandCover = $this->retrieveRawLandCover($footprint);
        
        /*
         * Return full land use description
         */
        return array(
            'landCover' => array(
                'area' => $this->toSquareKm($rawLandCover['area']),
                'landUse' => $this->getLandUse($rawLandCover),
                'landUseDetails' => $this->getLandUseDetails($rawLandCover)
        ));
    }

    /**
     * Returns main land use
     * 
     * @param array $rawLandCover
     */
    private function getLandUse($rawLandCover) {
        
        $sums = array();
        foreach ($this->linkage as $key => $value) {
            $sums[$key] = $this->sum($rawLandCover['classes'], $value);
        }
        arsort($sums);
        
        $landUse = array();
        foreach ($sums as $key => $val) {
            $pcover = $this->percentage($val, $rawLandCover['area']);
            if ($val !== 0 && $pcover > 10) {
                $name = isset($this->clcClassNames[$key]) ? $this->clcClassNames[$key] : 'unknown';
                array_push($landUse, array(
                    'name' => $name,
                    'id' => 'landuse:' . strtolower($name),
                    'area' => $this->toSquareKm($val),
                    'pcover' => $pcover
                ));
            }
        }
        
        return $landUse;

    }
    
    /**
     * Returns land use details
     * 
     * @param array $rawLandCover
     */
    private function getLandUseDetails($rawLandCover) {
        $landUseDetails = array();
        foreach ($rawLandCover['classes'] as $key => $val) {
            if ($val !== 0) {
                $name = isset($this->glcClassNames[$key]) ? $this->glcClassNames[$key] : 'unknown';
                array_push($landUseDetails, array(
                    'name' => $name,
                    'id' => 'landuse_details:' . strtolower(str_replace(array('/', ',', ' '), '-', $name)),
                    'parentId' => 'landuse:' . strtolower($this->getCLCParent($key)),
                    'code' => $key,
                    'area' => $this->toSquareKm($val),
                    'pcover' => $this->percentage($val, $rawLandCover['area'])
                ));
            }
        }
        return $landUseDetails;
    }
    
    /**
     * Retrieve landcover from iTag database
     * 
     * @param string $footprint
     * @return array
     */
    private function retrieveRawLandCover($footprint) {
        
        $classes = array();
        
        $geom = 'ST_GeomFromText(\'' . $footprint . '\', 4326)';
        $query = 'SELECT dn as dn, ' . $this->postgisArea($geom) . ' as totalarea, ' . $this->postgisArea('st_intersection(wkb_geometry, ' . $geom . ')') . ' as area FROM datasources.landcover WHERE st_intersects(wkb_geometry, ' . $geom . ')';
        
        $results = $this->query($query);
        $totalarea = 0;
        while ($result = pg_fetch_assoc($results)) {
            if (isset($classes[$result['dn']])) {
                $classes[$result['dn']] += $result['area'];
            }
            else {
                $classes[$result['dn']] = $result['area'];
            }
            $totalarea = $result['totalarea'];
        }
        
        return array(
            'area' => $totalarea,
            'classes' => $classes
        );
    }
    
    /**
     * Return the sum of classes[$keys] values
     * 
     * @param array $classes
     * @param array $keys
     */
    private function sum($classes, $keys) {
        $sum = 0;
        for ($i = count($keys); $i--;) {
            if (isset($classes[$keys[$i]])) {
                $sum += $classes[$keys[$i]];
            }
        }
        return $sum;
    }

    /**
     * Return CLC class name from child GLC $code
     * 
     * @param integer $code
     */
    private function getCLCParent($code) {
        foreach ($this->linkage as $key => $value) {
            if (in_array($code, $value) && isset($this->clcClassNames[$key])) {
                return $this->clcClassNames[$key];
            }
        }
        return 'unknown';
    }
}
