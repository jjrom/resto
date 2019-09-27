<?php
/*
 * Copyright 2018 Jérôme Gasperi
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
 * Collections API
 */
class CollectionsAPI
{
    private $context;
    private $user;

    /**
     * Constructor
     */
    public function __construct($context, $user)
    {
        $this->context = $context;
        $this->user = $user;
    }

    /**
     * Return collections descriptions
     *
     *  @OA\Get(
     *      path="/collections",
     *      summary="Get collections",
     *      description="Returns a list of all collection descriptions including statistics (i.e. number of products, etc.)",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *         name="_stats",
     *         in="query",
     *         required=false,
     *         description="Set to get individual statistics for all collection",
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="List of all collection descriptions",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="osDescription",
     *                  ref="#/components/schemas/OpenSearchDescription"
     *              ),
     *              @OA\Property(
     *                  property="statistics",
     *                  ref="#/components/schemas/Statistics"
     *              ),
     *              @OA\Property(
     *                  property="collections",
     *                  description="List of available collections",
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/OutputCollection"
     *                  )
     *              ),
     *              example={
     *                  "osDescription": {
     *                      "ShortName": "Datasources",
     *                      "LongName": "Datasources search service",
     *                      "Description": "Search on all datasources (i.e. S2)",
     *                      "Tags": "snapplanet",
     *                      "Developer": "SnapPlanet team",
     *                      "Contact": "jrom@snapplanet.io",
     *                      "Query": "europe 2016",
     *                      "Attribution": "SnapPlanet. Copyright 2016, All Rights Reserved"
     *                  },
     *                  "statistics": {
     *                      "count": 5322692,
     *                      "facets": {
     *                          "collection": {
     *                              "Example": 5074851
     *                          },
     *                          "continent": {
     *                              "Africa": 671538,
     *                              "Antarctica": 106337,
     *                              "Asia": 747836,
     *                              "Europe": 1992742,
     *                              "North America": 1012027,
     *                              "Oceania": 218789,
     *                              "Seven seas (open ocean)": 9481,
     *                              "South America": 313983
     *                          },
     *                          "instrument": {
     *                              "HRS": 2,
     *                              "MSI": 5322690
     *                          },
     *                          "platform": {
     *                              "S2A": 3346304,
     *                              "S2B": 1976386,
     *                              "SPOT6": 1
     *                          },
     *                          "processingLevel": {
     *                              "LEVEL1C": 5322690
     *                          },
     *                          "productType": {
     *                              "PX": 2,
     *                              "REFLECTANCE": 5322690
     *                          }
     *                      }
     *                  },
     *                  "collections": {
     *                      {
     *                          "name": "S2",
     *                          "visibility": "public",
     *                          "owner": "1919680409029837825",
     *                          "model": "OpticalModel",
     *                          "licenseId": "proprietary",
     *                          "osDescription": {
     *                              "ShortName": "S2",
     *                              "LongName": "Sentinel-2",
     *                              "Description": "Sentinel-2 tiles",
     *                              "Tags": "s2 sentinel2",
     *                              "Developer": "Jérôme Gasperi",
     *                              "Contact": "jerome.gasperi@gmail.com",
     *                              "Query": "Toulouse",
     *                              "Attribution": "Copyright 2019, All Rights Reserved"
     *                          }
     *                      }
     *                  }
     *              }
     *          )
     *      )
     *  )
     *
     */
    public function getCollections($params)
    {   
        return (new RestoCollections($this->context, $this->user))->load();
    }

    /**
     * Return collection description
     *
     *  @OA\Get(
     *      path="/collections/{collectionName}",
     *      summary="Get collection",
     *      description="Returns collection description including statistics (i.e. number of products, etc.)",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *         name="collectionName",
     *         in="path",
     *         required=true,
     *         description="Collection name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="_stats",
     *         in="query",
     *         description="True to return full statistics in summaries property. Default is *false*",
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Collection description",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/OutputCollection",
     *              example={
     *                  "name": "S2",
     *                  "model": "OpticalModel",
     *                  "licenseId": "proprietary",
     *                  "osDescription": {
     *                      "ShortName": "S2",
     *                      "LongName": "Sentinel-2",
     *                      "Description": "Sentinel-2 tiles",
     *                      "Tags": "s2 sentinel2",
     *                      "Developer": "Jérôme Gasperi",
     *                      "Contact": "jerome.gasperi@@gmail.com",
     *                      "Query": "Toulouse",
     *                      "Attribution": "Copyright 2019, All Rights Reserved"
     *                  },
     *                  "owner": "1359450309943886849",
     *                  "visibility": 1,
     *                  "statistics": {
     *                      "count": 5322724,
     *                      "facets": {
     *                          "continent": {
     *                              "Africa": 671538,
     *                              "Antarctica": 106337,
     *                              "Asia": 747847,
     *                              "Europe": 1992756,
     *                              "North America": 1012027,
     *                              "Oceania": 218789,
     *                              "Seven seas (open ocean)": 9481,
     *                              "South America": 313983
     *                          },
     *                          "instrument": {
     *                              "HRS": 2,
     *                              "MSI": 5322722
     *                          },
     *                          "platform": {
     *                              "S2A": 3346319,
     *                              "S2B": 1976403,
     *                              "SPOT6": 1
     *                          },
     *                          "processingLevel": {
     *                              "LEVEL1C": 5322722
     *                          },
     *                          "productType": {
     *                              "PX": 2,
     *                              "REFLECTANCE": 5322722
     *                          }
     *                      }
     *                  }
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Collection not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      )
     *  )
     *
     * @param array params
     */
    public function getCollection($params)
    {
        return (new RestoCollection($params['collectionName'], $this->context, $this->user))->load();
    }

    /**
     *
     * Create new collection
     *
     * @OA\Post(
     *      path="/collections",
     *      summary="Create collection",
     *      tags={"Collection"},
     *      @OA\Response(
     *          response="200",
     *          description="The collection is created",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Message information"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Collection Example created"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Missing mandatory collection name or collection already exist",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Only user with *create* rights can create a collection",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      @OA\RequestBody(
     *         description="Collection description",
     *         @OA\JsonContent(
     *              required={"name", "model", "osDescription"},
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="Collection name must be an alphanumeric string containing [a-zA-Z0-9] and not starting with a digit. It is used as the collection identifier"
     *              ),
     *              @OA\Property(
     *                  property="visibility",
     *                  description="Visibility of this collection. Collections with visibility 1 are visible to all users."
     *              ),
     *              @OA\Property(
     *                  property="model",
     *                  type="string",
     *                  description="[For developper] Name of the collection model class under $SRC/include/resto/Models."
     *              ),
     *              @OA\Property(
     *                  property="licenseId",
     *                  type="string",
     *                  description="License for this collection as a SPDX License identifier. Alternatively, use proprietary if the license is not on the SPDX license list or various if multiple licenses apply. In these two cases links to the license texts SHOULD be added, see the license link relation type."
     *              ),
     *              @OA\Property(
     *                  property="rights",
     *                  type="object",
     *                  description="Default collection rights settings",
     *                  @OA\Property(
     *                      property="download",
     *                      type="enum",
     *                      enum={0,1},
     *                      description="Feature download rights (1 can be downloaded; 0 cannot be downloaded)"
     *                  ),
     *                  @OA\Property(
     *                      property="visualize",
     *                      type="integer",
     *                      description="Features visualization rights (1 can be visualized; 0 cannot be visualized)"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="osDescription",
     *                  type="object",
     *                  required={"en"},
     *                  @OA\Property(
     *                      property="en",
     *                      description="OpenSearch description in English",
     *                      ref="#/components/schemas/OpenSearchDescription"
     *                  ),
     *                  @OA\Property(
     *                      property="fr",
     *                      description="OpenSearch description in French",
     *                      ref="#/components/schemas/OpenSearchDescription"
     *                  )
     *              ),
     *              example={
     *                  "name": "Example",
     *                  "model": "SatelliteModel",
     *                  "visibility": 1,
     *                  "licenseId": "proprietary",
     *                  "rights":{
     *                      "download":0,
     *                      "visualize":1
     *                  },
     *                  "osDescription": {
     *                      "en": {
     *                          "ShortName": "resto collection",
     *                          "LongName": "A dummy resto collection example",
     *                          "Description": "A dummy resto collection example",
     *                          "Tags": "resto example",
     *                          "Developer": "John Doe",
     *                          "Contact": "john.doe@dev.null",
     *                          "Query": "Toulouse",
     *                          "Attribution": "Copyright 2019, All Rights Reserved"
     *                      },
     *                      "fr": {
     *                          "ShortName": "Collection resto",
     *                          "LongName": "Un exemple de collection resto",
     *                          "Description": "Un exemple de collection resto",
     *                          "Developer": "John Doe",
     *                          "Contact": "john.doe@dev.null",
     *                          "Query": "SPOT6",
     *                          "Attribution": "Copyright 2019"
     *                      }
     *                  },
     *                  "propertiesMapping":{
     *
     *                  }
     *              }
     *          )
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     * )
     *
     * @param array $params
     * @param array $body
     *
     */
    public function createCollection($params, $body)
    {
        
        /*
         * Only a user with 'create' rights can POST a collection
         */
        if (!$this->user->hasRightsTo(RestoUser::CREATE)) {
            RestoLogUtil::httpError(403);
        }

        (new RestoCollections($this->context, $this->user))->create($body);
        
        return RestoLogUtil::success('Collection ' . $body['name'] . ' created');
    }

    /**
     * Update collection
     *
     * @OA\Put(
     *      path="/collections/{collectionName}",
     *      summary="Update collection",
     *      description="Note that *collectionName* and *model* properties cannot be updated",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *         name="collectionName",
     *         in="path",
     *         required=true,
     *         description="Collection name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The collection is created",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Message information"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Collection Example updated"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Missing mandatory collection name",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Only user with *update* rights can update a collection",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Collection not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      @OA\RequestBody(
     *         description="Collection description",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="visibility",
     *                  description="Visibility of this collection. Collections with visibility 1 are visible to all users."
     *              ),
     *              @OA\Property(
     *                  property="licenseId",
     *                  type="string",
     *                  description="License for this collectionas a SPDX License identifier or expression. Alternatively, use proprietary if the license is not on the SPDX license list or various if multiple licenses apply. In these two cases links to the license texts SHOULD be added, see the license link relation type."
     *              ),
     *              @OA\Property(
     *                  property="rights",
     *                  type="object",
     *                  description="Default collection rights settings",
     *                  @OA\Property(
     *                      property="download",
     *                      type="enum",
     *                      enum={0,1},
     *                      description="Feature download rights (1 can be downloaded; 0 cannot be downloaded)"
     *                  ),
     *                  @OA\Property(
     *                      property="visualize",
     *                      type="integer",
     *                      description="Features visualization rights (1 can be visualized; 0 cannot be visualized)"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="osDescription",
     *                  type="object",
     *                  required={"en"},
     *                  @OA\Property(
     *                      property="en",
     *                      description="OpenSearch description in English",
     *                      ref="#/components/schemas/OpenSearchDescription"
     *                  ),
     *                  @OA\Property(
     *                      property="fr",
     *                      description="OpenSearch description in French",
     *                      ref="#/components/schemas/OpenSearchDescription"
     *                  )
     *              ),
     *              example={
     *                  "osDescription": {
     *                      "en": {
     *                          "ShortName": "resto collection",
     *                          "LongName": "An updated dummy resto collection example",
     *                          "Description": "A dummy resto collection example",
     *                          "Tags": "resto example",
     *                          "Developer": "John Doe",
     *                          "Contact": "john.doe@dev.null",
     *                          "Query": "SPOT6",
     *                          "Attribution": "Copyright 2019, All Rights Reserved"
     *                      }
     *                  },
     *                  "propertiesMapping":{
     *
     *                  }
     *              }
     *          )
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     * )
     *
     * @param array $params
     * @param array $body
     */
    public function updateCollection($params, $body)
    {
        $collection = new RestoCollection($params['collectionName'], $this->context, $this->user);
        $collection->load();
        
        /*
         * Only owner of the collection can update it
         */
        if (! $this->user->hasRightsTo(RestoUser::UPDATE, array('collection' => $collection))) {
            RestoLogUtil::httpError(403);
        }

        /*
         * Update collection and store to database
         */
        $collection->update($body)->store();

        return RestoLogUtil::success('Collection ' . $collection->name . ' updated');
    }

    /**
     * Delete collection
     *
     * @OA\Delete(
     *      path="/collections/{collectionName}",
     *      summary="Delete collection",
     *      description="For security reason, only empty collection can be deleted",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *         name="collectionName",
     *         in="path",
     *         required=true,
     *         description="Collection name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The collection is delete",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Message information"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Collection Example deleted"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Missing mandatory collection name",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Only user with *update* rights can delete a collection",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Collection not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     * )
     *
     * @param array $params
     *
     */
    public function deleteCollection($params)
    {
        $collection = new RestoCollection($params['collectionName'], $this->context, $this->user);
        $collection->load();

        /*
         * Only owner of a collection can delete it
         */
        if (!$this->user->hasRightsTo(RestoUser::UPDATE, array('collection' => $collection))) {
            RestoLogUtil::httpError(403);
        }

        $collection->removeFromStore();

        return RestoLogUtil::success('Collection ' . $collection->name . ' deleted');
    }

    /**
     * Add a feature to collection
     *
     *  @OA\Post(
     *      path="/collections/{collectionName}/items",
     *      summary="Add feature to collection",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *         name="collectionName",
     *         in="path",
     *         required=true,
     *         description="Collection name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="tolerance",
     *         in="query",
     *         required=false,
     *         description="Simplify input geometry with tolerance in degrees (use in conjunction with *maxpoints*",
     *         @OA\Schema(
     *             type="float"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="maxpoints",
     *         in="query",
     *         required=false,
     *         description="If tolerance is set, geometry simplification of input geometry is performed only if the number of geometry vertices is greater than *maxpoints*",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="_useItag",
     *         in="query",
     *         required=false,
     *         description="[ADDON][Tag] Set to false to not use iTag during feature insertion. By default, iTag is triggered unless the collection is within the Tag add-on *excludedCollections* array option or *_useItag* is set to false. If force to true, then iTag is triggered even if collection is one of the *excludedCollections*",
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *      ),
     *      @OA\RequestBody(
     *         description="Feature description",
     *         @OA\JsonContent(ref="#/components/schemas/InputFeature")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Feature is inserted within collection",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Message information"
     *              ),
     *              @OA\Property(
     *                  property="collection",
     *                  type="string",
     *                  description="Collection name in which feature is inserted"
     *              ),
     *              @OA\Property(
     *                  property="featureId",
     *                  type="string",
     *                  description="Newly created feature identifier"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Feature inserted",
     *                  "collection": "Example",
     *                  "featureId": "c4f6ed9f-35ba-5c85-8449-e437c14ae428"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Invalid feature description",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Only user with *update* rights can add feature to collection",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      @OA\Response(
     *          response="409",
     *          description="Feature is already present in database",
     *          @OA\JsonContent(ref="#/components/schemas/ConflictError")
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Collection not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *  )
     *
     *
     * @param array $params
     * @param array $body
     */
    public function insertFeature($params, $body)
    {

        /*
         * Load collection
         */
        $collection = new RestoCollection($params['collectionName'], $this->context, $this->user);
        $collection->load();
        
        /*
         * Only a user with 'update' rights on collection can POST feature
         */
        if (!$this->user->hasRightsTo(RestoUser::UPDATE, array('collection' => $collection))) {
            RestoLogUtil::httpError(403);
        }

        /*
         * Insert feature within database
         */
        $result = $collection->addFeature($body, array(
            'tolerance' => isset($params['tolerance']) && is_numeric($params['tolerance']) ? (float) $params['tolerance'] : null,
            'maxpoints' => isset($params['maxpoints']) && ctype_digit($params['maxpoints']) ? (integer) $params['maxpoints'] : null
        ));

        /*
         * This should not happen
         */
        if ($result === false) {
            return RestoLogUtil::httpError(500, 'Cannot insert feature in database');
        }

        return RestoLogUtil::success('Feature inserted', array(
            'collection' => $collection->name,
            'featureId' => $result['id'],
            'productIdentifier' => $result['productIdentifier'],
            'facetsStored' => $result['facetsStored']
        ));
    }
}
