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
 * RESTo Collection
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
     * Collection licenses (i.e. conditions of use)
     * 
     * Structure
     *      array(
     *          'en' => //license url
     *          'fr' => //license url
     *          ...
     *      )
     */
    public $license;
    
    /*
     * Statistics
     */
    public $statistics = array();
    
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
     */
    public function toArray() {
        return array(
            'name' => $this->name,
            'status' => $this->status,
            'model' => $this->model->name,
            'license' => isset($this->license) ? $this->license : null,
            'osDescription' => $this->osDescription,
            //'propertiesMapping' => $this->propertiesMapping,
            'statistics' => $this->statistics
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
        $osdd = new RestoOSDD($this);
        return $osdd->toString();
    }
 
    /**
     * Return license in the current language
     */
    public function getLicense() {
        if (!isset($this->license)) {
            return null;
        }
        if (!isset($this->license[$this->context->dictionary->language])) {
            if (isset($this->license['en'])) {
                return $this->license['en'];
            }
            return null;
        }
        return $this->license[$this->context->dictionary->language];
    }
    
    /**
     * Load collection parameters from input collection description 
     * Collection description is a JSON file with the following structure
     * 
     *      {
     *          "name": "Charter",
     *          "controller": "RestoCollection_Default",
     *          "status": "public",
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
         * OpenSearch Description
         */
        $this->osDescription = $object['osDescription'];
        
        /*
         * Licence
         */
        $this->license = isset($object['license']) ? $object['license'] : null;
        
        /*
         * Properties mapping
         */
        $this->propertiesMapping = isset($object['propertiesMapping']) ? $object['propertiesMapping'] : array();
        
        /*
         * Save on database
         */
        $this->saveToStore($synchronize);
        
    }
   
    /**
     * Remove collection  from RESTo database
     */
    public function removeFromStore() {
        $this->context->dbDriver->remove(RestoDatabaseDriver::COLLECTION, array('collection' => $this));
    }
    
    /**
     * Load collection parameters from RESTo database
     */
    private function loadFromStore() {
        $description = $this->context->dbDriver->get(RestoDatabaseDriver::COLLECTIONS_DESCRIPTIONS, array(
            'collectionName' => $this->name,
            'facetFields' => $this->getFacetFields()
        ));
        $this->model = RestoUtil::instantiate($description['model'], array());
        $this->osDescription = $description['osDescription'];
        $this->status = $description['status'];
        $this->license = $description['license'];
        $this->propertiesMapping = $description['propertiesMapping'];
        $this->statistics = $description['statistics'];
        
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
        if (!is_array($object['osDescription']) || !is_array($object['osDescription']['en'])) {
            RestoLogUtil::httpError(500, 'English OpenSearch description is mandatory');
        }
        
    }
    
    /**
     * Save collection to database if synchronize is set to true
     * 
     * @param boolean $synchronize
     * @return boolean
     */
    private function saveToStore($synchronize) {
        if ($synchronize) {
            $this->context->dbDriver->store(RestoDatabaseDriver::COLLECTION, array('collection' => $this));
            return true;
        }
        return false;
    }
    
    /**
     * Get facet fields from collection model
     */
    private function getFacetFields() {
        $facetFields = array('collection', 'continent');
        $model = new RestoModel_default();
        foreach (array_values($model->searchFilters) as $filter) {
            if (isset($filter['options']) && $filter['options'] === 'auto') {
                $facetFields[] = $filter['key'];
            }
        }
        return $facetFields;
    }
    
}