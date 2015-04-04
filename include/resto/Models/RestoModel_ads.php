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
 * RESTo model for Airbus Defence and Space satellites
 */
class RestoModel_ads extends RestoModel {
    
    public $extendedProperties = array(
        'title' => null,
        'description' => null,
        'incidenceAngle' => array(
            'name' => 'incidenceangle',
            'type' => 'NUMERIC'
        ),
        'sunAzimuth' => array(
            'name' => 'sunazimuth',
            'type' => 'NUMERIC'
        ),
        'orientationAngle' => array(
            'name' => 'orientationangle',
            'type' => 'NUMERIC'
        ),
        'acrossTrackIncidenceAngle' => array(
            'name' => 'acrosstrackincidenceangle',
            'type' => 'NUMERIC'
        ),
        'alongTrackIncidenceAngle' => array(
            'name' => 'alongtrackincidenceangle',
            'type' => 'NUMERIC'
        ),
        'archivingStation' => array(
            'name' => 'archivingstation',
            'type' => 'TEXT'
        ),
        'receivingStation' => array(
            'name' => 'receivingstation',
            'type' => 'TEXT'
        ),
        'pitch' => array(
            'name' => 'pitch',
            'type' => 'NUMERIC'
        ),
        'roll' => array(
            'name' => 'roll',
            'type' => 'NUMERIC'
        ),
        'qualityQuotes' => array(
            'name' => 'qualityquotes',
            'type' => 'TEXT'
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
