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
 * RESTo REST router for GET requests
 */
class RestoRouteGET extends RestoRoute {

    /**
     * Constructor
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }

    /**
     * 
     * Process HTTP GET request
     * 
     *    api/collections/search                        |  Search on all collections
     *    api/collections/{collection}/search           |  Search on {collection}
     *    api/collections/describe                      |  Opensearch service description at collections level
     *    api/collections/{collection}/describe         |  Opensearch service description for products on {collection}
     *    api/users/connect                             |  Connect and return a new valid connection token
     *    api/users/resetPassword                       |  Ask for password reset (i.e. reset link sent to user email adress)
     *    api/users/checkToken                          |  Check if token is valid
     *    api/users/{userid}/activate                   |  Activate users with activation code
     *    
     *    collections                                   |  List all collections            
     *    collections/{collection}                      |  Get {collection} description
     *    collections/{collection}/{feature}            |  Get {feature} description within {collection}
     *    collections/{collection}/{feature}/download   |  Download {feature}
     * 
     *    users                                         |  List all users
     *    users/{userid}                                |  Show {userid} information
     *    users/{userid}/cart                           |  Show {userid} cart
     *    users/{userid}/orders                         |  Show orders for {userid}
     *    users/{userid}/orders/{orderid}               |  Show {orderid} order for {userid}
     *    users/{userid}/rights                         |  Show rights for {userid}
     *    users/{userid}/rights/{collection}            |  Show rights for {userid} on {collection}
     *    users/{userid}/rights/{collection}/{feature}  |  Show rights for {userid} on {feature} from {collection}
     *    users/{userid}/signatures                     |  Show signatures for {userid}
     *    users/{userid}/signatures/{collection}        |  Show signatures for {userid} on {collection}
     * 
     * Note: {userid} can be replaced by base64(email) 
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
         * api/users
         */
        else if ($segments[1] === 'users' && isset($segments[2])) {
            return $this->GET_apiUsers($segments);
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
        $this->storeQuery('search', isset($collectionName) ? $collectionName : '*', null);

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
        $this->storeQuery('describe', $collectionName, null);

        return $resource;
        
    }

    /**
     * Process api/users
     * 
     * @param array $segments
     * @return type
     */
    private function GET_apiUsers($segments) {
       
        if (!isset($segments[3])) {
            return $this->GET_apiUsersAll($segments);
        }
        
        if (!isset($segments[4])) {
            return $this->GET_apiUsersUserid($segments);
        }
        
    }
    
    /**
     * Process api/users
     * 
     * @param array $segments
     * @return type
     */
    private function GET_apiUsersAll($segments) {
        
        switch ($segments[2]) {

            /*
             * api/users/connect
             */
            case 'connect':
                return $this->GET_apiUsersConnect();

            /*
             * api/users/checkToken
             */
            case 'checkToken':
                return $this->GET_apiUsersCheckToken();
                
            /*
             * api/users/resetPassword
             */
            case 'resetPassword':
                return $this->GET_apiUsersResetPassword($segments);

            default:
                RestoLogUtil::httpError(403);

        }

    }
    
    /**
     * Process api/users/{userid}
     * 
     * @param array $segments
     * @return type
     */
    private function GET_apiUsersUserid($segments) {
        
        switch ($segments[3]) {
                
            /*
             * api/users/{userid}/activate
             */
            case 'activate':
                return $this->GET_apiUsersActivate($segments[2]);

            default:
                RestoLogUtil::httpError(403);

        }
        
    }
    
    /**
     * Process api/users/connect
     */
    private function GET_apiUsersConnect() {
        if (isset($this->user->profile['email']) && $this->user->profile['activated'] === 1) {
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
     * Process api/users/resetPassword
     */
    private function GET_apiUsersResetPassword() {

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
        $shared = $this->context->dbDriver->get(RestoDatabaseDriver::SHARED_LINK, array('resourceUrl' => $this->context->resetPasswordUrl . '/' . base64_encode($this->context->query['email'])));
        if (!$this->sendMail(array(
                    'to' => $this->context->query['email'],
                    'senderName' => $this->context->mail['senderName'],
                    'senderEmail' => $this->context->mail['senderEmail'],
                    'subject' => $this->context->dictionary->translate('resetPasswordSubject', $this->context->title),
                    'message' => $this->context->dictionary->translate('resetPasswordMessage', $this->context->title, $shared['resourceUrl'] . '?_tk=' . $shared['token'])
                ))) {
            RestoLogUtil::httpError(3003);
        }

        return RestoLogUtil::success('Reset link sent to ' . $this->context->query['email']);
    }

    /**
     * Process api/users/{userid}/activate
     * 
     * @param string $userid
     */
    private function GET_apiUsersActivate($userid) {
        if (isset($this->context->query['act'])) {
            if ($this->context->dbDriver->execute(RestoDatabaseDriver::ACTIVATE_USER, array('userid' => $userid, 'activationCode' => $this->context->query['act']))) {

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
     * Process api/users/checkToken
     * 
     * Success if JWT is valid i.e.
     *  - signed by server
     *  - still in the validity period
     *  - has not been revoked 
     */
    private function GET_apiUsersCheckToken() {
        
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
            $this->storeQuery('resource', $collection->name, $feature->identifier);
            return $feature;
        }

        /*
         * Download feature then exit
         */
        else if ($segments[3] === 'download') {
            return $this->GET_featureDownload($collection, $feature);
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
     * @return type
     */
    private function GET_featureDownload($collection, $feature) {
        
        /*
         * User do not have right to download product
         */
        if (!$this->user->canDownload($collection->name, $feature->identifier, !empty($this->context->query['_tk']) ? $this->context->query['_tk'] : null)) {
            RestoLogUtil::httpError(403);
        }
        /*
         * Or user has rigth but hasn't sign the license yet
         */
        else if ($this->user->hasToSignLicense($collection->toArray(false)) && empty($this->context->query['_tk'])) {
            return array(
                'ErrorMessage' => 'Forbidden',
                'collection' => $collection->name,
                'license' => $collection->getLicense(),
                'ErrorCode' => 3002
            );
        }
        /*
         * Rights + license signed = download and exit
         */
        else {
            $this->storeQuery('download', $collection->name, $feature->identifier);
            $feature->download();
            return null;
        }
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
            RestoLogUtil::httpError(501);
        }
    
        /*
         * users/{userid}
         */
        if (!isset($segments[2])) {
            return $this->GET_userProfile($segments[1]);
        }
        
        /*
         * users/{userid}/rights
         */
        if ($segments[2] === 'rights') {
            return $this->GET_userRights($segments[1], isset($segments[3]) ? $segments[3] : null, isset($segments[4]) ? $segments[4] : null);
        }
        
        /*
         * users/{userid}/cart
         */
        if ($segments[2] === 'cart') {
            return $this->GET_userCart($segments[1], isset($segments[3]) ? $segments[3] : null);
        }
        
        /*
         * users/{userid}/orders
         */
        if ($segments[2] === 'orders') {
            return $this->GET_userOrders($segments[1], isset($segments[3]) ? $segments[3] : null);
        }

        /*
         * users/{userid}/orders
         */
        if ($segments[2] === 'signatures') {
            return $this->GET_userSignatures($segments[1], isset($segments[3]) ? $segments[3] : null);
        }
        
        return RestoLogUtil::httpError(404);
    }

    /**
     * Process users/{userid}     
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
     * Process HTTP GET request on user rights
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
                    'groupname' => $user->profile['groupname'],
                    'rights' => $user->getFullRights($collectionName, $featureIdentifier)
        ));
    }

    /**
     * Process HTTP GET request on user signatures
     * 
     * @param string $emailOrId
     * @param string $collectionName
     * @throws Exception
     */
    private function GET_userSignatures($emailOrId, $collectionName = null) {
        
        /*
         * Rights can only be seen by its owner or by admin
         */
        $user = $this->getAuthorizedUser($emailOrId);
        $signatures = array();
        
        /*
         * Get collections
         */
        $collectionsDescriptions = $this->context->dbDriver->get(RestoDatabaseDriver::COLLECTIONS_DESCRIPTIONS);
        
        /*
         *  Get rights for collections
         */
        if (!isset($collectionName)) {
            foreach ($collectionsDescriptions as $collectionDescription) {
                $signatures[$collectionDescription['name']] = array(
                    'hasToSignLicense' => $user->hasToSignLicense($collectionDescription),
                    'licenseUrl' =>  $this->getLicenseUrl($collectionDescription)
                );
            }
        }
        else {
            $signatures[$collectionName] = array(
                'hasToSignLicense' => $user->hasToSignLicense($collectionsDescriptions[$collectionName]),
                'licenseUrl' => $this->getLicenseUrl($collectionsDescriptions[$collectionName])
            );
        }

        return RestoLogUtil::success('Signatures for ' . $user->profile['userid'], array(
            'userid' => $user->profile['userid'],
            'groupname' => $user->profile['groupname'],
            'signatures' => $signatures
        ));
    }

    /**
     * Process HTTP GET request on user cart
     *
     * @param string $emailOrId
     * @param string $itemid
     * @throws Exception
     */
    private function GET_userCart($emailOrId, $itemid = null) {

        /*
         * Cart can only be seen by its owner or by admin
         */
        $user = $this->getAuthorizedUser($emailOrId);

        if (isset($itemid)) {
            RestoLogUtil::httpError(404);
        }

        return $user->getCart();
    }

    /**
     * Process HTTP GET request on user orders
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
     * Return license url in the curent language
     * 
     * @param array $collectionDescription
     * @return string
     */
    private function getLicenseUrl($collectionDescription) {
        if (!empty($collectionDescription['license'])) {
            return isset($collectionDescription['license'][$this->context->dictionary->language]) ? $collectionDescription['license'][$this->context->dictionary->language] : $collectionDescription['license']['en'];
        }
        
        return null;
            
    }

}
