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

/*
 * English Dictionary class
 */

class RestoDictionary_en extends RestoDictionary {

    /*
     * For each entry 
     *
     *   - the key (left side) is the month key
     *   - the value (right side) is an array of month homonyms
     *     in the given language. The first value is the prefered one
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
            'in' => array('in', 'on', 'of', 'near', 'around', 'for')
        ),
        /*
         * Quantity modifiers
         */
        'quantityModifiers' => array(
            'equal' => array('equal'),
            'between' => array('between', 'from'),
            'greater' => array('greater', 'more', '>'),
            'lesser' => array('lesser', '<', 'less', 'lower'),
            'with' => array('with', 'containing'),
            'without' => array('without', 'no'),
            'for' => array('by', 'for')
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
        'stopWords' => array('a', 'the', 'of', 'with', 'than', 'that', 'or', 'and', 'by', 'acquired'),
        /*
         * List of words in the query that are
         * considered as 'noise' for the query analysis
         * and thus excluded from the analysis
         */
        'noise' => array('acquired', 'satellite'),
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
            '1' => array('one'),
            '2' => array('two'),
            '3' => array('three'),
            '4' => array('four'),
            '5' => array('five'),
            '6' => array('six'),
            '7' => array('seven'),
            '8' => array('eight'),
            '9' => array('nine'),
            '10' => array('ten'),
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
            'autumn' => array('autumn', 'falls'),
            'spring' => array('spring'),
            'summer' => array('summer'),
            'winter' => array('winter')
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
