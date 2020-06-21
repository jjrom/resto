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
 * RESTo Feature
 *
 *  @OA\Tag(
 *      name="Feature",
 *      description="A feature is an application object that represents a physical entity e.g. a building, a river, a person, a coverage taken by a a satellite. Practically, a resto feature is defined by a set of metadata including a geographical location (i.e. a (Multi)Point, a (Multi)LineString or a (Multi)Polygon). A feature always belongs to one and only one collection."
 *  )
 *
 *  @OA\Schema(
 *      schema="OutputFeature",
 *      description="Feature returned by resto",
 *      required={"type", "id", "geometry", "properties", "collection", "links", "assets"},
 *      @OA\Property(
 *          property="type",
 *          type="enum",
 *          enum={"Feature"},
 *          description="Always set to *feature*"
 *      ),
 *      @OA\Property(
 *          property="id",
 *          type="string",
 *          description="Feature identifier"
 *      ),
 *      @OA\Property(
 *          property="geometry",
 *          type="object",
 *          required={"type", "geometry"},
 *          description="Geometry definition",
 *          @OA\Property(
 *              property="type",
 *              type="enum",
 *              enum={"Point", "MultiPoint", "LineString", "MultiLineString", "Polygon", "MultiPolygon", "GeometryCollection"},
 *              description="Geometry type following GeoJSON specification"
 *          ),
 *          @OA\Property(
 *              property="coordinates",
 *              type="array",
 *              @OA\Items(
 *                  type="float",
 *              ),
 *              description="Geometry vertices following GeoJSON specification"
 *          )
 *      ),
 *      @OA\Property(
 *          property="collection",
 *          type="string",
 *          description="Collection identifier"
 *      ),
 *      @OA\Property(
 *          property="links",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/Link")
 *      ),
 *      @OA\Property(
 *          property="assets",
 *          type="object",
 *          @OA\JsonContent(
 *              @OA\Property(
 *                  property="thumbnail",
 *                  type="object",
 *                  @OA\Items(ref="#/components/schemas/Asset")
 *              )
 *          )
 *      ),
 *      @OA\Property(
 *          property="properties",
 *          type="object",
 *          description="Feature properties mainly based on *[OGC-13-026r8] OGC OpenSearch Extension for Earth Observation*. Only non null properties are returned",
 *          @OA\Property(
 *              property="title",
 *              type="string",
 *              description="A name given to the feature"
 *          ),
 *          @OA\Property(
 *              property="description",
 *              type="string",
 *              description="A descriptipon of the feature"
 *          ),
 *          @OA\Property(
 *              property="datetime",
 *              type="string",
 *              description="Start/end of feature life (e.g. start of acquisition for a satellite imagery) (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ/YYYY-MM-DD-THH:MM:SSZ)"
 *          ),
 *          @OA\Property(
 *              property="udpated",
 *              type="string",
 *              description="The date when the feature metadata was updated (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ)"
 *          ),
 *          @OA\Property(
 *              property="published",
 *              type="string",
 *              description="The date when the feature metadata was published (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ)"
 *          ),
 *          @OA\Property(
 *              property="hashtags",
 *              type="array",
 *              @OA\Items(
 *                  type="string"
 *              ),
 *              description="Array of hashtags attached to the feature"
 *          ),
 *          @OA\Property(
 *              property="centroid",
 *              type="object",
 *              @OA\Property(
 *                  property="type",
 *                  type="string",
 *                  description="Always set to *Point*"
 *              ),
 *              @OA\Property(
 *                  property="coordinates",
 *                  type="array",
 *                  description="Coordinates expressed in [longitude, latitude]",
 *                  @OA\Items(
 *                      type="float"
 *                  )
 *              ),
 *              description="Centroid of the feature"
 *          ),
 *          @OA\Property(
 *              property="likes",
 *              type="integer",
 *              description="Number of likes for this feature"
 *          ),
 *          @OA\Property(
 *              property="comments",
 *              type="integer",
 *              description="Number of comments on this feature"
 *          ),
 *          @OA\Property(
 *              property="owner",
 *              type="string",
 *              description="Owner of the feature i.e. user that created it"
 *          ),
 *          @OA\Property(
 *              property="status",
 *              type="integer",
 *              description="[Unused]"
 *          ),
 *          @OA\Property(
 *              property="liked",
 *              type="boolean",
 *              description="True if the user that requests the feature likes it"
 *          )
 *      ),
 *      example={
 *          "type": "Feature",
 *          "id": "b9eeaf68-5127-53e5-97ff-ddf44984ef56",
 *          "geometry": {
 *              "type": "Polygon",
 *              "coordinates": {
 *                  {
 *                      {
 *                          -16.34433,
 *                          -36.136821
 *                      },
 *                      {
 *                          -16.002576,
 *                           -36.14017
 *                      },
 *                      {
 *                          -16.003437,
 *                          -36.207726
 *                      },
 *                      {
 *                          -16.003437,
 *                          -36.207726
 *                      },
 *                      {
 *                         -16.073904,
 *                          -36.193064
 *                      },
 *                      {
 *                          -16.079613,
 *                          -36.194838
 *                      },
 *                      {
 *                          -16.343729,
 *                          -36.140707
 *                      },
 *                      {
 *                          -16.343453,
 *                          -36.137129
 *                      },
 *                      {
 *                          -16.34433,
 *                          -36.136821
 *                      }
 *                  }
 *              }
 *          },
 *          "collection": "S2",
 *          "properties": {
 *              "datetime": "2020-06-21T11:11:28.371000Z",
 *              "start_datetime": "2020-06-21T11:11:28.371000Z",
 *              "end_datetime": "2020-06-21T11:11:28.371000Z",
 *              "productIdentifier": "S2B_MSIL1C_20200621T111039_N0209_R008_T28HCE_20200621T132349",
 *              "updated": "2018-09-13T12:52:25.971969Z",
 *              "published": "2018-09-13T12:52:25.971969Z",
 *              "hashtags": {
 *                  "ocean:SouthAtlanticOcean:3358844",
 *                   "landcover:water",
 *                   "location:southern",
 *                   "season:winter",
 *                   "collection:S2",
 *                   "productType:REFLECTANCE",
 *                   "processingLevel:LEVEL1C",
 *                   "platform:S2B",
 *                   "instrument:MSI",
 *                   "year:2020",
 *                   "month:06",
 *                   "day:21"
 *               },
 *               "centroid": {
 *                  "type": "Point",
 *                  "coordinates": {
 *                      70.513407,
 *                      23.006623
 *                  }
 *              },
 *              "likes": 0,
 *              "comments": 0,
 *              "liked": false
 *          },
 *          "links": {
 *              {
 *                  "rel": "self",
 *                  "type": "application/geo+json",
 *                  "href": "https://tamn.snapplanet.io/collections/S2/items/af9f811b-f6b7-5dfc-ac43-c1d200a79088"
 *              }
 *          },
 *          "assets": {
 *              "thumbnail": {
 *                   "href": "https://roda.sentinel-hub.com/sentinel-s2-l1c/tiles/28/H/CE/2020/6/21/0/preview.jpg",
 *                   "type": "image/jpeg",
 *                   "role": "thumbnail"
 *               }
 *          }
 *      }
 *  )
 *
 *  @OA\Schema(
 *      schema="InputFeature",
 *      description="Feature ingested by resto",
 *      required={"type", "geometry", "properties"},
 *      @OA\Property(
 *          property="type",
 *          type="enum",
 *          enum={"Feature"},
 *          description="Always set to *feature*"
 *      ),
 *      @OA\Property(
 *          property="id",
 *          type="string",
 *          description="Feature identifier"
 *      ),
 *      @OA\Property(
 *          property="geometry",
 *          type="object",
 *          required={"type", "geometry"},
 *          description="Geometry definition",
 *          @OA\Property(
 *              property="type",
 *              type="enum",
 *              enum={"Point", "MultiPoint", "LineString", "MultiLineString", "Polygon", "MultiPolygon", "GeometryCollection"},
 *              description="Geometry type following GeoJSON specification"
 *          ),
 *          @OA\Property(
 *              property="coordinates",
 *              type="array",
 *              @OA\Items(
 *                  type="float",
 *              ),
 *              description="Geometry vertices following GeoJSON specification"
 *          )
 *      ),
 *      @OA\Property(
 *          property="properties",
 *          type="object",
 *          description="Feature properties mainly based on *[OGC-13-026r8] OGC OpenSearch Extension for Earth Observation*. Only non null properties are returned",
 *          @OA\Property(
 *              property="title",
 *              type="string",
 *              description="A name given to the feature"
 *          ),
 *          @OA\Property(
 *              property="description",
 *              type="string",
 *              description="Descritipon of the feature. Each hashtag within the description is indexed to speedup search"
 *          ),
 *          @OA\Property(
 *              property="productIdentifier",
 *              type="string",
 *              description="Original product identifier"
 *          ),
 *          @OA\Property(
 *              property="datetime",
 *              type="string",
 *              description="Start/end of feature life (e.g. start of acquisition for a satellite imagery) (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ/YYYY-MM-DD-THH:MM:SSZ)"
 *          ),
 *          @OA\Property(
 *              property="status",
 *              type="integer",
 *              description="[Unused]"
 *          )
 *      ),
 *      example={
 *          "type": "Feature",
 *          "geometry": {
 *              "type": "Polygon",
 *              "coordinates": {
 *                  {
 *                      {
 *                          -16.34433,
 *                          -36.136821
 *                      },
 *                      {
 *                          -16.002576,
 *                           -36.14017
 *                      },
 *                      {
 *                          -16.003437,
 *                          -36.207726
 *                      },
 *                      {
 *                          -16.003437,
 *                          -36.207726
 *                      },
 *                      {
 *                         -16.073904,
 *                          -36.193064
 *                      },
 *                      {
 *                          -16.079613,
 *                          -36.194838
 *                      },
 *                      {
 *                          -16.343729,
 *                          -36.140707
 *                      },
 *                      {
 *                          -16.343453,
 *                          -36.137129
 *                      },
 *                      {
 *                          -16.34433,
 *                          -36.136821
 *                      }
 *                  }
 *              }
 *          },
 *          "properties": {
 *              "productIdentifier": "S2B_MSIL1C_20200621T111039_N0209_R008_T28HCE_20200621T132349",
 *              "datetime": "2020-06-21T11:11:28.371000Z"
 *          }
 *      }
 *  )
 *
 */
class RestoFeature
{

    /*
     * Feature unique identifier
     */
    public $id;

    /*
     * Context
     */
    public $context;

    /*
     * User
     */
    public $user;

    /*
     * Parent collection
     */
    public $collection;

    /*
     * Feature array
     */
    private $featureArray;

    /**
     * Constructor
     *
     * @param RestoResto $context : Resto Context
     * @param RestoUser $user : Resto user
     * @param array $options : array(
     *                              'featureId':// string
     *                              'featureArray':// array(),
     *                              'collection: // RestoCollection
     *                         )
     *
     * Note that 'featureArray' should only be called by RestoFeatureCollection
     */
    public function __construct($context, $user, $options)
    {
        $this->context = $context;
        $this->user = $user;
        $this->load($options);
    }

    /**
     * Return true if Feature is valid, false otherwise
     */
    public function isValid()
    {
        return isset($this->id) ? true : false;
    }

    /**
     * Output product description as a PHP array
     */
    public function toArray()
    {
        return $this->featureArray;
    }

    /**
     * Feature for output
     */
    public function toPublicArray()
    {
        return (isset($this->collection) ? $this->collection->model : new DefaultModel())->remap($this->featureArray, $this->collection);
    }

    /**
     * Output product description as a GeoJSON Feature
     *
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false)
    {
        return json_encode($this->toPublicArray(), $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES);
    }

    /**
     * Output product description as an ATOM feed
     */
    public function toATOM()
    {

        $publicFeatureArray = $this->toPublicArray();

        /*
         * Initialize ATOM feed
         */
        $atomFeed = new ATOMFeed($publicFeatureArray['id'], $publicFeatureArray['properties']['title'], 'resto feature', isset($this->collection) ? $this->collection->model : new DefaultModel());

        /*
         * Entry for feature
         */
        $atomFeed->addEntry($publicFeatureArray, $this->context);

        /*
         * Return ATOM result
         */
        return $atomFeed->toString();
    }

    /**
     * Set feature either from input description or from database
     *
     * @param array $options
     */
    private function load($options)
    {

        /*
         * Set collection
         */
        $this->collection = $options['collection'] ?? null;
        
        /*
         * Load from database
         */
        if (isset($options['featureId'])) {
            $this->featureArray = (new FeaturesFunctions($this->context->dbDriver))->getFeatureDescription(
                $this->context,
                $this->user,
                $options['featureId'],
                $this->collection,
                $options['fields'] ?? "_all"
            );
        }
        /*
         * ...or from input array
         */
        else {
            $this->featureArray = $options['featureArray'];
        }

        /*
         * Empty feature or feature is not in input collection
         */
        if (empty($this->featureArray) || (isset($this->collection) && $this->collection->id !== $this->featureArray['collection'])) {
            $this->id = null;
            return;
        } 
        
        $this->id = $this->featureArray['id'];
        
    }

}
