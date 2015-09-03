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
 * RESTo Database
 */
abstract class RestoDatabaseDriver {
    
    /*
     * Constant
     */
    const ACTIVATE_USER = 1;
    const CART_ITEM = 2;
    const CART_ITEMS = 3;
    const COLLECTION = 4;
    const COLLECTIONS = 5;
    const COLLECTIONS_DESCRIPTIONS = 6;
    const DEACTIVATE_USER = 7;
    const DISCONNECT_USER = 8;
    const FACET = 9;
    const FACETS = 10;
    const FEATURE = 11;
    const FEATURES_DESCRIPTIONS = 12;
    const FEATURE_DESCRIPTION = 13;
    const GROUPS = 14;
    const HANDLER = 15;
    const HISTORY = 16;
    const KEYWORDS = 17;
    const LICENSE = 18;
    const LICENSES = 19;
    const ORDER = 20;
    const ORDERS = 21;
    const QUERY = 22;
    const RIGHTS = 23;
    const SCHEMA = 24;
    const SHARED_LINK = 25;
    const SIGNATURE = 26;
    const SIGNATURES = 27;
    const STATISTICS = 28;
    const TABLE = 29;
    const TABLE_EMPTY = 30;
    const TOKEN_REVOKED = 31;
    const USER = 32;
    const USER_PASSWORD = 33;
    const USER_PROFILE = 34;
    const USERS_PROFILES = 35;
    const GROUP = 36;
    
    /*
     * Results per page
     */
    public $resultsPerPage = 20;

    /*
     * Cache object
     */
    public $cache = null;
    
    /*
     * Database handler
     */
    public $dbh;
    
    /**
     * Constructor
     * 
     * @param array $config
     * @param RestoCache $cache
     * @throws Exception
     */
    public function __construct($config, $cache) {
        $this->cache = isset($cache) ? $cache : new RestoCache(null);
    } 
    
    /**
     * List object by type name
     * 
     * @return array
     * @throws Exception
     */
    abstract public function get($typeName);
    
    /**
     * Check if $typeName constraint is true
     * 
     * @param string $typeName - object type name ('collection', 'feature', 'user')
     * @param array $params
     * @return boolean
     * @throws Exception
     */
    abstract public function check($typeName, $params);

    /**
     * Execute action
     * 
     * @param string $typeName - object type name ('collection', 'feature', 'user')
     * @param array $params
     * @return boolean
     * @throws Exception
     */
    abstract public function execute($typeName, $params);
    
    /**
     * Return normalized $sentence i.e. in lowercase and without accents
     * This function is superseed in RestoDabaseDriver_PostgreSQL and use
     * the inner function normalize($sentence) defined in installDB.sh
     * 
     * @param string $sentence
     */
    abstract public function normalize($sentence);
    
    /**
     * Remove object from database
     * 
     * @param Object $object
     */
    abstract public function remove($object);

    /**
     * Store object within database
     * 
     * @param string $typeName
     * @param array $params
     * @throws Exception
     */
    abstract public function store($typeName, $params);

    /**
     * Update object within database
     * 
     * @param string $typeName
     * @param array $params
     * @throws Exception
     */
    abstract public function update($typeName, $params);
    
    /**
     * Close database handler
     */
    abstract public function closeDbh();
}
