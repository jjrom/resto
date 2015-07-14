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
 * High level resto API called by RestoRoute* classes
 */
class RestoAPI {

    /*
     * resto context reference
     */
    private $context;
    
    /**
     * Constructor
     */
    public function __construct($context) {
        $this->context = $context;
    }

    /**
     * Search features
     * 
     * @param RestoUser $user
     * @param string $collectionName
     * @throws Exception
     */
    public function searchInCollection($user, $collectionName = null) {

        /*
         * Search in one collection...or in all collections
         */
        $resource = isset($collectionName) ? new RestoCollection($collectionName, $this->context, $user, array('autoload' => true)) : new RestoCollections($this->context, $user);
        
        /*
         * Store query
         */
        if ($this->context->storeQuery === true) {
            $user->storeQuery($this->context->method, 'search', isset($collectionName) ? $collectionName : '*', null, $this->context->query, $this->context->getUrl());
        }
        
        /*
         * Search
         */
        return $resource->search();
        
    }
    
    /**
     * Describe collection (i.e. display OpenSearch Description Document)
     * 
     * @param RestoUser $user
     * @param string $collectionName
     * @throws Exception
     */
    public function describeCollection($user, $collectionName = null) {

        /*
         * Store query
         */
        if ($this->context->storeQuery === true) {
            $user->storeQuery($this->context->method, 'describe', isset($collectionName) ? $collectionName : '*', null, $this->context->query, $this->context->getUrl());
        }
        
        return isset($collectionName) ? new RestoCollection($collectionName, $this->context, $user, array('autoload' => true)) : new RestoCollections($this->context, $user);
        
    }

    /**
     * Connect user
     * 
     * @param RestoUser $user
     */
    public function connectUser($user) {
        if ($user->profile['userid'] !== -1 && $user->profile['activated'] === 1) {
            $user->token = $this->context->createToken($user->profile['userid'], $user->profile);
            return array(
                'token' => $user->token
            );
        }
        else {
            RestoLogUtil::httpError(403);
        }
    }

    /**
     * Send reset password link to $email
     * 
     * @param string $email
     */
    public function sendResetPasswordLink($email) {

        if (!isset($email)) {
            RestoLogUtil::httpError(400);
        }

        /*
         * Only existing local user can change there password
         */
        if (!$this->context->dbDriver->check(RestoDatabaseDriver::USER, array('email' => $email)) || $this->context->dbDriver->get(RestoDatabaseDriver::USER_PASSWORD, array('email' => $email)) === str_repeat('*', 40)) {
            RestoLogUtil::httpError(3005);
        }

        /*
         * Send email with reset link
         */
        $shared = $this->context->dbDriver->get(RestoDatabaseDriver::SHARED_LINK, array(
            'email' => $email,
            'resourceUrl' => $this->context->resetPasswordUrl . '/' . base64_encode($email),
            'duration' => isset($this->context->sharedLinkDuration) ? $this->context->sharedLinkDuration : null
        ));
        $fallbackLanguage = isset($this->context->mail['resetPassword'][$this->context->dictionary->language]) ? $this->context->dictionary->language : 'en';
        if (!$this->sendMail(array(
                    'to' => $email,
                    'senderName' => $this->context->mail['senderName'],
                    'senderEmail' => $this->context->mail['senderEmail'],
                    'subject' => $this->context->dictionary->translate($this->context->mail['resetPassword'][$fallbackLanguage]['subject'], $this->context->title),
                    'message' => $this->context->dictionary->translate($this->context->mail['resetPassword'][$fallbackLanguage]['message'], $this->context->title, $shared['resourceUrl'] . '?_tk=' . $shared['token'])
                ))) {
            RestoLogUtil::httpError(3003);
        }

        return RestoLogUtil::success('Reset link sent to ' . $email);
    }

    /**
     * Activate user
     * 
     * @param string $userid
     * @param string $activationCode
     * @param string $redirectUrl
     * 
     */
    public function activateUser($userid, $activationCode, $redirectUrl) {
        
        if (!isset($userid) || !isset($activationCode)) {
            RestoLogUtil::httpError(400);
        }
        
        if ($this->context->dbDriver->execute(RestoDatabaseDriver::ACTIVATE_USER, array('userid' => $userid, 'activationCode' => $activationCode))) {

            /*
             * Close database handler and redirect to a human readable page...
             */
            if (isset($redirectUrl)) {
                if (isset($this->context->dbDriver)) {
                    $this->context->dbDriver->closeDbh();
                }
                header('Location: ' . $redirectUrl);
                exit();
            }
            /*
             * ...or return json stream otherwise
             */
            else {
                return RestoLogUtil::success('User activated');
            }
        }
        else {
            return RestoLogUtil::error('User not activated');
        }
        
    }

    /**
     * Check JWT token validity
     * 
     * Success if JWT is valid i.e.
     *  - signed by server
     *  - still in the validity period
     *  - has not been revoked 
     * 
     * @param string $token
     */
    public function checkJWT($token) {
        
        if (!isset($token)) {
            RestoLogUtil::httpError(400);
        }
        
        try {

            $profile = json_decode(json_encode((array) $this->context->decodeJWT($token)), true);

            /*
             * Token is valid - i.e. signed by server and still in the validity period
             * Check if it is not revoked
             */
            if (isset($profile['data']['email']) && !$this->context->dbDriver->check(RestoDatabaseDriver::TOKEN_REVOKED, array('token' => $token))) {
                return RestoLogUtil::success('Valid token');
            }
            else {
                return RestoLogUtil::error('Invalid token');
            }

        } catch (Exception $ex) {
            return RestoLogUtil::error('User not connected');
        }
        
    }

    /**
     * Download feature
     * 
     * @param RestoUser $user
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * @param String $token
     * 
     */
    public function downloadFeature($user, $collection, $feature, $token) {
        
        /*
         * Check user download rights
         */
        $user = $this->checkRights('download', $user, $token, $collection, $feature);
        
        /*
         * User do not fullfill license requirements
         */
        if (!$user->fulfillLicenseRequirements($feature->getLicense())) {
            RestoLogUtil::httpError(403, 'You do not fulfill license requirements');
        }
        
        /*
         * User has to sign the license before downloading
         */
        if ($user->hasToSignLicense($feature->getLicense())) {
            return array(
                'ErrorMessage' => 'Forbidden',
                'feature' => $feature->identifier,
                'collection' => $collection->name,
                'license' => $feature->getLicense(),
                'userid' => $user->profile['userid'],
                'ErrorCode' => 3002
            );
        }

        /*
         * Rights + fullfill license requirements + license signed = download and exit
         */
        if ($this->context->storeQuery === true) {
            $user->storeQuery($this->context->method, 'download',  $collection->name, $feature->identifier, $this->context->query, $this->context->getUrl());
        }
        $feature->download();
        return null;
        
    }
    
    /**
     * Access WMS for a given feature
     *
     * @param RestoUser $user
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * @param string $token
     * 
     */
    public function viewFeature($user, $collection, $feature, $token) {
        
        /*
         * Check user visualize rights
         */
        $user = $this->checkRights('visualize', $user, $token, $collection, $feature);
        
        /*
         * User do not fullfill license requirements
         * Stream low resolution WMS if viewService is public
         * Forbidden otherwise
         */
        
        $wmsUtil = new RestoWMSUtil($this->context, $user);
        $license = $feature->getLicense();
        if (!$user->fulfillLicenseRequirements($license)) {
            if ($license['viewService'] !== 'public') {
                RestoLogUtil::httpError(403, 'You do not fulfill license requirements');
            }
            else {
                $wmsUtil->streamWMS($feature, true);
            }
        }
        /*
         * Stream full resolution WMS
         */
        else {
            $wmsUtil->streamWMS($feature);
        }
        return null;
    }
    
    /**
     * Get users profiles
     * 
     * @throws Exception
     */
    public function getUsersProfiles() {
        return RestoLogUtil::success('Profiles for all users', array(
            'profiles' => $this->context->dbDriver->get(RestoDatabaseDriver::USERS_PROFILES)
        ));
    }

    /**
     * Return user profile    
     * 
     * @param RestoUser $user
     * @throws Exception
     */
    public function getUserProfile($user) {
        return RestoLogUtil::success('Profile for ' . $user->profile['email'], array(
            'profile' => $user->profile
        ));
    }

    /**
     * Return user groups
     *
     * @param RestoUser $user
     * @throws Exception
     */
    public function getUserGroups($user) {
        return RestoLogUtil::success('Groups for ' . $user->profile['email'], array(
            'email' => $user->profile['email'],
            'groups' => $user->profile['groups']
        ));
    }

    /**
     * Return user rights
     * 
     * @param RestoUser $user
     * @param string $collectionName
     * @param string $featureIdentifier
     * @throws Exception
     */
    public function getUserRights($user, $collectionName = null, $featureIdentifier = null) {
        return RestoLogUtil::success('Rights for ' . $user->profile['email'], array(
                    'email' => $user->profile['email'],
                    'userid' => $user->profile['userid'],
                    'groups' => $user->profile['groups'],
                    'rights' => $user->getRights($collectionName, $featureIdentifier)
        ));
    }

    /**
     * Return user signatures
     * 
     * @param RestoUser $user
     * @param string $licenseId
     * @throws Exception
     */
    public function getUserSignatures($user, $licenseId = null) {
        
        /*
         * Not yet supported
         */
        if (isset($licenseId)) {
            return RestoLogUtil::httpError(404);
        }
        
        /*
         * Get all licenses
         */
        $licenses = $this->context->dbDriver->get(RestoDatabaseDriver::LICENSES);
        
        /*
         * Get user signatures
         */
        $signed = $this->context->dbDriver->get(RestoDatabaseDriver::SIGNATURES, array(
            'email' => $user->profile['email']
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

        return RestoLogUtil::success('Signatures for ' . $user->profile['email'], array(
            'email' => $user->profile['email'],
            'userid' => $user->profile['userid'],
            'groups' => $user->profile['groups'],
            'signatures' => $signatures
        ));
    }

    /**
     * Return user cart
     *
     * @param RestoUser $user
     * @param string $itemid
     * @throws Exception
     */
    public function getUserCart($user, $itemid = null) {

        if (isset($itemid)) {
            RestoLogUtil::httpError(404);
        }
        
        return $user->getCart();
    }

    /**
     * Return user orders
     *
     * @param RestoUser $user
     * @param string $orderid
     * @throws Exception
     */
    public function getUserOrders($user, $orderid = null) {

        /*
         * Special case of metalink for single order
         */
        if (isset($orderid)) {
            return new RestoOrder($user, $this->context, $orderid);
        }
        else {
            return RestoLogUtil::success('Orders for user ' . $user->profile['email'], array(
                'email' => $user->profile['email'],
                'userid' => $user->profile['userid'],
                'orders' => $user->getOrders()
            ));
        }
    }
    
    /**
     * Check $action rights returning user
     * 
     * @param string $action
     * @param RestoUser $user
     * @param string $token
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * 
     */
    private function checkRights($action, $user, $token, $collection, $feature) {
        
        /*
         * Get token inititiator - bypass user rights
         */
        if (!empty($token)) {
            $initiatorEmail = $this->context->dbDriver->check(RestoDatabaseDriver::SHARED_LINK, array(
                'resourceUrl' => $this->context->baseUrl . '/' . $this->context->path,
                'token' => $token
            ));
            if ($initiatorEmail && $user->profile['email'] !== $initiatorEmail) {
                $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('email' => strtolower($initiatorEmail))), $this->context);
            }
        }
        else {
            if ($action === 'download' && !$user->hasDownloadRights($collection->name, $feature->identifier)) {
                RestoLogUtil::httpError(403);
            }
            if ($action === 'visualize' && !$user->hasVisualizeRights($collection->name, $feature->identifier)) {
                RestoLogUtil::httpError(403);
            }
        }
        
        return $user;
    }
    
    /**
     * Remove items from user cart
     * 
     * @param RestoUser $user
     * @param string $itemId
     */
    public function removeFromUserCart($user, $itemId = null) {
        
        /*
         * Clear all cart items
         */
        if (!isset($itemId)) {
            return $this->removeAllUserCartItems($user);
        }
        /*
         * Delete itemId only
         */
        else {
            return $this->removeUserCartItem($user, $itemId);
        }
     
    }
    
    /**
     * Remove one item from cart
     * 
     * @param RestoUser $user
     * @param string $itemId
     */
    private function removeUserCartItem($user, $itemId) {
        if ($user->getCart()->remove($itemId, true)) {
            return RestoLogUtil::success('Item removed from cart', array(
                'itemid' => $itemId
            ));
        }
        else {
            return RestoLogUtil::error('Item cannot be removed', array(
                'itemid' => $itemId
            ));
        }
    }
    
    /**
     * Remove all items within cart
     * 
     * @param RestoUser $user
     * @param string $itemId
     */
    private function removeAllUserCartItems($user) {
        if ($user->getCart()->clear(true)) {
            return RestoLogUtil::success('Cart cleared');
        }
        else {
            return RestoLogUtil::error('Cannot clear cart');
        }
    }
    
    /**
     * Sign license
     * 
     * @param RestoUser $user
     * @param string $licenseId
     * 
     * @return type
     */
    public function signLicense($user, $licenseId) {
        
        $licenses = $this->context->dbDriver->get(RestoDatabaseDriver::LICENSES, array(
            'licenseId' => $licenseId
        ));
        $license = isset($licenses[$licenseId]) ? $licenses[$licenseId] : null;
        if (!isset($license)) {
            RestoLogUtil::httpError(400, 'Non existing license : ' . $licenseId);
        }
        
        /*
         * User can sign license if it does not reach the signature quota
         */
        if ($user->signLicense($license)) {
            return RestoLogUtil::success('License signed', array(
                'email' => $user->profile['email'],
                'license' => $license
            ));
        }
        else {
            return RestoLogUtil::error('Cannot sign license');
        }
    }
    
    /**
     * Reset user password
     * 
     * @param string $email
     * @param string $password
     * @param string $url
     * 
     * @return type
     */
    public function resetUserPassword($email, $password, $url) {
        
        /*
         * Explod data['url'] into resourceUrl and queryString
         */
        $pair = explode('?', $url);
        if (!isset($pair[1])) {
            RestoLogUtil::httpError(403);
        }
        
        /*
         * Only initiator of reset password can change its email
         */
        $splittedUrl = explode('/', $pair[0]);
        if (strtolower(base64_decode($splittedUrl[count($splittedUrl) - 1])) !== $email) {
            RestoLogUtil::httpError(403);
        }
        
        $query = RestoUtil::queryStringToKvps($pair[1]);
        if (!isset($query['_tk']) || !$this->context->dbDriver->check(RestoDatabaseDriver::SHARED_LINK, array('resourceUrl' => $pair[0], 'token' => $query['_tk']))) {
            RestoLogUtil::httpError(403);
        }
        
        if ($this->context->dbDriver->get(RestoDatabaseDriver::USER_PASSWORD, array('email' => $email)) === str_repeat('*', 40)) {
            RestoLogUtil::httpError(3004);
        }
        
        if ($this->context->dbDriver->update(RestoDatabaseDriver::USER_PROFILE, array('profile' => array('email' => $email, 'password' => $password)))) {
            return RestoLogUtil::success('Password updated');
        }
        else {
            RestoLogUtil::httpError(400);
        }
        
    }
    
    /**
     * Create collection from input data
     * 
     * @param RestoUser $user
     * @param array $data
     * 
     */
    public function createCollection($user, $data) {
        
        if (!isset($data['name'])) {
            RestoLogUtil::httpError(400);
        }
        if ($this->context->dbDriver->check(RestoDatabaseDriver::COLLECTION, array('collectionName' => $data['name']))) {
            RestoLogUtil::httpError(2003);
        }
        $collection = new RestoCollection($data['name'], $this->context, $user);
        $collection->loadFromJSON($data, true);
        
        /*
         * Store query
         */
        if ($this->context->storeQuery === true) {
            $user->storeQuery($this->context->method, 'create', $data['name'], null, $this->context->query, $this->context->getUrl());
        }
        
        return RestoLogUtil::success('Collection ' . $data['name'] . ' created');
    }
    
    /**
     * Add feature to collection 
     * 
     * @param RestoUser $user
     * @param RestoCollection $collection
     * @param array $data
     * 
     */
    public function addFeatureToCollection($user, $collection, $data) {
        
        $feature = $collection->addFeature($data);
        
        /*
         * Store query
         */
        if ($this->context->storeQuery === true) {
            $user->storeQuery($this->context->method, 'insert', $collection->name, $feature->identifier, $this->context->query, $this->context->getUrl());
        }
        
        return RestoLogUtil::success('Feature ' . $feature->identifier . ' inserted within ' . $collection->name, array(
            'featureIdentifier' => $feature->identifier
        ));
    }
    
    /**
     * Add item to cart
     * 
     * @param RestoUser $user
     * @param array $data
     * @throws Exception
     */
    public function addToCart($user, $data, $clear) {
        
        /*
         * Remove items first
         */
        if ($clear) {
            $user->getCart()->clear(true);
        }
        $items = $user->getCart()->add($data, true);
        
        if ($items !== false) {
            return RestoLogUtil::success('Add items to cart', array(
                'items' => $items
            ));
        }
        else {
            return RestoLogUtil::error('Cannot add items to cart');
        }
        
    }
    
    /**
     * Place order 
     * 
     * @param RestoUser $user
     * @param array $data
     * @throws Exception
     */
    public function placeOrder($user, $data) {
        $order = $user->placeOrder($data);
        if ($order) {
            return RestoLogUtil::success('Place order', array(
                'order' => $order
            ));
        }
        else {
            return RestoLogUtil::error('Cannot place order');
        }
    }
    
    /**
     * Create license
     *
     * @param array $data
     */
    public function createLicense($data) {

        if (!isset($data['licenseId'])) {
            RestoLogUtil::httpError(400, 'license Identifier is not set');
        }

        $license = $this->context->dbDriver->store(RestoDatabaseDriver::LICENSE, array(
            'license' => array(
                'licenseId' => isset($data['licenseId']) ? $data['licenseId'] : null,
                'grantedCountries' => isset($data['grantedCountries']) ? $data['grantedCountries'] : null,
                'grantedOrganizationCountries' => isset($data['grantedOrganizationCountries']) ? $data['grantedOrganizationCountries'] : null,
                'grantedFlags' => isset($data['grantedFlags']) ? $data['grantedFlags'] : null,
                'viewService' => isset($data['viewService']) ? $data['viewService'] : null,
                'hasToBeSigned' => isset($data['hasToBeSigned']) ? $data['hasToBeSigned'] : null,
                'signatureQuota' => isset($data['signatureQuota']) ? $data['signatureQuota'] : -1,
                'description' => isset($data['description']) ? $data['description'] : null
            ))
        );
        if (!isset($license)) {
            RestoLogUtil::httpError(500, 'Database connection error');
        }

        return RestoLogUtil::success('license ' . $data['licenseId'] . ' created');
    }
    
    /**
     * Remove license
     *
     * @param array $licenseId
     */
    public function removeLicense($licenseId) {
        $this->context->dbDriver->remove(RestoDatabaseDriver::LICENSE, array('licenseId' => $licenseId));
        return RestoLogUtil::success('License removed', array(
            'licenseId' => $licenseId
        ));
    }
    
    /**
     * Create user
     * 
     * @param array $data
     */
    public function createUser($data) {
        
        if (!isset($data['email'])) {
            RestoLogUtil::httpError(400, 'Email is not set');
        }

        if ($this->context->dbDriver->check(RestoDatabaseDriver::USER, array('email' => $data['email']))) {
            RestoLogUtil::httpError(3000);
        }
        
        $redirect = isset($data['activateUrl']) ? '&redirect=' . rawurlencode($data['activateUrl']) : '';
        $userInfo = $this->context->dbDriver->store(RestoDatabaseDriver::USER_PROFILE, array(
            'profile' => array(
                'email' => $data['email'],
                'password' => isset($data['password']) ? $data['password'] : null,
                'username' => isset($data['username']) ? $data['username'] : null,
                'givenname' => isset($data['givenname']) ? $data['givenname'] : null,
                'lastname' => isset($data['lastname']) ? $data['lastname'] : null,
                'country' => isset($data['country']) ? $data['country'] : null,
                'organization' => isset($data['organization']) ? $data['organization'] : null,
                'flags' => isset($data['flags']) ? $data['flags'] : null,
                'topics' => isset($data['topics']) ? $data['topics'] : null,
                'activated' => 0
            ))
        );
        if (isset($userInfo)) {
            $activationLink = $this->context->baseUrl . '/api/users/' . $userInfo['userid'] . '/activate?act=' . $userInfo['activationcode'] . $redirect;
            $fallbackLanguage = isset($this->context->mail['accountActivation'][$this->context->dictionary->language]) ? $this->context->dictionary->language : 'en';
            if (!$this->sendMail(array(
                        'to' => $data['email'],
                        'senderName' => $this->context->mail['senderName'],
                        'senderEmail' => $this->context->mail['senderEmail'],
                        'subject' => $this->context->dictionary->translate($this->context->mail['accountActivation'][$fallbackLanguage]['subject'], $this->context->title),
                        'message' => $this->context->dictionary->translate($this->context->mail['accountActivation'][$fallbackLanguage]['message'], $this->context->title, $activationLink)
                    ))) {
                RestoLogUtil::httpError(3001);
            }
        }
        else {
            RestoLogUtil::httpError(500, 'Database connection error');
        }

        return RestoLogUtil::success('User ' . $data['email'] . ' created');
    }
    
    /**
     * Update user profile
     * 
     * @param RestoUser $user
     * @param array $data
     */
    public function updateUserProfile($user, $data) {
        
        /*
         * For normal user (i.e. non admin), some properties cannot be modified after validation
         */
        if (!$user->isAdmin()) {
            
            /*
             * Already validated => avoid updating administrative properties
             */
            if (isset($user->profile['validatedby'])) {
                unset($data['activated'],
                        $data['validatedby'],
                        $data['validationdate'],
                        $data['country'],
                        $data['organization'],
                        $data['organizationcountry'],
                        $data['flags']
                );
            }
            
            /*
             * These properties can only be changed by admin
             */
            unset($data['groups']);
        }

        /*
         * Update profile
         */
        $data['email'] = $user->profile['email'];
        $this->context->dbDriver->update(RestoDatabaseDriver::USER_PROFILE, array('profile' => $data));

        return RestoLogUtil::success('Update profile for user ' . $user->profile['email']);
    }
    
    /**
     * 
     * Update user cart item
     * 
     * @param string $user
     * @param string $itemId
     * @param array $data
     */
    public function updateCartItem($user, $itemId, $data) {
        
        /*
         * Cart can only be modified by its owner or by admin
         */
        if ($user->getCart()->update($itemId, $data, true)) {
            return RestoLogUtil::success('Item ' . $itemId . ' updated', array(
                'itemId' => $itemId,
                'item' => $data
            ));
        }
        else {
            return RestoLogUtil::error('Cannot update item ' . $itemId);
        }
        
    }
    
    /**
     * Add groups to user
     * 
     * @param RestoUser $user
     * @param string $groups
     * @return array
     * @throws Exception
     */
    public function addUserGroups($user, $groups) {
        return RestoLogUtil::success('Groups updated', array(
                'email' => $user->profile['email'],
                'groups' => $this->context->dbDriver->store(RestoDatabaseDriver::GROUPS, array(
                    'userid' => $user->profile['userid'],
                    'groups' => $groups
                ))
        ));
    }
    
    /**
     * Remove groups from user
     * 
     * @param RestoUser $user
     * @param string $groups
     * @return array
     * @throws Exception
     */
    public function removeUserGroups($user, $groups) {
        return RestoLogUtil::success('Groups updated', array(
                'email' => $user->profile['email'],
                'groups' => $this->context->dbDriver->remove(RestoDatabaseDriver::GROUPS, array(
                    'userid' => $user->profile['userid'],
                    'groups' => $groups
                ))
        ));
    }
    
    /**
     * Send user activation code by email
     * 
     * @param array $params
     */
    private function sendMail($params) {
        $headers = 'From: ' . $params['senderName'] . ' <' . $params['senderEmail'] . '>' . "\r\n";
        $headers .= 'Reply-To: doNotReply <' . $params['senderEmail'] . '>' . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        $headers .= 'X-Priority: 3' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        if (mail($params['to'], $params['subject'], $params['message'] , $headers, '-f' . $params['senderEmail'])) {
            return true;
        }
        return false;
    }


}
