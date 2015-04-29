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

/*
 * English Dictionary class
 */
class RestoDictionary_en extends RestoDictionary {

    /*
     * Multiwords
     */
    public $multiwords = array(
        'greater than',
        'lower than',
        'equal to',
        'greater than',
        'more than',
        'lesser than',
        'less than',
        'lower than',
        'austral summer',
        'austral winter',
        'austral autumn',
        'austral spring'
    );
    
    /*
     * For each entry 
     *
     *   - the key (left side) is the key
     *   - the value (right side) is an array of homonyms
     *     in the given language.
     * The first value is the prefered one
     */ 
    protected $dictionary = array(
        
        /*
         * And modifiers
         */
        'andModifiers' => array(
            'and' => array('and', 'to')
        ),
        /*
         * Location modifiers
         */
        'locationModifiers' => array(
            'in' => array('in', 'on', 'of', 'near', 'around', 'for', 'covers')
        ),
        /*
         * Quantity modifiers
         */
        'quantityModifiers' => array(
            'equal' => array('equal to'),
            'between' => array('between', 'from'),
            'greater' => array('greater than', 'more than', '>'),
            'lesser' => array('lesser than', '<', 'less than', 'lower than'),
            'with' => array('with', 'containing'),
            'without' => array('without', 'no'),
            'for' => array('by', 'for', 'about', 'of', 'on')
        ),
        /*
         * Time modifiers
         */
        'timeModifiers' => array(
            'after' => array('after'),
            'ago' => array('ago'),
            'before' => array('before'),
            'between' => array('between', 'from'),
            'in' => array('in'),
            'last' => array('last'),
            'next' => array('next'),
            'since' => array('since'),
            'today' => array('today'),
            'tomorrow' => array('tomorrow'),
            'yesterday' => array('yesterday')
        ),
        /*
         * Stop words i.e. excluded words
         */
        'stopWords' => array('a', 'the', 'of', 'with', 'than', 'that', 'this', 'or', 'and', 'by', 'all', 'to', 'from'),
        /*
         * List of words in the query that are
         * considered as 'noise' for the query analysis
         * and thus excluded from the analysis
         */
        'noise' => array('acquired', 'satellite%', 'search', 'area%', 'to'),
        /*
         * Months
         */
        'months' => array(
            '01' => array('january'),
            '02' => array('february'),
            '03' => array('march'),
            '04' => array('april'),
            '05' => array('may'),
            '06' => array('june'),
            '07' => array('july'),
            '08' => array('august'),
            '09' => array('september'),
            '10' => array('october'),
            '11' => array('november'),
            '12' => array('december')
        ),
        /*
         * Numbers
         */
        'numbers' => array(
            '1' => array('one', '1st'),
            '2' => array('two', '2nd'),
            '3' => array('three', '3rd'),
            '4' => array('four', '4th'),
            '5' => array('five', '5th'),
            '6' => array('six', '6th'),
            '7' => array('seven', '7th'),
            '8' => array('eight', '8th'),
            '9' => array('nine', '9th'),
            '10' => array('ten', '10th'),
            '11' => array('11th'),
            '12' => array('12th'),
            '13' => array('13th'),
            '14' => array('14th'),
            '15' => array('15th'),
            '16' => array('16th'),
            '17' => array('17th'),
            '18' => array('18th'),
            '19' => array('19th'),
            '20' => array('20th'),
            '21' => array('21st'),
            '22' => array('22nd'),
            '23' => array('23rd'),
            '24' => array('24th'),
            '25' => array('25th'),
            '26' => array('26th'),
            '27' => array('27th'),
            '28' => array('28th'),
            '29' => array('29th'),
            '30' => array('30th'),
            '31' => array('31st'),
            '100' => array('hundred'),
            '1000' => array('thousand')
        ),
        /*
         * Quantities
         */
        'quantities' => array(
            'resolution' => array('resolution'),
            'orbit' => array('orbit'),
            'cloud' => array('cloud', 'clouds', 'cloud cover'),
            'snow' => array('snow'),
            'ice' => array('ice'),
            'urban' => array('city', 'cities', 'urban area'),
            'cultivated' => array('cultivated area', 'cropland', 'croplands', 'crop', 'crops'),
            'forest' => array('forest', 'forests'),
            'herbaceous' => array('herbaceous area', 'grass', 'lowland', 'prairie'),
            'desert' => array('desert', 'bare area'),
            'flooded' => array('flooded area'),
            'water' => array('water')
        ),
        /*
         * Seasons
         */
        'seasons' => array(
            'autumn' => array('austral spring','autumn', 'falls'),
            'spring' => array('austral autumn','spring'),
            'summer' => array('austral winter','summer'),
            'winter' => array('austral summer', 'winter')
        ),
        /*
         * Time units
         */
        'timeUnits' => array(
            'days' => array('days', 'day'),
            'months' => array('month', 'months'),
            'years' => array('year', 'years'),
            'weeks' => array('week', 'weeks')
        ),
        /*
         * Units
         */
        'units' => array(
            'm' => array('m', 'meter', 'meters'),
            'km' => array('km', 'kilometer', 'kilometers'),
            '%' => array('%', 'percent', 'percents', 'percentage')
        )
    );
    
    /*
     * Translations
     */
    protected $translations = array(
        'activationSubject' => '[{a:1}] Activation code',
        'activationMessage' => "Hi,\r\n\r\nYou have registered an account to {a:1} application\r\n\r\nTo validate this account, go to {a:2}\r\n\r\nRegards\r\n\r\n{a:1} team",
        'resetPasswordSubject' => '[{a:1}] Reset password',
        'resetPasswordMessage' => "Hi,\r\n\r\nYou ask to reset your password for the {a:1} application\r\n\r\nTo reset your password, go to {a:2}\r\n\r\nRegards\r\n\r\n{a:1} team",
        '_acquiredOn' => 'acquired on {a:1}',
        '_alternateCollectionLink' => 'alternate',
        '_atomLink' => 'ATOM link for {a:1}',
        '_firstCollectionLink' => 'first',
        '_firstPage' => '<<',
        '_htmlLink' => 'HTML link for {a:1}',
        '_jsonLink' => 'GeoJSON link for {a:1}',
        '_lastCollectionLink' => 'last',
        '_lastPage' => '>>',
        '_metadataLink' => 'Metadata link for {a:1}',
        '_multipleResult' => '{a:1} results',
        '_nextCollectionLink' => 'next',
        '_nextPage' => 'Next',
        '_oneResult' => '1 result',
        '_osddLink' => 'OpenSearch Description Document',
        '_previousCollectionLink' => 'previous',
        '_previousPage' => 'Previous',
        '_selfCollectionLink' => 'self',
        '_selfFeatureLink' => 'self'
    );

    /**
     * Constructor
     * 
     * @param RestoDatabaseDriver $dbDriver
     * @throws Exception
     */
    public function __construct($dbDriver) {
        parent::__construct($dbDriver);
    }
    
}
