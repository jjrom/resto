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
     *    api/users/lp_signatures                       |  Show signatures on licenses products for current user
     *    api/users/legal                               |  Show legal informations for all users (only admin)
     *    api/users/{userid}/activate                   |  Activate users with activation code
     *    
     *    collections                                   |  List all collections            
     *    collections/{collection}                      |  Get {collection} description
     *    collections/{collection}/{feature}            |  Get {feature} description within {collection}
     *    collections/{collection}/{feature}/download   |  Download {feature}
     *    collections/{collection}/{feature}/wms        |  Access WMS {feature}
     *
     *    users                                         |  List all users
     *    users/{userid}                                |  Show {userid} information
     *    users/{userid}/legal                          |  Show {userid} legal informations
     *    users/{userid}/grantedvisibility              |  Show {userid} granted visibility (only admin)
     *    users/{userid}/cart                           |  Show {userid} cart
     *    users/{userid}/orders                         |  Show orders for {userid}
     *    users/{userid}/orders/{orderid}               |  Show {orderid} order for {userid}
     *    users/{userid}/rights                         |  Show rights for {userid}
     *    users/{userid}/rights/{collection}            |  Show rights for {userid} on {collection}
     *    users/{userid}/rights/{collection}/{feature}  |  Show rights for {userid} on {feature} from {collection}
     *    users/{userid}/signatures                     |  Show signatures for {userid}
     *    users/{userid}/signatures/{collection}        |  Show signatures for {userid} on {collection}
     *
     *    licenses                                      |  List all licenses
     *    licenses/{licenseid}                          |  Get {licenseid} license description
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

        $results =  $resource->search();

        // Perform post processing on found features
        $this->apiCollectionsSearchPostProcessing($results->restoFeatures);

        return $results;
    }
    /**
     * @param $restoFeatures
     */
    private function apiCollectionsSearchPostProcessing($restoFeatures) {

        // Retrieve all the licenses signed by the user
        if (isset($this->user->profile['email'])) {
            $signatures = $this->context->dbDriver->get(RestoDatabaseDriver::PRODUCT_LICENSE_SIGNED, array('email' => $this->user->profile['email']));
        }

        // Retreive all the licenses defined in the database
        $licenses = $this->context->dbDriver->get(RestoDatabaseDriver::PRODUCT_LICENSE, array());

        // Iterate on each returned feature
        $count = count($restoFeatures);
        for ($i=0; $i<$count; $i++) {

            // Get a reference on the current feature
            $feature = &$restoFeatures[$i]->featureArray;

            // If the current feature has a license, just add the licenseInfo
            if (isset($feature['properties']['license'])) {
                $licenseId = $feature['properties']['license'];
                $license = $this->findLicenseById($licenses,$licenseId);

                // Add licenseInfo
                $properties = &$feature['properties'];
                if (isset($license['license_info'])) {
                    $properties['license_info'] = $license['license_info'];
                }

                // If the user has signed some licenses, try to add signature date if applicable.
                if (isset($signatures)) {
                    $sigDate = $this->findSignatureDateByLicenseId($signatures, $licenseId);
                    if ($sigDate !== null) {
                        $properties['license_signature_date'] = $sigDate;
                    }
                }
            }
        }
    }

    /**
     * @param $licenses
     * @param $licenseId
     */
    private function findLicenseById($licenses, $licenseId) {
        foreach (array_values($licenses) as $license) {
            if ($license['license_id'] == $licenseId) {
                return $license;
            }
        }
        return null;
    }

    private function findSignatureDateByLicenseId($signatures, $licenseId) {
        foreach (array_values($signatures) as $signature) {
            if ($signature['license_id'] == $licenseId) {
                return $signature['signature_date'];
            }
        }
        return null;
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

        /*
         * api/users/lp_signatures
         */
        if ($segments[2] === 'lp_signatures' && !isset($segments[3])) {
            return $this->GET_userLicensesProductSignatures();
        }

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
             * api/users/legal
             */
            case 'legal':
                return $this->GET_AllLegalInfo();

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
         * Access WMS then exit
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
     * Access WMS for a given feature
     *
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * @return type
     */
    private function GET_featureWMS($collection, $feature) {

        $wmsInfo = $this->context->dbDriver->get(RestoDatabaseDriver::WMS_INFORMATION, array(
            'featureIdentifier' => $feature->identifier,
            'collection' => isset($collection) ? $collection : null
        ));

        if (!isset($wmsInfo['wms'])) {
            RestoLogUtil::httpError(400);
        }

        $util = new RestoWMSUtil();
        $util->proxifyWMS($wmsInfo['wms'], $wmsInfo['license'], $this->user, $this->context);
        return null;
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
         * Or user has rigth but hasn't sign the license yet
         */
        else if ($this->user->hasToSignProductLicense($feature) && empty($this->context->query['_tk'])) {
            $arr = $feature->toArray();
            $featureLicense = $arr['properties']['license'];
            if (!$this->context->dbDriver->check(RestoDatabaseDriver::PRODUCT_LICENSE, array('license_id' => $featureLicense))) {
                RestoLogUtil::httpError(500, 'Contact administrator, license '. $featureLicense . ' is unknown.');
            }

            $license = $this->context->dbDriver->get(RestoDatabaseDriver::PRODUCT_LICENSE, array('license_id' => $featureLicense));
            return array(
                'ErrorMessage' => 'Forbidden',
                'feature' => $feature->identifier,
                'collection' => $collection->name,
                'license_id' => $featureLicense,
                'license' => $license[0]['license_info'],
                'user_id' => $this->user->profile['userid'],
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
         * users/{userid}/legal
         */
        if ($segments[2] === 'legal') {
            return $this->GET_userLegalInfo($segments[1]);
        }

        /*
         * users/{userid}/grantedvisibility
         */
        if ($segments[2] === 'grantedvisibility') {
            return $this->GET_userGrantedVisibility($segments[1]);
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
     * Process users/legal (only admin)
     *
     * @param string $emailOrId
     * @throws Exception
     */
    private function GET_AllLegalInfo() {

        /*
         * Granted Visibility can only be seen by admin users
         */
        if (!$this->isAdminUser()) {
            RestoLogUtil::httpError(403);
        }

        return RestoLogUtil::success('Legal info for all the users', array(
            'legal_infos' => $this->context->dbDriver->get(RestoDatabaseDriver::ALL_LEGAL_INFO)
        ));
    }

    /**
     * Process users/{userid}/legal
     *
     * @param string $emailOrId
     * @throws Exception
     */
    private function GET_userLegalInfo($emailOrId) {

        $user = $this->getAuthorizedUser($emailOrId);

        return RestoLogUtil::success('Legal info for ' . $user->profile['userid'], array(
            'legal' => $user->getLegalInfo()
        ));
    }

    /**
     * Process users/{userid}/grantedvisibility
     *
     * @param string $emailOrId
     * @throws Exception
     */
    private function GET_userGrantedVisibility($emailOrId) {

        /*
         * Granted Visibility can only be seen by admin users
         */
        if (!$this->isAdminUser()) {
            RestoLogUtil::httpError(403);
        }

        $user = $this->getAuthorizedUser($emailOrId);

        return RestoLogUtil::success('Granted visibility for ' . $user->profile['userid'], array(
            'grantedvisibility' => $user->profile['grantedvisibility']
        ));
    }

    /**
     * Process HTTP GET request on user signatures for product licenses
     *
     * @return array
     * @throws Exception
     */
    private function GET_userLicensesProductSignatures() {

        if (!isset($this->user->profile['email'])) {
            RestoLogUtil::httpError(403);
        }

        $signatures = $this->context->dbDriver->get(RestoDatabaseDriver::PRODUCT_LICENSE_SIGNED, array('email' => $this->user->profile['email']));

        return RestoLogUtil::success('Product license signatures for ' . $this->user->profile['userid'], array(
            'userid' => $this->user->profile['userid'],
            'email' => $this->user->profile['email'],
            'signatures' => $signatures
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

    /**
     *
     * Process HTTP GET request on licenses
     *
     * @param array $segments
     */
    private function GET_licenses($segments) {
        $licenseId = isset($segments[1]) ? $segments[1] : null;
        return $this->context->dbDriver->get(RestoDatabaseDriver::PRODUCT_LICENSE, array('license_id' => $licenseId));
    }

}
