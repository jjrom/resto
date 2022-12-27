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
 */
class STAC extends RestoAddOn
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
        'https://api.stacspec.org/v1.0.0-rc.1/core',
        'https://api.stacspec.org/v1.0.0-rc.1/collections',
        'https://api.stacspec.org/v1.0.0-rc.1/ogcapi-features',
        'https://api.stacspec.org/v1.0.0-rc.1/browseable',
        'https://api.stacspec.org/v1.0.0-rc.1/children',
        'https://api.stacspec.org/v1.0.0-rc.1/item-search',
        'https://api.stacspec.org/v1.0.0-rc.1/item-search#fields',
        // Unsupported
        //'https://api.stacspec.org/v1.0.0-rc.1/item-search#query',
        'https://api.stacspec.org/v1.0.0-rc.1/item-search#sort',
        'https://api.stacspec.org/v1.0.0-rc.1/item-search#filter',

        'http://www.opengis.net/spec/ogcapi_common-2/1.0/conf/collections',

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

    /**
     * Add-on version
     */
    public $version = '1.0.5';

    /*
     * Catalog title
     */
    public $title;

    /*
     * Catalog description
     */
    public $description = 'Available catalogs';

    /*
     * Links
     */
    public $links = array();

    /*
     * FeatureCollection
     */
    public $featureCollection = null;

    /*
     * STAC Util
     */
    private $stacUtil = null;

    /*
     * Url segments
     */
    private $segments = array();

    /**
     * Constructor
     *
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user)
    {
        parent::__construct($context, $user);
        $this->stacUtil = new STACUtil($context, $user);

        // Ensure valid options
        $this->options['minMatch'] = isset($this->options['minMatch']) && is_int($this->options['minMatch']) ? $this->options['minMatch'] : 0;

    }

    /**
     * Return an asset href within an HTTP 301 Redirect message
     * This trick is used to store download external asset statistics
     * 
     * @param array $params
     */
    public function getAsset($params) {

        $url = base64_decode($params['urlInBase64']);

        /*
         * Should be a valid url
         */
        if ( !$url || strpos($url, 'http') !== 0 )  {
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
     * Return a STAC catalog from facet
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
        if (!isset($params['segments'])) {
            return RestoLogUtil::httpError(404);
        }

        $this->segments = $params['segments'];

        $result = $this->load($params);
        
        return isset($result->featureCollection) ? $result->featureCollection : $result;

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

        $links = $this->stacUtil->getRootCatalogLinks();
        for ($i = 0, $ii = count($links); $i < $ii; $i++) {
            if ($links[$i]['rel'] == 'child') {
                try {
                    $response = $router->process('GET', parse_url($links[$i]['href'])['path'], array());
                }
                catch (Exception $e) {
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
                    'href' => $this->context->core['baseUrl'] . '/children'
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
     * Return the list of children catalog
     * (see https://github.com/radiantearth/stac-api-spec/tree/main/children)
     * 
     *    @OA\Get(
     *      path="/queryables",
     *      summary="Queryables for STAC API",
     *      description="Queryable names for the STAC API Item Search filter."",
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
            '$id' => $this->context->core['baseUrl'] . '/queryables',
            'type' => 'object',
            'title' => 'Queryables for Example STAC API',
            'description' => 'Queryable names for the example STAC API Item Search filter.',
            // Get common queryables (/queryables) or per collection (/collections/{collectionId}/queryables)  
            'properties' => (isset($params['collectionId']) ? ((new RestoCollection($params['collectionId'], $this->context, $this->user))->load())->model : new DefaultModel())->getQueryables(),
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
     *          description="Limit search to owner's features",
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
        if ( isset($params['collections']) )
        {
            $collections = array_map('trim', explode(',', $params['collections']));
            if  ( count($collections) === 1 ) {
                $params['collectionId'] = $params['collections'];
                unset($params['collections']);
                return (new RestoCollection($params['collectionId'], $this->context, $this->user))->load()->search($params);
            }
        }

        $restoCollections = (new RestoCollections($this->context, $this->user))->load($params);

        /* [TODO] Faire un UNION sur les collections
        if ( !isset($model) ) {
            $schemaNames = array();
            foreach (array_keys($restoCollections->collections) as $collectionId) {
                if ( !in_array($restoCollections->collections[$collectionId]->model->dbParams['tablePrefix'], $schemaNames) ) {
                    $schemaNames[] = $restoCollections->collections[$collectionId]-model->dbParams['tablePrefix'];
                }   
            }
        }
        */

        return $restoCollections->search($model, $params);
    }

    /**
     * Output catalog description as an array
     *
     */
    public function toArray()
    {
        $nbOfSegments = count($this->segments);
        $exploded = $nbOfSegments > 0 ? explode(':', array_slice($this->segments, -1)[0]) : array('root');
        return array(
            'id' => $exploded[count($exploded) > 1 ? 1 : 0],
            'type' => 'Catalog',
            'title' => $this->title,
            'description' => $this->description,
            'links' => array_merge(
                array(
                    array(
                        'rel' => 'self',
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $this->context->core['baseUrl'] . '/catalogs' . ($nbOfSegments > 0 ? '/' . join('/', $this->segments) : '')
                    ),
                    array(
                        'rel' => 'root',
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $this->context->core['baseUrl']
                    )
                ), 
                $this->links ?? array()    
            ),
            'stac_version' => STAC::STAC_VERSION
        );

    }

    /**
     * Output collection description as a JSON stream
     *
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false)
    {
        return json_encode($this->toArray(), $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES);
    }


    /**
     * Load catalog from database
     * Return 404 if  is not found
     *
     * @param array $params
     * @return This object
     */
    private function load($params = array())
    {
        
        $nbOfSegments = count($this->segments);

        // Root
        if ( $nbOfSegments === 0 )
        {
            $this->link = $this->stacUtil->getRootCatalogLinks();
        }

        // View special case
        else if ($this->segments[0] === 'views' && isset($this->context->addons['View']))
        {
            
            $view = new View($this->context, $this->user);
            
            // Root
            if ($nbOfSegments === 1)
            {
                $viewCatalog = $view->getViews(array(
                    'format' => 'stac'
                ));
                $this->title = $viewCatalog['title'];
                $this->description = $viewCatalog['description'];
                $this->links = $viewCatalog['links'];
            }
            // Individual view
            else if ($nbOfSegments === 2)
            {
                return $view->getView(array_merge($this->context->query, array(
                    'viewId' => $this->segments[1],
                    'format' => 'stac'
                )));
            }
            // Individual views
            else
            {
                return RestoLogUtil::httpError(404);
            }

        }

        // SOSA special case
        else if ($this->segments[0] === 'concepts' && isset($this->context->addons['SOSA']))
        {

            $skos = new SKOS($this->context, $this->user);
            
            // Root
            if ($nbOfSegments === 1)
            {
                return $skos->getConcepts($this->context->query);
            }
            // Individual concept
            else if ($nbOfSegments === 2)
            {
                return $skos->getConcept(array_merge($this->context->query, array(
                    'conceptId' => $this->segments[1],
                )));
            }
            // Nothing found
            else
            {
                return RestoLogUtil::httpError(404);
            }

        }

        // Classifications
        else if ($this->segments[0] === 'classifications')
        {
            
            // Root
            if ($nbOfSegments === 1)
            {
                $this->setClassificationsLinks(null);
            }

            // Two segments (except landcover)
            else if ($nbOfSegments === 2 && $this->segments[1] !== 'landcover')
            {
                $this->setClassificationsLinks($this->segments[1]);
            }

            // Otherwise compute links from facets
            else
            {
                $this->setCatalogsLinksFromFacet($params);
            }

        }

        // Hashtags
        else if ($this->segments[0] === 'hashtags' || $this->segments[0] === 'catalogs')
        {
            $this->setCatalogsLinksFromFacet($params);
        }

        // Themes
        else if ($this->segments[0] === 'themes')
        {

            // Root
            if ($nbOfSegments === 1)
            {
                $this->title = 'Themes';
                $this->description = 'List of collection per theme';
                $this->links = array_merge($this->links, $this->stacUtil->getThemesRootLinks());
            }

            // Two segments (except landcover)
            else if ($nbOfSegments === 2)
            {
                $this->setThemesLinks();
            }
            
            else {
                return RestoLogUtil::httpError(404);
            }
        }

        // Not found
        else {
            return RestoLogUtil::httpError(404);
        }
        
        return $this;
    }

    /**
     * Set links array for a given theme
     *
     * @return array
     */
    private function setThemesLinks()
    {

        $this->title = $this->segments[1];
        $this->description = 'Collections for theme **' . $this->segments[1] . '**';

        // Load collections
        $collections = (new RestoCollections($this->context, $this->user))->load();
        
        $candidates = array();
        foreach (array_values($collections->collections) as $collectionContent) {
            if ( isset($collectionContent->keywords) ) {
                for ($i = count($collectionContent->keywords); $i--;) {
                    $splitted = explode(':', $collectionContent->keywords[$i]);
                    if (count($splitted) > 1 && $splitted[0] === 'label' && $splitted[1] === $this->segments[1]) {
                        $collectionArray = $collectionContent->toArray();
                        $this->links[] = array(
                            'rel' => 'child',
                            'title' => $collectionArray['title'],
                            'description' => $collectionArray['description'],
                            'type' => RestoUtil::$contentTypes['json'],
                            'href' => $this->context->core['baseUrl'] . '/collections/' . $collectionContent->id,
                            'roles' => array(
                                'collection'
                            )
                        );
                        $candidates[] = $collectionContent->id;
                        break;
                    }
                }        
            }
        }

        // No collection matches the themes => 404
        if (count($candidates) === 0) {
            return RestoLogUtil::httpError(404);
        }

        // Add a STAC search on matching collections
        $this->links[] = array(
            'rel' => 'items',
            'title' => $splitted[1],
            'type' => RestoUtil::$contentTypes['json'],
            'href' => $this->context->core['baseUrl'] . '/search?collections=' . join(',', $candidates)
        );
        
    }

    /**
     * Set classifications links
     *
     * @param string $root
     * @return array
     */
    private function setClassificationsLinks($root)
    {

        $facets = $this->stacUtil->getFacets($this->options['minMatch']);
        $target = isset($root) && isset($facets['classifications'][$root]) ? $facets['classifications'][$root] : $facets['classifications'];

        if ( isset($target) ) {

            foreach (array_keys($target) as $key) {
                $this->links[] = array(
                    'rel' => 'child',
                    'title' => ucfirst($key),
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl'] . '/catalogs/classifications/' . (isset($root) ? $root . '/' : '') . $key
                );
            }

        }
       
        // Set minimalist description
        $this->title = isset($root) ? ucfirst($root) : 'Classifications';
        $this->description = isset($root) ? 'Automatic classification of features on facet ' . ucfirst($root) : 'Automatic classification of features';
    
    }

    /**
     * Initialize child catalogs from facet
     *
     * @param array $params
     * @return array
     */
    private function setCatalogsLinksFromFacet($params)
    {
        
        $nbOfSegments = count($this->segments);
        $leafValue = $this->segments[$nbOfSegments - 1];
        
        /*
         * Special case for '_' leafValue => compute FeatureCollection of parents
         */
        if ($leafValue === '_') {

            // This is not possible
            if ($nbOfSegments < 2) {
                return RestoLogUtil::httpError(404);
            }

            return $this->setFeatureCollection($this->segments[$nbOfSegments - 2], $params);
            
        }
        
        // Default segments structure starts with 'classifications/xxxx' - so start at position 3
        $leafPosition = 3;
    
        $where = 'type=$1';
        $whereValues = array(
            $leafValue
        );
        
        // Hashtags special case
        if ( $this->segments[0] === 'hashtags' || $this->segments[0] === 'catalogs' ) {

            // 'hastags' - so start at position 1
            $leafPosition = 1;

            // In database keyword is 'hashtag' not 'hashtags'
            if ($nbOfSegments === 1) {
                $leafValue = substr($this->segments[0], 0, -1);
                $whereValues = array(
                    $leafValue
                );
            }

            // Hack for catalog - force a hierarchy
            if ( $this->segments[0] === 'catalogs' ) {
                $where = $where . ' AND pid=$2';
                $whereValues[] = 'root';
            }
            
        }
        
        // Hack for landcover...
        else if ( $this->segments[1] === 'landcover' ) {
            
            // 'hashtags' - so start at position 1
            $leafPosition = 2;
            
            if ( $nbOfSegments === 2 ) {
                $where = 'type LIKE $1';
                $whereValues = array(
                    'landcover:%'
                );
            }

        }   

        /*
         * Get description from parent
         */
        $this->setTitleAndDescription($this->segments[$nbOfSegments - 1]);

        try {

            // First get type or pid
            // [TODO]  Return /search items instead of child for high number of results ?
            // $results = $this->context->dbDriver->pQuery('SELECT id, value, isleaf, sum(counter) as matched FROM ' . $this->context->dbDriver->targetSchema . '.facet WHERE ' . ($nbOfSegments === 1 ? 'type LIKE $1' : 'pid=$1' ) . ' GROUP BY id,value,isleaf ORDER BY matched DESC', array(
            $results = $this->context->dbDriver->pQuery('SELECT id, value, pid, isleaf, sum(counter) as matched FROM ' . $this->context->dbDriver->targetSchema . '.facet WHERE ' . ($nbOfSegments === $leafPosition ? $where : 'pid=$1' ) . ' GROUP BY id, value, pid, isleaf ORDER BY value ASC', $nbOfSegments === $leafPosition ? $whereValues : array($whereValues[0])); 

            if (!$results) {
                throw new Exception();
            }

            // No Results - either a wrong path or a leaf facet (except for hashtag)
            if (pg_num_rows($results) === 0) {
                //return $this->setItemsLinks($title, $leafValue);
                if ( !in_array($leafValue, array('hashtag')) && $nbOfSegments > 1) {
                    return $this->setFeatureCollection($leafValue, $params);
                }
            }

            // Add parent link
            if ($nbOfSegments > 1) {
                $this->links[] = array(
                    'rel' => 'parent',
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl'] . '/catalogs/' . join('/', array_slice($this->segments, 0, -1))
                );
            }
            
            $searchIsSet = false;
            while ($result = pg_fetch_assoc($results)) {
                
                // Add search link for first pid if not root
                if (!$searchIsSet && $result['pid'] !== 'root') {
                    $this->links[] = array(
                        'rel' => 'items',
                        'title' => $this->title,
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $this->context->core['baseUrl'] . '/catalogs/' . join('/', $this->segments) . '/_'
                    );
                    $searchIsSet = true;
                }
                
                $matched = (integer) $result['matched'];
                if ($matched > $this->options['minMatch'])
                {
                    $link = array(
                        'rel' => ((integer) $result['isleaf']) === 1 ? 'items' : 'child',
                        'title' => $result['value'],
                        'matched' => $matched,
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $this->context->core['baseUrl'] . '/catalogs/' . join('/', $this->segments) . '/' . $result['id']
                    );

                    // Add a geouid info if present
                    $exploded = explode(':', $result['id']);
                    if (count($exploded) === 3 && ctype_digit($exploded[2])) {
                        $link['geouid'] = (integer) $exploded[2];
                    }
                    
                    $this->links[] = $link;
                }
                
            }
            
        } catch (Exception $e) {
            // Keep going
        }

    }

    /**
     * Initialize child items
     *
     * @param string $hashtag
     * @param array $params
     * 
     * @return array
     */
    private function setFeatureCollection($hashtag, $params)
    {
        $searchParams = array(
            'q' => '#' . $hashtag
        );

        foreach (array_keys($params) as $key) {
            if ($key !== 'segments') {
                $searchParams[$key] = $params[$key];
            }
        }

        $this->context->query['fields'] = '_all';

        return $this->featureCollection = (new RestoCollections($this->context, $this->user))->load()->search(null, $searchParams);
    }

    /**
     * Get title and description from parentId 
     * 
     * @param string $facetId
     */
    private function setTitleAndDescription($facetId)
    {

        // Default
        $titleParts = explode(':', $facetId);
        $title = ucfirst(count($titleParts) > 1 ? $titleParts[1] : $titleParts[0]);
        $this->title = $title;
        $this->description = 'Search on ' . $title;

        try {
            $results = $this->context->dbDriver->pQuery('SELECT value, description FROM ' . $this->context->dbDriver->targetSchema . '.facet WHERE normalize(id)=normalize($1)', array($facetId)); 
            if (!$results) {
                throw new Exception();
            }
            $result = pg_fetch_assoc($results);
            if ( isset($result) ) {
                $this->description = $result['description'] ?? $this->description;
                $this->title = $result['value'] ?? $this->title;
            }
        } catch (Exception $e) {
            // Keep going
        }
        
    }

    /**
     * Convert JSON query to queryParam
     * 
     * @param array $jsonQuery
     */
    private function jsonQueryToKVP($jsonQuery) {
        return array(
            'query' => 'TODO'
        );
    }

}
