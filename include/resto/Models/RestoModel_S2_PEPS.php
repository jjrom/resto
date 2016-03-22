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
class RestoModel_S2_PEPS extends RestoModel {

    public $extendedProperties = array(
        's2TakeId' => array(
            'name' => 's2takeid',
            'type' => 'TEXT'
        ),
        'orbitDirection' => array(
            'name' => 'orbitDirection',
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

    /**
     * Add feature to the {collection}.features table following the class model
     * 
     * @param array $data : array (MUST BE GeoJSON in abstract Model)
     * @param string $collectionName : collection name
     */
    public function storeFeature($data, $collectionName) {
        return parent::storeFeature($this->parse(join('', $data)), $collectionName);
    }

    /**
     * Create JSON feature from xml string
     * 
     * @param {String} $xml : $xml string
     */
    private function parse($xml) {

        $dom = new DOMDocument();
        $dom->loadXML(rawurldecode($xml));

        return $this->parseNew($dom);
    }

    /**
     * Create JSON feature from new resource xml string
     *
     * <product>
      <title>S1A_IW_OCN__2SDV_20150727T044706_20150727T044731_006992_0097D1_F6DA</title>
      <resourceSize>6317404</resourceSize>
      <startTime>2015-07-27T04:47:06.611</startTime>
      <stopTime>2015-07-27T04:47:31.061</stopTime>
      <productType>OCN</productType>
      <missionId>S1A</missionId>
      <processingLevel>1</processingLevel>
      <mode>IW</mode>
      <absoluteOrbitNumber>6992</absoluteOrbitNumber>
      <orbitDirection>ASCENDING</orbitDirection>
      <s2takeid>38865</s2takeid>
      <cloudcover>0.0</cloudcover>
      <instrument>Multi-Spectral Instrument</instrument>
      <footprint>POLYGON ((-161.306549 21.163258,-158.915909 21.585093,-158.623169 20.077986,-160.989746 19.652864,-161.306549 21.163258))</footprint>
      </product>
     *
     * @param {DOMDocument} $dom : $dom DOMDocument
     */
    private function parseNew($dom) {

        /*
         * Retrieves orbit direction
         */
        $orbitDirection = strtolower($dom->getElementsByTagName('orbitDirection')->item(0)->nodeValue);

        $polygon = RestoGeometryUtil::WKTPolygonToArray($dom->getElementsByTagName('footprint')->item(0)->nodeValue);

        /*
         * Initialize feature
         */
        $feature = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Polygon',
                'coordinates' => array($polygon),
            ),
            'properties' => array(
                'productIdentifier' => $dom->getElementsByTagName('title')->item(0)->nodeValue,
                'title' => $dom->getElementsByTagName('title')->item(0)->nodeValue,
                'resourceSize' => $dom->getElementsByTagName('resourceSize')->item(0)->nodeValue,
                'authority' => 'ESA',
                'startDate' => $dom->getElementsByTagName('startTime')->item(0)->nodeValue,
                'completionDate' => $dom->getElementsByTagName('stopTime')->item(0)->nodeValue,
                'productType' => $dom->getElementsByTagName('productType')->item(0)->nodeValue,
                'processingLevel' => $dom->getElementsByTagName('processingLevel')->item(0)->nodeValue,
                'platform' => $dom->getElementsByTagName('missionId')->item(0)->nodeValue,
                'sensorMode' => $dom->getElementsByTagName('mode')->item(0)->nodeValue,
                'orbitNumber' => $dom->getElementsByTagName('absoluteOrbitNumber')->item(0)->nodeValue,
                'orbitDirection' => $orbitDirection,
                'instrument' => $dom->getElementsByTagName('instrument')->item(0)->nodeValue,
                'quicklook' => $this->getLocation($dom),
                's2TakeId' => $dom->getElementsByTagName('s2takeid')->item(0)->nodeValue,
                'cloudCover' => $dom->getElementsByTagName('cloudCover')->item(0)->nodeValue,
            )
        );

        return $feature;
    }

    function getLocation($dom) {
        $startTime = explode("T", $dom->getElementsByTagName('startTime')->item(0)->nodeValue);
        $result = str_replace("-", "/", $startTime[0]);
        $missionId = $dom->getElementsByTagName('missionId')->item(0)->nodeValue;
        $title = $dom->getElementsByTagName('title')->item(0)->nodeValue;
        return $result . "/" . $missionId . "/" . $title;
    }

}
