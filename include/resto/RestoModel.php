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
 * RESTo Model
 */
abstract class RestoModel {
    
    /*
     * Model name is mandatory and based on the name
     * of the class
     */
    public $name;
    
    /*
     * Mapping between RESTo model property keys (i.e. array keys - left column)
     * and RESTo database column names (i.e. array values - right column)
     */
    public $properties = array(
        'identifier' => array(
            'name' => 'identifier',
            'type' => 'TEXT',
            'constraint' => 'UNIQUE'
        ),
        'collection' => array(
            'name' => 'collection',
            'type' => 'TEXT'
        ),
        'productIdentifier' => array(
            'name' => 'productidentifier',
            'type' => 'TEXT'
        ),
        'parentIdentifier' => array(
            'name' => 'parentIdentifier',
            'type' => 'TEXT'
        ),
        'title' => array(
            'name' => 'title',
            'type' => 'TEXT'
        ),
        'description' => array(
            'name' => 'description',
            'type' => 'TEXT'
        ),
        'organisationName' => array(
            'name' => 'authority',
            'type' => 'TEXT'
        ),
        'startDate' => array(
            'name' => 'startdate',
            'type' => 'TIMESTAMP'
        ),
        'completionDate' => array(
            'name' => 'completiondate',
            'type' => 'TIMESTAMP'
        ),
        'productType' => array(
            'name' => 'producttype',
            'type' => 'TEXT'
        ),
        'processingLevel' => array(
            'name' => 'processinglevel',
            'type' => 'TEXT'
        ),
        'platform' => array(
            'name' => 'platform',
            'type' => 'TEXT'
        ),
        'instrument' => array(
            'name' => 'instrument',
            'type' => 'TEXT'
        ),
        'resolution' => array(
            'name' => 'resolution',
            'type' => 'NUMERIC'
        ),
        'sensorMode' => array(
            'name' => 'sensormode',
            'type' => 'TEXT'
        ),
        'orbitNumber' => array(
            'name' => 'orbitnumber',
            'type' => 'NUMERIC'
        ),
        'quicklook' => array(
            'name' => 'quicklook',
            'type' => 'TEXT'
        ),
        'thumbnail' => array(
            'name' => 'thumbnail',
            'type' => 'TEXT'
        ),
        'metadata' => array(
            'name' => 'metadata',
            'type' => 'TEXT'
        ),
        'metadataMimeType' => array(
            'name' => 'metadata_mimetype',
            'type' => 'TEXT'
        ),
        'resource' => array(
            'name' => 'resource',
            'type' => 'TEXT'
        ),
        'resourceMimeType' => array(
            'name' => 'resource_mimetype',
            'type' => 'TEXT'
        ),
        'resourceSize' => array(
            'name' => 'resource_size',
            'type' => 'INTEGER'
        ),
        'resourceChecksum' => array(
            'name' => 'resource_checksum',
            'type' => 'TEXT'
        ),
        'wms' => array(
            'name' => 'wms',
            'type' => 'TEXT'
        ),
        'updated' => array(
            'name' => 'updated',
            'type' => 'TIMESTAMP'
        ),
        'published' => array(
            'name' => 'published',
            'type' => 'TIMESTAMP'
        ),
        'cultivatedCover' => array(
            'name' => 'lu_cultivated',
            'type' => 'NUMERIC',
            'constraint' => 'DEFAULT 0',
            'notDisplayed' => true
        ),
        'desertCover' => array(
            'name' => 'lu_desert',
            'type' => 'NUMERIC',
            'contraint' => 'DEFAULT 0',
            'notDisplayed' => true
        ),
        'floodedCover' => array(
            'name' => 'lu_flooded',
            'type' => 'NUMERIC',
            'contraint' => 'DEFAULT 0',
            'notDisplayed' => true
        ),
        'forestCover' => array(
            'name' => 'lu_forest',
            'type' => 'NUMERIC',
            'constraint' => 'DEFAULT 0',
            'notDisplayed' => true
        ),
        'herbaceousCover' => array(
            'name' => 'lu_herbaceous',
            'type' => 'NUMERIC',
            'constraint' => 'DEFAULT 0',
            'notDisplayed' => true
        ),
        'iceCover' => array(
            'name' => 'lu_ice',
            'type' => 'NUMERIC',
            'constraint' => 'DEFAULT 0',
            'notDisplayed' => true
        ),
        'urbanCover' => array(
            'name' => 'lu_urban',
            'type' => 'NUMERIC',
            'constraint' => 'DEFAULT 0',
            'notDisplayed' => true
        ),
        'waterCover' => array(
            'name' => 'lu_water',
            'type' => 'NUMERIC',
            'constraint' => 'DEFAULT 0',
            'notDisplayed' => true
        ),
        'snowCover' => array(
            'name' => 'snowcover',
            'type' => 'NUMERIC'
        ),
        'cloudCover' => array(
            'name' => 'cloudcover',
            'type' => 'NUMERIC'
        ),
        'keywords' => array(
            'name' => 'keywords',
            'type' => 'TEXT'
        ),
        'geometry' => array(
            'name' => 'geometry',
            'type' => 'GEOMETRY'
        ),
        'hashes' => array(
            'name' => 'hashes',
            'type' => 'TEXT[]',
            'notDisplayed' => true
        ),
        'visible' => array(
            'name' => 'visible',
            'type' => 'INTEGER',
            'notDisplayed' => true
        )
    );
    
    /*
     * OpenSearch search filters
     * 
     *  'key' : 
     *      RESTo model property name
     *  'osKey' : 
     *      OpenSearch property name in template urls
     *  'operation' : 
     *      Search operation (keywords, intersects, distance, =, <=, >=)
     * 
     * 
     *  Below properties follow the "Paramater extension" (http://www.opensearch.org/Specifications/OpenSearch/Extensions/Parameter/1.0/Draft_2)
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
            'key' => 'hashes',
            'osKey' => 'q',
            'operation' => 'keywords',
            'title' => 'Free text search'
        ),
        'count' => array(
            'osKey' => 'maxRecords',
            'minInclusive' => 1,
            'maxInclusive' => 500,
            'title' => 'Number of results returned per page (default 50)'
        ),
        'startIndex' => array(
            'osKey' => 'index',
            'minInclusive' => 1
        ),
        'startPage' => array(
            'osKey' => 'page',
            'minInclusive' => 1
        ),
        'language' => array(
            'osKey' => 'lang',
            'pattern' => '^[a-z]$',
            'title' => 'Two letters language code according to ISO 639-1'
        ),
        'geo:uid' => array(
            'key' => 'identifier',
            'osKey' => 'identifier',
            'operation' => '=',
            'title' => 'Valid UUID according to RFC 4122',
            'pattern' => '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$'
        ),
        'geo:geometry' => array(
            'key' => 'geometry',
            'osKey' => 'geometry',
            'operation' => 'intersects',
            'title' => 'Defined in Well Known Text standard (WKT) with coordinates in decimal degrees (EPSG:4326)'
        ),
        'geo:box' => array(
            'key' => 'geometry',
            'osKey' => 'box',
            'operation' => 'intersects',
            'title' => 'Defined by \'west, south, east, north\' coordinates of longitude, latitude, in decimal degrees (EPSG:4326)'
        ),
        'geo:name' => array(
            'key' => 'geometry',
            'osKey' => 'location',
            'operation' => 'distance',
            'title' => 'Location string e.g. Paris, France'
        ),
        'geo:lon' => array(
            'key' => 'geometry',
            'osKey' => 'lon',
            'operation' => 'distance',
            'title' => 'Longitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lat'
        ),
        'geo:lat' => array(
            'key' => 'geometry',
            'osKey' => 'lat',
            'operation' => 'distance',
            'title' => 'Latitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lon'
        ),
        'geo:radius' => array(
            'key' => 'geometry',
            'osKey' => 'radius',
            'operation' => 'distance',
            'title' => 'Expressed in meters - should be used with geo:lon and geo:lat'
        ),
        'time:start' => array(
            'key' => 'startDate',
            'osKey' => 'startDate',
            'operation' => '>=',
            'title' => 'Beginning of the time slice of the search query. Format should follow RFC-3339',
            'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(Z|[\+\-][0-9]{2}:[0-9]{2})$' 
        ),
        'time:end' => array(
            'key' => 'startDate',
            'osKey' => 'completionDate',
            'operation' => '<=',
            'title' => 'End of the time slice of the search query. Format should follow RFC-3339',
            'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(Z|[\+\-][0-9]{2}:[0-9]{2})$' 
        ),
        'eo:parentIdentifier' => array(
            'key' => 'parentIdentifier',
            'osKey' => 'parentIdentifier',
            'operation' => '='
        ),
        'eo:productType' => array(
            'key' => 'productType',
            'osKey' => 'productType',
            'operation' => '=',
            'options' => 'auto'
        ),
        'eo:processingLevel' => array(
            'key' => 'processingLevel',
            'osKey' => 'processingLevel',
            'operation' => '=',
            'options' => 'auto'
        ),
        'eo:platform' => array(
            'key' => 'platform',
            'osKey' => 'platform',
            'operation' => '=',
            'keyword' => array(
                'value' => '{:platform:}',
                'type' => 'platform'
            ),
            'options' => 'auto'
        ),
        'eo:instrument' => array(
            'key' => 'instrument',
            'osKey' => 'instrument',
            'operation' => '=',
            'keyword' => array(
                'value' => '{:instrument:}',
                'type' => 'instrument'
            ),
            'options' => 'auto'
        ),
        'eo:resolution' => array(
            'key' => 'resolution',
            'osKey' => 'resolution',
            'operation' => 'interval',
            'title' => 'Spatial resolution expressed in meters',
            'pattern' => '^(?:[1-9]\d*|0)?(?:\.\d+)?$',
            'quantity' => array(
                'value' => 'resolution',
                'unit' => 'm'
            )
        ),
        'eo:organisationName' => array(
            'key' => 'organisationName',
            'osKey' => 'organisationName',
            'operation' => '='
        ),
        'eo:orbitNumber' => array(
            'key' => 'orbitNumber',
            'osKey' => 'orbitNumber',
            'operation' => 'interval',
            'minInclusive' => 1,
            'quantity' => array(
                'value' => 'orbit'
            )
        ),
        'eo:sensorMode' => array(
            'key' => 'sensorMode',
            'osKey' => 'sensorMode',
            'operation' => '=',
            'options' => 'auto'
        ),
        'eo:cloudCover' => array(
            'key' => 'cloudCover',
            'osKey' => 'cloudCover',
            'operation' => 'interval',
            'title' => 'Cloud cover expressed in percent',
            'quantity' => array(
                'value' => 'cloud',
                'unit' => '%'
            )
        ),
        'eo:snowCover' => array(
            'key' => 'snowCover',
            'osKey' => 'snowCover',
            'operation' => 'interval',
            'title' => 'Snow cover expressed in percent',
            'quantity' => array(
                'value' => 'snow',
                'unit' => '%'
            )
        ),
        'resto:cultivatedCover' => array(
            'key' => 'cultivatedCover',
            'osKey' => 'cultivatedCover',
            'operation' => 'interval',
            'title' => 'Cultivated area expressed in percent',
            'quantity' => array(
                'value' => 'cultivated',
                'unit' => '%'
            )
        ),
        'resto:desertCover' => array(
            'key' => 'desertCover',
            'osKey' => 'desertCover',
            'operation' => 'interval',
            'title' => 'Desert area expressed in percent',
            'quantity' => array(
                'value' => 'desert',
                'unit' => '%'
            )
        ),
        'resto:floodedCover' => array(
            'key' => 'floodedCover',
            'osKey' => 'floodedCover',
            'operation' => 'interval',
            'title' => 'Flooded area expressed in percent',
            'quantity' => array(
                'value' => 'flooded',
                'unit' => '%'
            )
        ),
        'resto:forestCover' => array(
            'key' => 'forestCover',
            'osKey' => 'forestCover',
            'operation' => 'interval',
            'title' => 'Forest area expressed in percent',
            'quantity' => array(
                'value' => 'forest',
                'unit' => '%'
            )
        ),
        'resto:herbaceousCover' => array(
            'key' => 'herbaceousCover',
            'osKey' => 'herbaceousCover',
            'operation' => 'interval',
            'title' => 'Herbaceous area expressed in percent',
            'quantity' => array(
                'value' => 'herbaceous',
                'unit' => '%'
            )
        ),
        'resto:iceCover' => array(
            'key' => 'iceCover',
            'osKey' => 'iceCover',
            'operation' => 'interval',
            'title' => 'Ice area expressed in percent',
            'quantity' => array(
                'value' => 'ice',
                'unit' => '%'
            )
        ),
        'resto:urbanCover' => array(
            'key' => 'urbanCover',
            'osKey' => 'urbanCover',
            'operation' => 'interval',
            'title' => 'Urban area expressed in percent',
            'quantity' => array(
                'value' => 'urban',
                'unit' => '%'
            )
        ),
        'resto:waterCover' => array(
            'key' => 'waterCover',
            'osKey' => 'waterCover',
            'operation' => 'interval',
            'title' => 'Water area expressed in percent',
            'quantity' => array(
                'value' => 'water',
                'unit' => '%'
            )
        ),
        'dc:date' => array(
            'key' => 'updated',
            'osKey' => 'updated',
            'operation' => '>=',
            'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(Z|[\+\-][0-9]{2}:[0-9]{2})$'
        )
    );

    public $extendedProperties = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->name = get_class($this);
        $this->properties = array_merge($this->properties, $this->extendedProperties);
    }
   
    /**
     * Return property database column type
     * 
     * @param type $modelKey
     */
    public function getDbType($modelKey) {
        
        if (!isset($this->properties[$modelKey])) {
            return null;
        }
        
        switch(strtoupper($this->properties[$modelKey]['type'])) {
            case 'INTEGER':
                return 'integer';
            case 'NUMERIC':
                return 'float';
            case 'TIMESTAMP':
                return 'date';
            case 'GEOMETRY':
                return 'geometry';
            case 'TEXT[]':
                return 'array';    
            default:
                return 'string';
        }
        
    }
    
    /**
     * Return property database column name
     * 
     * @param string $modelKey : RESTo model key
     * @return array
     */
    public function getDbKey($modelKey) {
        if (!isset($modelKey) || !isset($this->properties[$modelKey]) || !is_array($this->properties[$modelKey])) {
            return null;
        }
        return $this->properties[$modelKey]['name'];
    }
    
    /**
     * Remap properties array accordingly to $inputMapping array
     * 
     *  $inputMapping array structure:
     *      
     *          array(
     *              'propertyNameInInputFile' => 'restoPropertyName' or array('restoPropertyName1', 'restoPropertyName2)
     *          )
     * 
     * @param Array $properties
     */
    public function mapInputProperties($properties) {
        if (property_exists($this, 'inputMapping')) {
            foreach ($this->inputMapping as $key => $arr) {
                if (isset($properties[$key])) {
                    if (!is_array($arr)) {
                        $arr = Array($arr);
                    }
                    for ($i = count($arr); $i--;) {
                        $properties[$arr[$i]] = $properties[$key];
                    }
                    unset($properties[$key]);
                }
            }
        }
        /*
         * Remove unknown properties (i.e. properties not in model)
         */
        foreach (array_keys($properties) as $key) {
            if (!isset($this->properties[$key])) {
                unset($properties[$key]);
            }
        }
        return $properties;
    }
    
    /**
     * Store feature within {collection}.features table following the class model
     * 
     * @param array $data : array (MUST BE GeoJSON in abstract Model)
     * @param RestoCollection $collection
     * 
     */
    public function storeFeature($data, $collection) {
        
        /*
         * Assume input file or stream is a JSON Feature
         */
        if (!RestoGeometryUtil::isValidGeoJSONFeature($data)) {
            RestoLogUtil::httpError(500, 'Invalid feature description');
        }
        
        /*
         * Remap properties between RESTo model and input
         * GeoJSON Feature file 
         */
        $properties = $this->mapInputProperties($data['properties']);
        
        /*
         * Compute unique identifier
         */
        if (!isset($data['id']) || !RestoUtil::isValidUUID($data['id'])) {
            $featureIdentifier = $collection->toFeatureId((isset($properties['productIdentifier']) ? $properties['productIdentifier'] : md5(microtime().rand())));
        }
        else {
            $featureIdentifier = $data['id'];
        }
        
        /*
         * Store feature
         */
        $collection->context->dbDriver->store(RestoDatabaseDriver::FEATURE, array(
            'collection' => $collection,
            'featureArray' => array(
                'type' => 'Feature',
                'id' => $featureIdentifier,
                'geometry' => $data['geometry'],
                'properties' => array_merge($properties, array('keywords' => $this->getKeywords($properties, $data['geometry'], $collection)))
            )
        ));
        
        return new RestoFeature($collection->context, $collection->user, array(
            'featureIdentifier' => $featureIdentifier
        ));
        
    }
    
    /**
     * Get facet fields from model
     */
    public function getFacetFields() {
        $facetFields = array('collection', 'continent');
        foreach (array_values($this->searchFilters) as $filter) {
            if (isset($filter['options']) && $filter['options'] === 'auto') {
                $facetFields[] = $filter['key'];
            }
        }
        return $facetFields;
    }
    
    /**
     * Compute keywords from properties array
     * 
     * @param array $properties
     * @param array $geometry (GeoJSON)
     * @param RestoCollection $collection
     */
    private function getKeywords($properties, $geometry, $collection) {
        
        /*
         * Keywords utilities
         */
        $keywordsUtil = new RestoKeywordsUtil();
        
        /*
         * Initialize keywords array
         */
        $keywords = isset($properties['keywords']) ? $properties['keywords'] : array();
        
        /*
         * Validate keywords
         */
        if (!$keywordsUtil->areValids($keywords)) {
            RestoLogUtil::httpError(500, 'Invalid keywords property elements');
        }
        
        return array_merge($keywords, $keywordsUtil->computeKeywords($properties, $geometry, $collection));
       
    }
    
}
