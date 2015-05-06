<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2013 Jérôme Gasperi <https://github.com/jjrom>
 * 
 * jerome[dot]gasperi[at]gmail[dot]com
 * 
 * 
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
 * 
 */

/**
 * RESTo Sentinel-1 model 
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
class RestoModel_sentinel1 extends RestoModel {
    
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
        'location' => array(
            'name' => 'location',
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
                'productIdentifier' => $dom->getElementsByTagName('identifier')->item(0)->nodeValue,
                'title' => $dom->getElementsByTagName('title')->item(0)->nodeValue,
		'authority' => 'ESA',
                'startDate' => $dom->getElementsByTagName('startTime')->item(0)->nodeValue,
                'completionDate' => $dom->getElementsByTagName('stopTime')->item(0)->nodeValue,
                'productType' => $dom->getElementsByTagName('productType')->item(0)->nodeValue,
                'processingLevel' => 'LEVEL1',
                'platform' => $dom->getElementsByTagName('missionId')->item(0)->nodeValue,
                'sensorMode' => $dom->getElementsByTagName('mode')->item(0)->nodeValue,
                'orbitNumber' => $dom->getElementsByTagName('absoluteOrbitNumber')->item(0)->nodeValue,
            	'location'=> $this->getLocation($dom)
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
