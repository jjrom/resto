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
     * List of columns from resto.feature table
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
        'keywords',
        'hashtags',
        //'normalized_hashtags',
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
     * @param array $params
     * @param array $sorting
     *      array(
     *          'limit',
     *          'offset'
     *
     * @return array
     * @throws Exception
     */
    public function search($context, $user, $model, $collections, $params, $sorting)
    {
       
        /*
         * Prepare empty links
         */
        $links = array();

        /*
         * Check that mandatory filters are set
         */
        $this->checkMandatoryFilters($model->searchFilters, $params);

        /*
         * Set filters
         */
        $filtersFunctions = new FiltersFunctions($this->dbDriver);
        $filtersAndJoins = $filtersFunctions->prepareFilters($user, $model, $params, $sorting['sortKey']);
        
        /*
         * Special case for liked - return only features liked by owner if set, otherwise by $user
         */
        if (isset($params['resto:liked']) && isset($context->addons['Social'])) {
            $who = $params['resto:owner'] ?? $user->profile['id'];
            if (isset($who)) {
                $filtersAndJoins['filters'][] = array(
                    'value' => 'resto.likes.featureid=resto.feature.id AND resto.likes.userid=' . pg_escape_string($who),
                    'isGeo' => false
                );
                $filtersAndJoins['joins'][] = 'JOIN resto.likes ON resto.feature.id = resto.likes.featureid';
            }
        }

        /*
         * Get sorting - the $sortKey  is used for 'resto:lt' and 'resto:gt' search filters
         */
        $extra = join(' ', array(
            'ORDER BY',
            'resto.feature.' . $sorting['sortKey'],
            $sorting['realOrder'],
            'LIMIT',
            $sorting['limit'],
            $sorting['offset'] > 0 ? ' OFFSET ' . $sorting['offset'] : ''
        ));

        /*
         * Prepare query
         */
        $query = join(' ', array(
            $this->getSelectClause($this->featureColumns, $user, array(
                'fields' => $context->query['fields'] ?? '_default',
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
        $features = (new RestoFeatureUtil($context, $user, $collections))->toFeatureArrayList($this->dbDriver->fetch($this->dbDriver->query($query)));
        
        /*
         * Heatmap add-on
         */
        if (isset($context->addons['Heatmap'])) {
            $whereClauseNoGeo = $filtersFunctions->getWhereClause($filtersAndJoins, array('sort' => false, 'addGeo' => false));
            $heatmapLink = (new Heatmap($context, $user))->getEndPoint($whereClauseNoGeo, $this->getCount('FROM resto.feature ' . $whereClauseNoGeo, $params), null);
            if ( isset($heatmapLink) ) {
                $links[] = $heatmapLink;
            }
        }

        return array(
            'links' => $links,
            'count' => count($features) > 0 ? $this->getCount('FROM resto.feature ' . $filtersFunctions->getWhereClause($filtersAndJoins, array('sort' => false, 'addGeo' => true)), $params) : array(
                'total' => 0,
                'isExact' => true
            ),
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
        $selectClause = $this->getSelectClause($this->featureColumns, $user, array(
            'fields' => $fields,
            'useSocial' => isset($context->addons['Social'])
        ));
        $filterFunctions = new FiltersFunctions($this->dbDriver);
        $filtersAndJoins = $filterFunctions->prepareFilters($user, $model, array(), null);

        // Determine if search on id or productidentifier
        $filtersAndJoins['filters'][] = array(
            'value' => 'resto.feature.id=\'' . pg_escape_string((RestoUtil::isValidUUID($featureId) ? $featureId : RestoUtil::toUUID($featureId))) . '\'',
            'isGeo' => false
        );
        $results = $this->dbDriver->fetch($this->dbDriver->query($selectClause . ' ' . $filterFunctions->getWhereClause($filtersAndJoins, true)));
        return isset($results) && count($results) === 1 ? (new RestoFeatureUtil($context, $user, array($collection->id => $collection)))->toFeatureArray($results[0]) : null;
    }

    /**
     * Check if feature identified by $featureId exists
     *
     * @param string $featureId - feature UUID
     * @return boolean
     * @throws Exception
     */
    public function featureExists($featureId)
    {
        return !empty($this->dbDriver->fetch($this->dbDriver->pQuery('SELECT 1 FROM resto.feature WHERE id=($1)', array(
            $featureId
        ))));
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
        $keysValues = $this->featureArrayToKeysValues(
            $collection,
            $featureArray,
            array(
                'id' => $id,
                'collection' => $collection->id,
                'visibility' => Resto::GROUP_DEFAULT_ID,
                'owner' => isset($collection) && isset($collection->user) ? $collection->user->profile['id'] : null,
                'status' => isset($featureArray['properties']) && isset($featureArray['properties']['status']) && is_int($featureArray['properties']['status']) ? $featureArray['properties']['status'] : 1,
                'likes' => 0,
                'comments' => 0,
                'metadata' => array(),
                'created' => 'now()',
                'created_idx' => 'now()',
                'updated' => isset($featureArray['properties']) && isset($featureArray['properties']['updated']) ? $featureArray['properties']['updated'] : 'now()',
                'geometry' => $featureArray['topologyAnalysis']['geometry'] ?? null,
                'centroid' => $featureArray['topologyAnalysis']['centroid'],
                '_geometry' => $featureArray['topologyAnalysis']['_geometry']
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
            $result = pg_fetch_assoc($this->dbDriver->pQuery('INSERT INTO resto.feature (' . join(',', array_keys($keysValues['keysAndValues'])) . ') VALUES (' . join(',', array_values($keysValues['params'])) . ') RETURNING id, productidentifier', array_values($keysValues['keysAndValues'])), 0);
            
            /*
             * Store feature content
             */
            $this->storeFeatureAdditionalContent($result['id'], $collection->id, $keysValues['modelTables']);

            /*
             * Update collection spatio temporal extent
             */
            (new CollectionsFunctions($this->dbDriver))->updateExtent($collection, $featureArray);
        
            /*
             * Commit everything - rollback if one of the inserts failed
             */
            pg_query($dbh, 'COMMIT');
            
        } catch (Exception $e) {
            pg_query($dbh, 'ROLLBACK');
            RestoLogUtil::httpError(500, 'Feature ' . ($featureArray['productIdentifier'] ?? '') . ' cannot be inserted in database');
        }

        /*
         * Store facets outside of the transaction because error should not block feature ingestion
         */
        $facetsStored = $collection->context->core['storeFacets'];
        if ($facetsStored) {
            try {
                (new FacetsFunctions($this->dbDriver))->storeFacets($keysValues['facets']);
            } catch (Exception $e) {
                $facetsStored = false;
            }   
        }

        return array(
            'id' => $result['id'],
            'productIdentifier' => $result['productidentifier'] ?? null,
            'facetsStored' => $facetsStored
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
        
        /*
         * Remove feature
         */
        try {
            $this->dbDriver->pQuery('DELETE FROM resto.feature WHERE id=$1', array($feature->id));
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot delete feature ' . $feature->id);
        }
        
        /*
         * Remove facets - error is non blocking
         */
        $facetsDeleted = true;
        try {
            (new FacetsFunctions($this->dbDriver))->removeFacetsFromHashtags($featureArray['properties']['hashtags'] ?? array(), $featureArray['collection']);
        } catch (Exception $e) {
            $facetsDeleted = false;
        }

        return array(
            'facetsDeleted' => $facetsDeleted
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

        // Compute new keysValues
        $keysAndValues = $this->featureArrayToKeysValues(
            $collection,
            $newFeatureArray,
            array(
                /*'id' => $oldFeatureArray['id'],
                'productIdentifier' => $oldFeatureArray['properties']['productIdentifier'],
                'collection' => $oldFeatureArray['collection'],
                'visibility' => $oldFeatureArray['properties']['visibility'],
                'owner' => $oldFeatureArray['properties']['owner'],*/
                'status' => isset($newFeatureArray['properties']) && isset($newFeatureArray['properties']['status']) && is_int($newFeatureArray['properties']['status']) ? $newFeatureArray['properties']['status'] : $oldFeatureArray['properties']['status'],
                /*'likes' => $oldFeatureArray['properties']['likes'],
                'comments' => $oldFeatureArray['properties']['comments'],*/
                'metadata' => array(),
                'updated' => isset($newFeatureArray['properties']) && isset($newFeatureArray['properties']['updated']) ? $newFeatureArray['properties']['updated'] : 'now()'
            ),
            array(
                'title',
                'description',
                'startDate',
                'completionDate'
            )
        );

        
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
                'UPDATE resto.feature SET ' . join(',', $toUpdate) . ' WHERE id=$' . (count($toUpdate) + 1),
                array_merge(
                    array_values($keysAndValues['keysAndValues']),
                    array($feature->id)
                )
            );

            /*
             * Update model specific
             */
            for ($i = count($collection->model->tables); $i--;) {
                if ( isset($keysAndValues['modelTables'][$collection->model->tables[$i]['name']]) && ! empty($keysAndValues['modelTables'][$collection->model->tables[$i]['name']])) {
                    $toUpdate = $this->concatArrays(
                        array_keys($keysAndValues['modelTables'][$collection->model->tables[$i]['name']]),
                        $this->getCounterList(count($keysAndValues['modelTables'][$collection->model->tables[$i]['name']])),
                        '='
                    );
                    
                    $this->dbDriver->pQuery(
                        'UPDATE resto.' . $collection->model->tables[$i]['name'] . ' SET ' . join(',', $toUpdate) . ' WHERE id=$' . (count($toUpdate) + 1),
                        array_merge(
                            array_values($keysAndValues['modelTables'][$collection->model->tables[$i]['name']]),
                            array($feature->id)
                        )
                    );
                }
                
            }
            
            /*
             * Commit
             */
            $this->dbDriver->query('COMMIT');

            return RestoLogUtil::success('Update feature ' . $feature->id);
        } catch (Exception $e) {
            $this->dbDriver->query('ROLLBACK');
            RestoLogUtil::httpError(500, 'Cannot update feature ' . $feature->id);
        }

        /*
         * Update facets i.e. remove old facets and add new ones
         * This is non blocking i.e. if error just indicated in the result but feature is updated
         */
        $facetsUpdated = true;
        try {
            $facetsFunctions = new FacetsFunctions($this->dbDriver);
            $facetsFunctions->removeFacetsFromHashtags($oldFeatureArray['properties']['hashtags'] ?? array(), $collection->id);
            if ($feature->context->core['storeFacets']) {
                $facetsFunctions->storeFacets($keysAndValues['facets']);
            }
        } catch (Exception $e) {
            $facetsUpdated = false;
        }
        
        return RestoLogUtil::success('Udpate feature ' . $feature->id, array(
            'facetsUpdated' => $facetsUpdated
        ));

    }

    /**
     * Update feature property
     *
     * @param RestoFeature $feature
     * @param any $status
     * @throws Exception
     */
    public function updateFeatureProperty($feature, $property, $value)
    {
        
        // Special case for description
        if ($property === 'description') {
            return $this->updateFeatureDescription($feature, $value);
        }

        // Check property type validity
        if (in_array($property, array('visibility', 'owner', 'status'))) {
            if (! ctype_digit($value . '')) {
                RestoLogUtil::httpError(400, 'Invalid ' . $property . ' type - should be numeric');
            }
        }
        try {
            $this->dbDriver->pQuery('UPDATE resto.feature SET ' . $property . '=$1 WHERE id=$2', array(
                $value,
                $feature->id
            ));
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot update ' . $property . ' for feature ' . $feature->id);
        }
        
        return RestoLogUtil::success('Property ' . $property . ' updated for feature ' . $feature->id);
    }

    /**
     * Update feature description
     *
     * @param RestoFeature $feature
     * @param string $description
     * @throws Exception
     */
    public function updateFeatureDescription($feature, $description)
    {
        // Get hashtags to remove from feature before update
        $hashtagsToRemove = $this->extractHashtagsFromText($feature->toArray()['properties']['description']);
        $hashtagsToAdd = $this->extractHashtagsFromText($description);
        $hashtags = array_merge(array_diff($feature->toArray()['properties']['hashtags'], $hashtagsToRemove), $hashtagsToAdd);
        
        /*
         * Transaction 
         */
        try {
            
            /*
             * Update description, hashtags and normalized_hashtags
             */
            $this->dbDriver->pQuery('UPDATE resto.feature SET description=$1, hashtags=$2, normalized_hashtags=normalize_array($2) WHERE id=$3', array(
                $description,
                '{' . join(',', $hashtags) . '}',
                $feature->id
            ));

        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot update feature ' . $feature->id);
        }

        /*
         * Update facets i.e. remove old facets and add new ones
         * This is non blocking i.e. if error just indicated in the result but feature is updated
         */
        $facetsUpdated = true;
        try {
            $facetsFunctions = new FacetsFunctions($this->dbDriver);
            $facetsFunctions->removeFacetsFromHashtags($hashtagsToRemove, '*');
            if ($feature->context->core['storeFacets']) {
                $facetsFunctions->storeFacets($hashtagsToAdd);
            }
        } catch (Exception $e) {
            $facetsUpdated = false;
        }

        return RestoLogUtil::success('Property description updated for feature ' . $feature->id, array(
            'facetsUpdated' => $facetsUpdated
        ));

    }

    /**
     * Return exact count or estimate count from query
     *
     * @param String $from
     * @param Boolean $filters
     */
    public function getCount($from, $filters = array())
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
     * Return array of hashtags from a text
     *
     * [WARNING] The leading '#' is not returned
     *
     * Example:
     *
     *    $text = "This is a #test"
     *
     * returns:
     *    
     *    array('test)
     *
     * @param string $text
     * 
     * @return array
     */
    public function extractHashtagsFromText($text)
    {
        $matches = null;
        if (isset($text)) {
            preg_match_all("/#([^ ]+)/u", $text, $matches);
            if ($matches) {
                $hashtagsArray = array_count_values($matches[1]);
                $hashtags = array();
                foreach (array_keys($hashtagsArray) as $key) {
                    $hashtags[] = $key;
                }
                return $hashtags;
            }
        }
        return array();
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

            $columnsAndValues['id'] = $featureId;
            $columnsAndValues['collection'] = $collectionId;
            $this->dbDriver->pQuery('INSERT INTO resto.' . $tableName . ' (' . join(',', array_keys($columnsAndValues)) . ') VALUES (' . join(',', $this->getCounterList(count($columnsAndValues))) . ')', array_values($columnsAndValues));
        
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
            'facets' => null,
            'modelTables' => array()
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
             * Keywords
             */
            elseif ($propertyName === 'keywords' && is_array($propertyValue)) {

                $facetsFunctions = new FacetsFunctions($this->dbDriver);

                // Initialize keywords
                $keysAndValues['keywords'] = json_encode($propertyValue, JSON_UNESCAPED_SLASHES);

                // Compute facets
                $output['facets'] = array_merge($facetsFunctions->getFacetsFromKeywords($propertyValue, $collection->model->facetCategories, $collection->id), $this->extractHashtagsFromText($featureArray['properties']['description'] ?? '', true));
                
                // Compute hashtags
                $hashtags = $facetsFunctions->getHashtagsFromFacets($output['facets']);
                if (count($hashtags) > 0) {
                    $keysAndValues['hashtags'] = '{' . join(',', $hashtags) . '}';
                    $keysAndValues['normalized_hashtags'] = $keysAndValues['hashtags'];
                }
                
                // Special content for LandCoverModel (i.e. itag keys)
                if (count($collection->model->tables) > 0 && $collection->model->tables[0]['name'] == 'feature_landcover') {
                    $output['modelTables']['feature_landcover'] = $this->getITagColumnFromKeywords($propertyValue, $collection->model->tables[0]['columns']);
                }
               
            }
            
            /*
             * Directly add to metadata
             */
            else {
                if (!isset($keysAndValues['metadata'])) {
                    $keysAndValues['metadata'] = array();
                }
                $keysAndValues['metadata'][$propertyName] = $propertyValue;

                // Model specific (do not process LandCoverModel !!)
                for ($i = 0, $ii = count($collection->model->tables); $i < $ii; $i++) {
                    if ($collection->model->tables[$i]['name'] !== 'feature_landcover' && in_array(strtolower($propertyName), $collection->model->tables[$i]['columns'])) {
                        $output['modelTables'][$collection->model->tables[$i]['name']][strtolower($propertyName)] = $propertyValue;
                        break;
                    }
                }
                
            }
        }
       
        // JSON encode metadata
        $keysAndValues['metadata'] = json_encode($keysAndValues['metadata'], JSON_UNESCAPED_SLASHES);
        
        $counter = 0;
        $output['keysAndValues'] = array_merge($protected, $keysAndValues);
        foreach (array_keys($output['keysAndValues'] ?? array()) as $key) {
            if ($key === 'normalized_hashtags') {
                $output['params'][] = 'normalize_array($' . ++$counter . ')';
            }
            else if ($key === 'created_idx' || $key === 'startdate_idx') {
                $output['params'][] = 'public.timestamp_to_id($' . ++$counter . ', 1, nextval(\'resto.table_id_seq\'))';
            }
            else {
                $output['params'][] = '$' . ++$counter;
            }
        }
        
        return $output;
    }

    /**
     * Get database DefaultModel columns from input keywords
     *
     * @param array $keywords
     * @param array $tableColumns
     * @return array
     */
    private function getITagColumnFromKeywords($keywords, $tableColumns)
    {
        $columns = array();
        foreach (array_values($keywords) as $keyword) {
            if (in_array(strtolower($keyword['name']), $tableColumns)) {
                $columns[strtolower($keyword['name'])] = $keyword['value'];
            }
        }
        return $columns;
    }
    
    /**
     * Check that mandatory filters are set
     *
     * @param array $searchFilters
     * @param array $params
     * @return boolean
     */
    private function checkMandatoryFilters($searchFilters, $params)
    {
        $missing = array();
        foreach (array_keys($searchFilters) as $filterName) {
            if (isset($searchFilters[$filterName])) {
                if (isset($searchFilters[$filterName]['minimum']) && $searchFilters[$filterName]['minimum'] === 1 && (!isset($params[$filterName]))) {
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
     * Return resto.feature SELECT clause from input columns
     *
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
    private function getSelectClause($featureColumns, $user, $options)
    {
        $sanitized = $this->sanitizeSQLColumns($featureColumns, array_map('trim', explode(',', $options['fields'])));

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

                // [IMPORTANT] The geometry returned is _geometry not geometry !!!
                case 'geometry':
                    $columns[] = 'ST_AsGeoJSON(resto.feature._geometry, 6) AS geometry';
                    $columns[] = 'Box2D(resto.feature._geometry) AS bbox4326';
                    break;

                case 'centroid':
                    $columns[] = 'ST_AsGeoJSON(resto.feature.centroid, 6) AS centroid';
                    break;

                case 'startDate':
                case 'completionDate':
                case 'created':
                case 'updated':
                    $columns[] = 'to_iso8601(resto.feature.' . $key . ') AS "' . $key . '"';
                    break;

                default:
                    $columns[] = 'resto.feature.' . $key . ' AS "' . $key . '"';
                    break;
            }
            
        }

        /*
         * Add liked query if user is set an Social add-on is available
         */
        if (isset($user->profile['id']) && $options['useSocial']) {
            $columns[] = 'EXISTS(SELECT resto.likes.featureid FROM resto.likes WHERE resto.likes.featureid=resto.feature.id AND resto.likes.userid=' . $user->profile['id'] . ') AS liked';
        }

        /*
         * Add sort idx
         */
        if (!empty($options['sortKey'])) {
            $columns[] = 'resto.feature.' . $options['sortKey'] . ' AS sort_idx';
        }

        return 'SELECT ' . join(',', $columns) . ' FROM resto.feature';

    }

    /**
     * Sanitize input requested resto.feature columns
     * 
     * @param array $featureColumns
     */
    private function sanitizeSQLColumns($featureColumns, $fields) {

        $discarded = array();
        
        /*
         *  Only one field requested
         *   -  "_default" returns all properties except keywords
         *   -  "_all" returns all properties
         */
        if (count($fields) > 0) {
            if ($fields[0] === '_default') {
                $discarded[] = 'keywords';
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
     * @return string
     */
    private function getCounterList($size)
    {
        $iterator = array();
        for ($i = 1; $i <= $size; $i++) {
            $iterator[] = '$'.$i;
        }
        return $iterator;
    }


}
