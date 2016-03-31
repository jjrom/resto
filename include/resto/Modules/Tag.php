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
     */
    public function getKeywords($properties, $geometry) {
        
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
        return array_merge($inputKeywords, array_merge($this->keywordsFromITag($properties, $geometry), $this->keywordsFromProperties($properties)));
        
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
            'keywords' => $this->getKeywords($featureArray['properties'], $featureArray['geometry'])
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
        foreach (array_values($keywords) as $value) {
            if (!$this->isValid($value)) {
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
            $iTagFeature = $iTag->tag($metadata, isset($this->options['iTag']['taggers']) ? $this->options['iTag']['taggers'] : array());
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
            $hash = RestoUtil::getHash($keyword);
            list($type, $normalized) = explode(':', $keyword, 2);
            $keywords[$hash] = array(
                'name' => ucfirst($normalized),
                'type' => $type
            );
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
        $hash = RestoUtil::getHash($propertyId, $parentHash);
        $exploded = explode(':', $propertyId);
        $value = array(
            'name' => isset($property['name']) ? $property['name'] : $defaultName,
            'normalized' => $exploded[1],
            'type' => isset($type) ? $type : $exploded[0]
        );
        if (isset($parentHash)) {
            $value['parentHash'] = $parentHash;
        }
        if (isset($property['area'])) {
            $value['value'] = $property['area'];
        }
        if (isset($property['pcover'])) {
            $value['value'] = $property['pcover'];
        }
        /*
         * Absolute coverage of geographical entity
         */
        if (isset($property['gcover'])) {
            $value['gcover'] = $property['gcover'];
        }
        
        return array(
            'hash' => $hash,
            'value' => $value
        );
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
                    $keywords[RestoUtil::getHash('collection:' . strtolower($properties['collection']))] = array(
                        'name' => $properties['collection'],
                        'type' => 'collection',
                    );
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

    /**
     * Process date keywords
     * 
     * @param array $properties
     * @return array
     */
    private function getDateKeywords($properties) {
        
        $keywords = array();
        
        $model = new RestoModel_default();
        
        /*
         * Year
         */
        $yearKeyword = $this->getYearKeyword(substr($properties[$model->searchFilters['time:start']['key']], 0, 4));
        $keywords[$yearKeyword['hash']] = $yearKeyword['value'];
        
        /*
         * Month
         */
        $monthKeyword = $this->getMonthKeyword(substr($properties[$model->searchFilters['time:start']['key']], 5, 2), $yearKeyword['hash']);
        $keywords[$monthKeyword['hash']] = $monthKeyword['value'];
        
        /*
         * Day
         */
        $dayKeyword = $this->getDayKeyword(substr($properties[$model->searchFilters['time:start']['key']], 8, 2), $monthKeyword['hash']);
        $keywords[$dayKeyword['hash']] = $dayKeyword['value'];
        
        return $keywords;
    }
    
    /**
     * Add a keyword for year
     * 
     * @param array $year
     */
    private function getYearKeyword($year) {
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
     * @param string $month
     * @param string $parentHash
     */
    private function getMonthKeyword($month, $parentHash) {
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
     * @param string $day
     * @param string $parentHash
     */
    private function getDayKeyword($day, $parentHash) {
        return array(
            'hash' => RestoUtil::getHash('day:' . $day),
            'value' => array(
                'name' => $day,
                'type' => 'day',
                'parentHash' => $parentHash   
            )
        );
    }
    
    /**
     * Get keywords from iTag 'population' property
     * 
     * @param array $populationProperty
     */
    private function getPopulationKeywords($populationProperty) {
        $hash = RestoUtil::getHash('other:population');
        return array(
            $hash => array(
                'name' => 'Population',
                'type' => 'other',
                'count' => $populationProperty['count'],
                'densityPerSquareKm' => $populationProperty['densityPerSquareKm']
            )
        );
    }
    
}
