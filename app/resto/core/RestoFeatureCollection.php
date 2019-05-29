<?php
/*
 * Copyright 2018 Jérôme Gasperi
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
 *
 * @OA\Schema(
 *      schema="RestoFeatureCollection",
 *      description="Feature collection",
 *      required={"type", "properties", "features"},
 *      @OA\Property(
 *          property="properties",
 *          description="Information on query",
 *          @OA\Items(
 *              @OA\Property(
 *                  property="id",
 *                  type="string",
 *                  description="FeatureCollection unique identifier (uuid)"
 *              ),
 *              @OA\Property(
 *                  property="totalResults",
 *                  type="integer",
 *                  description="Number of total results for this query"
 *              ),
 *              @OA\Property(
 *                  property="exactCount",
 *                  type="boolean",
 *                  description="True if totalResults is exact - false means that is is approximative"
 *              ),
 *              @OA\Property(
 *                  property="startIndex",
 *                  type="integer",
 *                  description="Start index for the search (cf. pagination)"
 *              ),
 *              @OA\Property(
 *                  property="query",
 *                  type="object",
 *                  description="Feature collection unique identifier (uuid)"
 *              ),
 *              @OA\Property(
 *                  property="links",
 *                  type="object",
 *                  description="Links to self/search urls"
 *              )
 *          )
 *      ),
 *      @OA\Property(
 *          property="type",
 *          type="enum",
 *          enum={"FeatureCollection"},
 *          description="Always set to *FeatureCollection*"
 *      ),
 *      @OA\Property(
 *          property="features",
 *          type="array",
 *          description="Array of features",
 *          @OA\Items(ref="#/components/schemas/OutputFeature")
 *      ),
 *      example={
 *          "type": "FeatureCollection",
 *          "properties": {
 *              "id": "20ac2fc6-daee-5621-bca4-d88c0bb19da1",
 *              "totalResults": 1,
 *              "exactCount": true,
 *              "startIndex": 1,
 *              "query": {
 *                  "inputFilters": {}
 *              },
 *              "links": {
 *                  {
 *                      "rel": "self",
 *                      "type": "application/json",
 *                      "title": "self",
 *                      "href": "http://localhost:5252/features.json?"
 *                  },
 *                  {
 *                      "rel": "search",
 *                      "type": "application/opensearchdescription+xml",
 *                      "title": "OpenSearch Description Document",
 *                      "href": "http://localhost:5252/services/osdd"
 *                  }
 *              }
 *          },
 *          "features":{
 *              {
 *                  "type": "Feature",
 *                  "geometry": {
 *                      "type": "Polygon",
 *                      "coordinates": {
 *                          {
 *                              {
 *                                  69.979462,
 *                                  23.507467
 *                              },
 *                              {
 *                                  71.054486,
 *                                  23.496997
 *                              },
 *                              {
 *                                  71.039531,
 *                                  22.505778
 *                              },
 *                              {
 *                                  69.972328,
 *                                  22.515759
 *                              },
 *                              {
 *                                  69.979462,
 *                                  23.507467
 *                              }
 *                          }
 *                      }
 *                  },
 *                  "properties": {
 *                      "productIdentifier": "S2:tiles/42/Q/XL/2018/9/13/0",
 *                      "startDate": "2018-09-13T05:58:08.367Z"
 *                  }
 *              }
 *          }
 *      }
 *  )
 */
class RestoFeatureCollection
{

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
    private $restoFeatures = array();

    /*
     * All collections
     */
    private $collections = array();

    /*
     * Model of the main collection
     */
    private $model;

    /*
     * Total number of resources relative to the query
     */
    private $paging = array();

    /**
     * Constructor
     *
     * @param RestoResto $context : Resto Context
     * @param RestoUser $user : Resto user
     */
    public function __construct($context, $user)
    {
        $this->context = $context;
        $this->user = $user;
    }

    /**
     * Set collections
     *
     * @param array $collections
     */
    public function setCollections($collections)
    {
        $this->collections = $collections;
        return $this;
    }

    /**
     * Load featureCollection from database
     *
     * @param RestoModel $model
     * @param RestoCollection $collection
     */
    public function load($model, $collection = null)
    {

        /*
         * Request start time
         */
        $this->requestStartTime = microtime(true);
        
        /*
         * Model is mandatory - use default if not set
         */
        $this->model = $model;

        /*
         * Clean search filters
         */
        $inputFilters = $this->model->getFiltersFromQuery($this->context->query);
        
        /*
         * Force collection
         */
        if (isset($collection)) {
            $this->setCollections(array($collection));
            $inputFilters['resto:collection'] = $collection->name;
        }

        /*
         * result options
         */
        $sorting = $this->getSorting($inputFilters);

        /*
         * Query Analyzer
         */
        $analysis = (new RestoQueryAnalyzer($this->context, $this->user))->analyze($inputFilters);
        
        /*
         * Completely not understood query - return an empty result without
         * launching a search on the database
         */
        if (isset($analysis['notUnderstood'])) {
            $this->restoFeatures = array();
            $this->paging = $this->getPaging(array(
                 'total' => 0,
                 'isExact' => true
             ), $sorting['limit'], $sorting['offset']);
        }
        /*
         * Read features from database
         */
        else {
            $this->loadFeatures($analysis['details']['appliedFilters'], $sorting, $collection);
        }

        /*
         * Set description
         */
        $this->setDescription($analysis, $sorting, isset($collection) ? $collection->name : null);

        /*
         * Return object
         */
        return $this;
    }

    /**
     * Output product description as a PHP array
     *
     * @param boolean publicOutput
     */
    public function toArray($publicOutput = false)
    {
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
    public function toJSON($pretty = false)
    {
        return json_encode($this->toArray(true), $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES);
    }

    /**
     * Output product description as an ATOM feed
     */
    public function toATOM()
    {

        /*
         * Initialize ATOM feed
         */
        $atomFeed = new ATOMFeed($this->description['properties']['id'], $this->context->core['title'] ?? null, $this->getATOMSubtitle());

        /*
         * Set collection elements
         */
        $atomFeed->setCollectionElements($this->description['properties'], $this->model);

        /*
         * Add one entry per product
         */
        $atomFeed->addEntries($this->restoFeatures);

        /*
         * Return ATOM result
         */
        return $atomFeed->toString();
    }

    /**
     * Set description
     *
     * @param array $analysis
     * @param array $sorting
     * @param string $name
     */
    private function setDescription(&$analysis, $sorting, $name)
    {

        // Default name for all collection
        $defaultName = $name ?? '*';

        /*
         * Convert resto model to search service "osKey"
         */
        $query = array(
            'inputFilters' => $this->toOSKeys($analysis['inputFilters'])
        );
        
        /*
         * Location found
         */
        if (count($analysis['details']['Where']) > 0) {
            $coordinates = explode(',', $analysis['details']['Where'][0]['coordinates']);
            $query['locationFound'] = array(
                'name' => $analysis['details']['Where'][0]['name'],
                'geonameid' => $analysis['details']['Where'][0]['geonameid'],
                'geo:lon' => floatval(trim($coordinates[1])),
                'geo:lat' => floatval(trim($coordinates[0])),
                'country_code2' => $analysis['details']['Where'][0]['country_code2'],
                'feature_code' => $analysis['details']['Where'][0]['feature_code'],
                'feature_class' => $analysis['details']['Where'][0]['feature_class'],
                'population' => $analysis['details']['Where'][0]['name']
            );
        }

        /*
         * Display detailed analysis ?
         */
        if (isset($this->context->query['_analysis']) ? filter_var($this->context->query['_analysis'], FILTER_VALIDATE_BOOLEAN) : false) {
            $query['details'] = $analysis['details'];
            $query['details']['appliedFilters'] = $this->toOSKeys($analysis['details']['appliedFilters']);
            $query['details']['processingTime'] = microtime(true) - $this->requestStartTime;
        }

        /*
         * Sort results
         */
        $this->description = array(
            'type' => 'FeatureCollection',
            'properties' => array(
                'id' => RestoUtil::toUUID($defaultName . ':' . json_encode($this->cleanFilters($analysis['details']['appliedFilters']))),
                'totalResults' => $this->paging['count']['total'],
                'exactCount' => $this->paging['count']['isExact'],
                'startIndex' => $sorting['offset'] + 1,
                'query' => $query,
                'links' => $this->getLinks($sorting, $name)
            )
        );
    }

    /**
     * Return an array of request parameters formated for output url
     *
     * @param {array} $params - input params
     *
     */
    private function writeRequestParams($params)
    {
        $arr = array();

        foreach ($params as $key => $value) {

            /*
             * Support key tuples
             */
            if (is_array($value)) {
                for ($i = 0, $l = count($value); $i < $l; $i++) {
                    if (isset($this->model->searchFilters[$key]['osKey'])) {
                        $arr[$this->model->searchFilters[$key]['osKey'] . '[]'] = $value[$i];
                    } else {
                        $arr[$key . '[]'] = $value;
                    }
                }
            } else {
                if (isset($this->model->searchFilters[$key]['osKey'])) {
                    $arr[$this->model->searchFilters[$key]['osKey']] = $value;
                } else {
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
     * @param array $sorting
     * @param RestoCollection $collection
     */
    private function loadFeatures(&$params, &$sorting, $collection)
    {
        
        /*
         * Get features array from database
         */
        $featuresArray = (new FeaturesFunctions($this->context->dbDriver))->search(
            $this->context,
            $this->user,
            $collection,
            $params,
            $sorting
        );

        /*
         * Load collections array
         */
        for ($i = 0, $l = count($featuresArray['features']); $i < $l; $i++) {
            $feature = new RestoFeature($this->context, $this->user, array(
                'featureArray' => $featuresArray['features'][$i],
                'collection' => $this->collections[$featuresArray['features'][$i]['properties']['collection']] ?? null,
                'fields' => $this->context->query['fields'] ?? "_default"
            ));
            if (isset($feature)) {
                $this->restoFeatures[] = $feature;
            }
        }

        /*
         * Compute paging
         */
        $this->paging = $this->getPaging($featuresArray['count'], $sorting['limit'], $sorting['offset']);
    }

    /**
     * Search offset - first element starts at offset 0
     * Note: startPage has preseance over startIndex if both are specified in request
     * (see CEOS-BP-006 requirement of CEOS OpenSearch Best Practice document)
     *
     * @param array $params
     * @param integer $limit
     */
    public function getOffset($params, $limit)
    {
        $offset = 0;
        if (isset($params['startPage']) && is_numeric($params['startPage']) && $params['startPage'] > 0) {
            $offset = (($params['startPage'] - 1) * $limit);
        } elseif (isset($params['startIndex']) && is_numeric($params['startIndex']) && $params['startIndex'] > 0) {
            $offset = ($params['startIndex']) - 1;
        }

        /*
         * Limit offset to avoid very bad performance - Even Google does the same !
         */
        if ($offset > 999) {
            RestoLogUtil::httpError(400, 'Offset pagination is limited to 1000 elements');
        }

        return $offset;
    }

    /**
     * Get navigation links (i.e. next, previous, first, last)
     *
     * @param array $limit
     * @param string $name
     *
     * @return array
     */
    private function getLinks($sorting, $name)
    {
        
        /*
         * Base links are always returned
         */
        $links = $this->getBaseLinks($name);

        /*
         * resto:gt has preseance over startPage
         */
        if ($sorting['hasBefore']) {
            if (count($this->restoFeatures) > 0) {
                $featureArray = $this->restoFeatures[0]->toArray();
                $links[] = $this->getLink('previous', array(
                    'resto:lt' => null,
                    'resto:gt' => $featureArray['properties']['sort_idx'],
                    'count' => $sorting['limit']));
            }

            /*
             * First URL is the first search URL i.e. without any lt/gt
             */
            $links[] = $this->getLink('first', array(
                'resto:gt' => null,
                'resto:lt' => null,
                'count' => $sorting['limit'])
            );
        }
        /*
         * Start page cannot be lower than 1
         */
        elseif ($this->paging['startPage'] > 1) {

            /*
             * Previous URL is the previous URL from the self URL
             *
             */
            $links[] = $this->getLink('previous', array(
                'startPage' => max($this->paging['startPage'] - 1, 1),
                'count' => $sorting['limit']));

            /*
             * First URL is the first search URL i.e. with startPage = 1
             */
            $links[] = $this->getLink('first', array(
                'startPage' => 1,
                'count' => $sorting['limit'])
            );
        }

        /*
         * Theorically, startPage cannot be greater than the one from lastURL
         * ...but since we use a count estimate it is not possible to know the
         * real last page. So always set a nextPage !
         */
        $count = count($this->restoFeatures);
        if ($count >= $sorting['limit']) {

            /*
             * Next URL is the next search URL from the self URL
             */
            $featureArray = $this->restoFeatures[$count - 1]->toArray();
            $links[] = $this->getLink('next', array(
                //'startPage' => $this->paging['nextPage'],
                'resto:gt' => null,
                'resto:lt' => $featureArray['properties']['sort_idx'],
                'count' => $sorting['limit'])
            );

            /*
             * Last URL has the highest startIndex
             */
            $links[] = $this->getLink('last', array(
                'startPage' => max($this->paging['totalPage'], 1),
                'count' => $sorting['limit'])
            );
        }

        return $links;
    }

    /**
     * Return base links (i.e. links always present in response)
     * 
     * @param string $name
     */
    private function getBaseLinks($name)
    {
        return array(
            array(
                'rel' => 'self',
                'type' => RestoUtil::$contentTypes['json'],
                'href' => RestoUtil::updateUrl($this->context->getUrl(false), $this->writeRequestParams($this->context->query))
            ),
            array(
                'rel' => 'search',
                'type' => 'application/opensearchdescription+xml',
                'href' => $this->context->core['baseUrl'] . '/services/osdd' . (isset($name) ? '/' . $name : '')
            )
        );
    }

    /**
     * Return Link
     *
     * @param string $rel
     * @param array $params
     * @return array
     */
    private function getLink($rel, $params)
    {

        /*
         * Do not set count if equal to default limit
         */
        if (isset($params['count']) && $params['count'] === $this->context->dbDriver->resultsPerPage) {
            unset($params['count']);
        }

        return array(
            'rel' => $rel,
            'type' => RestoUtil::$contentTypes['json'],
            'href' => RestoUtil::updateUrl($this->context->getUrl(false), $this->writeRequestParams(array_merge($this->context->query, $params)))
        );
    }

    /**
     * Get start, next and last page from limit and offset
     *
     * @param array $count
     * @param integer $limit
     * @param integer $offset
     */
    private function getPaging($count, $limit, $offset)
    {

        /*
         * If first page contains no features count must be 0 not estimated value
         */
        if ($offset == 0 && count($this->restoFeatures) == 0) {
            $count = array(
                'total' => 0,
                'isExact' => true
            );
        }

        // Avoid Math problem
        if ($limit < 0) {
            $limit = 1;
        }

        /*
         * Default paging
         */
        $paging = array(
            'count' => $count,
            'startPage' => 1,
            'nextPage' => 1,
            'totalPage' => 0,
            'itemsPerPage' => $limit
        );
        if (count($this->restoFeatures) > 0) {
            $startPage = ceil(($offset + 1) / $limit);

            /*
             * Tricky part if count is estimate, then
             * the total count is the maximum between the database estimate
             * and the pseudo real count based on the retrieved features count
             */
            if (!$count['isExact']) {
                $count['total'] = max(count($this->restoFeatures) + (($startPage - 1) * $limit), $count['total']);
            }
            $totalPage = ceil($count['total'] / $limit);
            $paging = array(
                'count' => $count,
                'startPage' => $startPage,
                'nextPage' => $startPage + 1,
                'totalPage' => $totalPage,
                'itemsPerPage' => $limit
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
    private function cleanFilters($searchFilters)
    {
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
    private function getATOMSubtitle()
    {
        $subtitle = '';
        if (isset($this->description['properties']['totalResults']) && $this->description['properties']['totalResults'] !== -1) {
            $subtitle = $this->description['properties']['totalResults'] . ($this->description['properties']['totalResults'] > 1 ? 'results' : 'result');
        }
        if (isset($this->description['properties']['startIndex'])) {
            $previous = isset($this->description['properties']['links']['previous']) ? '<a href="' . RestoUtil::updateUrlFormat($this->description['properties']['links']['previous'], 'atom') . '">Previous</a>&nbsp;' : '';
            $next = isset($this->description['properties']['links']['next']) ? '&nbsp;<a href="' . RestoUtil::updateUrlFormat($this->description['properties']['links']['next'], 'atom') . '">Next</a>' : '';
            return $subtitle . '&nbsp;|&nbsp;' . $previous . $this->description['properties']['startIndex'] . ' - ' .  ($this->description['properties']['startIndex'] + 1) . $next;
        }
        return $subtitle;
    }

    /**
     * Convert array of filter names to array of OpenSearch keys
     *
     * @param array $filterNames
     * @return array
     */
    private function toOSKeys($filterNames)
    {
        $arr = array();
        foreach ($filterNames as $key => $value) {
            if (isset($this->model->searchFilters[$key])) {
                $arr[$this->model->searchFilters[$key]['osKey']] = $value;
            }
        }
        return $arr;
    }

    /**
     * Returns sorting paramers (offset,limit & sorting)
     *
     * @param array $filters
     */
    private function getSorting($filters)
    {

        /*
         * Number of returned results is never greater than MAXIMUM_LIMIT
         */
        $limit = isset($filters['count']) && is_numeric($filters['count']) ? min($filters['count'], $this->model->searchFilters['count']->maximumInclusive ?? 500) : $this->context->dbDriver->resultsPerPage;

        /*
         * Compute offset based on startPage or startIndex
         */
        $offset = $this->getOffset($filters, $limit);

        /*
         * Default sort key is the first element in "sortKeys" array (cf. config.php)
         */
        $sortKey = $this->context->dbDriver->sortKeys[0];

        /*
         * Default order is DESCENDING
         */
        $sortOrder = 'DESC';

        /*
         * Input sorting key
         */
        if (isset($filters['resto:sort'])) {

            // Check sort order with minus sign prefix
            if (substr($filters['resto:sort'], 0, 1) === '-') {
                $sortOrder = 'ASC';
                $sortKey = substr($filters['resto:sort'], 1);
            }
            if (! in_array($sortKey, $this->context->dbDriver->sortKeys) ) {
                return RestoLogUtil::httpError(400, "Invalid sorting key");
            }
        }

        /*
         * Result options
         */
        return array(
            'offset' => $offset,
            'limit' => $limit,
            'sortKey' => $sortKey === 'likes' ? $sortKey : $sortKey . '_idx',
            'order' => $sortOrder,
            'hasBefore' => isset($filters['resto:lt']) ? true : false,
            'hasAfter' => isset($filters['resto:gt']) ? true : false
        );
    }
}
