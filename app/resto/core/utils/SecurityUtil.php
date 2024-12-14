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
 *  Security utility - check authentication header, deals with (r)JWT etc.
 */

class SecurityUtil
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Authenticate and set user accordingly
     *
     * Various authentication method
     *
     *   - HTTP user:password (i.e. http authorization mechanism)
     *   - Single Sign On request with oAuth2
     *
     *
     *  @OA\SecurityScheme(
     *      type="http",
     *      scheme="bearer",
     *      bearerFormat="JWT",
     *      securityScheme="bearerAuth",
     *      description="Access token in HTTP header as JWT or rJWT (_resto JWT_) - this is the default"
     *  )
     *
     *  @OA\SecurityScheme(
     *      type="http",
     *      scheme="basic",
     *      securityScheme="basicAuth",
     *      description="Basic authentication in HTTP header - should be used first to get a valid rJWT token"
     *  )
     *
     *  @OA\SecurityScheme(
     *      type="apiKey",
     *      in="query",
     *      name="_bearer",
     *      securityScheme="queryAuth",
     *      description="Access token in query as preseance over token in HTTP header"
     *  )
     *
     * @param RestoContext $context
     * @return RestoUser
     *
     */
    public function authenticate($context)
    {
        $authRequested = false;

        /*
         * Authentication through token in url
         */
        if (isset($context->query['_bearer'])) {
            $authRequested = true;
            $user = $this->authenticateBearer($context, $context->query['_bearer']);
        //unset($context->query['_bearer']);
        }
        /*
         * ...or from headers
         */ else {
            list($authRequested, $user) = $this->headersAuthenticate($context);
        }

        /*
         * If we land here - set an unregistered user
         */
        if (!isset($user)) {
            $user = new RestoUser(null, $context);
        }

        /*
         * Authentication headers were present but authentication leads to unauthentified user => security error
         */
        if ($authRequested && !isset($user->profile['id'])) {
            RestoLogUtil::httpError(401);
        }

        return $user;
    }

    /**
     * Get authentication info from http headers
     *
     * @param RestoContext $context
     * @return array
     */
    private function headersAuthenticate($context)
    {
        $httpAuth = filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION', FILTER_UNSAFE_RAW);
        $rhttpAuth = filter_input(INPUT_SERVER, 'REDIRECT_HTTP_AUTHORIZATION', FILTER_UNSAFE_RAW);
        $authorization = !empty($httpAuth) ? $httpAuth : (!empty($rhttpAuth) ? $rhttpAuth : null);
        if (isset($authorization)) {
            list($method, $token) = explode(' ', $authorization, 2);
            switch ($method) {
                case 'Basic':
                    return array(true, $this->authenticateBasic($context, $token));
                    break;
                case 'Bearer':
                    return array(true, $this->authenticateBearer($context, $token));
                    break;
                default:
                    break;
            }
        }
        return array(false, null);
    }

    /**
     * Authenticate user from Basic authentication
     * (i.e. HTTP user:password)
     *
     * @param RestoContext $context
     * @param string $token
     * @return RestoUser
     */
    private function authenticateBasic($context, $token)
    {
        $user = null;
        list($username, $password) = explode(':', base64_decode($token), 2);
        if (!empty($username) && !empty($password) && (bool)preg_match('//u', $username) && (bool)preg_match('//u', $password) && strpos($username, '\'') === false) {
            
            // email has a mandatory @
            if (str_contains($username, '@')) {
                $user = new RestoUser(array(
                    'email' => strtolower($username),
                    'password' => $password
                ), $context);
            }
            else {
                $user = new RestoUser(array(
                    'username' => strtolower($username),
                    'password' => $password
                ), $context);
            }
            
        }
        return $user;
    }

    /**
     * Authenticate user from Bearer authentication
     * (i.e. Single Sign On request with oAuth2)
     *
     * Assume either a JSON Web Token encoded by resto or a token generated by an SSO issuer (e.g. google)
     *
     * @param RestoContext $context
     * @param string $token
     * @return RestoUser
     */
    private function authenticateBearer($context, $token)
    {
        $user = null;

        try {
            /*
             * If issuer_id is specified in the request then assumes a third party token.
             * In this case, transform this third party token into a resto token
             */
            $authClassName = 'Auth';
            if (isset($context->query['issuerId']) && isset($context->addons[$authClassName])) {
                $auth = new $authClassName($context, null);
                $token = $auth->getProfileToken($context->query['issuerId'], $token);
            }

            /*
             * Get user from JWT payload if valid
             */
            $username = $this->getUsernameFromBearer($context, $token);
            if (isset($username)) {
                $user = new RestoUser(array('username' => $username), $context);
                $user->token = $token;
            }
        } catch (Exception $ex) {
            return $user;
        }
        
        return $user;
    }

    /**
     * Check if token is not revoked
     * [PERFO WISE] only do this for long time token i.e. > 7 days
     *
     * @param RestoContext $context
     * @param array $payloadObject JWT payload
     * @return string
     */
    private function getUsernameFromBearer($context, $token)
    {
        $payloadObject = $context->decodeJWT($token);

        // Unvalid token => no auth
        if (!isset($payloadObject) || !isset($payloadObject['sub'])) {
            return null;
        }

        // Missing times in token => no auth
        if (!isset($payloadObject['iat']) || !isset($payloadObject['exp'])) {
            return null;
        }

        // Valid token but too old => no auth
        if ($payloadObject['exp'] - $payloadObject['iat'] <= 0) {
            return null;
        }

        // Token is valid but older than 1000 days - check revokation
        if ($payloadObject['exp'] - $payloadObject['iat'] > 86400000) {
            if ((new GeneralFunctions($context->dbDriver))->isTokenRevoked($token)) {
                return null;
            }
        }

        return $payloadObject['sub'];
    }
}
