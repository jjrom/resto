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
 * RESTo PostgreSQL filters functions
 */
class FiltersFunctions
{

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
        'geo:radius', // linked to geo:lon
        'resto:sort', // Sort special case
        'resto:lt', // Sort special case
        'resto:gt', // Sort special case
        'resto:liked' // Special case for liked
    );

    private $dbDriver = null;

    /*
     * JOINS table
     */
    private $joins = array();

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
     * Return search filters based on model and input search parameters
     *
     * @param RestoUser $user
     * @param RestoModel $model
     * @param Array $params
     * @param string $sortKey
     * @return array()
     */
    public function prepareFilters($user, $model, $params, $sortKey)
    {
        $filters = array();
        $sortFilters = array();

        /**
         * Append filter for contextual search
         */
        $filterCS = $this->prepareFilterQueryContextualSearch($user);
        if (isset($filterCS) && $filterCS !== '') {
            $filters[] = $filterCS;
        }

        /*
         * Skip the following
         */
        if (!empty($params)) {

            /*
             * Process each input search filter excepted excluded filters
             */
            foreach (array_keys($model->searchFilters) as $filterName) {
                if (!isset($params[$filterName]) || $params[$filterName] === '') {
                    continue;
                }

                /*
                 * Sorting special case
                 */
                if (!empty($sortKey) && ($filterName === 'resto:lt' || $filterName === 'resto:gt')) {
                    $sortFilters[] = 'resto.feature.' . $sortKey . $model->searchFilters[$filterName]['operation'] . '\'' . pg_escape_string($params[$filterName]) . '\'';
                }

                /*
                 * Followings special case
                 */
                elseif ($filterName === 'resto:owner' && !ctype_digit($params[$filterName])) {
                    if (isset($user->profile['id'])) {

                        /*
                         * Search on followed
                         */
                        if ($params[$filterName] === 'f') {
                            $filters[] = 'resto.feature.' . $model->searchFilters[$filterName]['key'] . ' IN (SELECT userid FROM resto.follower WHERE followerid=' . pg_escape_string($user->profile['id']) .  ')';
                        }
                        /*
                         * Search on followed + owner
                         */
                        elseif ($params[$filterName] === 'F') {
                            $filters[] = '(resto.feature.' . $model->searchFilters[$filterName]['key'] . '=' . pg_escape_string($user->profile['id']) . ' OR resto.feature.' . $model->searchFilters[$filterName]['key'] . ' IN (SELECT userid FROM resto.follower WHERE followerid=' . pg_escape_string($user->profile['id']) .  '))';
                        } else {
                            RestoLogUtil::httpError(400);
                        }
                    } else {
                        RestoLogUtil::httpError(403);
                    }
                }
                /*
                 * First check if filter is valid and as an associated column within database
                 */
                elseif (!in_array($filterName, $this->excludedFilters) && $model->searchFilters[$filterName]['key']) {
                    $filter = $this->prepareFilterQuery($model, $params, $filterName);
                    if (isset($filter) && $filter !== '') {
                        $filters[] = $filter;
                    }
                }
                
            }
        }
        
        return array(
            'filters' => $filters,
            'sortFilters' => $sortFilters,
            'joins' => $this->joins
        );
    }

    /**
     *
     * Get Where clause from input parameters
     *
     * @param array $filtersAndJoins
     * @param boolean $addSortFilters
     *
     * @return string
     * @throws Exception
     */
    public function getWhereClause($filtersAndJoins, $addSortFilters)
    {
        if (count($filtersAndJoins['filters']) > 0) {
            return join(' ', array(
                trim(join(' ', array_unique($filtersAndJoins['joins']))),
                'WHERE',
                join(' AND ', $addSortFilters ? array_merge($filtersAndJoins['filters'], $filtersAndJoins['sortFilters']) : $filtersAndJoins['filters'])
            ));
        }

        return '';
    }

    /**
     * Filter search result on group attribute using
     * the groups list from user profile
     *
     * @param RestoUser $user
     * @return string
     */
    private function prepareFilterQueryContextualSearch($user)
    {

        /*
         * Admin user has no restriction on search
         */
        if ($user->hasGroup(Resto::GROUP_ADMIN_ID)) {
            return null;
        }

        return 'resto.feature.visibility IN (' . join(',', $user->profile['groups']) . ')';
    }

    /**
     *
     * Prepare an SQL WHERE clause from input filterName
     *
     * @param RestoModel $model (with model keys)
     * @param array $requestParams (with model keys)
     * @param string $filterName
     * @param boolean $exclusion : if true, exclude instead of include filter (WARNING ! only works for geometry)
     * @return string
     *
     */
    private function prepareFilterQuery($model, $requestParams, $filterName, $exclusion = false)
    {

        /*
         * Special case model
         */
        if ($filterName === 'resto:model') {
            return $this->prepareFilterQueryModel($requestParams[$filterName]);
        }

        /*
         * Special case - published
         */
        if ($filterName === 'published') {
            return 'resto.feature.id ' . $model->searchFilters[$filterName]['operation'] . ' timestamp_to_id(\'' . pg_escape_string($requestParams[$filterName]) . '\')';
        }
            
        /*if (in_array($filterName, array('startDate', 'completionDate', 'published', 'updated'))) {
            return 'resto.feature.' . $model->searchFilters[$filterName]['key'] . ' ' . $model->searchFilters[$filterName]['operation'] . ' \'' . pg_escape_string($filters[$filterName]) . '\'';
            return $this->prepareFilterQueryDate($model, $filterName, $requestParams);
        }*/

        /*
         * Prepare filter from operation
         */
        switch ($model->searchFilters[$filterName]['operation']) {

            /*
             * searchTerms
             */
            case 'keywords':
                return $this->prepareFilterQueryKeywords($model, $filterName, $requestParams);
            /*
             * Intersects i.e. geo:*
             */
            case 'intersects':
                return $this->prepareFilterQueryIntersects($model, $filterName, $requestParams, $exclusion);
            /*
             * Distance i.e. geo:lon, geo:lat and geo:radius
             */
            case 'distance':
                return $this->prepareFilterQueryDistance($model, $filterName, $requestParams, $exclusion);
            /*
             * Intervals
             */
            case 'interval':
                return FormatUtil::intervalToQuery($requestParams[$filterName], $this->getTableName($model, $filterName) . $model->searchFilters[$filterName]['key']);
            /*
             * Simple case - non 'interval' operation on value or arrays
             */
            default:
                return $this->prepareFilterQueryGeneral($model, $filterName, $requestParams);
        }
    }

    /**
     * Prepare SQL query for model
     *
     * @param string $modelName
     * @return string
     */
    private function prepareFilterQueryModel($modelName)
    {
        return 'resto.feature.collection IN (SELECT name FROM resto.collection WHERE lineage @> ARRAY[\'' . pg_escape_string($modelName) . '\'])';
    }

    /**
     * Prepare SQL query for non 'interval' operation on value or arrays
     * If operation is '=' and last character of input value is a '%' sign then perform a like instead of an =
     *
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @return string
     */
    private function prepareFilterQueryGeneral($model, $filterName, $requestParams)
    {

        /*
         * Array of values assumes a 'OR' operation
         */
        $ors = $this->prepareORFilters($model, $filterName, $requestParams);

        return count($ors) > 1 ? '(' . join(' OR ', $ors) . ')' : $ors[0];
    }

    /**
     * Prepare SQL query for spatial operation ST_Intersects (Input bbox or polygon)
     *
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @param boolean $exclusion
     * @return string
     */
    private function prepareFilterQueryIntersects($model, $filterName, $requestParams, $exclusion)
    {

        /*
         * Default bounding box is the whole earth
         */
        if ($filterName === 'geo:box') {
            return $this->intersectFilterBBOX($model, $filterName, explode(',', $requestParams[$filterName]), $exclusion);
        }

        if ($filterName === 'geo:geometry') {
            $this->addToJoins('geometry_part', 'id');
            return ($exclusion ? 'NOT ' : '') . 'ST_intersects(resto.geometry_part.' . $model->searchFilters[$filterName]['key'] . ", ST_GeomFromText('" . pg_escape_string($requestParams[$filterName]) . "', 4326))";
        }

        return null;
    }

    /**
     * Return array for OR filters
     *
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @return array
     */
    private function prepareORFilters($model, $filterName, $requestParams)
    {

        /*
         * Set quote to "'" for non numeric filter types
         */
        $quote = in_array($filterName, array('visibility', 'likes', 'comments', 'status', 'liked')) ? '' : '\'';
        
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
            
            $tableName = $this->getTableName($model, $filterName);

            /*
             * LIKE case only if at least 4 characters
             */
            if ($operation === '=' && substr($values[$i], -1) === '%') {
                if (strlen($values[$i]) < 4) {
                    RestoLogUtil::httpError(400, '% is only allowed for string with 3+ characters');
                }
                $ors[] = $tableName . $model->searchFilters[$filterName]['key'] . ' LIKE ' . $quote . pg_escape_string($values[$i]) . $quote;
            }
            /*
             * Otherwise use operation
             */
            else {
                $ors[] = $tableName . $model->searchFilters[$filterName]['key'] . ' ' . $operation . ' ' . $quote . pg_escape_string($values[$i]) . $quote;
            }
        }
        return $ors;
    }

    /**
     * Prepare SQL query for spatial operation ST_Intersects with BBOX
     *
     * Note : in case of bounding box crosses the -180/180 line, split it into two separated polygons
     *
     * @param RestoModel $model
     * @param string $filterName
     * @param array $coords
     * @param boolean $exclusion
     * @return string
     */
    private function intersectFilterBBOX($model, $filterName, $coords, $exclusion)
    {
        if (count($coords) !== 4) {
            RestoLogUtil::httpError(400, 'Invalid geo:box');
        }

        $this->addToJoins('geometry_part', 'id');

        /*
         * Query build is $start . $geometry . $end
         */
        $start = 'ST_intersects(resto.geometry_part.' . $model->searchFilters[$filterName]['key'] . ', ST_GeomFromText(\'';
        $end = '\', 4326))';

        /*
         * -180/180 line is not crossed
         * (aka the easy part)
         */
        if ($coords[0] < $coords[2]) {
            $filter = $start . pg_escape_string('POLYGON((' . $coords[0] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[1] . '))') . $end;
        }
        /*
         * -180/180 line is crossed
         * (split in two polygons)
         */
        else {
            $filter = '(' . $start . pg_escape_string('POLYGON((' . $coords[0] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[3] . ',180 ' . $coords[3] . ',180 ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[1] . '))') . $end;
            $filter = $filter . ' OR ' . $start . pg_escape_string('POLYGON((-180 ' . $coords[1] . ',-180 ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[1] . ',-180 ' . $coords[1] . '))') . $end . ')';
        }

        return ($exclusion ? 'NOT ' : '') . $filter;
    }

    /**
     * Prepare SQL query for spatial operation ST_Distance (Input bbox or polygon)
     *
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @param boolean $exclusion
     * @return string
     */
    private function prepareFilterQueryDistance($model, $filterName, $requestParams, $exclusion)
    {

        /*
         * WARNING ! Quick benchmark show that st_distance is 100x slower than st_intersects
         * [TODO] - check if st_distance performance can be improved.
         */
        $useDistance = false;

        /*
         * geo:lon and geo:lat have preseance to geo:name
         * (avoid double call to Gazetteer)
         */
        if (isset($requestParams['geo:lon']) && isset($requestParams['geo:lat'])) {
            $this->addToJoins('geometry_part', 'id');
            $radius = RestoGeometryUtil::radiusInDegrees(isset($requestParams['geo:radius']) ? floatval($requestParams['geo:radius']) : 10000, floatval($requestParams['geo:lat']));
            if ($useDistance) {
                return 'ST_distance(resto.geometry_part.' . $model->searchFilters[$filterName]['key'] . ', ST_GeomFromText(\'' . pg_escape_string('POINT(' . $requestParams['geo:lon'] . ' ' . $requestParams['geo:lat'] . ')') . '\', 4326)) < ' . $radius;
            } else {
                return ($exclusion ? 'NOT ' : '') . 'ST_intersects(resto.geometry_part.' . $model->searchFilters[$filterName]['key'] . ', ST_GeomFromText(\'' . pg_escape_string(RestoGeometryUtil::WKTPolygonFromLonLat(floatval($requestParams['geo:lon']), floatval($requestParams['geo:lat']), $radius)) . '\', 4326))';
            }
        }
    }

    /**
     * Prepare SQL query for keywords/hashtags - i.e. searchTerms
     *
     * @param RestoModel $model
     * @param string $filterName
     * @param array $requestParams
     * @return string
     */
    private function prepareFilterQueryKeywords($model, $filterName, $requestParams)
    {
        $terms = array();
        $exclusion = false;
        $splitted = RestoUtil::splitString($requestParams[$filterName]);
        $filters = array(
            'with' => array(),
            'without' => array()
        );
        
        /*
         * Process each searchTerms
         *
         * Note: replace geouid: by hash: (see rocket)
         */
        for ($i = 0, $l = count($splitted); $i < $l; $i++) {

            $searchTerm = $splitted[$i];

            /*
             * Hashtags start with "#" or with "-#" (equivalent to "NOT #")
             */
            if (substr($searchTerm, 0, 1) === '#') {
                $searchTerm = ltrim($searchTerm, '#');
            } elseif (substr($searchTerm, 0, 2) === '-#') {
                $exclusion = true;
                $searchTerm = ltrim($searchTerm, '-#');
            }

            /*
             * Add prefix if needed
             */
            if (isset($model->searchFilters[$filterName]['prefix'])) {
                $searchTerm = $model->searchFilters[$filterName]['prefix'] . Resto::TAG_SEPARATOR . $searchTerm;
            }
            
            $terms = array_merge($this->processSearchTerms($searchTerm, $filters, $model, $filterName, $exclusion));
        }

        return join(' AND ', array_merge($terms, $this->mergeHashesFilters('resto.feature.' . $model->searchFilters[$filterName]['key'], $filters)));
    }

    /**
     *
     * @param string $searchTerm
     * @param array $filters
     * @param object $model
     * @param string $filterName
     * @param boolean $exclusion
     * @return array
     */
    private function processSearchTerms($searchTerm, &$filters, $model, $filterName, $exclusion)
    {

        $type = null;
        
        /*
         * searchTerm format is "value" or "type:value"
         */
        $splitted = explode(Resto::TAG_SEPARATOR, $searchTerm);
        if (isset($splitted[1])) {
            $type = $splitted[0];
            array_shift($splitted);
            $searchTerm = join(Resto::TAG_SEPARATOR, $splitted);
        }
        
        if (isset($type)) {

            /*
             * Everything other types are stored within hashtags column
             *
             * Structure is :
             *
             *      type{Resto::TAG_SEPARATOR}id or type{Resto::TAG_SEPARATOR}id1|id2|id3|.etc.
             *
             * In second case, '|' is understood as "OR"
             */
            $ors = array();
            $values = explode('|', $searchTerm);
            if (count($values) > 1) {
                for ($j = count($values); $j--;) {
                    $ors[] = 'resto.feature.' . $model->searchFilters[$filterName]['key'] . " @> normalize_array(ARRAY['" . pg_escape_string($type . Resto::TAG_SEPARATOR . $values[$j]) . "'])";
                }
                return array(($exclusion ? 'NOT (' : '(') . join(' OR ', $ors) . ')');
            }
        }
        
        $filters[$exclusion ? 'without' : 'with'][] = "'" . pg_escape_string((isset($type) ? $type . Resto::TAG_SEPARATOR : '') . $searchTerm) . "'";

        return array();
    }

    /**
     * Prepare terms for landcover search
     * 
     * Search within landcover table for value greater or lower than 25%
     *
     * @param string $value
     * @param string $tableName
     * @param boolean $exclusion
     * @return string
     */
    private function getLandCoverFilters($value, $tableName, $exclusion)
    {
        if (!isset($tableName)) {
            return RestoLogUtil::httpError(400, 'Search on landcover not supported');
        }

        if (!in_array($value, array('cultivated', 'desert', 'flooded', 'forest','herbaceous','ice','urban','water'))) {
            return RestoLogUtil::httpError(400, 'Invalid landcover - should be numeric value ');
        }
        
        // Add to joins table
        $this->addToJoins($tableName, 'id');
        
        return 'resto.' . $tableName . '.' . $value . ($exclusion ? ' < ' : ' > ') . '25)';

    }

    /**
     * Merge filters on hashes
     *
     * @param string $key
     * @param array $filters
     * @return array
     */
    private function mergeHashesFilters($key, $filters)
    {
        $terms = array();
        if (count($filters['without']) > 0) {
            $terms[] = 'NOT ' . $key . " @> normalize_array(ARRAY[" . join(',', $filters['without']) . "])";
        }
        if (count($filters['with']) > 0) {
            $terms[] = $key . " @> normalize_array(ARRAY[" . join(',', $filters['with']) . "])";
        }
        return $terms;
    }

    /**
     * Return a jointure if needed or untouched $query otherwise
     *
     * @param RestoModel $model
     * @param string $filterName
     * @return string
     */
    private function getTableName($model, $filterName)
    {
       
        for ($i = count($model->tables); $i--;) {
            if (in_array(strtolower($model->searchFilters[$filterName]['key']), $model->tables[$i]['columns'])) {
                $this->addToJoins($model->tables[$i]['name'], 'id');
                return 'resto.' . $model->tables[$i]['name'] . '.';
            }
        }
        
        return 'resto.feature.';
    }

    /**
     * Add a join entry to joins array
     * 
     * @param string $tableName
     * @param string $idName
     */
    private function addToJoins($tableName, $idName) {
        $this->joins[] = 'JOIN resto.' . $tableName . ' ON resto.feature.id=resto.' . $tableName . '.' . $idName;
    }

}
