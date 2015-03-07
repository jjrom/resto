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
            'type' => 'NUMERIC(8,2)'
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
            'constraint' => 'DEFAULT 0'
        ),
        'desertCover' => array(
            'name' => 'lu_desert',
            'type' => 'NUMERIC',
            'contraint' => 'DEFAULT 0'
        ),
        'floodedCover' => array(
            'name' => 'lu_flooded',
            'type' => 'NUMERIC',
            'contraint' => 'DEFAULT 0'
        ),
        'forestCover' => array(
            'name' => 'lu_forest',
            'type' => 'NUMERIC',
            'constraint' => 'DEFAULT 0'
        ),
        'herbaceousCover' => array(
            'name' => 'lu_herbaceous',
            'type' => 'NUMERIC',
            'constraint' => 'DEFAULT 0'
        ),
        'iceCover' => array(
            'name' => 'lu_ice',
            'type' => 'NUMERIC',
            'constraint' => 'DEFAULT 0'
        ),
        'urbanCover' => array(
            'name' => 'lu_urban',
            'type' => 'NUMERIC',
            'constraint' => 'DEFAULT 0'
        ),
        'waterCover' => array(
            'name' => 'lu_water',
            'type' => 'NUMERIC',
            'constraint' => 'DEFAULT 0'
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
            'type' => 'hstore',
            'constraint' => 'DEFAULT \'\''
        ),
        'geometry' => array(
            'name' => 'geometry',
            'type' => 'GEOMETRY'
        ),
        'hashes' => array(
            'name' => 'hashes',
            'type' => 'TEXT[]'
        ),
        'visible' => array(
            'name' => 'visible',
            'type' => 'BOOLEAN'
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
            'key' => 'completionDate',
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
            'osKey' => 'product',
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
            'operation' => '=',
            'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(Z|[\+\-][0-9]{2}:[0-9]{2})$'
        )
    );

    public $extendedProperties = array();
    
    /**
     * Constructor
     * 
     * @param RestoContext $context : Resto context
     * @param RestoUser $user : Resto user
     */
    public function __construct($context, $user) {
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            RestoUtil::httpError(500, 'Context must be defined');
        }
        $this->context = $context;
        $this->user = $user;
        $this->name = get_class($this);
        $this->properties = array_merge($this->properties, $this->extendedProperties); 
        
    }
   
    /**
     * Return property database column type
     * 
     * @param type $modelKey
     */
    public function getDbType($modelKey) {
        if (!isset($modelKey) || !isset($this->properties[$modelKey]) || !is_array($this->properties[$modelKey])) {
            return null;
        }
        $sqlType = trim(strtoupper($this->properties[$modelKey]['type']));
        if (substr($sqlType, 0, 6) === 'TEXT[]') {
            return 'array';
        }
        switch(substr($sqlType, 0, 3)) {
            case 'INT':
                return 'integer';
            case 'FLO':
            case 'NUM':
                return 'float';
            case 'TIM':
            case 'DAT':
                return 'date';
            case 'GEO':
                return 'geometry';
            case 'HST':
                return 'hstore';
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
     *  $inputMapping array strucuture:
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
     * Add feature to the {collection}.features table following the class model
     * 
     * @param array $data : array (MUST BE GeoJSON in abstract Model)
     * @param string $collectionName : collection name
     */
    public function addFeature($data, $collectionName) {
         
        if (!isset($collectionName)) {
            RestoLogUtil::httpError(500, 'Collection name must be defined');
        }
        
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
         * Feature 'id' must be a valid UUID
         * Otherwise it is replaced by a generated UUID based on productIdentifier and collection
         */
        if (!isset($data['id']) || !RestoUtil::isValidUUID($data['id'])) {
            $data['id'] = RestoUtil::UUIDv5($collectionName . ':' . (isset($properties['productIdentifier']) ? strtoupper($properties['productIdentifier']) : md5(microtime().rand())));
        }
        
        /*
         * Check that resource does not already exist in database
         */
        if ($this->context->dbDriver->check(RestoDatabaseDriver::FEATURE, array('featureIdentifier' => $data['id']))) {
            RestoLogUtil::httpError(500, 'Feature ' . $data['id'] . ' already in database');
        }
        
        /*
         * Initialize feature description with id and geometry
         */
        $elements = array(
            array('identifier', $data['id']),
            array('geometry', $data['geometry'])
        );
        
        /*
         * Process all properties
         */
        $keywords = null;
        foreach ($properties as $key => $value) {
            if ($key === 'keywords') {
                $keywords = array('keywords', $value);
            }
            else {
                $elements[] = array($key, $value);
            }
        }
        
        /* 
         * Add tags with iTag
         */
        if (isset($this->context->modules['iTag'])) {
            $iTagKeywords = $this->getKeywords($data['geometry']);
            if (isset($keywords)) {
                $keywords[1] = array_merge($keywords[1], $iTagKeywords);
            }
            else {
                $keywords = array('keywords', $iTagKeywords);
            }
        }
        
        if (isset($keywords)) {
            $elements = array_merge($elements, array($keywords));
        }
        
        try {
            $this->context->dbDriver->store(RestoDatabaseDriver::FEATURE, array(
                'collectionName' => $collectionName,
                'elements' => $elements,
                'model' => $this
            ));
        } catch (Exception $e) {
            RestoUtil::httpError(500, 'Feature ' . $data['id'] . ' cannot be inserted in database');
        }
        
        return new RestoFeature($data['id'], $this->context, $this->user);
        
    }
    
    /**
     * Extract iTag keywords from input geometry
     * 
     * @param string $geometry
     * @return array
     */
    private function getKeywords($geometry) {
        
        if (isset($this->context->modules['iTag']['database']) && isset($this->context->modules['iTag']['database']['dbname'])) {
            $iTag = new iTag($this->context->modules['iTag']['database']);
        }
        else {
            $iTag = new iTag(array('dbh' => $this->context->dbDriver->dbh));
        }
        return $this->iTagToKeywords($iTag->tag(RestoGeometryUtil::geoJSONGeometryToWKT($geometry, isset($this->context->modules['iTag']['keywords']) ? $this->context->modules['iTag']['keywords'] : array())));

    }
    
    /**
     * Return a RESTo keywords array from an iTag Hierarchical feature
     * 
     *      $keywords = array(
     *          array(
     *              array(
     *                  "name" => name
     *                  "id" => id, // type:value
     *                  "parentId" => id, // parentType:parentValue
     *                  "value" => value or array()
     *              ),
     *              array(
     *                  ...
     *              )
     *          )
     *      );
     * 
     * @param array $iTagFeature
     */
    private function iTagToKeywords($iTagFeature) {

        $keywords = array();

        if (!isset($iTagFeature) || !isset($iTagFeature['properties'])) {
            return $keywords;
        }

        $properties = $iTagFeature['properties'];

        if (isset($properties['political'])) {
            if (isset($properties['political']['continents'])) {

                // Continents
                for ($i = 0, $li = count($properties['political']['continents']); $i < $li; $i++) {
                    $continent = $properties['political']['continents'][$i];
                    $continentHash = RestoUtil::getHash($continent['id']);
                    $keywords[$continentHash] = array(
                        'id' => $continent['id'],
                        'hash' => $continentHash
                    );
                    // Countries
                    for ($j = 0, $lj = count($continent['countries']); $j < $lj; $j++) {
                        $country = $continent['countries'][$j];
                        $countryHash = RestoUtil::getHash($country['id'], $continentHash);
                        $keywords[$countryHash] = array(
                            'id' => $country['id'],
                            'parentId' => $continent['id'],
                            'hash' => $countryHash,
                            'parentHash' => $continentHash,
                            'value' => $country['pcover']
                        );

                        // Regions
                        if (isset($country['regions'])) {
                            for ($k = 0, $lk = count($country['regions']); $k < $lk; $k++) {
                                $region = $country['regions'][$k];
                                if (!isset($region['id'])) {
                                    $region['id'] = 'region:_all';
                                }
                                $regionHash = RestoUtil::getHash($region['id'], $countryHash);
                                $keywords[$regionHash] = array(
                                    'id' => $region['id'],
                                    'parentId' => $country['id'],
                                    'hash' => $regionHash,
                                    'parentHash' => $countryHash,
                                );

                                // States
                                for ($l = 0, $ll = count($region['states']); $l < $ll; $l++) {
                                    $state = $region['states'][$l];
                                    if (!isset($state['id'])) {
                                        $state['id'] = 'state:_unknown';
                                    }
                                    $stateHash = RestoUtil::getHash($state['id'], $regionHash);
                                    $keywords[$stateHash] = array(
                                        'id' => $state['id'],
                                        'parentId' => $region['id'],
                                        'hash' => $stateHash,
                                        'parentHash' => $regionHash,
                                        'value' => $state['pcover']
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        if (isset($properties['landCover'])) {
            if (isset($properties['landCover']['landUse'])) {
                foreach (array_values($properties['landCover']['landUse']) as $landuse) {
                    $id = 'landuse:' . strtolower($landuse['name']);
                    $hash = RestoUtil::getHash($id);
                    $keywords[$hash] = array(
                        'id' => $id,
                        'hash' => $hash,
                        'value' => $landuse['pcover']
                    );
                }
            }
            if (isset($properties['landCover']['landUseDetails'])) {
                foreach (array_values($properties['landCover']['landUseDetails']) as $landuse) {
                    $id = 'landuse_details:' . strtolower($landuse['name']);
                    $parentId = 'landuse:' . strtolower($landuse['parent']);
                    $parentHash = RestoUtil::getHash($parentId);
                    $hash = RestoUtil::getHash($id, $parentHash);
                    $keywords[$hash] = array(
                        'id' => $id,
                        'parentId' => $parentId,
                        'hash' => $hash,
                        'parentHash' => $parentHash,
                        'value' => $landuse['pcover']
                    );
                }
            }
        }

        return array_values($keywords);
    }
}
