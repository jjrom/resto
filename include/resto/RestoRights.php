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

class RestoRights{
    
    /*
     * User identifier
     */
    private $identifier;
    
    /*
     * User group
     */
    private $groupname;
    
    /*
     * Context
     */
    private $context;
    
    /*
     * Group rights
     */
    public $groupRights = array(
        'unregistered' => array(
            'search' => 1,
            'visualize' => 0,
            'download' => 0,
            'post' => 0,
            'put' => 0,
            'delete' => 0
        ),
        'default' => array(
            'search' => 1,
            'visualize' => 0,
            'download' => 0,
            'post' => 0,
            'put' => 0,
            'delete' => 0
        ),
        'admin' => array(
            'search' => 1,
            'visualize' => 1,
            'download' => 1,
            'post' => 1,
            'put' => 1,
            'delete' => 1
        )
    );
    
    /**
     * Constructor
     * 
     * @param string $identifier
     * @param string $groupname
     * @param RestoContext $context
     */
    public function __construct($identifier, $groupname, $context){
        $this->identifier = isset($identifier) ? $identifier : $groupname;
        $this->groupname = $groupname;
        $this->context = $context;
    }
    
    /**
     * Returns user rights for collection and/or identifier
     * 
     * Return array(
     *          'search' => true/false,
     *          'download' => true/false,
     *          'visualize' => true/false,
     *          'filters' => array(
     *                  ...
     *          ),
     *       )
     * 
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     */
    public function getRights($collectionName = null, $featureIdentifier = null){
        
        /*
         * Return feature rights
         */
        if (isset($collectionName) && isset($featureIdentifier)) {
            return $this->getFeatureRights($collectionName, $featureIdentifier);
        }
        
        /*
         * Return collection rights
         */
        if (isset($collectionName)){
            return $this->getCollectionRights($collectionName);
        }
        
        /*
         * Return group rights
         */
        $groupRights = $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS, array('emailOrGroup' => $this->groupname, 'collectionName' => $collectionName));
        if (!isset($groupRights) || $this->isIncomplete($groupRights)) {
            return !isset($groupRights) ? $this->groupRights[$this->groupname] : $this->mergeRights($groupRights, $this->groupRights[$this->groupname]);
        }
        
        return $this->groupRights[$this->groupname];
        
    }
    
    /**
     * Returns user rights for feature
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     */
    private function getFeatureRights($collectionName, $featureIdentifier) {
        
        $rights = $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS, array('emailOrGroup' => $this->identifier, 'collectionName' => $collectionName, 'featureIdentifier' => $featureIdentifier));
        if (!isset($rights)) {
            return $this->getRights($collectionName);
        }
        else if ($this->isIncomplete($rights)) {
            $collectionRights = $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS, array('emailOrGroup' => $this->identifier, 'collectionName' => $collectionName));
            if (!isset($collectionRights) || $this->isIncomplete($collectionRights)) {
                $rights = isset($collectionRights) ? $this->mergeRights($rights, $collectionRights) : $rights;
                $groupRights = $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS, array('emailOrGroup' => $this->groupname, 'collectionName' => $collectionName));
                if (!isset($groupRights) || $this->isIncomplete($groupRights)) {
                    return $this->mergeRights(isset($groupRights) ? $this->mergeRights($rights, $groupRights) : $rights, $this->groupRights[$this->groupname]);
                }
                return $this->mergeRights($rights, $groupRights);
            }
            return $this->mergeRights($rights, $collectionRights);
        }
        return $rights;
    }
    
    /**
     * Returns user rights for feature
     * 
     * @param string $collectionName
     */
    private function getCollectionRights($collectionName) {
        $collectionRights = $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS, array('emailOrGroup' => $this->identifier, 'collectionName' => $collectionName));
        if (!isset($collectionRights) || $this->isIncomplete($collectionRights)) {
            $groupRights = $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS, array('emailOrGroup' => $this->groupname, 'collectionName' => $collectionName));
            if (!isset($groupRights) || $this->isIncomplete($groupRights)) {
                $groupRights = !$groupRights ? $this->groupRights[$this->groupname] : $this->mergeRights($groupRights, $this->groupRights[$this->groupname]);
            }
            return !isset($collectionRights) ? $groupRights : $this->mergeRights($collectionRights, $groupRights);
        }
        return $collectionRights;
    }
    
    /**
     * Returns user full rights
     * 
     * Return array(
     *      'Collection1' => array(
     *          'search' => true/false,
     *          'download' => true/false,
     *          'visualize' => true/false,
     *          'filters' => array(
     *                  ...
     *          ),
     *          'features' => array(
     *              ...
     *          )
     *       ),
     *      'Collection2' => ...
     * 
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     */
    public function getFullRights($collectionName = null, $featureIdentifier = null) {
        $rights = $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS_FULL, array('emailOrGroup' => $this->identifier, 'collectionName' => $collectionName, 'featureIdentifier' => $featureIdentifier));
        return isset($rights) ? array_merge(array('*' => $this->groupRights[$this->groupname]), $rights) : array('*' => $this->groupRights[$this->groupname]);
    }
    
    /**
     * Replace true if rights array has null values
     * 
     * @param array $rights
     */
    private function isIncomplete($rights) {
        foreach (array_values($rights) as $value){
            if (!isset($value)){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Merge two rights array replacing null values if possible
     * Note that first array has preseance on the second 
     * 
     * @param array $masterRights
     * @param array $slaveRights
     */
    private function mergeRights($masterRights, $slaveRights) {
        if (isset($slaveRights)) {
            foreach ($masterRights as $key => $value){
                if (!isset($value) && isset($slaveRights[$key])) {
                    $masterRights[$key] = $slaveRights[$key];
                }
            }
        }
        return $masterRights;
    }
    
}
