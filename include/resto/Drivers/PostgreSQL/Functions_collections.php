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
 * RESTo PostgreSQL collections functions
 */
class Functions_collections {
    
    private $dbDriver = null;
    
    /**
     * Constructor
     * 
     * @param RestoDatabaseDriver $dbDriver
     * @throws Exception
     */
    public function __construct($dbDriver) {
        $this->dbDriver = $dbDriver;
    }

    /**
     * Get description of all collections including facets
     * 
     * @param string $collectionName
     * @return array
     * @throws Exception
     */
    public function getCollectionsDescriptions($collectionName = null) {
        
        $cached = $this->dbDriver->cache->retrieve(array('getCollectionsDescriptions', $collectionName));
        if (isset($cached)) {
            return $cached;
        }
        
        /*
         * First get licenses
         */
        $licenses = $this->dbDriver->get(RestoDatabaseDriver::LICENSES);
        
        /*
         * Then collections
         */
        $collectionsDescriptions = array();
        $descriptions = $this->dbDriver->query('SELECT collection, status, owner, model, mapping, licenseid FROM resto.collections' . (isset($collectionName) ? ' WHERE collection=\'' . pg_escape_string($collectionName) . '\'' : '') . ' ORDER BY collection');
        while ($collection = pg_fetch_assoc($descriptions)) {
            $collectionsDescriptions[$collection['collection']] = array(
                'name' => $collection['collection'],
                'model' => $collection['model'],
                'status' => $collection['status'],
                'owner' => $collection['owner'],
                'propertiesMapping' => json_decode($collection['mapping'], true),
                'license' => isset($licenses[$collection['licenseid']]) ? $licenses[$collection['licenseid']] : null,
                'osDescription' => $this->getOSDescriptions($collection['collection'])
            );
        }
        
        /*
         * Store in cache
         */
        $this->dbDriver->cache->store(array('getCollectionsDescriptions', $collectionName), $collectionsDescriptions);
        
        return $collectionsDescriptions;
        
    }
    
    /**
     * Check if collection $name exists within resto database
     * 
     * @param string $name - collection name
     * @return boolean
     * @throws Exception
     */
    public function collectionExists($name) {
        $query = 'SELECT collection FROM resto.collections WHERE collection=\'' . pg_escape_string($name) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return !empty($results);
    }
    
    /**
     * Remove collection from RESTo database
     * 
     * @param RestoCollection $collection
     * @return array
     * @throws Exception
     */
    public function removeCollection($collection) {
        
        /*
         * Never remove a non empty collection
         */
        if (!$this->collectionIsEmpty($collection)) {
            RestoLogUtil::httpError(403, 'Cannot delete a non empty collection ' . $collection->name);
        }
            
        $results = $this->dbDriver->query('SELECT collection FROM resto.collections WHERE collection=\'' . pg_escape_string($collection->name) . '\'');
        $schemaName = '_' . strtolower($collection->name);
        
        if (pg_fetch_assoc($results)) {
                
            /*
             * Delete (within transaction)
             *  - entry within osdescriptions table
             *  - entry within collections table
             */
            $query = 'BEGIN;';
            $query .= 'DELETE FROM resto.osdescriptions WHERE collection=\'' . pg_escape_string($collection->name) . '\';';
            $query .= 'DELETE FROM resto.collections WHERE collection=\'' . pg_escape_string($collection->name) . '\';';
            $query .= 'DELETE FROM usermanagement.rights WHERE ownertype=\'group\' AND owner=\'default\' AND targettype=\'collection\' AND target=\'' . pg_escape_string($collection->name) . '\';';
            
            /*
             * Do not drop schema if product table is not empty
             */
            if ($this->dbDriver->check(RestoDatabaseDriver::SCHEMA, array('name' => $schemaName)) && $this->dbDriver->check(RestoDatabaseDriver::TABLE_EMPTY, array('name' => 'features', 'schema' => $schemaName))) {
                $query .= 'DROP SCHEMA ' . $schemaName . ' CASCADE;';
            }

            $query .= 'COMMIT;';
            $this->dbDriver->query($query);
            /*
             * Rollback on error
             */
            if ($this->collectionExists($collection->name)) {
                $this->dbDriver->query('ROLLBACK');
                RestoLogUtil::httpError(500, 'Cannot delete collection ' . $collection->name);
            }
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
    public function storeCollection($collection, $rights) {
        
        $schemaName = '_' . strtolower($collection->name);
        
        try {
            
            /*
             * Start transaction
             */
            $this->dbDriver->query('BEGIN');

            /*
             * Create schema if needed
             */
            $this->createSchema($schemaName);
            
            /*
             * Create schema.features if needed
             */
            $this->createFeaturesTable($collection, $schemaName);
            
            /*
             * Create new entry in collections osdescriptions tables
             */
            $this->storeCollectionDescription($collection);
            
            /*
             * Store default rights for collection
             */
            $this->dbDriver->store(RestoDatabaseDriver::RIGHTS, array(
                'rights' => $rights,
                'ownerType' => 'group',
                'owner' => 'default',
                'targetType' => 'collection',
                'target' => $collection->name
            ));
            
            /*
             * Close transaction
             */
            $this->dbDriver->query('COMMIT');

            /*
             * Rollback on errors
             */
            if (!$this->dbDriver->check(RestoDatabaseDriver::SCHEMA, array('name' => $schemaName))) {
                $this->dbDriver->query('ROLLBACK');
                RestoLogUtil::httpError(2000);
            }
            if (!$this->collectionExists($collection->name)) {
                $this->dbDriver->query('ROLLBACK');
                RestoLogUtil::httpError(2000);
            }
            
        } catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }
    }
    
    /**
     * Get OpenSearch description array for input collection
     * 
     * @param string $collectionName
     * @return array
     * @throws Exception
     */
    private function getOSDescriptions($collectionName) {
        
        $osDescriptions = array();
                
        $results = $this->dbDriver->query('SELECT * FROM resto.osdescriptions WHERE collection = \'' . pg_escape_string($collectionName) . '\'');
        while ($description = pg_fetch_assoc($results)) {
            $osDescriptions[$description['lang']] = array(
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
     * Create schema if not already exist in database
     * 
     * @param string $schemaName
     */
    private function createSchema($schemaName) {
        if (!$this->dbDriver->check(RestoDatabaseDriver::SCHEMA, array('name' => $schemaName))) {
            $this->dbDriver->query('CREATE SCHEMA ' . $schemaName);
            $this->dbDriver->query('GRANT ALL ON SCHEMA ' . $schemaName . ' TO resto');
            return true;
        }
        return false;
    }
    
    /**
     * Create schema.features table
     * 
     * @param RestoCollection $collection
     * @param string $schemaName
     */
    private function createFeaturesTable($collection, $schemaName) {
        
        /*
         * Prepare one column for each key entry in model
         */
        $table = array();
        foreach (array_keys($collection->model->extendedProperties) as $key) {
            if (is_array($collection->model->extendedProperties[$key])) {
                if (isset($collection->model->extendedProperties[$key]['name']) && isset($collection->model->extendedProperties[$key]['type'])) {
                    $table[] = $collection->model->extendedProperties[$key]['name'] . ' ' . $collection->model->extendedProperties[$key]['type'] . (isset($collection->model->extendedProperties[$key]['constraint']) ? ' ' . $collection->model->extendedProperties[$key]['constraint'] : '');
                }
            }
        }

        /*
         * Create schema.features if needed with a CHECK on collection name
         */
        if (!$this->dbDriver->check(RestoDatabaseDriver::TABLE, array('name' => 'features', 'schema' => $schemaName))) {
            $this->dbDriver->query('CREATE TABLE ' . $schemaName . '.features (' . (count($table) > 0 ? join(',', $table) . ',' : '') . 'CHECK( collection = \'' . $collection->name . '\')) INHERITS (resto.features);');
            $indices = array(
                'identifier' => 'btree',
                'visibility' => 'btree',
                'platform' => 'btree',
                'resolution' => 'btree',
                'startDate' => 'btree',
                'cultivatedCover' => 'btree',
                'desertCover' => 'btree',
                'floodedCover' => 'btree',
                'forestCover' => 'btree',
                'herbaceousCover' => 'btree',
                'iceCover' => 'btree',
                'snowCover' => 'btree',
                'urbanCover' => 'btree',
                'waterCover' => 'btree',
                'cloudCover' => 'btree',
                '_geometry' => 'gist', // _geometry is geometry splitted against -180/180 degrees line
                'centroid' => 'gist',
                'hashes' => 'gin'
            );
            foreach ($indices as $key => $indexType) {
                if (!empty($key)) {
                    $this->dbDriver->query('CREATE INDEX ' . $schemaName . '_features_' . $collection->model->getDbKey($key) . '_idx ON ' . $schemaName . '.features USING ' . $indexType . ' (' . $collection->model->getDbKey($key) . ($key === 'startDate' ? ' DESC)' : ')'));
                }
            }
            $this->dbDriver->query('GRANT SELECT ON TABLE ' . $schemaName . '.features TO resto');
        }
    }
    
    /**
     * Store Collection description 
     * 
     * @param RestoCollection $collection
     * 
     */
    private function storeCollectionDescription($collection) {
        
        $licenseId = 'NULL';
        if (isset($collection->license)) {
            $licenseDescription = $collection->license->toArray();
            $licenseId = '\'' . pg_escape_string($licenseDescription['licenseId']) . '\'';
        }
        
        /*
         * Create collection
         */
        if (!$this->collectionExists($collection->name)) {
            $toBeSet = array(
                'collection' => '\'' . pg_escape_string($collection->name) . '\'',
                'creationdate' => 'now()',
                'model' => '\'' . pg_escape_string($collection->model->name) . '\'',
                'licenseid' => $licenseId, 
                'mapping' => '\'' . pg_escape_string(json_encode($collection->propertiesMapping)) . '\'',
                'status' => '\'' . pg_escape_string($collection->status) . '\'',
                'owner' => '\'' . pg_escape_string($collection->owner) . '\''
            );
            $this->dbDriver->query('INSERT INTO resto.collections (' . join(',', array_keys($toBeSet)) . ') VALUES(' . join(',', array_values($toBeSet)) . ')');
        }
        /*
         * TODO - review this code
         * Update collection fields (status, mapping and licenseid)
         */
        else {
            $this->dbDriver->query('UPDATE resto.collections SET status = \'' . pg_escape_string($collection->status) . '\', mapping = \'' . pg_escape_string(json_encode($collection->propertiesMapping)) . '\', licenseid=' . $licenseId . ' WHERE collection = \'' . pg_escape_string($collection->name) . '\'');
        }

        /*
         * Insert OpenSearch descriptions within osdescriptions table
         * (one description per lang)
         * 
         * CREATE TABLE resto.osdescriptions (
         *  collection          TEXT,
         *  lang                TEXT,
         *  shortname           VARCHAR(16),
         *  longname            VARCHAR(48),
         *  description         VARCHAR(1024),
         *  tags                VARCHAR(256),
         *  developer           VARCHAR(64),
         *  contact             TEXT,
         *  query               TEXT,
         *  attribution         VARCHAR(256),
         * );
         */
        $this->dbDriver->query('DELETE FROM resto.osdescriptions WHERE collection=\'' . pg_escape_string($collection->name) . '\'');
        
        foreach ($collection->osDescription as $lang => $description) {
            $osFields = array(
                'collection',
                'lang'
            );
            $osValues = array(
                '\'' . pg_escape_string($collection->name) . '\'',
                '\'' . pg_escape_string($lang) . '\''
            );
            
            /*
             * OpenSearch 1.1 draft 5 constraints
             * (http://www.opensearch.org/Specifications/OpenSearch/1.1)
             */
            $validProperties = array(
                'ShortName' => 16,
                'LongName' => 48,
                'Description' => 1024,
                'Tags' => 256,
                'Developer' => 64,
                'Contact' => -1,
                'Query' => -1,
                'Attribution' => 256
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
            $this->dbDriver->query('INSERT INTO resto.osdescriptions (' . join(',', $osFields) . ') VALUES(' . join(',', $osValues) . ')');
        }
        return true;
    }

    /**
     * Return true if collection is empty, false otherwise
     * 
     * @param RestoCollection $collection
     * @return boolean
     */
    private function collectionIsEmpty($collection) {
        $query = 'SELECT count(identifier) as count FROM _' . strtolower($collection->name) . '.features';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        if ($results[0]['count'] === '0') {
            return true;
        }
        return false;
    }
    
}
