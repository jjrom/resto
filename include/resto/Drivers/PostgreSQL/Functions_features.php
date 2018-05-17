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
     * @param array $params
     * @param array $options
     *      array(
     *          'limit',
     *          'offset'
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
         */
        $oFilter = implode(' AND ', $filtersUtils->prepareFilters($user, $model, $params));

        /*
         * Prepare query
         */
        $fields = implode(',', $filtersUtils->getSQLFields($model));
        $from = ' FROM ' . (isset($collection) ? '_' . strtolower($collection->name) : $this->dbDriver->schemaName) . '.features' . ($oFilter ? ' WHERE ' . $oFilter : '');

        /*
         * Result set ordering and limit
         */
        $sort = isset($options['sort']) && in_array(strtolower($options['sort']), $this->dbDriver->sortParams) ? strtolower($options['sort']) : 'startdate';
        $extra = ' ORDER BY ' . $sort . ' ' . (isset($options['order']) && preg_match('/^asc/i', $options['order']) ? 'ASC' : 'DESC');
        $extra .= ' LIMIT ' . $options['limit'] . ' OFFSET ' . $options['offset'];

        /*
         * Retrieve products from database
         * Note: totalcount is estimated except if input search contains a lon/lat filter
         */
        return array(
            'count' => $this->getCount($from, $params),
            'features' => $this->toFeatureArray($context, $user, $collection, $this->dbDriver->query('SELECT ' . $fields . $from . $extra))
        );
    }

    /**
     *
     * Get Where clause from input parameters
     *
     * @param RestoUser $user
     * @param RestoModel $model
     * @param array $params
     *
     * @return array
     * @throws Exception
     */
    public function getWhereClause($user, $model, $params) {
        $filtersUtils = new Functions_filters();
        return implode(' AND ', $filtersUtils->prepareFilters($user, $model, $params));
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
        $results = $this->dbDriver->query('SELECT ' . implode(',', $filtersUtils->getSQLFields($model)) . ' FROM ' . (isset($collection) ? '_' . strtolower($collection->name) : $this->dbDriver->schemaName) . '.features WHERE ' . $model->getDbKey('identifier') . "='" . pg_escape_string($identifier) . "'" . (count($filters) > 0 ? ' AND ' . join(' AND ', $filters) : ''));
        $arrayOfFeatureArray = $this->toFeatureArray($context, $user, $collection, $results);
        return isset($arrayOfFeatureArray[0]) ? $arrayOfFeatureArray[0] : null;
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
        $query = 'SELECT 1 FROM ' . (isset($schema) ? pg_escape_string($schema) : $this->dbDriver->schemaName) . '.features WHERE identifier=\'' . pg_escape_string($identifier) . '\'';
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
            if ($collection->context->storeFacets) {
                $this->storeKeywordsFacets($collection, json_decode(trim($columnsAndValues['keywords'], '\''), true));
            }

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
            $this->dbDriver->query('DELETE FROM ' . (isset($feature->collection) ? '_' . strtolower($feature->collection->name): $this->dbDriver->schemaName) . '.features WHERE identifier=\'' . pg_escape_string($feature->identifier) . '\'');

            /*
             * Remove facets
             */
            if ($feature->context->storeFacets) {
                $this->removeFeatureFacets($feature->toArray());
            }
            
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
     * Update feature keywords
     *
     * @param RestoFeature $feature
     * @param array $keywords
     * @throws Exception
     */
    public function updateFeatureKeywords($feature, $keywords) {

        $toUpdate = array();

        /*
         * Store new keywords
         */
        if (is_array($keywords)) {
            $columns = array_merge(array(
               'keywords' => '\'' . pg_escape_string(json_encode($keywords)) . '\''
            ), $this->landuseColumns($keywords));
            $columns[$feature->collection->model->getDbKey('hashes')] = '\'{' . join(',', $this->getHashes($keywords)) . '}\'';
            foreach ($columns as $columnName => $columnValue) {
                array_push($toUpdate, $columnName . '=' . $columnValue);
            }
        }

        if (empty($toUpdate)) {
            RestoLogUtil::httpError(400, 'Nothing to update for ' . $feature->identifier);
        }

        try {

            /*
             * Begin transaction
             */
            $this->dbDriver->query('BEGIN');

            /*
             * Remove previous facets
             */
            if ($feature->context->storeFacets) {
                $this->removeFeatureFacets($feature->toArray());
            }
            
            /*
             * Update feature
             */
            $this->dbDriver->query('UPDATE ' . $this->dbDriver->schemaName . '.features SET ' . join(',', $toUpdate) . ' WHERE identifier = \'' . pg_escape_string($feature->identifier) . '\'');

            /*
             * Store new facets
             */
            if ($feature->context->storeFacets) {
                $this->storeKeywordsFacets($feature->collection, $keywords, true);
            }

            /*
             * Commit
             */
            $this->dbDriver->query('COMMIT');

        } catch (Exception $e) {
            $this->dbDriver->query('ROLLBACK');
            RestoLogUtil::httpError(500, 'Cannot update feature ' . $feature->identifier);
        }

        return true;

    }

    /**
     * Return exact count or estimate count from query
     *
     * @param String $from
     * @param Boolean $filters
     */
    public function getCount($from, $filters = array()) {
        /*
         * Determine if the count is estimated or real
         */
        $realCount = false;
        if (isset($filters['geo:lon'])) {
            $realCount = true;
        }

        /*
         * Perform count estimation
         */
        $result = -1;
        if (!$realCount) {
            $result = pg_fetch_result($this->dbDriver->query('SELECT count_estimate(\'' . pg_escape_string('SELECT * ' . $from) . '\') as count'), 0, 0);
        }

        if ($result !== false && $result < 10 * $this->dbDriver->resultsPerPage) {
            $result = pg_fetch_result($this->dbDriver->query('SELECT count(*) as count ' . $from), 0, 0);
            $realCount = true;
        }

        return array(
            'total' => $result === false ? -1 : (integer) $result,
            'isExact' => $realCount
        );
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
                    'hash' => $keyword['id'],
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
        $wkt = RestoGeometryUtil::geoJSONGeometryToWKT($featureArray['geometry']);
        $extent = RestoGeometryUtil::getExtent($wkt);

        /*
         * Compute "in house centroid" to avoid -180/180 date line issue
         */
        $factor = 1;
        if (abs($extent[2] - $extent[0]) >= 180) {
            $factor = -1;
        }

        $columns = array_merge(
            array(
                $collection->model->getDbKey('identifier') => '\'' . $featureArray['id'] . '\'',
                $collection->model->getDbKey('collection') => '\'' . $collection->name . '\'',
                $collection->model->getDbKey('geometry') => 'ST_GeomFromText(\'' . $wkt . '\', 4326)',
                '_geometry' => 'ST_SplitDateLine(ST_GeomFromText(\'' . $wkt . '\', 4326))',
                $collection->model->getDbKey('centroid') => 'ST_GeomFromText(\'POINT(' . (($extent[2] + ($extent[0] * $factor)) / 2.0) . ' ' . (($extent[3] + $extent[1]) / 2.0) . ')\', 4326)',
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
            if (!isset($propertyValue) || in_array($propertyName, array('updated', 'published', 'collection', 'identifier', 'geometry', '_geometry', 'centroid'))) {
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
                $columnValue = '\'' . pg_escape_string(is_array($propertyValue) ? join(',', $propertyValue) : $propertyValue) . '\'';
            }

            /*
             * Add element only if exist in model
             */
            $column = $collection->model->getDbKey($propertyName);
            if (isset($column)) {
                $columns[$column] = $columnValue;
            }

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
        for ($i = count($keywords); $i--;) {

            /*
             * Do not index keywords if relative cover is lower than 10 % or if absolute coverage is lower than 20%
             */
            if (isset($keywords[$i]['value']) && $keywords[$i]['value'] < 10) {
                if (!isset($keywords[$i]['gcover']) || $keywords[$i]['gcover'] < 20) {
                    continue;
                }
            }
            $hashes[] = '"' . pg_escape_string($keywords[$i]['id']) . '"';
            $hashes[] = '"' . pg_escape_string($keywords[$i]['type'] . ':' . (isset($keywords[$i]['normalized']) ? $keywords[$i]['normalized'] : strtolower($keywords[$i]['name']))) . '"';
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
        $columns = array(
            'lu_cultivated' => 0,
            'lu_desert' => 0,
            'lu_flooded' => 0,
            'lu_forest' => 0,
            'lu_herbaceous' => 0,
            'lu_ice' => 0,
            'lu_urban' => 0,
            'lu_water' => 0
        );
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
        if (isset($featureArray['properties']['keywords'])) {
            foreach ($featureArray['properties']['keywords'] as $hash) {
                $this->dbDriver->remove(RestoDatabaseDriver::FACET, array(
                    'hash' => $hash['id'],
                    'collectionName' => $featureArray['properties']['collection']
                ));
            }
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
        }
        return $featuresArray;
    }
}
