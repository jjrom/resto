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
 * QueryAnalyzer What
 * 
 * @param array $params
 */
require 'WhatExtractor.php';
class WhatProcessor {

    const EQUAL = 0;
    const GREATER = 1;
    const LESSER = 2;
    
    /*
     * Process result
     */
    private $result = array();
    
    /*
     * Reference to QueryManager
     */
    private $queryManager;
    
    /*
     * Reference to WhatExtractor
     */
    private $extractor;
    
    /*
     * Minimal percentage for quantity
     */
    private $minimalQuantity;
    
    /**
     * Constructor
     * 
     * @param QueryManager $queryManager
     * @param Array $options
     */
    public function __construct($queryManager, $options) {
        $this->queryManager = $queryManager;
        $this->extractor = new WhatExtractor($this->queryManager);
        $this->minimalQuantity = isset($options['minimalQuantity']) ? $options['minimalQuantity'] : 25;
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
     * Process <for> "keyword" 
     * 
     * @param integer $startPosition
     * @param integer $delta
     * @param boolean $with
     * 
     */
    public function processFor($startPosition, $delta = 1, $with = true, $by = __METHOD__) {
        $keyword = $this->extractor->extractKeyword($startPosition + $delta);
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
            $unit = $this->extractor->extractUnit($startPosition + 4);
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
                if (isset($valuedUnitQuantity['valuedUnit']['unit'])) {
                    $value = (floatval($valuedUnitQuantity['valuedUnit']['value']) * $valuedUnitQuantity['valuedUnit']['unit']['factor']);
                }
                else {
                    $value = (floatval($valuedUnitQuantity['valuedUnit']['value']));
                }
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
        
        $endPosition = $this->queryManager->getEndPosition($startPosition + $delta);
        
        /*
         * Quantity ?
         */
        $quantity = $this->extractor->extractQuantity($startPosition + $delta, $this->queryManager->getEndPosition($startPosition + $delta), false);
        if (isset($quantity)) {
            $this->addToResult(array(
                $quantity['key'] => $with ? ']' . $this->minimalQuantity : 0
            ));
            $endPosition = $quantity['endPosition'];
        }

        /*
         * Keyword ?
         */
        else {
            $keyword = $this->extractor->extractKeyword($startPosition + $delta);
            if (isset($keyword)) {
                $this->addToResult($this->toFilter($keyword, $with));
                $endPosition = $keyword['endPosition'];
            }
            else {
                $error = QueryAnalyzer::NOT_UNDERSTOOD;
            }
        }
        
        if ($delta === 1 || !isset($error)) {
            $this->queryManager->discardPositionInterval(__METHOD__, $startPosition, $endPosition, isset($error) ? $error : null);
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
        $quantity = $this->extractor->extractQuantity(0, $betweenPosition - 1, true);
        
        /*
         * <between> ... "unit" "quantity"
         */
        if (!isset($quantity)) {
            $quantity = $this->extractor->extractQuantity($normalizedUnit['endPosition'] + 1, $this->queryManager->length, false);
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
     * Process a valid <between> ... <and> ... without unit
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
        $quantity = $this->extractor->extractQuantity(0, $betweenPosition - 1, true);
        
        /*
         * <between> ... "unit" "quantity"
         */
        if (!isset($quantity)) {
            $quantity = $this->extractor->extractQuantity($startPosition, $this->queryManager->length, false);
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
     * Extract (of) "numeric" "unit" (of) "quantity"
     * 
     * @param integer $startPosition
     */
    private function getValuedUnitQuantity($startPosition) {
        
        /*
         * (to) "numeric" "unit"
         */
        $valuedUnit = $this->extractor->extractValueAndUnit($startPosition + 1, $this->queryManager->length);
        if (!isset($valuedUnit)) {
            return null;
        }
            
        /*
         * 
         * "quantity" <xxx> (to) "numeric" "unit"
         */
        $quantity = $this->extractor->extractQuantity(0, $startPosition - 1, true);
       
        /*
         * <xxx> (to) "numeric" "unit" (of) "quantity"
         */
        if (!isset($quantity)) {
            $quantity = $this->extractor->extractQuantity($valuedUnit['endPosition'] + 1, $this->queryManager->length, false);
        }

        /*
         * Quantity was found 
         */
        if (isset($quantity)) {
            $startPosition = min(array($startPosition, $quantity['startPosition']));
            $endPosition = max(array($valuedUnit['endPosition'], $quantity['endPosition']));
            
            /*
             * Quantity with unit (e.g. "cloudCover")
             */
            if (isset($quantity['unit'])) {
                if (!isset($valuedUnit['unit']) || $valuedUnit['unit']['unit'] !== $quantity['unit']) {
                    return array(
                        'startPosition' => $startPosition,
                        'endPosition' => $endPosition,
                        'error' => QueryAnalyzer::INVALID_UNIT
                    );
                }
            }
            /*
             * Quantity without unit (e.g. "orbit")
             */
            return array(
                'valuedUnit' => $valuedUnit,
                'quantity' => $quantity,
                'startPosition' => $startPosition,
                'endPosition' => $endPosition
            );
            
        }
        
        return null;
        
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
    
}