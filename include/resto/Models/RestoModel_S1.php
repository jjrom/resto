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
 * resto Sentinel-1 model to ingest GeoJSON metadata
 * from PEPS server at https://peps.cnes.fr/resto/api/collections/S1/search.json
 */
class RestoModel_S1 extends RestoModel {
    
    public $extendedProperties = array(
        'swath' => array(
            'name' => 'swath',
            'type' => 'TEXT'
        ),
        'polarisation' => array(
            'name' => 'polarisation',
            'type' => 'TEXT'
        ),
        'missionTakeId' => array(
            'name' => 'missiontakeid',
            'type' => 'INTEGER'
        )
    );
    
    /**
     * Generate S1 download from PEPS 
     * 
     * @param $properties
     * @return string
     */
    public function generateDownloadUrl($properties) {
        if (isset($properties['identifier'])) {
            return 'http://peps.mapshup.com/resto/collections/S1/' . $properties['identifier'] . '/download';
        }
        return null;
    }
    
    /**
     * Generate WMS url on PEPS server based on image identifier
     * 
     *   e.g. S1A_EW_GRDM_1SSH_20151225T081351_20151225T081451_009196_00D3FB_2673
     *
     * @param $properties
     * @return string
     */
    public function generateWMSUrl($properties) {
        if (isset($properties['productIdentifier'])) {
            $exploded = explode('_', $properties['productIdentifier']);
            $mission = substr($exploded[0], 0, 2);
            $params = array(
                substr($exploded[4], 0, 4),
                substr($exploded[4], 4, 2),
                substr($exploded[4], 6, 2),
                $exploded[0],
                $properties['productIdentifier']
            );
            return 'https://peps.cnes.fr/cgi-bin/mapserver?map=WMS_' . $mission . '&data=' . join('/', $params) . '&layers=quicklook&format=image/png';
        }
        return null;
    }
    
    /**
     * Constructor
     * 
     * @param RestoContext $context : Resto context
     * @param RestoContext $user : Resto user
     */
    public function __construct() {
        parent::__construct();
    }
    
}
