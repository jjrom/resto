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
 * RESTo REST router
 * 
 * List of routes
 * --------------
 * 
 * ** Collections **
 *  
 *      A collection contains a list of products. Usually a collection contains homogeneous products
 *      (e.g. "Spot" collection should contains products from Spot satellites; "France" collection should
 *      contains products linked to France) 
 *                           
 *    |          Resource                                      |      Description
 *    |________________________________________________________|________________________________________________
 *    |  GET     collections                                   |  List all collections            
 *    |  POST    collections                                   |  Create a new {collection}            
 *    |  GET     collections/{collection}                      |  Get {collection} description
 *    |  DELETE  collections/{collection}                      |  Delete {collection}
 *    |  PUT     collections/{collection}                      |  Update {collection}
 *    |  GET     collections/{collection}/{feature}            |  Get {feature} description within {collection}
 *    |  GET     collections/{collection}/{feature}/download   |  Download {feature}
 *    |  POST    collections/{collection}                      |  Insert new product within {collection}
 *    |  PUT     collections/{collection}/{feature}            |  Update {feature}
 *    |  DELETE  collections/{collection}/{feature}            |  Delete {feature}
 * 
 *
 * ** Users **
 * 
 *      Users have rights on collections and/or products
 * 
 *    |          Resource                                      |     Description
 *    |________________________________________________________|______________________________________
 *    |  GET     users                                         |  List all users
 *    |  POST    users                                         |  Add a user
 *    |  GET     users/{userid}                                |  Show {userid} information
 *    |  GET     users/{userid}/cart                           |  Show {userid} cart
 *    |  POST    users/{userid}/cart                           |  Add new item in {userid} cart
 *    |  PUT     users/{userid}/cart/{itemid}                  |  Modify item in {userid} cart
 *    |  DELETE  users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
 *    |  GET     users/{userid}/orders                         |  Show orders for {userid}
 *    |  POST    users/{userid}/orders                         |  Send an order for {userid}
 *    |  GET     users/{userid}/orders/{orderid}               |  Show {orderid} order for {userid}
 *    |  GET     users/{userid}/rights                         |  Show rights for {userid}
 *    |  GET     users/{userid}/rights/{collection}            |  Show rights for {userid} on {collection}
 *    |  GET     users/{userid}/rights/{collection}/{feature}  |  Show rights for {userid} on {feature} from {collection}
 * 
 *    Note: {userid} can be replaced by base64(email) 
 * 
 * ** API **
 * 
 *    |          Resource                                      |     Description
 *    |________________________________________________________|______________________________________
 *    |  GET     api/collections/search                        |  Search on all collections
 *    |  GET     api/collections/{collection}/search           |  Search on {collection}
 *    |  GET     api/collections/describe                      |  Opensearch service description at collections level
 *    |  GET     api/collections/{collection}/describe         |  Opensearch service description for products on {collection}
 *    |  POST    api/users/connect                             |  Connect user
 *    |  GET     api/users/disconnect                          |  Disconnect user
 *    |  GET     api/users/resetPassword                       |  Ask for password reset (i.e. reset link sent to user email adress)
 *    |  GET     api/users/{userid}/activate                   |  Activate users with activation code
 *    |  GET     api/users/{userid}/isConnected                |  Check is user is connected
 *    |  POST    api/users/{userid}/signLicense                |  Sign license for input collection
 *
 */
class RestoRoute {
    
    /*
     * RestoContext
     */
    private $context;
    
    /*
     * RestoUser
     */
    private $user;
    
    /**
     * Constructor
     * 
     * Note : throws HTTP error 500 if resto.ini file does not exist or cannot be read
     * 
     */
    public function __construct($context, $user) {
        $this->context = $context;
        $this->user = $user;
    }
   
    /**
     * Route to resource
     * 
     * @param array $segments - path (i.e. a/b/c/d) exploded as an array (i.e. array('a', 'b', 'c', 'd')
     */
    public function route() {
        
        /*
         * Explode route into segment
         */
        $segments = explode('/', $this->context->path);
        
        /*
         * At least one segment is needed
         */
        if (!isset($segments[0])) {
            $this->error(404, null, __METHOD__);
        }
        
        /*
         * Switch on HTTP method
         */
        switch ($this->context->method) {
            
            /*
             * GET
             */
            case 'GET':
                return $this->GET($segments);
            
            /*
             * POST
             */
            case 'POST':
                return $this->POST($segments);
            
            /*
             * PUT
             */
            case 'PUT':
                return $this->PUT($segments);
            
            /*
             * DELETE
             */
            case 'DELETE':
                return $this->DELETE($segments);
                
            /*
             * OPTIONS
             */    
            case 'OPTIONS':
                $this->setCORSHeaders();
                exit(0);
                
            /*
             * Send an HTTP 404 Not Found
             */
            default:
                $this->error(404, null, __METHOD__);
        }
        
        
    }
    
    /**
     * 
     * Process HTTP GET request 
     * 
     *    api/collections/search                        |  Search on all collections
     *    api/collections/{collection}/search           |  Search on {collection}
     *    api/collections/describe                      |  Opensearch service description at collections level
     *    api/collections/{collection}/describe         |  Opensearch service description for products on {collection}
     *    api/users/disconnect                          |  Disconnect user
     *    api/users/resetPassword                       |  Ask for password reset (i.e. reset link sent to user email adress)
     *    api/users/{userid}/activate                   |  Activate users with activation code
     *    api/users/{userid}/isConnected                |  Check is user is connected
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
     * 
     * Note: {userid} can be replaced by base64(email) 
     * 
     * @param array $segments
     *
     */
    private function GET($segments) {
        
        switch($segments[0]) {
            case 'api':
                return $this->GET_api($segments);
            case 'collections':
                return $this->GET_collections($segments);
            case 'users':
                return $this->GET_users($segments);
            default:
                return $this->processModule($segments);
        }
        
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
    private function POST($segments) {
        
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
     * Process HTTP PUT request
     *  
     *    collections/{collection}                      |  Update {collection}
     *    collections/{collection}/{feature}            |  Update {feature}
     *    
     *    users/{userid}/cart/{itemid}                  |  Modify item in {userid} cart
     *    
     * @param array $segments
     */
    private function PUT($segments) {
        
        /*
         * Input data is mandatory for PUT request
         */
        $data = RestoUtil::readInputData();
        if (!is_array($data) || count($data) === 0) {
            $this->error(400, null, __METHOD__);
        }

        switch($segments[0]) {
            case 'collections':
                return $this->PUT_collections($segments, $data);
            case 'users':
                return $this->PUT_users($segments, $data);
            default:
                return $this->processModule($segments, $data);
        }
        
    }

    /**
     * 
     * Process HTTP DELETE request
     * 
     *    collections/{collection}                      |  Delete {collection}
     *    collections/{collection}/{feature}            |  Delete {feature}
     *    
     *    users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
     *    
     * @param array $segments
     */
    private function DELETE($segments) {
        
        switch($segments[0]) {
            case 'collections':
                return $this->DELETE_collections($segments);
            case 'users':
                return $this->DELETE_users($segments);
            default:
                return $this->processModule($segments);
        }
    }

    
    /**
     * 
     * Process HTTP GET request on api
     * 
     *    api/collections/search                        |  Search on all collections
     *    api/collections/{collection}/search           |  Search on {collection}
     *    api/collections/describe                      |  Opensearch service description at collections level
     *    api/collections/{collection}/describe         |  Opensearch service description for products on {collection}
     * 
     *    api/users/disconnect                          |  Disconnect user
     *    api/users/resetPassword                       |  Ask for password reset (i.e. reset link sent to user email adress)
     *    api/users/{userid}/activate                   |  Activate users with activation code
     *    api/users/{userid}/isConnected                |  Check is user is connected
     * 
     * @param array $segments
     */
    private function GET_api($segments) {
        
        
        if (!isset($segments[1]) || isset($segments[4])) {
            $this->error(404, null, __METHOD__);
        }

        /*
         * api/collections
         */
        if ($segments[1] === 'collections' && isset($segments[2])) {
            
            if ($segments[2] === 'search' || (isset($segments[3]) && $segments[3] === 'search')) {
                return $this->GET_apiCollectionsSearch(isset($segments[3]) ? $segments[2] : null);
            }
            else if ($segments[2] === 'describe' || (isset($segments[3]) && $segments[3] === 'describe')) {
                return $this->GET_apiCollectionsDescribe(isset($segments[3]) ? $segments[2] : null);
            }
            else {
                $this->error(404, null, __METHOD__);
            }
        }

        /*
         * api/users
         */
        else if ($segments[1] === 'users' && isset($segments[2])) {

            /*
             * api/users/connect
             */
            if ($segments[2] === 'connect' && !isset($segments[3])) {
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
             * api/users/disconnect
             */
            else if ($segments[2] === 'disconnect' && !isset($segments[3])) {
                $this->user->disconnect();
                return array(
                    'status' => 'success',
                    'message' => 'User disconnected'
                );
            }

            /*
             * api/users/resetPassword
             */
            else if ($segments[2] === 'resetPassword' && !isset($segments[3])) {

                if (!isset($this->context->query['email'])) {
                    $this->error(400, null, __METHOD__);
                }

                /*
                 * Send email with reset link
                 */
                $resetLink = "TODO";
                if (!$this->sendMail($this->context->query['email'], $this->context->config['mail']['senderName'], $this->context->config['mail']['senderEmail'], $this->context->dictionary->translate('resetPasswordSubject', $this->context->config['title']), $this->context->dictionary->translate('resetPasswordMessage', $this->context->config['title'], $resetLink))) {
                    $this->error(3003, 'Cannot send password reset link', __METHOD__);
                }
                else {
                    return array(
                        'status' => 'success',
                        'message' => 'Reset link sent to ' . $this->context->query['email']
                    );
                }
            }

            /*
             * api/users/activate
             */
            else if (isset($segments[3]) && $segments[3] === 'activate' && !isset($segments[4])) {

                if (isset($this->context->query['act'])) {
                    if ($this->dbDriver->activateUser($segments[2], $this->context->query['act'])) {

                        /*
                         * Redirect to a human readable page...
                         */
                        if (isset($this->context->query['redirect'])) {
                            header('Location: ' . $this->context->query['redirect']);
                            exit;
                        }
                        /*
                         * ...or return json stream otherwise
                         */
                        else {
                            return array(
                                'status' => 'success',
                                'message' => 'User activated'
                            );
                        }
                    }
                    else {
                        return array(
                            'status' => 'error',
                            'message' => 'User not activated'
                        );
                    }
                } else {
                    $this->error(400, null, __METHOD__);
                }
            }

            /*
             * api/users/isConnected
             */
            else if (isset($segments[3]) && $segments[3] === 'isConnected' && !isset($segments[4])) {
                if (isset($this->context->query['_sid'])) {
                    if ($this->dbDriver->userIsConnected($segments[2], $this->context->query['_sid'])) {
                        return array(
                            'status' => 'connected',
                            'message' => 'User is connected'
                        );
                    }
                    else {
                        return array(
                            'status' => 'error',
                            'message' => 'User not connected'
                        );
                    }
                }
                else {
                    $this->error(400, null, __METHOD__);
                }
            }
        }
        /*
         * Process module
         */
        else {
            return $this->processModule($segments);
        }
        
    }
    
    
    /**
     *Process HTTP GET request on api collections search
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
     * 
     * Process HTTP GET request on collections
     * 
     *    collections                                   |  List all collections            
     *    collections/{collection}                      |  Get {collection} description
     *    collections/{collection}/{feature}            |  Get {feature} description within {collection}
     *    collections/{collection}/{feature}/download   |  Download {feature}
     * 
     * @param array $segments
     */
    private function GET_collections($segments) {
        
        $collectionName = isset($segments[1]) ? $segments[1] : null;
        $featureIdentifier = isset($segments[2]) ? $segments[2] : null;
        $modifier = isset($segments[3]) ? $segments[3] : null;
        
        if (isset($collectionName)) {
            $collection = new RestoCollection($collectionName, $this->context, $this->user, array('autoload' => true));
        }
        if (isset($featureIdentifier)) {
            $feature = new RestoFeature($featureIdentifier, $this->context, $this->user, $collection);
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
        else if (!isset($featureIdentifier)) {
            return $collection;
        }

        /*
         * Feature description
         */
        else if (!isset($modifier)) {
            $this->storeQuery('resource', $collectionName, $featureIdentifier);
            return $feature;
        }

        /*
         * Download feature then exit
         */
        else if ($modifier === 'download') {

            if (!$this->user->canDownload($collectionName, $featureIdentifier, $this->context->baseUrl . $this->context->path, !empty($this->context->query['_tk']) ? $this->context->query['_tk'] : null)) {
                $this->error(403, null, __METHOD__);
            }
            else if ($this->user->hasToSignLicense($collection) && empty($this->context->query['_tk'])) {
                return array(
                    'ErrorMessage' => 'Forbidden',
                    'collection' => $collection->name,
                    'license' => $collection->getLicense(),
                    'ErrorCode' => 3002
                );
            }
            else {
                $this->storeQuery('download', $collectionName, $featureIdentifier);
                $feature->download();
                exit;
            }
        }
        else {
            $this->error(501, null, __METHOD__);
        }
        
    }
    
    
    /**
     * 
     * Process HTTP GET request on users
     * 
     *    users                                         |  List all users
     *    users/{userid}                                |  Show {userid} information
     *    users/{userid}/cart                           |  Show {userid} cart
     *    users/{userid}/orders                         |  Show orders for {userid}
     *    users/{userid}/orders/{orderid}               |  Show {orderid} order for {userid}
     *    users/{userid}/rights                         |  Show rights for {userid}
     *    users/{userid}/rights/{collection}            |  Show rights for {userid} on {collection}
     *    users/{userid}/rights/{collection}/{feature}  |  Show rights for {userid} on {feature} from {collection}
     * 
     * Note: {userid} can be replaced by base64(email) 
     * 
     * @param array $segments
     */
    private function GET_users($segments) {
   
        /*
         * users
         */
        if (!isset($segments[1])) {
            $this->error(501, null, __METHOD__);
        }
        /*
         * users/{userid}
         */
        else if (!isset($segments[2])) {
            
            /*
             * Profile can only be seen by its owner or by admin
             */
            $user = $this->user;
            $userid = $this->userid($segments[1]);
            
            if ($user->profile['userid'] !== $userid) {
                if ($user->profile['groupname'] !== 'admin') {
                    $this->error(403, null, __METHOD__);
                }
                else {
                    $user = new RestoUser($this->context->dbDriver->getUserProfile($userid), $this->context);
                }
            }
            else {
                return array(
                    'status' => 'success',
                    'message' => 'Profile for ' . $user->profile['userid'],
                    'profile' => $user->profile
                );
            }
            
        }
        else {
            
            /*
             * users/{userid}/rights
             */
            if ($segments[2] === 'rights') {
                return $this->GET_userRights($segments[1], isset($segments[3]) ? $segments[3] : null, isset($segments[4]) ? $segments[4] : null);
            }
            /*
             * users/{userid}/cart
             */
            else if ($segments[2] === 'cart') {
                return $this->GET_userCart($segments[1], isset($segments[3]) ? $segments[3] : null);
            }
            /*
             * users/{userid}/orders
             */
            else if ($segments[2] === 'orders') {
                return $this->GET_userOrders($segments[1], isset($segments[3]) ? $segments[3] : null);
            }
            
        }
        
    }
    
    
    /**
     * Process HTTP GET request on user rights
     *   
     *    users/{userid}/rights                         |  Show rights for {userid}
     *    users/{userid}/rights/{collection}            |  Show rights for {userid} on {collection}
     *    users/{userid}/rights/{collection}/{feature}  |  Show rights for {userid} on {feature} from {collection}
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
        $user = $this->user;
        $userid = $this->userid($emailOrId);
        if ($user->profile['userid'] !== $userid) {
            if ($user->profile['groupname'] !== 'admin') {
                $this->error(403, null, __METHOD__);
            }
            else {
                $user = new RestoUser($this->context->dbDriver->getUserProfile($userid), $this->context);
            }
        }

        return array(
            'status' => 'success',
            'message' => 'Rights for ' . $user->profile['userid'],
            'userid' => $user->profile['userid'],
            'groupname' => $user->profile['groupname'],
            'rights' => $user->getFullRights($collectionName, $featureIdentifier)
        );
        
    }
    
    
    /**
     * Process HTTP GET request on user cart
     *   
     *    users/{userid}/cart                           |  Show {userid} cart
     *
     * @param string $emailOrId
     * @param string $itemid
     * @throws Exception
     */
    private function GET_userCart($emailOrId, $itemid = null) {
        
        /*
         * Cart can only be seen by its owner or by admin
         */
        $user = $this->user;
        $userid = $this->userid($emailOrId);
        if ($user->profile['userid'] !== $userid) {
            if ($user->profile['groupname'] !== 'admin') {
                $this->error(403, null, __METHOD__);
            }
            else {
                $user = new RestoUser($this->context->dbDriver->getUserProfile($userid), $this->context);
            }
        }
        
        if (isset($itemid)) {
            $this->error(404, null, __METHOD__);
        }
        
        return $user->getCart();
        
    }
    
    
    /**
     * Process HTTP GET request on user orders
     *   
     *    users/{userid}/orders                         |  Show orders for {userid}
     *
     * @param string $emailOrId
     * @param string $orderid
     * @throws Exception
     */
    private function GET_userOrders($emailOrId, $orderid = null) {
        
        /*
         * Orders can only be seen by its owner or by admin
         */
        $user = $this->user;
        $userid = $this->userid($emailOrId);
        if ($user->profile['userid'] !== $userid) {
            if ($user->profile['groupname'] !== 'admin') {
                $this->error(403, null, __METHOD__);
            }
            else {
                $user = new RestoUser($this->context->dbDriver->getUserProfile($userid), $this->context);
            }
        }
        
        /*
         * Special case of metalink for single order
         */
        if (isset($orderid)) {
            return new RestoOrder($user, $this->context, $orderid);
        }
        else {
            return array(
                'status' => 'success',
                'message' => 'Orders for user ' . $userid,
                'orders' => $user->getOrders()
            );
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
                if (!$this->sendMail($data['email'], $this->context->config['mail']['senderName'], $this->context->config['mail']['senderEmail'], $this->context->dictionary->translate('activationSubject', $this->context->config['title']), $this->context->dictionary->translate('activationMessage', $this->context->config['title'], $activationLink))) {
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
        $user = $this->user;
        $userid = $this->userid($emailOrId);
        if ($user->profile['userid'] !== $userid) {
            if ($user->profile['groupname'] !== 'admin') {
                $this->error(403, null, __METHOD__);
            }
            else {
                $user = new RestoUser($this->context->dbDriver->getUserProfile($userid), $this->context);
            }
        }
        
        $items = $user->addToCart($data, true);           
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
         * Cart can only be modified by its owner or by admin
         */
        $user = $this->user;
        $userid = $this->userid($emailOrId);
        if ($user->profile['userid'] !== $userid) {
            if ($user->profile['groupname'] !== 'admin') {
                $this->error(403, null, __METHOD__);
            }
            else {
                $user = new RestoUser($this->context->dbDriver->getUserProfile($userid), $this->context);
            }
        }
            
        $order = $user->placeOrder();
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
    
    
    /**
     * 
     * Process HTTP PUT request on collections
     * 
     *    collections/{collection}                      |  Update {collection}
     *    collections/{collection}/{feature}            |  Update {feature}
     * 
     * @param array $segments
     * @param array $data
     */
    private function PUT_collections($segments, $data) {
        
        /*
         * {collection} is mandatory and no modifier is allowed
         */
        if (!isset($segments[1]) || isset($segments[3])) {
            $this->error(404, null, __METHOD__);
        }
        
        $collection = new RestoCollection($segments[1], $this->context, $this->user, array('autoload' => true));
        $featureIdentifier = isset($segments[2]) ? $segments[2] : null;
        if (isset($featureIdentifier)) {
            $feature = new RestoFeature($featureIdentifier, $this->context, $this->user, $collection);
        }
        
        /*
         * Check credentials
         */
        if (!$this->user->canPut($collection->name, $featureIdentifier)) {
            $this->error(403, null, __METHOD__);
        }

        /*
         * collections/{collection}
         */
        if (!isset($feature)) {
            $collection->loadFromJSON($data, true);
            $this->storeQuery('update', $collection->name, null);
            return array(
                'status' => 'success',
                'message' => 'Collection ' . $collection->name . ' updated'
            );
        }
        /*
         * collections/{collection}/{feature}
         */
        else {
            //$this->storeQuery('update', $collection->name, $featureIdentifier);
            $this->error(501, null, __METHOD__);
        }
        
    }
    
    
    /**
     * 
     * Process HTTP PUT request on users
     * 
     *    users/{userid}/cart/{itemid}                  |  Modify item in {userid} cart
     * 
     * @param array $segments
     * @param array $data
     */
    private function PUT_users($segments, $data) {
        
        /*
         * Mandatory {itemid}
         */
        if (!isset($segments[3])) {
            $this->error(404, null, __METHOD__);
        }
        
        if ($segments[1] === 'cart') {
            return $this->PUT_userCart($segments[1], $segments[3], $data);
        }
        else {
            $this->error(404, null, __METHOD__);
        }
        
    }
    
    
    /**
     * 
     * Process HTTP PUT request on users cart
     * 
     *    users/{userid}/cart/{itemid}                  |  Modify item in {userid} cart
     * 
     * @param string $emailOrId
     * @param string $itemId
     * @param array $data
     */
    private function PUT_userCart($emailOrId, $itemId, $data) {
        
        /*
         * Cart can only be modified by its owner or by admin
         */
        $user = $this->user;
        $userid = $this->userid($emailOrId);
        if ($user->profile['userid'] !== $userid) {
            if ($user->profile['groupname'] !== 'admin') {
                $this->error(403, null, __METHOD__);
            }
            else {
                $user = new RestoUser($this->context->dbDriver->getUserProfile($userid), $this->context);
            }
        }
         
        if ($user->updateCart($itemId, $data, true)) {
            return array(
                'status' => 'success',
                'message' => 'Item ' . $itemId . ' updated',
                'itemId' => $itemId,
                'item' => $data
            );
        }
        else {
            return array(
                'status' => 'error',
                'message' => 'Cannot update item ' . $itemId
            );
        }
        
    }
    
    
    /**
     * 
     * Process HTTP DELETE request on collections
     * 
     *    collections/{collection}                      |  Delete {collection}
     *    collections/{collection}/{feature}            |  Delete {feature}
     * 
     * @param array $segments
     */
    private function DELETE_collections($segments) {
        
        /*
         * {collection} is mandatory and no modifier is allowed
         */
        if (!isset($segments[1]) || isset($segments[3])) {
            $this->error(404, null, __METHOD__);
        }
        
        $collection = new RestoCollection($segments[1], $this->context, $this->user, array('autoload' => true));
        $featureIdentifier = isset($segments[2]) ? $segments[2] : null;
        if (isset($featureIdentifier)) {
            $feature = new RestoFeature($featureIdentifier, $this->context, $this->user, $collection);
        }
        
        /*
         * Check credentials
         */
        if (!$this->user->canDelete($collection->name, $featureIdentifier)) {
            $this->error(403, null, __METHOD__);
        }

        /*
         * collections/{collection}
         */
        if (!isset($feature)) {
            $collection->removeFromStore();
            $this->storeQuery('remove', $collection->name, null);
            return array(
                'status' => 'success',
                'message' => 'Collection ' . $collection->name . ' deleted'
            );
        }
        /*
         * collections/{collection}/{feature}
         */
        else {
            $feature->removeFromStore();
            $this->storeQuery('remove', $collection->name, $featureIdentifier);
            return array(
                'status' => 'success',
                'message' => 'Feature ' . $featureIdentifier . ' deleted',
                'featureIdentifier' => $featureIdentifier
            );
        }
        
    }
    
    
    /**
     * 
     * Process HTTP DELETE request on users
     * 
     *    users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
     * 
     * @param array $segments
     */
    private function DELETE_users($segments) {
        
        /*
         * Mandatory {itemid}
         */
        if (!isset($segments[3])) {
            $this->error(404, null, __METHOD__);
        }
        
        if ($segments[1] === 'cart') {
            return $this->DELETE_userCart($segments[1], $segments[3]);
        }
        else {
            $this->error(404, null, __METHOD__);
        }
        
    }
    
    
    /**
     * 
     * Process HTTP DELETE request on users cart
     * 
     *    users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
     * 
     * @param string $emailOrId
     * @param string $itemId
     */
    private function DELETE_userCart($emailOrId, $itemId) {
        
        /*
         * Cart can only be modified by its owner or by admin
         */
        $user = $this->user;
        $userid = $this->userid($emailOrId);
        if ($user->profile['userid'] !== $userid) {
            if ($user->profile['groupname'] !== 'admin') {
                $this->error(403, null, __METHOD__);
            }
            else {
                $user = new RestoUser($this->context->dbDriver->getUserProfile($userid), $this->context);
            }
        }
        
        /*
         * users/{userid}/cart/{itemid} 
         */
        if ($user->removeFromCart($itemId, true)) {
            return array(
                'status' => 'success',
                'message' => 'Item removed from cart',
                'itemid' => $itemId
            );
        }
        else {
            return array(
                'status' => 'error',
                'message' => 'Item cannot be removed',
                'itemid' => $itemId
            );
        }
    }
    
    
    /**
     * Launch module run() function if exist otherwise returns 404 Not Found
     * 
     * @param array $segments - path (i.e. a/b/c/d) exploded as an array (i.e. array('a', 'b', 'c', 'd')
     * @param array $data - data (POST or PUT)
     */
    private function processModule($segments, $data = array()) {
        
        $module = null;
        
        foreach (array_keys($this->context->config['modules']) as $moduleName) {
            
            if (isset($this->context->config['modules'][$moduleName]['route'])) {
                
                $moduleSegments = explode('/', $this->context->config['modules'][$moduleName]['route']);
                $routeIsTheSame = true;
                $count = 0;
                for ($i = 0, $l = count($moduleSegments); $i < $l; $i++) {
                    $count++;
                    if ($moduleSegments[$i] !== $segments[$i]) {
                        $routeIsTheSame = false;
                        break;
                    } 
                }
                if ($routeIsTheSame) {
                    $module = RestoUtil::instantiate($moduleName, array($this->context, $this->user));
                    for ($i = $count; $i--;) {
                        array_shift($segments);
                    }
                    return $module->run($segments, $data);
                }
            }
        }
        if (!isset($module)) {
            $this->error(404, null, __METHOD__);
        }
    }

    /**
     * Store query to database
     * 
     * @param string $serviceName
     * @param string $collectionName
     */
    private function storeQuery($serviceName, $collectionName, $featureIdentifier) {
        if ($this->context->storeQuery === true && isset($this->user)) {
            $this->user->storeQuery($this->context->method, $serviceName, isset($collectionName) ? $collectionName : null, isset($featureIdentifier) ? $featureIdentifier : null, $this->context->query, $this->context->getUrl());
        }
    }
    
    
    /**
     * Send user activation code by email
     * 
     * @param string $to
     * @param string $senderName
     * @param string $senderEmail
     * @param string $subject
     * @param string $message
     */
    private function sendMail($to, $senderName, $senderEmail, $subject, $message) {
        $headers = array(
            "From: " . $senderName . " <" . $senderEmail . ">\r\n",
            "Reply-To: doNotReply <" . $senderEmail . ">\r\n",
            "X-Mailer: PHP/" . phpversion(),
            "MIME-Version: 1.0\r\n",
            "Content-type: text/plain; charset=iso-8859-1\r\n"
        );
        if (mail($to, $subject, $message, join('', $headers), '-f' . $senderEmail)) {
            return true;
        }

        return false;
    }

    
    /**
     * Set CORS headers (HTTP OPTIONS request)
     */
    private function setCORSHeaders() {
        
       /*
        * Only set access to known servers
        */
       if (isset($_SERVER['HTTP_ORIGIN'])) {
           header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
           header('Access-Control-Allow-Credentials: true');
           header('Access-Control-Max-Age: 3600');
       }

       /*
        * Control header are received during OPTIONS requests
        */
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');         
        }
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
        }
       
    }
    
    
    /**
     * Return userid from base64 encoded email or id string
     * 
     * @param string $emailOrId
     */
    private function userid($emailOrId) {
        
        if (!ctype_digit($emailOrId)) {
            if (isset($this->user->profile['email']) && $this->user->profile['email'] === strtolower(base64_decode($emailOrId))) {
                return $this->user->profile['userid'];
            }
        }
        
        return $emailOrId;
    }
    
    /*
     * Throw 404 Not Found exception
     */
    private function error($code, $message = null, $method = null) {
        $error = isset($message) ? $message : isset(RestoUtil::$codes[$code]) ? RestoUtil::$codes[$code] : 'Unknown error';
        throw new Exception(($this->context->debug && isset($method) ? $method . ' - ' : '') . $error, 404);
    }
    
    
}
