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
 * Tag module
 * 
 * This module compute tags from feature.
 * 
 * It requires the iTag library (https://github.com/jjrom/itag)
 * 
 */
class Tag extends RestoModule {
    
    /*
     * resto reserved types
     */
    private $reservedTypes = array(
        'collection',
        'day',
        'year',
        'month',
        'season',
        'continent',
        'country',
        'region',
        'state',
        'landuse',
        'landuse_details',
        'location',
        'productType',
        'processingLevel',
        'platform',
        'instrument',
        'sensor'
    );
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoContext $user
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }

    /**
     * Run module - this function should be called by Resto.php
     * 
     * @param array $segments : route segments
     * @param array $data : POST or PUT parameters
     * 
     * @return string : result from run process in the $context->outputFormat
     */
    public function run($segments, $data = array()) {
        
        /*
         * Only administrators can access this module
         */
        if (!$this->user->isAdmin()) {
            RestoLogUtil::httpError(403);
        }

        /*
         * Switch on HTTP methods
         */
        switch ($this->context->method) {
            case 'PUT':
                return $this->processPUT($segments, $data);
            default:
                RestoLogUtil::httpError(404);
        }

    }
    
    /**
     * Compute keywords from properties array
     * 
     * @param array $properties
     * @param array $geometry (GeoJSON geometry)
     * @param boolean useItag 
     */
    public function getKeywords($properties, $geometry, $useItag = true) {
        
        /*
         * Initialize keywords array
         */
        $inputKeywords = isset($properties['keywords']) ? $properties['keywords'] : array();
        
        /*
         * Validate keywords
         */
        if (!$this->areValids($inputKeywords)) {
            RestoLogUtil::httpError(500, 'Invalid keywords property elements');
        }
        
        /*
         * Compute keywords from iTag and other properties
         */
        if ($useItag) {
            return array_merge($inputKeywords, array_merge($this->keywordsFromITag($properties, $geometry), $this->keywordsFromProperties($properties)));
        }
        else {
            return array_merge($inputKeywords, $this->keywordsFromProperties($properties));
        }
        
    }
    
    /**
     * Refresh tags for feature
     * 
     * @param RestoFeature $feature
     */
    public function refresh($feature) {
        $featureArray = $feature->toArray();
        $this->context->dbDriver->update(RestoDatabaseDriver::KEYWORDS, array(
            'feature' => $feature,
            'keywords' => $this->getKeywords($this->removeReservedProperties($featureArray['properties']), $featureArray['geometry'])
        ));
    }
    
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
    private function isValid($keyword) {
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
    private function areValids($keywords) {
        if (!isset($keywords) || !is_array($keywords)) {
            return false;
        }
        for ($i = count($keywords); $i--;) {
            if (!$this->isValid($keywords[$i])) {
                return false;
            }
        }
        return true;
    }
    
    /**
     *
     * Process HTTP PUT request on users
     *
     *      {featureid}   
     *
     * @param array $segments
     * @param array $data
     */
    private function processPUT($segments, $data) {

        /*
         * Check route pattern
         */
        if (!isset($segments[1]) || isset($segments[2])) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * First segment is the feature identifier
         */
        $feature = new RestoFeature($this->context, $this->user, array(
            'featureIdentifier' => $segments[0]
        ));
        if (!isset($feature)) {
            RestoLogUtil::httpError(404, 'Feature does not exist');
        }
        
        /*
         * Second segment is the action
         */
        switch ($segments[1]) {
            case 'refresh':
                $this->refresh($feature, $data);
                return RestoLogUtil::success('Recompute keywords for feature ' . $feature->identifier);
            default:
                RestoLogUtil::httpError(404);
        }
        
    }
    
    /**
     * Return a RESTo keywords array from an iTag Hierarchical feature
     * 
     * @param array $properties
     * @param array $geometry (GeoJSON)
     */
    private function keywordsFromITag($properties, $geometry) {
        
        /*
         * Initialize keywords array from faceted properties
         */
        $keywords = array();
        
        /* 
         * Compute keywords from iTag
         */
        if (isset($this->options['iTag'])) {
            $iTag = new iTag(array(
                'dbh' => $this->getDatabaseHandler(isset($this->options['iTag']['database']) ? $this->options['iTag']['database'] : null)
            ));
            $metadata = array(
                'footprint' => RestoGeometryUtil::geoJSONGeometryToWKT($geometry),
                'timestamp' => isset($properties['startDate']) ? $properties['startDate'] : null
            );

            /*
             * Exit from invalid iTag
             */
            try {
                $iTagFeature = $iTag->tag($metadata, isset($this->options['iTag']['taggers']) ? $this->options['iTag']['taggers'] : array());
            }
            catch (Exception $e) {
                RestoLogUtil::httpError($e->getCode(), $e->getMessage());
            }
        }
        
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
         * Physical data
         */
        if (isset($iTagFeature['content']['physical'])) {
            $keywords = array_merge($keywords, $this->getPhysicalKeywords($iTagFeature['content']['physical']));
        }
        
        /*
         * Landuse and landuse details
         */
        if (isset($iTagFeature['content']['landCover'])) {
            $keywords = array_merge($keywords, $this->getLandCoverKeywords($iTagFeature['content']['landCover']));
        }
        
        /*
         * Population
         */
        if (isset($iTagFeature['content']['population'])) {
            $keywords = array_merge($keywords, $this->getPopulationKeywords($iTagFeature['content']['population']));
        }
        
        /*
         * Keywords
         */
        if (isset($iTagFeature['content']['keywords'])) {
            $keywords = array_merge($keywords, $this->getAlwaysKeywords($iTagFeature['content']['keywords']));
        }
        
        return $keywords;
    }
    
    /**
     * Get keywords from iTag 'keywords' property
     * 
     * @param array $properties
     */
    private function getAlwaysKeywords($properties) {
        $keywords = array();
        foreach (array_values($properties) as $keyword) {
            $id = RestoUtil::getHash($keyword);
            list($type, $normalized) = explode(':', $keyword, 2);
            if (!$this->alreadyExists($keywords, $id)) {
                array_push($keywords, array(
                    'id' => $id,
                    'name' => ucfirst($normalized),
                    'type' => $type
                ));
            }
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
                $id = RestoUtil::getHash($landuse['id']);
                list($type, $normalized) = explode(':', $landuse['id'], 2);
                if (!$this->alreadyExists($keywords, $id)) {
                    array_push($keywords, array(
                        'id' => $id,
                        'name' => $landuse['name'],
                        'normalized' => $normalized,
                        'type' => $type,
                        'area' => $landuse['area'],
                        'value' => $landuse['pcover']
                    ));
                }
            }
        }
        
        /*
         * Landuse details
         */
        if (isset($properties['landCover']['landUseDetails'])) {
            foreach (array_values($properties['landCover']['landUseDetails']) as $landuse) {
                $parentHash = RestoUtil::getHash($landuse['parentId']);
                $id = RestoUtil::getHash($landuse['id'], $parentHash);
                list($type, $normalized) = explode(':', $landuse['id'], 2);
                if (!$this->alreadyExists($keywords, $id)) {
                    array_push($keywords, array(
                        'id' => $id,
                        'name' => $landuse['name'],
                        'normalized' => $normalized,
                        'type' => $type,
                        'parentHash' => $parentHash,
                        'area' => $landuse['area'],
                        'value' => $landuse['pcover']
                    ));
                }
            }
        }
        return $keywords;
    }
    
    /**
     * Get Physical keywords
     * 
     * @param array $properties
     */
    private function getPhysicalKeywords($properties) {
        return $this->getGenericKeywords($properties, array(
            'type' => null,
            'defaultName' => null,
            'parentHash' => null
        ));
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
    
    /**
     * Get continents keywords
     * 
     * @param array $properties
     */
    private function getContinentsKeywords($properties) {
        return $this->getGenericKeywords($properties, array(
            'type' => 'continent',
            'defaultName' => null,
            'parentHash' => null
        ));
    }

    /**
     * Get countries keywords
     * 
     * @param array $properties
     */
    private function getCountriesKeywords($properties, $parentHash) {
        return $this->getGenericKeywords($properties, array(
            'type' => 'country',
            'defaultName' => null,
            'parentHash' => $parentHash
        ));
    }

    /**
     * Get regions keywords
     * 
     * @param array $properties
     */
    private function getRegionsKeywords($properties, $parentHash) {
        return $this->getGenericKeywords($properties, array(
            'type' => 'region',
            'defaultName' => '_all',
            'parentHash' => $parentHash
        ));
    }

    /**
     * Get states keywords
     * 
     * @param array $properties
     */
    private function getStatesKeywords($properties, $parentHash) {
        return $this->getGenericKeywords($properties, array(
            'type' => 'state',
            'defaultName' => '_unknown',
            'parentHash' => $parentHash
        ));
    }
    
    /**
     * Get generic keyword
     * 
     * @param array $property
     * @param string $type
     * @param string $defaultName
     * @param string $parentHash
     * 
     */
    private function getGenericKeyword($property, $type, $defaultName, $parentHash) {
        $propertyId = isset($property['id']) ? $property['id'] : $type . ':' . $defaultName;
        $id = RestoUtil::getHash($propertyId, $parentHash);
        $exploded = explode(':', $propertyId);
        $keyword = array(
            'id' => $id,
            'name' => isset($property['name']) ? $property['name'] : $defaultName,
            'normalized' => $exploded[1],
            'type' => isset($type) ? $type : $exploded[0]
        );
        if (isset($parentHash)) {
            $keyword['parentHash'] = $parentHash;
        }
        if (isset($property['area'])) {
            $keyword['value'] = $property['area'];
        }
        if (isset($property['pcover'])) {
            $keyword['value'] = $property['pcover'];
        }
        /*
         * Absolute coverage of geographical entity
         */
        if (isset($property['gcover'])) {
            $keyword['gcover'] = $property['gcover'];
        }
        
        return $keyword;
    }
    
    /**
     * Get generic keywords
     * 
     * @param type $properties
     * @param type $options
     * @return type
     */
    private function getGenericKeywords($properties, $options) {
        $keywords = array();
        for ($i = 0, $ii = count($properties); $i < $ii; $i++) {
            $keyword = $this->getGenericKeyword($properties[$i], $options['type'], $options['defaultName'],  $options['parentHash']);
            if (!$this->alreadyExists($keywords, $keyword['id'])) {
                array_push($keywords, $keyword);
                switch ($options['type']) {
                    case 'continent':
                        $keywords = array_merge($keywords, $this->getCountriesKeywords($properties[$i]['countries'], $keyword['id']));
                        break;
                    case 'country':
                        if (isset($properties[$i]['regions'])) {
                            $keywords = array_merge($keywords, $this->getRegionsKeywords($properties[$i]['regions'], $keyword['id']));
                        }
                        break;
                    case 'region':
                        $keywords = array_merge($keywords, $this->getStatesKeywords($properties[$i]['states'], $keyword['id']));
                        break;
                    default:
                        break;
                }
            }
        }
        return $keywords;
    }
    
    
    /**
     * Return a RESTo keywords array from feature properties
     * 
     * @param array $properties
     */
    private function keywordsFromProperties($properties) {
     
        $keywords = array();
        
        /*
         * Roll over facet categories
         */
        foreach(array_values($this->context->dbDriver->facetUtil->facetCategories) as $facetCategory) {
            
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
                if (isset($properties['collection'])) {
                    $id = RestoUtil::getHash('collection:' . strtolower($properties['collection']));
                    if (!$this->alreadyExists($keywords, $id)) {
                        array_push($keywords, array(
                            'id' => $id,
                            'name' => $properties['collection'],
                            'type' => 'collection',
                        ));
                    }
                }
                continue;
            }
            
            $keywords = array_merge($keywords, $this->keywordsFromFacets($properties, $facetCategory));
            
        }
        
        /*
         * Get date keywords
         */
        return array_merge($keywords, $this->getDateKeywords($properties));
       
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
                $keyword = array();
                $id = RestoUtil::getHash($facetCategory[$i] . ':' . strtolower($properties[$facetCategory[$i]]), $parentHash);
                if (!$this->alreadyExists($keywords, $id)) {
                    $keyword = array(
                        'id' => $id,
                        'name' => $properties[$facetCategory[$i]],
                        'type' => $facetCategory[$i],
                    );
                    if (isset($parentHash)) {
                        $keyword['parentHash'] = $parentHash;
                    }
                    $parentHash = $id;
                    array_push($keywords, $keyword);
                }
            } else {
                $parentHash = null;
            }
        }
        return $keywords;
    }

    /**
     * Process date keywords
     * 
     * @param array $properties
     * @return array
     */
    private function getDateKeywords($properties) {
        
        $model = new RestoModel_default();
        
        /*
         * Year
         */
        $yearKeyword = $this->getYearKeyword(substr($properties[$model->searchFilters['time:start']['key']], 0, 4));
        
        /*
         * Month
         */
        $monthKeyword = $this->getMonthKeyword(substr($properties[$model->searchFilters['time:start']['key']], 5, 2), $yearKeyword['id']);
        
        /*
         * Day
         */
        $dayKeyword = $this->getDayKeyword(substr($properties[$model->searchFilters['time:start']['key']], 8, 2), $monthKeyword['id']);
        
        return array($yearKeyword, $monthKeyword, $dayKeyword);
    }
    
    /**
     * Add a keyword for year
     * 
     * @param array $year
     */
    private function getYearKeyword($year) {
        return array(
            'id' => RestoUtil::getHash('year:' . $year),
            'name' => $year,
            'type' => 'year'
        );
    }
    
    /**
     * Add a keyword for month
     * 
     * @param string $month
     * @param string $parentHash
     */
    private function getMonthKeyword($month, $parentHash) {
        return array(
            'id' =>  RestoUtil::getHash('month:' . $month, $parentHash),
            'name' => $month,
            'type' => 'month',
            'parentHash' => $parentHash
        );
    }
    
    /**
     * Add a keyword for day
     * 
     * @param string $day
     * @param string $parentHash
     */
    private function getDayKeyword($day, $parentHash) {
        return array(
            'id' => RestoUtil::getHash('day:' . $day),
            'name' => $day,
            'type' => 'day',
            'parentHash' => $parentHash   
        );
    }
    
    /**
     * Get keywords from iTag 'population' property
     * 
     * @param array $populationProperty
     */
    private function getPopulationKeywords($populationProperty) {
        return array(
            'id' => RestoUtil::getHash('other:population'),
            'name' => 'Population',
            'type' => 'other',
            'count' => $populationProperty['count'],
            'densityPerSquareKm' => $populationProperty['densityPerSquareKm']
        );
    }
    
    /**
     * Return true if id exists in keywords array
     * 
     * @param array $keywords
     * @param string id
     */
    private function alreadyExists($keywords, $id) {
        for ($i = count($keywords); $i--;) {
            if ($id === $keywords[$i]['id']) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Remove resto properties from input array to avoid double tagging
     * 
     * @param array $_properties
     * @return array
     */
    private function removeReservedProperties($_properties) {
        $properties = array();
        foreach ($_properties as $key => $value) {
            if ($key !== 'keywords') {
                $properties[$key] = $value;
            }
            else {
                $properties[$key] = array();
                for ($i = 0, $ii = count($_properties[$key]); $i < $ii; $i++) {
                    if (!in_array($_properties[$key][$i]['type'], $this->reservedTypes)) {
                        array_push($properties[$key], $_properties[$key][$i]);
                    }
                }
            }
        }
        return $properties;
    }
    
}
