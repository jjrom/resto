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
    public function route($segments) {
        switch($segments[0]) {
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
        
       /*
        * api/users/connect
        */
       if ($segments[2] === 'connect' && !isset($segments[3])) {
           return $this->GET_apiUsersConnect();
       }

       /*
        * api/users/disconnect
        */
       if ($segments[2] === 'disconnect' && !isset($segments[3])) {
          return $this->GET_apiUsersDisconnect();
       }

       /*
        * api/users/resetPassword
        */
       if ($segments[2] === 'resetPassword' && !isset($segments[3])) {
           return $this->GET_apiUsersResetPassword($segments);
       }

       /*
        * api/users/{userid}/activate
        */
       if (isset($segments[3]) && $segments[3] === 'activate' && !isset($segments[4])) {
           return $this->GET_apiUsersActivate($segments[2]);
       }

       /*
        * api/users/{userid}/isConnected
        */
       if (isset($segments[3]) && $segments[3] === 'isConnected' && !isset($segments[4])) {
           return $this->GET_apiUsersIsConnected($segments[2]);
       }
       
       /*
        * 404
        */
       RestoLogUtil::httpError(403);
       
    }
    
    /**
     * Process api/users/connect
     */
    private function GET_apiUsersConnect() {
        if (isset($this->user->profile['email'])) {
            return array(
                'token' => $this->context->createToken($this->user->profile['userid'], $this->user->profile)
            );
        }
        else {
            RestoLogUtil::httpError(403);
        }
    }
    
    /**
     * Process api/users/disconnect
     */
    private function GET_apiUsersDisconnect() {
        $this->user->disconnect();
        return RestoLogUtil::success('User disconnected');
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
                 * Redirect to a human readable page...
                 */
                if (isset($this->context->query['redirect'])) {
                    header('Location: ' . $this->context->query['redirect']);
                    return null;
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
     * Process api/users/{userid}/isConnected
     * 
     * @param string $userid
     */
    private function GET_apiUsersIsConnected($userid) {
        if (isset($this->context->query['_sid'])) {
            if ($this->context->dbDriver->execute(RestoDatabaseDriver::USER_CONNECTED, array('userid' => $userid))) {
                return RestoLogUtil::success('User is connected');
            }
            else {
                return RestoLogUtil::error('User not connected');
            }
        } else {
            RestoLogUtil::httpError(400);
        }
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
        
        if (isset($segments[1])) {
            $collection = new RestoCollection($segments[1], $this->context, $this->user, array('autoload' => true));
        }
        if (isset($segments[2])) {
            $feature = new RestoFeature($segments[2], $this->context, $this->user, array('collection' => $collection));
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
        else if (!$segments[3]) {
            $this->storeQuery('resource', $collection->name, $feature->identifier);
            return $feature;
        }

        /*
         * Download feature then exit
         */
        else if ($segments[3] === 'download') {
            return $this->GET_featureDownload();
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
        if (!$this->user->canDownload($collection->name, $feature->identifier, $this->context->baseUrl . $this->context->path, !empty($this->context->query['_tk']) ? $this->context->query['_tk'] : null)) {
            RestoLogUtil::httpError(403);
        }
        /*
         * Or user has rigth but hasn't sign the license yet
         */
        else if ($this->user->hasToSignLicense($collection) && empty($this->context->query['_tk'])) {
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
            RestoLogUtil::httpError(501);
        }
        /*
         * users/{userid}
         */
        else if (!isset($segments[2])) {
            return $this->GET_userProfile($segments[1]);
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
        $user = $this->getAuthorizedUser($emailOrId);
        
        return RestoLogUtil::success('Rights for ' . $user->profile['userid'], array(
            'userid' => $user->profile['userid'],
            'groupname' => $user->profile['groupname'],
            'rights' => $user->getFullRights($collectionName, $featureIdentifier)
        ));
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
        $user = $this->getAuthorizedUser($emailOrId);
        
        if (isset($itemid)) {
            RestoLogUtil::httpError(404);
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
}
