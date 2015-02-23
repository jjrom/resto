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
 *    | _tk                |     string     | (For download/visualize) sha1 token for resource access
 *    | _rc                |     boolean    | (For search) true to perform the total count of search results
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
     * REST path
     */
    private $path = '';
            
    /*
     * Configuration
     */
    private $config = array();
    
    /*
     * Output format
     */
    private $outputFormat;
    
    /*
     * Method requested (i.e. GET, POST, PUT, DELETE, OPTIONS)
     */
    private $method;
    
    /*
     * Debug mode
     */
    private $debug = false;
    
    /**
     * Constructor
     * 
     * Note : throws HTTP error 500 if resto.ini file does not exist or cannot be read
     * 
     */
    public function __construct() {
        
        try {
           
            /*
             * Read configuration file (i.e. config.php)
             */
            $this->setConfig();
            
            /*
             * HTTP Method is one of GET, POST, PUT or DELETE
             */
            $this->method = strtoupper(filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING));
            
            /*
             * Set REST path
             */
            $this->setPath();
            
            /*
             * Set output format
             */
            $this->setOutputFormat();
            
            /*
             * Context
             */
            $this->setContext();
       
            /*
             * Authenticate user
             */
            $this->authenticate();

            /*
             * Initialize route
             */
            $route = new RestoRoute($this->context, $this->user);
            
            /*
             * Process route
             */
            $responseObject = $route->route();
            
            $response = isset($responseObject) ? $this->format($responseObject) : null; 
            
            
        } catch (Exception $e) {
            
            /*
             * Error are always in JSON
             */
            $this->outputFormat = 'json';
            
            /*
             * Code under 500 is an HTTP code - otherwise it is a resto error code
             * All resto error codes lead to HTTP 200 error code
             */
            $responseStatus = $e->getCode() < 502 ? $e->getCode() : 200;
            $response = RestoUtil::json_format(array('ErrorMessage' => $e->getMessage(), 'ErrorCode' => $e->getCode()));
            
        }
        
        if (isset($response)) {
            $this->answer($response, isset($responseStatus) ? $responseStatus : 200);
        }
        
    }
    
    /**
     * Stream HTTP result and exit
     */
    private function answer($response, $responseStatus) {
        
        /*
         * HTTP 1.1 headers
         */
        header('HTTP/1.1 ' . $responseStatus . ' ' . (isset(RestoUtil::$codes[$responseStatus]) ? RestoUtil::$codes[$responseStatus] : RestoUtil::$codes[200]));
        header("Cache-Control: max-age=2592000, public");
        header('Content-Type: ' . RestoUtil::$contentTypes[$this->outputFormat]);
        
        /*
         * Set headers including cross-origin resource sharing (CORS)
         * http://en.wikipedia.org/wiki/Cross-origin_resource_sharing
         */
        $httpOrigin = filter_input(INPUT_SERVER, 'HTTP_ORIGIN', FILTER_SANITIZE_STRING);
        if (isset($httpOrigin)) {
           header('Access-Control-Allow-Origin: ' . $httpOrigin);
           header('Access-Control-Allow-Credentials: true');
           header('Access-Control-Max-Age: 3600');
        }
        
        /*
         * Stream data
         */
        echo $response;
        
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
            $this->user = new RestoUser($this->context->dbDriver->getUserProfile(strtolower($username), $password), $this->context);
        }
    }
    
    /**
     * Authenticate user from Bearer authentication
     * (i.e. Single Sign On request with oAuth2)
     * 
     * Assume a JSON Web Token encoded by resto
     * 
     * @param string $token
     */
    private function authenticateBearer($token) {
        try {
            $payloadObject = JWT::decode($token, $this->config['general']['passphrase']);
            $this->user = new RestoUser($payloadObject['data'], $this->context);
        } catch (Exception $ex) {
            $this->user = new RestoUser(null, $this->context);
        }
    }
    
    /**
     * Set configuration from config.php file
     */
    private function setConfig() {
        
        $configFile = realpath(dirname(__FILE__)) . '/../config.dev.php';
        
        if (!file_exists($configFile)) {
            throw new Exception(__METHOD__ . 'Missing mandatory configuration file', 4000);
        }
        
        $this->config = include($configFile);
        
        /*
         * JSON Web Token is mandatory
         */
        if (!isset($this->config['general']['passphrase'])) {
            throw new Exception(__METHOD__ . 'Missing mandatory passphrase in configuration file', 4000);
        }
        
        /*
         * Debug mode
         */
        $this->debug = isset($this->config['general']['debug']) ? $this->config['general']['debug'] : false;
        
    }
    
    /**
     * Set REST path
     */
    private function setPath() {
        $restoUrl = filter_input(INPUT_GET, 'RESToURL', FILTER_SANITIZE_STRING);
        if (isset($restoUrl)) {
            $this->path = substr($restoUrl, -1) === '/' ? substr($restoUrl, 0, strlen($restoUrl) - 1) : $restoUrl;
        }
    }

    /**
     * Set output format from suffix or HTTP_ACCEPT
     */
    private function setOutputFormat() {
        
        $this->outputFormat = $this->getPathSuffix();
        
        /*
         * Extract outputFormat from HTTP_ACCEPT 
         */
        if (!isset($this->outputFormat)) {
            $httpAccept = filter_input(INPUT_SERVER, 'HTTP_ACCEPT', FILTER_SANITIZE_STRING);
            $acceptedFormats = explode(',', strtolower(str_replace(' ', '', $httpAccept)));
            foreach ($acceptedFormats as $format) {
                $weight = 1;
                if (strpos($format, ';q=')) {
                    list($format, $weight) = explode(';q=', $format);
                }
                $AcceptTypes[$format] = $weight;
            }
            foreach (RestoUtil::$contentTypes as $key => $value) {
                if (isset($AcceptTypes[$value]) && $AcceptTypes[$value] !== 0) {
                    $this->outputFormat = $key;
                    break;
                }
            }
            
            if (!isset($this->outputFormat)) {
                $this->outputFormat = Resto::DEFAULT_GET_OUTPUT_FORMAT;
            }
        }
        
    }
    
    /**
     * Return suffix from input url
     * @return string
     */
    private function getPathSuffix() {
        
        $splitted = explode('.', $this->path);
        $size = count($splitted);
        if ($size > 1) {
            if (array_key_exists($splitted[$size - 1], RestoUtil::$contentTypes)) {
                $suffix = $splitted[$size - 1];
                array_pop($splitted);
                $this->path = join('.', $splitted);
                return $suffix;
            }
            else {
                throw new Exception('Not Found', 404);
            }
        }
        
        return null;
    }
    
    /**
     * Set context from configuration file
     */
    private function setContext() {
        
        /*
         * Get Database driver
         */
        $dbDriver = $this->getDbDriver();
        
        $this->context = new RestoContext(array(
            
            /*
             * Dictionary
             */
            'dictionary' => $this->getDictionary($dbDriver),
            
            /*
             * Database config
             */
            'dbDriver' => $dbDriver,
            
            /*
             * Base url is the root url of the webapp (e.g. http(s)://host/resto/)
             */
            'baseUrl' => $this->getBaseURL(),
            
            /*
             * Path set after the baseUrl
             */
            'path' => $this->path,
            
            /*
             * Query parameters
             */
            'query' => $this->getParams(),
            
            /*
             * Output format
             */
            'outputFormat' => $this->outputFormat,
            
            /*
             * Debug mode
             */
            'debug' => $this->debug,
            
            /*
             * Store query
             */
            'storeQuery' => isset($this->config['general']['storeQuery']) ? $this->config['general']['storeQuery'] : false,
            
            /*
             * Method
             */
            'method' => $this->method,
            
            /*
             * JSON Web Token passphrase
             * (see https://tools.ietf.org/html/draft-ietf-oauth-json-web-token-32)
             */
            'passphrase' => $this->config['general']['passphrase'],
            
            /*
             * RESTo Config
             */
            'config' => array(
                
                /*
                 * Title
                 */
                'title' => isset($this->config['general']['title']) ? $this->config['general']['title'] : 'resto',
                
                /*
                 * Accepted language
                 */
                'languages' => isset($this->config['general']['languages']) ? $this->config['general']['languages'] : array('en'),
                
                /*
                 * Timezone
                 */
                'timezone' => isset($this->config['general']['timezone']) ? $this->config['general']['timezone'] : 'Europe/Paris',
            
                /*
                 * HTML Theme
                 */
                'theme' => isset($this->config['general']['theme']) ? $this->config['general']['theme'] : 'default',
                
                /*
                 * Modules
                 */
                'modules' => $this->getModules(),
                
                /*
                 * Mail configuration
                 */
                'mail' => isset($this->config['mail']) ? $this->config['mail'] : array(),
                    
            )
        ));
    }
    
    /**
     * Get Database driver
     */
    private function getDbDriver() {
        
        /*
         * Database
         */
        if (!class_exists('RestoDatabaseDriver_' . $this->config['database']['driver'])) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'No database driver defined', 4002);
        }
        try {
            $databaseClass = new ReflectionClass('RestoDatabaseDriver_' . $this->config['database']['driver']);
            if (!$databaseClass->isInstantiable()) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'RestoDatabaseDriver_' . $this->config['database']['driver'] . ' is not insantiable', 4003);
        }   
        
        return $databaseClass->newInstance($this->config['database'], new RestoCache(isset($this->config['database']['dircache']) ? $this->config['database']['dircache'] : null),$this->debug);      
    }
   
    /**
     * Get dictionary from input language
     * 
     * @param RestoDatabaseDriver $dbDriver
     */
    private function getDictionary($dbDriver) {
        
        $languages = isset($this->config['general']['languages']) ? $this->config['general']['languages'] : array('en');
        $lang = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING);
        if (!isset($lang)) {
            $lang = substr($this->getLanguage(), 0, 2);
        }
        if (!in_array($lang, $languages) || !class_exists('RestoDictionary_' . $lang)) {
            $lang = 'en';
        }
        
        return RestoUtil::instantiate('RestoDictionary_' . $lang, array($dbDriver));
        
    }
    
    /**
     * Get activate modules from config.php
     */
    private function getModules() {
        
        $modules = array();
        
        foreach (array_keys($this->config['modules']) as $moduleName) {
            
            /*
             * Only activated module are registered
             */
            if (isset($this->config['modules'][$moduleName]['activate']) && $this->config['modules'][$moduleName]['activate'] === true && class_exists($moduleName)) {
                
                $modules[$moduleName] = isset($this->config['modules'][$moduleName]['options']) ? $this->config['modules'][$moduleName]['options'] : array();
                
                /*
                 * Add route to module
                 */
                if (isset($this->config['modules'][$moduleName]['route'])) {
                    $modules[$moduleName] = array_merge($modules[$moduleName], array('route' => $this->config['modules'][$moduleName]['route']));
                }
                
            }
            
        }
        
        return $modules;
        
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
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Invalid object', 4004);
        }
        
        /*
         * Case 1 - Object is an array 
         */
        if (is_array($object)) {
            
            /*
             * Only JSON is supported for arrays
             */
            $this->outputFormat = 'json';
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
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Invalid object', 4004);
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
        $pretty = isset($this->context->query['_pretty']) ? RestoUtil::toBoolean($this->context->query['_pretty']) : false;
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
        $methodName = 'to' . strtoupper($this->outputFormat);
        if (method_exists(get_class($object), $methodName)) {

            /*
             * JSON-P case
             */
            if ($this->outputFormat === 'json') {
                $pretty = isset($this->context->query['_pretty']) ? RestoUtil::toBoolean($this->context->query['_pretty']) : false;
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
            throw new Exception('Not Found', 404);
        }
    }
    
    /**
     * Get url with no parameters
     * 
     * @return string $pageUrl
     */
    private function getBaseURL() {
        
        $pageURL = 'http';
        $https = filter_input(INPUT_SERVER, 'HTTPS', FILTER_SANITIZE_STRING);
        if (isset($https) && $https === 'on') {
            $pageURL .= 's';
        }
        
        if (!$this->config['general']['restoUrl']) {
            throw new Exception('Missing mandatory restoUrl parameter in configuration file', 4000);
        }
        
        $restoUrl = $this->config['general']['restoUrl'];
        if (substr($restoUrl, -1) !== '/') {
            $restoUrl .= '/';
        }
        
        return substr($restoUrl, 0, 2) === '//' ? $pageURL . ':' . $restoUrl : $restoUrl;
    }

    /**
     * Get browser language
     * (see http://www.thefutureoftheweb.com/blog/use-accept-language-header)
     * 
     * @return string $lang
     */
    private function getLanguage() {
        $langs = array();
        $lang_parse = array();
        $acceptLanguage = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING);
        if (isset($acceptLanguage)) {
            // break up string into pieces (languages and q factors)
            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $acceptLanguage, $lang_parse);

            if (count($lang_parse[1])) {
                // create a list like "en" => 0.8
                $langs = array_combine($lang_parse[1], $lang_parse[4]);

                // set default to 1 for any without q factor
                foreach ($langs as $lang => $val) {
                    if ($val === '') {
                        $langs[$lang] = 1;
                    }
                }

                // sort list based on value	
                arsort($langs, SORT_NUMERIC);

                // Return prefered language
                foreach ($langs as $lang => $val) {
                    return $lang;
                }
            }
        }
    }

    /**
     * Return url parameters from method
     */
    private function getParams() {
        
        /*
         * Aggregate input parameters
         * 
         * Note: PUT is handled by RestoUtil::readInputData() function
         */
        $params = array();
        switch ($this->method) {
            case 'GET':
            case 'DELETE':
                $params = RestoUtil::sanitize($_GET);
                break;
            case 'POST':
                $params = array_merge($_POST, RestoUtil::sanitize($_GET));
                break;
            default:
                break;
        }
        
        /*
         * Remove unwanted parameters
         */
        if ($params['RESToURL']) {
            unset($params['RESToURL']);
        }
        
        /*
         * Trim all values
         */
        if (!function_exists('trim_value')) {
            function trim_value(&$value) {
                $value = trim($value);
            }
        }
        array_walk_recursive($params, 'trim_value');
        
        return $params;
        
    }

    
}
