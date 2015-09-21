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

class RestoOrder{
    
    /*
     * Context
     */
    public $context;
    
    /*
     * Owner
     */
    public $user;
    
    /*
     * Order items
     *  array(
     *      'url' //
     *      'size'
     *      'checksum'
     *      'mimeType'
     *  )
     */
    private $order = array();
    
    /**
     * Constructor
     * 
     * @param RestoUser $user
     * @param RestoContext $context
     */
    public function __construct($user, $context, $orderId){
        /*
         * Context is mandatory
         */
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            RestoLogUtil::httpError(500, 'Context must be defined');
        }
        /*
         * User is mandatory
         */
        if (!isset($user) || !is_a($user, 'RestoUser')) {
            RestoLogUtil::httpError(500, 'User must be defined');
        }
        
        $this->user = $user;
        $this->context = $context;
        $this->order = $this->context->dbDriver->get(RestoDatabaseDriver::ORDERS, array('email' => $this->user->profile['email'], 'orderId' => $orderId));
        /*
         * Is order id associated to a valid order
         */
        if(!isset($this->order['orderId'])){
            RestoLogUtil::httpError(500, 'Order with id=' . $orderId . ' does not exist');
        }
    }
    
    /**
     * Return the cart as a JSON file
     * 
     * @param boolean $pretty
     */
    public function toJSON($pretty) {
        return  RestoUtil::json_format(array(
            'status' => 'success',
            'message' => 'Order ' . $this->order['orderId'] . ' for user ' . $this->user->profile['email'],
            'order' => $this->order
        ), $pretty);
    }
    
    /**
     * Return order as a metalink XML file
     * 
     * Warning ! a link is created only for resource that can be downloaded by users
     */
    public function toMETA4() {
        
        $meta4 = new RestoMetalink($this->context, $this->user);
        
        /*
         * One metalink file per item - if user has rights to download file
         */
        foreach ($this->order['items'] as $item) {
           
            /*
             * Invalid item
             */
            if (!isset($item['properties']) || !isset($item['properties']['services']) || !isset($item['properties']['services']['download'])) {
                continue;
            }
        
            /*
             * Item not downloadable
             */
            if (!isset($item['properties']['services']['download']['url']) || !RestoUtil::isUrl($item['properties']['services']['download']['url'])) {
                continue;
            }
            
            $exploded = parse_url($item['properties']['services']['download']['url']);
            $segments = explode('/', $exploded['path']);
            $last = count($segments) - 1;
            if ($last > 2) {
                list($modifier) = explode('.', $segments[$last], 1);
                if ($modifier !== 'download' || !$this->user->hasRightsTo(RestoUser::DOWNLOAD, array('collectionName' => $segments[$last - 2], 'featureIdentifier' => $segments[$last - 1]))) {
                    continue;
                }
            }
            
            /*
             * Add link
             */
            $meta4->addLink($item);
        }
           
        return $meta4->toString();
        
    }
    
}
