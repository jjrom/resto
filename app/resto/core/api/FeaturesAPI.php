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
     *      @OA\Parameter(
     *          name="q",
     *          in="query",
     *          style="form",
     *          description="Free text search - OpenSearch {searchTerms}. Can include hashtags i.e. text starting with *#* characters. In this case, use the following:
* *#cryosphere* will search for *cryosphere*
* *#cryosphere #atmosphere* will search for *cryosphere* AND *atmosphere*
* *#cryosphere|atmosphere* will search for *cryosphere* OR *atmosphere*
* *#cryosphere!* will search for *cryosphere* OR any *broader* concept of *cryosphere* ([EXTENSION][SKOS])
* *#cryosphere\** will search for *cryosphere* OR any *narrower* concept of *cryosphere* ([EXTENSION][SKOS])
* *#cryosphere~* will search for *cryosphere* OR any *related* concept of *cryosphere* ([EXTENSION][SKOS])",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          style="form",
     *          description="Number of results returned per page - between 1 and 500 (default 20) - OpenSearch {count}",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              minimum=1,
     *              maximum=500,
     *              default=20
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="startIndex",
     *          in="query",
     *          style="form",
     *          description="First result to provide - minimum 1, (default 1) - OpenSearch {startIndex}",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              minimum=1,
     *              default=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          style="form",
     *          description="First page to provide - minimum 1, (default 1) - OpenSearch {startPage}",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              minimum=1,
     *              default=1
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="lang",
     *          in="query",
     *          style="form",
     *          description="Two letters language code according to ISO 639-1 (default *en*) - OpenSearch {language}",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="ids",
     *          in="query",
     *          style="form",
     *          description="Array of item ids to return. All other filter parameters that further restrict the number of search results (except next and limit) are ignored",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="intersects",
     *          in="query",
     *          style="form",
     *          description="Region of Interest defined in GeoJSON or in Well Known Text standard (WKT) with coordinates in decimal degrees (EPSG:4326) - OpenSearch {geo:geometry}",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="bbox",
     *          in="query",
     *          style="form",
     *          description="Region of Interest defined by 'west, south, east, north' coordinates of longitude, latitude, in decimal degrees (EPSG:4326) - OpenSearch {geo:box}",
     *          required=false,
     *          @OA\Schema(
     *              type="array",
     *              minItems=4,
     *              maxItems=6,
     *              @OA\Items(
     *                  type="number",
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          style="form",
     *          description="[EXTENSION][egg] Location string e.g. Paris, France  or toponym identifier (i.e. geouid:xxxx) - OpenSearch {geo:name}",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="lon",
     *          in="query",
     *          style="form",
     *          description="Longitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lat - OpenSearch {geo:lon}",
     *          required=false,
     *          @OA\Schema(
     *              type="number"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="lat",
     *          in="query",
     *          style="form",
     *          description="Latitude expressed in decimal degrees (EPSG:4326) - should be used with geo:lon - OpenSearch {geo:lat}",
     *          required=false,
     *          @OA\Schema(
     *              type="number"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="radius",
     *          in="query",
     *          style="form",
     *          description="Radius expressed in meters - should be used with geo:lon and geo:lat - OpenSearch {geo:radius}",
     *          required=false,
     *          @OA\Schema(
     *              type="number"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="datetime",
     *          in="query",
     *          style="form",
     *          description="Single date+time, or a range ('/' separator) of the search query. Format should follow RFC-3339 - OpenSearch {time:start}/{time:end}",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              format="date-time",
     *              pattern="^([0-9]{4})-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])[Tt]([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\.[0-9]+)?(([Zz])|([\+|\-]([01][0-9]|2[0-3]):[0-5][0-9]))$"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="start",
     *          in="query",
     *          style="form",
     *          description="Beginning of the time slice of the search query. Format should follow RFC-3339 - OpenSearch {time:start}.",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              format="date-time",
     *              pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="end",
     *          in="query",
     *          style="form",
     *          description="End of the time slice of the search query. Format should follow RFC-3339 - OpenSearch {time:end}",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              format="date-time",
     *              pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="published",
     *          in="query",
     *          style="form",
     *          description="Returns products with metadata publication date greater or equal than *published* - OpenSearch {dc:date}",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              format="date-time",
     *              pattern="^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="prev",
     *          in="query",
     *          style="form",
     *          description="Returns features with *sort* key value greater than *prev* value - use this for pagination. The value is a unique iterator computed from the *sort* key value and provided within each feature properties as *sort_idx* property",
     *          required=false,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="next",
     *          in="query",
     *          style="form",
     *          description="Returns features with *sort* key value lower than *next* value - use this for pagination. The value is a unique iterator computed from the *sort* key value and provided within each feature properties as *sort_idx* property",
     *          required=false,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="pid",
     *          in="query",
     *          style="form",
     *          description="Like on product identifier",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort",
     *          in="query",
     *          style="form",
     *          description="Sort results by property *startDate* or *created* (default *startDate*). Sorting order is DESCENDING (ASCENDING if property is prefixed by minus sign)",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="owner",
     *          in="query",
     *          style="form",
     *          description="Limit search to owner's features (i.e. resto user identifier as bigint)",
     *          required=false,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="likes",
     *          in="query",
     *          style="form",
     *          description="[EXTENSION][social] Limit search to number of likes (interval)",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="liked",
     *          in="query",
     *          style="form",
     *          description="[EXTENSION][social] Return only liked features from calling user",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          style="form",
     *          description="Feature status (unusued)",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="productType",
     *          in="query",
     *          style="form",
     *          description="[MODEL][SatelliteModel] A string identifying the entry type (e.g. ER02_SAR_IM__0P, MER_RR__1P, SM_SLC__1S, GES_DISC_AIRH3STD_V005) - OpenSearch {eo:productType}",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="processingLevel",
     *          in="query",
     *          style="form",
     *          description="[MODEL][SatelliteModel] A string identifying the processing level applied to the entry - OpenSearch {eo:processingLevel}",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="platform",
     *          in="query",
     *          style="form",
     *          description="[MODEL][SatelliteModel] A string with the platform short name (e.g. Sentinel-1) - OpenSearch {eo:platform}",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="instrument",
     *          in="query",
     *          style="form",
     *          description="[MODEL][SatelliteModel] A string identifying the instrument (e.g. MERIS, AATSR, ASAR, HRVIR. SAR) - OpenSearch {eo:instrument}",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sensorType",
     *          in="query",
     *          style="form",
     *          description="[MODEL][SatelliteModel] A string identifying the sensor type. Suggested values are: OPTICAL, RADAR, ALTIMETRIC, ATMOSPHERIC, LIMB - OpenSearch {eo:sensorType}",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="cloudCover",
     *          in="query",
     *          style="form",
     *          description="[MODEL][OpticalModel] Cloud cover expressed in percent",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="snowCover",
     *          in="query",
     *          style="form",
     *          description="[MODEL][OpticalModel] Snow cover expressed in percent",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="waterCover",
     *          in="query",
     *          style="form",
     *          description="[MODEL][LandCoverModel] Water area expressed in percent",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="urbanCover",
     *          in="query",
     *          style="form",
     *          description="[MODEL][LandCoverModel] Urban area expressed in percent",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="iceCover",
     *          in="query",
     *          style="form",
     *          description="[MODEL][LandCoverModel] Ice area expressed in percent",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="herbaceousCover",
     *          in="query",
     *          style="form",
     *          description="[MODEL][LandCoverModel] Herbaceous area expressed in percent",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="forestCover",
     *          in="query",
     *          style="form",
     *          description="[MODEL][LandCoverModel] Forest area expressed in percent",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="floodedCover",
     *          in="query",
     *          style="form",
     *          description="[MODEL][LandCoverModel] Flooded area expressed in percent",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="desertCover",
     *          in="query",
     *          style="form",
     *          description="[MODEL][LandCoverModel] Desert area expressed in percent",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="cultivatedCover",
     *          in="query",
     *          style="form",
     *          description="[MODEL][LandCoverModel] Cultivated area expressed in percent",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="_heatmapNoGeo",
     *          in="query",
     *          style="form",
     *          description="[EXTENSION][Heatmap] True to compute search result heatmap without taking account geographical filter",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean"
     *          )
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
            //return RestoLogUtil::httpError(400, 'You cannot specify a list of collections on a single collection search');
        }

        if (isset($params['ck'])) {
            return RestoLogUtil::httpError(400, 'You cannot filter on collections keywords on a single collection search');
        }

        if (isset($params['model'])) {
            return RestoLogUtil::httpError(400, 'You cannot specify a collection and a model at the same time');
        }

        // [STAC] Only one of either intersects or bbox should be specified. If both are specified, a 400 Bad Request response should be returned.
        if (isset($params['intersects']) && isset($params['bbox'])) {
            return RestoLogUtil::httpError(400, 'Only one of either intersects or bbox should be specified');
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

        if (!$this->user->hasRightsTo(RestoUser::UPDATE_FEATURE, array('feature' => $feature))) {
            RestoLogUtil::httpError(403);
        }

        // Specifically set splitGeometry
        $params['_splitGeom'] = isset($params['_splitGeom']) && filter_var($params['_splitGeom'], FILTER_VALIDATE_BOOLEAN) === false ? false : $this->context->core["splitGeometryOnDateLine"];

        return $collection->model->updateFeature($feature, $collection, $body, $params);
    }

    /**
     * Update feature property
     *
     *  @OA\Put(
     *      path="/collections/{collectionId}/items/{featureId}/properties/{property}",
     *      summary="Update feature property",
     *      description="Update {property} for feature {featureId}",
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
     *      @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         description="Property to update",
     *         @OA\Schema(
     *              type="string",
     *              enum={"title", "description", "visibility", "owner", "status"}
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The property is updated",
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
     *         description="Property value to update",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="value",
     *                  description="New property value"
     *              ),
     *              example={
     *                  "value":1
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
    public function updateFeatureProperty($params, $body)
    {
        $feature = new RestoFeature($this->context, $this->user, array(
            'featureId' => $params['featureId'],
            'collection' => $this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load()
        ));

        if (!$feature->isValid()) {
            RestoLogUtil::httpError(404);
        }

        if (!$this->user->hasRightsTo(RestoUser::UPDATE_FEATURE, array('feature' => $feature))) {
            RestoLogUtil::httpError(403);
        }
        
        // A value key is mandatory
        if (! array_key_exists('value', $body)) {
            return RestoLogUtil::httpError(400, 'Missing mandatory "value" property');
        }

        // Only these properties can be updated
        if (! in_array($params['property'], array('title', 'description', 'visibility', 'owner', 'status'))) {
            return RestoLogUtil::httpError(400, 'Invalid property "' . $params['property'] . '"');
        }
        
        // Only admin can change owner property
        if ($params['property'] === 'owner' && ! $this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID)) {
            RestoLogUtil::httpError(403);
        }

        return (new FeaturesFunctions($this->context->dbDriver))->updateFeatureProperty($feature, $params['property'], $body['value']);
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

        if (!$this->user->hasRightsTo(RestoUser::DELETE_FEATURE, array('feature' => $feature))) {
            RestoLogUtil::httpError(403);
        }

        // Result contains boolean for facetsDeleted
        $result = (new FeaturesFunctions($this->context->dbDriver))->removeFeature($feature);

        return RestoLogUtil::success('Feature deleted', array(
            'featureId' => $feature->id,
            'catalogsUpdated' => $result['catalogsUpdated']
        ));
    }
}
