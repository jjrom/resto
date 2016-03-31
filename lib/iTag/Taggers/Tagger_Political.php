<?php
/*
 * Copyright 2013 Jérôme Gasperi
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
require 'CountryInfos.php';
class Tagger_Political extends Tagger {
    
    const COUNTRIES = 1;
    const REGIONS = 2;
    
    /*
     * Data references
     */
    public $references = array(
        array(
            'dataset' => 'Admin level 0 - Countries',
            'author' => 'Natural Earth',
            'license' => 'Free of Charge',
            'url' => 'http://www.naturalearthdata.com/downloads/10m-cultural-vectors/10m-admin-0-countries/'
        ),
        array(
            'dataset' => 'Admin level 1 - States, Provinces',
            'author' => 'Natural Earth',
            'license' => 'Free of Charge',
            'url' => 'http://www.naturalearthdata.com/downloads/10m-cultural-vectors/10m-admin-1-states-provinces/'
        )
    );

    /*
     * Compute toponyms : 'main', 'all', null
     */
    private $addToponyms = 'main';
    
    /**
     * Constructor
     * 
     * @param DatabaseHandler $dbh
     * @param array $config
     */
    public function __construct($dbh, $config) {
        parent::__construct($dbh, $config);
    }
    
    /**
     * Tag metadata
     * 
     * @param array $metadata
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function tag($metadata, $options = array()) {
        parent::tag($metadata, $options);
        return $this->process($metadata['footprint'], $options);
    }
    
    /**
     * Compute intersected information from input WKT footprint
     * 
     * @param string $footprint
     * @param array $options
     * 
     */
    private function process($footprint, $options) {

        /*
         * Toponyms
         */
        if (isset($options['toponyms'])) {
            $this->addToponyms = $options['toponyms'];
        }
        
        /*
         * Initialize empty array
         */
        $continents = array();
                
        /*
         * Add continents and countries
         */
        $this->add($continents, $footprint, Tagger_Political::COUNTRIES);
        
        /*
         * Add regions/states
         */
        $this->add($continents, $footprint, Tagger_Political::REGIONS);
        
        return array(
            'political' => array(
                'continents' => $continents
            )
        );
        
    }
    
    /**
     * Add continents/countries or regions/states to political array
     * 
     * @param array $continents
     * @param string $footprint
     * @param integer $what
     * 
     */
    private function add(&$continents, $footprint, $what) {
        $prequery = 'WITH prequery AS (SELECT ' . $this->postgisGeomFromText($footprint) . ' AS corrected_geometry)';
        if ($what === Tagger_Political::COUNTRIES) {
            $query = $prequery . ' SELECT name as name, normalize(name) as id, continent as continent, normalize(continent) as continentid, ' . $this->postgisArea($this->postgisIntersection('geom', 'corrected_geometry')) . ' as area, ' . $this->postgisArea('geom') . ' as entityarea FROM prequery, datasources.countries WHERE st_intersects(geom, corrected_geometry) ORDER BY area DESC';
        }
        else {
            $query = $prequery . ' SELECT region, name as state, normalize(name) as stateid, normalize(region) as regionid, adm0_a3 as isoa3, ' .  $this->postgisArea($this->postgisIntersection('geom', 'corrected_geometry')) . ' as area, ' . $this->postgisArea('geom') . ' as entityarea, ' . $this->postgisIntersection('geom', 'corrected_geometry') . ' as wkb_geom FROM prequery, datasources.states WHERE st_intersects(geom, corrected_geometry) ORDER BY area DESC';
        }
        $results = $this->query($query);
        while ($element = pg_fetch_assoc($results)) {
            if ($what === Tagger_Political::COUNTRIES) {
                $this->addCountriesToContinents($continents, $element);
            }
            else {
                $this->addRegionsToCountries($continents, $element);
            }
        }
    }
    
    /**
     * Add regions/states under countries
     * 
     * @param array $continents
     * @param array $element
     */
    private function addRegionsToCountries(&$continents, $element) {
        for ($i = count($continents); $i--;) {
            for ($j = count($continents[$i]['countries']); $j--;) {
                $countryName = isset(CountryInfos::$countryNames[$element['isoa3']]) ? CountryInfos::$countryNames[$element['isoa3']] : null;
                if (isset($countryName) && ($continents[$i]['countries'][$j]['name'] === $countryName)) {
                    $this->addRegionsToCountry($continents[$i]['countries'][$j], $element);
                    break;
                }
            }
        }
    }
    
    /**
     * Add regions/states under countries
     * 
     * @param array $country
     * @param array $element
     */
    private function addRegionsToCountry(&$country, $element) {
        if (!isset($country['regions'])) {
            $country['regions'] = array();
        }
        $index = -1;
        for ($k = count($country['regions']); $k--;) {
            if (!$element['regionid'] && !isset($country['regions'][$k]['id'])) {
                $index = $k;
                break;
            }
            else if (isset($country['regions'][$k]['id']) && $country['regions'][$k]['id'] === $element['regionid']) {
                $index = $k;
                break;
            }
        }
        
        /*
         * Add region
         */
        if ($index === -1) {
            $this->mergeRegion($country['regions'], $element);
            $index = count($country['regions']) - 1;
        }
        
        /*
         * Add state (and toponyms)
         */
        if (isset($country['regions'][$index]['states'])) {
            $this->mergeState($country['regions'][$index]['states'], $element);
        }
    }
    
    /**
     * Add countries under content
     * 
     * @param array $continents
     * @param array $element
     */
    private function addCountriesToContinents(&$continents, $element) {
        $index = -1;
        for ($i = count($continents); $i--;) {
            if ($continents[$i]['name'] === $element['continent']) {
                $index = $i;
                break;
            }
        }
        if ($index === -1) {
            array_push($continents, array(
                'name' => $element['continent'],
                'id' => 'continent:' . $element['continentid'],
                'countries' => array()
            ));
            $index = count($continents) - 1;
        }
        $area = $this->toSquareKm($element['area']);
        array_push($continents[$index]['countries'], array(
            'name' => $element['name'],
            'id' => 'country:' . $element['id'],
            'pcover' => $this->percentage($area, $this->area),
            'gcover' => $this->percentage($area, $this->toSquareKm($element['entityarea']))
        ));
    }
    
    /**
     * Merge region to country array
     * 
     * @param array $country
     * @param array $element
     */
    private function mergeRegion(&$regions, $element) {
        if (!isset($element['regionid']) || !$element['regionid']) {
            array_push($regions, array(
                'states' => array()
            ));
        }
        else {
            $area = $this->toSquareKm($element['area']);
            array_push($regions, array(
                'name' => $element['region'],
                'id' => 'region:' . $element['regionid'],
                'pcover' => $this->percentage($area, $this->area),
                'gcover' => $this->percentage($area, $this->toSquareKm($element['entityarea'])),
                'states' => array()
            ));
        }
    }

    /**
     * Merge state to region array
     * 
     * @param array $country
     * @param array $element
     */
    private function mergeState(&$states, $element) {
        $area = $this->toSquareKm($element['area']);
        $state = array(
            'name' => $element['state'],
            'id' => 'state:' . $element['stateid'],
            'pcover' => $this->percentage($area, $this->area),
            'gcover' => $this->percentage($area, $this->toSquareKm($element['entityarea']))
        );
        
        if ($this->addToponyms) {
            $state['toponyms'] = $this->getToponyms($element['wkb_geom']);
        }
        
        array_push($states, $state);
    }
    
    /**
     * Add toponyms to political array
     * 
     * @param string $wkb geometry as wkb
     */
    private function getToponyms($wkb) {
        $toponyms = array();
        $codes = $this->addToponyms === 'all' && $this->isValidArea($this->area) ? "('PPL', 'PPLC', 'PPLA', 'PPLA2', 'PPLA3', 'PPLA4', 'STLMT')" : "('PPLA','PPLC')";
        $query = 'SELECT name, longitude, latitude, fcode, population FROM gazetteer.geoname WHERE st_intersects(geom, \'' . $wkb .  '\') AND fcode IN ' . $codes . ' ORDER BY CASE fcode WHEN \'PPLC\' then 1 WHEN \'PPLG\' then 2 WHEN \'PPLA\' then 3 WHEN \'PPLA2\' then 4 WHEN \'PPLA4\' then 5 WHEN \'PPL\' then 6 ELSE 7 END ASC, population DESC';
        $results = $this->query($query);
        while ($result = pg_fetch_assoc($results)) {
            $toponyms[] = array(
                'name' => $result['name'],
                'geo:lon' => (integer) $result['longitude'],
                'geo:lat' => (integer) $result['latitude'],
                'fcode' => $result['fcode'],
                'population' => (integer) $result['population']
            );      
        }
        return $toponyms;
    }
    
}
