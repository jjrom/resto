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
 * 
 * Gazetteer module
 * 
 * Return a list of geographical locations
 * from a string
 * 
 */
class Gazetteer extends RestoModule {
    
    const STATE_PRECISION = 0.1;
    const REGION_PRECISION = 0.1;
    const COUNTRY_PRECISION = 0.3;
    const CONTINENT_PRECISION = 0.5;
    const PHYSICAL_PRECISION = 0.3;
    
    /*
     * List of toponym fields returned
     */
    private $resultFields = array(
        'name',
        'countryname',
        'normalize(countryname) as countrynormalized',
        'latitude',
        'longitude',
        'country as ccode',
        'fclass',
        'fcode',
        'cc2',
        'admin1',
        'admin2',
        'admin3',
        'admin4',
        'population',
        'elevation',
        'gtopo30',
        'timezone',
        'geonameid'
    );

    /*
     * Database handler
     */
    private $dbh;
    
    /*
     * If true output geometry as WKT
     * GeoJSON otherwise
     */
    private $outputAsWKT = false;
    
    /*
     * Results
     */
    private $results = array();
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);        
        $this->dbh = $this->getDatabaseHandler();
    }

    /**
     * Run module - this function should be called by Resto.php
     * 
     * @param array $segments : route segments
     * @param array $data : POST or PUT parameters
     * 
     * @return string : result from run process in the $context->outputFormat
     */
    public function run($segments, $data = array()) {
        
        /*
         * Only GET method on 'search' route with json outputformat is accepted
         */
        if ($this->context->method !== 'GET' || count($segments) !== 0) {
            RestoLogUtil::httpError(404);
        }
        
        return $this->search($this->context->query);
        
    }
    
    /*
     * Search locations from input query
     * 
     * Toponyms return order is :
     *      - fclass priority chain is P, A, the rest 
     *      - for 'P', fcode priority chain is PPLC, PPLG, PPLA, PPLA2, PPLA3, PPLA4, PPL, the rest
     *
     * (See http://www.geonames.org/export/codes.html for class and code explanation)
     * 
     * 
     * Query structure :
     * 
     *    array(
     *      'q' => // location to search form (e.g. Paris or Paris, France) - MANDATORY
     *      'type' => // force search type (i.e. 'toponym, country or state) - OPTIONAL
     *      'bbox' => // bounding box to restrict the search on - OPTIONAL
     *      'wkt' => // if true return geometry as wkt - OPTIONAL
     *    )
     * Gazetteer tables format :
     * 
     *  CREATE TABLE geoname (
     *      geonameid   int,
     *      name varchar(200),
     *      asciiname varchar(200),
     *      alternatenames varchar(8000),
     *      latitude float,
     *      longitude float,
     *      fclass char(1),
     *      fcode varchar(10),
     *      country varchar(2),
     *      cc2 varchar(60),
     *      admin1 varchar(20),
     *      admin2 varchar(80),
     *      admin3 varchar(20),
     *      admin4 varchar(20),
     *      population bigint,
     *      elevation int,
     *      gtopo30 int,
     *      timezone varchar(40),
     *      moddate date,
     *      geom
     *  );
     * 
     * @param array $query
     * @param boolean $normalize
     * @return array
     * 
     */
    public function search($params, $normalize = true) {
        
        if (!$this->dbh || !isset($params) || !isset($params['q'])) {
            return RestoLogUtil::httpError(400);
        }
        
        /*
         * Set output type - GeoJSON (default) or WKT
         */
        $this->outputAsWKT = isset($params['wkt']) ? filter_var($params['wkt'], FILTER_VALIDATE_BOOLEAN) : false;
        
        /*
         * Remove accents from query and split it into 'toponym' and 'modifier'
         */
        $query = $this->splitQuery($normalize ? $this->context->dbDriver->normalize($params['q']) : $params['q']);
        
        /*
         * Limit search to input type
         */
        $type = isset($params['type']) ? $params['type'] : null;
        switch ($type) {
            
            /*
             * State only
             */
            case 'state':
                $this->results = $this->getStates($query['toponym'], Gazetteer::STATE_PRECISION);
                break;
            /*
             * Region only
             */
            case 'region':
                $this->results = $this->getRegions($query['toponym'], Gazetteer::REGION_PRECISION);
                break;
            /*
             * Country only
             */
            case 'country':
                $this->results = $this->getCountries($query['toponym'], Gazetteer::COUNTRY_PRECISION);
                break;
            /*
             * Continent only
             */
            case 'continent':
                $this->results = $this->getContinents($query['toponym'], Gazetteer::CONTINENT_PRECISION);
                break;
            /*
             * Physical only
             */
            case 'physical':
                $this->results = $this->getPhysical($query['toponym'], Gazetteer::PHYSICAL_PRECISION);
                break;
            /*
             * Search for all
             */
            default:
                $this->results = $this->getToponyms($query['toponym'], array(
                    'bbox' => isset($params['bbox']) ? $params['bbox'] : null,
                    'modifier' => isset($query['modifier']) ? $query['modifier'] : null
                ));
                if (!isset($query['modifier'])) {
                    $this->results = array_merge($this->results, $this->getContinents($query['toponym'], Gazetteer::CONTINENT_PRECISION));
                    $this->results = array_merge($this->results, $this->getCountries($query['toponym'], Gazetteer::COUNTRY_PRECISION));
                    $this->results = array_merge($this->results, $this->getRegions($query['toponym'], Gazetteer::REGION_PRECISION));
                    $this->results = array_merge($this->results, $this->getStates($query['toponym'], Gazetteer::STATE_PRECISION));
                    $this->results = array_merge($this->results, $this->getPhysical($query['toponym'], Gazetteer::PHYSICAL_PRECISION));
                }
        }
        
        /*
         * Close database handler
         */
        if ($this->closeDbh) {
            pg_close($this->dbh);
        }
        
        return RestoLogUtil::success(count($this->results) . ' toponym(s) found', array(
            'query' => $params['q'],
            'lang' => $this->context->dictionary->language,
            'results' => $this->results
        ));
    }
    
    /**
     * Search for cities in iTag gazetteer database
     * 
     * @param string $name
     * @param array $constraints
     */
    private function getToponyms($name, $constraints) {
        
        $toponyms = array();
        
        /*
         * Search in native language within alternatename table
         */
        if ($this->context->dictionary->language !== 'en') {
            $toponyms = $this->queryToponyms($name, $constraints, $this->context->dictionary->language);
        }
        
        /*
         * Always search in english
         */
        $englishToponyms = $this->queryToponyms($name, $constraints, 'en');
        foreach ($englishToponyms as $geonameid => $value) {
            if (!isset($toponyms[$geonameid])) {
                $toponyms[$geonameid] = $value;
            }
        }
        
        return array_values($toponyms);
    }
    
    /**
     * Search for continents
     * 
     * @param string $name
     * @param float $tolerance (tolerance for polygon simplification in degrees)
     */
    private function getContinents($name, $tolerance = 0) {
        return $this->getCCR($name, 'continent', $tolerance);
    }
    
    /**
     * Search for countries
     * 
     * @param string $name
     * @param float $tolerance (tolerance for polygon simplification in degrees)
     */
    private function getCountries($name, $tolerance = 0) {
        return $this->getCCR($name, 'country', $tolerance);
    }
    
    /**
     * Search for regions
     * 
     * @param string $name
     * @param float $tolerance (tolerance for polygon simplification in degrees)
     */
    private function getRegions($name, $tolerance = 0) {
        return $this->getCCR($name, 'region', $tolerance);
    }
    
    /**
     * Search for Administrative Level 1 stored in iTag database
     * 
     * (i.e. States for US, "Département" for France, etc.)
     * 
     * @param string $name
     * @param float $tolerance (tolerance for polygon simplification in degrees)
     */
    private function getStates($name, $tolerance = 0) {
        $output = array();
        $state = $this->context->dictionary->getKeyword($name, array(
            RestoDictionary::STATE
        ));
        if (isset($state)) {
            $query = 'SELECT name, normalize(name) as stateid, region, normalize(region) as regionid, admin, normalize(admin) as adminid, ' . $this->getFormatFunction() . '(' . $this->simplify('geom', $tolerance, true) . ') as geometry FROM datasources.states WHERE normalize(name)=normalize(\'' . $state['keyword'] . '\') order by name';
            $results = pg_query($this->dbh, $query);
            while ($row = pg_fetch_assoc($results)) {
                $output[] = array(
                    'name' => $this->context->dictionary->getKeywordFromValue($row['stateid'], 'state'),
                    'type' => 'state',
                    'region' => $row['region'],
                    'country' => $row['admin'],
                    'searchTerms' => 'state:' . $row['stateid'],
                    'geo:geometry' => $this->outputAsWKT ? $row['geometry'] : json_decode($row['geometry'], true)
                );
            }
        }
        return $output;
    }
    
    /**
     * Search for physical features
     * 
     * @param string $name
     * @param float $tolerance (tolerance for polygon simplification in degrees)
     */
    private function getPhysical($name, $tolerance = 0) {
        
        $keyword = $this->context->dictionary->getKeyword($name, RestoDictionary::getPhysicals());
        $query = 'SELECT name, featurecla as type, normalize(name) as normalized, ' . $this->getFormatFunction() . '(' . $this->simplify('geom', $tolerance, true) . ') as geometry FROM datasources.physical WHERE normalize(name)=normalize(\'' . $keyword['keyword'] . '\') order by name';
        $output = array();
        if (isset($keyword)) {
            $results = pg_query($this->dbh, $query);
            while ($row = pg_fetch_assoc($results)) {
                $output[] = array(
                    'name' => $this->context->dictionary->getKeywordFromValue($row['normalized'], $row['type']),
                    'type' => $row['type'],
                    'searchTerms' => $row['type'] . ':' . $row['normalized'],
                    'geo:geometry' => $this->outputAsWKT ? $row['geometry'] : json_decode($row['geometry'], true)
                );
            }
        }
        
        return $output;
    }
    
    /**
     * Launch database query
     * 
     * @param string $name
     * @param array $constraints
     * @param string $lang
     * @return array
     */
    private function queryToponyms($name, $constraints, $lang) {
        $toponyms = array();
        $results = pg_query($this->dbh, 'SELECT ' . join(',', $this->resultFields) . ' FROM gazetteer.geoname WHERE ' . join(' AND ', $this->getToponymsFilters($constraints, $name, $lang)) . ' ORDER BY CASE fcode WHEN \'PPLC\' then 1 WHEN \'PPLG\' then 2 WHEN \'PPLA\' then 3 WHEN \'PPLA2\' then 4 WHEN \'PPLA4\' then 5 WHEN \'PPL\' then 6 ELSE 7 END ASC, population DESC' . ($lang === 'en' ? ' LIMIT 30' : ''));
        while ($toponym = pg_fetch_assoc($results)) {
            if ($this->context->dictionary->language !== 'en') {
                $toponym['countryname'] = $this->context->dictionary->getKeywordFromValue($toponym['countrynormalized'], 'country');
            }
            $toponyms[$toponym['geonameid']] = array(
                'name' => $toponym['name'],
                'type' => 'toponym',
                'country' => $toponym['countryname'],
                'geo:lon' => (float) $toponym['longitude'],
                'geo:lat' => (float) $toponym['latitude'],
                'ccode' => $toponym['ccode'],
                'fcode' => $toponym['fcode'],
                'admin1' => $toponym['admin1'],
                'admin2' => $toponym['admin2'],
                'population' => (float) $toponym['population'],
                'elevation' => (float) $toponym['elevation'],
                'gtopo30' => $toponym['gtopo30'],
                'timezone' => $toponym['timezone']
            );
        }
        return $toponyms;
    }
    
    /**
     * Return filter PostGIS filter on bounding box
     * 
     * Input coordinates are in longitude/latitude (WGS84) ordered as follow
     * 
     *  array(lonMin, latMin, lonMax, latMax)
     * 
     * 
     * @param array $coords
     */
    private function getBBOXFilter($coords) {
        
        /*
         * Invalid coordinates
         */
        if (!is_array($coords) || count($coords) !== 4) {
            RestoLogUtil::httpError(400, 'Invalid bbox');
        }
        
        /*
         * Non numeric coordinates
         */
        for ($i = 4; $i--;) {
            if (!is_numeric($coords[$i])) {
                RestoLogUtil::httpError(400, 'Invalid bbox');
            }
        }
        
        return 'ST_intersects(geom, ST_GeomFromText(\'' . pg_escape_string('POLYGON((' . $coords[0] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[3] . ',' . $coords[2] . ' ' . $coords[1] . ',' . $coords[0] . ' ' . $coords[1] . '))') . '\', 4326))';
        
    }

    /**
     * Embed geometry column within PostGIS ST_SimplifyPreserveTopology function if needed
     * 
     * @param string $geometryColumn
     * @param float $tolerance (tolerance for polygon simplification in degrees)
     * @param booelan $preserveTopology
     * @return string
     */
    private function simplify($geometryColumn, $tolerance, $preserveTopology) {
        return $tolerance > 0 ? 'ST_Simplify' . ($preserveTopology ? 'PreserveTopology' : '') . '(' . $geometryColumn . ',' . $tolerance . ')' : $geometryColumn;
    }
    
    /**
     * Return " LIKE " if last character of $name is '%' and length is at least 4 characters 
     * Return "=" otherwise
     * 
     * @param string $name
     */
    private function likeOrEqual($name) {
        if (substr($name, -1) === '%') {
            if (isset($name{4})) {
                return ' LIKE ';
            }
            else {
                RestoLogUtil::httpError(400);
            }
        }
        return '=';
    }
 
    /**
     * Return array of search filters for toponyms
     * 
     * @param array $constraints
     * @param string $name
     * @param string $lang
     */
    private function getToponymsFilters($constraints, $name, $lang) {
        
        $where = array(
            'fclass=\'P\''
        );
        
        /*
         * Constrain search on country name or state
         */
        if (isset($constraints['modifier'])) {
            $modifierFilter = $this->getModifierFilter($constraints['modifier']);
            if (isset($modifierFilter)) {
                $where[] = $modifierFilter;
            }
        }
        
        /*
         * Bounding box filters
         */
        if (isset($constraints['bbox'])) {
            $where[] = $this->getBBOXFilter(explode(',', $constraints['bbox']));
        }
        
        /*
         * Lang filter
         */
        if ($lang !== 'en') {
            $where[] = 'geonameid = ANY((SELECT array(SELECT geonameid FROM gazetteer.alternatename WHERE normalize(alternatename)' . $this->likeOrEqual($name) . 'normalize(\'' . pg_escape_string($name) . '\')  AND isolanguage=\'' . $lang . '\' LIMIT 30))::integer[])';
        }
        else {
            $where[] = 'normalize(name)' . $this->likeOrEqual($name) . 'normalize(\'' . pg_escape_string($name) . '\')';
        }
       
        return $where;
    }
    
    /**
     * Split query "Toponym, modifier" into array('toponym' => ..., 'modifier' => ...)
     * 
     * @param string $query
     */
    private function splitQuery($query) {
        $splitted = explode(',', $query);
        if (count($splitted) > 1) {
            return array(
                'toponym' => str_replace(' ', '-', trim($splitted[0])),
                'modifier' => str_replace(' ', '-', trim($splitted[1]))
            );
        }  
        return array(
            'toponym' => str_replace(' ', '-', trim($query))
        );
    }
    
    /**
     * Return filter modifier
     * @param string $name
     * @return string
     */
    private function getModifierFilter($name) {
        $countryRegionOrState = $this->context->dictionary->getKeyword($name, array(
            RestoDictionary::COUNTRY,
            RestoDictionary::REGION,
            RestoDictionary::STATE
        ));
        if (isset($countryRegionOrState)) {
            if ($countryRegionOrState['type'] === 'country') {
                return 'normalize(countryname)=normalize(\'' . pg_escape_string($countryRegionOrState['keyword']) . '\')';
            }
            else if (isset($countryRegionOrState['bbox'])) {
                return $this->getBBOXFilter(explode(',', $countryRegionOrState['bbox']));
            }
        }
        return null;
    }
    
    /**
     * Return PostGIS format function
     */
    private function getFormatFunction() {
        return $this->outputAsWKT ? 'ST_AsText' : 'ST_AsGeoJSON';
    }
    
    /**
     * Search for continents, countries or regions
     * 
     * @param string $name
     * @param string $type
     * @param float $tolerance (tolerance for polygon simplification in degrees)
     */
    private function getCCR($name, $type, $tolerance = 0) {
        
        switch ($type) {
            case 'region':
                $keyword = $this->context->dictionary->getKeyword($name, array(
                    RestoDictionary::REGION
                ));
                $query = 'SELECT name, normalize(name) as normalized, ' . $this->getFormatFunction() . '(' . $this->simplify('geom', $tolerance, true) . ') as geometry FROM datasources.regions WHERE normalize(name)=normalize(\'' . $keyword['keyword'] . '\') order by name';
                break;
            case 'country':
                $keyword = $this->context->dictionary->getKeyword($name, array(
                    RestoDictionary::COUNTRY
                ));
                $query = 'SELECT name, normalize(name) as normalized, continent, ' . $this->getFormatFunction() . '(' . $this->simplify('geom', $tolerance, true) . ') as geometry FROM datasources.countries WHERE normalize(name)=normalize(\'' . $keyword['keyword'] . '\') order by admin';
                break;
            default:
                $keyword = $this->context->dictionary->getKeyword($name, array(
                    RestoDictionary::CONTINENT
                ));
                $query = 'SELECT continent, normalize(continent) as normalized, ' . $this->getFormatFunction() . '(' . $this->simplify('geom', $tolerance, false) . ') as geometry FROM datasources.continents WHERE normalize(continent)=normalize(\'' . $keyword['keyword'] . '\')';
            }
        
        $output = array();
        if (isset($keyword)) {
            $results = pg_query($this->dbh, $query);
            while ($row = pg_fetch_assoc($results)) {
                $output[] = array(
                    'name' => $this->context->dictionary->getKeywordFromValue($row['normalized'], $type),
                    'type' => $type,
                    'searchTerms' => $type . ':' . $row['normalized'],
                    'geo:geometry' => $this->outputAsWKT ? $row['geometry'] : json_decode($row['geometry'], true)
                );
            }
        }
        
        return $output;
    }
}