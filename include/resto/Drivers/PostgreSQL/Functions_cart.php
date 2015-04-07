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
 * RESTo PostgreSQL cart functions
 */
class Functions_cart{
    
    private $dbDriver = null;
    private $dbh = null;
    
    /**
     * Constructor
     * 
     * @param array $config
     * @param RestoCache $cache
     * @throws Exception
     */
    public function __construct($dbDriver) {
        $this->dbDriver = $dbDriver;
        $this->dbh = $dbDriver->dbh;
    }
    
    /**
     * Return cart for user
     * 
     * @param string $identifier
     * @return array
     * @throws exception
     */
    public function getCartItems($identifier) {
        
        $items = array();
        
        if (!isset($identifier)) {
            return $items;
        }
        
        $query = 'SELECT itemid, item FROM usermanagement.cart WHERE email=\'' . pg_escape_string($identifier) . '\'';
        $results = $this->dbDriver->query($query, 500, 'Cannot get cart items');
        while ($result = pg_fetch_assoc($results)) {
            $items[$result['itemid']] = json_decode($result['item'], true);
        }
        
        return $items;
    }
    
    /**
     * Return orders list for user
     * 
     * @param string $identifier
     * @param string $orderId
     * @return array
     * @throws exception
     */
    public function getOrders($identifier, $orderId = null) {
        
        $items = array();
        
        if (!isset($identifier)) {
            return $items;
        }
        
        $query = 'SELECT orderid, querytime, items FROM usermanagement.orders WHERE email=\'' . pg_escape_string($identifier) . '\'' . (isset($orderId) ? ' AND orderid=\'' . pg_escape_string($orderId) . '\'' : '');
        $results = $this->dbDriver->query($query);
        while ($result = pg_fetch_assoc($results)) {
            $items[] = array(
                'orderId' => $result['orderid'],
                'date' => $result['querytime'],
                'items' => json_decode($result['items'], true)
            );
        }
        if (isset($orderId) && isset($items[0])) {
            return $items[0];
        }
        
        return $items;
    }
    
    /**
     * Return true if resource is within cart
     * 
     * @param string $itemId
     * @return boolean
     * @throws exception
     */
    public function isInCart($itemId) {
        if (!isset($itemId)) {
            return false;
        }
        $query = 'SELECT 1 FROM usermanagement.cart WHERE itemid=\'' . pg_escape_string($itemId) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query(($query)));
        return !empty($results);
    }
    
    /**
     * Add resource url to cart
     * 
     * @param string $identifier
     * @param array $item
     *   
     *   Must contain at least an 'id' entry
     *   
     * @return boolean
     * @throws exception
     */
    public function addToCart($identifier, $item = array()) {
        if (!isset($identifier) || !isset($item) || !is_array($item) || !isset($item['id'])) {
            return false;
        }
        $itemId = RestoUtil::encrypt($identifier . $item['id']);
        if ($this->isInCart($itemId)) {
            RestoLogUtil::httpError(1000, 'Cannot add item : ' . $itemId . ' already exists');
        }
        $values = array(
            '\'' . pg_escape_string($itemId) . '\'',
            '\'' . pg_escape_string($identifier) . '\'',
            '\'' . pg_escape_string(json_encode($item)) . '\'',
            'now()'
        );
        $this->dbDriver->query('INSERT INTO usermanagement.cart (itemid, email, item, querytime) VALUES (' . join(',', $values) . ')');
        return array($itemId => $item);
    }
    
    /**
     * Update cart
     * 
     * @param string $identifier
     * @param string $itemId
     * @param array $item
     *   
     *   Must contain at least a 'url' entry
     *   
     * @return boolean
     * @throws exception
     */
    public function updateCart($identifier, $itemId, $item) {
        if (!isset($identifier) || !isset($itemId) || !isset($item) || !is_array($item) || !isset($item['url'])) {
            return false;
        }
        if (!$this->isInCart($itemId)) {
            RestoLogUtil::httpError(1001, 'Cannot update item : ' . $itemId . ' does not exist');
        }
        $this->dbDriver->query('UPDATE usermanagement.cart SET item = \''. pg_escape_string(json_encode($item)) . '\', querytime=now() WHERE email=\'' . pg_escape_string($identifier) . '\' AND itemid=\'' . pg_escape_string($itemId) . '\'');
        return true;
    }
    
    /**
     * Remove resource from cart
     * 
     * @param string $identifier
     * @param string $itemId
     * @return boolean
     * @throws exception
     */
    public function removeFromCart($identifier, $itemId) {
        if (!isset($identifier) || !isset($itemId)) {
            return false;
        }
        $this->dbDriver->query('DELETE FROM usermanagement.cart WHERE itemid=\'' . pg_escape_string($itemId) . '\' AND email=\'' . pg_escape_string($identifier) . '\'', 500, 'Cannot remove ' . $itemId . ' from cart');
        return true;
    }
    
    /**
     * Place order for user
     * 
     * @param string $identifier
     * 
     * @return array
     * @throws exception
     */
    public function placeOrder($identifier) {
        
        if (!isset($identifier)) {
            return false;
        }
        
        try {
            
            /*
             * Transaction
             */
            $this->dbDriver->query('BEGIN');
                
            /*
             * Do not create empty orders
             */
            $items = $this->getCartItems($identifier);
            if (!isset($items) || count($items) === 0) {
                return false;
            }
            
            $orderId = RestoUtil::encrypt($identifier . microtime());
            $values = array(
                '\'' . pg_escape_string($orderId) . '\'',
                '\'' . pg_escape_string($identifier) . '\'',
                '\'' . pg_escape_string(json_encode($items)) . '\'',
                'now()'
            );
            $this->dbDriver->query('INSERT INTO usermanagement.orders (orderid, email, items, querytime) VALUES (' . join(',', $values) . ')');
            
            /*
             * Empty cart
             */
            $this->dbDriver->query('DELETE FROM usermanagement.cart WHERE email=\'' . pg_escape_string($identifier) . '\'');
            $this->dbDriver->query('COMMIT');
            
            return array(
                'orderId' => $orderId,
                'items' => $items
            );
            
        } catch (Exception $e) {
            $this->dbDriver->query('ROLLBACK');
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }
        
        return false;
    }
    
}
