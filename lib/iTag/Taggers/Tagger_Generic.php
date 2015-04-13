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
     * Default features to tag
     */
    protected $defaultFeatures = array();
    
    /*
     * Return feature corresponding column name
     */
    protected $columnNames = array();
    
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

        if (!isset($options['features'])) {
            $options['features'] = $this->defaultFeatures;
        }
        
        /*
         * Process required classes
         */
        $result = array();
        foreach (array_values($options['features']) as $feature) {
            if (isset($this->columnNames[$feature])) {
                $content = $this->retrieveContent('datasources.' . $feature, $this->columnNames[$feature], $footprint);
                if (count($content) > 0) {
                    $result[$feature] = $content;
                }
            }
        }
        
        return $result;
    }

    /**
     * Retrieve content from table that intersects $footprint
     * 
     * @param {DatabaseConnection} $this->dbh
     * 
     */
    private function retrieveContent($tableName, $columnName, $footprint) {
        if ($this->config['returnGeometries']) {
            $query = 'SELECT distinct(' . $columnName . ') as name, ' . $this->postgisAsWKT($this->postgisSimplify($this->postgisIntersection('geom', $this->postgisGeomFromText($footprint)))) . ' as wkt FROM ' . $tableName . ' WHERE st_intersects(geom, ' . $this->postgisGeomFromText($footprint) . ')';
        }
        else {
            $query = 'SELECT distinct(' . $columnName . ') as name FROM ' . $tableName . ' WHERE st_intersects(geom, ' . $this->postgisGeomFromText($footprint) . ')';
        }
        $results = $this->query($query);
        $content = array();
        while ($result = pg_fetch_assoc($results)) {
            $content[] = $this->config['returnGeometries'] ? array(
                'name' => $result['name'],
                'geometry' => $result['wkt']
                    ) :
                    array(
                'name' => $result['name']);
        }
        return $content;
    }
    
}
