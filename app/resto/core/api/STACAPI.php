<?php
/*
 * Copyright 2022 Jérôme Gasperi
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
 * STAC add-on
 * 
 *  @OA\Tag(
 *      name="Catalog",
 *      description="A STAC Catalog is a collection of STAC Items"
 *  )
 * 
 *  @OA\Schema(
 *      schema="Catalog",
 *      required={"id", "description", "links", "stac_version"},
 *      @OA\Property(
 *          property="id",
 *          type="string",
 *          description="Identifier for the catalog."
 *      ),
 *      @OA\Property(
 *          property="title",
 *          type="string",
 *          description="A short descriptive one-line title for the catalog."
 *      ),
 *      @OA\Property(
 *          property="description",
 *          type="string",
 *          description="Detailed multi-line description to fully explain the catalog. CommonMark 0.28 syntax MAY be used for rich text representation."
 *      ),
 *      @OA\Property(
 *          property="links",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/Link")
 *      ),
 *      @OA\Property(
 *          property="stac_version",
 *          type="string",
 *          description="The STAC version the catalog implements"
 *      ),
 *      example={
 *          "id": "year",
 *          "title": "Facet : year",
 *          "description": "Catalog of items filtered by year",
 *          "links": {
 *              {
 *                  "rel": "self",
 *                  "type": "application/json",
 *                  "href": "http://127.0.0.1:5252/collections/S2.json?&_pretty=1"
 *              },
 *              {
 *                  "rel": "root",
 *                  "type": "application/json",
 *                  "href": "http://127.0.0.1:5252"
 *              },
 *              {
 *                  "rel": "license",
 *                  "href": "https://scihub.copernicus.eu/twiki/pub/SciHubWebPortal/TermsConditions/Sentinel_Data_Terms_and_Conditions.pdf",
 *                  "title": "Legal notice on the use of Copernicus Sentinel Data and Service Information"
 *              }
 *          },
 *          "stac_version": "1.0.0"
 *      }
 *  )
 * 
 *  @OA\Schema(
 *      schema="Queryables",
 *      @OA\Property(
 *          property="$schema",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="$id",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="type",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="title",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="description",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="properties",
 *          type="object",
 *          @OA\JsonContent()
 *      ),
 *      @OA\Property(
 *          property="additionalProperties",
 *          type="boolean"
 *      )
 *  )
 */
class STACAPI
{
    /**
     * Links
     *
     * @OA\Schema(
     *      schema="Link",
     *      description="Link",
     *      required={"rel", "href"},
     *      @OA\Property(
     *          property="rel",
     *          type="string",
     *          description="Relationship between the feature and the linked document/resource"
     *      ),
     *      @OA\Property(
     *          property="type",
     *          type="string",
     *          description="Mimetype of the resource"
     *      ),
     *      @OA\Property(
     *          property="title",
     *          type="string",
     *          description="Title of the resource"
     *      ),
     *      @OA\Property(
     *          property="href",
     *          type="string",
     *          description="Url to the resource"
     *      ),
     *      example={
     *          "rel": "self",
     *          "type": "application/json",
     *          "href": "http://127.0.0.1:5252/collections/S2.json?&_pretty=1"
     *      }
     * )
     *
     * Assets
     *
     * @OA\Schema(
     *      schema="Asset",
     *      description="Asset links",
     *      required={"rel", "href"},
     *      @OA\Property(
     *          property="rel",
     *          type="string",
     *          description="Relationship between the feature and the linked document/resource"
     *      ),
     *      @OA\Property(
     *          property="type",
     *          type="string",
     *          description="Mimetype of the resource"
     *      ),
     *      @OA\Property(
     *          property="title",
     *          type="string",
     *          description="Title of the resource"
     *      ),
     *      @OA\Property(
     *          property="href",
     *          type="string",
     *          description="Url to the resource"
     *      ),
     *      @OA\Property(
     *          property="roles",
     *          type="array",
     *          description="Asset roles",
     *          @OA\Items(
     *              type="string",
     *          )
     *      ),
     *      example={
     *          "href": "https://landsat-pds.s3.amazonaws.com/c1/L8/171/002/LC08_L1TP_171002_20200616_20200616_01_RT/LC08_L1TP_171002_20200616_20200616_01_RT_B1.TIF",
     *          "type": "image/tiff; application=geotiff; profile=cloud-optimized",
     *          "roles":{"data"},
     *          "bands": {
     *              0
     *          }
     *      }
     * )
     */

    /*
     * STAC version
     */
    const STAC_VERSION = '1.1.0';

    /*
     * STAC namespaces
     */
    const CONFORMANCE_CLASSES = array(
        'https://api.stacspec.org/v1.0.0/core',
        'https://api.stacspec.org/v1.0.0/collections',
        'https://api.stacspec.org/v1.0.0/ogcapi-features',
        'https://api.stacspec.org/v1.0.0-rc.3/browseable',
        'https://api.stacspec.org/v1.0.0-rc.2/children',
        'https://api.stacspec.org/v1.0.0/item-search',
        'https://api.stacspec.org/v1.0.0/item-search#fields',
        // Unsupported
        //'https://api.stacspec.org/v1.0.0/item-search#query',
        'https://api.stacspec.org/v1.0.0/item-search#sort',
        'https://api.stacspec.org/v1.0.0/item-search#filter',

        'https://api.stacspec.org/v1.0.0/ogcapi-features#fields',
        'https://api.stacspec.org/v1.0.0/ogcapi-features#sort',
        'https://api.stacspec.org/v1.0.0/ogcapi-features#filter',

        'http://www.opengis.net/spec/ogcapi_common-2/1.0/conf/collections',

        'http://www.opengis.net/spec/ogcapi-features-1/1.0/conf/core',
        'http://www.opengis.net/spec/ogcapi-features-1/1.0/conf/geojson',
        'http://www.opengis.net/spec/ogcapi-features-1/1.0/conf/oas30',
        'http://www.opengis.net/spec/ogcapi-features-1/1.0/conf/html',
        'http://www.opengis.net/spec/ogcapi-features-3/1.0/conf/filter',
        'http://www.opengis.net/spec/ogcapi-features-3/1.0/conf/features-filter',

        'http://www.opengis.net/spec/cql2/1.0/conf/basic-cql2',
        'http://www.opengis.net/spec/cql2/1.0/conf/cql2-text',
        'http://www.opengis.net/spec/cql2/1.0/conf/basic-spatial-functions',
        'http://www.opengis.net/spec/cql2/1.0/conf/basic-spatial-functions-plus',
        'http://www.opengis.net/spec/cql2/1.0/conf/advanced-comparison-operators'
    );

    /*
     * Reference to catalogsFunctions
     */
    private $catalogsFunctions;

    private $context;
    private $user;
    
    /**
     * Constructor
     *
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user)
    {
        $this->context = $context;
        $this->user = $user;
        $this->catalogsFunctions = new CatalogsFunctions($this->context->dbDriver);
    }

    /**
     * Return a STAC catalog
     *
     *    @OA\Get(
     *      path="/catalogs/*",
     *      summary="Get STAC catalogs",
     *      description="Get STAC catalogs",
     *      tags={"STAC"},
     *      @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Filter on catalog id and description",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="_countCatalogs",
     *         in="query",
     *         description="Set to 1 to not count number of items below catalogs. Speed up *a lot* the query so should be used when using this for suggest (see rocket catalog search for instance)",
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="STAC catalog definition - contains links to child catalogs and/or items",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/Catalog"
     *          )
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Not found"
     *      )
     *    )
     */
    public function getCatalogs($params)
    {
        // This is /catalogs
        if ( !isset($params['segments']) ) {
            $catalog = array(
                'stac_version' => STACAPI::STAC_VERSION,
                'id' => 'catalogs',
                'type' => 'Catalog',
                'title' => 'Catalogs',
                'description' => 'List of available catalogs',
                'links' => array_merge(
                    $this->getBaseLinks(),
                    $this->getRootCatalogLinks($params)
                )
            );
            return $this->context->core['useJSONLD'] ? JSONLDUtil::addDataCatalogMetadata($catalog) : $catalog;
        }

        // This is /catalogs/*
        return $this->processPath($params['segments'], $params);
    }

     /**
     * Add a catalog
     * 
     *    @OA\Post(
     *      path="/catalogs/*",
     *      summary="Add a STAC catalog",
     *      description="Add a STAC catalog",
     *      tags={"STAC"},
     *      @OA\RequestBody(
     *          description="A valid STAC Catalog",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Catalog")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The catalog is created",
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
     *                  "message": "Catalog created"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Missing one of the mandatory input property",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Only user with *createCatalog* rights can create a catalog",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *    )
     * 
     * @param array $params
     * @param array $body
     */
    public function addCatalog($params, $body)
    {

        /*
         * Check that parent catalogs exists
         */
        $parentId = null;
        if ( isset($params['segments']) ) {
            for ($i = 0, $ii = count($params['segments']); $i < $ii; $i++) {
                $parentId = isset($parentId) ? $parentId . '/' . $params['segments'][$i] : $params['segments'][$i];
                $parentCatalog = $this->catalogsFunctions->getCatalog($parentId, $this->user);
                if ( $parentCatalog === null) {
                    RestoLogUtil::httpError(400, 'Parent catalog ' . $parentId . ' does not exist.');
                }
                if ( isset($parentCatalog['stac_url']) ) {
                    RestoLogUtil::httpError(400, 'Cannot add a catalog under an external catalog.');
                }
            }
        }
       
        /*
         * Compute internal catalog id as full path
         */
        $body['id'] = $this->getIdPath($body, $parentId);

        /*
         * First check that user has the right to create a catalog
         */
        if (!$this->user->hasRightsTo(RestoUser::CREATE_CATALOG, array('catalog' => $body))) {
            RestoLogUtil::httpError(403);
        }

        /*
         * Check mandatory properties
         */
        if ( !isset($body['type']) || !in_array($body['type'], array('Catalog', 'Collection')) ) {
            RestoLogUtil::httpError(400, 'Missing mandatory type - must be set to *Catalog* or *Collection*');
        }
        if ( !isset($body['description']) && $body['type'] === 'Catalog' ) {
            RestoLogUtil::httpError(400, 'Missing mandatory description');
        }
        if ( !isset($body['links']) ) {
            $body['links'] = array();
        }

        /*
         * Convert visibility from names to ids
         */
        if ( isset($body['visibility']) ) {
            $body['visibility'] = (new GeneralFunctions($this->context->dbDriver))->visibilityNamesToIds($body['visibility']);
            if ( empty($body['visibility']) ) {
                RestoLogUtil::httpError(400, 'Visibility is set but either emtpy or referencing an unknown group'); 
            }
            if ( !$this->catalogsFunctions->canSeeCatalog($body['visibility'], $this->user, true) ) {
                RestoLogUtil::httpError(403, 'You are not allowed to set the visibility to a group you are not part of');
            }
        }

        // Owner of catalog can only be set by admin user
        if ( isset($body['owner']) ) {

            if ( !$this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ) {
                RestoLogUtil::httpError(403, 'You are not allowed to set property "owner"');
            }

            // Convert owner name to id
            $owner = new RestoUser(array('username' => $body['owner']), $this->context);
            $body['owner'] = $owner->profile['id'];
            
        }

        // Only admin can change pinned flag
        if ( isset($body['pinned']) && $body['pinned'] ) {

            if ( !$this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ) {
                RestoLogUtil::httpError(403, 'You are not allowed to set property "pinned"');
            }
            
        }

        /*
         * [IMPORTANT] Special case - post a collection under a catalog is in fact an update of 'links' property of this catalog
         */
        if ( $body['type'] === 'Collection' ) {

            // Collection does not exist - created first
            if ( !(new CollectionsFunctions($this->context->dbDriver))->collectionExists($body['id']) ) {
                $this->context->keeper->getRestoCollections($this->user)->create($body, $params['model'] ?? null);
            }
        }

        if ($this->catalogsFunctions->getCatalog($body['id'], $this->user) !== null) {
            RestoLogUtil::httpError(409, 'Catalog ' . $body['id'] . ' already exists');
        }

        return RestoLogUtil::success('Catalog added', $this->catalogsFunctions->storeCatalogs(array($body), $this->context, $this->user, null, null, true));

    }

    /**
     * Update catalog
     * 
     *    @OA\Put(
     *      path="/catalogs/*",
     *      summary="Update catalog",
     *      description="Update catalog",
     *      tags={"STAC"},
     *      @OA\Parameter(
     *         name="catalogId",
     *         in="path",
     *         required=true,
     *         description="Catalog identifier",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\RequestBody(
     *          description="Catalog fields to be update limited to title and description",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Catalog")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Catalog is updated",
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
     *                  "message": "Catalog updated"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Only user with *updateCatalog* rights can update a catalog",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *    )
     * 
     * @param array $params
     * @param array $body
     */
    public function updateCatalog($params, $body)
    {

        // Get catalogs and childs
        $catalogs = $this->catalogsFunctions->getCatalogs(array(
            'id' => join('/', $params['segments']),
            'countCatalogs' => false,
            'noProperties' => true
        ), true);

        if ( count($catalogs) === 0 ){
            RestoLogUtil::httpError(404);
        }

        // If catalog is a collection it cannot be updated this way
        if ( isset($catalogs[0]['rtype']) && $catalogs[0]['rtype'] === 'collection' ) {
            RestoLogUtil::httpError(400, 'This catalog is a collection. Collection should be updated using /collections endoint');
        }

        // If user has not the right to update catalog then 403
        if ( !$this->user->hasRightsTo(RestoUser::UPDATE_CATALOG, array('catalog' => $catalogs[0])) ) {
            RestoLogUtil::httpError(403);
        }

        /*
         * Convert visibility from names to ids
         * Note that a user can only set the visibility of a catalog if he is in the group
         */
        if ( isset($body['visibility']) ) {
            $body['visibility'] = (new GeneralFunctions($this->context->dbDriver))->visibilityNamesToIds($body['visibility']);
            if ( empty($body['visibility']) ) {
                RestoLogUtil::httpError(400, 'Visibility is set but either emtpy or referencing an unknown group'); 
            }

            if ( !$this->catalogsFunctions->canSeeCatalog($body['visibility'], $this->user, true) ) {
                RestoLogUtil::httpError(403, 'You are not allowed to set the visibility to a group you are not part of');
            }

        }

        // Owner of catalog can only be changed by admin user
        if ( isset($body['owner']) ) {

            if ( !$this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ) {
                RestoLogUtil::httpError(403, 'You are not allowed to change property "owner"');
            }

             // Convert owner name to id
             $owner = new RestoUser(array('username' => $body['owner']), $this->context);
             $body['owner'] = $owner->profile['id'];
            
        }

        // Only admin can change pinned flag
        if ( isset($body['pinned']) ) {

            if ( !$this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ) {
                RestoLogUtil::httpError(403, 'You are not allowed to change property "pinned"');
            }
            
        }
        
        // Update is not forced so we should check that input links array don't remove existing childs
        // [IMPORTANT] if no links object is in the body then only other properties are updated and existing links are not destroyed
        if ( array_key_exists('links', $body) && isset($body['links']) ) {
            $levelUp = array();
            for ($i = 0, $ii = count($catalogs); $i < $ii; $i++) {
                if ($catalogs[$i]['level'] !== $catalogs[0]['level'] + 1) {
                    continue;
                }
                $levelUp[$catalogs[$i]['id']] = false;
                for ($j = 0, $jj = count($body['links']); $j < $jj; $j++) {
                    if ( !str_starts_with($body['links'][$j]['href'], $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_CATALOGS ) ) {
                        continue;
                    }
                    if ($catalogs[$i]['id'] === substr($body['links'][$j]['href'], strlen($this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_CATALOGS) + 1)) {
                        $levelUp[$catalogs[$i]['id']] = true;
                        break;
                    }
                }
            }
            $removed = 0;
            foreach (array_keys($levelUp) as $key) {
                if ( $levelUp[$key] === false ) {
                    $removed++;
                }
            }

            if ($removed > 0 && !filter_var($params['_force'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                RestoLogUtil::httpError(400, 'The catalog update would remove ' . $removed . ' existing child(s). Set **_force** query parameter to true to force update anyway');
            }

        }

        // [TODO] not sure it's needed
        $body['id'] = $catalogs[0]['id'];
        
        return $this->catalogsFunctions->updateCatalog($body, $this->user, $this->context) ? RestoLogUtil::success('Catalog updated') : RestoLogUtil::httpError(500, 'Cannot update catalog');
    }

    /**
     * Delete catalog
     * 
     *    @OA\Delete(
     *      path="/catalogs/*",
     *      summary="Delete catalog",
     *      description="Delete catalog",
     *      tags={"STAC"},
     *      @OA\Parameter(
     *         name="catalogId",
     *         in="path",
     *         required=true,
     *         description="Catalog identifier",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Catalog deleted",
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
     *                  "message": "Catalog deleted",
     *                  "featuresUpdated": 345
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Only user with *deleteCatalog* rights can delete a catalog",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *    )
     * 
     * @param array $params
     * @param array $body
     */
    public function removeCatalog($params)
    {

        // Get catalogs and childs
        $catalogs = $this->catalogsFunctions->getCatalogs(array(
            'id' => join('/', $params['segments']),
            'countCatalogs' => false
        ), true);
        
        $count = count($catalogs);
        if ( $count === 0 ){
            RestoLogUtil::httpError(404);
        }

        // If user has not the right to delete catalog then 403
        if ( !$this->user->hasRightsTo(RestoUser::DELETE_CATALOG, array('catalog' => $catalogs[0])) ) {
            RestoLogUtil::httpError(403);
        }

        // If catalogs has child, do not remove it unless _force option is set to true
        if ( $count > 1 && !filter_var($params['_force'] ?? false, FILTER_VALIDATE_BOOLEAN) ){
            RestoLogUtil::httpError(400, 'The catalog contains ' . ($count - 1) . ' child(s) and cannot be deleted. Set **_force** query parameter to true to force deletion anyway');
        }
        
        return RestoLogUtil::success('Catalog deleted', $this->catalogsFunctions->removeCatalog($catalogs[0]['id']));

    }


    /**
     * 
     * Return an asset href within an HTTP 301 Redirect message
     * Get asset from this endpoint allows to store download external asset statistics
     * 
     *    @OA\Get(
     *      path="/assets/{urlInBase64}",
     *      summary="Download asset",
     *      description="Return the asset href within an HTTP 301 Redirect message. This allows to keep track of download of external assets in resto statistics",
     *      tags={"STAC"},
     *      @OA\Parameter(
     *         name="urlInBase64",
     *         in="path",
     *         required=true,
     *         description="Asset url encoded in Base64",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="301",
     *          description="HTTP/1.1 301 Moved Permanently"
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Invalid base64 encoded url",
     *      )
     *    )
     *
     * @param array $params
     */
    public function getAsset($params)
    {
        $url = base64_decode($params['urlInBase64']);

        /*
         * Should be a valid url
         */
        if (!$url || strpos($url, 'http') !== 0) {
            RestoLogUtil::httpError(400, 'Invalid base64 encoded url');
        }

        /*
         * Store download in logs
         */
        try {
            (new GeneralFunctions($this->context->dbDriver))->storeQuery($this->user && $this->user->profile ? $this->user->profile['id'] : null, array(
                'path' => $url,
                'method' => 'GET_ASSET'
            ));
        } catch (Exception $e) {
            error_log('[WARNING] Cannot store download info in resto.log');
        }

        /*
         * Permanent 301 redirection
         */
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $url);

        return;
    }

    /**
     * Return the list of children catalog
     * (see https://github.com/radiantearth/stac-api-spec/tree/main/children)
     *
     *    @OA\Get(
     *      path="/children",
     *      summary="Get root child catalogs",
     *      description="List of children of this catalog",
     *      tags={"STAC"},
     *      @OA\Response(
     *          response="200",
     *          description="List of children of the root catalog",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="features",
     *                  type="array",
     *                  description="Array of features",
     *                  @OA\Items(ref="#/components/schemas/OutputFeature")
     *              )
     *          )
     *     )
     *    )
     */
    public function getChildren($params)
    {
        $childs = array();

        // Initialize router to process each children individually
        $router = new RestoRouter($this->context, $this->user);

        $links = $this->getRootCatalogLinks($params);
        for ($i = 0, $ii = count($links); $i < $ii; $i++) {
            if ($links[$i]['rel'] == 'child') {
                try {
                    $response = $router->process('GET', parse_url($links[$i]['href'])['path'], array());
                } catch (Exception $e) {
                    continue;
                }
                if (isset($response)) {
                    $childs[] = $response;
                }
            }
        }

        return array(
            'children' => $childs,
            'links' => array(
                array(
                    'rel' => 'self',
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_STAC_CHILDREN
                ),
                array(
                    'rel' => 'root',
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl']
                )
            )
        );
    }

    /**
     * Return the list of queryables
     * (see https://github.com/stac-api-extensions/filter?tab=readme-ov-file#queryables)
     *
     *    @OA\Get(
     *      path="/queryables",
     *      summary="Queryables for STAC API",
     *      description="Queryable names for the STAC API Item Search filter.",
     *      tags={"STAC"},
     *      @OA\Response(
     *          response="200",
     *          description="Queryables for STAC API",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/Queryables"
     *          )
     *     )
     *    )
     */
    public function getQueryables($params)
    {
        // [IMPORTANT] Supersede output format
        $this->context->outputFormat = 'jsonschema';

        return array(
            '$schema' => 'https://json-schema.org/draft/2019-09/schema',
            '$id' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_STAC_QUERYABLES,
            'type' => 'object',
            'title' => 'Queryables for Example STAC API',
            'description' => 'Queryable names for the example STAC API Item Search filter.',
            // Get common queryables (/queryables) or per collection (/collections/{collectionId}/queryables)
            'properties' => (isset($params['collectionId']) ? ($this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load())->model : new DefaultModel())->getQueryables(),
            'additionalProperties' => true
        );
    }

    /**
     * Search for features in all collections
     *
     *  @OA\Get(
     *      path="/search",
     *      summary="STAC search endpoint",
     *      description="List of filters to search features within all collections",
     *      tags={"Feature"},
     *      @OA\Parameter(
     *          name="model",
     *          in="query",
     *          description="Search features within collections belonging to *model* - e.g. *model=SatelliteModel* will search in all satellite collections",
     *          required=false,
     *          style="form",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="collections",
     *          in="query",
     *          style="form",
     *          description="Search features within collections - comma separated list of collection identifiers",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
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
     *      @OA\Parameter(
     *          name="q",
     *          in="query",
     *          style="form",
     *          description="Free text search - OpenSearch {searchTerms}. Example:
* *cryosphere* will search for *cryosphere*
* *cryosphere atmosphere* will search for *cryosphere* AND *atmosphere*
* *cryosphere|atmosphere* will search for *cryosphere* OR *atmosphere*
* *cryosphere!* will search for *cryosphere* OR any *broader* concept of *cryosphere* ([EXTENSION][SKOS])
* *cryosphere\** will search for *cryosphere* OR any *narrower* concept of *cryosphere* ([EXTENSION][SKOS])
* *cryosphere~* will search for *cryosphere* OR any *related* concept of *cryosphere* ([EXTENSION][SKOS])",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="description",
     *          in="query",
     *          style="form",
     *          description="Keyword search on feature description field",
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
     *          description="Beginning of the time slice of the search query. Format should follow RFC-3339 - OpenSearch {time:start}",
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
     *          name="created",
     *          in="query",
     *          style="form",
     *          description="Returns products with metadata creation date greater or equal than *created* - OpenSearch {dc:date}",
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
     *          name="sortby",
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
     *          description="Limit search to owner's features (i.e. resto username)",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
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
     *          name="fields",
     *          in="query",
     *          style="form",
     *          description="Comma separated list of property fields to be returned",
     *          required=false,
     *          @OA\Items(
     *              type="string"
     *          ),
     *          description="Comma separated list of property fields to be returned. The following reserved keywords can also be used:
* _all: Return all properties (This is the default)
* _simple: Return all fields except *keywords* property"
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
    public function search($params, $body)
    {
        if ($this->context->method === 'POST') {
            $params = $this->jsonQueryToKVP($body);
        }
        
        $model = null;
        if (isset($params['model'])) {
            if (! class_exists($params['model'])) {
                RestoLogUtil::httpError(400, 'Unknown model ' . $params['model']);
            }
            $model = new $params['model']();
        }
        
        // [STAC] Only one of either intersects or bbox should be specified. If both are specified, a 400 Bad Request response should be returned.
        if (isset($params['intersects']) && isset($params['bbox'])) {
            RestoLogUtil::httpError(400, 'Only one of either intersects or bbox should be specified');
        }

        // Set Content-Type to GeoJSON
        $this->context->outputFormat = 'geojson';

        /*
         * [TODO][CHANGE THIS] Temporary solution for collection that are not in resto schema
         *   => replace search on single collection by direct search on single collection
         */
        if (isset($params['collections'])) {
            $collections = array_map('trim', explode(',', $params['collections']));
            if (count($collections) === 1) {
                $params['collectionId'] = $params['collections'];
                unset($params['collections']);
                return $this->context->keeper->getRestoCollection($params['collectionId'], $this->user)->load()->search($params);
            }
        }

        $restoCollections = $this->context->keeper->getRestoCollections($this->user)->load($params);

        return $restoCollections->search($model, $params);
    }

    /**
     * Return STAC catalog from path
     *
     * @param array $segments
     * @param array $params
     * @return array
     */
    private function processPath($segments, $params = array())
    {
    
        /*
         * Addons special case - not handle within resto.catalog table
         */
        $resultFromAddons = $this->processAddons($segments, $params);
        if ( isset($resultFromAddons) ) {
            return $resultFromAddons;
        }
        
        /*
         * Special case for '_' => compute FeatureCollection instead of catalogs
         */
        if ($segments[count($segments) - 1 ] === '_') {
            return $this->processSearch($segments, $params);
        }
        
        /*
         * Get catalogs - first one is $catalogId, other its childs
         */
        $catalogId = join('/', $segments);
        $selfHref = $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_CATALOGS . '/' . $catalogId;
        $catalogs = $this->catalogsFunctions->getCatalogs(array(
            'id' => $catalogId,
            'q' => $params['q'] ?? null,
            'countCatalogs' => isset($params['_countCatalogs']) ? filter_var($params['_countCatalogs'], FILTER_VALIDATE_BOOLEAN) : $this->context->core['countCatalogs'],
            'checkForExternal' => true
        ), true);

        if ( empty($catalogs) ) {
            RestoLogUtil::httpError(404);
        }

        /*
         * This is an external ressources !
         */
        if ( isset($catalogs[0]['stac_url']) ) {
            return $this->processExternalCatalog($catalogs[0], $selfHref);
        }
        
        // The path is the catalog identifier
        $parentAndChilds = $this->getParentAndChilds($catalogs, $params);

        if ( $parentAndChilds['parent']['visibility'] ) {
            $canSee = false;
            for ($i = count($parentAndChilds['parent']['visibility']); $i--;) {
                if ( $this->user->hasGroup($parentAndChilds['parent']['visibility'][$i]) ) {
                    $canSee = true;
                    break;
                }
            }
            if ( !$canSee ) {
                RestoLogUtil::httpError(403, 'You are not allowed to access this catalog');
            }
        }

        $catalog = array(
            'stac_version' => STACAPI::STAC_VERSION,
            'id' => $segments[count($segments) -1 ],
            'title' => $parentAndChilds['parent']['title'] ?? '',
            'description' => $parentAndChilds['parent']['description'] ?? '',
            'type' => ucfirst($parentAndChilds['parent']['rtype'] ?? 'catalog'),
            'links' => array_merge(
                $this->getBaseLinks($segments),
                !empty($parentAndChilds['parent']['links']) ? array_merge($parentAndChilds['childs'], $parentAndChilds['parent']['links']) : $parentAndChilds['childs']
            )
        );

        // Add additional metadata
        foreach (array_keys($parentAndChilds['parent']) as $key ) {
            if ( !in_array($key, CatalogsFunctions::CATALOG_PROPERTIES) ){
                $catalog[$key] = $parentAndChilds['parent'][$key];
            }
        }
        
        return $this->context->core['useJSONLD'] ? JSONLDUtil::addDataCatalogMetadata($catalog) : $catalog;
    }

    /**
     * Process search on catalogs
     * 
     * @param array $segments
     * @param array $params
     * @return array
     * @throws Exception
     */ 
    private function processSearch($segments, $params)
    {
        // This is not possible
        if (count($segments) < 2) {
            RestoLogUtil::httpError(404);
        }

        array_pop($segments);
        $catalogs = $this->catalogsFunctions->getCatalogs(array(
            'id' => join('/', $segments),
            'q' => $params['q'] ?? null,
            'countCatalogs' => isset($params['_countCatalogs']) ? filter_var($params['_countCatalogs'], FILTER_VALIDATE_BOOLEAN) : $this->context->core['countCatalogs']
        ), false);
        
        if ( empty($catalogs) ) {
            RestoLogUtil::httpError(404);
        }

        if ( $catalogs[0]['visibility'] ) {
            if ( !$this->catalogsFunctions->canSeeCatalog($catalogs[0]['visibility'], $this->user) ) {
                RestoLogUtil::httpError(403, 'You are not allowed to access this catalog');
            }
        }
        
        $searchParams = array(
            'q' => $catalogs[0]['id']
        );

        foreach (array_keys($params) as $key) {
            if ($key !== 'segments') {
                $searchParams[$key] = $params[$key];
            }
        }

        return $this->search($searchParams, null);
    }

    /**
     * Process catalogs from addons i.e. not handled within resto.catalog table
     * 
     * @param array $segments
     * @param array $params
     * @return array
     */
    private function processAddons($segments, $params)
    {

        $nbOfSegments = count($segments);
        
        $viewClassName = 'View';
        if ($segments[0] === 'views' && isset($this->context->addons[$viewClassName])) {

            $view = new $viewClassName($this->context, $this->user);
            
            // Root
            if ($nbOfSegments === 1) {
                return $view->getViews(array(
                    'format' => 'stac'
                ));
            }
            // Individual view
            elseif ($nbOfSegments === 2) {
                return $view->getView(array_merge($this->context->query, array(
                    'viewId' => $segments[1],
                    'format' => 'stac'
                )));
            }
            else {
                RestoLogUtil::httpError(404);
            }
        }

        // SOSA special case
        else if ($segments[0] === 'concepts' && isset($this->context->addons['SOSA'])) {

            $skosClassName = 'SKOS';
            $skos = new $skosClassName($this->context, $this->user);
            
            // Root
            if ($nbOfSegments === 1) {
                return $skos->getConcepts($this->context->query);
            }
            // Individual concept
            elseif ($nbOfSegments === 2) {
                return $skos->getConcept(array_merge($this->context->query, array(
                    'conceptId' => $segments[1],
                )));
            }
            else {
                RestoLogUtil::httpError(404);
            }
        }

        return null;
    }


    /**
     * Process external catalog i.e. resolve url and proxify links to resto links
     * 
     * @param array $catalog
     * @return array
     */
    private function processExternalCatalog($catalog, $selfHref)
    {

        $externalCatalog = STACUtil::resolveEndpoint($catalog['stac_url_to_be_resolved'] ?? $catalog['stac_url']);
        
        // Replace first level links
        if ( isset($externalCatalog['links']) ) {
            $externalCatalog['links'] = $this->replaceLinks($catalog, $externalCatalog['links'], $selfHref);
        }

        // Collections case
        if ( isset($externalCatalog['collections']) ) {
            for ($i = 0, $ii = count($externalCatalog['collections']); $i < $ii; $i++) {
                if ( isset($externalCatalog['collections'][$i]['links']) ) {
                    $externalCatalog['collections'][$i]['links'] = $this->replaceLinks($catalog, $externalCatalog['collections'][$i]['links'], $selfHref);
                }
            }
        }

        // Features case
        if ( isset($externalCatalog['features']) ) {
            for ($i = 0, $ii = count($externalCatalog['features']); $i < $ii; $i++) {
                if ( isset($externalCatalog['features'][$i]['links']) ) {
                    $externalCatalog['features'][$i]['links'] = $this->replaceLinks($catalog, $externalCatalog['features'][$i]['links'], $selfHref);
                }
            }
        }

        return $externalCatalog;
       
    }

    /**
     * 
     * Replace links in external catalog with resto links i.e. act as a proxy
     * 
     * @param array $catalog
     * @param array $links
     * @param string $selfHref
     * @return array
     */
    private function replaceLinks($catalog, $links, $selfHref)
    {

        $correctUrl = substr($catalog['stac_url'], -1) === '/' ? substr($catalog['stac_url'], 0, strlen($catalog['stac_url']) - 1) : $catalog['stac_url'];
        
        // Hack if url includes a json file
        if ( substr($correctUrl, -5) === '.json' ) {
            $correctUrl = substr($correctUrl, 0, strrpos($correctUrl, '/'));
        }
        
        for ($i = 0, $ii = count($links); $i < $ii; $i++) {
            if ( isset($links[$i]['href']) ) {
                if ( $links[$i]['rel'] === 'root') {
                    $links[$i]['href'] = $this->context->core['baseUrl'];
                }
                else {
                    $links[$i]['href'] = str_replace($correctUrl, join('/', array($this->context->core['baseUrl'], 'catalogs', $catalog['id'])), $this->toAbsoluteUrl($links[$i]['href'], $selfHref));
                    if ( $links[$i]['rel'] === 'parent') {
                        $links[$i]['href'] = substr($links[$i]['href'],0,strrpos($links[$i]['href'],'/'));
                    }
                }
            }
        }
        return $links;
    }

    /**
     * Convert JSON query to queryParam
     * 
     * Very basic - not supporting CQL2 JSON only key/values
     *
     * @param array $jsonQuery
     */
    private function jsonQueryToKVP($jsonQuery)
    {

        $params = array();

        /*
         * Input collections should be an array of collection id strings
         */
        if ( isset($jsonQuery['collections']) ) {
            if ( !is_array($jsonQuery['collections']) ) {
                RestoLogUtil::httpError(400, 'Invalid collections parameter. Should be an array of strings');
            }
            $params['collections'] = join(',', $jsonQuery['collections']);
        }

        /*
         * Input bbox should be an array of 4 floats
         */
        if ( isset($jsonQuery['bbox']) ) {
            if ( !is_array($jsonQuery['bbox']) || count($jsonQuery['bbox']) !== 4 ) {
                RestoLogUtil::httpError(400, 'Invalid bbox parameter. Should be an array of 4 coordinates');
            }
            $params['bbox'] = join(',', $jsonQuery['bbox']);
        }

        /*
         * Input intersects should be a GeoJSON geometry object
         */
        if ( isset($jsonQuery['intersects']) ) {
            if ( !isset($jsonQuery['intersects']['type']) || !isset($jsonQuery['intersects']['coordinates']) ) {
                RestoLogUtil::httpError(400, 'Invalid intersects. Should be a GeoJSON geometry object');
            }
            $params['intersects'] = RestoGeometryUtil::geoJSONGeometryToWKT($jsonQuery['intersects']);
        }
        
        if ( isset($jsonQuery['datetime']) ) {
            $params['datetime'] = $jsonQuery['datetime'];
        }

        return $params;

    }

    /**
     * Return catalog childs 
     * 
     * @param array $catalogs
     * @param array $params Search parameters
     * @return array
     */
    private function getParentAndChilds($catalogs, $params)
    {

        $parentAndChilds = array(
            'parent' => $catalogs[0] ?? null,
            'childs' => array()
        );

        // Parent has no catalog childs, so its childs are item or items if if's a collection
        if ( count($catalogs) === 1 ) {

            if ( $catalogs[0]['rtype'] === 'collection' ) {
                $items = array(
                    'rel' => 'items',
                    'title' => 'All items',
                    'type' => RestoUtil::$contentTypes['geojson'],
                    'href' => $this->context->core['baseUrl'] . '/collections/' . substr($parentAndChilds['parent']['id'], strrpos($parentAndChilds['parent']['id'], '/') + 1) . '/items'
                );
                if ( isset($parentAndChilds['parent']['counters']) && $parentAndChilds['parent']['counters']['total'] > 0) {
                    $items['matched'] = $parentAndChilds['parent']['counters']['total'];
                }
                $parentAndChilds['childs'][] = $items;
            }
            else {
                $parentAndChilds['childs'] = $this->catalogsFunctions->getCatalogItems($parentAndChilds['parent']['id'], $this->context->core['baseUrl']);
            }
        }
        else {

            // Parent has an hashtag thus can have a rel="items" child to directly search for its contents
            if ( $parentAndChilds['parent']['level'] > 1 && $this->context->core['showItemsLink'] ) {
                $element = array(
                    'rel' => 'items',
                    'type' => RestoUtil::$contentTypes['geojson'],
                    'href' => $this->context->core['baseUrl'] . ( str_starts_with($catalogs[0]['id'], 'collections/') ? '/' : '/catalogs/') .  join('/', array_map('rawurlencode', explode('/', $parentAndChilds['parent']['id']))) . '/_',
                    'title' => 'All items'
                );
                if ( $parentAndChilds['parent']['counters']['total'] > 0 ) {
                    $element['matched'] = $parentAndChilds['parent']['counters']['total'];
                }
                $parentAndChilds['childs'][] = $element;
            }

            // Childs are 1 level above their parent catalog level
            for ($i = 1, $ii = count($catalogs); $i < $ii; $i++) {
                if ($catalogs[$i]['level'] === $parentAndChilds['parent']['level'] + 1) {
                    if (!isset($catalogs[$i]['link_properties'])) {
                        $catalogs[$i]['link_properties'] = array();
                    }
                    $element = array(
                        'rel' => $catalogs[$i]['link_properties']['rel'] ?? 'child',
                        'type' => $catalogs[$i]['link_properties']['type'] ?? RestoUtil::$contentTypes['json'],
                        'href' => $this->context->core['baseUrl'] . ( str_starts_with($catalogs[$i]['id'], 'collections/') ? '/' : '/catalogs/') .  join('/', array_map('rawurlencode', explode('/', $catalogs[$i]['id'])))
                    );
                    
                    if (  $catalogs[$i]['counters']['total'] > 0 ) {
                        $element['matched'] = $catalogs[$i]['counters']['total'];
                    }
                    if ( isset($catalogs[$i]['title']) ) {
                        $element['title'] = $catalogs[$i]['title'];
                    }
                    if ( isset($catalogs[$i]['description']) ) {
                        $element['description'] = $catalogs[$i]['description'];
                    }
                    foreach (array_keys($catalogs[$i]['link_properties']) as $key ) {
                        if ( !in_array($key, array('rel', 'type')) ){
                            $element[$key] = $catalogs[$i]['link_properties'][$key];
                        }
                    }
                    $parentAndChilds['childs'][] = $element;
                }
            }
        }
        
        return $parentAndChilds;

    }

    /**
     * Return self/root/parent links
     * 
     * @param array segments
     */
    private function getBaseLinks($segments = array())
    {
        
        array_unshift($segments, 'catalogs');

        $links = array(
            array(
                'rel' => 'self',
                'type' => RestoUtil::$contentTypes['json'],
                'href' => $this->context->core['baseUrl'] . '/' . join('/', array_map('rawurlencode', $segments))
            ),
            array(
                'rel' => 'root',
                'type' => RestoUtil::$contentTypes['json'],
                'href' => $this->context->core['baseUrl']
            )
        );

        array_pop($segments);
        $links[] = array(
            'rel' => 'parent',
            'type' => RestoUtil::$contentTypes['json'],
            'href' => $this->context->core['baseUrl'] . (count($segments) > 0 ? '/' . join('/', array_map('rawurlencode', $segments)) : '')
        );
        
        return $links;

    }

    /**
     * Get root links
     *
     * @param $params // Additional filtering parameters for catalog search on description and title
     * @return array
     */
    private function getRootCatalogLinks($params)
    {
        $links = array();

        $viewClassName = 'View';

        /*
         * Exposed views as STAC catalogs
         * Only displayed if at least one theme exists
         */
        if (isset($this->context->addons[$viewClassName])) {
            
            $stacLink = (new $viewClassName($this->context, $this->user))->getSTACRootLink();
            if (isset($stacLink) && $stacLink['matched'] > 0) {
                $links[] = $stacLink;
            }
        }

        // Get first level catalog
        $catalogs = $this->catalogsFunctions->getCatalogs(array(
            'level' => 1,
            'q' => $params['q'] ?? null,
            'countCatalogs' => isset($params['_countCatalogs']) ? filter_var($params['_countCatalogs'], FILTER_VALIDATE_BOOLEAN) : $this->context->core['countCatalogs']
        ), false);
        
        for ($i = 0, $ii = count($catalogs); $i < $ii; $i++) {

            if ($catalogs[$i]['id'] === 'collections') {
                continue;
            }

            if ( $catalogs[$i]['visibility'] ) {
                if ( !$this->catalogsFunctions->canSeeCatalog($catalogs[$i]['visibility'], $this->user) ) {
                    continue;
                }
            }
            
            $link = array(
                'id' => $catalogs[$i]['id'],
                'rel' => 'child',
                'type' => RestoUtil::$contentTypes['json'],
                'href' => $this->context->core['baseUrl'] . ( str_starts_with($catalogs[$i]['id'], 'collections/') ? '/' : '/catalogs/') . rawurlencode($catalogs[$i]['id'])
            );
            if ( $catalogs[$i]['counters']['total'] > 0 ) {
                $link['matched'] = $catalogs[$i]['counters']['total'];
            }
            if ( isset($catalogs[$i]['title']) ) {
                $link['title'] = $catalogs[$i]['title'];
            }
            if ( isset($catalogs[$i]['description']) ) {
                $link['description'] = $catalogs[$i]['description'];
            }
            if ( isset($catalogs[$i]['rtype']) ) {
                $link['resto:type'] = $catalogs[$i]['rtype'];
            }
            $links[] = $link;
            
        }
        
        return $links;
    }

    /**
     * Return path identifier from input catalog
     * 
     * @param array $catalog
     * @param string $parentId
     * @return string
     */
    private function getIdPath($catalog, $parentId)
    {

        if ( !isset($catalog['id']) ) {
            RestoLogUtil::httpError(400, 'Missing mandatory catalog id');
        }
        if ( count(explode('/', $catalog['id'])) >  1 ) {
            RestoLogUtil::httpError(400, 'Catalog id cannot contain a slash');
        }

        $parentIdInBody = '';
        $hasParentInBody = false;

        // Retrieve parent if any
        if ( isset($catalog['links']) && is_array($catalog['links']) ) {
            for ($i = 0, $ii = count($catalog['links']); $i < $ii; $i++ ) {
                if ( isset($catalog['links'][$i]['rel']) &&$catalog['links'][$i]['rel'] === 'parent' ) {
                    $hasParentInBody = true;
                    $theoricalUrl = $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_CATALOGS; 
                    $exploded = explode($theoricalUrl, $catalog['links'][$i]['href']);
                    if (count($exploded) !== 2) {
                        RestoLogUtil::httpError(400, 'Parent link is set but it\'s url is invalid - should starts with ' . $theoricalUrl);
                    }
                    $parentIdInBody = str_starts_with($exploded[1], '/') ? substr($exploded[1], 1) : $exploded[1];
                    break;
                }
            }
        }

        if ( $hasParentInBody ) {
            if ( isset($parentId) && $parentId !== $parentIdInBody ) {
                RestoLogUtil::httpError(400, 'The rel=parent catalog differs from the path ' . $parentId);
            }
            return $parentIdInBody . '/' . $catalog['id'];
        }

        return isset($parentId) ? $parentId . '/' . $catalog['id'] : $catalog['id'];

    }    

    /**
     * 
     * Convert relative $href to absolute url from self url
     * Do nothing if the input $href is already absolute
     * 
     * @param string $href
     * @param string $selfHref
     * @return string
     */
    private function toAbsoluteUrl($href, $selfHref)
    {
        $root = substr($selfHref, 0, strrpos($selfHref, '/'));
        
        if ( str_starts_with(strtolower($href), 'http') ) {
            return $href;
        }

        if ( str_starts_with($href, '../') ) {
            return substr($root, 0, strrpos($root, '/')) . substr($href, 2);
        }
        
        return $root . '/' . ltrim(ltrim($href, './'), '/');
    }

}
