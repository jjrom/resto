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
 * RESTo PostgreSQL general functions
 */
class Functions_general {
    
    private $dbDriver = null;
    private $dbh = null;
    
    /**
     * Constructor
     * 
     * @param array $config
     * @param RestoCache $cache
     * @throws Exception
     */
    public function __construct($dbDriver) {
        $this->dbDriver = $dbDriver;
        $this->dbh = $dbDriver->dbh;
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
        return $this->dbDriver->exists($query);
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
        return $this->dbDriver->exists($query);
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
        $query = 'SELECT 1 FROM ' . pg_escape_string($schema) . '.' . pg_escape_string($name) . '';
        return $this->dbDriver->exists($query);
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
        $results = $this->dbDriver->query('SELECT name, lower(unaccent(name)) as normalized, type, value, location FROM resto.keywords WHERE ' . 'lang IN(\'' . pg_escape_string($language) . '\', \'**\')' . (count($types) > 0 ? ' AND type IN(' . join(',', $types) . ')' : ''));
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
     * Return true if resource is shared (checked with proof)
     * 
     * @param string $resourceUrl
     * @param string $token
     * @return boolean
     */
    public function isValidSharedLink($resourceUrl, $token) {
        
        if (!isset($resourceUrl) || !isset($token)) {
            return false;
        }
        $query = 'SELECT 1 FROM usermanagement.sharedlinks WHERE url=\'' . pg_escape_string($resourceUrl) . '\' AND token=\'' . pg_escape_string($token) . '\' AND validity > now()';
        return $this->dbDriver->exists($query);
        
    }
    
    /**
     * Create a shared resource and return it
     * 
     * @param string $resourceUrl
     * @return array
     */
    public function createSharedLink($resourceUrl, $duration = 86400) {
        
        if (!isset($resourceUrl) || !RestoUtil::isUrl($resourceUrl)) {
            return null;
        }
        if (!is_int($duration)) {
            $duration = 86400;
        }
        $results = $this->dbDriver->fetch($this->dbDriver->query('INSERT INTO usermanagement.sharedlinks (url, token, validity) VALUES (\'' . pg_escape_string($resourceUrl) . '\',\'' . (sha1(mt_rand() . microtime())) . '\',now() + ' . $duration . ' * \'1 second\'::interval) RETURNING token', 500, 'Cannot share link'));
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
     * @param string $userid : User id
     * @param array $query
     * @throws Exception
     */
    public function storeQuery($userid, $query) {
        $values = array(
            $userid,
            (isset($query['method']) ? "'" . pg_escape_string($query['method']) . "'" : 'NULL'),
            (isset($query['service']) ? "'" . pg_escape_string($query['service']) . "'" : 'NULL'),
            (isset($query['collection']) ? "'" . pg_escape_string($query['collection']) . "'" : 'NULL'),
            (isset($query['resourceid']) ? "'" . pg_escape_string($query['resourceid']) . "'" : 'NULL'),
            (isset($query['query']) ? "'" . pg_escape_string(json_encode($query['query'])) . "'" : 'NULL'),
            "now()",
            (isset($query['url']) ? "'" . pg_escape_string($query['url']) . "'" : 'NULL'),
            (isset($query['ip']) ? "'" . pg_escape_string($query['ip']) . "'" : '127.0.0.1')  
        );
        $this->dbDriver->query('INSERT INTO usermanagement.history (userid,method,service,collection,resourceid,query,querytime,url,ip) VALUES (' . join(',', $values) . ')');
        return true;
    }
    
     
    
}
