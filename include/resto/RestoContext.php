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

    /**
     * HTTP method
     */
    public $method = 'GET';
    
    /**
     * Format
     */
    public $outputFormat = 'json';
    
    /*
     *  JSON Web Token passphrase*
     * (see https://tools.ietf.org/html/draft-ietf-oauth-json-web-token-32)
     */
    private $passphrase = 'Not so secret!';
    
    /**
     * Path
     */
    public $path = '';
    
    /**
     * Query
     */
    public $query = array();
    
    /**
     * Store query
     */
    public $storeQuery = false;
   
    /**
     * Configuration
     */
    public $config = array(
        'title' => 'resto',
        'languages' => array('en'),
        'timezone' => 'Europe/Paris',
        'theme' => 'default',
        'modules' => array(),
        'mail' => array()
    );
    
    
    /**
     * Constructor
     * 
     *  $options structure :
     * 
     *      array(
     *          'dictionary' => RestoDictionary,
     *          'dbDriver' => RestoDatabaseDriver,
     *          'baseUrl' => '//localhost/resto/',
     *          'outputFormat' => 'json',
     *          'query' => array(
     *              'q' => 'Toulouse, France'
     *          ),
     *          'config' => array(
     *              'languages' => array('en', 'fr'),
     *              'timezone' => 'Europe/Paris',
     *              'theme' => 'default'
     *          )
     *      )
     * 
     * @param array $options
     * @throws Exception
     */
    public function __construct($options) {
        $this->initialize(!isset($options) || !is_array($options) ? array() : $options);
    }
    
    /**
     * Initialize context variable
     * 
     * @param array $options configuration
     */
    private function initialize($options) {
        
        /*
         * Set object values
         */
        foreach (array_values(array('baseUrl', 'dbDriver', 'debug', 'dictionary', 'method', 'outputFormat', 'passphrase', 'path', 'query', 'storeQuery')) as $key) {
            if (isset($options[$key])) {
                $this->{$key} = $options[$key];
            }
        }
        
        /*
         * Configuration options
         */
        if (isset($options['config'])) {
            foreach ($options['config'] as $key => $value) {
                $this->config[$key] = $value;
            }
        }
        
        /*
         * Set TimeZone
         */
        date_default_timezone_set($this->config['timezone']);
           
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
    
}
