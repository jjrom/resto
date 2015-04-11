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

class Tagger_Population extends Tagger {

    /*
     * Data references
     */
    public $references = array(
        array(
            'dataset' => 'Gridded Population of the World - 2015',
            'author' => 'SEDAC',
            'license' => 'Free of Charge',
            'url' => 'http://sedac.ciesin.columbia.edu/data/set/gpw-v3-population-count-future-estimates/data-download'
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
     * Return the estimated population for a given footprint
     *
     * @param string $footprint
     * @return integer
     */
    public function process($footprint) {
        $query = 'SELECT pcount FROM gpw.' . $this->getTableName($footprint) . ' WHERE ST_intersects(footprint, ST_GeomFromText(\'' . $footprint . '\', 4326))';
        $results = $this->query($query);
        $total = 0;
        while ($counts = pg_fetch_assoc($results)) {
            $total += $counts['pcount'];
        }
        return array(
            'population' => array(
                'count' => $total,
                'densityPerSquareKm' => $this->densityPerSquareKm($footprint, $total)
        ));
    }
    
    /**
     * Return table name dataset 
     * 
     * Dataset depends on the input polygon size
     * to avoid performance issues with large polygons
     * over the high resolution glp15ag table
     * 
     * @param array $footprint
     * @return string
     * 
     */
    private function getTableName($footprint) {

        $area = $this->getArea($footprint);
        if ($area > 0 && $area < 0.5) {
            $tablename = 'glp15ag';
        }
        else if ($area >= 0.5 && $area < 5) {
            $tablename = 'glp15ag15';
        }
        else if ($area >= 5 && $area < 10) {
            $tablename = 'glp15ag30';
        }
        else {
            $tablename = 'glp15ag60';
        }

        return $tablename;
    }

    /**
     * Return the density of people per square kilometer
     * 
     * @param string $footprint
     * @param integer $total
     */
    private function densityPerSquareKm($footprint, $total) {
        $query = 'SELECT ' . $this->postgisArea('ST_GeomFromText(\'' . $footprint . '\', 4326)') . ' as area';
        $result = pg_fetch_assoc($this->query($query));
        return $total / $this->toSquareKm($result['area']);
    }
}
