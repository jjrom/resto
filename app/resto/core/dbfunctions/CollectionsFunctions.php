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
        
        // Get Opensearch description
        $osDescriptions = $this->getOSDescriptions($id);
        $collection = null;
        $results = $this->dbDriver->pQuery('SELECT id, version, visibility, owner, model, mapping, licenseid, to_iso8601(startdate) as startdate, to_iso8601(completiondate) as completiondate, Box2D(bbox) as box2d, providers, properties, links FROM resto.collection WHERE normalize(id)=normalize($1)', array($id));
        while ($rowDescription = pg_fetch_assoc($results)) {
            $collection = array_merge(
                FormatUtil::collectionDescription($rowDescription),
                array('osDescription' => $osDescriptions[$id])
            );
        }
        return $collection;
    }

    /**
     * Get description of all collections including facets
     *
     * @param array $visibilities
     * @return array
     * @throws Exception
     */
    public function getCollectionsDescriptions($visibilities = null)
    {
        
        $collections = array();

        // Get all Opensearch descriptions
        $osDescriptions = $this->getOSDescriptions();
        $where = isset($visibilities) && count($visibilities) > 0 ? ' WHERE visibility IN (' . join(',', $visibilities) . ')' : '';
        $results = $this->dbDriver->query('SELECT id, version, visibility, owner, model, mapping, licenseid, to_iso8601(startdate) as startdate, to_iso8601(completiondate) as completiondate, Box2D(bbox) as box2d, providers, properties, links FROM resto.collection ' . $where . ' ORDER BY id');
        while ($rowDescription = pg_fetch_assoc($results)) {
            $collections[$rowDescription['id']] = array_merge(
                FormatUtil::collectionDescription($rowDescription),
                array('osDescription' => $osDescriptions[$rowDescription['id']])
            );
        }

        return $collections;
    }

    /**
     * Check if collection $id exists within resto database
     *
     * @param string $id - collection id
     * @return boolean
     * @throws Exception
     */
    public function collectionExists($id)
    {
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT id FROM resto.collection WHERE id=$1', array($id)));
        return !empty($results);
    }

    /**
     * Remove collection from RESTo database
     *
     * @param string $collectionId
     * @return array
     * @throws Exception
     */
    public function removeCollection($collectionId)
    {

        /*
         * Never remove a non empty collection
         */
        if (!$this->collectionIsEmpty($collectionId)) {
            RestoLogUtil::httpError(403, 'Collection ' . $collectionId . ' cannot be deleted - it is not empty !');
        }

        /*
         * Delete (within transaction)
         */
        try {

            $this->dbDriver->query('BEGIN');

            $this->dbDriver->pQuery('DELETE FROM resto.collection WHERE id=$1', array(
                $collectionId
            ));

            $this->dbDriver->pQuery('DELETE FROM resto.right WHERE collection=$1', array(
                $collectionId
            ));
            
            $this->dbDriver->query('COMMIT');

            /*
             * Rollback on error
             */
            if ($this->collectionExists($collectionId)) {
                $this->dbDriver->query('ROLLBACK');
                throw new Exception(500, 'Cannot delete collection ' . $collectionId);
            }

            /*
             * Clear cache
             */
            (new RestoCache())->clear();

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
             * Store default rights for collection
             *
             * [TODO] Should get userid from  input user ?
             */
            (new RightsFunctions($this->dbDriver))->storeOrUpdateRights(array(
                'right' => $rights,
                'id' => null,
                'groupid' => Resto::GROUP_DEFAULT_ID,
                'collectionId' => $collection->id,
                'featureId' => null
                )
            );
           
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

            /*
             * Clear cache
             */
            (new RestoCache())->clear();
            
        } catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Update
     *
     * @param RestoCollection $collection
     * @return array
     * @throws Exception
     */
    public function updateExtent($collection, $featureArray)
    {

        $toUpdate = array();

        // Time
        if ( ! isset($collection->datetime) || ! isset($collection->datetime['min']) || $collection->datetime['min'] > $featureArray['properties']['startDate'])
        {
            $toUpdate[] = 'startdate=\'' . pg_escape_string($featureArray['properties']['startDate']) .'\'';
        }
        if ( ! isset($collection->datetime) ||  ! isset($collection->datetime['max']) || $collection->datetime['max'] < $featureArray['properties']['startDate'])
        {
            $toUpdate[] = 'completiondate=\'' . pg_escape_string($featureArray['properties']['startDate']) .'\'';
        }
        
        // Space
        if ( ! isset($collection->bbox))
        {
            $toUpdate[] = 'bbox=ST_SetSRID(ST_MakeBox2D(ST_Point(' . $featureArray['topologyAnalysis']['bbox'][0] . ',' . $featureArray['topologyAnalysis']['bbox'][1] . '), ST_Point(' . $featureArray['topologyAnalysis']['bbox'][2] . ',' . $featureArray['topologyAnalysis']['bbox'][3] . ')), 4326)';
        }
        else if ( $collection->bbox[0] > $featureArray['topologyAnalysis']['bbox'][0] || $collection->bbox[1] > $featureArray['topologyAnalysis']['bbox'][1] || $collection->bbox[2] < $featureArray['topologyAnalysis']['bbox'][2] || $collection->bbox[3] < $featureArray['topologyAnalysis']['bbox'][3])
        {
            $bbox = array(
                min($collection->bbox[0],$featureArray['topologyAnalysis']['bbox'][0]),
                min($collection->bbox[1],$featureArray['topologyAnalysis']['bbox'][1]),
                max($collection->bbox[2],$featureArray['topologyAnalysis']['bbox'][2]),
                max($collection->bbox[3],$featureArray['topologyAnalysis']['bbox'][3]),
            );
            $toUpdate[] = 'bbox=ST_SetSRID(ST_MakeBox2D(ST_Point(' . $bbox[0] . ',' . $bbox[1] . '), ST_Point(' . $bbox[2] . ',' . $bbox[3] . ')), 4326)';
        }

        if ( ! empty($toUpdate) )
        {
            $this->dbDriver->query('UPDATE resto.collection SET ' . join(',', $toUpdate) . ' WHERE id=\'' . pg_escape_string($collection->id) . '\'');
        }
        
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
            $results = $this->dbDriver->pQuery('SELECT * FROM resto.osdescription WHERE collection=$1', array($collectionId));
        }
        else {
            $results = $this->dbDriver->query('SELECT * FROM resto.osdescription');
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
         * Create collection
         */
        if (! $this->collectionExists($collection->id)) {
            $toBeSet = array(
                'id' => $collection->id,
                'created' => 'now()',
                'model' => $collection->model->getName(),
                'lineage' => '{' . join(',', $collection->model->getLineage()) . '}',
                'licenseid' => $collection->licenseId,
                'mapping' => json_encode($collection->propertiesMapping, JSON_UNESCAPED_SLASHES),
                'visibility' => $collection->visibility,
                'owner' => $collection->owner,
                'providers' => json_encode($collection->providers, JSON_UNESCAPED_SLASHES),
                'properties' => json_encode($collection->properties, JSON_UNESCAPED_SLASHES),
                'links' => json_encode($collection->links, JSON_UNESCAPED_SLASHES),
                'version' => $collection->version
            );
            $this->dbDriver->pQuery('INSERT INTO resto.collection (' . join(',', array_keys($toBeSet)) . ') VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)', array_values($toBeSet));
        }
        /*
         * Otherwise update collection fields (version, visibility, mapping, licenseid, providers and properties)
         */
        else {
            $this->dbDriver->pQuery('UPDATE resto.collection SET visibility=$2, mapping=$3, licenseid=$4, providers=$5, properties=$6, links=$7, version=$8 WHERE id=$1', array(
                $collection->id,
                $collection->visibility,
                json_encode($collection->propertiesMapping, JSON_UNESCAPED_SLASHES),
                $collection->licenseId,
                json_encode($collection->providers, JSON_UNESCAPED_SLASHES),
                json_encode($collection->properties, JSON_UNESCAPED_SLASHES),
                json_encode($collection->links, JSON_UNESCAPED_SLASHES),
                $collection->version
            ));
        }

        /*
         * Insert OpenSearch descriptions within osdescriptions table
         * (one description per lang)
         *
         * CREATE TABLE resto.osdescription (
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
        $this->dbDriver->pQuery('DELETE FROM resto.osdescription WHERE collection=$1', array(
            $collection->id
        ));

        foreach ($collection->osDescription as $lang => $description) {
            $osFields = array(
                'collection',
                'lang'
            );
            $osValues = array(
                '\'' . pg_escape_string($collection->id) . '\'',
                '\'' . pg_escape_string($lang) . '\''
            );

            /*
             * OpenSearch 1.1 draft 5 constraints
             * (http://www.opensearch.org/Specifications/OpenSearch/1.1)
             */
            $validProperties = array(
                'ShortName' => 16,
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
                    $osValues[] = '\'' . pg_escape_string($description[$key]) . '\'';
                }
            }
            $this->dbDriver->query('INSERT INTO resto.osdescription (' . join(',', $osFields) . ') VALUES(' . join(',', $osValues) . ')');
        }

        return true;

    }

    /**
     * Return true if collection is empty, false otherwise
     *
     * @param string $collectionId
     * @return boolean
     */
    private function collectionIsEmpty($collectionId)
    {
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT count(id) as count FROM resto.feature WHERE collection=$1 LIMIT 1', array($collectionId)));
        if ($results[0]['count'] === '0') {
            return true;
        }
        return false;
    }

}
