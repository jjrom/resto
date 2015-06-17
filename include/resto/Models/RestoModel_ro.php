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
class RestoModel_ro extends RestoModel {

    /*
     * Configuration
     */
    private $config = array();

    public $extendedProperties = array(
        'incidenceAngle' => array(
            'name' => 'incidenceangle',
            'type' => 'NUMERIC'
        ),
    	'productMode' => array(
    		'name' => 'productmode',
    		'type' => 'TEXT'
    	),
        'license' => array(
            'name' => 'license',
            'type' => 'TEXT'
        ),
        'metadataVisibility' => array(
            'name' => 'metadatavisibility',
            'type' => 'TEXT'
        ),
        'productCrs' => array(
            'name' => 'productcrs',
            'type' => 'TEXT'
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
            'title' => 'Satellite incidence angle',
            'quantity' => array(
                'value' => 'incidenceAngle',
                'unit' => '°'
            )
    	),
        'ro:productMode' => array(
            'key' => 'productMode',
            'osKey' => 'productMode',
            'operation' => '=',
            'options' => 'auto'
        ),
        'ro:identifiers' => array(
            'key' => 'identifiers',
            'osKey' => 'identifiers',
            'function' => 'prepareFilterQuery_identifiers'
        ),
        'ro:onlyDownloadableProduct' => array(
            'key' => 'onlyDownloadableProduct',
            'osKey' => 'onlyDownloadableProduct',
            'function' => 'prepareFilterQuery_onlyDownloadableProduct'
        )
    );


    public function prepareFilterQuery_identifiers($param, $user = null) {
        $array_id = explode(",", $param);
        foreach ($array_id as &$id) {
            $id = '\'' . pg_escape_string($id) . '\'';
        }
        $filter = 'identifier IN (' . implode(",", $array_id) . ')';
        return $filter;
    }


    public function prepareFilterQuery_onlyDownloadableProduct($param, $user) {
        if (strtolower($param) === 'true') {
            $filter = 'license is null';
            if (isset($user->profile['email'])) {
                $filter .= ' OR license in (SELECT DISTINCT license_id FROM usermanagement.signatureslicense WHERE email=\'' . $user->profile['email'] . '\')';
            }
            return $filter;
        }
        return null;
    }

    /**
     * Generate the absolute path for RO products used for download feature
     *
     * @param $properties
     * @return string
     */
    public function generateResourcePath($properties) {

        $resource_path = $this->config['general']['rootPaths']['resource_path'];
        if (isset($resource_path)) {
            if (isset($properties['startDate'])) {
                $dateStr = date_format(date_create($properties['startDate']),'Ymd');
                return $resource_path . '/' . $dateStr . '/' . $properties['resource'];
            } else {
                return $resource_path . '/' . $properties['resource'];
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
