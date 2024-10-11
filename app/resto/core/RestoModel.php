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
     * Auto cataloging hierarchy
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
     *  'stacKey' :
     *      Search filter name equivalent in STAC
     *      [IMPORTANT] If not set then it is assumed that stacKey is the same as osKey
     *  'prefix' :
     *      (Optional) (for "keywords" operation only) Prefix systematically added to input value (i.e. prefix:value)
     *  'pathPrefix' :
     *      (Optional) (for "keywords" operation only) Path prefix for LTREE search in catalog_feature table. If not set will be *.)
     *  'operation' :
     *      Type of operation applied to the filter ("in", "keywords", "intersects", "distance", "=", "<=", ">=")
     *  'queryable' :
     *      (Optional) The name of the underlying STAC/OAFeature property that is queryable (i.e. that will be displayed in the /queryables endpoint)
     *  '$ref' :
     *      (Optional) Displayed in relation with queryable property (see https://github.com/radiantearth/stac-api-spec/tree/master/fragments/filter#queryables)
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
     *         In this case will be computed from catalog table
     */
    public $searchFilters = array(

        'filter' => array(
            'osKey' => 'filter',
            'operation' => 'cql2',
            'title' => 'Filter expression expressed in Common Query Lanbguage (CQL2)',
        ),

        'filter:lang' => array(
            'osKey' => 'filter-lang',
            'operation' => '=',
            'title' => 'The language of the filter expression. Only *cql2-text* is accepted',
            'pattern' => 'cql2-text'
        ),

        'filter:crs' => array(
            'osKey' => 'filter-crs',
            'operation' => '=',
            'title' => 'Coordinate reference system of geometries expressed in filter. Only *http://www.opengis.net/def/crs/OGC/1.3/CRS84* is accepted',
            'pattern' => 'http://www.opengis.net/def/crs/OGC/1.3/CRS84'
        ),

        'searchTerms' => array(
            'osKey' => 'q',
            'operation' => 'keywords',
            'title' => 'Free text search'
        ),

        'description' => array(
            'key' => 'description',
            'osKey' => 'description',
            'operation' => 'description',
            'title' => 'Free text search on feature description field'
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
            'stacKey' => 'id',
            'operation' => 'in',
            'title' => 'Array of item ids to return. All other filter parameters that further restrict the number of search results (except next and limit) are ignored',
            //'pattern' => '^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$',
            'queryable' => 'id',
            '$ref' => 'https://schemas.stacspec.org/v1.0.0/item-spec/json-schema/item.json#/id'
        ),

        'geo:geometry' => array(
            'key' => 'geom',
            'osKey' => 'intersects',
            'stacKey' => 'geometry',
            'operation' => 'intersects',
            'title' => 'Region of Interest defined in GeoJSON or in Well Known Text standard (WKT) with coordinates in decimal degrees (EPSG:4326)',
            'queryable' => 'geometry',
            '$ref' => 'https://schemas.stacspec.org/v1.0.0/item-spec/json-schema/item.json#/geometry'
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
            'pattern' => '^([0-9]{4})-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])[Tt]([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\.[0-9]+)?(([Zz])|([\+|\-]([01][0-9]|2[0-3]):[0-5][0-9]))$'
            /*'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$'*/
        ),

        'resto:collection' => array(
            'key' => 'collection',
            // This is used to have "collections" converted to "collection" in summaries without having a prefix
            'facetKey' => 'collection',
            'osKey' => 'collections',
            'stacKey' => 'collection',
            'title' => 'Comma separated list of collections name',
            /*'pattern' => '^[a-zA-Z0-9\-_\.\:]+$',*/
            'operation' => 'in',
            'hidden' => true,
            'options' => 'auto',
            'queryable' => 'collection',
            '$ref' => 'https://schemas.stacspec.org/v1.0.0/item-spec/json-schema/item.json#/collection'
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

        /*
         * The default sort order is DESCENDING - so the STAC "next" query parameter is equivalent
         */
        'resto:sort' => array(
            'osKey' => 'sortby',
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
         * Apply a filter on collections based on the DATABASE_TARGET_SCHEMA.collection keywords column
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
     * and indexed within the catalog_feature table
     */
    public $tables = array();

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

        if (isset($this->options['addons']['Social'])) {
            $this->searchFilters = array_merge($this->searchFilters, Social::$searchFilters);
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
            ),
            1
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

        if (!isset($data) || !in_array($data['type'], array('Feature', 'FeatureCollection'))) {
            return RestoLogUtil::httpError(400, 'Invalid input type - only "Feature" and "FeatureCollection" are allowed');
        }

        // Extent
        $dates = array();
        $bboxes = array();

        $featuresInserted = array();
        $featuresInError = array();

        // Feature case
        if ($data['type'] === 'Feature') {
            $insert = $this->storeFeature($collection, $data, $params);
            if ( isset($insert['result']) ) {
                $featuresInserted[] = array(
                    'featureId' => $insert['result']['id'],
                    'productIdentifier' => $insert['result']['productIdentifier']
                );

                $dates[] = isset($insert['featureArray']['properties']) && isset($insert['featureArray']['properties']['startDate']) ? $insert['featureArray']['properties']['startDate'] : null;
                $bboxes[] = isset($insert['featureArray']['topologyAnalysis']) && isset($insert['featureArray']['topologyAnalysis']['bbox']) ? $insert['featureArray']['topologyAnalysis']['bbox'] : null;
            }
        }

        // FeatureCollection case
        else {
            for ($i = 0, $ii = count($data['features']); $i<$ii; $i++) {
                try {
                    $insert = $this->storeFeature($collection, $data['features'][$i], $params);
                    if ($insert['result'] !== false) {
                        $featuresInserted[] = array(
                            'featureId' => $insert['result']['id'],
                            'productIdentifier' => $insert['result']['productIdentifier']
                        );

                        $dates[] = isset($insert['featureArray']['properties']) && isset($insert['featureArray']['properties']['startDate']) ? $insert['featureArray']['properties']['startDate'] : null;
                        $bboxes[] = isset($insert['featureArray']['topologyAnalysis']) && isset($insert['featureArray']['topologyAnalysis']['bbox']) ? $insert['featureArray']['topologyAnalysis']['bbox'] : null;
                    }
                } catch (Exception $e) {
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
            $this->prepareFeatureArray($collection, $this->inputToResto($body, $collection, $params), $params)
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
     *  - change input query keys to model parameter key including STAC conversion (i.e. input STAC query to resto model - e.g. processing:level => processingLevel)
     *  - check that filter value is valid regarding the model definition
     *
     * [IMPORTANT] Each unknown filter key that does not start with '_' is converted to catalog path
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
                $params[$filterKey] = $filterKey === 'geo:geometry' ? RestoGeometryUtil::forceWKT($value) : $value;
                $this->validateFilter($filterKey, $params[$filterKey]);
            }
            // Do not process query params starting with '_' or in the reserved list
            elseif (!in_array($key, array('collectionId', 'fields')) && substr($key, 0, 1) !== '_') {
                $unknowns[] = $this->toCatalogPath($key, $value);
            }
        }

        // Convert unknowns input to searchTerms
        if (count($unknowns) > 0) {
            $params['searchTerms'] = isset($params['searchTerms']) ? $params['searchTerms'] . ' ' . join(' ', $unknowns) : join(' ', $unknowns);
        }
        
        return $params;
    }

    /**
     * Return resto internal property (i.e. OpenSearch based) from a STAC property
     *
     * @param string $stacProperty
     */
    public function getRestoPropertyFromSTAC($stacProperty)
    {
        foreach (array_keys($this->stacMapping) as $restoProperty) {
            if ($this->stacMapping[$restoProperty]['key'] === $stacProperty) {
                return $restoProperty;
            }
        }

        return $stacProperty;
    }

    /**
     * Return OpenSearch filter name from OpenSearch or STAC key
     *
     * @param string $osOrSTACKey
     */
    public function getFilterName($osOrSTACKey)
    {
        foreach (array_keys($this->searchFilters) as $filterKey) {
            if ($osOrSTACKey === $this->searchFilters[$filterKey]['osKey'] || (isset($this->searchFilters[$filterKey]['stacKey']) && $osOrSTACKey === $this->searchFilters[$filterKey]['stacKey'])) {
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
            if (isset($this->searchFilters[$filterKey]['prefix']) && $this->searchFilters[$filterKey]['prefix'] === $prefix) {
                return $this->searchFilters[$filterKey]['osKey'];
            }
        }
        return $prefix;
    }

    /**
     * Return STAC/OAFeatures queryables
     */
    public function getQueryables()
    {
        $queryables = array();
        foreach (array_keys($this->searchFilters) as $filterKey) {
            if (isset($this->searchFilters[$filterKey]['queryable'])) {
                $queryable = array(
                    'description' => $this->searchFilters[$filterKey]['title']
                );
                if (isset($this->searchFilters[$filterKey]['$ref'])) {
                    $queryable['$ref'] = $this->searchFilters[$filterKey]['$ref'];
                }
                $queryables[$this->searchFilters[$filterKey]['queryable']] = $queryable;
            }
        }

        return $queryables;
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
            //'owner',
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
            } else {
                $properties[$key] = $featureArray['properties'][$key];
            }
        }

        $featureArray = array_merge($featureArray, array(
            'properties' => $properties
        ));

        /*
         * JSON-LD additionnal metadata
         */
        if ( isset($collection) && $collection->context->core['useJSONLD']) {
            $featureArray = JSONLDUtil::addDatasetsMetadata($featureArray);
        }

        return $featureArray;
    }

    /**
     * Remap input properties using inputMapping
     *
     * @param array $properties
     * @return array
     */
    public function remapInputProperties($properties)
    {
        if (empty($this->inputMapping)) {
            return $properties;
        }

        $newProperties = array();

        $rulesKeys = array_keys($this->inputMapping);
        foreach ($properties as $key => $value) {
            if (in_array($key, $rulesKeys)) {
                if ($this->inputMapping[$key]['key'] === null) {
                    continue;
                }
                $newProperties[$this->inputMapping[$key]['key']] = $this->convertTo($value, $this->inputMapping[$key]['convertTo'] ?? null);
            } else {
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
     * Convert array of filter names to array of OpenSearch keys
     *
     * @param array $filterNames
     * @param boolean $processSearchTerms
     * @return array
     */
    public function toOSKeys($filterNames, $processSearchTerms = false)
    {
        $arr = array();
        foreach ($filterNames as $key => $obj) {
            if (isset($this->searchFilters[$key])) {
                // Special case => explode searchTerms string
                if ($key === 'searchTerms' && $processSearchTerms) {
                    $arr = array_merge($arr, $this->explodeSearchTerms($obj));
                    continue;
                }

                // Convert to STAC
                $osKey = $this->searchFilters[$key]['osKey'];
                $arr[isset($this->stacMapping[$osKey]) ? $this->stacMapping[$osKey]['key']: $osKey] = $obj;
            }
        }
        return $arr;
    }

    /**
     * Convert input value to catalog path with the following convention : put key to plural (suffix with s) dot value
     * 
     * For instance, continent=Southamerica will be converted to #continents.southamerica          
     * @param string $key
     * @param string $value
     * @return string
     */
    public function toCatalogPath($filterName, $value)
    {
        // Special case for ',' (AND) and '|' (OR)
        $splitter = '';
        if (strpos($value, ',') !== false) {
            $splitter = ',';
            $exploded = explode(',', $value);
        } elseif (strpos($value, ',') !== false) {
            $splitter = '|';
            $exploded = explode('|', $value);
        } else {
            $exploded = array($value);
        }

        for ($i = 0, $ii = count($exploded); $i < $ii; $i++) {
            $exploded[$i] = $filterName . 's.' . $exploded[$i];
        }

        return join($splitter, $exploded);
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
        if (isset($body['properties'])) {
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

        /*
         * [WARNING] New in resto 7.x - if input id / productIdentifier is already a valid UUID use it directly
         * Correct issue #342 to be STAC compatible
         */
        $featureId = isset($productIdentifier) ? (RestoUtil::isValidUUID($productIdentifier) ? $productIdentifier : RestoUtil::toUUID($productIdentifier)) : RestoUtil::toUUID(md5(microtime().rand()));

        /*
         * First check if feature is already in database
         * [Note] Feature productIdentifier is UNIQUE
         *
         * (do this before getKeywords to avoid iTag process)
         */
        if (isset($productIdentifier) && (new FeaturesFunctions($collection->context->dbDriver))->featureExists($featureId, $collection->context->dbDriver->targetSchema . '.feature')) {
            return RestoLogUtil::httpError(409, 'Feature ' . $featureId . ' (with productIdentifier=' . $productIdentifier . ') already in database');
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
        if (isset($properties['datetime'])) {
            $dates = explode('/', $properties['datetime']);
            if (isset($dates[0])) {
                $properties['startDate'] = $dates[0];
            }
            if (isset($dates[1])) {
                $properties['completionDate'] = $dates[1];
            }
            unset($properties['datetime']);
        }
        if (isset($properties['start_datetime'])) {
            $properties['startDate'] = $properties['start_datetime'];
            unset($properties['start_datetime']);
        }
        if (isset($properties['end_datetime'])) {
            $properties['completionDate'] = $properties['end_datetime'];
            unset($properties['end_datetime']);
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

        // Compute catalogs associated with this feature
        // Eventually remove input _catalogs from properties
        $catalogs = (new Cataloger($collection->context, $collection->user))->getCatalogs($properties, $data['geometry'] ?? null, $collection, $this->getITagParams($collection));
        if (isset($properties['resto:catalogs'])) {
            unset($properties['resto:catalogs']);
        }
        
        /*
         * Return prepared data
         */
        return array(
            'topologyAnalysis' => $topologyAnalysis,
            'properties' => $properties,
            'assets' => $data['assets'] ?? null,
            'links' => $data['links'] ?? null,
            'catalogs' => $catalogs
        );
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
        if ($collection->context->addons['Cataloger']['options']['iTag']['taggers']) {
            for ($i = 0, $ii = count($collection->context->addons['Cataloger']['options']['iTag']['taggers']); $i < $ii; $i++) {
                $taggers[$collection->context->addons['Cataloger']['options']['iTag']['taggers'][$i]] = array();
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
        } elseif (preg_match('\'' . $this->searchFilters[$filterKey]['pattern'] . '\'', $value) !== 1) {
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
        // [STAC] Special case for count - accept value even if higher than maxInclusive
        if ($filterKey !== 'count' && isset($this->searchFilters[$filterKey]['maxInclusive']) && $value > $this->searchFilters[$filterKey]['maxInclusive']) {
            RestoLogUtil::httpError(400, 'Value for "' . $this->searchFilters[$filterKey]['osKey'] . '" must be lower than ' . ($this->searchFilters[$filterKey]['maxInclusive'] + 1));
        }
        return true;
    }

    /**
     * Explode a searchTerms string (e.g. "location::coastal year::2003 instrument::PHR,NIR thisisanormalahashtag")
     * into an array of filters (i.e. {"location":"coastal","year":2003,"instrument":"PHR,NIR","q":"thisisnormalhashtagh"})
     *
     * @param array $obj
     */
    private function explodeSearchTerms($obj)
    {
        $searchFilters = [];
        $output = [];
        
        /*
         * Process each searchTerm
         */
        if (is_string($obj)) {
            $obj = array(
                'value' => $obj,
                'operation' => '='
            );
        }
        $searchTerms = RestoUtil::splitString($obj['value']);
        for ($i = 0, $l = count($searchTerms); $i < $l; $i++) {
            $splitted = explode(RestoConstants::CONCEPT_SEPARATOR, $searchTerms[$i]);

            // This is a regular searchTerm
            if (count($splitted) === 1) {
                $searchFilters[] = $searchTerms[$i];
                continue;
            }

            // Concatenate splitted into prefix and value
            $key = array_shift($splitted);
            $value = join(RestoConstants::CONCEPT_SEPARATOR, $splitted);

            /*
             * Start with - (equivalent to "NOT ")
             */
            $osKey = $this->getOSKeyFromPrefix(substr($key, 0, 1) === '-' ? ltrim($key, '-') : $key);
            $output[isset($this->stacMapping[$osKey]) ? $this->stacMapping[$osKey]['key']: $osKey] = array(
                'value' => $value,
                'operation' =>  $obj['operation']
            );
        }

        if (count($searchFilters) > 0) {
            $output['searchTerms'] = array(
                'value' => join(' ', $searchFilters),
                'operation' =>  $obj['operation']
            );
        }

        return $output;
    }

}
