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
 * RESTo PostgreSQL collections functions
 */
class Functions_collections {
    
    private $dbDriver = null;
    private $dbh = null;
    
    /**
     * Constructor
     * 
     * @param array $config
     * @param RestoCache $cache
     * @throws Exception
     */
    public function __construct($dbDriver) {
        $this->dbDriver = $dbDriver;
        $this->dbh = $dbDriver->dbh();
    }

    /**
     * List all collections
     * 
     * @return array
     * @throws Exception
     */
    public function getCollections() {
        $query = 'SELECT collection FROM resto.collections';
        return $this->dbDriver->fetch($this->dbDriver->query($query));
    }
    
    /**
     * Get description of all collections including facets
     * 
     * @param string $collectionName
     * @param array $facetFields
     * @return array
     * @throws Exception
     */
    public function getCollectionsDescriptions($collectionName = null, $facetFields = null) {
         
        $cached = $this->dbDriver->cache->retrieve(array('getCollectionsDescriptions', $facetFields));
        if (isset($cached)) {
            return $cached;
        }
        
        $collectionsDescriptions = array();
        $descriptions = $this->dbDriver->query('SELECT collection, status, model, mapping, license FROM resto.collections' . (isset($collectionName) ? ' WHERE collection=\'' . pg_escape_string($collectionName) . '\'' : ''));
        while ($collection = pg_fetch_assoc($descriptions)) {
            $collectionsDescriptions[$collection['collection']] = array(
                'model' => $collection['model'],
                'status' => $collection['status'],
                'propertiesMapping' => json_decode($collection['mapping'], true),
                'license' => isset($collection['license']) ? json_decode($collection['license'], true) : null,
                'osDescription' => $this->getOSDescriptions($collection['collection']),
                'statistics' => isset($facetFields) ? $this->dbDriver->get(RestoDatabaseDriver::STATISTICS, array('collectionName' => $collection['collection'], 'facetFields' => $facetFields)) : null
            );
        }
        
        /*
         * Store in cache
         */
        $this->dbDriver->cache->store(array('getCollectionsDescriptions', $facetFields), $collectionsDescriptions);
        
        return isset($collectionName) ? $collectionsDescriptions[$collectionName] : $collectionsDescriptions;
        
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
        return !empty($this->dbDriver->fetch($this->dbDriver->query($query)));
    }
    
    /**
     * Remove collection from RESTo database
     * 
     * @param RestoCollection $collection
     * @return array
     * @throws Exception
     */
    public function removeCollection($collection) {
        
        $results = $this->dbDriver->query('SELECT collection FROM resto.collections WHERE collection=\'' . pg_escape_string($collection->name) . '\'');
        $schemaName = $this->dbDriver->getSchemaName($collection->name);
        
        if (pg_fetch_assoc($results)) {
                
            /*
             * Delete (within transaction)
             *  - entry within osdescriptions table
             *  - entry within collections table
             */
            $query = 'BEGIN;';
            $query .= 'DELETE FROM resto.osdescriptions WHERE collection=\'' . pg_escape_string($collection->name) . '\';';
            $query .= 'DELETE FROM resto.collections WHERE collection=\'' . pg_escape_string($collection->name) . '\';';
            
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
     * @throws Exception
     */
    public function storeCollection($collection) {
        
        $schemaName = $this->dbDriver->getSchemaName($collection->name);
        
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
            throw new Exception($e->getMessage(), $e->getCode());
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
                'Developper' => $description['developper'],
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
                'visible' => 'btree',
                'platform' => 'btree',
                'resolution' => 'btree',
                'startDate' => 'btree',
                'completionDate' => 'btree',
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
                'geometry' => 'gist',
                'hashes' => 'gin'
            );
            foreach ($indices as $key => $indexType) {
                if (!empty($key)) {
                    $this->dbDriver->query('CREATE INDEX ' . $schemaName . '_features_' . $collection->model->getDbKey($key) . '_idx ON ' . $schemaName . '.features USING ' . $indexType . ' (' . $collection->model->getDbKey($key) . ($key === 'startDate' || $key === 'completionDate' ? ' DESC)' : ')'));
                }
            }
            $this->dbDriver->query('GRANT SELECT ON TABLE ' . $schemaName . '.features TO resto');
        }
    }
    
    /**
     * 
     * @param RestoCollection $collection
     */
    private function storeCollectionDescription($collection) {
        
        /*
         * Insert collection within collections table
         * 
         * CREATE TABLE resto.collections (
         *  collection          TEXT PRIMARY KEY,
         *  creationdate        TIMESTAMP,
         *  model               TEXT DEFAULT 'Default',
         *  status              TEXT DEFAULT 'public',
         *  license             TEXT,
         *  mapping             TEXT
         * );
         * 
         */
        $license = isset($collection->license) && count($collection->license) > 0 ? '\'' . pg_escape_string(json_encode($collection->license)) . '\'' : 'NULL';
        if (!$this->collectionExists($collection->name)) {
            $this->dbDriver->query('INSERT INTO resto.collections (collection, creationdate, model, status, license, mapping) VALUES(' . join(',', array('\'' . pg_escape_string($collection->name) . '\'', 'now()', '\'' . pg_escape_string($collection->model->name) . '\'', '\'' . pg_escape_string($collection->status) . '\'', $license, '\'' . pg_escape_string(json_encode($collection->propertiesMapping)) . '\'')) . ')');
        }
        else {
            $this->dbDriver->query('UPDATE resto.collections SET status = \'' . pg_escape_string($collection->status) . '\', mapping = \'' . pg_escape_string(json_encode($collection->propertiesMapping)) . '\', license=' . $license . ' WHERE collection = \'' . pg_escape_string($collection->name) . '\'');
        }

        /*
         * Insert OpenSearch descriptions within osdescriptions table
         * (one description per lang
         * 
         * CREATE TABLE resto.osdescriptions (
         *  collection          VARCHAR(50),
         *  lang                VARCHAR(2),
         *  shortname           VARCHAR(50),
         *  longname            VARCHAR(255),
         *  description         TEXT,
         *  tags                TEXT,
         *  developper          VARCHAR(50),
         *  contact             VARCHAR(50),
         *  query               VARCHAR(255),
         *  attribution         VARCHAR(255)
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
            foreach (array_keys($description) as $key) {
                $osFields[] = strtolower($key);
                $osValues[] = '\'' . pg_escape_string($description[$key]) . '\'';
            }
            $this->dbDriver->query('INSERT INTO resto.osdescriptions (' . join(',', $osFields) . ') VALUES(' . join(',', $osValues) . ')');
        }
        return true;
    }

}
