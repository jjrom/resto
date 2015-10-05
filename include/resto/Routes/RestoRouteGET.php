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
 * 
 * RESTo REST router for GET requests
 * 
 *    api/collections/search                        |  Search on all collections
 *    api/collections/{collection}/search           |  Search on {collection}
 *    api/collections/describe                      |  Opensearch service description at collections level
 *    api/collections/{collection}/describe         |  Opensearch service description for products on {collection}
 *    api/user/connect                              |  Connect and return a new valid connection token
 *    api/user/resetPassword                        |  Ask for password reset (i.e. reset link sent to user email adress)
 *    api/user/checkToken                           |  Check if token is valid
 *    api/user/activate                             |  Activate users with activation code
 *    
 *    collections                                   |  List all collections            
 *    collections/{collection}                      |  Get {collection} description
 *    collections/{collection}/{feature}            |  Get {feature} description within {collection}
 *    collections/{collection}/{feature}/download   |  Download {feature}
 *    collections/{collection}/{feature}/wms        |  Access WMS for {feature}
 *
 *    licenses                                      |  List all licenses
 *    licenses/{licenseid}                          |  Get {licenseid} license description 
 * 
 *    user                                          |  Show user information
 *    user/groups                                   |  Show user groups
 *    user/cart                                     |  Show user cart
 *    user/orders                                   |  Show orders for user
 *    user/orders/{orderid}                         |  Show {orderid} order for user
 *    user/rights                                   |  Show rights for user
 *    user/rights/{collection}                      |  Show rights for user on {collection}
 *    user/rights/{collection}/{feature}            |  Show rights for user on {feature} from {collection}
 *    user/signatures                               |  Show signatures for user
 *
 */
class RestoRouteGET extends RestoRoute {

    /**
     * Constructor
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }

    /**
     * Process HTTP GET request
     *
     * @param array $segments
     *
     */
    public function route($segments) {
        switch ($segments[0]) {
            case 'api':
                return $this->GET_api($segments);
            case 'collections':
                return $this->GET_collections($segments);
            case 'users':
                return $this->GET_users($segments);
            case 'user':
                return $this->GET_user($segments);
            case 'licenses':
                return $this->GET_licenses($segments);
            default:
                return $this->processModuleRoute($segments);
        }
    }

    /**
     * 
     * Process HTTP GET request on api
     * 
     * @param array $segments
     */
    private function GET_api($segments) {


        if (!isset($segments[1]) || isset($segments[4])) {
            RestoLogUtil::httpError(404);
        }

        /*
         * api/collections
         */
        if ($segments[1] === 'collections' && isset($segments[2])) {
            return $this->GET_apiCollections($segments);
        }

        /*
         * api/user
         */
        else if ($segments[1] === 'user' && isset($segments[2])) {
            return $this->GET_apiUser($segments);
        }
        /*
         * Process module
         */
        else {
            return $this->processModuleRoute($segments);
        }
        
    }

    /**
     * Process api/collections
     * 
     * @SWG\Get(
     *      tags={"collections"},
     *      path="/api/collections/describe.xml",
     *      summary="Describe OSDD",
     *      description="Returns the OpenSearch Document Description (OSDD) for the search service on all collections",
     *      operationId="describeCollections",
     *      produces={"application/xml"},
     *      @SWG\Response(
     *          response="200",
     *          description="OpenSearch Document Description (OSDD)"
     *      )
     * )
     * 
     * @SWG\Get(
     *      tags={"collection"},
     *      path="/api/collections/{collectionId}/describe.xml",
     *      summary="Describe OSDD",
     *      description="Returns the OpenSearch Document Description (OSDD) for the search service of collection {collectionId}",
     *      operationId="describeCollection",
     *      produces={"application/xml"},
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
     *          description="OpenSearch Document Description (OSDD)"
     *      ),
     *      @SWG\Response(
     *          response="404",
     *          description="Collection not found"
     *      )
     * )
     * 
     * @SWG\Get(
     *      tags={"collections"},
     *      path="/api/collections/search.{format}",
     *      summary="Search",
     *      description="Search products within all collections",
     *      operationId="searchInCollections",
     *      produces={"application/json", "application/atom+xml"},
     *      @SWG\Parameter(
     *          name="format",
     *          in="path",
     *          description="Output format",
     *          required=true,
     *          type="string",
     *          @SWG\Items(type="string"),
     *          enum={"atom", "json"}
     *      ),
     *      @SWG\Parameter(
     *          name="q",
     *          in="query",
     *          description="Free text search - OpenSearch {searchTerms}",
     *          required=false,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="maxRecords",
     *          in="query",
     *          description="Number of results returned per page - OpenSearch {count}",
     *          required=false,
     *          type="integer",
     *          minimum=1,
     *          maximum=500,
     *          default=50
     *      ),
     *      @SWG\Parameter(
     *          name="index",
     *          in="query",
     *          description="First result to provide - OpenSearch {startIndex}",
     *          required=false,
     *          type="integer",
     *          minimum=1,
     *          default=1
     *      ),
     *      @SWG\Parameter(
     *          name="page",
     *          in="query",
     *          description="First page to provide - OpenSearch {startPage}",
     *          required=false,
     *          type="integer",
     *          minimum=1,
     *          default=1
     *      ),
     *      @SWG\Parameter(
     *          name="lang",
     *          in="query",
     *          description="Two letters language code according to ISO 639-1 - OpenSearch {language}",
     *          required=false,
     *          type="string",
     *          pattern="^[a-z]{2}$",
     *          default="en"
     *      ),
     *      @SWG\Parameter(
     *          name="identifier",
     *          in="query",
     *          description="Either resto identifier or productIdentifier - OpenSearch {geo:uid}",
     *          required=false,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="geometry",
     *          in="query",
     *          description="Region of Interest defined in Well Known Text standard (WKT) with coordinates in decimal degrees (EPSG:4326) - OpenSearch {geo:geometry}",
     *          required=false,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="box",
     *          in="query",
     *          description="Region of Interest defined by 'west, south, east, north' coordinates of longitude, latitude, in decimal degrees (EPSG:4326) - OpenSearch {geo:box}",
     *          required=false,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="name",
     *          in="query",
     *          description="Location string e.g. Paris, France - OpenSearch {geo:name}",
     *          required=false,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="lon",
     *          in="query",
     *          description="Longitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lat - OpenSearch {geo:lon}",
     *          required=false,
     *          minimum=-180,
     *          maximum=180,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="lat",
     *          in="query",
     *          description="Latitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lon - OpenSearch {geo:lat}",
     *          required=false,
     *          minimum=-90,
     *          maximum=90,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="radius",
     *          in="query",
     *          description="Expressed in meters - should be used with geo:lon and geo:lat - OpenSearch {geo:radius}",
     *          required=false,
     *          minimum=1,
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="startDate",
     *          in="query",
     *          description="Beginning of the time slice of the search query. Format should follow RFC-3339 - OpenSearch {time:start}",
     *          type="string",
     *          pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$"
     *      ),
     *      @SWG\Parameter(
     *          name="completionDate",
     *          in="query",
     *          description="End of the time slice of the search query. Format should follow RFC-3339 - OpenSearch {time:end}",
     *          type="string",
     *          pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$"
     *      ),
     *      @SWG\Parameter(
     *          name="parentIdentifier",
     *          in="query",
     *          description="OpenSearch {eo:parentIdentifier}",
     *          type="string",
     *          pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$"
     *      ),
     *      @SWG\Parameter(
     *          name="productType",
     *          in="query",
     *          description="OpenSearch {eo:productType}",
     *          type="string",
     *          enum={}
     *      ),
     *      @SWG\Parameter(
     *          name="processingLevel",
     *          in="query",
     *          description="OpenSearch {eo:processingLevel}",
     *          type="string",
     *          enum={}
     *      ),
     *      @SWG\Parameter(
     *          name="platform",
     *          in="query",
     *          description="OpenSearch {eo:platform}",
     *          type="string",
     *          enum={}
     *      ),
     *      @SWG\Parameter(
     *          name="instrument",
     *          in="query",
     *          description="OpenSearch {eo:instrument}",
     *          type="string",
     *          enum={}
     *      ),
     *      @SWG\Parameter(
     *          name="sensorMode",
     *          in="query",
     *          description="OpenSearch {eo:sensorMode}",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="resolution",
     *          in="query",
     *          description="Spatial resolution expressed in meters - OpenSearch {eo:resolution}",
     *          type="number",
     *          pattern="^(?:[1-9]\d*|0)?(?:\.\d+)?$"
     *      ),
     *      @SWG\Parameter(
     *          name="organisationName",
     *          in="query",
     *          description="OpenSearch {eo:organisationName}",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="orbitNumber",
     *          in="query",
     *          description="OpenSearch {eo:orbitNumber}",
     *          type="integer",
     *          minimum=1
     *      ),
     *      @SWG\Parameter(
     *          name="cloudCover",
     *          in="query",
     *          description="Cloud cover expressed in percent - OpenSearch {eo:cloudCover}",
     *          type="string",
     *          pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
     *      ),
     *      @SWG\Parameter(
     *          name="snowCover",
     *          in="query",
     *          description="Snow cover expressed in percent - OpenSearch {eo:snowCover}",
     *          type="string",
     *          pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
     *      ),
     *      @SWG\Parameter(
     *          name="cultivatedCover",
     *          in="query",
     *          description="Cultivated area expressed in percent - OpenSearch {resto:cultivatedCover}",
     *          type="string",
     *          pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
     *      ),
     *      @SWG\Parameter(
     *          name="desertCover",
     *          in="query",
     *          description="Desert area expressed in percent - OpenSearch {resto:cultivatedCover}",
     *          type="string",
     *          pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
     *      ),
     *      @SWG\Parameter(
     *          name="floodedCover",
     *          in="query",
     *          description="Flooded area expressed in percent - OpenSearch {resto:cultivatedCover}",
     *          type="string",
     *          pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
     *      ),
     *      @SWG\Parameter(
     *          name="forestCover",
     *          in="query",
     *          description="Forest area expressed in percent - OpenSearch {resto:cultivatedCover}",
     *          type="string",
     *          pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
     *      ),
     *      @SWG\Parameter(
     *          name="herbaceousCover",
     *          in="query",
     *          description="Herbaceous area expressed in percent - OpenSearch {resto:cultivatedCover}",
     *          type="string",
     *          pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
     *      ),
     *      @SWG\Parameter(
     *          name="iceCover",
     *          in="query",
     *          description="Ice area expressed in percent - OpenSearch {resto:cultivatedCover}",
     *          type="string",
     *          pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
     *      ),
     *      @SWG\Parameter(
     *          name="urbanCover",
     *          in="query",
     *          description="Urban area expressed in percent - OpenSearch {resto:cultivatedCover}",
     *          type="string",
     *          pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
     *      ),
     *      @SWG\Parameter(
     *          name="waterCover",
     *          in="query",
     *          description="Water area expressed in percent - OpenSearch {resto:waterCover}",
     *          type="string",
     *          pattern="^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$"
     *      ),
     *      @SWG\Parameter(
     *          name="updated",
     *          in="query",
     *          description="Last update of the product within database - OpenSearch {dc:date}",
     *          type="string",
     *          pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$"
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="OpenSearch Document Description (OSDD)"
     *      ),
     *      @SWG\Response(
     *          response="404",
     *          description="Collection not found"
     *      )
     * )
     * 
     * 
     * @param array $segments
     * @return type
     */
    private function GET_apiCollections($segments) {
        
        /*
         * Search/describe in all collections or in a given collection
         */
        $collectionName = isset($segments[3]) ? $segments[2] : null;
        $resource = isset($collectionName) ? new RestoCollection($collectionName, $this->context, $this->user, array('autoload' => true)) : new RestoCollections($this->context, $this->user);
        $action = isset($collectionName) ? $segments[3] : $segments[2];
        
        /*
         * Search
         */
        if ($action === 'search' || $action === 'describe') {
            
            /*
             * Store query
             */
            if ($this->context->storeQuery === true) {
                $this->user->storeQuery($this->context->method, $action, isset($collectionName) ? $collectionName : '*', null, $this->context->query, $this->context->getUrl());
            }

            /*
             * Search or describe
             */
            return $action === 'search' ? $resource->search() : $resource;      
            
        }
        else {
            RestoLogUtil::httpError(404);
        }
        
    }

    /**
     * Process api/user
     * 
     * @param array $segments
     * @return type
     */
    private function GET_apiUser($segments) {
       
        if (isset($segments[3])) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * Generate user from input email 
         */
        if (isset($this->context->query['email'])) {
            $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('email' => $this->context->query['email'])), $this->context);
        }
                
        switch ($segments[2]) {

            /*
             * api/user/activate
             */
            case 'activate':
                return $this->activateUser($user);
                
            /*
             * api/user/connect
             */
            case 'connect':
                return $this->user->connect();

            /*
             * api/user/checkToken
             */
            case 'checkToken':
                if (!isset($this->context->query['_tk'])) {
                    RestoLogUtil::httpError(400);
                }
                return $this->context->checkJWT($this->context->query['_tk']) ? RestoLogUtil::success('Valid token') : RestoLogUtil::error('Invalid token');
                
            /*
             * api/user/resetPassword
             */
            case 'resetPassword':
                if (isset($user)) {
                    return $user->sendResetPasswordLink();
                }
                else {
                    RestoLogUtil::httpError(400); 
                }
            default:
                RestoLogUtil::httpError(404);

        }
        
    }

    /**
     * 
     * Process HTTP GET request on collections
     *  
     * @SWG\Get(
     *      tags={"collections"},
     *      path="/collections.{format}",
     *      summary="Describe",
     *      description="Returns a list of all collection descriptions including license information and collection content statistics (i.e. number of products, etc.)",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="format",
     *          in="path",
     *          description="Output format",
     *          required=true,
     *          type="string",
     *          @SWG\Items(type="string"),
     *          enum={"json"}
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="List of all collection descriptions"
     *      )
     * )
     * 
     * @SWG\Get(
     *      tags={"collection"},
     *      path="/collections/{collectionId}.{format}",
     *      summary="Describe",
     *      description="Returns the {collectionId} collection description including license information and collection content statistics (i.e. number of products, etc.)",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="format",
     *          in="path",
     *          description="Output format",
     *          required=true,
     *          type="string",
     *          @SWG\Items(type="string"),
     *          enum={"json"}
     *      ),
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
     *          description="Describe collection {collectionId}"
     *      ),
     *      @SWG\Response(
     *          response="404",
     *          description="Collection not found"
     *      )
     * )
     * 
     * @param array $segments
     */
    private function GET_collections($segments) {

        if (isset($segments[1])) {
            $collection = new RestoCollection($segments[1], $this->context, $this->user, array('autoload' => true));
        }
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
         * collections
         */
        if (!isset($collection)) {
            return new RestoCollections($this->context, $this->user, array('autoload' => true));
        }

        /*
         * Collection description (XML is not allowed - see api/describe/collections)
         */
        else if (!isset($feature->identifier)) {
            return $collection;
        }

        /*
         * Feature description
         */
        else if (!isset($segments[3])) {
            if ($this->context->storeQuery === true) {
                $this->user->storeQuery($this->context->method, 'resource', $collection->name, $feature->identifier, $this->context->query, $this->context->getUrl());
            }
            return $feature;
        }

        /*
         * Download feature then exit
         */
        else if ($segments[3] === 'download') {
            return $this->downloadFeature($collection, $feature, isset($this->context->query['_tk']) ? $this->context->query['_tk'] : null);
        }
        
        /*
         * Access WMS for feature
         */
        else if ($segments[3] === 'wms') {
            return $this->viewFeature($collection, $feature, isset($this->context->query['_tk']) ? $this->context->query['_tk'] : null);
        }
        
        /*
         * 404
         */
        else {
            RestoLogUtil::httpError(404);
        }
    }

    /**
     * 
     * Process HTTP GET request on users
     * 
     * @param array $segments
     */
    private function GET_user($segments) {
        
        /*
         * user
         */
        if (!isset($segments[1])) {
            return RestoLogUtil::success('Profile for ' . $this->user->profile['email'], array(
                        'profile' => $this->user->profile
            ));
        }

        /*
         * user/groups
         */
        if ($segments[1] === 'groups') {
            if (isset($segments[2])) {
                return RestoLogUtil::httpError(404);
            }
            return RestoLogUtil::success('Groups for ' . $this->user->profile['email'], array(
                        'email' => $this->user->profile['email'],
                        'groups' => $this->user->profile['groups']
            ));
        }

        /*
         * user/rights
         */
        if ($segments[1] === 'rights') {
            return RestoLogUtil::success('Rights for ' . $this->user->profile['email'], array(
                        'email' => $this->user->profile['email'],
                        'userid' => $this->user->profile['userid'],
                        'groups' => $this->user->profile['groups'],
                        'rights' => $this->user->getRights(isset($segments[2]) ? $segments[2] : null, isset($segments[3]) ? $segments[3] : null)
            ));
        }
        
        /*
         * user/cart
         */
        if ($segments[1] === 'cart' && !isset($segments[2])) {
            return $this->user->getCart();
        }
        
        /*
         * user/orders
         */
        if ($segments[1] === 'orders') {
            if (isset($segments[2])) {
                return new RestoOrder($this->user, $this->context, $segments[2]);
            }
            else {
                return RestoLogUtil::success('Orders for user ' . $this->user->profile['email'], array(
                            'email' => $this->user->profile['email'],
                            'userid' => $this->user->profile['userid'],
                            'orders' => $this->user->getOrders()
                ));
            }
        }

        /*
         * user/signatures
         */
        if ($segments[1] === 'signatures' && !isset($segments[2])) {
            return RestoLogUtil::success('Signatures for ' . $this->user->profile['email'], array(
                        'email' => $this->user->profile['email'],
                        'userid' => $this->user->profile['userid'],
                        'groups' => $this->user->profile['groups'],
                        'signatures' => $this->user->getUserSignatures()
            ));
        }
        
        return RestoLogUtil::httpError(404);
    }

    /**
     *
     * Process licenses
     *
     * @param array $segments
     */
    private function GET_licenses($segments) {
        
        if (isset($segments[2])) {
            RestoLogUtil::httpError(404);
        }
        
        return array(
            'licences' => $this->context->dbDriver->get(RestoDatabaseDriver::LICENSES, array('licenseId' => isset($segments[1]) ? $segments[1] : null))
        );
    }
    
    /**
     * Activate user
     * @param RestoUser $user
     */
    private function activateUser($user) {
        if (isset($user) && isset($this->context->query['act'])) {
            if ($user->activate($this->context->query['act'])) {

                /*
                 * Close database handler and redirect to a human readable page...
                 */
                if (isset($this->context->query['redirect'])) {
                    if (isset($this->context->dbDriver)) {
                        $this->context->dbDriver->closeDbh();
                    }
                    header('Location: ' . $this->context->query['redirect']);
                    exit();
                }
                else {
                    RestoLogUtil::success('User activated');
                }
            }
            else {
                RestoLogUtil::error('User not activated');
            }
        }
        else {
            RestoLogUtil::httpError(400);
        }
    }
    
    /**
     * Download feature
     * 
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * @param String $token
     * 
     */
    private function downloadFeature($collection, $feature, $token) {
        
        /*
         * Check user download rights
         */
        $user = $this->checkRights('download', $this->user, $token, $collection, $feature);
        
        /*
         * User must be validated
         */
        if (!$user->isValidated()) {
            RestoLogUtil::httpError(403, 'User profile has not been validated. Please contact an administrator');
        }

        /*
         * User do not fullfill license requirements
         */
        if (!$feature->getLicense()->isApplicableToUser($user)) {
            RestoLogUtil::httpError(403, 'You do not fulfill license requirements');
        }
        
        /*
         * User has to sign the license before downloading
         */
        if ($feature->getLicense()->hasToBeSignedByUser($user)) {
            return array(
                'ErrorMessage' => 'Forbidden',
                'feature' => $feature->identifier,
                'collection' => $collection->name,
                'license' => $feature->getLicense(),
                'userid' => $user->profile['userid'],
                'ErrorCode' => 3002
            );
        }

        /*
         * Rights + fullfill license requirements + license signed = download and exit
         */
        if ($this->context->storeQuery === true) {
            $user->storeQuery($this->context->method, 'download',  $collection->name, $feature->identifier, $this->context->query, $this->context->getUrl());
        }
        $feature->download();
        return null;
        
    }
    
    /**
     * Access WMS for a given feature
     *
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * @param string $token
     * 
     */
    private function viewFeature($collection, $feature, $token) {
        
        /*
         * Check user visualize rights
         */
        $user = $this->checkRights('visualize', $this->user, $token, $collection, $feature);
        
        /*
         * User must be validated
         */
        if (!$user->isValidated()) {
            RestoLogUtil::httpError(403, 'User profile has not been validated. Please contact an administrator');
        }

        /*
         * User do not fullfill license requirements
         * Stream low resolution WMS if viewService is public
         * Forbidden otherwise
         */
        $wmsUtil = new RestoWMSUtil($this->context, $user);
        $license = $feature->getLicense();
        if (!$license->isApplicableToUser($user)) {
            if ($license['viewService'] !== 'public') {
                RestoLogUtil::httpError(403, 'You do not fulfill license requirements');
            }
            else {
                $wmsUtil->streamWMS($feature, true);
            }
        }
        /*
         * Stream full resolution WMS
         */
        else {
            $wmsUtil->streamWMS($feature);
        }
        return null;
    }

    
    /**
     * Check $action rights returning user
     * 
     * @param string $action
     * @param RestoUser $user
     * @param string $token
     * @param RestoCollection $collection
     * @param RestoFeature $feature
     * 
     */
    private function checkRights($action, $user, $token, $collection, $feature) {
        
        /*
         * Get token inititiator - bypass user rights
         */
        if (!empty($token)) {
            $initiatorEmail = $this->context->dbDriver->check(RestoDatabaseDriver::SHARED_LINK, array(
                'resourceUrl' => $this->context->baseUrl . '/' . $this->context->path,
                'token' => $token
            ));
            if ($initiatorEmail && $user->profile['email'] !== $initiatorEmail) {
                $user = new RestoUser($this->context->dbDriver->get(RestoDatabaseDriver::USER_PROFILE, array('email' => strtolower($initiatorEmail))), $this->context);
            }
        }
        else {
            if ($action === 'download' && !$user->hasRightsTo(RestoUser::DOWNLOAD, array('collectionName' => $collection->name, 'featureIdentifier' => $feature->identifier))) {
                RestoLogUtil::httpError(403);
            }
            if ($action === 'visualize' && !$user->hasRightsTo(RestoUser::VISUALIZE, array('collectionName' => $collection->name, 'featureIdentifier' => $feature->identifier))) {
                RestoLogUtil::httpError(403);
            }
        }
        
        return $user;
    }
    
}
