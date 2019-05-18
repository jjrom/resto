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
     * Array of RestoCollection (key = collection name)
     */
    private $collections = array();

    /*
     * Statistics
     */
    private $statistics;

    /*
     * True to compute individual statistics per collection
     */
    private $fullStats = false;

    /**
     * Constructor
     *
     * @param RestoContext $context
     * @param RestoUser $user
     * @param array $params
     */
    public function __construct($context, $user, $params = array())
    {

        /*
         * Context is mandatory
         */
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            RestoLogUtil::httpError(500, 'Context must be defined');
        }

        $this->context = $context;
        $this->user = $user;
        if (isset($params['fullStats'])) {
            $this->fullStats = $params['fullStats'];
        }
        return $this;
    }

    /**
     * Create a collection and store it within database
     *
     * @param array $object : collection description as json file
     */
    public function create($object)
    {
        if (!isset($object['name'])) {
            RestoLogUtil::httpError(400, 'Missing mandatory collection name');
        }

        /*
         * Check that collection does not exist based on name
         */
        if ((new CollectionsFunctions($this->context->dbDriver))->collectionExists($object['name'])) {
            RestoLogUtil::httpError(400, 'Collection ' . $object['name'] . ' already exist');
        }

        /*
         * Create collection
         */
        $collection = new RestoCollection($object['name'], $this->context, $this->user);
        $collection->load($object)->store();

        return true;
    }

    /**
     * Search features within collection
     *
     * @param RestoModel $model
     * @return array (FeatureCollection)
     */
    public function search($model)
    {
        return (new RestoFeatureCollection($this->context, $this->user))->setCollections($this->collections)->load($model ?? new DefaultModel());
    }

    /**
     * Load all collections from RESTo database and add them to this object
     */
    public function load()
    {
        
        $collectionsDesc = (new CollectionsFunctions($this->context->dbDriver))->getCollectionsDescriptions($this->user->hasGroup(Resto::GROUP_ADMIN_ID) ? null : $this->user->profile['groups']);
        
        foreach (array_keys($collectionsDesc) as $key) {
            $collection = new RestoCollection($key, $this->context, $this->user);
            $collection->model = new $collectionsDesc[$key]['model']();
            $collection->osDescription = $collectionsDesc[$key]['osDescription'];
            $collection->visibility = intval($collectionsDesc[$key]['visibility']);
            $collection->owner = $collectionsDesc[$key]['owner'];
            $collection->licenseId = $collectionsDesc[$key]['licenseId'];
            $collection->propertiesMapping = $collectionsDesc[$key]['propertiesMapping'];
            $this->collections[$collection->name] = $collection;
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
            $this->statistics = $this->context->fromCache($cacheKey) ?? (new FacetsFunctions($this->context->dbDriver))->getStatistics(null, (new DefaultModel())->getFacetFields());
            $this->context->toCache('getStatistics', $this->statistics);
        }
        return $this->statistics;
    }

    /**
     * Output collections descriptions as a JSON stream
     *
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty)
    {
        $collections = array(
            'osDescription' => $this->context->osDescription,
            'statistics' => $this->getStatistics(),
            'collections' => array()
        );
        foreach (array_keys($this->collections) as $key) {
            $collections['collections'][] = $this->collections[$key]->toArray($this->fullStats);
        }
        return json_encode($collections, $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES);
    }

    /**
     * Output collections description as an XML OpenSearch document
     */
    public function getOSDD($model)
    {
        return new OSDD($this->context, $model ?? new DefaultModel(), $this->getStatistics(), null);
    }
}
