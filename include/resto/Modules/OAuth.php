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
 * OAuth module
 * 
 * Callback function for oAuth2 Authentication
 */
class OAuth extends RestoModule {
    
    /*
     * Identity providers
     */
    private $providers = array();
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param array $options : array of module parameters
     */
    public function __construct($context, $options = array()) {
        parent::__construct($context, $options);
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
         * Only GET method with html outputformat is accepted
         */
        if ($this->context->method !== 'GET' || $this->context->outputFormat !== 'html' || count($params) !== 0) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }
        
        /*
         * No code - no authorization
         */
        $code = isset($_GET['code']) ? $_GET['code'] : null;
        $issuerId = isset($_GET['issuer_id']) ? $_GET['issuer_id'] : null;
        if (!$code || !$issuerId) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Bad Request', 400);
        }

        /*
         * The oauth_issuer should specify the identifier of the Identity Provider
         */
        $sso = null;
        foreach (array_keys($this->providers) as $key) {
            if ($issuerId === $this->providers[$key]['issuer_id']) {
                $sso = $this->providers[$key];
                break;
            }
        }
        if (!isset($sso)) {
            throw new Exception(($this->debug ? __METHOD__ . ' - ' : '') . 'Invalid issuer', 400);
        }

        /*
         * Get current script url
         */
        $redirect_uri = $this->context->baseUrl . 'api/oauth/callback?issuer_id=' . $issuerId;
       
        /*
         * First retrieve the oauth token using input code
         */
        try {
            /*
             *
            if ($sso['useBearer']) {
                $params = array(
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirect_uri
                );
                $context = stream_context_create(array('http' => array(
                        'method' => 'POST',
                        'header' => array(
                            "Authorization: Basic " . base64_encode($sso['clientId'] . ':' . $sso['clientSecret']),
                            "Content-Type: application/x-www-form-urlencoded",
                            "Host: " . $sso['host']
                ))));
            }
            else {
                $params = array(
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirect_uri,
                    'client_id' => $sso['clientId'],
                    'client_secret' => $sso['clientSecret']
                );
                $context = stream_context_create(array('http' => array(
                        'method' => 'POST',
                        'header' => array(
                            "Content-Length: 10"
                ))));
            }
            
            $token = json_decode(file_get_contents($sso['accessTokenUrl'] . http_build_query($params), false, $context));
            if (isset($token) && $token->access_token) {
                $userIdentifier = $this->authenticate($token->access_token, $issuerId);
                if ($userIdentifier) {
                    $trimed = trim(strtolower($userIdentifier));
                    if (!$this->context->dbDriver->userExists($trimed)) {
                        $this->context->dbDriver->storeUserProfile(array(
                            'email' => $trimed,
                            'activated' => true,
                            'lastsessionid' => session_id()
                        ));
                    }
                    else {
                        $this->context->dbDriver->updateUserProfile(array(
                            'email' => $trimed,
                            'lastsessionid' => session_id()
                        ));
                    }
                    $_SESSION['profile'] = $this->context->dbDriver->getUserProfile($trimed);
                    $_SESSION['access_token'] = $token->access_token; 
                    $_SESSION['expires_in'] = $token->expires_in;
                    $_SESSION['expires_at'] = time() + $_SESSION['expires_in'];
                    return RestoUtil::get_include_contents(realpath(dirname(__FILE__)) . '/../../../themes/' . $this->context->config['theme'] . '/Modules/OAuth/templates/success.php', $this);
                }
            }
        } catch (Exception $e) {}
             */
            $ch = curl_init($sso['accessTokenUrl']);
            curl_setopt($ch, CURLOPT_POST, true);
            //curl_setopt($ch, CURLOPT_CAPATH, CACERT_PATH);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if ($sso['useBearer']) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                    'grant_type' => "authorization_code",
                    'code' => $code,
                    'redirect_uri' => $redirect_uri
                )));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: Basic " . base64_encode($sso['clientId'] . ':' . $sso['clientSecret']),
                    "Content-Type: application/x-www-form-urlencoded",
                    "Host: " . $sso['host']));
            }
            else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                    'grant_type' => "authorization_code",
                    'code' => $code,
                    'redirect_uri' => $redirect_uri,
                    'client_id' => $sso['clientId'],
                    'client_secret' => $sso['clientSecret']
                )));
            }
            $jsonData = json_decode(curl_exec($ch), true);
            if (isset($jsonData) && $jsonData['access_token']) {
                $userIdentifier = $this->authenticate($jsonData['access_token'], $issuerId);
                if ($userIdentifier) {
                    $trimed = trim(strtolower($userIdentifier));
                    if (!$this->context->dbDriver->userExists($trimed)) {
                        $this->context->dbDriver->storeUserProfile(array(
                            'email' => $trimed,
                            'activated' => true,
                            'lastsessionid' => session_id()
                        ));
                    }
                    else {
                        $this->context->dbDriver->updateUserProfile(array(
                            'email' => $trimed,
                            'lastsessionid' => session_id()
                        ));
                    }
                    $_SESSION['profile'] = $this->context->dbDriver->getUserProfile($trimed);
                    $_SESSION['access_token'] = $jsonData['access_token'];
                    $_SESSION['expires_in']   = $jsonData['expires_in'];
                    $_SESSION['expires_at']   = time() + $_SESSION['expires_in'];
            
                    return RestoUtil::get_include_contents(realpath(dirname(__FILE__)) . '/../../../themes/' . $this->context->config['theme'] . '/Modules/OAuth/templates/success.php', $this);
                }
            }
            curl_close($ch);
        } catch (Exception $e) {}
            
        return RestoUtil::get_include_contents(realpath(dirname(__FILE__)) . '/../../../themes/' . $this->context->config['theme'] . '/Modules/OAuth/templates/error.php', $this);
    
    }
    
    /**
     * Authenticate user from access token
     * 
     * @param string $accessToken
     * @param string $issuerId
     * 
     * @return type
     */
    public function authenticate($accessToken, $issuerId) {
        
        if (isset($accessToken) && $accessToken) {
            
            /*
             * If access_token is set in the session avoid oauth authentication
             */
            if (isset($_SESSION['profile']) && isset($_SESSION['access_token']) && $_SESSION['access_token'] === $accessToken) {
                return $_SESSION['profile']['email'];
            }
            
            /*
             * Initialize empty SSO configuration object
             */
            $sso = array(
                'issuer' => null,
                'access_token' => $accessToken
            );

            /*
             * The oauth_issuer should specify the identifier of the Identity Provider
             * Note: if no issuerIf is specified we take the first issuer
             */
            foreach (array_keys($this->providers) as $key) {
                if (!isset($issuerId) || $issuerId === $this->providers[$key]['issuer_id']) {
                    $sso['issuer'] = $this->providers[$key];
                    break;
                }
            }
            
            /*
             * Authenticate user and get profile
             */
            try {
                $userInfo = json_decode(file_get_contents($sso['issuer']['userInfoUrl'], false, stream_context_create(array(
                    'http' => array(
                        'method' => 'GET',
                        'header' => "Authorization: Bearer " . $sso['access_token'] . "\r\n" . "x-li-format: json\r\n"
                    )
                ))), true);
                if (is_array($userInfo) && $userInfo[$sso['issuer']['uidKey']]) {
                    return $userInfo[$sso['issuer']['uidKey']];    
                }
            } catch (Exception $e) {}
        }
        
        return null;
        
    }
    
}
