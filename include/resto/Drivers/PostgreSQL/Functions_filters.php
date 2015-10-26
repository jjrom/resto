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
 * RESTo PostgreSQL filters functions
 */
class Functions_filters {
    
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
     */
    public function __construct() {}
    
    /**
     * Return an array of database column names
     * 
     * @param RestoModel $model
     * 
     * @return array
     */
    public function getSQLFields($model) {

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
     * Return search filters based on model and input search parameters
     *
     * @param RestoUser $user
     * @param RestoModel $model
     * @param Array $params
     * @return boolean
     */
    public function prepareFilters($user, $model, $params) {
       
        $filters = array();
        
        /**
         * Append filter for contextual search
         */
        $filterCS = $this->prepareFilterQuery_contextualSearch($user, $model);
        if (isset($filterCS) && $filterCS !== '') {
            $filters[] = $filterCS;
        }

        /*
         * Process each input search filter excepted excluded filters
         */
        foreach (array_keys($model->searchFilters) as $filterName) {
            
            if (!isset($params[$filterName]) || $params[$filterName] === '') {
                continue;
            }
            
            /*
             * First check if filter is valid and as an associated column within database
             */
            if (!in_array($filterName, $this->excludedFilters) && $model->getDbKey($model->searchFilters[$filterName]['key'])) {
                $filter = $this->prepareFilterQuery($model, $params, $filterName);
                if (isset($filter) && $filter !== '') {
                    $filters[] = $filter;
                }
            }
            /*
             * Check for included function
             */
            else if (isset($model->searchFilters[$filterName]['function'])) {
                $function = $model->searchFilters[$filterName]['function'];
                $filters[] = $model->$function($params[$filterName], $user);
            }
        }
        
        return $filters;
        
    }

    /**
     * Filter search result on group attribute using
     * the groups list from user profile
     * 
     * @param RestoUser $user
     * @param RestoModel $model
     * @return string
     */
    private function prepareFilterQuery_contextualSearch($user, $model) {
        
        /*
         * Admin user has no restriction on search
         */
        if ($user->isAdmin()) {
            return null;
        }
         
        /*
         * Merge user groups with 'public' visibility
         * Note: feature with 'public' visibility can be seen by every user
         * (even unregistered)
         */
        $visibilities = array();
        $groups = explode(',', (isset($user->profile['groups']) ? $user->profile['groups'] . ',' : '') . 'public');
        for ($i = count($groups); $i--;) {
            $visibilities[] = '\'' . pg_escape_string($groups[$i]) . '\'';
        }
 
        return $model->properties['visibility']['name'] . ' IN (' . join(',', $visibilities) . ')';
        
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
     * Prepare SQL query for date
     * 
     * @param RestoModel $model
     * @param string $filterName
     * @param array $filters
     * @return type
     */
    private function prepareFilterQuery_date($model, $filterName, $filters) {
        return $model->getDbKey($model->searchFilters[$filterName]['key']) . ' ' . $model->searchFilters[$filterName]['operation'] . ' \'' . pg_escape_string($filters[$filterName]) . '\'';
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
        
        /*
         * Array of values assumes a 'OR' operation
         */
        $ors = $this->prepareORFilters($model, $filterName, $requestParams, $type);
        
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
            return $this->intersectFilterBBOX($model, $filterName, explode(',', $requestParams[$filterName]), $exclusion);
        }
        
        if ($filterName === 'geo:geometry') {
            return ($exclusion ? 'NOT ' : '') . 'ST_intersects(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ", ST_GeomFromText('" . pg_escape_string($requestParams[$filterName]) . "', 4326))";
        }
        
        return null;
    }
    
    private function prepareORFilters($model, $filterName, $requestParams, $type) {
        
        /*
         * Set quote to "'" for non numeric filter types
         */
        $quote = $type === 'numeric' ? '' : '\'';
        
        /*
         * Set operation
         */
        $operation = $model->searchFilters[$filterName]['operation'];
        
        /*
         * Split requestParams on |
         */
        $values = explode('|', $requestParams[$filterName]);
        $ors = array();
        for ($i = count($values); $i--;) {
            
            /*
             * LIKE case only if at least 4 characters
             */
            if ($operation === '=' && substr($values[$i], -1) === '%') {
                if  (strlen($values[$i]) < 3) {
                    RestoLogUtil::httpError(400, '% is only allowed for string with 3+ characters');
                }
                $ors[] = $model->getDbKey($model->searchFilters[$filterName]['key']) . ' LIKE ' . $quote . pg_escape_string($values[$i]) . $quote;
            }
            /*
             * Otherwise use operation
             */
            else {
                $ors[] = $model->getDbKey($model->searchFilters[$filterName]['key']) . ' ' . $operation . ' ' . $quote . pg_escape_string($values[$i]) . $quote;
            }
        }
        return $ors;
    }
    
    /**
     * Prepare SQL query for spatial operation ST_Intersects (Input bbox or polygon)
     * 
     * @param RestoModel $model
     * @param string $filterName
     * @param array $coords
     * @param boolean $exclusion
     * @return type
     */
    private function intersectFilterBBOX($model, $filterName, $coords, $exclusion) {
        if (count($coords) !== 4) {
            RestoLogUtil::httpError(400, 'Invalid geo:box');
        }
        return ($exclusion ? 'NOT ' : '') . 'ST_intersects(' . $model->getDbKey($model->searchFilters[$filterName]['key']) . ", ST_GeomFromText('" . pg_escape_string('POLYGON((' . $coords[0] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[1] . '))') . "', 4326))";
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
        $filters = array(
            'with' => array(),
            'without' => array()
        );
        
        /*
         * Process each searchTerms
         * 
         * Note: replace geohash: by hash: (see rocket)
         */
        for ($i = 0, $l = count($splitted); $i < $l; $i++) {
            $terms = array_merge($this->processSearchTerms(str_replace('geohash:', 'hash:', $splitted[$i]), $filters, $model, $filterName, $exclusion));
        }

        return join(' AND ', array_merge($terms, $this->mergeHashesFilters($model->getDbKey($model->searchFilters[$filterName]['key']), $filters)));

    }
    
    /**
     * 
     * @param type $model
     * @param type $filterName
     * @param type $requestParams
     * @param boolean $exclusion
     */
    private function processSearchTerms($searchTerm, &$filters, $model, $filterName, $exclusion) {
        
        /*
         * If term as a '-' prefix then performs a "NOT keyword"
         * If keyword contain a + then transform it into a ' '
         */
        if (substr($searchTerm, 0, 1) === '-') {
            $searchTerm = substr($searchTerm, 1);
            $exclusion = true;
        }

        /*
         * Keywords structure is "type:value"
         */
        $typeAndValue = $this->getTypeAndValue($searchTerm);

        /*
         * Landuse columns are NUMERIC columns
         */
        if ($typeAndValue[0] === 'landuse') {
            return $this->getLandUseFilters($typeAndValue[1], $exclusion);
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
                $ors[] = $model->getDbKey($model->searchFilters[$filterName]['key']) . " @> ARRAY['" . pg_escape_string($typeAndValue[0] !== 'hash' ? $typeAndValue[0] . ':' . $values[$j] : $values[$j]) . "']";
            }
            return ($exclusion ? 'NOT (' : '(') . join(' OR ', $ors) . ')';
        }
        
        $filters[$exclusion ? 'without' : 'with'][] = "'" . pg_escape_string($typeAndValue[0] !== 'hash' ? $searchTerm : $typeAndValue[1]) . "'";
        
        return array();
    }
    
    /**
     * Prepare terms for landuse search
     * 
     * @param string $value
     * @param boolean $exclusion
     * @return array
     */
    private function getLandUseFilters($value, $exclusion) {
        $terms = array();
        if (in_array($value, array('cultivated', 'desert', 'flooded', 'forest','herbaceous','ice','urban','water'))) {
            $terms[] = 'lu_' . $value . ($exclusion ? ' = ' : ' > ') . '0';
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
     * Return type and value from searchTerm
     * 
     * @param string $searchTerm
     */
    private function getTypeAndValue($searchTerm) {
        $typeAndValue = explode(':', $searchTerm);
        if (count($typeAndValue) !== 2) {
            RestoLogUtil::httpError(400, 'Invalid keyword structure ' . $searchTerm);
        }
        return $typeAndValue;
    }
   
}
