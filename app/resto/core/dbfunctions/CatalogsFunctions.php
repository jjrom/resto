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
     */
    public static function format($rawCatalog)
    {
        return array(
            'id' => $rawCatalog['id'],
            'title' => $rawCatalog['title'],
            'description' => $rawCatalog['description'],
            'links' => isset($rawCatalog['links']) ? json_decode($rawCatalog['links'], true) : array(),
            'level' => (integer) $rawCatalog['level'],
            'counters' => isset($rawCatalog['counters']) ? json_decode($rawCatalog['counters'], true) : null,
            'owner' => $rawCatalog['owner'] ?? null,
            'visibility' => (integer) $rawCatalog['visibility'],
            'created' => $rawCatalog['created'],
            'rtype' => $rawCatalog['rtype'] ?? null,
            'hashtag' => $rawCatalog['hashtag'] ?? null
        );
    }

    /**
     * Get catalog
     *
     * @param string $id
     */
    public function getCatalog($catalogId)
    {
    
        $catalogs = $this->getCatalogs(array(
            'id' => $catalogId
        ));

        if ( isset($catalogs) && count($catalogs) === 1) {
            return $catalogs[0];
        }

        return null;

    }

    /**
     * Get catalogs (and eventually all its childs if id is set)
     *
     * @param string $params
     * @param boolean $withChilds
     */
    public function getCatalogs($params, $withChilds = false)
    {

        $catalogs = array();
        $where = array();
        $values = array();
        $params = isset($params) ? $params : array();

        // Direct where clause
        if ( isset($params['where'])) {
            $where[] = $params['where'];
        }

        if ( isset($params['id']) ) {
            $values[] = $params['id'];
            $_where = 'public.normalize(id) = public.normalize($' . count($values) . ')';
            if ( $withChilds ) {
                $values[] = $params['id'] . '/%';
                $_where = '(' . $_where . ' OR public.normalize(id) LIKE public.normalize($' . count($values) . '))';
            }
            $where[] = $_where;
        }

        if ( isset($params['description']) ) {
            $values[] = '%' . $params['description'] . '%';
            $where[] = 'public.normalize(description) LIKE public.normalize($' . count($values) . ')';
        }

        if ( isset($params['level']) ) {
            $values[] = $params['level'];
            $where[] = 'level=$' . count($values);
        }
        
        try {
            $results = $this->dbDriver->pQuery('SELECT id, title, description, level, counters, owner, links, visibility, rtype, hashtag, to_iso8601(created) as created FROM ' . $this->dbDriver->targetSchema . '.catalog' . ( empty($where) ? '' : ' WHERE ' . join(' AND ', $where) . ' ORDER BY id ASC'), $values);
            while ($result = pg_fetch_assoc($results)) {
                $catalogs[] = CatalogsFunctions::format($result);
            }
        }  catch (Exception $e) {
            RestoLogUtil::httpError(500, $e->getMessage());
        }

        return $catalogs;
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
            $results = $this->dbDriver->pQuery('SELECT featureid, collection FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE catalogid=$1', array(
                $catalogId
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
     * Store catalog within database
     *
     * !! THIS FUNCTION IS THREAD SAFE !!
     *
     * @param array $catalog
     * @param string $userid
     * @param string $baseUrl
     * @param string $collectionId
     * @param string $featureId
     */
    public function storeCatalog($catalog, $userid, $baseUrl, $collectionId, $featureId)
    {
        // Empty catalog - do nothing
        if (!isset($catalog)) {
            return;
        }

        $counters = array(
            'total' => 1,
            'collections' => array()
        );

        if (isset($collectionId)) {
            $counters['collections'][$collectionId] = 1;
        }

        $cleanLinks = $this->getCleanLinks($catalog, $userid, $baseUrl);

        try {

            $this->dbDriver->query('BEGIN');

            /*
             * Thread safe ingestion using upsert - guarantees that counter is correctly incremented during concurrent transactions
             */
            $insert = 'INSERT INTO ' . $this->dbDriver->targetSchema . '.catalog (id, title, description, level, counters, owner, links, visibility, rtype, hashtag, created) SELECT $1,$2,$3,$4,$5,$6,$7,$8,$9,$10,now()';
            $upsert = 'UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET counters=public.increment_counters(counters, 1, ' . (isset($collectionId) ? '\'' . $collectionId . '\'' : 'NULL') . ') WHERE public.normalize(id)=public.normalize($1)';
            $this->dbDriver->pQuery('WITH upsert AS (' . $upsert . ' RETURNING *) ' . $insert . ' WHERE NOT EXISTS (SELECT * FROM upsert)', array(
                $catalog['id'],
                $catalog['title'] ?? $catalog['id'],
                $catalog['description'] ?? null,
                isset($catalog['id']) ? count(explode('/', $catalog['id'])) : 0,
                // If no input counter is specified - set to 1
                json_encode($counters, JSON_UNESCAPED_SLASHES),
                $catalog['owner'] ?? $userid,
                json_encode($cleanLinks['links'], JSON_UNESCAPED_SLASHES),
                RestoConstants::GROUP_DEFAULT_ID,
                $catalog['rtype'] ?? null,
                $catalog['hashtag'] ?? null
            ), 500, 'Cannot insert catalog ' . $catalog['id']);

            // Feature is set => fill catalog_feature table
            if ( isset($featureId) && isset($catalog['hashtag']) ) {
                $this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->targetSchema . '.catalog_feature (featureid, catalogid, hashtag, collection) VALUES ($1,$2,$3,$4) ON CONFLICT (featureid, catalogid) DO NOTHING', array(
                    $featureId,
                    $catalog['id'],
                    $catalog['hashtag'],
                    $collectionId
                ), 500, 'Cannot catalog_feature association ' . $catalog['id'] . '/' . $featureId);
            }
            
            /*
             * Now the tricky part - change catalogs level
             */
            for ($i = 0, $ii = count($cleanLinks['updateCatalogs']); $i < $ii; $i++) {
                $updateCatalogs = $cleanLinks['updateCatalogs'][$i];
                $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET id=$2, level=level + 1 WHERE id=$1', array(
                    $updateCatalogs['id'],
                    $catalog['id'] . '/' . $updateCatalogs['id']
                ), 500, 'Cannot update child link ' . $updateCatalogs['id']);
                $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.catalog_feature SET catalogid=$2 WHERE catalogid=$1', array(
                    $updateCatalogs['id'],
                    $catalog['id'] . '/' . $updateCatalogs['id']
                ), 500, 'Cannot update catalog feature association for child link ' . $updateCatalogs['id']);
            }
            
            $this->dbDriver->query('COMMIT');

        } catch (Exception $e) {
            $this->dbDriver->query('ROLLBACK');
            RestoLogUtil::httpError(500, $e->getMessage());
        }

        return $catalog;

    }


    /**
     * Store catalogs within database
     *
     * !! THIS FUNCTION IS THREAD SAFE !!
     *
     * @param array $catalogs
     * @param string $userid
     * @param RestoCollection $collection
     * @param string $featureId
     */
    public function storeCatalogs($catalogs, $userid, $collection, $featureId)
    {
        // Empty catalogs - do nothing
        if (!isset($catalogs) || count($catalogs) === 0) {
            return array();
        }

        $collectionId = null;
        $baseUrl = null;
        if (isset($collection)) {
            $collectionId = $collection->id;
            $baseUrl = $collection->context->core['baseUrl'];
        }
        for ($i = count($catalogs); $i--;) {
            $this->storeCatalog($catalogs[$i], $userid, $baseUrl, $collectionId, $featureId);
        }

        return $catalogs;

    }

    /**
     * Update catalog 
     * 
     * @param array $catalog
     * @return integer // number of catalogs updated
     */
    public function updateCatalog($catalog)
    {
        
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
        foreach (array_keys($catalog) as $key ) {
            if (in_array($key, $canBeUpdated)) {
                $values[] = $key === 'links' ? json_encode($catalog[$key], JSON_UNESCAPED_SLASHES) : $catalog[$key];
                $set[] = $key . '=$' . count($values);
            }
        }

        if ( empty($set) ) {
            return array(
                'catalogsUpdated' => 0
            );
        }
        
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET ' . join(',', $set) . ' WHERE public.normalize(id)=public.normalize($1) RETURNING id', $values, 500, 'Cannot update catalog ' . $catalog['id']));

        return array(
            'catalogsUpdated' => count($results)
        );

    }

    /**
     * Increment input catalogs count
     *
     * @param array $catalogs
     * @param string $collectionId
     * @param integer $increment
     */
    public function updateCatalogsCounters($catalogs, $collectionId, $increment)
    {

        $catalogIds = [];
        for ($i = count($catalogs); $i--;) {
            $catalogIds[] = $catalogs[$i]['id'];
        } 

        $query = join(' ', array(
            'UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET counters=public.increment_counters(counters,' . $increment . ',' . (isset($collectionId) ? '\'' . $collectionId . '\'': 'NULL') . ')',
            'WHERE id IN (\'' . join('\',\'', $catalogIds) . '\') RETURNING id'
        ));

        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        
        return count($results);

    }


    /**
     * Increment all catalogs relied to feature
     *
     * @param string $featureId
     * @param string $collectionId
     * @param integer $increment
     */
    public function updateFeatureCatalogsCounters($featureId, $collectionId, $increment)
    {

        $query = join(' ', array(
            'UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET counters=public.increment_counters(c.counters,' . $increment . ',' . (isset($collectionId) ? '\'' . $collectionId . '\'': 'NULL') . ')',
            'FROM ' . $this->dbDriver->targetSchema . '.catalog c,' . $this->dbDriver->targetSchema . '.catalog_feature cf',
            'WHERE c.id = cf.catalogid AND cf.featureid=\'' . pg_escape_string($this->dbDriver->getConnection(), $featureId) . '\' RETURNING c.id'
        ));

        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        
        return count($results);

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

        $query = 'SELECT c.id, c.title, c.description, c.level, c.counters, c.owner, c.links, c.visibility, c.rtype, c.hashtag, to_iso8601(c.created) as created FROM ' . $this->dbDriver->targetSchema . '.catalog c, ' . $this->dbDriver->targetSchema . '.catalog_feature cf WHERE c.id = cf.catalogid AND cf.featureid=$1 ORDER BY id ASC';
        $results = $this->dbDriver->pQuery($query, array(
            $featureId
        ));

        while ($result = pg_fetch_assoc($results)) {
            $catalogs[] = CatalogsFunctions::format($result);
        }

        return $catalogs;

    }

    /**
     * Remove catalog from id - can only works if catalog has no child
     *
     * @param string $catalogId
     */
    public function removeCatalog($catalogId)
    {

        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog WHERE public.normalize(id)=public.normalize($1) RETURNING id', array($catalogId), 500, 'Cannot delete catalog' . $catalogId));
        $catalogsDeleted = count($results);

        // Next remove the catalog entry from all features
        $query = join(' ', array(
                'UPDATE ' . $this->dbDriver->targetSchema . '.feature SET',
                'hashtags=ARRAY_REMOVE(hashtags, $1),normalized_hashtags=ARRAY_REMOVE(normalized_hashtags,public.normalize($1)),',
                'keywords=(SELECT json_agg(e) FROM json_array_elements(keywords) AS e WHERE e->>\'id\' <> $1)',
                'WHERE normalized_hashtags @> public.normalize_array(ARRAY[$1]) RETURNING id'
            )
        );
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery($query, array($catalogId), 500, 'Cannot update features' . $catalogId));
        
        return array(
            'catalogDeleted' => $catalogsDeleted,
            'featuresUpdated' => count($results)
        );

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
     * @param string $collectionId
     * 
     * @return array
     */
    public function getSummaries($types, $collectionId)
    {
        
        $summaries = array();

        $pivots = array();
        
        $catalogs = $this->getCatalogs(array(
            'where' => !empty($types) ? 'rtype IN (\'' . join('\',\'', $types) . '\')' : 'rtype NOT IN (\'' . join('\',\'', CatalogsFunctions::TOPONYM_TYPES) . '\')'
        ));
        
        $counter = 0;
        for ($i = 0, $ii = count($catalogs); $i < $ii; $i++) {
         
            // Discard level 1 catalog and empty one
            if ( $catalogs[$i]['level'] === 1 || !isset($catalogs[$i]['counters']) || $catalogs[$i]['counters']['total'] === 0 ) {
                continue;
            }

            // Collection is set => get only catalogs with collectionId counter > 0
            // Otherwise get only catalogs with total counter > 0
            if ( isset($collectionId) ) {
                if ( !isset($catalogs[$i]['counters']['collections'][$collectionId]) || $catalogs[$i]['counters']['collections'][$collectionId] === 0 ) {
                    continue;
                }
                $counter = $catalogs[$i]['counters']['collections'][$collectionId];
            }
            else {
                $collectionId = '*';
                $counter = $catalogs[$i]['counters']['total'];
            }

            if ( !isset($pivots[$collectionId]) ) {
                $pivots[$collectionId] = array();
            }
            
            $type = $catalogs[$i]['rtype'];
            
            if (!isset($pivots[$collectionId][$type])) {
                $pivots[$collectionId][$type] = array();
            }

            $create = true;
            
            // Constant is the last part of the id url
            $exploded = explode('/', $catalogs[$i]['id']);
            $const = array_pop($exploded);
            for ($j = count($pivots[$collectionId][$type]); $j--;) {
                if (isset($pivots[$collectionId][$type][$j]['const'])) {
                    if ($pivots[$collectionId][$type][$j]['const'] === $const) {
                        $pivots[$collectionId][$type][$j]['count'] += $counter;
                        $create = false;
                        break;
                    }
                }
            }
            
            if ($create) {
                $newPivot = array(
                    'const' => $const,
                    'count' => $counter
                );
                
                if ($catalogs[$i]['title'] !== $newPivot['const']) {
                    $newPivot['title'] = $catalogs[$i]['title'];
                }
                $pivots[$collectionId][$type][] = $newPivot;
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
     * Return a diff between $oldCatalogs array and $newCatalogs array
     * 
     * @param Array $oldCatalogs
     * @param Array $newCatalogs
     */
    public function diff($oldCatalogs, $newCatalogs)
    {
        $output = array(
            'added' => array(),
            'removed' => array()
        );

        for ($i = count($newCatalogs); $i--;) {

            // Catalogs without hashtag are discarded because they are not stored within
            // catalog_feature table
            if ( !isset($newCatalogs[$i]['hashtag']) ) {
                continue;
            }

            $isNew = true;
            for ($j = count($oldCatalogs); $j--;) {
                // Catalog exist => do nothing
                if ($oldCatalogs[$j]['id'] === $newCatalogs[$i]['id']) {
                    $isNew = false;
                    break;
                }
            }
            if ($isNew) {
                $output['added'][] = $newCatalogs[$i];
            }
        }

        for ($i = count($oldCatalogs); $i--;) {

            // Catalogs without hashtag are discarded because they are not stored within
            // catalog_feature table
            if ( !isset($oldCatalogs[$i]['hashtag']) ) {
                continue;
            }

            $isRemoved = true;
            for ($j = count($newCatalogs); $j--;) {
                // Catalog exist => do nothing
                if ($oldCatalogs[$i]['id'] === $newCatalogs[$j]['id']) {
                    $isRemoved = false;
                    break;
                }
            }
            if ($isRemoved) {
                $output['removed'][] = $oldCatalogs[$i];
            }
        }

        return $output;

    }

    /**
     * Return a "cleaned" list of catalog links.
     * Cleaned list means :
     *  - discard root, parent and self links
     *  - move first level child links that belong to user to update array
     *  - keep non first level child links
     * 
     * @param array $catalog
     * @param string userid
     * @param string $baseUrl
     * @return array
     */
    private function getCleanLinks($catalog, $userid, $baseUrl) {

        $output = array(
            'links' => array(),
            'updateCatalogs' => array()
        );

        if ( !isset($catalog['links']) ) {
            return $output;
        }

        for ($i = 0, $ii = count($catalog['links']); $i < $ii; $i++) {
            $link = $catalog['links'][$i];
            if ( !isset($link['rel']) || in_array($link['rel'], array('root', 'parent', 'self')) ) {
                continue;
            }
            
            if ( in_array($link['rel'], array('child', 'item', 'items')) ) {
                if ( !isset($link['href']) ) {
                    return RestoLogUtil::httpError(400, 'One link child has an empty href');    
                }
                if (in_array($link['rel'], array('item', 'items')) || str_starts_with($link['href'], $baseUrl . RestoRouter::ROUTE_TO_COLLECTIONS )) {
                    $output['links'][] = $link;
                    continue;
                }
                $exploded = explode($baseUrl . RestoRouter::ROUTE_TO_CATALOGS . '/', $link['href']);
                if ( count($exploded) !== 2) {
                    return RestoLogUtil::httpError(400, 'One link child has an external href i.e. not starting with ' . $baseUrl . RestoRouter::ROUTE_TO_CATALOGS);    
                }
                $childCatalog = $this->getCatalog($exploded[1]);
                if ( $childCatalog === null ) {
                    return RestoLogUtil::httpError(400, 'Catalog child ' . $link['href'] . ' does not exist in database');    
                }
                
                if ($childCatalog['level'] === 1 && $childCatalog['owner'] === $userid) {
                    $output['updateCatalogs'][] = $childCatalog;
                }
                else {
                    $output['links'][] = $link;
                }
                
            }

        }
        
        return $output;

    }

}
