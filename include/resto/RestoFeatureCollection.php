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
 * RESTo FeatureCollection
 */
class RestoFeatureCollection {
    
    /*
     * Context
     */
    public $context;
    
    /*
     * User
     */
    public $user;
    
    /*
     * Parent collection
     */
    private $defaultCollection;
    
    /*
     * FeatureCollectionDescription
     */
    private $description;
    
    /*
     * Features
     */
    private $restoFeatures;
    
    /*
     * All collections
     */
    private $collections = array();
    
    /*
     * Model of the main collection
     */
    private $defaultModel;
    
    /*
     * Total number of resources relative to the query
     * 
     * If "_rc" query parameter is set to true, each query include
     * returns a real count of the total number of resources relative to the query
     * Otherwise, the total count is not known and set to -1
     */
    private $totalCount = -1;
    
    /*
     * Query analyzer
     */
    private $queryAnalyzer;
    
    /**
     * Constructor 
     * 
     * @param RestoResto $context : Resto Context
     * @param RestoUser $user : Resto user
     * @param RestoCollection or array of RestoCollection $collections => First collection is the master collection !!
     */
    public function __construct($context, $user, $collections) {
        
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            RestoLogUtil::httpError(500, 'Context is undefined or not valid');
        }
        
        $this->context = $context;
        $this->user = $user;
        if (isset($this->context->modules['QueryAnalyzer'])) {
            $this->queryAnalyzer = new QueryAnalyzer($this->context, $this->user);
        }
 
        $this->initialize($collections);
        
    }
  
    /**
     * Output product description as a PHP array
     * 
     * @param boolean publicOutput
     */
    public function toArray($publicOutput = false) {
        $features = array();
        for ($i = 0, $l = count($this->restoFeatures); $i < $l; $i++) {
            $features[] = $this->restoFeatures[$i]->toArray($publicOutput);
        }
        return array_merge($this->description, array('features' => $features));
    }
    
    /**
     * Output product description as a GeoJSON FeatureCollection
     * 
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false) {
        return RestoUtil::json_format($this->toArray(true), $pretty);
    }
    
    /**
     * Output product description as an ATOM feed
     */
    public function toATOM() {
        
        /*
         * Initialize ATOM feed
         */
        $atomFeed = new RestoATOMFeed($this->description['properties']['id'], isset($this->description['properties']['title']) ? $this->description['properties']['title'] : '', $this->getATOMSubtitle());
       
        /*
         * Set collection elements
         */
        $atomFeed->setCollectionElements($this->description['properties']);
        
        /*
         * Add one entry per product
         */
        $atomFeed->addEntries($this->restoFeatures, $this->context);

        /*
         * Return ATOM result
         */
        return $atomFeed->toString();
    }
    
    /**
     * Initialize RestoFeatureCollection from database
     * 
     * @param RestoCollection or array of RestoCollection $collections
     * @return type
     */
    private function initialize($collections) {
        if (!isset($collections) || (is_array($collections) && count($collections) === 0)) {
            $this->defaultModel = new RestoModel_default();
        }
        else if (!is_array($collections)) {
            $this->defaultCollection = $collections;
            $this->defaultModel = $this->defaultCollection->model;
        }
        else {
            $this->collections = $collections;
            reset($collections);
            $this->defaultCollection = $this->collections[key($collections)];
            $this->defaultModel = $this->defaultCollection->model;
        }
        return $this->loadFromStore();
    }

    /**
     * Set featureCollection from database
     */
    private function loadFromStore() {
        
        /*
         * Request start time
         */
        $this->requestStartTime = microtime(true);
        
        /*
         * Clean search filters
         */
        $originalFilters = $this->getOriginalFilters();
        
        /*
         * Number of returned results is never greater than MAXIMUM_LIMIT
         */
        $limit = isset($originalFilters['count']) && is_numeric($originalFilters['count']) ? min($originalFilters['count'], isset($this->defaultModel->searchFilters['count']->maximumInclusive) ? $this->defaultModel->searchFilters['count']->maximumInclusive : 500) : $this->context->dbDriver->resultsPerPage;

        /*
         * Compute offset based on startPage or startIndex
         */
        $offset = $this->getOffset($originalFilters, $limit);
        
        /*
         * Query Analyzer 
         */
        $analysis = $this->analyze($originalFilters);
        
        /*
         * Completely not understood query - return an empty result without
         * launching a search on the database
         */
        if (isset($analysis['notUnderstood'])) {
             $this->restoFeatures = array();
             $this->totalCount = 0;
        }
        /*
         * Read features from database
         * If '_rc' parameter is set to true, then totalCount is also computed
         */   
        else {
            $forceCount = isset($this->context->query['_rc']) ? filter_var($this->context->query['_rc'], FILTER_VALIDATE_BOOLEAN) : false;
            $this->loadFeatures($analysis['searchFilters'], $limit, $offset, $forceCount);
        }
        
        /*
         * Set description
         */
        $this->setDescription($analysis, $offset, $limit);
        
    }
    
    /**
     * Set description
     * 
     * @param array $analysis
     * @param integer $offset
     * @param integer $limit
     */
    private function setDescription($analysis, $offset, $limit) {
        
        /*
         * Query is made from request parameters
         */
        $query = $this->cleanFilters($analysis['searchFilters']);
        
        /*
         * Sort results
         */
        $this->description = array(
            'type' => 'FeatureCollection',
            'properties' => array(
                'title' => $analysis['analysis']['query'],
                'id' => RestoUtil::UUIDv5((isset($this->defaultCollection) ? $this->defaultCollection->name : '*') . ':' . json_encode($query)),
                'totalResults' => $this->totalCount !== -1 ? $this->totalCount : null,
                'startIndex' => $offset + 1,
                'itemsPerPage' => count($this->restoFeatures),
                'query' => array(
                    'searchFilters' => $analysis['searchFilters'],
                    'analysis' => $analysis['analysis'],
                    'processingTime' => microtime(true) - $this->requestStartTime
                ),
                'links' => $this->getLinks($limit, $offset)
            )
        );
    }
    
    /**
     * Return an array of request parameters formated for output url
     * 
     * @param {array} $params - input params
     * 
     */
    private function writeRequestParams($params) {

        $arr = array();

        foreach ($params as $key => $value) {

            /*
             * Support key tuples
             */
            if (is_array($value)) {
                for ($i = 0, $l = count($value); $i < $l; $i++) {
                    if (isset($this->defaultModel->searchFilters[$key]['osKey'])) {
                        $arr[$this->defaultModel->searchFilters[$key]['osKey'] . '[]'] = $value[$i];
                    }
                    else {
                        $arr[$key . '[]'] = $value;
                    }
                }
            }
            else {
                if (isset($this->defaultModel->searchFilters[$key]['osKey'])) {
                    $arr[$this->defaultModel->searchFilters[$key]['osKey']] = $value;
                }
                else {
                    $arr[$key] = $value;
                }
            }
        }
        
        return $arr;
    }
    
    /**
     * Set restoFeatures and collections array
     *
     * @param array $params
     * @param integer $limit
     * @param integer $offset
     * @param integer $realCount
     */
    private function loadFeatures($params, $limit, $offset, $realCount) {
        
        /*
         * Convert productIdentifier to identifier if needed
         */
        if (isset($params['geo:uid']) && !RestoUtil::isValidUUID($params['geo:uid'])) {
            if (isset($this->defaultCollection)) {
                $params['geo:uid'] = RestoUtil::UUIDv5($this->defaultCollection->name . ':' . strtoupper($params['geo:uid']));
            }
        }
        
        /*
         * Get features array from database
         */
        $featuresArray = $this->context->dbDriver->get(RestoDatabaseDriver::FEATURES_DESCRIPTIONS, array(
            'context' => $this->context,
            'user' => $this->user,
            'collection' => isset($this->defaultCollection) ? $this->defaultCollection : null,
            'filters' => $params,
                'options' => array(
                    'limit' => $limit,
                    'offset' => $offset,
                    'count' => $realCount
                )
            )
        );
        
        /*
         * Load collections array
         */
        for ($i = 0, $l = count($featuresArray['features']); $i < $l; $i++) {
            if (isset($this->collections) && !isset($this->collections[$featuresArray['features'][$i]['properties']['collection']])) {
                $this->collections[$featuresArray['features'][$i]['properties']['collection']] = new RestoCollection($featuresArray['features'][$i]['properties']['collection'], $this->context, $this->user, array('autoload' => true));
            }
            $feature = new RestoFeature($this->context, $this->user, array(
                'featureArray' => $featuresArray['features'][$i],
                'collection' => isset($this->collections) && isset($featuresArray['features'][$i]['properties']['collection']) && $this->collections[$featuresArray['features'][$i]['properties']['collection']] ? $this->collections[$featuresArray['features'][$i]['properties']['collection']] : $this->defaultCollection
            ));
            if (isset($feature)) {
                $this->restoFeatures[] = $feature;
            }
        }
        
        /*
         * Total count
         */
        $this->totalCount = $featuresArray['totalcount'];
        
    }

    /**
     * Clean input parameters
     *  - change parameter keys to model parameter key
     *  - remove unset parameters
     *  - remove all HTML tags from input to avoid XSS injection
     */
    private function getOriginalFilters() {
        $params = array();
        foreach ($this->context->query as $key => $value) {
            foreach (array_keys($this->defaultModel->searchFilters) as $filterKey) {
                if ($key === $this->defaultModel->searchFilters[$filterKey]['osKey']) {
                    $params[$filterKey] = preg_replace('/<.*?>/', '', $value);
                }
            }
        }
        return $params;
    }
    
    /**
     * Search offset - first element starts at offset 0
     * Note: startPage has preseance over startIndex if both are specified in request
     * (see CEOS-BP-006 requirement of CEOS OpenSearch Best Practice document)
     *     
     * @param type $params
     */
    private function getOffset($params, $limit) {
        $offset = 0;
        if (isset($params['startPage']) && is_numeric($params['startPage']) && $params['startPage'] > 0) {
            $offset = (($params['startPage'] - 1) * $limit);
        }
        else if (isset($params['startIndex']) && is_numeric($params['startIndex']) && $params['startIndex'] > 0) {
            $offset = ($params['startIndex']) - 1;
        }
        return $offset;
    }
    
    /**
     * Get navigation links (i.e. next, previous, first, last)
     * 
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    private function getLinks($limit, $offset) {
        
        /*
         * Base links are always returned
         */
        $links = $this->getBaseLinks();
        
        /*
         * Get paging infos
         */
        $paging = $this->getPaging($limit, $offset);
        
        /*
         * Start page cannot be lower than 1
         */
        if ($paging['startPage'] > 1) {
            
            /*
             * Previous URL is the previous URL from the self URL
             * 
             */
            $links[] = $this->getLink('previous', '_previousCollectionLink', array(
                'startPage' => max($paging['startPage'] - 1, 1),
                'count' => $limit));
            
            /*
             * First URL is the first search URL i.e. with startPage = 1
             */
            $links[] = $this->getLink('first', '_firstCollectionLink', array(
                'startPage' => 1,
                'count' => $limit)
            );
        }

        /*
         * StartPage cannot be greater than the one from lastURL 
         */
        if ($paging['nextPage'] < $paging['totalPage']) {
            
            /*
             * Next URL is the next search URL from the self URL
             */
            $links[] = $this->getLink('next', '_nextCollectionLink', array(
                'startPage' => min($paging['startPage'], $paging['totalPage']),
                'count' => $limit)
            );
            
            /*
             * Last URL has the highest startIndex
             */
            $links[] = $this->getLink('last', '_lastCollectionLink', array(
                'startPage' => max($paging['totalPage'], 1),
                'count' => $limit)
            );
        }
        
        /*
         * If total = -1 then it means that total number of resources is unknown
         * The last index cannot be displayed
         */
        if ($this->totalCount === -1 && $paging['count'] >= $limit) {
            $links[] = $this->getLink('next', '_nextCollectionLink', array(
                'startPage' => $paging['startPage'] + 1,
                'count' => $limit)
            );
        }
        
        return $links;
        
    }
    
    /**
     * Return base links (i.e. links always present in response)
     */
    private function getBaseLinks() {
        return array(
            array(
                'rel' => 'self',
                'type' => RestoUtil::$contentTypes['json'],
                'title' => $this->context->dictionary->translate('_selfCollectionLink'),
                'href' => RestoUtil::updateUrl($this->context->getUrl(false), $this->writeRequestParams($this->context->query))
            ),
            array(
                'rel' => 'search',
                'type' => 'application/opensearchdescription+xml',
                'title' => $this->context->dictionary->translate('_osddLink'),
                'href' => $this->context->baseUrl . '/api/collections/' . (isset($this->defaultCollection) ? $this->defaultCollection->name . '/' : '') . 'describe.xml'
            )
        );
    }
    
    /**
     * Return Link
     * 
     * @param string $rel
     * @param string $title
     * @param array $params
     * @return array
     */
    private function getLink($rel, $title, $params) {
        
        /*
         * Do not set count if equal to default limit
         */
        if (isset($params['count']) && $params['count'] === $this->context->dbDriver->resultsPerPage) {
            unset($params['count']);
        }
            
        return array(
            'rel' => $rel,
            'type' => RestoUtil::$contentTypes['json'],
            'title' => $this->context->dictionary->translate($title),
            'href' => RestoUtil::updateUrl($this->context->getUrl(false), $this->writeRequestParams(array_merge($this->context->query, $params)))
        );
    }
    
    /**
     * Get start, next and last page from limit and offset
     * 
     * @param integer $limit
     * @param integer $offset
     */
    private function getPaging($limit, $offset) {
        $count = count($this->restoFeatures);
        $paging = array(
            'startPage' => 1,
            'nextPage' => 1,
            'totalPage' => 1,
            'count' => $count
        );
        if ($count > 0) {
            $paging = array(
                'startPage' => ceil(($offset + 1) / $limit),
                'nextPage' => $paging['startPage'] + 1,
                'totalPage' => ceil(($this->totalCount !== -1 ? $this->totalCount : $count) / $limit),
                'count' => $count
            );
        }
        return $paging;
    }
    
    /**
     * Return query array from search filters
     * 
     * @param array $searchFilters
     * @return array
     */
    private function cleanFilters($searchFilters) {
        $query = array();
        $exclude = array(
            'count',
            'startIndex',
            'startPage'
        );
        foreach ($searchFilters as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }
            $query[$key] = $key === 'searchTerms' ? stripslashes($value) : $value;
        }
        ksort($query);
        return $query;
    }
    
    /**
     * Get ATOM subtitle - construct from $this->description['properties']['title']
     * 
     * @return string
     */
    private function getATOMSubtitle() {
        $subtitle = '';
        if (isset($this->description['properties']['totalResults'])) {
            $subtitle = $this->context->dictionary->translate($this->description['properties']['totalResults'] === 1 ? '_oneResult' : '_multipleResult', $this->description['properties']['totalResults']);
        }
        $previous = isset($this->description['properties']['links']['previous']) ? '<a href="' . RestoUtil::updateUrlFormat($this->description['properties']['links']['previous'], 'atom') . '">' . $this->context->dictionary->translate('_previousPage') . '</a>&nbsp;' : '';
        $next = isset($this->description['properties']['links']['next']) ? '&nbsp;<a href="' . RestoUtil::updateUrlFormat($this->description['properties']['links']['next'], 'atom') . '">' . $this->context->dictionary->translate('_nextPage') . '</a>' : '';
        $subtitle .= isset($this->description['properties']['startIndex']) ? '&nbsp;|&nbsp;' . $previous . $this->context->dictionary->translate('_pagination', $this->description['properties']['startIndex'], $this->description['properties']['startIndex'] + 1) . $next : '';
        return $subtitle;
    }
    
    /**
     * Analyse searchTerms
     * 
     * @param array $params
     */
    private function analyze($params) {
        
        /*
         * No searchTerms specify - leave input search filters untouched
         */
        if (empty($params['searchTerms'])) {
            return array(
                'searchFilters' => $params,
                'analysis' => array(
                    'query' => ''
                )
            );
        }
        
        /*
         * Analyse query
         */
        $analysis = $this->queryAnalyzer->analyze($params['searchTerms']);
        
        /*
         * Not understood - return error
         */
        if (empty($analysis['analyze']['What']) && empty($analysis['analyze']['When']) && empty($analysis['analyze']['Where'])) {
            return array(
                'notUnderstood' => true,
                'searchFilters' => $params,
                'analysis' => $analysis
            );
        }
        
        /*
         * What
         */
        $params = $this->setWhatFilters($analysis['analyze']['What'], $params);
        
        /*
         * When
         */
        $params = $this->setWhenFilters($analysis['analyze']['When'], $params);
        
        /*
         * Where
         */
        $params = $this->setWhereFilters($analysis['analyze']['Where'], $params);
        
        return array(
            'searchFilters' => $params,
            'analysis' => $analysis
        );
    }
    
    /**
     * Set what filters from query analysis
     * 
     * @param array $what
     * @param array $params
     */
    private function setWhatFilters($what, $params) {
        $params['searchTerms'] = array();
        foreach($what as $key => $value) {
            if ($key === 'searchTerms') {
                for ($i = count($value); $i--;) {
                    $params['searchTerms'][] = $value[$i];
                }
            }
            else {
                $params[$key] = $value;
            }
        }
        return $params;
    }
    
    /**
     * Set when filters from query analysis
     * 
     * @param array $when
     * @param array $params
     */
    private function setWhenFilters($when, $params) {
        foreach($when as $key => $value) {
            
            /*
             * times is an array of time:start/time:end pairs
             * TODO : Currently only one pair is supported
             */
            if ($key === 'times') {
                $params = array_merge($params, $this->timesToOpenSearch($value));
            }
            else {
                $params['searchTerms'][] = $key . ':' . $value;
            }
        }
        return $params;
    }
    
    /**
     * 
     * @param array $times
     */
    private function timesToOpenSearch($times) {
        $params = array();
        for ($i = 0, $ii = count($times); $i < $ii; $i++) {
            foreach($times[$i] as $key => $value) {
                $params[$key] = $value;
            }
        }
        return $params;
    }
    
    /**
     * Set location filters from query analysis
     * 
     * @param array $where
     * @param array $params
     */
    private function setWhereFilters($where, $params) {
        for ($i = count($where); $i--;) {
            
            /*
             * Only one toponym is supported (the last one) 
             */
            if (isset($where[$i]['geo:lon'])) {
                $params['geo:lon'] = $where[$i]['geo:lon'];
                $params['geo:lat'] = $where[$i]['geo:lat'];
            }
            /*
             * Searching for keywords is faster than geometry
             */
            else if (isset($where[$i]['searchTerms'])) {
                $params['searchTerms'][] = $where[$i]['searchTerms'];
            }
            /*
             * Geometry
             */
            else {
                $params['geo:geometry'] = $where[$i]['geometry'];
            }
        }
        $params['searchTerms'] = join(' ', $params['searchTerms']);
        return $params;
    }
    
    
}
