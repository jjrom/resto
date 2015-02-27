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

class RestoUser{
    
    /*
     * User profile
     */
    public $profile;
    
    /*
     * Context
     */
    public $context;
    
    /*
     * User cart
     */
    private $cart;
    
    /*
     * Resto rights
     */
    private $rights;
    
    /**
     * Constructor
     * 
     * @param array $profile : User profile
     * @param RestoContext $context
     */
    public function __construct($profile, $context) {
        
        $this->context = $context;
        
        /*
         * Assign default profile for unauthentified user
         */
        if (!isset($profile)) {
            $this->profile = array(
                'userid' => -1,
                'groupname' => 'unregistered',
                'activated' => false
            );
        }
        else {
            $this->profile = $profile;
        }
        
        /*
         * Set rights and cart for identified user
         */
        if ($this->profile['userid'] === -1) {
            $this->rights = new RestoRights('unregistered', 'unregistered', $this->context);
        }
        else {
            $this->rights = new RestoRights($this->profile['email'], $this->profile['groupname'], $this->context);
            $this->cart = new RestoCart($this, $this->context, true);
        }
        
    }
    
    /**
     * Returns rights for collection and/or identifier
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     */
    public function getRights($collectionName = null, $featureIdentifier = null) {
        return $this->profile['activated'] === false ? $this->rights->groupRights['unregistered'] : $this->rights->getRights($collectionName, $featureIdentifier);
    }
    
    /**
     * Returns full rights for collection and/or identifier
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     */
    public function getFullRights($collectionName = null, $featureIdentifier = null) {
        return $this->profile['activated'] === false ? array('*' => $this->rights->groupRights['unregistered']) : $this->rights->getFullRights($collectionName, $featureIdentifier);
    }
    
    /**
     * Store user query to database
     * 
     * @param string $method
     * @param string $service
     * @param string $collectionName
     * @param string $featureIdentifier
     * @param array $query
     * @param string $url
     */
    public function storeQuery($method, $service, $collectionName, $featureIdentifier, $query, $url){
        try {
            $remoteAdress = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING); 
            $this->context->dbDriver->store(RestoDatabaseDriver::QUERY, array(
                'userid' => $this->profile['userid'],
                'query' => array(
                    'method' => $method,
                    'service' => $service,
                    'collection' => $collectionName,
                    'resourceid' => $featureIdentifier,
                    'query' => $query,
                    'url' => $url,
                    'ip' => $remoteAdress,
                ))
            );
        } catch (Exception $e) {}
    }
    
    /**
     * Can User visualize ?
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     * @param string $resourceUrl
     * @param string $token
     * @return boolean
     */
    public function canVisualize($collectionName = null, $featureIdentifier = null, $resourceUrl = null, $token = null){
        return $this->canDownloadOrVisualize('visualize', $collectionName, $featureIdentifier, $resourceUrl, $token);
    }
    
    /**
     * Can User download ? 
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     * @param string $resourceUrl
     * @param string $token
     * @return boolean
     */
    public function canDownload($collectionName = null, $featureIdentifier = null, $resourceUrl = null, $token = null){
        return $this->canDownloadOrVisualize('download', $collectionName, $featureIdentifier, $resourceUrl, $token);
    }
    
    /**
     * Can User POST ?
     * 
     * @param string $collectionName
     * @return boolean
     */
    public function canPost($collectionName = null){
        $rights = $this->rights->getRights($collectionName);
        return $rights['post'];
    }
    
    /**
     * Can User PUT ?
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     * @return boolean
     */
    public function canPut($collectionName, $featureIdentifier = null){
        $rights = $this->rights->getRights($collectionName, $featureIdentifier);
        return $rights['put'];
    }
    
    /**
     * Can User DELETE ?
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     * @return boolean
     */
    public function canDelete($collectionName, $featureIdentifier = null){
        $rights = $this->rights->getRights($collectionName, $featureIdentifier);
        return $rights['delete'];
    }
    
    /**
     * Check if user has to sign license for collection
     * 
     * @param RestoCollection $collection
     */
    public function hasToSignLicense($collection) {
        if (!empty($collection->license)) {
            if (!isset($this->profile['email']) || !$this->context->dbDriver->is(RestoDatabaseDriver::LICENSE_SIGNED, array('email' => $this->profile['email'], 'collectionName' => $collection->name))) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Sign license for collection
     * 
     * @param string $collectionName
     */
    public function signLicense($collectionName) {
        if ($this->context->dbDriver->execute(RestoDatabaseDriver::SIGN_LICENSE, array('email' => $this->profile['email'], 'collectionName' => $collectionName))) {
            return true;
        }
        return false;
    }
    
    /**
     * Disconnect user
     */
    public function disconnect() {
        if (!$this->context->dbDriver->execute(RestoDatabaseDriver::DISCONNECT_USER, array('email' => $this->profile['email']))) {
            return false;
        }
        return true;
    }
    
    /**
     * Return user cart
     */
    public function getCart() {
        return $this->cart;
    }
    
    /**
     * Add item to cart
     * 
     * @param array $data
     * @param boolean $synchronize
     */
    public function addToCart($data, $synchronize = false) {
        return isset($this->cart) ? $this->cart->add($data, $synchronize) : false;
    }
    
    /**
     * Add item to cart
     * 
     * @param string $itemId
     * @param array $item
     * @param boolean $synchronize
     */
    public function updateCart($itemId, $item, $synchronize = false) {
        return isset($this->cart) ? $this->cart->update($itemId, $item, $synchronize) : false;
    }
    
    /**
     * Remove item from cart
     * 
     * @param string $itemId
     * @param boolean $synchronize
     */
    public function removeFromCart($itemId, $synchronize = false) {
        return isset($this->cart) ? $this->cart->remove($itemId, $synchronize) : false;
    }
    
    /**
     * Return user orders
     */
    public function getOrders() {
        return $this->context->dbDriver->get(RestoDatabaseDriver::ORDERS, array('email' => $this->profile['email']));
    }
    
    /**
     * Place order
     */
    public function placeOrder() {
        $order = $this->context->dbDriver->store(RestoDatabaseDriver::ORDER, array('email' => $this->profile['email']));
        if (isset($order) && isset($this->cart)) {
            $this->cart->clear();
        }
        return $order;
    }
    
    /**
     * Can User download or visualize 
     * 
     * @param string $action
     * @param string $collectionName
     * @param string $featureIdentifier
     * @param string $resourceUrl
     * @param string $token
     * @return boolean
     */
    private function canDownloadOrVisualize($action, $collectionName = null, $featureIdentifier = null, $resourceUrl = null, $token = null){
        
        if (!isset($resourceUrl) || !isset($token)) {
            return false;
        }
        if ($this->context->dbDriver->is(RestoDatabaseDriver::SHARED_LINK, array('resourceUrl' => $resourceUrl, 'token' => $token))) {
            return true;
        }
    
        $rights = $this->rights->getRights($collectionName, $featureIdentifier);
        return $rights[$action];
    }
    
    
}

