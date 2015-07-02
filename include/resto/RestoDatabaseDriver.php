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
    const FEATURES = 12;
    const FEATURES_DESCRIPTIONS = 13;
    const FEATURE_DESCRIPTION = 14;
    const GROUPS = 15;
    const HANDLER = 16;
    const KEYWORDS = 18;
    const LICENSE_SIGNED = 19;
    const ORDER = 20;
    const ORDERS = 21;
    const QUERY = 22;
    const RIGHTS = 23;
    const RIGHTS_FULL = 24;
    const SCHEMA = 25;
    const SHARED_LINK = 26;
    const SIGN_LICENSE = 27;
    const STATISTICS = 28;
    const TABLE = 29;
    const TABLE_EMPTY = 30;
    const TOKEN_REVOKED = 31;
    const USER = 32;
    const USER_PASSWORD = 33;
    const USER_PROFILE = 34;

    const USER_GRANTED_VISIBILITY = 100;
    const USER_LEGAL_INFO = 110;
    const ALL_LEGAL_INFO = 111;
    const VALIDATE_USER_LEGAL_INFO = 112;

    const PRODUCT_LICENSE = 200;
    const SIGN_PRODUCT_LICENSE = 201;
    const PRODUCT_LICENSE_SIGNED = 202;
    const PRODUCT_LICENSE_HABILITATION = 203;
    const PRODUCT_LICENSE_MAX_SIGNATURES = 204;

    const WMS_INFORMATION = 300;

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
