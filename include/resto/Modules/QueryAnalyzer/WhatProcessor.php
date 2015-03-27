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
    
    public function processWithout($words, $position) {
        return $this->processWithOrWithout($words, $position, false);
    }
    
    /**
     * Process <without> "quantity" 
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
            $sentence = $this->queryAnalyzer->toSentence($words, $position + 1, $endPosition);
            if (!$this->extractQuantity($sentence, $with)) {
                $keyword = $this->queryAnalyzer->dictionary->getKeyword(RestoDictionary::NOLOCATION, $sentence);
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
     * @param string $word
     * @param boolean $with
     */
    private function extractQuantity($word, $with) {
        $quantity = $this->queryAnalyzer->dictionary->get(RestoDictionary::QUANTITY, $word);
        if (isset($quantity)) {
            $searchFilter = $this->getSearchFilter($quantity);
            if (isset($searchFilter)) {
                $this->result[$searchFilter['key']] = $with ? ']0' : 0;
                return $quantity;
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