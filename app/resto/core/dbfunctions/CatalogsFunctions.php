<?php
/*
 * Copyright 2018 Jérôme Gasperi
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
 * RESTo PostgreSQL catalogs functions
 */
class CatalogsFunctions
{

    /*
     * These are types created by iTag 
     * Not returned from getSummaries unless explicitely requested
     * to avoid large summaries array
     */
    const TOPONYM_TYPES = array(
        'bay',
        'channel',
        'continent',
        'country',
        'fjord',
        'gulf',
        'inlet',
        'lagoon',
        'ocean',
        'region',
        'river',
        'sea',
        'sound',
        'state',
        'strait'    
    );
    
    /*
     * These are STAC/resto default properties in catalog
     */
    const CATALOG_PROPERTIES = array(
        'id', 'title', 'description', 'type', 'owner', 'visibility', 'rtype', 'stac_version', 'stac_extension', 'links', 'level', 'counters', 'created'
    );

    private $dbDriver = null;

    /**
     * Constructor
     *
     * @param RestoDatabaseDriver $dbDriver
     * @throws Exception
     */
    public function __construct($dbDriver)
    {
        $this->dbDriver = $dbDriver;
    }

    /**
     * Format catalog for output
     *
     * @param array $rawCatalog
     * @param boolean $noProperties
     */
    public static function format($rawCatalog, $noProperties)
    {

        $catalog = array(
            'id' => $rawCatalog['id'],
            'title' => $rawCatalog['title'],
            'description' => $rawCatalog['description'],
            'links' => isset($rawCatalog['links']) ? json_decode($rawCatalog['links'], true) : array(),
            'level' => (integer) $rawCatalog['level'],
            'counters' => isset($rawCatalog['counters']) ? json_decode($rawCatalog['counters'], true) : null,
            'owner' => $rawCatalog['owner'] ?? null,
            'visibility' => json_decode($rawCatalog['visibility'], true),
            'created' => $rawCatalog['created'],
            'rtype' => $rawCatalog['rtype'] ?? null
        );

        if ( $noProperties ) {
            return $catalog;
        }

        $properties = isset($rawCatalog['properties']) ? json_decode($rawCatalog['properties'], true) : array();
        foreach (array_keys($properties) as $key) {
            if ($key === 'resto:links' && is_array($properties['resto:links'])) {
                $catalog['links']= array_merge($catalog['links'], $properties['resto:links']);
                continue;
            }
            $catalog[$key] = $properties[$key];
        }
        
        return $catalog;
    
    }

    /**
     * Get catalog
     *
     * @param string $id
     * @param RestoUser $user
     */
    public function getCatalog($catalogId, $user)
    {
    
        $catalogs = $this->getCatalogs(array(
            'id' => $catalogId
        ), $user, false);

        if ( isset($catalogs) && count($catalogs) === 1) {
            return $catalogs[0];
        }

        return null;

    }

    /**
     * Get catalogs (and eventually all its childs if id is set)
     *
     * @param array $params
     * @param RestoUser $user
     * @param boolean $withChilds
     */
    public function getCatalogs($params, $user, $withChilds)
    {
        
        $catalogs = array();
        $where = array();
        $values = array();
        $params = isset($params) ? $params : array();

        $visibilityClause = RightsFunctions::getVisibilityClause($user);
        if ( isset($visibilityClause) ) {
            $where[] = $visibilityClause;
        } 

        // Direct where clause
        if ( isset($params['where'])) {
            $where[] = $params['where'];
        }

        if ( isset($params['id']) ) {
            $values[] = $params['id'];
            $_where = 'id=$' . count($values);
            if ( $withChilds ) {
                $values[] = $params['id'] . '/%';
                $_where = '(' . $_where . ' OR id LIKE $' . count($values) . ')';
            }
            $where[] = $_where;
        }

        // Filter on description / title
        if ( isset($params['q']) ) {
            $values[] = '%' . $params['q'] . '%';
            $where[] = '(public.normalize(description) ILIKE public.normalize($' . count($values) . ') OR id ILIKE $' . count($values) . ')';
        }
        // [IMPORTANT] Discard level if q is set
        else if ( isset($params['level']) ) {
            $values[] = $params['level'];
            $where[] = 'level=$' . count($values);
        }
        
        try {
            $results = $this->dbDriver->pQuery('SELECT id, title, description, level, counters, owner, links, visibility, rtype, properties, to_iso8601(created) as created FROM ' . $this->dbDriver->targetSchema . '.catalog' . ( empty($where) ? '' : ' WHERE ' . join(' AND ', $where) . ' ORDER BY id ASC'), $values);
            while ($result = pg_fetch_assoc($results)) {
                $catalogs[] = CatalogsFunctions::format($result, $params['noProperties'] ?? false);
            }
        }  catch (Exception $e) {
            RestoLogUtil::httpError(500, $e->getMessage());
        }

        /*
         * Recursively add child collection counters to catalog counters
         */
        return !empty($params['noCount']) ? $catalogs : $this->onTheFlyUpdateCountersWithCollection($catalogs);
    
    }

    /**
     * Get catalog items as STAC links
     *
     * @param string $catalogId
     * @param string $baseUrl
     * @return array
     */
    public function getCatalogItems($catalogId, $baseUrl) 
    {

        $items = [];

        /*
         * Delete (within transaction)
         */
        try {
            $results = $this->dbDriver->pQuery('SELECT featureid, collection FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE path=$1::ltree', array(
                RestoUtil::path2ltree($catalogId)
            ));    
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, $e->getMessage());
        }

        while ($result = pg_fetch_assoc($results)) {
            $items[] = array(
                'rel' => 'item',
                'id' => $result['featureid'],
                'type' => RestoUtil::$contentTypes['geojson'],
                'href' => $baseUrl . '/collections/' . $result['collection'] . '/items/' . $result['featureid']
            );
        }

        return $items;

    }


    /**
     * Store catalogs within database
     *
     * !! THIS FUNCTION IS THREAD SAFE !!
     *
     * @param array $catalogs
     * @param RestoContext $context
     * @param RestoUser $user
     * @param RestoCollection $collection
     * @param array $feature
     * @param boolean addBeginCommit // True means that call is already within a BEGIN/COMMIT block
     */
    public function storeCatalogs($catalogs, $context, $user, $collection, $feature, $addBeginCommit)
    {

        // Empty catalogs - do nothing
        if (!isset($catalogs) || count($catalogs) === 0) {
            return array();
        }

        $collectionId = isset($collection) ? $collection->id : null;

        try {

            if ( $addBeginCommit ) {
                $this->dbDriver->query('BEGIN');
            }

            for ($i = count($catalogs); $i--;) {
                $this->storeCatalog($catalogs[$i], $user, $context, $collectionId, $feature);
            }
    
            // Update all counters at the same time for a given featureId
            if ( isset($feature) ) {
                $this->updateFeatureCatalogsCounters($feature['id'], $collectionId, 1);
            }

            if ( $addBeginCommit) {
                $this->dbDriver->query('COMMIT');
            }

        } catch (Exception $e) {
            if ( $addBeginCommit) {
                $this->dbDriver->query('ROLLBACK');
            }
            RestoLogUtil::httpError($e->getCode() ?? 500, $e->getMessage());
        }
       
        return $catalogs;

    }

    /**
     * Store collection under catalog 
     * 
     * @param array $catalog
     * @param string $userid
     * @param RestoContext $context
     * @return boolean
     */
    public function storeCollectionUnderCatalog($catalogId, $collectionId, $userid, $context)
    {

        $link = array(
            'rel' => 'child',
            'type' => RestoUtil::$contentTypes['json'],
            'href' => $context->core['baseUrl'] . RestoRouter::ROUTE_TO_COLLECTIONS . '/' . $collectionId
        );

        try {

            $links = array();
            $results = $this->dbDriver->pQuery('SELECT links FROM ' . $this->dbDriver->targetSchema . '.catalog WHERE id=$1', array(
                $catalogId
            ));
            while ($result = pg_fetch_assoc($results)) {
                $links = json_decode($result['links'], true) ?? array();
                break;
            }
            // Check duplicate
            $addLink = true;
            for ($i = 0, $ii = count($links); $i < $ii; $i++) {
                if ($links[$i]['href'] === $link['href']) {
                    $addLink = false;
                    break;
                }
            }
            if ($addLink) {
                $links[] = $link;
            }
            $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET links=$2 WHERE id=$1 RETURNING id', array(
                $catalogId,
                json_encode($links, JSON_UNESCAPED_SLASHES)
            ));
            
        } catch (Exception $e) {
            $this->dbDriver->query('ROLLBACK');
            RestoLogUtil::httpError($e->getCode() ?? 500, $e->getMessage());
        }

        return true;
    }

    /**
     * Update catalog 
     * 
     * @param array $catalog
     * @param RestoUser $user
     * @param RestoContext $context
     * @return boolean
     */
    public function updateCatalog($catalog, $user, $context)
    {
        
        if ( !isset($catalog['id']) ) {
            return false;
        }

        $values = array(
            $catalog['id']
        );
        $canBeUpdated = array(
            'title',
            'owner',
            'description',
            'links',
            'visibility'
        );

        $set = array();
        $cleanLinks = $this->getCleanLinks($catalog, $user, $context);
        
        if ( array_key_exists('links', $cleanLinks) ) {
            $catalog['links'] = $cleanLinks['links'];
        }

        $properties = null;
        foreach (array_keys($catalog) as $key ) {
            if (in_array($key, $canBeUpdated)) {
                if ($key === 'links') {
                    $values[] = json_encode($catalog[$key], JSON_UNESCAPED_SLASHES);
                }
                else if ($key === 'visibility') {
                    try {
                        $values[] = QueryUtil::visibilityToSQL($catalog[$key]);
                    } catch (Exception $e) {
                        RestoLogUtil::httpError($e->getCode(), $e->getMessage());
                    }
                }
                else {
                    $values[] = $catalog[$key];
                }
                $set[] = $key . '=$' . count($values);
            }
            // Other properties goes to properties
            else if ( !in_array($key, array('id', 'stac_version')) ){
                if ( !isset($properties) ) {
                    $properties = array();
                }
                $properties[$key] = $catalog[$key];
            }
        }

        // Additional properties
        if ( isset($properties) ) {
            $values['properties'] = json_encode($properties, JSON_UNESCAPED_SLASHES);
            $set[] = 'properties=$' . count($values);
        }
        
        // Nothing to update
        if ( empty($set) ) {
            return false;
        }

        try {
            
            $this->dbDriver->query('BEGIN');

            /*
             * Delete catalog childs BUT NOT HIMSELF and the one in childIds
             */
            if ( array_key_exists('links', $cleanLinks) ) {

                // No childIds => easy !
                if ( empty($cleanLinks['childIds']) ) {
                    $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog WHERE id LIKE $1 RETURNING id', array(
                        $catalog['id'] . '/%'
                    ), 500, 'Cannot update catalog ' . $catalog['id']));
                    $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE path ~ $1 AND path <> $2' , array(
                        RestoUtil::path2ltree($catalog['id']) . '.*',
                        RestoUtil::path2ltree($catalog['id'])
                    ), 500, 'Cannot update catalog_feature association for catalog ' . $catalog['id']));
                }
                else {
                    $ids = array();
                    $paths = array('\'' . RestoUtil::path2ltree($catalog['id']) . '\'');
                    for ($i = 0, $ii = count($cleanLinks['childIds']); $i < $ii; $i++) {
                        $ids[] = '\'' . $this->dbDriver->escape_string( $cleanLinks['childIds'][$i]) . '\'';
                        $paths[] = '\'' . RestoUtil::path2ltree($cleanLinks['childIds'][$i]) . '\'';
                    }
                    $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog WHERE id LIKE $1 AND id NOT IN (' . join(',', $ids) . ') RETURNING id', array(
                        $catalog['id'] . '/%'
                    ), 500, 'Cannot update catalog ' . $catalog['id']));
                    $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE path ~ $1 AND path NOT IN (' . join(',', $paths) . ')' , array(
                        RestoUtil::path2ltree($catalog['id']) . '.*'
                    ), 500, 'Cannot update catalog_feature association for catalog ' . $catalog['id']));
                    
                }
            
            }
            
            /*
             * Then update catalog
             */
            $this->dbDriver->fetch($this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET ' . join(',', $set) . ' WHERE id=$1 RETURNING id', $values, 500, 'Cannot update catalog ' . $catalog['id']));

            /*
             * Add an entry in catalog_feature for each interalItems but first remove all items !
             */
            if ( array_key_exists('links', $cleanLinks) ) {
                $this->removeCatalogFeatures($catalog['id']);
                $this->addInternalItems($cleanLinks['internalItems'] ?? array(), $catalog['id']);
            }

            $this->dbDriver->query('COMMIT');
            
        } catch (Exception $e) {
            $this->dbDriver->query('ROLLBACK');
            RestoLogUtil::httpError($e->getCode() ?? 500, $e->getMessage());
        }
        
        return true;
    }

    /**
     * Increment catalog counters
     *
     * @param string $featureId
     * @param string $collectionId
     * @param integer $increment
     */
    public function updateFeatureCatalogsCounters($featureId, $collectionId, $increment)
    {

        // Increment by increment all catalog counters for featureId
        $query = join(' ', array(
            'WITH path_hierarchy AS (SELECT collection, catalogid FROM ' . $this->dbDriver->targetSchema . '.catalog_feature',
            'WHERE featureid = \'' . $this->dbDriver->escape_string( $featureId) . '\')',
            'UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET counters=public.increment_counters(counters,' . $increment . ', (SELECT path_hierarchy.collection FROM path_hierarchy LIMIT 1))',
            'WHERE id IN (SELECT path_hierarchy.catalogid FROM path_hierarchy)'
        ));
        $nbOfResults = count($this->dbDriver->fetch($this->dbDriver->query($query)));

        // And don't forget the collection !
        if ( isset($collectionId) ) {
            $this->dbDriver->query('UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET counters=public.increment_counters(counters,' . $increment . ',\'' . $collectionId . '\') WHERE id = \'collections/' . $collectionId . '\'');   
            $nbOfResults++;
        }
        
        return $nbOfResults;

    }

    /**
     * Get feature catalogs
     *
     * @param string $featureId
     * @return array
     */
    public function getFeatureCatalogs($featureId)
    {
        
        $catalogs = [];

        $query = 'SELECT c.id, c.title, c.description, c.level, c.counters, c.owner, c.links, c.visibility, c.rtype, to_iso8601(c.created) as created FROM ' . $this->dbDriver->targetSchema . '.catalog c, ' . $this->dbDriver->targetSchema . '.catalog_feature cf WHERE lower(c.id) = lower(cf.path AND cf.featureid=$1 ORDER BY c.id ASC';
        $results = $this->dbDriver->pQuery($query, array(
            $featureId
        ));

        while ($result = pg_fetch_assoc($results)) {
            $catalogs[] = CatalogsFunctions::format($result, false);
        }

        return $catalogs;

    }

    /**
     * Remove catalog from id 
     * 
     * [WARNING] This also will remove all child catalogs 
     *
     * @param string $catalogId
     */
    public function removeCatalog($catalogId)
    {

        try {
            $this->dbDriver->query('BEGIN');
            $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog WHERE id LIKE $1 RETURNING id', array($catalogId . '%'), 500, 'Cannot delete catalog ' . $catalogId));
            $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE path ~ $1' , array(RestoUtil::path2ltree($catalogId) . '.*'), 500, 'Cannot delete catalog_feature association for catalog ' . $catalogId));
            $this->dbDriver->query('COMMIT');
        } catch (Exception $e) {
            $this->dbDriver->query('ROLLBACK');
            RestoLogUtil::httpError(500, $e->getMessage());
        }
        
        return array();

    }

    /**
     * Remove catalog feature - updating catalog counters accordingly
     * 
     * [WARNING] This DOES NOT REMOVE FEATURE IN TABLE feature
     *
     * @param string $featureId
     * @param string $catalogId
     */
    public function removeCatalogFeature($featureId, $catalogId)
    {
        $this->updateFeatureCatalogsCounters($featureId, null, -1);
        $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE path = $1' , array(RestoUtil::path2ltree($catalogId)), 500, 'Cannot delete catalog_feature association for catalog ' . $catalogId));   
    }

    /**
     * Store catalog within database
     *
     * !! THIS FUNCTION IS THREAD SAFE !!
     *
     * @param array $catalog
     * @param RestoUser $user
     * @param RestoContext $context
     * @param string $collectionId
     * @param array $feature
     */
    private function storeCatalog($catalog, $user, $context, $collectionId, $feature)
    {
        // Empty catalog - do nothing
        if (!isset($catalog)) {
            return;
        }

        // Set rtype for Collection
        if ( isset($catalog['type']) && $catalog['type'] === 'Collection' ) {
            $catalog['rtype'] = 'collection';
        }

        // [IMPORTANT] Catalog identifier should never have a trailing /
        if ( substr($catalog['id'], -1) === '/' ) {
            $catalog['id'] = rtrim($catalog['id'], '/');
        }
       
        $cleanLinks = $this->getCleanLinks($catalog, $user, $context);
       
        // For collection, do not store properties since it's a duplication of properties within collection table
        $properties = null;
        if ( isset($catalog['rtype']) && $catalog['rtype'] === 'collection' ) {
            $catalog = array_merge($catalog, [
                'title' => null,
                'description' => null,
                'rtype' => 'collection'
            ]);
        }
        else {
            foreach (array_keys($catalog) as $key ) {
                if ( !in_array($key, CatalogsFunctions::CATALOG_PROPERTIES) ){
                    if ( !isset($properties) ) {
                        $properties = array();
                    }
                    $properties[$key] = $catalog[$key];
                }
            }
        }
        
        // Visibility
        $visibility = QueryUtil::visibilityToSQL($catalog['visibility']) ?? RestoUtil::getDefaultVisibility($user, $context->core['defaultCatalogVisibility']);

        $insert = '(id, title, description, level, counters, owner, visibility, rtype, properties, created) SELECT $1,$2,$3,$4,$5,$6,$7,$8,$9,now()';
        $values = array(
            $catalog['id'],
            $catalog['title'] ?? null,
            $catalog['description'] ?? null,
            isset($catalog['id']) ? count(explode('/', $catalog['id'])) : 0,
            // If no input counter is specified - set to 1
            str_replace('[]', '{}', json_encode(array(
                'total' => 0,
                'collections' => array()
            ), JSON_UNESCAPED_SLASHES)),
            $catalog['owner'] ?? $user->profile['id'],
            $visibility,
            $catalog['rtype'] ?? null,
            isset($properties) ? json_encode($properties, JSON_UNESCAPED_SLASHES) : null
        );
        if ( array_key_exists('links', $cleanLinks) ) {
            $values[] = json_encode($cleanLinks['links'] ?? array(), JSON_UNESCAPED_SLASHES);
            $insert = '(id, title, description, level, counters, owner, visibility, rtype, properties, links, created) SELECT $1,$2,$3,$4,$5,$6,$7,$8,$9,$10,now()';
        }

        $insert = 'INSERT INTO ' . $this->dbDriver->targetSchema . '.catalog ' . $insert . ' ON CONFLICT (id) DO NOTHING';
        $this->dbDriver->pQuery($insert, $values, 500, 'Cannot insert catalog ' . $catalog['id']);

        /*
         * Catalog id are like this
         * 
         * years/2024/06/21
         * years/2024/06
         * years/2024
         * years
         * hashtags/hastagfromdescrption
         * hashtags
         * collections/S2
         * collections
         * 
         * We should ingest 
         * 
         *   1. Non first level catalog (so "years", "hashtags", etc. are discared EXCEPT true catalog one !!
         * 
         *   2. Non rtype = "collection". Since each item mandatory attached to a collection, adding entry to catalog_feature
         *      will basically add one entry per feature
         * 
         *   3. Only the childest catalog so in the previous example only
         * 
         *      years/2024/06/21
         *      hashtags/hastagfromdescrption
         * 
         */
        $catalogLevel = count(explode('/', $catalog['id']));
        $path = RestoUtil::path2ltree($catalog['id']);
        
        if ( !isset($catalog['rtype']) ) {
            $catalog['rtype'] = null;
        }

        if ( isset($feature) && $catalog['rtype'] !== 'collection' && ($catalogLevel > 1 || $catalog['rtype'] === 'catalog')  ) {
            $this->insertIntoCatalogFeature($feature, $path, $catalog['id'], $collectionId);
        }

        /*
         * Add an entry in catalog_feature for each internalItems
         */
        $this->addInternalItems($cleanLinks['internalItems'] ?? array(), $catalog['id']);
        
        return $catalog;

    }

    /**
     * Add one catalog entry in catalog_feature for each input item - increase catalog counters by one accordingly
     * 
     * @param array $items
     * @param string $catalogId
     */
    private function addInternalItems($items, $catalogId)
    {

        // Convert catalog['id'] to LTREE path - first replace dot with underscore
        $path = RestoUtil::path2ltree($catalogId);

        for ($i = 0, $ii = count($items); $i < $ii; $i++) {
            $this->insertIntoCatalogFeature($items[$i], $path, $catalogId, $items[$i]['collection']);
            $query = 'UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET counters=public.increment_counters(counters,1,\'' . $this->dbDriver->escape_string( $items[$i]['collection']) . '\') WHERE id = \'' . $this->dbDriver->escape_string( $catalogId) . '\'';
            $this->dbDriver->fetch($this->dbDriver->query($query));
        }
        
    }

    /**
     * Remove features from a catalog i.e. unassociate feature from a catalog
     * 
     * [WARNING] This DOES NOT REMOVE FEATURE IN TABLE feature
     *
     * @param string $catalogId
     */
    private function removeCatalogFeatures($catalogId)
    {
        $this->dbDriver->query('UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET counters=\'{"total":0, "collections":{}}\' WHERE id = \'' . $this->dbDriver->escape_string( $catalogId) . '\'');
        $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE path = $1' , array(RestoUtil::path2ltree($catalogId)), 500, 'Cannot delete catalog_feature association for catalog ' . $catalogId));   
    }

    /**
     * Return STAC Summaries from catalogs elements from a type for a given collection
     *
     * Returned array of array indexed by collection id
     *
     *      array(
     *          'collection1' => array(
     *                 'type#' => array(
     *                     'value1' => count1,
     *                     'value2' => count2,
     *                     'parent' => array(
     *                         'value3' => count3,
     *                         ...
     *                     )
     *                     ...
     *                 ),
     *                 'type2' => array(
     *                     ...
     *                 ),
     *                 ...
     *          ),
     *          'collection2' => array(
     *                 'type#' => array(
     *                     'value1' => count1,
     *                     'value2' => count2,
     *                     'parent' => array(
     *                         'value3' => count3,
     *                         ...
     *                     )
     *                     ...
     *                 ),
     *                 'type2' => array(
     *                     ...
     *                 ),
     *                 ...
     *          )
     *      )
     * 
     * @param array $types
     * 
     * @return array
     */
    public function getSummaries($types)
    {
        
        $summaries = array();

        $catalogs = $this->getCatalogs(array(
            'where' => !empty($types) ? 'rtype IN (\'' . join('\',\'', $types) . '\')' : 'rtype NOT IN (\'' . join('\',\'', CatalogsFunctions::TOPONYM_TYPES) . '\')'
        ), null, false);
        
        $counter = 0;

        // First create collection pivots
        $pivots = array();
        for ($i = 0, $ii = count($catalogs); $i < $ii; $i++) {

            // Process only collection
            if ( $catalogs[$i]['rtype'] !== 'collection' ) {
                continue;
            }
            $exploded = explode('/', $catalogs[$i]['id']);
            $_collectionId = array_pop($exploded);
            if ( !isset($pivots[$_collectionId]) ) {
                $pivots[$_collectionId] = array(
                    'collection' => array(
                        array(
                            'const' => $_collectionId,
                            'count' => $catalogs[$i]['counters']['total'] ?? 0
                        )
                    )
                );
            }
        }

        // Populate with summaries i.e. other rtype
        for ($i = 0, $ii = count($catalogs); $i < $ii; $i++) {
         
            if ( $catalogs[$i]['rtype'] === 'collection' ) {
                continue;
            }

            $type = $catalogs[$i]['rtype'];

            if ( isset($catalogs[$i]['counters']['collections']) ) {
                foreach (array_keys($catalogs[$i]['counters']['collections']) as $_collectionId) {

                    if ( !isset($pivots[$_collectionId][$type]) ) {
                        $pivots[$_collectionId][$type] = array();
                    }

                    // Constant is the last part of the id url
                    $exploded = explode('/', $catalogs[$i]['id']);
                    $const = array_pop($exploded);

                    $newPivot = array(
                        'const' => $const,
                        'count' => $catalogs[$i]['counters']['collections'][$_collectionId]
                    );

                    if ($catalogs[$i]['title'] !== $newPivot['const']) {
                        $newPivot['title'] = $catalogs[$i]['title'];
                    }

                    $pivots[$_collectionId][$type][] = $newPivot;

                }
            }
        }
        
        foreach (array_keys($pivots) as $_collectionId) {
            if ( !isset($summaries[$_collectionId]) ) {
                $summaries[$_collectionId] = array();
            }
            foreach (array_keys($pivots[$_collectionId]) as $key) {
                if (count($pivots[$_collectionId][$key]) === 1) {
                    $summaries[$_collectionId][$key] = array_merge($pivots[$_collectionId][$key][0], array('type' => 'string'));
                } else {
                    $summaries[$_collectionId][$key] = array(
                        'type' => 'string',
                        'oneOf' => $pivots[$_collectionId][$key]
                    );
                }
            }
        }

        return $summaries;
        
    }

    /**
     * Create catalog -> featureId association
     * 
     * @param array $feature
     * @param string $path
     * @param string $catalogId
     * @param string $collectionId
     */
    private function insertIntoCatalogFeature($feature, $path, $catalogId, $collectionId) {

        $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.catalog_feature SET featureid=$1, path=$2, catalogid=$3, collection=$4, title=$5 WHERE featureid=$1 AND path @> $2::ltree AND nlevel(path) < nlevel($2::ltree)', array(
            $feature['id'],
            $path,
            $catalogId,
            $collectionId,
            $feature['title'] ?? null
        ), 500, 'Cannot create association for ' . $feature['id'] . ' in catalog ' . $catalogId);
        
        $this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->targetSchema . '.catalog_feature (featureid, path, catalogid, collection, title) SELECT $1, $2::ltree, $3, $4, $5 WHERE NOT EXISTS (SELECT 1 FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE featureid = $1 AND (path <@ $2::ltree OR path @> $2::ltree))', array(
            $feature['id'],
            $path,
            $catalogId,
            $collectionId,
            $feature['title'] ?? null
        ), 500, 'Cannot create association for ' . $feature['id'] . ' in catalog ' . $catalogId);
      
    }

    /**
     * Return a "cleaned" list of catalog links.
     * Cleaned list means :
     *  - discard root, parent and self links
     *  - move first level child links that belong to user to update array
     *  - keep non first level child links
     * 
     * @param array $catalog
     * @param RestoUser $user
     * @param RestoContext $context
     * @return array
     */
    private function getCleanLinks($catalog, $user, $context) {

        $output = array(
            'childIds' => array(),
            'internalItems' => array()
        );

        if ( !array_key_exists('links', $catalog) ) {
            return $output;
        };

        $output['links'] = array();

        if ( empty($catalog['links']) ) {
            return $output;
        };

        for ($i = 0, $ii = count($catalog['links']); $i < $ii; $i++) {
            $link = $catalog['links'][$i];
            if ( !isset($link['rel']) || in_array($link['rel'], array('root', 'parent', 'self')) ) {
                continue;
            }
            
            if ( in_array($link['rel'], array('child', 'item', 'items')) ) {
                
                if ( !isset($link['href']) ) {
                    RestoLogUtil::httpError(400, 'One link has an empty href');    
                }
            
                /*
                 * [IMPORTANT] Only put EXTERNAL item/items to links array. Local one are processed later on
                 */
                if ( in_array($link['rel'], array('item', 'items')) ) {
                    
                    if ( !str_starts_with($link['href'], $context->core['baseUrl'] . RestoRouter::ROUTE_TO_COLLECTIONS ) ) {
                        $output['links'][] = $link;
                        continue;
                    }

                    $exploded = explode('/', substr($link['href'], strlen($context->core['baseUrl'] . RestoRouter::ROUTE_TO_COLLECTIONS) + 1));
                    // A item endpoint is /collections/{collectionId}/items/{featureId}
                    if (count($exploded) === 3) {

                        // Eventually convert collection alias to real collection id
                        $collectionId = (new CollectionsFunctions($this->dbDriver))->aliasToCollectionId($exploded[0]) ?? $exploded[0];
                        $internalItem = array(
                            'id' => RestoUtil::isValidUUID($exploded[2]) ? $exploded[2] : RestoUtil::toUUID($exploded[2]),
                            'href' => $link['href'],
                            'collection' => $collectionId
                        );

                        // Check for link existence !
                        $minimalFeature = (new FeaturesFunctions($this->dbDriver))->getMinimalFeature($internalItem['id'], $this->dbDriver->targetSchema . '.feature', $internalItem['collection']);
                        if ( empty($minimalFeature) ) {
                            RestoLogUtil::httpError(400, 'Feature ' . $internalItem['href'] . ' does not exist. Ingest it first !');
                        }
                        else {
                            $internalItem['title'] = $link['title'] ?? $minimalFeature[0]['title'];
                        }

                        $output['internalItems'][] = $internalItem;

                        continue;
                    }

                }

                
                if ( $link['rel'] === 'child') {

                    /*
                     * Avoid cycling (i.e. catalog self referencing one of its parent)
                     */
                    if (str_starts_with($link['href'], $context->core['baseUrl'] . RestoRouter::ROUTE_TO_CATALOGS )) {
                        $childId = substr($link['href'], strlen($context->core['baseUrl'] . RestoRouter::ROUTE_TO_CATALOGS) + 1);
                        $exploded = explode('/', $childId);
                        if ( count($exploded) <= count(explode('/', $catalog['id'])) ) {
                            RestoLogUtil::httpError(400, 'Child ' . $link['href'] . ' is invalid because it references a parent resource');
                        }
                        // Keep track of child ids for delete before update
                        else {
                            $output['childIds'][] = $childId;
                        }
                    }

                    /*
                     * Store local collection within links
                     */
                    if (str_starts_with($link['href'], $context->core['baseUrl'] . RestoRouter::ROUTE_TO_COLLECTIONS )) {
                        continue;   
                    }

                }
                
                $exploded = explode($context->core['baseUrl'] . RestoRouter::ROUTE_TO_CATALOGS . '/', $link['href']);
                if ( count($exploded) !== 2) {
                    RestoLogUtil::httpError(400, 'One link child has an external href i.e. not starting with ' . $context->core['baseUrl'] . RestoRouter::ROUTE_TO_CATALOGS);    
                }

                $childCatalog = $this->getCatalog($exploded[1], $user);
                if ( $childCatalog === null ) {
                    RestoLogUtil::httpError(400, 'Catalog child ' . $link['href'] . ' does not exist');    
                }

            }

        }
        
        return $output;

    }

    /**
     * Return an update links array and counters object by adding child collection counters
     * to the input catalog
     * 
     * @param array $catalogs
     */
    private function onTheFlyUpdateCountersWithCollection($catalogs)
    {
        
        $collectionsList = [];
        for ($i = 0, $ii = count($catalogs); $i < $ii; $i++){
            if ($catalogs[$i]['rtype'] === 'collection') {
                $collectionsList[] = '\'collections/' . substr($catalogs[$i]['id'], strrpos($catalogs[$i]['id'], '/') + 1) . '\'';
            }
        }

        // First get collections counts
        if ( !empty($collectionsList) ) {
            try {
                $query = 'WITH tmp AS (SELECT id, substring(id, 13) as collectionid, counters, title, description FROM ' . $this->dbDriver->targetSchema . '.catalog WHERE id IN (' . join(',', $collectionsList) .  '))';
                $query = $query . ' SELECT tmp.id, col.title, col.description, tmp.counters FROM ' . $this->dbDriver->targetSchema . '.collection as col, tmp WHERE col.id = tmp.collectionid';
                $results = $this->dbDriver->query($query);
                while ($result = pg_fetch_assoc($results)) {
                    for ($i = 0, $ii = count($catalogs); $i < $ii; $i++) {
                        if ($catalogs[$i]['rtype'] === 'collection' && $result['id'] === 'collections/' . substr($catalogs[$i]['id'], strrpos($catalogs[$i]['id'], '/') + 1)) {
                            $catalogs[$i] = array_merge($catalogs[$i], [
                                'counters' => json_decode($result['counters'], true),
                                'title' => $result['title'] ?? null,
                                'description' => $result['description'] ?? null
                            ]);
                            break;
                        }
                    }
                }
            }  catch (Exception $e) {
                RestoLogUtil::httpError(500, $e->getMessage());
            }
        }
        
        $catalogsUpdated = array();
        for ($i = 0, $ii = count($catalogs); $i < $ii; $i++)
        {   
            $catalogsUpdated[] = $this->computeCountersSum($catalogs[$i], $catalogs);
        }
        
        return $catalogsUpdated;
    }

    /** 
     * Calculate the total counter for a given path and its children
     * 
     * @param array $parentCatalog
     */
    private function computeCountersSum($parentCatalog, $catalogs) {

        $parentCatalogId = $parentCatalog['id'] . '/';

        // Iterate over all catalog entries
        for ($k = 0, $kk = count($catalogs); $k < $kk; $k++) {
            
            $catalog = $catalogs[$k];
            
            // Check if the catalog's path starts with the parent path
            if ( !str_starts_with($catalog['id'], $parentCatalogId) ) {
                continue;
            }

            $parentCatalog['counters']['total'] = $parentCatalog['counters']['total'] + $catalog['counters']['total'];

        }

        return $parentCatalog;
    }

}
