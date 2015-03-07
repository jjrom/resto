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
     * List of countries extract from Gazetteer database
     */
    private $countries = array(
        'afghanistan' => 'AF',
        'aland islands' => 'AX',
        'albania' => 'AL',
        'algeria' => 'DZ',
        'american samoa' => 'AS',
        'andorra' => 'AD',
        'angola' => 'AO',
        'anguilla' => 'AI',
        'antarctica' => 'AQ',
        'antigua and barbuda' => 'AG',
        'argentina' => 'AR',
        'armenia' => 'AM',
        'aruba' => 'AW',
        'australia' => 'AU',
        'austria' => 'AT',
        'azerbaijan' => 'AZ',
        'bahamas' => 'BS',
        'bahrain' => 'BH',
        'bangladesh' => 'BD',
        'barbados' => 'BB',
        'belarus' => 'BY',
        'belgium' => 'BE',
        'belize' => 'BZ',
        'benin' => 'BJ',
        'bermuda' => 'BM',
        'bhutan' => 'BT',
        'bolivia' => 'BO',
        'bonaire, saint eustatius and saba ' => 'BQ',
        'bosnia and herzegovina' => 'BA',
        'botswana' => 'BW',
        'bouvet island' => 'BV',
        'brazil' => 'BR',
        'british indian ocean territory' => 'IO',
        'british virgin islands' => 'VG',
        'brunei' => 'BN',
        'bulgaria' => 'BG',
        'burkina faso' => 'BF',
        'burundi' => 'BI',
        'cambodia' => 'KH',
        'cameroon' => 'CM',
        'canada' => 'CA',
        'cape verde' => 'CV',
        'cayman islands' => 'KY',
        'central african republic' => 'CF',
        'chad' => 'TD',
        'chile' => 'CL',
        'china' => 'CN',
        'christmas island' => 'CX',
        'cocos islands' => 'CC',
        'colombia' => 'CO',
        'comoros' => 'KM',
        'cook islands' => 'CK',
        'costa rica' => 'CR',
        'croatia' => 'HR',
        'cuba' => 'CU',
        'curacao' => 'CW',
        'cyprus' => 'CY',
        'czech republic' => 'CZ',
        'democratic republic of the congo' => 'CD',
        'denmark' => 'DK',
        'djibouti' => 'DJ',
        'dominica' => 'DM',
        'dominican republic' => 'DO',
        'east timor' => 'TL',
        'ecuador' => 'EC',
        'egypt' => 'EG',
        'el salvador' => 'SV',
        'equatorial guinea' => 'GQ',
        'eritrea' => 'ER',
        'estonia' => 'EE',
        'ethiopia' => 'ET',
        'falkland islands' => 'FK',
        'faroe islands' => 'FO',
        'fiji' => 'FJ',
        'finland' => 'FI',
        'france' => 'FR',
        'french guiana' => 'GF',
        'french polynesia' => 'PF',
        'french southern territories' => 'TF',
        'gabon' => 'GA',
        'gambia' => 'GM',
        'georgia' => 'GE',
        'germany' => 'DE',
        'ghana' => 'GH',
        'gibraltar' => 'GI',
        'greece' => 'GR',
        'greenland' => 'GL',
        'grenada' => 'GD',
        'guadeloupe' => 'GP',
        'guam' => 'GU',
        'guatemala' => 'GT',
        'guernsey' => 'GG',
        'guinea' => 'GN',
        'guinea-bissau' => 'GW',
        'guyana' => 'GY',
        'haiti' => 'HT',
        'heard island and mcdonald islands' => 'HM',
        'honduras' => 'HN',
        'hong kong' => 'HK',
        'hungary' => 'HU',
        'iceland' => 'IS',
        'india' => 'IN',
        'indonesia' => 'ID',
        'iran' => 'IR',
        'iraq' => 'IQ',
        'ireland' => 'IE',
        'isle of man' => 'IM',
        'israel' => 'IL',
        'italy' => 'IT',
        'ivory coast' => 'CI',
        'jamaica' => 'JM',
        'japan' => 'JP',
        'jersey' => 'JE',
        'jordan' => 'JO',
        'kazakhstan' => 'KZ',
        'kenya' => 'KE',
        'kiribati' => 'KI',
        'kosovo' => 'XK',
        'kuwait' => 'KW',
        'kyrgyzstan' => 'KG',
        'laos' => 'LA',
        'latvia' => 'LV',
        'lebanon' => 'LB',
        'lesotho' => 'LS',
        'liberia' => 'LR',
        'libya' => 'LY',
        'liechtenstein' => 'LI',
        'lithuania' => 'LT',
        'luxembourg' => 'LU',
        'macao' => 'MO',
        'macedonia' => 'MK',
        'madagascar' => 'MG',
        'malawi' => 'MW',
        'malaysia' => 'MY',
        'maldives' => 'MV',
        'mali' => 'ML',
        'malta' => 'MT',
        'marshall islands' => 'MH',
        'martinique' => 'MQ',
        'mauritania' => 'MR',
        'mauritius' => 'MU',
        'mayotte' => 'YT',
        'mexico' => 'MX',
        'micronesia' => 'FM',
        'moldova' => 'MD',
        'monaco' => 'MC',
        'mongolia' => 'MN',
        'montenegro' => 'ME',
        'montserrat' => 'MS',
        'morocco' => 'MA',
        'mozambique' => 'MZ',
        'myanmar' => 'MM',
        'namibia' => 'NA',
        'nauru' => 'NR',
        'nepal' => 'NP',
        'netherlands' => 'NL',
        'netherlands antilles' => 'AN',
        'new caledonia' => 'NC',
        'new zealand' => 'NZ',
        'nicaragua' => 'NI',
        'niger' => 'NE',
        'nigeria' => 'NG',
        'niue' => 'NU',
        'norfolk island' => 'NF',
        'north korea' => 'KP',
        'northern mariana islands' => 'MP',
        'norway' => 'NO',
        'oman' => 'OM',
        'pakistan' => 'PK',
        'palau' => 'PW',
        'palestinian territory' => 'PS',
        'panama' => 'PA',
        'papua new guinea' => 'PG',
        'paraguay' => 'PY',
        'peru' => 'PE',
        'philippines' => 'PH',
        'pitcairn' => 'PN',
        'poland' => 'PL',
        'portugal' => 'PT',
        'puerto rico' => 'PR',
        'qatar' => 'QA',
        'republic of the congo' => 'CG',
        'reunion' => 'RE',
        'romania' => 'RO',
        'russia' => 'RU',
        'rwanda' => 'RW',
        'saint barthelemy' => 'BL',
        'saint helena' => 'SH',
        'saint kitts and nevis' => 'KN',
        'saint lucia' => 'LC',
        'saint martin' => 'MF',
        'saint pierre and miquelon' => 'PM',
        'saint vincent and the grenadines' => 'VC',
        'samoa' => 'WS',
        'san marino' => 'SM',
        'sao tome and principe' => 'ST',
        'saudi arabia' => 'SA',
        'senegal' => 'SN',
        'serbia' => 'RS',
        'serbia and montenegro' => 'CS',
        'seychelles' => 'SC',
        'sierra leone' => 'SL',
        'singapore' => 'SG',
        'sint maarten' => 'SX',
        'slovakia' => 'SK',
        'slovenia' => 'SI',
        'solomon islands' => 'SB',
        'somalia' => 'SO',
        'south africa' => 'ZA',
        'south georgia and the south sandwich islands' => 'GS',
        'south korea' => 'KR',
        'south sudan' => 'SS',
        'spain' => 'ES',
        'sri lanka' => 'LK',
        'sudan' => 'SD',
        'suriname' => 'SR',
        'svalbard and jan mayen' => 'SJ',
        'swaziland' => 'SZ',
        'sweden' => 'SE',
        'switzerland' => 'CH',
        'syria' => 'SY',
        'taiwan' => 'TW',
        'tajikistan' => 'TJ',
        'tanzania' => 'TZ',
        'thailand' => 'TH',
        'togo' => 'TG',
        'tokelau' => 'TK',
        'tonga' => 'TO',
        'trinidad and tobago' => 'TT',
        'tunisia' => 'TN',
        'turkey' => 'TR',
        'turkmenistan' => 'TM',
        'turks and caicos islands' => 'TC',
        'tuvalu' => 'TV',
        'u.s. virgin islands' => 'VI',
        'uganda' => 'UG',
        'ukraine' => 'UA',
        'united arab emirates' => 'AE',
        'united kingdom' => 'GB',
        'united states' => 'US',
        'united states minor outlying islands' => 'UM',
        'uruguay' => 'UY',
        'uzbekistan' => 'UZ',
        'vanuatu' => 'VU',
        'vatican' => 'VA',
        'venezuela' => 'VE',
        'vietnam' => 'VN',
        'wallis and futuna' => 'WF',
        'western sahara' => 'EH',
        'yemen' => 'YE',
        'zambia' => 'ZM',
        'zimbabwe' => 'ZW'
    );

    /*
     * Database handler
     */
    private $dbh;
    
    /*
     * iTag Gazetteer schema name
     */
    private $toponymsSchema = 'gazetteer';
    
    
    /*
     * iTag modifiers schema name
     * (i.e. schema containing other toponyms) 
     */
    private $modifiersSchema = 'datasources';
    
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
        $query = 'SELECT admin, continent, ST_AsGeoJSON(' . $this->simplify('geom', $tolerance) . ') as geometry FROM ' . $this->modifiersSchema . '.countries WHERE lower(unaccent(admin))=lower(unaccent(\'' . $name . '\')) order by admin';
        $results = pg_query($this->dbh, $query);
        while ($row = pg_fetch_assoc($results)) {
            $output[] = array(
                'name' => $row['admin'],
                'type' => 'country',
                'geometry' => json_decode($row['geometry'], true)
            );
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
        $query = 'SELECT name, lower(unaccent(name)) as stateid, region, lower(unaccent(region)) as regionid, admin, lower(unaccent(admin)) as adminid, ST_AsGeoJSON(' . $this->simplify('geom', $tolerance) . ') as geometry FROM ' . $this->modifiersSchema . '.worldadm1level WHERE lower(unaccent(name))=lower(unaccent(\'' . $name . '\')) order by name';
        $results = pg_query($this->dbh, $query);
        while ($row = pg_fetch_assoc($results)) {
            $output[] = array(
                'name' => $row['name'],
                'type' => 'state',
                'region' => $row['region'],
                'country' => $row['admin'],
                'geometry' => json_decode($row['geometry'], true)
            );
        }
        return $output;
    }
    
    /**
     * Return country code for a given country
     * 
     * @param string $countryName
     */
    private function getCountryCode($countryName) {
        
        if (!isset($countryName)) {
            return null;
        }
        
        return isset($this->countries[strtolower($countryName)]) ? $this->countries[strtolower($countryName)] : null;
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
        $results = pg_query($this->dbh, 'SELECT ' . join(',', $this->resultFields) . ' FROM ' . $this->toponymsSchema . '.geoname WHERE ' . join(' AND ', $this->getToponymsFilters($constraints, $name, $lang)) . ' ORDER BY CASE fcode WHEN \'PPLC\' then 1 WHEN \'PPLA\' then 2 WHEN \'PPLA2\' then 3 WHEN \'PPLA4\' then 4 WHEN \'PPL\' then 5 ELSE 6 END ASC, population DESC' . ($lang === 'en' ? ' LIMIT 30' : ''));
        while ($toponym = pg_fetch_assoc($results)) {
            if ($this->context->dictionary->language !== 'en') {
                $toponym['countryname'] = $this->context->dictionary->getKeywordFromValue(array_search($toponym['ccode'], $this->countries), 'country');
            }
            $toponyms[$toponym['geonameid']] = $toponym;
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
     * Return " LIKE " if last character of $name is '%' and length is at least 3 characters 
     * Return "=" otherwise
     * 
     * @param string $name
     */
    private function likeOrEqual($name) {
        if (substr($name, -1) === '%') {
            if (strlen($name) > 2) {
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
            $where[] = 'geonameid = ANY((SELECT array(SELECT geonameid FROM ' . $this->toponymsSchema . '.alternatename WHERE lower(unaccent(alternatename))' . $this->likeOrEqual($name) . '\'' . pg_escape_string($name) . '\'  AND isolanguage=\'' . $lang . '\' LIMIT 30))::integer[])';
        }
        else {
            $where[] = 'lower(unaccent(name))' . $this->likeOrEqual($name) . '\'' . pg_escape_string($name) . '\'';
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
                $code = $this->getCountryCode($countryOrState['keyword']);
                if (isset($code)) {
                    return 'country =\'' . pg_escape_string($code) . '\'';
                }
            }
            else if (isset($countryOrState['bbox'])) {
                return $this->getBBOXFilter(explode(',', $countryOrState['bbox']));
            }
        }
        return null;
    }
    
}