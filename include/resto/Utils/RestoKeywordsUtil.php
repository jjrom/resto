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
            $keywords = $this->keywordsFromITag($iTag->tag(RestoGeometryUtil::geoJSONGeometryToWKT($geometry), isset($collection->context->modules['iTag']['keywords']) ? $collection->context->modules['iTag']['keywords'] : array()));
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
        
        if (!isset($iTagFeature) || !isset($iTagFeature['properties'])) {
            return $keywords;
        }

        /*
         * Continents, countries, regions and states
         */
        if (isset($iTagFeature['properties']['political'])) {
            $keywords = $this->getPoliticalKeywords($iTagFeature['properties']['political']);
        }
        
        /*
         * Landuse and landuse details
         */
        if (isset($iTagFeature['properties']['landCover'])) {
            $keywords = array_merge($keywords, $this->getLandCoverKeywords($iTagFeature['properties']['landCover']));
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
        $keywords = array();
        for ($i = 0, $ii = count($properties); $i < $ii; $i++) {
            $keyword = $this->getGenericKeyword($properties[$i], 'continent', null, null);
            $keywords[$keyword['hash']] = $keyword['value'];
            $keywords = array_merge($keywords, $this->getCountriesKeywords($properties[$i]['countries'], $keyword['hash']));
        }
        return $keywords;
    }

    private function getCountriesKeywords($properties, $parentHash) {
        $keywords = array();
        for ($i = 0, $ii = count($properties); $i < $ii; $i++) {
            $keyword = $this->getGenericKeyword($properties[$i], 'country', null, $parentHash);
            $keywords[$keyword['hash']] = $keyword['value'];
            if (isset($properties[$i]['regions'])) {
                $keywords = array_merge($keywords, $this->getRegionsKeywords($properties[$i]['regions'], $keyword['hash']));
            }
        }
        return $keywords;
    }

    private function getRegionsKeywords($properties, $parentHash) {
        $keywords = array();
        for ($i = 0, $ii = count($properties); $i < $ii; $i++) {
            $keyword = $this->getGenericKeyword($properties[$i], 'region', '_all', $parentHash);
            $keywords[$keyword['hash']] = $keyword['value'];
            $keywords = array_merge($keywords, $this->getStatesKeywords($properties[$i]['states'], $keyword['hash']));
        }
        return $keywords;
    }

    private function getStatesKeywords($properties, $parentHash) {
        $keywords = array();
        for ($i = 0, $ii = count($properties); $i < $ii; $i++) {
            $keyword = $this->getGenericKeyword($properties[$i], 'state', '_unknown', $parentHash);
            $keywords[$keyword['hash']] = $keyword['value'];
        }
        return $keywords;
    }
    
    
    private function getGenericKeyword($property, $type, $defaultName, $parentHash) {
        $id = isset($property['id']) ? $property['id'] : $type . ':' . $defaultName;
        $hash = RestoUtil::getHash($id, $parentHash);
        list($type, $normalized) = explode(':', $id, 2);
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
            
            /*
             * Process keyword for facet
             */
            $parentHash = null;
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
                }
                else  {
                    $parentHash = null;
                }
            }
            
        }
        
        /*
         * Get date keywords
         */
        return array_merge($keywords, $this->getDateKeywords($properties, $collection));
       
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
        $month = substr($properties[$collection->model->searchFilters['time:start']['key']], 0, 7);
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
        $day = substr($properties[$collection->model->searchFilters['time:start']['key']], 0, 10);
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
