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
        if ($segments[2] === 'search' || (isset($segments[3]) && $segments[3] === 'search')) {
            return $this->API->searchInCollection($this->user, isset($segments[3]) ? $segments[2] : null);
        }
        else if ($segments[2] === 'describe' || (isset($segments[3]) && $segments[3] === 'describe')) {
            return $this->API->describeCollection($this->user, isset($segments[3]) ? $segments[2] : null);
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
        
        switch ($segments[2]) {

            /*
             * api/user/activate
             */
            case 'activate':
                return $this->API->activateUser(isset($this->context->query['userid']) ? $this->context->query['userid'] : null, isset($this->context->query['act']) ? $this->context->query['act'] : null, isset($this->context->query['redirect']) ? $this->context->query['redirect'] : null);

            /*
             * api/user/connect
             */
            case 'connect':
                return $this->API->connectUser($this->user);

            /*
             * api/user/checkToken
             */
            case 'checkToken':
                return $this->API->checkJWT(isset($this->context->query['_tk']) ? $this->context->query['_tk'] : null);
                
            /*
             * api/user/resetPassword
             */
            case 'resetPassword':
                return $this->API->sendResetPasswordLink(isset($this->context->query['email']) ? $this->context->query['email'] : null);

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
            return $this->API->downloadFeature($this->user, $collection, $feature, isset($this->context->query['_tk']) ? $this->context->query['_tk'] : null);
        }
        
        /*
         * Access WMS for feature
         */
        else if ($segments[3] === 'wms') {
            return $this->API->viewFeature($this->user, $collection, $feature, isset($this->context->query['_tk']) ? $this->context->query['_tk'] : null);
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
            return $this->API->getUserProfile($this->user);
        }
    
        /*
         * user/groups
         */
        if ($segments[1] === 'groups') {
            if (isset($segments[2])) {
                return RestoLogUtil::httpError(404);
            }
            return $this->API->getUserGroups($this->user);
        }

        /*
         * user/rights
         */
        if ($segments[1] === 'rights') {
            return $this->API->getUserRights($this->user, isset($segments[2]) ? $segments[2] : null, isset($segments[3]) ? $segments[3] : null);
        }
        
        /*
         * user/cart
         */
        if ($segments[1] === 'cart') {
            return $this->API->getUserCart($this->user, isset($segments[2]) ? $segments[2] : null);
        }
        
        /*
         * user/orders
         */
        if ($segments[1] === 'orders') {
            return $this->API->getUserOrders($this->user, isset($segments[2]) ? $segments[2] : null);
        }

        /*
         * user/signatures
         */
        if ($segments[1] === 'signatures') {
            return $this->API->getUserSignatures($this->user, isset($segments[2]) ? $segments[2] : null);
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
    
}
