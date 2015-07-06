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

class Tagger_Generic extends Tagger {

    /*
     * Columns mapping per table
     */
    protected $columnsMapping = array();
    
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
        return $this->process($metadata['footprint'], $options);
    }
    
    /**
     * Compute intersected information from input WKT footprint
     * 
     * @param string footprint
     * @param array $options
     * 
     */
    protected function process($footprint, $options) {

        $result = array();
        
        /*
         * Process required classes
         */
        foreach ($this->columnsMapping as $tableName => $mapping) {
            $content = $this->retrieveContent('datasources.' . $tableName, $mapping, $footprint, $options);
            if (count($content) > 0) {
                $result[$tableName] = $content;
            }
        }
        
        return $result;
    }

    /**
     * Retrieve content from table that intersects $footprint
     * 
     * @param String $tableName
     * @param Array $mapping
     * @param String $footprint
     * @param Array $options
     * 
     */
    private function retrieveContent($tableName, $mapping, $footprint, $options = array()) {
        
        /*
         * Return WKT if specified in config file
         */
        if ($this->config['returnGeometries']) {
            $mapping['geom'] = 'geometry';
        }
        
        $content = array();
        $results = $this->getResults($tableName, $mapping, $footprint, $options);
        while ($result = pg_fetch_assoc($results)) {
            
            /*
             * Compute id from normalized
             */
            if (isset($result['type'])) {
                $result['id'] = strtolower($result['type']) . ':' . $result['normalized'];
            }
            if (isset($result['area'])) {
                $result['pcover'] = $this->percentage($this->toSquareKm($result['area']), $this->area);
            }
            unset($result['area'], $result['normalized'], $result['type']);
            $content[] = $result;
        }
        return $content;
    }
    
    /**
     * Return structured results from database
     * 
     * @param String $tableName
     * @param Array $mapping
     * @param String $footprint
     * @param Array $options
     */
    private function getResults($tableName, $mapping, $footprint, $options) {
  
        $propertyList = array();
        $geom = $this->postgisGeomFromText($footprint);
        $orderBy = '';
        foreach ($mapping as $asName => $columnName) {
            if ($asName === 'name') {
                $propertyList[] = 'distinct(' . $columnName . ') as name';
                $propertyList[] = 'normalize(' . $columnName . ') as normalized';
            }
            else if ($asName === 'geometry') {
                $propertyList[] = $this->postgisAsWKT($this->postgisSimplify($this->postgisIntersection('geom', $geom))) . ' as geometry';
            }
            else {
                $propertyList[] = $columnName . ' as ' . $asName;
            }
        }
        
        /*
         * Return area
         */
        if (isset($options['computeArea']) && $options['computeArea'] === true) {
            $propertyList[] = $this->postgisArea($this->postgisIntersection('geom', $geom)) . ' as area';
            $orderBy = ' ORDER BY area DESC';
        }
        
        return $this->query('SELECT ' . join(',', $propertyList) .  ' FROM ' . $tableName . ' WHERE st_intersects(geom, ' . $this->postgisGeomFromText($footprint) . ')' . $orderBy);
        
    }
    
}
