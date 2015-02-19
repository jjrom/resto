<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2014 Jérôme Gasperi <https://github.com/jjrom>
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
return array(
    
    /*
     * General
     */
    'general' => array(
        
        /*
         * Title
         */
        'title' => 'resto',
        
        /*
         * Root url for the application - do not specify protocol
         */
        'restoUrl' => '//localhost/resto2',
        
        /*
         * Supported languages
         * 
         * All supported languages must be associated with a dictionary class
         * called RestoDictionary_{language} (usually located under $RESTO_BUILD/include/resto/Dictionaries) 
         */
        'languages' => array('en', 'fr'),
        
        /*
         * Debug mode
         */
        'debug' => true,
        
        /*
         * Timezone
         */
        'timezone' => 'Europe/Paris',
        
        /*
         * Store queries ? (i.e. logs)
         */
        'storeQuery' => true,
        
        /*
         * Shared links validity duration (in seconds)
         * Default is 1 day (i.e. 86400 seconds)
         */
        'sharedLinksDuration' => 86400,
        
        /*
         * JSON Web Token passphrase
         * (see https://tools.ietf.org/html/draft-ietf-oauth-json-web-token-32)
         */
        'passphrase' => 'Super secret passphrase'
    ),
    
    /*
     * Database configuration
     */
    'database' => array(
        
        /*
         * Driver name must be associated to a RestoDatabaseDriver class called
         * RestoDatabaseDriver_{driver} (usually located under $RESTO_BUILD/include/resto/Drivers)
         */
        'driver' => 'PostgreSQL',
        
        /*
         * Cache directory used to store Database queries
         * Must be readable and writable for Webserver user
         * If not set, then no cache is used
         */
        //'dircache' => '/tmp',
        
        /*
         * Database name
         */
        'dbname' => 'resto2',
        
        /*
         * Database host - if not specified connect through socket instead of TCP/IP
         */
        //'host' => 'localhost',
        
        /*
         * Database port
         */
        'port' => 5432,
        
        /*
         * Pagination
         * Default number of search results returned by page if not specified in the request
         */
        'resultsPerPage' => 20,
        
        /*
         * Database user with READ+WRITE privileges (see http://github.com/jjrom/resto/README.md)
         */
        'user' => 'resto',
        'password' => 'resto'
    ),
    
    /*
     * Authentication
     */
    'mail' => array(
        
        /*
         * Name display to users when they receive email from application
         */
        'senderName' => 'admin',
        
        /*
         * Email display to users when they receive email from application
         */
        'senderEmail' => 'restoadmin@localhost',
        
    ),
    
    /*
     * Modules
     */
    'modules' => array(
        
        /*
         * OAuth authentication module
         */
        'Auth' => array(
            'activate' => true,
            'route' => 'api/auth',
            'options' => array(
                'providers' => array(
                    'google' => array(
                        'clientId' => '===>Insert your clienId here<===',
                        'clientSecret' => '===>Insert your clienSecret here<==='
                    ),
                    'linkedin' => array(
                        'clientId' => '===>Insert your clienId here<===',
                        'clientSecret' => '===>Insert your clienSecret here<==='
                    ),
                    'theiatest' => array(
                        'protocol' => 'oauth2',
                        'clientId' => '===>Insert your clienSecret here<===',
                        'clientSecret' => '===>Insert your clienSecret here<===',
                        'accessTokenUrl' => 'https://sso.kalimsat.eu/oauth2/token',
                        'peopleApiUrl' => 'https://sso.kalimsat.eu/oauth2/userinfo?schema=openid',
                        'uidKey' => 'http://theia.org/claims/emailaddress'
                    )
                )
            )
        ),
        
        /*
         * Query Analyzer module - convert natural language query to EO query
         */
        'QueryAnalyzer' => array(
            'activate' => true,
            'route' => 'api/query/analyze',
        ),
        
        /*
         * Gazetteer module - enable location based search
         * Note : set database options if gazetteer is not installed in RESTo database
         * 
         * !!! Require iTag !!!
         */
        'Gazetteer' => array(
            'activate' => false,
            'route' => 'api/gazetteer/search'
        ),
        
        /*
         * Wikipedia module - enable location based wikipedia entries display
         * 
         * !!! Require iTag !!!
         */
        'Wikipedia' => array(
            'activate' => false,
            'route' => 'api/wikipedia/search'
        ),
        
        /*
         * iTag module - automatically tag posted feature 
         * 
         * !!! Require iTag !!!
         */
        'iTag' => array(
            'activate' => false
        )
        
    )
);