<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2013 Jérôme Gasperi <https://github.com/jjrom>
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

/*
 * 
 */
class RestoContext {
    
    /**
     * Base url
     */
    public $baseUrl = '//localhost/';
    
    /**
     * Database driver
     */
    public $dbDriver;
    
    /**
     * Debug mode
     */
    public $debug = false;
     
    /**
     * Dictionary
     */
    public $dictionary;

    /*
     * Available languages
     */
    public $languages = array('en');
    
    /**
     * HTTP method
     */
    public $method = 'GET';
    
    /**
     * Mail configuration
     */
    public $mail = array();
    
    /**
     * Available modules list/configuration
     */
    public $modules = array();
    
    /**
     * Format
     */
    public $outputFormat = 'json';
    
    /*
     *  JSON Web Token passphrase*
     * (see https://tools.ietf.org/html/draft-ietf-oauth-json-web-token-32)
     */
    private $passphrase = 'Super secret passphrase';
    
    /**
     * Path
     */
    public $path = '';
    
    /**
     * Query
     */
    public $query = array();
    
    /**
     * Reset password page url
     */
    public $resetPasswordUrl = 'http://localhost/resto2-client/#/resetPassword';
    
    /**
     * Store query
     */
    public $storeQuery = false;
   
    /*
     * Server timezone
     */
    public $timezone = 'Europe/Paris';
    
    /*
     * Application name
     */
    public $title = 'resto';
    
    /**
     * Constructor
     * 
     * @param array $config : configuration extracted from config.php file
     * @throws Exception
     */
    public function __construct($config) {
        
        /*
         * JSON Web Token is mandatory
         */
        if (!isset($config['general']['passphrase'])) {
            throw new Exception(__METHOD__ . 'Missing mandatory passphrase in configuration file', 4000);
        }
        
        /*
         * Set variables
         */
        $this->configure($config);
        
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
    public function getUrl($withparams = true) {
        return $this->baseUrl . $this->path . '.' . $this->outputFormat . (isset($withparams) ? RestoUtil::kvpsToQueryString($this->query) : '');
    }
    
    /**
     * Create a Json Web Token
     * 
     * @param string $identifier
     * @param json $jsonData
     * @return string
     */
    public function createToken($identifier, $jsonData) {
        return JWT::encode(array(
            'iss' => 'resto:server',
            'sub' => $identifier,
            'iat' => time(),
            'exp' => time() + (2 * 7 * 24 * 60 * 60), // 14 days
            'data' => $jsonData
        ), $this->passphrase);
    }
    
    /**
     * Decode and verify signed JSON Web Token
     * 
     * @param string $token
     * @return array
     */
    public function decodeJWT($token) {
        return JWT::decode($token, $this->passphrase);
    }
    
    /**
     * Initialize context variable
     * 
     * @param array $config configuration
     */
    private function initialize($config) {
        
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
        $this->setDbDriver($config['database']);
        
        /*
         * Initialize dictionary
         */
        $this->setDictionary();
        
        /*
         * Initialize server endpoint url
         */
        $this->setBaseURL($config['general']['rootEndpoint']);
        
        /*
         * Initialize query array
         */
        $this->setQuery();
        
    }
    
    /**
     * Set configuration variables
     * 
     * @param array $config configuration
     */
    private function configure($config) {
        
        /*
         * Set TimeZone
         */
        date_default_timezone_set(isset($config['timezone']) ? $config['timezone'] : 'Europe/Paris');
         
        /*
         * HTTP Method is one of GET, POST, PUT or DELETE
         */
        $this->method = strtoupper(filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING));
        
        /*
         * Debug mode
         */
        if (isset($config['general']['debug'])) {
            $this->debug = $config['general']['debug'];
        }
        
        /*
         * True to store queries within database
         */
        if (isset($config['general']['storeQuery'])) {
            $this->storeQuery = $config['general']['storeQuery'];
        }
                
        /*
         * Passphrase for JSON Web Token signing/veryfying
         */
        $this->passphrase = $config['general']['passphrase'];
        
        /*
         * Available languages
         */
        if (isset($config['general']['languages'])) {
            $this->languages = $config['general']['languages'];
        }
        
        /*
         * Mail configuration
         */
        if (isset($config['mail'])) {
            $this->mail = $config['mail'];
        }
      
        /*
         * Reset password url
         */
        if (isset($config['general']['resetPasswordUrl'])) {
            $this->resetPasswordUrl = $config['general']['resetPasswordUrl'];
        }
        
        /*
         * Title
         */
        if (isset($config['general']['title'])) {
            $this->title = $config['general']['title'];
        }
        
        /*
         * Initialize modules
         */
        $this->setModules($config['modules']);
        
    }
    
    /**
     * Get url with no parameters
     * 
     * @return string $endPoint
     */
    private function setBaseURL($endPoint) {
        $https = filter_input(INPUT_SERVER, 'HTTPS', FILTER_SANITIZE_STRING);
        $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING);
        $this->baseUrl = (isset($https) && $https === 'on' ? 'https' : 'http') . '://' . $host . $endPoint;
    }
    
    /**
     * Set query parameters from input
     */
    private function setQuery() {
        
        /*
         * Aggregate input parameters
         * 
         * Note: PUT is handled by RestoUtil::readInputData() function
         */
        $query = array();
        switch ($this->method) {
            case 'GET':
            case 'DELETE':
                $query = RestoUtil::sanitize($_GET);
                break;
            case 'POST':
                $query = array_merge($_POST, RestoUtil::sanitize($_GET));
                break;
            default:
                break;
        }
        
        /*
         * Remove unwanted parameters
         */
        if (isset($query['RESToURL'])) {
            unset($query['RESToURL']);
        }
        
        /*
         * Trim all values
         */
        if (!function_exists('trim_value')) {
            function trim_value(&$value) {
                $value = trim($value);
            }
        }
        array_walk_recursive($query, 'trim_value');
        
        $this->query = $query;
        
    }
    
    /**
     * Set dictionary from input language
     */
    private function setDictionary() {
        
        $lang = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING);
        if (!isset($lang)) {
            $lang = substr($this->getLanguage(), 0, 2);
        }
        if (!in_array($lang, $this->languages) || !class_exists('RestoDictionary_' . $lang)) {
            $lang = 'en';
        }
        
        $this->dictionary = RestoUtil::instantiate('RestoDictionary_' . $lang, array($this->dbDriver));
        
    }
    
    /**
     * Set Database driver
     * 
     * @param array $databaseConfig
     */
    private function setDbDriver($databaseConfig) {
        
        /*
         * Database
         */
        if (!class_exists('RestoDatabaseDriver_' . $databaseConfig['driver'])) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'No database driver defined', 4002);
        }
        try {
            $databaseClass = new ReflectionClass('RestoDatabaseDriver_' . $databaseConfig['driver']);
            if (!$databaseClass->isInstantiable()) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'RestoDatabaseDriver_' . $databaseConfig['driver'] . ' is not insantiable', 4003);
        }   
        
        $this->dbDriver = $databaseClass->newInstance($databaseConfig, new RestoCache(isset($databaseConfig['dircache']) ? $databaseConfig['dircache'] : null),$this->debug);      
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
     * Set activated modules
     * 
     * @param array $modulesConfig
     * 
     */
    private function setModules($modulesConfig) {
        
        $modules = array();
        
        foreach (array_keys($modulesConfig) as $moduleName) {
            
            /*
             * Only activated module are registered
             */
            if (isset($modulesConfig[$moduleName]['activate']) && $modulesConfig[$moduleName]['activate'] === true && class_exists($moduleName)) {
                
                $modules[$moduleName] = isset($modulesConfig[$moduleName]['options']) ? $modulesConfig[$moduleName]['options'] : array();
                
                /*
                 * Add route to module
                 */
                if (isset($modulesConfig[$moduleName]['route'])) {
                    $modules[$moduleName] = array_merge($modules[$moduleName], array('route' => $modulesConfig[$moduleName]['route']));
                }
                
            }
            
        }
        
        $this->modules = $modules;
        
    }
    
}
