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
 * RESTo PostgreSQL features functions
 */
class Functions_features {
    
    /*
     * Reference to database driver
     */
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
     * 
     * Get an array of features descriptions
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     * @param RestoCollection $collection
     * @param RestoModel $params
     * @param array $options
     *      array(
     *          'limit',
     *          'offset',
     *          'count'// true to return the total number of results without pagination
     * 
     * @return array
     * @throws Exception
     */
    public function search($context, $user, $collection, $params, $options) {
        
        /*
         * Search filters functions
         */
        $filtersUtils = new Functions_filters();
        
        /*
         * Set model
         */
        $model = isset($collection) ? $collection->model : new RestoModel_default();
        
        /*
         * Check that mandatory filters are set
         */
        $this->checkMandatoryFilters($model, $params);
        
        /*
         * Set search filters
         * TODO - get count from facet statistic and not from count() OVER()
         */
        $oFilter = implode(' AND ', $filtersUtils->prepareFilters($user, $model, $params));
        
        /*
         * Note that the total number of results (i.e. with no LIMIT constraint)
         * is retrieved with PostgreSQL "count(*) OVER()" technique
         */
        $query = 'SELECT ' . implode(',', $filtersUtils->getSQLFields($model)) . ($options['count'] ? ', count(' . $model->getDbKey('identifier') . ') OVER() AS totalcount' : '') . ' FROM ' . (isset($collection) ? '_' . strtolower($collection->name) : 'resto') . '.features' . ($oFilter ? ' WHERE ' . $oFilter : '') . ' ORDER BY startdate DESC LIMIT ' . $options['limit'] . ' OFFSET ' . $options['offset'];
       
        /*
         * Retrieve products from database
         */
        return $this->toFeatureArray($context, $user, $collection, $results = $this->dbDriver->query($query));
        
    }
    
    /**
     * 
     * Get feature description
     *
     * @param RestoContext $context
     * @param RestoUser $user
     * @param integer $identifier
     * @param RestoModel $model
     * @param RestoCollection $collection
     * @param array $filters
     * 
     * @return array
     * @throws Exception
     */
    public function getFeatureDescription($context, $user, $identifier, $collection = null, $filters = array()) {
        $model = isset($collection) ? $collection->model : new RestoModel_default();
        $filtersUtils = new Functions_filters();
        $results = $this->dbDriver->query('SELECT ' . implode(',', $filtersUtils->getSQLFields($model)) . ' FROM ' . (isset($collection) ? '_' . strtolower($collection->name) : 'resto') . '.features WHERE ' . $model->getDbKey('identifier') . "='" . pg_escape_string($identifier) . "'" . (count($filters) > 0 ? ' AND ' . join(' AND ', $filters) : ''));
        $arrayOfFeatureArray = $this->toFeatureArray($context, $user, $collection, $results);
        return isset($arrayOfFeatureArray['features']) && isset($arrayOfFeatureArray['features'][0]) ? $arrayOfFeatureArray['features'][0] : null;
    }
    
    /**
     * Check if feature identified by $identifier exists within {schemaName}.features table
     * 
     * @param string $identifier - feature unique identifier 
     * @param string $schema - schema name
     * @return boolean
     * @throws Exception
     */
    public function featureExists($identifier, $schema = null) {
        $query = 'SELECT 1 FROM ' . (isset($schema) ? pg_escape_string($schema) : 'resto') . '.features WHERE identifier=\'' . pg_escape_string($identifier) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return !empty($results);
    }
    
    /**
     * Insert feature within collection
     * 
     * @param RestoCollection $collection
     * @param array $featureArray
     * @throws Exception
     */
    public function storeFeature($collection, $featureArray) {
        
        /*
         * Check that resource does not already exist in database
         */
        if ($collection->context->dbDriver->check(RestoDatabaseDriver::FEATURE, array('featureIdentifier' => $featureArray['id']))) {
            RestoLogUtil::httpError(500, 'Feature ' . $featureArray['id'] . ' already in database');
        }
        
        /*
         * Get database columns array
         */
        $columnsAndValues = $this->getColumnsAndValues($collection, $featureArray);
        
        try {
            
            /*
             * Start transaction
             */
            pg_query($this->dbDriver->dbh, 'BEGIN');
            
            /*
             * Store feature
             */
            pg_query($this->dbDriver->dbh, 'INSERT INTO ' . pg_escape_string('_' . strtolower($collection->name)) . '.features (' . join(',', array_keys($columnsAndValues)) . ') VALUES (' . join(',', array_values($columnsAndValues)) . ')');
            
            /*
             * Store facets
             */
            $this->storeKeywordsFacets($collection, json_decode(trim($columnsAndValues['keywords'], '\''), true));
            
            pg_query($this->dbDriver->dbh, 'COMMIT');
            
        } catch (Exception $e) {
            pg_query($this->dbDriver->dbh, 'ROLLBACK');
            RestoLogUtil::httpError(500, 'Feature ' . $featureArray['id'] . ' cannot be inserted in database');
        }
    }
    
    /**
     * Remove feature from database
     * 
     * @param RestoFeature $feature
     */
    public function removeFeature($feature) {
        
        try {
            
            /*
             * Begin transaction
             */
            $this->dbDriver->query('BEGIN');
            
            /*
             * Remove feature
             */
            $this->dbDriver->query('DELETE FROM ' . (isset($feature->collection) ? '_' . strtolower($feature->collection->name): 'resto') . '.features WHERE identifier=\'' . pg_escape_string($feature->identifier) . '\'');
            
            /*
             * Remove facets
             */
            $this->removeFeatureFacets($feature->toArray());
            
            /*
             * Commit
             */
            $this->dbDriver->query('COMMIT');
            
        } catch (Exception $e) {
            $this->dbDriver->query('ROLLBACK'); 
            RestoLogUtil::httpError(500, 'Cannot delete feature ' . $feature->identifier);
        }
    }
   
    /**
     * Store keywords facets
     * 
     * @param RestoCollection $collection
     * @param array $keywords
     */
    private function storeKeywordsFacets($collection, $keywords) {
        
        /*
         * One facet per keyword
         */
        $facets = array();
        foreach ($keywords as $hash => $keyword) {
            if ($this->dbDriver->facetUtil->getFacetCategory($keyword['type'])) {
                $facets[] = array(
                    'name' => $keyword['name'],
                    'type' => $keyword['type'],
                    'hash' => $hash,
                    'parentHash' => isset($keyword['parentHash']) ? $keyword['parentHash'] : null
                );
            }
        }
        
        /*
         * Store to database
         */
        $this->dbDriver->store(RestoDatabaseDriver::FACETS, array(
            'facets' => $facets,
            'collectionName' => $collection->name
        ));
            
    }
    /**
     * Convert feature array to database column/value pairs
     * 
     * @param RestoCollection $collection
     * @param array $featureArray
     * @throws Exception
     */
    private function getColumnsAndValues($collection, $featureArray) {
        
        /*
         * Initialize columns array
         */
        $columns = array_merge(
            array(
                $collection->model->getDbKey('identifier') => '\'' . $featureArray['id'] . '\'',
                $collection->model->getDbKey('collection') => '\'' . $collection->name . '\'',
                $collection->model->getDbKey('geometry') => 'ST_GeomFromText(\'' . RestoGeometryUtil::geoJSONGeometryToWKT($featureArray['geometry']) . '\', 4326)',
                'updated' => 'now()',
                'published' => 'now()'
            ),
            $this->propertiesToColumns($collection, $featureArray['properties'])
        );
        
        return $columns;
            
    }
    
    /**
     * Convert feature properties array to database column/value pairs
     * 
     * @param RestoCollection $collection
     * @param array $properties
     * @throws Exception
     */
    private function propertiesToColumns($collection, $properties) {
        
        /*
         * Roll over properties
         */
        $columns = array();
        foreach ($properties as $propertyName => $propertyValue) {

            /*
             * Do not process null and already processed values
             */
            if (!isset($propertyValue) || in_array($propertyName, array('updated', 'published', 'collection'))) {
                continue;
            }
            
            /*
             * Keywords
             */
            if ($propertyName === 'keywords' && is_array($propertyValue)) {
                
                $columnValue = '\'' . pg_escape_string(json_encode($propertyValue)) . '\'';
                
                /*
                 * Compute hashes
                 */
                $columns[$collection->model->getDbKey('hashes')] = '\'{' . join(',', $this->getHashes($propertyValue)) . '}\'';
                
                /*
                 * landuse keywords are also stored in dedicated
                 * table columns to speed up search requests
                 */
                $columns = array_merge($columns, $this->landuseColumns($propertyValue));
                
            }
            /*
             * Special case for array
             */
            else if ($collection->model->getDbType($propertyName) === 'array') {
                $columnValue = '\'{' . pg_escape_string(join(',', $propertyValue)) . '}\'';
            }
            else {
                $columnValue = '\'' . pg_escape_string($propertyValue) . '\'';
            }
            
            /*
             * Add element
             */
            $columns[$collection->model->getDbKey($propertyName)] = $columnValue;
            
        }
        
        return $columns;

    }
    
    /**
     * Return array of hashes from keywords
     * 
     * @param type $keywords
     */
    private function getHashes($keywords) {
        $hashes = array();
        foreach (array_keys($keywords) as $hash) {
            
            /*
             * Do not index keywords if cover is lower than 10 %
             */
            if (isset($keywords[$hash]['value']) && $keywords[$hash]['value'] < 10) {
                continue;
            }
            $hashes[] = '"' . pg_escape_string($hash) . '"';
            $hashes[] = '"' . pg_escape_string($keywords[$hash]['type'] . ':' . (isset($keywords[$hash]['normalized']) ? $keywords[$hash]['normalized'] : strtolower($keywords[$hash]['name']))) . '"';
        }
        return $hashes;
    }
    
    /**
     * Get landuse database columns from input keywords
     * 
     * @param array $keywords
     * @return type
     */
    private function landuseColumns($keywords) {
        $columns = array();
        foreach (array_values($keywords) as $keyword) {
            if ($keyword['type'] === 'landuse') {
                $columns['lu_' . strtolower($keyword['name'])] = $keyword['value'];
            }
        }
        return $columns;
    }

    /**
     * Check that mandatory filters are set
     * 
     * @param RestoModel $model
     * @param Array $params
     * @return boolean
     */
    private function checkMandatoryFilters($model, $params) {
        $missing = array();
        foreach (array_keys($model->searchFilters) as $filterName) {
            if (isset($model->searchFilters[$filterName])) {
                if (isset($model->searchFilters[$filterName]['minimum']) && $model->searchFilters[$filterName]['minimum'] === 1 && (!isset($params[$filterName]))) {
                    $missing[] = $filterName;
                }
            } 
        }
        if (count($missing) > 0) {
            RestoLogUtil::httpError(400, 'Missing mandatory filter(s) ' . join(', ', $filterName));
        }
        
        return true;
        
    }
    
    /**
     * Remove feature facets
     * 
     * @param array $featureArray
     */
    private function removeFeatureFacets($featureArray) {
        foreach (array_keys($featureArray['properties']['keywords']) as $hash) {
            $this->dbDriver->remove(RestoDatabaseDriver::FACET, array(
                'hash' => $hash,
                'collectionName' => $featureArray['properties']['collection']
            ));
        }
    }

    /**
     * Return featureArray array from database results
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     * @param RestoCollection $collection
     * @param array $results
     * @return array
     */
    private function toFeatureArray($context, $user, $collection, $results) {
        $featuresArray = array();
        $featureUtil = new RestoFeatureUtil($context, $user, $collection);
        while ($result = pg_fetch_assoc($results)) {
            $featuresArray[] = $featureUtil->toFeatureArray($result);
            if (isset($result['totalcount'])) {
                $totalcount = $result['totalcount'];
            }
        }
        return array(
            'totalcount' => isset($totalcount) ? (integer) $totalcount : -1,
            'features' => $featuresArray
        );
    }
}
