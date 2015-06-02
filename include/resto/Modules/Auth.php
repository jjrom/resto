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
     * @param array $elements : route elements
     * @param array $data : POST or PUT parameters
     * 
     * @return string : result from run process in the $context->outputFormat
     */
    public function run($elements, $data = array()) {
        
        if (!$this->context) {
            RestoLogUtil::httpError(500, 'Invalid Context');
        }
        
        /*
         * Set POST data from resto
         */
        $this->data = $data;
        
        /*
         * Authentication issuer is identified as the first $elements
         */
        $issuerId = isset($elements[0]) ? RestoUtil::sanitize($elements[0]) : null;
        
        /*
         * Get provider
         */
        $provider = $this->getProvider($issuerId);
                
        /*
         * Authenticate from input protocol
         */
        switch ($provider['protocol']) {
            case 'oauth2':
                return $this->oauth2($issuerId, $provider);
            default:
                RestoLogUtil::httpError(400, 'Unknown sso protocol for issuer "' . $issuerId . '"');
          
        }
    }
    
    /**
     * Return user profile from access_token key
     * 
     * @param string $access_token
     */
    public function getProfileToken($issuerId, $access_token) {
        
        /*
         * Get provider
         */
        $provider = $this->getProvider($issuerId);
        
        /*
         * Get profile from SSO issuer
         */
        switch ($provider['protocol']) {
            case 'oauth2':
                $profile = $this->oauth2GetProfile($access_token, $provider);
                break;
            default:
                RestoLogUtil::httpError(400, 'Unknown sso protocol for issuer "' . $issuerId . '"');
        }
        
        /*
         * Return resto profile token
         */
        return $this->token($profile[$this->getUidKey($provider)]);
        
    }
    
    /**
     * Authenticate with generic Oauth2 API
     * 
     * @param string $issuerId
     * @param array $provider
     * 
     * @return json
     */
    private function oauth2($issuerId, $provider) {
        
        /*
         * Step 1. Get access token
         */
        $access_token = $this->oauth2GetAccessToken($issuerId, $provider['accessTokenUrl']);
        
        /*
         * Step 2. Get oauth profile
         */
        $profile = $this->oauth2GetProfile($access_token, $provider);
        
        /*
         * Insert user in resto database if needed
         */
        $this->createUserInDatabase($profile[$this->getUidKey($provider)]);
        
        return array(
            'token' => $this->token($profile[$this->getUidKey($provider)]
        ));
        
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
            ),
            'ssl' => isset($this->options['ssl']) ? $this->options['ssl'] : array()
        ))), true);
        
        return $postResponse['access_token'];
        
    }
    
    /**
     * Return resto profile using from OAuth2 server
     * 
     * @param string $access_token
     * 
     * @throws Exception
     */
    private function oauth2GetProfile($access_token, $provider) {
        
        $profileResponse = json_decode(file_get_contents($provider['peopleApiUrl'], false, stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Authorization: Bearer ' . $access_token . (isset($provider['forceJSON']) && $provider['forceJSON'] ? "\r\nx-li-format: json\r\n" : '')
            ),
            'ssl' => isset($this->options['ssl']) ? $this->options['ssl'] : array()
        ))), true);
        
        if (!isset($profileResponse) || empty($profileResponse[$this->getUidKey($provider)])) {
            RestoLogUtil::httpError(401, 'Unauthorized');
        }
        
        return $profileResponse;
        
    }
    
    /**
     * Insert user into resto database if needed
     * @param string $email
     * @throws Exception
     */
    private function createUserInDatabase($email) {
        
        try {
            $this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array(
                'email' => strtolower($email)
            ));
        } catch (Exception $e) {
            
            /*
             * User does not exist - create it
             */
            $this->context->dbDriver->store(RestoDatabaseDriver::USER_PROFILE, array(
                'profile' => array(
                    'email' => $email,
                    'activated' => 1
                ))
            );
            return true;
        }
        
        return false;
    }
    
    /**
     * Return SSO provider
     * 
     * @param string $issuerId
     */
    private function getProvider($issuerId) {
        
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
        
        return $provider;
        
    }
    
    /**
     * Return profile token if profile exist - throw exception otherwise
     * 
     * @param string $key
     * @return json
     */
    private function token($key) {
        
        $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array(
            'email' => strtolower($key)
        )), $this->context);
        
        if (!isset($user->profile['email'])) {
            RestoLogUtil::httpError(401, 'Unauthorized');
        }
        
        return $this->context->createToken($user->profile['userid'], $user->profile);
        
    }
    
    /**
     * Get provider uidKey
     * 
     * @param array $provider
     * @return string
     */
    private function getUidKey($provider) {
        return isset($provider['uidKey']) ? $provider['uidKey'] : 'email';
    }
    
}
