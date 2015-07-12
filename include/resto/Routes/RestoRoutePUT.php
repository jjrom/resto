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
 */
class RestoRoutePUT extends RestoRoute {
    
    /**
     * Constructor
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }
   
    /**
     * 
     * Process HTTP PUT request
     *  
     *    collections/{collection}                      |  Update {collection}
     *    collections/{collection}/{feature}            |  Update {feature}
     *    
     *    users/{userid}                                |  Modify {userid} profile
     *    users/{userid}/cart/{itemid}                  |  Modify item in {userid} cart
     *    users/{userid}/grantedvisibility              |  Modify {userid} granted visibility (only admin)
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
            case 'users':
                return $this->PUT_users($segments, $data);
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
            $this->storeQuery('update', $collection->name, null);
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
     *    users/{userid}/grantedvisibility              |  Modify {userid} granted visibility (only admin)
     *    users/{userid}/cart/{itemid}                  |  Modify item in {userid} cart
     * 
     * @param array $segments
     * @param array $data
     */
    private function PUT_users($segments, $data) {

        /*
         * {userid} is mandatory
         */
        if (!isset($segments[1])) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * users/{userid}
         */
        if (!isset($segments[2])) {
            return $this->PUT_userProfile($segments[1], $data);
        }
        
        if ($segments[2] === 'grantedvisibility') {
            return $this->PUT_userGrantedVisibility($segments[1], $data);
        }

        /*
         * Mandatory {itemid}
         */
        if (!isset($segments[3])) {
            RestoLogUtil::httpError(404);
        }
        
        if ($segments[2] === 'cart') {
            return $this->PUT_userCart($segments[1], $segments[3], $data);
        }
        else {
            RestoLogUtil::httpError(404);
        }
        
    }
    
    /**
     *
     * Process HTTP PUT request on user profile
     *
     *    users/{userid}                       |  Update {userid} profile
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
     * Process HTTP PUT request on users granted visibility
     *
     *    users/{userid}/grantedvisibility              |  Modify {userid} granted visibility (only admin)
     *
     * @param string $emailOrId
     * @param array $data
     */
    private function PUT_userGrantedVisibility($emailOrId, $data) {

        /*
         * Granted visibility for a user can only be modified by admin
         */
        if (!$this->user->isAdmin()) {
            RestoLogUtil::httpError(403);
        }

        $user = $this->getAuthorizedUser($emailOrId);

        $this->context->dbDriver->update(RestoDatabaseDriver::USER_PROFILE, array(
                'profile' => array(
                    'email' => $user->profile['email'],
                    'grantedvisibility' => isset($data['grantedvisibility']) && $data['grantedvisibility'] !== '' ? $data['grantedvisibility'] : null
                ))
        );

        return RestoLogUtil::success('Granted visibity of user ' . $emailOrId . ' has been updated with '. $data['grantedvisibility']);
    }

    /**
     * 
     * Process HTTP PUT request on users cart
     * 
     *    users/{userid}/cart/{itemid}                  |  Modify item in {userid} cart
     * 
     * @param string $emailOrId
     * @param string $itemId
     * @param array $data
     */
    private function PUT_userCart($emailOrId, $itemId, $data) {
        
        /*
         * Cart can only be modified by its owner or by admin
         */
        $user = $this->getAuthorizedUser($emailOrId);
         
        if ($user->getCart()->update($itemId, $data, true)) {
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
