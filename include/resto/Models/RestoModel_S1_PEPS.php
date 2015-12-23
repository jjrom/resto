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
 * 
 * Input metadata is an XML file with the following structure 
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
        return parent::storeFeature($this->parse(join('',$data)), $collectionName);
    }
    
    /**
     * Create JSON feature from xml string
     * 
     * <product>
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
     * @param {String} $xml : $xml string
     */
    private function parse($xml) {
        
        $dom = new DOMDocument();
        $dom->loadXML(rawurldecode($xml));
        
        $geolocationGridPoint = $dom->getElementsByTagName('geolocationGridPoint');
        $ul = array();
        $ur = array();
        $ll = array();
        $lr = array();
        $lineMax = 0;
	$lineMin = 0;
	$lineMinStatus = 0;
        $pixelMax = 0;
        for ($i = 0, $ii = $geolocationGridPoint->length; $i < $ii; $i++) { 
            $line = (integer) $geolocationGridPoint->item($i)->getElementsByTagName('line')->item(0)->nodeValue;
            $pixel = (integer) $geolocationGridPoint->item($i)->getElementsByTagName('pixel')->item(0)->nodeValue;
            $coordinates = array($geolocationGridPoint->item($i)->getElementsByTagName('longitude')->item(0)->nodeValue, $geolocationGridPoint->item($i)->getElementsByTagName('latitude')->item(0)->nodeValue);     
	    if ($lineMinStatus == 0)
		{
		  $lineMinStatus = 1;
		  $lineMin=$line;
		}
            if ($line === $lineMin) {
                if ($pixel === 0) {
                    $ul = $coordinates;
                }
                else if ($pixel >= $pixelMax) {
                    $pixelMax = $pixel;
                    $ur = $coordinates;
                }
            }
            else if ($line >= $lineMax) {
                $lineMax = $line;
                if ($pixel === 0) {
                    $ll = $coordinates;
                }
                else if ($pixel >= $pixelMax) {
                    $pixelMax = $pixel;
                    $lr = $coordinates;
                }
            }
        } 
        $polygon = array($ul, $ur, $lr, $ll, $ul);
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
		'authority' => 'ESA',
                'startDate' => $dom->getElementsByTagName('startTime')->item(0)->nodeValue,
                'completionDate' => $dom->getElementsByTagName('stopTime')->item(0)->nodeValue,
                'productType' => $dom->getElementsByTagName('productType')->item(0)->nodeValue,
                'processingLevel' => 'LEVEL1',
                'platform' => $dom->getElementsByTagName('missionId')->item(0)->nodeValue,
                'sensorMode' => $dom->getElementsByTagName('mode')->item(0)->nodeValue,
                'orbitNumber' => $dom->getElementsByTagName('absoluteOrbitNumber')->item(0)->nodeValue,
            	'quicklook'=> $this->getLocation($dom),
                'cloudCover' => 0
	    )
        );

        return $feature;
        
    }

    function getLocation($dom) {
        $startTime = $dom->getElementsByTagName('startTime')->item(0)->nodeValue;
        $startTime = explode("T", $startTime);
        $result = str_replace("-","/",$startTime[0]);
        $missionId = $dom->getElementsByTagName('missionId')->item(0)->nodeValue;
        $title= $dom->getElementsByTagName('title')->item(0)->nodeValue;
	return $result."/".$missionId."/".$title;
    }

}
