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
 * QueryAnalyzer What
 * 
 * @param array $params
 */
class WhatProcessor {

    const EQUAL = 0;
    const GREATER = 1;
    const LESSER = 2;
    
    /*
     * Process result
     */
    public $result = array();
    
    /*
     * Reference to QueryManager
     */
    private $queryManager;
    
    /**
     * Constructor
     * 
     * @param QueryManager $queryManager
     */
    public function __construct($queryManager) {
        $this->queryManager = $queryManager;
    }
    
    /**
     * Process <for> "keyword" 
     * 
     * @param integer $startPosition
     * @param integer $delta
     * @param boolean $with
     * 
     */
    public function processFor($startPosition, $delta = 1, $with = true, $by = __METHOD__) {
        $keyword = $this->extractKeyword($startPosition + $delta);
        if (isset($keyword)) {
            $this->addToResult($this->toFilter($keyword, $with));
            $this->queryManager->discardPositionInterval($by, $startPosition, $keyword['endPosition']);
        }

    }
    
    /**
     * Process <with> "quantity" 
     * 
     * @param array $startPosition
     * 
     */
    public function processWith($startPosition, $delta = 1) {
        $this->processWithOrWithout($startPosition, true, $delta);
    }
    
    /**
     * Process <without> "quantity" 
     * 
     * @param integer $startPosition
     * 
     */
    public function processWithout($startPosition) {
        $this->processWithOrWithout($startPosition, false);
    }
    
    /**
     * Process <between>
     * 
     *  "quantity" <between> "numeric" <and> "numeric" "unit"
     *  <between> "numeric" <and> "numeric" "unit" (of) "quantity"
     * 
     * @param integer $startPosition of word in the list
     */
    public function processBetween($startPosition) {
        
        /*
         * To be valid at least 3 words are mandatory after <between> and second word must be <and>
         */
        if (!$this->queryManager->isValidPosition($startPosition + 3) || !$this->queryManager->isAndPosition($startPosition + 2)) {
            //TODO return $this->queryManager->whenProcessor->processBetween($words, $position);
            return null;
        }
        
        /*
         * Words in 1st and 3rd position after <between> must be numeric values
         * Word in 2nd position after <between> must be a valid unit
         * Otherwise try to process <between> with WhenProcessor
         */
        $values = array(
            $this->queryManager->dictionary->getNumber($this->queryManager->words[$startPosition + 1]['word']),
            $this->queryManager->dictionary->getNumber($this->queryManager->words[$startPosition + 3]['word'])
        );
        
        if (!isset($values[0]) || !isset($values[1]) || ($this->queryManager->isValidPosition($startPosition + 4) && $this->queryManager->dictionary->get(RestoDictionary::MONTH, $this->queryManager->words[$startPosition + 4]['word']))) {
            //TODO return $this->queryManager->whenProcessor->processBetween($words, $position);
            return null;
        }
        
        /*
         * Process differs if unit is specified or not
         */
        if ($this->queryManager->isValidPosition($startPosition + 4)) {
            $unit = $this->extractUnit($startPosition + 4);
        }
        
        isset($unit) ? $this->processValidBetweenWithUnit($startPosition, $values, $unit) : $this->processValidBetweenWithoutUnit($startPosition, $values);
        
    }
    
    /**
     * Process <equal>
     * 
     *      "quantity" <equal> (to) "numeric" "unit"
     *      <equal> (to) "numeric" "unit" (of) "quantity"
     * 
     * @param integer $startPosition of word in the list
     */
    public function processEqual($startPosition) {
        $this->processEqualOrGreaterOrLesser($startPosition, WhatProcessor::EQUAL);
    }
    
    /**
     * Process <greater>
     * 
     *      "quantity" <greater> (to) "numeric" "unit"
     *      <greater> (to) "numeric" "unit" (of) "quantity"
     * 
     * @param integer $startPosition of word in the list
     */
    public function processGreater($startPosition) {
        $this->processEqualOrGreaterOrLesser($startPosition, WhatProcessor::GREATER);
    }
    
    /**
     * Process <lesser>
     * 
     *      "quantity" <lesser> (to) "numeric" "unit"
     *      <lesser> (to) "numeric" "unit" (of) "quantity"
     * 
     * @param integer $startPosition of word in the list
     */
    public function processLesser($startPosition) {
        $this->processEqualOrGreaterOrLesser($startPosition, WhatProcessor::LESSER);
    }
    
    /**
     * Process <equal> or <greater> or <lesser>
     * 
     *      "quantity" <xxx> (to) "numeric" "unit"
     *      <xxx> (to) "numeric" "unit" (of) "quantity"
     * 
     * @param integer $startPosition of word in the list
     * @param integer $operator
     */
    private function processEqualOrGreaterOrLesser($startPosition, $operator) {
        
        /*
         * <equal>, <greater> or <lesser>
         */
        $valuedUnitQuantity = $this->getValuedUnitQuantity($startPosition);
        if (isset($valuedUnitQuantity)) {
            if (isset($valuedUnitQuantity['valuedUnit'])) {
                $value = (floatval($valuedUnitQuantity['valuedUnit']['value']) * $valuedUnitQuantity['valuedUnit']['unit']['factor']);
                switch ($operator) {
                    case WhatProcessor::EQUAL:
                        $this->addToResult(array($valuedUnitQuantity['quantity']['key'] => $value));
                        break;
                    case WhatProcessor::GREATER:
                        $this->addToResult(array($valuedUnitQuantity['quantity']['key'] => ']' .$value));
                        break;
                    case WhatProcessor::LESSER:
                        $this->addToResult(array($valuedUnitQuantity['quantity']['key'] => $value . '['));
                        break;
                }
            }
            $this->queryManager->discardPositionInterval(__METHOD__, $valuedUnitQuantity['startPosition'], $valuedUnitQuantity['endPosition'], isset($valuedUnitQuantity['error']) ? $valuedUnitQuantity['error'] : null);
        }
        
    }
    
    /**
     * Process <with> or <without> "quantity" 
     * 
     * @param integer $startPosition
     * @param boolean $with
     * @param integer $delta
     * 
     */
    private function processWithOrWithout($startPosition, $with, $delta = 1) {
        
        /*
         * <with/without> nothing
         */
        if (!isset($this->queryManager->words[$startPosition + $delta])) {
            $this->queryManager->discardPosition(__METHOD__, $startPosition, QueryAnalyzer::MISSING_ARGUMENT);
        }
        
        /*
         * <with> "quantity" means quantity
         * <without> "quantity" means quantity = 0
         */
        else {
            $this->processQuantityOrKeyword($startPosition, $with, $delta);
        }
        
    }
    
    /**
     * Process keyword or quantity
     * 
     * @param integer $startPosition
     * @param boolean $with
     * @param integer $delta
     */
    private function processQuantityOrKeyword($startPosition, $with, $delta) {
        
        /*
         * Quantity ?
         */
        $quantity = $this->extractQuantity($startPosition + $delta, $this->queryManager->getEndPosition($startPosition + $delta));
        if (isset($quantity)) {
            $this->addToResult(array(
                $quantity['key'] => $with ? ']0' : 0
            ));
            $this->queryManager->discardPositionInterval(__METHOD__, $startPosition, $quantity['endPosition']);
        }

        /*
         * Keyword ?
         */
        else {
            $this->processFor($startPosition, $delta, $with, __METHOD__);
        }
        
    }   
    
    /**
     * Process <between> ... <and> ... on quantity with unit
     * 
     * @param integer $betweenPosition
     * @param array $values
     * @param array $normalizedUnit 
     * @return type
     */
    private function processValidBetweenWithUnit($betweenPosition, $values, $normalizedUnit) {
        
        /*
         * 
         * "quantity" <between> (...)
         */
        $quantity = $this->extractQuantity(0, $betweenPosition - 1, true);
        
        /*
         * <between> ... "unit" "quantity"
         */
        if (!isset($quantity)) {
            $quantity = $this->extractQuantity($normalizedUnit['endPosition'] + 1, $this->queryManager->length);
        }
        
        /*
         * Quantity was found 
         */
        if (isset($quantity)) {
            
            if ($normalizedUnit['unit']['unit'] === $quantity['unit']) {
                $this->addToResult(array(
                    $quantity['key'] => '[' . (floatval($values[0]) * $normalizedUnit['unit']['factor']) . ',' . (floatval($values[1]) * $normalizedUnit['unit']['factor']) . ']'
                ));
            }
            else {
                $error = QueryAnalyzer::INVALID_UNIT;
            }
            
            $this->queryManager->discardPositionInterval(__METHOD__, min(array($betweenPosition, $quantity['startPosition'])), max(array($normalizedUnit['endPosition'] + 1, $quantity['endPosition'])), isset($error) ? $error : null);
        }
        
        
    }
    
    /**
     * Process a valid <between> ... <and> ... with unit
     * 
     * @param integer $betweenPosition
     * @param array $values
     * @return type
     */
    private function processValidBetweenWithoutUnit($betweenPosition, $values) {
        
        $quantityPosition = $this->queryManager->isValidPosition($betweenPosition + 4) ? $betweenPosition + 4 : $betweenPosition + 3;
        $endPosition = $this->queryManager->getEndPosition($quantityPosition);
        $startPosition = min($quantityPosition, $endPosition);
        
        /*
         * 
         * "quantity" <between> (...)
         */
        $quantity = $this->extractQuantity(0, $betweenPosition - 1, true);
        
        /*
         * <between> ... "unit" "quantity"
         */
        if (!isset($quantity)) {
            $quantity = $this->extractQuantity($startPosition, $this->queryManager->length);
        }
        
        /*
         * Quantity was found 
         */
        if (isset($quantity)) {
            
            if (!isset($quantity['unit'])) {
                $this->addToResult(array(
                    $quantity['key'] => '[' . $values[0] . ',' . $values[1] . ']'
                ));
            }
            else {
                $error = QueryAnalyzer::MISSING_UNIT;
            }
            
            $this->queryManager->discardPositionInterval(__METHOD__, min(array($betweenPosition, $quantity['startPosition'])), max(array($startPosition, $quantity['endPosition'])), isset($error) ? $error : null);
        
        }
        
    }
    
    /**
     * Extract quantity
     * 
     * @param integer $startPosition
     * @param array $endPosition
     */
    private function extractQuantity($startPosition, $endPosition) {
        
        if ($startPosition > $endPosition) {
            return null;
        }
        
        /*
         * Process words within $startPosition and $endPosition
         */
        $word = '';
        for ($i = $startPosition; $i <= $endPosition; $i++) {

            /*
             * Reconstruct word from words without stop words
             */
            if ($this->queryManager->isValidPosition($i) && !$this->queryManager->isStopWordPosition($i)) {
                $word = trim($word . ' ' . $this->queryManager->words[$i]['word']);
            }
            
            $quantity = $this->getQuantity($word);
            if (isset($quantity)) {
                return array_merge($quantity, array(
                    'startPosition' => $startPosition,
                    'endPosition' => $i
                ));
            }
            
        }
        return null;
    }
    
    /**
     * Extract keyword
     * 
     * @param integer $startPosition
     */
    private function extractKeyword($startPosition) {
     
        $endPosition = $this->queryManager->getEndPosition($startPosition);
        
        /*
         * Process words within $startPosition and $endPosition
         */
        $word = '';
        for ($i = $startPosition; $i <= $endPosition; $i++) {

            /*
             * Reconstruct word from words without stop words
             */
            if (!$this->queryManager->isStopWordPosition($i)) {
                $word = trim($word . ' ' . $this->queryManager->words[$i]['word']);
            }

            $keyword = $this->queryManager->getNonLocationKeyword($word);
            if (isset($keyword)) {
                return array(
                    'startPosition' => $startPosition,
                    'endPosition' => $i,
                    'keyword' => $keyword['keyword'],
                    'type' => $keyword['type']
                );
            }
        }
        
        return null;
    }
    
    /**
     * Extract (of) "numeric" "unit" (of) "quantity"
     * 
     * @param integer $startPosition
     */
    private function getValuedUnitQuantity($startPosition) {
        
        /*
         * (to) "numeric" "unit"
         */
        $valuedUnit = $this->extractValueAndUnit($startPosition + 1, $this->queryManager->length);
        if (!isset($valuedUnit)) {
            return null;
        }
            
        /*
         * 
         * "quantity" <xxx> (to) "numeric" "unit"
         */
        $quantity = $this->extractQuantity(0, $startPosition - 1, true);
       
        /*
         * <xxx> (to) "numeric" "unit" (of) "quantity"
         */
        if (!isset($quantity)) {
            $quantity = $this->extractQuantity($valuedUnit['endPosition'] + 1, $this->queryManager->length);
        }

        /*
         * Quantity was found 
         */
        if (isset($quantity)) {
            $startPosition = min(array($startPosition, $quantity['startPosition']));
            $endPosition = max(array($valuedUnit['endPosition'], $quantity['endPosition']));
            if ($valuedUnit['unit']['unit'] === $quantity['unit']) {
                return array(
                    'valuedUnit' => $valuedUnit,
                    'quantity' => $quantity,
                    'startPosition' => $startPosition,
                    'endPosition' => $endPosition
                );
            }
            else {
                return array(
                    'startPosition' => $startPosition,
                    'endPosition' => $endPosition,
                    'error' => QueryAnalyzer::INVALID_UNIT
                );
            }
        }
        
        return null;
        
    }
    
    /**
     * Extract (of) "numeric" "unit"
     * 
     * @param integer $startPosition
     * @param integer $endPosition
     */
    private function extractValueAndUnit($startPosition, $endPosition) {
        
        for ($i = $startPosition; $i < $endPosition; $i++) {
            
            /*
             * Skip stop words and processed words
             */
            if ($this->queryManager->isStopWordPosition($i) || !$this->queryManager->isValidPosition($i)) {
                continue;
            }
           
            /*
             * "numeric" "unit"
             */
            $value = $this->queryManager->dictionary->getNumber($this->queryManager->words[$i]['word']);
            if (isset($value) && $this->queryManager->isValidPosition($i + 1)) {
                $unit = $this->queryManager->dictionary->get(RestoDictionary::UNIT, $this->queryManager->words[$i + 1]['word']);
                return array(
                    'value' => $value,
                    'endPosition' => $i + 1,
                    'unit' => $this->normalizedUnit($unit)
                );
            }
            
        }
     
        return null;
        
    }
    
    /**
     * Extract "unit"
     * 
     * @param integer $startPosition
     */
    private function extractUnit($startPosition) {
        
        $endPosition = $this->queryManager->getEndPosition($startPosition);
       
        for ($i = $startPosition; $i <= $endPosition; $i++) {
            
            /*
             * Skip stop words
             */
            if ($this->queryManager->isStopWordPosition($i)) {
                continue;
            }
           
            /*
             * "numeric" "unit"
             */
            $unit = $this->queryManager->dictionary->get(RestoDictionary::UNIT, $this->queryManager->words[$i]['word']);
            if (isset($unit)) {
                return array(
                    'endPosition' => $i,
                    'unit' => $this->normalizedUnit($unit)
                );
            }
            
        }
     
        return null;
        
    }
    
    /**
     * Return normalized unit from $unit
     * e.g. if $unit = 'km', returned value is 
     *      array(
     *          'unit' => 'm',
     *          'factor' => 1000
     *      )
     * 
     * @param string $unit
     */
    private function normalizedUnit($unit) {
        
        if (!$unit) {
            return null;
        }
        
        $factor = 1.0;
        switch ($unit) {
            case 'km':
                $unit = 'm';
                $factor = 1000.0;
                break;
            default:
                break;
        }
        
        return array(
            'unit' => $unit,
            'factor' => $factor
        );
    }
    
    /**
     * Return result as a filter from keyword
     * 
     * @param array $keyword
     * @param boolean $with
     */
    private function toFilter($keyword, $with) {
        $sign = ($with ? '' : '-');
        switch ($keyword['type']) {
            case 'instrument':
            case 'platform':
                return array('eo:'.$keyword['type'] => $sign . $keyword['keyword']);
            default:
                return array('searchTerms' => $sign . $keyword['type'] . ':' . $keyword['keyword']);
        }
    }
    
    /**
     * Add filters to result
     * 
     * @param array $filters
     */
    private function addToResult($filters) {
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'eo:instrument':
                case 'eo:platform':
                    $this->result[$key] = isset($this->result[$key]) ? $this->result[$key] . '|' . $value : $value;
                    break;
                case 'searchTerms':
                    if (!isset($this->result[$key])) {
                        $this->result[$key] = array();
                    }
                    $this->result[$key][] = $value;
                    $this->result[$key] = array_unique($this->result[$key]);
                    break;
                default:
                    $this->result[$key] = $this->mergeIntervals($key, $value);
            }
        }
    }
    
    /**
     * Merge intervals TODO
     * 
     * @param string $key
     * @param string $value
     */
    private function mergeIntervals($key, $value) {
        return isset($this->result[$key]) ? $this->result[$key] . ' ' . $value : $value;
    }
    
    /**
     * Return quantity
     * 
     * A valid quantity should be defined with searchFilters as
     *      'quantity' => array(
     *          'value' => // name of the quantity (i.e. an existing entry in "quantities" dictionary array)
     *          'unit' => // unit of the quantity (i.e. an existing entry in "units" dictionnary array)
     *      )
     * 
     * @param String $word
     */
    private function getQuantity($word) {
        
        $quantity = $this->queryManager->dictionary->get(RestoDictionary::QUANTITY, $word);
        
        if (!isset($quantity)) {
            return null;
        }
        
        foreach(array_keys($this->queryManager->model->searchFilters) as $key) {
            if (isset($this->queryManager->model->searchFilters[$key]['quantity']) && is_array($this->queryManager->model->searchFilters[$key]['quantity']) && $this->queryManager->model->searchFilters[$key]['quantity']['value'] === $quantity) {
                return array(
                    'key' => $key,
                    'unit' => isset($this->queryManager->model->searchFilters[$key]['quantity']['unit']) ? $this->queryManager->model->searchFilters[$key]['quantity']['unit'] : null
                );
            }
        }
        
        return null;
    }
    
}