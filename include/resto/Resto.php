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
 *    |  POST    users/{userid}/cart                           |  Add new item in {userid} cart (through 'resourceUrl' key)
 *    |  DELETE  users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
 *    |  GET     users/{userid}/rights                         |  Show rights for {userid}
 *    |  GET     users/{userid}/rights/{collection}            |  Show rights for {userid} on {collection}
 *    |  GET     users/{userid}/rights/{collection}/{feature}  |  Show rights for {userid} on {feature} from {collection}
 * 
 * 
 * ** Tags **
 * 
 *      Tags are associated to a product
 * 
 *    |          Resource                                      |     Description
 *    |________________________________________________________|______________________________________
 *    |  GET     tags/{collection}/{feature}                   |  Returns tags for {feature}
 *    |  POST    tags/{collection}/{feature}                   |  Add tags to {feature}
 * 
 * 
 * ** API **
 * 
 *    |          Resource                                      |     Description
 *    |________________________________________________________|______________________________________
 *    |  GET     api/collections/search                        |  Search on all collections
 *    |  GET     api/collections/{collection}/search           |  Search on {collection}
 *    |  GET     api/collections/describe                      |  Opensearch service description at collections level
 *    |  GET     api/collections/{collection}/describe         |  Opensearch service description for products on {collection}
 *    |  GET     api/users/connect                             |  Connect user
 *    |  GET     api/users/disconnect                          |  Disconnect user
 *    |  GET     api/users/{userid}/activate                   |  Activate users with activation code
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
 *    | _showQuery         |     boolean    | (For HTML output only) true to display query analysis result
 *    | _tk                |     string     | (For download/visualize) sha1 token for resource access
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
 */
class Resto {
    
    /*
     * RESTo major version number
     */
    const version = '2.0';
    
    /*
     * Default output format if not specified in request
     */
    const DEFAULT_GET_OUTPUT_FORMAT = 'html';
    
    /*
     * String storing response
     */
    private $response;

    /*
     * Response HTTP status
     */
    private $responseStatus = 200;
    
    /*
     * Configuration
     */
    private $config = array();
    
    /*
     * RestoContext
     */
    public $context;
    
    /*
     * Resto Database driver
     */
    private $dbDriver;
    
    /*
     * Output format
     */
    private $outputFormat;
    
    /*
     * Method requested (i.e. GET, POST, PUT, DELETE)
     */
    private $method;
    
    /*
     * RestoUser
     */
    private $user;
    
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
             * Initialization (includes user authentication)
             */
            $this->initialize();
            
            /*
             * Set RESTo context
             */
            $this->setContext();
        
            /*
             * Route action
             */
            $this->route(explode('/', $this->path));
            
        } catch (Exception $e) {
            $this->responseStatus = $e->getCode();
            if ($e->getCode() === 404 && $this->outputFormat === 'html') {
                $this->response = RestoUtil::get_include_contents(realpath(dirname(__FILE__)) . '/../../themes/' . $this->context->config['theme'] . '/templates/404.php', $this);
            }
            else {
                $this->outputFormat = 'json';
                $this->response = RestoUtil::json_format(array('ErrorMessage' => $e->getMessage(), 'ErrorCode' => $e->getCode()));
            }
        }
        
        /*
         * Set headers including cross-origin resource sharing (CORS)
         * http://en.wikipedia.org/wiki/Cross-origin_resource_sharing
         */
        ob_start();
        header('HTTP/1.1 ' . $this->responseStatus . ' ' . (isset(RestoUtil::$codes[$this->responseStatus]) ? RestoUtil::$codes[$this->responseStatus] : RestoUtil::$codes[200]));
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: ' . RestoUtil::$contentTypes[$this->outputFormat]);
        echo $this->response;
        ob_end_flush();

    }
   
    /**
     * Route to resource
     * 
     * @param array $segments - path (i.e. a/b/c/d) exploded as an array (i.e. array('a', 'b', 'c', 'd')
     */
    private function route($segments) {
        
        switch ($segments[0]) {
            
            /*
             * Homepage
             */
            case '':
                $this->process404(); // TODO
                break;
            /*
             * Collections
             * 
             *      collections/
             *      collections/{collection}
             *      collections/{collection}/{feature}
             *      collections/{collection}/{feature}/download
             * 
             */
            case 'collections':
                $this->processCollections(isset($segments[1]) ? $segments[1] : null, isset($segments[2]) ? $segments[2] : null, isset($segments[3]) ? $segments[3] : null);
                break;
            /*
             * API
             * 
             *      api/collections/search
             *      api/collections/{collection}/search
             *      api/collections/describe (Opensearch description endpoint)
             *      api/collections/{collection}/describe (Opensearch description endpoint)
             *      api/users/connect
             *      api/users/disconnect
             *      api/users/{userid}/activate
             */
            case 'api':
                if (!isset($segments[1])) {
                    $this->process404();
                }
                else if ($segments[1] === 'collections') {
                    if (!isset($segments[2])) {
                        $this->process404();
                    }
                    else if ($segments[2] === 'search' && !isset($segments[3])) {
                        $this->processAPISearch(null);
                    }
                    else if ($segments[2] === 'describe' && !isset($segments[3])) {
                        $this->processAPIDescribeSearch(null);
                    }
                    else if (isset($segments[3]) && $segments[3] === 'search' && !isset($segments[4])) {
                        $this->processAPISearch($segments[2]);
                    }
                    else if (isset($segments[3]) && $segments[3] === 'describe' && !isset($segments[4])) {
                        $this->processAPIDescribeSearch($segments[2]);
                    }
                    else {
                        $this->process404();
                    }
                }
                else if ($segments[1] === 'users') {
                    
                    /*
                     * Output is always in JSON
                     */
                    $this->outputFormat = 'json';
                    
                    if (!isset($segments[2])) {
                        $this->process404();
                    }
                    /*
                     * User should already be connected with setUser
                     */
                    else if ($segments[2] === 'connect' && !isset($segments[3])) {
                        if (isset($this->user->profile['email'])) {
                            $this->response = json_encode(array('status' => 'success', 'message' => 'User ' . $this->user->profile['email'] . ' connected'));
                        }
                        else {
                            throw new Exception('Forbidden', 403);
                        }
                    }
                    else if ($segments[2] === 'disconnect' && !isset($segments[3])) {
                        $this->user->disconnect();
                        $this->response = json_encode(array('status' => 'success', 'message' => 'User disconnected'));
                    }
                    else if (isset($segments[3]) && $segments[3] === 'activate' && !isset($segments[4])) {
                        if (isset($this->context->query['act'])) {
                            if ($this->dbDriver->activateUser($segments[2], $this->context->query['act'])) {
                                $this->response = json_encode(array('status' => 'success', 'message' => 'User activated'));
                            }
                            else {
                                $this->response = json_encode(array('status' => 'error', 'message' => 'User not activated'));
                            }
                        }
                        else {
                            throw new Exception('Bad Request', 400);
                        }
                    }
                    else {
                        $this->process404();
                    }
                }
                else {
                    $this->processModule($segments);
                }
                break;
            /*
             * Tags
             * 
             *      tags/{collection}/{feature}
             */
            case 'tags':
                $this->processTags(isset($segments[1]) ? $segments[1] : null, isset($segments[2]) ? $segments[2] : null);
                break;
            /*
             * Users
             *  
             *      users
             *      users/{userid}
             *      users/{userid}/rights
             *      users/{userid}/rights/{collection}
             *      users/{userid}/rights/{collection}/{feature}
             *      users/{userid}/cart
             *      users/{userid}/cart/{itemid}
             */
            case 'users':
                if (isset($segments[2])) {
                    if ($segments[2] === 'rights') {
                        $this->processUsers($segments[1], isset($segments[3]) ? $segments[3] : null, isset($segments[4]) ? $segments[4] : null);
                    }
                    else if ($segments[2] === 'cart') {
                        $this->processUserCart($segments[1], isset($segments[3]) ? $segments[3] : null);
                    }
                    else {
                        $this->process404();
                    }
                }
                else {
                    $this->processUsers(isset($segments[1]) ? $segments[1] : null);
                }
                break;
            /*
             * Otherwise check for module routes
             * or return 404 Not Found
             */
            default:
                $this->processModule($segments);
        }
        
    }
    
    /**
     * Launch module run() function if exist otherwise returns 404 Not Found
     * 
     * @param array $segments - path (i.e. a/b/c/d) exploded as an array (i.e. array('a', 'b', 'c', 'd')
     */
    private function processModule($segments) {
        
        $module = null;
        foreach (array_keys($this->config['modules']) as $moduleName) {
            if ($this->config['modules'][$moduleName]['activate'] === true && isset($this->config['modules'][$moduleName]['route'])) {
                $moduleSegments = explode('/', $this->config['modules'][$moduleName]['route']);
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
                    $module = RestoUtil::instantiate($moduleName, array($this->context, isset($this->config['modules'][$moduleName]['options']) ? array_merge($this->config['modules'][$moduleName]['options'], array('debug' => $this->debug)) : array('debug' => $this->debug)));
                    for ($i = $count; $i--;) {
                        array_shift($segments);
                    }
                    $this->response = $module->run($segments);
                    break;
                }
            }
        }
        if (!isset($module)) {
            $this->process404();
        }
    }

    /**
     * Process 'search' requests
     * 
     *    |  GET     api/collections/search                             |  Search on all collections
     *    |  GET     api/collections/{collection}/search                |  Search on {collection}
     *    
     * @param string $collectionName
     * @throws Exception
     */
    private function processAPISearch($collectionName = null) {
        
        /*
         * Only GET method is allowed
         */
        if ($this->method !== 'GET') {
            $this->process404();
        }
        $resource = isset($collectionName) ? new RestoCollection($collectionName, $this->context, array('autoload' => true)) : new RestoCollections($this->context); 
        $this->response = $this->format($resource->search());
        $this->storeQuery('search', $collectionName, null);

    }
    
    /**
     * Process 'describesearch' requests
     * 
     *    |  GET     api/collections/describe                      |  Opensearch description file for collections search
     *    |  GET     api/collections/{collection}/describe         |  Opensearch description file for products search in {collection}
     *    
     * @param string $collectionName
     * @throws Exception
     */
    private function processAPIDescribeSearch($collectionName = null) {
        
        /*
         * Only GET method is allowed
         */
        if ($this->method !== 'GET') {
            $this->process404();
        }
        /*
         * Only XML format is allowed
         */
        if ($this->outputFormat !== 'xml') {
            $this->process404();
        }
        $resource = isset($collectionName) ? new RestoCollection($collectionName, $this->context, array('autoload' => true)) : new RestoCollections($this->context); 
        $this->response = $this->format($resource);
        $this->storeQuery('describe', $collectionName, null);
    }
    
    /**
     * Process 'users' requests
     *   
     *    |  GET     users                                         |  List all users
     *    |  POST    users                                         |  Add a user
     *    |  GET     users/{userid}                                |  Show information on {userid}
     *    |  GET     users/{userid}/rights                         |  Show rights for {userid}
     *    |  GET     users/{userid}/rights/{collection}            |  Show rights for {userid} on {collection}
     *    |  GET     users/{userid}/rights/{collection}/{feature}  |  Show rights for {userid} on {feature} from {collection}
     *
     * @param string $userid
     * @param string $collectionName
     * @param string $featureIdentifier
     * @throws Exception
     */
    private function processUsers($userid = null, $collectionName = null, $featureIdentifier = null) {
        
        if (!isset($userid)) {
            
            /*
             * Add user
             */
            if ($this->method === 'POST') {
                if (!isset($this->context->query['email']) || $this->dbDriver->userExists($this->context->query['email'])) {
                    throw new Exception('Bad Request', 400);
                }
                $userInfo = $this->dbDriver->storeUserProfile(array(
                    'email' => $this->context->query['email'],
                    'password' => isset($this->context->query['password']) ? $this->context->query['password'] : null,
                    'givenname' => isset($this->context->query['givenname']) ? $this->context->query['givenname'] : null,
                    'lastname' => isset($this->context->query['lastname']) ? $this->context->query['lastname'] : null
                ));
                if (isset($userInfo)) {
                    if (!$this->sendActivationMail($this->context->query['email'], isset($this->config['authentication']['activationEmail']) ? $this->config['authentication']['activationEmail'] : null, $this->context->baseUrl . 'api/users/' . $userInfo['userid'] . '/activate?act=' . $userInfo['activationcode'])) {
                        throw new Exception('Problem sending activation code', 500);
                    }
                }
                else {
                    throw new Exception('Database connection error', 500);
                }
                $this->response = json_encode(array('status' => 'success', 'message' => 'User ' . $this->context->query['email'] . ' created'));
            }
            /*
             * List all users
             */
            else if ($this->method === 'GET') {
                throw new Exception('Not Implemented', 501);
            }
            else {
                $this->process404();
            }
            
        }
        else {
            throw new Exception('Not Implemented', 501);
        }
        
    }
    
    /**
     * Process 'users/{userid}/cart' requests
     *   
     *    |  GET     users/{userid}/cart                           |  Show {userid} cart
     *    |  POST    users/{userid}/cart                           |  Add new item in {userid} cart
     *    |  DELETE  users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
     *
     * @param string $userid
     * @param string $itemid
     * @throws Exception
     */
    private function processUserCart($userid, $itemid = null) {
        
        /*
         * Cart can only be seen by its owner
         */
        if ($this->user->profile['userid'] !== $userid) {
            throw new Exception('Forbidden', 403); 
        }
        
        /*
         * List cart
         */
        if ($this->method === 'GET') {
            if (isset($itemid)) {
                $this->process404();
            }
            $this->response = $this->format($this->user->getCart());
        }
        /*
         * Add item to cart - POST must contain a 'resourceUrl' property
         */
        else if ($this->method === 'POST') {
            if (isset($itemid)) {
                $this->process404();
            }
            if (!isset($this->context->query['resourceUrl'])) {
                throw new Exception('Bad Request', 400);
            }
            if ($this->user->addToCart($this->context->query['resourceUrl'], true)) {
                $this->response = json_encode(array('status' => 'success', 'message' => 'Add item to cart'));
            }
            else {
                $this->response = json_encode(array('status' => 'error', 'message' => 'Cannot add item to cart'));
            }
        }
        /*
         * Remove item from cart
         */
        else if ($this->method === 'DELETE') {
            if (!isset($itemid)) {
                $this->process404();
            }
            if ($this->user->removeFromCart($itemid, true)) {
                $this->response = json_encode(array('status' => 'success', 'message' => 'Item removed from cart'));
            }
            else {
                $this->response = json_encode(array('status' => 'error', 'message' => 'Item cannot be removed'));
            }
        }
        
    }
    
    /**
     * 
     * Process 'collections' request
     * 
     *    |  GET     collections                                   |  List all collections descriptions            
     *    |  GET     collections/{collection}                      |  Get {collection} description
     *    |  GET     collections/{collection}/{feature}            |  Get {feature} description within {collection}
     *    |  GET     collections/{collection}/{feature}/download   |  Download {feature}
     *    |  POST    collections                                   |  Create a new {collection}            
     *    |  POST    collections/{collection}                      |  Insert new product within {collection}
     *    |  PUT     collections/{collection}                      |  Update {collection}
     *    |  PUT     collections/{collection}/{feature}            |  Update {feature}
     *    |  DELETE  collections/{collection}                      |  Delete {collection}
     *    |  DELETE  collections/{collection}/{feature}            |  Delete {feature}
     * 
     */
    private function processCollections($collectionName, $featureIdentifier, $modifier) {
        
        $collection = null;
        $feature = null;
        
        if (isset($collectionName)) {
            $collection = new RestoCollection($collectionName, $this->context, array('autoload' => true));
        }
        if (isset($featureIdentifier)) {
            $feature = new RestoFeature($featureIdentifier, $this->context, $collection);
        }
        
        if ($this->method === 'GET') {
            
            /*
             * All collections description (XML is not allowed - see api/describe/collections)
             */
            if (!isset($collection)) {
                if ($this->outputFormat === 'xml') {
                    $this->outputFormat = 'json';
                    $this->process404();
                }
                $collections = new RestoCollections($this->context, array('autoload' => true));
                $this->response = $this->format($collections);
            }
            
            /*
             * Collection description (XML is not allowed - see api/describe/collections)
             */
            else if (!isset($featureIdentifier)) {
                if ($this->outputFormat === 'xml') {
                    $this->outputFormat = 'json';
                    $this->process404();
                }
                $this->response = $this->format($collection);
            }
            
            /*
             * Feature description
             */
            else if (!isset($modifier)) {
                $this->response = $this->format($feature);
                $this->storeQuery('resource', $collectionName, $featureIdentifier);
            }
            
            /*
             * Download feature then exit
             */
            else if ($modifier === 'download') {
                if (!$this->user->canDownload($collectionName, $featureIdentifier, $this->getBaseURL() .  $this->context->path, isset($this->context->query['_tk']) ? $this->context->query['_tk'] : null)) {
                    throw new Exception('Forbidden', 403);
                }
                $feature->download();
                $this->storeQuery('download', $collectionName, $featureIdentifier);
                exit;
            }
            
            else {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Implemented', 501); 
            }
        }
        else if ($this->method === 'POST') {
            
            if (isset($modifier)) {
                $this->process404();
            }
            
            /*
             * Read POST data (input files or 'data' property)
             */
            $data = RestoUtil::readInputData();
            
            if (!is_array($data) || count($data) === 0) {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Invalid posted file', 500);
            }
            
            /*
             * Create new collection
             */
            if (!isset($collection)) {
                if (!isset($data['name'])) {
                    throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Invalid posted file', 500);
                }
                if ($this->dbDriver->collectionExists($data['name'])) {
                    throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Collection already exists', 500);
                }
                $collection = new RestoCollection($data['name'], $this->context);
                $collection->loadFromJSON($data, true);
                $this->response = json_encode(array('status' => 'success', 'message' => 'Collection ' . $data['name'] . ' created'));
                $this->storeQuery('create', $data['name'], null);
            }
            /*
             * Insert new feature in collection
             */
            else {
                $feature = $collection->addFeature($data);
                $this->response = json_encode(array('status' => 'success', 'message' => 'Feature ' . $feature->identifier . ' inserted within '. $collection->name));
                $this->storeQuery('insert', $collection->name, $feature->identifier);
            }
        }
        else if ($this->method === 'PUT') {
            
            if (isset($modifier) || !isset($collection)) {
                $this->process404();
            }
            
            /*
             * Read PUT data (input files or 'data' property)
             */
            $data = RestoUtil::readInputData();
            
            if (!is_array($data) || count($data) === 0) {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Invalid posted file', 500);
            }
            
            /*
             * Update collection
             */
            if (!isset($feature)) {
                $collection->loadFromJSON($data, true);
                $this->response = json_encode(array('status' => 'success', 'message' => 'Collection ' . $collectionName . ' updated'));
                $this->storeQuery('update', $collectionName, null);
            }
            /*
             * Update feature
             */
            else {
                $this->storeQuery('update', $collectionName, $featureIdentifier);
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Implemented', 501);
            }
        }
        else if ($this->method === 'DELETE') {
            
            if (isset($modifier) || !isset($collection)) {
                $this->process404();
            }
            
            /*
             * Delete collection
             */
            if (!isset($feature)) {
                $collection->removeFromStore();
                $this->response = json_encode(array('status' => 'success', 'message' => 'Collection ' . $collectionName . ' deleted'));
                $this->storeQuery('remove', $collectionName, null);
            }
            /*
             * Delete feature
             */
            else {
                $feature->removeFromStore();
                $this->response = json_encode(array('status' => 'success', 'message' => 'Feature ' . $featureIdentifier . ' deleted'));
                $this->storeQuery('remove', $collectionName, $featureIdentifier);
            }
        }
    }
    
    /*
     * Throw 404 Not Found exception
     */
    private function process404() {
        throw new Exception('Not Found', 404);
    }
    
    /**
     * Get url with no parameters
     * 
     * @return string $pageUrl
     */
    private function getBaseURL() {
        
        $pageURL = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $pageURL .= 's';
        }
        
        if (!$this->config['general']['restoUrl']) {
            throw new Exception('Missing mandatory restoUrl parameter in configuration file', 500);
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
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // break up string into pieces (languages and q factors)
            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

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
                $params = $_GET;
                break;
            case 'POST':
                $params = array_merge($_POST, $_GET);
                break;
            case 'DELETE':
                $params = $_GET;
                break;
            default:
                break;
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

    /**
     * Initialize essential variables from config file 
     * and input url
     */
    private function initialize() {
        
        /*
         * Read resto.ini configuration file
         */
        $iniFile = realpath(dirname(__FILE__)) . '/../resto.ini';
        if (!file_exists($iniFile)) {
            throw new Exception(__METHOD__ . 'Missing mandatory configuration file', 500);
        }
        $this->config = IniParser::read($iniFile);
        
        /*
         * Debug mode
         */
        $this->debug = isset($this->config['general']['debug']) ? $this->config['general']['debug'] : false;
        
        /*
         * dbDriver
         */
        $this->setDbDriver();
       
        /*
         * Set RestoUser
         */
        $this->setUser();

        /*
         * Method is one of GET, POST, PUT or DELETE
         */
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);

        /*
         * Extract path
         */
        $this->path = trim(isset($_GET['RESToURL']) && !empty($_GET['RESToURL']) ? (substr($_GET['RESToURL'], -1) === '/' ? substr($_GET['RESToURL'], 0, strlen($_GET['RESToURL']) - 1) : $_GET['RESToURL']) : '');
        
        $splitted = explode('.', $this->path);
        $size = count($splitted);
        if ($size > 1) {
            if (array_key_exists($splitted[$size - 1], RestoUtil::$contentTypes)) {
                $this->outputFormat = $splitted[$size - 1];
                array_pop($splitted);
                $this->path = join('.', $splitted);
            } else {
                throw new Exception(($this->config['general']['debug'] ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
            }
        }
        unset($_GET['RESToURL']);
        
        /*
         * Output format is always JSON for HTTP methods except GET
         */
        if ($this->method !== 'GET') {
            $this->outputFormat = 'json';
        }
        
        /*
         * Extract outputFormat from HTTP_ACCEPT 
         */
        if (!isset($this->outputFormat)) {
            $accepted = explode(',', strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT'])));
            foreach ($accepted as $a) {
                $q = 1;
                if (strpos($a, ';q=')) {
                    list($a, $q) = explode(';q=', $a);
                }
                $AcceptTypes[$a] = $q;
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
        
        /*
         * Avoid bug with special characters ' and " in path
         */
        if (strrpos($this->path, '\'') !== false || strrpos($this->path, '"') !== false) {
            $this->process404();
        }
       
    }
    
    /**
     * Set context from configuration file
     */
    private function setContext() {
        
        /*
         * Dictionary
         */
        $languages = isset($this->config['general']['languages']) ? $this->config['general']['languages'] : array('en');
        $lang = substr(isset($_GET['lang']) ? $_GET['lang'] : $this->getLanguage(), 0, 2);
        if (!in_array($lang, $languages) || !class_exists('RestoDictionary_' . $lang)) {
            $lang = 'en';
        }
        try {
            $dictionaryClass = new ReflectionClass('RestoDictionary_' . $lang);
            if (!$dictionaryClass->isInstantiable()) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception((debug ? __METHOD__ . ' - ' : '') . 'RestDictionary_' . $lang . ' is not insantiable', 500);
        }
        $dictionary = $dictionaryClass->newInstance($this->dbDriver);
        
        /*
         * Activated modules are reference within context
         */
        $modules = array();
        foreach (array_keys($this->config['modules']) as $moduleName) {
            if ($this->config['modules'][$moduleName]['activate'] === true && class_exists($moduleName)) {
                $modules[$moduleName] = isset($this->config['modules'][$moduleName]['options']) ? array_merge($this->config['modules'][$moduleName]['options'], array('debug' => $this->debug)) : array('debug' => $this->debug);
            }
        }
        
        /*
         * Identity providers
         */
        $ssoServices = array();
        if (isset($modules['OAuth'])) {
            if (isset($modules['OAuth']['providers'])) {
                foreach (array_keys($modules['OAuth']['providers']) as $provider) {
                    $ssoServices[$provider] = array(
                        'authorizeUrl' => $modules['OAuth']['providers'][$provider]['authorizeUrl'] . '&client_id=' . $modules['OAuth']['providers'][$provider]['clientId'] . '&redirect_uri=' . $this->getBaseURL() . 'api/oauth/callback?issuer_id=' . $modules['OAuth']['providers'][$provider]['issuer_id']
                    );
                }
            }
        }
        
        $this->context = new RestoContext(array(
            
            /*
             * Dictionary
             */
            'dictionary' => $dictionary,
            
            /*
             * Database config
             */
            'dbDriver' => $this->dbDriver,
            
            /*
             * User
             */
            'user' => $this->user,
            
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
             * Method
             */
            'method' => $this->method,
            
            /*
             * RESTo Config
             */
            'config' => array(
                
                /*
                 * SSO Services
                 */
                'ssoServices' => $ssoServices,
                
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
                 * Count total result during searches 
                 */
                'realCount' => isset($this->config['general']['realCount']) ? $this->config['general']['realCount'] : false,
                
                /*
                 * Non routed modules
                 */
                'modules' => $modules
                
            )
        ));
    }
    
    /*
     * Database driver
     */
    private function setDbDriver() {
        
        /*
         * Database
         */
        if (!class_exists('RestoDatabaseDriver_' . $this->config['database']['driver'])) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'No database driver defined', 500);
        }
        try {
            $databaseClass = new ReflectionClass('RestoDatabaseDriver_' . $this->config['database']['driver']);
            if (!$databaseClass->isInstantiable()) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'RestoDatabaseDriver_' . $this->config['database']['driver'] . ' is not insantiable', 500);
        }
        $this->dbDriver = $databaseClass->newInstance($this->config['database'], $this->debug);
        
        
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
    private function setUser() {
            
        /*
         * Check session
         */
        if (isset($_SESSION) && isset($_SESSION['profile']) && isset($_SESSION['profile']['lastsessionid']) && $_SESSION['profile']['lastsessionid'] === session_id()) {
            $this->user = new RestoUser($_SESSION['profile']['email'], null, $this->dbDriver);
        }
        /*
         * HTTP user:password authentication method
         * 
         * Set PHP_AUTH_USER and PHP_AUTH_PW from HTTP_AUTHORIZATION
         * (http://stackoverflow.com/questions/3663520/php-auth-user-not-set)
         */
        else if ((isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION']) || (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && $_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $tmp = explode(':', base64_decode(substr(isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)));
            if (isset($tmp[0]) && $tmp[0] && isset($tmp[1]) && $tmp[1]) {
                $this->user = new RestoUser(trim(strtolower($tmp[0])), trim($tmp[1]), $this->dbDriver);
            }
        }
        /*
         * SSO through oAuth2 
         */
        else if ($this->config['modules']['OAuth'] && $this->config['modules']['OAuth']['activate'] === true && class_exists('OAuth')) {
            $oauth = new OAuth(null, array_merge($this->config['modules']['OAuth']['options'], array('debug' => $this->debug)));
            $userIdentifier = $oauth->authenticate(isset($_GET['access_token']) ? $_GET['access_token'] : null, isset($_GET['issuer_id']) ? $_GET['issuer_id'] : null);
            if ($userIdentifier) {
                $trimed = trim(strtolower($userIdentifier));
                if (!$this->dbDriver->userExists($trimed)) {
                    $this->dbDriver->storeUserProfile(array(
                        'email' => $trimed,
                        'activated' => true,
                        'lastsessionid' => session_id()
                    ));
                }
                else {
                    $this->dbDriver->updateUserProfile(array(
                        'email' => $trimed,
                        'lastsessionid' => session_id()
                    ));
                }
                $this->user = new RestoUser($trimed, null, $this->dbDriver);
                $_SESSION['encrypted_access_token'] = sha1($_GET['access_token']);
            }
        }
        
        /*
         * If we land here, create an unregistered user
         */
        if (!$this->user) {
            $this->user = new RestoUser(null, null, $this->dbDriver);
        }
        
    }
    
    /**
     * Call one of the output method from $object (i.e. toJSON(), toATOM(), etc.)
     * 
     * @param object $object
     * @throws Exception
     */
    private function format($object) {
        if (!isset($object) && !is_object($object)) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Invalid object', 500);
        }
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
            $this->process404();
        }
    }
    
    /**
     * Store query to database
     * 
     * @param string $serviceName
     * @param string $collectionName
     */
    private function storeQuery($serviceName, $collectionName, $featureIdentifier) {
        if ($this->config['general']['storeQuery'] === true && isset($this->user)) {
            $this->user->storeQuery($this->method, $serviceName, isset($collectionName) ? $collectionName : null, isset($featureIdentifier) ? $featureIdentifier : null, $this->context->query, $this->context->getUrl());
        }
    }
    
    /**
     * Send user activation code by email
     * 
     * @param string $to
     * @param string $sender
     * @param $userid
     * @param $activationcode
     */
    private function sendActivationMail($to, $sender, $activationUrl) {
        
        $subject = "[RESTo] Activation code for user " . $to;
        $message = "Hi,\r\n\r\n" .
                "You have registered an account to RESTo application\r\n\r\n" .
                "To validate this account, go to " . $activationUrl . "\r\n\r\n" .
                "Regards" . "\r\n\r\n" .
                "RESTo administrator";

        if (!isset($sender)) {
            $sender = 'restobot@' . $_SERVER['SERVER_NAME'];
        }
        $headers = "From: " . $_SERVER['SERVER_NAME'] . " <" . $sender . ">\r\n";
        $headers .= "Reply-To: doNotReply <" . $sender . ">\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
        if (mail($to, $subject, $message, $headers, '-f' . $sender)) {
            return true;
        }

        return false;
    }

}
