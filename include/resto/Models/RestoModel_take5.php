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
