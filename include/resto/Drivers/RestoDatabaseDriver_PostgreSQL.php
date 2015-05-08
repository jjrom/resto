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

/*
 * Required includes
 */
require 'PostgreSQL/Functions_general.php';
require 'PostgreSQL/Functions_cart.php';
require 'PostgreSQL/Functions_collections.php';
require 'PostgreSQL/Functions_facets.php';
require 'PostgreSQL/Functions_features.php';
require 'PostgreSQL/Functions_filters.php';
require 'PostgreSQL/Functions_rights.php';
require 'PostgreSQL/Functions_users.php';

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
             * Get Database Handler
             */
            case parent::HANDLER:
                return $this->getHandler($params);
            
            /*
             * Get cart items
             */
            case parent::CART_ITEMS:
                $cartFunctions = new Functions_cart($this);
                return $cartFunctions->getCartItems($params['email']);
            
            /*
             * Get collections descriptions
             */
            case parent::COLLECTIONS_DESCRIPTIONS:
                $collectionsFunctions = new Functions_collections($this);
                return $collectionsFunctions->getCollectionsDescriptions(isset($params['collectionName']) ? $params['collectionName'] : null);
            
            /*
             * Get collections descriptions
             */
            case parent::FEATURE_DESCRIPTION:
                $featuresFunctions = new Functions_features($this);
                return $featuresFunctions->getFeatureDescription($params['context'], $params['user'], $params['featureIdentifier'], isset($params['collection']) ? $params['collection'] : null, isset($params['filters']) ? $params['filters'] : array());
            
            /*
             * Get feature collections description
             */
            case parent::FEATURES_DESCRIPTIONS:
                $featuresFunctions = new Functions_features($this);
                return $featuresFunctions->search($params['context'], $params['user'], $params['collection'], $params['filters'], $params['options']);
            
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
                return $usersFunctions->revokeToken($params['token']);
               
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
             * True if user is item is in cart
             */
            case parent::CART_ITEM:
                $cartFunctions = new Functions_cart($this);
                return $cartFunctions->isInCart($params['itemId']);
            
            /*
             * True if user is license is signed
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
             * True if user is license is signed
             */
            case parent::TOKEN_REVOKED:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->isTokenRevoked($params['token']);
                
            /*
             * True if user exists
             */
            case parent::USER:
                $usersFunctions = new Functions_users($this);
                return $usersFunctions->userExists($params['email']);
            
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
                return $cartFunctions->removeFromCart($params['email'], $params['itemId']);
            
            /*
             * Remove all cart items
             */
            case parent::CART_ITEMS:
                $cartFunctions = new Functions_cart($this);
                return $cartFunctions->clearCart($params['email']);
            
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
                return $featuresFunctions->storeFeature($params['collection'], $params['featureArray']);
            
            /*
             * Store cart item
             */
            case parent::ORDER:
                $cartFunctions = new Functions_cart($this);
                return $cartFunctions->placeOrder($params['email'], isset($params['items']) ? $params['items'] : null);
            
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
                return $rightsFunctions->storeRights($params['rights'], $params['emailOrGroup'], $params['collectionName'], isset($params['featureIdentifier']) ? $params['featureIdentifier'] : null);
            
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
     * Close database handler
     */
    public function closeDbh() {
        if (isset($this->dbh)) {
            pg_close($this->dbh);
        }
    }
    
    /**
     * Return $sentence in lowercase, without accent and with "'" character 
     * replaced by a space
     * 
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
            return str_replace('\'', ' ', $result['normalized']);
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
        $output = pg_fetch_all($results);
        return $output === false ? array() : $output;
    }
    
    /**
     * Return PostgreSQL database handler
     * 
     * @param array $options
     * @throws Exception
     */
    private function getHandler($options = array()) {
    
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
                RestoLogUtil::httpError(500, 'Database connection error');
            }
        }   

        return $dbh;
    }
    
}
