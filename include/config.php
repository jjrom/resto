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
         * Relative endpoint to directory containing index.php
         * i.e. if index.php is at http://localhost/resto then
         * rootEndPoint would be '/resto'
         */
        'rootEndpoint' => '/resto',
        
        /*
         * Supported languages
         * 
         * All supported languages must be associated with a dictionary class
         * called RestoDictionary_{language} (usually located under $RESTO_BUILD/include/resto/Dictionaries) 
         */
        'languages' => array('en', 'fr'),
        
        /*
         * OpenSearch description for "all collections" search service
         * (i.e. API call to /api/collections/search)
         */
        'osDescription' => array(
            'en' => array(
                'ShortName' => 'resto',
                'LongName' => 'resto search service',
                'Description' => 'Search on all collections',
                'Tags' => 'resto',
                'Developper' => 'resto team',
                'Contact' => 'restoadmin@localhost',
                'Query' => 'europe 2015',
                'Attribution' => 'resto framework. Copyright 2015, All Rights Reserved'
            )
        ),
        
        /*
         * Debug mode
         */
        'debug' => false,

        /*
         * Low resolution WMS layer in the Map File for WMS Proxy
         */
        'low_resolution_wms_layer' => 'lowres',

        /*
         * Timezone
         */
        'timezone' => 'Europe/Paris',
        
        /*
         * Protocol :
         *  - http : use http
         *  - https : use https
         *  - auto : server will choose depending on input request
         */
        'protocol' => 'auto',
        
        /*
         * Store queries ? (i.e. logs)
         */
        'storeQuery' => true,
        
        /*
         * Shared links validity duration (in seconds)
         * Default is 1 day (i.e. 86400 seconds)
         */
        'sharedLinkDuration' => 86400,
        
        /*
         * Authentication tokens validity duration (in seconds)
         * Default is 1 hour (i.e. 3600 seconds)
         */
        'tokenDuration' => 3600,
        
        /*
         * JSON Web Token passphrase
         * (see https://tools.ietf.org/html/draft-ietf-oauth-json-web-token-32)
         */
        'passphrase' => 'Super secret passphrase',
        
        /*
         * JSON Web Token accepted encryption algorithms
         */
        'tokenEncryptions' => array('HS256','HS512','HS384','RS256'),
        
        /*
         * Url to call for password reset
         */
        'resetPasswordUrl' => 'http://localhost/rocket/#/resetPassword',
        
        /*
         * Url to call for search HTML client
         */
        'htmlSearchUrl' => 'http://localhost/rocket/#/search',
        
        /*
         * Upload directory (for POST with attachement request)
         */
        'uploadDirectory' => '/tmp/resto_uploads',
        
        /*
         * Set how the products are streamed to user :
         *   - 'php' : stream through PHP process (slowest but works on all platforms)
         *   - 'apache' : stream through Apache (needs the XSendfile module to be installed and configured)
         *   - 'nginx' : stream through Nginx using the X-accel method
         */
        'streamMethod' => 'php',
        
        /*
         * List of http origin that have CORS access to server
         * (see http://en.wikipedia.org/wiki/Cross-origin_resource_sharing)
         * 
         * If the array is empty, then every http origin have CORS access
         */
        'corsWhiteList' => array(
            'localhost'
        )
        
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
        'dbname' => 'resto',
        
        /*
         * Database host - if not specified connect through unix domain socket (IPC socket) instead of TCP/IP socket
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
        
        /*
         * Account activation email
         */
        'accountActivation' => array(
            'en' => array(
                'subject' => '[{a:1}] Activation code',
                'message' => 'Hi,<br>You have registered an account to {a:1} application<br><br>To validate this account, go to {a:2} <br><br>Regards<br><br>{a:1} team"'
            ),
            'fr' => array(
                'subject' => '[{a:1}] Code d\'activation',
                'message' => "Bonjour,<br><br>Vous vous êtes enregistré sur l'application {a:1}<br><br>Pour valider votre compte, cliquer sur le lien {a:2} <br><br>Cordialement<br><br>L'équipe {a:1}"
            )
        ),
        
        /*
         * Reset password email
         */
        'resetPassword' => array(
            'en' => array(
                'subject' => '[{a:1}] Reset password',
                'message' => 'Hi,<br><br>You ask to reset your password for the {a:1} application<br><br>To reset your password, go to {a:2} <br><br>Regards<br><br>{a:1} team'
            ),
            'fr' => array(
                'subject' => '[{a:1}] Demande de réinitialisation de mot de passe',
                'message' => "Bonjour,<br><br>Vous avez demandé une réinitialisation de votre mot de passe pour l'application {a:1}<br><br>Pour réinitialiser ce mot de passe, veuillez vous rendre sur le lien suivante {a:2} <br><br>Cordialement<br><br>L'équipe {a:1}"
            )
        )
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
                ),
                /*
                 * PHP >= 5.6 check SSL certificate
                 * Set verify_peer and verify_peer_name to false if you have issue
                 */
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false
                )
            )
        ),
        
        /*
         * Query Analyzer module - convert natural language query to EO query
         */
        'QueryAnalyzer' => array(
            'activate' => true,
            'route' => 'api/query/analyze',
            'options' => array(
                'minimalQuantity' => 25
            )
        ),
        
        /*
         * Gazetteer module - enable location based search
         * Note : set database options if gazetteer is not installed in RESTo database
         * 
         * !!! Require iTag !!!
         */
        'Gazetteer' => array(
            'activate' => false,
            'route' => 'api/gazetteer/search',
            'options' => array(
                'database' => array(
                    'dbname' => 'itag',
                    /*
                     * Database host - if not specified connect through unix domain socket (IPC socket) instead of TCP/IP socket
                     */
                    //'host' => 'localhost',
                    'user' => 'itag',
                    'password' => 'itag'
                )
            )
        ),
        
        /*
         * Wikipedia module - enable location based wikipedia entries display
         * 
         * !!! Require iTag !!!
         */
        'Wikipedia' => array(
            'activate' => false,
            'route' => 'api/wikipedia/search',
            'options' => array(
                'database' => array(
                    'dbname' => 'itag',
                    /*
                     * Database host - if not specified connect through unix domain socket (IPC socket) instead of TCP/IP socket
                     */
                    //'host' => 'localhost',
                    'user' => 'itag',
                    'password' => 'itag'
                )
            )
        ),
        
        /*
         * iTag module - automatically tag posted feature 
         * 
         * !!! Require iTag !!!
         */
        'iTag' => array(
            'activate' => false,
            'options' => array(
                'database' => array(
                    'dbname' => 'itag',
                    /*
                     * Database host - if not specified connect through unix domain socket (IPC socket) instead of TCP/IP socket
                     */
                    //'host' => 'localhost',
                    'user' => 'itag',
                    'password' => 'itag'
                ),
                'taggers' => array(
                    'Political' => array(),
                    'LandCover' => array()
                )
            )
        )
        
    )
);