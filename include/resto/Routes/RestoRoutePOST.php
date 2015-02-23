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
            $this->error(400, null, __METHOD__);
        }

        switch($segments[0]) {
            case 'api':
                return $this->POST_api($segments, $data);
            case 'collections':
                return $this->POST_collections($segments, $data);
            case 'users':
                return $this->POST_users($segments, $data);
            default:
                return $this->processModule($segments, $data);
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
            $this->error(404, null, __METHOD__);
        }

        /*
         * api/users/connect
         */
        if ($segments[1] === 'users') {
            
            if (!isset($segments[2])) {
                $this->error(404, null, __METHOD__);
            }
            
            if ($segments[2] === 'connect' && !isset($segments[3])) {

                if (!isset($data['email']) || !isset($data['password'])) {
                    $this->error(400, null, __METHOD__);
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
                    $this->error(403, null, __METHOD__);
                }

            }

            /*
             * api/users/signLicense
             */
            else if (isset($segments[3]) && $segments[3] === 'signLicense' && !isset($segments[4])) {

                /*
                 * Only user can sign its license
                 */
                if ($this->user->profile['userid'] !== $this->userid($segments[2])) {
                    $this->error(403, null, __METHOD__);
                }

                if ($this->user->signLicense($data[0], true)) {
                    return array(
                        'status' => 'success',
                        'message' => 'License signed'
                    );
                }
                else {
                    return array(
                        'status' => 'error',
                        'message' => 'Cannot sign license'
                    );
                }
            }
        }
        /*
         * Process module
         */
        else {
            return $this->processModule($segments, $data);
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
         * No modifier allowed
         */
        if (isset($segments[3]) ? $segments[3] : null) {
            $this->error(404, null, __METHOD__);
        }
        
        $collectionName = isset($segments[1]) ? $segments[1] : null;
        $featureIdentifier = isset($segments[2]) ? $segments[2] : null;
        
        if (isset($collectionName)) {
            $collection = new RestoCollection($collectionName, $this->context, $this->user, array('autoload' => true));
        }
        if (isset($featureIdentifier)) {
            $feature = new RestoFeature($featureIdentifier, $this->context, $this->user, $collection);
        }
        
        /*
         * Check credentials
         */
        if (!$this->user->canPost($collectionName)) {
            $this->error(403, null, __METHOD__);
        }

        /*
         * Create new collection
         */
        if (!isset($collection)) {
            if (!isset($data['name'])) {
                $this->error(400, null, __METHOD__);
            }
            if ($this->context->dbDriver->collectionExists($data['name'])) {
                $this->error(2003, 'Collection already exists', __METHOD__);
            }
            $collection = new RestoCollection($data['name'], $this->context, $this->user);
            $collection->loadFromJSON($data, true);
            $this->storeQuery('create', $data['name'], null);
            return array(
                'status' => 'success',
                'message' => 'Collection ' . $data['name'] . ' created'
            );
        }
        /*
         * Insert new feature in collection
         */
        else {
            $feature = $collection->addFeature($data);
            $this->storeQuery('insert', $collection->name, $feature->identifier);
            return array(
                'status' => 'success',
                'message' => 'Feature ' . $feature->identifier . ' inserted within ' . $collection->name,
                'featureIdentifier' => $feature->identifier
            );
        }
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
            $this->error(404, null, __METHOD__);
        }
        
        /*
         * users
         */
        if (!isset($segments[1])) {
            
            if (!isset($data['email'])) {
                $this->error(400, 'Email is not set', __METHOD__);
            }
            
            if ($this->dbDriver->userExists($data['email'])) {
                $this->error(3000, 'User exists', __METHOD__);
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
                            'senderName' => $this->context->config['mail']['senderName'],
                            'senderEmail' => $this->context->config['mail']['senderEmail'],
                            'subject' => $this->context->dictionary->translate('activationSubject', $this->context->config['title']),
                            'message' => $this->context->dictionary->translate('activationMessage', $this->context->config['title'], $activationLink)
                        ))) {
                    $this->error(3001, 'Problem sending activation code', __METHOD__);
                }
            } else {
                $this->error(500, 'Database connection error', __METHOD__);
            }
            
            return array(
                'status' => 'success',
                'message' => 'User ' . $data['email'] . ' created'
            );
            
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
            $this->error(404, null, __METHOD__);
        }
        
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
            return array(
                'status' => 'success',
                'message' => 'Add items to cart',
                'items' => $items
            );
        }
        else {
            return array(
                'status' => 'error',
                'message' => 'Cannot add items to cart'
            );
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
        $order = $this->getAuthorizedUser($emailOrId)->placeOrder();
        if ($order) {
            return array(
                'status' => 'success',
                'message' => 'Place order',
                'order' => $order
            );
        }
        else {
            return array(
                'status' => 'error',
                'message' => 'Cannot place order'
            );
        }
        
    }

}
