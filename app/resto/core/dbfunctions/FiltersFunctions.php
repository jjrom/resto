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

    /*
     * JOINS table
     */
    private $joins = array();

    private $context;
    private $user;

    private $model;

    private $tablePrefix;

    /**
     * Constructor
     *
     * @param RestoResto $context : Resto Context
     * @param RestoUser $user : Resto user
     * @param RestoModel $model
     */
    public function __construct($context, $user, $model)
    {
        $this->context = $context;
        $this->user = $user;
        $this->model = $model;
        $this->tablePrefix = $this->context->dbDriver->targetSchema . '.' . $this->model->dbParams['tablePrefix'];
    }
    
    /**
     * Return search filters based on model and input search parameters
     *
     * @param array $paramsWithOperation
     * @param string $sortKey
     * @return array
     */
    public function prepareFilters($paramsWithOperation, $sortKey)
    {
        $filters = array();
        $sortFilters = array();

        /**
         * Append filter for contextual search
         */
        $filterCS = $this->prepareFilterQueryContextualSearch($this->tablePrefix . 'feature');
        if (isset($filterCS) && $filterCS !== '') {
            $filters[] = $filterCS;
        }
        
        /*
         * Skip the following
         */
        if (!empty($paramsWithOperation)) {
            /*
             * Process each input search filter excepted excluded filters
             */
            foreach (array_keys($this->model->searchFilters) as $filterName) {
                if (!isset($paramsWithOperation[$filterName]['value']) || $paramsWithOperation[$filterName]['value'] === '') {
                    continue;
                }

                /*
                 * Sorting special case
                 */
                if (!empty($sortKey) && ($filterName === 'resto:lt' || $filterName === 'resto:gt')) {
                    $sortFilters[] =   $this->optimizeNotEqual($paramsWithOperation[$filterName]['operation'], $this->tablePrefix . 'feature.' . $sortKey, '\'' . pg_escape_string($this->context->dbDriver->dbh, $paramsWithOperation[$filterName]['value']) . '\'');
                }

                /*
                 * Followings special case
                 */
                elseif ($filterName === 'resto:owner' && !ctype_digit($paramsWithOperation[$filterName]['value'])) {
                    if (isset($this->user->profile['id'])) {
                        /*
                         * Search on followed
                         */
                        if ($paramsWithOperation[$filterName]['value'] === 'f') {
                            $filters[] = array(
                                'value' => $this->tablePrefix . 'feature.' . $this->model->searchFilters[$filterName]['key'] . ' IN (SELECT userid FROM ' . $this->context->dbDriver->commonSchema . '.follower WHERE followerid=' . pg_escape_string($this->context->dbDriver->dbh, $this->user->profile['id']) .  ')',
                                'isGeo' => false
                            );
                        }
                        /*
                         * Search on followed + owner
                         */
                        elseif ($paramsWithOperation[$filterName]['value'] === 'F') {
                            $filters[] = array(
                                'value' => '(' . $this->tablePrefix . 'feature.' . $this->model->searchFilters[$filterName]['key'] . '=' . pg_escape_string($this->context->dbDriver->dbh, $this->user->profile['id']) . ' OR ' . $this->tablePrefix . 'feature.' . $this->model->searchFilters[$filterName]['key'] . ' IN (SELECT userid FROM ' . $this->context->dbDriver->commonSchema . '.follower WHERE followerid=' . pg_escape_string($this->context->dbDriver->dbh, $this->user->profile['id']) .  '))',
                                'isGeo' => false
                            );
                        } else {
                            RestoLogUtil::httpError(400);
                        }
                    } else {
                        RestoLogUtil::httpError(403);
                    }
                }
 
                /*
                 * Process valid filter if it has an associated column within database
                 */
                elseif (!in_array($filterName, $this->excludedFilters)) {
                    // [STAC] CQL2 filter must be processed separately
                    if (isset($this->model->searchFilters[$filterName]['operation']) && $this->model->searchFilters[$filterName]['operation'] === 'cql2') {
                        $filter = $this->prepareFilterQueryCQL2($paramsWithOperation[$filterName]['value']);
                    } elseif (isset($this->model->searchFilters[$filterName]['key'])) {
                        $filter = $this->prepareFilterQuery($paramsWithOperation, $filterName);
                    }

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
     * @param array $options
     *
     * @return string
     * @throws Exception
     */
    public function getWhereClause($filtersAndJoins, $options = array())
    {
        $size = count($filtersAndJoins['filters']);

        if ($size > 0) {
            $filters = array();
            for ($i = $size; $i--;) {
                if (!$options['addGeo'] && $filtersAndJoins['filters'][$i]['isGeo']) {
                    continue;
                }
                $filters[] = $filtersAndJoins['filters'][$i]['value'];
            }
            
            $mergedFilters = $options['sort'] ? array_merge($filters, $filtersAndJoins['sortFilters']) : $filters;
            
            return count($mergedFilters) === 0 ? trim(join(' ', array_unique($filtersAndJoins['joins']))) : join(' ', array(
                trim(join(' ', array_unique($filtersAndJoins['joins']))),
                'WHERE',
                join(' AND ', $mergedFilters)
            ));
        }

        return '';
    }

    /**
     * Filter search result on group attribute using
     * the groups list from user profile
     *
     * @param string $tableName
     * @return string
     */
    private function prepareFilterQueryContextualSearch($tableName)
    {
        /*
         * Admin user has no restriction on search
         */
        if ($this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID)) {
            return null;
        }

        return array(
            'value' =>  $tableName . '.visibility IN (' . join(',', $this->user->profile['groups']) . ')',
            'isGeo' => false
        );
    }

    /**
     *
     * Prepare an SQL WHERE clause from input filterName
     *
     * @param array $paramsWithOperation (with model keys)
     * @param string $filterName
     * @return string
     *
     */
    private function prepareFilterQuery($paramsWithOperation, $filterName)
    {
        $featureTableName = $this->tablePrefix . 'feature';
        $exclusion = isset($paramsWithOperation[$filterName]['not']) && $paramsWithOperation[$filterName]['not'] ? true : false;

        /*
         * Special case model
         */
        if ($filterName === 'resto:model') {
            return $this->prepareFilterQueryModel($featureTableName, $paramsWithOperation[$filterName]['value'], $exclusion);
        }
        
        /*
         * Special case for date - get id from timestamp
         *
         * [Issue][#267] Convert ',' to '.' character for seconds fraction since its a valid RFC339 (https://datatracker.ietf.org/doc/html/rfc3339)
         * but an invalid PostgreSQL date
         */
        if (in_array($filterName, array('time:start', 'time:end', 'dc:date'))) {
            return array(
                'value' => $this->optimizeNotEqual($paramsWithOperation[$filterName]['operation'], $this->addNot($exclusion) . $featureTableName . '.' . strtolower($this->model->searchFilters[$filterName]['key']) . '_idx ', ' timestamp_to_firstid(\'' . pg_escape_string($this->context->dbDriver->dbh, str_replace(',', '.', $paramsWithOperation[$filterName]['value'])) . '\')'),
                'isGeo' => false
            );
        }
        
        /*
         * Prepare filter from operation
         */
        switch ($paramsWithOperation[$filterName]['operation']) {
            /*
             * in
             */
            case 'in':
                return $this->prepareFilterQueryIn($featureTableName, $filterName, $paramsWithOperation[$filterName]['value'], $exclusion);
                /*
                 * searchTerms
                 */
            case 'keywords':
                return $this->prepareFilterQueryKeywords($featureTableName, $filterName, RestoUtil::splitString($paramsWithOperation[$filterName]['value']), $exclusion);
                /*
                 * Intersects i.e. geo:*
                 */
            case 'intersects':
                return $this->prepareFilterQueryIntersects($filterName, $paramsWithOperation[$filterName], $exclusion);
                /*
                 * Distance i.e. geo:lon, geo:lat and geo:radius
                 */
            case 'distance':
                return $this->prepareFilterQueryDistance($filterName, $paramsWithOperation, $exclusion);
                /*
                 * Intervals
                 */
            case 'interval':
                return array(
                    'value' => $this->addNot($exclusion) . QueryUtil::intervalToQuery($this->context->dbDriver->dbh, $paramsWithOperation[$filterName]['value'], $this->getTableName($filterName) . '.' . $this->model->searchFilters[$filterName]['key']),
                    'isGeo' => false
                );

                /*
                 * Simple case - non 'interval' operation on value or arrays
                 * Note that array of values assumes a 'OR' operation
                 */
            default:
                $ors = $this->prepareORFilters($filterName, $paramsWithOperation[$filterName], $exclusion);
                return array(
                    'value' => count($ors) > 1 ? '(' . join(' OR ', $ors) . ')' : $ors[0],
                    'isGeo' => false
                );
        }
    }

    /**
     *
     * Convert an input CQL2 request into a valid resto SQL WHERE clause
     *
     * @param string $cql2
     * @return array
     */
    private function prepareFilterQueryCQL2($cql2)
    {
        $filterParser = new FilterParser();
        try {
            $parsed = $filterParser->parseCQL2($cql2);
        } catch (Exception $e) {
            RestoLogUtil::httpError(400, $e->getMessage());
        }

        $sqls = array();
        foreach ($parsed as $operator => $filters) {
            $sqls[] = $this->cql2FiltersToSQL($filters, strtoupper($operator));
        }

        return array(
            'value' => join(' OR ', $sqls),
            'isGeo' => false
        );
    }

    /**
     * Prepare SQL query for operation in
     *
     * @param string $featureTableName
     * @param string $filterName
     * @param string $value
     * @param boolean $exclusion
     * @return string
     */
    private function prepareFilterQueryIn($featureTableName, $filterName, $value, $exclusion)
    { 
        $targetColumn = $featureTableName . '.' . $this->model->searchFilters[$filterName]['key'];
        $elements = explode(',', $value);
        if (count($elements) === 1) {
            // Special case because input id could be either the resto id (uuid) or the productIdentifier
            return array(
                'value' => $this->addNot($exclusion) . $targetColumn . '=\'' . pg_escape_string($this->context->dbDriver->dbh, $filterName === 'geo:uid' && ! RestoUtil::isValidUUID($value) ? RestoUtil::toUUID($value) : $value) . '\'',
                'isGeo' => false
            );
        }
        return array(
            'value' => $this->addNot($exclusion) . $targetColumn . ' IN (' . implode(',', array_map(function ($str) use ($targetColumn) {
                // Special case because input id could be either the resto id (uuid) or the productIdentifier
                return '\'' .  pg_escape_string($this->context->dbDriver->dbh, $targetColumn === 'id' && ! RestoUtil::isValidUUID($str) ? RestoUtil::toUUID($str) : $str) . '\'';
            }, $elements)) . ')',
            'isGeo' => false
        );
    }

    /**
     * Prepare SQL query for model
     *
     * @param string $featureTableName
     * @param string $modelName
     * @return string
     */
    private function prepareFilterQueryModel($featureTableName, $modelName)
    {
        return array(
            'value' => $featureTableName . '.collection IN (SELECT id FROM ' . $this->context->dbDriver->targetSchema . '.collection WHERE lineage @> ARRAY[\'' . pg_escape_string($this->context->dbDriver->dbh, $modelName) . '\'])',
            'isGeo' => false
        );
    }

    /**
     * Prepare SQL query for spatial operation ST_Intersects (Input bbox or polygon)
     *
     * @param string $filterName
     * @param array $filterValue
     * @param boolean $exclusion
     * @return string
     */
    private function prepareFilterQueryIntersects($filterName, $filterValue, $exclusion)
    {
        $output = null;
        $coords = null;

        /*
         * Default bounding box is the whole earth
         *
         * Note: input 3D bbox are accepted but converted to 2D
         */
        if ($filterName === 'geo:box') {
            $coords =  explode(',', $filterValue['value']);
            if (count($coords) === 6) {
                $coords = array($coords[0], $coords[1], $coords[3], $coords[4]);
            }
            $output = $this->intersectFilterBBOX($filterName, $coords, $exclusion);
        } elseif ($filterName === 'geo:geometry') {
            $tableName = $this->getGeometryTableName();

            // Eventually correct input GEOMETRYCOLLECTION with a ST_buffer
            $inputGeom = strpos($filterValue['value'], 'GEOMETRYCOLLECTION') === 0 ?  "ST_Buffer(ST_GeomFromText('" . pg_escape_string($this->context->dbDriver->dbh, $filterValue['value']) . "', 4326), 0)" : "ST_GeomFromText('" . pg_escape_string($this->context->dbDriver->dbh, $filterValue['value']) . "', 4326)";
            $output = $this->addNot($exclusion) . 'ST_intersects(' . $tableName . '.' . $this->model->searchFilters[$filterName]['key'] . ", " . $inputGeom . ")";
        }

        return array(
            'value' => $output,
            'wkt' => isset($coords) ? 'POLYGON((' . $coords[0] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[1] . '))' : $filterValue['value'],
            'isGeo' => true
        );
    }

    /**
     * Return array for OR filters
     *
     * @param string $filterName
     * @param array $filterValue
     * @param boolean $exclusion
     * @return array
     */
    private function prepareORFilters($filterName, $filterValue, $exclusion)
    {
        /*
         * Set quote to "'" for non numeric filter types
         */
        $quote = in_array($filterName, array('visibility', 'likes', 'comments', 'status', 'liked')) ? '' : '\'';

        /*
         * Split requestParams on |
         */
        $values = explode('|', $filterValue['value']);
        $ors = array();
        for ($i = count($values); $i--;) {
            $tableNameWitNot = $this->addNot($exclusion) . $this->getTableName($filterName);

            /*
             * LIKE case only if at least 4 characters
             */
            if ($filterValue['operation'] === '=' && substr($values[$i], -1) === '%') {
                if (strlen($values[$i]) < 4) {
                    RestoLogUtil::httpError(400, '% is only allowed for string with 3+ characters');
                }
                $ors[] = $tableNameWitNot . '.' . $this->model->searchFilters[$filterName]['key'] . ' LIKE ' . $quote . pg_escape_string($this->context->dbDriver->dbh, $values[$i]) . $quote;
            }
            /*
             * isNull case do not use value
             */
            elseif (strtolower($filterValue['operation']) === strtolower('isNull')) {
                $ors[] = $tableNameWitNot . '.' . $this->model->searchFilters[$filterName]['key'] . ' IS NULL';
            }
            /*
             * Otherwise use operation
             */
            else {
                $ors[] = $this->optimizeNotEqual($filterValue['operation'], $tableNameWitNot . '.' . $this->model->searchFilters[$filterName]['key'], $quote . pg_escape_string($this->context->dbDriver->dbh, $values[$i]) . $quote);
            }
        }
        return $ors;
    }

    /**
     * Prepare SQL query for spatial operation ST_Intersects with BBOX
     *
     * Note : in case of bounding box crosses the -180/180 line, split it into two separated polygons
     *
     * @param string $filterName
     * @param array $coords
     * @param boolean $exclusion
     * @return string
     */
    private function intersectFilterBBOX($filterName, $coords, $exclusion)
    {
        $tableName = $this->getGeometryTableName();
        
        /*
         * Query build is $start . $geometry . $end
         */
        $start = 'ST_intersects('. $tableName .  '.' . $this->model->searchFilters[$filterName]['key'] . ', ST_GeomFromText(\'';
        $end = '\', 4326))';

        /*
         * -180/180 line is not crossed
         * (aka the easy part)
         */
        if ($coords[0] <= $coords[2]) {
            $filter = $start . pg_escape_string($this->context->dbDriver->dbh, 'POLYGON((' . $coords[0] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[1] . '))') . $end;
        }
        /*
         * -180/180 line is crossed
         * (split in two polygons)
         */
        else {
            $filter = '(' . $start . pg_escape_string($this->context->dbDriver->dbh, 'POLYGON((' . $coords[0] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[3] . ',180 ' . $coords[3] . ',180 ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[1] . '))') . $end;
            $filter = $filter . ' OR ' . $start . pg_escape_string($this->context->dbDriver->dbh, 'POLYGON((-180 ' . $coords[1] . ',-180 ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[1] . ',-180 ' . $coords[1] . '))') . $end . ')';
        }

        return ($exclusion ? 'NOT ' : '') . $filter;
    }

    /**
     * Prepare SQL query for spatial operation ST_Distance (Input bbox or polygon)
     *
     * @param string $filterName
     * @param array $paramsWithOperation
     * @param boolean $exclusion
     * @return string
     */
    private function prepareFilterQueryDistance($filterName, $paramsWithOperation, $exclusion)
    {
        /*
         * ST_Distance does not use spatial index, but ST_DWithin yes :)
         * (see http://blog.cleverelephant.ca/2021/05/indexes-and-queries.html)
         */
        $useDistance = true;

        /*
         * geo:lon and geo:lat have preseance to geo:name
         * (avoid double call to Gazetteer)
         */
        if (isset($paramsWithOperation['geo:lon']) && isset($paramsWithOperation['geo:lat'])) {
            $tableName = $this->getGeometryTableName();

            $radius = RestoGeometryUtil::radiusInDegrees(isset($paramsWithOperation['geo:radius']) ? floatval($paramsWithOperation['geo:radius']['value']) : 10000, floatval($paramsWithOperation['geo:lat']['value']));
            if ($useDistance) {
                $wkt = 'POINT(' . $paramsWithOperation['geo:lon']['value'] . ' ' . $paramsWithOperation['geo:lat']['value'] . ')';
                return array(
                    'value' => $this->addNot($exclusion) . 'ST_dwithin(' . $tableName . '.' . $this->model->searchFilters[$filterName]['key'] . ', ST_GeomFromText(\'' . pg_escape_string($this->context->dbDriver->dbh, $wkt) . '\', 4326), '. $radius . ')',
                    'wkt' => $wkt,
                    'isGeo' => true
                );
            } else {
                $wkt = RestoGeometryUtil::WKTPolygonFromLonLat(floatval($paramsWithOperation['geo:lon']['value']), floatval($paramsWithOperation['geo:lat']['value']), $radius);
                return array(
                    'value' => $this->addNot($exclusion) . 'ST_intersects(' . $tableName . '.' . $this->model->searchFilters[$filterName]['key'] . ', ST_GeomFromText(\'' . pg_escape_string($this->context->dbDriver->dbh, $wkt) . '\', 4326))',
                    'wkt' => $wkt,
                    'isGeo' => true
                );
            }
        }
    }

    /**
     * Prepare SQL query for keywords/hashtags - i.e. searchTerms
     *
     * @param string $featureTableName
     * @param string $filterName
     * @param array $searchTerms
     * @param boolean $exclusion
     * @return string
     */
    private function prepareFilterQueryKeywords($featureTableName, $filterName, $searchTerms, $exclusion)
    {
        $terms = array();
        $filters = array(
            'with' => array(),
            'without' => array()
        );
        
        /*
         * Process each searchTerms
         *
         * Note: replace geouid: by hash: (see rocket)
         */
        for ($i = 0, $l = count($searchTerms); $i < $l; $i++) {
            $searchTerm = $searchTerms[$i];

            /*
             * Hashtags start with "#" or with "-#" (equivalent to "NOT #")
             */
            if (substr($searchTerm, 0, 1) === '#') {
                $searchTerm = ltrim($searchTerm, '#');
                $exclusion = false;
            } elseif (substr($searchTerm, 0, 2) === '-#') {
                $exclusion = true;
                $searchTerm = ltrim($searchTerm, '-#');
            }

            /*
             * Add prefix in front of all elements if needed
             * See for instance [eo:instrument]
             */
            if (isset($this->model->searchFilters[$filterName]['prefix'])) {
                $searchTerm = $this->addPrefix($searchTerm, $this->model->searchFilters[$filterName]['prefix']);
            }
            
            $terms = array_merge($this->processSearchTerms($searchTerm, $filters, $featureTableName, $filterName, $exclusion));
        }

        return array(
            'value' => join(' AND ', array_merge($terms, $this->mergeHashesFilters($featureTableName . '.' . $this->model->searchFilters[$filterName]['key'], $filters))),
            'isGeo' => false
        );
    }

    /**
     * Process input searchTerm
     * Possible values:
     *
     *    toto
     *    toto|titi|tutu => means 'toto' OR 'titi' OR 'tutu'
     *    toto,titi,tutu => means 'toto' AND 'titi' AND 'tutu'
     *    toto! => means 'toto' and all broader concept of 'toto' (needs resto-addon-sosa add-on)
     *    toto* => means 'toto' and all narrower concept of 'toto' (needs resto-addon-sosa add-on)
     *    toto~ => means 'toto' and all related concept of 'toto' (needs resto-addon-sosa add-on)
     *
     *
     * @param string $searchTerm
     * @param array $filters
     * @param string $featureTableName
     * @param string $filterName
     * @param boolean $exclusion
     * @return array
     */
    private function processSearchTerms($searchTerm, &$filters, $featureTableName, $filterName, $exclusion)
    {
        /*
         * The '|' character is understood as "OR"
         * For performance reason it is better to use && operator instead of multiple @> with OR
         */
        $operator = null;
        $exploded = explode('|', $searchTerm);
        if (count($exploded) > 1) {
            $operator = '&&';
        } else {
            $exploded = explode(',', $searchTerm);
            if (count($exploded) > 1) {
                $operator = '@>';
            }
        }

        if (isset($operator)) {
            $quotedValues = array();
            for ($j = count($exploded); $j--;) {
                $quotedValues[] = '\'' . pg_escape_string($this->context->dbDriver->dbh, trim($exploded[$j])) . '\'';
            }
            return array($this->addNot($exclusion) . '(' . $featureTableName . '.' . $this->model->searchFilters[$filterName]['key'] . $operator . 'normalize_array(ARRAY[' . join(',', $quotedValues) . ']))');
        }
        
        $filters[$exclusion ? 'without' : 'with'][] = "'" . pg_escape_string($this->context->dbDriver->dbh, $searchTerm) . "'";

        return array();
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
     * @param string $filterName
     * @return string
     */
    private function getTableName($filterName)
    {
        for ($i = count($this->model->tables); $i--;) {
            if (in_array(strtolower($this->model->searchFilters[$filterName]['key']), $this->model->tables[$i]['columns'])) {
                $this->joins[] = 'JOIN ' . $this->tablePrefix . $this->model->tables[$i]['name'] . ' ON ' . $this->tablePrefix . 'feature.id=' . $this->tablePrefix . $this->model->tables[$i]['name'] . '.id';
                return $this->tablePrefix . $this->model->tables[$i]['name'];
            }
        }
        
        return $this->tablePrefix . 'feature';
    }

    /**
     *
     * If $this->context->dbDriver->useGeometryPart is true then geometry is indexed in targetSchema.geometry_part joined table
     * Otherwise is is directly retrieved from the indexed "feature_geometry" table
     * This should be used for large geometry
     *
     */
    private function getGeometryTableName()
    {
        if ($this->context->dbDriver->useGeometryPart) {
            $this->joins[] = 'JOIN ' . $this->tablePrefix . 'geometry_part ON ' . $this->tablePrefix . 'feature.id=' . $this->tablePrefix . 'geometry_part.id';
            return $this->tablePrefix . 'geometry_part';
        }

        return $this->tablePrefix . 'feature';
    }

    /**
     * Add prefix to each elements of input searchTerm
     *
     * @param string $searchTerm
     * @param string $prefix
     * @return string
     */
    private function addPrefix($searchTerm, $prefix)
    {
        $searchTerms = array();

        // OR case
        $splitter = '|';
        $exploded = explode($splitter, $searchTerm);
        if (count($exploded) < 2) {
            // AND case
            $splitter = ',';
            $exploded = explode($splitter, $searchTerm);
        }

        for ($j = count($exploded); $j--;) {
            $searchTerms[] = $prefix . RestoConstants::TAG_SEPARATOR . $exploded[$j];
        }
        
        return join($splitter, $searchTerms);
    }

    /**
     * Convert triplets extracted from FilterParser->parseCQL2 to equivalent SQL resto query
     *
     * Concretely, this means that STAC properties are renamed to their corresponding Resto filter name
     * Note - leading "properties." is discarded
     *
     *
     * Input example :
     *    Array(
     *      Array (
     *         [property] => properties.eo:cloud_cover
     *         [operator] => >
     *         [value] => 10
     *      ),
     *      Array (
     *         [property] => eo:cloud_cover
     *         [operator] => <=
     *         [value] => 30
     *      ),
     *      Array (
     *         [property] => geometry
     *         [operator] => intersects
     *         [value] => POINT(10 10)
     *      ),
     *      Array (
     *         [property] => instruments
     *         [operation] => =
     *         [value] => PHR
     *      )
     *    )
     *
     *  Output example :
     *    Array(
     *      'resto.feature_optical.cloudCover > 10',
     *      'resto.feature_optical.cloudCover <= 30',
     *      'ST_Intersects(resto.feature.geom, ST_GeomFromText('POINT(10 10)', 4326))',
     *      'resto.feature.normalized_hashtags @> normalize_array(ARRAY['instrument:PHR']
     *    )
     *
     *
     * @param array $cql2Filters
     * @param string $operator (AND|OR)
     * @return array
     *
     */
    private function cql2FiltersToSQL($cql2Filters, $operator)
    {
        $filters = array();
        $paramsWithOperation = array();

        for ($i = 0, $ii = count($cql2Filters); $i < $ii; $i++) {
            // Remove leading 'properties.' if present
            $stacKey = strpos($cql2Filters[$i]['property'], 'properties.') === 0 ? substr($cql2Filters[$i]['property'], 11) : $cql2Filters[$i]['property'];

            // STAC property must be renamed to resto osKey
            $filterName = $this->model->getFilterName($stacKey);
            
            if (!isset($filterName)) {
                RestoLogUtil::httpError(400, 'Unknown property in filter - ' . $stacKey);
            }

            /*
             * [STAC][WFS] convert datetime to time:start
             */
            if ($filterName === 'resto:datetime') {
                $filterName = 'time:start';
            }

            $paramsWithOperation[$filterName] = array(
                'value' => $cql2Filters[$i]['value'],
                'operation' => $cql2Filters[$i]['operation'],
                'not' => $cql2Filters[$i]['not'] ?? false
            );

            // If filter model operation is 'keywords' then it must be changed !
            if (isset($this->model->searchFilters[$filterName]['operation']) && $this->model->searchFilters[$filterName]['operation'] === 'keywords') {
                $paramsWithOperation[$filterName]['operation'] = 'keywords';
                if ($cql2Filters[$i]['operation'] === '<>') {
                    $paramsWithOperation[$filterName]['not'] = ! $paramsWithOperation[$filterName]['not'];
                }
            }

            $filters[] = $this->prepareFilterQuery($paramsWithOperation, $filterName)['value'];
        }
        
        return join(' ' . $operator . ' ', $filters);
    }

    /**
     * Return NOT prefix if exclusion is true
     */
    private function addNot($exclusion)
    {
        return isset($exclusion) && $exclusion ? 'NOT ' : '';
    }

    /**
     * Convert <> operation to < AND > to force database index usage
     *
     * @param string $operation
     * @param string $before
     * @param string $after
     * @return string
     */
    private function optimizeNotEqual($operation, $before, $after)
    {
        return $operation === '<>' ? $before . '<' . $after . ' AND ' . $before . '>' . $after : $before . $operation . $after;
    }
}
