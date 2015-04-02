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
    const HIERACHICAL_FACETS = 17;
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
    
}
