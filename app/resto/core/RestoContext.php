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

require(realpath(dirname(__FILE__)) . '/../../vendor/php-jwt/JWT.php');

/*
 *
 */
class RestoContext
{
    /*
     * JWT Header for HS256 encryption algorithm added to convert rJWT to regular JWT
     */
    private $JWTDefaultHeader = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.";

    /**
     * Core configuration
     */
    public $core = array(

        // API endpoint
        'baseUrl' => 'http://127.0.0.1:5252',

        // Data related "planet"
        'planet' => 'earth',

        // Supported API languages
        'languages' => array('en', 'fr'),

        // Passphrase for JSON Web Token encryption
        'passphrase' => 'Super secret passphrase',

        // Shared links validity duration (in seconds)
        'sharedLinkDuration' => 86400,

        // True to store all user queries to database
        'storeQuery' => false,

        // Display catalog that have at least 'catalogMinMatch' object
        'catalogMinMatch' => 0,

        // Display collection that have at least 'collectionMinMatch' object
        'collectionMinMatch' => 0,

        // Use cache
        'useCache' => false,
        
        // Timezone for date display
        'timezone' => 'Europe/Paris',

        // JSON Web Token validity duration (in seconds) - default is 100 days
        'tokenDuration' => 8640000,

        // Permanent storage directory to store/retrieve files
        'storageInfo' => array(
            'path' => '/var/www/static',
            'endpoint' => '/static'
        ),

        // OpenSearch HTML search endpoint
        'htmlSearchEndpoint' => null,

        // True to automatically validate user on activation
        'userAutoValidation' => true,

        // True to automatically validate user on activation
        'userAutoActivation' => false,

        // True to split geometries that cross the -180/180 dateline
        'splitGeometryOnDateLine' => true,

        // Sendmail configuration
        'sendmail' => array(
            'senderName' => 'admin',
            'senderEmail' => 'restoadmin@localhost',
            'smtp' => array(
                'activate' => false,
                'host' => 'xxx.xxx.xxx',
                'port' => 465,
                'secure' => 'ssl', // one of 'ssl' or 'tls'
                'debug' => 0, // 0: no debug, 1: error and message, 2: message only
                'auth' => array(
                    'user' => 'xxx',
                    'password' => 'xxx'
                )
            )
        ),

        // Default routes superseed RestoRouter defaultRoutes
        'defaultRoutes' => null

    );

    /**
     * Default lang is english
     */
    public $lang = 'en';

    /**
     * Database driver
     */
    public $dbDriver;

    /**
     * HTTP method
     */
    public $method = 'GET';

    /**
     * Default osDescription
     */
    public $osDescription = array();

    /**
     * Default servicesInfos
     */
    public $servicesInfos = array();

    /**
     * Available addons list/configuration
     */
    public $addons = array();

    /**
     * Format
     */
    public $outputFormat = 'json';

    /**
     * Default response HTTP status
     */
    public $httpStatus = 200;

    /**
     * Path
     */
    public $path = '';

    /**
     * Query
     */
    public $query = array();

    /**
     * Keeper function
     */
    public $keeper;

    /**
     * Constructor
     *
     * @param array $config : configuration extracted from config.php file
     * @throws Exception
     */
    public function __construct($config)
    {
        /*
         * Set TimeZone
         */
        date_default_timezone_set($config['timezone'] ?? 'Europe/Paris');
        
        /*
         * HTTP input request method is one of GET, POST, PUT or DELETE
         */
        $this->method = strtoupper(filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_UNSAFE_RAW));
        
        /*
         * Set general configuration
         */
        $this->setCoreConfig($config);
        
        /*
         * Set default lang
         */
        $this->setLang(filter_input(INPUT_GET, 'lang', FILTER_UNSAFE_RAW));
        
        /*
         * Set osDescription
         */
        $this->setOsDescription($config);
        
        /*
         * Initialize addons
         */
        $this->addons = $config['addons'] ?? array();
        
        /*
         * Initialize keeper
         */
        $this->keeper = new RestoKeeper($this);

        /*
         * Initialize objects
         */
        $this->initialize($config);
    }

    /**
     * Return complete url
     *
     * @param boolean $withparams : true to return url with parameters (i.e. with ?key=value&...) / false otherwise
     */
    public function getUrl($withparams = true)
    {
        return $this->core['baseUrl'] . $this->path . (isset($withparams) ? '?' . RestoUtil::kvpsToQueryString($this->query) : '');
    }

    /**
     * Create a Json Web Token
     *
     * @param string $identifier
     * @param integer $duration
     * @param json $jsonData
     * @return string
     */
    public function createJWT($identifier, $duration, $jsonData = null)
    {
        $payload = array(
            'sub' => $identifier,
            'iat' => time(),
            'exp' => time() + $duration
        );
        if (isset($jsonData)) {
            $payload['data'] = $jsonData;
        }
        
        return JWT::encode($payload, $this->core['passphrase']);
    }

    /**
     * Create a rJWT "resto Json Web Token" i.e. a JWT without the header part
     *
     * @param string $identifier
     * @param integer $duration (in seconds)
     * @param json $jsonData
     * @return string
     */
    public function createRJWT($identifier, $duration, $jsonData = null)
    {
        $splitJWT = explode('.', $this->createJWT($identifier, $duration, $jsonData));
        return $splitJWT[1] . '.' .$splitJWT[2];
    }

    /**
     * Decode and verify signed JSON Web Token and "resto reduced JSON Web Token" (rJWT)
     *
     * rJWT is JWT without JWT HEADER part - assuming that algorithm is always HS256
     *
     * @param string $token
     * @param boolean $acceptExpired True to accept expired token
     * @return array
     */
    public function decodeJWT($token, $acceptExpired = false)
    {
        try {
            // Convert rJWT to JWT
            if (count(explode('.', $token)) == 2) {
                $token = $this->JWTDefaultHeader . $token;
            }
            
            $payload = json_decode(json_encode((array) JWT::decode($token, $this->core['passphrase'], array('HS256')), JSON_UNESCAPED_SLASHES), true);

            // Check if this token has expired
            if (isset($payload['exp']) && time() >= $payload['exp']) {
                if ($acceptExpired) {
                    error_log('[WARNING] Accept expired token for user ' . $payload['data']['id']);
                } else {
                    return null;
                }
            }

            return $payload;
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     * Retrieve from cache
     *
     * @param string $key
     */
    public function fromCache($key)
    {
        return $this->core['useCache'] ? (new RestoCache())->retrieve($key) : null;
    }

    /**
     * Store array to cache
     *
     * @param string $key
     * @param array $arr
     */
    public function toCache($key, $arr)
    {
        return $this->core['useCache'] ? (new RestoCache())->store($key, $arr) : null;
    }

    /**
     * Initialize context variable
     *
     * @param array $config configuration
     */
    private function initialize($config)
    {
        /*
         * Initialize path
         */
        $this->setPath();
        
        /*
         * Initialize output format
         */
        $this->setOutputFormat();
        
        /*
         * Initialize database driver
         */
        $this->dbDriver = new RestoDatabaseDriver($config['database'] ?? null);
        
        /*
         * Set servicesInfos
         */
        $this->servicesInfos = $config['serviceInfos'] ?? array();
        
        /*
         * Initialize query array
         */
        $this->setQuery();
    }

    /**
     * Set query parameters from input
     */
    private function setQuery()
    {
        /*
         * Aggregate input parameters
         *
         * Note: PUT is handled by Router->readInputData() function
         */
        $query = array();
        
        switch ($this->method) {
            case 'GET':
            case 'DELETE':
            case 'POST':
            case 'PUT':
                $query = RestoUtil::sanitize(filter_input_array(INPUT_GET));
                break;
            default:
                break;
        }
        
        /*
         * Remove unwanted parameters
         */
        if (isset($query['_path'])) {
            unset($query['_path']);
        }

        /*
         * Trim all values and remove empty values
         */
        foreach ($query as $key => $value) {
            $query[$key] = trim($value);
            if ($query[$key] === '') {
                unset($query[$key]);
            }
        }

        $this->query = $query;
    }

    /**
     * Set REST path from the input query "_path" param set up from nginx rewrite
     */
    private function setPath()
    {
        $restoUrl = filter_input(INPUT_GET, '_path', FILTER_UNSAFE_RAW);
        if (isset($restoUrl)) {
            $this->path = ($restoUrl !== '/' && substr($restoUrl, -1) === '/' ? substr($restoUrl, 0, strlen($restoUrl) - 1) : $restoUrl);
        }
    }

    /**
     * Set output format from suffix or HTTP_ACCEPT
     */
    private function setOutputFormat()
    {
        $this->outputFormat = $this->getPathSuffix();
        
        /*
         * Extract outputFormat from HTTP_ACCEPT
         */
        if (!isset($this->outputFormat)) {
            $httpAccept = filter_input(INPUT_SERVER, 'HTTP_ACCEPT', FILTER_UNSAFE_RAW);
            $acceptedFormats = explode(',', strtolower(str_replace(' ', '', $httpAccept)));
            foreach ($acceptedFormats as $format) {
                $weight = 1;
                if (strpos($format, ';q=')) {
                    list($format, $weight) = explode(';q=', $format);
                }
                $acceptTypes[$format] = $weight;
            }
            foreach (array('json' => 'application/json') as $key => $value) {
                if (isset($acceptTypes[$value]) && $acceptTypes[$value] !== 0) {
                    $this->outputFormat = $key;
                    break;
                }
            }

            if (!isset($this->outputFormat)) {
                $this->outputFormat = 'json';
            }
        }
    }

    /**
     * Return suffix from input url
     * @return string
     */
    private function getPathSuffix()
    {
        $splitted = explode('.', $this->path);
        $size = count($splitted);
        if ($size > 1) {
            if (array_key_exists($splitted[$size - 1], RestoUtil::$contentTypes)) {
                $suffix = $splitted[$size - 1];
                array_pop($splitted);
                $this->path = join('.', $splitted);
                return $suffix;
            }
        }

        return null;
    }

    /**
     * Set available languages and defaultLang
     *
     * @param string $lang
     */
    private function setLang($lang)
    {
        if (!isset($lang) || !in_array($lang, $this->core['languages'])) {
            $lang = 'en';
        }
        $this->lang = $lang;
    }

    /**
     * Set osDescription
     *
     * @param array $config
     */
    private function setOsDescription($config)
    {
        if (isset($config['osDescriptions'])) {
            $this->osDescription = $config['osDescriptions'][$this->lang] ?? $config['osDescriptions']['en'];
        }
    }

    /**
     * Set public config from configuration input
     *
     * @param array $config
     */
    private function setCoreConfig($config)
    {
        foreach (array_keys($this->core) as $key) {
            if (isset($config[$key])) {
                $this->core[$key] = $config[$key];
            }
        }

        // Correct storageInfo endpoint
        if (strpos($this->core['storageInfo']['endpoint'], 'http') !== 0) {
            $this->core['storageInfo']['endpoint'] = $this->core['baseUrl'] . $this->core['storageInfo']['endpoint'];
        }
    }
}
