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
 * 
 * 
 *    collections/{collection}                      |  Delete {collection}
 *    collections/{collection}/{feature}            |  Delete {feature}
 *    
 *    user/cart/{itemid}                            |  Remove {itemid} from user cart
 *    user/groups/{groups}                          |  Remove {groups} for user (only admin)
 *
 *    licenses/{licenseid}                          |  Delete {licenseid}
 *    
 */
class RestoRouteDELETE extends RestoRoute {
    
    /**
     * Constructor
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }
   
    /**
     * Process HTTP DELETE request
     *
     * @param array $segments
     */
    public function route($segments) {
        switch($segments[0]) {
            case 'collections':
                return $this->DELETE_collections($segments);
            case 'user':
                return $this->DELETE_user($segments);
            case 'licenses':
                return $this->DELETE_license($segments);
            default:
                return $this->processModuleRoute($segments);
        }
    }
    
    /**
     * 
     * Process collections
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
        if (!$this->user->hasDELETERights($collection->name, isset($feature) ? $feature->identifier : null)) {
            RestoLogUtil::httpError(403);
        }

        /*
         * collections/{collection}
         */
        if (!isset($feature)) {
            $collection->removeFromStore();
            $this->storeQuery('remove', $this->user, $collection->name, null);
            return RestoLogUtil::success('Collection ' . $collection->name . ' deleted');
        }
        /*
         * collections/{collection}/{feature}
         */
        else {
            $feature->removeFromStore();
            $this->storeQuery('remove', $this->user, $collection->name, $feature->identifier);
            return RestoLogUtil::success('Feature ' . $feature->identifier . ' deleted', array(
                'featureIdentifier' => $feature->identifier
            ));
        }
        
    }
    
    /**
     * 
     * Process user
     * 
     *    user/cart                                     |  Remove all cart items
     *    user/cart/{itemid}                            |  Remove {itemid} from user cart
     *    user/groups/{groups}                          |  Remove {groups} from groups for user (only admin)
     * 
     * @param array $segments
     */
    private function DELETE_user($segments) {
        
        $emailOrId = $this->getRequestedEmailOrId();
        
        if ($segments[1] === 'cart') {
            return $this->DELETE_userCart($emailOrId, isset($segments[2]) ? $segments[2] : null);
        }
        else if ($segments[1] === 'groups') {
            return $this->DELETE_userGroups($emailOrId, isset($segments[2]) ? $segments[2] : null);
        }
        else {
            RestoLogUtil::httpError(404);
        }
        
    }
    
    /**
     * 
     * Process user/cart
     * 
     *    user/cart                                     |  Remove all cart items
     *    user/cart/{itemid}                            |  Remove {itemid} from user cart
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
         * user/cart
         */
        if (!isset($itemId)) {
            return $this->DELETE_userCartAllItems($user);
        }
        /*
         * user/cart/{itemId}
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
        if ($user->getCart()->remove($itemId, true)) {
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
        if ($user->getCart()->clear(true)) {
            return RestoLogUtil::success('Cart cleared');
        }
        else {
            return RestoLogUtil::error('Cannot clear cart');
        }
    }

    /**
     *
     * Process HTTP DELETE request on groups
     *
     *    user/groups/{groups}                      |  Remove {groups} from groups for user (only admin)
     *
     * @param string $emailOrId
     * @param string $groups
     * @return array
     * @throws Exception
     */
    private function DELETE_userGroups($emailOrId, $groups) {
        
        /*
         * only available for admin
         */
        if (!$this->user->isAdmin()) {
            RestoLogUtil::httpError(403);
        }

        if (!isset($groups)) {
            RestoLogUtil::httpError(404);
        }
        
        $user = $this->getAuthorizedUser($emailOrId);
        
        return RestoLogUtil::success('Groups removed', array(
                    'groups' => $this->context->dbDriver->remove(RestoDatabaseDriver::GROUPS, array(
                        'userid' => $user->profile['userid'],
                        'groups' => $groups
                    ))
        ));
    }
    
    /**
     *
     * Process HTTP DELETE request on licenses
     *
     *    licenses/{licenseid}                          |  Delete {licenseid}
     *
     * @param array $segments
     */
    private function DELETE_license($segments) {
        
        /*
         * only available for admin
         */
        if (!$this->user->isAdmin()) {
            RestoLogUtil::httpError(403);
        }

        if (!isset($segments[1])) {
            RestoLogUtil::httpError(404);
        }
        else {
            $licenseId = $segments[1];
            $this->context->dbDriver->remove(RestoDatabaseDriver::LICENSE, array('licenseId' => $licenseId));
            return RestoLogUtil::success('License removed', array(
                'licenseId' => $licenseId
            ));
        }
    }

}