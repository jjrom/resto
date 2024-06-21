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
     *          "eo:bands": {
     *              0
     *          }
     *      }
     * )
     */

    /*
     * STAC version
     */
    const STAC_VERSION = '1.0.0';

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
        'https://api.stacspec.org/v1.0.0-rc.3/item-search#filter',

        'http://www.opengis.net/spec/ogcapi_common-2/1.0/conf/collections',

        'https://api.stacspec.org/v1.0.0/ogcapi-features#fields',
        'https://api.stacspec.org/v1.0.0/ogcapi-features#sort',
        'https://api.stacspec.org/v1.0.0/ogcapi-features#filter',

        'http://www.opengis.net/spec/ogcapi-features-1/1.0/conf/core',
        'http://www.opengis.net/spec/ogcapi-features-1/1.0/conf/geojson',
        'http://www.opengis.net/spec/ogcapi-features-1/1.0/conf/oas30',
        'http://www.opengis.net/spec/ogcapi-features-1/1.0/conf/html',
        'http://www.opengis.net/spec/ogcapi-features-3/1.0/conf/filter',
        'http://www.opengis.net/spec/ogcapi-features-3/1.0/conf/features-filter',

        'http://www.opengis.net/spec/cql2/1.0/conf/basic-cql2',
        'http://www.opengis.net/spec/cql2/1.0/conf/cql2-text',
        'http://www.opengis.net/spec/cql2/1.0/conf/basic-spatial-operators'
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
            return array(
                'stac_version' => STACAPI::STAC_VERSION,
                'id' => 'catalogs',
                'type' => 'Catalog',
                'title' => 'Catalogs',
                'description' => 'List of available catalogs',
                'links' => array_merge(
                    $this->getBaseLinks(),
                    $this->getRootCatalogLinks($this->context->core['catalogMinMatch'])
                )
            );
        }

        // This is /catalogs/*
        return  $this->processPath($params['segments'], $params);
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
        
        if (!$this->user->hasRightsTo(RestoUser::CREATE_CATALOG)) {
            return RestoLogUtil::httpError(403);
        }

        /*
         * Check mandatory properties
         */
        /*if ( isset($body['stac_version']) ) {
            return RestoLogUtil::httpError(400, 'Missing mandatory catalog stac_version - should be set to ' . STACAPI::STAC_VERSION );
        }*/
        if ( !isset($body['id']) ) {
            return RestoLogUtil::httpError(400, 'Missing mandatory catalog id');
        }
        if ( !isset($body['description']) ) {
            return RestoLogUtil::httpError(400, 'Missing mandatory description');
        }
        if ( !isset($body['links']) ) {
            $body['links'] = array();
        }

        /*
         * Convert input catalog to resto:catalog
         * i.e. add rtype and hashtag properties and convert id to a path
         */
        $body['rtype'] = 'catalog';
        $body['hashtag'] = 'catalog' . RestoConstants::TAG_SEPARATOR . $body['id'];
        $body['id'] = $this->getIdPath($body);

        if ($this->catalogsFunctions->getCatalog($body['id']) !== null) {
            RestoLogUtil::httpError(409, 'Catalog ' . $body['id'] . ' already exists');
        }
        $baseUrl = $this->context->core['baseUrl'];
        return RestoLogUtil::success('Catalog added', $this->catalogsFunctions->storeCatalog($body, $this->user->profile['id'], $baseUrl, null, null));

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
            'id' => join('/', $params['segments'])
        ), true);
        
        if ( count($catalogs) === 0 ){
            RestoLogUtil::httpError(404);
        }


        // If user has not the right to update catalog then 403
        if ( !$this->user->hasRightsTo(RestoUser::UPDATE_CATALOG, array('catalog' => $catalogs[0])) ) {
            return RestoLogUtil::httpError(403);
        }

        $updatable = array('title', 'description', 'owner');
        for ($i = count($updatable); $i--;) {
            if ( isset($body[$updatable[$i]]) ) {
                $catalogs[0][$updatable[$i]] = $body[$updatable[$i]];
            }    
        }
        
        return RestoLogUtil::success('Catalog updated', $this->catalogsFunctions->updateCatalog($catalogs[0]));
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
     *      @OA\Parameter(
     *         name="force",
     *         in="query",
     *         description="Force catalog removal even if this catalog has child. In this case, catalogs childs are attached to the remove catalog parent",
     *         @OA\Schema(
     *             type="boolean"
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
            'id' => join('/', $params['segments'])
        ), true);
        
        if ( count($catalogs) === 0 ){
            RestoLogUtil::httpError(404);
        }

        // If user has not the right to delete catalog then 403
        if ( !$this->user->hasRightsTo(RestoUser::DELETE_CATALOG, array('catalog' => $catalogs[0])) ) {
            return RestoLogUtil::httpError(403);
        }
        
        // If catalog has childs it cannot be removed
        for ($i = 1, $ii = count($catalogs); $i < $ii; $i--) {
            if (isset($params['force']) && filter_var($params['force'], FILTER_VALIDATE_BOOLEAN) === true) {
                return RestoLogUtil::httpError(400, 'TODO - force removal of non empty catalog is not implemented');
            }
            else {
                return RestoLogUtil::httpError(400, 'The catalog cannot be deleted because it has ' . (count($catalogs) - 1) . ' childs');
            }    
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

        $links = $this->getRootCatalogLinks($this->context->core['catalogMinMatch']);
        for ($i = 0, $ii = count($links); $i < $ii; $i++) {
            if ($links[$i]['rel'] == 'child') {
                try {
                    $response = $router->process('GET', parse_url($links[$i]['href'])['path'], array());
                } catch (Exception $e) {
                    continue;
                }
                if (isset($response)) {
                    $childs[] = $response->toArray();
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
                return RestoLogUtil::httpError(400, 'Unknown model ' . $params['model']);
            }
            $model = new $params['model']();
        }
        
        // [STAC] Only one of either intersects or bbox should be specified. If both are specified, a 400 Bad Request response should be returned.
        if (isset($params['intersects']) && isset($params['bbox'])) {
            return RestoLogUtil::httpError(400, 'Only one of either intersects or bbox should be specified');
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

            // This is not possible
            if (count($segments) < 2) {
                return RestoLogUtil::httpError(404);
            }

            array_pop($segments);
            $catalogs = $this->catalogsFunctions->getCatalogs(array(
                'id' => join('/', $segments)
            ));
    
            if ( empty($catalogs) || !$catalogs[0]['hashtag'] ) {
                return RestoLogUtil::httpError(404);
            }
            
            $searchParams = array(
                'q' => '#' . $catalogs[0]['hashtag']
            );
    
            foreach (array_keys($params) as $key) {
                if ($key !== 'segments') {
                    $searchParams[$key] = $params[$key];
                }
            }

            return $this->search($searchParams, null);

        }
        
        // The path is the catalog identifier
        $parentAndChilds = $this->getParentAndChilds(join('/', $segments));
        return array(
            'stac_version' => STACAPI::STAC_VERSION,
            'id' => $segments[count($segments) -1 ],
            'title' => $parentAndChilds['parent']['title'] ?? '',
            'description' => $parentAndChilds['parent']['description'] ?? '',
            'type' => 'Catalog',
            'links' => array_merge(
                $this->getBaseLinks($segments),
                $parentAndChilds['childs']
            )
        );

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
        
        if ($segments[0] === 'views' && isset($this->context->addons['View'])) {

            $view = new View($this->context, $this->user);
            
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
                return RestoLogUtil::httpError(404);
            }
        }

        // SOSA special case
        else if ($segments[0] === 'concepts' && isset($this->context->addons['SOSA'])) {
            $skos = new SKOS($this->context, $this->user);
            
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
                return RestoLogUtil::httpError(404);
            }
        }

        return null;
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
                return RestoLogUtil::httpError(400, 'Invalid collections parameter. Should be an array of strings');
            }
            $params['collections'] = join(',', $jsonQuery['collections']);
        }

        /*
         * Input bbox should be an array of 4 floats
         */
        if ( isset($jsonQuery['bbox']) ) {
            if ( is_array($jsonQuery['bbox']) || count($jsonQuery['bbox']) !== 4 ) {
                return RestoLogUtil::httpError(400, 'Invalid bbox parameter. Should be an array of 4 coordinates');
            }
            $params['bbox'] = join(',', $jsonQuery['bbox']);
        }

        /*
         * Input intersects should be a GeoJSON geometry object
         */
        if ( isset($jsonQuery['intersects']) ) {
            if ( !isset($jsonQuery['intersects']['type']) || !isset($jsonQuery['intersects']['coordinates']) ) {
                return RestoLogUtil::httpError(400, 'Invalid intersects. Should be a GeoJSON geometry object');
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
     * @param string $catalogId
     * @return array
     */
    private function getParentAndChilds($catalogId)
    {

        // Get catalogs - first one is $catalogId, other its childs
        $catalogs = $this->catalogsFunctions->getCatalogs(array(
            'id' => $catalogId
        ), true);

        $parentAndChilds = array(
            'parent' => $catalogs[0],
            'childs' => array()
        );

        // Parent has no catalog childs, so its childs are item
        if ( count($catalogs) === 1 ) {
            $parentAndChilds['childs'] = $this->catalogsFunctions->getCatalogItems($parentAndChilds['parent']['id'], $this->context->core['baseUrl']);
        }
        else {

            // Parent has an hashtag thus can have a rel="items" child to directly search for its contents
            if ( isset($parentAndChilds['parent']['hashtag']) ) {
                $parentAndChilds['childs'][] = array(
                    'rel' => 'items',
                    'title' => $parentAndChilds['parent']['title'],
                    'type' => RestoUtil::$contentTypes['geojson'],
                    'href' => $this->context->core['baseUrl'] . '/catalogs/' .  join('/', array_map('rawurlencode', explode('/', $parentAndChilds['parent']['id']))) . '/_',
                    'matched' => $parentAndChilds['parent']['counters']['total']
                );
            }

            // Childs are 1 level above their parent catalog level
            for ($i = 1, $ii = count($catalogs); $i < $ii; $i++) {
                if ($catalogs[$i]['level'] === $parentAndChilds['parent']['level'] + 1) {
                    $parentAndChilds['childs'][] = array(
                        'rel' => 'child',
                        'title' => $catalogs[$i]['title'],
                        'description' => $catalogs[$i]['description'] ?? '',
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $this->context->core['baseUrl'] . '/catalogs/' .  join('/', array_map('rawurlencode', explode('/', $catalogs[$i]['id']))),
                        'matched' => $catalogs[$i]['counters']['total']
                    );
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
     * @return array
     */
    private function getRootCatalogLinks()
    {
        $links = array();

        /*
         * Exposed views as STAC catalogs
         * Only displayed if at least one theme exists
         */
        if (isset($this->context->addons['View'])) {
            $stacLink = (new View($this->context, $this->user))->getSTACRootLink();
            if (isset($stacLink) && $stacLink['matched'] > 0) {
                $links[] = $stacLink;
            }
        }

        // Get first level catalog
        $catalogs = $this->catalogsFunctions->getCatalogs(array(
            'level' => 1
        ));

        for ($i = 0, $ii = count($catalogs); $i < $ii; $i++) {

            // Returns only catalogs with count >= minMath
            if ($catalogs[$i]['counters']['total'] >= $this->context->core['catalogMinMatch']) {
                $link = array(
                    'rel' => 'child',
                    'title' => $catalogs[$i]['title'],
                    'description' => $catalogs[$i]['description'] ?? '',
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl'] . '/catalogs/' . rawurlencode($catalogs[$i]['id']),
                    'matched' => $catalogs[$i]['counters']['total']
                );
                if ($catalogs[$i]['id'] === 'collections') {
                    $link['roles'] = array('collections');
                }
                $links[] = $link;
            }
            
        }
        
        return $links;
    }

    /**
     * Return path identifier from input catlaog
     * 
     * @param array $catalog
     * @return string
     */
    private function getIdPath($catalog)
    {

        $parentId = '';

        // Retrieve parent if any
        for ($i = 0, $ii = count($catalog['links']); $i < $ii; $i++ ) {
            if ( isset($catalog['links'][$i]['rel']) &&$catalog['links'][$i]['rel'] === 'parent' ) {
                $theoricalUrl = $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_CATALOGS; 
                $exploded = explode($theoricalUrl, $catalog['links'][$i]['href']);
                if (count($exploded) !== 2) {
                    return RestoLogUtil::httpError(400, 'Parent link is set but it\'s url is invalid - should starts with ' . $theoricalUrl);
                }
                $parentId = str_starts_with($exploded[1], '/') ? substr($exploded[1], 1) : $exploded[1];
                break;
            }
        }

        return ($parentId === '' ? '' : $parentId . '/') . $catalog['id'];

    }    

}
