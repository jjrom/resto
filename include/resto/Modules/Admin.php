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
 * 
 * Administration module
 * 
 * Only admin user can access the following endpoints :
 * Note : {module_route} is the "route" value defines within module configuration (see config.php)
 *        By default {module_route} value is "admin"
 * 
 * -- GET
 * 
 *      {module_route}/users                                         |  Show all users profiles (only admin)
 *      {module_route}/users/{userid}                                 |  Show user profile
 *      {module_route}/users/{userid}/groups                          |  Show user groups
 *      {module_route}/users/{userid}/cart                            |  Show user cart
 *      {module_route}/users/{userid}/orders                          |  Show orders for user
 *      {module_route}/users/{userid}/orders/{orderid}                |  Show {orderid} order for user
 *      {module_route}/users/{userid}/rights                          |  Show rights for user
 *      {module_route}/users/{userid}/rights/{collection}             |  Show rights for user on {collection}
 *      {module_route}/users/{userid}/rights/{collection}/{feature}   |  Show rights for user on {feature} from {collection}
 *      {module_route}/users/{userid}/signatures                      |  Show signatures for user
 *
 * -- POST
 *    
 *      {module_route}/licenses                                      |  Create a license
 * 
 * -- PUT
 * 
 *      {module_route}/users/{userid}/groups                          |  Update {userid} groups
 *     
 * -- DELETE
 * 
 *      {module_route}/licenses/{licenseid}                          |  Delete {licenseid}
 *      {module_route}/users/{userid}/groups/{groups}                |  Remove {groups} for user
 *    
 */
class Admin extends RestoModule {
    
    /*
     * Reference to RestoAPI functions
     */
    private $API;
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
        $this->API = new RestoAPI($context);
    }

    /**
     * Run module - this function should be called by Resto.php
     * 
     * @param array $segments : route segments
     * @param array $data : POST or PUT parameters
     * 
     * @return string : result from run process in the $context->outputFormat
     */
    public function run($segments, $data) {
        
        /*
         * Only administrators can access this module
         */
        if (!$this->user->isAdmin()) {
            RestoLogUtil::httpError(403);
        }
        
        /*
         * Switch on HTTP methods
         */
        switch ($this->context->method) {
            case 'GET':
                return $this->processGET($segments);
            case 'POST':
                return $this->processPOST($segments, $data);
            case 'PUT':
                return $this->processPUT($segments, $data);
            case 'DELETE':
                return $this->processDELETE($segments);
            default:
                RestoLogUtil::httpError(404);
        }
        
    }
    
    /**
     * Process HTTP GET requests
     * 
     * @param array $segments
     */
    private function processGET($segments) {
        switch ($segments[0]) {
            case 'users':
                return $this->GET_users($segments);
            default:
                RestoLogUtil::httpError(404);
        }
    }
    
    /**
     * Process HTTP POST requests
     * 
     * @param array $segments
     * @param array $data
     */
    private function processPOST($segments, $data) {
        
        switch ($segments[0]) {
            case 'licenses':
                return $this->POST_licenses($segments, $data);
            default:
                RestoLogUtil::httpError(404);
        }
    }
    
    /**
     * Process HTTP PUT requests
     * 
     * @param array $segments
     * @param array $data
     */
    private function processPUT($segments, $data) {
        switch ($segments[0]) {
            case 'users':
                return $this->PUT_users($segments, $data);
            default:
                RestoLogUtil::httpError(404);
        }
    }
    
    /**
     * Process HTTP PUT requests
     * 
     * @param array $segments
     */
    private function processDELETE($segments) {
        switch ($segments[0]) {
            case 'licenses':
                return $this->DELETE_licenses($segments);
            case 'users':
                return $this->DELETE_users($segments);
            default:
                RestoLogUtil::httpError(404);
        }
        
    }
    
    /**
     * Process HTTP GET request on user
     * 
     *      users
     *      users/{userid}                                 
     *      users/{userid}/groups                          
     *      users/{userid}/cart                            
     *      users/{userid}/orders                          
     *      users/{userid}/orders/{orderid}                   
     *      users/{userid}/rights                          
     *      users/{userid}/rights/{collection}             
     *      users/{userid}/rights/{collection}/{feature}   
     *      users/{userid}/signatures                      
     * 
     * @param array $segments
     */
    private function GET_users($segments) {
        
        /*
         * No {userid} => return all profiles
         */
        if (!isset($segments[1])) {
            return $this->API->getUsersProfiles();
        }
        
        /*
         * Get user
         */
        $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('userid' => $segments[1])), $this->context);
        
        /*
         * users/{userid}
         */
        if (!isset($segments[2])) {
            return $this->API->getUserProfile($user);
        }
    
        /*
         * users/{userid}/groups
         */
        if ($segments[2] === 'groups') {
            if (isset($segments[3])) {
                return RestoLogUtil::httpError(404);
            }
            return $this->API->getUserGroups($user);
        }

        /*
         * users/{userid}/rights
         */
        if ($segments[2] === 'rights') {
            return $this->API->getUserRights($user, isset($segments[3]) ? $segments[3] : null, isset($segments[4]) ? $segments[4] : null);
        }
        
        /*
         * users/{userid}/cart
         */
        if ($segments[2] === 'cart') {
            return $this->API->getUserCart($user, isset($segments[3]) ? $segments[3] : null);
        }
        
        /*
         * users/{userid}/orders
         */
        if ($segments[2] === 'orders') {
            return $this->API->getUserOrders($user, isset($segments[3]) ? $segments[3] : null);
        }

        /*
         * users/{userid}/signatures
         */
        if ($segments[2] === 'signatures') {
            return $this->API->getUserSignatures($user, isset($segments[3]) ? $segments[3] : null);
        }
        
        return RestoLogUtil::httpError(404);
    }
    
    /**
     *
     * Process HTTP POST request on licenses
     *
     *    licenses           
     *
     * @param array $segments
     * @param array $data
     */
    private function POST_licenses($segments, $data) {

        /*
         * No modifier allowed
         */
        if (isset($segments[1])) {
            RestoLogUtil::httpError(404);
        }

        return $this->API->createLicense($data);
        
    }
    
    /**
     *
     * Process HTTP PUT request on users
     *
     *    users/{userid}/groups 
     *
     * @param array $segments
     * @param array $data
     */
    private function PUT_users($segments, $data) {

        /*
         * Mandatory {userid}
         */
        if (empty($segments[2]) || !ctype_digit($segments[1]) || $segments[2] !== 'groups') {
            return RestoLogUtil::httpError(404);
        }
        
        /*
         * Get user
         */
        $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('userid' => $segments[1])), $this->context);
        if (!isset($data['groups'])) {
            RestoLogUtil::httpError(400, 'Groups is not set');
        }
        
        return $this->API->addUserGroups($user, $data['groups']);
        
    }
    
    /**
     * 
     * Process HTTP DELETE request on licenses
     *
     *    licenses/{licenseid}                          |  Delete {licenseid}
     *
     * @param array $segments
     */
    private function DELETE_licenses($segments) {
        
        /*
         * {licenseid} is mandatory
         */
        if (!isset($segments[1]) || isset($segments[2])) {
            RestoLogUtil::httpError(404);
        }
        
        return $this->API->removeLicense($segments[1]);
        
    }
    
    /**
     * 
     * Process HTTP DELETE request on users
     *
     *     users/{userid}/groups/{groups}              
     *
     * @param array $segments
     */
    private function DELETE_users($segments) {
        
        /*
         * Mandatory {userid} and {groups}
         */
        if (empty($segments[3]) || !ctype_digit($segments[1]) || $segments[2] !== 'groups') {
            return RestoLogUtil::httpError(404);
        }
        
        /*
         * Get user
         */
        $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('userid' => $segments[1])), $this->context);
        
        return $this->API->removeUserGroups($user, $segments[3]);
        
    }
    
}