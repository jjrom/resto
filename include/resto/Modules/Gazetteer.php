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
 * 
 * Gazetteer module
 * 
 * Return a list of geographical locations
 * from a string
 * 
 */
class Gazetteer extends RestoModule {
    
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
     * @param array $params : input parameters
     * @param array $data : POST or PUT parameters
     * 
     * @return string : result from run process in the $context->outputFormat
     */
    public function run($params) {
       
        /*
         * Only GET method on 'search' route with json outputformat is accepted
         */
        if ($this->context->method !== 'GET' || count($params) !== 0) {
            RestoLogUtil::httpError(404);
        }
        
        return $this->search($this->context->query);
        
    }
    
    /*
     * Search locations from input query
     * 
     * Toponyms return order is :
     *      - fclass priority chain is P, A, the rest 
     *      - for 'P', fcode priority chain is PPLC, PPLA, PPLA2, PPLA3, PPLA4, PPL, the rest
     *
     * (See http://www.geonames.org/export/codes.html for class and code explanation)
     * 
     * 
     * Query structure :
     * 
     *    array(
     *      'q' => // location to search form (e.g. Paris or Paris, France) - MANDATORY
     *      'country' => // country to restrict the search on (e.g 'France', 'Texas') - OPTIONAL
     *      'state' => // state to restrict the search on (e.g 'Texas') - OPTIONAL
     *      'bbox' => // bounding box to restrict the search on - OPTIONAL
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
     * @return array
     * 
     */
    public function search($params = array()) {
        
        if (!$this->dbh || !is_array($params) || empty($params['q'])) {
            return RestoLogUtil::httpError(400);
        }
        
        /*
         * Remove accents from query and split it into 'toponym' and 'modifier'
         */
        $query = $this->splitQuery($this->context->dbDriver->normalize($params['q']));
       
        /*
         * Search for cities
         */
        $countries = array();
        $states = array();
        $toponyms = $this->getToponyms($query['toponym'], array(
            'bbox' => isset($params['bbox']) ? $params['bbox'] : null,
            'modifier' => isset($query['modifier']) ? $query['modifier'] : null
        ));
        
        /*
         * "Toponym" search only => search also for states and countries
         */
        if (!isset($query['modifier'])) {
            $countries = $this->getCountries($query['toponym'], 0.1);
            $states = $this->getStates($query['toponym'], 0.1);
        }
        
        if (empty($toponyms) && empty($countries) && empty($states)) {
            return RestoLogUtil::success('No toponym found for "' . (isset($params['q']) ? $params['q'] : '') . '"');
        }
        
        return array(
            'toponyms' => $toponyms,
            'states' => $states,
            'countries' => $countries
        );
        
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
     * Search for countries
     * 
     * @param string $name
     * @param float $tolerance (tolerance for polygon simplification in degrees)
     */
    private function getCountries($name, $tolerance = 0) {
        $output = array();
        $country = $this->context->dictionary->getKeyword($name);
        if (isset($country)) {
            $query = 'SELECT admin, normalize(admin) as countryid, continent, ST_AsGeoJSON(' . $this->simplify('geom', $tolerance) . ') as geometry FROM datasources.countries WHERE normalize(admin)=\'' . $country['keyword'] . '\' order by admin';
            $results = pg_query($this->dbh, $query);
            while ($row = pg_fetch_assoc($results)) {
                $output[] = array(
                    'name' => $this->context->dictionary->getKeywordFromValue($row['countryid'], 'country'),
                    'type' => 'country',
                    'geometry' => json_decode($row['geometry'], true)
                );
            }
        }
        return $output;
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
        $state = $this->context->dictionary->getKeyword($name);
        if (isset($state)) {
            $query = 'SELECT name, normalize(name) as stateid, region, normalize(region) as regionid, admin, normalize(admin) as adminid, ST_AsGeoJSON(' . $this->simplify('geom', $tolerance) . ') as geometry FROM datasources.worldadm1level WHERE normalize(name)=\'' . $state['keyword'] . '\' order by name';
            $results = pg_query($this->dbh, $query);
            while ($row = pg_fetch_assoc($results)) {
                $output[] = array(
                    'name' => $this->context->dictionary->getKeywordFromValue($row['stateid'], 'state'),
                    'type' => 'state',
                    'region' => $row['region'],
                    'country' => $row['admin'],
                    'geometry' => json_decode($row['geometry'], true)
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
        $results = pg_query($this->dbh, 'SELECT ' . join(',', $this->resultFields) . ' FROM gazetteer.geoname WHERE ' . join(' AND ', $this->getToponymsFilters($constraints, $name, $lang)) . ' ORDER BY CASE fcode WHEN \'PPLC\' then 1 WHEN \'PPLA\' then 2 WHEN \'PPLA2\' then 3 WHEN \'PPLA4\' then 4 WHEN \'PPL\' then 5 ELSE 6 END ASC, population DESC' . ($lang === 'en' ? ' LIMIT 30' : ''));
        while ($toponym = pg_fetch_assoc($results)) {
            if ($this->context->dictionary->language !== 'en') {
                $toponym['countryname'] = $this->context->dictionary->getKeywordFromValue($toponym['countrynormalized'], 'country');
            }
            $toponyms[$toponym['geonameid']] = array(
                'name' => $toponym['name'],
                'countryname' => $toponym['countryname'],
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array((float) $toponym['longitude'], (float) $toponym['latitude'])
                ),
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
     * @return string
     */
    private function simplify($geometryColumn, $tolerance) {
        return $tolerance > 0 ? 'ST_SimplifyPreserveTopology(' . $geometryColumn . ',' . $tolerance . ')' : $geometryColumn;
    }
    
    /**
     * Return " LIKE " if last character of $name is '%' and length is at least 4 characters 
     * Return "=" otherwise
     * 
     * @param string $name
     */
    private function likeOrEqual($name) {
        if (substr($name, -1) === '%') {
            if (strlen($name) > 3) {
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
            $where[] = 'geonameid = ANY((SELECT array(SELECT geonameid FROM gazetteer.alternatename WHERE normalize(alternatename)' . $this->likeOrEqual($name) . '\'' . pg_escape_string($name) . '\'  AND isolanguage=\'' . $lang . '\' LIMIT 30))::integer[])';
        }
        else {
            $where[] = 'normalize(name)' . $this->likeOrEqual($name) . '\'' . pg_escape_string($name) . '\'';
        }
       
        return $where;
    }
    
    /**
     * Split query "Toponym, modifier" into array('toponym' => ..., 'modifier' => ...)
     * 
     * @param string $query
     */
    private function splitQuery($query) {
        $output = array(
            'toponym' => trim($query),
            'modifier' => null
        ); 
        $splitted = explode(',', $query);
        if (count($splitted) > 1) {
            $output = array(
                'toponym' => trim($splitted[0]),
                'modifier' => trim($splitted[1])
            );
        }  
        return $output;
    }
    
    /**
     * Return filter modifier
     * @param string $name
     * @return string
     */
    private function getModifierFilter($name) {
        $countryOrState = $this->context->dictionary->getKeyword($name);
        if (isset($countryOrState)) {
            if ($countryOrState['type'] === 'country') {
                return 'normalize(countryname)=\'' . pg_escape_string($countryOrState['keyword']) . '\'';
            }
            else if (isset($countryOrState['bbox'])) {
                return $this->getBBOXFilter(explode(',', $countryOrState['bbox']));
            }
        }
        return null;
    }
    
}