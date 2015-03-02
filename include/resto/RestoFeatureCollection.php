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
    
    /**
     * Constructor 
     * 
     * @param RestoResto $context : Resto Context
     * @param RestoUser $user : Resto user
     * @param RestoCollection or array of RestoCollection $collections => First collection is the master collection !!
     */
    public function __construct($context, $user, $collections) {
        
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            throw new Exception('Context is undefined or not valid', 500);
        }
        
        $this->context = $context;
        $this->user = $user;
        return $this->initialize($collections);
        
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
         * Real count - if set to true, each query include returns a real count
         * of the total number of resources relative to the query
         * Otherwise, the total count is not known
         */
        $realCount = isset($this->context->query['_rc']) && RestoUtil::toBoolean($this->context->query['_rc']) === true ? true : false;
       
        /*
         * Input parameters :
         *  - change parameter keys to model parameter key
         *  - remove unset parameters
         *  - remove all HTML tags from input to avoid XSS injection
         *  - convert productIdentifier to identifier
         */
        $params = array();
        foreach ($this->context->query as $key => $value) {
            foreach (array_keys($this->defaultModel->searchFilters) as $filterKey) {
                if ($key === $this->defaultModel->searchFilters[$filterKey]['osKey']) {
                    $params[$filterKey] = preg_replace('/<.*?>/', '', $value);
                }
            }
        }
        
        /*
         * Number of returned results is never greater than MAXIMUM_LIMIT
         */
        $limit = isset($params['count']) && is_numeric($params['count']) ? min($params['count'], isset($this->defaultModel->searchFilters['count']->maximumInclusive) ? $this->defaultModel->searchFilters['count']->maximumInclusive : 500) : $this->context->dbDriver->resultsPerPage;

        /*
         * Search offset - first element starts at offset 0
         * Note: startPage has preseance over startIndex if both are specified in request
         * (see CEOS-BP-006 requirement of CEOS OpenSearch Best Practice document)
         */
        $offset = 0;
        if (isset($params['startPage']) && is_numeric($params['startPage']) && $params['startPage'] > 0) {
            $offset = (($params['startPage'] - 1) * $limit);
        }
        else if (isset($params['startIndex']) && is_numeric($params['startIndex']) && $params['startIndex'] > 0) {
            $offset = ($params['startIndex']) - 1;
        }
        
        /*
         * Query Analyzer 
         */
        $original = $params;
        $processingTime = null;
        if (isset($this->context->modules['QueryAnalyzer'])) {
            $qa = new QueryAnalyzer($this->context, $this->user);
            $analyzis = $qa->analyze($params, $this->defaultModel);
            $params = $analyzis['analyze'];
            $processingTime = $analyzis['queryAnalyzeProcessingTime']; 
        }
        

        /*
         * Convert productIdentifier to identifier if needed
         */
        if (isset($params['geo:uid']) && !RestoUtil::isValidUUID($params['geo:uid'])) {
            if (isset($this->defaultCollection)) {
                $params['geo:uid'] = RestoUtil::UUIDv5($this->defaultCollection->name . ':' . strtoupper($params['geo:uid']));
            }
        }
        
        /*
         * Get features array
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
        for ($i = 0, $l = count($featuresArray); $i < $l; $i++) {
            if (isset($this->collections) && !isset($this->collections[$featuresArray[$i]['collection']])) {
                $this->collections[$featuresArray[$i]['collection']] = new RestoCollection($featuresArray[$i]['collection'], $this->context, $this->user, array('autoload' => true));
            }
            $this->restoFeatures[] = new RestoFeature($featuresArray[$i], $this->context, $this->user, isset($this->collections) && isset($featuresArray[$i]['collection']) && $this->collections[$featuresArray[$i]['collection']] ? $this->collections[$featuresArray[$i]['collection']] : $this->defaultCollection, isset($this->defaultCollection) ? true : false);
            $total = isset($featuresArray[$i]['totalcount']) ? $featuresArray[$i]['totalcount'] : -1;
        }
        
        /*
         * Compute links i.e. self, first, next, previous and last URLs
         */
        $count = count($this->restoFeatures);
        $startIndex = $offset + 1;
        $total = isset($total) ? $total : $count;
        if ($count > 0) {
            $startPage = ceil($startIndex / $limit);
            $nextPage = $startPage + 1;
            $totalPage = ceil($total / $limit);
        }
        else {
            $startPage = 1;
            $nextPage = 1;
            $totalPage = 1;
        }
        
        /*
         * Query is made from request parameters
         */
        $query = array();
        $exclude = array(
            'count',
            'startIndex',
            'startPage'
        );
        foreach ($params as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }
            $query[$key] = $key === 'searchTerms' ? stripslashes($value) : $value;
        }

        /*
         * Determine if real query contain a location i.e.
         * geo:name or searchTerms containing keyword of type 'city', 'country', 'state', 'region' or 'continent'
         */
        $hasLocation = false;
        if (isset($params['geo:name'])) {
            $hasLocation = true;
        }
        else {
            if (isset($params['searchTerms'])) {
                $splitted = RestoUtil::splitString($params['searchTerms']);
                for ($i = count($splitted); $i--;) {
                    $arr = explode(':', $splitted[$i]);
                    if ($arr[0] === 'continent' || $arr[0] === 'country' || $arr[0] === 'region' || $arr[0] === 'state'|| $arr[0] === 'city') {
                        $hasLocation = true;
                        break;
                    }
                }
            }
        }
        
        /*
         * Request stop time
         */
        $requestStopTime = microtime(true);

        /*
         * Links
         */
        $url = $this->context->getUrl(false);
        
        $links = array(
            array(
                'rel' => 'self',
                'type' => RestoUtil::$contentTypes['json'],
                'title' => $this->context->dictionary->translate('_selfCollectionLink'),
                'href' => RestoUtil::updateUrl($url, $this->writeRequestParams($this->context->query))
            ),
            array(
                'rel' => 'search',
                'type' => 'application/opensearchdescription+xml',
                'title' => $this->context->dictionary->translate('_osddLink'),
                'href' => $this->context->baseUrl . 'api/collections/' . (isset($this->defaultCollection) ? $this->defaultCollection->name . '/' : '') . 'describe.xml'
            )
        );
        
        /*
         * Previous URL is the previous URL from the self URL
         * startPage cannot be lower than 1
         */
        if ($startPage > 1) {
            $links[] = array(
                'rel' => 'previous',
                'type' => RestoUtil::$contentTypes['json'],
                'title' => $this->context->dictionary->translate('_previousCollectionLink'),
                'href' => RestoUtil::updateUrl($url, $this->writeRequestParams(array_merge($this->context->query, array(
                            'startPage' => max($startPage - 1, 1),
                            'count' => $limit))))
            );
            // First URL is the first search URL i.e. with startPage = 1
            $links[] = array(
                'rel' => 'first',
                'type' => RestoUtil::$contentTypes['json'],
                'title' => $this->context->dictionary->translate('_firstCollectionLink'),
                'href' => RestoUtil::updateUrl($url, $this->writeRequestParams(array_merge($this->context->query, array(
                            'startPage' => 1,
                            'count' => $limit))))
            );
        }

        /*
         * Next URL is the next search URL from the self URL
         * startPage cannot be greater than the one from lastURL 
         */
        if ($nextPage < $totalPage) {
            $links[] = array(
                'rel' => 'next',
                'type' => RestoUtil::$contentTypes['json'],
                'title' => $this->context->dictionary->translate('_nextCollectionLink'),
                'href' => RestoUtil::updateUrl($url, $this->writeRequestParams(array_merge($this->context->query, array(
                            'startPage' => min($startPage, $totalPage),
                            'count' => $limit))))
            );
            
            // Last URL has the highest startIndex
            $links[] = array(
                'rel' => 'last',
                'type' => RestoUtil::$contentTypes['json'],
                'title' => $this->context->dictionary->translate('_lastCollectionLink'),
                'href' => RestoUtil::updateUrl($url, $this->writeRequestParams(array_merge($this->context->query, array(
                            'startIndex' => max($totalPage, 1),
                            'count' => $limit))))
            );
        }
        
        /*
         * If total = -1 then it means that total number of resources is unknown
         * 
         * The last index cannot be displayed
         */
        if ($total === -1 && $count >= $limit) {
            $links[] = array(
                'rel' => 'next',
                'type' => RestoUtil::$contentTypes['json'],
                'title' => $this->context->dictionary->translate('_nextCollectionLink'),
                'href' => RestoUtil::updateUrl($url, $this->writeRequestParams(array_merge($this->context->query, array(
                            'startPage' => $startPage + 1,
                            'count' => $limit))))
            );
        }
        
        /*
         * Analyzed query
         */
        $analyzedSearchterms = array();
        $splitted = RestoUtil::splitString($query['searchTerms']);
        for ($i = count($splitted); $i--;) {
            if (empty($splitted[$i])) {
                continue;
            }
            $typeAndId = explode(':', $splitted[$i]);
            $analyzedSearchterms[] = array(
                'id' => $splitted[$i],
                'type' => $typeAndId[0],
                'name' => $this->context->dictionary->getKeywordFromValue($typeAndId[1], $typeAndId[0])
            );
        }    
        
        /*
         * Sort results
         */
        ksort($query);   
        $this->description = array(
            'type' => 'FeatureCollection',
            'properties' => array(
                'title' => isset($query['searchTerms']) ? $query['searchTerms'] : '',
                'id' => RestoUtil::UUIDv5((isset($this->defaultCollection) ?$this->defaultCollection->name : '*') . ':' . json_encode($query)),
                'totalResults' => $total !== -1 ? $total : null,
                'startIndex' => $startIndex,
                'itemsPerPage' => $count,
                'query' => array(
                    'original' => $original,
                    'analyzed' => array_merge($query, array('searchTerms' => $analyzedSearchterms)),
                    'queryAnalyzeProcessingTime' => isset($processingTime) ? $processingTime : null,
                    'searchProcessingTime' => $requestStopTime - $requestStartTime,
                    'hasLocation' => $hasLocation
                ),
                'links' => $links
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
        
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');

        /*
         * feed - Start element
         */
        $xml->startElement('feed');
        $xml->writeAttribute('xml:lang', 'en');
        $xml->writeAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        $xml->writeAttribute('xmlns:time', 'http://a9.com/-/opensearch/extensions/time/1.0/');
        $xml->writeAttribute('xmlns:os', 'http://a9.com/-/spec/opensearch/1.1/');
        $xml->writeAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $xml->writeAttribute('xmlns:georss', 'http://www.georss.org/georss');
        $xml->writeAttribute('xmlns:gml', 'http://www.opengis.net/gml');
        $xml->writeAttribute('xmlns:geo', 'http://a9.com/-/opensearch/extensions/geo/1.0/');
        $xml->writeAttribute('xmlns:eo', 'http://a9.com/-/opensearch/extensions/eo/1.0/');
        $xml->writeAttribute('xmlns:metalink', 'urn:ietf:params:xml:ns:metalink');
        $xml->writeAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $xml->writeAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');

        /*
         * Element 'title' 
         *  read from $this->description['properties']['title']
         */
        $xml->writeElement('title', isset($this->description['properties']['title']) ? $this->description['properties']['title'] : '');

        /*
         * Element 'subtitle' 
         *  constructed from $this->description['properties']['title']
         */
        $subtitle = '';
        if (isset($this->description['properties']['totalResults'])) {
            $subtitle = $this->context->dictionary->translate($this->description['properties']['totalResults'] === 1 ? '_oneResult' : '_multipleResult', $this->description['properties']['totalResults']);
        }
        $previous = isset($this->description['properties']['links']['previous']) ? '<a href="' . RestoUtil::updateURLFormat($this->description['properties']['links']['previous'], 'atom') . '">' . $this->context->dictionary->translate('_previousPage') . '</a>&nbsp;' : '';
        $next = isset($this->description['properties']['links']['next']) ? '&nbsp;<a href="' . RestoUtil::updateURLFormat($this->description['properties']['links']['next'], 'atom') . '">' . $this->context->dictionary->translate('_nextPage') . '</a>' : '';
        $subtitle .= isset($this->description['properties']['startIndex']) ? '&nbsp;|&nbsp;' . $previous . $this->context->dictionary->translate('_pagination', $this->description['properties']['startIndex'], $this->description['properties']['startIndex'] + 1) . $next : '';

        $xml->startElement('subtitle');
        $xml->writeAttribute('type', 'html');
        $xml->text($subtitle);
        $xml->endElement(); // subtitle

        /*
         * Updated time is now
         */
        $xml->startElement('generator');
        $xml->writeAttribute('uri', 'http://mapshup.info');
        $xml->writeAttribute('version', '1.0');
        $xml->text('RESTo');
        $xml->endElement(); // generator
        $xml->writeElement('updated', date('Y-m-d\TH:i:sO'));

        /*
         * Element 'id' - UUID generate from RESTo::UUID and response URL
         */
        $xml->writeElement('id', $this->description['properties']['id']);

        /*
         * Update outputFormat links except for OSDD 'search'
         */
        if (is_array($this->description['properties']['links'])) {
            for ($i = 0, $l = count($this->description['properties']['links']); $i < $l; $i++) {
                $xml->startElement('link');
                $xml->writeAttribute('rel', $this->description['properties']['links'][$i]['rel']);
                $xml->writeAttribute('title', $this->description['properties']['links'][$i]['title']);
                if ($this->description['properties']['links'][$i]['type'] === 'application/opensearchdescription+xml') {
                    $xml->writeAttribute('type', $this->description['properties']['links'][$i]['type']);
                    $xml->writeAttribute('href', $this->description['properties']['links'][$i]['href']);
                }
                else {
                    $xml->writeAttribute('type', RestoUtil::$contentTypes['atom']);
                    $xml->writeAttribute('href', RestoUtil::updateURLFormat($this->description['properties']['links'][$i]['href'], 'atom'));
                }
                $xml->endElement(); // link
            }
        }

        /*
         * Total results, startIndex and itemsPerpage
         */
        if (isset($this->description['properties']['totalResults'])) {
            $xml->writeElement('os:totalResults', $this->description['properties']['totalResults']);
        }
        if (isset($this->description['properties']['startIndex'])) {
            $xml->writeElement('os:startIndex', $this->description['properties']['startIndex']);
        }
        if (isset($this->description['properties']['itemsPerPage'])) {
            $xml->writeElement('os:itemsPerPage', $this->description['properties']['itemsPerPage']);
        }

        /*
         * Query is made from request parameters
         */
        $xml->startElement('os:Query');
        $xml->writeAttribute('role', 'request');
        if (isset($this->description['properties']['query'])) {
            foreach ($this->description['properties']['query']['original'] as $key => $value) {
                $xml->writeAttribute($key, $value);
            }
        }
        $xml->endElement(); // os:Query

        /*
         * Loop over all products
         */
        for ($i = 0, $l = count($this->restoFeatures); $i < $l; $i++) {
            $this->restoFeatures[$i]->addAtomEntry($xml);
        }

        /*
         * feed - End element
         */
        $xml->endElement();

        /*
         * Return ATOM result
         */
        return $xml->outputMemory(true);
    }

}
