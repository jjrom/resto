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
        ),
        array(
            'landuse'
        )
    );
    
    /**
     * Constructor
     */
    public function __construct() {
    } 
   
    /**
     * Return facet category 
     * 
     * @param string $facetId
     */
    public function getFacetCategory($facetId) {
        if (!isset($facetId)) {
            return null;
        }
        $splitted = explode(':', $facetId);
        for ($i = count($this->facetCategories); $i--;) {
            for ($j = count($this->facetCategories[$i]); $j--;) {
                if ($this->facetCategories[$i][$j] === $splitted[0]) {
                    return $this->facetCategories[$i];
                }
            }
        }
        return null;
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
