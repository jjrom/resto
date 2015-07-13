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
 * RESTo REST router
 * 
 * See list of routes per HTTP verb in Routes/*.php
 * 
 */
abstract class RestoRoute {
    
    /*
     * RestoContext
     */
    protected $context;
    
    /*
     * RestoUser
     */
    protected $user;
    
    /**
     * Constructor
     */
    public function __construct($context, $user) {
        $this->context = $context;
        $this->user = $user;
    }
   
    /**
     * Route to resource
     * 
     * @param array $segments : path as route segments
     */
    abstract public function route($segments);
    
    /**
     * Launch module run() function if exist otherwise returns 404 Not Found
     * 
     * @param array $segments - path (i.e. a/b/c/d) exploded as an array (i.e. array('a', 'b', 'c', 'd')
     * @param array $data - data (POST or PUT)
     */
    protected function processModuleRoute($segments, $data = array()) {
        
        $module = null;
        
        foreach (array_keys($this->context->modules) as $moduleName) {
            
            if (isset($this->context->modules[$moduleName]['route'])) {
                
                $moduleSegments = explode('/', $this->context->modules[$moduleName]['route']);
                $routeIsTheSame = true;
                $count = 0;
                for ($i = 0, $l = count($moduleSegments); $i < $l; $i++) {
                    $count++;
                    if (!isset($segments[$i]) || $moduleSegments[$i] !== $segments[$i]) {
                        $routeIsTheSame = false;
                        break;
                    } 
                }
                if ($routeIsTheSame) {
                    $module = RestoUtil::instantiate($moduleName, array($this->context, $this->user));
                    for ($i = $count; $i--;) {
                        array_shift($segments);
                    }
                    return $module->run($segments, $data);
                }
            }
        }
        if (!isset($module)) {
            RestoLogUtil::httpError(404);
        }
    }

    /**
     * Store query to database
     * 
     * @param string $serviceName
     * @param RestoUser $user
     * @param string $collectionName
     * @param string $featureIdentifier
     * 
     */
    protected function storeQuery($serviceName, $user, $collectionName, $featureIdentifier) {
        if ($this->context->storeQuery === true) {
            $user->storeQuery($this->context->method, $serviceName, isset($collectionName) ? $collectionName : null, isset($featureIdentifier) ? $featureIdentifier : null, $this->context->query, $this->context->getUrl());
        }
    }
   
    /**
     * Send user activation code by email
     * 
     * @param array $params
     */
    protected function sendMail($params) {
        $headers = 'From: ' . $params['senderName'] . ' <' . $params['senderEmail'] . '>' . "\r\n";
        $headers .= 'Reply-To: doNotReply <' . $params['senderEmail'] . '>' . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
        $headers .= 'X-Priority: 3' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        if (mail($params['to'], $params['subject'], $params['message'] , $headers, '-f' . $params['senderEmail'])) {
            return true;
        }
        return false;
    }

    /**
     * Return userid from email or id string
     * 
     * @param string $emailOrId
     */
    protected function userid($emailOrId) {
        
        if (!ctype_digit($emailOrId)) {
            if ($this->user->profile['userid'] !== -1 && $this->user->profile['email'] === strtolower($emailOrId)) {
                return $this->user->profile['userid'];
            }
        }
        
        return $emailOrId;
    }
    
    /**
     * Return user object if authorized
     * 
     * @param string $emailOrId
     * @param boolean $byPassAuthorization
     */
    protected function getAuthorizedUser($emailOrId, $byPassAuthorization = false) {
        
        $user = $this->user;
        $userid = $this->userid($emailOrId);
        if ($user->profile['userid'] !== $userid) {
            
            if (!$user->isAdmin()) {
                if (!$byPassAuthorization) {
                    RestoLogUtil::httpError(403);
                }
            }
            
            if (!ctype_digit($emailOrId)) {
                $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('email' => strtolower($emailOrId))), $this->context);
            }
            else {
                $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('userid' => $userid)), $this->context);
            }
        }
        
        return $user;
        
    }
    
    /**
     * Return the requested email/id
     * 
     * Order of preseance :
     *  - "_emailorid" parameter from query
     *  - authenticated user userid
     * 
     * @return string
     */
    protected function getRequestedEmailOrId() {
        
        if (isset($this->context->query['_emailorid'])) {
            return $this->context->query['_emailorid'];
        }
        
        return $this->user->profile['userid'];
        
    }

}
