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
     * Get description for collection
     *
     * @param string $id
     * @return array
     * @throws Exception
     */
    public function getCollectionDescription($id)
    {

        // Eventually convert input alias to the real collection id
        $collectionId = $this->aliasToCollectionId($id); 
        if ( isset($collectionId) ) {
            $id = $collectionId;
        }

        // Query with aliases
        $query = join(' ', array(
            'SELECT id, version, visibility, owner, model, licenseid, to_iso8601(startdate) as startdate, to_iso8601(completiondate) as completiondate, Box2D(bbox) as box2d, providers, properties, links, assets, array_to_json(keywords) as keywords, STRING_AGG(ca.alias, \', \' ORDER BY ca.alias) AS aliases',
            'FROM ' . $this->dbDriver->targetSchema . '.collection',
            'LEFT JOIN ' . $this->dbDriver->targetSchema . '.collection_alias ca ON id = ca.collection',
            'WHERE public.normalize(id)=public.normalize($1)',
            'GROUP BY id'
        ));

        $results = $this->dbDriver->pQuery($query, array($id));

        $collection = null;
        while ($rowDescription = pg_fetch_assoc($results)) {
            // Get Opensearch description
            $osDescriptions = $this->getOSDescriptions($id);
            $collection = $this->format($rowDescription, $osDescriptions[$id] ?? null);
        }

        return $collection;

    }

    /**
     * Get description of all collections including facets
     *
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function getCollectionsDescriptions($params = array())
    {
        $collections = array();

        // Get all Opensearch descriptions
        $osDescriptions = $this->getOSDescriptions();

        // Where clause
        $where = array();
        if (isset($params['group']) && count($params['group']) > 0) {
            $where[] = 'visibility IN (' . join(',', $params['group']) . ')';
        }
        
        // Filter on keywords
        if (isset($params['ck'])) {
            $where[] = 'keywords @> ARRAY[\'' . pg_escape_string($this->dbDriver->getConnection(), $params['ck']) . '\']';
        }
        
        // Query with aliases
        $query = join(' ', array(
            'SELECT id, version, visibility, owner, model, licenseid, to_iso8601(startdate) as startdate, to_iso8601(completiondate) as completiondate, Box2D(bbox) as box2d, providers, properties, links, assets, array_to_json(keywords) as keywords, STRING_AGG(ca.alias, \', \' ORDER BY ca.alias) AS aliases',
            'FROM ' . $this->dbDriver->targetSchema . '.collection',
            'LEFT JOIN ' . $this->dbDriver->targetSchema . '.collection_alias ca ON id = ca.collection',
            (count($where) > 0 ? 'WHERE ' . join(' AND ', $where) : ''),
            'GROUP BY id',
            'ORDER BY id'
        ));

        $results = $this->dbDriver->query($query);
        while ($rowDescription = pg_fetch_assoc($results)) {
            $collections[$rowDescription['id']] = $this->format($rowDescription, $osDescriptions[$rowDescription['id']] ?? null);
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
     * @return array
     * @throws Exception
     */
    public function removeCollection($collection)
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

            $this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.collection WHERE id=$1', array(
                $collection->id
            ));

            $this->dbDriver->query('COMMIT');

            /*
             * Rollback on error
             */
            if ($this->collectionExists($collection->id)) {
                $this->dbDriver->query('ROLLBACK');
                throw new Exception(500, 'Cannot delete collection ' . $collection->id);
            }

        } catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Save collection to database
     *
     * @param RestoCollection $collection
     * @param Array $rights
     *
     * @throws Exception
     */
    public function storeCollection($collection, $rights)
    {
        try {
            /*
             * Start transaction
             */
            $this->dbDriver->query('BEGIN');

            /*
             * Create new entry in collections osdescriptions tables
             */
            $this->storeCollectionDescription($collection);
           
            /*
             * Close transaction
             */
            $this->dbDriver->query('COMMIT');

            /*
             * Rollback on errors
             */
            if (! $this->collectionExists($collection->id)) {
                $this->dbDriver->query('ROLLBACK');
                throw new Exception(500, 'Missing collection');
            }

        } catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Update collection extent
     *
     * @param RestoCollection $collection
     * @param array $extentArrays - array of "dates" and "bboxes" arrays
     * @return array
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
            $toBeSet[] = 'startdate=least(startdate, \'' . pg_escape_string($this->dbDriver->getConnection(), $timeExtent['startDate']) . '\')';
        }

        if (isset($timeExtent['completionDate'])) {
            $toBeSet[] = 'completiondate=greatest(completiondate, \'' . pg_escape_string($this->dbDriver->getConnection(), $timeExtent['completionDate']) . '\')';
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
     * @param String $collectionName
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
     * @return array
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
     * Get OpenSearch description array for input collection
     *
     * @param string $collectionId
     * @return array
     * @throws Exception
     */
    private function getOSDescriptions($collectionId = null)
    {
        $osDescriptions = array();

        if (isset($collectionId)) {
            $results = $this->dbDriver->pQuery('SELECT * FROM ' . $this->dbDriver->targetSchema . '.osdescription WHERE collection=$1', array($collectionId));
        } else {
            $results = $this->dbDriver->query('SELECT * FROM ' . $this->dbDriver->targetSchema . '.osdescription');
        }
        
        while ($description = pg_fetch_assoc($results)) {
            if (!isset($osDescriptions[$description['collection']])) {
                $osDescriptions[$description['collection']]['collection'] = array();
            }
            $osDescriptions[$description['collection']][$description['lang']] = array(
                'ShortName' => $description['shortname'],
                'LongName' => $description['longname'],
                'Description' => $description['description'],
                'Tags' => $description['tags'],
                'Developer' => $description['developer'],
                'Contact' => $description['contact'],
                'Query' => $description['query'],
                'Attribution' => $description['attribution']
            );
        }

        return $osDescriptions;
    }

    /**
     * Store Collection description
     *
     * @param RestoCollection $collection
     *
     */
    private function storeCollectionDescription($collection)
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

        /*
         * Create collection
         */
        if (! $this->collectionExists($collection->id)) {
            $toBeSet = array(
                'id' => $collection->id,
                'created' => 'now()',
                'model' => $collection->model->getName(),
                'lineage' => '{' . join(',', $collection->model->getLineage()) . '}',
                // Be carefull license column is named licenseid in table
                'licenseid' => $collection->license,
                'visibility' => $collection->visibility,
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
                    return RestoLogUtil::httpError(400, 'Invalid input bbox');
                }
                $this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->targetSchema . '.collection (' . join(',', array_keys($toBeSet)) . ', bbox) VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, ST_SetSRID(ST_MakeBox2D(ST_Point($16, $17), ST_Point($18, $19)), 4326) )', array_merge(array_values($toBeSet), $collection->extent['spatial']['bbox'][0]));
            } else {
                $this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->targetSchema . '.collection (' . join(',', array_keys($toBeSet)) . ') VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15)', array_values($toBeSet));
            }
        }
        /*
         * Otherwise update collection fields (version, visibility, licenseid, providers and properties)
         */
        else {
            $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.collection SET model=$2, lineage=$3, licenseid=$4, visibility=$5, providers=$6, properties=$7, links=$8, assets=$9, keywords=$10, version=$11 WHERE id=$1', array(
                $collection->id,
                $collection->model->getName(),
                '{' . join(',', $collection->model->getLineage()) . '}',
                $collection->license,
                $collection->visibility,
                json_encode($collection->providers, JSON_UNESCAPED_SLASHES),
                json_encode($collection->properties, JSON_UNESCAPED_SLASHES),
                json_encode($collection->links, JSON_UNESCAPED_SLASHES),
                json_encode($collection->assets, JSON_UNESCAPED_SLASHES),
                $keywords,
                $collection->version
            ));
        }

        // Store aliases
        $this->updateAliases($collection->id, $collection->aliases ?? array());

        // OpenSearch description is stored in another table
        $this->storeOSDescription($collection);
        
        return true;
    }

    /**
     * Store OpenSearch collection description
     * (This function is called within storeCollectionDescription)
     *
     * @param RestoCollection $collection
     *
     */
    private function storeOSDescription($collection)
    {
        /*
         * Insert OpenSearch descriptions within osdescriptions table
         * (one description per lang)
         *
         * CREATE TABLE [$this->dbDriver->targetSchema].osdescription (
         *  collection          TEXT,
         *  lang                TEXT,
         *  shortname           TEXT,
         *  longname            TEXT,
         *  description         TEXT,
         *  tags                TEXT,
         *  developer           TEXT,
         *  contact             TEXT,
         *  query               TEXT,
         *  attribution         TEXT
         * );
         */
        $this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.osdescription WHERE collection=$1', array(
            $collection->id
        ));

        foreach ($collection->osDescription as $lang => $description) {
            $osFields = array(
                'collection',
                'lang'
            );
            $osValues = array(
                '\'' . pg_escape_string($this->dbDriver->getConnection(), $collection->id) . '\'',
                '\'' . pg_escape_string($this->dbDriver->getConnection(), $lang) . '\''
            );

            /*
             * OpenSearch 1.1 draft 5 constraints
             * (http://www.opensearch.org/Specifications/OpenSearch/1.1)
             *
             * [STAC] Remove constraints on ShortName, LongName and Description
             */
            $validProperties = array(
                //'ShortName' => 16,
                'ShortName' => -1,
                //'LongName' => 48,
                'LongName' => -1,
                //'Description' => 1024,
                'Description' => -1,
                'Tags' => 256,
                'Developer' => 64,
                'Contact' => -1,
                'Query' => -1,
                //'Attribution' => 256
                'Attribution' => -1
            );
            foreach (array_keys($description) as $key) {
                /*
                 * Throw exception if property is invalid
                 */
                if (isset($validProperties[$key])) {
                    if ($validProperties[$key] !== -1 && strlen($description[$key]) > $validProperties[$key]) {
                        RestoLogUtil::httpError(400, 'OpenSearch property ' . $key . ' length is greater than ' . $validProperties[$key] . ' characters');
                    }
                    $osFields[] = strtolower($key);
                    $osValues[] = '\'' . pg_escape_string($this->dbDriver->getConnection(), $description[$key]) . '\'';
                }
            }
            $this->dbDriver->query('INSERT INTO ' . $this->dbDriver->targetSchema . '.osdescription (' . join(',', $osFields) . ') VALUES(' . join(',', $osValues) . ')');
        }
    }

    /**
     * Return true if collection is empty, false otherwise
     *
     * @param RestoCollection $collection
     * @return boolean
     */
    private function collectionIsEmpty($collection)
    {
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT count(id) as count FROM ' . $this->dbDriver->targetSchema . '.' . $collection->model->dbParams['tablePrefix'] . 'feature WHERE collection=$1 LIMIT 1', array($collection->id)));
        if ($results[0]['count'] === '0') {
            return true;
        }
        return false;
    }

    /**
     * Return a formated collection description
     *
     * @param array $rawDescription
     * @param array $osDescription
     */
    private function format($rawDescription, $osDescription)
    {
        $collection = array(
            'id' => $rawDescription['id'],
            'aliases' => $rawDescription['aliases'] ?? null,
            'version' => $rawDescription['version'] ?? null,
            'model' => $rawDescription['model'],
            'visibility' => (integer) $rawDescription['visibility'],
            'owner' => $rawDescription['owner'],
            'providers' => json_decode($rawDescription['providers'], true),
            'assets' => json_decode($rawDescription['assets'], true),
            'keywords' => isset($rawDescription['keywords']) ? json_decode($rawDescription['keywords'], true) : array(),
            'links' => json_decode($rawDescription['links'], true),
            'extent' => array(
                'spatial' => array(
                    'bbox' => array(
                        RestoGeometryUtil::box2dTobbox($rawDescription['box2d'])
                    ),
                    'crs' => 'http://www.opengis.net/def/crs/OGC/1.3/CRS84'
                ),
                'temporal' => array(
                    'interval' => array(
                        array(
                            $rawDescription['startdate'] ?? null, $rawDescription['completiondate'] ?? null
                        )
                    ),
                    'trs' => 'http://www.opengis.net/def/uom/ISO-8601/0/Gregorian'
                )
            ),
            // Be carefull license column is named licenseid in table
            'license' => $rawDescription['licenseid'],
            // Special _properties will be discarded in toArray()
            'properties' => json_decode($rawDescription['properties'], true),
            'osDescription' => $osDescription
        );

        /*
         * If OpenSearch Description object is not set, create a minimal one from $object['description']
         */
        if (!isset($osDescription) || !is_array($osDescription) || !isset($osDescription['en']) || !is_array($osDescription['en'])) {
            $collection['osDescription'] = array(
                'en' => array(
                    'ShortName' => $collection['properties']['title'] ?? $collection['id'],
                    'Description' => $collection['properties']['description'] ?? ''
                )
            );
        }

        return $collection;
    }
}
