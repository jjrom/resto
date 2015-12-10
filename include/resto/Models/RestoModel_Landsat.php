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
 * resto model for Landsat satellite
 */
class RestoModel_Landsat extends RestoModel {
    public $extendedProperties = array(
        'path' => array(
            'name' => 'path',
            'type' => 'NUMERIC'
        ),
        'row' => array(
            'name' => 'row',
            'type' => 'NUMERIC'
        ),
        'dayOrNight' => array(
            'name' => 'dayornight',
            'type' => 'TEXT'
        ),
        'sunAzimuth' => array(
            'name' => 'sunazimuth',
            'type' => 'NUMERIC'
        ),
        'sunElevation' => array(
            'name' => 'sunelevation',
            'type' => 'NUMERIC'
        )
    );

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

}
