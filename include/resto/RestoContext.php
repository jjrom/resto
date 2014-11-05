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
     * Debug mode
     */
    public $debug = false;
    
    /**
     * Dictionary
     */
    public $dictionary;

    /**
     * Database driver
     */
    public $dbDriver;
    
    /**
     * User
     */
    public $user;
    
    /**
     * Base url
     */
    public $baseUrl;
    
    /**
     * Path
     */
    public $path;
    
    /**
     * Format
     */
    public $outputFormat;
    
    /**
     * HTTP method
     */
    public $method;
    
    /**
     * Query
     */
    public $query = array();
    
    /**
     * Configuration
     */
    public $config = array(
        'languages' => array('en'),
        'timezone' => 'Europe/Paris',
        'theme' => 'default',
        'modules' => array(),
        'ssoServices' => array()
    );
    
    /**
     * Constructor
     * 
     *  $options structure :
     * 
     *      array(
     *          'dictionary' => RestoDictionary,
     *          'dbDriver' => RestoDatabaseDriver,
     *          'user' => RestoUser,
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
        
        if (!isset($options) || !is_array($options)) {
            $options = array();
        } 
        if (!isset($options['config'])) {
            $options['config'] = array();
        }
        
        /*
         * Debug mode
         */
        $this->debug = isset($options['debug']) ? $options['debug'] : false;
        
        /*
         * Base Url - (default is '//localhost/')
         */
        $this->baseUrl = isset($options['baseUrl']) ? $options['baseUrl'] : '//localhost/';
        
        /*
         * Path
         */
        $this->path = isset($options['path']) ? $options['path'] : '';
        
        /*
         * Output format - (default is 'json')
         */
        $this->outputFormat = isset($options['outputFormat']) ? $options['outputFormat'] : 'json';
        
        /*
         * HTTP method - (default is 'GET')
         */
        $this->method = isset($options['method']) ? $options['method'] : 'GET';
        
        /*
         * Dictionary
         */
        $this->dictionary = isset($options['dictionary']) ? $options['dictionary'] : null;
        
        /*
         * Database Driver
         */
        $this->dbDriver = isset($options['dbDriver']) ? $options['dbDriver'] : null;
        
        /*
         * User
         */
        $this->user = isset($options['user']) ? $options['user'] : null; 
        
        /*
         * Query
         */
        if (isset($options['query'])) {
            $this->query = $options['query'];
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
        $paramsStr = null;
        if ($withparams) {
            foreach ($this->query as $key => $value) {
                if (is_array($value)) {
                    for ($i = count($value); $i--;) {
                        $paramsStr .= (isset($paramsStr) ? '&' : '') . urlencode($key) . '[]=' . urlencode($value[$i]);
                    }
                }
                else {
                    $paramsStr .= (isset($paramsStr) ? '&' : '') . urlencode($key) . '=' . urlencode($value);
                }
            }
        }
        return $this->baseUrl . $this->path . '.' . $this->outputFormat . (isset($paramsStr) ? '?' . $paramsStr : '');
    }
    
}
