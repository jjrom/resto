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
     * Current JWT token
     */
    public $token = null;
    
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
        if (!isset($profile) || !isset($profile['userid'])) {
            $this->profile = array(
                'userid' => -1,
                'groupname' => 'unregistered',
                'activated' => 0
            );
        }
        else {
            // Refresh user profile
            $this->profile = $this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('userid' => $profile['userid']));
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
        return $this->profile['activated'] === 0 ? $this->rights->groupRights['unregistered'] : $this->rights->getRights($collectionName, $featureIdentifier);
    }
    
    /**
     * Returns full rights for collection and/or identifier
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     */
    public function getFullRights($collectionName = null, $featureIdentifier = null) {
        return $this->profile['activated'] === 0 ? array('*' => $this->rights->groupRights['unregistered']) : $this->rights->getFullRights($collectionName, $featureIdentifier);
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
     * @param string $token
     * @return boolean
     */
    public function canVisualize($collectionName = null, $featureIdentifier = null, $token = null){
        return $this->canDownloadOrVisualize('visualize', $collectionName, $featureIdentifier, $token);
    }
    
    /**
     * Can User download ? 
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     * @param string $token
     * @return boolean
     */
    public function canDownload($collectionName = null, $featureIdentifier = null, $token = null){
        return $this->canDownloadOrVisualize('download', $collectionName, $featureIdentifier, $token);
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
     * @param array $collectionDescription
     */
    public function hasToSignLicense($collectionDescription) {
        
        /*
         * Collection has not license => never need to sign one
         */
        if (empty($collectionDescription['license'])) {
            return false;
        }
        
        /*
         * Unregistered user always have to sign
         */
        if (!isset($this->profile['email'])) {
            return true;
        }
        
        /*
         * Check in database
         */
        return !$this->context->dbDriver->check(RestoDatabaseDriver::LICENSE_SIGNED, array('email' => $this->profile['email'], 'collectionName' => $collectionDescription['name']));
        
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
     * Check if the user is habilitated to sign the product license {licenseId}
     * @param $licenseId
     * @return mixed
     */
    public function isHabilitedToSignProductLicense($licenseId) {
        return $this->context->dbDriver->check(RestoDatabaseDriver::PRODUCT_LICENSE_HABILITATION, array('email' => $this->profile['email'], 'license_id' =>  $licenseId));
    }

    /**
     * Check if the user is habilitated to sign the product license {licenseId}
     * @param $licenseId
     * @return mixed
     */
    public function isPublicVisibleWMS($licenseId) {
        $license = $this->context->dbDriver->get(RestoDatabaseDriver::PRODUCT_LICENSE, array('license_id' => $licenseId));
        return $license[0]['public_visibility_wms'];
    }

    /**
     * Check if user has to sign license for the product
     *
     * @param array $feature
     */
    public function hasToSignProductLicense($feature) {
        $arr = $feature->toArray();
        $licenseId = $arr['properties']['license'];

        /*
         * feature has not license => never need to sign one
         */
        if (!isset($licenseId)) {
            return false;
        }

        /*
         * Unregistered user always Forbidden : Need to be registered to sign a license
         */
        if (!isset($this->profile['email'])) {
            RestoLogUtil::httpError(403, 'Need to be registered to sign a license');
        }

        /**
         * Check if user is habilitated for this license
         */
        if (!$this->context->dbDriver->check(RestoDatabaseDriver::PRODUCT_LICENSE_HABILITATION, array('email' => $this->profile['email'], 'license_id' =>  $licenseId))) {
            RestoLogUtil::httpError(403, 'User not habilitated for this license. Please contact administrator.');
        }

        /**
         * Check if user is maximum signatures number for this license is not exceeded
         */
        if (!$this->context->dbDriver->check(RestoDatabaseDriver::PRODUCT_LICENSE_MAX_SIGNATURES, array('license_id' =>  $licenseId))) {
            RestoLogUtil::httpError(403, 'Maximum number of signatures for this license is reached. Please contact administrator.');
        }

        /*
         * Check in database
         */
        return !$this->context->dbDriver->check(RestoDatabaseDriver::PRODUCT_LICENSE_SIGNED, array('email' => $this->profile['email'], 'license_id' => $licenseId));

    }

    /**
     * Sign license for a product
     *
     * @param string $licenseId
     */
    public function signProductLicense($licenseId) {
        /**
         * Check if user is habilitated for this license
         */
        if (!$this->context->dbDriver->check(RestoDatabaseDriver::PRODUCT_LICENSE_HABILITATION, array('email' => $this->profile['email'], 'license_id' =>  $licenseId))) {
            RestoLogUtil::httpError(403, 'User not habilitated for this license. Please contact administrator.');
        }

        /**
         * Check if user is maximum signatures number for this license is not exceeded
         */
        if (!$this->context->dbDriver->check(RestoDatabaseDriver::PRODUCT_LICENSE_MAX_SIGNATURES, array('license_id' =>  $licenseId))) {
            RestoLogUtil::httpError(403, 'Maximum number of signatures for this license is reached. Please contact administrator.');
        }

        /**
         * Sign product license
         */
        if ($this->context->dbDriver->execute(RestoDatabaseDriver::SIGN_PRODUCT_LICENSE, array('email' => $this->profile['email'], 'license_id' =>  $licenseId))) {
            return true;
        }
        return false;
    }

    /**
     * Get user legal infos
     */
    public function getLegalInfo() {
        return $this->context->dbDriver->get(RestoDatabaseDriver::USER_LEGAL_INFO, array('email' => $this->profile['email']));
    }

    /**
     * Disconnect user
     */
    public function disconnect() {
        if (!$this->context->dbDriver->execute(RestoDatabaseDriver::DISCONNECT_USER, array('token' => $this->token))) {
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
     * Clear cart
     * 
     * @param boolean $synchronize
     */
    public function clearCart($synchronize = false) {
        return isset($this->cart) ? $this->cart->clear($synchronize) : false;
    }
    
    /**
     * Return user orders
     */
    public function getOrders() {
        return $this->context->dbDriver->get(RestoDatabaseDriver::ORDERS, array('email' => $this->profile['email']));
    }
    
    /**
     * Place order
     * 
     * @param array $data
     */
    public function placeOrder($data) {
        $fromCart = isset($this->context->query['_fromCart']) ? filter_var($this->context->query['_fromCart'], FILTER_VALIDATE_BOOLEAN) : false;
        if ($fromCart) {
            $order = $this->context->dbDriver->store(RestoDatabaseDriver::ORDER, array('email' => $this->profile['email']));
            if (isset($order) && isset($this->cart)) {
                $this->cart->clear();
            }
        }
        else {
            $order = $this->context->dbDriver->store(RestoDatabaseDriver::ORDER, array('email' => $this->profile['email'], 'items' => $data));
        }
        return $order;
    }
    
    /**
     * Can User download or visualize 
     * 
     * @param string $action
     * @param string $collectionName
     * @param string $featureIdentifier
     * @param string $token
     * @return boolean
     */
    private function canDownloadOrVisualize($action, $collectionName = null, $featureIdentifier = null, $token = null){
        
        /*
         * Token case - bypass user rights
         */
        if (isset($token)) {
            if (!isset($collectionName) || !isset($featureIdentifier)) {
                return false;
            }
            if ($this->context->dbDriver->check(RestoDatabaseDriver::SHARED_LINK, array('resourceUrl' => $this->context->baseUrl . '/' . $this->context->path, 'token' => $token))) {
                return true;
            }
        }
        
        /*
         * Normal case - checke user rights
         */
        $rights = $this->rights->getRights($collectionName, $featureIdentifier);
        return $rights[$action];
    }
    
    
}

