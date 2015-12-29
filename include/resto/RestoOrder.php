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

class RestoOrder {
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
    public function __construct($user, $context, $orderId) {
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
        if (!isset($this->order['orderId'])) {
            RestoLogUtil::httpError(404, 'Order with id=' . $orderId . ' does not exist');
        }
    }

    /**
     * Return the cart as a JSON file
     * 
     * @param boolean $pretty
     */
    public function toJSON($pretty) {
        
        $informations = $this->generateItemsInformations();

        return RestoUtil::json_format(array(
                    'status' => 'success',
                    'message' => 'Order ' . $this->order['orderId'] . ' for user ' . $this->user->profile['email'],
                    'order' => array(
                        'orderId' => $this->order['orderId'],
                        'items' => $informations['items'],   // downloadable items
                        'errors' => $informations['errors']   // undownloadable items
                    )
                        ), $pretty);
    }

    /**
     * Return order as a metalink XML file
     * 
     * Warning ! a link is created only for resource that can be downloaded by users
     */
    public function toMETA4() {

        $meta4 = new RestoMetalink($this->context, $this->user);
        
        $informations = $this->generateItemsInformations();

        /*
         * One metalink file per item - if user has rights to download file
         */
        foreach ($informations['items'] as $item) {
            /*
             * Add link
             */
            $meta4->addLink($item);
        }

        return $meta4->toString();
    }

    /**
     * Generate items informations
     * 
     * Get an array containing two lists :
     *  -> items : Downloadable items with url
     *  -> errors : Not downloadable items with explanations
     * 
     * @return array
     */
    private function generateItemsInformations() {
        
        /*
         * Features with errors
         */
        $errors = array();
        /*
         * Features without errors
         */
        $items = array();

        /*
         * Loop over items
         */
        foreach ($this->order['items'] as $item) {

            /*
             * Check if item get an id
             */
            if (!isset($item['id'])) {
                array_push($errors, array(
                    'type' => 'Feature',
                    'ErrorMessage' => 'Wrong item',
                    'ErrorCode' => 404,
                    'properties' => isset($item['properties']) ? $item['properties'] : null
                ));
                continue;
            }

            /*
             * Create RestoFeature
             */
            $feature = new RestoFeature($this->context, $this->user, array(
                'featureIdentifier' => $item['id']
            ));

            /*
             * Check if User is fullfilling license requirements
             */
            if (!$feature->getLicense()->isApplicableToUser($this->user)) {

                array_push($errors, array(
                    'type' => 'Feature',
                    'id' => $feature->identifier,
                    'ErrorMessage' => 'User does not fulfill license requirements',
                    'ErrorCode' => 403,
                    'properties' => isset($item['properties']) ? $item['properties'] : null
                ));

                continue;
            }

            /*
             * Check if user has to sign license
             */
            if ($feature->getLicense()->hasToBeSignedByUser($this->user)) {

                array_push($errors, array(
                    'type' => 'Feature',
                    'id' => $feature->identifier,
                    'ErrorMessage' => 'User has to sign license',
                    'ErrorCode' => 3002,
                    'license' => $feature->getLicense()->toArray(),
                    'properties' => isset($item['properties']) ? $item['properties'] : null
                ));

                continue;
            }

            /*
             * Check if item is valid
             */
            if (!isset($item['properties']) || !isset($item['properties']['services']) || !isset($item['properties']['services']['download'])) {

                array_push($errors, array(
                    'type' => 'Feature',
                    'id' => $feature->identifier,
                    'ErrorMessage' => 'Invalid item',
                    'ErrorCode' => 404,
                    'properties' => isset($item['properties']) ? $item['properties'] : null
                ));

                continue;
            }

            /*
             * Check is item is downloadable
             */
            if (!isset($item['properties']['services']['download']['url']) || !RestoUtil::isUrl($item['properties']['services']['download']['url'])) {

                array_push($errors, array(
                    'type' => 'Feature',
                    'id' => $feature->identifier,
                    'ErrorMessage' => 'Item not downloadable',
                    'ErrorCode' => 404,
                    'properties' => isset($item['properties']) ? $item['properties'] : null
                ));

                continue;
            }

            /*
             * Check user rights
             */
            if (!$this->user->hasRightsTo(RestoUser::DOWNLOAD, array('collectionName' => $item['properties']['collection'], 'featureIdentifier' => $feature->identifier))) {

                    array_push($errors, array(
                    'type' => 'Feature',
                    'id' => $feature->identifier,
                    'ErrorMessage' => "User hasn't enough rights. Please contact an administrator",
                    'ErrorCode' => 403,
                    'properties' => isset($item['properties']) ? $item['properties'] : null
                ));

                continue;
            }
            
            /*
             * Update local download url with a shared link
             */
            $exploded = explode('?', $item['properties']['services']['download']['url']);
            if ($exploded[0] === $this->context->baseUrl . join('/',array('/collections', $item['properties']['collection'], $feature->identifier, 'download'))) {
                $item['properties']['services']['download']['url'] = $this->getSharedLink($item['properties']['services']['download']['url']);
            }
            
            /*
             * Add item to downloadable items list
             */
            array_push($items, array(
                'type' => 'Feature',
                'id' => $feature->identifier,
                'properties' => isset($item['properties']) ? $item['properties'] : null
            ));
        }
        
        /*
         * Return array containing errors and downloadable items
         */
        return array(
            'errors' => $errors,
            'items' => $items
        );
    }

    /**
     * Return a sharable public link from input resourceUrl
     * 
     * @param string $resourceUrl
     * @return string
     */
    private function getSharedLink($resourceUrl) {
        $shared = $this->context->dbDriver->get(RestoDatabaseDriver::SHARED_LINK, array(
            'email' => $this->user->profile['email'],
            'resourceUrl' => $resourceUrl,
            'duration' => isset($this->context->sharedLinkDuration) ? $this->context->sharedLinkDuration : null
        ));
        return $resourceUrl . (strpos($resourceUrl, '?') === false ? '?_tk=' : '&_tk=') . $shared['token'];
    }

}
