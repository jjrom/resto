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
     *                  "type": "Catalog",
     *                  "links": {
     *                      {
     *                          "rel": "self",
     *                          "type": "application/json",
     *                          "href": "https://api.staging.edito.eu/data/collections"
     *                      },
     *                      {
     *                          "rel": "root",
     *                          "type": "application/json",
     *                          "href": "https://api.staging.edito.eu/data"
     *                      },
     *                      {
     *                          "title": "All items",
     *                          "matched": 147868,
     *                          "rel": "items",
     *                          "type": "application/geo+json",
     *                          "href": "https://api.staging.edito.eu/data/search"
     *                      },
     *                      {
     *                          "title": "Animal Tracking Datasets",
     *                          "matched": 51,
     *                          "rel": "child",
     *                          "type": "application/json",
     *                          "href": "https://api.staging.edito.eu/data/collections/animal_tracking_datasets",
     *                          "roles": {
     *                              "collection"
     *                          },
     *                          "description": "A collection of public datasets from the European Tracking Network."
     *                      },
     *                      {
     *                          "title": "Age of sea ice (Climate Forecast convention)",
     *                          "matched": 45,
     *                          "rel": "child",
     *                          "type": "application/json",
     *                          "href": "https://api.staging.edito.eu/data/collections/climate_forecast-age_of_sea_ice",
     *                          "roles": {
     *                              "collection"
     *                          },
     *                          "description": "Age of sea ice means the length of time elapsed since the ice formed. Sea ice means all ice floating in the sea which has formed from freezing sea water, rather than by other processes such as calving of land ice to form icebergs."
     *                      },
     *                      {
     *                          "title": "Air pressure at mean sea level (Climate Forecast convention)",
     *                          "matched": 2938,
     *                          "rel": "child",
     *                          "type": "application/json",
     *                          "href": "https://api.staging.edito.eu/data/collections/climate_forecast-air_pressure_at_mean_sea_level",
     *                          "roles": {
     *                              "collection"
     *                          },
     *                          "description": "Air pressure at sea level is the quantity often abbreviated as MSLP or PMSL. Air pressure is the force per unit area which would be exerted when the moving gas molecules of which the air is composed strike a theoretical surface of any orientation. Mean sea level means the time mean of sea surface elevation at a given location over an arbitrary period sufficient to eliminate the tidal signals."
     *                      },
     *                      {
     *                          "title": "Air temperature (Climate Forecast convention)",
     *                          "matched": 64,
     *                          "rel": "child",
     *                          "type": "application/json",
     *                          "href": "https://api.staging.edito.eu/data/collections/climate_forecast-air_temperature",
     *                          "roles": {
     *                              "collection"
     *                          },
     *                          "description": "Air temperature is the bulk temperature of the air, not the surface (skin) temperature."
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
                RestoLogUtil::httpError(400, 'Visibility is set but either empty or referencing an unknown group');
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
