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
class RestoCart{
    
    /*
     * Context
     */
    public $context;
    
    /*
     * Owner of the cart
     */
    public $user;
    
    /*
     * Cart items 
     *  array(
     *      'url' //
     *      'size'
     *      'checksum'
     *      'mimeType'
     *  )
     */
    private $items = array();
    
    /**
     * Constructor
     * 
     * @param RestoUser $user
     * @param RestoContext $context
     */
    public function __construct($user, $context, $synchronize = false){
        $this->user = $user;
        $this->context = $context;
        if ($synchronize) {
            $this->items = $this->context->dbDriver->get(RestoDatabaseDriver::CART_ITEMS, array('email' => $this->user->profile['email']));
        }
    }
    
    /**
     * Add items to cart
     * 
     * $data should be an array of array.
     * 
     * Structure :
     *      array(
     *          array(
     *              'id' => //featureidentifier
     *             'properties' => array(
     *              
     *              )
     *          ),
     *          array(
     * 
     *          ),
     *          ...
     *      )
     * 
     * @param array $data
     * @param boolean $synchronize : true to synchronize with database
     */
    public function add($data, $synchronize = false) {
        
        if (!is_array($data)) {
            return false;
        }
                    
        $items = array();
        for ($i = count($data); $i--;) {
                    
            if (!isset($data[$i]['id'])) {
                continue;
            }
            
            /*
             * Same resource cannot be added twice
             */
            $itemId = RestoUtil::encrypt($this->user->profile['email'] . $data[$i]['id']);
            if (isset($this->items[$itemId])) {
                continue;
            }   
            
            if ($synchronize) {
                if (!$this->context->dbDriver->store(RestoDatabaseDriver::CART_ITEM, array('email' => $this->user->profile['email'], 'item' => $data[$i]))) {
                    return false;
                }
            }
            $this->items[$itemId] = $data[$i];
            $items[$itemId] = $data[$i];
        }
        
        return $items;
    }
    
    /**
     * Update item in cart
     * 
     * @param string $itemId
     * @param array $item
     * @param boolean $synchronize : true to synchronize with database
     */
    public function update($itemId, $item, $synchronize = false) {
        if (!isset($itemId)) {
            return false;
        }
        if (!isset($this->items[$itemId])) {
            RestoLogUtil::httpError(1001, 'Cannot update item : ' . $itemId . ' does not exist');
        }
        if ($synchronize) {
            $this->items[$itemId] = $item;
            return $this->context->dbDriver->update(RestoDatabaseDriver::CART_ITEM, array('email' => $this->user->profile['email'], 'itemId' => $itemId, 'item' => $item));
        }
        else {
            $this->items[$itemId] = $item;
            return true;
        }
        
        return false;
    }
    
    /**
     * Remove item from cart
     * 
     * @param string $itemId
     * @param boolean $synchronize : true to synchronize with database
     */
    public function remove($itemId, $synchronize = false) {
        if (!isset($itemId)) {
            return false;
        }
        if ($synchronize) {
            if (isset($this->items[$itemId])) {
                unset($this->items[$itemId]);
            }
            return $this->context->dbDriver->remove(RestoDatabaseDriver::CART_ITEM, array('email' => $this->user->profile['email'], 'itemId' => $itemId));
        }
        else if (isset($this->items[$itemId])) {
            unset($this->items[$itemId]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Remove all items from cart
     * 
     * @param boolean $synchronize : true to synchronize with database
     */
    public function clear($synchronize = false) {
        $this->items = array();
        if ($synchronize) {
            return $this->context->dbDriver->remove(RestoDatabaseDriver::CART_ITEMS, array('email' => $this->user->profile['email']));
        }
    }
    
    /**
     * Returns all items from cart
     */
    public function getItems() {
        return $this->items;
    }
    
    /**
     * Return the cart as a JSON file
     * 
     * @param boolean $pretty
     */
    public function toJSON($pretty) {
        return RestoUtil::json_format($this->getItems(), $pretty);
    }

}
