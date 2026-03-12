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
     *         name="ck",
     *         in="query",
     *         style="form",
     *         required=false,
     *         description="Stands for *collection keyword* - limit results to collection containing the input keyword",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="List of all collections",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="extent",
     *                  type="object",
     *                  ref="#/components/schemas/Extent"
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
     *                  "extent": {
     *                      "spatial": {
     *                          "bbox": {
     *                              {
     *                                  -180,
     *                                  -77.28054,
     *                                  180,
     *                                  82.77201
     *                              }
     *                          },
     *                          "crs": "http://www.opengis.net/def/crs/OGC/1.3/CRS84"
     *                      },
     *                      "temporal": {
     *                          "interval": {
     *                              {
     *                                  "2018-09-13T05:58:08.367000Z",
     *                                  "2019-06-11T16:11:41.808000Z"
     *                              }
     *                          },
     *                          "trs": "http://www.opengis.net/def/uom/ISO-8601/0/Gregorian"
     *                      }
     *                  },
     *                  "collections": {
     *                      {
     *                          "id": "L8",
     *                          "title": "Landsat-8",
     *                          "description": "Landsat represents the world's longest continuously acquired collection of space-based moderate-resolution land remote sensing data. Four decades of imagery provides a unique resource for those who work in agriculture, geology, forestry, regional planning, education, mapping, and global change research. Landsat images are also invaluable for emergency response and disaster relief",
     *                          "keywords": {
     *                              "landsat",
     *                              "level1C",
     *                              "USGS"
     *                          },
     *                          "license": "other",
     *                          "extent": {
     *                              "spatial": {
     *                                  "bbox": {
     *                                      {
     *                                          -180,
     *                                          -77.28054,
     *                                          180,
     *                                          82.77201
     *                                      }
     *                                  },
     *                                  "crs": "http://www.opengis.net/def/crs/OGC/1.3/CRS84"
     *                              },
     *                              "temporal": {
     *                                  "interval": {
     *                                      {
     *                                          "2019-05-19T13:59:47.695508Z",
     *                                          "2019-06-06T13:28:04.338517Z"
     *                                      }
     *                                  },
     *                                  "trs": "http://www.opengis.net/def/uom/ISO-8601/0/Gregorian"
     *                              }
     *                          },
     *                          "links": {
     *                              {
     *                                  "rel": "self",
     *                                  "type": "application/json",
     *                                  "href": "http://127.0.0.1:5252/collections.json?&_pretty=1"
     *                              },
     *                              {
     *                                  "rel": "root",
     *                                  "type": "application/json",
     *                                  "href": "http://127.0.0.1:5252"
     *                              }
     *                          },
     *                          "resto:info": {
     *                              "model": "OpticalModel",
     *                              "lineage": {
     *                                  "DefaultModel",
     *                                  "LandCoverModel",
     *                                  "SatelliteModel",
     *                                  "OpticalModel"
     *                              },
     *                              "owner": "203883411255198721"
     *                          },
     *                          "summaries": {
     *                              "datetime": {
     *                                  "minimum": "2019-05-19T13:59:47.695508Z",
     *                                  "maximum": "2019-06-06T13:28:04.338517Z"
     *                              },
     *                              "eo:instrument": {
     *                                  "OLI_TIRS",
     *                                  "TIRS"
     *                              },
     *                              "eo:platform": {
     *                                  "LANDSAT_8"
     *                              },
     *                              "processingLevel": {
     *                                  "LEVEL1C"
     *                              },
     *                              "productType": {
     *                                  "L1GT",
     *                                  "L1TP"
     *                              },
     *                              "sensorType": {
     *                                  "OPTICAL"
     *                              }
     *                          },
     *                          "stac_version": "1.0.0",
     *                          "stac_extensions": {
     *                              "https://stac-extensions.github.io/eo/v1.0.0/schema.json"
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
        return $this->context->keeper->getRestoCollections($this->user)->load($params);
    }

    /**
     * Return collection description
     *
     *  @OA\Get(
     *      path="/collections/{collectionId}",
     *      summary="Get collection",
     *      description="Returns collection including statistics (i.e. number of products, etc.)",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *         name="collectionId",
     *         in="path",
     *         required=true,
     *         description="Collection identifier",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Collection",
     *          @OA\JsonContent(ref="#/components/schemas/OutputCollection")
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
        return $this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load();
    }

    /**
     *
     * Create new collection
     *
     * @OA\Post(
     *      path="/collections",
     *      summary="Create collection",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *         name="model",
     *         in="query",
     *         description="Set the model for the collection (e.g. *OpticalModel*). This superseed the *model* property from the input collection description",
     *         required=false,
     *         style="form",
     *         @OA\Schema(
     *              type="string"
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
     *                  "message": "Collection S2 created"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Missing mandatory collection id or collection already exist",
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
     *         @OA\JsonContent(ref="#/components/schemas/InputCollection")
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
        if ($this->user->hasRightsTo(RestoUser::CREATE_COLLECTION)) {
            $this->context->keeper->getRestoCollections($this->user)->create($body, $params['model'] ?? null);
            return RestoLogUtil::success('Collection ' . $body['id'] . ' created');
        } elseif (empty($body['visibility'])) {
            RestoLogUtil::httpError(403);
        }

        foreach ($body['visibility'] as $group) {
            $canCreateInGroup = $this->user->hasRightsTo(RestoGroup::createCollectionRight($group));
            if (!$canCreateInGroup) {
                RestoLogUtil::httpError(403, 'Insufficient rights to create a collection in group ' . $group);
            }
        }
        $this->context->keeper->getRestoCollections($this->user)->create($body, $params['model'] ?? null);
        return RestoLogUtil::success('Collection ' . $body['id'] . ' created');
    }

    /**
     * Update collection
     *
     * @OA\Put(
     *      path="/collections/{collectionId}",
     *      summary="Update collection",
     *      description="Note that *collectionId* and *model* properties cannot be updated",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *         name="collectionId",
     *         in="path",
     *         required=true,
     *         description="Collection identifier",
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
     *                  "message": "Collection S2 updated"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Missing mandatory collection id",
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
     *         @OA\JsonContent(ref="#/components/schemas/InputCollection")
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
        $collection = $this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load();
        if ($this->user->hasRightsTo(RestoUser::UPDATE_COLLECTION, array('collection' => $collection))) {
            $collection->update($body);
            return RestoLogUtil::success('Collection ' . $collection->id . ' updated');
        } elseif (isset($body['visibility'])) {
            RestoLogUtil::httpError(403, 'Forbidden to update collection visibility');
        }
        if (empty($collection->visibility)) {
            RestoLogUtil::httpError(403);
        }
        $groups = (new GroupsFunctions($this->context->dbDriver))->getGroups(array('in' => $collection->visibility));


        foreach ($groups as $group) {
            $canUpdateInGroup = $this->user->hasRightsTo(RestoGroup::updateCollectionRight($group['name']));
            if ($canUpdateInGroup) {
                $collection->update($body);
                return RestoLogUtil::success('Collection ' . $collection->id . ' updated');
            }
        }
        RestoLogUtil::httpError(403, 'Insufficient rights to update a collection');
    }

    /**
     * Delete collection
     *
     * @OA\Delete(
     *      path="/collections/{collectionId}",
     *      summary="Delete collection",
     *      description="For security reason, only empty collection can be deleted",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *         name="collectionId",
     *         in="path",
     *         required=true,
     *         description="Collection identifier",
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
     *                  "message": "Collection S2 deleted"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Missing mandatory collection id",
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
        $collection = $this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load();

        if ($this->user->hasRightsTo(RestoUser::DELETE_COLLECTION, array('collection' => $collection))) {
            (new CollectionsFunctions($this->context->dbDriver))->removeCollection($collection, $this->context->core['baseUrl']);

            return RestoLogUtil::success('Collection ' . $collection->id . ' deleted');
        }
        if (empty($collection->visibility)) {
            RestoLogUtil::httpError(403, 'No visibility');
        }
        $groups = (new GroupsFunctions($this->context->dbDriver))->getGroups(array('in' => $collection->visibility));

        foreach ($groups as $group) {
            $canDeleteInGroup = $this->user->hasRightsTo(RestoGroup::deleteItemRight($group['name']));
            if ($canDeleteInGroup) {
                (new CollectionsFunctions($this->context->dbDriver))->removeCollection($collection, $this->context->core['baseUrl']);
                return RestoLogUtil::success('Collection ' . $collection->id . ' deleted');
            }
        }
        RestoLogUtil::httpError(403, 'Insufficient rights to delete a collection');
    }

    /**
     * Add feature(s) to collection
     *
     *  @OA\Post(
     *      path="/collections/{collectionId}/items",
     *      summary="Add feature(s) to collection",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *         name="collectionId",
     *         in="path",
     *         required=true,
     *         description="Collection identifier",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="tolerance",
     *         in="query",
     *         style="form",
     *         required=false,
     *         description="Simplify input geometry with tolerance in degrees (use in conjunction with *maxpoints*). [IMPORTANT] Simplification only affects the internal indexed geometry used by the search engine. The original geometry is stored unmodified.",
     *         @OA\Schema(
     *             type="number"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="maxpoints",
     *         in="query",
     *         style="form",
     *         required=false,
     *         description="If tolerance is set, geometry simplification of input geometry is performed only if the number of geometry vertices is greater than *maxpoints*",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="_splitGeom",
     *         in="query",
     *         style="form",
     *         required=false,
     *         description="Superseed the SPLIT_GEOMETRY_ON_DATELINE configuration i.e. set to true to split geometry during feature insertion - false otherwise. Default is set to SPLIT_GEOMETRY_ON_DATELINE",
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="_useItag",
     *         in="query",
     *         style="form",
     *         required=false,
     *         description="[ADDON][Tag] Set to false to not use iTag during feature insertion. Default is true",
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *      ),
     *      @OA\RequestBody(
     *         description="Either a GeoJSON Feature or a GeoJSON FeatureCollection",
     *         @OA\JsonContent(
     *              oneOf={
     *                  @OA\Schema(ref="#/components/schemas/InputFeatureCollection"),
     *                  @OA\Schema(ref="#/components/schemas/InputFeature")
     *              }
     *         )
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
     *                  description="Collection identifier in which feature is inserted"
     *              ),
     *              @OA\Property(
     *                  property="featureId",
     *                  type="string",
     *                  description="Newly created feature identifier"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Feature inserted",
     *                  "collection": "S2",
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
    public function insertFeatures($params, $body)
    {
        /*
         * Load collection
         */
        $collection = $this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load();
        if (!$this->user->hasRightsTo(RestoUser::CREATE_ITEM, array('collection' => $collection))) {
            if (empty($collection->visibility)) {
                RestoLogUtil::httpError(403);
            }
            $groups = (new GroupsFunctions($this->context->dbDriver))->getGroups(array('in' => $collection->visibility));

            $can = false;
            foreach ($groups as $group) {
                if ($this->user->hasRightsTo(RestoGroup::createItemRight($group['name']))) {
                    $can = true;
                }
            }
            if (!$can) {
                RestoLogUtil::httpError(403);
            }
        }
        /*
         * Convert visibility from names to ids
         */
        if (isset($body['properties']['visibility'])) {
            if (empty($body['properties']['visibility'])) {
                RestoLogUtil::httpError(400, 'Visibility is set but either emtpy or referencing an unknown group');
            }
            $visibility = (new GeneralFunctions($collection->context->dbDriver))->visibilityNamesToIds($body['properties']['visibility']);
            if (!$this->context->core['anyoneCanSwitchVisibilityToPublic'] && in_array(RestoConstants::GROUP_DEFAULT_ID, $visibility)) {
                $isAdmin = $this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID);
                if (!$isAdmin) {
                    RestoLogUtil::httpError(403, 'You are not allowed to set the visibility of the default group');
                }
            }
        }

        /*
         * Insert feature(s) within database
         */
        $result = $collection->addFeatures($body, array(
            '_splitGeom' => isset($params['_splitGeom']) && filter_var($params['_splitGeom'], FILTER_VALIDATE_BOOLEAN) === false ? false : $this->context->core["splitGeometryOnDateLine"],
            'tolerance' => isset($params['tolerance']) && is_numeric($params['tolerance']) ? (float) $params['tolerance'] : null,
            'maxpoints' => isset($params['maxpoints']) && ctype_digit($params['maxpoints']) ? (int) $params['maxpoints'] : null
        ));

        /*
         * This should not happen
         */
        if ($result === false) {
            RestoLogUtil::httpError(500, 'Cannot insert feature in database');
        }

        return RestoLogUtil::success('Inserted features', $result);
    }
}
