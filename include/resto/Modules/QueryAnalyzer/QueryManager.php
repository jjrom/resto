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
 * Query Manager for QueryAnalyzer module
 */
class QueryManager {

    /*
     * Words
     */
    public $words = array();
    
    /*
     * True if keywords are present
     */
    public $hasKeywords = false;
    
    /*
     * Query length
     */
    public $length;
    
    /*
     * Analysis error
     */
    public $errors = array();
    
    /*
     * Dictionary
     */
    public $dictionary;
    
    /*
     * Model
     */
    public $model;
    
    /**
     * Constructor
     * 
     * @param RestoDictionary $dictionary
     * @param RestoModel $model
     */
    public function __construct($dictionary, $model) {
        $this->dictionary = $dictionary;
        $this->model = isset($model) ? $model : new RestoModel_default();
    }
    
    /**
     * Set array of words
     * 
     * @param array $words
     */
    public function initialize($words) {
        for ($i = 0, $ii = count($words); $i < $ii; $i++) {
            if (strpos($words[$i], ':') !== false) {
                $this->hasKeywords = true;
            }
            $this->words[$i] = array(
                'word' => $words[$i],
                'processed' => false
            );
        }
        $this->length = count($this->words);
    }
    
    /**
     * Set position processed status to true
     * 
     * @param string $by
     * @param integer $position
     * @param string $error
     */
    public function discardPosition($by, $position, $error = null) {
        $this->words[$position]['processed'] = true;
        $this->words[$position]['by'] = $by;
        $this->words[$position]['error'] = $error;
    }
    
    /**
     * Add position to list of processed words positions
     * 
     * @param string $by
     * @param integer $startPosition
     * @param integer $endPosition
     * @param string $error
     */
    public function discardPositionInterval($by, $startPosition, $endPosition, $error = null) {
        if ($startPosition > $endPosition) {
            $this->discardPosition($by, $startPosition, $error);
        }
        else {
            for ($i = $startPosition; $i <= $endPosition; $i++) {
                $this->discardPosition($by, $i, $error);
            }
        }
        return true;
    }
    
    /**
     * Return true if position is valid i.e. word exist and is not yet processed
     * 
     * @param integer $position
     */
    public function isValidPosition($position) {
        if ($this->isNull($position)) {
            return false;
        }
        return !$this->words[$position]['processed'];
    }
    
    /**
     * Return true if word at position is 'and'
     * 
     * @param integer $position
     */
    public function isAndPosition($position) {
        if ($this->isNull($position)) {
            return false;
        }
        if ($this->dictionary->get(RestoDictionary::AND_MODIFIER, $this->words[$position]['word']) === 'and') {
            return true;
        }
        return false;
    }
     
    /**
     * Return true if word at position $position is a modifier
     * 
     * @param integer $position
     * @return boolean
     */
    public function isModifierPosition($position) {
        if ($this->isNull($position)) {
            return false;
        }
        return $this->dictionary->isModifier($this->words[$position]['word']);
    }
    
    /**
     * Return true if word at position $position is a stop word
     * 
     * @param integer $position
     * @return boolean
     */
    public function isStopWordPosition($position) {
        if ($this->isNull($position)) {
            return false;
        }
        return $this->dictionary->isStopWord($this->words[$position]['word']);
    }
    
    /**
     * Return location keyword
     * 
     * @param string $name
     * @return array
     */
    public function getLocationKeyword($name) {
        return $this->dictionary->getKeyword($name, array(
                    RestoDictionary::CONTINENT,
                    RestoDictionary::COUNTRY,
                    RestoDictionary::REGION,
                    RestoDictionary::STATE,
                    RestoDictionary::BAY,
                    RestoDictionary::CHANNEL,
                    RestoDictionary::FJORD,
                    RestoDictionary::GULF,
                    RestoDictionary::INLET,
                    RestoDictionary::LAGOON,
                    RestoDictionary::OCEAN,
                    RestoDictionary::REEF,
                    RestoDictionary::RIVER,
                    RestoDictionary::SEA,
                    RestoDictionary::SOUND,
                    RestoDictionary::STRAIT
        ));
    }
    
    /**
     * Return non location keyword
     * 
     * @param string $name
     * @return array
     */
    public function getNonLocationKeyword($name) {
        return $this->dictionary->getKeyword($name, array(
            RestoDictionary::NOLOCATION
        ));
    }
    
    /**
     * Get the last non processed word position of the query before a modifier
     * 
     * @param integer $position
     */
    public function getEndPosition($position) {
        $endPosition = $position;
        for ($i = $position; $i < $this->length; $i++) {
            if ($this->words[$i]['processed'] || $this->isModifierPosition($i)) {
                $endPosition = $i - 1;
                break;
            }
            $endPosition = $i;
        }
        return $endPosition;
    }
   
    /**
     * Return true if position is outside of $words array
     * 
     * @param type $position
     */
    private function isNull($position) {
        if ($position < 0 || $position > $this->length - 1) {
            return true;
        }
        return false;
    }
    
}