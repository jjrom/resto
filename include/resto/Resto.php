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
 * RESTo entry point
 * 
 * This class should be instantiate with
 * 
 *      $resto = new Resto();
 * 
 * Access to resource
 * ==================
 * 
 * General url template
 * --------------------
 *     
 *      http(s)://host/resto/search/collections/{collection}/?key1=value1&key2=value2&...
 *      \__________________/\______________________________/\___________________________/
 *            baseUrl                   path                             query
 *
 *      Where :
 * 
 *          {collection} is the name of the collection (e.g. 'Charter', 'SPIRIT', etc.).
 *          {feature} is the identifier of a product within a {collection}
 * 
 * List of "path"
 * --------------
 * 
 *  Available routes are described in RestoRoute.php
 *    
 * Query
 * -----
 * 
 *   Query parameters are described within OpenSearch Description file
 *
 *   Special query parameters can be used to modify the query. These parameters are not specified 
 *   within the OpenSearch Description file. Below is the list of Special query parameters
 *
 * 
 *    | Query parameter    |      Type      | Description
 *    |______________________________________________________________________________________________
 *    | _pretty            |     boolean    | (For JSON output only) true to return pretty print JSON
 *    | _tk                |     string     | (For download/visualize/resetPassword) token for resource access
 *    |                                     | (For /api/users/checkToken) JWT profile token 
 *    | _rc                |     boolean    | (For search) true to perform the total count of search results
 *    | _fromCart          |     boolean    | (For orders) true to order the content of the cart
 *    | _clear             |     boolean    | (For POST /users/{userid}/cart) true to remove cart items before inserting new items
 *    | _bearer            |     string     | (For authentication) JWT token - has preseance over header authentication (see rocket)
 *    | callback           |     string     | (For JSON output only) name of callback funtion for JSON-P
 * 
 * Returned error
 * --------------
 *  
 *   - HTTP 400 'Bad Request' for invalid request
 *   - HTTP 403 'Forbiden' when accessing protected resource/service with invalid credentials
 *   - HTTP 404 'Not Found' when accessing non existing resource/service
 *   - HTTP 405 'Method Not Allowed' when accessing existing resource/service with unallowed HTTP method
 *   - HTTP 500 'Internal Server Error' for technical errors (i.e. database connection error, etc.)
 * 
 * ErrorCode list
 * --------------
 * 
 *   - 1000 : Cannot add item to cart because item already exist
 *   - 1001 : Cannot update item in cart because item does not exist in cart
 *   - 2000 : Abort create collection - schema does not exist
 *   - 2001 : Abort create collection - collection not created
 *   - 2003 : Cannot create collection - collection already exist
 *   - 3000 : Cannot create user - user already exists
 *   - 3001 : Cannot create user - cannot send activation code
 *   - 3002 : User has to sign license
 *   - 3003 : Cannot send password reset link
 *   - 3004 : Cannot reset password for a non local user
 *   - 3005 : Invalid user
 *   - 4000 : Configuration file problem
 *   - 4001 : Dictionary is not instantiable
 *   - 4002 : Database driver does not exist
 *   - 4003 : Database driver is not instantiable
 *   - 4004 : Invalid input object
 *   - 4005 : Invalid input array
 */
class Resto {
    
    /*
     * RESTo major version number
     */
    const VERSION = '2.0';
    
    /*
     * Default output format if not specified in request
     */
    const DEFAULT_GET_OUTPUT_FORMAT = 'json';
    
    /*
     * RestoContext
     */
    public $context;
    
    /*
     * RestoUser
     */
    public $user;
    
    /*
     * If true then output is set to JSON 
     */
    private $inError = false;
    
    /*
     * CORS white list
     */
    private $corsWhiteList = array();
    
    /**
     * Constructor
     * 
     * @param string $configFile
     * 
     */
    public function __construct($configFile) {
        
        try {
           
            /*
             * Context
             */
            $this->context = new RestoContext($this->readConfig($configFile));
            
            /*
             * Authenticate user
             */
            $this->authenticate();

            /*
             * Route
             */
            $response = $this->getResponse();
            
            
        } catch (Exception $e) {
            
            /*
             * Output in error - format output as JSON in the following
             */
            $this->inError = true;
            
            /*
             * Code under 500 is an HTTP code - otherwise it is a resto error code
             * All resto error codes lead to HTTP 200 error code
             */
            $responseStatus = $e->getCode() < 502 ? $e->getCode() : 400;
            $response = RestoUtil::json_format(array('ErrorMessage' => $e->getMessage(), 'ErrorCode' => $e->getCode()));
            
        }
        
        /*
         * Close database handler
         */
        if (isset($this->context) && isset($this->context->dbDriver)) {
            $this->context->dbDriver->closeDbh();
        }
        
        $this->answer(isset($response) ? $response : null, isset($responseStatus) ? $responseStatus : 200);
        
    }
    
    /**
     * Initialize route and get response from server
     */
    private function getResponse() {
        
        /*
         * Initialize route from HTTP method
         */
        switch ($this->context->method) {
            
            /*
             * GET
             */
            case 'GET':
                $route = new RestoRouteGET($this->context, $this->user);
                break;
            /*
             * POST
             */
            case 'POST':
                $route = new RestoRoutePOST($this->context, $this->user);
                break;
            /*
             * PUT
             */
            case 'PUT':
                $route = new RestoRoutePUT($this->context, $this->user);
                break;
            /*
             * DELETE
             */
            case 'DELETE':
                $route = new RestoRouteDELETE($this->context, $this->user);
                break;
            /*
             * OPTIONS
             */    
            case 'OPTIONS':
                $this->setCORSHeaders();
                return null;
            /*
             * Send an HTTP 404 Not Found
             */
            default:
                RestoLogUtil::httpError(404);
        }
        
        /*
         * Process route
         */
        $responseObject = $route->route(explode('/', $this->context->path));

        return isset($responseObject) ? $this->format($responseObject) : null;
    }

    /**
     * Stream HTTP result and exit
     */
    private function answer($response, $responseStatus) {
        
        /*
         * HTTP 1.1 headers
         */
        header('HTTP/1.1 ' . $responseStatus . ' ' . (isset(RestoLogUtil::$codes[$responseStatus]) ? RestoLogUtil::$codes[$responseStatus] : RestoLogUtil::$codes[200]));
        header('Cache-Control:  no-cache');
        header('Content-Type: ' . RestoUtil::$contentTypes[$this->inError ? 'json' : $this->context->outputFormat]);
        
        /*
         * Set headers including cross-origin resource sharing (CORS)
         * http://en.wikipedia.org/wiki/Cross-origin_resource_sharing
         */
        $this->setCORSHeaders();
        
        /*
         * Stream data
         */
        if (isset($response)) {
            echo $response;
        }
        
    }
    
    /**
     * Authenticate and set user accordingly
     * 
     * Various authentication method
     * 
     *   - HTTP user:password (i.e. http authorization mechanism) 
     *   - Single Sign On request with oAuth2
     * 
     */
    private function authenticate() {
        
        /*
         * Auhtentication through token in url
         */
        if (isset($this->context->query['_bearer'])) {
            $this->authenticateBearer($this->context->query['_bearer']);
            unset($this->context->query['_bearer']);
            return true;
        }
        
        /*
         * Get authorization headers
         */
        $httpAuth = filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION', FILTER_SANITIZE_STRING);
        $rhttpAuth = filter_input(INPUT_SERVER, 'REDIRECT_HTTP_AUTHORIZATION', FILTER_SANITIZE_STRING);
        $authorization = !empty($httpAuth) ? $httpAuth : (!empty($rhttpAuth) ? $rhttpAuth : null);
        
        /*
         * Authenticate
         */
        if (isset($authorization)) {
            list($method, $token) = explode(' ', $authorization, 2);
            switch ($method) {
                case 'Basic':
                    $this->authenticateBasic($token);
                    break;
                case 'Bearer':
                    $this->authenticateBearer($token);
                    break;
                default:
                    break;
            }
        }
        
        /*
         * Otherwise user is unregistered
         */
        if (!isset($this->user)) {
            $this->user = new RestoUser(null, $this->context);
        }
        
        return true;
        
    }
    
    /**
     * Authenticate user from Basic authentication
     * (i.e. HTTP user:password)
     * 
     * @param string $token
     */
    private function authenticateBasic($token) {
        list($username, $password) = explode(':', base64_decode($token), 2);
        if (!empty($username) && !empty($password)) {
            try {
                $profile = $this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array(
                    'email' => strtolower($username),
                    'password' => $password
                ));
            } catch (Exception $ex) {
                $profile = null;
            }
            $this->user = new RestoUser($profile, $this->context);
        }
    }
    
    /**
     * Authenticate user from Bearer authentication
     * (i.e. Single Sign On request with oAuth2)
     * 
     * Assume either a JSON Web Token encoded by resto or a token generated by an SSO issuer (e.g. google)
     * 
     * @param string $token
     */
    private function authenticateBearer($token) {
        
        try {
            
            /*
             * If issuer_id is specified in the request then assumes a third party token.
             * In this case, transform this third party token into a resto token
             */
            if (isset($this->context->query['issuerId'])) {
                $auth = new Auth($this->context, null);
                $token = $auth->getProfileToken($this->context->query['issuerId'], $token);
            }
            
            $payloadObject = json_decode(json_encode((array) $this->context->decodeJWT($token)), true);
            $this->user = new RestoUser($payloadObject['data'], $this->context);
            
            /*
             * Assign token to user
             */
            $this->user->token = $token;
            
        } catch (Exception $ex) {}
    }
    
    /**
     * Read configuration from config.php file
     */
    private function readConfig($configFile) {
        if (!file_exists($configFile)) {
            RestoLogUtil::httpError(4000);
        }
        $config = include($configFile);
        
        /*
         * Set global debug mode
         */
        if (isset($config['general']['debug'])) {
            RestoLogUtil::$debug = $config['general']['debug'];
        }
        
        /*
         * Set white list for CORS
         */
        if (isset($config['general']['corsWhiteList'])) {
            $this->corsWhiteList = $config['general']['corsWhiteList'];
        }
        
        return $config;
    }
    
    /**
     * Call one of the output method from $object (i.e. toJSON(), toATOM(), etc.)
     * 
     * @param object $object
     * @throws Exception
     */
    private function format($object) {
        
        /*
         * Case 0 - Object is null
         */
        if (!isset($object)) {
            RestoLogUtil::httpError(4004);
        }
        
        /*
         * Case 1 - Object is an array 
         */
        if (is_array($object)) {
            
            /*
             * Only JSON is supported for arrays
             */
            $this->context->outputFormat = 'json';
            return $this->toJSON($object);
        }
        
        /*
         * Case 2 - Object is an object
         */
        else if (is_object($object)) {
            return $this->formatObject($object);
        }
        /*
         * Unknown stuff
         */
        else {
            RestoLogUtil::httpError(4004);
        }
        
    }
    
    /**
     * Encode input $array to JSON
     * 
     * @param array $array
     * @throws Exception
     */
    private function toJSON($array) {
        
        /*
         * JSON-P case
         */
        $pretty = isset($this->context->query['_pretty']) ? filter_var($this->context->query['_pretty'], FILTER_VALIDATE_BOOLEAN) : false;
        if (isset($this->context->query['callback'])) {
            return $this->context->query['callback'] . '(' . json_encode($array, $pretty) . ')';
        }
        
        return RestoUtil::json_format($array, $pretty);
        
    }
    
    /**
     * Encode input $array to JSON
     * 
     * @param array $object
     * @throws Exception
     */
    private function formatObject($object) {
        $methodName = 'to' . strtoupper($this->context->outputFormat);
        if (method_exists(get_class($object), $methodName)) {

            /*
             * JSON-P case
             */
            if ($this->context->outputFormat === 'json') {
                $pretty = isset($this->context->query['_pretty']) ? filter_var($this->context->query['_pretty'], FILTER_VALIDATE_BOOLEAN) : false;
                if (isset($this->context->query['callback'])) {
                    return $this->context->query['callback'] . '(' . $object->$methodName($pretty) . ')';
                }
                return $object->$methodName($pretty);
            }
            else {
                return $object->$methodName();
            }
        }
        else {
            RestoLogUtil::httpError(404);
        }
    }
    
    /**
     * Set CORS headers (HTTP OPTIONS request)
     */
    private function setCORSHeaders() {

        $httpOrigin = filter_input(INPUT_SERVER, 'HTTP_ORIGIN', FILTER_SANITIZE_STRING);
        $httpRequestMethod = filter_input(INPUT_SERVER, 'HTTP_ACCESS_CONTROL_REQUEST_METHOD', FILTER_SANITIZE_STRING);
        $httpRequestHeaders = filter_input(INPUT_SERVER, 'HTTP_ACCESS_CONTROL_REQUEST_HEADERS', FILTER_SANITIZE_STRING);
        
        /*
         * Only set access to known servers
         */
        if (isset($httpOrigin) && $this->corsIsAllowed($httpOrigin)) {
            header('Access-Control-Allow-Origin: ' . $httpOrigin);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 3600');
        }

        /*
         * Control header are received during OPTIONS requests
         */
        if (isset($httpRequestMethod)) {
            header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
        }
        if (isset($httpRequestHeaders)) {
            header('Access-Control-Allow-Headers: ' . $httpRequestHeaders);
        }
    }
    
    /**
     * Return true if $httpOrigin is allowed to do CORS
     * If corsWhiteList is empty, then every $httpOrigin is allowed.
     * Otherwise only origin in white list are allowed
     * 
     * @param {String} $httpOrigin
     */
    private function corsIsAllowed($httpOrigin) {
        
        /*
         * No white list => all allowed
         */
        if (empty($this->corsWhiteList)) {
            return true;
        }
        
        $url = explode('//', $httpOrigin);
        if (isset($url[1])) {
            for ($i = count($this->corsWhiteList); $i--;) {
                if ($this->corsWhiteList[$i] === $url[1]) {
                    return true;
                }
            }
        }
        
        return false;
        
    }
    
}
