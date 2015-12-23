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
 * RESTo PostgreSQL general functions
 */
class Functions_general {
    
    private $dbDriver = null;
    
    /**
     * Constructor
     * 
     * @param RestoDatabaseDriver $dbDriver
     * @throws Exception
     */
    public function __construct($dbDriver) {
        $this->dbDriver = $dbDriver;
    }

    /**
     * Check if schema $name exists within resto database
     * 
     * @param string $name - schema name
     * @return boolean
     * @throws Exception
     */
    public function schemaExists($name) {
        $query = 'SELECT 1 FROM pg_namespace WHERE nspname = \'' . pg_escape_string($name) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return !empty($results);
    }

    /**
     * Check if table $name exists within resto database
     * 
     * @param string $name - table name
     * @param string $schema - schema name
     * @return boolean
     * @throws Exception
     */
    public function tableExists($name, $schema = 'public') {
        $query = 'SELECT 1 FROM pg_tables WHERE schemaname=\'' . pg_escape_string($schema) . '\' AND tablename=\'' . pg_escape_string($name) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return !empty($results);
    }
    
    /**
     * Check if table $name is empty
     * 
     * @param string $name : table name
     * @param string $schema : schema name
     * @return boolean
     * @throws Exception
     */
    public function tableIsEmpty($name, $schema = 'public') {
        $query = 'SELECT count(*) as count FROM ' . pg_escape_string($schema) . '.' . pg_escape_string($name) . '';
        $results = $this->dbDriver->query($query);
        $result = pg_fetch_assoc($results);
        return (integer) $result['count'] === 0 ? true : false;
    }
    
    /**
     * 
     * Return keywords from database
     *
     * @param string $language : ISO A2 language code
     * 
     * @return array
     * @throws Exception
     */
    public function getKeywords($language = 'en', $types = array()) {
        $keywords = array();
        $cached = $this->dbDriver->cache->retrieve(array('getKeywords', $language, $types));
        if (isset($cached)) {
            return array('keywords' => $cached);
        }
        $results = $this->dbDriver->query('SELECT name, normalize(name) as normalized, type, value, location FROM resto.keywords WHERE ' . 'lang IN(\'' . pg_escape_string($language) . '\', \'**\')' . (count($types) > 0 ? ' AND type IN(' . join(',', $types) . ')' : ''));
        while ($result = pg_fetch_assoc($results)) {
            if (!isset($keywords[$result['type']])) {
                $keywords[$result['type']] = array();
            }
            $keywords[$result['type']][$result['normalized']] = array(
                'name' => $result['name'],
                'value' => $result['value']
            );
            if (isset($result['location'])) {
                list($isoa2, $bbox) = explode(':', $result['location']);
                $keywords[$result['type']][$result['normalized']]['bbox'] = $bbox;
                $keywords[$result['type']][$result['normalized']]['isoa2'] = $isoa2;
            }
        }
        
        /*
         * Store in cache
         */
        $this->dbDriver->cache->store(array('getKeywords', $language, $types), $keywords);
        
        return array('keywords' => $keywords);
    }
   
   
    /**
     * Returns shared link initiator email if resource is shared (checked with proof)
     * Returns false otherwise
     * 
     * @param string $resourceUrl
     * @param string $token
     * @return boolean
     */
    public function getSharedLinkInitiator($resourceUrl, $token) {
        if (!isset($resourceUrl) || !isset($token)) {
            return false;
        }
        $query = 'SELECT email FROM usermanagement.sharedlinks WHERE url=\'' . pg_escape_string($resourceUrl) . '\' AND token=\'' . pg_escape_string($token) . '\' AND validity > now()';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return !empty($results) ? $results[0]['email'] : false;
    }
    
    /**
     * Create a shared resource and return it
     * 
     * @param string $identifier
     * @param string $resourceUrl
     * @param integer $duration
     * @return array
     */
    public function createSharedLink($identifier, $resourceUrl, $duration = 86400) {
        
        if (!isset($resourceUrl) || !RestoUtil::isUrl($resourceUrl)) {
            return null;
        }
        if (!is_int($duration)) {
            $duration = 86400;
        }
        $results = $this->dbDriver->fetch($this->dbDriver->query('INSERT INTO usermanagement.sharedlinks (url, token, email, validity) VALUES (\'' . pg_escape_string($resourceUrl) . '\',\'' . (RestoUtil::encrypt(mt_rand() . microtime())) . '\',\'' . pg_escape_string($identifier) . '\',now() + ' . $duration . ' * \'1 second\'::interval) RETURNING token', 500, 'Cannot share link'));
        if (count($results) === 1) {
            return array(
                'resourceUrl' => $resourceUrl,
                'token' => $results[0]['token']
            );
        }
        
        return null;
        
    }
    
    /**
     * Save query to database
     * 
     * @param string $identifier
     * @param array $query
     * @throws Exception
     */
    public function storeQuery($identifier, $query) {
        
        $toBeSet = array(
            'email' => '\'' . pg_escape_string($identifier) . '\'',
            'method' => isset($query['method']) ? '\'' . pg_escape_string($query['method']) . '\'' : 'NULL',
            'service' => isset($query['service']) ? '\'' . pg_escape_string($query['service']) . '\'' : 'NULL',
            'collection' => isset($query['collection']) ? '\'' . pg_escape_string($query['collection']) . '\'' : 'NULL',
            'resourceid' => isset($query['resourceid']) ? '\'' . pg_escape_string($query['resourceid']) . '\'' : 'NULL',
            'query' => isset($query['query']) ? '\'' . pg_escape_string(json_encode($query['query'])) . '\'' : 'NULL',
            'querytime' => 'now()',
            'url' => isset($query['url']) ? '\'' . pg_escape_string($query['url']) . '\'' : 'NULL',
            'ip' => isset($query['ip']) ? '\'' . pg_escape_string($query['ip']) . '\'' : '127.0.0.1'  
        );
        $this->dbDriver->query('INSERT INTO usermanagement.history (' . join(',', array_keys($toBeSet)) . ') VALUES (' . join(',', array_values($toBeSet)) . ')');
        return true;
    }
    
    /**
     * Return true if token is revoked
     * 
     * @param string $token
     */
    public function isTokenRevoked($token) {
        $query = 'SELECT 1 FROM usermanagement.revokedtokens WHERE token= \'' . pg_escape_string($token) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return !empty($results);
    }

    /**
     * Revoke token
     * 
     * @param string $token
     */
    public function revokeToken($token) {
        if (isset($token) && !$this->isTokenRevoked($token)) {
            $this->dbDriver->query('INSERT INTO usermanagement.revokedtokens (token) VALUES(\'' . pg_escape_string($token) . '\')');
        }
        return true;
    }
    
    /**
     * Return area of input EPSG:4326 WKT 
     * 
     * @param string $wkt
     * @param string $unit
     */
    public function getArea($wkt, $unit = 'deg') {
        if ($unit === 'deg') {
            $result = $this->dbDriver->query('SELECT st_area(st_geometryFromText(\'' . $wkt . '\', 4326)) as area;');
        }
        else {
            $result = $this->dbDriver->query('SELECT st_area(geography(st_geometryFromText(\'' . $wkt . '\', 4326))) as area;');
        }
        while ($row = pg_fetch_assoc($result)) {
            return (integer) $row['area'];
        }
        return -1;
    }
    
}
