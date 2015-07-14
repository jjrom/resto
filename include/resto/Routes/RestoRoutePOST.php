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
 * RESTo REST router for POST requests
 * 
 *    api/licenses/{licenseid}/sign                 |  Sign license
 *    api/user/connect                              |  Connect user
 *    api/user/disconnect                           |  Disconnect user
 *    api/user/resetPassword                        |  Reset password
 * 
 *    collections                                   |  Create a new {collection}            
 *    collections/{collection}                      |  Insert new product within {collection}
 *
 *    user/cart                                     |  Add new item in user cart
 *    user/orders                                   |  Send an order for user
 *    user/groups                                   |  Set groups for user (only admin)
 * 
 *    users                                         |  Add a user
 *    
 */
class RestoRoutePOST extends RestoRoute {
    
    /**
     * Constructor
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }
   
    /**
     * Process HTTP POST request
     * 
     * @param array $segments
     */
    public function route($segments) {
        
        /*
         * Input data for POST request
         */
        $data = RestoUtil::readInputData($this->context->uploadDirectory);
        
        switch($segments[0]) {
            case 'api':
                return $this->POST_api($segments, $data);
            case 'collections':
                return $this->POST_collections($segments, $data);
            case 'user':
                return $this->POST_user($segments, $data);
            case 'users':
                return $this->POST_users($segments, $data);
            default:
                return $this->processModuleRoute($segments, $data);
        }
    }
   
    /**
     * 
     * Process HTTP POST request on api
     * 
     *    api/licenses/{licenseid}/sign                |  Sign license
     *    api/user/connect                             |  Connect user
     *    api/user/disconnect                          |  Disconnect user
     * 
     * @param array $segments
     * @param array $data
     */
    private function POST_api($segments, $data) {
        
        if (!isset($segments[1])) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * api/licenses/{licenseid}/sign
         */
        if ($segments[1] === 'licenses') {
            
            if (isset($segments[3]) && $segments[3] === 'sign' && !isset($segments[4])) {
                if ($this->user->profile['email'] === 'unregistered') {
                    RestoLogUtil::httpError(403);
                }
                return $this->API->signLicense($this->user, $segments[2]);
            }
            
            RestoLogUtil::httpError(404);
            
        }
        
        /*
         * api/user
         */
        else if ($segments[1] === 'user') {
            
            if (!isset($segments[2])) {
                RestoLogUtil::httpError(404);
            }
            
            /*
             * api/user/connect
             */
            if ($segments[2] === 'connect' && !isset($segments[3])) {
                
                if (!isset($data['email']) || !isset($data['password'])) {
                    RestoLogUtil::httpError(400);
                }
                
                /*
                 * Disconnect user
                 */
                if ($this->user->profile['userid'] !== -1) {
                    $this->user->disconnect();
                }
                
                return $this->API->connectUser(new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('email' => strtolower($data['email']), 'password' => $data['password'])), $this->context));
                
            }

            /*
             * api/user/disconnect
             */
            if ($segments[2] === 'disconnect' && !isset($segments[3])) {
                $this->user->disconnect();
                return RestoLogUtil::success('User disconnected');
            }
            
            /*
             * api/user/resetPassword
             */
            if ($segments[2] === 'resetPassword' && !isset($segments[3])) {
                if (!isset($data['email']) || !isset($data['password']) || !isset($data['url'])) {
                    RestoLogUtil::httpError(400);
                }
                return $this->API->resetUserPassword(strtolower($data['email']), $data['password'], $data['url']);
            }
            
            RestoLogUtil::httpError(404);
            
        }
        /*
         * Process module
         */
        else {
            return $this->processModuleRoute($segments, $data);
        }
        
    }

    /**
     * 
     * Process HTTP POST request on collections
     * 
     *    collections                                   |  Create a new {collection}            
     *    collections/{collection}                      |  Insert new product within {collection}
     * 
     * @param array $segments
     * @param array $data
     */
    private function POST_collections($segments, $data) {
        
        /*
         * No feature allowed
         */
        if (isset($segments[2]) ? $segments[2] : null) {
            RestoLogUtil::httpError(404);
        }
        
        if (isset($segments[1])) {
            $collection = new RestoCollection($segments[1], $this->context, $this->user, array('autoload' => true));
        }
        
        /*
         * Create new collection
         */
        if (!isset($collection)) {
            
            /*
             * Only a user with 'create' rights can POST a collection
             */
            if (!$this->user->hasCreateRights()) {
                RestoLogUtil::httpError(403);
            }

            return $this->API->createCollection($data);
            
        }
        /*
         * Insert new feature in collection
         */
        else {
            
            /*
             * Only a user with 'update' rights on collection can POST feature
             */
            if (!$this->user->hasUpdateRights($collection)) {
                RestoLogUtil::httpError(403);
            }
            
            return $this->API->addFeatureToCollection($collection, $data);
            
        }
    }
   
    /**
     * 
     * Process HTTP POST request on users
     * 
     *    user/cart                                     |  Add new item in user cart
     *    user/orders                                   |  Send an order for user
     *
     * @param array $segments
     * @param array $data
     */
    private function POST_user($segments, $data) {
        
        if (!isset($segments[1]) || isset($segments[2])) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * user/cart
         */
        if ($segments[1] === 'cart') {
            return $this->API->addToCart($this->user, $data, isset($this->context->query['_clear']) ? filter_var($this->context->query['_clear'], FILTER_VALIDATE_BOOLEAN) : false);
        }
      
        /*
         * user/orders
         */
        else if ($segments[1] === 'orders') {
            return $this->API->placeOrder($this->user, $data);
        }

        /*
         * Unknown route
         */
        else {
            RestoLogUtil::httpError(404);
        }
        
    }
    
    /**
     * 
     * Process HTTP POST request on users
     * 
     *    users                                         |  Add a user
     *
     * @param array $segments
     * @param array $data
     */
    private function POST_users($segments, $data) {
        
        /*
         * No modifier allowed
         */
        if (isset($segments[1])) {
            RestoLogUtil::httpError(404);
        }
        
        return $this->API->createUser($data);
        
    }   

}
