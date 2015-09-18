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
 * RESTo example model 
 */
class RestoModel_example extends RestoModel {
    /*
     * Properties mapping between RESTo model and input
     * GeoJSON Feature file
     */

    public $inputMapping = array(
        'properties.productId' => 'productIdentifier',
        'properties.acquisitionDate' => array('startDate', 'completionDate'),
        'properties.satellite' => 'platform',
        'properties.sensorfamily' => 'sensorMode',
        'properties.imageUrl' => 'quicklook',
        'properties.archivingStation' => 'archivingCenter',
        'properties.receivingStation' => 'acquisitionStation',
        'properties.cloudCoverPercentage' => 'cloudCover',
        'properties.snowCoverPercentage' => 'snowCover'
    );
    public $extendedProperties = array(
        'test' => array(
            'name' => 'test',
            'type' => 'VARCHAR(50)',
            'constraint' => 'DEFAULT \'essai\''
        )
    );

    /**
     * Constructor
     */
    public function __construct() {
   
        parent::__construct();
   
        $this->searchFilters['eo:test'] = array(
            'key' => 'test',
            'osKey' => 'test',
            'operation' => '=',
            'keyword' => array(
                'value' => 'test={:test:}',
                'type' => 'other'
            )
        );
    }

}
