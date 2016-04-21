<?php
/*
 * Copyright 2014 Jérôme Gasperi
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
 * QueryAnalyzer Where
 * 
 * @param array $params
 */
require 'WhereExtractor.php';
class WhereProcessor {

    /*
     * Process result
     */
    private $result = array();
    
    /*
     * Reference to QueryManager
     */
    private $queryManager;
    
    /*
     * Reference to Where extractor
     */
    private $extractor;
    
    /**
     * Constructor
     * 
     * @param QueryManagerqueryManager
     * @param Gazetteer $gazetteer
     */
    public function __construct($queryManager, $gazetteer) {
        $this->queryManager = $queryManager;
        $this->gazetteer = $gazetteer;
        $this->extractor = new WhereExtractor($this->queryManager, $gazetteer);
    }
    
    /**
     * Return processing result
     * 
     * @return array
     */
    public function getResult() {
        return $this->result;
    }
    
    /**
     * 
     * Process <in> "location"
     * 
     * @param integer $startPosition of word in the list
     * @param integer $delta
     */
    public function processIn($startPosition, $delta = 1) {
        
        /*
         * Extract locations
         */
        $locations = $this->extractor->extractLocations($startPosition + $delta);
        
        /*
         * At least one location found - get the most relevant one and set the others within a 'SeeAlso' property
         */
        if (count($locations['location']['results']) > 0) {
            $this->result[] = $this->getMostRelevantLocation($locations['location']['results']);
        }
        
        /*
         * Nothing found - set specific error
         */
        else {
            $error = QueryAnalyzer::LOCATION_NOT_FOUND;
        }
        
        /*
         * In any case, set words as processed
         */
        $this->queryManager->discardPositionInterval(__METHOD__, $startPosition, $locations['endPosition'], isset($error) ? $error : null);
        
    }
    
    /**
     * Return most relevant location from a set of locations
     * 
     * Order of relevance is :
     * 
     *   - Continent
     *   - Country
     *   - Capitals (i.e. PPLC toponyms or PPLG)
     *   - First Administrative division (i.e. PPLA toponyms)
     *   - Region
     *   - State
     *   - Other toponyms
     * 
     * @param array $locations
     */
    private function getMostRelevantLocation($locations) {
        
        $bestPosition = 0;
        for ($i = 0, $ii = count($locations); $i < $ii; $i++) {
            if (!isset($locations[$i]['type'])) {
                continue;
            }
            if ($locations[$i]['type'] === 'continent') {
                $bestPosition = $i;
                break;
            }
            if ($locations[$i]['type'] === 'country') {
                $bestPosition = $i;
                break;
            }
            if ($locations[$i]['type'] === 'state' || $locations[$i]['type'] === 'region') {
                if (isset($locations[0]['fcode']) && $locations[0]['fcode'] !== 'PPLC' && $locations[0]['fcode'] !== 'PPLG' && $locations[0]['fcode'] !== 'PPLA') {
                    $bestPosition = $i;
                }
                break;
            }
        }
        
        $best = $locations[$bestPosition];
        array_splice($locations, $bestPosition, 1);
        return array_merge($best, array('SeeAlso' => $locations));
    }
    
}