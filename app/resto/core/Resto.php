<?php
/*
 * Copyright 2018 Jérôme Gasperi
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
 *  resto
 *
 *  This class should be instantiate with
 *
 *      $resto = new Resto();
 *
 * Access to resource
 * ==================
 *
 * General url template
 * --------------------
 *
 *      http(s)://host/resto/collections/{collection}.json?key1=value1&key2=value2&...
 *      \__________________/\_______________________/\____/\___________________________/
 *            baseUrl                   path         format          query
 *
 *      Where :
 *
 *          {collection} is the name of the collection (e.g. 'Charter', 'SPIRIT', etc.)
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
 *
 *
 * Returned error
 * --------------
 *
 *   - HTTP 400 'Bad Request' for invalid request
 *   - HTTP 403 'Forbiden' when accessing protected resource/service with invalid credentials
 *   - HTTP 404 'Not Found' when accessing non existing resource/service
 *   - HTTP 405 'Method Not Allowed' when accessing existing resource/service with unallowed HTTP method
 *   - HTTP 412 'Precondition failed' when existing but non activated user try to connect
 *   - HTTP 500 'Internal Server Error' for technical errors (i.e. database connection error, etc.)
 *
 * Open API
 * ========
 *
 * @OA\OpenApi(
 *   @OA\Info(
 *       title=API_INFO_TITLE,
 *       description=API_INFO_DESCRIPTION,
 *       version=RESTO_VERSION,
 *       @OA\Contact(
 *           email=API_INFO_CONTACT_EMAIL
 *       )
 *   ),
 *   @OA\Server(
 *       description=API_HOST_DESCRIPTION,
 *       url=PUBLIC_ENDPOINT
 *   )
 *  )
 */

class Resto
{

    /*
     * RestoContext
     */
    public $context;

    /*
     * RestoUser
     */
    public $user;

    /*
     * Time measurement
     */
    private $startTime;

    /*
     * CORS white list
     */
    private $corsWhiteList = array();

    /*
     * Reference to router
     */
    private $router;
    
    /**
     * Constructor
     *
     * @param array $config
     *
     */
    public function __construct($config = array())
    {
        // Initialize start of processing
        $this->startTime = microtime(true);
        
        try {
            /*
             * Set global debug mode
             */
            if (isset($config['debug'])) {
                RestoLogUtil::$debug = $config['debug'];
            }

            /*
             * Set white list for CORS
             */
            if (isset($config['corsWhiteList'])) {
                $this->corsWhiteList = $config['corsWhiteList'];
            }

            /*
             * Context
             */
            $this->context = new RestoContext($config);

            /*
             * Authenticate user
             */
            $this->user = (new SecurityUtil())->authenticate($this->context);

            /*
             * Initialize router
             */
            $this->router = new RestoRouter($this->context, $this->user);
            
            /*
             * Process route
             */
            $response = $this->getResponse();

        } catch (Exception $e) {
            /*
             * Output in error - format output as JSON in the following
             */
            $this->context->outputFormat = 'json';

            /*
             * All error codes are HTTP error codes
             */
            $responseStatus = $e->getCode();
            $response = json_encode(array('ErrorMessage' => $e->getMessage(), 'ErrorCode' => $e->getCode()), JSON_UNESCAPED_SLASHES);
        }

        $this->answer($response ?? null, $responseStatus ?? $this->context->httpStatus);
    }

    /**
     * Initialize route from HTTP method and get response from server
     */
    private function getResponse()
    {
        switch ($this->context->method) {
            case 'GET':
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $method = $this->context->method;
                break;

            case 'HEAD':
                $method = 'GET';
                break;

            case 'OPTIONS':
                return $this->setCORSHeaders();

            default:
                return RestoLogUtil::httpError(404);
        }
        
        $response = $this->router->process($method, $this->context->path, $this->context->query);

        return isset($response) ? $this->format($response) : null;
    }

    /**
     * Stream HTTP result and exit
     */
    private function answer($response, $responseStatus)
    {
        if (isset($response)) {
            /*
             * HTTP 1.1 headers
             */
            header('HTTP/1.1 ' . $responseStatus . ' ' . (RestoLogUtil::$codes[$responseStatus] ?? RestoLogUtil::$codes[200]));
            header('Pragma: no-cache');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Expires: Fri, 1 Jan 2010 00:00:00 GMT');
            header('Server-processing-time: ' . (microtime(true) - $this->startTime));
            header('Content-Type: ' . RestoUtil::$contentTypes[$this->context->outputFormat]);

            /*
             * Set headers including cross-origin resource sharing (CORS)
             * http://en.wikipedia.org/wiki/Cross-origin_resource_sharing
             */
            $this->setCORSHeaders();

            /*
             * Stream data unless HTTP HEAD is requested
             */
            if ($this->context == null || $this->context->method !== 'HEAD') {
                echo $response;
            }

            /*
             * Store query
             */
            try {
                $this->storeQuery();
            } catch (Exception $e) {
                error_log('[WARNING] Cannot store query');
            }

            /*
             * Close database handler
             *
             * [DEPRECATED] This is unecessary. Code kept in comment for discussion
             * (see https://www.postgresql.org/message-id/20633C46-A536-11D9-8FA8-000A95B03262%40pgedit.com)
             *
            if (isset($this->context) && isset($this->context->dbDriver)) {
                $this->context->dbDriver->closeDbh();
            }
            */
        }
    }

    /**
     * Call one of the output method from $object (i.e. toJSON(), toATOM(), etc.)
     *
     * @param object $object
     * @throws Exception
     */
    private function format($object)
    {
        /*
         * Case 0 - Object is null
         */
        if (!isset($object)) {
            return RestoLogUtil::httpError(400, 'Empty object');
        }
        
        $pretty = isset($this->context->query['_pretty']) ? filter_var($this->context->query['_pretty'], FILTER_VALIDATE_BOOLEAN) : false;

        /*
         * Case 1 - Object is an array
         */
        if (is_array($object)) {
            return json_encode($object, $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES);
        }

        /*
         * Case 2 - Object is an object
         */
        elseif (is_object($object)) {
            // Convert json* types in to json type
            $outputFormat = in_array($this->context->outputFormat, array('json', 'geojson', 'openapi+json')) ? 'json' : $this->context->outputFormat;
            $methodName = 'to' . strtoupper($outputFormat);
            if (method_exists(get_class($object), $methodName)) {
                return $outputFormat === 'json' ? $object->$methodName($pretty) : $object->$methodName();
            }
            return RestoLogUtil::httpError(404);
        }

        return $object;
        
        /*
         * Unknown stuff
         */
        return RestoLogUtil::httpError(400, 'Invalid object');
    }

    /**
     * Set CORS headers (HTTP OPTIONS request)
     */
    private function setCORSHeaders()
    {
        /*
         * Only set access to known servers
         */
        $httpOrigin = filter_input(INPUT_SERVER, 'HTTP_ORIGIN', FILTER_UNSAFE_RAW);
        if (isset($httpOrigin) && $this->corsIsAllowed($httpOrigin)) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 3600');
        }

        /*
         * Control header are received during OPTIONS requests
         */
        $httpRequestMethod = filter_input(INPUT_SERVER, 'HTTP_ACCESS_CONTROL_REQUEST_METHOD', FILTER_UNSAFE_RAW);
        if (isset($httpRequestMethod)) {
            header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
        }

        $httpRequestHeaders = filter_input(INPUT_SERVER, 'HTTP_ACCESS_CONTROL_REQUEST_HEADERS', FILTER_UNSAFE_RAW);
        if (isset($httpRequestHeaders)) {
            header('Access-Control-Allow-Headers: ' . $httpRequestHeaders);
        }

        return null;
    }

    /**
     * Return true if $httpOrigin is allowed to do CORS
     * If corsWhiteList is empty, then every $httpOrigin is allowed.
     * Otherwise only origin in white list are allowed
     *
     * @param string $httpOrigin
     */
    private function corsIsAllowed($httpOrigin)
    {
        /*
         * No white list => all allowed
         */
        if (!isset($this->corsWhiteList) || count($this->corsWhiteList) === 0) {
            return true;
        }

        /*
         * Nasty hack for WKWebView and iOS setting a HTTP_ORIGIN null
         * Will remove it once corrected by Telerik
         * (https://github.com/Telerik-Verified-Plugins/WKWebView/issues/59)
         */
        $toCheck = 'null';
        $url = explode('//', $httpOrigin);
        if (isset($url[1])) {
            $toCheck = explode(':', $url[1])[0];
        }
        for ($i = count($this->corsWhiteList); $i--;) {
            if ($this->corsWhiteList[$i] === $toCheck) {
                return true;
            }
        }

        return false;
    }

    /**
     * Store query
     */
    private function storeQuery()
    {
        if (!$this->context->core['storeQuery'] || !isset($this->user)) {
            return false;
        }

        return (new GeneralFunctions($this->context->dbDriver))->storeQuery($this->user->profile['id'], array(
            'path' => $this->context->path,
            'query' => RestoUtil::kvpsToQueryString($this->context->query),
            'method' => $this->context->method
        ));
    }
}
