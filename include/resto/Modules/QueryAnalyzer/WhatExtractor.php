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
 * Extractor for QueryAnalyzer What processor
 * 
 * @param array $params
 */
class WhatExtractor {

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
     * Extract quantity
     * 
     * @param integer $startPosition
     * @param integer $endPosition
     * @param boolean $reverse
     */
    public function extractQuantity($startPosition, $endPosition, $reverse = false) {
        
        if ($startPosition > $endPosition) {
            return null;
        }
        
        return $reverse ? $this->extractQuantityRightToLeft($startPosition, $endPosition) : $this->extractQuantityLeftToRight($startPosition, $endPosition);
        
    }
    
    /**
     * Extract keyword
     * 
     * @param integer $startPosition
     */
    public function extractKeyword($startPosition) {
     
        /*
         * Process words within $startPosition and $endPosition
         */
        for ($i = $startPosition; $i < $this->queryManager->length; $i++) {
            
            /*
             * Skip first word if stop words
             */
            if ($i === $startPosition && $this->queryManager->isStopWordPosition($i)) {
                continue;
            }
            
            /*
             * Discard noise
             */
            if ($this->queryManager->dictionary->isNoise($this->queryManager->words[$i]['word'])) {
                continue;
            }
            
            $word = (isset($word) ? $word . '-' : '') . $this->queryManager->words[$i]['word'];
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
     * Extract (of) "numeric" "unit"
     * 
     * @param integer $startPosition
     * @param integer $endPosition
     */
    public function extractValueAndUnit($startPosition, $endPosition) {
        
        for ($i = $startPosition; $i < $endPosition; $i++) {
            
            /*
             * Skip stop words and processed words
             */
            if ($this->queryManager->isStopWordPosition($i) || !$this->queryManager->isValidPosition($i)) {
                continue;
            }
           
            /*
             * "numeric"
             */
            $value = $this->queryManager->dictionary->getNumber($this->queryManager->words[$i]['word']);
            if (isset($value)) {
                
                $endPosition = $i;
                
                /*
                 * "unit"
                 */
                if ($this->queryManager->isValidPosition($i + 1)) {
                    $unit = $this->queryManager->dictionary->get(RestoDictionary::UNIT, $this->queryManager->words[$i + 1]['word']);
                    $endPosition = $i + 1;
                }
                
                return array(
                    'value' => $value,
                    'endPosition' => $endPosition,
                    'unit' => isset($unit) ? $this->normalizedUnit($unit) : null
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
    public function extractUnit($startPosition) {
        
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
    
    /**
     * Extract quantity - parsing words left to right
     * 
     * @param integer $startPosition
     * @param integer $endPosition
     */
    private function extractQuantityLeftToRight($startPosition, $endPosition) {
        
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
     * Extract quantity - parsing words right to left
     * 
     * @param integer $startPosition
     * @param integer $endPosition
     */
    private function extractQuantityRightToLeft($startPosition, $endPosition) {
        
        /*
         * Process words within $startPosition and $endPosition
         */
        $word = '';
        for ($i = $endPosition; $i >= $startPosition; $i--) {
            
            /*
             * Reconstruct word from words without stop words
             */
            if ($this->queryManager->isValidPosition($i) && !$this->queryManager->isStopWordPosition($i)) {
                $word = trim($this->queryManager->words[$i]['word'] . ' ' . $word);
            }

            $quantity = $this->getQuantity($word);
            if (isset($quantity)) {
                return array_merge($quantity, array(
                    'startPosition' => $i,
                    'endPosition' => $endPosition
                ));
            }
        }
        
        return null;
        
    }
    
}