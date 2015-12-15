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
     *          ref="#/parameters/q"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/maxRecords"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/index"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/page"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/lang"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/identifier"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/geometry"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/box"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/name"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/lon"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/lat"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/radius"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/startDate"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/completionDate"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/parentIdentifier"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/platform"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/instrument"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/sensorMode"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/productType"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/processingLevel"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/resolution"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/organisationName"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/orbitNumber"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/cloudCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/snowCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/cultivatedCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/desertCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/floodedCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/forestCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/herbaceousCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/iceCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/urbanCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/waterCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/updated"
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
     *      tags={"collection"},
     *      path="/api/collections/{collectionId}/search.{format}",
     *      summary="Search",
     *      description="Search products within collection {collectionId}",
     *      operationId="searchInCollection",
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
     *          name="collectionId",
     *          in="path",
     *          description="Collection identifier",
     *          required=true,
     *          type="string",
     *          @SWG\Items(type="string")
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/q"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/maxRecords"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/index"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/page"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/lang"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/identifier"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/geometry"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/box"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/name"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/lon"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/lat"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/radius"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/startDate"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/completionDate"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/parentIdentifier"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/platform"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/instrument"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/sensorMode"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/productType"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/processingLevel"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/resolution"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/organisationName"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/orbitNumber"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/cloudCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/snowCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/cultivatedCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/desertCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/floodedCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/forestCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/herbaceousCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/iceCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/urbanCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/waterCover"
     *      ),
     *      @SWG\Parameter(
     *          ref="#/parameters/updated"
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
        } else {
            $user = null;
        }
                
        switch ($segments[2]) {

            /**
             *  @SWG\Get(
             *      tags={"user"},
             *      path="/api/user/activate",
             *      summary="Activate user",
             *      description="Activate registered user",
             *      operationId="activateUser",
             *      produces={"application/json"},
             *      @SWG\Parameter(
             *          name="act",
             *          in="query",
             *          description="Activation token",
             *          required=true,
             *          type="string",
             *          @SWG\Items(type="string")
             *      ),
             *      @SWG\Parameter(
             *          name="redirect",
             *          in="query",
             *          description="Redirect url to the rocket activation status page",
             *          required=true,
             *          type="string",
             *          @SWG\Items(type="string")
             *      ),
             *      @SWG\Response(
             *          response="200",
             *          description="Activation status - user activated or not"
             *      ),
             *      @SWG\Response(
             *          response="400",
             *          description="Bad request"
             *      )
             *  )
             */
            case 'activate':
                return $this->activateUser($user);
                
            /**
             *  @SWG\Get(
             *      tags={"user"},
             *      path="/api/user/connect",
             *      summary="Connect user",
             *      description="Connect user and return user profile encoded within a JWT",
             *      operationId="connectUser",
             *      produces={"application/json"},
             *      @SWG\Response(
             *          response="200",
             *          description="Return user profile encoded within a JWT"
             *      ),
             *      @SWG\Response(
             *          response="403",
             *          description="Forbidden"
             *      )
             *  )
             */
            case 'connect':
                return $this->user->connect();

            /**
             *  @SWG\Get(
             *      tags={"user"},
             *      path="/api/user/checkToken",
             *      summary="Check security token",
             *      description="Check if security token associated to user is valid. Usually security token is used to temporarely replace authentication to download/visualize ressources",
             *      operationId="checkToken",
             *      produces={"application/json"},
             *      @SWG\Parameter(
             *          name="_tk",
             *          in="query",
             *          description="Security token",
             *          required=true,
             *          type="string",
             *          @SWG\Items(type="string")
             *      ),
             *      @SWG\Response(
             *          response="200",
             *          description="Token validity - valid or invalid"
             *      ),
             *      @SWG\Response(
             *          response="400",
             *          description="Bad request"
             *      )
             *  )
             */
            case 'checkToken':
                if (!isset($this->context->query['_tk'])) {
                    RestoLogUtil::httpError(400);
                }
                return $this->context->checkJWT($this->context->query['_tk']) ? RestoLogUtil::success('Valid token') : RestoLogUtil::error('Invalid token');
                
            /**
             *  @SWG\Get(
             *      tags={"user"},
             *      path="/api/user/resetPassword",
             *      summary="Send reset password link",
             *      description="Send reset password link to the user email adress",
             *      operationId="resetPassword",
             *      produces={"application/json"},
             *      @SWG\Response(
             *          response="200",
             *          description="Acknowledgment of email notification"
             *      ),
             *      @SWG\Response(
             *          response="400",
             *          description="Bad request"
             *      )
             *  )
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
        
        /**
         * Collection descriptions
         * 
         *  @SWG\Get(
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
         *  )
         * 
         */
        if (!isset($collection)) {
            return new RestoCollections($this->context, $this->user, array('autoload' => true));
        }

        /**
         * Collection description (XML is not allowed - see api/describe/collections)
         * 
         *  @SWG\Get(
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
         *  )
         * 
         */
        else if (!isset($feature->identifier)) {
            return $collection;
        }

        /**
         * Feature description
         * 
         *  @SWG\Get(
         *      tags={"feature"},
         *      path="/collections/{collectionId}/{featureId}.{format}",
         *      summary="Get feature",
         *      description="Returns feature {featureId} metadata",
         *      produces={"application/json", "application/atom+xml"},
         *      security={
         *          {
         *             "localAuthentication": {"read:feature"}
         *          }
         *      },
         *      @SWG\Parameter(
         *          name="format",
         *          in="path",
         *          description="Output format",
         *          required=true,
         *          type="string",
         *          @SWG\Items(type="string"),
         *          enum={"json", "atom"}
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
         *          description="Feature metadata"
         *      ),
         *      @SWG\Response(
         *          response="404",
         *          description="Collection not found"
         *      )
         *  )
         *  
         */
        else if (!isset($segments[3])) {
            if ($this->context->storeQuery === true) {
                $this->user->storeQuery($this->context->method, 'resource', $collection->name, $feature->identifier, $this->context->query, $this->context->getUrl());
            }
            return $feature;
        }
        
        /**
         * Download feature and exit
         * 
         *  @SWG\Get(
         *      tags={"feature"},
         *      path="/collections/{collectionId}/{featureId}/download",
         *      summary="Download feature",
         *      description="Download feature attached resource i.e. usually the eo product as a zip file or an image file (e.g. TIF)",
         *      produces={"application/octet-stream"},
         *      security={
         *          {
         *             "localAuthentication": {"download:resource"}
         *          }
         *      },
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
         *          description="Feature identifier (i.e. resto UUID)",
         *          required=true,
         *          type="string",
         *          @SWG\Items(type="string")
         *      ),
         *      @SWG\Parameter(
         *          name="_tk",
         *          in="query",
         *          description="Security token",
         *          required=false,
         *          type="string",
         *          @SWG\Items(type="string")
         *      ),
         *      @SWG\Response(
         *          response="200",
         *          description="Resource stream"
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
         *  
         */
        else if ($segments[3] === 'download') {
            return $this->downloadFeature($collection, $feature, isset($this->context->query['_tk']) ? $this->context->query['_tk'] : null);
        }
        
        /**
         * View feature as a WMS stream
         * 
         *  @SWG\Get(
         *      tags={"feature"},
         *      path="/collections/{collectionId}/{featureId}/wms",
         *      summary="View full resolution product",
         *      description="View feature attached resource (i.e. usually the eo product) in full resolution through a WMS stream",
         *      produces={"application/octet-stream"},
         *      security={
         *          {
         *             "localAuthentication": {"view:resource"}
         *          }
         *      },
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
         *          description="Feature identifier (i.e. resto UUID)",
         *          required=true,
         *          type="string",
         *          @SWG\Items(type="string")
         *      ),
         *      @SWG\Parameter(
         *          name="_tk",
         *          in="query",
         *          description="Security token",
         *          required=false,
         *          type="string",
         *          @SWG\Items(type="string")
         *      ),
         *      @SWG\Response(
         *          response="200",
         *          description="Resource stream"
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
         *  
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
        
        /**
         *  @SWG\Get(
         *      tags={"user"},
         *      path="/user",
         *      summary="User profile",
         *      description="Returns user profile",
         *      operationId="getUserProfile",
         *      produces={"application/json"},
         *      security={
         *          {
         *             "localAuthentication": {"read:profile"}
         *          }
         *      },
         *      @SWG\Response(
         *          response="200",
         *          description="Return user profile"
         *      ),
         *      @SWG\Response(
         *          response="403",
         *          description="Forbidden"
         *      )
         *  )
         */
        if (!isset($segments[1])) {
            return RestoLogUtil::success('Profile for ' . $this->user->profile['email'], array(
                        'profile' => $this->user->profile
            ));
        }

        /**
         *  @SWG\Get(
         *      tags={"user"},
         *      path="/user/groups",
         *      summary="User groups",
         *      description="Returns user groups list",
         *      operationId="getUserGroups",
         *      produces={"application/json"},
         *      security={
         *          {
         *             "localAuthentication": {"read:groups"}
         *          }
         *      },
         *      @SWG\Response(
         *          response="200",
         *          description="Return user groups list"
         *      ),
         *      @SWG\Response(
         *          response="403",
         *          description="Forbidden"
         *      )
         *  )
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

        /**
         *  @SWG\Get(
         *      tags={"user"},
         *      path="/user/rights",
         *      summary="User rights",
         *      description="Returns user rights",
         *      operationId="getUserRights",
         *      produces={"application/json"},
         *      security={
         *          {
         *             "localAuthentication": {"read:rights"}
         *          }
         *      },
         *      @SWG\Response(
         *          response="200",
         *          description="Return user rights"
         *      ),
         *      @SWG\Response(
         *          response="403",
         *          description="Forbidden"
         *      )
         *  )
         */
        if ($segments[1] === 'rights') {
            return RestoLogUtil::success('Rights for ' . $this->user->profile['email'], array(
                        'email' => $this->user->profile['email'],
                        'userid' => $this->user->profile['userid'],
                        'groups' => $this->user->profile['groups'],
                        'rights' => $this->user->getRights(isset($segments[2]) ? $segments[2] : null, isset($segments[3]) ? $segments[3] : null)
            ));
        }
        
        /**
         *  @SWG\Get(
         *      tags={"user"},
         *      path="/user/cart",
         *      summary="User cart",
         *      description="Returns user cart",
         *      operationId="getUserCart",
         *      produces={"application/json"},
         *      security={
         *          {
         *             "localAuthentication": {"read:cart"}
         *          }
         *      },
         *      @SWG\Response(
         *          response="200",
         *          description="Return user cart"
         *      ),
         *      @SWG\Response(
         *          response="403",
         *          description="Forbidden"
         *      )
         *  )
         */
        if ($segments[1] === 'cart' && !isset($segments[2])) {
            return $this->user->getCart();
        }
        
        /**
         *  @SWG\Get(
         *      tags={"user"},
         *      path="/user/orders",
         *      summary="User orders",
         *      description="Returns user orders",
         *      operationId="getUserOrders",
         *      produces={"application/json"},
         *      security={
         *          {
         *             "localAuthentication": {"read:orders"}
         *          }
         *      },
         *      @SWG\Response(
         *          response="200",
         *          description="Return user orders"
         *      ),
         *      @SWG\Response(
         *          response="403",
         *          description="Forbidden"
         *      )
         *  )
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

        /**
         *  @SWG\Get(
         *      tags={"user"},
         *      path="/user/signatures",
         *      summary="User signatures",
         *      description="Returns user license signatures (i.e. on feature and/or on collection)",
         *      operationId="getSignatures",
         *      produces={"application/json"},
         *      security={
         *          {
         *             "localAuthentication": {"read:signatures"}
         *          }
         *      },
         *      @SWG\Response(
         *          response="200",
         *          description="Return user signatures"
         *      ),
         *      @SWG\Response(
         *          response="403",
         *          description="Forbidden"
         *      )
         *  )
         */
        if ($segments[1] === 'signatures' && !isset($segments[2])) {
            return RestoLogUtil::success('Signatures for ' . $this->user->profile['email'], array(
                        'email' => $this->user->profile['email'],
                        'userid' => $this->user->profile['userid'],
                        'groups' => $this->user->profile['groups'],
                        'signatures' => $this->user->getSignatures()
            ));
        }
        
        return RestoLogUtil::httpError(404);
    }

    /**
     *
     * Process licenses
     * 
     * @SWG\Get(
     *      tags={"license"},
     *      path="/licenses/{licenseId}",
     *      summary="Get license description",
     *      description="Returns license(s) description(s)",
     *      operationId="getLicenses",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="licenseId",
     *          in="path",
     *          description="License identifier",
     *          required=false,
     *          type="string",
     *          @SWG\Items(type="string")
     *      ),      
     *      @SWG\Response(
     *          response="200",
     *          description="License(s) description(s)"
     *      )
     * )
     *
     * @param array $segments
     */
    private function GET_licenses($segments) {
        
        if (isset($segments[2])) {
            RestoLogUtil::httpError(404);
        }
        
        return array(
            'licenses' => $this->context->dbDriver->get(RestoDatabaseDriver::LICENSES, array('licenseId' => isset($segments[1]) ? $segments[1] : null))
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
                'license' => $feature->getLicense()->toArray(),
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
         * User do not fullfill license requirements
         * Stream low resolution WMS if viewService is public
         * Forbidden otherwise
         */
        $wmsUtil = new RestoWMSUtil($this->context, $user);
        $license = $feature->getLicense();
        if (!$license->isApplicableToUser($user)) {
            /*
             * Check if viewService is public
             */
            $t_license_arr = $license->toArray();
            if ($t_license_arr['viewService'] !== 'public') {
                /*
                 * viewService isn't public
                 */
                RestoLogUtil::httpError(403, 'You do not fulfill license requirements');
            }
            else {
                /*
                 * viewService is public, Stream low resolution WMS
                 */
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
            
            /*
             * Non existing Token => exit
             */
            if (!$initiatorEmail) {
                RestoLogUtil::httpError(403);
            }
            
            if ($user->profile['email'] !== $initiatorEmail) {
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
