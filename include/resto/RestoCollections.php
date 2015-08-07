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
 * RESToCollections is a list of RestoCollection objects
 * 
 */
class RestoCollections {
    
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
     * Model
     */
    private $model;
    
    /*
     * Statistics
     */
    private $statistics;
    
    /**
     * Constructor 
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     * @param array $options
     */
    public function __construct($context, $user, $options = array()) {
        
        /*
         * Context is mandatory
         */
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            RestoLogUtil::httpError(500, 'Context must be defined');
        }
        
        $this->context = $context;
        $this->user = $user;
        $this->model = new RestoModel_default($context, $user);
        
        /*
         * Load collection description from database 
         */
        if (isset($options['autoload']) && $options['autoload']) {
            $this->loadCollectionsFromStore();
        }
        
        return $this;
    }
    
    /**
     * Return the list of RestoCollections
     */
    public function getCollections() {
        return $this->collections;
    }
    
    /**
     * Return $collection or 
     * @param String $name : collection name
     */
    public function getCollection($name) {
        return $this->collections[$name];
    }
    
    /**
     * Create a collection and store it within database
     * 
     * @param array $object : collection description as json file
     */
    public function create($object) {
        
        $name = isset($object['name']) ? $object['name'] : null;
        
        /*
         * Check that collection does not exist
         */
        if (isset($name) && isset($this->collections[$name])) {
            RestoLogUtil::httpError(2003);
        }
        
        /*
         * Load collection
         */
        $collection = new RestoCollection($name, $this->context, $this->user);
        $collection->loadFromJSON($object, true);
        
        /*
         * Store query
         */
        if ($this->context->storeQuery === true) {
            $this->user->storeQuery($this->context->method, 'create', $name, null, $this->context->query, $this->context->getUrl());
        }
        
        return true;
        
    }
    
    /**
     * Remove collection identified by name
     * @param String $name : collection name
     */
    public function remove($name) {
        unset($this->collections[$name]);
    }

    /**
     * Search features within collection
     * 
     * @return array (FeatureCollection)
     */
    public function search() {
        return new RestoFeatureCollection($this->context, $this->user, $this->getCollections());
    }
    
    /**
     * Load all collections from RESTo database and add them to this object
     */
    public function loadCollectionsFromStore() {
        $collectionsDescriptions = $this->context->dbDriver->get(RestoDatabaseDriver::COLLECTIONS_DESCRIPTIONS);
        foreach (array_keys($collectionsDescriptions) as $key) {
            $collection = new RestoCollection($key, $this->context, $this->user);
            $collection->model = RestoUtil::instantiate($collectionsDescriptions[$key]['model'], array($collection->context, $collection->user));
            $collection->osDescription = $collectionsDescriptions[$key]['osDescription'];
            $collection->status = $collectionsDescriptions[$key]['status'];
            $collection->owner = $collectionsDescriptions[$key]['owner'];
            $collection->license = new RestoLicense($this->context, $collectionsDescriptions[$key]['license']['licenseId'], false);
            $collection->license->setDescription($collectionsDescriptions[$key]['license'], false);
            $collection->propertiesMapping = $collectionsDescriptions[$key]['propertiesMapping'];
            $this->collections[$collection->name] = $collection;
        }
        return $this;
    }
    
    /**
     * Return collections statistics
     */
    public function getStatistics() {
        if (!isset($this->statistics)) {
            $this->statistics = $this->context->dbDriver->get(RestoDatabaseDriver::STATISTICS, array('collectionName' => null, 'facetFields' => $this->model->getFacetFields())); 
        }
        return $this->statistics;
    }
    
    /**
     * Output collections descriptions as a JSON stream
     * 
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty) {
        $collections = array(
            'synthesis' => array(
                'name' => '*',
                'osDescription' => isset($this->context->osDescription[$this->context->dictionary->language]) ? $this->context->osDescription[$this->context->dictionary->language] : $this->context->osDescription['en'],
                'statistics' => $this->context->dbDriver->get(RestoDatabaseDriver::STATISTICS, array('collectionName' => null, 'facetFields' => $this->model->getFacetFields()))
            ),
            'collections' => array()
        );
        foreach(array_keys($this->collections) as $key) {
            $collections['collections'][] = $this->collections[$key]->toArray(true);
        }
        return RestoUtil::json_format($collections, $pretty);
    }
    
    /**
     * Output collections description as an XML OpenSearch document
     */
    public function toXML() {
        $osdd = new RestoOSDD($this->context, $this->model, $this->getStatistics(), null);
        return $osdd->toString();
    }
    
}
