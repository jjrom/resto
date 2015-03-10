<?php

/*
 * RESTo
 * 
 * REST OpenSearch - Very Lightweigt PHP REST Library for OpenSearch EO
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
