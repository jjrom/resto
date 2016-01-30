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
        'visibility' => array(
            'name' => 'visibility',
            'type' => 'TEXT'
        ),
        'licenseId' => array(
            'name' => 'licenseid',
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
            'type' => 'NUMERIC'
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
        'centroid' => array(
            'name' => 'centroid',
            'type' => 'POINT'
        ),
        'hashes' => array(
            'name' => 'hashes',
            'type' => 'TEXT[]',
            'notDisplayed' => true
        )
    );
    
    /**
     * OpenSearch search filters
     * 
     *  'key' : 
     *      RESTo model property name
     *  'osKey' : 
     *      OpenSearch property name in template urls
     *  'operation' : 
     *      Search operation (keywords, intersects, distance, =, <=, >=)
     *  'htmlFilter' : 
     *      If set to true then this filter is added to the text/html OpenSearch <Url>
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
        /**
         *  @SWG\Parameter(
         *      name="q",
         *      in="query",
         *      description="Free text search - OpenSearch {searchTerms}",
         *      required=false,
         *      type="string"
         *  )
         */
        'searchTerms' => array(
            'key' => 'hashes',
            'osKey' => 'q',
            'operation' => 'keywords',
            'title' => 'Free text search',
            'htmlFilter' => true
        ),
        /**
         *  @SWG\Parameter(
         *      name="maxRecords",
         *      in="query",
         *      description="Number of results returned per page - OpenSearch {count}",
         *      required=false,
         *      type="integer",
         *      minimum=1,
         *      maximum=500,
         *      default=50
         *  )
         */
        'count' => array(
            'osKey' => 'maxRecords',
            'minInclusive' => 1,
            'maxInclusive' => 500,
            'title' => 'Number of results returned per page (default 50)'
        ),
        /**
         *  @SWG\Parameter(
         *      name="index",
         *      in="query",
         *      description="First result to provide - OpenSearch {startIndex}",
         *      required=false,
         *      type="integer",
         *      minimum=1,
         *      default=1
         *  )
         */
        'startIndex' => array(
            'osKey' => 'index',
            'minInclusive' => 1
        ),
        /**
         *  @SWG\Parameter(
         *      name="page",
         *      in="query",
         *      description="First page to provide - OpenSearch {startPage}",
         *      required=false,
         *      type="integer",
         *      minimum=1,
         *      default=1
         *  )
         */
        'startPage' => array(
            'osKey' => 'page',
            'minInclusive' => 1
        ),
        /**
         *  @SWG\Parameter(
         *      name="lang",
         *      in="query",
         *      description="Two letters language code according to ISO 639-1 - OpenSearch {language}",
         *      required=false,
         *      type="string",
         *      pattern="^[a-z]{2}$",
         *      default="en"
         *  )
         */
        'language' => array(
            'osKey' => 'lang',
            'pattern' => '^[a-z]{2}$',
            'title' => 'Two letters language code according to ISO 639-1'
        ),
        /**
         *  @SWG\Parameter(
         *      name="identifier",
         *      in="query",
         *      description="Either resto identifier or productIdentifier - OpenSearch {geo:uid}",
         *      required=false,
         *      type="string"
         *  )
         */
        'geo:uid' => array(
            'key' => 'identifier',
            'osKey' => 'identifier',
            'operation' => '=',
            'title' => 'Either resto identifier or productIdentifier'
        ),
        /**
         *  @SWG\Parameter(
         *      name="geometry",
         *      in="query",
         *      description="Region of Interest defined in Well Known Text standard (WKT) with coordinates in decimal degrees (EPSG:4326) - OpenSearch {geo:geometry}",
         *      required=false,
         *      type="string"
         *  )
         */
        'geo:geometry' => array(
            'key' => 'geometry',
            'osKey' => 'geometry',
            'operation' => 'intersects',
            'title' => 'Region of Interest defined in Well Known Text standard (WKT) with coordinates in decimal degrees (EPSG:4326)'
        ),
        /**
         *  @SWG\Parameter(
         *      name="box",
         *      in="query",
         *      description="Region of Interest defined by 'west, south, east, north' coordinates of longitude, latitude, in decimal degrees (EPSG:4326) - OpenSearch {geo:box}",
         *      required=false,
         *      type="string"
         *  )
         */
        'geo:box' => array(
            'key' => 'geometry',
            'osKey' => 'box',
            'operation' => 'intersects',
            'title' => 'Region of Interest defined by \'west, south, east, north\' coordinates of longitude, latitude, in decimal degrees (EPSG:4326)'
        ),
        /**
         *  @SWG\Parameter(
         *      name="name",
         *      in="query",
         *      description="Location string e.g. Paris, France - OpenSearch {geo:name}",
         *      required=false,
         *      type="string"
         *  )
         */
        'geo:name' => array(
            'key' => 'geometry',
            'osKey' => 'name',
            'operation' => 'distance',
            'title' => 'Location string e.g. Paris, France'
        ),
        /**
         *  @SWG\Parameter(
         *      name="lon",
         *      in="query",
         *      description="Longitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lat - OpenSearch {geo:lon}",
         *      required=false,
         *      minimum=-180,
         *      maximum=180,
         *      type="string"
         *  )
         */
        'geo:lon' => array(
            'key' => 'geometry',
            'osKey' => 'lon',
            'operation' => 'distance',
            'title' => 'Longitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lat',
            'minInclusive' => -180,
            'maxInclusive' => 180
        ),
        /**
         *  @SWG\Parameter(
         *      name="lat",
         *      in="query",
         *      description="Latitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lon - OpenSearch {geo:lat}",
         *      required=false,
         *      minimum=-90,
         *      maximum=90,
         *      type="string"
         *  )
         */
        'geo:lat' => array(
            'key' => 'geometry',
            'osKey' => 'lat',
            'operation' => 'distance',
            'title' => 'Latitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lon',
            'minInclusive' => -90,
            'maxInclusive' => 90
        ),
        /**
         *  @SWG\Parameter(
         *      name="radius",
         *      in="query",
         *      description="Expressed in meters - should be used with geo:lon and geo:lat - OpenSearch {geo:radius}",
         *      required=false,
         *      minimum=1,
         *      type="string"
         *  )
         */
        'geo:radius' => array(
            'key' => 'geometry',
            'osKey' => 'radius',
            'operation' => 'distance',
            'title' => 'Expressed in meters - should be used with geo:lon and geo:lat',
            'minInclusive' => 1
        ),
        /**
         *  @SWG\Parameter(
         *      name="startDate",
         *      in="query",
         *      description="Beginning of the time slice of the search query. Format should follow RFC-3339 - OpenSearch {time:start}",
         *      type="string",
         *      pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$"
         *  )
         */
        'time:start' => array(
            'key' => 'startDate',
            'osKey' => 'startDate',
            'operation' => '>=',
            'title' => 'Beginning of the time slice of the search query. Format should follow RFC-3339',
            'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$'
        ),
        /**
         *  @SWG\Parameter(
         *      name="completionDate",
         *      in="query",
         *      description="End of the time slice of the search query. Format should follow RFC-3339 - OpenSearch {time:end}",
         *      type="string",
         *      pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$"
         *  )
         */
        'time:end' => array(
            'key' => 'startDate',
            'osKey' => 'completionDate',
            'operation' => '<=',
            'title' => 'End of the time slice of the search query. Format should follow RFC-3339',
            'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$'
        ),
        /**
         *  @SWG\Parameter(
         *      name="parentIdentifier",
         *      in="query",
         *      description="OpenSearch {eo:parentIdentifier}",
         *      type="string"
         *  )
         */
        'eo:parentIdentifier' => array(
            'key' => 'parentIdentifier',
            'osKey' => 'parentIdentifier',
            'operation' => '='
        ),
        /**
         * @SWG\Parameter(
         *      name="productType",
         *      in="query",
         *      description="OpenSearch {eo:productType}",
         *      type="string",
         *      enum={}
         *  ) 
         */
        'eo:productType' => array(
            'key' => 'productType',
            'osKey' => 'productType',
            'operation' => '=',
            'options' => 'auto'
        ),
        /**
         * @SWG\Parameter(
         *      name="processingLevel",
         *      in="query",
         *      description="OpenSearch {eo:processingLevel}",
         *      type="string",
         *      enum={}
         *  ) 
         */
        'eo:processingLevel' => array(
            'key' => 'processingLevel',
            'osKey' => 'processingLevel',
            'operation' => '=',
            'options' => 'auto'
        ),
        /**
         * @SWG\Parameter(
         *      name="platform",
         *      in="query",
         *      description="OpenSearch {eo:platform}",
         *      type="string",
         *      enum={}
         *  ) 
         */
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
        /**
         * @SWG\Parameter(
         *      name="instrument",
         *      in="query",
         *      description="OpenSearch {eo:instrument}",
         *      type="string",
         *      enum={}
         *  )
         */
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
        /**
         *  @SWG\Parameter(
         *      name="resolution",
         *      in="query",
         *      description="Spatial resolution expressed in meters - OpenSearch {eo:resolution}",
         *      type="string",
         *      pattern="'^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$'"
         *  )
         */
        'eo:resolution' => array(
            'key' => 'resolution',
            'osKey' => 'resolution',
            'operation' => 'interval',
            'title' => 'Spatial resolution expressed in meters',
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'resolution',
                'unit' => 'm'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="organisationName",
         *      in="query",
         *      description="OpenSearch {eo:organisationName}",
         *      type="string"
         *  )
         */
        'eo:organisationName' => array(
            'key' => 'organisationName',
            'osKey' => 'organisationName',
            'operation' => '='
        ),
        /**
         *  @SWG\Parameter(
         *      name="orbitNumber",
         *      in="query",
         *      description="OpenSearch {eo:orbitNumber}",
         *      type="integer",
         *      minimum=1
         *  )
         */
        'eo:orbitNumber' => array(
            'key' => 'orbitNumber',
            'osKey' => 'orbitNumber',
            'operation' => 'interval',
            'minInclusive' => 1,
            'quantity' => array(
                'value' => 'orbit'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="sensorMode",
         *      in="query",
         *      description="OpenSearch {eo:sensorMode}",
         *      type="string"
         *  )
         */
        'eo:sensorMode' => array(
            'key' => 'sensorMode',
            'osKey' => 'sensorMode',
            'operation' => '=',
            'options' => 'auto'
        ),
        /**
         *  @SWG\Parameter(
         *      name="cloudCover",
         *      in="query",
         *      description="Cloud cover expressed in percent - OpenSearch {resto:cloudCover}",
         *      type="string",
         *      pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
         *  )
         */
        'eo:cloudCover' => array(
            'key' => 'cloudCover',
            'osKey' => 'cloudCover',
            'operation' => 'interval',
            'title' => 'Cloud cover expressed in percent',
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'cloud',
                'unit' => '%'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="snowCover",
         *      in="query",
         *      description="Snow cover expressed in percent - OpenSearch {resto:snowCover}",
         *      type="string",
         *      pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
         *  )
         */
        'eo:snowCover' => array(
            'key' => 'snowCover',
            'osKey' => 'snowCover',
            'operation' => 'interval',
            'title' => 'Snow cover expressed in percent',
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'snow',
                'unit' => '%'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="cultivatedCover",
         *      in="query",
         *      description="Cultivated area expressed in percent - OpenSearch {resto:cultivatedCover}",
         *      type="string",
         *      pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
         *  )
         */
        'resto:cultivatedCover' => array(
            'key' => 'cultivatedCover',
            'osKey' => 'cultivatedCover',
            'operation' => 'interval',
            'title' => 'Cultivated area expressed in percent',
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'cultivated',
                'unit' => '%'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="desertCover",
         *      in="query",
         *      description="Desert area expressed in percent - OpenSearch {resto:desertCover}",
         *      type="string",
         *      pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
         *  )
         */
        'resto:desertCover' => array(
            'key' => 'desertCover',
            'osKey' => 'desertCover',
            'operation' => 'interval',
            'title' => 'Desert area expressed in percent',
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'desert',
                'unit' => '%'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="floodedCover",
         *      in="query",
         *      description="Flooded area expressed in percent - OpenSearch {resto:floodedCover}",
         *      type="string",
         *      pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
         *  )
         */
        'resto:floodedCover' => array(
            'key' => 'floodedCover',
            'osKey' => 'floodedCover',
            'operation' => 'interval',
            'title' => 'Flooded area expressed in percent',
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'flooded',
                'unit' => '%'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="forestCover",
         *      in="query",
         *      description="Forest area expressed in percent - OpenSearch {resto:forestCover}",
         *      type="string",
         *      pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
         *  )
         */
        'resto:forestCover' => array(
            'key' => 'forestCover',
            'osKey' => 'forestCover',
            'operation' => 'interval',
            'title' => 'Forest area expressed in percent',
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'forest',
                'unit' => '%'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="herbaceousCover",
         *      in="query",
         *      description="Herbaceous area expressed in percent - OpenSearch {resto:herbaceousCover}",
         *      type="string",
         *      pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
         *  )
         */
        'resto:herbaceousCover' => array(
            'key' => 'herbaceousCover',
            'osKey' => 'herbaceousCover',
            'operation' => 'interval',
            'title' => 'Herbaceous area expressed in percent',
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'herbaceous',
                'unit' => '%'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="iceCover",
         *      in="query",
         *      description="Ice area expressed in percent - OpenSearch {resto:iceCover}",
         *      type="string",
         *      pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
         *  )
         */
        'resto:iceCover' => array(
            'key' => 'iceCover',
            'osKey' => 'iceCover',
            'operation' => 'interval',
            'title' => 'Ice area expressed in percent',
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'ice',
                'unit' => '%'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="urbanCover",
         *      in="query",
         *      description="Urban area expressed in percent - OpenSearch {resto:urbanCover}",
         *      type="string",
         *      pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
         *  )
         */
        'resto:urbanCover' => array(
            'key' => 'urbanCover',
            'osKey' => 'urbanCover',
            'operation' => 'interval',
            'title' => 'Urban area expressed in percent',
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'urban',
                'unit' => '%'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="waterCover",
         *      in="query",
         *      description="Water area expressed in percent - OpenSearch {resto:waterCover}",
         *      type="string",
         *      pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
         *  )
         */
        'resto:waterCover' => array(
            'key' => 'waterCover',
            'osKey' => 'waterCover',
            'operation' => 'interval',
            'title' => 'Water area expressed in percent',
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'water',
                'unit' => '%'
            )
        ),
        /**
         *  @SWG\Parameter(
         *      name="updated",
         *      in="query",
         *      description="Last update of the product within database - OpenSearch {dc:date}",
         *      type="string",
         *      pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$"
         *  )
         */
        'dc:date' => array(
            'key' => 'updated',
            'osKey' => 'updated',
            'title' => 'Last update of the product within database',
            'operation' => '>=',
            'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$'
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
            case 'POINT':
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
     * @param Array $geojson
     */
    public function mapInputProperties($geojson) {
        if (property_exists($this, 'inputMapping')) {
            foreach ($this->inputMapping as $key => $arr) {
                
                /*
                 * key can be a path i.e. key1.key2.key3 
                 */
                $childs = explode('.', $key);
                $property = isset($geojson[$childs[0]]) ? $geojson[$childs[0]] : null;
                if ($property) {
                    
                    for ($i = 1, $ii = count($childs); $i < $ii; $i++) {
                        if (isset($property[$childs[$i]])) {
                            $property = $property[$childs[$i]];
                        }
                    }

                    if (isset($property)) {
                        if (!is_array($arr)) {
                            $arr = Array($arr);
                        }
                        for ($i = count($arr); $i--;) {
                            $geojson['properties'][$arr[$i]] = $property;
                        }
                    }
                }
            }
        }
        /*
         * Remove unknown properties (i.e. properties not in model)
         */
        foreach (array_keys($geojson['properties']) as $key) {
            if (!isset($this->properties[$key])) {
                unset($geojson['properties'][$key]);
            }
        }
        return $geojson['properties'];
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
        $properties = $this->mapInputProperties($data);
        
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
         * First check if feature is already in database
         * (do this before getKeywords to avoid iTag process)
         */
        if ($collection->context->dbDriver->check(RestoDatabaseDriver::FEATURE, array('featureIdentifier' => $featureIdentifier))) {
            RestoLogUtil::httpError(500, 'Feature ' . $featureIdentifier . ' already in database');
        }
        
        /*
         * Tagger module
         */
        $keywords = array();
        if (isset($collection->context->modules['Tagger'])) {
            $tagger = RestoUtil::instantiate($collection->context->modules['Tagger']['className'], array($collection->context, $collection->user));
            $keywords = $tagger->getKeywords($properties, $data['geometry']);
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
                'properties' => array_merge($properties, array('keywords' => $keywords))
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
     * Get resto filters from input query parameters
     *  - change parameter keys to model parameter key
     *  - remove unset parameters
     *  - remove all HTML tags from input to avoid XSS injection
     *  - check that filter value is valid regarding the model definition
     * 
     * @param array $query
     */
    public function getFiltersFromQuery($query) {
        $params = array();
        foreach ($query as $key => $value) {
            foreach (array_keys($this->searchFilters) as $filterKey) {
                if ($key === $this->searchFilters[$filterKey]['osKey']) {
                    $params[$filterKey] = preg_replace('/<.*?>/', '', $value);
                    $this->validateFilter($filterKey, $params[$filterKey]);
                }
            }
        }
        return $params;
    }
    
    /**
     * Check if value is valid for a given filter regarding the model
     * 
     * @param string $filterKey
     * @param string $value
     */
    private function validateFilter($filterKey, $value) {
        
        /*
         * Check pattern for string
         */
        if (isset($this->searchFilters[$filterKey]['pattern'])) {
            if (preg_match('\'' . $this->searchFilters[$filterKey]['pattern'] . '\'', $value) !== 1) {
                RestoLogUtil::httpError(400, 'Value for "' . $this->searchFilters[$filterKey]['osKey'] . '" must follow the pattern ' . $this->searchFilters[$filterKey]['pattern']);
            }
        }
        /*
         * Check pattern for number
         */
        else if (isset($this->searchFilters[$filterKey]['minInclusive']) || isset($this->searchFilters[$filterKey]['maxInclusive'])) {
            if (!is_numeric($value)) {
                RestoLogUtil::httpError(400, 'Value for "' . $this->searchFilters[$filterKey]['osKey'] . '" must be numeric');
            }
            if (isset($this->searchFilters[$filterKey]['minInclusive']) && $value < $this->searchFilters[$filterKey]['minInclusive']) {
                RestoLogUtil::httpError(400, 'Value for "' . $this->searchFilters[$filterKey]['osKey'] . '" must be greater than ' . ($this->searchFilters[$filterKey]['minInclusive'] - 1));
            }
            if (isset($this->searchFilters[$filterKey]['maxInclusive']) && $value > $this->searchFilters[$filterKey]['maxInclusive']) {
                RestoLogUtil::httpError(400, 'Value for "' . $this->searchFilters[$filterKey]['osKey'] . '" must be lower than ' . ($this->searchFilters[$filterKey]['maxInclusive'] + 1));
            }
        }
            
        return true;
        
    }
    
}
