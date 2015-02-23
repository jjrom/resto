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
            'search' => true,
            'visualize' => false,
            'download' => false,
            'post' => false,
            'put' => false,
            'delete' => false
        ),
        'default' => array(
            'search' => true,
            'visualize' => false,
            'download' => false,
            'post' => false,
            'put' => false,
            'delete' => false
        ),
        'admin' => array(
            'search' => true,
            'visualize' => true,
            'download' => true,
            'post' => true,
            'put' => true,
            'delete' => true
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
        $groupRights = $this->context->dbDriver->getRights($this->groupname, $collectionName);
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
        
        $rights = $this->context->dbDriver->getRights($this->identifier, $collectionName, $featureIdentifier);
        if (!isset($rights)) {
            return $this->getRights($collectionName);
        }
        else if ($this->isIncomplete($rights)) {
            $collectionRights = $this->context->dbDriver->getRights($this->identifier, $collectionName);
            if (!isset($collectionRights) || $this->isIncomplete($collectionRights)) {
                $rights = isset($collectionRights) ? $this->mergeRights($rights, $collectionRights) : $rights;
                $groupRights = $this->context->dbDriver->getRights($this->groupname, $collectionName);
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
        $collectionRights = $this->context->dbDriver->getRights($this->identifier, $collectionName);
        if (!isset($collectionRights) || $this->isIncomplete($collectionRights)) {
            $groupRights = $this->context->dbDriver->getRights($this->groupname, $collectionName);
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
        return array_merge(array('*' => $this->groupRights[$this->groupname]), $this->context->dbDriver->getFullRights($this->identifier, $collectionName, $featureIdentifier));
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
