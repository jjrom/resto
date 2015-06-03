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
 */
class RestoRoutePOST extends RestoRoute {
    
    /**
     * Constructor
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }
   
    /**
     *
     * Process HTTP POST request
     * 
     *    api/users/connect                             |  Connect user
     *    api/users/disconnect                          |  Disconnect user
     *    api/users/{userid}/signLicense                |  Sign license for input collection
     *    api/users/resetPassword                       |  Reset password
     * 
     *    collections                                   |  Create a new {collection}            
     *    collections/{collection}                      |  Insert new product within {collection}
     *    
     *    users                                         |  Add a user
     *    users/{userid}/cart                           |  Add new item in {userid} cart
     *    users/{userid}/orders                         |  Send an order for {userid}
     * 
     * @param array $segments
     */
    public function route($segments) {
        
        /*
         * Input data for POST request
         */
        $data = RestoUtil::readInputData($this->context->uploadDirectory);
        /*if (!is_array($data) || count($data) === 0) {
            RestoLogUtil::httpError(400);
        }*/

        switch($segments[0]) {
            case 'api':
                return $this->POST_api($segments, $data);
            case 'collections':
                return $this->POST_collections($segments, $data);
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
     *    api/users/connect                             |  Connect user
     *    api/users/disconnect                          |  Disconnect user
     *    api/users/{userid}/signLicense                |  Sign license for input collection
     * 
     * @param array $segments
     * @param array $data
     */
    private function POST_api($segments, $data) {
        
        
        if (!isset($segments[1])) {
            RestoLogUtil::httpError(404);
        }

        /*
         * api/users
         */
        if ($segments[1] === 'users') {
            
            if (!isset($segments[2])) {
                RestoLogUtil::httpError(404);
            }
            
            /*
             * api/users/connect
             */
            if ($segments[2] === 'connect' && !isset($segments[3])) {
                return $this->POST_apiUsersConnect($data);
            }
            
            /*
             * api/users/disconnect
             */
            if ($segments[2] === 'disconnect' && !isset($segments[3])) {
                return $this->POST_apiUsersDisconnect($data);
            }
            
            /*
             * api/users/resetPassword
             */
            if ($segments[2] === 'resetPassword' && !isset($segments[3])) {
                return $this->POST_apiUsersResetPassword($data);
            }
            
            /*
             * api/users/{userid}/signLicense
             */
            if (isset($segments[3]) && $segments[3] === 'signLicense' && !isset($segments[4])) {
                return $this->POST_apiUsersSignLicense($segments[2], $data);
            }
        }
        /*
         * Process module
         */
        else {
            return $this->processModuleRoute($segments, $data);
        }
        
    }
    
    /**
     * Process api/users/connect
     * 
     * @param array $data
     * @return type
     */
    private function POST_apiUsersConnect($data) {
        
        if (!isset($data['email']) || !isset($data['password'])) {
            RestoLogUtil::httpError(400);
        }

        /*
         * Disconnect user
         */
        if (isset($this->user->profile['email'])) {
            $this->user->disconnect();
        }
        
        /*
         * Get profile
         */
        try {
            $this->user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('email' => strtolower($data['email']), 'password' => $data['password'])), $this->context);
            if (!isset($this->user->profile['email'])) {
                throw new Exception();
            }
            return array(
                'token' => $this->context->createToken($this->user->profile['userid'], $this->user->profile)
            );
        } catch (Exception $ex) {
            RestoLogUtil::httpError(403);
        }

    }
    
    /**
     * Process api/users/disconnect
     */
    private function POST_apiUsersDisconnect($data) {
        $this->user->disconnect();
        return RestoLogUtil::success('User disconnected');
    }

    /**
     * Process api/users/resetPassword
     * 
     * @param array $data
     * @return type
     */
    private function POST_apiUsersResetPassword($data) {
        
        if (!isset($data['url']) || !isset($data['email']) || !isset($data['password'])) {
            RestoLogUtil::httpError(400);
        }
        
        $email = base64_decode($data['email']);
        
        /*
         * Explod data['url'] into resourceUrl and queryString
         */
        $pair = explode('?', $data['url']);
        if (!isset($pair[1])) {
            RestoLogUtil::httpError(403);
        }
        $query = RestoUtil::queryStringToKvps($pair[1]);
        if (!isset($query['_tk']) || !$this->context->dbDriver->check(RestoDatabaseDriver::SHARED_LINK, array('resourceUrl' => $pair[0], 'token' => $query['_tk']))) {
            RestoLogUtil::httpError(403);
        }
        
        if ($this->context->dbDriver->get(RestoDatabaseDriver::USER_PASSWORD, array('email' => $email)) === str_repeat('*', 40)) {
            RestoLogUtil::httpError(3004);
        }
        
        if ($this->context->dbDriver->update(RestoDatabaseDriver::USER_PROFILE, array('profile' => array('email' => $email, 'password' => $data['password'])))) {
            return RestoLogUtil::success('Password updated');
        }
        else {
            RestoLogUtil::httpError(400);
        }
        
    }
    
    /**
     * Process api/users/{userid}/signLicense
     * 
     * @param string $userid
     * @param array $data
     * @return type
     */
    private function POST_apiUsersSignLicense($userid, $data) {
        
        /*
         * Only user can sign its license
         */
        if ($this->user->profile['userid'] !== $this->userid($userid)) {
            RestoLogUtil::httpError(403);
        }

        if (isset($data['collection']) && $this->user->signLicense($data['collection'], true)) {
            return RestoLogUtil::success('License signed');
        }
        else {
            return RestoLogUtil::error('Cannot sign license');
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
         * Check credentials
         */
        if (!$this->user->canPost(isset($collection) ? $collection->name : null)) {
            RestoLogUtil::httpError(403);
        }

        /*
         * Create new collection
         */
        if (!isset($collection)) {
            return $this->POST_createCollection($data);
        }
        /*
         * Insert new feature in collection
         */
        else {
            return $this->POST_insertFeature($collection, $data);
        }
    }
    
    /**
     * Create collection from input data
     * @param array $data
     * @return type
     */
    private function POST_createCollection($data) {
        
        if (!isset($data['name'])) {
            RestoLogUtil::httpError(400);
        }
        if ($this->context->dbDriver->check(RestoDatabaseDriver::COLLECTION, array('collectionName' => $data['name']))) {
            RestoLogUtil::httpError(2003);
        }
        $collection = new RestoCollection($data['name'], $this->context, $this->user);
        $collection->loadFromJSON($data, true);
        $this->storeQuery('create', $data['name'], null);
        
        return RestoLogUtil::success('Collection ' . $data['name'] . ' created');
    }
    
    /**
     * Insert feature into collection 
     * 
     * @param array $data
     * @return type
     */
    private function POST_insertFeature($collection, $data) {
        $feature = $collection->addFeature($data);
        $this->storeQuery('insert', $collection->name, $feature->identifier);
        return RestoLogUtil::success('Feature ' . $feature->identifier . ' inserted within ' . $collection->name, array(
            'featureIdentifier' => $feature->identifier
        ));
    }
    
    /**
     * 
     * Process HTTP POST request on users
     * 
     *    users                                         |  Add a user
     *    users/{userid}/cart                           |  Add new item in {userid} cart
     *    users/{userid}/orders                         |  Send an order for {userid}
     * 
     * @param array $segments
     * @param array $data
     */
    private function POST_users($segments, $data) {
        
        /*
         * No modifier allwed
         */
        if (isset($segments[3])) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * users
         */
        if (!isset($segments[1])) {
            return $this->POST_createUser($data);
        }
     
        /*
         * users/{userid}/cart
         */
        else if (isset($segments[2]) && $segments[2] === 'cart') {
            return $this->POST_userCart($segments[1], $data);
        }
      
        /*
         * users/{userid}/orders
         */
        else if (isset($segments[2]) && $segments[2] === 'orders') {
            return $this->POST_userOrders($segments[1], $data);
        }
        
        /*
         * Unknown route
         */
        else {
            RestoLogUtil::httpError(404);
        }
        
    }
    
    /**
     * Create user
     * 
     * @param array $data
     */
    private function POST_createUser($data) {
        
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
        } else {
            RestoLogUtil::httpError(500, 'Database connection error');
        }

        return RestoLogUtil::success('User ' . $data['email'] . ' created');
    }
    
    /**
     * Process HTTP POST request on user cart
     * 
     *    users/{userid}/cart                           |  Add new item in {userid} cart
     * 
     * @param string $emailOrId
     * @param array $data
     * @throws Exception
     */
    private function POST_userCart($emailOrId, $data) {
        
        /*
         * Cart can only be modified by its owner or by admin
         */
        $user = $this->getAuthorizedUser($emailOrId);
        
        /*
         * Remove items first
         */
        $clear = isset($this->context->query['_clear']) ? filter_var($this->context->query['_clear'], FILTER_VALIDATE_BOOLEAN) : false;
        if ($clear) {
            $user->clearCart(true);
        }
        $items = $user->addToCart($data, true);
        
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
     * Process HTTP POST request on user orders
     * 
     *    users/{userid}/orders                         |  Send an order for {userid}
     * 
     * @param string $emailOrId
     * @param array $data
     * @throws Exception
     */
    private function POST_userOrders($emailOrId, $data) {
        
        /*
         * Order can only be modified by its owner or by admin
         */
        $order = $this->getAuthorizedUser($emailOrId)->placeOrder($data);
        if ($order) {
            return RestoLogUtil::success('Place order', array(
                'order' => $order
            ));
        }
        else {
            return RestoLogUtil::error('Cannot place order');
        }
        
    }

}
