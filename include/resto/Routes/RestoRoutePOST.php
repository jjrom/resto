<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2014 Jérôme Gasperi <https://github.com/jjrom>
 * 
 * jerome[dot]gasperi[at]gmail[dot]com
 * 
 * 
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
 * 
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
     *    api/users/{userid}/signLicense                |  Sign license for input collection
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
         * Input data is mandatory for POST request
         */
        $data = RestoUtil::readInputData();
        if (!is_array($data) || count($data) === 0) {
            $this->httpError(400, null, __METHOD__);
        }

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
     *    api/users/{userid}/signLicense                |  Sign license for input collection
     * 
     * @param array $segments
     * @param array $data
     */
    private function POST_api($segments, $data) {
        
        
        if (!isset($segments[1])) {
            $this->httpError(404, null, __METHOD__);
        }

        /*
         * api/users/connect
         */
        if ($segments[1] === 'users') {
            
            if (!isset($segments[2])) {
                $this->httpError(404, null, __METHOD__);
            }
            
            if ($segments[2] === 'connect' && !isset($segments[3])) {
                return $this->POST_apiUsersConnect($data);
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
            $this->httpError(400, null, __METHOD__);
        }

        /*
         * Disconnect user
         */
        if (isset($this->user->profile['email'])) {
            $this->user->disconnect();
        }

        $this->user = new RestoUser($this->context->dbDriver->getUserProfile(strtolower($data['email']), $data['password']), $this->context);
        if (isset($this->user->profile['email'])) {
            return array(
                'token' => $this->context->createToken($this->user->profile['userid'], $this->user->profile)
            );
        }
        else {
            $this->httpError(403, null, __METHOD__);
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
            $this->httpError(403, null, __METHOD__);
        }

        if ($this->user->signLicense($data[0], true)) {
            return $this->success('License signed');
        }
        else {
            return $this->error('Cannot sign license');
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
            $this->httpError(404, null, __METHOD__);
        }
        
        if (isset($segments[1])) {
            $collection = new RestoCollection($segments[1], $this->context, $this->user, array('autoload' => true));
        }
        
        /*
         * Check credentials
         */
        if (!$this->user->canPost(isset($collection) ? $collection->name : null)) {
            $this->httpError(403, null, __METHOD__);
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
            $this->httpError(400, null, __METHOD__);
        }
        if ($this->context->dbDriver->collectionExists($data['name'])) {
            $this->httpError(2003, 'Collection already exists', __METHOD__);
        }
        $collection = new RestoCollection($data['name'], $this->context, $this->user);
        $collection->loadFromJSON($data, true);
        $this->storeQuery('create', $data['name'], null);
        
        return $this->success('Collection ' . $data['name'] . ' created');
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
        return $this->success('Feature ' . $feature->identifier . ' inserted within ' . $collection->name, array(
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
            $this->httpError(404, null, __METHOD__);
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
            return $this->POST_userOrders($segments[1]);
        }
        
        /*
         * Unknown route
         */
        else {
            $this->httpError(404, null, __METHOD__);
        }
        
    }
    
    /**
     * Create user
     * 
     * @param array $data
     */
    private function POST_createUser($data) {
        
        if (!isset($data['email'])) {
            $this->httpError(400, 'Email is not set', __METHOD__);
        }

        if ($this->dbDriver->userExists($data['email'])) {
            $this->httpError(3000, 'User exists', __METHOD__);
        }

        $redirect = isset($data['confirm_success_url']) ? '&redirect=' . urlencode($data['confirm_success_url']) : '';
        $userInfo = $this->dbDriver->storeUserProfile(array(
            'email' => $data['email'],
            'password' => isset($data['password']) ? $data['password'] : null,
            'username' => isset($data['username']) ? $data['username'] : null,
            'givenname' => isset($data['givenname']) ? $data['givenname'] : null,
            'lastname' => isset($data['lastname']) ? $data['lastname'] : null
        ));
        if (isset($userInfo)) {
            $activationLink = $this->context->baseUrl . 'api/users/' . $userInfo['userid'] . '/activate?act=' . $userInfo['activationcode'] . $redirect;
            if (!$this->sendMail(array(
                        'to' => $data['email'],
                        'senderName' => $this->context->mail['senderName'],
                        'senderEmail' => $this->context->mail['senderEmail'],
                        'subject' => $this->context->dictionary->translate('activationSubject', $this->context->title),
                        'message' => $this->context->dictionary->translate('activationMessage', $this->context->title, $activationLink)
                    ))) {
                $this->httpError(3001, 'Problem sending activation code', __METHOD__);
            }
        } else {
            $this->httpError(500, 'Database connection error', __METHOD__);
        }

        return $this->success('User ' . $data['email'] . ' created');
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
        $items = $this->getAuthorizedUser($emailOrId)->addToCart($data, true);
        
        if ($items) {
            return $this->success('Add items to cart', array(
                'items' => $items
            ));
        }
        else {
            return $this->error('Cannot add items to cart');
        }
        
    }
    
    
    /**
     * Process HTTP POST request on user orders
     * 
     *    users/{userid}/orders                         |  Send an order for {userid}
     * 
     * @param string $emailOrId
     * @throws Exception
     */
    private function POST_userOrders($emailOrId) {
        
        /*
         * Order can only be modified by its owner or by admin
         */
        $order = $this->getAuthorizedUser($emailOrId)->placeOrder();
        if ($order) {
            return $this->success('Place order', array(
                'order' => $order
            ));
        }
        else {
            return $this->error('Cannot place order');
        }
        
    }

}
