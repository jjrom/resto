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
 * Authentication module
 * 
 * This module allows authentication from SSO servers
 * 
 * Predefiend supported authentication servers :
 *      - Google
 *      - Linkedin
 * 
 * Generic supported authentication method :
 *      - OAuth 1.0
 *      - OAuth 2.0
 * 
 */
class Auth extends RestoModule {
    
    /*
     * Data
     */
    private $data = array();
    
    /*
     * Identity providers
     */
    private $providers = array();
    
    /*
     * Known providers configuration
     */
    private $providersConfig = array(
        
        /*
         *  {
         *    "kind": "plus#personOpenIdConnect",
         *    "gender": "male",
         *    "sub": "123456",
         *    "name": "John Doe",
         *    "given_name": "John",
         *    "family_name": "Do",
         *    "profile": "https:\\\/\\\/plus.google.com\\\/123456",
         *    "picture": "https:\\\/\\\/lh4.googleusercontent.com\\\/-sdsdf\\\/sdfqsd\\\/qsdfsqd\\\/qsdf\\\/photo.jpg?sz=50",
         *    "email": "john.doe@dev.null",
         *    "email_verified": "true",
         *    "locale": "fr"
         *  } 
         */
        'google' => array(
            'protocol' => 'oauth2',
            'accessTokenUrl' => 'https://accounts.google.com/o/oauth2/token',
            'peopleApiUrl' => 'https://www.googleapis.com/plus/v1/people/me/openIdConnect',
            'uidKey' => 'email'
        ),
        
        /*
         * linkedin
         * 
         *  {
         *      "emailAddress": "john.doe@dev.null",
         *      "firstName": "John",
         *      "id": "xxxx",
         *      "lastName": "Doe",
         *      "pictureUrl": "https:\\\/\\\/media.licdn.com\\\/mpr\\\/mprx\\\/dvsdgfsdfgs9B-TjLa1rdXl2a"
         *  }
         * 
         */
        'linkedin' => array(
            'protocol' => 'oauth2',
            'accessTokenUrl' => 'https://www.linkedin.com/uas/oauth2/accessToken',
            'peopleApiUrl' => 'https://api.linkedin.com/v1/people/~:(id,first-name,last-name,email-address,picture-url)',
            'uidKey' => 'emailAddress',
            'forceJSON' => true
        )
    );
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoContext $user
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
        $this->providers = isset($this->context->modules[get_class($this)]['providers']) ? $this->context->modules[get_class($this)]['providers'] : array();
    }

    /**
     * Run module - this function should be called by Resto.php
     * 
     * @param array $params : input parameters
     * @param array $data : POST or PUT parameters
     * 
     * @return string : result from run process in the $context->outputFormat
     */
    public function run($params, $data = array()) {
        
        if (!$this->context) {
            RestoLogUtil::httpError(500, 'Invalid Context');
        }
        
        /*
         * Set POST data from resto
         */
        $this->data = $data;
        
        /*
         * Authentication issuer is identified as the first $params
         */
        $issuerId = isset($params[0]) ? RestoUtil::sanitize($params[0]) : null;
        
        /*
         * Get provider
         */
        if (isset($this->providers[$issuerId])) {

            /*
             * Search for known providers first
             */
            if (isset($this->providersConfig[$issuerId])) {
                $provider = $this->providersConfig[$issuerId];
            }
            else {
                $provider = $this->providers[$issuerId];
            }
            
        }
        
        /*
         * No provider => exit
         */
        if (!isset($provider)) {
            RestoLogUtil::httpError(400, 'No configuration found for issuer "' . $issuerId . '"');
        }
        
        /*
         * Authenticate from input protocol
         */
        switch ($provider['protocol']) {
            
            case 'oauth2':
                return $this->oauth2(array(
                    'issuerId' => $issuerId,
                    'accessTokenUrl' => $provider['accessTokenUrl'],
                    'peopleApiUrl' => $provider['peopleApiUrl'],
                    'forceJSON' => isset($provider['forceJSON']) ? $provider['forceJSON'] : false,
                    'uidKey' => $provider['uidKey']
                ));
            default:
                RestoLogUtil::httpError(400, 'Unknown sso protocol for issuer "' . $issuerId . '"');
          
        }
    }
    
    
    /**
     * Authenticate with generic Oauth2 API
     * 
     * @param array $config
     * 
     * @return json
     */
    private function oauth2($config) {
        
        /*
         * Step 1. Get access token
         */
        $access_token = $this->oauth2GetAccessToken($config['issuerId'], $config['accessTokenUrl']);
        
        /*
         * Step 2. Return profile
         */
        return $this->oauth2GetProfile($config['peopleApiUrl'], $access_token, isset($config['uidKey']) ? $config['uidKey'] : 'email', isset($config['forceJSON']) ? $config['forceJSON'] : false);
        
    }
    
    /**
     * Get OAuth2 access token
     * 
     * @param string $issuerId
     * @param string $accessTokenUrl
     * 
     * @return string
     */
    private function oauth2GetAccessToken($issuerId, $accessTokenUrl) {
        
        if (!isset($this->data['code']) || !isset($this->data['redirectUri'])) {
            RestoLogUtil::httpError(400);
        }
        
        $postResponse = json_decode(file_get_contents($accessTokenUrl, false, stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query(array(
                    'code' => $this->data['code'],
                    'client_id' => $this->providers[$issuerId]['clientId'],
                    'redirect_uri' => $this->data['redirectUri'],
                    'grant_type' => 'authorization_code',
                    'client_secret' => $this->providers[$issuerId]['clientSecret']
                ))
            )
        ))), true);
        
        return $postResponse['access_token'];
        
    }
    
    /**
     * Return resto profile using from OAuth2 server
     * 
     * @param string $peopleApiUrl
     * @param string $access_token
     * @param string $uidKey
     * @param boolean $forceJSON
     * 
     * @throws Exception
     */
    private function oauth2GetProfile($peopleApiUrl, $access_token, $uidKey, $forceJSON = false) {
        
        $profileResponse = json_decode(file_get_contents($peopleApiUrl, false, stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Authorization: Bearer ' . $access_token . ($forceJSON ? "\r\nx-li-format: json\r\n" : '')
            )
        ))), true);
        
        if (!isset($profileResponse) || empty($profileResponse[$uidKey])) {
            RestoLogUtil::httpError(500, 'Authorization failed');
        }
        
        return $this->token($profileResponse[$uidKey]);
        
    }
    
    /**
     * Return profile token
     * @param string $key
     * @return json
     */
    private function token($key) {
        
        if (!isset($key)) {
            throw new Exception();
        }
        
        $profile = $this->context->dbDriver->getUserProfile(strtolower($key));
        
        return array(
            'token' => $this->context->createToken($profile['userid'], $profile
        ));
    }
    
}
