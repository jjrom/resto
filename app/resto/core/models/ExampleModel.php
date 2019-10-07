<?php
/*
 * Copyright 2018 Jérôme Gasperi
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
 * Dummy example model based on default
 */
class ExampleModel extends SatelliteModel
{

    /*
     * Properties mapping between RESTo model and input
     * GeoJSON Feature file
     *
     *  Left part   [KEY]   is the path to the property in input file
     *  Right part  [VALUE] is the name of the property stored.
     *
     *  If [VALUE] is an array, then the [KEY] is duplicated in each [VALUE]
     */
    public $inputMapping = array(
        'properties.productId' => 'productIdentifier',
        'properties.acquisitionDate' => array('startDate', 'completionDate'),
        'properties.satellite' => 'platform',
        'properties.sensorFamily' => 'sensorMode',
        'properties.imageUrl' => 'quicklook',
        'properties.cloudCoverPercentage' => 'cloudCover',
        'properties.test.snowCoverPercentage' => 'snowCover'
    );

    /**
     * Constructor
     * 
     * @param array $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
    }

    /**
     * The return value from this function will replace
     * output feature properties['links'] object
     *
     * @param array $properties : feature properties
     * @param string $href : resto download url i.e. http://locahost/items/id/download
     *
     */
    public function generateLinksArray($properties, $href)
    {
        return parent::generateLinksArray($properties, $href);
    }

    /**
     * The return value from this function will replace
     * feature properties['quicklook'] string
     */
    public function generateQuicklookUrl($properties)
    {
        return parent::generateQuicklookUrl($properties);
    }

    /**
     * The return value from this function will replace
     * feature properties['thumbnail'] string
     */
    public function generateThumbnailUrl($properties)
    {
        return parent::generateThumbnailUrl($properties);
    }
}
