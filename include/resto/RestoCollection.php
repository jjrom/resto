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
 *  resto collection
 * 
 *  @SWG\Tag(
 *      name="collection",
 *      description="A collection is a set of features that usually share common properties - for instance features from the same mission (e.g. 'Sentinel1')"
 *  )
 */
class RestoCollection {
    
    /*
     * Collection name must be unique
     */
    public $name =  null;
   
    /*
     * Data model for this collection
     */
    public $model = null;
    
    /*
     * Properties mapping
     */
    public $propertiesMapping = array();
    
    /*
     * Context reference
     */
    public $context = null;
    
    /*
     * User
     */
    public $user = null;
    
    /*
     * Array of OpenSearch Description parameters per lang
     */
    public $osDescription = null;
    
    /*
     * Collection license 
     */
    public $license;
    
    /*
     * Statistics
     */
    private $statistics = null;
    
    /*
     * Array of options
     */
    private $options = array();
    
    /**
     * Constructor
     * 
     * @param string $name : collection name
     * @param RestoContext $context : RESTo context
     * @param RestoUser $user : RESTo user
     * @param array $options : constructor options
     */
    public function __construct($name, $context, $user, $options = array()) {
        
        /*
         * Context is mandatory
         */
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            RestoLogUtil::httpError(500, 'Context must be defined');
        }
        
        /*
         * Collection name should be alphanumeric based only except for reserved '*' collection
         */
        if (!isset($name) || !ctype_alnum($name) || is_numeric(substr($name, 0, 1))) {
            RestoLogUtil::httpError(500, 'Collection name must be an alphanumeric string not starting with a digit');
        }
        
        $this->name = $name;
        $this->context = $context;
        $this->user = $user;
        $this->options = $options;
        
        /*
         * Load collection description from database 
         */
        if (isset($options['autoload']) && $options['autoload']) {
            $this->loadFromStore();
        }
        
    }
    
    /**
     * Return collection url
     * 
     * @param string $format : output format for url
     */
    public function getUrl($format = '') {
        return RestoUtil::restoUrl($this->context->baseUrl, '/collections/' . $this->name, $format);
    }
    
    /**
     * Search features within collection
     * 
     * @return array (FeatureCollection)
     */
    public function search() {
        return new RestoFeatureCollection($this->context, $this->user, $this);
    }
    
    /**
     * Add feature to the {collection}.features table
     * 
     * @param array $data : GeoJSON file or file splitted in array
     */
    public function addFeature($data) {
        return $this->model->storeFeature($data, $this);
    }
    
    /**
     * Return UUIDv5 from input $identifier
     * 
     * @param string $identifier 
     */
    public function toFeatureId($identifier) {
        return RestoUtil::UUIDv5($this->name . ':' . strtoupper($identifier));
    }
    
    /**
     * Output collection description as an array
     * 
     * @param boolean $setStatistics (true to return statistics)
     */
    public function toArray($setStatistics = true) {
        return array(
            'name' => $this->name,
            'status' => $this->status,
            'owner' => $this->owner,
            'model' => $this->model->name,
            'license' => isset($this->license) ? $this->license->toArray() : null,
            'osDescription' => isset($this->osDescription[$this->context->dictionary->language]) ? $this->osDescription[$this->context->dictionary->language] : $this->osDescription['en'],
            'statistics' => $setStatistics ? $this->getStatistics() : array()
        );
    }
    
    /**
     * Output collection description as a JSON stream
     * 
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false) {
        return RestoUtil::json_format($this->toArray(), $pretty);
    }
    
    /**
     * Output collection description as an XML OpenSearch document
     */
    public function toXML() {
        $osdd = new RestoOSDD($this->context, $this->model, $this->getStatistics(), $this);
        return $osdd->toString();
    }
 
    /**
     * Load collection parameters from input collection description 
     * Collection description is a JSON file with the following structure
     * 
     *      {
     *          "name": "Charter",
     *          "controller": "RestoCollection_Default",
     *          "status": "public",
     *          "licenseId": "license",
     *          "rights":{
     *              "download":0,
     *              "visualize":1
     *          },
     *          "osDescription": {
     *              "en": {
     *                  "ShortName": "International Charter Space and Major Disasters",
     *                  "LongName": "International Charter Space and Major Disasters catalog",
     *                  "Description": "The International Charter aims at providing a unified system of space data acquisition and delivery to those affected by natural or man-made disasters through Authorized Users. Each member agency has committed resources to support the provisions of the Charter and thus is helping to mitigate the effects of disasters on human life and property",
     *                  "Tags": "international charter space disasters",
     *                  "Developper": "J\u00e9r\u00f4me Gasperi",
     *                  "Contact": "jerome.gasperi@gmail.com",
     *                  "Query": "Cyclones in Asia in october 2013",
     *                  "Attribution": "RESTo framework. Copyright 2013, All Rights Reserved"
     *              },
     *              "fr": {
     *                  ...
     *              }
     *          },
     *          "propertiesMapping": {
     *              "identifier": "{a:1} will be replaced by identifier property value",
     *              "organisationName": "This is a constant"
     *              ...
     *          }
     *      }
     * 
     * @param array $object : collection description as json file
     * @param boolean $synchronize : true to store collection to database
     */
    public function loadFromJSON($object, $synchronize = false) {
        
        /*
         * Check JSON validity
         */
        $this->checkJSONValidity($object);
        
        /*
         * Set Model
         */
        $this->setModel($object['model']);
        
        /*
         * Default collection status is 'public'
         */
        $this->status = isset($object['status']) && $object['status'] === 'private' ? 'private' : 'public';
        
        /*
         * Collection owner is the current user
         */
        $this->owner = $this->user->profile['email'];
        
        /*
         * OpenSearch Description
         */
        $this->osDescription = $object['osDescription'];
        
        /*
         * Licence - set to 'unlicensed' if not specified
         */
        $this->license = new RestoLicense($this->context, isset($object['licenseId']) ? $object['licenseId'] : 'unlicensed');
        
        /*
         * Properties mapping
         */
        $this->propertiesMapping = isset($object['propertiesMapping']) ? $object['propertiesMapping'] : array();
        
        /*
         * Save on database
         */
        $this->saveToStore(isset($object['rights']) ? $object['rights'] : array(), $synchronize);
        
    }
   
    /**
     * Remove collection  from RESTo database
     */
    public function removeFromStore() {
        $this->context->dbDriver->remove(RestoDatabaseDriver::COLLECTION, array('collection' => $this));
    }
    
    /**
     * Return collection statistics
     */
    public function getStatistics() {
        if (!isset($this->statistics)) {
            $this->statistics = $this->context->dbDriver->get(RestoDatabaseDriver::STATISTICS, array('collectionName' => $this->name, 'facetFields' => $this->model->getFacetFields())); 
        }
        return $this->statistics;
    }
    
    /**
     * Load collection parameters from RESTo database
     */
    private function loadFromStore() {
        $descriptions = $this->context->dbDriver->get(RestoDatabaseDriver::COLLECTIONS_DESCRIPTIONS, array(
            'collectionName' => $this->name
        ));
        if (!isset($descriptions) || !isset($descriptions[$this->name])) {
            RestoLogUtil::httpError(404);
        }
        $this->model = RestoUtil::instantiate($descriptions[$this->name]['model'], array());
        $this->osDescription = $descriptions[$this->name]['osDescription'];
        $this->status = $descriptions[$this->name]['status'];
        $this->owner = $descriptions[$this->name]['owner'];
        $this->license = new RestoLicense($this->context, $descriptions[$this->name]['license']['licenseId'], false);
        $this->license->setDescription($descriptions[$this->name]['license'], false);
        $this->propertiesMapping = $descriptions[$this->name]['propertiesMapping'];
    }
    
    /**
     * Set model 
     * 
     * @param string $name
     */
    private function setModel($name) {
        
        /*
         * Check that input file is for the current collection
         */
        if (isset($this->model)) {
            if ($this->model->name !== $name) {
                RestoLogUtil::httpError(500, 'Property "model" and collection name differ');
            }
        }
        /*
         * Set model
         */
        else {
            $this->model = RestoUtil::instantiate($name, array($this->context, $this->user));
        }
        
    }
    
    /**
     * Check that input json description is valid
     * 
     * @param array $object
     */
    private function checkJSONValidity($object) {
        
        /*
         * Input $object should be JSON
         */
        if (!isset($object) || !is_array($object)) {
            RestoLogUtil::httpError(500, 'Invalid input JSON');
        }
        
        /*
         * Check that input file is for the current collection
         */
        if (!isset($object['name']) ||$this->name !== $object['name']) {
            RestoLogUtil::httpError(500, 'Property "name" and collection name differ');
        }
        
        /*
         * Model name must be set in JSON file
         */
        if (!isset($object['model'])) {
            RestoLogUtil::httpError(500, 'Property "model" is mandatory');
        }
        
        /*
         * At least an english OpenSearch Description object is mandatory
         */
        if (!isset($object['osDescription']) || !is_array($object['osDescription']) || !isset($object['osDescription']['en']) || !is_array($object['osDescription']['en'])) {
            RestoLogUtil::httpError(500, 'English OpenSearch description is mandatory');
        }
        
    }
    
    /**
     * Save collection to database if synchronize is set to true
     * 
     * @param array $rights
     * @param boolean $synchronize
     * @return boolean
     */
    private function saveToStore($rights, $synchronize) {
        if ($synchronize) {
            $this->context->dbDriver->store(RestoDatabaseDriver::COLLECTION, array('collection' => $this, 'rights' => $rights));
            return true;
        }
        return false;
    }
    
}