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

        /**
         * collections/{collection}
         * 
         *  @SWG\Delete(
         *      tags={"collection"},
         *      path="/collections/{collectionId}",
         *      summary="Delete collection",
         *      description="Delete collection {collectionId} if collection is not empty",
         *      operationId="deleteCollection",
         *      produces={"application/json"},
         *      @SWG\Parameter(
         *          name="collectionId",
         *          in="path",
         *          description="Collection identifier",
         *          required=true,
         *          type="string",
         *          @SWG\Items(type="string")
         *      ),
         *      @SWG\Response(
         *          response="200",
         *          description="Acknowledgment on successful collection deletion"
         *      ),
         *      @SWG\Response(
         *          response="404",
         *          description="Collection not found"
         *      ),
         *      @SWG\Response(
         *          response="403",
         *          description="Forbidden"
         *      )
         *  )
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
        /**
         * collections/{collection}/{featureId}
         * 
         *  @SWG\Delete(
         *      tags={"feature"},
         *      path="/collections/{collectionId}/{featureId}",
         *      summary="Delete feature",
         *      description="Delete feature {featureId} within collection {collectionId}",
         *      operationId="deleteFeature",
         *      produces={"application/json"},
         *      @SWG\Parameter(
         *          name="collectionId",
         *          in="path",
         *          description="Collection identifier",
         *          required=true,
         *          type="string",
         *          @SWG\Items(type="string")
         *      ),
         *      @SWG\Parameter(
         *          name="featureId",
         *          in="path",
         *          description="Feature identifier",
         *          required=true,
         *          type="string",
         *          @SWG\Items(type="string")
         *      ),
         *      @SWG\Response(
         *          response="200",
         *          description="Acknowledgment on successful feature deletion"
         *      ),
         *      @SWG\Response(
         *          response="404",
         *          description="Feature not found"
         *      ),
         *      @SWG\Response(
         *          response="403",
         *          description="Forbidden"
         *      )
         *  )
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
     *  @SWG\Delete(
     *      tags={"user"},
     *      path="/user/cart/{itemId}",
     *      summary="Delete cart item(s)",
     *      description="Delete cart item {itemId}. Delete all items if no {itemId} is specified",
     *      operationId="deleteCartItem",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="itemId",
     *          in="path",
     *          description="Cart item identifier",
     *          required=false,
     *          type="string",
     *          @SWG\Items(type="string")
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="Acknowledgment on successful cart item(s) deletion"
     *      ),
     *      @SWG\Response(
     *          response="404",
     *          description="ItemId not found"
     *      ),
     *      @SWG\Response(
     *          response="403",
     *          description="Forbidden"
     *      )
     *  )
     * 
     * @param array $segments
     */
    private function DELETE_user($segments) {
        
        if (isset($segments[1]) && $segments[1] === 'cart') {
            
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
                return $this->user->getCart()->remove($segments[2], true) ? RestoLogUtil::success('Item removed from cart', array('itemid' => $segments[2])) : RestoLogUtil::error('Item cannot be removed', array('itemid' => $segments[2]));   
            }
        }
        else {
            RestoLogUtil::httpError(404);
        }
        
    }
    
}