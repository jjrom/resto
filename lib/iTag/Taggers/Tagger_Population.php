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
        parent::tag($metadata, $options);
        return $this->process($metadata['footprint']);
    }
    
    /**
     * Return the estimated population for a given footprint
     *
     * @param string $footprint
     * @return integer
     */
    public function process($footprint) {
        $prequery = 'WITH prequery AS (SELECT ' . $this->postgisGeomFromText($footprint) . ' AS corrected_geometry)';
        $query = $prequery . ' SELECT pcount FROM prequery, gpw.' . $this->getTableName() . ' WHERE ST_intersects(footprint, corrected_geometry)';
        $results = $this->query($query);
        $total = 0;
        while ($counts = pg_fetch_assoc($results)) {
            $total += $counts['pcount'];
        }
        return array(
            'population' => array(
                'count' => $total,
                'densityPerSquareKm' => $this->densityPerSquareKm($total)
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
    private function getTableName() {
        if ($this->area > 0 && $this->area < 6000) {
            $tablename = 'glp15ag';
        }
        else if ($this->area >= 6000 && $this->area < 60000) {
            $tablename = 'glp15ag15';
        }
        else if ($this->area >= 60000 && $this->area < 120000) {
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
     * @param integer $total
     */
    private function densityPerSquareKm($total) {
        return $total / $this->area;
    }
}
