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
 * RESTo PostgreSQL rights functions
 */
class Functions_rights {
    
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
     * List all groups
     * 
     * @return array
     * @throws Exception
     */
    public function getGroups() {
        $query = 'SELECT DISTINCT groupname FROM usermanagement.users';
        return $this->dbDriver->fetch($this->dbDriver->query($query));
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
        $query = 'SELECT search, download, visualize, canpost as post, canput as put, candelete as delete, filters FROM usermanagement.rights WHERE emailorgroup=\'' . pg_escape_string($identifier) . '\' AND collection=\'' . pg_escape_string($collectionName) . '\' AND featureid' . (isset($featureIdentifier) ? '=\'' . pg_escape_string($featureIdentifier) . '\'' : ' IS NULL');
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        if (count($results) === 1) {
            if (isset($results[0]['filters'])) {
                $results[0]['filters'] = json_decode($results[0]['filters'], true);
            }
            foreach (array_values(array('search', 'download', 'visualize', 'post', 'put', 'delete')) as $key) {
                $results[0][$key] = isset($results[0][$key]) ? (integer) $results[0][$key] : null;
            }
            return $results[0];
        }
        return null;
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
        $query = 'SELECT collection, featureid, search, download, visualize, canpost as post, canput as put, candelete as delete, filters FROM usermanagement.rights WHERE emailorgroup=\'' . pg_escape_string($identifier) . '\'' . (isset($collectionName) ?  ' AND collection=\'' . pg_escape_string($collectionName) . '\'' : '')  . (isset($featureIdentifier) ?  ' AND featureid=\'' . pg_escape_string($featureIdentifier) . '\'' : '');
        $results = $this->dbDriver->query($query);
        $rights = array();
        while ($row = pg_fetch_assoc($results)){
            $properties = array();
            if (isset($row['collection']) && !isset($rights[$row['collection']])) {
                $rights[$row['collection']] = array(
                    'features' => array()
                );
            }
            if (isset($row['filters'])) {
                $properties['filters'] = json_decode($row['filters'], true);
            }
            foreach (array_values(array('search', 'download', 'visualize', 'post', 'put', 'delete')) as $field){
                $properties[$field] =  isset($row[$field]) ? (integer) $row[$field] : null;
            }
            if (isset($row['featureid'])) {
                $rights[$row['collection']]['features'][$row['featureid']] = $properties;
            }
            else {
                $rights[$row['collection']] = array_merge($rights[$row['collection']], $properties);
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
            if (!$this->dbDriver->check(RestoDatabaseDriver::COLLECTION, array(
                'collectionName' => $collectionName
            ))) {
                throw new Exception();
            }
            $values = array(
                '\'' . pg_escape_string($collectionName) . '\'',
                isset($featureIdentifier) ? '\'' . pg_escape_string($featureIdentifier) . '\'' : 'NULL',
                '\'' . pg_escape_string($identifier) . '\'',
                $this->valueOrNull($rights['search']),
                $this->valueOrNull($rights['visualize']),
                $this->valueOrNull($rights['download']),
                $this->valueOrNull($rights['canpost']),
                $this->valueOrNull($rights['canput']),
                $this->valueOrNull($rights['candelete']),
                isset($rights['filters']) ? '\'' . pg_escape_string(json_encode($rights['filters'])) . '\'' : 'NULL'
            );
            $result = pg_query($this->dbh, 'INSERT INTO usermanagement.rights (collection,featureid,emailorgroup,search,visualize,download,canpost,canput,candelete,filters) VALUES (' . join(',', $values) . ')');    
            if (!$result){
                throw new Exception();
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot create right');
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
        
        if (!$this->dbDriver->check(RestoDatabaseDriver::COLLECTION, array(
            'collectionName' => $collectionName
        ))) {
            RestoLogUtil::httpError(500, 'Cannot update rights - collection ' . $collectionName . ' does not exist');
        }
        $values = "collection='" . pg_escape_string($collectionName) . "',";
        if (isset($featureIdentifier)) {
            $values .= "featureid='" . pg_escape_string($featureIdentifier) . "',";
        }
        foreach (array_values(array('search', 'visualize', 'download', 'canpost', 'canput', 'candelete')) as $action) {
            if (isset($rights[$action])) {
                $values .= $action ."=" . $rights[$action] . ",";
            }
        }
        $values .= "emailorgroup='" . pg_escape_string($identifier) . "'";
        
        $this->dbDriver->query('UPDATE usermanagement.rights SET ' . $values . ' WHERE collection=\'' . pg_escape_string($collectionName) . '\' AND emailorgroup=\'' . pg_escape_string($identifier) . '\' AND featureid' . (isset($featureIdentifier) ? ('=\'' . $featureIdentifier . '\'') : ' IS NULL'));    
        return true;
        
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
            RestoLogUtil::httpError(500, 'Cannot delete rights for ' . $identifier);
        }
    }
    
    /**
     * Return $value or NULL
     * @param string $value
     */
    private function valueOrNull($value) {
        return isset($value) ? $value : 'NULL';
    }
   
}
