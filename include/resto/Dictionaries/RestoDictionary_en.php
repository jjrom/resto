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

    protected $dictionary = array(
        /*
         * List of words in the query that are
         * considered as 'noise' for the query analysis
         * and thus excluded from the analysis
         */
        'excluded' => array(
            'than',
            'over',
            'acquired',
            'image',
            'images',
            'cover',
            'area',
            'zone'
        ),
        /*
         * Modifiers
         * 
         * Valid modifiers values are
         *  - with
         *  - witout
         *  - less
         *  - greater
         *  - and
         * 
         * For each entry 
         *   - the key (left side) is what the user types 
         *   - the value (right side) is the equivalent modifier
         */
        'modifiers' => array(
            'ago' => 'ago',
            'before' => 'before',
            'after' => 'after',
            'between' => 'between',
            'containing' => 'with',
            'with' => 'with',
            'without' => 'without',
            'no' => 'without',
            'less' => 'lesser',
            'lesser' => 'lesser',
            'lower' => 'lesser',
            'more' => 'greater',
            'greater' => 'greater',
            'equal' => 'equal',
            'and' => 'and',
            'since' => 'since',
            'last' => 'last',
            'today' => 'today',
            'yesterday' => 'yesterday'
        ),
        /*
         * Units
         * 
         * For each entry 
         *   - the key (left side) is what the user types
         *   - the value (right side) is the equivalent unit
         * 
         */
        'units' => array(
            'm' => 'm',
            'meter' => 'm',
            'meters' => 'm',
            'km' => 'km',
            'kilometer' => 'km',
            'kilometers' => 'km',
            'percent' => '%',
            'percents' => '%',
            'percentage' => '%',
            '%' => '%',
            'day' => 'days',
            'days' => 'days',
            'month' => 'months',
            'months' => 'months',
            'year' => 'years',
            'years' => 'years'
        ),
        /*
         * Numbers
         * 
         * For each entry 
         *   - the key (left side) is the textual number
         *   - the value (right side) is number
         * 
         */
        'numbers' => array(
            'one' => '1',
            'two' => '2',
            'three' => '3',
            'four' => '4',
            'five' => '5',
            'six' => '6',
            'seven' => '7',
            'eight' => '8',
            'nine' => '9',
            'ten' => '10',
            'hundred' => '100',
            'thousand' => '1000'
        ),
        /*
         * Months
         * 
         * For each entry 
         *   - the key (left side) is the month
         *   - the value (right side) is the equivalent
         *     month number (from 01 to 12)
         * 
         */
        'months' => array(
            'january' => '01',
            'february' => '02',
            'march' => '03',
            'april' => '04',
            'may' => '05',
            'june' => '06',
            'july' => '07',
            'august' => '08',
            'september' => '09',
            'october' => '10',
            'november' => '11',
            'december' => '12'
        ),
        /*
         * Quantities
         * 
         * Quantity is the entity on which apply a comparaison modifier
         * 
         *  e.g.
         *      "resolution   lesser    than 10  meters"
         *       <quantity> <modifier>           <units>
         * 
         */
        'quantities' => array(
            'resolution' => 'resolution',
            'orbit' => 'orbit',
            'cloud' => 'cloud',
            'clouds' => 'cloud',
            'snow' => 'snow',
            'ice' => 'ice',
            'urban' => 'urban',
            'city' => 'urban',
            'cities' => 'urban',
            'urban area' => 'urban',
            'cultivated area' => 'cultivated',
            'cropland' => 'cultivated',
            'croplands' => 'cultivated',
            'crop' => 'cultivated',
            'crops' => 'cultivated',
            'forest' => 'forest',
            'forests' => 'forest',
            'herbaceous area' => 'herbaceous',
            'herbaceous' => 'herbaceous',
            'grass' => 'herbaceous',
            'desert' => 'desert',
            'bare area' => 'desert',
            'flooded' => 'flooded',
            'water' => 'water'
        )
    );
    
    /*
     * Translations
     */
    protected $translations = array(
        '_headerTitle' => 'resto',
        '_headerDescription' => '<b>resto</b> - <b>RE</b>stful <b>S</b>emantic search <b>T</b>ool for ge<b>O</b>spatial<br/>RESTo implements search service with semantic query analyzis on Earth Observation metadata database. It conforms to OGC 13-026 standard - OpenSearch Extension for Earth Observation.',
        '_selfCollectionLink' => 'self',
        '_alternateCollectionLink' => 'alternate',
        '_firstCollectionLink' => 'first',
        '_lastCollectionLink' => 'last',
        '_nextCollectionLink' => 'next',
        '_previousCollectionLink' => 'previous',
        '_selfFeatureLink' => 'self',
        '_about' => 'About',
        '_close' => 'Close',
        '_acquiredOn' => 'acquired on {a:1}',
        '_placeHolder' => 'Search - ex. {a:1}',
        '_query' => 'Search filters - {a:1}',
        '_notUnderstood' => 'Request not understood - no search filters applied',
        '_noResult' => 'Found no result - try another request !',
        '_oneResult' => '1 result',
        '_multipleResult' => '{a:1} results',
        '_firstPage' => '<<',
        '_previousPage' => 'Previous',
        '_nextPage' => 'Next',
        '_lastPage' => '>>',
        '_pageNumber' => 'Page {a:1}',
        '_identifier' => 'Identifier',
        '_resolution' => 'Resolution',
        '_startDate' => 'Start of acquisition',
        '_completionDate' => 'End of acquisition',
        '_viewMetadata' => 'View description of product {a:1}',
        '_viewMapshup' => 'View on map',
        '_viewMapshupFullResolution' => 'View on map',
        '_download' => 'Download',
        '_keywords' => 'Keywords',
        '_atomLink' => 'ATOM link for {a:1}',
        '_htmlLink' => 'HTML link for {a:1}',
        '_jsonLink' => 'GeoJSON link for {a:1}',
        '_thisResourceContainsLanduse' => 'Images that contain {a:2}',
        '_thisResourceIsLocated' => 'Images located in {a:1}',
        '_thisResourceContainsCity' => 'Images around {a:1}',
        '_thisResourceWasAcquiredBy' => 'Images acquired by {a:1} satellite',
        '_landUse' => 'Land use',
        '_location' => 'Location',
        '_platform' => 'Satellite',
        '_tags' => 'Tags',
        '_other' => 'Other',
        // landuse
        'urban' => 'Urban area',
        'cultivated' => 'Cultivated area',
        'flooded' => 'Flooded area',
        'herbaceous' => 'Herbaceous area',
        'desert' => 'Desert',
        'water' => 'Water',
        'forest' => 'Forest',
        'THR' => 'Images with resolution between 0 and 2.5 m',
        'HR' => 'Images with resolution between 2.5 and 30 m',
        'MR' => 'Images with resolution between 30 and 500 m',
        'LR' => 'Images with resolution greater than 500 m',
        '_home' => 'Home',
        '_viewAtomFeed' => 'View Atom feed for this search result',
        '_zoom' => 'Zoom map',
        '_unZoom' => 'Unzoom map',
        '_centerOnLayer' => 'Center view on search result',
        '_globalMapView' => 'Center on whole earth',
        '_showOnMap' => 'View on map',
        '_addCollection' => 'Add a collection',
        '_update' => 'Update',
        '_deactivate' => 'Deactivate',
        '_remove' => 'Remove',
        '_login' => 'Connect',
        '_logout' => 'Disconnect',
        '_dropCollection' => 'Drop a collection description file',
        '_email' => 'Email',
        '_password' => 'Password',
        '_createAccount' => 'Create an account',
        '_givenName' => 'Given name',
        '_lastName' => 'Last name',
        '_userName' => 'User name',
        '_retypePassword' => 'Retype password',
        '_back' => 'Back',
        '_signWithOauth' => 'Sign in with {a:1} account',
        '_addResource' => 'Add a resource',
        '_dropResource' => 'Drop a resource metadata file',
        '_resultFor' => 'Search results for &#34;{a:1}&#34;',
        '_resourceSummary' => '{a:1} image ({a:2} m) acquired on {a:3}',
        '_poi' => 'Points of interest',
        // Menu
        '_menu_shareOn' => 'Share on {a:1}',
        '_menu_viewCart' => 'View Cart',
        '_menu_connexion' => 'Connexion',
        '_menu_search' => 'Search...',
        //Administration
        '_users_groupname' => 'Group Name',
        '_user_set_admin_as_group' => 'Set admin as group',
        '_user_set_default_as_group' => 'Set default as group',
        '_users_username' => 'Username',
        '_users_lastname' => 'Lastname',
        '_users_givenname' => 'Givenname',
        '_users_registrationdate' => 'Registration Date',
        '_users_activated' => 'Activated',
        '_users_deactivated' => 'Deactivated',
        '_users_email' => 'Email',
        '_user_profil' => 'Profil',
        '_user_password' => 'Password',
        '_user_showfullhistory' => 'Show full history',
        '_user_createright' => 'Create right',
        '_user_deactivate_user' => 'Deactivate user',
        '_user_activate_user' => 'Activate user',
        '_user_delete_user' => 'Delete user',
        '_user_group_rights' => 'Group Rights',
        '_user_signed_licenses' => 'Signed Licenses',
        '_user_private_rights' => 'Private Rights',
        '_user_last_history' => 'Last History',
        '_start_users' => 'List Users',
        '_menu_create_user' => 'Create User',
        '_menu_history' => 'History',
        '_history_choose_service' => 'Choose Service',
        '_search' => 'Search',
        '_visualize' => 'Visualize',
        '_group' => 'Group',
        '_history_choose_collection' => 'Choose Collection',
        '_history_select_group_name' => 'Select Group Name',
        '_unregistered' => 'Unregistered',
        '_default' => 'Default',
        '_admin' => 'Admin',
        '_save_user' => 'Save User',
        '_save_right' => 'Save Right',
        '_rights_collection_and_feature' => 'Collection and Feature',
        '_feature_id' => 'Feature Id',
        '_can_post' => 'Can Post',
        '_can_put' => 'Can Put',
        '_can_delete' => 'Can Delete',
        '_true' => 'True',
        '_false' => 'False',
        '_history' => 'History'
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
