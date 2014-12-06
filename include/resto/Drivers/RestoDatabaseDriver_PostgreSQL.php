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
 * RESTo PostgreSQL Database
 */
class RestoDatabaseDriver_PostgreSQL extends RestoDatabaseDriver {
    
    /*
     * Database handler
     */
    private $dbh;
    
    /**
     * Constructor
     * 
     * @param array $config
     * @param RestoCache $cache
     * @param boolean $debug
     * @throws Exception
     */
    public function __construct($config, $cache, $debug) {
        
        parent::__construct($config, $cache, $debug);
        
        if (!isset($config) || !is_array($config)) {
            $config = array();
        }
        try {
            $dbInfo = array(
                'dbname=' . (isset($config['dbname']) ? $config['dbname'] : 'resto2'),
                'user=' . (isset($config['user']) ? $config['user'] : 'resto'),
                'password=' . (isset($config['password']) ? $config['password'] : 'resto')
            );
            /*
             * If host is specified, then TCP/IP connection is used
             * Otherwise socket connection is used
             */
            if (isset($config['host'])) {
                $dbInfo[] = 'host=' . $config['host'];
                $dbInfo[] = 'port=' . (isset($config['port']) ? $config['port'] : '5432');
            }
            $this->dbh = pg_connect(join(' ', $dbInfo));
            if (!$this->dbh) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        
        if (isset($config['resultsPerPage'])) {
            $this->resultsPerPage = $config['resultsPerPage'];
        }
        
    }

    /**
     * List all collections
     * 
     * @return array
     * @throws Exception
     */
    public function listCollections() {
        try{
            $results = pg_query($this->dbh, 'SELECT collection FROM resto.collections');
            if (!$results) {
                throw new Exception();
            }
            $collections = array();
            while ($row = pg_fetch_assoc($results)){
                $collections[] = $row;
            }
            return $collections;
            
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
    }
    
    /**
     * List all groups
     * 
     * @return array
     * @throws Exception
     */
    public function listGroups() {
        try{
            $results = pg_query($this->dbh, 'SELECT DISTINCT groupname FROM usermanagement.users');
            if (!$results) {
                throw new Exception();
            }
            $groups = array();
            while ($row = pg_fetch_assoc($results)){
                $groups[] = $row;
            }
            return $groups;
            
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
    }
    
    /**
     * Return $sentence in lowercase and without accent
     * This function is superseed in RestoDabaseDriver_PostgreSQL and use
     * the inner function lower(unaccent($sentence)) defined in installDB.sh
     * 
     * @param string $sentence
     */
    public function normalize($sentence) {
        try {
            if (!isset($sentence)) {
                throw new Exception();
            }
            $results = pg_query($this->dbh, 'SELECT lower(unaccent(\'' . pg_escape_string($sentence) . '\')) as normalized');
            if (!$results) {
                throw new Exception();
            }
            $result = pg_fetch_assoc($results);
            return $result['normalized'];
        } catch (Exception $e) {
            return $sentence;
        }
    }
    
    /**
     * Return database handler
     * 
     * @return database handler
     */
    public function getHandler() {
        return $this->dbh;
    }
    
    /**
     * Check if collection $name exists within resto database
     * 
     * @param string $name - collection name
     * @return boolean
     * @throws Exception
     */
    public function collectionExists($name) {

        $results = pg_query($this->dbh, 'SELECT collection FROM resto.collections WHERE collection=\'' . pg_escape_string($name) . '\'');
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        while ($result = pg_fetch_assoc($results)) {
            return true;
        }

        return false;
    }
    
    /**
     * Check if feature identified by $identifier exists within {schemaName}.features table
     * 
     * @param string $identifier - feature unique identifier 
     * @param string $schema - schema name
     * @return boolean
     * @throws Exception
     */
    public function featureExists($identifier, $schema = null) {
        
        $results = pg_query($this->dbh, 'SELECT 1 FROM ' . (isset($schema) ? pg_escape_string($schema) : 'resto') . '.features WHERE identifier=\'' . pg_escape_string($identifier) . '\'');
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        while ($result = pg_fetch_assoc($results)) {
            return true;
        }

        return false;
    }
    
    /**
     * Check if user identified by $identifier exists within database
     * 
     * @param string $identifier - user email
     * 
     * @return boolean
     * @throws Exception
     */
    public function userExists($identifier) {
        
        $results = pg_query($this->dbh, 'SELECT 1 FROM usermanagement.users WHERE email=\'' . pg_escape_string($identifier) . '\'');
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        while ($result = pg_fetch_assoc($results)) {
            return true;
        }

        return false;
    }
    
    /**
     * Return true if $userid is connected (from $sessionid)
     * 
     * @param string $identifier : userid or email
     * @param string $sessionid
     * 
     * @throws Exception
     */
    public function userIsConnected($identifier, $sessionid) {
        
        if (!isset($identifier) || !isset($sessionid)) {
            return false;
        }
        $where = ctype_digit($identifier) ? 'userid=' . $identifier : 'email=\'' . pg_escape_string($identifier) . '\'';
        $results = pg_query($this->dbh, 'SELECT lastsessionid FROM usermanagement.users WHERE ' . $where . ' AND lastsessionid=\'' . pg_escape_string($sessionid) . '\'');
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        while ($result = pg_fetch_assoc($results)) {
            return true;
        }

        return false;
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
        try {
            $results = pg_query($this->dbh, 'SELECT 1 FROM usermanagement.sharedlinks WHERE url=\'' . pg_escape_string($resourceUrl) . '\' AND token=\'' . pg_escape_string($token) . '\' AND validity > now()');
            if (!$results) {
                throw new Exception();
            }
            while ($result = pg_fetch_assoc($results)) {
                return true;
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        
        return false;
        
    }
    
    /**
     * Create a shared resource and return it
     * 
     * @param string $resourceUrl
     * @return boolean
     */
    public function createSharedLink($resourceUrl, $duration = 86400) {
        
        if (!isset($resourceUrl) || !RestoUtil::isUrl($resourceUrl)) {
            return null;
        }
        if (!is_int($duration)) {
            $duration = 86400;
        }
        try {
            $results = pg_query($this->dbh, 'INSERT INTO usermanagement.sharedlinks (url, token, validity) VALUES (\'' . pg_escape_string($resourceUrl) . '\',\'' . (sha1(mt_rand() . microtime())) . '\',now() + ' . $duration . ' * \'1 second\'::interval) RETURNING token');
            if (!$results) {
                throw new Exception();
            }
            $result = pg_fetch_assoc($results);
            return $resourceUrl . (strrpos($resourceUrl, '?') === false ? '?_tk=' : '&_tk=') . $result['token'];
            
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot share link', 500);
        }
        
        return null;
        
    }
    
    /**
     * Insert feature within collection
     * 
     * @param array $elements
     * @param RestoModel $model
     * @throws Exception
     */
    public function storeFeature($collectionName, $elements, $model) {
        
        $keys = array(pg_escape_string($model->getDbKey('collection')));
        $values = array('\'' . pg_escape_string($collectionName) . '\'');
        $facets = array(
            array(
                'id' => 'collection:' . $collectionName,
                'hash' => RestoUtil::getHash('collection:' . $collectionName)
            )
        );
        try {
            
            /*
             * Initialize hashes array
             */
            $hashes = array();
                        
            for ($i = count($elements); $i--;) {
                
                /*
                 * Do not process null values
                 */
                if (!isset($elements[$i][1])) {
                    continue;
                }
                
                if (in_array($elements[$i][0], array('updated', 'published', 'collection'))) {
                    continue;
                }
                
                $keys[] = pg_escape_string($model->getDbKey($elements[$i][0]));
                
                /*
                 * Convert geometry to PostgreSQL WKT
                 */
                if ($elements[$i][0] === 'geometry') {
                    $values[] = 'ST_GeomFromText(\'' . RestoUtil::geoJSONGeometryToWKT($elements[$i][1]) . '\', 4326)';
                }
                
                /*
                 * Special case for keywords
                 * 
                 * It is assumed that $value has the same structure as
                 * the output keywords property i.e. 
                 *   
                 *      $keywords = array(
                 *          array(
                 *              array(
                 *                  "name" => name
                 *                  "id" => id, // type:value
                 *                  "parentId" => id, // parentType:parentValue
                 *                  "hash" => // unique hash for this id
                 *                  "parentHash" => // parent unique hash
                 *                  "value" => value or array()
                 *              ),
                 *              array(
                 *                  ...
                 *              )
                 *          )
                 *      );
                 * 
                 *  keyword are stored in hstore column with the following convention :
                 * 
                 *       hash => urlencode({"name":"...", "hash":"...", "parentHash":"...", "value":"..."})
                 * 
                 *  hash for each keyword is stored within the hases column for search purpose
                 */
                else if ($elements[$i][0] === 'keywords' && is_array($elements[$i][1])) {
                    foreach (array_values($elements[$i][1]) as $keyword) {
                        
                        /*
                         * Compute hash from id if not specified
                         * (this should be the case for input non iTag keywords)
                         */
                        if (isset($keyword['parentId']) && !isset($keyword['parentHash'])) {
                            $keyword['parentHash'] = RestoUtil::getHash($keyword['parentId']);
                        }
                        if (!isset($keyword['hash'])) {
                            $keyword['hash'] = RestoUtil::getHash($keyword['id'], isset($keyword['parentHash']) ? $keyword['parentHash'] : null);
                        }
                        if ($this->getFacetCategory($keyword['id'])) {
                            $facets[] = array(
                                'id' => $keyword['id'],
                                'hash' => $keyword['hash'],
                                'parentId' => isset($keyword['parentId']) ? $keyword['parentId'] : null,
                                'parentHash' => isset($keyword['parentHash']) ? $keyword['parentHash'] : null
                            );
                        }
                       
                        /*
                         * Prepare hstore value as a json string
                         */
                        $json = array(
                            'hash' => $keyword['hash']
                        );
                        foreach (array_keys($keyword) as $property) {
                            if (!in_array($property, array('id', 'parentId', 'hash'))) {
                                $json[$property] = $keyword[$property];
                            }
                        }
                        $quote = count(explode(' ', $keyword['id'])) > 1 ? '"' : '';
                        $propertyTags[] =  $quote . $keyword['id'] . $quote . '=>"' . urlencode(json_encode($json)) . '"';
                        
                        /*
                         * Store both hashes and id to hashes
                         */
                        $hashes[] = '"' . pg_escape_string($keyword['hash']) . '"';
                        
                        
                        $splitted = explode(':', $keyword['id']);
                        if ($splitted[0] !== 'landuse_details') {
                            $hashes[] = '"' . pg_escape_string($keyword['id']) . '"';
                        }
                        
                    }
                    $values[] = '\'' . pg_escape_string(join(',', $propertyTags)) . '\'';
                    
                    /*
                     * landuse keywords are also stored in dedicated
                     * table columns to speed up search requests
                     */
                    foreach (array_values($elements[$i][1]) as $keyword) {
                        list($facetType, $idId) = explode(':', $keyword['id'], 2);
                        if ($facetType === 'landuse') {
                            $keys[] = 'lu_' . $idId;
                            $values[] = $keyword['value'];
                        }
                    }
                }
                else {
                    
                    /*
                     * Special case for array
                     */
                    if ($model->getDbType($elements[$i][0]) === 'array') {
                        $values[] = '\'{' . pg_escape_string(join(',', $elements[$i][1])) . '}\'';'\'';
                    }
                    else {
                        $values[] = '\'' . pg_escape_string($elements[$i][1]) . '\'';
                    }
                    
                    $id = $elements[$i][0] . ':' . $elements[$i][1];
                    if ($this->getFacetCategory($id)) {
                        
                        /*
                         * Retrieve parent value from input elements
                         */
                        $parentType = $elements[$i][0];
                        $parentIds = array();
                        
                        /*
                         * Compute parentHash from ancestors !
                         */
                        while (isset($parentType)) {
                            $parentType = $this->getFacetParentType($parentType);
                            for ($j = count($elements); $j--;) {
                                if ($elements[$j][0] === $parentType && $elements[$j][1]) {
                                    $parentIds[] = $parentType . ':' . $elements[$j][1];
                                    break;
                                }
                            }
                        }
                        
                        if (count($parentIds) > 0) {
                            $parentHash = null;
                            for ($k = count($parentIds); $k--;) {
                                $parentHash = RestoUtil::getHash($parentIds[$k], $parentHash);
                            }
                            $hash = RestoUtil::getHash($id, $parentHash);
                            $hashes[] = '"' . pg_escape_string($hash) . '"';
                            $facets[] = array(
                                'id' => $id,
                                'hash' => $hash,
                                'parentId' => $parentIds[0],
                                'parentHash' => $parentHash
                            );
                        }
                        else {
                            $hash = RestoUtil::getHash($id);
                            $hashes[] = '"' . pg_escape_string($hash) . '"';
                            $facets[] = array(
                                'id' => $id,
                                'hash' => $hash
                            );
                        }
                        
                        /*
                         * In any case store unmodified id to hashes
                         */
                        if ($elements[$i][0] !== 'landuse_details') {
                            $hashes[] = '"' . pg_escape_string($id) . '"';
                        }
                    }
                    /*
                     * Create facet for year/month/date
                     */
                    else if ($elements[$i][0] === 'startDate' && RestoUtil::isISO8601($elements[$i][1])) {
                        $idYear = 'year:' . substr($elements[$i][1], 0, 4);
                        $hashYear = RestoUtil::getHash($idYear);
                        $idMonth = 'month:' . substr($elements[$i][1], 5, 2);
                        $hashMonth = RestoUtil::getHash($idMonth, $hashYear);
                        $idDay = 'day:' . substr($elements[$i][1], 8, 2);
                        $hashDay = RestoUtil::getHash($idDay);
                        $hashes[] = '"' . pg_escape_string($idYear) . '"';
                        $hashes[] = '"' . pg_escape_string($idMonth) . '"';
                        $hashes[] = '"' . pg_escape_string($idDay) . '"';
                        $facets[] = array(
                            'id' => $idYear,
                            'hash' => $hashYear
                        );
                        $facets[] = array(
                            'id' => $idMonth,
                            'hash' => $hashMonth,
                            'parentId' => $idYear,
                            'parentHash' => $hashYear
                        );
                        $facets[] = array(
                            'id' => $idDay,
                            'hash' => $hashDay,
                            'parentId' => $idMonth,
                            'parentHash' => $hashMonth
                        );
                    }
                }
            }
            
            /*
             * Add "updated" and "published" keywords 
             */
            $keys[] = 'updated';
            $values[] = 'now()';
            $keys[] = 'published';
            $values[] = 'now()';
            
            /*
             * Hashes column
             */
            if (count($hashes) > 0) {
                $keys[] = $model->getDbKey('hashes');
                $values[] = '\'{' . join(',', $hashes) . '}\'';
            }
            
            /*
             * Start transaction
             */
            pg_query($this->dbh, 'BEGIN');
            pg_query($this->dbh, 'INSERT INTO ' . pg_escape_string($this->getSchemaName($collectionName)) . '.features (' . join(',', $keys) . ') VALUES (' . join(',', $values) . ')');
            $this->storeFacets($facets, $collectionName);
            pg_query($this->dbh, 'COMMIT');
        } catch (Exception $e) {
            pg_query($this->dbh, 'ROLLBACK');
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Feature ' . $keys['identifier'] . ' cannot be inserted in database', 500);
        }
    }
    
    /**
     * Remove feature from database
     * 
     * @param RestoFeature $feature
     */
    public function removeFeature($feature) {
        try {
            
            pg_query($this->dbh, 'BEGIN');
            
            /*
             * Remove facets
             */
            $f = $feature->toArray();
            
            foreach($f['properties'] as $key => $value) {
                 
                /*
                 * Non keywords facets
                 */
                $id = $key . ':' . $value;
                if ($this->getFacetCategory($id)) {
                    $parentHash = null;
                    $parentType = $key;
                    $parentIds = array();
                    while (isset($parentType)) {
                        $parentType = $this->getFacetParentType($parentType);
                        foreach ($f['properties'] as $pKey => $pValue) {
                            if ($pKey === $parentType && $pValue) {
                                $parentIds[] = $parentType . ':' . $pValue;
                                break;
                            }
                        }
                    }
                    if (count($parentIds) > 0) {
                        for ($k = count($parentIds); $k--;) {
                            $parentHash = RestoUtil::getHash($parentIds[$k], $parentHash);
                        }
                    }
                    $this->removeFacet(RestoUtil::getHash($id, $parentHash), $f['properties']['collection']);
                }
                /*
                 * Keywords facets
                 */
                else if ($key === 'keywords') {
                    for ($i = count($f['properties'][$key]); $i--;) {
                        if (isset($f['properties'][$key][$i]['hash'])) {
                            $this->removeFacet($f['properties'][$key][$i]['hash'], $f['properties']['collection']);
                        }
                    }
                }
            }
            pg_query($this->dbh, 'DELETE FROM ' . (isset($feature->collection) ? $this->getSchemaName($feature->collection->name): 'resto') . '.features WHERE identifier=\'' . pg_escape_string($feature->identifier) . '\'');
            pg_query($this->dbh, 'COMMIT');    
        } catch (Exception $e) {
            pg_query($this->dbh, 'ROLLBACK'); 
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot delete feature ' . $feature->identifier, 500);
        }
    }
    
    /**
     * Return facets elements from a type for a given collection
     * 
     * Returned array structure if collectionName is set
     * 
     *      array(
     *          'type#' => array(
     *              'value1' => count1,
     *              'value2' => count2,
     *              'parent' => array(
     *                  'value3' => count3,
     *                  ...
     *              )
     *              ...
     *          ),
     *          'type2' => array(
     *              ...
     *          ),
     *          ...
     *      )
     * 
     * Or an array of array indexed by collection name if $collectionName is null
     *  
     * @param string $collectionName
     * @param array $facetFields
     * @param string $hash
     * 
     * @return array
     */
    public function getStatistics($collectionName = null, $facetFields = null) {
        
        $cached = $this->retrieveFromCache(array('getStatistics', $collectionName, $facetFields));
        if (isset($cached)) {
            return $cached;
        }
        
        /*
         * Retrieve pivot for each input facet fields
         */
        $statistics = array();
        if (isset($facetFields) && count($facetFields) > 0) {
            $pivots = $this->getFacetsPivots($collectionName, $facetFields, null);
            foreach(array_values($pivots) as $pivot) {
                if (isset($pivot) && count($pivot) > 0) {
                    for ($j = count($pivot); $j--;) {
                        if (isset($statistics[$pivot[$j]['field']][$pivot[$j]['value']])) {
                            $statistics[$pivot[$j]['field']][$pivot[$j]['value']] += (integer) $pivot[$j]['count'];
                        }
                        else {
                            $statistics[$pivot[$j]['field']][$pivot[$j]['value']] = (integer) $pivot[$j]['count'];
                        }
                    }
                }
            }
        }
        /*
         * or for all master facet fields
         */
        else {
            $fields = array();
            foreach (array_values($this->facetCategories) as $facetCategory) {
                $fields[] = $facetCategory[0];
            }
            $pivots = $this->getFacetsPivots($collectionName, $fields, null);
            foreach(array_values($pivots) as $pivot) {
                if (isset($pivot) && count($pivot) > 0) {
                    for ($j = count($pivot); $j--;) {
                        if (isset($statistics[$pivot[$j]['field']][$pivot[$j]['value']])) {
                            $statistics[$pivot[$j]['field']][$pivot[$j]['value']] += (integer) $pivot[$j]['count'];
                        }
                        else {
                            $statistics[$pivot[$j]['field']][$pivot[$j]['value']] = (integer) $pivot[$j]['count'];
                        }
                    }
                }
            }
        }
        
        return $statistics;
    }

    /**
     * Return hierarchical facets (i.e. "SOLR4 like" pivot) for a $hash for a given collection
     * 
     * Returned array structure :
     * 
     *      array(
     *          'facet_counts' => array(
     *              'facet_fields' => array(...),
     *              'facet_pivot' => array(...)
     *          )
     *      )
     * 
     * @param string $hash
     * @param string $collectionName
     * 
     * @return array
     */
    public function getHierarchicalFacets($hash, $collectionName = null) {
        
        $cached = $this->retrieveFromCache(array('getFacets', $collectionName, $hash));
        if (isset($cached)) {
            return $cached;
        }
        
        /*
         * Set facet_fields from statistics
         */
        $facet_fields = array();
        $statistics = $this->getStatistics($collectionName);
        foreach ($statistics as $facetField => $entry) {
            foreach ($entry as $value => $count) {
                $facet_fields[$facetField][] = $value;
                $facet_fields[$facetField][] = $count;
            }
        }
        /*
         * Initialize output
         */
        $hierarchicalFacets = array(
            'facet_counts' => array(
                'facet_fields' => $facet_fields
            )
        );
        
        /*
         * Get facet for $hash
         */
        $facet = $this->getFacet($hash, $collectionName);
        
        /*
         * No pivot required
         */
        if (!isset($facet)) {
            return $hierarchicalFacets;
        }
        
        /*
         * Compute pivot for children type if exists ot for itself otherwise
         */
        $childrenField = $this->getFacetChildrenType($facet['id']);
        $splitted = explode(':', $facet['id']);
        if (isset($childrenField)) {
            $pivot = $this->getFacetPivot($collectionName, $childrenField, $facet['hash']);
        }
        else {
            $pivot = $this->getFacetPivot($collectionName, $splitted[0], $facet['hash']);
        }
        
        $currentPivot = array(
            'field' => $splitted[0],
            'value' => $splitted[1],
            'count' => $facet['counter'],
            'hash' => $facet['hash'],
            'parentHash' => $facet['parentHash']
        );
        
        if (isset($pivot) && count($pivot) > 0) {
            $currentPivot['pivot'] = $pivot;
        }
        
        /*
         * Recursively process parents
         */
        $names = array($splitted[0]);
        while (true) {

            /*
             * Get parent
             */
            $parent = $this->getFacet($currentPivot['parentHash'], $collectionName);

            /*
             * No parent ? We reach the end
             */
            if (!isset($parent)) {
                break;
            }
            else {
                $splitted = explode(':', $parent['id']);
                $pivot = $currentPivot;
                $currentPivot = array(
                    'field' => $splitted[0],
                    'value' => $splitted[1],
                    'count' => $parent['counter'],
                    'hash' => $parent['hash'],
                    'parentHash' => $parent['parentHash']
                );
                $currentPivot['pivot'] = array($pivot);
                array_unshift($names, $splitted[0]);
            }
        }
        
        $hierarchicalFacets['facet_counts']['facet_pivot'] = array(
            join(',', $names) . (isset($childrenField) ? ',' . $childrenField : '') => $currentPivot
        );
        
        return $hierarchicalFacets;
    }

    /**
     * Return facet pivot (SOLR4 like)
     * 
     * @param string $collectionName
     * @param string $field
     * @param string $parentHash : parent hash
     * @return array
     */
    private function getFacetPivot($collectionName, $field, $parentHash) {
        $counters = array();
        $cached = $this->retrieveFromCache(array('getFacetPivot', $field, $parentHash));
        if (isset($cached)) {
            return $cached;
        }
        try {
            
            /*
             * Facet for one collection
             */
            if (isset($collectionName)) {
                $results = pg_query($this->dbh, 'SELECT * FROM resto.facets WHERE counter > 0 AND collection=\'' . pg_escape_string($collectionName) . '\' AND type=\'' . pg_escape_string($field) . '\'' . (isset($parentHash) ? ' AND pid=\'' . pg_escape_string($parentHash) . '\'' : '') . ' ORDER BY value');
            }
            /*
             * Facets for all collections
             */
            else {
                $results = pg_query($this->dbh, 'SELECT * FROM resto.facets WHERE counter > 0 AND type=\'' . pg_escape_string($field) . '\'' . (isset($parentHash) ? ' AND pid=\'' . pg_escape_string($parentHash) . '\'' : '') . ' ORDER BY value');
            }
            if (!$results) {
                throw new Exception();
            }
            while ($result = pg_fetch_assoc($results)) {
                $create = true;
                if (!isset($collectionName)) {
                    for ($i = count($counters); $i--;) {
                        if ($counters[$i]['value'] === $result['value']) {
                            $counters[$i]['count'] += (integer) $result['counter'];
                            $create = false;
                            break;
                        }
                    }
                }
                if ($create) {
                    $counters[] = array(
                        'field' => $field,
                        'value' => $result['value'],
                        'count' => (integer) $result['counter'],
                        'hash' => $result['uid'],
                        'parentHash' => isset($parentHash) ? $parentHash : null
                    );
                }
            }
            $this->storeInCache(array('getFacetPivot', $field, $parentHash), $counters);
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot retrieve facets', 500);
        }
        return $counters;
    }
    
    /**
     * Return facet pivots (SOLR4 like)
     * 
     * @param string $collectionName
     * @param array $fields
     * @param string $parentHash : parent hash
     * @return array
     */
    private function getFacetsPivots($collectionName, $fields, $parentHash) {
        $pivots = array();
        $cached = $this->retrieveFromCache(array('getFacetsPivots', $fields, $parentHash));
        if (isset($cached)) {
            return $cached;
        }
        try {
            
            /*
             * Facets for one collection
             */
            if (isset($collectionName)) {
                $results = pg_query($this->dbh, 'SELECT * FROM resto.facets WHERE counter > 0 AND collection=\'' . pg_escape_string($collectionName) . '\' AND type IN(\'' . join('\',\'', $fields) . '\')' . (isset($parentHash) ? ' AND pid=\'' . pg_escape_string($parentHash) . '\'' : '') . ' ORDER BY type ASC, value DESC');
            }
            /*
             * Facets for all collections
             */
            else {
                $results = pg_query($this->dbh, 'SELECT * FROM resto.facets WHERE counter > 0 AND type IN(\'' . join('\',\'', $fields) . '\')' . (isset($parentHash) ? ' AND pid=\'' . pg_escape_string($parentHash) . '\'' : '') . ' ORDER BY type ASC, value DESC');
            }
            if (!$results) {
                throw new Exception();
            }
            while ($result = pg_fetch_assoc($results)) {
                if (!isset($pivots[$result['type']])) {
                    $pivots[$result['type']] = array();
                }
                $create = true;
                if (!isset($collectionName)) {
                    for ($i = count($pivots[$result['type']]); $i--;) {
                        if ($pivots[$result['type']][$i]['value'] === $result['value']) {
                            $pivots[$result['type']][$i]['count'] += (integer) $result['counter'];
                            $create = false;
                            break;
                        }
                    }
                }
                if ($create) {
                    $pivots[$result['type']][] = array(
                        'field' => $result['type'],
                        'value' => $result['value'],
                        'count' => (integer) $result['counter'],
                        'hash' => $result['uid'],
                        'parentHash' => isset($parentHash) ? $parentHash : null
                    );
                }
            }
            $this->storeInCache(array('getFacetsPivots', $fields, $parentHash), $pivots);
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot retrieve facets', 500);
        }
        return $pivots;
    }
    
    /**
     * Store facet within database (i.e. add 1 to the counter of facet if exist)
     * 
     * !! THIS FUNCTION IS THREAD SAFE !!
     * 
     * Input facet structure :
     *      array(
     *          array(
     *              'id' => 'instrument:PHR',
     *              'hash' => '...'
     *              'parentId' => 'platform:PHR',
     *              'parentHash' => '...'
     *          ),
     *          array(
     *              'id' => 'year:2011',
     *              'hash' => 'xxxxxx'
     *          ),
     *          ...
     *      )
     * 
     * @param array $facets
     * @param type $collectionName
     */
    public function storeFacets($facets, $collectionName) {
        try {
            foreach (array_values($facets) as $facetElement) {
                
                list($ptype, $pvalue) = isset($facetElement['parentId']) ? explode(':', $facetElement['parentId'],2) : array(null, null);
                list($type, $value) = explode(':', $facetElement['id'], 2);
                
                /*
                 * Thread safe ingestion
                 */
                $arr = array(
                    '\'' . pg_escape_string($facetElement['hash']) . '\'',
                    '\'' . pg_escape_string($value) . '\'',
                    '\'' . pg_escape_string($type) . '\'',
                    isset($facetElement['parentHash']) ? '\'' . pg_escape_string($facetElement['parentHash']) . '\'' : 'NULL',
                    isset($pvalue) ? '\'' . pg_escape_string($pvalue) . '\'' : 'NULL',
                    isset($ptype) ? '\'' . pg_escape_string($ptype) . '\'' : 'NULL',
                    isset($collectionName) ? '\'' . pg_escape_string($collectionName) . '\'' : 'NULL',
                    '1'
                );
                $lock = 'LOCK TABLE resto.facets IN SHARE ROW EXCLUSIVE MODE;';
                $insert = 'INSERT INTO resto.facets (uid, value, type, pid, pvalue, ptype, collection, counter) SELECT ' . join(',', $arr);
                $upsert = 'UPDATE resto.facets SET counter = counter + 1 WHERE uid = \'' . pg_escape_string($facetElement['hash']) . '\' AND collection = \'' . pg_escape_string($collectionName) . '\'';
                pg_query($this->dbh, $lock . 'WITH upsert AS (' . $upsert . ' RETURNING *) ' . $insert . ' WHERE NOT EXISTS (SELECT * FROM upsert)');
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot insert facet for ' . $collectionName, 500);
        }
    }
    
    /**
     * Remove facet for collection i.e. decrease by one counter
     * 
     * @param string $hash
     * @param string $collectionName
     */
    public function removeFacet($hash, $collectionName) {
        try {
            if ($this->facetExists($hash, $collectionName)) {
                pg_query($this->dbh, 'UPDATE resto.facets SET counter = counter - 1 WHERE uid=\'' . pg_escape_string($hash) . '\' AND collection=\'' . pg_escape_string($collectionName) . '\'');
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot delete facet for ' . $collectionName, 500);
        }
    }
    
    /**
     * Get facet
     * 
     * @param string $hash
     * @param string $collectionName
     */
    public function getFacet($hash, $collectionName = null) {
        if (!isset($hash)) {
            return null;
        }
        try {
            $results = pg_query($this->dbh, 'SELECT * FROM resto.facets WHERE uid=\'' . pg_escape_string($hash) . '\'' . (isset($collectionName) ? ' AND collection=\'' . pg_escape_string($collectionName) . '\'' : ''));
            if (!$results) {
                throw new Exception();
            }
            $counter = 0;
            while($result = pg_fetch_assoc($results)) {
                if ($counter === 0) {
                    $output = array(
                        'id' => $result['type'] . ':' . $result['value'],
                        'hash' => $result['uid'],
                        'parentId' => isset($result['pvalue']) && isset($result['ptype']) ? $result['ptype'] . ':' . $result['pvalue'] : null,
                        'parentHash' => isset($result['pid']) ? $result['pid'] : null,
                        'collection' => $collectionName
                    );
                } 
                $counter = (integer) $result['counter'];
            }
            if (isset($output)) {
                $output['counter'] = $counter;
                return $output;
            }
            return null;
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot get facet for ' . $collectionName, 500);
        }
    }
    
    /**
     * Get facet
     * 
     * @param string $hash
     * @param string $collectionName
     * @return array();
     */
    /*
    public function getFacets($hash, $collectionName = null) {
        $facets = array();
        if (!isset($hash)) {
            return $facets;
        }
        try {
            $results = pg_query($this->dbh, 'SELECT * FROM resto.facets WHERE uid=\'' . pg_escape_string($hash) . '\'' . isset($collectionName) ? ' AND collection=\'' . pg_escape_string($collectionName) . '\'' : '');
            if (!$results) {
                throw new Exception();
            }
            while($result = pg_fetch_assoc($results)) {
                $facets[] = array(
                    'id' => $result['type'] . ':' . $result['value'],
                    'hash' => $result['uid'],
                    'parentId' => isset($result['pvalue']) && isset($result['ptype']) ? $result['ptype'] . ':' . $result['pvalue'] : null,
                    'parentHash' => isset($result['pid']) ? $result['pid'] : null,
                    'collection' => $collectionName,
                    'counter' => (integer) $result['counter']
                );
            }
            return $facets;
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot get facet for ' . $collectionName, 500);
        }
    }
    */
    /**
     * Save user profile to database i.e. create new entry if user does not exist
     * 
     * @param array $profile
     * @return array (userid, activationcode)
     * @throws exception
     */
    public function storeUserProfile($profile) {
        
        try {
            
            if (!is_array($profile) || !isset($profile['email'])) {
                throw new Exception('Cannot save user profile - invalid user identifier', 500);
            }
            if ($this->userExists($profile['email'])) {
                throw new Exception('Cannot save user profile - user already exist', 500);
            }
            $email = trim(strtolower($profile['email']));
            $values = array(
                '\'' . pg_escape_string($email) . '\'',
                '\'' . (isset($profile['password']) ? sha1($profile['password']) : str_repeat('*', 40)) . '\'',
                isset($profile['groupname']) ? '\'' . pg_escape_string($profile['groupname']) . '\'' : '\'default\'',
                isset($profile['username']) ? '\'' . pg_escape_string($profile['username']) . '\'' : 'NULL',
                isset($profile['givenname']) ? '\'' . pg_escape_string($profile['givenname']) . '\'' : 'NULL',
                isset($profile['lastname']) ? '\'' . pg_escape_string($profile['lastname']) . '\'' : 'NULL',
                '\'' . pg_escape_string(sha1($email . microtime())) . '\'',
                isset($profile['activated']) ? 'TRUE' : 'FALSE',
                'now()',
                isset($profile['lastsessionid']) ? '\'' . pg_escape_string($profile['lastsessionid']) . '\'' : 'NULL'
            );
            $results = pg_query($this->dbh, 'INSERT INTO usermanagement.users (email,password,groupname,username,givenname,lastname,activationcode,activated,registrationdate,lastsessionid) VALUES (' . join(',', $values) . ') RETURNING userid, activationcode');
            if (!$results) {
                throw new Exception('Database connection error', 500);
            }
            return pg_fetch_array($results);
        } catch (Exception $e) {
            throw new Exception(($this->debug ? ($this->debug ? __METHOD__ . ' - ' : '') . '' : '') . $e->getMessage(), $e->getCode());
        }
        
        return null;
    }
    
    /**
     * Update user profile to database
     * 
     * @param array $profile
     * @return integer (userid)
     * @throws exception
     */
    public function updateUserProfile($profile) {
        
        try {
            
            if (!is_array($profile) || !isset($profile['email'])) {
                throw new Exception('Cannot update user profile - invalid user identifier', 500);
            }
            
            /*
             * Only password, groupname, activated and lastsessionid fields can be updated
             */
            $values = array();
            if (isset($profile['password'])) {
                $values[] = 'password=\'' . sha1($profile['password']) . '\'';
            }
            if (isset($profile['groupname'])) {
                $values[] = 'groupname=\'' . pg_escape_string($profile['groupname']) . '\'';
            }
            if (isset($profile['activated'])) {
                if ($profile['activated'] === true) {
                    $values[] = 'activated=TRUE';
                }
                else if ($profile['activated'] === false) {
                    $values[] = 'activated=FALSE';
                }
            }
            if (isset($profile['lastsessionid'])) {
                $values[] = 'lastsessionid=\'' .  pg_escape_string($profile['lastsessionid']) . '\'';
            }
            $results = pg_query($this->dbh, 'UPDATE usermanagement.users SET ' . join(',', $values) . ' WHERE email=\'' . pg_escape_string(trim(strtolower($profile['email']))) .'\' RETURNING userid');
            if (!$results) {
                throw new Exception('Database connection error', 500);
            }
            $result = pg_fetch_array($results);
            if (isset($result) && $result['userid']) {
                return $result['userid'];
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . $e->getMessage(), $e->getCode());
        }
        
        return null;
    }
    
    /**
     * Disconnect user
     * 
     * @param string $identifier : can be email (or string) or integer (i.e. uid)
     */
    public function disconnectUser($identifier) {
        try {
            $where = 'email=\'' . pg_escape_string($identifier) . '\'';
            if (ctype_digit($identifier)) {
                $where = 'userid=' . $identifier;
            }
            pg_query($this->dbh, 'UPDATE usermanagement.users SET lastsessionid=NULL WHERE ' . $where);
        } catch (Exception $e) {
            return false;
        }   
        return true;    
    }
    
    /**
     * Get user profile
     * 
     * @param string $identifier : can be email (or string) or integer (i.e. uid)
     * @param string $password : if set then profile is returned only if password is valid
     * @return array : this function should return array('userid' => -1, 'groupname' => 'unregistered')
     *                 if user is not found in database
     * @throws exception
     */
    public function getUserProfile($identifier, $password = null) {
        
        /*
         * Unregistered users
         */
        if (!isset($identifier) || !$identifier || $identifier === 'unregistered') {
            return array(
                'userid' => -1,
                'groupname' => 'unregistered'
            );
        }
        
        try {
            $checkpassword = '';
            if (isset($password)) {
                $checkpassword = ' AND password=\'' . pg_escape_string(sha1($password)). '\'';
            }
            
            /*
             * If $identifier is an integer check against userid, otherwise check against email
             */
            $idColumn = 'email';
            $idValue = '\'' . pg_escape_string($identifier) . '\'';
            if (ctype_digit($identifier)) {
                $idColumn = 'userid';
                $idValue = $identifier;
            }
            $results = pg_query($this->dbh, 'SELECT userid, email, groupname, username, givenname, lastname, registrationdate, activated, lastsessionid FROM usermanagement.users WHERE ' . $idColumn . '=' . $idValue . '' . $checkpassword);
            if (!$results) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot get profile for user ' . $identifier, 500);
        }
        $user = pg_fetch_assoc($results);
        if (!$user) {
            return array(
                'userid' => -1,
                'groupname' => 'unregistered'
            );
        }
        $user['userhash'] = md5($user['email']);
        $user['activated'] = $user['activated'] === 't' ? true : false;
        $user['registrationdate'] = substr(str_replace(' ', 'T', $user['registrationdate']), 0, 19) . 'Z';
        return $user;
    }
    
    /**
     * Get users profile
     * 
     * @param type $keyword
     * @param type $min
     * @param type $number
     * @return array
     * @throws Exception
     */
    public function getUsersProfiles($keyword = null, $min = 0, $number = 50) {
        try {
            $results = pg_query($this->dbh, 'SELECT userid, email, groupname, username, givenname, lastname, registrationdate, activated, lastsessionid FROM usermanagement.users ' . (isset($keyword) ? 'WHERE email LIKE \'%'  . $keyword . '%\' OR username LIKE \'%' . $keyword .'%\' OR groupname LIKE \'%' . $keyword . '%\' OR givenname LIKE \'%' . $keyword . '%\' OR lastname LIKE \'%' . $keyword . '%\'' : '') . ' LIMIT ' . $number . ' OFFSET ' . $min);
            if (!$results) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot get profiles for users', 500);
        }
       $usersProfile = array();
        while ($user = pg_fetch_assoc($results)){
                if (!$user) {
                    return $usersProfile;
                }
                $user['activated'] = $user['activated'] === 't' ? true : false;
                $user['registrationdate'] = substr(str_replace(' ', 'T', $user['registrationdate']), 0, 19) . 'Z';
        
                $usersProfile[] = $user;
        }
        
        return $usersProfile;
    }
    
    /**
     * Return rights from user $identifier
     * 
     * @param string $identifier
     * @param string $collectionName
     * @param string $featureIdentifier
     * 
     * @return array
     * @throws exception
     */
    public function getRights($identifier, $collectionName, $featureIdentifier = null) {
        try {
            $results = pg_query($this->dbh, 'SELECT search, download, visualize, canpost as post, canput as put, candelete as delete, filters FROM usermanagement.rights WHERE emailorgroup=\'' . pg_escape_string($identifier) . '\' AND collection=\'' . pg_escape_string($collectionName) . '\' AND featureid' . (isset($featureIdentifier) ? '=\'' . pg_escape_string($featureIdentifier) . '\'' : ' IS NULL'));
            if (!$results) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot get rights for ' . $identifier, 500);
        }
        $result = pg_fetch_assoc($results);
        if (!$result) {
            return null;
        }
        if (isset($result['filters'])) {
            $result['filters'] = json_decode($result['filters'], true);
        }
        $booleans = array('search', 'download', 'visualize', 'post', 'put', 'delete');
        for ($i = count($booleans); $i--;) {
            if (isset($result[$booleans[$i]])){
                $result[$booleans[$i]] = $result[$booleans[$i]] === 't' ? true : ($result[$booleans[$i]] = $result[$booleans[$i]] === 'f' ? false : null);
            }
        }
        return $result;
    }
    
    /**
     * Get complete rights list for $identifier
     * 
     * @param string $identifier
     * @return array
     * @throws Exception
     */
    public function getRightsList($identifier) {
        try {
            $results = pg_query($this->dbh, 'SELECT collection, featureid, search, download, visualize, canpost as post, canput as put, candelete as delete, filters FROM usermanagement.rights WHERE emailorgroup=\'' . pg_escape_string($identifier) . '\'');
            if (!$results) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(__METHOD__ . 'Cannot get rights for ' . $identifier, 500);
        }
        $rights = array();
        while ($row = pg_fetch_assoc($results)){
            if (!$row) {
                return $rights;
            }
            if (isset($row['filters'])) {
                $row['filters'] = json_decode($row['filters'], true);
            }
            $booleans = array('search', 'download', 'visualize', 'post', 'put', 'delete');
            for ($i = count($booleans); $i--;) {
                $row[$booleans[$i]] = $row[$booleans[$i]] === 't' ? true : ($row[$booleans[$i]] = $row[$booleans[$i]] === 'f' ? false : null);
            }
            $rights[] = $row;
        }
        return $rights;
    }
    
    /**
     * Get complete rights for $identifier for $collectionName or for all collections
     * 
     * @param string $identifier
     * @param string $collectionName
     * @param string $featureIdentifier
     * @return array
     * @throws Exception
     */
    public function getFullRights($identifier, $collectionName = null, $featureIdentifier = null) {
        try {
            $results = pg_query($this->dbh, 'SELECT collection, featureid, search, download, visualize, canpost as post, canput as put, candelete as delete, filters FROM usermanagement.rights WHERE emailorgroup=\'' . pg_escape_string($identifier) . '\'' . (isset($collectionName) ?  ' AND collection=\'' . pg_escape_string($collectionName) . '\'' : '')  . (isset($featureIdentifier) ?  ' AND featureid=\'' . pg_escape_string($featureIdentifier) . '\'' : ''));
            if (!$results) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(__METHOD__ . 'Cannot get rights for ' . $identifier, 500);
        }
        $rights = array();
        while ($row = pg_fetch_assoc($results)){
            
            if (!$row) {
                return $rights;
            }
            if (isset($row['collection']) && !isset($rights[$row['collection']])) {
                $rights[$row['collection']] = array(
                    'features' => array()
                );
            }
            $properties = array();
            if (isset($row['filters'])) {
                $properties['filters'] = json_decode($row['filters'], true);
            }
            $booleans = array('search', 'download', 'visualize', 'post', 'put', 'delete');
            for ($i = count($booleans); $i--;) {
                $properties[$booleans[$i]] = $row[$booleans[$i]] === 't' ? true : ($properties[$booleans[$i]] = $row[$booleans[$i]] === 'f' ? false : null);
            }
            if (isset($row['featureid'])) {
                $rights[$row['collection']]['features'][$row['featureid']] = $properties;
            }
            else {
                foreach ($properties as $key => $value) {
                    $rights[$row['collection']][$key] = $value;
                }
            }
        }
       
        return $rights;
    }
    
    /**
     * Store rights to database
     *     
     *     array(
     *          'search' => // true or false
     *          'visualize' => // true or false
     *          'download' => // true or false
     *          'canpost' => // true or false
     *          'canput' => // true or false
     *          'candelete' => //true or false
     *          'filters' => array(...)
     *     )
     * 
     * @param array $rights
     * @param string $identifier
     * @param string $collectionName
     * @param string $featureIdentifier
     * 
     * @throws Exception
     */
    public function storeRights($rights, $identifier, $collectionName, $featureIdentifier = null) {
        try {
            if (!$this->collectionExists($collectionName)) {
                throw new Exception();
            }
            $values = array(
                '\'' . pg_escape_string($collectionName) . '\'',
                isset($featureIdentifier) ? '\'' . pg_escape_string($featureIdentifier) . '\'' : 'NULL',
                '\'' . pg_escape_string($identifier) . '\'',
                (isset($rights['search']) ? ($rights['search'] === 'true' ? 'TRUE' : 'FALSE') : 'NULL'),
                (isset($rights['visualize']) ? ($rights['visualize'] === 'true' ? 'TRUE' : 'FALSE') : 'NULL'),
                (isset($rights['download']) ? ($rights['download'] === 'true' ? 'TRUE' : 'FALSE') : 'NULL'),
                (isset($rights['canpost']) ? ($rights['canpost'] === 'true' ? 'TRUE' : 'FALSE') : 'NULL'),
                (isset($rights['canput']) ? ($rights['canput'] === 'true' ? 'TRUE' : 'FALSE') : 'NULL'),
                (isset($rights['candelete']) ? ($rights['candelete'] === 'true' ? 'TRUE' : 'FALSE') : 'NULL'),
                isset($rights['filters']) ? '\'' . pg_escape_string(json_encode($rights['filters'])) . '\'' : 'NULL'
            );
            $result = pg_query($this->dbh, 'INSERT INTO usermanagement.rights (collection,featureid,emailorgroup,search,visualize,download,canpost,canput,candelete,filters) VALUES (' . join(',', $values) . ')');    
            if (!$result){
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot create right', 500);
        }
    }
    
    /**
     * Update rights to database
     *     
     *     array(
     *          'search' => // true or false
     *          'visualize' => // true or false
     *          'download' => // true or false
     *          'canpost' => // true or false
     *          'canput' => // true or false
     *          'candelete' => //true or false
     *          'filters' => array(...)
     *     )
     * 
     * @param array $rights
     * @param string $identifier
     * @param string $collectionName
     * @param string $featureIdentifier
     * 
     * @throws Exception
     */
    public function updateRights($rights, $identifier, $collectionName, $featureIdentifier = null) {
        try {
            if (!$this->collectionExists($collectionName)) {
                throw new Exception();
            }
            $values = array(
                'collection=\'' . pg_escape_string($collectionName) . '\',',
                (isset($featureIdentifier) ? 'featureid=\'' . pg_escape_string($featureIdentifier) . '\',' : '') ,
                'emailorgroup=\'' . pg_escape_string($identifier) . '\'',
                (isset($rights['search']) ? ($rights['search'] === 'true' ? ',search=TRUE' : ',search=FALSE') : ''),
                (isset($rights['visualize']) ? ($rights['visualize'] === 'true' ? ',visualize=TRUE' : ',visualize=FALSE') : ''),
                (isset($rights['download']) ? ($rights['download'] === 'true' ? ',download=TRUE' : ',download=FALSE') : ''),
                (isset($rights['canpost']) ? ($rights['canpost'] === 'true' ? ',canpost=TRUE' : ',canpost=FALSE') : ''),
                (isset($rights['canput']) ? ($rights['canput'] === 'true' ? ',canput=TRUE' : ',canput=FALSE') : ''),
                (isset($rights['candelete']) ? ($rights['candelete'] === 'true' ? ',candelete=TRUE' : ',candelete=FALSE') : ''),
                (isset($rights['filters']) ? 'filters=\'' . pg_escape_string(json_encode($rights['filters'])) . '\'' : '')
            );
            $result = pg_query($this->dbh, 'UPDATE usermanagement.rights SET ' . join('', $values) . ' WHERE collection=\'' . pg_escape_string($collectionName) . '\' AND emailorgroup=\'' . pg_escape_string($identifier) . '\' AND featureid' . (isset($featureIdentifier) ? ('=\'' . $featureIdentifier . '\'') : ' IS NULL'));    
            if (!$result){
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot update right', 500);
        }
    }
    
    /**
     * Delete rights from database
     * 
     * @param string $identifier
     * @param string $collectionName
     * @param string $featureIdentifier
     * 
     * @throws Exception
     */
    public function deleteRights($identifier, $collectionName = null, $featureIdentifier = null) {
        try{
            $result = pg_query($this->dbh, 'DELETE from usermanagement.rights WHERE emailorgroup=\'' . pg_escape_string($identifier) . '\'' . (isset($collectionName) ? ' AND collection=\'' . pg_escape_string($collectionName) . '\'' : '') . (isset($featureIdentifier) ? ' AND featureid=\'' . pg_escape_string($featureIdentifier) . '\'' : ''));
            if (!$result){
                throw new Exception;
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot delete rights for ' . $identifier, 500);
        }
    }
   
    /**
     * Check if user signed collection license
     * 
     * @param string $identifier
     * @param string $collectionName
     * 
     * @return boolean
     */
    public function licenseSigned($identifier, $collectionName) {
        $results = pg_query($this->dbh, 'SELECT EXISTS(SELECT 1 FROM usermanagement.signatures WHERE email= \'' . pg_escape_string($identifier) . '\' AND collection= \'' . pg_escape_string($collectionName) . '\') AS exists');
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        $result = pg_fetch_assoc($results);
        if ($result['exists'] === 't') {
            return true;
        }
        return false;
    }
    
    /**
     * Return true if resource is within cart
     * 
     * @param string $itemId
     * @return boolean
     * @throws exception
     */
    public function isInCart($itemId) {
        if (!isset($itemId)) {
            return false;
        }
        try {
            $results = pg_query($this->dbh, 'SELECT 1 FROM usermanagement.cart WHERE itemid=\'' . pg_escape_string($itemId) . '\'');
            if (!$results) {
                throw new Exception();
            }
            while ($result = pg_fetch_assoc($results)) {
                return true;
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        
        return false;
    }
    
    /**
     * Return cart for user
     * 
     * @param string $identifier
     * @return array
     * @throws exception
     */
    public function getCartItems($identifier) {
        
        $items = array();
        
        if (!isset($identifier)) {
            return $items;
        }
        try {
            $results = pg_query($this->dbh, 'SELECT itemid, item FROM usermanagement.cart WHERE email=\'' . pg_escape_string($identifier) . '\'');
            if (!$results) {
                throw new Exception();
            }
            while ($result = pg_fetch_assoc($results)) {
                $items[$result['itemid']] = json_decode($result['item'], true);
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot get cart items', 500);
        }
        
        return $items;
    }
    
    /**
     * Add resource url to cart
     * 
     * @param string $identifier
     * @param array $item
     *   
     *   Must contain at least an 'id' entry
     *   
     * @return boolean
     * @throws exception
     */
    public function addToCart($identifier, $item = array()) {
        if (!isset($identifier) || !isset($item) || !is_array($item) || !isset($item['id'])) {
            return false;
        }
        $itemId = sha1($identifier . $item['id']);
        try {
            if ($this->isInCart($itemId)) {
                throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot add item : ' . $itemId . ' already exists', 1000);
            }
            $values = array(
                '\'' . pg_escape_string($itemId) . '\'',
                '\'' . pg_escape_string($identifier) . '\'',
                '\'' . pg_escape_string(json_encode($item)) . '\'',
                'now()'
            );
            $results = pg_query($this->dbh, 'INSERT INTO usermanagement.cart (itemid, email, item, querytime) VALUES (' . join(',', $values) . ')');
            if (!$results) {
                throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
            }
            return array($itemId => $item);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        
        return false;
    }
    
    /**
     * Update cart
     * 
     * @param string $identifier
     * @param string $itemId
     * @param array $item
     *   
     *   Must contain at least a 'url' entry
     *   
     * @return boolean
     * @throws exception
     */
    public function updateCart($identifier, $itemId, $item) {
        if (!isset($identifier) || !isset($itemId) || !isset($item) || !is_array($item) || !isset($item['url'])) {
            return false;
        }
        try {
            if (!$this->isInCart($itemId)) {
                throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot update item : ' . $itemId . ' does not exist', 1001);
            }
            $results = pg_query($this->dbh, 'UPDATE usermanagement.cart SET item = \''. pg_escape_string(json_encode($item)) . '\', querytime=now() WHERE email=\'' . pg_escape_string($identifier) . '\' AND itemid=\'' . pg_escape_string($itemId) . '\'');
            if (!$results) {
                throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        
        return false;
    }
    
    /**
     * Remove resource from cart
     * 
     * @param string $identifier
     * @param string $itemId
     * @return boolean
     * @throws exception
     */
    public function removeFromCart($identifier, $itemId) {
        if (!isset($identifier) || !isset($itemId)) {
            return false;
        }
        try {
            pg_query($this->dbh, 'DELETE FROM usermanagement.cart WHERE itemid=\'' . pg_escape_string($itemId) . '\' AND email=\'' . pg_escape_string($identifier) . '\'');
            return true;
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot remove ' . $itemId . ' from cart', 500);
        }
        
        return false;
    }
    
    /**
     * Return orders list for user
     * 
     * @param string $identifier
     * @param string $orderId
     * @return array
     * @throws exception
     */
    public function getOrders($identifier, $orderId) {
        $items = array();
        
        if (!isset($identifier)) {
            return $items;
        }
        try {
            $results = pg_query($this->dbh, 'SELECT orderid, querytime, items FROM usermanagement.orders WHERE email=\'' . pg_escape_string($identifier) . '\'' . (isset($orderId) ? ' AND orderid=\'' . pg_escape_string($orderId) . '\'' : ''));
            if (!$results) {
                throw new Exception();
            }
            while ($result = pg_fetch_assoc($results)) {
                $items[] = array(
                    'orderId' => $result['orderid'],
                    'date' => $result['querytime'],
                    'items' => json_decode($result['items'], true)
                );
            }
            if (isset($orderId) && isset($items[0])) {
                return $items[0];
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        
        return $items;
    }
    
    /**
     * Place order for user
     * 
     * @param string $identifier
     * 
     * @return array
     * @throws exception
     */
    public function placeOrder($identifier) {
        
        if (!isset($identifier)) {
            return false;
        }
        
        try {
            
            /*
             * Transaction
             */
            pg_query($this->dbh, 'BEGIN');
                
            /*
             * Do not create empty orders
             */
            $items = $this->getCartItems($identifier);
            if (!isset($items) || count($items) === 0) {
                return false;
            }
            
            $orderId = sha1($identifier . microtime());
            $values = array(
                '\'' . pg_escape_string($orderId) . '\'',
                '\'' . pg_escape_string($identifier) . '\'',
                '\'' . pg_escape_string(json_encode($items)) . '\'',
                'now()'
            );
            $results = pg_query($this->dbh, 'INSERT INTO usermanagement.orders (orderid, email, items, querytime) VALUES (' . join(',', $values) . ')');
            if (!$results) {
                throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
            }
            
            /*
             * Empty cart
             */
            pg_query($this->dbh, 'DELETE FROM usermanagement.cart WHERE email=\'' . pg_escape_string($identifier) . '\'');
            pg_query($this->dbh, 'COMMIT');
            
            return array(
                'orderId' => $orderId,
                'items' => $items
            );
        } catch (Exception $e) {
            pg_query($this->dbh, 'ROLLBACK');
            throw new Exception($e->getMessage(), $e->getCode());
        }
        
        return false;
    }
    
    /**
     * Get collection description
     * 
     * @param string $collectionName
     * @param array $facetFields
     * @return array
     * @throws Exception
     */
    public function getCollectionDescription($collectionName, $facetFields = array()) {
        
        $cached = $this->retrieveFromCache(array('getCollectionDescription', $collectionName, $facetFields));
        if (isset($cached)) {
            return $cached;
        }
        
        $collectionDescription = array();
        try {
            $description = pg_query($this->dbh, 'SELECT collection, status, model, mapping, license FROM resto.collections WHERE collection=\'' . pg_escape_string($collectionName) . '\'');
            if (!$description) {
                throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
            }
            $collection = pg_fetch_assoc($description);
            if (isset($collection['collection'])) {
                $collectionDescription['model'] = $collection['model'];
                $collectionDescription['osDescription'] = array();
                $collectionDescription['status'] = $collection['status'];
                $collectionDescription['propertiesMapping'] = json_decode($collection['mapping'], true);
                $collectionDescription['license'] = isset($collection['license']) ? json_decode($collection['license'], true) : null;
                
                /*
                 * Get OpenSearch descriptions
                 */
                $results = pg_query($this->dbh, 'SELECT * FROM resto.osdescriptions WHERE collection = \'' . pg_escape_string($collectionName) . '\'');
                while ($description = pg_fetch_assoc($results)) {
                    $collectionDescription['osDescription'][$description['lang']] = array(
                        'ShortName' => $description['shortname'],
                        'LongName' => $description['longname'],
                        'Description' => $description['description'],
                        'Tags' => $description['tags'],
                        'Developper' => $description['developper'],
                        'Contact' => $description['contact'],
                        'Query' => $description['query'],
                        'Attribution' => $description['attribution']
                    );
                }
     
                /*
                 * Get Facets
                 */
                if (isset($facetFields)) {
                    $collectionDescription['statistics'] = $this->getStatistics($collectionName, $facetFields);
                }
                /*
                 * Store in cache
                 */
                $this->storeInCache(array('getCollectionDescription', $collectionName, $facetFields), $collectionDescription);
            }
            else {
                throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        
        return $collectionDescription;
        
    }
    
    
    /**
     * Remove collection from RESTo database
     * 
     * @param RestoCollection $collection
     * @return array
     * @throws Exception
     */
    public function removeCollection($collection) {
        try {
            $results = pg_query($this->dbh, 'SELECT collection FROM resto.collections WHERE collection=\'' . pg_escape_string($collection->name) . '\'');
            if (!$results) {
                throw new Exception( ($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
            }
            
            if (pg_fetch_assoc($results)) {
                
                /*
                 * Delete (within transaction)
                 *  - entry within osdescriptions table
                 *  - entry within collections table
                 */
                pg_query($this->dbh, 'BEGIN');
                pg_query($this->dbh, 'DELETE FROM resto.osdescriptions WHERE collection=\'' . pg_escape_string($collection->name) . '\'');
                pg_query($this->dbh, 'DELETE FROM resto.collections WHERE collection=\'' . pg_escape_string($collection->name) . '\'');

                /*
                 * Do not drop schema if product table is not empty
                 */
                if ($this->schemaExists($this->getSchemaName($collection->name)) && $this->tableIsEmpty('features', $this->getSchemaName($collection->name))) {
                    pg_query($this->dbh, 'DROP SCHEMA ' . $this->getSchemaName($collection->name) . ' CASCADE');
                }

                pg_query($this->dbh, 'COMMIT');

                /*
                 * Rollback on error
                 */
                if ($this->collectionExists($collection->name)) {
                    pg_query($this->dbh, 'ROLLBACK');
                    throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot delete collection ' . $collection->name, 500);
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
      
    /**
     * Save query to database
     * 
     * @param string $userid : User id
     * @param array $query
     * @throws Exception
     */
    public function storeQuery($userid, $query) {
        try {
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
            $results = pg_query($this->dbh, 'INSERT INTO usermanagement.history (userid,method,service,collection,resourceid,query,querytime,url,ip) VALUES (' . join(',', $values) . ')');
            if (!$results) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot store query', 500);
        }
    }
    
    /**
     * Save collection to database
     * 
     * @param RestoCollection $collection
     * @throws Exception
     */
    public function storeCollection($collection) {

        try {
            
            /*
             * Prepare one column for each key entry in model
             */
            $table = array();
            foreach (array_keys($collection->model->extendedProperties) as $key) {
                if (is_array($collection->model->extendedProperties[$key])) {
                    if (isset($collection->model->extendedProperties[$key]['name']) && isset($collection->model->extendedProperties[$key]['type'])) {
                        $table[] = $collection->model->extendedProperties[$key]['name'] . ' ' . $collection->model->extendedProperties[$key]['type'] . (isset($collection->model->extendedProperties[$key]['constraint']) ? ' ' . $collection->model->extendedProperties[$key]['constraint'] : '');
                    }
                }
            }

            /*
             * Start transaction
             */
            pg_query($this->dbh, 'BEGIN');

            /*
             * Create schema if needed
             */
            if (!$this->schemaExists($this->getSchemaName($collection->name))) {
                pg_query($this->dbh, 'CREATE SCHEMA ' . $this->getSchemaName($collection->name));
                pg_query($this->dbh, 'GRANT ALL ON SCHEMA ' . $this->getSchemaName($collection->name) . ' TO resto');
            }
            /*
             * Create schema.features if needed with a CHECK on collection name
             */
            if (!$this->tableExists('features', $this->getSchemaName($collection->name))) {
                pg_query($this->dbh, 'CREATE TABLE ' . $this->getSchemaName($collection->name) . '.features (' . (count($table) > 0 ? join(',', $table) . ',' : '') . 'CHECK( collection = \'' . $collection->name . '\')) INHERITS (resto.features);');
                $indices = array(
                    'identifier' => 'btree',
                    'visible' => 'btree',
                    'platform' => 'btree',
                    'resolution' => 'btree',
                    'startDate' => 'btree',
                    'completionDate' => 'btree',
                    'cultivatedCover' => 'btree',
                    'desertCover' => 'btree',
                    'floodedCover' => 'btree',
                    'forestCover' => 'btree',
                    'herbaceousCover' => 'btree',
                    'iceCover' => 'btree',
                    'snowCover' => 'btree',
                    'urbanCover' => 'btree',
                    'waterCover' => 'btree',
                    'cloudCover' => 'btree',
                    'geometry' => 'gist',
                    'hashes' => 'gin'
                );
                foreach ($indices as $key => $indexType) {
                    if (!empty($key)) {
                        pg_query($this->dbh, 'CREATE INDEX ' . $this->getSchemaName($collection->name) . '_features_' . $collection->model->getDbKey($key) . '_idx ON ' . $this->getSchemaName($collection->name) . '.features USING ' . $indexType . ' (' . $collection->model->getDbKey($key) . ($key === 'startDate' || $key === 'completionDate' ? ' DESC)' : ')'));
                    }
                }
                pg_query($this->dbh, 'GRANT SELECT ON TABLE ' . $this->getSchemaName($collection->name) . '.features TO resto');
            }


            /*
             * Insert collection within collections table
             * 
             * CREATE TABLE resto.collections (
             *  collection          TEXT PRIMARY KEY,
             *  creationdate        TIMESTAMP,
             *  model               TEXT DEFAULT 'Default',
             *  status              TEXT DEFAULT 'public',
             *  license             TEXT,
             *  mapping             TEXT
             * );
             * 
             */
            $license = isset($collection->license) && count($collection->license) > 0 ? '\'' . pg_escape_string(json_encode($collection->license)) . '\'' : 'NULL';
            if (!$this->collectionExists($collection->name)) {
                pg_query($this->dbh, 'INSERT INTO resto.collections (collection, creationdate, model, status, license, mapping) VALUES(' . join(',', array('\'' . pg_escape_string($collection->name) . '\'', 'now()', '\'' . pg_escape_string($collection->model->name) . '\'', '\'' . pg_escape_string($collection->status) . '\'', $license, '\'' . pg_escape_string(json_encode($collection->propertiesMapping)) . '\'')) . ')');
            }
            else {
                pg_query($this->dbh, 'UPDATE resto.collections SET status = \'' . pg_escape_string($collection->status) . '\', mapping = \'' . pg_escape_string(json_encode($collection->propertiesMapping)) . '\', license=' . $license . ' WHERE collection = \'' . pg_escape_string($collection->name) . '\'');
            }

            /*
             * Insert OpenSearch descriptions within osdescriptions table
             * 
             * CREATE TABLE resto.osdescriptions (
             *  collection          VARCHAR(50),
             *  lang                VARCHAR(2),
             *  shortname           VARCHAR(50),
             *  longname            VARCHAR(255),
             *  description         TEXT,
             *  tags                TEXT,
             *  developper          VARCHAR(50),
             *  contact             VARCHAR(50),
             *  query               VARCHAR(255),
             *  attribution         VARCHAR(255)
             * );
             */
            pg_query($this->dbh, 'DELETE FROM resto.osdescriptions WHERE collection=\'' . pg_escape_string($collection->name) . '\'');

            /*
             * Insert one description per lang
             */
            foreach ($collection->osDescription as $lang => $description) {
                $osFields = array(
                    'collection',
                    'lang'
                );
                $osValues = array(
                    '\'' . pg_escape_string($collection->name) . '\'',
                    '\'' . pg_escape_string($lang) . '\''
                );
                foreach (array_keys($description) as $key) {
                    $osFields[] = strtolower($key);
                    $osValues[] = '\'' . pg_escape_string($description[$key]) . '\'';
                }
                pg_query($this->dbh, 'INSERT INTO resto.osdescriptions (' . join(',', $osFields) . ') VALUES(' . join(',', $osValues) . ')');
            }

            /*
             * Close transaction
             */
            pg_query($this->dbh, 'COMMIT');

            /*
             * Rollback on errors
             */
            if (!$this->schemaExists($this->getSchemaName($collection->name))) {
                pg_query($this->dbh, 'ROLLBACK');
                throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot create table ' . $this->getSchemaName($collection->name) . '.features', 2000);
            }
            if (!$this->collectionExists($collection->name)) {
                pg_query($this->dbh, 'ROLLBACK');
                throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot insert collection "' . $collection->name . '" in RESTo database', 2001);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * Get description of all collections including facets
     * 
     * @param array $facetFields
     * @return array
     * @throws Exception
     */
     public function getCollectionsDescriptions($facetFields = array()) {
         
        $cached = $this->retrieveFromCache(array('getCollectionsDescriptions', $facetFields));
        if (isset($cached)) {
            return $cached;
        }

        $collectionsDescriptions = array();

        try {
            $descriptions = pg_query($this->dbh, 'SELECT collection, status, model, mapping, license FROM resto.collections');
            if (!$descriptions) {
                throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . ' - Database connection error', 500);
            }
            while ($collection = pg_fetch_assoc($descriptions)) {
                $collectionsDescriptions[$collection['collection']]['model'] = $collection['model'];
                $collectionsDescriptions[$collection['collection']]['osDescription'] = array();
                $collectionsDescriptions[$collection['collection']]['status'] = $collection['status'];
                $collectionsDescriptions[$collection['collection']]['propertiesMapping'] = json_decode($collection['mapping'], true);
                $collectionsDescriptions[$collection['collection']]['license'] = isset($collection['license']) ? json_decode($collection['license'], true) : null;

                /*
                 * Get OpenSearch descriptions
                 */
                $results = pg_query($this->dbh, 'SELECT * FROM resto.osdescriptions WHERE collection = \'' . pg_escape_string($collection['collection']) . '\'');
                while ($description = pg_fetch_assoc($results)) {
                    $collectionsDescriptions[$collection['collection']]['osDescription'][$description['lang']] = array(
                        'ShortName' => $description['shortname'],
                        'LongName' => $description['longname'],
                        'Description' => $description['description'],
                        'Tags' => $description['tags'],
                        'Developper' => $description['developper'],
                        'Contact' => $description['contact'],
                        'Query' => $description['query'],
                        'Attribution' => $description['attribution']
                    );
                }

                /*
                 * Get Facets
                 */
                if (isset($facetFields)) {
                    $collectionsDescriptions[$collection['collection']]['statistics'] = $this->getStatistics($collection['collection'], $facetFields);
                }
                
            }
            /*
             * Store in cache
             */
            $this->storeInCache(array('getCollectionsDescriptions', $facetFields), $collectionsDescriptions);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $collectionsDescriptions;
    }
    
    /**
     * 
     * Get array of features descriptions
     *
     * @param array $params
     * @param RestoModel $model
     * @param string $collectionName
     * @param integer $limit
     * @param integer $offset
     * @param boolean $count : true to return the total number of results without pagination
     * 
     * @return array
     * @throws Exception
     */
    public function getFeaturesDescriptions($params, $model, $collectionName, $limit, $offset, $count = false) {

        /*
         * Check that mandatory filters are set
         */
        foreach (array_keys($model->searchFilters) as $filterName) {
            if (isset($model->searchFilters[$filterName])) {
                if (isset($model->searchFilters[$filterName]['minimum']) && $model->searchFilters[$filterName]['minimum'] === 1 && (!isset($params[$filterName]))) {
                    throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Missing mandatory filter ' . $filterName, 400);
                }
            } 
        }
        
        /*
         * Remove box filter if location filter is set
         */
        if (isset($params['geo:name'])) {
            unset($params['geo:box']);
        }
        else {
            if (isset($params['searchTerms'])) {
                $splitted = RestoUtil::splitString($params['searchTerms']);
                for ($i = count($splitted); $i--;) {
                    $arr = explode(':', $splitted[$i]);
                    if ($arr[0] === 'continent' || $arr[0] === 'country' || $arr[0] === 'region' || $arr[0] === 'state' || $arr[0] === 'city') {
                        unset($params['geo:box']);
                    }
                    if ($arr[0] === 'country') {
                        $countryName = $arr[1];
                    }
                    if ($arr[0] === 'state') {
                        $stateName = $arr[1];
                    }
                    if ($arr[0] === 'city') {
                        $cityName = $arr[1];
                    }
                }
                
                /*
                 * City exists
                 */
                if (isset($cityName)) {
                    if (isset($model->context->config['modules']['Gazetteer'])) {
                        $gazetteer = new Gazetteer($model->context, $model->user, $model->context->config['modules']['Gazetteer']);
                        $locations = $gazetteer->search(array(
                            'q' => $cityName,
                            'country' => isset($countryName) ? $countryName : null,
                            'state' => isset($stateName) ? $stateName : null
                            )
                        );
                        if (count($locations) > 0) {
                            $params['geo:name'] = $locations[0]['name'] . ($locations[0]['countryname'] !== '' ? ', ' . $locations[0]['countryname'] : '');
                            $params['geo:lon'] = $locations[0]['longitude'];
                            $params['geo:lat'] = $locations[0]['latitude'];
                        }
                    }
                }
            }
        }
        
        /*
         * Prepare WHERE clause from filters
         */
        $filters = array();
        $exclude = array(
            'count',
            'startIndex',
            'startPage',
            'language',
            'geo:name',
            'geo:lat', // linked to geo:lon
            'geo:radius' // linked to geo:lon
        );

        foreach (array_keys($model->searchFilters) as $filterName) {
            if (!in_array($filterName, $exclude)) {
                $filter = $this->prepareFilterQuery($model, $params, $filterName);
                if (isset($filter) && $filter !== '') {
                    
                    /*
                     * If one filter is invalid return an empty array
                     * without launching the request
                     */
                    if ($filter === 'INVALID') {
                        return array();
                    }
                    $filters[] = $filter;
                    
                }
            }
        }
        /*
         * TODO - get count from facet statistic and not from count() OVER()
         */
        /*
         * Add filters depending on user rights
         */
        /* TODO
        $oFilter = superImplode(' AND ', array_merge($filters, $this->getRightsFilters($this->R->getUser()->getRights($this->description['name'], 'get', 'search'))));
        */
        $oFilter = implode(' AND ', $filters);
        
        /*
         * Note that the total number of results (i.e. with no LIMIT constraint)
         * is retrieved with PostgreSQL "count(*) OVER()" technique
         */
        $query = 'SELECT ' . implode(',', $this->getSQLFields($model)) . ($count ? ', count(' . $model->getDbKey('identifier') . ') OVER() AS totalcount' : '') . ' FROM ' . (isset($collectionName) ? $this->getSchemaName($collectionName) : 'resto') . '.features' . ($oFilter ? ' WHERE ' . $oFilter : '') . ' ORDER BY startdate DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
     
        /*
         * Retrieve products from database
         */
        try {
            $results = pg_query($this->dbh, $query);
            if (!$results) {
                throw new Exception();
            }

            /*
             * Loop over results
             */
            $featuresArray = array();
            while ($result = pg_fetch_assoc($results)) {
                $featuresArray[] = $this->correctTypes($model, $result);
            }
        } catch (Exception $e) {
            return new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        
        return $featuresArray;
    }
    
    /**
     * 
     * Get feature description
     *
     * @param integer $identifier
     * @param RestoModel $model
     * @param string $collectionName
     * @param array $filters
     * 
     * @return array
     * @throws Exception
     */
    public function getFeatureDescription($identifier, $model, $collectionName = null, $filters = array()) {
        try {
           $result = pg_query($this->dbh, 'SELECT ' . implode(',', $this->getSQLFields($model, array('continents', 'countries'))) . ' FROM ' . (isset($collectionName) ? $this->getSchemaName($collectionName) : 'resto') . '.features WHERE ' . $model->getDbKey('identifier') . "='" . pg_escape_string($identifier) . "'" . (count($filters) > 0 ? ' AND ' . join(' AND ', $filters) : ''));
           if (!$result) {
               throw new Exception();
           }
           return $this->correctTypes($model, pg_fetch_assoc($result));
        } catch (Exception $e) {
            return new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }      
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
        $cached = $this->retrieveFromCache(array('getKeywords', $language, $types));
        if (isset($cached)) {
            return array('keywords' => $cached);
        }
        try {
            $results = pg_query($this->dbh, 'SELECT name, lower(unaccent(name)) as normalized, type, value, location FROM resto.keywords WHERE ' . 'lang IN(\'' . pg_escape_string($language) . '\', \'**\')' . (count($types) > 0 ? ' AND type IN(' . join(',', $types) . ')' : ''));
            if (!$results) {
                throw new Exception();
            }
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
            $this->storeInCache(array('getKeywords', $language, $types), $keywords);
        } catch (Exception $e) {
            return new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }

        return array('keywords' => $keywords);
    }
    
    /**
     * 
     * Prepare an SQL WHERE clause from input filterName
     * 
     * @param RestoModel $model (with model keys)
     * @param array $requestParams (with model keys)
     * @param string $filterName
     * @param boolean $exclusion : if true, exclude instead of include filter (WARNING ! only works for geometry and keywords)
     * 
     */
    private function prepareFilterQuery($model, $requestParams, $filterName, $exclusion = false) {

        /*
         * Get filter type
         */
        $type = $model->getDbType($model->searchFilters[$filterName]['key']);

        /*
         * Get operation
         */
        $operation = $model->searchFilters[$filterName]['operation'];

        if (isset($requestParams[$filterName]) && (is_array($requestParams[$filterName]) || $requestParams[$filterName] !== '')) {

            /*
             * Check if filter as an associated column within database
             */
            if (!$model->getDbKey($model->searchFilters[$filterName]['key'])) {
                return null;
            }

            /*
             * Check if date is valid
             */
            if ($type === 'date') {

                if (!RestoUtil::isISO8601($requestParams[$filterName])) {
                    return 'INVALID';
                }

                /*
                 * time:start
                 */
                if ($filterName === 'time:start') {
                    return $model->getDbKey($model->searchFilters['time:start']['key']) . ' >= \'' . pg_escape_string($requestParams['time:start']) . '\'';
                }
                
                /*
                 * time:end
                 */
                if ($filterName === 'time:end') {
                    return $model->getDbKey($model->searchFilters['time:end']['key']) . ' <= \'' . pg_escape_string($requestParams['time:end']) . '\'';
                }
                
                /*
                 * time:start and time:end cannot be processed separately
                 * 
                 * The following schema show cases where input (time:start/time:end) pairs 
                 * intersect (db:startDate/db:completionDate) resources 
                 * 
                 * 
                 *     db:startDate               db:completionDate
                 *          X============================X
                 *                  
                 * 
                 * Case 1 : (db:startDate) >= (time:start) && (db:startDate) <= (time:end)
                 * 
                 *   time:start      time:end
                 *       X===============X
                 * 
                 * 
                 * Case 2 : (db:startDate) <= (time:start) && (db:completionDate) >= (time:end) 
                 * 
                 *             time:start      time:end
                 *                  X===============X
                 * 
                 * 
                 * Case 3 : (db:startDate) <= (time:start) && (db:completionDate) <= (time:end) && (db:completionDate) >= (time:start)
                 * 
                 *                        time:start      time:end
                 *                            X===============X
                 *
                else if ($requestParams['time:start'] && $requestParams['time:end']) {
                    
                    //time:start and time:end are linked to two differents colums in database
                    if (($this->getModelName($this->description['searchFilters']['time:start']['key']) !== $this->getModelName($this->description['searchFilters']['time:end']['key']))) {
                        return '((' . $this->getModelName($this->description['searchFilters']['time:start']['key']) . ' >= \'' . pg_escape_string($requestParams['time:start']) . '\' AND ' . $this->getModelName($this->description['searchFilters']['time:start']['key']) . ' <= \'' . pg_escape_string($requestParams['time:end']) . '\')'
                                . ' OR (' . $this->getModelName($this->description['searchFilters']['time:start']['key']) . ' <= \'' . pg_escape_string($requestParams['time:start']) . '\' AND ' . $this->getModelName($this->description['searchFilters']['time:end']['key']) . ' >= \'' . pg_escape_string($requestParams['time:end']) . '\')'
                                . ' OR (' . $this->getModelName($this->description['searchFilters']['time:start']['key']) . ' <= \'' . pg_escape_string($requestParams['time:start']) . '\' AND ' . $this->getModelName($this->description['searchFilters']['time:end']['key']) . ' <= \'' . pg_escape_string($requestParams['time:end']) . '\' AND ' . $this->getModelName($this->description['searchFilters']['time:end']['key']) . ' >= \'' . pg_escape_string($requestParams['time:start']) . '\'))';
                    }
                    //time:start and time:end are linked to the same colum in database
                    else {
                        return '(' . $this->getModelName($this->description['searchFilters']['time:start']['key']) . ' >= \'' . pg_escape_string($requestParams['time:start']) . '\' AND ' . $this->getModelName($this->description['searchFilters']['time:end']['key']) . ' <= \'' . pg_escape_string($requestParams['time:end']) . '\')';
                    }
                }
                
                */
            }

            /*
             * Set quote to "'" for non numeric filter types
             */
            $quote = $type === 'numeric' ? '' : '\'';

            /*
             * Simple case - non 'interval' operation on value or arrays
             * 
             * if operation is '=' and last character of input value is a '%' sign then perform a like instead of an =
             */
            if ($operation === '=' || $operation === '>' || $operation === '>=' || $operation === '<' || $operation === '<=') {
                
                /*
                 * Array of values assumes a 'OR' operation
                 */
                if (!is_array($requestParams[$filterName])) {
                    $requestParams[$filterName] = array($requestParams[$filterName]);
                }
                $ors = array();
                for ($i = count($requestParams[$filterName]); $i--;) {
                    if ($operation === '=' && substr($requestParams[$filterName][$i], -1) === '%') {
                        $ors[] = $model->getDbKey($model->searchFilters[$filterName]['key']) . ' LIKE ' . $quote . pg_escape_string($requestParams[$filterName][$i]) . $quote;
                    }
                    else {
                        $ors[] = $model->getDbKey($model->searchFilters[$filterName]['key']) . ' ' . $operation . ' ' . $quote . pg_escape_string($requestParams[$filterName][$i]) . $quote;
                    }
                }
                if (count($ors) > 1) {
                    return '(' . join(' OR ', $ors) . ')';
                }
                return $ors[0];
                
            }
            /*
             * Spatial operation ST_Intersects (Input bbox or polygon)
             */
            else if ($operation === 'intersects') {
               
                /*
                 * Default bounding box is the whole earth
                 */
                if ($filterName === 'geo:box') {
                    $coords = explode(',', $requestParams[$filterName]);
                    if (count($coords) !== 4) {
                        return 'INVALID';
                    }
                    $lonmin = is_numeric($coords[0]) ? $coords[0] : -180;
                    $latmin = is_numeric($coords[1]) ? $coords[1] : -90;
                    $lonmax = is_numeric($coords[2]) ? $coords[2] : 180;
                    $latmax = is_numeric($coords[3]) ? $coords[3] : 90;
                    
                    return ($exclusion ? 'NOT ' : '') . 'ST_intersects(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ", ST_GeomFromText('" . pg_escape_string('POLYGON((' . $lonmin . ' ' . $latmin . ',' . $lonmin . ' ' . $latmax . ',' . $lonmax . ' ' . $latmax . ',' . $lonmax . ' ' . $latmin . ',' . $lonmin . ' ' . $latmin . '))') . "', 4326))";
                    
                }
                else if ($filterName === 'geo:geometry') {
                    return ($exclusion ? 'NOT ' : '') . 'ST_intersects(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ", ST_GeomFromText('" . pg_escape_string($requestParams[$filterName]) . "', 4326))";
                }
                
            }
            /*
             * Spatial operation ST_Distance (Center point + radius)
             * 
             * WARNING ! Quick benchmark show that st_distance is 100x slower than st_intersects
             * 
             * TODO - check if st_distance performance can be improved.
             * 
             */
            else if ($operation === 'distance') {
                
                $use_distance = false;
                
                /*
                 * geo:lon and geo:lat have preseance to geo:name
                 * (avoid double call to Gazetteer)
                 */
                if (isset($requestParams['geo:lon']) && isset($requestParams['geo:lat'])) {
                    $radius = RestoUtil::radiusInDegrees(isset($requestParams['geo:radius']) ? floatval($requestParams['geo:radius']) : 10000, $requestParams['geo:lat']);
                    if ($use_distance) {
                        return 'ST_distance(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ', ST_GeomFromText(\'' . pg_escape_string('POINT(' . $requestParams['geo:lon'] . ' ' . $lat = $requestParams['geo:lat'] . ')') . '\', 4326)) < ' . $radius;
                    }
                    else {
                        $lonmin = $requestParams['geo:lon'] - $radius;
                        $latmin = $requestParams['geo:lat'] - $radius;
                        $lonmax = $requestParams['geo:lon'] + $radius;
                        $latmax = $requestParams['geo:lat'] + $radius;
                        return ($exclusion ? 'NOT ' : '') . 'ST_intersects(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ", ST_GeomFromText('" . pg_escape_string('POLYGON((' . $lonmin . ' ' . $latmin . ',' . $lonmin . ' ' . $latmax . ',' . $lonmax . ' ' . $latmax . ',' . $lonmax . ' ' . $latmin . ',' . $lonmin . ' ' . $latmin . '))') . "', 4326))";
                    }
                }
            }
            /*
             * keywords case - i.e. searchTerms
             * 
             * !! IMPORTANT NOTE !!
             * 
             *      keywords are stored in hstore 'keywords' column
             *      BUT searches are done on the array 'hashes' column
             * 
             */
            else if ($operation === 'keywords') {
                
                $terms = array();
                $splitted = RestoUtil::splitString($requestParams[$filterName]);
                $key = $model->getDbKey($model->searchFilters[$filterName]['key']);
                $filters = array(
                    'with' => array(),
                    'without' => array()
                );
                for ($i = 0, $l = count($splitted); $i < $l; $i++) {

                    /*
                     * If term as a '-' prefix then performs a "NOT keyword"
                     * If keyword contain a + then transform it into a ' '
                     */
                    $s = ($exclusion ? '-' : '') . $splitted[$i];
                    $not = false;
                    if (substr($s, 0, 1) === '-') {
                        $not = true;
                        $s = substr($s, 1);
                    }

                    /*
                     * Keywords structure is "type:value"
                     */
                    $typeAndValue = explode(':', $s);
                    if (count($typeAndValue) !== 2) {
                        return 'INVALID';
                    }
                    
                    /*
                     * Landuse columns are NUMERIC columns
                     */
                    if ($typeAndValue[0] === 'landuse') {
                        if (in_array($typeAndValue[1], array('cultivated', 'desert', 'flooded', 'forest','herbaceous','snow','ice','urban','water'))) {
                            $terms[] = 'lu_' . $typeAndValue[1] . ($not ? ' = ' : ' > ') . '0';
                        }
                        else {
                            return 'INVALID';
                        }
                    }
                    /*
                     * TODO - need to be rewritten (see getFeaturesDescriptions)
                     */
                    else if ($typeAndValue[0] === 'city') {
                        continue;
                    }
                    /*
                     * Everything other types are stored within hashes column
                     * If input keyword is a hash leave value unchanged
                     * 
                     * Structure is :
                     * 
                     *      type:id or type:id1|id2|id3|.etc.
                     * 
                     * In second case, '|' is understood as "OR"
                     */
                    else {
                        $ors = array();
                        $arr = explode('|', $typeAndValue[1]);
                        if (count($arr) > 1) {
                            for ($j = count($arr); $j--;) {
                                $ors[] = $key . " @> ARRAY['" . pg_escape_string($typeAndValue[0] !== 'hash' ? $typeAndValue[0] . ':' . $arr[$j] : $arr[$j]) . "']";
                            }
                            if (count($ors) > 1) {
                                $terms[] = ($not ? 'NOT (' : '(') . join(' OR ', $ors) . ')';
                            }
                        }
                        else {
                            $filters[$not ? 'without' : 'with'][] = "'" . pg_escape_string($typeAndValue[0] !== 'hash' ? $s : $typeAndValue[1]) . "'";
                        }
                    }
                }
                
                if (count($filters['without']) > 0) {
                    $terms[] = 'NOT ' . $key . " @> ARRAY[" . join(',', $filters['without']) . "]";
                }
                if (count($filters['with']) > 0) {
                    $terms[] = $key . " @> ARRAY[" . join(',', $filters['with']) . "]";
                }
                
                return join(' AND ', $terms);
                
            }

            /*
             * Interval case 
             * 
             *  If
             *      A is the value of $this->request['params'][$this->description['searchFilters'][$filterName]['osKey']]
             *  Then
             *      A = n1 then returns value = n1
             *      A = {n1,n2} then returns  value = n1 or value = n2
             *      A = [n1,n2] then returns  n1 ≤ value ≤ n2
             *      A = [n1,n2[ then returns  n1 ≤ value < n2
             *      A = ]n1,n2[ then returns  n1 < value < n2
             *      A = ]n1 then returns n1 < value
             *      A = [n1 then returns  n1 ≤ value
             *      A = n1[ then returns value < n2
             *      A = n1] then returns value ≤ n2 
             */
            else if ($operation === 'interval') {

                $values = explode(',', $requestParams[$filterName]);

                /*
                 * No ',' present i.e. simple equality or non closed interval
                 */
                if (count($values) === 1) {
                    
                    /* 
                     * Non closed interval
                     */
                    $op1 = substr(trim($values[0]), 0, 1);
                    $val1 = substr(trim($values[0]), 1);
                    if ($op1 === '[' || $op1 === ']') {
                        return $model->getDbKey($model->searchFilters[$filterName]['key']) . ($op1 === '[' ? ' >= ' : ' > ') . pg_escape_string($val1);
                    }
                    $op2 = substr(trim($values[0]), -1);
                    $val2 = substr(trim($values[0]), 0, strlen(trim($values[0])) - 1);
                    if ($op2 === '[' || $op2 === ']') {
                        return $model->getDbKey($model->searchFilters[$filterName]['key']) . ($op2 === ']' ? ' <= ' : ' < ') . pg_escape_string($val2);
                    }
                    /*
                     * Simple equality
                     */
                    return $model->getDbKey($model->searchFilters[$filterName]['key']) . ' = ' . pg_escape_string($requestParams[$filterName]);
                }
                /*
                 * Two values
                 */
                else if (count($values) === 2) {

                    /*
                     * First and last characters give operators
                     */
                    $op1 = substr(trim($values[0]), 0, 1);
                    $val1 = substr(trim($values[0]), 1);
                    $op2 = substr(trim($values[1]), -1);
                    $val2 = substr(trim($values[1]), 0, strlen(trim($values[1])) - 1);

                    /*
                     * A = {n1,n2} then returns  = n1 or = n2
                     */
                    if ($op1 === '{' && $op2 === '}') {
                        return '(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ' = ' . pg_escape_string($val1) . ' OR ' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ' = ' . pg_escape_string($val2) . ')';
                    }

                    /*
                     * Other cases i.e. 
                     * A = [n1,n2] then returns <= n1 and <= n2
                     * A = [n1,n2[ then returns <= n1 and B < n2
                     * A = ]n1,n2[ then returns < n1 and B < n2
                     * 
                     */
                    if (($op1 === '[' || $op1 === ']') && ($op2 === '[' || $op2 === ']')) {
                        return $model->getDbKey($model->searchFilters[$filterName]['key']) . ($op1 === '[' ? ' >= ' : ' > ') . pg_escape_string($val1) . ' AND ' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ($op2 === ']' ? ' <= ' : ' < ') . pg_escape_string($val2);
                    }
                }
            }
        }

        return null;
    }
    
    /**
     * Return an array of database column names
     * 
     * @param RestoModel $model
     * @param array $excluded : list of fields to exclude from request
     * @return array
     */
    private function getSQLFields($model, $excluded = array()) {

        /*
         * Get Controller database fields
         */
        $columns = Array();
        foreach (array_keys($model->properties) as $key) {

            /*
             * Avoid null value
             */
            if (!isset($model->properties[$key])) {
                continue;
            }
            
            /*
             * Do not return excluded fields
             */
            if (in_array($key, $excluded)) {
                continue;
            }
            
            $v = is_array($model->properties[$key]) ? $model->properties[$key]['name'] : $model->properties[$key];
            
            /*
             * Force geometry element to be retrieved as GeoJSON
             * Retrieve also BoundinBox in EPSG:4326
             */
            if ($key === 'geometry') {
                $columns[] = 'ST_AsGeoJSON(' . $v . ') AS ' . $key;
                $columns[] = 'Box2D(' . $v . ') AS bbox4326';
            }
            /*
             * Other fields are retrieved normally
             */
            else {
                $columns[] = $v . ' AS "' . $key . '"';
            }
        }

        return $columns;
        
    }
    
    /**
     * 
     * Convert an array of strings to the correct type
     * (Since pg_fetch_assoc returns only strings whatever the PostgreSQL type
     * we need to cast each feature properties to the right type)
     * 
     * @param RestoModel $model
     * @param Array $pgResult : pg_fetch_assoc result
     * @return array
     */
    private function correctTypes($model, $pgResult) {
        if (!isset($pgResult) || !is_array($pgResult)) {
            return null;
        }
        foreach ($pgResult as $key => $value) {
            
            /*
             * Special keys
             */
            if ($key === 'bbox4326') {
                $pgResult[$key] = str_replace(' ', ',', substr(substr($pgResult[$key], 0, strlen($pgResult[$key]) - 1), 4));
                      
               /*
                * Compute EPSG:3857 bbox
                */
               $pgResult['bbox3857'] = RestoUtil::bboxToMercator($pgResult[$key]);
            
            }
            else if ($key === 'totalcount') {
                $pgResult[$key] = (integer) $value;
            }
            else {
                switch($model->getDbType($key)) {
                    case 'integer':
                        $pgResult[$key] = (integer) $value;
                        break;
                    case 'float':
                        $pgResult[$key] = (float) $value;
                        break;
                    /*
                     * PostgreSQL returns date as YYYY-MM-DD HH:MM:SS
                     * Replace ' ' by 'T', add trailing 'Z' and remove microseconds to make a valid ISO8601 date
                     */
                    case 'date':
                        if (isset($value)) {
                            $pgResult[$key] = substr(str_replace(' ', 'T', $value), 0, 19) . 'Z';
                        }
                        else {
                            $pgResult[$key] = null;
                        }
                        break;
                    /*
                     * PostgreSQL returns ST_AsGeoJSON(geometry) 
                     */
                    case 'geometry':
                        $pgResult[$key] = json_decode($value, true);
                        break;
                    case 'hstore':
                        $pgResult[$key] = $this->hstoreToKeywords($value, $model->context->baseUrl . 'api/collections' . (isset($pgResult['collection']) ? '/' . $pgResult['collection'] : '' ) . '/search.json', $model);
                        break;
                    case 'array':
                        $pgResult[$key] = explode(',', substr($value, 1, -1));
                        break;
                    default:
                        break;
                }
            }
        }
        
        return $pgResult;
    }
    
    /**
     * 
     * Return keyword array assuming an input hstore $string 
     * 
     * Note : $string format is "type:name" => urlencode(json)
     *
     *      e.g. "continent:oceania"=>"%7B%22hash%22%3A%2262f4365c66c1f64%22%7D", "country:australia"=>"%7B%22hash%22%3A%228f36daace0ea948%22%2C%22parentHash%22%3A%2262f4365c66c1f64%22%2C%22value%22%3A100%7D"
     * 
     * 
     * Structure of output is 
     *      array(
     *          "id" => // Keyword id (optional)
     *          "type" => // Keyword type
     *          "value" => // Keyword value if it make sense
     *          "href" => // RESTo search url to get keyword
     *      )
     * 
     * @param string $hstore
     * @param string $url : Base url for setting href links
     * @param RestoModel $model
     * @return array
     */
    private function hstoreToKeywords($hstore, $url, $model) {
        
        if (!isset($hstore)) {
            return null;
        }
        
        $json = json_decode('{' . str_replace('}"', '}', str_replace('\"', '"', str_replace('"{', '{', str_replace('"=>"', '":"', $hstore)))) . '}', true);
        
        if (!isset($json) || !is_array($json)) {
            return null;
        }
        
        $keywords = array();
        foreach ($json as $key => $value) {

            /*
             * $key format is "type:id"
             */
            list($type, $id) = explode(':', $key, 2);
            $hrefKey = $key;
            
            /*
             * Do not display landuse_details
             */
            if ($type === 'landuse_details') {
                continue;
            }

            /*
             * Value format is urlencode(json)
             */
            $properties = json_decode(urldecode($value), true); 
            if (!isset($properties['name'])) {
                $properties['name'] = trim($model->context->dictionary->getKeywordFromValue($id, $type));
                if (!isset($properties['name'])) {
                    $properties['name'] = ucwords($id);
                }
                $hrefKey = $properties['name'];
            }
            $keywords[] = array(
                'name' => isset($properties['name']) && $properties['name'] !== '' ? $properties['name'] : $key,
                'id' => $key,
                'href' => RestoUtil::updateUrl($url, array($model->searchFilters['language']['osKey'] => $model->context->dictionary->language,  $model->searchFilters['searchTerms']['osKey'] => count(explode(' ', $hrefKey)) > 1 ? '"'. $hrefKey . '"' : $hrefKey))
            );
            foreach (array_keys($properties) as $property) {
                if (!in_array($property, array('name', 'id'))) {
                    $keywords[count($keywords) - 1][$property] = $properties[$property];
                }
            }
        }

        return $keywords;
    }
    
    /**
     * Check if facet exists
     * 
     * @param string $hash - facet hash
     * @param string $collectionName
     * @return boolean
     * @throws Exception
     */
    private function facetExists($hash, $collectionName) {
        $results = pg_query($this->dbh, 'SELECT EXISTS(SELECT 1 FROM resto.facets WHERE uid=\'' . pg_escape_string($hash) . '\' AND collection = \'' . pg_escape_string($collectionName) . '\') AS exists');
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        $result = pg_fetch_assoc($results);
        if ($result['exists'] === 't') {
            return true;
        }
        return false;
    }
    
    /**
     * Check if schema $name exists within resto database
     * 
     * @param string $name - schema name
     * @return boolean
     * @throws Exception
     */
    private function schemaExists($name) {
        
        $results = pg_query($this->dbh, 'SELECT EXISTS(SELECT 1 FROM pg_namespace WHERE nspname = \'' . pg_escape_string($name) . '\') AS exists');
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        $result = pg_fetch_assoc($results);
        if ($result['exists'] === 't') {
            return true;
        }

        return false;
    }

    /**
     * Check if table $name exists within resto database
     * 
     * @param string $name - table name
     * @param string $schema - schema name
     * @return boolean
     * @throws Exception
     */
    private function tableExists($name, $schema = 'public') {

        $results = pg_query($this->dbh, 'select EXISTS(SELECT 1 FROM pg_tables WHERE schemaname=\'' . pg_escape_string($schema) . '\' AND tablename=\'' . pg_escape_string($name) . '\') AS exists');
        
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        $result = pg_fetch_assoc($results);
        if ($result['exists'] === 't') {
            return true;
        }

        return false;
    }
    
    /**
     * Check if table $name is empty
     * 
     * @param string $name : table name
     * @param string $schema : schema name
     * @return boolean
     * @throws Exception
     */
    private function tableIsEmpty($name, $schema = 'public') {

        $results = pg_query($this->dbh, 'SELECT EXISTS(SELECT 1 FROM ' . pg_escape_string($schema) . '.' . pg_escape_string($name) . ') AS exists');
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        $result = pg_fetch_assoc($results);
        if ($result['exists'] === 't') {
            return false;
        }

        return true;
    }
    
    /**
     * Collection tables are stored within a dedicated schema
     * based on the collection name
     * 
     * @param string $collectionName
     */
    private function getSchemaName($collectionName) {
        return '_' . strtolower($collectionName);
    }
    
    /**
     * Retrieve cached request result
     * 
     * @param array $arr
     */
    private function retrieveFromCache($arr) {
        $fileName = $this->getCacheFileName($arr);
        if (!$this->cache->isInCache($fileName)) {
            return null;
        }
        return $this->cache->read($fileName);
    }
    
    /**
     * Store result in cache
     * 
     * @param array $arr
     * @param array $obj
     */
    private function storeInCache($arr, $obj) {
        $fileName = $this->getCacheFileName($arr);
        return $this->cache->write($fileName, $obj);
    }
    
    /**
     * Generate a unique cache fileName from input array
     * 
     * @param array $arr
     */
    private function getCacheFileName($arr) {
        if (!isset($arr) || !is_array($arr) || count($arr) === 0) {
            return null;
        }
        return sha1(serialize($arr)) . '.cache';
    }
    
    /**
     * Get signed license for user
     * 
     * @param string $identifier
     * @return array
     * @throws Exception
     */
    public function getSignedLicenses($identifier){
        $results = pg_query($this->dbh, 'SELECT collection, signdate from usermanagement.signatures WHERE email= \'' . pg_escape_string($identifier) . '\'');
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        $result = array();
        while ($row = pg_fetch_assoc($results)){
            if (!$row) {
                return $result;
            }
            $result[$row['collection']] = $row['signdate'];
        }
        return $result;
    }
    
    /**
     * Sign license for collection collectionName
     * 
     * @param string $identifier : user identifier 
     * @param string $collectionName
     * @return boolean
     * @throws Exception
     */
    public function signLicense($identifier, $collectionName) {
        try {
            if (!$this->collectionExists($collectionName)) {
                throw new Exception();
            }
            $results = pg_query($this->dbh, 'SELECT email FROM usermanagement.signatures WHERE email=\'' . pg_escape_string($identifier) . '\' AND collection=\'' . pg_escape_string($collectionName) . '\'');
            if (!$results) {
                throw new Exception();
            }
            if (pg_fetch_assoc($results)) {
                pg_query($this->dbh, 'UPDATE usermanagement.signatures SET signdate=now() WHERE email=\'' . pg_escape_string($identifier) . '\' AND collection=\'' . pg_escape_string($collectionName) . '\'');
            }
            else {
                pg_query($this->dbh, 'INSERT INTO usermanagement.signatures (email, collection, signdate) VALUES (\'' . pg_escape_string($identifier) . '\',\'' . pg_escape_string($collectionName) . '\',now())');
            }
            return true;
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot sign license', 500);
        }
        return false;
    }
    
    /**
     * Get user history
     * 
     * @param integer $userid
     * @param array $options
     *          
     *      array(
     *         'orderBy' => // order field (default querytime),
     *         'ascOrDesc' => // ASC or DESC (default DESC)
     *         'collectionName' => // collection name
     *         'service' => // 'search', 'download' or 'visualize' (default null),
     *         'startIndex' => // (default 0),
     *         'numberOfResults' => // (default 50)
     *     )
     *          
     * @return array
     * @throws Exception
     */
    public function getHistory($userid = null, $options = array()) {
        $options = array(
            'orderBy' => isset($options['orderBy']) ? $options['orderBy'] : 'querytime',
            'ascOrDesc' => isset($options['ascOrDesc']) ? $options['ascOrDesc'] : 'DESC',
            'collectionName' => isset($options['collectionName']) ? $options['collectionName'] : null,
            'service' => isset($options['service']) ? $options['service'] : null,
            'startIndex' => isset($options['startIndex']) ? $options['startIndex'] : 0,
            'numberOfResults' => isset($options['numberOfResults']) ? $options['numberOfResults'] : 50
        );
        try {
            $where = array();
            if (isset($userid)) {
                $where[] = 'userid=' . pg_escape_string($userid);
            }
            if (isset($options['service'])) {
                $where[] = 'service=\'' . pg_escape_string($options['service']) . '\'';
            }
            if (isset($options['collectionName'])) {
                $where[] = 'collection=\'' . pg_escape_string($options['collectionName']) . '\'';
            }
            $results = pg_query($this->dbh, 'SELECT gid, userid, method, service, collection, resourceid, query, querytime, url, ip FROM usermanagement.history' . (count($where) > 0 ? ' WHERE ' . join(' AND ', $where) : '') . ' ORDER BY ' . pg_escape_string($options['orderBy']) . ' ' . pg_escape_string($options['ascOrDesc']) . ' LIMIT ' . $options['numberOfResults'] . ' OFFSET ' . $options['startIndex']);
            if (!$results) {
                throw new Exception();
            } 
            $result = array();
            while ($row = pg_fetch_assoc($results)) {
                $result[$row['gid']] = $row;
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot get history', 500);
        }
        
    }
    
    /**
     * Activate user
     * 
     * @param string $userid : can be userid or base64(email)
     * @param string $activationcode
     * 
     * @throws Exception
     */
    public function activateUser($userid, $activationcode = null) {
        try {
            /*
             * If $userid is not an integer we assume it is the email
             * encoded in base64
             */
            if (!ctype_digit($userid)) {
                $profile = $this->getUserProfile(base64_decode($userid));
                $userid = $profile['userid'];
            }
            $updateResults = pg_query($this->dbh, 'UPDATE usermanagement.users SET activated=true WHERE userid=\'' . pg_escape_string($userid) . '\'' . (isset($activationcode) ? ' AND activationcode=\'' . pg_escape_string($activationcode) . '\'' :'') . ' RETURNING userid');
            if (!$updateResults) {
                throw new Exception();
            }
            while ($row = pg_fetch_assoc($updateResults)) {
                return true;
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot activate user : ' . $userid, 500);
        }
        return false;
    }
    
    /**
     * Deactivate user
     * 
     * @param string $userid
     * @throws Exception
     */
    public function deactivateUser($userid) {
        try{
            /*
             * If $userid is not an integer we assume it is the email
             * encoded in base64
             */
            if (!ctype_digit($userid)) {
                $profile = $this->getUserProfile(base64_decode($userid));
                $userid = $profile['userid'];
            }
            $updateResults = pg_query($this->dbh, 'UPDATE usermanagement.users SET activated=false WHERE userid=\'' . pg_escape_string($userid) . '\'');
            if (!$updateResults) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Cannot deactivate user : ' . $userid, 500);
        }
    }
    
    /**
     * Count history logs per service
     * 
     * @param string $service : i.e. one of 'download', 'search', etc.
     * @param string $collectionName
     * @param integer $userid
     * @return integer
     * @throws Exception
     */
    public function countService($service, $collectionName = null, $userid = null){
        $results = pg_query($this->dbh, 'SELECT count(gid) FROM usermanagement.history WHERE service=\'' . pg_escape_string($service) .'\'' . (isset($collectionName) ? ' AND collection=\'' . pg_escape_string($collectionName) . '\'' : '') . (isset($userid) ? ' AND userid=\'' . pg_escape_string($userid) . '\'' : ''));
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        return pg_fetch_assoc($results);
    }
    
    /**
     * Count history logs per service
     * 
     * @param boolean $activated
     * @param string $groupname
     * @return integer
     * @throws Exception
     */
    public function countUsers($activated = null, $groupname = null){
        $results = pg_query($this->dbh, 'SELECT COUNT(*) FROM usermanagement.users ' . (isset($activated) ?  (' WHERE activated=\'' . ($activated === true ? 't' : 'f') . '\'') : '') . (isset($groupname) ? ' AND groupname=\'' . pg_escape_string($groupname) . '\'' : ''));
        if (!$results) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Database connection error', 500);
        }
        return pg_fetch_assoc($results);
    }
    
}
