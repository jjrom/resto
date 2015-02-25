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
abstract class RestoRoute {
    
    /*
     * RestoContext
     */
    protected $context;
    
    /*
     * RestoUser
     */
    protected $user;
    
    /**
     * Constructor
     */
    public function __construct($context, $user) {
        $this->context = $context;
        $this->user = $user;
    }
   
    /**
     * Route to resource
     * 
     * @param array $segments : path as route segments
     */
    abstract public function route($segments);
    
    /**
     * Launch module run() function if exist otherwise returns 404 Not Found
     * 
     * @param array $segments - path (i.e. a/b/c/d) exploded as an array (i.e. array('a', 'b', 'c', 'd')
     * @param array $data - data (POST or PUT)
     */
    protected function processModuleRoute($segments, $data = array()) {
        
        $module = null;
        
        foreach (array_keys($this->context->modules) as $moduleName) {
            
            if (isset($this->context->modules[$moduleName]['route'])) {
                
                $moduleSegments = explode('/', $this->context->modules[$moduleName]['route']);
                $routeIsTheSame = true;
                $count = 0;
                for ($i = 0, $l = count($moduleSegments); $i < $l; $i++) {
                    $count++;
                    if (!isset($segments[$i]) || $moduleSegments[$i] !== $segments[$i]) {
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
            $this->httpError(404, null, __METHOD__);
        }
    }

    /**
     * Store query to database
     * 
     * @param string $serviceName
     * @param string $collectionName
     */
    protected function storeQuery($serviceName, $collectionName, $featureIdentifier) {
        if ($this->context->storeQuery === true && isset($this->user)) {
            $this->user->storeQuery($this->context->method, $serviceName, isset($collectionName) ? $collectionName : null, isset($featureIdentifier) ? $featureIdentifier : null, $this->context->query, $this->context->getUrl());
        }
    }
   
    /**
     * Send user activation code by email
     * 
     * @param array $params
     */
    protected function sendMail($params) {
        $headers = 'From: ' . $params['senderName'] . ' <' . $params['senderEmail'] . '>' . "\r\n";
        $headers .= 'Reply-To: doNotReply <' . $params['senderEmail'] . '>' . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        $headers .= 'X-Priority: 3' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/plain; charset=iso-8859-1' . "\r\n";
        if (mail($params['to'], $params['subject'], $params['message'] , $headers, '-f' . $params['senderEmail'])) {
            return true;
        }
        return false;
    }

    /**
     * Return userid from base64 encoded email or id string
     * 
     * @param string $emailOrId
     */
    protected function userid($emailOrId) {
        
        if (!ctype_digit($emailOrId)) {
            if (isset($this->user->profile['email']) && $this->user->profile['email'] === strtolower(base64_decode($emailOrId))) {
                return $this->user->profile['userid'];
            }
        }
        
        return $emailOrId;
    }
    
    /*
     * Throw HTTP error
     */
    protected function httpError($code, $message = null, $method = null) {
        $error = isset($message) ? $message : (isset(RestoUtil::$codes[$code]) ? RestoUtil::$codes[$code] : 'Unknown error');
        throw new Exception(($this->context->debug && isset($method) ? $method . ' - ' : '') . $error, $code);
    }
    
    /**
     * Return user object if authorized
     * 
     * @param string $emailOrId
     */
    protected function getAuthorizedUser($emailOrId) {
        
        $user = $this->user;
        $userid = $this->userid($emailOrId);
        if ($user->profile['userid'] !== $userid) {
            if ($user->profile['groupname'] !== 'admin') {
                $this->httpError(403, null, __METHOD__);
            }
            else {
                $user = new RestoUser($this->context->dbDriver->getUserProfile($userid), $this->context);
            }
        }
        
        return $user;
        
    }
    
    /**
     * Return output execution status as an array
     *  
     * @param string $message
     * @param array $additional
     */
    protected function success($message, $additional = array()) {
        return $this->message('success', $message, $additional);
    }
    
    /**
     * Return output execution status as an array
     *  
     * @param string $message
     * @param array $additional
     */
    protected function error($message, $additional = array()) {
        return $this->message('error', $message, $additional);
    }
    
    private function message($status, $message, $additional = array()) {
        return array_merge(array(
            'status' => $status,
            'message' => $message
        ), $additional);
    }
}
