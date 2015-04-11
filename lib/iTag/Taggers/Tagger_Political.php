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

class Tagger_Political extends Tagger {
    
    private $countryNames = array(
        'AD' => 'Andorra',
        'AND' => 'Andorra',
        'AF' => 'Afghanistan',
        'AFG' => 'Afghanistan',
        'AG' => 'Antigua and Barbuda',
        'AI' => 'Anguilla',
        'AL' => 'Albania',
        'ALB' => 'Albania',
        'AN' => 'Netherlands Antilles',
        'AO' => 'Angola',
        'AGO' => 'Angola',
        'AQ' => 'Antarctica',
        'AR' => 'Argentina',
        'AE' => 'United Arab Emirates',
        'ARE' => 'United Arab Emirates',
        'ARG' => 'Argentina',
        'AM' => 'Armenia',
        'ARM' => 'Armenia',
        'AS' => 'American Samoa',
        'AT' => 'Austria',
        'ATA' => 'Antarctica',
        'ATF' => 'French Southern and Antarctic Lands',
        'AU' => 'Australia',
        'AUS' => 'Australia',
        'AUT' => 'Austria',
        'AW' => 'Aruba',
        'AX' => 'Aland Islands',
        'AZ' => 'Azerbaijan',
        'AZE' => 'Azerbaijan',
        'BA' => 'Bosnia and Herzegovina',
        'BB' => 'Barbados',
        'BD' => 'Bangladesh',
        'BDI' => 'Burundi',
        'BE' => 'Belgium',
        'BEL' => 'Belgium',
        'BEN' => 'Benin',
        'BF' => 'Burkina Faso',
        'BFA' => 'Burkina Faso',
        'BG' => 'Bulgaria',
        'BGD' => 'Bangladesh',
        'BGR' => 'Bulgaria',
        'BH' => 'Bahrain',
        'BHR' => 'Bahrain',
        'BHS' => 'Bahamas',
        'BI' => 'Burundi',
        'BIH' => 'Bosnia and Herzegovina',
        'BJ' => 'Benin',
        'BL' => 'Saint Barthelemy',
        'BLR' => 'Belarus',
        'BLZ' => 'Belize',
        'BM' => 'Bermuda',
        'BN' => 'Brunei',
        'BO' => 'Bolivia',
        'BOL' => 'Bolivia',
        'BQ' => 'Bonaire, Saint Eustatius and Saba ',
        'BR' => 'Brazil',
        'BRA' => 'Brazil',
        'BRN' => 'Brunei',
        'BS' => 'Bahamas',
        'BT' => 'Bhutan',
        'BTN' => 'Bhutan',
        'BV' => 'Bouvet Island',
        'BW' => 'Botswana',
        'BWA' => 'Botswana',
        'BY' => 'Belarus',
        'BZ' => 'Belize',
        'CA' => 'Canada',
        'CAF' => 'Central African Republic',
        'CAN' => 'Canada',
        'CC' => 'Cocos Islands',
        'CD' => 'Democratic Republic of the Congo',
        'CF' => 'Central African Republic',
        'CG' => 'Republic of the Congo',
        'CH' => 'Switzerland',
        'CHE' => 'Switzerland',
        'CHL' => 'Chile',
        'CHN' => 'China',
        'CI' => 'Ivory Coast',
        'CIV' => 'Ivory Coast',
        'CK' => 'Cook Islands',
        'COK' => 'Cook Islands',
        'CL' => 'Chile',
        'CM' => 'Cameroon',
        'CMR' => 'Cameroon',
        'CN' => 'China',
        'CO' => 'Colombia',
        'COD' => 'Democratic Republic of the Congo',
        'COG' => 'Republic of the Congo',
        'COL' => 'Colombia',
        'CR' => 'Costa Rica',
        'CRI' => 'Costa Rica',
        'CS' => 'Serbia and Montenegro',
        'CU' => 'Cuba',
        'CUB' => 'Cuba',
        'CV' => 'Cape Verde',
        'CW' => 'Curacao',
        'CX' => 'Christmas Island',
        'CY' => 'Cyprus',
        'CYN' => 'Northern Cyprus',
        'CYP' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'CZE' => 'Czech Republic',
        'DE' => 'Germany',
        'DEU' => 'Germany',
        'DJ' => 'Djibouti',
        'DJI' => 'Djibouti',
        'DK' => 'Denmark',
        'DM' => 'Dominica',
        'DNK' => 'Denmark',
        'DO' => 'Dominican Republic',
        'DOM' => 'Dominican Republic',
        'DZ' => 'Algeria',
        'DZA' => 'Algeria',
        'EC' => 'Ecuador',
        'ECU' => 'Ecuador',
        'EE' => 'Estonia',
        'EG' => 'Egypt',
        'EGY' => 'Egypt',
        'EH' => 'Western Sahara',
        'ER' => 'Eritrea',
        'ERI' => 'Eritrea',
        'ES' => 'Spain',
        'ESP' => 'Spain',
        'EST' => 'Estonia',
        'ET' => 'Ethiopia',
        'ETH' => 'Ethiopia',
        'FI' => 'Finland',
        'FIN' => 'Finland',
        'FJ' => 'Fiji',
        'FJI' => 'Fiji',
        'FK' => 'Falkland Islands',
        'FLK' => 'Falkland Islands',
        'FM' => 'Micronesia',
        'FO' => 'Faroe Islands',
        'FR' => 'France',
        'FRA' => 'France',
        'GA' => 'Gabon',
        'GAB' => 'Gabon',
        'GB' => 'United Kingdom',
        'GBR' => 'United Kingdom',
        'GD' => 'Grenada',
        'GE' => 'Georgia',
        'GEO' => 'Georgia',
        'GF' => 'French Guiana',
        'GG' => 'Guernsey',
        'GGY' => 'Guernsey',
        'GH' => 'Ghana',
        'GHA' => 'Ghana',
        'GI' => 'Gibraltar',
        'GIB' => 'Gibraltar',
        'GI' => 'Guinea',
        'GIN' => 'Guinea',
        'GL' => 'Greenland',
        'GM' => 'Gambia',
        'GMB' => 'Gambia',
        'GN' => 'Guinea',
        'GNB' => 'Guinea-Bissau',
        'GNQ' => 'Equatorial Guinea',
        'GP' => 'Guadeloupe',
        'GQ' => 'Equatorial Guinea',
        'GR' => 'Greece',
        'GRC' => 'Greece',
        'GRL' => 'Greenland',
        'GS' => 'South Georgia and the South Sandwich Islands',
        'GT' => 'Guatemala',
        'GTM' => 'Guatemala',
        'GU' => 'Guam',
        'GUY' => 'Guyana',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HK' => 'Hong Kong',
        'HKG' => 'Hong Kong',
        'HM' => 'Heard Island and McDonald Islands',
        'HN' => 'Honduras',
        'HND' => 'Honduras',
        'HR' => 'Croatia',
        'HRV' => 'Croatia',
        'HT' => 'Haiti',
        'HTI' => 'Haiti',
        'HU' => 'Hungary',
        'HUN' => 'Hungary',
        'ID' => 'Indonesia',
        'IDN' => 'Indonesia',
        'IE' => 'Ireland',
        'IL' => 'Israel',
        'IM' => 'Isle of Man',
        'IMN' => 'Isle of Man',
        'IN' => 'India',
        'IND' => 'India',
        'IO' => 'British Indian Ocean Territory',
        'IQ' => 'Iraq',
        'IR' => 'Iran',
        'IRL' => 'Ireland',
        'IRN' => 'Iran',
        'IRQ' => 'Iraq',
        'IS' => 'Iceland',
        'ISL' => 'Iceland',
        'ISR' => 'Israel',
        'IT' => 'Italy',
        'ITA' => 'Italy',
        'JAM' => 'Jamaica',
        'JE' => 'Jersey',
        'JEY' => 'Jersey',
        'JM' => 'Jamaica',
        'JO' => 'Jordan',
        'JOR' => 'Jordan',
        'JP' => 'Japan',
        'JPN' => 'Japan',
        'KAS' => 'Kashmir',
        'KAZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KEN' => 'Kenya',
        'KG' => 'Kyrgyzstan',
        'KGZ' => 'Kyrgyzstan',
        'KH' => 'Cambodia',
        'KHM' => 'Cambodia',
        'KI' => 'Kiribati',
        'KM' => 'Comoros',
        'KN' => 'Saint Kitts and Nevis',
        'KOR' => 'Korea',
        'KOS' => 'Kosovo',
        'KP' => 'North Korea',
        'KR' => 'South Korea',
        'KW' => 'Kuwait',
        'KWT' => 'Kuwait',
        'KY' => 'Cayman Islands',
        'KZ' => 'Kazakhstan',
        'LA' => 'Laos',
        'LAO' => 'Laos',
        'LB' => 'Lebanon',
        'LBN' => 'Lebanon',
        'LBR' => 'Liberia',
        'LBY' => 'Libya',
        'LC' => 'Saint Lucia',
        'LI' => 'Liechtenstein',
        'LIE' => 'Liechtenstein',
        'LK' => 'Sri Lanka',
        'LKA' => 'Sri Lanka',
        'LR' => 'Liberia',
        'LS' => 'Lesotho',
        'LSO' => 'Lesotho',
        'LT' => 'Lithuania',
        'LTU' => 'Lithuania',
        'LU' => 'Luxembourg',
        'LUX' => 'Luxembourg',
        'LV' => 'Latvia',
        'LVA' => 'Latvia',
        'LY' => 'Libya',
        'MA' => 'Morocco',
        'MAR' => 'Morocco',
        'MC' => 'Monaco',
        'MCO' => 'Monaco',
        'MD' => 'Moldova',
        'MDA' => 'Moldova',
        'MDG' => 'Madagascar',
        'ME' => 'Montenegro',
        'MEX' => 'Mexico',
        'MF' => 'Saint Martin',
        'MG' => 'Madagascar',
        'MH' => 'Marshall Islands',
        'MK' => 'Macedonia',
        'MKD' => 'Macedonia',
        'ML' => 'Mali',
        'MLI' => 'Mali',
        'MM' => 'Myanmar',
        'MMR' => 'Myanmar',
        'MN' => 'Mongolia',
        'MNE' => 'Montenegro',
        'MNG' => 'Mongolia',
        'MO' => 'Macao',
        'MAC' => 'Macao',
        'MOZ' => 'Mozambique',
        'MP' => 'Northern Mariana Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MRT' => 'Mauritania',
        'MS' => 'Montserrat',
        'MT' => 'Malta',
        'MU' => 'Mauritius',
        'MUS' => 'Mauritius',
        'MV' => 'Maldives',
        'MW' => 'Malawi',
        'MWI' => 'Malawi',
        'MX' => 'Mexico',
        'MY' => 'Malaysia',
        'MYS' => 'Malaysia',
        'MZ' => 'Mozambique',
        'NA' => 'Namibia',
        'NAM' => 'Namibia',
        'NC' => 'New Caledonia',
        'NCL' => 'New Caledonia',
        'NE' => 'Niger',
        'NER' => 'Niger',
        'NF' => 'Norfolk Island',
        'NG' => 'Nigeria',
        'NGA' => 'Nigeria',
        'NI' => 'Nicaragua',
        'NIC' => 'Nicaragua',
        'NL' => 'Netherlands',
        'NLD' => 'Netherlands',
        'NO' => 'Norway',
        'NOR' => 'Norway',
        'NP' => 'Nepal',
        'NPL' => 'Nepal',
        'NR' => 'Nauru',
        'NU' => 'Niue',
        'NZ' => 'New Zealand',
        'NZL' => 'New Zealand',
        'OM' => 'Oman',
        'OMN' => 'Oman',
        'PA' => 'Panama',
        'PAK' => 'Pakistan',
        'PAN' => 'Panama',
        'PE' => 'Peru',
        'PER' => 'Peru',
        'PF' => 'French Polynesia',
        'PYF' => 'French Polynesia',
        'PG' => 'Papua New Guinea',
        'PH' => 'Philippines',
        'PHL' => 'Philippines',
        'PK' => 'Pakistan',
        'PL' => 'Poland',
        'PM' => 'Saint Pierre and Miquelon',
        'PN' => 'Pitcairn',
        'PNG' => 'Papua New Guinea',
        'POL' => 'Poland',
        'PR' => 'Puerto Rico',
        'PRI' => 'Puerto Rico',
        'PRK' => 'North Korea',
        'PRT' => 'Portugal',
        'PRY' => 'Paraguay',
        'PS' => 'Palestinian Territory',
        'PSX' => 'Palestine',
        'PT' => 'Portugal',
        'PW' => 'Palau',
        'PY' => 'Paraguay',
        'QA' => 'Qatar',
        'QAT' => 'Qatar',
        'RE' => 'Reunion',
        'RO' => 'Romania',
        'ROU' => 'Romania',
        'RS' => 'Serbia',
        'RU' => 'Russia',
        'RUS' => 'Russia',
        'RW' => 'Rwanda',
        'RWA' => 'Rwanda',
        'SA' => 'Saudi Arabia',
        'SAH' => 'Western Sahara',
        'SAU' => 'Saudi Arabia',
        'SB' => 'Solomon Islands',
        'SC' => 'Seychelles',
        'SD' => 'Sudan',
        'SDN' => 'Sudan',
        'SDS' => 'South Sudan',
        'SE' => 'Sweden',
        'SEN' => 'Senegal',
        'SG' => 'Singapore',
        'SGP' => 'Singapore',
        'SH' => 'Saint Helena',
        'SI' => 'Slovenia',
        'SJ' => 'Svalbard and Jan Mayen',
        'SK' => 'Slovakia',
        'SL' => 'Sierra Leone',
        'SLB' => 'Solomon Islands',
        'SLE' => 'Sierra Leone',
        'SLV' => 'El Salvador',
        'SM' => 'San Marino',
        'SMR' => 'San Marino',
        'SN' => 'Senegal',
        'SO' => 'Somalia',
        'SOL' => 'Somaliland',
        'SOM' => 'Somalia',
        'SR' => 'Suriname',
        'SRB' => 'Serbia',
        'SS' => 'South Sudan',
        'ST' => 'Sao Tome and Principe',
        'SUR' => 'Suriname',
        'SV' => 'El Salvador',
        'SVK' => 'Slovakia',
        'SVN' => 'Slovenia',
        'SWE' => 'Sweden',
        'SWZ' => 'Swaziland',
        'SX' => 'Sint Maarten',
        'SY' => 'Syria',
        'SYR' => 'Syria',
        'SZ' => 'Swaziland',
        'TC' => 'Turks and Caicos Islands',
        'TCD' => 'Chad',
        'TD' => 'Chad',
        'TF' => 'French Southern Territories',
        'TG' => 'Togo',
        'TGO' => 'Togo',
        'TH' => 'Thailand',
        'THA' => 'Thailand',
        'TJ' => 'Tajikistan',
        'TJK' => 'Tajikistan',
        'TK' => 'Tokelau',
        'TKM' => 'Turkmenistan',
        'TL' => 'East Timor',
        'TLS' => 'Timor-Leste',
        'TM' => 'Turkmenistan',
        'TN' => 'Tunisia',
        'TO' => 'Tonga',
        'TON' => 'Tonga',
        'TR' => 'Turkey',
        'TT' => 'Trinidad and Tobago',
        'TTO' => 'Trinidad and Tobago',
        'TUN' => 'Tunisia',
        'TUR' => 'Turkey',
        'TV' => 'Tuvalu',
        'TW' => 'Taiwan',
        'TWN' => 'Taiwan',
        'TZ' => 'Tanzania',
        'TZA' => 'Tanzania',
        'UA' => 'Ukraine',
        'UG' => 'Uganda',
        'UGA' => 'Uganda',
        'UKR' => 'Ukraine',
        'UM' => 'United States Minor Outlying Islands',
        'URY' => 'Uruguay',
        'US' => 'United States',
        'USA' => 'United States',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'UZB' => 'Uzbekistan',
        'VA' => 'Vatican',
        'VAT' => 'Vatican',
        'VC' => 'Saint Vincent and the Grenadines',
        'VE' => 'Venezuela',
        'VEN' => 'Venezuela',
        'VG' => 'British Virgin Islands',
        'VI' => 'U.S. Virgin Islands',
        'VN' => 'Vietnam',
        'VNM' => 'Vietnam',
        'VU' => 'Vanuatu',
        'VUT' => 'Vanuatu',
        'WF' => 'Wallis and Futuna',
        'WS' => 'Samoa',
        'XK' => 'Kosovo',
        'YE' => 'Yemen',
        'YEM' => 'Yemen',
        'YT' => 'Mayotte',
        'ZA' => 'South Africa',
        'ZAF' => 'South Africa',
        'ZM' => 'Zambia',
        'ZMB' => 'Zambia',
        'ZW' => 'Zimbabwe',
        'ZWE' => 'Zimbabwe'
    );

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
        return $this->process($metadata['footprint'], $options);
    }
    
    /**
     * Compute intersected information from input WKT footprint
     * 
     * @param string footprint
     * @param array $options
     * 
     */
    private function process($footprint, $options) {

        /*
         * Initialize empty array
         */
        $continents = array();
                
        /*
         * Add continents and countries
         */
        $this->addCountries($continents, $footprint);
        
        /*
         * Add regions/states
         */
        $this->addRegions($continents, $footprint);
        
        /*
         * Add cities
         * TODO : not working
         */
        if (isset($options['cities'])) {
            $this->addCities($continents, $footprint, $options['cities']);
        }
        
        return array(
            'political' => array(
                'continents' => $continents
            )
        );
        
    }
    
    /**
     * Add continents/countries to political array
     * 
     * @param array $continents
     * @param string $footprint
     */
    private function addCountries(&$continents, $footprint) {
        $query = 'SELECT name as name, normalize(name) as id, continent as continent, normalize(continent) as continentid, ' . $this->postgisArea('st_intersection(geom, ST_GeomFromText(\'' . $footprint . '\', 4326))') . ' as area, ' . $this->postgisArea('ST_GeomFromText(\'' . $footprint . '\', 4326)') . ' as totalarea FROM datasources.countries WHERE st_intersects(geom, ST_GeomFromText(\'' . $footprint . '\', 4326)) ORDER BY area DESC';
        $results = $this->query($query);
        while ($element = pg_fetch_assoc($results)) {
            $this->addCountriesToContinents($continents, $element);
        }
    }

    /**
     * Add regions/states to political array
     * 
     * @param array $continents
     * @param string $footprint
     */
    private function addRegions(&$continents, $footprint) {
        $query = 'SELECT region, name as state, normalize(name) as stateid, normalize(region) as regionid, adm0_a3 as isoa3, ' .  $this->postgisArea('st_intersection(geom, ST_GeomFromText(\'' . $footprint . '\', 4326))') . ' as area, ' . $this->postgisArea('ST_GeomFromText(\'' . $footprint . '\', 4326)') . ' as totalarea FROM datasources.worldadm1level WHERE st_intersects(geom, ST_GeomFromText(\'' . $footprint . '\', 4326)) ORDER BY area DESC';
        $results = $this->query($query);
        while ($element = pg_fetch_assoc($results)) {
            $this->addRegionsToCountries($continents, $element);       
        }
    }
    
    /**
     * Add cities to political array
     * 
     * @param array $continents
     * @param string $footprint
     * @param string $what : 'all' means all cities; main cities otherwise
     */
    private function addCities(&$continents, $footprint, $what) {
        $codes = $what === 'all' && $this->isValidArea($footprint) ? "('PPL', 'PPLC', 'PPLA', 'PPLA2', 'PPLA3', 'PPLA4', 'STLMT')" : "('PPLA','PPLC')";
        $query = "SELECT g.name, g.countryname as country, d.region as region, d.name as state, d.adm0_a3 as isoa3 FROM gazetteer.geoname g LEFT OUTER JOIN datasources.worldadm1level d ON g.country || '.' || g.admin2 = d.gn_a1_code WHERE st_intersects(g.geom, ST_GeomFromText('" . $footprint . "', 4326)) and g.fcode in " . $codes . " ORDER BY g.name";
        $results = $this->query($query);
        while ($element = pg_fetch_assoc($results)) {
            print_r($element);
            $this->addCitiesToStates($continents, $element);       
        }
    }
    
    /**
     * Add cities under states
     * 
     * @param array $continents
     * @param array $element
     */
    private function addCitiesToStates(&$continents, $element) {
        foreach (array_keys($continents) as $continent) {
            foreach (array_keys($continents[$continent]['countries']) as $country) {
                if ($continents[$continent]['countries'][$country]['name'] === $element['country']) {
                    print_r($continents[$continent]['countries'][$country]['regions'][$element['region']]);
                    foreach (array_keys($continents[$continent]['countries'][$country]['regions'][$element['region']]['states']) as $state) {
                        if ($continents[$continent]['countries'][$country]['regions'][$element['region']]['states'][$state]['name'] === $element['state']) {
                            if (!isset($continents[$continent]['countries'][$country]['regions'][$element['region']]['states'][$state]['cities'])) {
                                $continents[$continent]['countries'][$country]['regions'][$element['region']]['states'][$state]['cities'] = array();
                            }
                            array_push($continents[$continent]['countries'][$country]['regions'][$element['region']]['states'][$state]['cities'], $element['name']);
                        }
                    }
                }
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
                $countryName = isset($this->countryNames[$element['isoa3']]) ? $this->countryNames[$element['isoa3']] : null;
                if (isset($countryName) && ($continents[$i]['countries'][$j]['name'] === $countryName)) {
                    if (!isset($continents[$i]['countries'][$j]['regions'])) {
                        $continents[$i]['countries'][$j]['regions'] = array();
                    }
                    $index = -1;
                    for ($k = count($continents[$i]['countries'][$j]['regions']); $k--;) {
                        if (!$element['regionid'] && !isset($continents[$i]['countries'][$j]['regions'][$k]['id'])) {
                            $index = $k;
                            break;
                        }
                        else if (isset($continents[$i]['countries'][$j]['regions'][$k]['id']) && $continents[$i]['countries'][$j]['regions'][$k]['id'] === $element['regionid']) {
                            $index = $k;
                            break;
                        }
                    }
                    if ($index === -1) {
                        if (!isset($element['regionid']) || !$element['regionid']) {
                            array_push($continents[$i]['countries'][$j]['regions'], array(
                                'states' => array()
                            ));
                        }
                        else {
                            array_push($continents[$i]['countries'][$j]['regions'], array(
                                'name' => $element['region'],
                                'id' => 'region:' . $element['regionid'],
                                'states' => array()
                            ));
                        }
                        $index = count($continents[$i]['countries'][$j]['regions']) - 1;
                    }
                    if (isset($continents[$i]['countries'][$j]['regions'][$index]['states'])) {
                        array_push($continents[$i]['countries'][$j]['regions'][$index]['states'], array('name' => $element['state'], 'id' => 'state:' . $element['stateid'], 'pcover' => $this->percentage($element['area'], $element['totalarea'])));
                    }
                    break;
                }
            }
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
        array_push($continents[$index]['countries'], array(
            'name' => $element['name'],
            'id' => 'country:' . $element['id'],
            'pcover' => $this->percentage($element['area'], $element['totalarea'])
        ));
    }

}
