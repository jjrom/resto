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
 *      {module_route}/users                                          |  Show all users profiles
 *      {module_route}/users/groups                                   |  Show all users groups
 *      {module_route}/users/{userid}                                 |  Show user profile
 *      {module_route}/users/{userid}/groups                          |  Show user groups
 *      {module_route}/users/{userid}/cart                            |  Show user cart
 *      {module_route}/users/{userid}/orders                          |  Show orders for user
 *      {module_route}/users/{userid}/orders/{orderid}                |  Show {orderid} order for user
 *      {module_route}/users/{userid}/rights                          |  Show rights for user
 *      {module_route}/users/{userid}/rights/{collection}             |  Show rights for user on {collection}
 *      {module_route}/users/{userid}/rights/{collection}/{feature}   |  Show rights for user on {feature} from {collection}
 *      {module_route}/users/{userid}/signatures                      |  Show signatures for user
 *      {module_route}/history                                        |  Show history
 *      {module_route}/groups/{groupid}/rights                        |  Show all rights for group {groupid}
 *      {module_route}/groups/{groupid}/users                         |  Show all users for group {groupid}
 * 
 * -- POST
 *    
 *      {module_route}/licenses                                       |  Create a license
 *      {module_route}/users/{userid}/rights                          |  Add/update rights for {userid} on all collections
 *      {module_route}/users/{userid}/rights/{collection}             |  Add/update rights for {userid} on {collection}
 *      {module_route}/users/{userid}/rights/{collection}/{featureid} |  Add/update rights for {userid} on {featureid}
 *      {module_route}/groups                                         |  Add new group
 * 
 * -- PUT
 * 
 *      {module_route}/users/{userid}/groups                          |  Update {userid} groups
 *      {module_route}/groups/{groupid}/rights                        |  Update {groupid] group rights
 * 
 * -- DELETE
 * 
 *      {module_route}/licenses/{licenseid}                           |  Delete {licenseid}
 *      {module_route}/users/{userid}/groups/{groups}                 |  Remove {groups} for user
 *      {module_route}/users/{userid}/rights                          |  Delete rights for {userid} on '*'
 *      {module_route}/users/{userid}/rights/{collection}             |  Delete rights for {userid} on {collection}
 *      {module_route}/users/{userid}/rights/{collection}/{featureid} |  Delete rights for {userid} on {featureid}
 *    
 */
class Admin extends RestoModule {

    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
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
            case 'history':
                return $this->GET_history($segments);
            case 'groups':
                return $this->GET_groups($segments);
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
            case 'users':
                return $this->POST_users($segments, $data);
            case 'groups':
                return $this->POST_groups($segments, $data);
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
            case 'groups':
                return $this->PUT_groups($segments, $data);
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
            /*
             * Get filters
             */
            $limit = filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_STRING);
            $offset = filter_input(INPUT_GET, 'offset', FILTER_SANITIZE_STRING);
            $keywords = filter_input(INPUT_GET, 'keywords', FILTER_SANITIZE_STRING);
            
            return RestoLogUtil::success('Profiles for all users', array(
                        'profiles' => $this->context->dbDriver->get(RestoDatabaseDriver::USERS_PROFILES, array(
                            'limit' => $limit,
                            'offset' => $offset,
                            'keywords' => $keywords
                        ))
            ));
        } else if ($segments[1] === 'groups') {
            return RestoLogUtil::success('Groups for all users', array(
                        'groups' => $this->context->dbDriver->get(RestoDatabaseDriver::GROUPS)
            ));
        }

        /*
         * Get user
         */
        $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('userid' => $segments[1])), $this->context);

        /*
         * users/{userid}
         */
        if (!isset($segments[2])) {
            return RestoLogUtil::success('Profile for ' . $user->profile['email'], array(
                        'profile' => $user->profile
            ));
        }

        /*
         * users/{userid}/groups
         */
        if ($segments[2] === 'groups') {
            if (isset($segments[3])) {
                return RestoLogUtil::httpError(404);
            }
            return RestoLogUtil::success('Groups for ' . $user->profile['email'], array(
                        'email' => $user->profile['email'],
                        'groups' => $user->profile['groups']
            ));
        }

        /*
         * users/{userid}/rights
         */
        if ($segments[2] === 'rights') {
            return $this->getRights($user, isset($segments[3]) ? $segments[3] : null, isset($segments[4]) ? $segments[4] : null);
        }

        /*
         * users/{userid}/cart
         */
        if ($segments[2] === 'cart' && !isset($segments[3])) {
            return $this->user->getCart();
        }

        /*
         * users/{userid}/orders
         */
        if ($segments[2] === 'orders') {
            if (isset($segments[3])) {
                return new RestoOrder($user, $this->context, $segments[3]);
            } else {
                return RestoLogUtil::success('Orders for user ' . $user->profile['email'], array(
                            'email' => $user->profile['email'],
                            'userid' => $user->profile['userid'],
                            'orders' => $user->getOrders()
                ));
            }
        }

        /*
         * users/{userid}/signatures
         */
        if ($segments[2] === 'signatures' && !isset($segments[3])) {


            return RestoLogUtil::success('Signatures for ' . $user->profile['email'], array(
                        'email' => $user->profile['email'],
                        'userid' => $user->profile['userid'],
                        'groups' => $user->profile['groups'],
                        'signatures' => $this->getSignatures($user)
            ));
        }

        return RestoLogUtil::httpError(404);
    }

    /**
     * Get signatures informations
     * 
     * @param RestoUser $_user
     * @return array
     */
    private function getSignatures($_user) {

        $_collections = new RestoCollections($this->context, $_user, array('autoload' => true));
        $_collectionsList = $_collections->getCollections();
        $signatures = array();
        foreach ($_collectionsList as $collection) {
            $signatures[$collection->name] = array(
                'isApplicableToUser' => $collection->license->isApplicableToUser($_user),
                'hasToBeSignedByUser' => $collection->license->hasToBeSignedByUser($_user)
            );
        }

        return $signatures;
    }

    /**
     * Process HTTP GET request on history
     * 
     *      history                     
     * 
     * @param array $segments
     */
    private function GET_history($segments) {

        /*
         * history
         */
        if (!isset($segments[2])) {
            return RestoLogUtil::success('History', array('history' => $this->context->dbDriver->get(RestoDatabaseDriver::HISTORY, $this->context->query)));
        }

        return RestoLogUtil::httpError(404);
    }

    /**
     * Process HTTP GET request on groups
     * 
     *      groups                     
     * 
     * @param array $segments
     */
    private function GET_groups($segments) {
        /*
         * Get on /groups/{groupid}/rights
         */
        if (isset($segments[2]) && $segments[2] === 'rights' && !isset($segments[3])) {
            return RestoLogUtil::success('Group rights', array(
                        'groupid' => $segments[1],
                        'rights' => $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS, array('groups' => $segments[1]))
            ));
        } else if (isset($segments[2]) && $segments[2] === 'users' && !isset($segments[3])) {
            /*
             * Get filters
             */
            $limit = filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_STRING);
            $offset = filter_input(INPUT_GET, 'offset', FILTER_SANITIZE_STRING);
            $keywords = filter_input(INPUT_GET, 'keywords', FILTER_SANITIZE_STRING);
            
            /*
             * Get on /groups/{groupid}/users
             */
            return RestoLogUtil::success('Group users', array(
                        'groupid' => $segments[1],
                        'users' => $this->context->dbDriver->get(RestoDatabaseDriver::USERS_PROFILES, array(
                            'groupid' => $segments[1],
                            'limit' => $limit,
                            'offset' => $offset,
                            'keywords' => $keywords
                                ))
            ));
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

        if (!isset($data['licenseId'])) {
            RestoLogUtil::httpError(400, 'license Identifier is not set');
        }

        $license = new RestoLicense($this->context, $data['licenseId'], false);
        $license->setDescription($data);

        return RestoLogUtil::success('license ' . $data['licenseId'] . ' created');
    }

    /**
     *
     * Process HTTP POST request on users
     *
     *      users/{userid}/rights         
     *      users/{userid}/rights/{collection}      
     *      users/{userid}/rights/{collection}/{featureid}   
     *
     * @param array $segments
     * @param array $data
     */
    private function POST_users($segments, $data) {

        /*
         * Check route pattern
         */
        if (!isset($segments[2]) || $segments[2] !== 'rights' || !isset($data['rights'])) {
            RestoLogUtil::httpError(404);
        }

        /*
         * Get user
         */
        $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('userid' => $segments[1])), $this->context);

        /*
         * Store/update rights
         */
        $user->setRights($data['rights'], isset($segments[3]) ? $segments[3] : null, isset($segments[4]) ? $segments[4] : null);

        return $this->getRights($user, isset($segments[3]) ? $segments[3] : null, isset($segments[4]) ? $segments[4] : null);
    }

    /**
     *
     * Process HTTP POST request on groups
     *
     *      groups
     * 
     * @param array $segments
     * @param array $data
     */
    private function POST_groups($segments, $data) {

        /*
         * Check endpoint and data content
         */
        if (isset($segments[1]) || !isset($data['groupid'])) {
            return RestoLogUtil::httpError(404);
        } else {
            /*
             * Store group
             */
            $groups = $this->context->dbDriver->store(RestoDatabaseDriver::GROUP, array('groupid' => $data['groupid']));

            return RestoLogUtil::success('Group created', array(
                        'groupid' => $data['groupid'],
                        'groups' => $groups
            ));
        }
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

        return $user->addGroups($data['groups']);
    }

    /**
     *
     * Process HTTP PUT request on groups
     *
     *    groups/{groupid}/rights
     *
     * @param array $segments
     * @param array $data
     */
    private function PUT_groups($segments, $data) {

        /*
         * Mandatory {groupid}
         */
        if (empty($segments[1]) || empty($segments[2]) || $segments[2] !== 'rights' || !isset($data['rights']) || !isset($data['targetType']) || !isset($data['target'])) {
            return RestoLogUtil::httpError(404);
        } else {
            return $this->update_group_rights($segments[1], $data);
        }
    }

    /**
     * Update rights for group
     * 
     * @param string $groupid
     * @param array $data
     * @return type
     */
    private function update_group_rights($groupid, $data) {

        /*
         * Update rights
         */
        try {
            $this->context->dbDriver->update(RestoDatabaseDriver::RIGHTS, array(
                'rights' => array(
                    'create' => isset($data['rights']['create']) ? $data['rights']['create'] : null,
                    'download' => isset($data['rights']['download']) ? $data['rights']['download'] : null,
                    'visualize' => isset($data['rights']['visualize']) ? $data['rights']['visualize'] : null
                ),
                'ownerType' => 'group',
                'owner' => $groupid,
                'targetType' => $data['targetType'],
                'target' => $data['target']
            ));
        } catch (Exception $ex) {
            /*
             * TODO : set error code
             */
            RestoLogUtil::httpError(404);
        }

        return RestoLogUtil::success('Rights updated', array(
                    'groupid' => $groupid,
                    'rights' => $this->context->dbDriver->get(RestoDatabaseDriver::RIGHTS, array('groups' => $groupid))
        ));
    }

    /**
     * 
     * Process HTTP DELETE request on licenses
     *
     *    licenses/{licenseid}
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

        $this->context->dbDriver->remove(RestoDatabaseDriver::LICENSE, array('licenseId' => $segments[1]));
        return RestoLogUtil::success('License removed', array(
                    'licenseId' => $segments[1]
        ));
    }

    /**
     * 
     * Process HTTP DELETE request on users
     *
     *     users/{userid}/groups/{groups}                     
     *     users/{userid}/rights         
     *     users/{userid}/rights/{collection}      
     *     users/{userid}/rights/{collection}/{featureid}            
     *
     * @param array $segments
     */
    private function DELETE_users($segments) {

        /*
         * Check route pattern
         */
        if (empty($segments[2]) || !ctype_digit($segments[1])) {
            RestoLogUtil::httpError(404);
        }
        /*
         * Get user
         */
        $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('userid' => $segments[1])), $this->context);


        if ($segments[2] === 'groups' && !empty($segments[3])) {
            /*
             * users/{userid}/groups/{groups}
             */
            return $user->removeGroups($segments[3]);
        } else if ($segments[2] === 'rights') {
            /*
             * users/{userid}/rights
             */
            $user->removeRights(isset($segments[3]) ? $segments[3] : null, isset($segments[4]) ? $segments[4] : null);
            return RestoLogUtil::success('Rights updated', array(
                    'email' => $user->profile['email'],
                    'userid' => $user->profile['userid'],
                    'groups' => $user->profile['groups']
        ));
        }

        RestoLogUtil::httpError(404);
    }

    /**
     * Return formated rights
     * 
     * @param RestoUser $user
     * @param string $collectionName
     * @param string $featureIdentifier
     */
    private function getRights($user, $collectionName, $featureIdentifier) {
        return RestoLogUtil::success('Rights for ' . $user->profile['email'], array(
                    'email' => $user->profile['email'],
                    'userid' => $user->profile['userid'],
                    'groups' => $user->profile['groups'],
                    'rights' => $user->getRights($collectionName, $featureIdentifier)
        ));
    }

}
