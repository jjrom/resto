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
    
    const CREATE = 'create'; 
    const DOWNLOAD = 'download';
    const UPDATE = 'update';
    const VISUALIZE = 'visualize';
    
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
        'create' => 0
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
     * Return true if user is validated by admin - false otherwise
     * 
     * @return boolean
     */
    public function isValidated() {
        if ($this->profile['userid'] !== -1 && isset($this->profile['validatedby'])) {
            return true;
        }
        return false;
    }
    
    /**
     * Do user has rights to :
     *   - 'download' feature,
     *   - 'view' feature,
     *   - 'create' collection,
     *   - 'update' collection (i.e. add/delete feature and/or delete collection)
     * 
     * @param string $action
     * @param array $params
     * @return boolean 
     */
    public function hasRightsTo($action, $params = array()) {
        switch ($action) {
            case RestoUser::DOWNLOAD:
            case RestoUser::VISUALIZE:
                return $this->hasDownloadOrVisualizeRights($action, isset($params['collectionName']) ? $params['collectionName'] : null, isset($params['featureIdentifier']) ? $params['featureIdentifier'] : null);
            case RestoUser::CREATE:
                return $this->hasCreateRights();
            case RestoUser::UPDATE:
                if (isset($params['collection'])) {
                    return $this->hasUpdateRights($params['collection']);
                }
            default:
                break;
        }
        return false;
    }
    
    /**
     * Activate user
     * 
     * @param string $activationCode
     * @param string $redirectUrl
     * 
     */
    public function activate($activationCode = null) {
        if ($this->context->dbDriver->execute(RestoDatabaseDriver::ACTIVATE_USER, array('userid' => $this->profile['userid'], 'activationCode' => isset($activationCode) ? $activationCode : null, 'userAutoValidation' => $this->context->userAutoValidation))) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Connect user
     */
    public function connect() {
        if ($this->profile['userid'] !== -1 && $this->profile['activated'] === 1) {
            $this->token = $this->context->createJWT($this->profile['userid'], $this->profile);
            return array(
                'token' => $this->token
            );
        }
        else {
            RestoLogUtil::httpError(403);
        }
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
     * Set/update user rights
     * 
     * @param array $rights
     * @param string $collectionName
     * @param string $featureIdentifier
     * @throws Exception
     */
    public function setRights($rights, $collectionName = null, $featureIdentifier = null) {
        $this->context->dbDriver->store(RestoDatabaseDriver::RIGHTS, $this->getRightsArray($rights, $collectionName, $featureIdentifier));
        $this->rights = $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS, array('user' => $this));
        return true;
    }
    
    /**
     * Remove user rights
     * 
     * @param array $rights
     * @param string $collectionName
     * @param string $featureIdentifier
     * @throws Exception
     */
    public function removeRights($rights, $collectionName = null, $featureIdentifier = null) {
        $this->context->dbDriver->remove(RestoDatabaseDriver::RIGHTS, $this->getRightsArray($rights, $collectionName, $featureIdentifier));
        $this->rights = $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS, array('user' => $this));
        return true;
    }
    
    
    /**
     * Add groups to user
     * 
     * @param string $groups
     * @return array
     * @throws Exception
     */
    public function addGroups($groups) {
        return RestoLogUtil::success('Groups updated', array(
                'email' => $this->profile['email'],
                'groups' => $this->context->dbDriver->store(RestoDatabaseDriver::GROUPS, array(
                    'userid' => $this->profile['userid'],
                    'groups' => $groups
                ))
        ));
    }
    
    /**
     * Remove groups from user
     * 
     * @param string $groups
     * @return array
     * @throws Exception
     */
    public function removeGroups($groups) {
        return RestoLogUtil::success('Groups updated', array(
                'email' => $this->profile['email'],
                'groups' => $this->context->dbDriver->remove(RestoDatabaseDriver::GROUPS, array(
                    'userid' => $this->profile['userid'],
                    'groups' => $groups
                ))
        ));
    }
    
   /**
     * Return user signatures
     * 
     * @throws Exception
     */
    public function getSignatures() {
        
        /*
         * Get all licenses
         */
        $licenses = $this->context->dbDriver->get(RestoDatabaseDriver::LICENSES);
        
        /*
         * Get user signatures
         */
        $signed = $this->context->dbDriver->get(RestoDatabaseDriver::SIGNATURES, array(
            'email' => $this->profile['email']
        ));
        
        /*
         * Merge signatures with licences
         */
        $signatures = array();
        for ($i = count($signed); $i--;) {
            if (isset($licenses[$signed[$i]['licenseId']])) {
                $signatures[$signed[$i]['licenseId']] = array(
                    'lastSignatureDate' => $signed[$i]['lastSignatureDate'],
                    'counter' => $signed[$i]['counter'],
                    'license' => $licenses[$signed[$i]['licenseId']]
                );
            }
        }

        return $signatures;
        
    }
    
    /**
     * Sign license
     * 
     *  @param RestoLicense $license
     */
    public function signLicense($license) {
        
        /*
         * User can sign license if it does not reach the signature quota
         */
        if ($this->context->dbDriver->execute(RestoDatabaseDriver::SIGNATURE, array(
            'email' => $this->profile['email'],
            'licenseId' => $license['licenseId'],
            'signatureQuota' => $license['signatureQuota']
        ))) {
            return RestoLogUtil::success('License signed', array(
                'email' => $this->profile['email'],
                'license' => $license
            ));
        }
        else {
            return RestoLogUtil::error('Cannot sign license');
        }
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
     * Send reset password link to user email adress
     * 
     */
    public function sendResetPasswordLink() {

        /*
         * Only existing local user can change there password
         */
        if (!$this->context->dbDriver->check(RestoDatabaseDriver::USER, array('email' => $this->profile['email'])) || $this->context->dbDriver->get(RestoDatabaseDriver::USER_PASSWORD, array('email' => $this->profile['email'])) === str_repeat('*', 40)) {
            RestoLogUtil::httpError(3005);
        }

        /*
         * Send email with reset link
         */
        $shared = $this->context->dbDriver->get(RestoDatabaseDriver::SHARED_LINK, array(
            'email' => $this->profile['email'],
            'resourceUrl' => $this->context->resetPasswordUrl . '/' . base64_encode($this->profile['email']),
            'duration' => isset($this->context->sharedLinkDuration) ? $this->context->sharedLinkDuration : null
        ));
        $fallbackLanguage = isset($this->context->mail['resetPassword'][$this->context->dictionary->language]) ? $this->context->dictionary->language : 'en';
        if (!RestoUtil::sendMail(array(
                    'to' => $this->profile['email'],
                    'senderName' => $this->context->mail['senderName'],
                    'senderEmail' => $this->context->mail['senderEmail'],
                    'subject' => $this->context->dictionary->translate($this->context->mail['resetPassword'][$fallbackLanguage]['subject'], $this->context->title),
                    'message' => $this->context->dictionary->translate($this->context->mail['resetPassword'][$fallbackLanguage]['message'], $this->context->title, $shared['resourceUrl'] . '?_tk=' . $shared['token'])
                ))) {
            RestoLogUtil::httpError(3003);
        }

        return RestoLogUtil::success('Reset link sent to ' . $this->profile['email']);
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
    private function hasDownloadOrVisualizeRights($action, $collectionName, $featureIdentifier = null){
        $rights = $this->getRights($collectionName, $featureIdentifier);
        return $rights[$action];
    }
    
    /**
     * Can user create collection ?
     * 
     * @return boolean
     */
    private function hasCreateRights() {
        $rights = $this->getRights();
        return isset($rights['collections']['*']) ? $rights['collections']['*']['create'] : 0;
    }
    
    /**
     * A user can update a collection if he is the owner of the collection
     * or if he is an admin
     * 
     * @param RestoCollection $collection
     * @return boolean
     */
    private function hasUpdateRights($collection) {
        
        if (!$this->hasCreateRights()) {
            return false;
        }
        /*
         * Only collection owner and admin can update the collection
         */
        else if (!$this->isAdmin() && $collection->owner !== $this->profile['email']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Return rights array for add/update/delete
     * 
     * @param array $rights
     * @param string $collectionName
     * @param string $featureIdentifier
     * 
     * @return string
     */
    private function getRightsArray($rights, $collectionName = null, $featureIdentifier = null) {
        
        /*
         * Default target is all collections
         */
        $target = '*';
        
        /*
         * Check that collection/feature exists
         */
        if (isset($collectionName)) {
            if (!$this->context->dbDriver->check(RestoDatabaseDriver::COLLECTION, array('collectionName' => $collectionName))) {
                RestoLogUtil::httpError(404, 'Collection does not exist');
            }
            $target = $collectionName;
        }
        if (isset($featureIdentifier)) {
            if (!$this->context->dbDriver->check(RestoDatabaseDriver::FEATURE, array('featureIdentifier' => $featureIdentifier))) {
                RestoLogUtil::httpError(404, 'Feature does not exist');
            }
            $target = $featureIdentifier;
        }
        
        return array(
            'rights' => $rights,
            'ownerType' => 'user',
            'owner' => $this->profile['email'],
            'targetType' => isset($featureIdentifier) ? 'feature' : 'collection',
            'target' => $target
        );
        
    }
    
}

