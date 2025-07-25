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
 *  resto collection
 *
 *  @OA\Tag(
 *      name="Collection",
 *      description="A collection is a set of features. This set is usually homogeneous (e.g. *Sentinel-2 images*) but not necessary. A collection is defined by a *model* physically described within a dedicated class under $SRC/include/resto/Models. The purpose of the model class is to convert the input collection feature format (i.e. whatever) to the resto generic format (i.e. GeoJSON) described within the RestoModel class."
 *  )
 *
 *  @OA\Schema(
 *      schema="InputCollection",
 *      required={"id", "description"},
 *      @OA\Property(
 *          property="id",
 *          type="string",
 *          description="Collection identifier. It must be an unique alphanumeric string containing only [a-zA-Z0-9\-_]."
 *      ),
 *      @OA\Property(
 *          property="title",
 *          type="string",
 *          description="A short descriptive one-line title for the Collection."
 *      ),
 *      @OA\Property(
 *          property="description",
 *          type="string",
 *          description="Detailed multi-line description to fully explain the Collection. CommonMark 0.29 syntax MAY be used for rich text representation."
 *      ),
 *      @OA\Property(
 *          property="aliases",
 *          type="array",
 *          description="Alias names for this collection. Each alias must be unique and not be the same as an already existing collection name",
 *          @OA\Items(
 *              type="string",
 *          )
 *      ),
 *      @OA\Property(
 *          property="version",
 *          type="string",
 *          description="Version of the collection."
 *      ),
 *      @OA\Property(
 *          property="visibility",
 *          description="Visibility for this collection as a list of group names. Only user from one of the group can see the collection."
 *      ),
 *      @OA\Property(
 *          property="model",
 *          type="string",
 *          description="[For developper] Name of the collection model class under $SRC/include/resto/Models - Default is DefaultModel"
 *      ),
 *      @OA\Property(
 *          property="license",
 *          type="string",
 *          description="License for this collection as a SPDX License identifier. Alternatively, use other if the license is not on the SPDX license list. In these case link to the license texts SHOULD be added, see the license link relation type."
 *      ),
 *      @OA\Property(
 *          property="links",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/Link")
 *      ),
 *      @OA\Property(
 *          property="assets",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/Asset")
 *      ),
 *      @OA\Property(
 *          property="keywords",
 *          type="array",
 *          description="List of keywords describing the collection.",
 *          @OA\Items(
 *              type="string",
 *          )
 *      ),
 *      @OA\Property(
 *          property="providers",
 *          type="array",
 *          description="A list of providers, which may include all organizations capturing or processing the data or the hosting provider. Providers should be listed in chronological order with the most recent provider being the last element of the list",
 *          @OA\Items(ref="#/components/schemas/Provider")
 *      ),
 *      @OA\Property(
 *          property="properties",
 *          type="object",
 *          @OA\JsonContent()
 *      ),
 *      @OA\Property(
 *          property="summaries",
 *          type="object",
 *          @OA\JsonContent()
 *      ),
 *      example={
 *          "id": "S2",
 *          "type": "Collection",
 *          "title": "Level 1C Sentinel-2 images",
 *          "description": "The SENTINEL-2 mission is a land monitoring constellation of two satellites each equipped with a MSI (Multispectral Imager) instrument covering 13 spectral bands providing high resolution optical imagery (i.e., 10m, 20m, 60 m) every 10 days with one satellite and 5 days with two satellites",
 *          "version": "1.0",
 *          "model": "OpticalModel",
 *          "visibility": {"default"},
 *          "license": "other",
 *          "providers": {
 *              {
 *                  "name": "European Union/ESA/Copernicus",
 *                  "roles": {
 *                      "producer",
 *                      "licensor"
 *                  },
 *                  "url": "https://sentinel.esa.int/web/sentinel/user-guides/sentinel-2-msi"
 *              }
 *          },
 *          "links": {
 *              {
 *                  "rel": "license",
 *                  "href": "https://scihub.copernicus.eu/twiki/pub/SciHubWebPortal/TermsConditions/Sentinel_Data_Terms_and_Conditions.pdf",
 *                  "title": "Legal notice on the use of Copernicus Sentinel Data and Service Information"
 *              }
 *          },
 *          "summaries": {
 *              "bands": {
 *                  {
 *                      "name": "B1",
 *                      "common_name": "coastal",
 *                      "center_wavelength": 4.439,
 *                      "gsd": 60
 *                  },
 *                  {
 *                      "name": "B2",
 *                      "common_name": "blue",
 *                      "center_wavelength": 4.966,
 *                      "gsd": 10
 *                  },
 *                  {
 *                      "name": "B3",
 *                      "common_name": "green",
 *                      "center_wavelength": 5.6,
 *                      "gsd": 10
 *                  },
 *                  {
 *                      "name": "B4",
 *                      "common_name": "red",
 *                      "center_wavelength": 6.645,
 *                      "gsd": 10
 *                  },
 *                  {
 *                      "name": "B5",
 *                      "center_wavelength": 7.039,
 *                      "gsd": 20
 *                  },
 *                  {
 *                      "name": "B6",
 *                      "center_wavelength": 7.402,
 *                      "gsd": 20
 *                  },
 *                  {
 *                      "name": "B7",
 *                      "center_wavelength": 7.825,
 *                      "gsd": 20
 *                  },
 *                  {
 *                      "name": "B8",
 *                      "common_name": "nir",
 *                      "center_wavelength": 8.351,
 *                      "gsd": 10
 *                  },
 *                  {
 *                      "name": "B8A",
 *                      "center_wavelength": 8.648,
 *                      "gsd": 20
 *                  },
 *                  {
 *                      "name": "B9",
 *                      "center_wavelength": 9.45,
 *                      "gsd": 60
 *                  },
 *                  {
 *                      "name": "B10",
 *                      "center_wavelength": 1.3735,
 *                      "gsd": 60
 *                  },
 *                  {
 *                      "name": "B11",
 *                      "common_name": "swir16",
 *                      "center_wavelength": 1.6137,
 *                      "gsd": 20
 *                  },
 *                  {
 *                      "name": "B12",
 *                      "common_name": "swir22",
 *                      "center_wavelength": 2.2024,
 *                      "gsd": 20
 *                  }
 *              }
 *          }
 *      }
 *  )
 *
 *  @OA\Schema(
 *      schema="OutputCollection",
 *      required={"id", "type", "description", "license", "extent", "links"},
 *      @OA\Property(
 *          property="id",
 *          type="string",
 *          description="Collection identifier. It must be an unique alphanumeric string containing only [a-zA-Z0-9\-_]."
 *      ),
 *      @OA\Property(
 *          property="type",
 *          type="string",
 *          enum={"Collection"},
 *          description="[EXTENSION][STAC] Always set to *Collection*"
 *      ),
 *      @OA\Property(
 *          property="title",
 *          type="string",
 *          description="A short descriptive one-line title for the collection."
 *      ),
 *      @OA\Property(
 *          property="description",
 *          type="string",
 *          description="Detailed multi-line description to fully explain the collection. CommonMark 0.28 syntax MAY be used for rich text representation."
 *      ),
 *      @OA\Property(
 *          property="keywords",
 *          type="array",
 *          description="List of keywords describing the collection.",
 *          @OA\Items(
 *              type="string",
 *          )
 *      ),
 *      @OA\Property(
 *          property="aliases",
 *          type="array",
 *          description="Alias names for this collection. Each alias must be unique and not be the same as an already existing collection name",
 *          @OA\Items(
 *              type="string",
 *          )
 *      ),
 *      @OA\Property(
 *          property="license",
 *          type="string",
 *          enum={"other", "<license id>"},
 *          description="License for this collection as a SPDX License identifier or expression. Alternatively, use other if the license is not on the SPDX license list. In this case, links to the license texts SHOULD be added, see the license link relation type."
 *      ),
 *      @OA\Property(
 *          property="extent",
 *          type="object",
 *          ref="#/components/schemas/Extent"
 *      ),
 *      @OA\Property(
 *          property="links",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/Link")
 *      ),
 *      @OA\Property(
 *          property="resto:info",
 *          type="object",
 *          description="resto additional information",
 *          @OA\JsonContent(
 *              @OA\Property(
 *                  property="model",
 *                  type="string",
 *                  description="[For developper] Name of the collection model corresponding to the class under $SRC/include/resto/Models without *Model* suffix."
 *              ),
 *              @OA\Property(
 *                  property="lineage",
 *                  type="array",
 *                  description="[For developper] Model lineage for this collection. Sort from general to specific.",
 *                  @OA\Items(
 *                      type="string",
 *                  )
 *              ),
 *              @OA\Property(
 *                  property="owner",
 *                  type="string",
 *                  description="Collection owner (i.e. resto user identifier as bigint)"
 *              )
 *          )
 *      ),
 *      @OA\Property(
 *          property="providers",
 *          type="array",
 *          description="A list of providers, which may include all organizations capturing or processing the data or the hosting provider. Providers should be listed in chronological order with the most recent provider being the last element of the list",
 *          @OA\Items(ref="#/components/schemas/Provider")
 *      ),
 *      @OA\Property(
 *          property="properties",
 *          type="object",
 *          description="Free properties object",
 *          @OA\JsonContent()
 *      ),
 *      @OA\Property(
 *          property="summaries",
 *          type="object",
 *          @OA\JsonContent(
 *              @OA\Property(
 *                  property="datetime",
 *                  type="string",
 *                  description="Temporal extent of collection (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ/YYYY-MM-DD-THH:MM:SSZ)"
 *              ),
 *              @OA\Property(
 *                  property="resto:stats",
 *                  type="object",
 *                  ref="#/components/schemas/Statistics"
 *              )
 *          )
 *      ),
 *      @OA\Property(
 *          property="stac_version",
 *          type="string",
 *          description="[EXTENSION][STAC] The STAC version the Collection implements"
 *      ),
 *      @OA\Property(
 *          property="stac_extensions",
 *          type="array",
 *          description="[EXTENSION][STAC] A list of extensions the Collection implements.",
 *          @OA\Items(
 *              type="string"
 *          )
 *      ),
 *      example={
 *          "id": "S2",
 *          "title": "Sentinel-2",
 *          "description": "The SENTINEL-2 mission is a land monitoring constellation of two satellites each equipped with a MSI (Multispectral Imager) instrument covering 13 spectral bands providing high resolution optical imagery (i.e., 10m, 20m, 60 m) every 10 days with one satellite and 5 days with two satellites",
 *          "keywords": {
 *              "copernicus",
 *              "esa",
 *              "eu",
 *              "msi",
 *              "radiance",
 *              "sentinel",
 *              "sentinel2"
 *          },
 *          "license": "other",
 *          "extent": {
 *              "spatial": {
 *                  "bbox": {
 *                      {
 *                          -48.6198530870596,
 *                          74.6749788966259,
 *                          -44.6464244356188,
 *                          75.6843970710939
 *                      }
 *                  },
 *                  "crs": "http://www.opengis.net/def/crs/OGC/1.3/CRS84"
 *              },
 *              "temporal": {
 *                  "interval": {
 *                      {
 *                          "2019-06-11T16:11:41.808000Z",
 *                          "2019-06-11T16:11:41.808000Z"
 *                      }
 *                  },
 *                  "trs": "http://www.opengis.net/def/uom/ISO-8601/0/Gregorian"
 *              }
 *          },
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
 *          "resto:info": {
 *              "model": "OpticalModel",
 *              "lineage": {
 *                  "DefaultModel",
 *                  "LandCoverModel",
 *                  "SatelliteModel",
 *                  "OpticalModel"
 *              },
 *              "owner": "203883411255198721"
 *          },
 *          "providers": {
 *              {
 *                  "name": "European Union/ESA/Copernicus",
 *                  "roles": {
 *                      "producer",
 *                      "licensor"
 *                  },
 *                  "url": "https://sentinel.esa.int/web/sentinel/user-guides/sentinel-2-msi"
 *              }
 *          },
 *          "summaries": {
 *              "datetime": {
 *                  "minimum": "2019-06-11T16:11:41.808000Z",
 *                  "maximum": "2019-06-11T16:11:41.808000Z"
 *              },
 *              "eo:instrument": {
 *                  "MSI"
 *              },
 *              "eo:platform": {
 *                  "S2A"
 *              },
 *              "processingLevel": {
 *                  "LEVEL1C"
 *              },
 *              "productType": {
 *                  "REFLECTANCE"
 *              },
 *              "bands": {
 *                  {
 *                      "name": "B1",
 *                      "common_name": "coastal",
 *                      "center_wavelength": 4.439,
 *                      "gsd": 60
 *                  },
 *                  {
 *                      "name": "B2",
 *                      "common_name": "blue",
 *                      "center_wavelength": 4.966,
 *                      "gsd": 10
 *                  },
 *                  {
 *                      "name": "B3",
 *                      "common_name": "green",
 *                      "center_wavelength": 5.6,
 *                      "gsd": 10
 *                  },
 *                  {
 *                      "name": "B4",
 *                      "common_name": "red",
 *                      "center_wavelength": 6.645,
 *                      "gsd": 10
 *                  },
 *                  {
 *                      "name": "B5",
 *                      "center_wavelength": 7.039,
 *                      "gsd": 20
 *                  },
 *                  {
 *                      "name": "B6",
 *                      "center_wavelength": 7.402,
 *                      "gsd": 20
 *                  },
 *                  {
 *                      "name": "B7",
 *                      "center_wavelength": 7.825,
 *                      "gsd": 20
 *                  },
 *                  {
 *                      "name": "B8",
 *                      "common_name": "nir",
 *                      "center_wavelength": 8.351,
 *                      "gsd": 10
 *                  },
 *                  {
 *                      "name": "B8A",
 *                      "center_wavelength": 8.648,
 *                      "gsd": 20
 *                  },
 *                  {
 *                      "name": "B9",
 *                      "center_wavelength": 9.45,
 *                      "gsd": 60
 *                  },
 *                  {
 *                      "name": "B10",
 *                      "center_wavelength": 1.3735,
 *                      "gsd": 60
 *                  },
 *                  {
 *                      "name": "B11",
 *                      "common_name": "swir16",
 *                      "center_wavelength": 1.6137,
 *                      "gsd": 20
 *                  },
 *                  {
 *                      "name": "B12",
 *                      "common_name": "swir22",
 *                      "center_wavelength": 2.2024,
 *                      "gsd": 20
 *                  }
 *              }
 *          },
 *          "stac_version": "1.0.0",
 *          "stac_extensions": {
 *              "https://stac-extensions.github.io/eo/v1.0.0/schema.json"
 *          }
 *      }
 *  )
 */
class RestoCollection
{
    /*
     * Collection identifier must be unique
     */
    public $id =  null;

    /*
     * Collection title
     */
    public $title =  null;

    /*
     * Collection description
     */
    public $description =  null;

    /*
     * Data model for this collection
     */
    public $model = null;

    /*
     * Context reference
     */
    public $context = null;

    /*
     * All collection metadata that are not stored in a dedicated table column
     * are stored within the properties column
     */
    public $properties = array();

    /*
     * User
     */
    public $user = null;

    /*
     * [STAC] Collection root attributes
     */
    public $aliases = array();
    public $version = '1.0.0';
    public $license = 'other';
    public $links = array();
    public $providers = array();
    public $assets = array();
    public $keywords = array();
    public $extent = array(
        'spatial' => array(
            'bbox' => array(null),
            'crs' => 'http://www.opengis.net/def/crs/OGC/1.3/CRS84'
        ),
        'temporal' => array(
            'interval' => array(
                array(null, null)
            ),
            'trs' => 'http://www.opengis.net/def/uom/ISO-8601/0/Gregorian'
        )
    );

    /*
     * Collection owner
     */
    public $owner;

    /*
     * Visibility for collection
     */
    public $visibility;

    /**
     * Summaries
     */
    private $summaries = null;
    
    /**
     * @OA\Schema(
     *      schema="Provider",
     *      description="A provider is any of the organizations that captured or processed the content of the collection and therefore influenced the data offered by this collection",
     *      required={"name"},
     *      @OA\Property(
     *          property="name",
     *          type="string",
     *          description="The name of the organization or the individual"
     *      ),
     *      @OA\Property(
     *          property="description",
     *          type="string",
     *          description="Multi-line description to add further provider information such as processing details for processors and producers, hosting details for hosts or basic contact information. CommonMark 0.28 syntax MAY be used for rich text representation"
     *      ),
     *      @OA\Property(
     *          property="roles",
     *          type="array",
     *          description="Roles of the provider.",
     *          @OA\Items(
     *              type="string",
     *              enum={"licensor", "producer", "processor", "host"},
     *          )
     *      ),
     *      @OA\Property(
     *          property="url",
     *          type="string",
     *          description="Homepage on which the provider describes the dataset and publishes contact information."
     *      ),
     *      example={
     *          {
     *              "name": "European Union/ESA/Copernicus",
     *              "roles": {
     *                  "producer",
     *                  "licensor"
     *              },
     *              "url": "https://sentinel.esa.int/web/sentinel/user-guides/sentinel-2-msi"
     *          }
     *      }
     *  )
     */

    /*
     * Properties that are not stored in properties column
     */
    private $notInProperties = array(
        'aliases',
        'assets',
        'description',
        'extent',
        'id',
        'keywords',
        'license',
        'links',
        'model',
        'providers',
        'stac_extension',
        'stac_version',
        'title',
        'type',
        'version',
        'visibility'
    );

    /**
     * Avoid call to database when object is already loaded
     */
    private $isLoaded = false;

    /**
     * Constructor
     *
     * @param string $id : collection id
     * @param RestoContext $context : RESTo context
     * @param RestoUser $user : RESTo user
     */
    public function __construct($id, $context, $user)
    {
        $this->id = $id;
        $this->context = $context;
        $this->user = $user;
        $this->visibility = RestoUtil::getDefaultVisibility($this->user, isset($this->user->profile['settings']['createdCollectionIsPublic']) ? $this->user->profile['settings']['createdCollectionIsPublic'] : true);
    }

    /**
     * Load collection from database
     * Return 404 if collection is not found
     *
     * @return object
     */
    public function load()
    {

        if ( !$this->isLoaded ) {
            $this->isLoaded = true;
            $collectionObject = (new CollectionsFunctions($this->context->dbDriver))->getCollection($this->id, $this->user);
            if (! isset($collectionObject)) {
                RestoLogUtil::httpError(404);
            }
    
            foreach ($collectionObject as $key => $value) {
                $this->$key = $key === 'model' ? new $value(array(
                    'collectionId' => $this->id,
                    'addons' => $this->context->addons
                )) : $value;
            }

        }
        
        return $this;
    }

    /**
     * Store collection to database
     *
     * @param array $object
     */
    public function store()
    {
        (new CollectionsFunctions($this->context->dbDriver))->storeCollection($this);
    }

    /**
     * Update collection - collection must be load first !
     *
     * @param array $object
     * @return RestoCollection
     */
    public function update($object)
    {
        // It means that collection is not loaded - so cannot be updated
        if (! isset($this->model)) {
            RestoLogUtil::httpError(400, 'Model does not exist');
        }

        (new CollectionsFunctions($this->context->dbDriver))->updateCollection($this, $this->cleanJSON($object));

    }

    /**
     * Search features within collection
     *
     * @param array $query
     * @return RestoFeatureCollection
     */
    public function search($query)
    {
        return (new RestoFeatureCollection($this->context, $this->user, array($this->id => $this), $this->model, $query))->load($this);
    }

    /**
     * Add feature to the {collection}.features table
     *
     * @param array $body HTTP body
     * @param array $params : Insertion params
     */
    public function addFeatures($body, $params)
    {
        return $this->model->storeFeatures($this, $body, $params);
    }

    /**
     * Return STAC summaries
     */
    public function getSummaries()
    {
        if ( !isset($this->summaries) ) {
            $summaries = (new CatalogsFunctions($this->context->dbDriver))->getSummaries(null);
            if ( isset($summaries[$this->id]) ) {
                $this->setSummaries($summaries[$this->id]);
            }
            else {
                $this->summaries = array();
            }
        }
        return $this->summaries;
    }

    /**
     * Set summaries eventually map summary id with STAC naming
     */
    public function setSummaries($summaries)
    {

        $this->summaries = array();

        if ( isset($this->extent['temporal']['interval'][0][0]) && isset($this->extent['temporal']['interval'][0][1]) ) {
            $this->summaries['datetime'] = array(
                'minimum' => $this->extent['temporal']['interval'][0][0],
                'maximum' => $this->extent['temporal']['interval'][0][1]
            );
        }

        // [STAC] Change the key name if needed (e.g. "instrument" => "instruments")
        foreach(array_keys($summaries) as $key) {
            $this->summaries[isset($this->model->stacMapping[$key]) ? $this->model->stacMapping[$key]['key'] : $key] = $summaries[$key];
        }

        // Hack to add eo:cloud_cover
        if (isset($this->model) && isset($this->model->searchFilters) && isset($this->model->searchFilters['eo:cloudCover'])) {
            $this->summaries['eo:cloud_cover'] = array(
                'minimum' => 0,
                'maximum' => 100
            );
        }
        
    }

    /**
     * Output collection description as an array
     */
    public function toArray()
    {

        $collectionArray = array(
            'stac_version' => STACAPI::STAC_VERSION,
            'stac_extensions' => $this->model->stacExtensions,
            'id' => $this->id,
            'type' => 'Collection',
            'title' => $this->title,
            'description' => $this->description,
            'version' => $this->version ?? null,
            'aliases' => $this->aliases ?? array(),
            'license' => $this->license,
            'extent' => $this->extent,
            'links' => array_merge(
                array(
                    array(
                        'rel' => 'self',
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $this->context->core['baseUrl'] . RestoUtil::replaceInTemplate(RestoRouter::ROUTE_TO_COLLECTION, array('collectionId' => $this->id))
                    ),
                    array(
                        'rel' => 'root',
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $this->context->core['baseUrl']
                    ),
                    array(
                        'rel' => 'items',
                        'title' => 'All items',
                        'type' => RestoUtil::$contentTypes['geojson'],
                        'href' => $this->context->core['baseUrl'] . RestoUtil::replaceInTemplate(RestoRouter::ROUTE_TO_FEATURES, array('collectionId' => $this->id))
                    ),
                    array(
                        'rel' => 'http://www.opengis.net/def/rel/ogc/1.0/queryables',
                        'type' => RestoUtil::$contentTypes['jsonschema'],
                        'title' => 'Queryables',
                        'href' => $this->context->core['baseUrl'] . RestoUtil::replaceInTemplate(RestoRouter::ROUTE_TO_COLLECTION, array('collectionId' => $this->id)) . '/queryables'
                    )
                ),
                $this->links ?? array()
            ),
            'resto:info' => array(
                'model' => $this->model->getName(),
                'lineage' => $this->model->getLineage(),
                'owner' => $this->owner/*,
                'visibility' => $this->visibility*/
            )
        );

        foreach (array_values(array('keywords', 'providers', 'assets')) as $key) {
            if (isset($this->$key)) {
                $collectionArray[$key] = $key === 'assets' ? (object) $this->$key : $this->$key;
            }
        }
        
        $summaries = $this->getSummaries();

        // Properties
        if (is_array($this->properties)) {
            foreach ($this->properties as $key => $value) {
                if ($key === 'summaries') {
                    if (is_array($value)) {
                        $summaries = array_merge($summaries, $value);
                    }
                } else {
                    $collectionArray[$key] = $value;
                }
            }
        }

        // Force summaries to be an object not an array during json_encode
        if ( !empty($summaries) ) {
            $collectionArray['summaries'] = $summaries;
        }

        return $this->context->core['useJSONLD'] ? JSONLDUtil::addDataCatalogMetadata($collectionArray) : $collectionArray;
        
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
     * On which planet this collection applied
     * Based on ssys:target
     */
    public function getPlanet()
    {
        if ($this->properties && isset($this->properties['ssys:targets'])) {
            return is_array($this->properties['ssys:targets']) ? $this->properties['ssys:targets'][0] : $this->properties['ssys:targets'];
        }
        return $this->context->core['planet'];
    }

    /**
     * Load collection parameters from input collection description
     * (See collection file example in examples/collections/S2.json)
     *
     * @param array $object : collection description as json file
     * @param string $modelName
     */
    public function loadFromJSON($object, $modelName = null)
    {

        $cleanObject = $this->cleanJSON($object, $modelName);
        
        /*
         * Check mandatory properties are required
         */
        $this->checkCreationMandatoryProperties($cleanObject);
        
        /*
         * Set collection model
         */
        if ( !isset($cleanObject['model']) ) {
            $cleanObject['model'] = $this->context->core['defaultModel'];
        }
        $this->model = new $cleanObject['model'](array(
            'collectionId' => $this->id,
            'addons' => $this->context->addons
        ));
        
        /*
         * Collection owner is the current user
         */
        $this->owner = $this->user->profile['id'];

        /*
         * Set values
         */
        foreach ($cleanObject as $key => $value) {
            if ( !in_array($key, array('model', 'owner')) ) {
                $this->$key = $value;
            }
        }

        $this->isLoaded = true;
        
        return $this;
    }

    /**
     * Return a clean collection array from json 
     * 
     * @param array $object
     * @param string $modelName
     * @return array
     */
    private function cleanJSON($object, $modelName = null)
    {

        $clean = array();

        /*
         * Check that object is a valid array
         */
        if (!is_array($object)) {
            RestoLogUtil::httpError(400, 'Invalid input JSON');
        }

        /*
         * Set DefaultModel if not set - preseance to input $modelName
         */
        if ( isset($object['model']) ) {
            $clean['model'] = $object['model'];
        }
        if ( isset($modelName) ) {
            $clean['model'] = $modelName;
        }

        if ( isset($clean['model']) ) {
            if (!class_exists($clean['model']) || !is_subclass_of($clean['model'], 'RestoModel')) {
                RestoLogUtil::httpError(400, 'Model "' . $clean['model'] . '" is not a valid model name');
            }
        }

        /*
         * Default collection visibility is the value of RestoConstants::GROUP_DEFAULT_ID
         */
        if ( isset($object['visibility']) ) {
            if ( !is_array($object['visibility']) ) {
                RestoLogUtil::httpError(400, 'Invalid visibility type - should be an array of group names' );
            }
            // [IMPORTANT] Convert input names to ids
            $clean['visibility'] = (new GeneralFunctions($this->context->dbDriver))->visibilityNamesToIds($object['visibility']);
            
            if ( empty($clean['visibility']) ) {
                RestoLogUtil::httpError(400, 'Visibility is set but either emtpy or referencing an unknown group'); 
            }
            if ( !(new CatalogsFunctions($this->context->dbDriver))->canSeeCatalog($clean['visibility'], $this->user, true) ) {
                RestoLogUtil::httpError(403, 'You are not allowed to set the visibility to a group you are not part of');
            }
        
        }

        /*
         * Set values
         */
        foreach (array_values(array('id', 'type', 'title', 'description', 'aliases', 'version', 'license', 'links', 'providers', 'assets', 'keywords', 'extent')) as $key) {
            if (isset($object[$key])) {
                $clean[$key] = $key === 'links' ? $this->cleanInputLinks($object['links']) : $object[$key];
            }
        }

        /*
         * Store every other properties to $this->properties
         *
         * [IMPORTANT] Clear properties first !
         */
        $clean['properties'] = array();
        foreach ($object as $key => $value) {
            if (!in_array($key, $this->notInProperties)) {
                $clean['properties'][$key] = $value;
            }
        }

        return $clean;

    }

    /**
     * Check mandatory properties for collection creation
     *
     * @param array $object
     */
    private function checkCreationMandatoryProperties($object)
    {
        /*
         * Check that input file is for the current collection
         */
        if (!isset($object['id']) || $this->id !== $object['id']) {
            RestoLogUtil::httpError(400, 'Property "id" and collection id differ');
        }

        /*
         * Type is mandatory and must be set to 'Collection'
         */
        if ( !isset($object['type']) || $object['type'] !== 'Collection') {
            RestoLogUtil::httpError(400, 'Property "type" is mandatory and must be set to *Collection*');
        }

        /*
         * description is mandatory
         */
        if ( !isset($object['description']) ) {
            RestoLogUtil::httpError(400, 'Property "description" is mandatory');
        }
         
    }

    /**
     * Remove input links that should be computed by resto
     *
     * @param array $links
     * @return array
     */
    private function cleanInputLinks($links)
    {
        $cleanLinks = array();

        for ($i = 0, $ii = count($links); $i < $ii; $i++) {
            $rel = $links[$i]['rel'] ?? null;
            if ($rel && in_array($rel, array('root', 'self', 'parent', 'child', 'item', 'items'))) {
                continue;
            }
            $cleanLinks[] = $links[$i];
        }

        return $cleanLinks;
    }
}
