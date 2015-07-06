<?php
/*
 * Copyright 2013 Jérôme Gasperi
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

class Tagger_Geology extends Tagger_Generic {

    /*
     * Data references
     */
    public $references = array(
        array(
            'dataset' => 'World Glacier Inventory',
            'author' => 'NSIDC',
            'license' => 'Free of Charge',
            'url' => 'http://nsidc.org/data/docs/noaa/g01130_glacier_inventory/#data_descriptions'
        ),
        array(
            'dataset' => 'Major world fault lines',
            'author' => 'ESRI',
            'license' => 'Access granted to Licensee only',
            'url' => 'http://edcommunity.esri.com/Resources/Collections/mapping-our-world'
        ),
        array(
            'dataset' => 'Major world tectonic plates',
            'author' => 'ESRI',
            'license' => 'Access granted to Licensee only',
            'url' => 'http://edcommunity.esri.com/Resources/Collections/mapping-our-world'
        ),
        array(
            'dataset' => 'Major volcanos of the world',
            'author' => 'ESRI',
            'license' => 'Access granted to Licensee only',
            'url' => 'http://edcommunity.esri.com/Resources/Collections/mapping-our-world'
        ),
        array(
            'dataset' => 'Glaciated area',
            'author' => 'Natural Earth',
            'license' => 'Free of Charge',
            'url' => 'http://www.naturalearthdata.com/downloads/10m-physical-vectors/10m-glaciated-areas/'
        )
    );
    
    /*
     * Columns mapping per table
     */
    protected $columnsMapping = array(
        'glaciers' => array(
            'name' => 'name'
        ),
        'faults' => array(
            'name' => 'type'
        ),
        'plates' => array(
            'name' => 'name'
        ),
        'volcanoes' => array(
            'name' => 'name'
        ),
    );
    
    /**
     * Constructor
     * 
     * @param DatabaseHandler $dbh
     * @param array $config
     */
    public function __construct($dbh, $config) {
        parent::__construct($dbh, $config);
    }
    
    /**
     * Tag metadata
     * 
     * @param array $metadata
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function tag($metadata, $options = array()) {
        return array(
            'geology' => parent::tag($metadata, $options)
        );
    }
    
}
