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
     * @param array $endPosition
     */
    public function extractQuantity($startPosition, $endPosition) {
        
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
    public function extractKeyword($startPosition) {
     
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
    
}