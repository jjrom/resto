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
     * Reference to QueryAnalyzer
     */
    private $queryAnalyzer;
    
    /**
     * Constructor
     * 
     * @param QueryAnalyzer $queryAnalyzer
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($queryAnalyzer, $context, $user) {
        $this->queryAnalyzer = $queryAnalyzer;
        $this->context = $context;
        $this->user = $user;
    }
    
    /**
     * Process <by> "quantity" 
     * 
     * @param array $words
     * @param integer $position
     * @param array $options
     * 
     */
    public function processBy($words, $position, $options = array('delta' => 1, 'nullIfNotFound' => false)) {
        $endPosition = $this->queryAnalyzer->getEndPosition($words, $position + $options['delta']);
        $keyword = $this->extractKeyword($words, $position + $options['delta'], $endPosition);
        if (isset($keyword)) {
            $keyValue = $this->toKeyValue($keyword, true);
            $this->addToResult($keyValue[0], $keyValue[1]);
            $endPosition = $keyword['endPosition'];
        }
        array_splice($words, $position, $endPosition - $position + 1);
       
        return $words;
    }
    
    /**
     * Process <with> "quantity" 
     * 
     * @param array $words
     * @param integer $position
     * @param array $options
     * 
     */
    public function processWith($words, $position, $options = array('delta' => 1, 'nullIfNotFound' => false)) {
        return $this->processWithOrWithout($words, $position, true, $options);
    }
    
    /**
     * Process <without> "quantity" 
     * 
     * @param array $words
     * @param integer $position
     * @param boolean $with
     * 
     */
    public function processWithout($words, $position) {
        return $this->processWithOrWithout($words, $position, false);
    }
    
    /**
     * Process <between>
     * 
     *  "quantity" <between> "numeric" <and> "numeric" "unit"
     *  <between> "numeric" <and> "numeric" "unit" (of) "quantity"
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    public function processBetween($words, $position) {
        
        /*
         * To be valid at least 3 words are mandatory after <between> and second word must be <and>
         */
        if (!isset($words[$position + 3]) || $this->queryAnalyzer->dictionary->get(RestoDictionary::AND_MODIFIER, $words[$position + 2]) !== 'and') {
            return $this->queryAnalyzer->whenProcessor->processBetween($words, $position);
        }
        
        /*
         * Words in 1st and 3rd position after <between> must be numeric values
         * Word in 2nd position after <between> must be a valid unit
         * Otherwise try to process <between> with WhenProcessor
         */
        $values = array(
            $this->queryAnalyzer->dictionary->getNumber($words[$position + 1]),
            $this->queryAnalyzer->dictionary->getNumber($words[$position + 3])
        );
        
        if (!isset($values[0]) || !isset($values[1]) || (isset($words[$position + 4]) && $this->queryAnalyzer->dictionary->get(RestoDictionary::MONTH, $words[$position + 4]))) {
            return $this->queryAnalyzer->whenProcessor->processBetween($words, $position);
        }
        
        /*
         * Process differs if unit is specified or not
         */
        if (isset($words[$position + 4])) {
            $unit = $this->extractUnit($words, $position + 4, count($words));
        }
        
        return isset($unit) ? $this->processValidBetweenWithUnit($words, $position, $values, $unit) : $this->processValidBetweenWithoutUnit($words, $position, $values);
        
    }
    
    /**
     * Process <equal>
     * 
     *      "quantity" <equal> (to) "numeric" "unit"
     *      <equal> (to) "numeric" "unit" (of) "quantity"
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    public function processEqual($words, $position) {
        return $this->processEqualOrGreaterOrLesser($words, $position, WhatProcessor::EQUAL);
    }
    
    /**
     * Process <greater>
     * 
     *      "quantity" <greater> (to) "numeric" "unit"
     *      <greater> (to) "numeric" "unit" (of) "quantity"
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    public function processGreater($words, $position) {
        return $this->processEqualOrGreaterOrLesser($words, $position, WhatProcessor::GREATER);
    }
    
    /**
     * Process <lesser>
     * 
     *      "quantity" <lesser> (to) "numeric" "unit"
     *      <lesser> (to) "numeric" "unit" (of) "quantity"
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    public function processLesser($words, $position) {
        return $this->processEqualOrGreaterOrLesser($words, $position, WhatProcessor::LESSER);
    }
    
    /**
     * Process <equal> or <greater> or <lesser>
     * 
     *      "quantity" <xxx> (to) "numeric" "unit"
     *      <xxx> (to) "numeric" "unit" (of) "quantity"
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function processEqualOrGreaterOrLesser($words, $position, $modifier) {
        
        /*
         * <equal>, <greater> or <lesser>
         */
        $extracted = $this->extractValueUnitQuantity($words, $position);
        if (isset($extracted['valuedUnit'])) {
            $value = (floatval($extracted['valuedUnit']['value']) * $extracted['valuedUnit']['unit']['factor']);
            switch ($modifier) {
                case WhatProcessor::EQUAL:
                    $this->addToResult($extracted['quantity']['key'], $value);
                    break;
                case WhatProcessor::GREATER:
                    $this->addToResult($extracted['quantity']['key'], ']' .$value);
                    break;
                case WhatProcessor::LESSER:
                    $this->addToResult($extracted['quantity']['key'], $value . '[');
                    break;
            }
        }
        else {
            $this->queryAnalyzer->error(QueryAnalyzer::NOT_UNDERSTOOD, $this->queryAnalyzer->toSentence($words, $extracted['startPosition'], $extracted['endPosition']));
        }
        
        array_splice($words, $extracted['startPosition'], $extracted['endPosition'] - $extracted['startPosition'] + 1);
        
        return $words;
        
    }
    
    /**
     * Process <with> or <without> "quantity" 
     * 
     * @param array $words
     * @param integer $position
     * @param boolean $with
     * @param integer $delta
     * @param boolean $nullIfNotFound
     * 
     */
    private function processWithOrWithout($words, $position, $with, $options = array('delta' => 1, 'nullIfNotFound' => false)) {
       
        $endPosition = $this->queryAnalyzer->getEndPosition($words, $position + $options['delta']);
                
        /*
         * <with/without> nothing
         */
        if (!isset($words[$position + $options['delta']])) {
            $this->queryAnalyzer->error(QueryAnalyzer::NOT_UNDERSTOOD, $this->queryAnalyzer->toSentence($words, $position, $endPosition));
        }
        /*
         * <with> "quantity" means quantity
         * <without> "quantity" means quantity = 0
         */
        else {
            $quantity = $this->extractQuantity($words, $position + $options['delta'], $endPosition);
            if (isset($quantity)) {
                $this->addToResult($quantity['key'], $with ? ']0' : 0);
                $endPosition = $quantity['endPosition'];
            }
            else {
                $keyword = $this->extractKeyword($words, $position + $options['delta'], $endPosition);
                if (isset($keyword)) {
                    $keyValue = $this->toKeyValue($keyword, $with);
                    $this->addToResult($keyValue[0], $keyValue[1]);
                    $endPosition = $keyword['endPosition'];
                }
                else {
                    if ($options['nullIfNotFound']) {
                        return null;
                    }
                    $this->queryAnalyzer->error(QueryAnalyzer::NOT_UNDERSTOOD, $this->queryAnalyzer->toSentence($words, $position, $endPosition));
                }
            }
        }
        
        array_splice($words, $position, $endPosition - $position + 1);
       
        return $words;
    }
    
    /**
     * Process <between> ... <and> ... on quantity with unit
     * 
     * @param array $words
     * @param integer $position
     * @param array $values
     * @param array $normalizedUnit 
     * @return type
     */
    private function processValidBetweenWithUnit($words, $position, $values, $normalizedUnit) {
        
        $endPosition = count($words) - 1;
        $startPosition = $normalizedUnit['endPosition'] + 1;
        
        /*
         * 
         * "quantity" <between> (...)
         */
        $quantity = $this->extractQuantity($words, 0, $position - 1, true);
        
        /*
         * <between> ... "unit" "quantity"
         */
        if (!isset($quantity)) {
            $quantity = $this->extractQuantity($words, $startPosition, count($words));
        }
        
        /*
         * Quantity was found 
         */
        if (isset($quantity)) {
            
            /*
             * Recompute start and end position
             */
            $position = min(array($position, $quantity['startPosition']));
            $endPosition = max(array($startPosition, $quantity['endPosition']));
            
            if ($normalizedUnit['unit']['unit'] === $quantity['unit']) {
                $this->addToResult($quantity['key'], '[' . (floatval($values[0]) * $normalizedUnit['unit']['factor']) . ',' . (floatval($values[1]) * $normalizedUnit['unit']['factor']) . ']');
            }
            else {
                $this->queryAnalyzer->error(QueryAnalyzer::INVALID_UNIT, $this->queryAnalyzer->toSentence($words, $position, $endPosition));
            }
        }
        else {
            $this->queryAnalyzer->error(QueryAnalyzer::NOT_UNDERSTOOD, $this->queryAnalyzer->toSentence($words, $position, $endPosition));
        }
        
        array_splice($words, $position, $endPosition - $position + 1);
        
        return $words;
    }
    
    /**
     * Process a valid <between> ... <and> ... with unit
     * 
     * @param array $words
     * @param integer $position
     * @param array $values
     * @return type
     */
    private function processValidBetweenWithoutUnit($words, $position, $values) {
        
        $quantityPosition = isset($words[$position + 4]) ? $position + 4 : $position + 3;
        $endPosition = $this->queryAnalyzer->getEndPosition($words, $quantityPosition);
        $startPosition = min($quantityPosition, $endPosition);
        
        /*
         * 
         * "quantity" <between> (...)
         */
        $quantity = $this->extractQuantity($words, 0, $position - 1, true);
        
        /*
         * <between> ... "unit" "quantity"
         */
        if (!isset($quantity)) {
            $quantity = $this->extractQuantity($words, $startPosition, count($words));
        }
        
        /*
         * Quantity was found 
         */
        if (isset($quantity)) {
            
            /*
             * Recompute start and end position
             */
            $position = min(array($position, $quantity['startPosition']));
            $endPosition = max(array($startPosition, $quantity['endPosition']));
            
            if (!isset($quantity['unit'])) {
                $this->addToResult($quantity['key'], '[' . $values[0] . ',' . $values[1] . ']');
            }
            else {
                $this->queryAnalyzer->error(QueryAnalyzer::MISSING_UNIT, $this->queryAnalyzer->toSentence($words, $position, $endPosition));
            }
        }
        else {
            $this->queryAnalyzer->error(QueryAnalyzer::NOT_UNDERSTOOD, $this->queryAnalyzer->toSentence($words, $position, $endPosition));
        }
        
        array_splice($words, $position, $endPosition - $position + 1);
        
        return $words;
    }
    
    /**
     * Extract quantity
     * 
     * @param array $words
     * @param integer $startPosition
     * @param array $endPosition
     * @param boolean $reverse
     */
    private function extractQuantity($words, $startPosition, $endPosition, $reverse = false) {
        
        if ($startPosition > $endPosition) {
            return null;
        }
        
        /*
         * Process (reversed) words within $startPosition and $endPosition
         */
        $slicedWords = $this->queryAnalyzer->slice($words, $startPosition, $endPosition - $startPosition + 1, $reverse);
        $word = '';
        for ($i = 0, $ii = count($slicedWords); $i < $ii; $i++) {

            /*
             * Reconstruct word from words without stop words
             */
            if (!$this->queryAnalyzer->dictionary->isStopWord($slicedWords[$i])) {
                $word = trim($reverse ? $slicedWords[$i] . ' ' . $word : $word . ' ' . $slicedWords[$i]);
            }
            
            $trueQuantity = $this->getTrueQuantity($word, $reverse ? $endPosition - $i : $startPosition, $reverse ? $endPosition : $startPosition + $i);
            if (isset($trueQuantity)) {
                return $trueQuantity;
            }
            
        }
        return null;
    }
    
    /**
     * Extract quantity
     * 
     * @param array $words
     * @param integer $startPosition
     * @param array $endPosition
     * @param boolean $reverse
     */
    private function extractKeyword($words, $startPosition, $endPosition, $reverse = false) {
     
        /*
         * Process (reversed) words within $startPosition and $endPosition
         */
        $slicedWords = $this->queryAnalyzer->slice($words, $startPosition, $endPosition - $startPosition + 1, $reverse);
        $word = '';
        for ($i = 0, $ii = count($slicedWords); $i < $ii; $i++) {

            /*
             * Reconstruct word from words without stop words
             */
            if (!$this->queryAnalyzer->dictionary->isStopWord($slicedWords[$i])) {
                $word = trim($reverse ? $slicedWords[$i] . '-' . $word : $word . ' ' . $slicedWords[$i]);
            }

            $keyword = $this->queryAnalyzer->dictionary->getKeyword(RestoDictionary::NOLOCATION, $word);
            if (isset($keyword)) {
                return array(
                    'startPosition' => $reverse ? $endPosition - $i : $startPosition,
                    'endPosition' => $reverse ? $endPosition : $startPosition + $i,
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
     * @param array $words
     * @param integer $position
     */
    private function extractValueUnitQuantity($words, $position) {
        
        $endPosition = $this->queryAnalyzer->getEndPosition($words, $position + 1);
        
        /*
         * (to) "numeric" "unit"
         */
        $valuedUnit = $this->extractValueUnit($words, $position + 1, $endPosition);
        if (!isset($valuedUnit)) {
            return array(
                'startPosition' => $position,
                'endPosition' => $endPosition
            );
        }
            
        /*
         * 
         * "quantity" <xxx> (to) "numeric" "unit"
         */
        $quantity = $this->extractQuantity($words, 0, $position - 1, true);
       
        /*
         * <xxx> (to) "numeric" "unit" (of) "quantity"
         */
        if (!isset($quantity)) {
            $quantity = $this->extractQuantity($words, $valuedUnit['endPosition'] + 1, count($words));
        }

        /*
         * Quantity was found 
         */
        if (isset($quantity)) {
            $startPosition = min(array($position, $quantity['startPosition']));
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
                $this->queryAnalyzer->error(QueryAnalyzer::INVALID_UNIT, $this->queryAnalyzer->toSentence($words, $position, $endPosition));
                return array(
                    'startPosition' => $startPosition,
                    'endPosition' => $endPosition
                );
            }
        }
        
        return array(
            'startPosition' => $valuedUnit['startPosition'],
            'endPosition' => $valuedUnit['endPosition']
        );
        
    }
    
    /**
     * Extract (of) "numeric" "unit"
     * 
     * @param array $words
     * @param integer $startPosition
     * @param integer $endPosition
     */
    private function extractValueUnit($words, $startPosition, $endPosition) {
        
        for ($i = $startPosition; $i < $endPosition; $i++) {
            
            /*
             * Skip stop words
             */
            if ($this->queryAnalyzer->dictionary->isStopWord($words[$i])) {
                continue;
            }
           
            /*
             * "numeric" "unit"
             */
            $value = $this->queryAnalyzer->dictionary->getNumber($words[$i]);
            if (isset($value) && isset($words[$i + 1])) {
                $unit = $this->queryAnalyzer->dictionary->get(RestoDictionary::UNIT, $words[$i + 1]);
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
     * @param array $words
     * @param integer $startPosition
     * @param integer $endPosition
     */
    private function extractUnit($words, $startPosition, $endPosition) {
        
        for ($i = $startPosition; $i < $endPosition; $i++) {
            
            /*
             * Skip stop words
             */
            if ($this->queryAnalyzer->dictionary->isStopWord($words[$i])) {
                continue;
            }
           
            /*
             * "numeric" "unit"
             */
            $unit = $this->queryAnalyzer->dictionary->get(RestoDictionary::UNIT, $words[$i]);
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
     * Return filter name associated to $quantity
     * 
     * A valid quantity should be defined with searchFilters as
     *      'quantity' => array(
     *          'value' => // name of the quantity (i.e. an existing entry in "quantities" dictionary array)
     *          'unit' => // unit of the quantity (i.e. an existing entry in "units" dictionnary array)
     *      )
     * 
     * @param String $quantity
     */
    private function getSearchFilter($quantity) {
        
        if (!isset($quantity)) {
            return null;
        }
        
        foreach(array_keys($this->queryAnalyzer->model->searchFilters) as $key) {
            if (isset($this->queryAnalyzer->model->searchFilters[$key]['quantity']) && is_array($this->queryAnalyzer->model->searchFilters[$key]['quantity']) && $this->queryAnalyzer->model->searchFilters[$key]['quantity']['value'] === $quantity) {
                return array(
                    'key' => $key,
                    'unit' => isset($this->queryAnalyzer->model->searchFilters[$key]['quantity']['unit']) ? $this->queryAnalyzer->model->searchFilters[$key]['quantity']['unit'] : null
                );
            }
        }
        
        return null;
    }
    
    /**
     * Return quantity if valid
     * 
     * @param string $word
     * @param integer $startPosition
     * @param integer $endPosition
     * @return array
     */
    private function getTrueQuantity($word, $startPosition, $endPosition) {
        $quantity = $this->queryAnalyzer->dictionary->get(RestoDictionary::QUANTITY, $word);
        if (isset($quantity)) {
            $searchFilter = $this->getSearchFilter($quantity);
            if (isset($searchFilter)) {
                return array(
                    'startPosition' => $startPosition,
                    'endPosition' => $endPosition,
                    'key' => $searchFilter['key'],
                    'unit' => isset($searchFilter['unit']) ? $searchFilter['unit'] : null
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
    private function toKeyValue($keyword, $with) {
        $sign = ($with ? '' : '-');
        switch ($keyword['type']) {
            case 'instrument':
            case 'platform':
                return array('eo:'.$keyword['type'], $sign . $keyword['keyword']);
            default:
                return array('searchTerms', $sign . $keyword['type'] . ':' . $keyword['keyword']);
        }
    }
    
    /**
     * Add a key to result
     * 
     * @param string $key
     * @param string $value
     */
    private function addToResult($key, $value) {
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