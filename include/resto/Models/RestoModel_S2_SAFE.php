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
 * RESTo Sentinel-2 model from High Level SAFE XML file
 */
class RestoModel_S2_SAFE extends RestoModel {

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
     * Create JSON feature from High Level SAFE file
     *
     * <n1:Level-1C_User_Product xmlns:n1="https://psd-13.sentinel2.eo.esa.int/PSD/User_Product_Level-1C.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://psd-13.sentinel2.eo.esa.int/PSD/User_Product_Level-1C.xsd">
    <n1:General_Info>
        <Product_Info>
            <PRODUCT_START_TIME>2016-10-14T16:58:02.026Z</PRODUCT_START_TIME>
            <PRODUCT_STOP_TIME>2016-10-14T16:58:01.458Z</PRODUCT_STOP_TIME>
            <PRODUCT_URI>S2A_OPER_MSI_L1C_TL_MTI__20161014T211140_A006858_T01CEL_N02.04</PRODUCT_URI>
            <PROCESSING_LEVEL>Level-1C</PROCESSING_LEVEL>
            <PRODUCT_TYPE>S2MSI1C</PRODUCT_TYPE>
            <PROCESSING_BASELINE>02.04</PROCESSING_BASELINE>
            <GENERATION_TIME>2016-10-15T13:00:15.000758Z</GENERATION_TIME>
            <PREVIEW_IMAGE_URL>https://pdmcdam2.sentinel2.eo.esa.int/s2pdgs_geoserver/geo_service.php?service=WMS&amp;version=1.1.0&amp;request=GetMap&amp;layers=S2A_A006858_N0204:S2A_A006858_N0204&amp;styles=&amp;bbox=-177.00116424176912,-81.14849785889395,-172.97948396186467,-80.1435795619881&amp;width=1492&amp;height=372&amp;srs=EPSG:4326&amp;format=image/png</PREVIEW_IMAGE_URL>
            <PREVIEW_GEO_INFO>BrowseImageFootprint</PREVIEW_GEO_INFO>
            <Datatake datatakeIdentifier="GS2A_20161014T165802_006858_N02.04">
      <SPACECRAFT_NAME>Sentinel-2A</SPACECRAFT_NAME>
      <DATATAKE_TYPE>INS-NOBS</DATATAKE_TYPE>
      <DATATAKE_SENSING_START>2016-10-14T16:58:02.026Z</DATATAKE_SENSING_START>
      <SENSING_ORBIT_NUMBER>140</SENSING_ORBIT_NUMBER>
      <SENSING_ORBIT_DIRECTION>DESCENDING</SENSING_ORBIT_DIRECTION>
    </Datatake>
<Query_Options>
     ...etc...
     *
     * @param {DOMDocument} $dom : $dom DOMDocument
     */
    private function parseNew($dom) {

        $idSplitted = explode('_', $dom->getElementsByTagName('PRODUCT_URI')->item(0)->nodeValue);
        $platform = $idSplitted[0];

        /*
         * Initialize feature
         */
        $feature = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Polygon',
                'coordinates' => array($this->EXTPOSLIST2Array($dom->getElementsByTagName('EXT_POS_LIST')->item(0)->nodeValue)),
            ),
            'properties' => array(
                'productIdentifier' => $dom->getElementsByTagName('PRODUCT_URI')->item(0)->nodeValue,
                'title' => $dom->getElementsByTagName('PRODUCT_URI')->item(0)->nodeValue,
                'authority' => 'ESA',
                'startDate' => $dom->getElementsByTagName('PRODUCT_START_TIME')->item(0)->nodeValue,
                'completionDate' => $dom->getElementsByTagName('PRODUCT_STOP_TIME')->item(0)->nodeValue,
                'productType' => $dom->getElementsByTagName('PRODUCT_TYPE')->item(0)->nodeValue,
                'processingLevel' => 'L1C',
                'platform' => $platform,
                'orbitNumber' => $dom->getElementsByTagName('SENSING_ORBIT_NUMBER')->item(0)->nodeValue,
                'orbitDirection' => strtolower($dom->getElementsByTagName('SENSING_ORBIT_DIRECTION')->item(0)->nodeValue),
                'quicklook' => $dom->getElementsByTagName('PREVIEW_IMAGE_URL')->item(0)->nodeValue,
                's2TakeId' => $dom->getElementsByTagName('Datatake')->item(0)->getAttribute('datatakeIdentifier'),
                'cloudCover' => $dom->getElementsByTagName('Cloud_Coverage_Assessment')->item(0)->nodeValue,
            )
        );

        return $feature;
    }

    /**
     * Convert string of lon lat pairs to Array of coordinates
     * 
     * @param string $strCoords lon lat coordinates space separated
     */
    private function EXTPOSLIST2Array($strCoords) {
        $strExploded = explode(' ', trim($strCoords));
        $coords = array();
        for ($i = 0, $ii = count($strExploded); $i < $ii; $i = $i + 2) {
            $coords[] = array(floatval($strExploded[$i]), floatval($strExploded[$i + 1]));
        }
        return $coords;
    }

}
