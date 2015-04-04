<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2013 Jérôme Gasperi <https://github.com/jjrom>
 * 
 * jerome[dot]gasperi[at]gmail[dot]com
 * 
 * 
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
 * 
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
    public $result = array();
    
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
     *   - State
     *   - Other toponyms
     * 
     * @param array $locations
     */
    private function getMostRelevantLocation($locations) {
        
        $bestPosition = 0;
        for ($i = 0, $ii = count($locations); $i < $ii; $i++) {
            if ($locations[$i]['type'] === 'continent') {
                $bestPosition = $i;
                break;
            }
            if ($locations[$i]['type'] === 'country') {
                $bestPosition = $i;
                break;
            }
            if ($locations[$i]['type'] === 'state') {
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