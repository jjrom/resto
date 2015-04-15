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
     * Add a collection
     * 
     * @param RestoCollection $collection
     */
    public function add($collection) {
        $this->collections[$collection->name] = $collection;
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
        $collectionsDescriptions = $this->context->dbDriver->get(RestoDatabaseDriver::COLLECTIONS_DESCRIPTIONS, array(
            'context' => $this->context,
            'user' => $this->user
        ));
        foreach (array_keys($collectionsDescriptions) as $key) {
            $collection = new RestoCollection($key, $this->context, $this->user);
            $collection->model = RestoUtil::instantiate($collectionsDescriptions[$key]['model'], array($collection->context, $collection->user));
            $collection->osDescription = $collectionsDescriptions[$key]['osDescription'];
            $collection->status = $collectionsDescriptions[$key]['status'];
            $collection->license = $collectionsDescriptions[$key]['license'];
            $collection->propertiesMapping = $collectionsDescriptions[$key]['propertiesMapping'];
            $collection->statistics = $collectionsDescriptions[$key]['statistics'];
            $this->add($collection);
        }
        return $this;
    }
    
    /**
     * Output collections descriptions as a JSON stream
     * 
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty) {
        $collections = array('collections' => array());
        foreach(array_keys($this->collections) as $key) {
            $collections['collections'][] = $this->collections[$key]->toArray();
        }
        return RestoUtil::json_format($collections, $pretty);
    }
    
}
