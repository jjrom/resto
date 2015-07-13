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
 * 
 * RESTo REST router for GET requests
 * 
 *    api/collections/search                        |  Search on all collections
 *    api/collections/{collection}/search           |  Search on {collection}
 *    api/collections/describe                      |  Opensearch service description at collections level
 *    api/collections/{collection}/describe         |  Opensearch service description for products on {collection}
 *    api/user/connect                              |  Connect and return a new valid connection token
 *    api/user/resetPassword                        |  Ask for password reset (i.e. reset link sent to user email adress)
 *    api/user/checkToken                           |  Check if token is valid
 *    api/user/activate                             |  Activate users with activation code
 *    
 *    collections                                   |  List all collections            
 *    collections/{collection}                      |  Get {collection} description
 *    collections/{collection}/{feature}            |  Get {feature} description within {collection}
 *    collections/{collection}/{feature}/download   |  Download {feature}
 *    collections/{collection}/{feature}/wms        |  Access WMS for {feature}
 *
 *    licenses                                      |  List all licenses
 *    licenses/{licenseid}                          |  Get {licenseid} license description 
 * 
 *    user                                          |  Show user information
 *    user/groups                                   |  Show user groups
 *    user/cart                                     |  Show user cart
 *    user/orders                                   |  Show orders for user
 *    user/orders/{orderid}                         |  Show {orderid} order for user
 *    user/rights                                   |  Show rights for user
 *    user/rights/{collection}                      |  Show rights for user on {collection}
 *    user/rights/{collection}/{feature}            |  Show rights for user on {feature} from {collection}
 *    user/signatures                               |  Show signatures for user
 *                          
 *    users                                         |  List all users profiles (only admin)
 * 
 */
class RestoRouteGET extends RestoRoute {

    /**
     * Constructor
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }

    /**
     * Process HTTP GET request
     *
     * @param array $segments
     *
     */
    public function route($segments) {
        switch ($segments[0]) {
            case 'api':
                return $this->GET_api($segments);
            case 'collections':
                return $this->GET_collections($segments);
            case 'users':
                return $this->GET_users($segments);
            case 'user':
                return $this->GET_user($segments);
            case 'licenses':
                return $this->GET_licenses($segments);
            default:
                return $this->processModuleRoute($segments);
        }
    }

    /**
     * 
     * Process HTTP GET request on api
     * 
     * @param array $segments
     */
    private function GET_api($segments) {


        if (!isset($segments[1]) || isset($segments[4])) {
            RestoLogUtil::httpError(404);
        }

        /*
         * api/collections
         */
        if ($segments[1] === 'collections' && isset($segments[2])) {
            return $this->GET_apiCollections($segments);
        }

        /*
         * api/user
         */
        else if ($segments[1] === 'user' && isset($segments[2])) {
            return $this->GET_apiUser($segments);
        }
        /*
         * Process module
         */
        else {
            return $this->processModuleRoute($segments);
        }
        
    }

    /**
     * Process api/collections
     * 
     * @param array $segments
     * @return type
     */
    private function GET_apiCollections($segments) {
        if ($segments[2] === 'search' || (isset($segments[3]) && $segments[3] === 'search')) {
            return $this->GET_apiCollectionsSearch(isset($segments[3]) ? $segments[2] : null);
        }
        else if ($segments[2] === 'describe' || (isset($segments[3]) && $segments[3] === 'describe')) {
            return $this->GET_apiCollectionsDescribe(isset($segments[3]) ? $segments[2] : null);
        }
        else {
            RestoLogUtil::httpError(404);
        }
    }

    /**
     * Process
     * 
     *    api/collections/search                        |  Search on all collections
     *    api/collections/{collection}/search           |  Search on {collection}
     *    
     * @param string $collectionName
     * @throws Exception
     */
    private function GET_apiCollectionsSearch($collectionName = null) {

        /*
         * Search in one collection...or in all collections
         */
        $resource = isset($collectionName) ? new RestoCollection($collectionName, $this->context, $this->user, array('autoload' => true)) : new RestoCollections($this->context, $this->user);
        $this->storeQuery('search', $this->user, isset($collectionName) ? $collectionName : '*', null);

        return $resource->search();
        
    }
    
    /**
     * Process 'describesearch' requests
     * 
     *    api/collections/describe                      |  Opensearch service description at collections level
     *    api/collections/{collection}/describe         |  Opensearch service description for products on {collection}s search in {collection}
     *    
     * @param string $collectionName
     * @throws Exception
     */
    private function GET_apiCollectionsDescribe($collectionName = null) {

        $resource = isset($collectionName) ? new RestoCollection($collectionName, $this->context, $this->user, array('autoload' => true)) : new RestoCollections($this->context, $this->user);
        $this->storeQuery('describe', $this->user, $collectionName, null);

        return $resource;
        
    }

    /**
     * Process api/user
     * 
     * @param array $segments
     * @return type
     */
    private function GET_apiUser($segments) {
       
        if (isset($segments[3])) {
            RestoLogUtil::httpError(404);
        }
        
        switch ($segments[2]) {

            /*
             * api/user/activate
             */
            case 'activate':
                return $this->GET_apiUserActivate();

            /*
             * api/user/connect
             */
            case 'connect':
                return $this->GET_apiUserConnect();

            /*
             * api/user/checkToken
             */
            case 'checkToken':
                return $this->GET_apiUserCheckToken();
                
            /*
             * api/user/resetPassword
             */
            case 'resetPassword':
                return $this->GET_apiUserResetPassword($segments);

            default:
                RestoLogUtil::httpError(404);

        }
        
    }
    
    /**
     * Process api/user/connect
     */
    private function GET_apiUserConnect() {
        if ($this->user->profile['userid'] !== -1 && $this->user->profile['activated'] === 1) {
            $this->user->token = $this->context->createToken($this->user->profile['userid'], $this->user->profile);
            return array(
                'token' => $this->user->token
            );
        }
        else {
            RestoLogUtil::httpError(403);
        }
    }

    /**
     * Process api/user/resetPassword
     */
    private function GET_apiUserResetPassword() {

        if (!isset($this->context->query['email'])) {
            RestoLogUtil::httpError(400);
        }

        /*
         * Only existing local user can change there password
         */
        if (!$this->context->dbDriver->check(RestoDatabaseDriver::USER, array('email' => $this->context->query['email'])) || $this->context->dbDriver->get(RestoDatabaseDriver::USER_PASSWORD, array('email' => $this->context->query['email'])) === str_repeat('*', 40)) {
            RestoLogUtil::httpError(3005);
        }

        /*
         * Send email with reset link
         */
        $shared = $this->context->dbDriver->get(RestoDatabaseDriver::SHARED_LINK, array(
            'email' => $this->context->query['email'],
            'resourceUrl' => $this->context->resetPasswordUrl . '/' . base64_encode($this->context->query['email']),
            'duration' => isset($this->context->sharedLinkDuration) ? $this->context->sharedLinkDuration : null
        ));
        $fallbackLanguage = isset($this->context->mail['resetPassword'][$this->context->dictionary->language]) ? $this->context->dictionary->language : 'en';
        if (!$this->sendMail(array(
                    'to' => $this->context->query['email'],
                    'senderName' => $this->context->mail['senderName'],
                    'senderEmail' => $this->context->mail['senderEmail'],
                    'subject' => $this->context->dictionary->translate($this->context->mail['resetPassword'][$fallbackLanguage]['subject'], $this->context->title),
                    'message' => $this->context->dictionary->translate($this->context->mail['resetPassword'][$fallbackLanguage]['message'], $this->context->title, $shared['resourceUrl'] . '?_tk=' . $shared['token'])
                ))) {
            RestoLogUtil::httpError(3003);
        }

        return RestoLogUtil::success('Reset link sent to ' . $this->context->query['email']);
    }

    /**
     * Process api/user/activate
     * 
     * @param string $userid
     */
    private function GET_apiUserActivate() {
        
        if (!isset($this->context->query['_emailorid'])) {
            RestoLogUtil::httpError(400);
        }
        
        /*
         * Get userid for user to be activated
         */
        $user = $this->getAuthorizedUser($this->context->query['_emailorid'], true);
        
        if (isset($this->context->query['act'])) {
            if ($this->context->dbDriver->execute(RestoDatabaseDriver::ACTIVATE_USER, array('userid' => $user->profile['userid'], 'activationCode' => $this->context->query['act']))) {

                /*
                 * Close database handler and redirect to a human readable page...
                 */
                if (isset($this->context->query['redirect'])) {
                    if (isset($this->context->dbDriver)) {
                        $this->context->dbDriver->closeDbh();
                    }
                    header('Location: ' . $this->context->query['redirect']);
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
        else {
            RestoLogUtil::httpError(400);
        }
    }

    /**
     * Process api/user/checkToken
     * 
     * Success if JWT is valid i.e.
     *  - signed by server
     *  - still in the validity period
     *  - has not been revoked 
     */
    private function GET_apiUserCheckToken() {
        
        if (isset($this->context->query['_tk'])) {
            try {
                
                $profile = json_decode(json_encode((array) $this->context->decodeJWT($this->context->query['_tk'])), true);
                
                /*
                 * Token is valid - i.e. signed by server and still in the validity period
                 * Check if it is not revoked
                 */
                if (isset($profile['data']['email']) && !$this->context->dbDriver->check(RestoDatabaseDriver::TOKEN_REVOKED, array('token' => $this->context->query['_tk']))) {
                    return RestoLogUtil::success('Valid token');
                }
                else {
                    return RestoLogUtil::error('Invalid token');
                }
                
            } catch (Exception $ex) {
                return RestoLogUtil::error('User not connected');
            }
        }
        else {
            RestoLogUtil::httpError(400);
        }
    }

    /**
     * 
     * Process HTTP GET request on collections
     * 
     * @param array $segments
     */
    private function GET_collections($segments) {

        if (isset($segments[1])) {
            $collection = new RestoCollection($segments[1], $this->context, $this->user, array('autoload' => true));
        }
        if (isset($segments[2])) {
            $feature = new RestoFeature($this->context, $this->user, array(
                'featureIdentifier' => $segments[2], 
                'collection' => $collection
            ));
            if (!$feature->isValid()) {
                RestoLogUtil::httpError(404);
            }
        }
        
        /*
         * collections
         */
        if (!isset($collection)) {
            return new RestoCollections($this->context, $this->user, array('autoload' => true));
        }

        /*
         * Collection description (XML is not allowed - see api/describe/collections)
         */
        else if (!isset($feature->identifier)) {
            return $collection;
        }

        /*
         * Feature description
         */
        else if (!isset($segments[3])) {
            $this->storeQuery('resource', $this->user, $collection->name, $feature->identifier);
            return $feature;
        }

        /*
         * Download feature then exit
         */
        else if ($segments[3] === 'download') {
            return $this->GET_featureDownload($collection, $feature);
        }
        
        /*
         * Access WMS for feature
         */
        else if ($segments[3] === 'wms') {
            return $this->GET_featureWMS($collection, $feature);
        }
        
        /*
         * 404
         */
        else {
            RestoLogUtil::httpError(404);
        }
    }

    /**
     * Download feature
     * 
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * 
     */
    private function GET_featureDownload($collection, $feature) {
        
        /*
         * Check user download rights
         */
        $user = $this->checkRights('download', $collection, $feature);
        
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
        $this->storeQuery('download', $user, $collection->name, $feature->identifier);
        $feature->download();
        return null;
        
    }
    
    /**
     * Access WMS for a given feature
     *
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * 
     */
    private function GET_featureWMS($collection, $feature) {
        
        /*
         * Check user visualize rights
         */
        $user = $this->checkRights('visualize', $collection, $feature);
        
        /*
         * User do not fullfill license requirements
         * Stream low resolution WMS if viewService is public
         * Forbidden otherwise
         */
        
        $wmsUtil = new RestoWMSUtil($this->context, $user);
        $license = $feature->getLicense();
        if (!$this->user->fulfillLicenseRequirements($license)) {
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
     * 
     * Process HTTP GET request on users
     * 
     * @param array $segments
     */
    private function GET_users($segments) {

        /*
         * users
         */
        if (!isset($segments[1])) {
            return $this->GET_usersProfiles();
        }
    
        return RestoLogUtil::httpError(404);
    }

    /**
     * 
     * Process HTTP GET request on users
     * 
     * @param array $segments
     */
    private function GET_user($segments) {
        
        $emailOrId = $this->getRequestedEmailOrId();
        
        /*
         * user
         */
        if (!isset($segments[1])) {
            return $this->GET_userProfile($emailOrId);
        }
    
        /*
         * user/groups
         */
        if ($segments[1] === 'groups') {
            if (isset($segments[2])) {
                return RestoLogUtil::httpError(404);
            }
            return $this->GET_userGroups($emailOrId);
        }

        /*
         * user/rights
         */
        if ($segments[1] === 'rights') {
            return $this->GET_userRights($emailOrId, isset($segments[2]) ? $segments[2] : null, isset($segments[3]) ? $segments[3] : null);
        }
        
        /*
         * user/cart
         */
        if ($segments[1] === 'cart') {
            return $this->GET_userCart($emailOrId, isset($segments[2]) ? $segments[2] : null);
        }
        
        /*
         * user/orders
         */
        if ($segments[1] === 'orders') {
            return $this->GET_userOrders($emailOrId, isset($segments[2]) ? $segments[2] : null);
        }

        /*
         * user/signatures
         */
        if ($segments[1] === 'signatures') {
            return $this->GET_userSignatures($emailOrId, isset($segments[2]) ? $segments[2] : null);
        }
        
        return RestoLogUtil::httpError(404);
    }

    /**
     * Process user     
     * 
     * @param string $emailOrId
     * @throws Exception
     */
    private function GET_userProfile($emailOrId) {

        /*
         * Profile can only be seen by its owner or by admin
         */
        $user = $this->getAuthorizedUser($emailOrId);

        return RestoLogUtil::success('Profile for ' . $user->profile['userid'], array(
            'profile' => $user->profile
        ));
    }

    /**
     * Process user/groups
     *
     * @param string $emailOrId
     * @throws Exception
     */
    private function GET_userGroups($emailOrId) {

        $user = $this->getAuthorizedUser($emailOrId, true);

        return RestoLogUtil::success('Groups for ' . $user->profile['userid'], array(
            'groups' => $user->profile['groups']
        ));
    }

    /**
     * Process user/rights
     * 
     * @param string $emailOrId
     * @param string $collectionName
     * @param string $featureIdentifier
     * @throws Exception
     */
    private function GET_userRights($emailOrId, $collectionName = null, $featureIdentifier = null) {

        /*
         * Rights can only be seen by its owner or by admin
         */
        $user = $this->getAuthorizedUser($emailOrId);

        return RestoLogUtil::success('Rights for ' . $user->profile['userid'], array(
                    'userid' => $user->profile['userid'],
                    'groups' => $user->profile['groups'],
                    'rights' => $user->getRights($collectionName, $featureIdentifier)
        ));
    }

    /**
     * Process user/signatures
     * 
     * @param string $emailOrId
     * @param string $licenseId
     * @throws Exception
     */
    private function GET_userSignatures($emailOrId, $licenseId = null) {
        
        /*
         * Not yet supported
         */
        if (isset($licenseId)) {
            return RestoLogUtil::httpError(404);
        }
        
        /*
         * Signatures can only be seen by its owner or by admin
         */
        $user = $this->getAuthorizedUser($emailOrId);
        
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

        return RestoLogUtil::success('Signatures for ' . $user->profile['userid'], array(
            'userid' => $user->profile['userid'],
            'groups' => $user->profile['groups'],
            'signatures' => $signatures
        ));
    }

    /**
     * Process user/cart
     *
     * @param string $emailOrId
     * @param string $itemid
     * @throws Exception
     */
    private function GET_userCart($emailOrId, $itemid = null) {

        if (isset($itemid)) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * Cart can only be seen by its owner or by admin
         */
        return $this->getAuthorizedUser($emailOrId)->getCart();
    }

    /**
     * Process user/orders
     *
     * @param string $emailOrId
     * @param string $orderid
     * @throws Exception
     */
    private function GET_userOrders($emailOrId, $orderid = null) {

        /*
         * Orders can only be seen by its owner or by admin
         */
        $user = $this->getAuthorizedUser($emailOrId);

        /*
         * Special case of metalink for single order
         */
        if (isset($orderid)) {
            return new RestoOrder($user, $this->context, $orderid);
        }
        else {
            return RestoLogUtil::success('Orders for user ' . $user->profile['userid'], array(
                'orders' => $user->getOrders()
            ));
        }
    }
    
    /**
     * Process users (only admin)
     * 
     * @throws Exception
     */
    private function GET_usersProfiles() {

        /*
         * Profiles can only be seen by admin users
         */
        if (!$this->user->isAdmin()) {
            RestoLogUtil::httpError(403);
        }

        return RestoLogUtil::success('Profiles for all users', array(
            'profiles' => $this->context->dbDriver->get(RestoDatabaseDriver::USERS_PROFILES)
        ));
    }

    /**
     *
     * Process licenses
     *
     * @param array $segments
     */
    private function GET_licenses($segments) {
        
        if (isset($segments[2])) {
            RestoLogUtil::httpError(404);
        }
        
        return array(
            'licences' => $this->context->dbDriver->get(RestoDatabaseDriver::LICENSES, array('licenseId' => isset($segments[1]) ? $segments[1] : null))
        );
    }
    
    /**
     * Check $action rights returning user
     * 
     * @param string $action
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * 
     */
    private function checkRights($action, $collection, $feature) {
        
        $user = $this->user;
        
        /*
         * Get token inititiator - bypass user rights
         */
        if (!empty($this->context->query['_tk'])) {
            $initiator = $this->context->dbDriver->check(RestoDatabaseDriver::SHARED_LINK, array(
                'resourceUrl' => $this->context->baseUrl . '/' . $this->context->path,
                'token' => $this->context->query['_tk']
            ));
            if ($initiator) {
                $user = $this->getAuthorizedUser($initiator, true);
            }
        }
        else {
            if ($action === 'download' && !$this->user->hasDownloadRights($collection->name, $feature->identifier)) {
                RestoLogUtil::httpError(403);
            }
            if ($action === 'visualize' && !$this->user->hasVisualizeRights($collection->name, $feature->identifier)) {
                RestoLogUtil::httpError(403);
            }
        }
        
        return $user;
    }
}
