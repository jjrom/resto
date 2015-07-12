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
     * Reference to cart object
     */
    private $cart;
    
    /*
     * Reference to rights object
     */
    private $rights;
    
    /*
     * Fallback rights if no collection is found
     */
    private $fallbackRights = array(
        'download' => 0,
        'visualize' => 0,
        'post' => 0,
        'put' => 0,
        'delete' => 0
    );
    
    /**
     * Constructor
     * 
     * @param array $profile : User profile
     * @param RestoContext $context
     */
    public function __construct($profile, $context) {
        
        $this->context = $context;
        
        /*
         * Set profile
         */
        $this->profile = (!isset($profile) || !isset($profile['userid'])) ? array(
            'userid' => -1,
            'email' => 'unregistered',
            'groups' => 'default',
            'activated' => 0
                ) : $profile;

        /*
         * Set cart
         */
        $this->cart = new RestoCart($this, true);
        
    }
    
    /**
     * Return true if user has administration rights
     * 
     * @return boolean
     */
    public function isAdmin() {
        $exploded = explode(',', $this->profile['groups']);
        for ($i = count($exploded);$i--;) {
            if (trim($exploded[$i]) === 'admin') {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Return user cart
     */
    public function getCart() {
        return $this->cart;
    }
    
    /**
     * Return user orders
     */
    public function getOrders() {
        return $this->context->dbDriver->get(RestoDatabaseDriver::ORDERS, array('email' => $this->profile['email']));
    }
    
    /**
     * Returns rights
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     */
    public function getRights($collectionName = null, $featureIdentifier = null) {
        
        /*
         * Compute rights if they are not already set
         */
        if (!isset($this->rights)) {
            $this->rights = $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS, array('user' => $this));
        }
        
        /*
         * Return specific rights for feature
         */
        if (isset($collectionName) && isset($featureIdentifier)) {
            if (isset($this->rights['features'][$featureIdentifier])) {
                return $this->rights['features'][$featureIdentifier];
            }
            return $this->getRights($collectionName);
        }
        
        /*
         * Return specific rights for collection
         */
        if (isset($collectionName)) {
            return isset($this->rights['collections'][$collectionName]) ? $this->rights['collections'][$collectionName] : (isset($this->rights['collections']['*']) ? $this->rights['collections']['*'] : $this->fallbackRights);
        }
        
        /*
         * Return rights for all collections/features
         */
        return $this->rights;
    }
    
    /**
     * Can User download ? 
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     * @return boolean
     */
    public function hasDownloadRights($collectionName = null, $featureIdentifier = null){
        return $this->hasDownloadOrVisualizeRights('download', $collectionName, $featureIdentifier);
    }
    
    /**
     * Can User visualize ?
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     * @return boolean
     */
    public function hasVisualizeRights($collectionName = null, $featureIdentifier = null){
        return $this->hasDownloadOrVisualizeRights('visualize', $collectionName, $featureIdentifier);
    }
    
    /**
     * Can User POST ?
     * 
     * @param string $collectionName
     * @return boolean
     */
    public function hasPOSTRights($collectionName = null){
        $rights = $this->getRights($collectionName);
        return $rights['post'];
        
    }
    
    /**
     * Can User PUT ?
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     * @return boolean
     */
    public function hasPUTRights($collectionName, $featureIdentifier = null){
        $rights = $this->getRights($collectionName, $featureIdentifier);
        return $rights['put'];
    }
    
    /**
     * Can User DELETE ?
     * 
     * @param string $collectionName
     * @param string $featureIdentifier
     * @return boolean
     */
    public function hasDELETERights($collectionName, $featureIdentifier = null){
        $rights = $this->getRights($collectionName, $featureIdentifier);
        return $rights['delete'];
    }
    
    /**
     * Check if user fulfill license requirements 
     * 
     * To be fulfilled, the user profile :
     *  - should be validated
     *  - should match at least one of the granteFlags of the license
     *  - should match at least one of the grantedCountries or the grantedOrganizationCountries of the license
     * 
     * @param array $license
     */
    public function fulfillLicenseRequirements($license) {
        
        /*
         * Always be pessimistic :)
         */
        $fulfill = false;
        
        /**
         * No license restriction (e.g. 'unlicensed' license)
         * => Every user fulfill license requirements
         */
        if (!isset($license['grantedCountries']) && !isset($license['grantedOrganizationCountries']) && !isset($license['grantedFlags'])) {
            return true;
        }

        /**
         * Registered user profile should be validated
         */
        if ($this->profile['userid'] !== -1 && !isset($this->profile['validatedby'])) {
            RestoLogUtil::httpError(403, 'User profile has not been validated. Please contact an administrator');
        }

        /**
         * User profile should match at least one of the license granted flags 
         */
        if (isset($license['grantedFlags']))  {
           
            /*
             * Registered user has automatically the REGISTERED flag
             * (see 'unlicensedwithregistration' license)
             */
            $userFlags = !empty($this->profile['flags']) ? array_map('trim', explode(',', $this->profile['flags'])) : array();
            if ($this->profile['userid'] !== -1) {
                $userFlags[] = 'REGISTERED';
            }
            
            /*
             * No match => no fulfill
             */
            if (!$this->matches($userFlags, array_map('trim', explode(',', $license['grantedFlags'])))) {
                return false;
            }
            
        }
        
        /**
         * User profile should match either one of the license granted countries or organization countries
         */
        if (isset($license['grantedCountries']) && isset($this->profile['country']))  {
            $fulfill = $fulfill || $this->matches(array_map('trim', explode(',', $this->profile['country'])), array_map('trim', explode(',', $license['grantedCountries'])));
        }
        if (isset($license['grantedOrganizationCountries']) && isset($this->profile['organizationcountry']))  {
            $fulfill = $fulfill || $this->matches(array_map('trim', explode(',', $this->profile['organizationcountry'])), array_map('trim', explode(',', $license['grantedOrganizationCountries'])));
        }
        
        return $fulfill;
    }
    
    /**
     * Check if user has to sign license
     * 
     * @param array $license
     */
    public function hasToSignLicense($license) {
        
        /*
         * No need to sign for 'never' 
         */
        if ($license['hasToBeSigned'] === 'never') {
            return false;
        }
        
        /*
         * Always need to sign for 'always'
         */
        if ($license['hasToBeSigned'] === 'always') {
            return true;
        }
        
        /*
         * Otherwise check if license has been signed once
         */
        return !$this->context->dbDriver->check(RestoDatabaseDriver::SIGNATURE, array(
            'email' => $this->profile['email'],
            'licenseId' => $license['licenseId']
        ));
        
    }
    
    /**
     * Sign license
     * 
     *  @param array $license
     */
    public function signLicense($license) {
        return $this->context->dbDriver->execute(RestoDatabaseDriver::SIGNATURE, array(
            'email' => $this->profile['email'],
            'licenseId' => $license['licenseId'],
            'signatureQuota' => $license['signatureQuota']
        ));
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
                'email' => $this->profile['email'],
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
     * Can User download or visualize 
     * 
     * @param string $action
     * @param string $collectionName
     * @param string $featureIdentifier
     * @return boolean
     */
    private function hasDownloadOrVisualizeRights($action, $collectionName = null, $featureIdentifier = null){
        $rights = $this->getRights($collectionName, $featureIdentifier);
        return $rights[$action];
    }
    
    /**
     * Return true if there is at least one match between user and license grant
     * 
     * @param array $userGrant
     * @param array $licenseGrant
     * @return type
     */
    private function matches($userGrant, $licenseGrant) {
        $match = false;
        foreach (array_values($userGrant) as $grant) {
            $match = $match || (array_search($grant, $licenseGrant) !== false);
        }
        return $match;
        
    }
    
}

