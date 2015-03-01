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
 * RESTo PostgreSQL users functions
 */
class Functions_users {
    
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
        $this->dbh = $dbDriver->getHandler();
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
     * Return encrypted user password
     * 
     * @param string $identifier : email
     * 
     * @throws Exception
     */
    public function getUserPassword($identifier) {
        $query = 'SELECT password FROM usermanagement.users WHERE email=\'' . pg_escape_string($identifier) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return count($results) === 1 ? $results[0]['password'] : null;
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
         * Default profile - for unregistered users
         */
        $profile = array(
            'userid' => -1,
            'groupname' => 'unregistered'
        );
        
        /*
         * Unregistered users
         */
        if (!isset($identifier) || !$identifier || $identifier === 'unregistered') {
            return $profile;
        }
        
        $query = 'SELECT userid, email, md5(email) as userhash, groupname, username, givenname, lastname, ' . $this->dbDriver->formatTimestamp('registrationdate') . ', activated, connected FROM usermanagement.users WHERE ' . $this->useridOrEmailFilter($identifier) . (isset($password) ? ' AND password=\'' . pg_escape_string(sha1($password)). '\'' : '');
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        
        return count($results) === 1 ? $results[0] : $profile;
        
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
        
        $query = 'SELECT itemid, item FROM usermanagement.cart WHERE email=\'' . pg_escape_string($identifier) . '\'';
        $results = $this->dbDriver->query($query, 500, 'Cannot get cart items');
        while ($result = pg_fetch_assoc($results)) {
            $items[$result['itemid']] = json_decode($result['item'], true);
        }
        
        return $items;
    }
    
    /**
     * Return orders list for user
     * 
     * @param string $identifier
     * @param string $orderId
     * @return array
     * @throws exception
     */
    public function getOrders($identifier, $orderId = null) {
        
        $items = array();
        
        if (!isset($identifier)) {
            return $items;
        }
        
        $query = 'SELECT orderid, querytime, items FROM usermanagement.orders WHERE email=\'' . pg_escape_string($identifier) . '\'' . (isset($orderId) ? ' AND orderid=\'' . pg_escape_string($orderId) . '\'' : '');
        $results = $this->dbDriver->query($query);
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
        
        return $items;
    }
    
    /**
     * Check if user identified by $identifier exists within database
     * 
     * @param string $identifier - user email
     * 
     * @return boolean
     * @throws Exception
     */
    public function userExists($email) {
        $query = 'SELECT 1 FROM usermanagement.users WHERE email=\'' . pg_escape_string($email) . '\'';
        return !$this->dbDriver->isEmpty($this->dbDriver->fetch($this->dbDriver->query($query)));
    }
    
    /**
     * Return true if $userid is connected
     * 
     * @param string $identifier : userid or email
     * 
     * @throws Exception
     */
    public function userIsConnected($identifier) {
        if (!isset($identifier)) {
            return false;
        }
        $query = 'SELECT 1 FROM usermanagement.users WHERE ' . $this->useridOrEmailFilter($identifier) . ' AND connected=1';
        return !$this->dbDriver->isEmpty($this->dbDriver->fetch($this->dbDriver->query(($query))));
    }
    
    /**
     * Save user profile to database i.e. create new entry if user does not exist
     * 
     * @param array $profile
     * @return array (userid, activationcode)
     * @throws exception
     */
    public function storeUserProfile($profile) {
       
        if (!is_array($profile) || !isset($profile['email'])) {
            RestoLogUtil::httpError(500, 'Cannot save user profile - invalid user identifier');
        }
        if ($this->userExists($profile['email'])) {
            RestoLogUtil::httpError(500, 'Cannot save user profile - user already exist');
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
            $profile['activated'],
            'now()'
        );
        // TODO change to pg_fetch_assoc ?
        $results = $this->dbDriver->query('INSERT INTO usermanagement.users (email,password,groupname,username,givenname,lastname,activationcode,activated,registrationdate) VALUES (' . join(',', $values) . ') RETURNING userid, activationcode');
        return pg_fetch_array($results);
        
    }
    
    /**
     * Update user profile to database
     * 
     * @param array $profile
     * @return integer (userid)
     * @throws exception
     */
    public function updateUserProfile($profile) {
       
        if (!is_array($profile) || !isset($profile['email'])) {
            RestoLogUtil::httpError(500, 'Cannot update user profile - invalid user identifier');
        }

        /*
         * Only password, groupname, activated and connected fields can be updated
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
        if (isset($profile['connected'])) {
            if ($profile['connected'] === true) {
                $values[] = 'connected=TRUE';
            }
            else if ($profile['connected'] === false) {
                $values[] = 'connected=FALSE';
            }
        }
        
        $results = $this->dbDriver->fetch($this->dbDriver->query('UPDATE usermanagement.users SET ' . join(',', $values) . ' WHERE email=\'' . pg_escape_string(trim(strtolower($profile['email']))) .'\' RETURNING userid'));
        
        return count($results) === 1 ? $results[0]['userid'] : null;
        
    }
    
    /**
     * Disconnect user
     * 
     * @param string $email
     */
    public function disconnectUser($email) {
        $query = 'UPDATE usermanagement.users SET connected=FALSE WHERE email=\'' . pg_escape_string($email) . '\'';
        $this->dbDriver->query($query);
        return true;
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
        $this->dbDriver->query('UPDATE usermanagement.rights SET ' . join('', $values) . ' WHERE collection=\'' . pg_escape_string($collectionName) . '\' AND emailorgroup=\'' . pg_escape_string($identifier) . '\' AND featureid' . (isset($featureIdentifier) ? ('=\'' . $featureIdentifier . '\'') : ' IS NULL'));    
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
     * Check if user signed collection license
     * 
     * @param string $identifier
     * @param string $collectionName
     * 
     * @return boolean
     */
    public function isLicenseSigned($identifier, $collectionName) {
        $query = 'SELECT 1 FROM usermanagement.signatures WHERE email= \'' . pg_escape_string($identifier) . '\' AND collection= \'' . pg_escape_string($collectionName) . '\'';
        return !$this->dbDriver->isEmpty($this->dbDriver->fetch($this->dbDriver->query(($query))));
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
        $query = 'SELECT 1 FROM usermanagement.cart WHERE itemid=\'' . pg_escape_string($itemId) . '\'';
        return !$this->dbDriver->isEmpty($this->dbDriver->fetch($this->dbDriver->query(($query))));
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
        if ($this->isInCart($itemId)) {
            RestoLogUtil::httpError(1000, 'Cannot add item : ' . $itemId . ' already exists');
        }
        $values = array(
            '\'' . pg_escape_string($itemId) . '\'',
            '\'' . pg_escape_string($identifier) . '\'',
            '\'' . pg_escape_string(json_encode($item)) . '\'',
            'now()'
        );
        $this->dbDriver->query($this->dbh, 'INSERT INTO usermanagement.cart (itemid, email, item, querytime) VALUES (' . join(',', $values) . ')');
        return array($itemId => $item);
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
        if (!$this->isInCart($itemId)) {
            RestoLogUtil::httpError(1001, 'Cannot update item : ' . $itemId . ' does not exist');
        }
        $this->dbDriver->query('UPDATE usermanagement.cart SET item = \''. pg_escape_string(json_encode($item)) . '\', querytime=now() WHERE email=\'' . pg_escape_string($identifier) . '\' AND itemid=\'' . pg_escape_string($itemId) . '\'');
        return true;
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
        $this->dbDriver->query('DELETE FROM usermanagement.cart WHERE itemid=\'' . pg_escape_string($itemId) . '\' AND email=\'' . pg_escape_string($identifier) . '\'', 500, 'Cannot remove ' . $itemId . ' from cart');
        return true;
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
                RestoLogUtil::httpError(500, 'Database connection error');
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
     * Sign license for collection collectionName
     * 
     * @param string $identifier : user identifier 
     * @param string $collectionName
     * @return boolean
     * @throws Exception
     */
    public function signLicense($identifier, $collectionName) {
        
        if (!$this->collectionExists($collectionName)) {
            RestoLogUtil::httpError(500, 'Cannot sign license');
        }
        $results = $this->dbDriver->query('SELECT email FROM usermanagement.signatures WHERE email=\'' . pg_escape_string($identifier) . '\' AND collection=\'' . pg_escape_string($collectionName) . '\'');
        if (pg_fetch_assoc($results)) {
            $this->dbDriver->query('UPDATE usermanagement.signatures SET signdate=now() WHERE email=\'' . pg_escape_string($identifier) . '\' AND collection=\'' . pg_escape_string($collectionName) . '\'');
        }
        else {
            $this->dbDriver->query('INSERT INTO usermanagement.signatures (email, collection, signdate) VALUES (\'' . pg_escape_string($identifier) . '\',\'' . pg_escape_string($collectionName) . '\',now())');
        }
        return true;
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
            RestoLogUtil::httpError(500, 'Cannot get history');
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
        $query = 'UPDATE usermanagement.users SET activated=1 WHERE userid=\'' . pg_escape_string($userid) . '\'' . (isset($activationcode) ? ' AND activationcode=\'' . pg_escape_string($activationcode) . '\'' :'') . ' RETURNING userid';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        if (count($results) === 1) {
            return true;
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
        $query = 'UPDATE usermanagement.users SET activated=0 WHERE userid=\'' . pg_escape_string($userid) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        if (count($results) === 1) {
            return true;
        }
        return false;
    }
    
    /**
     * Return filter on user
     * 
     * @param string $identifier
     */
    private function useridOrEmailFilter($identifier) {
        return ctype_digit($identifier) ? 'userid=' . $identifier : 'email=\'' . pg_escape_string($identifier) . '\'';
    }
    
}
