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
 * QueryAnalyser Where
 * 
 * @param array $params
 */
class WhereProcessor {

    /*
     * Process result
     */
    public $result = array();
    
    /*
     * Reference to QueryAnalyser
     */
    private $queryAnalyser;
    
    /*
     * Reference to gazetteer object
     */
    private $gazetteer;
    
    /**
     * Constructor
     * 
     * @param QueryAnalyser $queryAnalyser
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($queryAnalyser, $context, $user) {
        $this->queryAnalyser = $queryAnalyser;
        $this->context = $context;
        $this->user = $user;
        if (isset($this->context->modules['Gazetteer'])) {
            $this->gazetteer = new Gazetteer($this->context, $this->user, $this->context->modules['Gazetteer']);
        }
    }
    
    /**
     * 
     * Process <in> "location"
     * 
     * @param array $words
     * @param integer $position of word in the list
     * @param array $options
     */
    public function processIn($words, $position, $options = array('delta' => 1, 'nullIfNotFound' => false)) {
        
        /*
         * Extract locations
         */
        $location = $this->extractLocation($words, $position + $options['delta']);
        
        if (count($location['location']['results']) > 0) {
            $this->result[] = $this->getMostRelevantLocation($location['location']['results']);
        }
        else {
            if ($options['nullIfNotFound']) {
                return null;
            }
            $this->queryAnalyser->error(QueryAnalyser::LOCATION_NOT_FOUND, $location['location']['query']);
        }
        
        array_splice($words, $position, $location['endPosition'] - $position + 1);
       
        return $words;
       
    }
    
    /**
     * Return most relevant location from a set of locations
     * 
     * Order of relevance is :
     * 
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
    
    /**
     * 
     * Extract location from sentence
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function extractLocation($words, $position) {
        
        /*
         * Get the last index position
         */
        $endPosition = $this->queryAnalyser->getEndPosition($words, $position);
        
        /*
         * Search location modifier
         * Location modifier is a country or a state
         */
        $locationModifiers = array();
        $stopWordPosition = -1;
        $length = 0;
        
        for ($i = $position; $i <= $endPosition; $i++) {
            
            /*
             * Search for a location modifier
             */
            $locationModifier = $this->getLocationModifier($words, $i + $length, $endPosition, $stopWordPosition);
            
            /*
             * Eventually get the position of the last stopWord
             */
            if ($this->queryAnalyser->dictionary->isStopWord($words[$i])) {
               $stopWordPosition = $i;
            } 
            
            /*
             * Break if location modifier was found
             */
            if (isset($locationModifier)) {
                $locationModifiers[] = $locationModifier;
                $length = $locationModifier['endPosition'] - $locationModifier['startPosition'];
            }
            else {
                $length = 0;
            }
            
        }
        
        /*
         * If multiple location modifiers found, get the last one
         */
        $locationModifier = null;
        if (count($locationModifiers) > 0) {
            $locationModifier = end($locationModifiers);
            $endPosition = $locationModifier['endPosition'];
        }
        
        /*
         * Search toponym in gazetteer
         */
        if (isset($this->gazetteer)) {
            return $this->extractToponym($words, $position, $locationModifier);
        }
        /*
         * Return location modifier
         */
        else {
            return array(
                'endPosition' => $endPosition,
                'location' => $locationModifier
            );
        }
        
    }
    
    /**
     * 
     * Extract toponym
     * 
     * @param array $words
     * @param integer $position of word in the list
     * @param array $locationModifier
     */
    private function extractToponym($words, $position, $locationModifier = null) {
        
        /*
         * Reconstruct sentence from words
         */
        $toponymName = '';
        $endPosition = isset($locationModifier) ? $locationModifier['endPosition'] : count($words);
        for ($i = $position, $ii = $endPosition; $i < $ii; $i++) {
            
            $endPosition = $i;
            
            /*
             * Exit if stop modifier is found
             */
            if ($this->queryAnalyser->dictionary->isModifier($words[$i])) {
                $endPosition = $i - 1;
                break;
            }
            
            /*
             * Discard locationModifier
             */
            if (isset($locationModifier)) {
                if ($i >= $locationModifier['startPosition'] && $i <= $locationModifier['endPosition']) {
                    continue;
                }
            }
            
            $toponymName .= ($toponymName === '' ? '' : '-') . $words[$i];
            
        }
        
        /*
         * Search modifier only
         */
        if (isset($locationModifier)) {
            if (empty($toponymName) || $this->queryAnalyser->dictionary->isStopWord($toponymName)) {
                return array(
                    'endPosition' => $locationModifier['endPosition'],
                    'location' => $this->gazetteer->search(array(
                        'q' => $locationModifier['keyword'],
                        'wkt' => true
                    ))
                );
            }
            $modifier = $locationModifier['keyword'];
            $endPosition = max(array($endPosition, $locationModifier['endPosition']));
        }
        else {
            $modifier = '';
        }
        return $this->getToponymFromTuples($toponymName, $modifier, $endPosition);
        
    }
        
    /**
     * Return location modifier (i.e. country or state- from words array
     * 
     * If input words are array('the', 'united', 'states')
     * 
     * Then keyword will be tested against : 
     *  the, the-united, the-united-states
     * 
     * @param array $words
     * @param integer $startPosition
     * @param integer $endPosition
     * @param integer $stopWordPosition
     * @return array
     */
    private function getLocationModifier($words, $startPosition, $endPosition, $stopWordPosition = -1) {
        $locationName = '';
        for ($i = $startPosition; $i <= $endPosition; $i++) {
            
            /*
             * Reconstruct sentence from words without stop words
             */
            $locationName .= ($locationName === '' ? '' : '-') . $words[$i];
            $keyword = $this->queryAnalyser->dictionary->getKeyword(RestoDictionary::LOCATION, $locationName);
            if (isset($keyword)) {
                return array(
                    'startPosition' => $stopWordPosition === -1 ? $startPosition : $stopWordPosition,
                    'endPosition' => $i,
                    'keyword' => $locationName,
                    'type' => $keyword['type']
                );
            }

        }
        return null;
    }
    
    /**
     * 
     * @param string $toponymName
     * @param string $modifier
     * @param integer $endPosition
     * @return type
     */
    private function getToponymFromTuples($toponymName, $modifier, $endPosition) {
        
        
        /*
         * Search for toponym
         */
        $discarded = array();
        while(true) {
            
            $location = $this->gazetteer->search(array(
                'q' => $toponymName . ($modifier !== '' ? ',' . $modifier : ''),
                'wkt' => true
            ));
            
            /*
             * Location was found or toponym name has only one word left
             */
            $pos = strrpos($toponymName, '-');
            if ($pos === false || count($location['results']) > 0) {
                break;
            }
            
            /*
             * Remove last word
             */
            $discarded[] = substr($toponymName, $pos + 1, strlen($toponymName));
            $toponymName = substr($toponymName, 0, $pos);
            
        }
        
        if (count($discarded) > 0) {
            $this->queryAnalyser->error(QueryAnalyser::NOT_UNDERSTOOD, join(' ', $discarded));
        }
        
        return array(
            'endPosition' => $endPosition,
            'location' => $location
        );
        
    }
    
}