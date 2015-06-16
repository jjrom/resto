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
 * RESTo model for Recovery Observatory
 */
class RestoModel_RO extends RestoModel {

    /*
     * Configuration
     */
    private $config = array();

    public $extendedProperties = array(
        'parentIdentifier' => null,
        'sensorMode' => null,
        'metadata' => null,
        'metadataMimeType' => null,
        'incidenceAngle' => array(
            'name' => 'incidenceangle',
            'type' => 'NUMERIC'
        ),
        'productMode' => array(
            'name' => 'productmode',
            'type' => 'VARCHAR(20)'
        ),
        'productCrs' => array(
            'name' => 'productcrs',
            'type' => 'VARCHAR(250)'
        ),
        'productGeometry' => array(
            'name' => 'productgeometry',
            'type' => 'TEXT'
        )
    );


    public $extendedSearchFilters = array(
        'ro:incidenceAngle' => array(
            'key' => 'incidenceAngle',
            'osKey' => 'incidenceAngle',
            'operation' => 'interval',
            'title' => 'Satellite incident angle',
            'quantity' => array(
                'value' => 'cloud',
                'unit' => '%'
            )
        ),
        'ro:identifiers' => array(
            'key' => 'identifiers',
            'osKey' => 'identifiers',
            'function' => 'prepareFilterQuery_contextualSearch'
        )
    );

    /**
     * Generate the absolute path for RO products used for download feature
     *
     * @param $properties
     * @return string
     */
    public function generateResourcePath($properties) {

        if (isset($this->config['rootPaths']['resource_path'])) {
            if (isset($properties['startDate'])) {
                $dateStr = date_format(date_create($properties['startDate']),'Ymd');
                return $this->config['rootPaths']['resource_path'] . '/' . $dateStr . '/' . $properties['resource'];
            } else {
                return $this->config['rootPaths']['resource_path'] . '/' . $properties['resource'];
            }
        } else {
            return $properties['resource'];
        }
    }

    /**
     * Generate the dynamic relative path for RO quicklooks
     *
     * @param $properties
     * @return string relative path in the form of YYYYMMdd/quicklook_filename with YYYYMMdd is the formated startDate parameter
     */
    public function generateQuicklookPath($properties) {
        if (isset($properties['startDate'])) {
            $dateStr = date_format(date_create($properties['startDate']),'Ymd');
            return $dateStr . '/' . $properties['quicklook'];
        } else {
            return $properties['quicklook'];
        }
    }

    /**
     * Generate the dynamic relative path for RO thumbnails
     *
     * @param $properties
     * @return string relative path in the form of YYYYMMdd/thumbnail_filename with YYYYMMdd is the formated startDate parameter
     */
    public function generateThumbnailPath($properties) {
        if (isset($properties['startDate'])) {
            $dateStr = date_format(date_create($properties['startDate']),'Ymd');
            return $dateStr . '/' . $properties['thumbnail'];
        } else {
            return $properties['thumbnail'];
        }
    }
    
    /**
     * 
     * @param String $param
     * @return string
     */
    public function prepareFilterQuery_contextualSearch($param) {
        $array_id = explode(",", $param);
        foreach ($array_id as &$id) {
            $id = '\'' . $id . '\'';
        }
        $filter = 'identifier IN (' . implode(",", $array_id) . ')';
        return $filter;
    }

    /**
     * Constructor
     */
    public function __construct() {
        
        parent::__construct();
        $this->searchFilters = array_merge($this->searchFilters, $this->extendedSearchFilters);

        /**
         * Read config.php file
         */
        $configFile = realpath(dirname(__FILE__)) . '/../../config.php';
        if (!file_exists($configFile)) {
            RestoLogUtil::httpError(4000, 'Missing mandatory configuration file');
        }
        $this->config = include($configFile);
    }

}
