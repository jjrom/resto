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
         * Only owner of a collection can delete it
         */
        if (!$this->user->hasRightsTo(RestoUser::UPDATE, array('collection' => $collection))) {
            RestoLogUtil::httpError(403);
        }

        /*
         * collections/{collection}
         */
        if (!isset($feature)) {
            
            $collection->removeFromStore();
            
            /*
             * Store query
             */
            if ($this->context->storeQuery === true) {
                $this->user->storeQuery($this->context->method, 'remove', $collection->name, null, $this->context->query, $this->context->getUrl());
            }
            
            return RestoLogUtil::success('Collection ' . $collection->name . ' deleted');
        }
        /*
         * collections/{collection}/{feature}
         */
        else {
            
            $feature->removeFromStore();
            
            /*
             * Store query
             */
            if ($this->context->storeQuery === true) {
                $this->user->storeQuery($this->context->method, 'remove', $collection->name, $feature->identifier, $this->context->query, $this->context->getUrl());
            }
            
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
     * 
     * @param array $segments
     */
    private function DELETE_user($segments) {
        
        if ($segments[1] === 'cart') {
            
            /*
             * Clear all cart items
             */
            if (!isset($segments[2])) {
                return $this->user->getCart()->clear(true) ? RestoLogUtil::success('Cart cleared') : RestoLogUtil::error('Cannot clear cart');
            }
            
            /*
             * Delete itemId only
             */
            else {
                return $this->user->getCart()->remove($segments[2], true) ? RestoLogUtil::success('Item removed from cart', array('itemid' => $itemId)) : RestoLogUtil::error('Item cannot be removed', array('itemid' => $itemId));
                
            }
        }
        else {
            RestoLogUtil::httpError(404);
        }
        
    }
    
}