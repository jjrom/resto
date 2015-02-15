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
     * Identity providers
     */
    private $providers = array();
    
    /*
     * Route to module
     */
    private $route = 'api/auth/';
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoContext $user
     * @param array $options : array of module parameters
     */
    public function __construct($context, $user, $options = array()) {
        parent::__construct($context, $user, $options);
        if (isset($options['providers']) && is_array($options['providers'])) {
            $this->providers = $options['providers'];
        }
    }

    /**
     * Run module - this function should be called by Resto.php
     * 
     * @param array $params : input parameters
     * @return string : result from run process in the $context->outputFormat
     */
    public function run($params) {
        
        if (!$this->context) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Invalid Context', 500);
        }
        
        /*
         * Authentication issuer is identified as the first $params
         */
        $issuerId = isset($params[0]) ? RestoUtil::sanitize($params[0]) : null;
        
        /*
         * Identity Provider configuration is mandatory (see config.php)
         */
        foreach (array_keys($this->providers) as $key) {
    
            /*
             * Redirect to known providers
             */
            if ($issuerId === $key) {
                switch ($issuerId) {
                    case 'google':
                        return $this->google();
                    case 'linkedin':
                        return $this->linkedin();
                    default:
                        if (!isset($this->providers[$key]['protocol'])) {
                            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Unknown sso protocol for issuer "' . $issuerId . '"', 400);
                        }
                        if ($this->providers[$key]['protocol'] === 'oauth2') {
                            return $this->oauth2($key);
                        }
                        else if ($this->providers[$key]['protocol'] === 'oauth1') {
                            return $this->oauth1();
                        }
                        else {
                            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Unknown sso protocol for issuer "' . $issuerId . '"', 400);
                        }
                }
            }
        }
        
        throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'No configuration found for issuer "' . $issuerId . '"', 400);

    }
    
    /**
     * Get params from input request
     * 
     * @throws Exception
     * @return string
     */
    private function getParams() {
        
        if ($this->context->method === 'GET') {
            return RestoUtil::sanitize($_GET);
        }
        /*
         * POST request : code is provided within JSON stream
         */
        else if ($this->context->method === 'POST') {
            $data = RestoUtil::readInputData();
            if (!is_array($data) || count($data) === 0) {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Bad Request', 400);
            }
            return $data;
        }
        /*
         * Other HTTP methods are not allowed
         */
        else {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }
        
    }
    
    /**
     * Get OAuth2 access token
     * 
     * @param string $issuerId
     * @param string $accessTokenUrl
     * 
     * @return string
     */
    private function getOAuth2AccessToken($issuerId, $accessTokenUrl) {
        
        $params = $this->getParams();
        if (!isset($params['code']) || !isset($params['redirectUri'])) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Bad Request', 400);
        }
        $ch = curl_init($accessTokenUrl);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'code' => $params['code'],
                'client_id' => $this->providers[$issuerId]['clientId'],
                'redirect_uri' => $params['redirectUri'],
                'grant_type' => 'authorization_code',
                'client_secret' => $this->providers[$issuerId]['clientSecret']
            ))
        ));
        $jsonData = json_decode(curl_exec($ch), true);
        curl_close($ch);
        if (!isset($jsonData) || !isset($jsonData['access_token'])) {
            throw new Exception();
        }
        
        return $jsonData['access_token'];
    }
    
    /**
     * Authenticate with google Oauth2 API
     * 
     * @return json
     */
    private function google() {
        
        /*
         * Google configuration
         */
        $issuerId = 'google';
        $accessTokenUrl = 'https://accounts.google.com/o/oauth2/token';
        $peopleApiUrl = 'https://www.googleapis.com/plus/v1/people/me/openIdConnect';
        
        try {
            
            /*
             * Step 1. Exchange authorization code for access token
             */
            $access_token = $this->getOAuth2AccessToken($issuerId, $accessTokenUrl);
            
            /*
             * Step 2. Get profile from access_token
             * 
             * {
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
            $profileResponse = json_decode(file_get_contents($peopleApiUrl, false, stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'header' => "Authorization: Bearer " . $access_token
                )
            ))), true);
            
            if (isset($profileResponse)) {
                $profile = $this->context->dbDriver->getUserProfile(strtolower($profileResponse['email']));
                return RestoUtil::json_format(array(
                    'token' => $this->context->createToken($profile['userid'], $profile
                )));
            }
            else {
                throw new Exception();
            }
            
        } catch (Exception $ex) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Authorization failed', 400);
        }

    }
    
    /**
     * Authenticate with Linkedin Oauth2 API
     * 
     * @return json
     */
    private function linkedin() {
        
        /*
         * Linkedin configuration
         */
        $issuerId = 'linkedin';
        $accessTokenUrl = 'https://www.linkedin.com/uas/oauth2/accessToken';
        $peopleApiUrl = 'https://api.linkedin.com/v1/people/~:(id,first-name,last-name,email-address,picture-url)';
        
        try {
            
            /*
             * Step 1. Exchange authorization code for access token
             */
            $access_token = $this->getOAuth2AccessToken($issuerId, $accessTokenUrl);
            
            /*
             * Step 2. Get profile from access_token
             * 
             * {
             *      "emailAddress": "john.doe@dev.null",
             *      "firstName": "John",
             *      "id": "xxxx",
             *      "lastName": "Doe",
             *      "pictureUrl": "https:\\\/\\\/media.licdn.com\\\/mpr\\\/mprx\\\/dvsdgfsdfgs9B-TjLa1rdXl2a"
             *  }
             */
            $profileResponse = json_decode(file_get_contents($peopleApiUrl, false, stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'header' => "Authorization: Bearer " . $access_token . "\r\n" . "x-li-format: json\r\n"
                )
            ))), true);
            if (isset($profileResponse)) {
                $profile = $this->context->dbDriver->getUserProfile(strtolower($profileResponse['emailAddress']));
                return RestoUtil::json_format(array(
                    'token' => $this->context->createToken($profile['userid'], $profile
                )));
            }
            else {
                throw new Exception();
            }
            
        } catch (Exception $ex) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Authorization failed', 400);
        }

    }
    
    /**
     * Authenticate with generic Oauth2 API
     * 
     * @param string $issuerId
     * @return json
     */
    private function oauth2($issuerId) {
        
        try {
            
            /*
             * Step 1. Exchange authorization code for access token
             */
            $access_token = $this->getOAuth2AccessToken($issuerId, $this->providers[$issuerId]['accessTokenUrl']);
            
            /*
             * Step 2. Get profile from access_token
             */
            $ch = curl_init($this->providers[$issuerId]['peopleApiUrl']);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token)
            ));
            $profileResponse = json_decode(curl_exec($ch), true);
            curl_close($ch);
            error_log(json_encode($profileResponse));
            
            /*
            $profileResponse = json_decode(file_get_contents($this->providers[$issuerId]['peopleApiUrl'], false, stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'header' => "Authorization: Bearer " . $access_token
                )
            ))), true);*/
            error_log(json_encode($profileResponse));
            if (isset($profileResponse)) {
                $profile = $this->context->dbDriver->getUserProfile(strtolower($profileResponse['email']));
                return RestoUtil::json_format(array(
                    'token' => $this->context->createToken($profile['userid'], $profile
                )));
            }
            else {
                throw new Exception();
            }
            
        } catch (Exception $ex) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Authorization failed', 400);
        }

    }
    
}
