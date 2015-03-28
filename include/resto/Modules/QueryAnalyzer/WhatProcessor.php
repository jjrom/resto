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
     * Process <with> "quantity" 
     * 
     * @param array $words
     * @param integer $position
     * 
     */
    public function processWith($words, $position) {
        return $this->processWithOrWithout($words, $position, true);
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
         * To be valid at least 4 words are mandatory after <between> and second word must be <and>
         */
        if (!isset($words[$position + 4]) || $this->queryAnalyzer->dictionary->get(RestoDictionary::VARIOUS_MODIFIER, $words[$position + 2]) !== 'and') {
            return $this->queryAnalyzer->whenProcessor->processBetween($words, $position);
        }
        
        /*
         * Words in 1st and 3rd position after <between> must be numeric values
         * Word in 2nd position after <between> must be a valid unit
         * Otherwise try to process <between> with WhenProcessor
         */
        $firstValue = $this->queryAnalyzer->dictionary->getNumber($words[$position + 1]);
        $secondValue = $this->queryAnalyzer->dictionary->getNumber($words[$position + 3]);
        $unit = $this->queryAnalyzer->dictionary->get(RestoDictionary::UNIT, $words[$position + 4]);
        if (!isset($firstValue) || !isset($secondValue) || !isset($unit)) {
            return $this->queryAnalyzer->whenProcessor->processBetween($words, $position);
        }
        
        /*
         * Quantity is mandatory - either before <between> or after "unit"
         */
        
        array_splice($words, $position, $endPosition - $position + 1);
       
        return $words;
        
    }
    
    /**
     * Process <with> or <without> "quantity" 
     * 
     * @param array $words
     * @param integer $position
     * @param boolean $with
     * 
     */
    private function processWithOrWithout($words, $position, $with = true) {
       
        $endPosition = $this->queryAnalyzer->getEndPosition($words, $position + 1);
                
        /*
         * <with/without> nothing
         */
        if (!isset($words[$position + 1])) {
            $this->queryAnalyzer->addToNotUnderstood($words, $position, $endPosition);
        }
        /*
         * <with> "quantity" means quantity
         * <without> "quantity" means quantity = 0
         */
        else {
            $quantity = $this->extractQuantity($words, $position + 1, $endPosition);
            if (isset($quantity)) {
                $this->result[$quantity['key']] = $with ? ']0' : 0;
                $endPosition = $quantity['endPosition'];
            }
            else {
                $keyword = $this->extractKeyword($words, $position + 1, $endPosition);
                if (isset($keyword)) {
                    $this->result[] = ($with ? '' : '-') . $keyword['type'] . ':' . $keyword['keyword']; 
                }
                else {
                    $this->queryAnalyzer->addToNotUnderstood($words, $position, $endPosition);
                }
            }
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
     
        /*
         * Process (reversed) words within $startPosition and $endPosition
         */
        $slicedWords = $this->queryAnalyzer->slice($words, $startPosition, $endPosition - $startPosition + 1);
        
        $word = '';
        for ($i = 0, $ii = count($slicedWords); $i < $ii; $i++) {

            /*
             * Reconstruct word from words without stop words
             */
            if (!$this->queryAnalyzer->dictionary->isStopWord($slicedWords[$i])) {
                $word = trim($reverse ? $slicedWords[$i] . ' ' . $word : $word . ' ' . $slicedWords[$i]);
            }

            $quantity = $this->queryAnalyzer->dictionary->get(RestoDictionary::QUANTITY, $word);
            if (isset($quantity)) {
                $searchFilter = $this->getSearchFilter($quantity);
                if (isset($searchFilter)) {
                    return array(
                        'startPosition' => $reverse ? $endPosition - $i : $startPosition,
                        'endPosition' => $reverse ? $endPosition : $startPosition + $i,
                        'key' => $searchFilter['key']
                    );
                }
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
        $slicedWords = $this->queryAnalyzer->slice($words, $startPosition, $endPosition - $startPosition + 1);
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
                return array('key' => $key, 'unit' => $this->queryAnalyzer->model->searchFilters[$key]['quantity']['unit']);
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
    
    
}