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
 * Extractor for QueryAnalyzer Where processor
 * 
 * @param array $params
 */
class WhereExtractor {

    /*
     * Reference to QueryManager
     */
    private $queryManager;
    
    /*
     * Reference to gazetteer object
     */
    private $gazetteer;
    
    /**
     * Constructor
     * 
     * @param QueryManager $queryManager
     * @param Gazetteer $gazetteer
     */
    public function __construct($queryManager, $gazetteer) {
        $this->queryManager = $queryManager;
        $this->gazetteer = $gazetteer;
    }
    
    /**
     * 
     * Extract locations from sentence
     * 
     * @param integer $startPosition of word in the list
     */
    public function extractLocations($startPosition) {
        
        /*
         * Get location modifier i.e. continent, country or state
         */
        $locationModifiers = $this->extractLocationModifiers($startPosition, $this->queryManager->getEndPosition($startPosition));
        
        /*
         * If multiple location modifiers found, get the last one
         */
        $locationModifier = null;
        if (count($locationModifiers) > 0) {
            $locationModifier = end($locationModifiers);
        }
        
        /*
         * Search toponym in gazetteer
         */
        return $this->extractToponym($startPosition, $locationModifier);
        
    }
    
    /**
     * 
     * Extract modifiers i.e. continent, country or state
     * 
     * @param integer $startPosition of word in the list
     * @param integer $endPosition last word to test
     */
    private function extractLocationModifiers($startPosition, $endPosition) {
        
        $locationModifiers = array();
        $length = 0;
        for ($i = $startPosition; $i <= $endPosition; $i++) {
            
            /*
             * Search for a location modifier
             */
            $locationModifier = $this->getLocationModifier($i + $length, $endPosition);
            
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
        
        return $locationModifiers;
        
    }
    
    /**
     * 
     * Extract toponym
     * 
     * @param integer $startPosition of word in the list
     * @param array $locationModifier
     */
    private function extractToponym($startPosition, $locationModifier) {
        if (isset($locationModifier)) {
            return $this->extractToponymWithModifier($startPosition, $locationModifier);
        }
        else {
            return $this->extractToponymWithoutModifier($startPosition);
        }
    }
    
    /**
     * 
     * Extract toponym with modifier
     * 
     * @param integer $startPosition of word in the list
     * @param array $locationModifier
     */
    private function extractToponymWithModifier($startPosition, $locationModifier) {
        $toponymName = $this->toToponymName($startPosition, $locationModifier['startPosition'] - 1);
        if (empty($toponymName) || $this->queryManager->dictionary->isStopWord($toponymName)) {
            return array(
                'endPosition' => $locationModifier['endPosition'],
                'location' => $this->gazetteer->search(array(
                    'q' => $locationModifier['keyword'],
                    'wkt' => true
                ))
            );
        }
        return array(
            'endPosition' => $locationModifier['endPosition'],
            'location' => $this->gazetteer->search(array(
                'q' => $toponymName . ',' . $locationModifier['keyword'],
                'wkt' => true
            ))
        );
    }
     
    /**
     * 
     * Extract toponym without modifier
     * 
     * @param integer $startPosition of word in the list
     * 
     */
    private function extractToponymWithoutModifier($startPosition) {
        
        $endPosition = $this->queryManager->getEndPosition($startPosition);
        $toponymName = $this->toToponymName($startPosition, $endPosition);
        while(true) {
            
            $location = $this->gazetteer->search(array(
                'q' => $toponymName,
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
            $toponymName = substr($toponymName, 0, $pos);
            $endPosition--;
        }
        
        return array(
            'endPosition' => $endPosition,
            'location' => $location
        );
        
    }
    
    /**
     * Return location modifier (i.e. country or state- from words array
     * 
     * If input words are array('the', 'united', 'states')
     * 
     * Then keyword will be tested against : 
     *  the, the-united, the-united-states
     * 
     * @param integer $startPosition
     * @param integer $endPosition
     * @return array
     */
    private function getLocationModifier($startPosition, $endPosition) {
        
        /*
         * Reconstruct sentence from words without stop words
         */
        $locationName = '';
        for ($i = $startPosition; $i <= $endPosition; $i++) {    
            $locationName .= ($locationName === '' ? '' : '-') . $this->queryManager->words[$i]['word'];
            $keyword = $this->queryManager->getLocationKeyword($locationName);
            if (isset($keyword)) {
                return array(
                    'startPosition' => $this->queryManager->isStopWordPosition($startPosition - 1) ? $startPosition - 1 : $startPosition,
                    'endPosition' => $i,
                    'keyword' => $locationName,
                    'type' => $keyword['type']
                );
            }
        }
        return null;
    }
    
    /**
     * Merge words with '-' separator from $startPosition to $endPosition included
     * 
     * @param integer $startPosition
     * @param integer $endPosition
     */
    private function toToponymName($startPosition, $endPosition) {
        $toponymName = '';
        for ($i = $startPosition; $i <= $endPosition; $i++) {
            $toponymName .= ($toponymName === '' ? '' : '-') . $this->queryManager->words[$i]['word'];
        }
        return $toponymName;
    }
    
}