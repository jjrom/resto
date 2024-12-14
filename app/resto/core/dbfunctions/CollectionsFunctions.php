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
 * RESTo PostgreSQL collections functions
 */
class CollectionsFunctions
{
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
     * Get collection
     *
     * @param string $id
     * @param RestoUser $user
     * @return array
     * @throws Exception
     */
    public function getCollection($id, $user)
    {

        // Eventually convert input alias to the real collection id
        $collectionId = $this->aliasToCollectionId($id); 
        if ( isset($collectionId) ) {
            $id = $collectionId;
        }

        $where = array(
            'id=$1'
        );

        // Visibility
        $visibilityFilter = (new FiltersFunctions(null, null, null))->prepareFilterQueryVisibility($this->dbDriver->targetSchema . '.collection', $user);
        if ( isset($visibilityFilter) ) {
            $where[] = $visibilityFilter['value'];
        }

        // Query with aliases
        $query = join(' ', array(
            'SELECT id, title, description, version, visibility, owner, model, licenseid, to_iso8601(startdate) as startdate, to_iso8601(completiondate) as completiondate, Box2D(bbox) as box2d, providers, properties, links, assets, array_to_json(keywords) as keywords, STRING_AGG(ca.alias, \', \' ORDER BY ca.alias) AS aliases',
            'FROM ' . $this->dbDriver->targetSchema . '.collection',
            'LEFT JOIN ' . $this->dbDriver->targetSchema . '.collection_alias ca ON id = ca.collection',
            'WHERE ' . join(' AND ', $where),
            'GROUP BY id'
        ));

        $results = $this->dbDriver->pQuery($query, array($id));
        $collection = null;
        while ($rawCollection = pg_fetch_assoc($results)) {
            $collection = $this->format($rawCollection);
        }

        return $collection;

    }

    /**
     * Get all collections
     *
     * @param RestoUser $user
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function getCollections($user, $params = array())
    {
        $collections = array();
        
        // Where clause
        $where = array();
        
        // Visibility
        $visibilityClause = RightsFunctions::getVisibilityClause($user);
        if ( isset($visibilityClause) ) {
            $where[] = $visibilityClause;
        } 
        
        // Filter on keywords
        if (isset($params['ck'])) {
            $where[] = 'keywords @> ARRAY[\'' . $this->dbDriver->escape_string( $params['ck']) . '\']';
        }
        
        // Query with aliases
        $query = join(' ', array(
            'SELECT id, title, description, version, visibility, owner, model, licenseid, to_iso8601(startdate) as startdate, to_iso8601(completiondate) as completiondate, Box2D(bbox) as box2d, providers, properties, links, assets, array_to_json(keywords) as keywords, STRING_AGG(ca.alias, \', \' ORDER BY ca.alias) AS aliases',
            'FROM ' . $this->dbDriver->targetSchema . '.collection',
            'LEFT JOIN ' . $this->dbDriver->targetSchema . '.collection_alias ca ON id = ca.collection',
            (count($where) > 0 ? 'WHERE ' . join(' AND ', $where) : ''),
            'GROUP BY id',
            'ORDER BY id'
        ));

        $results = $this->dbDriver->query($query);
        while ($rawCollection = pg_fetch_assoc($results)) {
            $collections[$rawCollection['id']] = $this->format($rawCollection);
        }
        return $collections;
    }

    /**
     * Check if collection $id exists within resto database
     *
     * @param string $collectionId - collection id
     * @return boolean
     * @throws Exception
     */
    public function collectionExists($collectionId)
    {
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT id FROM ' . $this->dbDriver->targetSchema . '.collection WHERE id=$1', array($collectionId)));
        return !empty($results);
    }

    /**
     * Remove collection from RESTo database
     *
     * @param RestoCollection $collection
     * @param string $baseUrl
     * @throws Exception
     */
    public function removeCollection($collection, $baseUrl)
    {
        /*
         * Never remove a non empty collection
         */
        if (!$this->collectionIsEmpty($collection)) {
            RestoLogUtil::httpError(403, 'Collection ' . $collection->id . ' cannot be deleted - it is not empty !');
        }

        /*
         * Delete (within transaction)
         */
        try {

            $this->dbDriver->query('BEGIN');

            /*
             * First remove collection referenced within catalogs
             */
            $this->removeCollectionFromCatalogs($baseUrl . RestoRouter::ROUTE_TO_COLLECTIONS . '/' . $collection->id);
            
            $this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.collection WHERE id=$1', array(
                $collection->id
            ));

            $this->dbDriver->query('COMMIT');

            /*
             * Rollback on error
             */
            if ($this->collectionExists($collection->id)) {
                $this->dbDriver->query('ROLLBACK');
                throw new Exception('Cannot delete collection ' . $collection->id, 500);
            }

        } catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Save collection to database
     *
     * @param RestoCollection $collection
     *
     * @throws Exception
     */
    public function storeCollection($collection)
    {

        /*
         * First generate a right keywords array
         */
        $keywords = null;
        if (!empty($collection->keywords)) {
            $keywords = array();
            for ($i = 0, $ii = count($collection->keywords); $i < $ii; $i++) {
                $keywords[] = '"' . $collection->keywords[$i] . '"';
            }
            $keywords = '{' . join(',', $keywords) . '}';
        }

        /*
         * Extract spatial and temporals extents
         */
        $startDate = $collection->extent['temporal']['interval'][0][0] ?? null;
        $completionDate = $collection->extent['temporal']['interval'][0][1] ?? null;

        try {

            /*
             * Start transaction
             */
            $this->dbDriver->query('BEGIN');

            /*
             * Create collection
             */
            $toBeSet = array(
                'id' => $collection->id,
                'title' => $collection->title,
                'description' => $collection->description,
                'created' => 'now()',
                'model' => $collection->model->getName(),
                'lineage' => '{' . join(',', $collection->model->getLineage()) . '}',
                // Be carefull license column is named licenseid in table
                'licenseid' => $collection->license,
                'visibility' => '{' . join(',', $collection->visibility) . '}',
                'owner' => $collection->owner,
                'providers' => json_encode($collection->providers, JSON_UNESCAPED_SLASHES),
                'properties' => json_encode($collection->properties, JSON_UNESCAPED_SLASHES),
                'links' => json_encode($collection->links, JSON_UNESCAPED_SLASHES),
                'assets' => json_encode($collection->assets, JSON_UNESCAPED_SLASHES),
                'keywords' => $keywords,
                'version' => $collection->version,
                'startdate' => $startDate,
                'completiondate' => $completionDate
            );
            
            // bbox is set
            if (isset($collection->extent['spatial']['bbox'][0])) {
                if (count($collection->extent['spatial']['bbox'][0]) !== 4) {
                    RestoLogUtil::httpError(400, 'Invalid input bbox');
                }
                $this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->targetSchema . '.collection (' . join(',', array_keys($toBeSet)) . ', bbox) VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, ST_SetSRID(ST_MakeBox2D(ST_Point($18, $19), ST_Point($20, $21)), 4326) )', array_merge(array_values($toBeSet), $collection->extent['spatial']['bbox'][0]));
            } else {
                $this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->targetSchema . '.collection (' . join(',', array_keys($toBeSet)) . ') VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17)', array_values($toBeSet));
            }
            
            // Store aliases
            $this->updateAliases($collection->id, $collection->aliases ?? array());

            /*
             * Close transaction
             */
            $this->dbDriver->query('COMMIT');

            /*
             * Rollback on errors
             */
            if (! $this->collectionExists($collection->id)) {
                $this->dbDriver->query('ROLLBACK');
                throw new Exception('Missing collection', 500);
            }

        } catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Update collection
     * 
     * @param RestoCollection $collection
     * @param array $body
     */
    public function updateCollection($collection, $body)
    {

        $keys = array();
        $values = array(
            $collection->id
        );

        $body['properties'] = array_merge($collection->properties, $body['properties'] ?? array());

        foreach($body as $key => $value) {

            switch ($key) {

                case 'providers':
                case 'properties':
                case 'links':
                case 'assets':
                    $keys[] = $key . '=$' . (count($keys) + 2);
                    $values[] = json_encode($value, JSON_UNESCAPED_SLASHES);
                    break;

                case 'license':
                    $keys[] = 'licenseid=$' . (count($keys) + 2);
                    $values[] = $value;
                    break;

                case 'version':
                case 'title':
                case 'description':
                    $keys[] = $key . '=$' . (count($keys) + 2);
                    $values[] = $value;
                    break;
                
                case 'keywords':
                case 'visibility':
                    $keys[] = $key . '=$' . (count($keys) + 2);
                    $values[] = '{' . join(',', $value) . '}';
                    break;

                case 'model':
                    $model = (new $value(array(
                        'collectionId' => $collection->id,
                        'addons' => $collection->context->addons
                    )));
                    $keys[] =  'model=$' . (count($keys) + 2);
                    $values[] = $model->getName();
                    $keys[] = 'lineage=$' . (count($keys) + 2);
                    $values[] = '{' . join(',', $model->getLineage()) . '}';
                    break;
            }

        }

        try {

            /*
             * Start transaction
             */
            $this->dbDriver->query('BEGIN');

            $query = 'UPDATE ' . $this->dbDriver->targetSchema . '.collection SET ' . join(',', $keys) . ' WHERE id=$1';
            $this->dbDriver->pQuery($query, $values);

            // Store aliases
            $this->updateAliases($collection->id, $body['aliases'] ?? array());

            /*
             * Close transaction
             */
            $this->dbDriver->query('COMMIT');

        } catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }

    }

    /**
     * Update collection extent
     *
     * @param RestoCollection $collection
     * @param array $extentArrays - array of "dates" and "bboxes" arrays
     * @return boolean
     * @throws Exception
     */
    public function updateExtent($collection, $extentArrays)
    {
        if (! isset($extentArrays)) {
            return false;
        }

        // Compute extents
        $timeExtent = $this->getTimeExtent($extentArrays['dates']);
        $bbox = $this->getSpatialExtent($extentArrays['bboxes']);

        $toBeSet = array();

        if (isset($timeExtent['startDate'])) {
            $toBeSet[] = 'startdate=least(startdate, \'' . $this->dbDriver->escape_string( $timeExtent['startDate']) . '\')';
        }

        if (isset($timeExtent['completionDate'])) {
            $toBeSet[] = 'completiondate=greatest(completiondate, \'' . $this->dbDriver->escape_string( $timeExtent['completionDate']) . '\')';
        }

        if (isset($bbox)) {
            $toBeSet[] = 'bbox=ST_Envelope(ST_Union(coalesce(bbox,ST_GeomFromText(\'GEOMETRYCOLLECTION EMPTY\', 4326)), ST_SetSRID(ST_MakeBox2D(ST_Point(' . $bbox[0] . ',' . $bbox[1] . '), ST_Point(' . $bbox[2] . ',' . $bbox[3] . ')), 4326)))';
        }

        if (empty($toBeSet)) {
            return false;
        }

        try {
            $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.collection SET ' . join(',', $toBeSet) . ' WHERE id=$1', array(
                $collection->id
            ));
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Store collection aliases
     *
     * @param string $collectionName
     * @param array $aliases
     *
     */
    public function updateAliases($collectionName, $aliases)
    {
        
        // First DELETE all existing collection aliases
        $this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.collection_alias WHERE collection=$1', array(
            $collectionName
        ));

        // Next INSERT one row per alias
        for ($i = 0, $ii = count($aliases); $i < $ii; $i++) {
            $this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->targetSchema . '.collection_alias (alias, collection) VALUES($1, $2) ON CONFLICT (alias) DO NOTHING', array(
                $aliases[$i],
                $collectionName
            ));
        }
        
    }
        
    /**
     * Return collection name from alias
     * 
     * @param string $alias
     * @return string
     */
    public function aliasToCollectionId($alias) {
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT collection FROM ' . $this->dbDriver->targetSchema . '.collection_alias WHERE alias=$1', array(
            $alias
        )));
        if ( isset($results) && count($results) === 1 ) {
            return $results[0]['collection'];
        }
        return null;
    }

    /**
     * Get time extent from a list of time extent
     *
     * @param array $dates - array of ISO8601 dates
     * @return array
     * @throws Exception
     */
    private function getTimeExtent($dates)
    {
        if (count($dates) === 0 || !isset($dates[0])) {
            return array(
                'startDate' => null,
                'completionDate' => null
            );
        }

        $startDate = $dates[0];
        $competionDate = $dates[0];
        for ($i = 1, $ii = count($dates); $i < $ii; $i++) {
            if (isset($dates[$i])) {
                if ($startDate > $dates[$i]) {
                    $startDate = $dates[$i];
                }
                if ($competionDate < $dates[$i]) {
                    $competionDate = $dates[$i];
                }
            }
        }

        return array(
            'startDate' => $startDate,
            'completionDate' => $competionDate
        );
    }

    /**
     * Get bounding box of bounding boxes
     *
     * @param array $bboxes - array of bbox
     * @return array|null
     * @throws Exception
     */
    private function getSpatialExtent($bboxes)
    {
        if (count($bboxes) === 0 || !isset($bboxes[0])) {
            return null;
        }

        /*
         * Empty geometry is allowed in GeoJSON
         */
        $bbox = $bboxes[0];
        for ($i = 1, $ii = count($bboxes); $i < $ii; $i++) {
            if (isset($bboxes[$i])) {
                $bbox = array(
                    min($bbox[0], $bboxes[$i][0]),
                    min($bbox[1], $bboxes[$i][1]),
                    max($bbox[2], $bboxes[$i][2]),
                    max($bbox[3], $bboxes[$i][3]),
                );
            }
        }

        return $bbox;
    }

    /**
     * Return true if collection is empty, false otherwise
     *
     * @param RestoCollection $collection
     * @return boolean
     */
    private function collectionIsEmpty($collection)
    {
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT count(id) as count FROM ' . $this->dbDriver->targetSchema . '.feature WHERE collection=$1 LIMIT 1', array($collection->id)));
        if ($results[0]['count'] === '0') {
            return true;
        }
        return false;
    }

    /**
     * Return a formated collection description
     *
     * @param array $rawCollection
     */
    private function format($rawCollection)
    {
        $collection = array(
            'id' => $rawCollection['id'],
            'title' => $rawCollection['title'],
            'description' => $rawCollection['description'],
            'aliases' => isset($rawCollection['aliases']) ? json_decode($rawCollection['aliases'], true) : array(),
            'version' => $rawCollection['version'] ?? null,
            'model' => $rawCollection['model'],
            'visibility' => RestoUtil::SQLTextArrayToPHP($rawCollection['visibility']),
            'owner' => $rawCollection['owner'],
            'providers' => json_decode($rawCollection['providers'], true),
            'assets' => json_decode($rawCollection['assets'], true),
            'keywords' => isset($rawCollection['keywords']) ? json_decode($rawCollection['keywords'], true) : array(),
            'links' => json_decode($rawCollection['links'], true),
            'extent' => array(
                'spatial' => array(
                    'bbox' => array(
                        RestoGeometryUtil::box2dTobbox($rawCollection['box2d'])
                    ),
                    'crs' => 'http://www.opengis.net/def/crs/OGC/1.3/CRS84'
                ),
                'temporal' => array(
                    'interval' => array(
                        array(
                            $rawCollection['startdate'] ?? null, $rawCollection['completiondate'] ?? null
                        )
                    ),
                    'trs' => 'http://www.opengis.net/def/uom/ISO-8601/0/Gregorian'
                )
            ),
            // Be carefull license column is named licenseid in table
            'license' => $rawCollection['licenseid'],
            // Special _properties will be discarded in toArray()
            'properties' => json_decode($rawCollection['properties'], true)
        );
        
        return $collection;
    }

    /**
     * 
     * Remove all collectionUrl entries in catalog links property
     * 
     * @param string $collectionurl
     * @return void
     */
    private function removeCollectionFromCatalogs($collectionurl)
    {
        
        $this->dbDriver->query("WITH tmp AS (
            WITH target_links as (
                SELECT id, links
                FROM " . $this->dbDriver->targetSchema . ".catalog
                WHERE EXISTS (
                    SELECT 1
                    FROM json_array_elements(links) AS link
                    WHERE link->>'href' = '" . $collectionurl . "'
                )
            ),
            expanded_links AS (
                SELECT
                    id,
                    json_array_elements(links) AS link
                FROM target_links
                WHERE json_typeof(links) = 'array'
            ),
            filtered_links AS (
                SELECT
                    id,
                    json_agg(link) AS new_links
                FROM expanded_links
                WHERE link->>'href' != '" . $collectionurl . "' -- Exclude the link with the specific ID
                GROUP BY id
            )
            SELECT " . $this->dbDriver->targetSchema . ".catalog.id, f.new_links AS modified_links
            FROM " . $this->dbDriver->targetSchema . ".catalog
            LEFT JOIN filtered_links f ON " . $this->dbDriver->targetSchema . ".catalog.id = f.id
        )
        UPDATE " . $this->dbDriver->targetSchema . ".catalog
        SET links = tmp.modified_links
        FROM tmp
        WHERE " . $this->dbDriver->targetSchema . ".catalog.id = tmp.id
        AND EXISTS (
            SELECT 1
            FROM json_array_elements(links) AS link
            WHERE link->>'href' = '" . $collectionurl .  "'
        );");

    }
}
