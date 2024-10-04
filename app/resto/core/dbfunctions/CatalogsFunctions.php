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
            $_where = 'lower(id) = lower($' . count($values) . ')';
            if ( $withChilds ) {
                $values[] = $params['id'] . '/%';
                $_where = '(' . $_where . ' OR lower(id) LIKE lower($' . count($values) . '))';
            }
            $where[] = $_where;
        }

        // Filter on description / title
        if ( isset($params['q']) ) {
            $values[] = '%' . $params['q'] . '%';
            $where[] = '(public.normalize(description) ILIKE public.normalize($' . count($values) . ') OR public.normalize(hashtag) ILIKE public.normalize($' . count($values) . ') )';
        }
        // [IMPORTANT] Discard level if q is set
        else if ( isset($params['level']) ) {
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
            $upsert = 'UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET counters=public.increment_counters(counters, 1, ' . (isset($collectionId) ? '\'' . $collectionId . '\'' : 'NULL') . ') WHERE lower(id)=lower($1)';
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
             *   1. Non first level catalog (so "years", "hashtags" and "collections" are discared EXCEPT true catalog one !!
             * 
             * 
             *   2. Only the childest catalog so in the previous example only
             * 
             *      years/2024/06/21
             *      hashtags/hastagfromdescrption
             *      collections/S2
             * 
             */
            $catalogLevel = count(explode('/', $catalog['id']));
            // Convert catalogId to LTREE path - first replace dot with underscore
            $path = RestoUtil::path2ltree($catalog['id']);
                
            if ( isset($featureId) && ($catalogLevel > 1 || $catalog['rtype'] === 'catalog')  ) {
                $this->insertIntoCatalogFeature($featureId, $path, $catalog['id'], $collectionId);
            }

            /*
             * Add an entry in catalog_feature for each interalItems
             */
            for ($i = 0, $ii = count($cleanLinks['internalItems']); $i < $ii; $i++) {
                $this->insertIntoCatalogFeature($cleanLinks['internalItems'][$i]['id'], $path, $catalog['id'], $cleanLinks['internalItems'][$i]['collection']);
            }

            /*
             * Now the tricky part - change catalogs level
             */
            for ($i = 0, $ii = count($cleanLinks['updateCatalogs']); $i < $ii; $i++) {
                $updateCatalogs = $cleanLinks['updateCatalogs'][$i];
                $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET id=$2, level=level + 1 WHERE lower(id)=lower($1)', array(
                    $updateCatalogs['id'],
                    $catalog['id'] . '/' . $updateCatalogs['id']
                ), 500, 'Cannot update child link ' . $updateCatalogs['id']);
                $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.catalog_feature SET path=$2 WHERE path=$1', array(
                    RestoUtil::path2ltree($updateCatalogs['id']),
                    RestoUtil::path2ltree($catalog['id'] . '/' . $updateCatalogs['id'])
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
        
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.catalog SET ' . join(',', $set) . ' WHERE lower(id)=lower($1) RETURNING id', $values, 500, 'Cannot update catalog ' . $catalog['id']));

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
            'WHERE lower(id) IN (\'' . strtolower(join('\',\'', $catalogIds)) . '\') RETURNING id'
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
            'WITH path_hierarchy AS (SELECT distinct featureid, subpath(path, 0, generate_series(1, nlevel(path))) AS p FROM resto.catalog_feature',
            'WHERE featureid = \'' . pg_escape_string($this->dbDriver->getConnection(), $featureId) . '\')',
            'UPDATE resto.catalog SET counters=public.increment_counters(counters,' . $increment . ',' . (isset($collectionId) ? '\'' . $collectionId . '\'': 'NULL') . ')',
            'WHERE lower(id) IN (SELECT LOWER(REPLACE(REPLACE(path_hierarchy.p::text, \'_\', \'.\'), \'.\', \'/\')) FROM path_hierarchy)'
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

        $query = 'SELECT c.id, c.title, c.description, c.level, c.counters, c.owner, c.links, c.visibility, c.rtype, c.hashtag, to_iso8601(c.created) as created FROM ' . $this->dbDriver->targetSchema . '.catalog c, ' . $this->dbDriver->targetSchema . '.catalog_feature cf WHERE lower(c.id) = lower(cf.path AND cf.featureid=$1 ORDER BY c.id ASC';
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

        try {

            $this->dbDriver->query('BEGIN');

            $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog WHERE lower(id)=lower($1) RETURNING id', array($catalogId), 500, 'Cannot delete catalog' . $catalogId));
            $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE path=$1' , array(RestoUtil::path2ltree($catalogId)), 500, 'Cannot update features' . $catalogId));
        
            $this->dbDriver->query('COMMIT');

        } catch (Exception $e) {
            $this->dbDriver->query('ROLLBACK');
            RestoLogUtil::httpError(500, $e->getMessage());
        }
        
        return array();

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
        ));
        
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
                            'count' => $catalogs[$i]['counters']['collections'][$_collectionId]
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
     */
    private function insertIntoCatalogFeature($featureId, $path, $catalogId, $collectionId) {

        $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.catalog_feature SET featureid=$1, path=$2, catalogid=$3, collection=$4 WHERE featureid=$1 AND path @> $2::ltree AND nlevel(path) < nlevel($2::ltree)', array(
            $featureId,
            $path,
            $catalogId,
            $collectionId
        ), 500, 'Cannot create association for ' . $featureId . ' intp catalog ' . $catalogId);
        
        $this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->targetSchema . '.catalog_feature (featureid, path, catalogid, collection) SELECT $1, $2::ltree, $3, $4 WHERE NOT EXISTS (SELECT 1 FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE featureid = $1 AND (path <@ $2::ltree OR path @> $2::ltree))', array(
            $featureId,
            $path,
            $catalogId,
            $collectionId
        ), 500, 'Cannot create association for ' . $featureId . ' intp catalog ' . $catalogId);
      
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
            'updateCatalogs' => array(),
            'internalItems' => array()
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
                
                /*
                 * [IMPORTANT] Only put EXTERNAL item/items to links array. Local one are processed later on
                 */
                if ( in_array($link['rel'], array('item', 'items')) ) {
                    
                    if ( !str_starts_with($link['href'], $baseUrl . RestoRouter::ROUTE_TO_COLLECTIONS ) ) {
                        $output['links'][] = $link;
                        continue;
                    }

                    $exploded = explode('/', substr($link['href'], strlen($baseUrl . RestoRouter::ROUTE_TO_COLLECTIONS) + 1));
                    if (count($exploded) === 2) {
                        $output['internalItems'][] = array(
                            'id' => RestoUtil::isValidUUID($exploded[1]) ? $exploded[1] : RestoUtil::toUUID($exploded[1]),
                            'href' => $link['href'],
                            'collection' => $exploded[0]
                        );
                        continue;
                    }

                }

                /*
                 * [TODO] Local collection -should not be in links but should appears in catalog 
                 *  under /catalogs/catalogThatIsIngested/{collectionId} so we can keep trace of this in item ??
                 */
                if ( $link['rel'] === 'child' && str_starts_with($link['href'], $baseUrl . RestoRouter::ROUTE_TO_COLLECTIONS )) {
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
                    array_push($output['updateCatalogs'], ...$this->getCatalogs(array('id' => $childCatalog['id']), true));
                }
                else {
                    $output['links'][] = $link;
                }
                
            }

        }
        
        return $output;

    }

}
