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
        
        /*
         * Search/describe in all collections or in a given collection
         */
        $collectionName = isset($segments[3]) ? $segments[2] : null;
        $resource = isset($collectionName) ? new RestoCollection($collectionName, $this->context, $this->user, array('autoload' => true)) : new RestoCollections($this->context, $this->user);
        $action = isset($collectionName) ? $segments[3] : $segments[2];
        
        /*
         * Search
         */
        if ($action === 'search' || $action === 'describe') {
            
            /*
             * Store query
             */
            if ($this->context->storeQuery === true) {
                $this->user->storeQuery($this->context->method, $action, isset($collectionName) ? $collectionName : '*', null, $this->context->query, $this->context->getUrl());
            }

            /*
             * Search or describe
             */
            return $action === 'search' ? $resource->search() : $resource;      
            
        }
        else {
            RestoLogUtil::httpError(404);
        }
        
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
        
        /*
         * Generate user from input email 
         */
        if (isset($this->context->query['email'])) {
            $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('email' => $this->context->query['email'])), $this->context);
        }
                
        switch ($segments[2]) {

            /*
             * api/user/activate
             */
            case 'activate':
                return $this->activateUser($user);
                
            /*
             * api/user/connect
             */
            case 'connect':
                return $this->user->connect();

            /*
             * api/user/checkToken
             */
            case 'checkToken':
                if (!isset($this->context->query['_tk'])) {
                    RestoLogUtil::httpError(400);
                }
                return $this->context->checkJWT($this->context->query['_tk']) ? RestoLogUtil::success('Valid token') : RestoLogUtil::error('Invalid token');
                
            /*
             * api/user/resetPassword
             */
            case 'resetPassword':
                if (isset($user)) {
                    return $user->sendResetPasswordLink();
                }
                else {
                    RestoLogUtil::httpError(400); 
                }
            default:
                RestoLogUtil::httpError(404);

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
            if ($this->context->storeQuery === true) {
                $this->user->storeQuery($this->context->method, 'resource', $collection->name, $feature->identifier, $this->context->query, $this->context->getUrl());
            }
            return $feature;
        }

        /*
         * Download feature then exit
         */
        else if ($segments[3] === 'download') {
            return $this->downloadFeature($collection, $feature, isset($this->context->query['_tk']) ? $this->context->query['_tk'] : null);
        }
        
        /*
         * Access WMS for feature
         */
        else if ($segments[3] === 'wms') {
            return $this->viewFeature($collection, $feature, isset($this->context->query['_tk']) ? $this->context->query['_tk'] : null);
        }
        
        /*
         * 404
         */
        else {
            RestoLogUtil::httpError(404);
        }
    }

    /**
     * 
     * Process HTTP GET request on users
     * 
     * @param array $segments
     */
    private function GET_user($segments) {
        
        /*
         * user
         */
        if (!isset($segments[1])) {
            return RestoLogUtil::success('Profile for ' . $this->user->profile['email'], array(
                        'profile' => $this->user->profile
            ));
        }

        /*
         * user/groups
         */
        if ($segments[1] === 'groups') {
            if (isset($segments[2])) {
                return RestoLogUtil::httpError(404);
            }
            return RestoLogUtil::success('Groups for ' . $this->user->profile['email'], array(
                        'email' => $this->user->profile['email'],
                        'groups' => $this->user->profile['groups']
            ));
        }

        /*
         * user/rights
         */
        if ($segments[1] === 'rights') {
            return RestoLogUtil::success('Rights for ' . $this->user->profile['email'], array(
                        'email' => $this->user->profile['email'],
                        'userid' => $this->user->profile['userid'],
                        'groups' => $this->user->profile['groups'],
                        'rights' => $this->user->getRights(isset($segments[2]) ? $segments[2] : null, isset($segments[3]) ? $segments[3] : null)
            ));
        }
        
        /*
         * user/cart
         */
        if ($segments[1] === 'cart' && !isset($segments[2])) {
            return $this->user->getCart();
        }
        
        /*
         * user/orders
         */
        if ($segments[1] === 'orders') {
            if (isset($segments[2])) {
                return new RestoOrder($this->user, $this->context, $segments[2]);
            }
            else {
                return RestoLogUtil::success('Orders for user ' . $this->user->profile['email'], array(
                            'email' => $this->user->profile['email'],
                            'userid' => $this->user->profile['userid'],
                            'orders' => $this->user->getOrders()
                ));
            }
        }

        /*
         * user/signatures
         */
        if ($segments[1] === 'signatures' && !isset($segments[2])) {
            return RestoLogUtil::success('Signatures for ' . $this->user->profile['email'], array(
                        'email' => $this->user->profile['email'],
                        'userid' => $this->user->profile['userid'],
                        'groups' => $this->user->profile['groups'],
                        'signatures' => $this->user->getUserSignatures()
            ));
        }
        
        return RestoLogUtil::httpError(404);
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
     * Activate user
     * @param RestoUser $user
     */
    private function activateUser($user) {
        if (isset($user) && isset($this->context->query['act'])) {
            if ($user->activate($this->context->query['act'])) {

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
                else {
                    RestoLogUtil::success('User activated');
                }
            }
            else {
                RestoLogUtil::error('User not activated');
            }
        }
        else {
            RestoLogUtil::httpError(400);
        }
    }
    
    /**
     * Download feature
     * 
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * @param String $token
     * 
     */
    private function downloadFeature($collection, $feature, $token) {
        
        /*
         * Check user download rights
         */
        $user = $this->checkRights('download', $this->user, $token, $collection, $feature);
        
        /*
         * User must be validated
         */
        if (!$user->isValidated()) {
            RestoLogUtil::httpError(403, 'User profile has not been validated. Please contact an administrator');
        }

        /*
         * User do not fullfill license requirements
         */
        if (!$feature->getLicense()->isApplicableToUser($user)) {
            RestoLogUtil::httpError(403, 'You do not fulfill license requirements');
        }
        
        /*
         * User has to sign the license before downloading
         */
        if ($feature->getLicense()->hasToBeSignedByUser($user)) {
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
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * @param string $token
     * 
     */
    private function viewFeature($collection, $feature, $token) {
        
        /*
         * Check user visualize rights
         */
        $user = $this->checkRights('visualize', $this->user, $token, $collection, $feature);
        
        /*
         * User must be validated
         */
        if (!$user->isValidated()) {
            RestoLogUtil::httpError(403, 'User profile has not been validated. Please contact an administrator');
        }

        /*
         * User do not fullfill license requirements
         * Stream low resolution WMS if viewService is public
         * Forbidden otherwise
         */
        $wmsUtil = new RestoWMSUtil($this->context, $user);
        $license = $feature->getLicense();
        if (!$license->isApplicableToUser($user)) {
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
            if ($action === 'download' && !$user->hasRightsTo(RestoUser::DOWNLOAD, array('collectionName' => $collection->name, 'featureIdentifier' => $feature->identifier))) {
                RestoLogUtil::httpError(403);
            }
            if ($action === 'visualize' && !$user->hasRightsTo(RestoUser::VISUALIZE, array('collectionName' => $collection->name, 'featureIdentifier' => $feature->identifier))) {
                RestoLogUtil::httpError(403);
            }
        }
        
        return $user;
    }
    
}
