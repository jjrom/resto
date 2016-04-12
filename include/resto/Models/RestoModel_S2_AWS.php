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
 * RESTo Sentinel-2 model for PEPS project
 */
class RestoModel_S2_AWS extends RestoModel {

    public $extendedProperties = array(
        'dataTakeId' => array(
            'name' => 'datatakeid',
            'type' => 'TEXT'
        ),
        'tileId' => array(
            'name' => 'tileid',
            'type' => 'TEXT'
        ),
        // AWS path
        'path' => array(
            'name' => 'path',
            'type' => 'TEXT'
        ),
        // Percentage of tile coverage (i.e. 100 if completely fill the tile)
        'coverage' => array(
            'name' => 'coverage',
            'type' => 'TEXT'
        )
    );

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
