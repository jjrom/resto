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

/**
 * 
 * Wikipedia module
 * 
 * Return a list of wikipedia articles under given location
 * 
 */
class Wikipedia extends RestoModule {
    
    /*
     * Database handler
     */
    private $dbh;
    
    /*
     * Wikipedia schema name
     */
    private $schema;
    
    /*
     * Default number of articles returned
     */
    private $nbOfResults = 10;
    
    /*
     * Maximum number of articles returned
     */
    private $maximumNbOfResults = 100;
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user) {
        
        parent::__construct($context, $user);
        
        $this->schema = 'gazetteer';
        
        $options = $this->context->config['modules'][get_class($this)];
        
        if (isset($options['database'])) {
            
            /*
             * Database schema
             */
            if (isset($options['database']['schema'])) {
                $this->schema = $options['database']['schema'];
            }
        
            /*
             * Set database handler from configuration
             */
            if (isset($options['database']['dbname'])) {
                try {
                    $dbInfo = array(
                        'dbname=' . $options['database']['dbname'],
                        'user=' . (isset($options['database']['user']) ? $options['database']['user'] : 'itag'),
                        'password=' . (isset($options['database']['password']) ? $options['database']['password'] : 'itag')
                    );
                    /*
                     * If host is specified, then TCP/IP connection is used
                     * Otherwise socket connection is used
                     */
                    if (isset($options['database']['host'])) {
                        array_push($dbInfo, 'host=' . $options['database']['host']);
                        array_push($dbInfo, 'port=' . (isset($options['database']['port']) ? $options['database']['port'] : '5432'));
                    }
                    $this->dbh = pg_connect(join(' ', $dbInfo));
                    if (!$this->dbh) {
                        throw new Exception();
                    }
                } catch (Exception $e) {
                    throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
                }
            }
            
        }
        
        /*
         * Default database handler is RestoDatabaseDriver_PostgreSQL handler
         */
        if (!isset($this->dbh)) {
            if (get_class($this->context->dbDriver) === 'RestoDabaseDriver_PostgreSQL') {
                $this->dbh = $this->context->dbDriver->getHandler();
            }
            else {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Wikipedia module only support PostgreSQL database handler', 500);
            }
        }
        
    }

    /**
     * Run module - this function should be called by Resto.php
     * 
     * @param array $params : input parameters
     * @param array $data : POST or PUT parameters
     * 
     * @return string : result from run process in the $context->outputFormat
     */
    public function run($params, $data = array()) {
       
        /*
         * Only GET method on 'search' route with json outputformat is accepted
         */
        if ($this->context->method !== 'GET' || $this->context->outputFormat !== 'json' || count($params) !== 0) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }
        
        return RestoUtil::json_format($this->search($this->context->query), true);
        
    }
    /**
     * Return wikipedia articles within a given bbox order by relevance
     *
     * @param array query
     *
     * @return array
     *
     */
    public function search($query = array()) {

        $result = array();
        
        if (!$this->dbh) {
            return $result;
        }
        
        /*
         * By default $this->nbOfResults articles are returned - and never more than $this->maximumNbOfResults
         */
        $limit = min(isset($query['limit']) ? $query['limit'] : $this->nbOfResults, $this->maximumNbOfResults);
        
        $where = '';
        if (isset($query['polygon'])) {
            $where = 'WHERE ST_intersects(geom, ST_GeomFromText(\'' . pg_escape_string($query['polygon']) . '\', 4326))';
        }
        
        /*
         * Search in input language
         */
        $entries = pg_query($this->dbh, 'SELECT title, summary FROM ' . $this->schema . '.wk WHERE lang = \'' . pg_escape_string($this->context->dictionary->language) . '\' AND wikipediaid IN (SELECT wikipediaid FROM ' . $this->schema . '.wikipedia ' . $where . ' ORDER BY relevance DESC) LIMIT ' . $limit);

        if (!$entries) {
            return $result;
        }

        /*
         * Retrieve first result
         */
        while ($entry = pg_fetch_assoc($entries)) {
            $entry['url'] = '//' . $this->context->dictionary->language . '.wikipedia.com/wiki/' . rawurlencode($entry['title']);
            $result[] = $entry;
        }

        return $result;
    }

}
