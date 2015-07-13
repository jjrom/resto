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
 * RESTo REST router for PUT requests
 * 
 *    collections/{collection}                      |  Update {collection}
 *    collections/{collection}/{feature}            |  Update {feature}
 *    
 *    user                                          |  Modify user profile
 *    user/cart/{itemid}                            |  Modify item in user cart
 *    user/groups                                   |  Modify user groups (only admin)
 *   
 */
class RestoRoutePUT extends RestoRoute {
    
    /**
     * Constructor
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }
   
    /**
     * Process HTTP PUT request
     *
     * @param array $segments
     */
    public function route($segments) {
        
        /*
         * Input data is mandatory for PUT request
         */
        $data = RestoUtil::readInputData($this->context->uploadDirectory);
        if (!is_array($data) || count($data) === 0) {
            RestoLogUtil::httpError(400);
        }

        switch($segments[0]) {
            case 'collections':
                return $this->PUT_collections($segments, $data);
            case 'user':
                return $this->PUT_user($segments, $data);
            default:
                return $this->processModuleRoute($segments, $data);
        }
        
    }
    
    /**
     * 
     * Process HTTP PUT request on collections
     * 
     *    collections/{collection}                      |  Update {collection}
     *    collections/{collection}/{feature}            |  Update {feature}
     * 
     * @param array $segments
     * @param array $data
     */
    private function PUT_collections($segments, $data) {
        
        /*
         * {collection} is mandatory and no modifier is allowed
         */
        if (!isset($segments[1]) || isset($segments[3])) {
            RestoLogUtil::httpError(404);
        }
        
        $collection = new RestoCollection($segments[1], $this->context, $this->user, array('autoload' => true));
        $featureIdentifier = isset($segments[2]) ? $segments[2] : null;
        if (isset($featureIdentifier)) {
            $feature = new RestoFeature($this->context, $this->user, array(
                'featureIdentifier' => $featureIdentifier,
                'collection' => $collection
            ));
            if (!$feature->isValid()) {
                RestoLogUtil::httpError(404);
            }
        }
        
        /*
         * Check credentials
         */
        if (!$this->user->hasPUTRights($collection->name, $featureIdentifier)) {
            RestoLogUtil::httpError(403);
        }

        /*
         * collections/{collection}
         */
        if (!isset($feature)) {
            $collection->loadFromJSON($data, true);
            $this->storeQuery('update', $this->user, $collection->name, null);
            return RestoLogUtil::success('Collection ' . $collection->name . ' updated');
        }
        /*
         * collections/{collection}/{feature}
         */
        else {
            //$this->storeQuery('update', $collection->name, $featureIdentifier);
            RestoLogUtil::httpError(501);
        }
        
    }
    
    
    /**
     * 
     * Process HTTP PUT request on users
     *
     *    user
     *    user/groups                                   |  Modify user groups (only admin)
     *    user/cart/{itemid}                            |  Modify item in user cart
     * 
     * @param array $segments
     * @param array $data
     */
    private function PUT_user($segments, $data) {
        
        $emailOrId = $this->getRequestedEmailOrId();
        
        /*
         * user
         */
        if (!isset($segments[1])) {
            return $this->PUT_userProfile($emailOrId, $data);
        }
        
        /*
         * user/groups
         */
        if ($segments[1] === 'groups') {
            return $this->PUT_userGroups($emailOrId, $data);
        }

        /*
         * user/cart/{itemid}
         */
        else if ($segments[1] === 'cart' && isset($segments[2])) {
            return $this->PUT_userCart($emailOrId, $segments[2], $data);
        }
        else {
            RestoLogUtil::httpError(404);
        }
        
    }
    
    /**
     *
     * Process HTTP PUT request on user profile
     *
     *    user                                 |  Update user profile
     *
     * @param string $emailOrId
     * @param array $data
     */
    private function PUT_userProfile($emailOrId, $data) {
        
        /*
         * Get user to be modified
         */
        $user = $this->getAuthorizedUser($emailOrId);
        
        /*
         * For normal user (i.e. non admin), some properties cannot be modified after validation
         */
        if (!$this->user->isAdmin()) {
            
            /*
             * Already validated => avoid updating administrative properties
             */
            if (isset($user->profile['validatedby'])) {
                unset($data['activated'],
                        $data['validatedby'],
                        $data['validationdate'],
                        $data['country'],
                        $data['organization'],
                        $data['organizationcountry'],
                        $data['flags']
                );
            }
            
            /*
             * These properties can only be changed by admin
             */
            unset($data['groups']);
        }

        /*
         * Update profile
         */
        $data['email'] = $user->profile['email'];
        $this->context->dbDriver->update(RestoDatabaseDriver::USER_PROFILE, array('profile' => $data));

        return RestoLogUtil::success('Update profile for user ' . $emailOrId);
    }

    /**
     *
     * Process HTTP PUT request on users groups
     *
     *    user/groups                                 |  Modify user groups (only admin)
     *
     * @param string $emailOrId
     * @param array $data
     */
    private function PUT_userGroups($emailOrId, $data) {

        /*
         * Groups for a user can only be modified by admin
         */
        if (!$this->user->isAdmin()) {
            RestoLogUtil::httpError(403);
        }
        
        if (empty($data['groups'])) {
            RestoLogUtil::httpError(400, 'Input groups cannot be empty');
        }
        
        /*
         * Clean groups - in any case, either 'default' or 'admin' is mandatory
         */
        $groups = array();
        $rawGroups = explode(',', $data['groups']);
        for ($i = 0, $ii = count($rawGroups); $i < $ii; $i++) {
            if ($rawGroups[$i] !== '') {
                $groups[$rawGroups[$i]] = 1;
            }
        }
        if (!isset($groups['default']) && !isset($groups['admin'])) {
            $groups['default'] = 1;
        }
        $groups = join(',', array_keys($groups));
        
        $this->context->dbDriver->update(RestoDatabaseDriver::USER_PROFILE, array(
                'profile' => array(
                    'email' => $this->getAuthorizedUser($emailOrId)->profile['email'],
                    'groups' => $groups
                ))
        );

        return RestoLogUtil::success('Groups updated', array(
                'groups' => $groups
        ));
        
    }

    /**
     * 
     * Process HTTP PUT request on users cart
     * 
     *    user/cart/{itemid}                        |  Modify item in user cart
     * 
     * @param string $emailOrId
     * @param string $itemId
     * @param array $data
     */
    private function PUT_userCart($emailOrId, $itemId, $data) {
        
        /*
         * Cart can only be modified by its owner or by admin
         */
        if ($this->getAuthorizedUser($emailOrId)->getCart()->update($itemId, $data, true)) {
            return RestoLogUtil::success('Item ' . $itemId . ' updated', array(
                'itemId' => $itemId,
                'item' => $data
            ));
        }
        else {
            return RestoLogUtil::error('Cannot update item ' . $itemId);
        }
        
    }
    
}
