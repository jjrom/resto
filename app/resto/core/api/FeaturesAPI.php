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
 * Features API
 */
class FeaturesAPI
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
     * Return feature
     *
     * @OA\Get(
     *      path="/collections/{collectionId}/items/{featureId}",
     *      summary="Get feature",
     *      description="Returns feature {featureId} metadata",
     *      tags={"Feature"},
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
     *          name="featureId",
     *          in="path",
     *          description="Feature identifier",
     *          required=true,
     *          @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *          name="fields",
     *          in="query",
     *          style="form",
     *          description="Comma separated list of property fields to be returned",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          ),
     *          description="Comma separated list of property fields to be returned. The following reserved keywords can also be used:
     * _all: Return all properties (This is the default)
     * _simple: Return all fields except *keywords* property"
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Feature metadata",
     *          @OA\JsonContent(ref="#/components/schemas/OutputFeature")
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Feature not found"
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *  )
     *
     * @param array params
     */
    public function getFeature($params)
    {
        $feature = new RestoFeature($this->context, $this->user, array(
            'featureId' => $params['featureId'],
            'fields' => $params['fields'] ?? null,
            'collection' => $this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load()
        ));

        if (!$feature->isValid()) {
            RestoLogUtil::httpError(404);
        }

        // Set Content-Type to GeoJSON
        if ($this->context->outputFormat === 'json') {
            $this->context->outputFormat = 'geojson';
        }

        return $feature;
    }

    /**
     * Search for features in a given collections
     *
     *  @OA\Get(
     *      path="/collections/{collectionId}/items",
     *      summary="Get features (search on a specific collection)",
     *      description="List of filters to search features within collection {collectionId}",
     *      tags={"Feature"},
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
     *          description="Features collection",
     *          @OA\JsonContent(ref="#/components/schemas/RestoFeatureCollection")
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad request (i.e. invalid parameter)",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Collection not Found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      )
     * )
     *
     * @param array params
     */
    public function getFeaturesInCollection($params)
    {

        // This should return HTTP 400 but we discard it instead otherwise it brokes pystac requests
        if (isset($params['collections'])) {
            unset($params['collections']);
            //RestoLogUtil::httpError(400, 'You cannot specify a list of collections on a single collection search');
        }

        if (isset($params['ck'])) {
            RestoLogUtil::httpError(400, 'You cannot filter on collections keywords on a single collection search');
        }

        if (isset($params['model'])) {
            RestoLogUtil::httpError(400, 'You cannot specify a collection and a model at the same time');
        }

        // [STAC] Only one of either intersects or bbox should be specified. If both are specified, a 400 Bad Request response should be returned.
        if (isset($params['intersects']) && isset($params['bbox'])) {
            RestoLogUtil::httpError(400, 'Only one of either intersects or bbox should be specified');
        }

        // Set Content-Type to GeoJSON
        if ($this->context->outputFormat === 'json') {
            $this->context->outputFormat = 'geojson';
        }

        return $this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load()->search($params);
    }

    /**
     * Update feature
     *
     *  @OA\Put(
     *      path="/collections/{collectionId}/items/{featureId}",
     *      summary="Update feature property",
     *      description="Update feature {featureId}",
     *      tags={"Feature"},
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
     *         name="featureId",
     *         in="path",
     *         required=true,
     *         description="Feature identifier",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The feature is updated",
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
     *                  "message": "Update feature b9eeaf6b-9868-5418-9455-3e77cd349e21"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Invalid property",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Feature not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      @OA\RequestBody(
     *         description="Feature description",
     *         @OA\JsonContent(ref="#/components/schemas/InputFeature")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     * )
     *
     * @param array $params
     * @param array $body
     */
    public function updateFeature($params, $body)
    {
        // Load collection
        $collection = $this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load();

        $feature = new RestoFeature($this->context, $this->user, array(
            'featureId' => $params['featureId'],
            'collection' => $collection
        ));

        if (!$feature->isValid()) {
            RestoLogUtil::httpError(404);
        }

        if ($this->user->hasRightsTo(RestoUser::UPDATE_ITEM, array('item' => $feature))) {
            // Specifically set splitGeometry
            $params['_splitGeom'] = isset($params['_splitGeom']) && filter_var($params['_splitGeom'], FILTER_VALIDATE_BOOLEAN) === false ? false : $this->context->core["splitGeometryOnDateLine"];
            return $collection->model->updateFeature($feature, $collection, $body, $params);
        }
        if (isset($body['properties']['visibility'])) {
            RestoLogUtil::httpError(403, 'Forbidden to update item visibility');
        }
        if (empty($feature->visibility)) {
            RestoLogUtil::httpError(403, 'No visibility');
        }
        $groups = (new GroupsFunctions($this->context->dbDriver))->getGroups(array('in' => $feature->visibility));

        foreach ($groups as $group) {
            $canUpdateInGroup = $this->user->hasRightsTo(RestoGroup::updateItemRight($group['name']));
            if ($canUpdateInGroup) {
                // Specifically set splitGeometry
                $params['_splitGeom'] = isset($params['_splitGeom']) && filter_var($params['_splitGeom'], FILTER_VALIDATE_BOOLEAN) === false ? false : $this->context->core["splitGeometryOnDateLine"];
                return $collection->model->updateFeature($feature, $collection, $body, $params);
            }
        }
        RestoLogUtil::httpError(403, 'Insufficient rights to update an item');
    }

    /**
     * Update feature properties
     *
     *  @OA\Put(
     *      path="/collections/{collectionId}/items/{featureId}/properties",
     *      summary="Update feature properties",
     *      description="Update properties for feature {featureId}. Allowed properties are one of : title, description, visibility, owner and status",
     *      tags={"Feature"},
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
     *         name="featureId",
     *         in="path",
     *         required=true,
     *         description="Feature identifier",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The properties are updated",
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
     *                  "message": "Update property for feature b9eeaf6b-9868-5418-9455-3e77cd349e21"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Invalid property",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Feature not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      @OA\RequestBody(
     *         description="Properties to update",
     *         @OA\JsonContent()
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     * )
     *
     * @param array $params
     * @param array $body
     */
    public function updateFeatureProperties($params, $body)
    {
        $feature = new RestoFeature($this->context, $this->user, array(
            'featureId' => $params['featureId'],
            'collection' => $this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load()
        ));

        if (!$feature->isValid()) {
            RestoLogUtil::httpError(404);
        }

        if (!$this->user->hasRightsTo(RestoUser::UPDATE_ITEM, array('item' => $feature))) {
            RestoLogUtil::httpError(403);
        }

        // A value key is mandatory
        $allowed = array('title', 'description', 'visibility', 'owner', 'status');
        foreach (array_keys($body) as $property) {

            if (! in_array($property, $allowed)) {
                RestoLogUtil::httpError(400, 'Property "' . $property . '" is not one of updatable properties (' . join(',', $allowed) . ')');
            }

            // Only admin can change owner property
            if ($property === 'owner' && ! $this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID)) {
                RestoLogUtil::httpError(403, 'You are not allowed to change property "owner"');
            }

            // Convert visibility from names to ids
            if ($property === 'visibility' && isset($body[$property])) {
                $body[$property] = (new GeneralFunctions($this->context->dbDriver))->visibilityNamesToIds($body[$property]);
                if (empty($body[$property])) {
                    RestoLogUtil::httpError(400, 'Visibility is set but either emtpy or referencing an unknown group');
                }
                if (!(new CatalogsFunctions($this->context->dbDriver))->canSeeCatalog($body[$property], $this->user, true)) {
                    RestoLogUtil::httpError(403, 'You are not allowed to set the visibility to a group you are not part of');
                }
            }
        }

        return (new FeaturesFunctions($this->context->dbDriver))->updateFeatureProperties($feature, $body, $this->context, $this->user);
    }

    /**
     * Delete feature
     *
     * @OA\Delete(
     *      tags={"Feature"},
     *      path="/collections/{collectionId}/items/{featureId}",
     *      summary="Delete feature",
     *      description="Delete feature {featureId}",
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
     *          name="featureId",
     *          in="path",
     *          description="Feature identifier",
     *          required=true,
     *          @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The feature is delete",
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
     *                  "message": "Feature 7e5caa78-5127-53e5-97ff-ddf44984ef56 deleted"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Missing mandatory feature identifier",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Only user with *update* rights can delete a feature",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Feature not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *  )
     * @param array $params
     */
    public function deleteFeature($params)
    {
        $feature = new RestoFeature($this->context, $this->user, array(
            'featureId' => $params['featureId'],
            'collection' => $this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load()
        ));

        if (!$feature->isValid()) {
            RestoLogUtil::httpError(404);
        }

        if ($this->user->hasRightsTo(RestoUser::DELETE_ITEM, array('item' => $feature))) {
            $result = (new FeaturesFunctions($this->context->dbDriver))->removeFeature($feature);

            return RestoLogUtil::success('Feature deleted', array(
                'featureId' => $feature->id,
                'catalogsUpdated' => $result['catalogsUpdated']
            ));
        }
        if (empty($feature->visibility)) {
            RestoLogUtil::httpError(403, 'No visibility');
        }
        $groups = (new GroupsFunctions($this->context->dbDriver))->getGroups(array('in' => $feature->visibility));

        foreach ($groups as $group) {
            $canDeleteInGroup = $this->user->hasRightsTo(RestoGroup::deleteItemRight($group['name']));
            if ($canDeleteInGroup) {
                $result = (new FeaturesFunctions($this->context->dbDriver))->removeFeature($feature);

                return RestoLogUtil::success('Feature deleted', array(
                    'featureId' => $feature->id,
                    'catalogsUpdated' => $result['catalogsUpdated']
                ));
            }
        }
        RestoLogUtil::httpError(403, 'Insufficient rights to delete an item');
    }
}
