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
 * RESTo REST router for DELETE requests
 */
class RestoRouteDELETE extends RestoRoute {
    
    /**
     * Constructor
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }
   
    /**
     * 
     * Process HTTP DELETE request
     * 
     *    collections/{collection}                      |  Delete {collection}
     *    collections/{collection}/{feature}            |  Delete {feature}
     *    
     *    users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
     *    users/{userid}/legal                          |  Remove {userid} legal informations (only admin)
     *    users/{userid}/grantedvisibility/{visibility} |  Remove {visibility} to {userid} granted visibilities (only admin)
     *
     *    licenses/{licenseid}                          |  Delete {licenseid}
     *
     *
     * @param array $segments
     */
    public function route($segments) {
        switch($segments[0]) {
            case 'collections':
                return $this->DELETE_collections($segments);
            case 'users':
                return $this->DELETE_users($segments);
            case 'licenses':
                return $this->DELETE_license($segments);
            default:
                return $this->processModuleRoute($segments);
        }
    }
    
    /**
     * 
     * Process HTTP DELETE request on collections
     * 
     *    collections/{collection}                      |  Delete {collection}
     *    collections/{collection}/{feature}            |  Delete {feature}
     * 
     * @param array $segments
     */
    private function DELETE_collections($segments) {
        
        /*
         * {collection} is mandatory and no modifier is allowed
         */
        if (!isset($segments[1]) || isset($segments[3])) {
            RestoLogUtil::httpError(404);
        }
        
        $collection = new RestoCollection($segments[1], $this->context, $this->user, array('autoload' => true));
        if (isset($segments[2])) {
            $feature = new RestoFeature($this->context, $this->user, array(
                'featureIdentifier' => $segments[2],
                'collection' => $collection
            ));
            if (!$feature->isValid()) {
                RestoLogUtil::httpError(404);
            }
        }
        
        /*
         * Check credentials
         */
        if (!$this->user->canDelete($collection->name, isset($feature) ? $feature->identifier : null)) {
            RestoLogUtil::httpError(403);
        }

        /*
         * collections/{collection}
         */
        if (!isset($feature)) {
            $collection->removeFromStore();
            $this->storeQuery('remove', $collection->name, null);
            return RestoLogUtil::success('Collection ' . $collection->name . ' deleted');
        }
        /*
         * collections/{collection}/{feature}
         */
        else {
            $feature->removeFromStore();
            $this->storeQuery('remove', $collection->name, $feature->identifier);
            return RestoLogUtil::success('Feature ' . $feature->identifier . ' deleted', array(
                'featureIdentifier' => $feature->identifier
            ));
        }
        
    }
    
    
    /**
     * 
     * Process HTTP DELETE request on users
     * 
     *    users/{userid}/cart                           |  Remove all cart items
     *    users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
     *    users/{userid}/legal                          |  Remove {userid} legal informations (only admin)
     *    users/{userid}/grantedvisibility/{visibility} |  Remove {visibility} to {userid} granted visibilities (only admin)
     *
     * @param array $segments
     */
    private function DELETE_users($segments) {
        
        if ($segments[2] === 'cart') {
            return $this->DELETE_userCart($segments[1], isset($segments[3]) ? $segments[3] : null);
        }
        else if ($segments[2] === 'legal') {
            return $this->DELETE_userLegalInfo($segments[1]);
        }
        else if ($segments[2] === 'grantedvisibility') {
            return $this->DELETE_userGrantedVisibility($segments[1], isset($segments[3]) ? $segments[3] : null);
        }
        else {
            RestoLogUtil::httpError(404);
        }
        
    }
    
    /**
     * 
     * Process HTTP DELETE request on users cart
     * 
     *    users/{userid}/cart                           |  Remove all cart items
     *    users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
     * 
     * @param string $emailOrId
     * @param string $itemId
     */
    private function DELETE_userCart($emailOrId, $itemId) {
        
        /*
         * Cart can only be modified by its owner or by admin
         */
        $user = $this->getAuthorizedUser($emailOrId);
                
        /*
         * users/{userid}/cart
         */
        if (!isset($itemId)) {
            return $this->DELETE_userCartAllItems($user);
        }
        /*
         * users/{userid}/cart/{itemId}
         */
        else {
            return $this->DELETE_userCartItem($user, $itemId);
        }
     
    }
    
    /**
     * 
     * Delete one item
     * 
     * @param RestoUser $user
     * @param string $itemId
     */
    private function DELETE_userCartItem($user, $itemId) {
        if ($user->removeFromCart($itemId, true)) {
            return RestoLogUtil::success('Item removed from cart', array(
                'itemid' => $itemId
            ));
        }
        else {
            return RestoLogUtil::error('Item cannot be removed', array(
                'itemid' => $itemId
            ));
        }
    }
    
    /**
     * 
     * Delete all items within cart
     * 
     * @param RestoUser $user
     * @param string $itemId
     */
    private function DELETE_userCartAllItems($user) {
        if ($user->clearCart(true)) {
            return RestoLogUtil::success('Cart cleared');
        }
        else {
            return RestoLogUtil::error('Cannot clear cart');
        }
    }

    /**
     *
     * Process HTTP DELETE request on grantedvisibility
     *
     *    users/{userid}/grantedvisibility/{visibility} |  Remove {visibility} to {userid} granted visibilities (only admin)
     *
     * @param $userId
     * @param $visibility
     * @return array
     * @throws Exception
     */
    private function DELETE_userGrantedVisibility($userId, $visibility) {
        
        /*
         * only available for admin
         */
        if (!$this->isAdminUser()) {
            RestoLogUtil::httpError(403);
        }

        if (!isset($visibility)) {
            RestoLogUtil::httpError(404);
        }
        else {
            $this->context->dbDriver->remove(RestoDatabaseDriver::USER_GRANTED_VISIBILITY,
                array('userid' => $userId, 'visibility' => $visibility));
            return RestoLogUtil::success('Granted visibility removed', array(
                'visibility' => $visibility
            ));
        }
    }

    /**
     *
     * Process HTTP DELETE request on legalinfo
     *
     *    users/{userid}/legal                 |  Remove {userid} legal informations (only admin)
     *
     * @param $userId
     * @return array
     * @throws Exception
     */
    private function DELETE_userLegalInfo($userId)
    {
        /*
         * only available for admin
         */
        if (!$this->isAdminUser()) {
            RestoLogUtil::httpError(403);
        }
        else {
            $this->context->dbDriver->remove(RestoDatabaseDriver::USER_LEGAL_INFO, array('userid' => $userId));
            return RestoLogUtil::success('Legal information removed', array('userid' => $userId));
        }
    }

    /**
     *
     * Process HTTP DELETE request on licenses
     *
     *    licenses/{licenseid}                          |  Delete {licenseid}
     *
     * @param array $segments
     */
    private function DELETE_license($segments)
    {
        /*
         * only available for admin
         */
        if (!$this->isAdminUser()) {
            RestoLogUtil::httpError(403);
        }

        if (!isset($segments[1])) {
            RestoLogUtil::httpError(404);
        }
        else {
            $licenseId = $segments[1];
            $this->context->dbDriver->remove(RestoDatabaseDriver::PRODUCT_LICENSE, array('license_id' => $licenseId));
            return RestoLogUtil::success('license removed', array(
                'license_id' => $licenseId
            ));
        }
    }
}