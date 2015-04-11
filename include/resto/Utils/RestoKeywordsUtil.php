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
 * RESTo keywords Utilities
 */
class RestoKeywordsUtil {
    
    /**
     * Constructor
     */
    public function __construct() {} 
   
    /**
     * Check if keyword is valid
     * 
     * Valid keyword structure :
     *      array(
     *          "name" => name
     *          "type" => type
     *          "parentId" => id, // parentType:parentValue
     *          "value" => value or array()
     *      )
     * 
     * Note: only 'name' and 'type' are mandatory
     * 
     * @param array $keyword
     * 
     */
    public function isValid($keyword) {
        if (!isset($keyword) || !is_array($keyword)) {
            return false;
        }
        if (!isset($keyword['name']) || !isset($keyword['type'])) {
            return false;
        }
        return true;
    }
    
    /**
     * Check if an array of keywords is valid
     * 
     * @param array $keywords
     * @return boolean
     */
    public function areValids($keywords) {
        if (!isset($keywords) || !is_array($keywords)) {
            return false;
        }
        foreach (array_values($keywords) as $value) {
            if (!$this->isValid($value)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Compute keywords from properties array
     * 
     * @param array $properties
     * @param array $geometry (GeoJSON)
     * @param RestoCollection $collection
     */
    public function computeKeywords($properties, $geometry, $collection) {
        
        /*
         * Initialize empty keywords array
         */
        $keywords = array();
        
        /* 
         * Compute keywords from iTag
         */
        if (isset($collection->context->modules['iTag'])) {
            $iTag = new iTag(isset($collection->context->modules['iTag']['database']) && isset($collection->context->modules['iTag']['database']['dbname']) ? $collection->context->modules['iTag']['database'] : array('dbh' => $collection->context->dbDriver->dbh));
            $metadata = array(
                'footprint' => RestoGeometryUtil::geoJSONGeometryToWKT($geometry)
            );
            $keywords = $this->keywordsFromITag($iTag->tag($metadata, isset($collection->context->modules['iTag']['taggers']) ? $collection->context->modules['iTag']['taggers'] : array()));
        }
        
        /*
         * Compute keywords from other properties
         */
        if (isset($keywords)) {
            $keywords = array_merge($keywords, $this->keywordsFromProperties($properties, $collection));
        }
        
        return $keywords;
        
    }
    
    /**
     * Return a RESTo keywords array from an iTag Hierarchical feature
     * 
     * @param array $iTagFeature
     */
    private function keywordsFromITag($iTagFeature) {

        /*
         * Initialize keywords array from faceted properties
         */
        $keywords = array();
        
        if (!isset($iTagFeature) || !isset($iTagFeature['content'])) {
            return $keywords;
        }

        /*
         * Continents, countries, regions and states
         */
        if (isset($iTagFeature['content']['political'])) {
            $keywords = $this->getPoliticalKeywords($iTagFeature['content']['political']);
        }
        
        /*
         * Landuse and landuse details
         */
        if (isset($iTagFeature['content']['landCover'])) {
            $keywords = array_merge($keywords, $this->getLandCoverKeywords($iTagFeature['content']['landCover']));
        }
        
        return $keywords;
    }
    
    /**
     * Get Landcover keywords
     * 
     * @param array $properties
     */
    private function getLandCoverKeywords($properties) {
        
        $keywords = array();
        
        /*
         * Landuse
         */
        if (isset($properties['landUse'])) {
            foreach (array_values($properties['landUse']) as $landuse) {
                $hash = RestoUtil::getHash($landuse['id']);
                list($type, $normalized) = explode(':', $landuse['id'], 2);
                $keywords[$hash] = array(
                    'name' => $landuse['name'],
                    'normalized' => $normalized,
                    'type' => $type,
                    'area' => $landuse['area'],
                    'value' => $landuse['pcover']
                );
            }
        }
        
        /*
         * Landuse details
         */
        if (isset($properties['landCover']['landUseDetails'])) {
            foreach (array_values($properties['landCover']['landUseDetails']) as $landuse) {
                $parentHash = RestoUtil::getHash($landuse['parentId']);
                $hash = RestoUtil::getHash($landuse['id'], $parentHash);
                list($type, $normalized) = explode(':', $landuse['id'], 2);
                $keywords[$hash] = array(
                    'name' => $landuse['name'],
                    'normalized' => $normalized,
                    'type' => $type,
                    'parentHash' => $parentHash,
                    'area' => $landuse['area'],
                    'value' => $landuse['pcover']
                );
            }
        }
        return $keywords;
    }
    
    /**
     * Get political keywords
     * 
     * @param array $properties
     */
    private function getPoliticalKeywords($properties) {
        if (isset($properties['continents'])) {
            return $this->getContinentsKeywords($properties['continents']);
        }
        return array();
    }
    
    private function getContinentsKeywords($properties) {
        return $this->getGenericKeywords($properties, array(
            'type' => 'continent',
            'defaultName' => null,
            'parentHash' => null
        ));
    }

    private function getCountriesKeywords($properties, $parentHash) {
        return $this->getGenericKeywords($properties, array(
            'type' => 'country',
            'defaultName' => null,
            'parentHash' => $parentHash
        ));
    }

    private function getRegionsKeywords($properties, $parentHash) {
        return $this->getGenericKeywords($properties, array(
            'type' => 'region',
            'defaultName' => '_all',
            'parentHash' => $parentHash
        ));
    }

    private function getStatesKeywords($properties, $parentHash) {
        return $this->getGenericKeywords($properties, array(
            'type' => 'state',
            'defaultName' => '_unknown',
            'parentHash' => $parentHash
        ));
    }
    
    
    private function getGenericKeyword($property, $type, $defaultName, $parentHash) {
        $propertyId = isset($property['id']) ? $property['id'] : $type . ':' . $defaultName;
        $hash = RestoUtil::getHash($propertyId, $parentHash);
        list($type, $normalized) = explode(':', $propertyId, 2);
        $value = array(
            'name' => isset($property['name']) ? $property['name'] : $defaultName,
            'normalized' => $normalized,
            'type' => $type
        );
        if (isset($parentHash)) {
            $value['parentHash'] = $parentHash;
        }
        if (isset($property['pcover'])) {
            $value['value'] = $property['pcover'];
        }
        return array(
            'hash' => $hash,
            'value' => $value
        );
    }
    
    private function getGenericKeywords($properties, $options) {
        $keywords = array();
        for ($i = 0, $ii = count($properties); $i < $ii; $i++) {
            $keyword = $this->getGenericKeyword($properties[$i], $options['type'], $options['defaultName'],  $options['parentHash']);
            $keywords[$keyword['hash']] = $keyword['value'];
            switch ($options['type']) {
                case 'continent':
                    $keywords = array_merge($keywords, $this->getCountriesKeywords($properties[$i]['countries'], $keyword['hash']));
                    break;
                case 'country':
                    if (isset($properties[$i]['regions'])) {
                        $keywords = array_merge($keywords, $this->getRegionsKeywords($properties[$i]['regions'], $keyword['hash']));
                    }
                    break;
                case 'region':
                    $keywords = array_merge($keywords, $this->getStatesKeywords($properties[$i]['states'], $keyword['hash']));
                    break;
                default:
                    break;
            }
        }
        return $keywords;
    }
    
    
    /**
     * Return a RESTo keywords array from feature properties
     * 
     * @param array $properties
     */
    private function keywordsFromProperties($properties, $collection) {
     
        $keywords = array();
        
        /*
         * Roll over facet categories
         */
        foreach(array_values($collection->context->dbDriver->facetUtil->facetCategories) as $facetCategory) {
            
            /*
             * Already processed keywords
             */
            if (in_array($facetCategory[0], array('continent', 'landuse', 'year'))) {
                continue;
            }
            
            /*
             * Collection
             */
            if ($facetCategory[0] === 'collection') {
                $keywords[RestoUtil::getHash('collection:' . strtolower($collection->name))] = array(
                    'name' => $collection->name,
                    'type' => 'collection',
                );
                continue;
            }
            
            $keywords = array_merge($keywords, $this->keywordsFromFacets($properties, $facetCategory));
            
        }
        
        /*
         * Get date keywords
         */
        return array_merge($keywords, $this->getDateKeywords($properties, $collection));
       
    }
    
    /**
     * Process keywords for facets
     * 
     * @param array $properties
     * @param array $facetCategory
     * @return type
     */
    private function keywordsFromFacets($properties, $facetCategory) {
        
        $parentHash = null;
        $keywords = array();
        for ($i = 0, $ii = count($facetCategory); $i < $ii; $i++) {
            if (isset($properties[$facetCategory[$i]])) {
                $hash = RestoUtil::getHash($facetCategory[$i] . ':' . strtolower($properties[$facetCategory[$i]]), $parentHash);
                $keywords[$hash] = array(
                    'name' => $properties[$facetCategory[$i]],
                    'type' => $facetCategory[$i],
                );
                if (isset($parentHash)) {
                    $keywords[$hash]['parentHash'] = $parentHash;
                }
                $parentHash = $hash;
            } else {
                $parentHash = null;
            }
        }
        return $keywords;
    }

    private function getDateKeywords($properties, $collection) {
        
        $keywords = array();
        
        /*
         * Year
         */
        $yearKeyword = $this->getYearKeyword($properties, $collection);
        $keywords[$yearKeyword['hash']] = $yearKeyword['value'];
        
        /*
         * Month
         */
        $monthKeyword = $this->getMonthKeyword($properties, $collection, $yearKeyword['hash']);
        $keywords[$monthKeyword['hash']] = $monthKeyword['value'];
        
        /*
         * Day
         */
        $dayKeyword = $this->getDayKeyword($properties, $collection, $monthKeyword['hash']);
        $keywords[$dayKeyword['hash']] = $dayKeyword['value'];
        
        return $keywords;
    }
    
    /**
     * Add a keyword for year
     * 
     * @param array $properties
     * @param RestoCollection $collection
     */
    private function getYearKeyword($properties, $collection) {
        $year = substr($properties[$collection->model->searchFilters['time:start']['key']], 0, 4);
        return array(
            'hash' => RestoUtil::getHash('year:' . $year),
            'value' => array(
                'name' => $year,
                'type' => 'year'
            )
        );
    }
    
    /**
     * Add a keyword for month
     * 
     * @param array $properties
     * @param RestoCollection $collection
     * @param string $parentHash
     */
    private function getMonthKeyword($properties, $collection, $parentHash) {
        $month = substr($properties[$collection->model->searchFilters['time:start']['key']], 5, 2);
        return array(
            'hash' => RestoUtil::getHash('month:' . $month, $parentHash),
            'value' => array(
                'name' => $month,
                'type' => 'month',
                'parentHash' => $parentHash
            )
        );
    }
    
    /**
     * Add a keyword for day
     * 
     * @param array $properties
     * @param RestoCollection $collection
     * @param string $parentHash
     */
    private function getDayKeyword($properties, $collection, $parentHash) {
        $day = substr($properties[$collection->model->searchFilters['time:start']['key']], 8, 2);
        return array(
            'hash' => RestoUtil::getHash('day:' . $day),
            'value' => array(
                'name' => $day,
                'type' => 'day',
                'parentHash' => $parentHash   
            )
        );
    }
    
}
