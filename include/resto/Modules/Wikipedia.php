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
    private $schema = 'gazetteer';
    
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
        $this->dbh = $this->getDatabaseHandler();   
    }

    /**
     * Run module - this function should be called by Resto.php
     * 
     * @param array $segments : route segments
     * @param array $data : POST or PUT parameters
     * 
     * @return string : result from run process in the $context->outputFormat
     */
    public function run($segments, $data = array()) {
       
        /*
         * Only GET method on 'search' route with json outputformat is accepted
         */
        if ($this->context->method !== 'GET' || count($segments) !== 0) {
            RestoLogUtil::httpError(404);
        }
        
        return $this->search($this->context->query);
        
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
        
        /*
         * Close database handler
         */
        if ($this->closeDbh) {
            pg_close($this->dbh);
        }
        
        return $result;
    }

}
