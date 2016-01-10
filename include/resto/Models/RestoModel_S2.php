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
 * resto Sentinel-2 model to ingest GeoJSON metadata
 * from PEPS server at https://peps.cnes.fr/resto/api/collections/S2/search.json
 */
class RestoModel_S2 extends RestoModel {
    
    public $extendedProperties = array(
        's2TakeId' => array(
            'name' => 's2takeid',
            'type' => 'TEXT'
        )
    );
    
    /**
     * Generate S2 download from PEPS 
     *
     * @param $properties
     * @return string
     */
    public function generateDownloadUrl($properties) {
        if (isset($properties['identifier'])) {
            return 'http://peps.mapshup.com/resto/collections/S2/' . $properties['identifier'] . '/download';
        }
        return null;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
}
