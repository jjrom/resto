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
 * RESTo PostgreSQL features functions
 */
class Functions_features {
    
    /*
     * Reference to database driver
     */
    private $dbDriver = null;
    
    /*
     * Reference to database handler
     */
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
        $this->dbh = $dbDriver->dbh;
    }

    /**
     * 
     * Get an array of features descriptions
     * 
     * @param RestoContext $context
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
    public function search($context, $collection, $params, $options) {
        
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
         */
        $filters = $filtersUtils->prepareFilters($model, $params);
        
        /*
         * TODO - get count from facet statistic and not from count() OVER()
         * 
         * TODO - Add filters depending on user rights
         * $oFilter = superImplode(' AND ', array_merge($filters, $this->getRightsFilters($this->R->getUser()->getRights($this->description['name'], 'get', 'search'))));
         */
        $oFilter = implode(' AND ', $filters);
        
        /*
         * Note that the total number of results (i.e. with no LIMIT constraint)
         * is retrieved with PostgreSQL "count(*) OVER()" technique
         */
        $query = 'SELECT ' . implode(',', $filtersUtils->getSQLFields($model)) . ($options['count'] ? ', count(' . $model->getDbKey('identifier') . ') OVER() AS totalcount' : '') . ' FROM ' . (isset($collection) ? $this->dbDriver->getSchemaName($collection->name) : 'resto') . '.features' . ($oFilter ? ' WHERE ' . $oFilter : '') . ' ORDER BY startdate DESC LIMIT ' . $options['limit'] . ' OFFSET ' . $options['offset'];
     
        /*
         * Retrieve products from database
         */
        return $this->toFeatureArray($context, $collection, $results = $this->dbDriver->query($query));
        
    }
    
    /**
     * 
     * Get feature description
     *
     * @param RestoContext $context
     * @param integer $identifier
     * @param RestoModel $model
     * @param RestoCollection $collection
     * @param array $filters
     * 
     * @return array
     * @throws Exception
     */
    public function getFeatureDescription($context, $identifier, $collection = null, $filters = array()) {
        $model = isset($collection) ? $collection->model : new RestoModel_default();
        $filtersUtils = new Functions_filters();
        $result = $this->dbDriver->query('SELECT ' . implode(',', $filtersUtils->getSQLFields($model)) . ' FROM ' . (isset($collection) ? $this->dbDriver->getSchemaName($collection->name) : 'resto') . '.features WHERE ' . $model->getDbKey('identifier') . "='" . pg_escape_string($identifier) . "'" . (count($filters) > 0 ? ' AND ' . join(' AND ', $filters) : ''));
        $arrayOfFeatureArray = $this->toFeatureArray($context, $collection, $result);
        return $arrayOfFeatureArray[0];
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
        return $this->dbDriver->exists($query);
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
            pg_query($this->dbh, 'BEGIN');
            
            /*
             * Store feature
             */
            pg_query($this->dbh, 'INSERT INTO ' . pg_escape_string($this->dbDriver->getSchemaName($collection->name)) . '.features (' . join(',', array_keys($columnsAndValues)) . ') VALUES (' . join(',', array_values($columnsAndValues)) . ')');
            
            /*
             * Store facets
             */
            $this->storeKeywordsFacets($collection, json_decode(trim($columnsAndValues['keywords'], '\''), true));
            
            pg_query($this->dbh, 'COMMIT');
            
        } catch (Exception $e) {
            pg_query($this->dbh, 'ROLLBACK');
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
            $this->dbDriver->query('DELETE FROM ' . (isset($feature->collection) ? $this->dbDriver->getSchemaName($feature->collection->name): 'resto') . '.features WHERE identifier=\'' . pg_escape_string($feature->identifier) . '\'');
            
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
     * @param RestoColeection $collection
     * @param array $results
     * @return array
     */
    private function toFeatureArray($context, $collection, $results) {
        $featuresArray = array();
        $featureUtil = new RestoFeatureUtil($context, $collection);
        while ($result = pg_fetch_assoc($results)) {
            $featuresArray[] = $featureUtil->toFeatureArray($result);
        }
        return $featuresArray;
    }
}
