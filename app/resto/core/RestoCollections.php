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
     *                      type="float"
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
                    'min' => null,
                    'max' => null
                )
            ),
            'trs' => 'http://www.opengis.net/def/uom/ISO-8601/0/Gregorian'
        )
    );

    /*
     * Array of RestoCollection (key = collection id)
     */
    private $collections = array();

    /*
     * Statistics
     */
    private $statistics;

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
     */
    public function create($object)
    {
        if (!isset($object['id'])) {
            RestoLogUtil::httpError(400, 'Missing mandatory collection id');
        }

        /*
         * Check that collection does not exist based on id
         */
        if ((new CollectionsFunctions($this->context->dbDriver))->collectionExists($object['id'])) {
            RestoLogUtil::httpError(400, 'Collection ' . $object['id'] . ' already exist');
        }

        /*
         * Create collection
         */
        $collection = new RestoCollection($object['id'], $this->context, $this->user);
        $collection->load($object)->store();

        return true;
    }

    /**
     * Search features within collection
     *
     * @param RestoModel $model
     * @param array $query
     * @return array (FeatureCollection)
     */
    public function search($model, $query)
    {
        return (new RestoFeatureCollection($this->context, $this->user, $this->collections))->load($model ?? new DefaultModel(), null, $query);
    }

    /**
     * Load all collections from RESTo database and add them to this object
     */
    public function load()
    {
        
        $group = $this->user->hasGroup(Resto::GROUP_ADMIN_ID) ? null : $this->user->profile['groups'];
        $cacheKey = 'collections' . ($group ? join(',', $group) : '');
        
        $collectionsDesc = $this->context->fromCache($cacheKey);
        if (!isset($collectionsDesc)) {
            $collectionsDesc = (new CollectionsFunctions($this->context->dbDriver))->getCollectionsDescriptions($group);
            $this->context->toCache($cacheKey, $collectionsDesc);
        }

        foreach (array_keys($collectionsDesc) as $collectionId) {
            $collection = new RestoCollection($collectionId, $this->context, $this->user);
            foreach ($collectionsDesc[$collectionId] as $key => $value) {
                $collection->$key = $key === 'model' ? new $value() : $value;
            }
            $this->collections[$collectionId] = $collection;
            $this->updateExtent($collection);
        }
        
        return $this;
    }

    /**
     * Return collections statistics
     */
    public function getStatistics()
    {
        if (!isset($this->statistics)) {
            $cacheKey = 'getStatistics';
            $this->statistics = $this->context->fromCache($cacheKey);
            if (!isset($this->statistics)) {
                $this->statistics = (new FacetsFunctions($this->context->dbDriver))->getStatistics(null, (new DefaultModel())->getAutoFacetFields());
                $this->context->toCache('getStatistics', $this->statistics);
            }
        }
        return $this->statistics;
    }

    /**
     * Return object as an array
     * 
     * @return array
     */
    public function toArray()
    {

        $collections = array(
            'id' => $this->context->osDescription['ShortName'],
            'title' => $this->context->osDescription['LongName'] ?? $this->context->osDescription['ShortName'],
            'description' => $this->context->osDescription['Description'],
            'keywords' => explode(' ', $this->context->osDescription['Tags']),
            'links' => array(
                array(
                    'rel' => 'self',
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl'] . '/collections'
                ),
                array(
                    'rel' => 'root',
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl']
                )   
            ),
            'extent' => $this->extent,
            'summaries' => array(
                'resto:stats' => $this->getStatistics()
            ),
            'resto:info' => array(
                'osDescription' => $this->context->osDescription
            ),
            'collections' => array()
        );

        // STAC
        if ( isset($this->context->addons['STAC']) ) {
            
            $collections['links'][] = array(
                'rel' => 'items',
                'type' => RestoUtil::$contentTypes['geojson'],
                'href' => $this->context->core['baseUrl'] . '/search'
            );

            $collections['stac_version'] = STAC::STAC_VERSION;

        }

        
        foreach (array_keys($this->collections) as $key) {
            $collection = $this->collections[$key]->toArray(array(
                'stats' => isset($this->context->query['_stats']) ? filter_var($this->context->query['_stats'], FILTER_VALIDATE_BOOLEAN) : false
            ));
            $collections['collections'][] = $collection;
            $collections['links'][] = array(
                'rel' => 'child',
                'type' => RestoUtil::$contentTypes['json'],
                'title' => $collection['title'],
                'description' => $collection['description'],
                'matched' => $collections['summaries']['resto:stats']['facets']['collection'][$key] ?? 0,
                'href' => $this->context->core['baseUrl'] . '/collections/' . $key,
                'roles' => array('collection')
            );
                
        }

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
        return new OSDD($this->context, $model ?? new DefaultModel(), $this->getStatistics(), null);
    }

    /**
     * Update collections extent using input collection extent
     * 
     * @param RestoCollection $collection
     */
    private function updateExtent($collection)
    {

        if (isset($collection->datetime['min'])) {
            if ( ! isset($this->extent['temporal']['interval'][0]['min']) || $collection->datetime['min'] < $this->extent['temporal']['interval'][0]['min']) {
                $this->extent['temporal']['interval'][0]['min'] = $collection->datetime['min'];
            }
        }

        if (isset($collection->datetime['max'])) {
            if ( ! isset($this->extent['temporal']['interval'][0]['max']) || $collection->datetime['max'] > $this->extent['temporal']['interval'][0]['max']) {
                $this->extent['temporal']['interval'][0]['max'] = $collection->datetime['max'];
            }
        }
           
        if (isset($collection->bbox)) {
            if ( ! isset($this->extent['spatial']['bbox'][0]) ) {
                $this->extent['spatial']['bbox'][0] = $collection->bbox;
            }
            else {
                if ( $collection->bbox[0] < $this->extent['spatial']['bbox'][0][0] ) {
                    $this->extent['spatial']['bbox'][0][0] = $collection->bbox[0];
                }
                if ( $collection->bbox[1] < $this->extent['spatial']['bbox'][0][1] ) {
                    $this->extent['spatial']['bbox'][0][1] = $collection->bbox[1];
                }
                if ( $collection->bbox[2] > $this->extent['spatial']['bbox'][0][2] ) {
                    $this->extent['spatial']['bbox'][0][2] = $collection->bbox[2];
                }
                if ( $collection->bbox[3] > $this->extent['spatial']['bbox'][0][3] ) {
                    $this->extent['spatial']['bbox'][0][3] = $collection->bbox[3];
                }
            }
        }

    }

}
