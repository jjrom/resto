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
     * Facet Util reference
     */
    public $facetUtil;
    
    /**
     * Constructor
     * 
     * @param array $config
     * @param RestoCache $cache
     * @throws Exception
     */
    public function __construct($config, $cache) {
        
        parent::__construct($config, $cache);
        
        $this->dbh = $this->getHandler($config);
        
        $this->facetUtil = new RestoFacetUtil();
        
        if (isset($config['resultsPerPage'])) {
            $this->resultsPerPage = $config['resultsPerPage'];
        }
        
    }
    
    /**
     * Get object by typename
     * 
     * @param string $typeName
     * @param array $params
     * @return type
     */
    public function get($typeName, $params = array()) {
        switch ($typeName) {
            
            /*
             * Get cart items
             */
            case parent::CART_ITEMS:
                $cartFunctions = new Functions_cart($this);
                return $cartFunctions->getCartItems($params['email']);
            
            /*
             * Get collections list
             */
            case parent::COLLECTIONS:
                $collectionsFunctions = new Functions_collections($this);
                return $collectionsFunctions->getCollections();
            
            /*
             * Get collections descriptions
             */
            case parent::COLLECTIONS_DESCRIPTIONS:
                $collectionsFunctions = new Functions_collections($this);
                return $collectionsFunctions->getCollectionsDescriptions(isset($params['collectionName']) ? $params['collectionName'] : null, isset($params['facetFields']) ? $params['facetFields'] : null);
            
            /*
             * Get collections descriptions
             */
            case parent::FEATURE_DESCRIPTION:
                $featuresFunctions = new Functions_features($this);
                return $featuresFunctions->getFeatureDescription($params['featureIdentifier'], $params['model'], isset($params['collectionName']) ? $params['collectionName'] : null, isset($params['filters']) ? $params['filters'] : array());
            
            /*
             * Get feature collections description
             */
            case parent::FEATURES_DESCRIPTIONS:
                $featuresFunctions = new Functions_features($this);
                return $featuresFunctions->search($params['model'], $params['collectionName'], $params['filters'], $params['options']);
            
            /*
             * Get groups list
             */
            case parent::GROUPS:
                $rightsFunctions = new Functions_rights($this);
                return $rightsFunctions->getGroups();
            
            /*
             * Get hierarchical facets
             */
            case parent::HIERACHICAL_FACETS:
                //TODO return $this->getHierarchicalFacets($params['hash'], $params['collectionName']);
                
            /*
             * Get Keywords
             */
            case parent::KEYWORDS:
                $generalFunctions = new Functions_general($this);
                return $generalFunctions->getKeywords($params['language'], isset($params['types']) ? $params['types'] : array());
            
            /*
             * Get orders
             */    
            case parent::ORDERS:
                $cartFunctions = new Functions_cart($this);
                return $cartFunctions->getOrders($params['email'], isset($params['orderId']) ? $params['orderId'] : null);
                
            /*
             * Get rights
             */
            case parent::RIGHTS:
                $rightsFunctions = new Functions_rights($this);
                return $rightsFunctions->getRights($params['emailOrGroup'], isset($params['collectionName']) ? $params['collectionName'] : null, isset($params['featureIdentifier']) ? $params['featureIdentifier'] : null);
            
            /*
             * Get rights
             */
            case parent::RIGHTS_FULL:
                $rightsFunctions = new Functions_rights($this);
                return $rightsFunctions->getFullRights($params['emailOrGroup'], isset($params['collectionName']) ? $params['collectionName'] : null, isset($params['featureIdentifier']) ? $params['featureIdentifier'] : null);
            
            /*
             * Get statistics
             */
            case parent::STATISTICS:
                $facetsFunctions = new Functions_facets($this);
                return $facetsFunctions->getStatistics($params['collectionName'], $params['facetFields']);
            
            /*
             * Get statistics
             */
            case parent::SHARED_LINK:
                $generalFunctions = new Functions_general($this);
                return $generalFunctions->createSharedLink($params['resourceUrl']);
            
            /*
             * Get encrypted user password
             */
            case parent::USER_PASSWORD:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->getUserPassword($params['email']);
            
            /*
             * Get user profile
             */
            case parent::USER_PROFILE:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->getUserProfile(isset($params['email']) ? $params['email'] : $params['userid'], isset($params['password']) ? $params['password'] : null);
                
            default:
                return null;
        }
    }
    
    
    /**
     * Execute action
     * 
     * @param string $typeName
     * @param array $params
     * @return type
     */
    public function execute($typeName, $params = array()) {
        switch ($typeName) {
                 
            /*
             * Activate user
             */
            case parent::ACTIVATE_USER:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->activateUser($params['userid'], $params['activationCode']);
            
            /*
             * Deactivate user
             */
            case parent::DEACTIVATE_USER:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->deactivateUser($params['userid']);
            
            /*
             * Deactivate user
             */
            case parent::DISCONNECT_USER:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->disconnectUser($params['email']);
               
            /*
             * Sign license
             */
            case parent::SIGN_LICENSE:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->signLicense($params['email'], $params['collectionName']);
                
            default:
                return null;
        }
    }
    
    
    /**
     * Return true if object exist
     * 
     * @param string $typeName
     * @param array $params
     * @return type
     */
    public function check($typeName, $params = array()) {
        switch ($typeName) {
            
            /*
             * True if collection exists
             */
            case parent::COLLECTION:
                $collectionsFunctions = new Functions_collections($this);
                return $collectionsFunctions->collectionExists($params['collectionName']);
            
            /*
             * True if feature exists
             */
            case parent::FEATURE:
                $featuresFunctions = new Functions_features($this);
                return $featuresFunctions->featureExists($params['featureIdentifier'], isset($params['schema']) ? $params['schema'] : null);
            
            /*
             * True if user is connected
             */
            case parent::CART_ITEM:
                $cartFunctions = new Functions_cart($this);
                return $cartFunctions->isInCart($params['itemId']);
            
            /*
             * True if user is connected
             */
            case parent::LICENSE_SIGNED:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->isLicenseSigned($params['email'], $params['collectionName']);
                
            /*
             * True if schema exists
             */
            case parent::SCHEMA:
                $generalFunctions = new Functions_general($this);
                return $generalFunctions->schemaExists($params['name']);
                
            /*
             * True if shared link is valid
             */
            case parent::SHARED_LINK:
                $generalFunctions = new Functions_general($this);
                return $generalFunctions->isValidSharedLink($params['resourceUrl'], $params['token']);
                
            /*
             * True if table exists
             */
            case parent::TABLE:
                $generalFunctions = new Functions_general($this);
                return $generalFunctions->tableExists($params['name'], isset($params['schema']) ? $params['schema'] : 'public');
                
            /*
             * True if table is empty
             */
            case parent::TABLE_EMPTY:
                $generalFunctions = new Functions_general($this);
                return $generalFunctions->tableIsEmpty($params['name'], isset($params['schema']) ? $params['schema'] : 'public');
                     
            /*
             * True if user exists
             */
            case parent::USER:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->userExists($params['email']);
            
            /*
             * True if user is connected
             */
            case parent::USER_CONNECTED:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->userIsConnected($params['identifier']);
                
            default:
                return null;
        }
    }
    
    /**
     * Remove object
     * 
     * @param string $typeName
     * @param array $params
     * @return type
     */
    public function remove($typeName, $params = array()) {
        switch ($typeName) {
            
            /*
             * Remove collection
             */
            case parent::COLLECTION:
                $collectionsFunctions = new Functions_collections($this);
                return $collectionsFunctions->removeCollection($params['collection']);
            
            /*
             * Remove facet
             */
            case parent::FACET:
                $facetsFunctions = new Functions_facets($this);
                return $facetsFunctions->removeFacet($params['hash'], $params['collectionName']);
                
            /*
             * Remove feature
             */
            case parent::FEATURE:
                $featuresFunctions = new Functions_features($this);
                return $featuresFunctions->removeFeature($params['feature']);
            
            /*
             * Remove cart item
             */
            case parent::CART_ITEM:
                $cartFunctions = new Functions_cart($this);
                return $cartFunctions->removeFromCart($params['identifier'], $params['itemId']);
                
            /*
             * Remove collection/feature rights for user
             */
            case parent::RIGHTS:
                $rightsFunctions = new Functions_rights($this);
                return $rightsFunctions->deleteRights($params['emailOrGroup'], $params['collectionName'],  $params['featureIdentifier']);
                
            default:
                return null;
        }
    }
    
    /**
     * Store object
     * 
     * @param string $typeName
     * @param array $params
     * @return type
     */
    public function store($typeName, $params = array()) {
        switch ($typeName) {
            
            /*
             * Store cart item
             */
            case parent::CART_ITEM:
                $cartFunctions = new Functions_cart($this);
                return $cartFunctions->addToCart($params['email'], $params['item']);
            
            /*
             * Store collection
             */
            case parent::COLLECTION:
                $collectionsFunctions = new Functions_collections($this);
                return $collectionsFunctions->storeCollection($params['collection']);
            
            /*
             * Store facets
             */
            case parent::FACETS:
                $facetsFunctions = new Functions_facets($this);
                return $facetsFunctions->storeFacets($params['facets'], $params['collectionName']);
                
            /*
             * Store feature
             */
            case parent::FEATURE:
                $featuresFunctions = new Functions_features($this);
                return $featuresFunctions->storeFeature($params['collectionName'], $params['elements'], $params['model']);
            
            /*
             * Store cart item
             */
            case parent::ORDER:
                $cartFunctions = new Functions_cart($this);
                return $cartFunctions->placeOrder($params['email']);
            
            /*
             * Store query
             */
            case parent::QUERY:
                $generalFunctions = new Functions_general($this);
                return $generalFunctions->storeQuery($params['userid'], $params['query']);
            
            /*
             * Store rights
             */
            case parent::RIGHTS:
                $rightsFunctions = new Functions_rights($this);
                return $rightsFunctions->storeRights($params['rights'], $params['emailOrGroup'], $params['collectionName'], $params['featureIdentifier']);
            
            /*
             * Store user profile
             */
            case parent::USER_PROFILE:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->storeUserProfile($params['profile']);
            
            default:
                return null;
        }
    }
   
    /**
     * Update object
     * 
     * @param string $typeName
     * @param array $params
     * @return type
     */
    public function update($typeName, $params = array()) {
        switch ($typeName) {
            
            /*
             * Update cart item
             */
            case parent::CART_ITEM:
                $cartFunctions = new Functions_cart($this);
                return $cartFunctions->updateCart($params['email'], $params['itemId'], $params['item']);
            
            /*
             * Update rights
             */
            case parent::RIGHTS:
                $rightsFunctions = new Functions_rights($this);
                return $rightsFunctions->updateRights($params['rights'], $params['emailOrGroup'], $params['collectionName'], $params['featureIdentifier']);
            
            /*
             * Update user profile
             */
            case parent::USER_PROFILE:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->updateUserProfile($params['profile']);
            
            default:
                return null;
        }
    }
    
    /**
     * Collection tables are stored within a dedicated schema
     * based on the collection name
     * 
     * @param string $collectionName
     */
    public function getSchemaName($collectionName) {
        return '_' . strtolower($collectionName);
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
     * Perform query on database
     * 
     * @param string $query
     * @param integer $errorCode
     * @param string $errorMessage
     * @return Database result
     * @throws Exception
     */
    public function query($query, $errorCode = 500, $errorMessage = null) {
        try {
            $results = pg_query($this->dbh, $query);
            if (!$results) {
                throw new Exception();
            }
            return $results;
        }
        catch (Exception $e) {
            RestoLogUtil::httpError($errorCode, isset($errorMessage) ? $errorMessage : 'Database connection error');
        }
    }
    
    /**
     * Convert database query result into array
     * 
     * @param DatabaseResult $results
     * @return array
     */
    public function fetch($results) {
        $output = array();
        while ($row = pg_fetch_assoc($results)){
            $output[] = $row;
        }
        return $output;
    }
    
    /**
     * Return true if results exists
     * 
     * @param string $query
     * @return boolean
     */
    public function exists($query) {
        $results = $this->fetch($this->query(($query)));
        return !empty($results);
    }
    
    /**
     * Return PostgreSQL database handler
     * 
     * @param array $options
     * @throws Exception
     */
    public function getHandler($options = array()) {
    
        $dbh = null;
        
        if (isset($options) && isset($options['dbname'])) {
            try {
                $dbInfo = array(
                    'dbname=' . $options['dbname'],
                    'user=' . $options['user'],
                    'password=' . $options['password']
                );
                /*
                 * If host is specified, then TCP/IP connection is used
                 * Otherwise socket connection is used
                 */
                if (isset($options['host'])) {
                    $dbInfo[] = 'host=' . $options['host'];
                    $dbInfo[] = 'port=' . (isset($options['port']) ? $options['port'] : '5432');
                }
                $dbh = pg_connect(join(' ', $dbInfo));
                if (!$dbh) {
                    throw new Exception();
                }
            } catch (Exception $e) {
                throw new Exception('Database connection error', 500);
            }
        }   

        return $dbh;
    }
    
}
