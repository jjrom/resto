<?php
/*
 * Copyright 2018 Jérôme Gasperi
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
 * Authentication add-on
 *
 * This add-on allows authentication from SSO servers
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
class Auth extends RestoAddOn
{
    /**
     * Add-on version
     */
    public $version = '1.2.1';

    /*
     * Data
     */
    private $data = array();

    /*
     * Known providers configuration
     */
    private $providersConfig = array(

        /*
         * Google
         */
        'google' => array(
            'protocol' => 'oauth2',
            'accessTokenUrl' => 'https://accounts.google.com/o/oauth2/token',
            'peopleApiUrl' => 'https://people.googleapis.com/v1/people/me?personFields=emailAddresses,names,nicknames,photos',
            'forceCreation' => true
        ),

        /*
         * Authentication with google using the JWT token
         *
         *  {
         *      "iss": "https://accounts.google.com",
         *      "iat": "1486397062",
         *      "exp": "1486400662",
         *      "at_hash": "XQUIDHFIDbcd",
         *      "aud": "xxxxxx.apps.googleusercontent.com",
         *      "sub": "110613268514751241292",
         *      "email_verified": "true",
         *      "azp": "xxxxxx.apps.googleusercontent.com",
         *      "email": "xxxxx@gmail.com",
         *      "name": "Jérôme G",
         *      "picture": "https://lh4.googleusercontent.com/-b2ZwDfR874M/AAAAAAAAAAI/AAAAAAAAAv4/xxxxx/photo.jpg",
         *      "given_name": "Jérôme",
         *      "family_name": "G",
         *      "locale": "fr",
         *      "alg": "RS256",
         *      "kid": "2f7c552b3b91db466e73f0972rt6b19c5f0dd8e"
         *  }
         */
        'googlejwt' => array(
            'protocol' => 'jwt',
            'validationUrl' => 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=',
            'checkProperty' => 'sub',
            'mapping' => array(
                'email' => 'email',
                'firstname' => 'given_name',
                'lastname' => 'family_name',
                'picture' => 'picture'
            ),
            'forceCreation' => true
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
            'mapping' => array(
                'email' => 'emailAddress'
            ),
            'forceJSON' => true
        ),

        /*
         * facebook
         *
         *  {
         *      "email": "john.doe@dev.null",
         *      "name": "John",
         *      "id": "xxxx"
         *  }
         *
         */
        'facebook' => array(
            'protocol' => 'oauth2',
            'accessTokenUrl' => 'https://graph.facebook.com/oauth/access_token',
            'peopleApiUrl' => 'https://graph.facebook.com/me/?fields=email,first_name,last_name,id',
            'mapping' => array(
                'email' => 'email',
                'firstname' => 'first_name',
                'lastname' => 'last_name'
            ),
            'forceCreation' => true
        ),

        /*
         * Theia
         */
        'theia' => array(
            'protocol' => 'oauth2',
            'accessTokenUrl' => 'https://sso.theia-land.fr/oauth2/token',
            'peopleApiUrl' => 'https://sso.theia-land.fr/oauth2/userinfo?schema=openid',
            'useUrlEncoded' => true,
            'forceCreation' => true
        ),
        /*
         * EDITO
         */
        'edito' => array(
            'protocol' => 'oauth2',
            'useUrlEncoded' => true,
            'forceCreation' => true
        )

    );

    /**
     * Constructor
     *
     * @param RestoContext $context
     * @param RestoContext $user
     */
    public function __construct($context, $user)
    {
        parent::__construct($context, $user);
    }

    /**
     * Main function - return authentication token as JWT
     *
     * @param array $params : route parameters
     * @param array $data : POST or PUT parameters
     *
     * @return string
     */
    public function authenticate($params, $data = array())
    {

        // Authentication issuer is mandatory
        if (!isset($params) || !isset($params['issuerId'])) {
            RestoLogUtil::httpError(400, 'Missing issuerId');
        }

        /*
         * Set POST data from resto
         */
        $this->data = $data;

        if (!isset($this->data['code']) || !isset($this->data['redirectUri'])) {
            error_log("No code and redirect uri provided");
            RestoLogUtil::httpError(400);
        }

        /*
         * Get provider
         */
        $provider = $this->getProvider($params['issuerId']);

        /*
         * Authenticate from input protocol
         */
        switch ($provider['protocol']) {
            case 'oauth2':
                return $this->oauth2($provider);
            default:
                RestoLogUtil::httpError(400, 'Unknown sso protocol for issuer "' . $params['issuerId'] . '"');
        }
    }

    /**
     * Authenticate using idp external token
     *
     * @param array $params : route parameters
     * @param array $data : POST or PUT parameters
     *
     * @return string
     */
    public function authenticateWithToken($params, $data = array())
    {

        // Authentication issuer is mandatory
        if ( !isset($params) || !isset($params['issuerId']) )  {
            RestoLogUtil::httpError(400, 'Missing input issuerId');
        }

        /*
         * Set POST data from resto
         */
        $this->data = $data;

        if (!(isset($this->data['token']))) {
            error_log("No token provided");
            RestoLogUtil::httpError(400, 'Missing input token');
        }

        /*
         * Get provider
         */
        $provider = $this->getProvider($params['issuerId']);

        /*
         * Authenticate from input protocol
         */
        switch ($provider['protocol']) {
            case 'oauth2':
                return $this->oauth2($provider);
            default:
                RestoLogUtil::httpError(400, 'Unknown sso protocol for issuer "' . $params['issuerId'] . '"');
        }
    }

    /**
     * Return user profile from token key
     *
     * @param string $token
     */
    public function getProfileToken($issuerId, $token)
    {

        /*
         * Get provider
         */
        $provider = $this->getProvider($issuerId);

        /*
         * Get profile from SSO issuer
         */
        switch ($provider['protocol']) {
            case 'oauth2':
                $profile = $this->convertProfile($this->oauth2GetProfile($token, $provider), $provider);
                break;
            case 'jwt':
                $profile = $this->convertProfile($this->jwtGetProfile($token, $provider), $provider);
                break;
            default:
                RestoLogUtil::httpError(400, 'Unknown sso protocol for issuer "' . $issuerId . '"');
        }

        /*
         * Return resto profile token
         */
        return $this->tokenAndProfile($profile, $provider)['token'];
    }

    /**
     * Validate an OpenId Connect token
     *
     * @param array $provider
     */
    private function validateOpenIDToken($provider) {

        $token = $this->data['token'];
        $audience = $provider['clientId']; // TODO - verify Token

        if ( empty($provider['openidConfigurationUrl']) ) {
            RestoLogUtil::httpError(400, 'Missing openIdConfigurationUrl in provider configuration');
        }
        $openidConfigurationUrl = $provider['openidConfigurationUrl'];
        $openidConfiguration = json_decode(file_get_contents($openidConfigurationUrl), true);

        // Extract the key URL
        $jwksUri = $openidConfiguration['jwks_uri'];

        // Fetch the JSON Web Key Set (JWKS)
        $jwks = json_decode(file_get_contents($jwksUri), true);

        // Decode and validate the token
        $parts = explode('.', $token);
        $header = json_decode(base64_decode($parts[0]), true);
        // Find the correct key to verify the token
        $keys = array_filter($jwks['keys'], function ($key) use ($header) {
            return $key['kid'] == $header['kid'];
        });
        if (empty($keys)) {
            throw new Exception('Unable to find key to verify token');
        }

        foreach ($keys as $key) {
            // Get the algorithm from the token header
            $publicKey = "-----BEGIN CERTIFICATE-----\n" . $key['x5c'][0] . "\n-----END CERTIFICATE-----\n";
            try {
                $decoded = JWT::decode($token, $publicKey, ['RS512']);
                return $token;
            } catch (Exception $e) {
                error_log($e);
                RestoLogUtil::httpError(401);
            }
        }
        error_log("cannot validate token\n");
        throw new Exception('Token verification failed');
    }


    /**
     * Authenticate with generic Oauth2 API
     *
     * @param array $provider
     *
     * @return json
     */
    private function oauth2($provider)
    {

        /*
         * Step 1. Get access token
         */
        if (isset($this->data['code']) && isset($this->data['redirectUri'])){
            $accessToken = $this->oauth2GetAccessToken($provider);
        }
        elseif (isset($this->data['token']) ){
            $accessToken = $this->validateOpenIDToken($provider);
        }

        /*
         * Step 2. Get oauth profile
         */
        $profile = $this->convertProfile($this->oauth2GetProfile($accessToken, $provider), $provider);

        /*
         * Insert user in resto database if needed
         */
        $this->createUserInDatabase($profile);

        return $this->tokenAndProfile($profile, $provider);
    }

    /**
     * Get OAuth2 access token
     *
     * @param array $provider
     *
     * @return string
     */
    private function oauth2GetAccessToken($provider)
    {

        $params = array(
            'code' => $this->data['code'],
            'client_id' => $provider['clientId'],
            'redirect_uri' => $this->data['redirectUri'],
            'grant_type' => 'authorization_code',
            'client_secret' => $provider['clientSecret']
        );

        try {
            $curl = new Curly();

            if ( isset($provider['useUrlEncoded']) && $provider['useUrlEncoded'] ) {
                $curl->setHeaders(array(
                    'Content-Type:application/x-www-form-urlencoded'
                ));
                $postResponse = json_decode($curl->post($provider['accessTokenUrl'] , http_build_query($params)), true);
            }
            else {
                $postResponse = json_decode($curl->post($provider['accessTokenUrl'], json_encode($params)), true);
            }

            $curl->close();
        } catch (Exception $e) {
            $curl->close();
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }

        if ( isset($postResponse['error']) ) {
            RestoLogUtil::httpError(400, $postResponse['error']);
        }

        return $postResponse['access_token'] ?? null;
    }

    /**
     * Return resto profile using from OAuth2 server
     *
     * @param string $accessToken
     *
     * @throws Exception
     */
    private function oauth2GetProfile($accessToken, $provider)
    {

        try {
            $curl = new Curly();
            $curl->setHeaders(array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $accessToken . (isset($provider['forceJSON']) && $provider['forceJSON'] ? "\r\nx-li-format: json\r\n" : '')
            ));
            $profileResponse = json_decode($curl->get($provider['peopleApiUrl']), true);
            $curl->close();
        } catch (Exception $e) {
            $curl->close();
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }

        if ( !isset($profileResponse) ) {
            RestoLogUtil::httpError(401, 'Unauthorized');
        }

        return $profileResponse;
    }

    /**
     * Return resto profile using from JWT token
     *
     * @param string $token
     *
     * @throws Exception
     */
    private function jwtGetProfile($jwt, $provider)
    {
        $data = @file_get_contents($provider['validationUrl'] . $jwt, false, stream_context_create(array(
            'http' => array(
                'method' => 'GET'
            ),
            'ssl' => isset($this->options['ssl']) ? $this->options['ssl'] : array()
        )));

        if (!$data) {
            RestoLogUtil::httpError(401);
        }

        $profileResponse = json_decode($data, true);

        // 'checkProperty' must be present otherwise there is an error
        if (!isset($profileResponse) || !isset($profileResponse[$provider['checkProperty']])) {
            RestoLogUtil::httpError(401);
        }

        return $profileResponse;
    }

    /**
     * Insert user into resto database if needed
     *
     * @param array $profile
     * @throws Exception
     */
    private function createUserInDatabase($profile)
    {

        try {
            (new UsersFunctions($this->context->dbDriver))->getUserProfile('email', strtolower($profile['email']));
        } catch (Exception $e) {

            /*
             * User does not exist - create it
             */
            return $this->storeUser($profile);
        }

        return false;
    }

    /**
     * Return SSO provider
     *
     * @param string $issuerId
     */
    private function getProvider($issuerId)
    {

        /*
         * Get providers from input ADDON_AUTH_PROVIDERS
         */
        $providers = $this->getProviders($this->options['providers'] ?? null);

        /*
         * No provider => exit
         */
        if ( !isset($providers[$issuerId])) {
            RestoLogUtil::httpError(400, 'No configuration found for issuer "' . $issuerId . '"');
        }

        /*
         * Search for known providers first
         */
        if (isset($this->providersConfig[$issuerId])) {
            $provider = array_merge($this->providersConfig[$issuerId], $providers[$issuerId]);
        } else {
            $provider = $providers[$issuerId];
        }

        /*
         * Set default protocol to oauth2 if not set
         */
        if ( !isset($provider['protocol']) ) {
            $provider['protocol'] = 'oauth2';
        }

        return $provider;
    }

    /**
     * Return profile token if profile exist - throw exception otherwise
     *
     * @param Array $profile
     * @param Array $provider
     * @return json
     */
    private function tokenAndProfile($profile, $provider)
    {
        if (isset($profile['email'])) {
            try {
                $user = new RestoUser(array('email' => strtolower($profile['email'])), $this->context, true);
            } catch (Exception $e) {}
        }

        // User exists => return JWT
        if (isset($user) && isset($user->profile['username'])) {
            $this->setGroups($user, $profile, $provider);
            return array(
                'token' => $this->context->createJWT($user->profile['username'], $this->context->core['tokenDuration'], null),
                'profile' => $user->profile
            );
        }

        // User does not exist => Special case - create it
        if (isset($provider['forceCreation']) && $provider['forceCreation']) {
            $restoProfile = $this->storeUser($profile);
            $this->setGroups($user, $profile, $provider);
            return array(
                'token' => $this->context->createJWT($restoProfile['username'], $this->context->core['tokenDuration'], null),
                'profile' => $restoProfile
            );
        }

        return RestoLogUtil::httpError(401, 'Unauthorized');
    }

    /**
     * Store user in database
     *
     * @param  array $profile
     * @param  array $provider
     * @return array
     */
    private function storeUser($profile)
    {
        return (new UsersFunctions($this->context->dbDriver))->storeUserProfile(array_merge($profile, array(
            'username' => $this->generateUsername($profile),
            'activated' => 1,
            'validatedby' => $this->context->core['userAutoValidation'] ? 'auto' : null
        )), $this->context->core['storageInfo']);

    }

    /**
     * Set groups from profile if any
     * 
     * @param {RestoUser} $user
     * @param array $profile
     * @param array $provider
     */
    private function setGroups($user, $profile, $provider)
    {
        
        if ( !$this->options['setGroups'] || !isset($profile['groups'])) {
            return;
        }
        
        $groupsFunctions = new GroupsFunctions($this->context->dbDriver);
        $userGroups = $groupsFunctions->getGroups(array('userid' => $user->profile['id']));
        $userGroupNames = array_map(function($g) { return $g['name']; }, $userGroups);

        // Add user to groups in inputGroups not already associated
        foreach ($inputGroups as $groupName) {
            if (!in_array($groupName, $userGroupNames)) {
                $group = $groupsFunctions->getGroup($groupName);
                if (isset($group['id'])) {
                    $groupsFunctions->addUserToGroup(array('id' => $group['id']), $user->profile['id'], true);
                }
            }
        }

        // Remove user from groups not in inputGroups
        foreach ($userGroups as $group) {
            if (!in_array($group['name'], $inputGroups)) {
                $groupsFunctions->removeUserFromGroup(array('id' => $group['id']), $user->profile['id'], true);
            }
        }

    }

    /**
     * Convert profile from provider to resto profile
     *
     * @param {Array} $profile
     * @param {array} $provider
     */
    private function convertProfile($profile, $provider)
    {
        switch ($provider['id']) {

            case 'google':
                return $this->convertGoogle($profile);

            case 'facebook':
                return $this->convertFacebook($profile);

            case 'theia':
                return $this->convertTheia($profile);

            case 'edito':
                return $this->convertEdito($profile);

            default:
                return $this->convertGeneric($provider, $profile);
        }

    }

    /**
     * Return resto profile from google profile
     *
     * {
     *      "resourceName": "people/110613268514751241292",
     *      "names":[
     *          {
     *              "displayName":"Jérôme Gasperi",
     *              "familyName":"Gasperi",
     *              "givenName":"Jérôme",
     *              "displayNameLastFirst":"Gasperi, Jérôme",
     *              "unstructuredName":"Jérôme Gasperi"
     *          }
     *      ],
     *      "nicknames":[
     *          {
     *              "value":"jrom",
     *              "type":"ALTERNATE_NAME"
     *          }
     *      ],
     *      "photos":[
     *          {
     *              "url":"https://lh3.googleusercontent.com/a-/AOh14GgIJitSkG_3bc-dHO3O2o-j7Zs5F0mJdH4PNjJRrA=s100"
     *          }
     *      ],
     *      "emailAddresses":[
     *          {
     *              "metadata":{
     *                  "source":{
     *                      "id":110613268514751241292
     *                  }
     *              }
     *              "value": "Jerome.Gasperi@gmail.com"
     *          }
     *      ]
     *  }
     *
     * @param {Array} $profile
     * @return {Array}
     */
    private function convertGoogle($profile)
    {

        return array(
            'email' => isset($profile['emailAddresses']) &&  isset($profile['emailAddresses'][0]) ? $profile['emailAddresses'][0]['value'] : null,
            'firstname' => isset($profile['names']) &&  isset($profile['names'][0]) ? $profile['names'][0]['givenName'] : null,
            'lastname' => isset($profile['names']) &&  isset($profile['names'][0]) ? $profile['names'][0]['familyName'] : null,
            'username' => isset($profile['names']) &&  isset($profile['names'][0]) ? $profile['names'][0]['displayName'] : null,
            'picture' => isset($profile['photos']) &&  isset($profile['photos'][0]) ? $profile['photos'][0]['url'] : null,
            'externalidp' => array(
                'google' => $profile
            )
        );

    }

    /**
     * Convert facebook profile to resto profile
     *
     * @param {Array} $profile
     * @return {Array}
     */
    private function convertFacebook($profile)
    {
        return $profile;
    }

    /**
     * Convert theia profile to resto profile
     *
     *  {
     *       "http://theia.org/claims/emailaddress": "jerome.gasperi@gmail.com",
     *       "http://theia.org/claims/givenname": "Jérôme",
     *       "http://theia.org/claims/lastname": "Gasperi",
     *       "http://theia.org/claims/organization": "Centre National d'Etudes Spatiales (CNES) - Projet Pôle Thématique Surfaces Continentales (THEIA)",
     *       "http://theia.org/claims/function": "Senior Expert",
     *       "http://theia.org/claims/type": "person",
     *       "http://theia.org/claims/telephone": "+33561282523",
     *       "http://theia.org/claims/streetaddress": "18 avenue Edouard Belin\r\n31400 Toulouse\r\nFrance",
     *       "http://theia.org/claims/source": "theia",
     *       "http://theia.org/claims/country": "FR",
     *       "http://theia.org/claims/ignkey": "",
     *       "http://theia.org/claims/ignauthentication": "",
     *       "http://theia.org/claims/role": "Internal/admin,Application/resto.mapshup.com,Internal/identity,Internal/everyone,Internal/oauth_admin,Application/sparkindata.com",
     *       "http://theia.org/claims/regDate": 1426240519617,
     *       "http://theia.org/claims/foreignauthorization": "false"
     *   }
     *
     * @param {Array} $profile
     * @return {Array}
     */
    private function convertTheia($profile)
    {

        return array(
            'email' => $profile['http://theia.org/claims/emailaddress'] ?? null,
            'firstname' => $profile['http://theia.org/claims/givenname'] ?? null,
            'lastname' => $profile['http://theia.org/claims/lastname'] ?? null,
            'country' => $profile['http://theia.org/claims/country'] ?? null,
            'organization' => $profile['http://theia.org/claims/organization'] ?? null,
            'externalidp' => array(
                'theia' => $profile
            )
        );

    }

    /**
     * 
     * Example of EDITO profile return
     * {
     *      "sub": "5f3febcc-3cd6-47b4-a208-c50684d48cd7",
     *      "email_verified": true,
     *      "name": "J G",
     *      "groups": [
     *        "CONTRIBUTION",
     *        "EDITO_RESTRICTED_SERVICE_CATALOG",
     *        "EDITO_USER",
     *        "chlorophyll",
     *        "coclico",
     *        "infra",
     *        "mer-ep",
     *        "nature-based-solution",
     *        "omi",
     *        "plastic-marine-debris-drift",
     *        "sargasse"
     *      ],
     *      "preferred_username": "jg",
     *      "given_name": "J",
     *      "family_name": "G",
     *      "email": "jg@example.com"
     * }
     */
    private function convertEdito($profile)
    {
        return array(
            'email' => $profile['email'] ?? null,
            'username' => $profile['preferred_username'] ?? null,
            'firstname' => $profile['given_name'] ?? null,
            'lastname' => $profile['family_name'] ?? null,
            'externalidp' => array(
                'edito' => $profile
            ),
            'groups' => isset($profile['groups']) ? (is_array($profile['groups']) ? $profile['groups'] : explode(',', $profile['groups'])) : null
        );
    }

    /**
     * Convert profile from input provider mapping, leaves untouched otherwise
     *
     * @param array $provider
     * @param array $profile
     */
    private function convertGeneric($provider, $profile)
    {

        if ( !isset($provider['mapping']) ) {
            return $profile;
        }

        $convertedProfile = array(
            'externalidp' => array(
                $provider['id'] => $profile
            )
        );

        // Input mapping is "email=email,firstname=given_name,lastname=family_name"
        $keyValues = explode(',', $provider['mapping']);
        for ($i = 0, $ii = count($keyValues); $i < $ii; $i++) {
            $split = explode('=', $keyValues[$i]);
            if (count($split) == 2) {
                $convertedProfile[$split[0]] = $split[1];
            }
        }

        return $convertedProfile;

    }

    /**
     * Get providers from input string $str
     * Format of $str is
     *
     *  providerId1|clientId1|clientSecret1|(accessTokenUrl)|(peopleApiUrl)|(openidConfigurationUrl)|(mapping);providerId2|clientId2|clientSecret2;...etc...
     *
     * Where :
     *   - parts in () are optionals
     *   - mapping allows the conversion of properties names returned by the IdP to
     *     resto names. It is a string like "email=xxx,firstname=yyyy,lastname=zzzz"
     *     For instance, the mapping for theia oauth2 IdP would be :
     *       "email=email,firstname=given_name,lastname=family_name"
     *
     * @param {String} $str
     */
    private function getProviders($str)
    {
        $providers = array();

        if ( !isset($str) ) {
            return $providers;
        }

        $arr = explode(';', $str);
        for ($i = 0, $ii = count($arr); $i < $ii; $i++) {
            $split = explode('|', $arr[$i]);
            if (count($split) > 0) {
                $id = trim($split[0]);
                $providers[$id] = array(
                    'id' => $id,
                    'clientId' => (isset($split[1]) ? trim($split[1]) : null) ?? '',
                    'clientSecret' => (isset($split[2]) ? trim($split[2]) : null) ?? '',
                    'accessTokenUrl' => (isset($split[3]) ? trim($split[3]) : null) ?? null,
                    'peopleApiUrl' => (isset($split[4]) ? trim($split[4]) : null) ?? null,
                    'openidConfigurationUrl' => (isset($split[5]) ? trim($split[5]) : null) ?? null,
                    'mapping' => (isset($split[6]) ? trim($split[6]) : null) ?? null
                );
            }
        }

        return $providers;

    }

    /**
     * Generate a unique username from profile
     * (see https://stackoverflow.com/questions/43232989/how-to-generate-unique-username-php)
     *
     * @param array $profile
     * @return string
     */
    private function generateUsername($profile)
    {

        $username = $profile['username'] ?? null;
        $firstname = strtolower($profile['firstname'] ?? str_replace(array('.', '-', '_'), '', explode('@', $profile['email'])[0]));
        $lastname = strtolower($profile['lastname'] ?? 'doe');

        /**
         * an array of numbers that may be used as suffix for the user names index 0 would be the year
         * and index 1, 2 and 3 would be month, day and hour respectively.
         */
        $numSufix = explode('-', date('Y-m-d-H'));

        // username if set has preseance over everything
        $userNamesList = isset($username) ? array(
            $username,
            $username.$numSufix[0],    //john2024
            $username.$numSufix[1],    //john12 i.e the month of reg
            $username.$numSufix[2],    //john16 i.e the day of reg
            $username.$numSufix[3]     //john08 i.e the hour of day of reg
        ) : array();

        // create an array of nice possible user names from the first name and last name
        array_push($userNamesList,
            $firstname,                           //john
            $lastname,                            //doe
            $firstname.$lastname,                 //johndoe
            $firstname.$numSufix[0],              //john2024
            $firstname.$numSufix[1],              //john12 i.e the month of reg
            $firstname.$numSufix[2],              //john16 i.e the day of reg
            $firstname.$numSufix[3],              //john08 i.e the hour of day of reg
            $firstname.$lastname.$numSufix[0],    //johndoe2024
            $firstname.$lastname.$numSufix[1],    //johndoe12 i.e the month of reg
            $firstname.$lastname.$numSufix[2],    //johndoe16 i.e the day of reg
            $firstname.$lastname.$numSufix[3]     //johndoe08 i.e the hour of day of reg
        );

        $isAvailable = false; //initialize available with false
        $index = 0;
        $maxIndex = count($userNamesList) - 1;

        // loop through all the userNameList and find the one that is available
        do {
            $availableUserName = $userNamesList[$index];
            $isAvailable = !$this->usernameExists($availableUserName);
            $limit =  $index >= $maxIndex;
            $index += 1;
            if ($limit) {
                break;
            }
        } while ( !$isAvailable );

        // No unique ? Use random
        if( !$isAvailable ){
            return $firstname . $lastname . random_int(1, 9999);
        }
        return $availableUserName;
    }

    /**
     * Check if username exists in database
     * @param string $username
     * @return boolean
     */
    private function usernameExists($username)
    {
        $results = $this->context->dbDriver->fetch($this->context->dbDriver->pQuery('SELECT id FROM ' . $this->context->dbDriver->commonSchema . '.user WHERE username=$1', array($username)));
        return !empty($results);
    }

}