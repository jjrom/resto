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
    
    private $dbDriver = null;
    
    private $dbh = null;
    
    /*
     * Non search filters are excluded from search
     */
    private $excludedFilters = array(
        'count',
        'startIndex',
        'startPage',
        'language',
        'geo:name',
        'geo:lat', // linked to geo:lon
        'geo:radius' // linked to geo:lon
    );
    
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
        $filters = $this->prepareFilters($model, $params);
        
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
        $query = 'SELECT ' . implode(',', $this->getSQLFields($model)) . ($options['count'] ? ', count(' . $model->getDbKey('identifier') . ') OVER() AS totalcount' : '') . ' FROM ' . (isset($collection) ? $this->dbDriver->getSchemaName($collection->name) : 'resto') . '.features' . ($oFilter ? ' WHERE ' . $oFilter : '') . ' ORDER BY startdate DESC LIMIT ' . $options['limit'] . ' OFFSET ' . $options['offset'];
     
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
        $result = $this->dbDriver->query('SELECT ' . implode(',', $this->getSQLFields($model)) . ' FROM ' . (isset($collection) ? $this->dbDriver->getSchemaName($collection->name) : 'resto') . '.features WHERE ' . $model->getDbKey('identifier') . "='" . pg_escape_string($identifier) . "'" . (count($filters) > 0 ? ' AND ' . join(' AND ', $filters) : ''));
        $featureUtil = new RestoFeatureUtil($context, $collection);
        return $featureUtil->toFeatureArray(pg_fetch_assoc($result));
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
            $this->toColumns($collection, $featureArray['properties'])
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
    private function toColumns($collection, $properties) {
        
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
     * 
     * Prepare an SQL WHERE clause from input filterName
     * 
     * @param RestoModel $model (with model keys)
     * @param array $requestParams (with model keys)
     * @param string $filterName
     * @param boolean $exclusion : if true, exclude instead of include filter (WARNING ! only works for geometry and keywords)
     * 
     */
    private function prepareFilterQuery($model, $requestParams, $filterName, $exclusion = false) {

        /*
         * Special case - dates
         */
        if ($model->getDbType($model->searchFilters[$filterName]['key']) === 'date') {
            return $this->prepareFilterQuery_date($model, $filterName, $requestParams);
        }

        /*
         * Prepare filter from operation
         */
        switch ($model->searchFilters[$filterName]['operation']) {
            
            /*
             * Keywords i.e. searchTerms
             */
            case 'keywords':
                return $this->prepareFilterQuery_keywords($model, $filterName, $requestParams, $exclusion);
            /*
             * Intersects i.e. geo:*
             */
            case 'intersects':
                return $this->prepareFilterQuery_intersects($model, $filterName, $requestParams, $exclusion);
            /*
             * Distance i.e. geo:lon, geo:lat and geo:radius
             */
            case 'distance':
                return $this->prepareFilterQuery_distance($model, $filterName, $requestParams, $exclusion);
            /*
             * Intervals 
             */
            case 'interval':
                return $this->prepareFilterQuery_interval($model, $filterName, $requestParams);
            /*
             * Simple case - non 'interval' operation on value or arrays
             */
            default:
                return $this->prepareFilterQuery_general($model, $filterName, $requestParams, $model->getDbType($model->searchFilters[$filterName]['key']));
        }
    }
    
    /**
     * Return an array of database column names
     * 
     * @param RestoModel $model
     * 
     * @return array
     */
    private function getSQLFields($model) {

        /*
         * Get Controller database fields
         */
        $columns = array();
        foreach (array_keys($model->properties) as $key) {

            /*
             * Avoid null value and excluded fields
             */
            if (!isset($model->properties[$key]) || isset($model->properties[$key]['notDisplayed'])) {
                continue;
            }
            
            $value = is_array($model->properties[$key]) ? $model->properties[$key]['name'] : $model->properties[$key];
            
            /*
             * Force geometry element to be retrieved as GeoJSON
             * Retrieve also BoundinBox in EPSG:4326
             */
            if ($key === 'geometry') {
                $columns[] = 'ST_AsGeoJSON(' . $value . ') AS ' . $key;
                $columns[] = 'Box2D(' . $value . ') AS bbox4326';
            }
            else if ($model->getDbType($key) === 'date') {
                $columns[] = 'to_char(' . $value . ', \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') AS "' . $key . '"';
            }
            /*
             * Other fields are retrieved normally
             */
            else {
                $columns[] = $value . ' AS "' . $key . '"';
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
     * Return search filters based on model and input search parameters
     * 
     * @param RestoModel $model
     * @param Array $params
     * @return boolean
     */
    private function prepareFilters($model, $params) {
       
        /*
         * Only visible features are returned
         */
        $filters = array(
            'visible=1'
        );
        
        /*
         * Process each input search filter excepted excluded filters
         */
        foreach (array_keys($model->searchFilters) as $filterName) {
            
            /*
             * First check if filter is valid and as an associated column within database
             */
            if (!in_array($filterName, $this->excludedFilters) && !empty($params[$filterName]) && $model->getDbKey($model->searchFilters[$filterName]['key'])) {
                $filter = $this->prepareFilterQuery($model, $params, $filterName);
                if (isset($filter) && $filter !== '') {
                    $filters[] = $filter;
                }
            }
        }
        
        return $filters;
        
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
     * Prepare SQL query for date
     * 
     * @param RestoModel $model
     * @param string $filterName
     * @param array $filters
     * @return type
     */
    private function prepareFilterQuery_date($model, $filterName, $filters) {
        
        if (!RestoUtil::isISO8601($filters[$filterName])) {
            RestoLogUtil::httpError(400, 'Invalid date parameter - ' . $filterName);
        }
        
        /*
         * Process time:start and time:end filters
         */
        switch ($filterName) {
            case 'time:start':
                return $model->getDbKey($model->searchFilters['time:start']['key']) . ' >= \'' . pg_escape_string($filters['time:start']) . '\'';
            case 'time:end':
                return $model->getDbKey($model->searchFilters['time:end']['key']) . ' <= \'' . pg_escape_string($filters['time:end']) . '\'';
            default:
                return null;
        }
    }
    
    /**
     * Prepare SQL query for non 'interval' operation on value or arrays
     * If operation is '=' and last character of input value is a '%' sign then perform a like instead of an =
     * 
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @paral string $type
     * @return type
     */
    private function prepareFilterQuery_general($model, $filterName, $requestParams, $type) {
        
        if (!is_array($requestParams[$filterName])) {
            $requestParams[$filterName] = array($requestParams[$filterName]);
        }
        
        /*
         * Set quote to "'" for non numeric filter types
         */
        $quote = $type === 'numeric' ? '' : '\'';
        
        /*
         * Set operation
         */
        $operation = $model->searchFilters[$filterName]['operation'];
        
        /*
         * Array of values assumes a 'OR' operation
         */
        $ors = array();
        for ($i = count($requestParams[$filterName]); $i--;) {
            
            /*
             * LIKE case
             */
            if ($operation === '=' && substr($requestParams[$filterName][$i], -1) === '%') {
                $ors[] = $model->getDbKey($model->searchFilters[$filterName]['key']) . ' LIKE ' . $quote . pg_escape_string($requestParams[$filterName][$i]) . $quote;
            }
            /*
             * Otherwise use operation
             */
            else {
                $ors[] = $model->getDbKey($model->searchFilters[$filterName]['key']) . ' ' . $operation . ' ' . $quote . pg_escape_string($requestParams[$filterName][$i]) . $quote;
            }
        }
        
        return count($ors) > 1 ? '(' . join(' OR ', $ors) . ')' : $ors[0];
    }
    
    /**
     * Prepare SQL query for spatial operation ST_Intersects (Input bbox or polygon)
     * 
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @param boolean $exclusion
     * @return type
     */
    private function prepareFilterQuery_intersects($model, $filterName, $requestParams, $exclusion) {
        
        /*
         * Default bounding box is the whole earth
         */
        if ($filterName === 'geo:box') {
            return $this->intersectFilterBBOX($model, $filterName, $requestParams, $exclusion);
        }
        
        if ($filterName === 'geo:geometry') {
            return ($exclusion ? 'NOT ' : '') . 'ST_intersects(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ", ST_GeomFromText('" . pg_escape_string($requestParams[$filterName]) . "', 4326))";
        }
        
        return null;
    }
    
    /**
     * Prepare SQL query for spatial operation ST_Intersects (Input bbox or polygon)
     * 
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @param boolean $exclusion
     * @return type
     */
    private function intersectFilterBBOX($model, $filterName, $requestParams, $exclusion) {
        
        $coords = explode(',', $requestParams[$filterName]);
        if (count($coords) !== 4) {
            RestoLogUtil::httpError(400, 'Invalid geo:box');
        }
        $lonmin = is_numeric($coords[0]) ? $coords[0] : -180;
        $latmin = is_numeric($coords[1]) ? $coords[1] : -90;
        $lonmax = is_numeric($coords[2]) ? $coords[2] : 180;
        $latmax = is_numeric($coords[3]) ? $coords[3] : 90;
        
        return ($exclusion ? 'NOT ' : '') . 'ST_intersects(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ", ST_GeomFromText('" . pg_escape_string('POLYGON((' . $lonmin . ' ' . $latmin . ',' . $lonmin . ' ' . $latmax . ',' . $lonmax . ' ' . $latmax . ',' . $lonmax . ' ' . $latmin . ',' . $lonmin . ' ' . $latmin . '))') . "', 4326))";
   
    }
    
    /**
     * Prepare SQL query for spatial operation ST_Distance (Input bbox or polygon)
     * 
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @param boolean $exclusion
     * @return type
     */
    private function prepareFilterQuery_distance($model, $filterName, $requestParams, $exclusion) {
        
        /*
         * WARNING ! Quick benchmark show that st_distance is 100x slower than st_intersects
         * TODO - check if st_distance performance can be improved.
         */
        $use_distance = false;

        /*
         * geo:lon and geo:lat have preseance to geo:name
         * (avoid double call to Gazetteer)
         */
        if (isset($requestParams['geo:lon']) && isset($requestParams['geo:lat'])) {
            $radius = RestoGeometryUtil::radiusInDegrees(isset($requestParams['geo:radius']) ? floatval($requestParams['geo:radius']) : 10000, $requestParams['geo:lat']);
            if ($use_distance) {
                return 'ST_distance(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ', ST_GeomFromText(\'' . pg_escape_string('POINT(' . $requestParams['geo:lon'] . ' ' . $lat = $requestParams['geo:lat'] . ')') . '\', 4326)) < ' . $radius;
            }
            else {
                $lonmin = $requestParams['geo:lon'] - $radius;
                $latmin = $requestParams['geo:lat'] - $radius;
                $lonmax = $requestParams['geo:lon'] + $radius;
                $latmax = $requestParams['geo:lat'] + $radius;
                return ($exclusion ? 'NOT ' : '') . 'ST_intersects(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ", ST_GeomFromText('" . pg_escape_string('POLYGON((' . $lonmin . ' ' . $latmin . ',' . $lonmin . ' ' . $latmax . ',' . $lonmax . ' ' . $latmax . ',' . $lonmax . ' ' . $latmin . ',' . $lonmin . ' ' . $latmin . '))') . "', 4326))";
            }
        }
    }
    
    /**
     * Prepare SQL query for keywords- i.e. searchTerms
     * 
     * !! IMPORTANT NOTE !!
     *      
     *      Searches are done on the array 'hashes' column
     * 
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @param boolean $exclusion
     * @return type
     */
    private function prepareFilterQuery_keywords($model, $filterName, $requestParams, $exclusion) {
        
        $terms = array();
        $splitted = RestoUtil::splitString($requestParams[$filterName]);
        $key = $model->getDbKey($model->searchFilters[$filterName]['key']);
        $filters = array(
            'with' => array(),
            'without' => array()
        );
        
        for ($i = 0, $l = count($splitted); $i < $l; $i++) {

            /*
             * If term as a '-' prefix then performs a "NOT keyword"
             * If keyword contain a + then transform it into a ' '
             */
            $searchTerm = ($exclusion ? '-' : '') . $splitted[$i];
            $not = false;
            if (substr($searchTerm, 0, 1) === '-') {
                $not = true;
                $searchTerm = substr($searchTerm, 1);
            }

            /*
             * Keywords structure is "type:value"
             */
            $typeAndValue = explode(':', $searchTerm);
            if (count($typeAndValue) !== 2) {
                RestoLogUtil::httpError(400, 'Invalid keyword strucuture ' . $searchTerm);
            }

            /*
             * TODO - need to be rewritten (see search(...))
             */
            if ($typeAndValue[0] === 'city') {
                continue;
            }
            
            /*
             * Landuse columns are NUMERIC columns
             */
            if ($typeAndValue[0] === 'landuse') {
                $terms[] = array_merge($terms, $this->getLandUseFilters($typeAndValue[1], $not));
                continue;
            }
            
            /*
             * Everything other types are stored within hashes column
             * If input keyword is a hash leave value unchanged
             * 
             * Structure is :
             * 
             *      type:id or type:id1|id2|id3|.etc.
             * 
             * In second case, '|' is understood as "OR"
             */
            $ors = array();
            $values = explode('|', $typeAndValue[1]);
            if (count($values) > 1) {
                for ($j = count($values); $j--;) {
                    $ors[] = $key . " @> ARRAY['" . pg_escape_string( $typeAndValue[0] !== 'hash' ? $typeAndValue[0] . ':' . $values[$j] : $values[$j]) . "']";
                }
                if (count($ors) > 1) {
                    $terms[] = ($not ? 'NOT (' : '(') . join(' OR ', $ors) . ')';
                }
            }
            else {
                $filters[$not ? 'without' : 'with'][] = "'" . pg_escape_string($typeAndValue[0] !== 'hash' ? $searchTerm : $typeAndValue[1]) . "'";
            }
         
        }

        return join(' AND ', array_merge($terms, $this->mergeHashesFilters($key, $filters)));

    }
    
    /**
     * Prepare terms for landuse search
     * 
     * @param string $value
     * @param boolean $not
     * @return array
     */
    private function getLandUseFilters($value, $not) {
        $terms = array();
        if (in_array($value, array('cultivated', 'desert', 'flooded', 'forest','herbaceous','snow','ice','urban','water'))) {
            $terms[] = 'lu_' . $value . ($not ? ' = ' : ' > ') . '0';
        }
        else {
            RestoLogUtil::httpError(400, 'Invalid landuse - should be numerice value ');
        }
        return $terms;
    }
    
    /**
     * Merge filters on hashes
     * 
     * @param string $key
     * @param array $filters
     * @return array
     */
    private function mergeHashesFilters($key, $filters) {
        $terms = array();
        if (count($filters['without']) > 0) {
            $terms[] = 'NOT ' . $key . " @> ARRAY[" . join(',', $filters['without']) . "]";
        }
        if (count($filters['with']) > 0) {
            $terms[] = $key . " @> ARRAY[" . join(',', $filters['with']) . "]";
        }
        return $terms;
    }
    
    /**
     * Prepare SQL query for intervals
     * 
     * If
     *      A is the value of $this->request['params'][$this->description['searchFilters'][$filterName]['osKey']]
     * Then
     *      A = n1 then returns value = n1
     *      A = {n1,n2} then returns  value = n1 or value = n2
     *      A = [n1,n2] then returns  n1 ≤ value ≤ n2
     *      A = [n1,n2[ then returns  n1 ≤ value < n2
     *      A = ]n1,n2[ then returns  n1 < value < n2
     *      A = ]n1 then returns n1 < value
     *      A = [n1 then returns  n1 ≤ value
     *      A = n1[ then returns value < n2
     *      A = n1] then returns value ≤ n2 
     * 
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @return type
     */
    private function prepareFilterQuery_interval($model, $filterName, $requestParams) {
        
        $values = explode(',', $requestParams[$filterName]);

        /*
         * No ',' present i.e. simple equality or non closed interval
         */
        if (count($values) === 1) {
            return $this->processSimpleInterval($model, $filterName, $requestParams, trim($values[0]));
        }
        /*
         * Two values
         */
        else if (count($values) === 2) {
            return $this->processComplexInterval($model, $filterName, $values);
        }
        
    }
    
    /**
     * Process simple interval
     * 
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @param string $value
     * @return string
     */
    private function processSimpleInterval($model, $filterName, $requestParams, $value) {
        
        /* 
         * A = ]n1 then returns n1 < value
         * A = n1[ then returns value < n2
         */
        $op1 = substr($value, 0, 1);
        if ($op1 === '[' || $op1 === ']') {
            return $model->getDbKey($model->searchFilters[$filterName]['key']) . ($op1 === '[' ? ' >= ' : ' > ') . pg_escape_string(substr($value, 1));
        }
        
        /*
         * A = [n1 then returns  n1 ≤ value
         * A = n1] then returns value ≤ n2 
         */
        $op2 = substr($value, -1);
        if ($op2 === '[' || $op2 === ']') {
            return $model->getDbKey($model->searchFilters[$filterName]['key']) . ($op2 === ']' ? ' <= ' : ' < ') . pg_escape_string(substr($value, 0, strlen($value) - 1));
        }
        
        /*
         * A = n1 then returns value = n1
         */
        return $model->getDbKey($model->searchFilters[$filterName]['key']) . ' = ' . pg_escape_string($requestParams[$filterName]);
    }
    
    /**
     * Process complex interval
     * 
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @param array $values
     * @return string
     */
    private function processComplexInterval($model, $filterName, $values) {
        
        /*
         * First and last characters give operators
         */
        $op1 = substr(trim($values[0]), 0, 1);
        $op2 = substr(trim($values[1]), -1);

        /*
         * A = {n1,n2} then returns  = n1 or = n2
         */
        if ($op1 === '{' && $op2 === '}') {
            return '(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ' = ' . pg_escape_string(substr($values[0], 1)) . ' OR ' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ' = ' . pg_escape_string(substr($values[1], 0, strlen($values[1]) - 1)) . ')';
        }

        /*
         * Other cases i.e. 
         * A = [n1,n2] then returns <= n1 and <= n2
         * A = [n1,n2[ then returns <= n1 and B < n2
         * A = ]n1,n2[ then returns < n1 and B < n2
         * 
         */
        if (($op1 === '[' || $op1 === ']') && ($op2 === '[' || $op2 === ']')) {
            return $model->getDbKey($model->searchFilters[$filterName]['key']) . ($op1 === '[' ? ' >= ' : ' > ') . pg_escape_string(substr($values[0], 1)) . ' AND ' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ($op2 === ']' ? ' <= ' : ' < ') . pg_escape_string(substr($values[1], 0, strlen($values[1]) - 1));
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
