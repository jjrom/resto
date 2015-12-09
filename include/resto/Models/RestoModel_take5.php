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
 * RESTo Muscate model 
 */
class RestoModel_take5 extends RestoModel {
    
    public $extendedProperties = array(
        'title' => null,
        'description' => null,
        'orbitNumber' => null,
        'project' => array(
            'name' => 'project',
            'type' => 'TEXT'
        ),
        'version' => array(
            'name' => 'version',
            'type' => 'TEXT'
        ),
        'zone_geo' => array(
            'name' => 'zone_geo',
            'type' => 'TEXT'
        ),
        'site_identifier' => array(
            'name' => 'site_identifier',
            'type' => 'TEXT'
        ),
        'productionDate' => array(
            'name' => 'productionDate',
            'type' => 'TIMESTAMP'
        ),
        'bands' => array(
            'name' => 'bands',
            'type' => 'TEXT'
        ),
        'thermBands' => array(
            'name' => 'therm_bands',
            'type' => 'TEXT'
        ),
        'nb_cols' => array(
            'name' => 'nb_cols',
            'type' => 'INTEGER'
        ),
        'nb_rows' => array(
            'name' => 'nb_rows',
            'type' => 'INTEGER'
        )
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->searchFilters['take5:zone_geo'] = array(
            'key' => 'zone_geo',
            'osKey' => 'zone_geo',
            'operation' => '=',
            'keyword' => array(
                'value' => 'zone_geo={:zone_geo:}',
                'type' => 'other'
            )
        );
    }
    
    /**
     * Create JSON feature from xml string
     * 
     * @param {String} $xml : $xml string
     */
    private function parse($xml) {
        
        $dom = new DOMDocument();
        $dom->loadXML(rawurldecode($xml));
        
        /**
        * Get all attributes needed
        */
        $ident = $dom->getElementsByTagName("IDENT")->item(0)->nodeValue;
        // Get the site_identifier
        $site_identifiers = explode("_",$ident);
        $site_identifier = preg_replace("/(.*)[A-Z][0-9]{4}[A-Z][0-9]{4}/", "$1", end($site_identifiers));
        
        /*
         * Initialize feature
         */
        $feature = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Polygon',
                'coordinates' => array(
                    array(
                        array(
                            $dom->getElementsByTagName("HGX")->item(0)->nodeValue,
                            $dom->getElementsByTagName("HGY")->item(0)->nodeValue
                        ),
                        array(
                            $dom->getElementsByTagName("HDX")->item(0)->nodeValue,
                            $dom->getElementsByTagName("HDY")->item(0)->nodeValue
                        ),
                        array(
                            $dom->getElementsByTagName("BDX")->item(0)->nodeValue,
                            $dom->getElementsByTagName("BDY")->item(0)->nodeValue
                        ),
                        array(
                            $dom->getElementsByTagName("BGX")->item(0)->nodeValue,
                            $dom->getElementsByTagName("BGY")->item(0)->nodeValue
                        ),
                        array(
                            $dom->getElementsByTagName("HGX")->item(0)->nodeValue,
                            $dom->getElementsByTagName("HGY")->item(0)->nodeValue
                        )
                    )
                )
            ),
            'properties' => array(
                'productIdentifier' => $dom->getElementsByTagName("IDENT")->item(0)->nodeValue,
                'startDate' => str_replace(' ', 'T', $dom->getElementsByTagName("DATE_PDV")->item(0)->nodeValue),
                'completionDate' => str_replace(' ', 'T', $dom->getElementsByTagName("DATE_PDV")->item(0)->nodeValue),
                'productionDate' => str_replace(' ', 'T', $dom->getElementsByTagName("DATE_PROD")->item(0)->nodeValue),
                'productType' => $this->getProductType($dom->getElementsByTagName("LEVEL")->item(0)->nodeValue),
                'processingLevel' => $this->getProcessingLevel($dom->getElementsByTagName("LEVEL")->item(0)->nodeValue),
                'platform' => $dom->getElementsByTagName("PLATEFORM")->item(0)->nodeValue,
                'instrument' => $dom->getElementsByTagName("SENSOR")->item(0)->nodeValue,
                'resolution' => $dom->getElementsByTagName("RESOLUTION")->item(0)->nodeValue,
                'sensorMode' => $dom->getElementsByTagName("MODE")->item(0)->nodeValue,
                'bands' =>  $dom->getElementsByTagName("BANDS")->item(0)->nodeValue,
                'thermBands' =>  $dom->getElementsByTagName("THERM_BANDS")->item(0)->nodeValue,
                'zone_geo' => preg_replace("/(.*)[A-Z][0-9]{4}[A-Z][0-9]{4}/", "$1", $dom->getElementsByTagName("ZONE_GEO")->item(0)->nodeValue),
                'site_identifier' => $site_identifier,
                'version' => $dom->getElementsByTagName("VERSION")->item(0)->nodeValue,
                'nb_cols' => $dom->getElementsByTagName("NB_COLS")->item(0)->nodeValue,
                'nb_rows' => $dom->getElementsByTagName("NB_ROWS")->item(0)->nodeValue
            )
        );
        
        return $feature;
        
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

    private function getProductType($level) {

        $product = $level;

        if ($level === "N1_TUILE" || $level === "N1_SCENE") {
            $product = "REFLECTANCETOA";
        } else if ($level === "N2A") {
            $product = "REFLECTANCE";
        }


        return $product;
    }

    private function getProcessingLevel($level) {

        $product = $level;

        if ($level === "N1_TUILE" || $level === "N1_SCENE") {
            $product = "LEVEL1C";
        } else if ($level === "N2A") {
            $product = "LEVEL2A";
        }


        return $product;
    }

}
