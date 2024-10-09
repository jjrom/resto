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
 * RESToCollections is a list of RestoCollection objects
 */
class RestoCollections
{
    /**
     * RestoContext
     */
    public $context;

    /**
     * RestoUser
     */
    public $user;

    /*
     * Array of RestoCollection (key = collection id)
     */
    public $collections = array();

    /**
     * * @OA\Schema(
     *      schema="Extent",
     *      description="Spatio-temporal extents of the Collection",
     *      required={"spatial", "temporal"},
     *      @OA\Property(
     *          property="spatial",
     *          type="object",
     *          description="The spatial extents of the Collection",
     *          @OA\JsonContent(
     *              required={"bbox"},
     *              @OA\Property(
     *                  property="bbox",
     *                  type="array",
     *                  description="Potential spatial extent covered by the collection. The coordinate reference system of the values is WGS 84 longitude/latitude",
     *                  @OA\Items(
     *                      minItems=4,
     *                      maxItems=6,
     *                      type="number"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Property(
     *          property="temporal",
     *          type="object",
     *          description="The temporal extents of the Collection",
     *          @OA\JsonContent(
     *              required={"interval"},
     *              @OA\Property(
     *                  property="interval",
     *                  type="array",
     *                  description="Potential temporal extent covered by the collection. The temporal reference system is the Gregorian calendar",
     *                  @OA\Items(
     *                      type="string"
     *                  )
     *              )
     *          )
     *      ),
     *      example={
     *          "spatial": {
     *              "bbox": {
     *                  {
     *                      -48.6198530870596,
     *                      74.6749788966259,
     *                      -44.6464244356188,
     *                      75.6843970710939
     *                  }
     *              },
     *              "crs": "http://www.opengis.net/def/crs/OGC/1.3/CRS84"
     *          },
     *          "temporal": {
     *              "interval": {
     *                  {
     *                      "2019-06-11T16:11:41.808000Z",
     *                      "2019-06-11T16:11:41.808000Z"
     *                  }
     *              },
     *              "trs": "http://www.opengis.net/def/uom/ISO-8601/0/Gregorian"
     *          }
     *      }
     *  )
     *
     */
    private $extent = array(
        'spatial' => array(
            'bbox' => array(
                null
            ),
            'crs' => 'http://www.opengis.net/def/crs/OGC/1.3/CRS84'
        ),
        'temporal' => array(
            'interval' => array(
                array(
                    null,
                    null
                )
            ),
            'trs' => 'http://www.opengis.net/def/uom/ISO-8601/0/Gregorian'
        )
    );

    /*
     * Summaries
     */
    private $summaries;

    /*
     * Avoid multiple database calls
     */
    private $isLoaded = false;

    /**
     * Constructor
     *
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user)
    {
        /*
         * Context is mandatory
         */
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            RestoLogUtil::httpError(500, 'Context must be defined');
        }

        $this->context = $context;
        $this->user = $user;

        return $this;
    }

    /**
     * Create a collection and store it within database
     *
     * @param array $object : collection description as json file
     * @param string $modelName : model in which to create this collection
     */
    public function create($object, $modelName)
    {
        if (!isset($object['id'])) {
            RestoLogUtil::httpError(400, 'Missing mandatory collection id');
        }

        /*
         * Check that collection does not exist i.e. id or alias not used
         */
        $collectionsFunctions = new CollectionsFunctions($this->context->dbDriver);
        $collectionId = $collectionsFunctions->aliasToCollectionId($object['id']);
        if ( isset($collectionId) ) {
            RestoLogUtil::httpError(409, 'Collection ' . $object['id'] . ' already exist as an alias of collection ' . $collectionId);    
        }
        if ($collectionsFunctions->collectionExists($object['id'])) {
            RestoLogUtil::httpError(409, 'Collection ' . $object['id'] . ' already exist');
        }

        /*
         * Create collection
         */
        $collection = $this->context->keeper->getRestoCollection($object['id'], $this->user);
        $collection->load($object, $modelName)->store();

        return true;
    }

    /**
     * Search features within collections
     *
     * @param RestoModel $model
     * @param array $query
     * @return array (FeatureCollection)
     */
    public function search($model, $query)
    {
        /*
         * Set a global model with all searchFilters and all tables from other collection
         */
        $model = $model ?? $this->getFullModel();

        return (new RestoFeatureCollection($this->context, $this->user, $this->collections, $model, $query))->load(null);
    }

    /**
     * Load all collections from RESTo database and add them to this object
     *
     * @param array $params
     */
    public function load($params = array())
    {
       
        if ( !$this->isLoaded ) {
            
            $params['group'] = $this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ? null : $this->user->getGroupIds();
            $collectionsDesc = (new CollectionsFunctions($this->context->dbDriver))->getCollectionsDescriptions($params);
            
            foreach (array_keys($collectionsDesc) as $collectionId) {
                $collection = $this->context->keeper->getRestoCollection($collectionId, $this->user);
                foreach ($collectionsDesc[$collectionId] as $key => $value) {
                    $collection->$key = $key === 'model' ? new $value() : $value;
                    if ($key === 'model') {
                        $collection->$key = class_exists($value) ? new $value() : new DefaultModel(); 
                    }
                    else {
                        $collection->$key = $value;
                    }
                }
                $this->collections[$collectionId] = $collection;
                $this->updateExtent($collection);
            }
            $this->isLoaded = true;
        }
                
        return $this;
    }

    /**
     * Return object as an array
     *
     * @return array
     */
    public function toArray()
    {
        $collections = array(
            'stac_version' => STACAPI::STAC_VERSION,
            'id' => $this->context->osDescription['ShortName'],
            'type' => 'Catalog',
            'title' => 'Collections',
            'description' => $this->context->osDescription['Description'],
            'keywords' => explode(' ', $this->context->osDescription['Tags']),
            'links' => array(
                array(
                    'rel' => 'self',
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_COLLECTIONS
                ),
                array(
                    'rel' => 'root',
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl']
                ),
                array(
                    'rel' => 'items',
                    'title' => 'All collections',
                    'matched' => 0,
                    'type' => RestoUtil::$contentTypes['geojson'],
                    'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_STAC_SEARCH
                )
            ),
            'extent' => $this->extent,
            'resto:info' => array(
                'osDescription' => $this->context->osDescription
            ),
            'collections' => array()
        );

        if ($this->context->core['useJSONLD']) {
            $collections = JSONLDUtil::addDataCatalogMetadata($collections);
        }

        $totalMatched = 0;

        // Compute global summaries
        $this->getSummaries();

        foreach (array_keys($this->collections) as $key) {

            // Store collection summaries
            $this->collections[$key]->setSummaries($this->summaries[$this->collections[$key]->id] ?? array());

            $collection = $this->collections[$key]->toArray();

            $matched = $collection['summaries']['collection']['count'] ?? 0;

            if ($matched < $this->context->core['collectionMinMatch']) {
                continue;
            }

            $collections['links'][] = array(
                'rel' => 'child',
                'type' => RestoUtil::$contentTypes['json'],
                'title' => $collection['title'],
                'description' => $collection['description'],
                'matched' => $matched,
                'href' => $this->context->core['baseUrl'] . RestoUtil::replaceInTemplate(RestoRouter::ROUTE_TO_COLLECTION, array('collectionId' => $key)),
                'roles' => array('collection')
            );
            $collections['collections'][] = $collection;
            $totalMatched += $collection['summaries']['collection']['count'] ?? 0;
        }

        // Update count for all collections
        $collections['links'][2]['matched'] = $totalMatched;

        /*
         * Sort collections array alphabetically (based on collection title)
         */
        usort($collections['collections'], function ($a, $b) {
            return $a['title'] < $b['title'] ? -1 : 1;
        });
        
        return $collections;
    }

    /**
     * Output collections descriptions as a JSON stream
     *
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false)
    {
        return json_encode($this->toArray(), $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES);
    }

    /**
     * Output collections description as an XML OpenSearch document
     */
    public function getOSDD($model)
    {
        $model = $model ?? new DefaultModel();
        return new OSDD($this->context, $model, $this->filterSummaries($this->getSummaries(), $model->getAutoFacetFields()), null);
    }

    /**
     * Return STAC summaries
     */
    public function getSummaries()
    {
        if ( !isset($this->summaries) ) {
            $this->summaries = (new CatalogsFunctions($this->context->dbDriver))->getSummaries(null, $this->context->core['baseUrl']);
        }
        return $this->summaries;
    }

    /**
     * Update collections extent using input collection extent
     *
     * @param RestoCollection $collection
     */
    private function updateExtent($collection)
    {
        if (isset($collection->extent['temporal']['interval'][0][0]) && (! isset($this->extent['temporal']['interval'][0][0]) || $collection->extent['temporal']['interval'][0][0] < $this->extent['temporal']['interval'][0][0])) {
            $this->extent['temporal']['interval'][0][0] = $collection->extent['temporal']['interval'][0][0];
        }

        if (isset($collection->extent['temporal']['interval'][0][1]) && (! isset($this->extent['temporal']['interval'][0][1]) || $collection->extent['temporal']['interval'][0][1] > $this->extent['temporal']['interval'][0][1])) {
            $this->extent['temporal']['interval'][0][1] = $collection->extent['temporal']['interval'][0][1];
        }
           
        if (isset($collection->extent['spatial']['bbox'][0])) {
            if (! isset($this->extent['spatial']['bbox'][0])) {
                $this->extent['spatial']['bbox'][0] = $collection->extent['spatial']['bbox'][0];
            } else {
                $this->extent['spatial']['bbox'][0] = array(
                    min($this->extent['spatial']['bbox'][0][0], $collection->extent['spatial']['bbox'][0][0]),
                    min($this->extent['spatial']['bbox'][0][1], $collection->extent['spatial']['bbox'][0][1]),
                    max($this->extent['spatial']['bbox'][0][2], $collection->extent['spatial']['bbox'][0][2]),
                    max($this->extent['spatial']['bbox'][0][3], $collection->extent['spatial']['bbox'][0][3])
                );
            }
        }
    }

    /**
     * Return an array of all available searchFilters on all collections
     *
     * @return array
     */
    private function getFullModel()
    {
        $model = new DefaultModel();

        foreach (array_keys($this->collections) as $key) {
            $collection = $this->collections[$key];
            if (isset($collection->model)) {
                $model->searchFilters = array_merge($model->searchFilters, $collection->model->searchFilters);
                $model->tables = array_merge($model->tables, $collection->model->tables);
                $model->stacMapping = array_merge($model->stacMapping, $collection->model->stacMapping);
            }
        }

        return $model;
    }

    
    /**
     * Return an array of summaries containing only $types
     * from an array of collection summaries
     *
     * @param array $summaries
     * @param array $types
     *
     * @return array
     */
    private function filterSummaries($summaries, $types)
    {
        $filteredSummaries = array();
        
        foreach (array_values($summaries) as $summary) {
            foreach (array_keys($summary) as $key) {
                if ( in_array($key, $types) ) {
                    if ( !isset($filteredSummaries[$key]) ) {
                        $filteredSummaries[$key] = $summary[$key];
                    }
                    else if ( isset($summary[$key]['count']) ) {
                        $filteredSummaries[$key]['count'] = ($filteredSummaries[$key]['count'] ?? 0) + $summary[$key]['count'];
                    }
                    else if ( isset($summary[$key]['oneOf']) && $filteredSummaries[$key]['oneOf'] ) {
                        for ($i = 0, $ii = count($summary[$key]['oneOf']); $i < $ii; $i++) {
                            $isNew = true;
                            foreach (array_keys($filteredSummaries[$key]['oneOf']) as $consts) {
                                if ( $filteredSummaries[$key]['oneOf'][$consts]['const'] === $summary[$key]['oneOf'][$i]['const'] ) {
                                    $isNew = false;
                                    $filteredSummaries[$key]['oneOf'][$consts]['count'] = ($filteredSummaries[$key]['oneOf'][$consts]['count'] ?? 0) + $summary[$key]['oneOf'][$i]['count']; 
                                    break;
                                }
                            }
                            if ($isNew) {
                                $filteredSummaries[$key]['oneOf'][] = $summary[$key]['oneOf'][$i];
                            }
                        }
                        usort($filteredSummaries[$key]['oneOf'], function ($a, $b) {
                            return $a['const'] > $b['const'] ? -1 : 1;
                        });
                    }
                }
            }
        }

        return $filteredSummaries;
    }

}
