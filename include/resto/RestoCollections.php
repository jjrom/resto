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
 * RESToCollections is a list of RestoCollection objects
 * 
 */
class RestoCollections {
    
    /**
     * RestoContext
     */
    public $context;
    
    /*
     * Array of RestoCollection (key = collection name)
     */
    private $collections = array();
    
    /**
     * Constructor 
     * 
     * @param RestoContext $context
     * @param array $options
     */
    public function __construct($context, $options = array()) {
        
        /*
         * Context is mandatory
         */
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            throw new Exception('Context must be defined', 500);
        }
        
        $this->context = $context;
        
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
        return new RestoFeatureCollection($this->context, null);
    }
    
    /**
     * Load all collections from RESTo database and add them to this object
     */
    public function loadCollectionsFromStore() {
        $collectionsDescriptions = $this->context->dbDriver->getCollectionsDescriptions();
        foreach (array_keys($collectionsDescriptions) as $key) {
            $collection = new RestoCollection($key, $this->context);
            $collection->model = RestoUtil::instantiate($collectionsDescriptions[$key]['model'], array($collection->context));
            $collection->osDescription = $collectionsDescriptions[$key]['osDescription'];
            $collection->status = $collectionsDescriptions[$key]['status'];
            $collection->licence = $collectionsDescriptions[$key]['license'];
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
    
    /**
     * Output collection description as an HTML page
     */
    public function toHTML() {
        return RestoUtil::get_include_contents(realpath(dirname(__FILE__)) . '/../../themes/' . $this->context->config['theme'] . '/templates/collections.php', $this);
    }
}
