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
 *      required={"type", "links", "features", "context"},
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
 *      @OA\Property(
 *           property="links",
 *           type="array",
 *           @OA\Items(ref="#/components/schemas/Links")
 *      ),
 *      @OA\Property(
 *          property="context",
 *          description="Information on search query",
 *          required={"next", "returned"},
 *          @OA\Items(
 *              @OA\Property(
 *                  property="next",
 *                  type="string",
 *                  description="The value to set for the next query parameter in order to get the next page of results"
 *              ),
 *              @OA\Property(
 *                  property="prev",
 *                  type="string",
 *                  description="The value to set for the prev query parameter in order to get the previous page of results"
 *              ),
 *              @OA\Property(
 *                  property="returned",
 *                  type="integer",
 *                  description="The count of results returned by this response. equal to the cardinality of features array"
 *              ),
 *              @OA\Property(
 *                  property="limit",
 *                  type="integer",
 *                  description="The maximum number of results to which the result was limited"
 *              ),
 *              @OA\Property(
 *                  property="matched",
 *                  type="integer",
 *                  description="The count of total number of results that match for this query, possibly estimated"
 *              ),
 *              @OA\Property(
 *                  property="exactCount",
 *                  type="boolean",
 *                  description="True if *matched* is exact - false means that it is estimated"
 *              ),
 *              @OA\Property(
 *                  property="startIndex",
 *                  type="integer",
 *                  description="Start index for the search (cf. pagination)"
 *              ),
 *              @OA\Property(
 *                  property="query",
 *                  type="object",
 *                  description="Query details"
 *              )
 *          )
 *      ),
 *      @OA\Property(
 *          property="id",
 *          type="string",
 *          description="FeatureCollection unique identifier (uuid)"
 *      ),
 *      example={
 *          "type": "FeatureCollection",
 *          "features":{
 *              {
 *                  "stac_version": "0.8.0",
 *                  "stac_extensions": {
 *                      "eo"
 *                  },
 *                  "type": "Feature",
 *                  "id": "8030a391-4002-556f-929b-d7ff9dad6705",
 *                  "bbox": {
 *                      -48.6198530870596,
 *                      74.6749788966259,
 *                      -44.6464244356188,
 *                      75.6843970710939
 *                  },
 *                  "geometry": {
 *                      "type": "Polygon",
 *                      "coordinates": {
 *                          {
 *                              {
 *                                  -48.619853,
 *                                  75.657209
 *                              },
 *                              {
 *                                  -44.646424,
 *                                  75.684397
 *                              },
 *                              {
 *                                  -44.660672,
 *                                  75.069386
 *                              },
 *                              {
 *                                  -44.698432,
 *                                  75.060518
 *                              },
 *                              {
 *                                  -45.489771,
 *                                  74.830977
 *                              },
 *                              {
 *                                  -45.857954,
 *                                  74.720238
 *                              },
 *                              {
 *                                  -45.921685,
 *                                  74.698702
 *                              },
 *                              {
 *                                  -48.392706,
 *                                  74.674979
 *                              },
 *                              {
 *                                  -48.619853,
 *                                  75.657209
 *                              }
 *                          }
 *                      }
 *                  },
 *                  "properties": {
 *                      "datetime":"2019-06-11T16:11:41Z",
 *                      "productIdentifier": "S2A_MSIL1C_20190611T160901_N0207_R140_T23XMD_20190611T193040",
 *                      "startDate": "2019-06-11T16:11:41.808000Z"
 *                  },
 *                  "collection": "S2",
 *                  "links": {
 *                      {
 *                          "rel": "self",
 *                          "type": "application/json",
 *                          "href": "http://127.0.0.1:5252/collections/S2/items/8030a391-4002-556f-929b-d7ff9dad6705?&lang=en"
 *                      },
 *                      {
 *                          "rel": "collection",
 *                          "type": "application/json",
 *                          "title": "S2",
 *                          "href": "http://127.0.0.1:5252/collections/S2?&lang=en"
 *                      }
 *                  },
 *                  "assets": {
 *                      "thumbnail": {
 *                          "href": "https://roda.sentinel-hub.com/sentinel-s2-l1c/tiles/23/X/MD/2019/6/11/0/preview.jpg",
 *                          "type": "image/jpeg"
 *                      },
 *                      "metadata": {
 *                          "href": "https://roda.sentinel-hub.com/sentinel-s2-l1c/tiles/23/X/MD/2019/6/11/0/metadata.xml",
 *                          "type": "text/xml"
 *                      },
 *                      "tileInfo": {
 *                          "href": "https://roda.sentinel-hub.com/sentinel-s2-l1c/tiles/23/X/MD/2019/6/11/0/tileInfo.json",
 *                          "type": "application/json"
 *                      },
 *                      "productInfo": {
 *                          "href": "https://roda.sentinel-hub.com/sentinel-s2-l1c/tiles/23/X/MD/2019/6/11/0/productInfo.json",
 *                          "type": "application/json"
 *                      }
 *                  }
 *              }
 *          },
 *          "links":{
 *              {
 *                  "rel": "self",
 *                  "type": "application/json",
 *                  "href": "http://127.0.0.1:5252/stac/search.json?"
 *              },
 *              {
 *                  "rel": "search",
 *                  "type": "application/opensearchdescription+xml",
 *                  "href": "http://127.0.0.1:5252/services/osdd"
 *              },
 *              {
 *                  "rel": "next",
 *                  "type": "application/json",
 *                  "href": "http://127.0.0.1:5252/stac/search.json?next=204449069316703379"
 *              }
 *          },
 *          "context": {
 *              "next": "204449069316703379",
 *              "returned": 20,
 *              "limit": 20,
 *              "matched": 11345,
 *              "exactCount": false,
 *              "startIndex": 1,
 *              "query": {
 *                  "inputFilters": {}
 *              }
 *          },
 *          "id": "20ac2fc6-daee-5621-bca4-d88c0bb19da1"
 *      }
 *  )
 * 
 *  @OA\Schema(
 *      schema="InputFeatureCollection",
 *      description="Feature collection",
 *      required={"type", "features"},
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
 *          @OA\Items(ref="#/components/schemas/InputFeature")
 *      ),
 *      example={
 *          "type": "FeatureCollection",
 *          "features":{
 *              {
 *                  "stac_version": "0.8.0",
 *                  "stac_extensions": {
 *                      "eo"
 *                  },
 *                  "type": "Feature",
 *                  "id": "8030a391-4002-556f-929b-d7ff9dad6705",
 *                  "bbox": {
 *                      -48.6198530870596,
 *                      74.6749788966259,
 *                      -44.6464244356188,
 *                      75.6843970710939
 *                  },
 *                  "geometry": {
 *                      "type": "Polygon",
 *                      "coordinates": {
 *                          {
 *                              {
 *                                  -48.619853,
 *                                  75.657209
 *                              },
 *                              {
 *                                  -44.646424,
 *                                  75.684397
 *                              },
 *                              {
 *                                  -44.660672,
 *                                  75.069386
 *                              },
 *                              {
 *                                  -44.698432,
 *                                  75.060518
 *                              },
 *                              {
 *                                  -45.489771,
 *                                  74.830977
 *                              },
 *                              {
 *                                  -45.857954,
 *                                  74.720238
 *                              },
 *                              {
 *                                  -45.921685,
 *                                  74.698702
 *                              },
 *                              {
 *                                  -48.392706,
 *                                  74.674979
 *                              },
 *                              {
 *                                  -48.619853,
 *                                  75.657209
 *                              }
 *                          }
 *                      }
 *                  },
 *                  "properties": {
 *                      "datetime":"2019-06-11T16:11:41Z",
 *                      "productIdentifier": "S2A_MSIL1C_20190611T160901_N0207_R140_T23XMD_20190611T193040",
 *                      "startDate": "2019-06-11T16:11:41.808000Z"
 *                  },
 *                  "collection": "S2",
 *                  "links": {
 *                      {
 *                          "rel": "self",
 *                          "type": "application/json",
 *                          "href": "http://127.0.0.1:5252/collections/S2/items/8030a391-4002-556f-929b-d7ff9dad6705?&lang=en"
 *                      },
 *                      {
 *                          "rel": "collection",
 *                          "type": "application/json",
 *                          "title": "S2",
 *                          "href": "http://127.0.0.1:5252/collections/S2?&lang=en"
 *                      }
 *                  },
 *                  "assets": {
 *                      "thumbnail": {
 *                          "href": "https://roda.sentinel-hub.com/sentinel-s2-l1c/tiles/23/X/MD/2019/6/11/0/preview.jpg",
 *                          "type": "image/jpeg"
 *                      },
 *                      "metadata": {
 *                          "href": "https://roda.sentinel-hub.com/sentinel-s2-l1c/tiles/23/X/MD/2019/6/11/0/metadata.xml",
 *                          "type": "text/xml"
 *                      },
 *                      "tileInfo": {
 *                          "href": "https://roda.sentinel-hub.com/sentinel-s2-l1c/tiles/23/X/MD/2019/6/11/0/tileInfo.json",
 *                          "type": "application/json"
 *                      },
 *                      "productInfo": {
 *                          "href": "https://roda.sentinel-hub.com/sentinel-s2-l1c/tiles/23/X/MD/2019/6/11/0/productInfo.json",
 *                          "type": "application/json"
 *                      }
 *                  }
 *              }
 *          }
 *      }
 *  )
 * 
 * 
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
     * Unique identifier
     */
    private $id;

    /*
     * Next iterator
     */
    private $next = null;

    /*
     * Previous iterator
     */
    private $prev = null;

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
     * Query for search
     */
    private $query = array();

    /*
     * Total number of resources relative to the query
     */
    private $paging = array();

    /**
     * Links
     *
     * @OA\Schema(
     *      schema="Links",
     *      description="Collection facets statistics",
     *      required={"rel", "href"},
     *      @OA\Property(
     *          property="rel",
     *          type="string",
     *          description="Relationship between the feature and the linked document/resource"
     *      ),
     *      @OA\Property(
     *          property="type",
     *          type="string",
     *          description="Mimetype of the resource"
     *      ),
     *      @OA\Property(
     *          property="title",
     *          type="string",
     *          description="Title of the resource"
     *      ),
     *      @OA\Property(
     *          property="href",
     *          type="string",
     *          description="Url to the resource"
     *      ),
     *      example={
     *          "rel": "self",
     *          "type": "application/json",
     *          "href": "http://127.0.0.1:5252/collections/S2.json?&_pretty=1"
     *      }
     * )
     */
    private $links = array();

    /**
     * Constructor
     *
     * @param RestoResto $context : Resto Context
     * @param RestoUser $user : Resto user
     * @param array $collections
     */
    public function __construct($context, $user, $collections)
    {
        $this->context = $context;
        $this->user = $user;
        $this->collections = $collections;
    }

    /**
     * Load featureCollection from database
     *
     * @param RestoModel $model
     * @param RestoCollection $collection
     * @param Array $query
     */
    public function load($model, $collection, $query)
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
         * Query parameters to perform the search
         */
        $this->query = $query;

        /*
         * Clean search filters
         */
        $inputFilters = $this->model->getFiltersFromQuery($query);
        
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
            
            /*
             * [IMPORTANT] Add explicit 'resto:collection' filter if $collection is set
             */
            $this->loadFeatures(isset($collection) ? array_merge($analysis['details']['appliedFilters'], array('resto:collection' => $collection->id)) : $analysis['details']['appliedFilters'], $sorting);
        }
        
        /*
         * Initial values
         */
        $this->init($analysis, $sorting, isset($collection) ? $collection->id : null);
    
        /*
         * Return object
         */
        return $this;
    }

    /**
     * Output product description as a GeoJSON FeatureCollection
     *
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false)
    {
        $features = array();
        for ($i = 0, $l = count($this->restoFeatures); $i < $l; $i++) {
            $features[] = $this->restoFeatures[$i]->toPublicArray();
        }

        return json_encode(array(
            'type' => 'FeatureCollection',
            'id' => $this->id,
            'context' => $this->searchContext,
            'links' => $this->links,
            'features' => $features
        ), $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES);
        
    }

    /**
     * Output product description as an ATOM feed
     */
    public function toATOM()
    {

        /*
         * Initialize ATOM feed
         */
        $atomFeed = new ATOMFeed($this->id, $this->context->core['title'] ?? null, $this->getATOMSubtitle());

        /*
         * Set collection elements
         */
        $atomFeed->setCollectionElements($this->links, $this->searchContext, $this->model);

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
     * Initialize properties based on search query
     *
     * @param array $analysis
     * @param array $sorting
     * @param string $collectionId
     */
    private function init($analysis, $sorting, $collectionId)
    {

        // Default name for all collection
        $defaultName = $collectionId ?? '*';

        /*
         * Set id
         */
        $this->id = RestoUtil::toUUID($defaultName . ':' . json_encode($this->cleanFilters($analysis['details']['appliedFilters']), JSON_UNESCAPED_SLASHES));

        /*
         * Set links
         */
        $this->links = $this->getLinks($sorting, $collectionId);

        /*
         * Set context
         */
        $this->searchContext = array(
            /*'next' => $this->next ?? null,
            'prev' => $this->prev ?? null,*/
            'returned' => $this->paging['count']['returned'],
            'limit' => $sorting['limit'],
            'matched' => $this->paging['count']['total'],
            'exactCount' => $this->paging['count']['isExact'],
            'startIndex' => $sorting['offset'] + 1,
            'query' => $this->getSearchQuery($analysis)
        );

    }

    /**
     * Return detailed query block from query analysis
     *  
     * @param array $analysis
     * @return array
     */
    private function getSearchQuery($analysis)
    {
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
        if (isset($this->query['_analysis']) ? filter_var($this->query['_analysis'], FILTER_VALIDATE_BOOLEAN) : false) {
            $query['details'] = $analysis['details'];
            $query['details']['appliedFilters'] = $this->toOSKeys($analysis['details']['appliedFilters']);
            $query['details']['processingTime'] = microtime(true) - $this->requestStartTime;
        }

        return $query;

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
                    } /*else {
                        $arr[$key . '[]'] = $value;
                    } */
                }
            } else {

                if (isset($this->model->searchFilters[$key]['osKey'])) {
                    $arr[$this->model->searchFilters[$key]['osKey']] = $value;
                } /* else {
                    $arr[$key] = $value;
                } */
            }
        }

        return $arr;
    }

    /**
     * Set restoFeatures and collections array
     *
     * @param array $params
     * @param array $sorting
     */
    private function loadFeatures($params, $sorting)
    {

        /*
         * Get features array from database
         */
        $featuresArray = (new FeaturesFunctions($this->context->dbDriver))->search(
            $this->context,
            $this->user,
            $this->model,
            $this->collections,
            $params,
            $sorting
        );
        
        /*
         * Load collections array
         */
        for ($i = 0, $l = count($featuresArray['features']); $i < $l; $i++) {
            $feature = new RestoFeature($this->context, $this->user, array(
                'featureArray' => $featuresArray['features'][$i],
                'collection' => $this->collections[$featuresArray['features'][$i]['collection']] ?? null,
                'fields' => $this->query['fields'] ?? "_default"
            ));
            if ( $feature->isValid() ) {
                $this->restoFeatures[] = $feature;
            }
        }

        /*
         * Compute paging
         */
        $this->paging = $this->getPaging($featuresArray['count'], $sorting['limit'], $sorting['offset']);

        /*
         * Additional links computed during search (e.g. heatmap - see resto-addon-heatmap)
         */
        if ( !empty($featuresArray['links']) ) {
            $this->links = array_merge($this->links, $featuresArray['links']);
        }
        
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
     * @param string $collectionId
     *
     * @return array
     */
    private function getLinks($sorting, $collectionId)
    {
        
        /*
         * Base links are always returned
         */
        $this->getBaseLinks($collectionId);
        
        /*
         * resto:lt has preseance over startPage
         */
        if ($sorting['resto:lt'] || $sorting['resto:gt']) {

            if (isset($this->restoFeatures[0]))
            {
                $featureArray = $this->restoFeatures[0]->toArray();

                $this->prev = $featureArray['properties']['sort_idx'];

                /* 
                 * Previous
                 */
                $this->links[] = $this->getLink('previous', array(
                    'resto:lt' => null,
                    'resto:gt' => $this->prev,
                    'count' => $sorting['limit'])
                );

            }

            /*
             * First URL is the first search URL i.e. without any lt/gt
             */
            $this->links[] = $this->getLink('first', array(
                'resto:lt' => null,
                'resto:gt' => null,
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
            $this->links[] = $this->getLink('previous', array(
                'startPage' => max($this->paging['startPage'] - 1, 1),
                'count' => $sorting['limit']));

            /*
             * First URL is the first search URL i.e. with startPage = 1
             */
            $this->links[] = $this->getLink('first', array(
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

            $featureArray = $this->restoFeatures[$count - 1]->toArray();
            
            $this->next = $featureArray['properties']['sort_idx'];

            /*
             * Next URL is the next search URL from the self URL
             */
            $this->links[] = $this->getLink('next', array(
                'resto:gt' => null,
                'resto:lt' => $this->next,
                'count' => $sorting['limit'])
            );

        }

        return $this->links;
    }

    /**
     * Return base links (i.e. links always present in response)
     * 
     * @param string $collectionId
     */
    private function getBaseLinks($collectionId)
    {
        $this->links[] = array(
            'rel' => 'self',
            'type' => RestoUtil::$contentTypes['geojson'],
            'href' => RestoUtil::updateUrl($this->context->getUrl(false), $this->writeRequestParams($this->query))
        );

        $this->links[] = array(
            'rel' => 'search',
            'type' => 'application/opensearchdescription+xml',
            'href' => $this->context->core['baseUrl'] . '/services/osdd' . (isset($collectionId) ? '/' . $collectionId : '')
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
            'type' => RestoUtil::$contentTypes['geojson'],
            'href' => RestoUtil::updateUrl($this->context->getUrl(false), $this->writeRequestParams(array_merge($this->query, $params)))
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

        $count['returned'] = count($this->restoFeatures);

        /*
         * If first page contains no features count must be 0 not estimated value
         */
        if ($offset == 0 && $count['returned'] == 0) {
            $count = array(
                'returned' => 0,
                'total' => $count['total'] ?? 0,
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
        if ($count['returned'] > 0) {
            $startPage = ceil(($offset + 1) / $limit);

            /*
             * Tricky part if count is estimate, then
             * the total count is the maximum between the database estimate
             * and the pseudo real count based on the retrieved features count
             */
            if (!$count['isExact']) {
                $count['total'] = max($count['returned'] + (($startPage - 1) * $limit), $count['total']);
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
     * Get ATOM subtitle
     *
     * @return string
     */
    private function getATOMSubtitle()
    {
        $subtitle = '';
        if (isset($this->searchContext['totalResults']) && $this->searchContext['totalResults'] !== -1) {
            $subtitle = $this->searchContext['totalResults'] . ($this->searchContext['totalResults'] > 1 ? 'results' : 'result');
        }
        if (isset($this->searchContext['startIndex'])) {
            $previous = isset($this->links['previous']) ? '<a href="' . RestoUtil::updateUrlFormat($this->links['previous'], 'atom') . '">Previous</a>&nbsp;' : '';
            $next = isset($this->links['next']) ? '&nbsp;<a href="' . RestoUtil::updateUrlFormat($this->links['next'], 'atom') . '">Next</a>' : '';
            return $subtitle . '&nbsp;|&nbsp;' . $previous . $this->searchContext['startIndex'] . ' - ' .  ($this->searchContext['startIndex'] + 1) . $next;
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
         * Default order is DESCENDING
         */
        $sortOrder = 'DESC';
        
        /*
         * Default sort key is the first element in "sortKeys" array (cf. config.php)
         */
        $sortKey = $filters['resto:sort'] ?? $this->context->dbDriver->sortKeys[0];

        /*
         * Check sortKey order (i.e. minus sign prefix)
         */
        if (substr($sortKey, 0, 1) === '-') {
            $sortOrder = 'ASC';
            $sortKey = substr($sortKey, 1);
        }

        /*
         * Finally check validity
         */
        if (! in_array($sortKey, $this->context->dbDriver->sortKeys) ) {
            return RestoLogUtil::httpError(400, "Invalid sorting key");
        }

        /*
         * Result options
         */
        return array(
            'offset' => $offset,
            'limit' => $limit,
            'sortKey' => $sortKey === 'likes' ? $sortKey : $sortKey . '_idx',
            'order' => $sortOrder,
            // [IMPORTANT] We need to force ASC order to make "previous" link working
            'realOrder' => isset($filters['resto:gt']) ? 'ASC' : $sortOrder,
            'resto:lt' => isset($filters['resto:lt']) ? $filters['resto:lt'] : null,
            'resto:gt' => isset($filters['resto:gt']) ? $filters['resto:gt'] : null
        );
    }
}
