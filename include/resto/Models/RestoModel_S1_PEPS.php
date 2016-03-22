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
 * RESTo Sentinel-1 model for PEPS project 
 */
class RestoModel_S1_PEPS extends RestoModel {

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

        $this->searchFilters['eo:orbitDirection'] = array(
            'key' => 'orbitDirection',
            'osKey' => 'orbitDirection',
            'operation' => '=',
            'options' => 'auto'
        );

        $this->searchFilters['polarisation'] = array(
            'key' => 'polarisation',
            'osKey' => 'polarisation',
            'operation' => '=',
            'options' => 'auto'
        );
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
        /*
         * adsHeader is a tag only found in the old xml version
         */
        $verifyVersion = $dom->getElementsByTagName('adsHeader');
        if ($verifyVersion->length == 0) {
            /*
             * We parse the file with the new version
             */
            return $this->parseNew($dom);
        } else {
            /*
             * We parse the file with the old version 
             */
            return $this->parseOld($dom);
        }
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
      <processingLevel>2</processingLevel>
      <mode>IW</mode>
      <absoluteOrbitNumber>6992</absoluteOrbitNumber>
      <orbitDirection>ASCENDING</orbitDirection>
      <swath>IW</swath>
      <polarisation>VV VH</polarisation>
      <missiontakeid>38865</missiontakeid>
      <instrument>Multi-Spectral Instrument</instrument>
      <footprint>POLYGON ((-161.306549 21.163258,-158.915909 21.585093,-158.623169 20.077986,-160.989746 19.652864,-161.306549 21.163258))</footprint>
      </product>
     *
     * @param {DOMDocument} $dom : $dom DOMDocument
     */
    private function parseNew($dom) {

        /*
         * Retreives orbit direction
         */
        $orbitDirection = strtolower($dom->getElementsByTagName('orbitDirection')->item(0)->nodeValue);
        /*
         * Performs an inversion of the specified Sentinel-1 quicklooks footprint (inside the ZIP files, i.e SAFE product).
         * The datahub systematically performs an inversion of the Sentinel-1 quicklooks taking as input the quicklook images (.png) inside
         * the ZIP files (i.e. as produced by the S1 ground segment).
         */
        $polygon = array($this->reorderSafeFootprintToDhus(RestoGeometryUtil::WKTPolygonToArray($dom->getElementsByTagName('footprint')->item(0)->nodeValue), $orbitDirection));

        /*
         * Initialize feature
         */
        $feature = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Polygon',
                'coordinates' => $polygon,
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
                'swath' => $dom->getElementsByTagName('swath')->item(0)->nodeValue,
                'polarisation' => $dom->getElementsByTagName('polarisation')->item(0)->nodeValue,
                'missionTakeId' => $dom->getElementsByTagName('missiontakeid')->item(0)->nodeValue,
                'instrument' => $dom->getElementsByTagName('instrument')->item(0)->nodeValue,
                'quicklook' => $this->getLocation($dom),
                'cloudCover' => 0
            )
        );

        return $feature;
    }

    /**
     * Create JSON feature from old resource xml string
     *
      <product>
      <adsHeader>
      <missionId>S1A</missionId>
      <productType>GRD</productType>
      <polarisation>VV</polarisation>
      <mode>IW</mode>
      <swath>IW</swath>
      <startTime>2014-10-03T18:47:39.842715</startTime>
      <stopTime>2014-10-03T18:48:08.834276</stopTime>
      <absoluteOrbitNumber>2669</absoluteOrbitNumber>
      <missionDataTakeId>12181</missionDataTakeId>
      <imageNumber>001</imageNumber>
      </adsHeader>
      (...)
      <geolocationGrid>
      <geolocationGridPointList count="231">
      <geolocationGridPoint>
      <azimuthTime>2014-10-03T18:47:39.842455</azimuthTime>
      <slantRangeTime>5.364633780973990e-03</slantRangeTime>
      <line>0</line>
      <pixel>0</pixel>
      <latitude>6.588778439216060e+01</latitude>
      <longitude>1.785002983064243e+02</longitude>
      (...)
     *
     * @param {DOMDocument} $dom : $dom DOMDocument
     */
    private function parseOld($dom) {
        /*
         * Retreives geolocation grid point
         */
        $geolocationGridPoint = $dom->getElementsByTagName('geolocationGridPoint');
        /*
         * Retreives orbit direction
         */
        $orbitDirection = strtolower($dom->getElementsByTagName('pass')->item(0)->nodeValue);
        /*
         * Performs an inversion of the specified Sentinel-1 quicklooks footprint (inside the ZIP files, i.e SAFE product).
         * The datahub systematically performs an inversion of the Sentinel-1 quicklooks taking as input the quicklook images (.png) inside 
         * the ZIP files (i.e. as produced by the S1 ground segment).
         */
        $polygon = $this->readFootprintFromGeolocationGridPoint($geolocationGridPoint, $orbitDirection);

        /*
         * Initialize feature
         */
        $feature = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Polygon',
                'coordinates' => array($polygon)
            ),
            'properties' => array(
                'productIdentifier' => $dom->getElementsByTagName('title')->item(0)->nodeValue,
                'title' => $dom->getElementsByTagName('title')->item(0)->nodeValue,
                'resourceSize' => $dom->getElementsByTagName('resourceSize')->item(0)->nodeValue,
                'authority' => 'ESA',
                'startDate' => $dom->getElementsByTagName('startTime')->item(0)->nodeValue,
                'completionDate' => $dom->getElementsByTagName('stopTime')->item(0)->nodeValue,
                'productType' => $dom->getElementsByTagName('productType')->item(0)->nodeValue,
                'processingLevel' => 'LEVEL1',
                'platform' => $dom->getElementsByTagName('missionId')->item(0)->nodeValue,
                'sensorMode' => $dom->getElementsByTagName('mode')->item(0)->nodeValue,
                'orbitNumber' => $dom->getElementsByTagName('absoluteOrbitNumber')->item(0)->nodeValue,
                'orbitDirection' => $orbitDirection,
                'swath' => $dom->getElementsByTagName('swath')->item(0)->nodeValue,
                'polarisation' => $dom->getElementsByTagName('polarisation')->item(0)->nodeValue,
                'missionTakeId' => $dom->getElementsByTagName('missionDataTakeId')->item(0)->nodeValue,
                'quicklook' => $this->getLocation($dom),
                'cloudCover' => 0
            )
        );
        return $feature;
    }

    private function getLocation($dom) {
        $startTime = explode("T", $dom->getElementsByTagName('startTime')->item(0)->nodeValue);
        $result = str_replace("-", "/", $startTime[0]);
        $missionId = $dom->getElementsByTagName('missionId')->item(0)->nodeValue;
        $title = $dom->getElementsByTagName('title')->item(0)->nodeValue;
        return $result . "/" . $missionId . "/" . $title;
    }

    /**
     * Reads Footprint from geolocation grid point
     * 
     * @param unknown $geolocationGridPoint
     * @param unknown $orbitDirection
     */
    private function readFootprintFromGeolocationGridPoint($geolocationGridPoint, $orbitDirection) {

        /*
         * On Ascending orbit, we are:
         * ll : pt(0, 0) i.e pixel 0, line 0 in geolocation grid point
         * lr : pt(max, 0)
         * ul : pt(0, max)
         * ur : pt(max, max),
         * 
         * On Descending orbit, we are:
         * ul : pt(0, 0) i.e pixel 0, line 0 in geolocation grid point
         * ur : pt(max, 0)
         * ll : pt(0, max)
         * lr : pt(max, max), 
         */
        $ll = array();
        $lr = array();
        $ul = array();
        $ur = array();
        $lineMax = 0;
        $lineMin = 0;
        $lineMinStatus = 0;
        $pixelMax = 0;
        for ($i = 0, $ii = $geolocationGridPoint->length; $i < $ii; $i++) {
            $line = (integer) $geolocationGridPoint->item($i)->getElementsByTagName('line')->item(0)->nodeValue;
            $pixel = (integer) $geolocationGridPoint->item($i)->getElementsByTagName('pixel')->item(0)->nodeValue;
            $coordinates = array($geolocationGridPoint->item($i)->getElementsByTagName('longitude')->item(0)->nodeValue, $geolocationGridPoint->item($i)->getElementsByTagName('latitude')->item(0)->nodeValue);
            if ($lineMinStatus == 0) {
                $lineMinStatus = 1;
                $lineMin = $line;
            }
            if ($line === $lineMin) {
                if ($pixel === 0) {
                    $ll = $coordinates;
                } else if ($pixel >= $pixelMax) {
                    $pixelMax = $pixel;
                    $lr = $coordinates;
                }
            } else if ($line >= $lineMax) {
                $lineMax = $line;
                if ($pixel === 0) {
                    $ul = $coordinates;
                } else if ($pixel >= $pixelMax) {
                    $pixelMax = $pixel;
                    $ur = $coordinates;
                }
            }
        }
        /*
         * On descending orbit, the North and South are inverted
         */
        if (strtolower($orbitDirection) === "descending") {
            /*
             * Temporary coordinates
             */
            $ll_ = $ll;
            $lr_ = $lr;
            $ur_ = $ur;
            $ul_ = $ul;
            /*
             * Inverts North and South
             */
            $lr = $ul_;
            $ll = $ur_;
            $ul = $lr_;
            $ur = $ll_;
        }
        $polygon = array($ll, $lr, $ur, $ul, $ll);
        return $polygon;
    }
    
    /**
     * Performs an inversion of the specified Sentinel-1 quicklooks footprint (inside the ZIP files, i.e SAFE product).
     * The datahub systematically performs an inversion of the Sentinel-1 quicklooks taking as input the quicklook images (.png) inside 
     * the ZIP files (i.e. as produced by the S1 ground segment).
     * 
     * @param String $footprint polygon array(ll, lr, ur, ul, ll)
     * @param String $orbitDirection orbit direction
     * @return multitype:
     */
    private function reorderSafeFootprintToDhus($footprint, $orbitDirection){
        
        /*
         * For ascending orbits, the quicklook is flipped horizontally (i.e. North/South inversion)
         */
        if (strtolower($orbitDirection) === "ascending"){
            $ul = $footprint[0];
            $ur = $footprint[1];
            $lr = $footprint[2];
            $ll = $footprint[3];
        }
        /*
         * For descending orbits, the quiclook is flipped vertically and horizontally (i.e. West/East and North/South inversions)
         */
        else {
            $lr = $footprint[0];
            $ll = $footprint[1];
            $ul = $footprint[2];
            $ur = $footprint[3];
        }

        $flippedFootprint = array($ll, $lr, $ur, $ul, $ll);
        return $flippedFootprint;
    }

}
