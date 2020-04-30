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
 *      required={"type", "id", "geometry", "properties"},
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
 *              property="collection",
 *              type="string",
 *              description="Name of the features collection"
 *          ),
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
 *              property="startDate",
 *              type="string",
 *              description="Start of feature life (e.g. start of acquisition for a satellite imagery) (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ)"
 *          ),
 *          @OA\Property(
 *              property="completionDate",
 *              type="string",
 *              description="End of feature life (e.g. end of acquisition for a satellite imagery). Not returned if same as startDate (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ)"
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
 *          ),
 *          @OA\Property(
 *              property="links",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/Links")
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
 *                          69.979462,
 *                          23.507467
 *                      },
 *                      {
 *                          71.054486,
 *                          23.496997
 *                      },
 *                      {
 *                          71.039531,
 *                          22.505778
 *                      },
 *                      {
 *                          69.972328,
 *                          22.515759
 *                      },
 *                      {
 *                          69.979462,
 *                          23.507467
 *                      }
 *                  }
 *              }
 *          },
 *          "properties": {
 *              "collection": "S2",
 *              "title": "S2:tiles/42/Q/XL/2018/9/13/0",
 *              "productIdentifier": "S2:tiles/42/Q/XL/2018/9/13/0",
 *              "startDate": "2018-09-13T05:58:08.367Z",
 *              "updated": "2018-09-13T12:52:25.971969Z",
 *              "published": "2018-09-13T12:52:25.971969Z",
 *              "hashtags": {
 *                  "#s2b",
 *                  "#reflectance",
 *                  "#summer",
 *                  "#coastal"
 *              },
 *              "centroid": {
 *                  "type": "Point",
 *                  "coordinates": {
 *                      70.513407,
 *                      23.006623
 *                  }
 *              },
 *              "likes": 0,
 *              "comments": 0,
 *              "liked": false,
 *              "links": {
 *                  {
 *                      "rel": "self",
 *                      "type": "application/json",
 *                      "title": "GeoJSON link for b9eeaf68-5127-53e5-97ff-ddf44984ef56",
 *                      "href": "https://ds.snapplanet.io/2.0/collections/S2/items/b9eeaf68-5127-53e5-97ff-ddf44984ef56?&collectionId=S2.json&lang=en"
 *                  }
 *              }
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
 *              property="startDate",
 *              type="string",
 *              description="Start of feature life (e.g. start of acquisition for a satellite imagery) (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ)"
 *          ),
 *          @OA\Property(
 *              property="completionDate",
 *              type="string",
 *              description="End of feature life (e.g. end of acquisition for a satellite imagery) (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ)"
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
 *                          69.979462,
 *                          23.507467
 *                      },
 *                      {
 *                          71.054486,
 *                          23.496997
 *                      },
 *                      {
 *                          71.039531,
 *                          22.505778
 *                      },
 *                      {
 *                          69.972328,
 *                          22.515759
 *                      },
 *                      {
 *                          69.979462,
 *                          23.507467
 *                      }
 *                  }
 *              }
 *          },
 *          "properties": {
 *              "productIdentifier": "S2:tiles/42/Q/XL/2018/9/13/0",
 *              "startDate": "2018-09-13T05:58:08.367Z"
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
        return (new DefaultModel())->remap($this->featureArray, $this->collection);
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
