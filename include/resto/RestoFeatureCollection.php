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
 * RESTo FeatureCollection
 */
class RestoFeatureCollection {
    
    /*
     * Model of the main collection
     */
    public $defaultModel;
    
    /*
     * Parent collection
     */
    public $defaultCollection;
    
    /*
     * Context
     */
    public $context;
    
    /*
     * User
     */
    public $user;
    
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
    private $collections;
    
    /*
     * Total number of resources relative to the query
     * 
     * If "_rc" query parameter is set to true, each query include
     * returns a real count of the total number of resources relative to the query
     * Otherwise, the total count is not known and set to -1
     */
    private $totalCount = -1;
    
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
        return $this->initialize($collections);
        
    }
  
    /**
     * Output product description as a PHP array
     */
    public function toArray() {
        $features = array();
        for ($i = 0, $l = count($this->restoFeatures); $i < $l; $i++) {
            $features[] = $this->restoFeatures[$i]->toArray();
        }
        return array_merge($this->description, array('features' => $features));
    }
    
    /**
     * Output product description as a GeoJSON FeatureCollection
     * 
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false) {
        return RestoUtil::json_format($this->toArray(), $pretty);
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
         * Update outputFormat links except for OSDD 'search'
         */
        $this->setATOMLinks($atomFeed);
        
        /*
         * Total results, startIndex and itemsPerpage
         */
        if (isset($this->description['properties']['totalResults'])) {
            $atomFeed->writeElement('os:totalResults', $this->description['properties']['totalResults']);
        }
        if (isset($this->description['properties']['startIndex'])) {
            $atomFeed->writeElement('os:startIndex', $this->description['properties']['startIndex']);
        }
        if (isset($this->description['properties']['itemsPerPage'])) {
            $atomFeed->writeElement('os:itemsPerPage', $this->description['properties']['itemsPerPage']);
        }

        /*
         * Query element
         */
        $this->setATOMQuery($atomFeed);
        
        /*
         * Add one entry per product
         */
        for ($i = 0, $l = count($this->restoFeatures); $i < $l; $i++) {
            $this->restoFeatures[$i]->addAtomEntry($atomFeed);
        }

        /*
         * Return ATOM result
         */
        return $atomFeed->toString();
    }
    
    /**
     * Set ATOM feed links element from request parameters
     * 
     * @param RestoATOMFeed $atomFeed
     */
    private function setATOMLinks($atomFeed) {
        if (is_array($this->description['properties']['links'])) {
            for ($i = 0, $l = count($this->description['properties']['links']); $i < $l; $i++) {
                $atomFeed->startElement('link');
                $atomFeed->writeAttributes(array(
                    'rel' => $this->description['properties']['links'][$i]['rel'],
                    'title' => $this->description['properties']['links'][$i]['title']
                ));
                if ($this->description['properties']['links'][$i]['type'] === 'application/opensearchdescription+xml') {
                    $atomFeed->writeAttributes(array(
                        'type' => $this->description['properties']['links'][$i]['type'],
                        'href' => $this->description['properties']['links'][$i]['href']
                    ));
                }
                else {
                    $atomFeed->writeAttributes(array(
                        'type' => RestoUtil::$contentTypes['atom'],
                        'href' => RestoUtil::updateURLFormat($this->description['properties']['links'][$i]['href'], 'atom')
                    ));
                }
                $atomFeed->endElement(); // link
            }
        }
    }
    
    /**
     * Set ATOM feed Query element from request parameters
     * 
     * @param RestoATOMFeed $atomFeed
     */
    private function setATOMQuery($atomFeed) {
        $atomFeed->startElement('os:Query');
        $atomFeed->writeAttributes(array('role' => 'request'));
        if (isset($this->description['properties']['query'])) {
            $atomFeed->writeAttributes($this->description['properties']['query']['original']);
        }
        $atomFeed->endElement();
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
        $previous = isset($this->description['properties']['links']['previous']) ? '<a href="' . RestoUtil::updateURLFormat($this->description['properties']['links']['previous'], 'atom') . '">' . $this->context->dictionary->translate('_previousPage') . '</a>&nbsp;' : '';
        $next = isset($this->description['properties']['links']['next']) ? '&nbsp;<a href="' . RestoUtil::updateURLFormat($this->description['properties']['links']['next'], 'atom') . '">' . $this->context->dictionary->translate('_nextPage') . '</a>' : '';
        $subtitle .= isset($this->description['properties']['startIndex']) ? '&nbsp;|&nbsp;' . $previous . $this->context->dictionary->translate('_pagination', $this->description['properties']['startIndex'], $this->description['properties']['startIndex'] + 1) . $next : '';
        return $subtitle;
    }
    
    /**
     * Initialize RestoFeatureCollection from database
     * 
     * @param RestoCollection or array of RestoCollection $collections
     * @return type
     */
    private function initialize($collections) {
        if (!isset($collections) || (is_array($collections) && count($collections) === 0)) {
            $this->defaultModel = new RestoModel_default($this->context, $this->user);
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
        $requestStartTime = microtime(true);
        
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
        $searchFilters = $this->getSearchFilters($originalFilters);
        
        /*
         * Read features from database
         * If '_rc' parameter is set to true, then totalCount is also computed
         */
        $this->loadFeatures($searchFilters, $limit, $offset, isset($this->context->query['_rc']) && filter_var($this->context->query['_rc'], FILTER_VALIDATE_BOOLEAN) ? true : false);
        
        /*
         * Query is made from request parameters
         */
        $query = $this->cleanFilters($searchFilters);
        
        /*
         * Sort results
         */
        $this->description = array(
            'type' => 'FeatureCollection',
            'properties' => array(
                'title' => isset($query['searchTerms']) ? $query['searchTerms'] : '',
                'id' => RestoUtil::UUIDv5((isset($this->defaultCollection) ? $this->defaultCollection->name : '*') . ':' . json_encode($query)),
                'totalResults' => $this->totalCount !== -1 ? $this->totalCount : null,
                'startIndex' => $offset + 1,
                'itemsPerPage' => count($this->restoFeatures),
                'query' => array(
                    'original' => $originalFilters,
                    'real' => $query,
                    'processingTime' => microtime(true) - $requestStartTime
                ),
                'links' => $this->getLinks($limit, $offset)
            )
        );
        
        return $this;
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
     * @param boolean $realCount
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
            'model' => $this->defaultModel,
            'collectionName' => isset($this->defaultCollection) ? $this->defaultCollection->name : null,
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
        for ($i = 0, $l = count($featuresArray); $i < $l; $i++) {
            if (isset($this->collections) && !isset($this->collections[$featuresArray[$i]['collection']])) {
                $this->collections[$featuresArray[$i]['collection']] = new RestoCollection($featuresArray[$i]['collection'], $this->context, $this->user, array('autoload' => true));
            }
            $this->restoFeatures[] = new RestoFeature($featuresArray[$i], $this->context, $this->user, array(
                'collection' => isset($this->collections) && isset($featuresArray[$i]['collection']) && $this->collections[$featuresArray[$i]['collection']] ? $this->collections[$featuresArray[$i]['collection']] : $this->defaultCollection,
                'forceCollectionName' => isset($this->defaultCollection) ? true : false)
            );
            $this->totalCount = isset($featuresArray[$i]['totalcount']) ? $featuresArray[$i]['totalcount'] : -1;
        }
        
    }

    /**
     * Clean input parameters
     *  - change parameter keys to model parameter key
     *  - remove unset parameters
     *  - remove all HTML tags from input to avoid XSS injection
     *  - convert productIdentifier to identifier
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
         * Get start, next and last page from limit and offset
         */
        $startPage = 1;
        $nextPage = 1;
        $totalPage = 1;
        $count = count($this->restoFeatures);
        if ($count > 0) {
            $startPage = ceil(($offset + 1) / $limit);
            $nextPage = $startPage + 1;
            $totalPage = ceil(($this->totalCount !== -1 ? $this->totalCount : $count) / $limit);
        }
        
        /*
         * Start page cannot be lower than 1
         */
        if ($startPage > 1) {
            
            /*
             * Previous URL is the previous URL from the self URL
             * 
             */
            $links[] = $this->getLink('previous', '_previousCollectionLink', array(
                'startPage' => max($startPage - 1, 1),
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
        if ($nextPage < $totalPage) {
            
            /*
             * Next URL is the next search URL from the self URL
             */
            $links[] = $this->getLink('next', '_nextCollectionLink', array(
                'startPage' => min($startPage, $totalPage),
                'count' => $limit)
            );
            
            /*
             * Last URL has the highest startIndex
             */
            $links[] = $this->getLink('last', '_lastCollectionLink', array(
                'startPage' => max($totalPage, 1),
                'count' => $limit)
            );
        }
        
        /*
         * If total = -1 then it means that total number of resources is unknown
         * The last index cannot be displayed
         */
        if ($this->totalCount === -1 && $count >= $limit) {
            $links[] = $this->getLink('next', '_nextCollectionLink', array(
                'startPage' => $startPage + 1,
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
        return array(
            'rel' => $rel,
            'type' => RestoUtil::$contentTypes['json'],
            'title' => $this->context->dictionary->translate($title),
            'href' => RestoUtil::updateUrl($this->context->getUrl(false), $this->writeRequestParams(array_merge($this->context->query, $params)))
        );
    }
    
    /**
     * Returned analyzed filters
     * 
     * @param array $params
     */
    private function getSearchFilters($params) {
        if (isset($this->context->modules['QueryAnalyzer'])) {
            $qa = new QueryAnalyzer($this->context, $this->user);
            $analyzis = $qa->analyze($params, $this->defaultModel);
            return $analyzis['analyze'];
        }
        return $params;
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
        return ksort($query);
    }
}
