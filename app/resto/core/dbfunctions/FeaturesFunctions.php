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
 * RESTo PostgreSQL features functions
 */
class FeaturesFunctions
{
    /**
     * List of columns from *.feature table
     * that are retrieved with SELECT
     *
     * Commented column are not retrieved
     */
    private $featureColumns = array(
        'id',
        'collection',
        'productIdentifier',
        'visibility',
        'title',
        'description',
        'startDate',
        'completionDate',
        'metadata',
        'assets',
        'links',
        'updated',
        'created',
        'catalogs',
        'likes',
        'comments',
        'owner',
        'status',
        'centroid',
        'geometry'
    );

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
    public function __construct($dbDriver)
    {
        $this->dbDriver = $dbDriver;
    }

    /**
     *
     * Get an array of features descriptions
     *
     * @param RestoContext $context
     * @param RestoUser $user
     * @param RestoModel $model
     * @param array $collections
     * @param array $paramsWithOperation
     * @param array $sorting
     *      array(
     *          'limit',
     *          'offset'
     * @return array
     * @throws Exception
     */
    public function search($context, $user, $model, $collections, $paramsWithOperation, $sorting)
    {
        /*
         * Check that mandatory filters are set
         */
        $this->checkMandatoryFilters($model->searchFilters, $paramsWithOperation);

        $featureTableName = $this->dbDriver->targetSchema . '.feature';
        
        /*
         * Set filters
         */
        $filtersFunctions = new FiltersFunctions($context, $user, $model);
        $filtersAndJoins = $filtersFunctions->prepareFilters($paramsWithOperation, $sorting['sortKey']);

        /*
         * If a resto:ckeywords was used, then automatically reduce the search on the collection
         */
        if (isset($paramsWithOperation['resto:ckeywords'])) {
            $collectionIds = array_keys($collections);
            if (count($collectionIds) === 0) {
                return array(
                    'links' => array(),
                    'count' => array(
                        'total' => 0,
                        'isExact' => true
                    ),
                    'features' => array()
                );
            }
            $filtersAndJoins['filters'][] = array(
                'value' => $featureTableName . '.collection IN (' . implode(',', array_map(function ($str) {
                    return '\'' .  $this->dbDriver->escape_string( $str) . '\'';
                }, $collectionIds)) . ')',
                'isGeo' => false
            );
        }
        
        /*
         * Special case for liked - return only features liked by owner if set, otherwise by $user
         */
        if (isset($paramsWithOperation['resto:liked']) && isset($context->addons['Social'])) {
            $who = isset($paramsWithOperation['resto:owner']) ? $paramsWithOperation['resto:owner']['value'] : $user->profile['id'];
            if (isset($who)) {
                $filtersAndJoins['filters'][] = array(
                    'value' => $this->dbDriver->targetSchema . '.likes.featureid=' . $featureTableName . '.id AND ' . $this->dbDriver->targetSchema . '.likes.userid=' . $this->dbDriver->escape_string( $who),
                    'isGeo' => false
                );
                $filtersAndJoins['joins'][] = 'JOIN ' . $this->dbDriver->targetSchema . '.likes ON ' . $featureTableName . '.id = ' . $this->dbDriver->targetSchema . '.likes.featureid';
            }
        }

        /*
         * Get sorting - the $sortKey  is used for 'resto:lt' and 'resto:gt' search filters
         */
        $extra = join(' ', array(
            'ORDER BY',
            $featureTableName . '.' . $sorting['sortKey'],
            $sorting['realOrder'],
            'LIMIT',
            $sorting['limit'],
            $sorting['offset'] > 0 ? ' OFFSET ' . $sorting['offset'] : ''
        ));

        /*
         * Prepare query
         */
        $query = join(' ', array(
            join(' ', $filtersAndJoins['withs']),
            $this->getSelectClause($featureTableName, $this->featureColumns, $user, array(
                'fields' => $context->query['fields'] ?? null,
                'useSocial' => isset($context->addons['Social']),
                'sortKey' => $sorting['sortKey']
            )),
            $filtersFunctions->getWhereClause($filtersAndJoins, array(
                'sort' => true,
                'addGeo' => true
            )),
            $extra
        ));
        
        //echo $query;

        /*
         * Retrieve products from database
         * Note: totalcount is estimated except if input search contains a lon/lat filter
         */
        try {
            $results = $this->dbDriver->query($query);
        } catch (Exception $e) {
            RestoLogUtil::httpError(400, $e->getMessage());
        }
        
        $features = (new RestoFeatureUtil($context, $user, $collections))->toFeatureArrayList($this->dbDriver->fetch($results));
        
        /*
         * Common where clause
         */
        $whereClause = $filtersFunctions->getWhereClause($filtersAndJoins, array('sort' => false, 'addGeo' => true));
        $count = $this->getCount('FROM ' . $featureTableName . ' ' . $whereClause, join(' ', $filtersAndJoins['withs']), $paramsWithOperation);

        $links = array();
        
        /*
         * Heatmap
         */
        $heatmapClassName = 'Heatmap';
        if (isset($context->addons[$heatmapClassName])) {

            $wkt = null;

            /*
             * Recompute where clause without geo information
             */
            if (isset($context->query['_heatmapNoGeo']) && filter_var($context->query['_heatmapNoGeo'], FILTER_VALIDATE_BOOLEAN)) {
                $whereClause = $filtersFunctions->getWhereClause($filtersAndJoins, array('sort' => false, 'addGeo' => false));
                // [IMPORTANT] Set empty $params in getCount() to avoid computation of real count
                $heatmapLink = (new $heatmapClassName($context, $user))->getEndPoint($featureTableName, $whereClause, $this->getCount('FROM ' . $featureTableName . ' ' . $whereClause, join(' ', $filtersAndJoins['withs']),), $wkt);
            } else {
                for ($i = count($filtersAndJoins['filters']); $i--;) {
                    if ($filtersAndJoins['filters'][$i]['isGeo']) {
                        $wkt = $filtersAndJoins['filters'][$i]['wkt'];
                        break;
                    }
                }
                $heatmapLink = (new $heatmapClassName($context, $user))->getEndPoint($featureTableName, $whereClause, $count, $wkt);
            }
            
            if (isset($heatmapLink)) {
                $links[] = $heatmapLink;
            }
        }

        return array(
            'links' => $links,
            'count' => $count,
            // Reverse features array if needed
            'features' => $sorting['realOrder'] !== $sorting['order'] ? array_reverse($features) : $features
        );
    }

    /**
     *
     * Get feature description
     *
     * @param RestoContext $context
     * @param RestoUser $user
     * @param string $featureId
     * @param RestoModel $model
     * @param RestoCollection $collection
     * @param string $fields
     *
     * @return array
     * @throws Exception
     */
    public function getFeatureDescription($context, $user, $featureId, $collection, $fields)
    {
        $model = isset($collection) ? $collection->model : new DefaultModel();

        $selectClause = $this->getSelectClause($this->dbDriver->targetSchema . '.feature', $this->featureColumns, $user, array(
            'fields' => $fields,
            'useSocial' => isset($context->addons['Social'])
        ));
        $filtersFunctions = new FiltersFunctions($context, $user, $model);
        $filtersAndJoins = $filtersFunctions->prepareFilters(array(), null);

        // Determine if search on id or productidentifier
        $filtersAndJoins['filters'][] = array(
            'value' => $this->dbDriver->targetSchema . '.feature.id=\'' . $this->dbDriver->escape_string( (RestoUtil::isValidUUID($featureId) ? $featureId : RestoUtil::toUUID($featureId))) . '\'',
            'isGeo' => false
        );
        $results = $this->dbDriver->fetch($this->dbDriver->query($selectClause . ' ' . $filtersFunctions->getWhereClause($filtersAndJoins, array('sort' => false, 'addGeo' => true))));
        return isset($results) && count($results) === 1 ? (new RestoFeatureUtil($context, $user, isset($collection) ? array($collection->id => $collection) : array()))->toFeatureArray($results[0]) : null;
    }

    /**
     * Check if feature identified by $featureId exists
     * If collectionId is set then returns true only if feature exists and within the collection
     *
     * @param string $featureId - feature UUID
     * @param string $featureTableName
     * @param string 
     * @return boolean
     * @throws Exception
     */
    public function featureExists($featureId, $featureTableName, $collectionId = null)
    {
        return !empty($this->getMinimalFeature($featureId, $featureTableName, $collectionId));
    }

    /**
     * Check if feature identified by $featureId exists
     * If collectionId is set then returns true only if feature exists and within the collection
     *
     * @param string $featureId - feature UUID
     * @param string $featureTableName
     * @param string 
     * @return array
     * @throws Exception
     */
    public function getMinimalFeature($featureId, $featureTableName, $collectionId = null)
    {

        $params = array(
            $featureId
        );
        $where = array(
            'id=$1'
        );

        if ( isset($collectionId) ) {
            $params[] = $collectionId;
            $where[] = 'collection=$2';
        }

        return $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT id,title FROM ' . $featureTableName . ' WHERE ' . join(' AND ', $where), $params));
    }

    /**
     * Insert feature within collection
     *
     * @param string $id
     * @param RestoCollection $collection
     * @param array $featureArray
     * @return array
     * @throws Exception
     */
    public function storeFeature($id, $collection, $featureArray)
    {
       
        // Default visibility
        if ( !isset($featureArray['properties']['visibility']) ) {
            $featureArray['properties']['visibility'] = RestoUtil::getDefaultVisibility($collection->user, isset($collection->user->profile['settings']['createdItemIsPublic']) ? $collection->user->profile['settings']['createdItemIsPublic'] : true);
        }
        $keysAndValues = $this->featureArrayToKeysValues(
            $collection,
            $featureArray,
            array(
                'id' => $id,
                'collection' => $collection->id,
                'visibility' => '{' . join(',', $featureArray['properties']['visibility']) . '}',
                'owner' => isset($collection) && isset($collection->user) ? $collection->user->profile['id'] : null,
                'status' => isset($featureArray['properties']) && isset($featureArray['properties']['status']) && is_int($featureArray['properties']['status']) ? $featureArray['properties']['status'] : 1,
                'likes' => 0,
                'comments' => 0,
                'metadata' => array(),
                'created' => 'now()',
                'created_idx' => 'now()',
                'updated' => isset($featureArray['properties']) && isset($featureArray['properties']['updated']) ? $featureArray['properties']['updated'] : 'now()',
                'geometry' => $featureArray['topologyAnalysis']['geometry'] ?? null,
                'centroid' => $featureArray['topologyAnalysis']['centroid'] ?? null,
                'geom' => $featureArray['topologyAnalysis']['geom'] ?? null
            ),
            array(
                'productIdentifier',
                'title',
                'description',
                'startDate',
                'completionDate'
            )
        );
        
        /*
         * Eventually an 'isExternal' catalog can be created during feature ingestion.
         * Check that user can create catalog
         */
        foreach (array_values($keysAndValues['catalogs']) as $catalog) {
            if (isset($catalog['isExternal']) && $catalog['isExternal']) {
                if ( !$collection->user->hasRightsTo(RestoUser::CREATE_CATALOG, array('catalog' => $catalog)) ) {
                    RestoLogUtil::httpError(403, 'Feature ingestion leads to creation of catalog ' . $catalog['id'] . ' but you don\'t have right to create catalogs');
                }
                break;
            }
        }
        
        /*
         * Generate pg_query_params $* array
         */
        try {
            /*
             * Get connection
             */
            $dbh = $this->dbDriver->getConnection();

            /*
             * Start transaction
             */
            pg_query($dbh, 'BEGIN');
            
            /*
             * Store feature - identifier is generated with public.timestamp_to_id()
             */
            $result = pg_fetch_assoc($this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->targetSchema . '.feature (' . join(',', array_keys($keysAndValues['keysAndValues'])) . ') VALUES (' . join(',', array_values($keysAndValues['params'])) . ') RETURNING id, productidentifier, title', array_values($keysAndValues['keysAndValues'])), 0);
            
            /*
             * Store feature content
             */
            $this->storeFeatureAdditionalContent($result['id'], $collection->id, $keysAndValues['modelTables']);

            /*
             * Store catalogs
             * [IMPORTANT] These are inner catalogs thus created using admin not user that post item !!!
             */
            (new CatalogsFunctions($this->dbDriver))->storeCatalogs($keysAndValues['catalogs'], $collection->context, $collection->user, $collection, array('id' => $result['id'], 'title' => $result['title'] ?? null), false, true);
        
            /*
             * Commit everything - rollback if one of the inserts failed
             */
            pg_query($dbh, 'COMMIT');

        } catch (Exception $e) {
            pg_query($dbh, 'ROLLBACK');
            RestoLogUtil::httpError(500, 'Feature ' . ($featureArray['properties']['productIdentifier'] ?? '') . ' cannot be inserted in database');
        }

        return array(
            'id' => $result['id'],
            'productIdentifier' => $result['productidentifier'] ?? null
        );

    }

    /**
     * Remove feature from database
     *
     * @param RestoFeature $feature
     */
    public function removeFeature($feature)
    {
        $featureArray = $feature->toArray();
        
        $model = isset($feature->collection) ? $feature->collection->model : new DefaultModel();

        /*
         * Remove feature
         */
        try {
            /*
             * Get connection
             */
            $dbh = $this->dbDriver->getConnection();

            /*
             * Start transaction
             */
            pg_query($dbh, 'BEGIN');
            
            /*
             * Update statistics counter for featureId - i.e. remove 1 per catalogs containing this feature 
             */
            $catalogsUpdated = (new CatalogsFunctions($this->dbDriver))->updateFeatureCatalogsCounters($feature->id, $feature->collection->id, -1);
        
            /*
             * Next remove
             */
            $result = pg_fetch_assoc($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.feature WHERE id=$1 RETURNING id', array($feature->id)));
            
            /*
             * Commit everything - rollback if one of the inserts failed
             */
            pg_query($dbh, 'COMMIT');

        } catch (Exception $e) {
            pg_query($dbh, 'ROLLBACK');
            RestoLogUtil::httpError(500, 'Feature ' . ($featureArray['productIdentifier'] ?? $feature->id) . ' cannot be removed from database');
        }

        if (empty($result)) {
            RestoLogUtil::httpError(404);
        }
        
        return array(
            'catalogsUpdated' => $catalogsUpdated
        );
    }

    /**
     * Udpate feature description
     *
     * @param RestoFeature $feature
     * @param RestoCollection $collection
     * @param array $newFeatureArray parameters to update
     */
    public function updateFeature($feature, $collection, $newFeatureArray)
    {
        if (!isset($feature)) {
            RestoLogUtil::httpError(404);
        }
        
        // Get old feature properties
        $oldFeatureArray = $feature->toArray();

        // Compute new keysAndValues
        $keysAndValues = $this->featureArrayToKeysValues(
            $collection,
            $newFeatureArray,
            array(
                'status' => isset($newFeatureArray['properties']) && isset($newFeatureArray['properties']['status']) && is_int($newFeatureArray['properties']['status']) ? $newFeatureArray['properties']['status'] : $oldFeatureArray['properties']['status'],
                'metadata' => array(),
                'updated' => isset($newFeatureArray['properties']) && isset($newFeatureArray['properties']['updated']) ? $newFeatureArray['properties']['updated'] : 'now()',
                'geometry' => $newFeatureArray['topologyAnalysis']['geometry'] ?? null,
                'centroid' => $newFeatureArray['topologyAnalysis']['centroid'] ?? null,
                'geom' => $newFeatureArray['topologyAnalysis']['geom'] ?? null
            ),
            array(
                'title',
                'description',
                'startDate',
                'completionDate'
            )
        );

        /*
         * Eventually an 'isExternal' catalog can be created during feature ingestion.
         * Check that user can create catalog
         */
        foreach (array_values($keysAndValues['catalogs']) as $catalog) {
            if (isset($catalog['isExternal']) && $catalog['isExternal']) {
                if ( !$collection->user->hasRightsTo(RestoUser::CREATE_CATALOG, array('catalog' => $catalog)) ) {
                    RestoLogUtil::httpError(403, 'Feature update leads to creation of catalog ' . $catalog['id'] . ' but you don\'t have right to create catalogs');
                }
                break;
            }
        }

        try {
            /*
             * Begin transaction
             */
            $this->dbDriver->query('BEGIN');

            /*
             * Update description
             */
            $toUpdate = $this->concatArrays(array_keys($keysAndValues['keysAndValues']), $keysAndValues['params'], '=');
            $this->dbDriver->pQuery(
                'UPDATE ' . $this->dbDriver->targetSchema . '.feature SET ' . join(',', $toUpdate) . ' WHERE id=$' . (count($toUpdate) + 1),
                array_merge(
                    array_values($keysAndValues['keysAndValues']),
                    array($feature->id)
                )
            );

            /*
             * Update model specific
             */
            $this->storeFeatureAdditionalContent($feature->id, $collection->id, $keysAndValues['modelTables']);
            
            /*
             * Remove then add catalogs
             * 
             *  1. First update resto.catalog counters
             *  2. Then delete resto.catalog_feature rows
             *  3. Then add resto.catalog_feature rows
             */
            (new CatalogsFunctions($this->dbDriver))->updateFeatureCatalogsCounters($feature->id, $feature->collection->id, -1);
            
            $this->dbDriver->pQuery(
                'DELETE FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE featureid=$1', array(
                    $feature->id
                )
            );
            (new CatalogsFunctions($this->dbDriver))->storeCatalogs($keysAndValues['catalogs'], $collection->context, $collection->user, $collection, $feature->toArray(), false, true);
        
            /*
             * Commit
             */
            $this->dbDriver->query('COMMIT');

        } catch (Exception $e) {
            $this->dbDriver->query('ROLLBACK');
            RestoLogUtil::httpError(500, 'Cannot update feature ' . $feature->id);
        }
        
        return RestoLogUtil::success('Udpate feature ' . $feature->id, array(
            'id' => $feature->id
        ));
    }

    /**
     * Update feature property
     *
     * @param RestoFeature $feature
     * @param array $properties
     * @param RestoContext $context
     * @param RestoUser $user
     * @throws Exception
     */
    public function updateFeatureProperties($feature, $properties, $context, $user)
    {

        try {

            foreach ($properties as $property => $value) {

                // Special case for description
                if ($property === 'description') {
                    $newCatalogs = (new Cataloger(null, null))->catalogsFromText($value);
                    $oldCatalogs = (new Cataloger(null, null))->catalogsFromText($feature->toArray()['properties']['description'] ?? null);
                    // Remove old catalogs and add new ones
                    for ($i = 0, $ii = count($oldCatalogs); $i < $ii; $i++) {
                        if ($oldCatalogs[$i]['id'] !== 'hashtags') {
                            (new CatalogsFunctions($this->dbDriver))->removeCatalogFeature($feature->id, $oldCatalogs[$i]['id']);
                        }
                    }
                    (new CatalogsFunctions($this->dbDriver))->storeCatalogs($newCatalogs, $context, $user, $feature->collection, $feature->toArray(), false, true);
                }

                // Check property type validity
                if (in_array($property, array('owner', 'status'))) {
                    if (! ctype_digit($value . '')) {
                        RestoLogUtil::httpError(400, 'Invalid ' . $property . ' type - should be numeric');
                    }
                }

                // Visibility
                if ($property === 'visibility') {
                    $value = QueryUtil::visibilityToSQL($value);
                }

                $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.feature SET ' . $property . '=$1 WHERE id=$2', array(
                    $value,
                    $feature->id
                ));
                
            }

        } catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode() ?? 500, $e->getMessage() ?? 'Cannot update ' . $property . ' for feature ' . $feature->id);
        }

        return RestoLogUtil::success('Properties ' . join(',', array_keys($properties)) . ' updated for feature ' . $feature->id);
        
    }

    /**
     * Return exact count or estimate count from query
     *
     * @param String $from
     * @param String $with
     * @param Boolean $filters
     */
    public function getCount($from, $with, $filters = array())
    {
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
            $result = pg_fetch_result($this->dbDriver->query(' SELECT count_estimate(\'' . $this->dbDriver->escape_string( $with . ' SELECT * ' . $from) . '\') as count'), 0, 0);
        }

        if ($result !== false && $result < 10 * $this->dbDriver->resultsPerPage) {
            $result = pg_fetch_result($this->dbDriver->query($with . ' SELECT count(*) as count ' . $from), 0, 0);
            $realCount = true;
        }

        /*
         * Approximate
         */
        if (!$realCount && $result !== false) {
            $result = $this->approximate((integer) $result);
        }
        
        return array(
            'total' => $result === false ? -1 : (integer) $result,
            'isExact' => $realCount
        );
    }

    /**
     * Store feature additional content
     *
     * @param string $featureId
     * @param string $collectionId
     * @param array $tables
     */
    private function storeFeatureAdditionalContent($featureId, $collectionId, $tables)
    {
        foreach ($tables as $tableName => $columnsAndValues) {
            if (count($columnsAndValues) === 0) {
                return false;
            }

            $updates = array();
            $count = 1;
            foreach (array_keys($columnsAndValues) as $key) {
                $updates[] =  $key . '=' . '$' . $count;
                $count++;
            }
            $columnsAndValues['id'] = $featureId;
            $columnsAndValues['collection'] = $collectionId;
            $this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->targetSchema . '.' . $tableName . ' (' . join(',', array_keys($columnsAndValues)) . ') VALUES (' . join(',', $this->getCounterList(count($columnsAndValues))) . ') ON CONFLICT (id) DO UPDATE SET ' . join(',', $updates), array_values($columnsAndValues));
        }

        return true;
    }

    /**
     * Convert feature array to database column/value pairs
     *
     * @param RestoCollection $collection
     * @param array $featureArray
     * @param array $protected
     * @param array $updatabled
     * @throws Exception
     * @return array
     */
    private function featureArrayToKeysValues($collection, $featureArray, $protected, $updatabled)
    {
        // Initialize
        $keysAndValues = array(
            'links' => isset($featureArray['links']) ? json_encode($featureArray['links'], JSON_UNESCAPED_SLASHES) : null,
            'assets' => isset($featureArray['assets']) ? json_encode($featureArray['assets'], JSON_UNESCAPED_SLASHES) : null
        );

        $output = array(
            'keysAndValues' => array(),
            'params' => array(),
            'modelTables' => array(),
            'catalogs' => $featureArray['catalogs']
        );

        /*
         * Roll over properties
         */
        foreach ($featureArray['properties'] as $propertyName => $propertyValue) {

            /*
             * Do not process null values and protected values
             */
            if (!isset($propertyValue) || in_array($propertyName, array_keys($protected))) {
                continue;
            }

            /*
             * Updatable properties
             */
            if (in_array($propertyName, $updatabled)) {
                $keysAndValues[strtolower($propertyName)] = $propertyValue;

                /*
                 * startDate special case, add startdate_idx
                 */
                if (strtolower($propertyName) === 'startdate') {
                    $keysAndValues['startdate_idx'] = $propertyValue;
                }
            }
            
            /*
             * Directly add to metadata
             */
            else {

                if ( !isset($keysAndValues['metadata']) ) {
                    $keysAndValues['metadata'] = array();
                }
                $keysAndValues['metadata'][$propertyName] = $propertyValue;

                // Model specific (do not process LandCoverModel !!)
                for ($i = 0, $ii = count($collection->model->tables); $i < $ii; $i++) {

                    /* 
                     *[IMPORTANT] Eventually convert STAC osKey to resto osKey
                     * since internal resto use osKey (e.g. "cloudCover" instead of "eo:cloud_cover" )
                     */
                    $updatedPropertyName = strtolower($collection->model->getRestoPropertyFromSTAC($propertyName));
                    if ($collection->model->tables[$i]['name'] !== 'feature_landcover' && in_array($updatedPropertyName, $collection->model->tables[$i]['columns'])) {
                        $output['modelTables'][$collection->model->tables[$i]['name']][$updatedPropertyName] = $propertyValue;
                        break;
                    }
                }
            }
        }
       
        /*
         * Catalogs
         */
        for ($i = count($output['catalogs']); $i--;) {

            // Append additionnal properties
            if ( isset($output['catalogs'][$i]['properties']) ) {
                if ( !isset($keysAndValues['metadata']) ) {
                    $keysAndValues['metadata'] = array();
                }
                $keysAndValues['metadata'] = array_merge($keysAndValues['metadata'], $output['catalogs'][$i]['properties']);
            }
            
            // Special content for LandCoverModel (i.e. itag keys)
            for ($j = 0, $jj = count($collection->model->tables); $j < $jj; $j++) {
                if ($collection->model->tables[$j]['name'] == 'feature_landcover') {
                    $columns = $this->getITagColumnFromCatalogs($output['catalogs'][$i], $collection->model->tables[$j]['columns']);
                    if ( !empty($columns) ) {
                        $output['modelTables']['feature_landcover'] = $columns;
                    }
                    break;
                }
            }
           
        }
        
        // JSON encode metadata
        $keysAndValues['metadata'] = isset($keysAndValues['metadata']) ? json_encode($keysAndValues['metadata'], JSON_UNESCAPED_SLASHES) : null;

        $counter = 0;
        $output['keysAndValues'] = array_merge($protected, $keysAndValues);
        foreach (array_keys($output['keysAndValues'] ?? array()) as $key) {
            if ($key === 'created_idx' || $key === 'startdate_idx') {
                $output['params'][] = 'public.timestamp_to_id($' . ++$counter . ')';
            } else {
                $output['params'][] = '$' . ++$counter;
            }
        }
        
        return $output;
    }

    /**
     * Get iTag column name from input catalogs
     *
     * @param array $catalogs
     * @param array $tableColumns
     * @return array
     */
    private function getITagColumnFromCatalogs($catalogs, $tableColumns)
    {
        $columns = array();
        for ($i = 0, $ii = count($catalogs); $i < $ii; $i++) {
            if (isset($catalogs[$i]['title']) && in_array(strtolower($catalogs[$i]['title']), $tableColumns)) {
                $columns[strtolower($catalogs[$i]['title'])] = $catalogs[$i]['properties']['pcover'];
            }
        }
        return $columns;
    }
    
    /**
     * Check that mandatory filters are set
     *
     * @param array $searchFilters
     * @param array $paramsWithOperation
     * @return boolean
     */
    private function checkMandatoryFilters($searchFilters, $paramsWithOperation)
    {
        $missing = array();
        foreach (array_keys($searchFilters) as $filterName) {
            if (isset($searchFilters[$filterName])) {
                if (isset($searchFilters[$filterName]['minimum']) && $searchFilters[$filterName]['minimum'] === 1 && (!isset($paramsWithOperation[$filterName]))) {
                    $missing[] = $filterName;
                }
            }
        }
        if (count($missing) > 0) {
            RestoLogUtil::httpError(400, 'Missing mandatory filter(s) ' . join(', ', $missing));
        }

        return true;
    }

    /**
     * Return $featureTableName SELECT clause from input columns
     *
     * @param string $tableName
     * @param array $featureColumns
     * @param RestoUser $user
     * @param array $options
     *                  {
     *                      "fields": "keywords,owner,etc." ,
     *                      "useSocial": false
     *                      "sortKey": "startDate"
     *                  }
     *
     * @return array
     */
    private function getSelectClause($featureTableName, $featureColumns, $user, $options)
    {
        $sanitized = $this->sanitizeSQLColumns($featureColumns, isset($options['fields']) ? array_map('trim', explode(',', $options['fields'])) : array());

        /*
         * Get Controller database fields
         */
        $columns = array();
        foreach ($sanitized['columns'] as $key) {
            /*
             * Avoid null value and excluded fields
             */
            if (in_array($key, $sanitized['discarded'])) {
                continue;
            }

            /*
             * Force geometry element to be retrieved as GeoJSON
             * Retrieve also BoundinBox in EPSG:4326
             */
            switch ($key) {
                // [IMPORTANT] The geometry returned is geom not geometry !!!
                case 'geometry':
                    $columns[] = 'ST_AsGeoJSON(' . $featureTableName . '.geom, 6) AS geometry';
                    $columns[] = 'Box2D(' . $featureTableName . '.geom) AS bbox4326';
                    break;

                case 'centroid':
                    $columns[] = 'ST_AsGeoJSON(' . $featureTableName . '.centroid, 6) AS centroid';
                    break;

                case 'startDate':
                case 'completionDate':
                case 'created':
                case 'updated':
                    $columns[] = 'to_iso8601(' . $featureTableName . '.' . $key . ') AS "' . $key . '"';
                    break;

                case 'catalogs':
                    $columns[] = '(SELECT array_to_json(array_agg(catalogid)) FROM ' . $this->dbDriver->targetSchema . '.catalog_feature WHERE featureid=' . $this->dbDriver->targetSchema . '.feature.id GROUP BY featureid) as catalogs';
                    break;
                default:
                    $columns[] = '' . $featureTableName . '.' . $key . ' AS "' . $key . '"';
                    break;
            }
        }

        /*
         * Add liked query if user is set an Social add-on is available
         */
        if (isset($user->profile['id']) && $options['useSocial']) {
            $columns[] = 'EXISTS(SELECT ' . $this->dbDriver->targetSchema . '.likes.featureid FROM ' . $this->dbDriver->targetSchema . '.likes WHERE ' . $this->dbDriver->targetSchema . '.likes.featureid=' . $featureTableName . '.id AND ' . $this->dbDriver->targetSchema . '.likes.userid=' . $user->profile['id'] . ') AS liked';
        }

        /*
         * Add sort idx
         */
        if (!empty($options['sortKey'])) {
            $columns[] = '' . $featureTableName . '.' . $options['sortKey'] . ' AS sort_idx';
        }

        return 'SELECT ' . join(',', $columns) . ' FROM ' . $featureTableName;
    }

    /**
     * Sanitize input requested columns
     *
     * @param array $featureColumns
     */
    private function sanitizeSQLColumns($featureColumns, $fields)
    {
        $discarded = array();
        
        /*
         *  Only one field requested
         *   -  "_simple" returns all properties except catalogs
         *   -  "_all" returns all properties
         */
        if (count($fields) > 0) {
            if ($fields[0] === '_simple') {
                $discarded[] = 'catalogs';
            }
            // Always add mandatories field id, geometry and collection
            elseif ($fields[0] !== '_all') {
                foreach ($fields as $column) {
                    if (!in_array($column, $this->featureColumns)) {
                        $discarded[] = $column;
                    }
                }

                $featureColumns = array_unique(array_merge(array('id','geometry','collection'), $fields));
            }
        }

        return array(
            'discarded' => $discarded,
            'columns' => $featureColumns
        );
    }

    /**
     * Convert keysAndValues array
     */
    private function concatArrays($keys, $values, $glu)
    {
        $concat = array();
        for ($i = 0, $ii = count($keys); $i < $ii; $i++) {
            $concat[] = $keys[$i] . $glu . $values[$i];
        }
        return $concat;
    }

    /**
     * Get a string of iterator for insertion
     *
     * @param integer $size
     * @return array
     */
    private function getCounterList($size)
    {
        $iterator = array();
        for ($i = 1; $i <= $size; $i++) {
            $iterator[] = '$'.$i;
        }
        return $iterator;
    }

    /**
     * Return approximated number
     *
     * @param integer $integer
     */
    private function approximate($integer)
    {
        $precision = pow(10, strlen((string) $integer) - 2);
        return round($integer / $precision) *  $precision;
    }
}
