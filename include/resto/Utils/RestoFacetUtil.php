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
 * RESTo Facet Utilities
 */
class RestoFacetUtil {
    
    /*
     * Facet hierarchy
     */
    public $facetCategories = array(
        array(
            'collection'
        ),
        array(
            'productType'
        ),
        array(
            'processingLevel'
        ),
        array(
            'platform',
            'instrument',
            'sensorMode'
        ),
        array(
            'continent',
            'country',
            'region',
            'state'
        ),
        array(
            'year',
            'month',
            'day'
        )
    );
    
    /**
     * Constructor
     */
    public function __construct() {} 
   
    /**
     * Return facet category 
     * 
     * @param string $type
     */
    public function getFacetCategory($type) {
        if (!isset($type)) {
            return null;
        }
        for ($i = count($this->facetCategories); $i--;) {
            for ($j = count($this->facetCategories[$i]); $j--;) {
                if ($this->facetCategories[$i][$j] === $type) {
                    return $this->facetCategories[$i];
                }
            }
        }
        
        /*
         * Otherwise return $type as a new facet category
         */
        return $type;
        
    }
    
    /**
     * Return facet parent type
     * 
     * @param string $facetId
     */
    public function getFacetParentType($facetId) {
        $category = $this->getFacetCategory($facetId);
        if (!isset($category)) {
            return null;
        }
        $splitted = explode(':', $facetId);
        for ($i = count($category); $i--;) {
            if ($splitted[0] === $category[$i] && $i > 0) {
                return $category[$i - 1];
            }
        }
        return null;
    }
    
    /**
     * Return facet children type
     * 
     * @param string $facetId
     */
    public function getFacetChildrenType($facetId) {
        $category = $this->getFacetCategory($facetId);
        if (!isset($category)) {
            return null;
        }
        $splitted = explode(':', $facetId);
        $count = count($category);
        for ($i = $count; $i--;) {
            if ($splitted[0] === $category[$i] && $i < $count - 1) {
                return $category[$i + 1];
            }
        }
        return null;
    }
    
}
