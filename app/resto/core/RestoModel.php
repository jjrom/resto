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
 * resto model
 */
abstract class RestoModel
{

    /*
     * Model options
     */
    public $options = array();

    /*
     * STAC extensions - override in child models
     * 
     * [STAC 1.0.0] Desactivated due to https://github.com/stac-extensions/stac-extensions.github.io/issues/20
     * [TODO] How to deal with this constraint ?
     */
    public $stacExtensions = array(
        //'https://stac-extensions.github.io/processing/v1.0.0/schema.json',
    );

    /*
     * Mapping applied to input properties
     * It is used to convert input GeoJSON properties BEFORE being inserted in database
     */
    public $inputMapping = array();

    /*
     * STAC mapping is used to convert output property names to STAC properties names
     */
    public $stacMapping = array(

        /*
         * Common metadata
         * [TODO][WARNING] The "published" metadata is part of https://github.com/stac-extensions/timestamps so we should not replace it with "created"
         */
        'published' => array(
            'key' => 'created'
        ),

        /*
         * Processing Extension Specification
         * (https://stac-extensions.github.io/processing/v1.0.0/schema.json)
         */
        'processingLevel' => array(
            'key' => 'processing:level'
        )
    );

    /*
     * Facet hierarchy
     */
    public $facetCategories = array(
        array(
            'collection'
        ),
        array(
            'continent',
            'country',
            'region',
            'state'
        ),
        array(
            'year'
        ),
        array(
            'month'
        ),
        array(
            'day'
        )
    );

    /**
     * OpenSearch search filters
     *
     *  'key' :
     *      *.feature column name
     *  'osKey' :
     *      OpenSearch property name in template urls
     *  'prefix' :
     *      (for "keywords" operation only) Prefix systematically added to input value (i.e. prefix:value)
     *  'operation' :
     *      Type of operation applied to the filter ("in", "keywords", "intersects", "distance", "=", "<=", ">=")
     *
     *
     *  Below properties follow the "Parameter extension" (http://www.opensearch.org/Specifications/OpenSearch/Extensions/Parameter/1.0/Draft_2)
     *
     *  'minimum' :
     *      Minimum number of times this parameter must be included in the search request (default 0)
     *  'maximum' :
     *      Maximum number of times this parameter must be included in the search request (default 1)
     *  'pattern' :
     *      Regular expression against which the parameter's value
     *      Pattern follows Javascript (http://www.ecma-international.org/publications/standards/Ecma-262.htm)
     *  'title' :
     *      Tooltip
     *  'minExclusive'
     *      Minimum value for the element that cannot be reached
     *  'maxExclusive'
     *      Maximum value for the element that cannot be reached
     *  'hidden'
     *      Do not display this search parameter in OpenSearch Description Document
     *  'options'
     *      List of possible values. Two ways
     *      1. Array of predefined value/label
     *          array(
     *              array(
     *                  'value'
     *                  'label'
     *              ),
     *              ...
     *          )
     *      2. 'auto'
     *         In this case will be computed from facets table
     */
    public $searchFilters = array(

        'searchTerms' => array(
            'key' => 'normalized_hashtags',
            'type' => 'array',
            'osKey' => 'q',
            'operation' => 'keywords',
            'title' => 'Free text search'
        ),
        
        'count' => array(
            'osKey' => 'limit',
            'minInclusive' => 1,
            'maxInclusive' => 500,
            'title' => 'The maximum number of results returned per page (default 10)'
        ),
        
        'startIndex' => array(
            'osKey' => 'startIndex',
            'minInclusive' => 1
        ),
        
        'startPage' => array(
            'osKey' => 'page',
            'minInclusive' => 1
        ),
        
        'language' => array(
            'osKey' => 'lang',
            'title' => 'Two letters language code according to ISO 639-1',
            'pattern' => '^[a-z]{2}$'
        ),
        
        'geo:uid' => array(
            'key' => 'id',
            'osKey' => 'ids',
            'operation' => 'in',
            'title' => 'Array of item ids to return. All other filter parameters that further restrict the number of search results (except next and limit) are ignored',
            'pattern' => '^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$'
        ),
        
        'geo:geometry' => array(
            'key' => 'geom',
            'osKey' => 'intersects',
            'operation' => 'intersects',
            'title' => 'Region of Interest defined in GeoJSON or in Well Known Text standard (WKT) with coordinates in decimal degrees (EPSG:4326)'
        ),

        'geo:box' => array(
            'key' => 'geom',
            'osKey' => 'bbox',
            'operation' => 'intersects',
            'title' => 'Region of Interest defined by \'west,south,east,north\' coordinates of longitude, latitude, in decimal degrees (EPSG:4326). Note: Box3D are accepted as input but converted to 2D equivalent',
            'pattern' => '^[-]?[0-9]*\.?[0-9]+,[-]?[0-9]*\.?[0-9]+,[-]?[0-9]*\.?[0-9]+,[-]?[0-9]*\.?[0-9]+$|^[-]?[0-9]*\.?[0-9]+,[-]?[0-9]*\.?[0-9]+,[-]?[0-9]*\.?[0-9]+,[-]?[0-9]*\.?[0-9],[-]?[0-9]*\.?[0-9]+,[-]?[0-9]*\.?[0-9]+$'
            /*'pattern' => '^\[[-]?[0-9]*\.?[0-9]+,[-]?[0-9]*\.?[0-9]+,[-]?[0-9]*\.?[0-9]+,[-]?[0-9]*\.?[0-9]+\]$'*/
        ),
        
        'geo:name' => array(
            'key' => 'geom',
            'osKey' => 'name',
            'operation' => 'distance',
            'title' => 'Location string e.g. Paris, France or toponym identifier (i.e. geouid:xxxx)'
        ),
        
        'geo:lon' => array(
            'key' => 'geom',
            'osKey' => 'lon',
            'operation' => 'distance',
            'title' => 'Longitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lat',
            'minInclusive' => -180,
            'maxInclusive' => 180
        ),
        
        'geo:lat' => array(
            'key' => 'geom',
            'osKey' => 'lat',
            'operation' => 'distance',
            'title' => 'Latitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lon',
            'minInclusive' => -90,
            'maxInclusive' => 90
        ),
        
        'geo:radius' => array(
            'key' => 'geom',
            'osKey' => 'radius',
            'operation' => 'distance',
            'title' => 'Expressed in meters - should be used with geo:lon and geo:lat',
            'minInclusive' => 1
        ),
        
        'dc:date' => array(
            'key' => 'created',
            'osKey' => 'published',
            'title' => 'Metadata product publication date within database - must follow RFC3339 pattern',
            'operation' => '>=',
            'pattern' => '^([0-9]{4})-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])[Tt]([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)([\.,][0-9]+)?(([Zz])|([\+|\-]([01][0-9]|2[0-3]):[0-5][0-9]))$'
            /*'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$'*/
        ),
        
        'resto:collection' => array(
            'key' => 'collection',
            // This is used to have "collections" converted to "collection" in summaries without having a prefix
            'facetKey' => 'collection',
            'osKey' => 'collections',
            'title' => 'Comma separated list of collections name',
            'pattern' => '^[a-zA-Z0-9\-_]+$',
            'operation' => 'in',
            'hidden' => true,
            'options' => 'auto'
        ),

        'resto:model' => array(
            'key' => 'model',
            'osKey' => 'model',
            'title' => 'Model name',
            'pattern' => '^[A-Za-z][a-zA-Z0-9]+$',
            'operation' => '='
        ),
        
        /*
         * Opposite to STAC "next" query parameter does not exist but could be named "prev"
         */
        'resto:gt' => array(
            'osKey' => 'prev',
            'title' => 'Cursor pagination - return result with sort key greater than sort value',
            'pattern' => "^[0-9\-]+$",
            'operation' => '>'
        ),
        
        /*
         * The default sort order is DESCENDING - so the STAC "next" query parameter is equivalent
         * to the "resto:lt" (lower than) filter 
         */
        'resto:lt' => array(
            'osKey' => 'next',
            'title' => 'Cursor pagination - return result with sort key lower than sort value',
            'pattern' => "^[0-9\-]+$",
            'operation' => '<'
        ),
        
        'resto:pid' => array(
            'key' => 'productIdentifier',
            'osKey' => 'pid',
            'operation' => '=',
            'title' => 'Equal on productIdentifier'
        ),
        
        'resto:sort' => array(
            'osKey' => 'sort',
            'pattern' => '^[a-zA-Z\-]*$',
            'title' => 'Sort results by property (startDate or created - Default is startDate). Sorting order is DESCENDING (ASCENDING if property is prefixed by minus sign)'
        ),
        
        'resto:owner' => array(
            'key' => 'owner',
            'osKey' => 'owner',
            'title' => 'Owner of features',
            'operation' => '='
        ),
        
        'resto:status' => array(
            'key' => 'status',
            'osKey' => 'status',
            'title' => 'Feature status',
            'operation' => '=',
            'pattern' => '^[0-9]+$'
        ),

        /*
         * Apply a filter on collections based on the resto.collection keywords column
         * Equivalent to apply a collections filter with a list of collections
         */
        'resto:ckeywords' => array(
            'osKey' => 'ck',
            'title' => 'Stands for "collection keyword" - Limit search to collection containing the input keyword',
            'operation' => '='
        ),

    );

    /*
     * Array of table names to store "model specific" properties for feature
     * Usually only numeric properties are stored (for search) since
     * string property are stored within metadata property of *.feature table
     * and indexed with normalized_hashtags property of the same table
     */
    public $tables = array();

    /*
     * Parameters to apply to database storage for products related to this model
     * 
     *  - tablePrefix : all features belonging to a collection referencing this model will be stored in a dedicated table [tablePrefix]__feature instead of feature"
     *  - storeFacets = if true, facets are stored for model related products
     */
    public $dbParams = array(
        'tablePrefix' => '',
        'storeFacets' => true
    );

    /* 
     * Tag add-on configuration:
     * [IMPORTANT] strategy values:
     *    - "merge" $tagConfig->taggers is merged with Tag add-on default taggers
     *    - "replace" $tagConfig->taggers replace Tag add-on default taggers
     *    - "none" Tag add-on is not used
     */
    public $tagConfig = array(
        'strategy' => 'merge',
        'taggers' => array()
    );
    
    /**
     * Constructor
     * 
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->options = $options;

        if ( isset($this->options['addons']['Social']) ) {
            $this->searchFilters = array_merge($this->searchFilters, SocialAPI::$searchFilters);       
        }

    }

    /**
     * Return the model name (i.e. the name of the Model class)
     */
    public function getName()
    {
        return get_class($this);
    }

    /**
     * Return model inheritance hierarchy stripping out RestoModel
     */
    public function getLineage()
    {
        return array_slice(
            array_merge(
                array_values(array_reverse(class_parents($this))),
                array($this->getName())
            ), 1
        );
    }

    /**
     * Store several features within {collection}.features table following the class model
     *
     * @param RestoCollection $collection
     * @param array $body : HTTP body (MUST BE a GeoJSON "Feature" or "FeatureCollection" in abstract Model)
     * @param array $params
     *
     */
    public function storeFeatures($collection, $body, $params)
    {
        
        // Convert input to resto model
        $data = $this->inputToResto($body, $collection, $params);

        if ( !isset($data) || !in_array($data['type'], array('Feature', 'FeatureCollection')) ) {
            return RestoLogUtil::httpError(400, 'Invalid input type - only "Feature" and "FeatureCollection" are allowed');
        }

        // Extent
        $dates = array();
        $bboxes = array();

        $featuresInserted = array();
        $featuresInError = array();
        
        // Feature case
        if ( $data['type'] === 'Feature' ) {

            $insert = $this->storeFeature($collection, $data, $params);

            if ($insert['result'] !== false) {

                $featuresInserted[] = array(
                    'featureId' => $insert['result']['id'],
                    'productIdentifier' => $insert['result']['productIdentifier'],
                    'facetsStored' => $insert['result']['facetsStored']
                );
            
                $dates[] = isset($insert['featureArray']['properties']) && isset($insert['featureArray']['properties']['startDate']) ? $insert['featureArray']['properties']['startDate'] : null;
                $bboxes[] = isset($insert['featureArray']['topologyAnalysis']) && isset($insert['featureArray']['topologyAnalysis']['bbox']) ? $insert['featureArray']['topologyAnalysis']['bbox'] : null;

            }

        }

        // FeatureCollection case
        else {

            for ($i = 0, $ii = count($data['features']); $i<$ii; $i++)
            {

                try {

                    $insert = $this->storeFeature($collection, $data['features'][$i], $params);
                    if ($insert['result'] !== false) {
                        $featuresInserted[] = array(
                            'featureId' => $insert['result']['id'],
                            'productIdentifier' => $insert['result']['productIdentifier'],
                            'facetsStored' => $insert['result']['facetsStored']
                        );
                        
                        $dates[] = isset($insert['featureArray']['properties']) && isset($insert['featureArray']['properties']['startDate']) ? $insert['featureArray']['properties']['startDate'] : null;
                        $bboxes[] = isset($insert['featureArray']['topologyAnalysis']) && isset($insert['featureArray']['topologyAnalysis']['bbox']) ? $insert['featureArray']['topologyAnalysis']['bbox'] : null;

                    }
                
                }
                catch (Exception $e) {
                    $featuresInError[] = array(
                        'code' => $e->getCode(),
                        'error' => $e->getMessage()
                    );
                    continue;
                }

            }      
        
        }
        
        /*
         * Update collection spatio temporal extent
         */
        (new CollectionsFunctions($collection->context->dbDriver))->updateExtent($collection, array(
            'dates' => $dates,
            'bboxes' => $bboxes
        ));
        
        return array(
            'inserted' => count($featuresInserted),
            'inError' => count($featuresInError),
            'features' => $featuresInserted,
            'errors' => $featuresInError
        );

    }

    /**
     * Update feature within {collection}.features table following the class model
     *
     * @param RestoFeature $feature
     * @param RestoCollection $collection
     * @param array $body
     * @param array $params
     * 
     */
    public function updateFeature($feature, $collection, $body, $params)
    {
        return (new FeaturesFunctions($collection->context->dbDriver))->updateFeature(
            $feature,
            $collection,
            $this->prepareFeatureArray($collection, $this->inputToResto($body, $collection, $params))
        );
    }

    /**
     * Get auto facet fields from model
     */
    public function getAutoFacetFields()
    {
        $facetFields = array();
        foreach (array_values($this->searchFilters) as $filter) {
            if (isset($filter['options']) && $filter['options'] === 'auto') {
                // [IMPORTANT] prefix has preseance over osKey
                $facetFields[] = $filter['prefix'] ?? $filter['facetKey'] ?? $filter['osKey'];
            }
        }
        return $facetFields;
    }

    /**
     * Get resto filters from input query parameters
     * 
     *  - change parameter keys to model parameter key
     *  - remove all HTML tags from input to avoid XSS injection
     *  - check that filter value is valid regarding the model definition
     * 
     * [IMPORTANT]CHANGE] Each unknown filter key that does not start with '_' is converted to hashtag with the following convention : "#<filterName>:value"
     *
     * @param array $query
     */
    public function getFiltersFromQuery($query)
    {
        $params = array();
        $unknowns = array();
        foreach ($query as $key => $value) {
            $filterKey = $this->getFilterName($key);
            if (isset($filterKey)) {
                // Special case geo:geometry also accept GeoJSON => convert it to WKT
                $params[$filterKey] = preg_replace('/<.*?>/', '', $filterKey === 'geo:geometry' ? RestoGeometryUtil::forceWKT($value) : $value);
                $this->validateFilter($filterKey, $params[$filterKey]);
            }
            // Do not process query params starting with '_' or in the reserved list
            else if ( !in_array($key, array('collectionId', 'fields')) && substr($key, 0, 1) !== '_') {
                // Protect against XSS injection
                $unknowns[] = '#' . $this->toHashTag($key, preg_replace('/<.*?>/', '', ltrim($value, '#')));
            }
        }

        // Convert unknowns input to hashtags
        if ( count($unknowns) > 0 ) {
            $params['searchTerms'] = isset($params['searchTerms']) ? $params['searchTerms'] . ' ' . join(' ', $unknowns) : join(' ', $unknowns);
        }

        // [STAC] If "ids" filter is set, then discard every other filters except next and limit
        return isset($params['geo:uid']) ? array(
            'geo:uid' => $params['geo:uid'],
            'resto:lt' => $params['resto:lt'] ?? null,
            'limit' => $params['limit'] ?? null
        ) : $params;
    }

    /**
     * Return OpenSearch filter name from OpenSearch key
     * 
     * @param string $osKey
     */
    public function getFilterName($osKey)
    {
        foreach (array_keys($this->searchFilters) as $filterKey) {
            if ($osKey === $this->searchFilters[$filterKey]['osKey']) {
                return $filterKey;
            }
        }
        return null;
    }

    /**
     * Return OpenSearch filter name from prefix key or input prefix otherwise
     * 
     * @param string $prefix
     */
    public function getOSKeyFromPrefix($prefix)
    {
        foreach (array_keys($this->searchFilters) as $filterKey) {
            if ( isset($this->searchFilters[$filterKey]['prefix']) && $this->searchFilters[$filterKey]['prefix'] === $prefix ) {
                return $this->searchFilters[$filterKey]['osKey'];
            }
        }
        return $prefix;
    }

    /**
     * Check if value is valid for a given filter regarding the model
     *
     * @param string $filterKey
     * @param string $value
     */
    public function validateFilter($filterKey, $value)
    {

        /*
         * Check pattern for string
         */
        if (isset($this->searchFilters[$filterKey]['pattern'])) {
            return $this->validateFilterString($filterKey, $value);
        }
        /*
         * Check pattern for number
         */
        elseif (isset($this->searchFilters[$filterKey]['minInclusive']) || isset($this->searchFilters[$filterKey]['maxInclusive'])) {
            return $this->validateFilterNumber($filterKey, $value);
        }

        return true;
        
    }

    /**
     * Rewrite input $featureArray for output.
     * This function can be superseeded in child Model
     * 
     * @param array $featureArray
     * @param RestoCollection $collection
     * @return array
     */
    public function remap($featureArray, $collection)
    {
        /*
         * These properties are discarded from output
         */
        $discardedProperties = array(
            'id',
            'visibility',
            'owner',
            'sort_idx',
            '_keywords',
            'resto:internal'
        );

        $properties = array();
        
        foreach (array_keys($featureArray['properties']) as $key) {

            // Remove null and non public properties
            if (! isset($featureArray['properties'][$key]) || in_array($key, $discardedProperties)) {
                continue;
            }
            
            // [STAC] Eventually follows STAC mapping for properties names 
            if (isset($this->stacMapping[$key])) {
                $properties[$this->stacMapping[$key]['key']] = $this->convertTo($featureArray['properties'][$key], $this->stacMapping[$key]['convertTo'] ?? null);
            }
            else {
                $properties[$key] = $featureArray['properties'][$key];
            }
        }

        return array_merge($featureArray, array(
            'properties' => $properties
        ));

    }

    /**
     * Remap input properties using inputMapping
     * 
     * @param array $properties
     * @return array
     */
    public function remapInputProperties($properties)
    {

        if ( empty($this->inputMapping) ) {
            return $properties;
        }

        $newProperties = array();

        $rulesKeys = array_keys($this->inputMapping);
        foreach ($properties as $key => $value)
        {
            if ( in_array($key, $rulesKeys) ) {
                if ( $this->inputMapping[$key]['key'] === null ) {
                    continue;
                }
                $newProperties[$this->inputMapping[$key]['key']] = $this->convertTo($value, $this->inputMapping[$key]['convertTo'] ?? null);
            }
            else {
                $newProperties[$key] = $value;
            }
            
        }

        return $newProperties;

    }

    /**
     * Apply type converstion to value
     * 
     * @param integer|float|string|object $value
     * @param string $type
     * @return array|integer|float|string|object
     */
    public function convertTo($value, $type)
    {

        switch ($type) {
            case 'array':
                return array(
                    $value
                );
            default:
                return $value;
        }

    }

    /**
     * Convert input data to resto model
     *
     * @param array $body : any input data
     * @param RestoCollection $collection
     * @param array $params
     *
     */
    protected function inputToResto($body, $collection, $params)
    {   
        if ( isset($body['properties']) ) {
            $body['properties'] = $this->remapInputProperties($body['properties']);
        }
        return $body;
    }

    /**
     * Store individual feature within {collection}.features table following the class model
     *
     * @param RestoCollection $collection
     * @param array $data : array (MUST BE a GeoJSON "Feature" in abstract Model)
     * @param array $params
     *
     */
    private function storeFeature($collection, $data, $params)
    {
        
        /*
         * Input feature cannot have both an id and a productIdentifier
         */
        if (isset($data['id']) && isset($data['properties']['productIdentifier']) && $data['id'] !== $data['properties']['productIdentifier']) {
            return RestoLogUtil::httpError(400, 'Invalid input feature - found both "id" and "properties.productIdentifier"');
        }

        $productIdentifier = $data['id'] ?? $data['properties']['productIdentifier'] ?? null;
        $data['properties']['productIdentifier'] = $productIdentifier;
        $featureId = isset($productIdentifier) ? RestoUtil::toUUID($productIdentifier) : RestoUtil::toUUID(md5(microtime().rand()));

        /*
         * First check if feature is already in database
         * [Note] Feature productIdentifier is UNIQUE
         *  
         * (do this before getKeywords to avoid iTag process)
         */
        if (isset($productIdentifier) && (new FeaturesFunctions($collection->context->dbDriver))->featureExists($featureId, $collection->context->dbDriver->schema . '.' . $collection->model->dbParams['tablePrefix'] . 'feature')) {
            RestoLogUtil::httpError(409, 'Feature ' . $featureId . ' (with productIdentifier=' . $productIdentifier . ') already in database');
        }

        /*
         * Compute featureArray
         */
        $featureArray = $this->prepareFeatureArray($collection, $data, $params);

        /*
         * Insert feature
         */
        return array(
            'featureArray' => $featureArray,
            'result' => (new FeaturesFunctions($collection->context->dbDriver))->storeFeature(
                $featureId,
                $collection,
                $featureArray
            )
        );

    }

    /**
     * Prepare featureArray for store/update
     *
     * @param RestoCollection $collection
     * @param array $data : array (MUST BE GeoJSON in abstract Model)
     * @param array $params : optional options for ingestion
     *
     */
    private function prepareFeatureArray($collection, $data, $params = array())
    {

        /*
         * Assume input file or stream is a JSON Feature
         */
        $checkGeoJSON = RestoGeometryUtil::checkGeoJSONFeature($data);
        if (! $checkGeoJSON['isValid']) {
            RestoLogUtil::httpError(400, $checkGeoJSON['error']);
        }

        /*
         * Clean properties
         */
        $properties = RestoUtil::cleanAssociativeArray($data['properties']);
        
        /*
         * Convert datetime to startDate / completionDate
         */
        if ( isset($properties['datetime']) ) {
            $dates = explode('/', $properties['datetime']);
            if ( isset($dates[0]) ) {
                $properties['startDate'] = $dates[0];
            }
            if ( isset($dates[1]) ) {
                $properties['completionDate'] = $dates[1];
            }
            unset($properties['datetime']);
        }

        /*
         * Add collection to $properties to initialize facet counts on collection
         * [WARNING] if properties['collection'] is already set, it is discarded and replaced by the current collection
         */
        $properties['collection']  = $collection->id;

        /*
         * Check geometry topology integrity
         */
        $topologyAnalysis = (new GeneralFunctions($collection->context->dbDriver))->getTopologyAnalysis($data['geometry'] ?? null, $params);
        if (!$topologyAnalysis['isValid']) {
            RestoLogUtil::httpError(400, $topologyAnalysis['error']);
        }

        /*
         * Return prepared data
         */
        return array(
            'topologyAnalysis' => $topologyAnalysis,
            'properties' => array_merge($properties, array('keywords' => $this->computeKeywords($collection, $data, $properties))),
            'assets' => $data['assets'] ?? null,
            'links' => $data['links'] ?? null
        );

    }

    /**
     * Compute keywords using Tag add-on
     *
     * iTag is triggered by default unless query parameter "_useItag" is set to false or model->itagConfig strategy is set to 'none'
     * 
     * @param RestoCollection $collection
     * @param array $data : array (MUST BE GeoJSON in abstract Model)
     * @param array $properties
     */
    private function computeKeywords($collection, $data, $properties)
    {

        // Skip iTag
        if ( ! isset($collection->context->addons['Tag']) ) {
            return array();
        }

        /*
         * Default : useItag with defaultTaggers
         * Convert array of string to associative array
         */
        $taggers = $this->getITagParams($collection);

        return (new Tag($collection->context, $collection->user))->getKeywords($properties, $data['geometry'] ?? null, $collection->model, $taggers);

    }

    /**
     * Return collection taggers associative array
     * 
     * @param RestoCollection $collection
     * @return array
     */
    private function getITagParams($collection)
    {

        // iTag is not use because model strategy is 'none' or explicitely _useItag is set to false
        if ((isset($collection->context->query['_useItag']) && filter_var($collection->context->query['_useItag'], FILTER_VALIDATE_BOOLEAN) === false) || ($collection->model->tagConfig['strategy'] === 'none')) {
            return null;
        }

        $taggers = array();

        /*
         * Default is to convert array of string to associative array
         */ 
        if ($collection->context->addons['Tag']['options']['iTag']['taggers']) {
            for ($i = 0, $ii = count($collection->context->addons['Tag']['options']['iTag']['taggers']); $i < $ii; $i++) {
                $taggers[$collection->context->addons['Tag']['options']['iTag']['taggers'][$i]] = array();
            }
        }
        
        /*
         * Superseed default per collection (replace or merge)
         */
        if (isset($collection->model->tagConfig['taggers'])) {
            if ($collection->model->tagConfig['strategy'] === 'replace') {
                $taggers = $collection->model->tagConfig['taggers'];
            } elseif ($collection->model->tagConfig['strategy'] === 'merge') {
                $taggers = array_merge($taggers, $collection->model->tagConfig['taggers']);
            }
        }
        
        return array(
            'taggers' => $taggers,
            'planet' => $collection->getPlanet()
        );

    }

    /**
     * Check if value is valid for a given pattern filter regarding the model
     *
     * @param string $filterKey
     * @param string $value
     * @return boolean
     */
    private function validateFilterString($filterKey, $value)
    {
        /*
         * If operation = "in" then value is a comma separated list - check pattern for each element of the list
         */
        if (isset($this->searchFilters[$filterKey]['operation']) && $this->searchFilters[$filterKey]['operation'] === 'in') {
            $elements = array_map('trim', explode(',', $value));
            for ($i = count($elements); $i--;) {
                if (preg_match('\'' . $this->searchFilters[$filterKey]['pattern'] . '\'', $elements[$i]) !== 1) {
                    return RestoLogUtil::httpError(400, 'Comma separated list of "' . $this->searchFilters[$filterKey]['osKey'] . '" must follow the pattern ' . $this->searchFilters[$filterKey]['pattern']);
                }
            }
        }
        else if (preg_match('\'' . $this->searchFilters[$filterKey]['pattern'] . '\'', $value) !== 1) {
            return RestoLogUtil::httpError(400, 'Value for "' . $this->searchFilters[$filterKey]['osKey'] . '" must follow the pattern ' . $this->searchFilters[$filterKey]['pattern']);
        }

        return true;

    }

    /**
     * Check if value is valid for a given number filter regarding the model
     *
     * @param string $filterKey
     * @param string $value
     * @return boolean
     */
    private function validateFilterNumber($filterKey, $value)
    {
        if (!is_numeric($value)) {
            RestoLogUtil::httpError(400, 'Value for "' . $this->searchFilters[$filterKey]['osKey'] . '" must be numeric');
        }
        if (isset($this->searchFilters[$filterKey]['minInclusive']) && $value < $this->searchFilters[$filterKey]['minInclusive']) {
            RestoLogUtil::httpError(400, 'Value for "' . $this->searchFilters[$filterKey]['osKey'] . '" must be greater than ' . ($this->searchFilters[$filterKey]['minInclusive'] - 1));
        }
        if (isset($this->searchFilters[$filterKey]['maxInclusive']) && $value > $this->searchFilters[$filterKey]['maxInclusive']) {
            RestoLogUtil::httpError(400, 'Value for "' . $this->searchFilters[$filterKey]['osKey'] . '" must be lower than ' . ($this->searchFilters[$filterKey]['maxInclusive'] + 1));
        }
        return true;
    }

    /**
     * Convert input value to hashtag with the following convention : "#<filterName>:value"
     * 
     * @param string $key
     * @param string $value
     * @return string
     */
    private function toHashTag($filterName, $value)
    {   

        // Special case for ',' (AND) and '|' (OR)
        $splitter = '';
        if (strpos($value, ',') !== false) {
            $splitter = ',';
            $exploded = explode(',', $value);
        }
        else if (strpos($value, ',') !== false) {
            $splitter = '|';
            $exploded = explode('|', $value);
        }
        else {
            $exploded = array($value);
        }
        
        for ($i = 0, $ii = count($exploded); $i < $ii; $i++) {
            $exploded[$i] = $filterName . Resto::TAG_SEPARATOR . $exploded[$i];
        }
        
        return join($splitter, $exploded);
    }

}
