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
 *          description="Visibility of this collection. Collections with visibility 1 are visible to all users."
 *      ),
 *      @OA\Property(
 *          property="model",
 *          type="string",
 *          description="[For developper] Name of the collection model class under $SRC/include/resto/Models - Default is DefaultModel"
 *      ),
 *      @OA\Property(
 *          property="license",
 *          type="string",
 *          description="License for this collection as a SPDX License identifier. Alternatively, use proprietary if the license is not on the SPDX license list or various if multiple licenses apply. In these two cases links to the license texts SHOULD be added, see the license link relation type."
 *      ),
 *      @OA\Property(
 *          property="rights",
 *          type="object",
 *          description="Default collection rights settings",
 *          @OA\Property(
 *              property="download",
 *              type="integer",
 *              enum={0,1},
 *              description="Feature download rights (1 can be downloaded; 0 cannot be downloaded)"
 *          ),
 *          @OA\Property(
 *              property="visualize",
 *              type="integer",
 *              description="Features visualization rights (1 can be visualized; 0 cannot be visualized)"
 *          )
 *      ),
 *      @OA\Property(
 *          property="osDescription",
 *          type="object",
 *          required={"en"},
 *          @OA\Property(
 *              property="en",
 *              description="OpenSearch description in English",
 *              ref="#/components/schemas/OpenSearchDescription"
 *          ),
 *          @OA\Property(
 *              property="fr",
 *              description="OpenSearch description in French",
 *              ref="#/components/schemas/OpenSearchDescription"
 *          )
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
 *          "version": "1.0",
 *          "model": "OpticalModel",
 *          "rights": {
 *              "download": 1,
 *              "visualize": 1
 *          },
 *          "visibility": 1,
 *          "license": "proprietary",
 *          "osDescription": {
 *              "en": {
 *                  "ShortName": "Sentinel-2",
 *                  "LongName": "Level 1C Sentinel-2 images",
 *                  "Description": "The SENTINEL-2 mission is a land monitoring constellation of two satellites each equipped with a MSI (Multispectral Imager) instrument covering 13 spectral bands providing high resolution optical imagery (i.e., 10m, 20m, 60 m) every 10 days with one satellite and 5 days with two satellites",
 *                  "Tags": "copernicus esa eu msi radiance sentinel sentinel2",
 *                  "Developer": "Jérôme Gasperi",
 *                  "Contact": "jrom@snapplanet.io",
 *                  "Query": "Toulouse",
 *                  "Attribution": "European Union/ESA/Copernicus"
 *              },
 *              "fr": {
 *                  "ShortName": "Sentinel-2",
 *                  "LongName": "Images Sentinel-2 Niveau 1C",
 *                  "Description": "La mission SENTINEL-2 est constituée de deux satellites d'imagerie optique équipés d’un imageur multispectral (MSI) en 13 bandes spectrales avec des résolutions de 10, 20 et 60 mètres et d'une fauchée unique de 290 km de large. La capacité d'observation des deux satellites permet de surveiller l'intégralité des terres émergées du globe tous les 5 jours",
 *                  "Tags": "copernicus esa eu msi radiance sentinel sentinel2",
 *                  "Developer": "Jérôme Gasperi",
 *                  "Contact": "jrom@snapplanet.io",
 *                  "Query": "Toulouse",
 *                  "Attribution": "European Union/ESA/Copernicus"
 *              }
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
 *          "links": {
 *              {
 *                  "rel": "license",
 *                  "href": "https://scihub.copernicus.eu/twiki/pub/SciHubWebPortal/TermsConditions/Sentinel_Data_Terms_and_Conditions.pdf",
 *                  "title": "Legal notice on the use of Copernicus Sentinel Data and Service Information"
 *              }
 *          },
 *          "summaries": {
 *              "eo:bands": {
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
 *      required={"id", "type", "title", "description", "license", "extent", "links"},
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
 *          enum={"proprietary", "various", "<license id>"},
 *          description="License for this collection as a SPDX License identifier or expression. Alternatively, use proprietary if the license is not on the SPDX license list or various if multiple licenses apply. In these two cases links to the license texts SHOULD be added, see the license link relation type."
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
 *                  property="osDescription",
 *                  type="object",
 *                  ref="#/components/schemas/OpenSearchDescription"
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
 *                  enum={"public", "<group id>"},
 *                  description="Visibility of this collection. *public* collections are visible to all users. Non public collections are visible to owner and member of <group id> only"
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
 *          "license": "proprietary",
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
 *              "osDescription": {
 *                  "ShortName": "Sentinel-2",
 *                  "LongName": "Level 1C Sentinel-2 images",
 *                  "Description": "The SENTINEL-2 mission is a land monitoring constellation of two satellites each equipped with a MSI (Multispectral Imager) instrument covering 13 spectral bands providing high resolution optical imagery (i.e., 10m, 20m, 60 m) every 10 days with one satellite and 5 days with two satellites",
 *                  "Tags": "copernicus esa eu msi radiance sentinel sentinel2",
 *                  "Developer": "J\u00e9r\u00f4me Gasperi",
 *                  "Contact": "jrom@snapplanet.io",
 *                  "Query": "Toulouse",
 *                  "Attribution": "European Union/ESA/Copernicus"
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
 *              "eo:bands": {
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
    public $visibility = RestoConstants::GROUP_DEFAULT_ID;
    public $version = '1.0.0';
    public $license = 'proprietary';
    public $links = array();
    public $providers = array();
    public $rights = array();
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

    /**
     *
     * Array of OpenSearch Description parameters per lang
     *
     * @OA\Schema(
     *      schema="OpenSearchDescription",
     *      description="OpenSearch description of the search engine attached to the collection",
     *      required={"ShortName", "Description"},
     *      @OA\Property(
     *          property="ShortName",
     *          type="string",
     *          description="Contains a brief human-readable title that identifies the search engine"
     *      ),
     *      @OA\Property(
     *          property="LongName",
     *          type="string",
     *          description="Contains an extended human-readable title that identifies this search engine"
     *      ),
     *      @OA\Property(
     *          property="Description",
     *          type="string",
     *          description="Contains a human-readable text description of the collection search engine"
     *      ),
     *      @OA\Property(
     *          property="Tags",
     *          type="string",
     *          description="Contains a set of words that are used as keywords to identify and categorize this search content. Tags must be a single word and are delimited by the space character"
     *      ),
     *      @OA\Property(
     *          property="Developer",
     *          type="string",
     *          description="Contains the human-readable name or identifier of the creator or maintainer of the description document"
     *      ),
     *      @OA\Property(
     *          property="Contact",
     *          type="string",
     *          description="Contains an email address at which the maintainer of the description document can be reached"
     *      ),
     *      @OA\Property(
     *          property="Query",
     *          type="string",
     *          description="Defines a search query that can be performed by search clients. Please see the OpenSearch Query element specification for more information"
     *      ),
     *      @OA\Property(
     *          property="Attribution",
     *          type="string",
     *          description="Contains a list of all sources or entities that should be credited for the content contained in the search feed"
     *      ),
     *      example={
     *          "ShortName": "S2",
     *          "LongName": "Sentinel-2",
     *          "Description": "Sentinel-2 tiles",
     *          "Tags": "s2 sentinel2",
     *          "Developer": "Jérôme Gasperi",
     *          "Contact": "jrom@snapplanet.io",
     *          "Query": "Toulouse",
     *          "Attribution": "SnapPlanet - Copyright 2016, All Rights Reserved"
     *      }
     * )
     */
    public $osDescription = null;

    /**
     * Statistics
     *
     * @OA\Schema(
     *      schema="Statistics",
     *      description="Collection facets statistics",
     *      required={"count", "facets"},
     *      @OA\Property(
     *          property="count",
     *          type="integer",
     *          description="Total number of features in the collection"
     *      ),
     *      @OA\Property(
     *          property="facets",
     *          description="Statistics per facets",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="continent",
     *                  type="array",
     *                  description="Number of features in the collection per continent"
     *              ),
     *              @OA\Property(
     *                  property="instrument",
     *                  type="array",
     *                  description="Number of features in the collection per instrument"
     *              ),
     *              @OA\Property(
     *                  property="platform",
     *                  type="array",
     *                  description="Number of features in the collection per platform"
     *              ),
     *              @OA\Property(
     *                  property="processingLevel",
     *                  type="array",
     *                  description="Number of features in the collection per processing level"
     *              ),
     *              @OA\Property(
     *                  property="productType",
     *                  type="array",
     *                  description="Number of features in the collection per product Type"
     *              )
     *          )
     *      ),
     *      example={
     *          "count": 5322724,
     *          "facets": {
     *              "continent": {
     *                  "Africa": 671538,
     *                  "Antarctica": 106337,
     *                  "Asia": 747847,
     *                  "Europe": 1992756,
     *                  "North America": 1012027,
     *                  "Oceania": 218789,
     *                  "Seven seas (open ocean)": 9481,
     *                  "South America": 313983
     *              },
     *              "instrument": {
     *                  "HRS": 2,
     *                  "MSI": 5322722
     *              },
     *              "platform": {
     *                  "S2A": 3346319,
     *                  "S2B": 1976403,
     *                  "SPOT6": 1
     *              },
     *              "processingLevel": {
     *                  "LEVEL1C": 5322722
     *              },
     *              "productType": {
     *                  "PX": 2,
     *                  "REFLECTANCE": 5322722
     *              }
     *          }
     *      }
     * )
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
        'osDescription',
        'providers',
        'rights',
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
    }

    /**
     * Load collection from database or from input data
     * Return 404 if collection is not found
     *
     * @param array $object
     * @param string $modelName
     * @return object
     */
    public function load($object = null, $modelName = null)
    {

        if (isset($object)) {
            return $this->loadFromJSON($object, $modelName);
        }
        
        if ( !$this->isLoaded ) {
            $this->isLoaded = true;
            $collectionObject = (new CollectionsFunctions($this->context->dbDriver))->getCollectionDescription($this->id);
            if (! isset($collectionObject)) {
                return RestoLogUtil::httpError(404);
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
        (new CollectionsFunctions($this->context->dbDriver))->storeCollection($this, $this->rights);
    }

    /**
     * Update collection - collection must be load first !
     *
     * @param array $object
     * @return This object
     */
    public function update($object)
    {
        // It means that collection is not loaded - so cannot be updated
        if (! isset($this->model)) {
            return RestoLogUtil::httpError(400, 'Model does not exist');
        }

        $this->loadFromJSON($object);
        
        return $this;
    }

    /**
     * Search features within collection
     *
     * @param array $query
     * @return array (FeatureCollection)
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
            $summaries = (new FacetsFunctions($this->context->dbDriver))->getSummaries(null, $this->id);
            if ( isset($summaries[$this->id]) ) {
                $this->setSummaries($summaries[$this->id]);
            }
            else $this->summaries = array();
        }
        return $this->summaries;
    }

    /**
     * Set summaries eventually map summary id with STAC naming
     */
    public function setSummaries($summaries)
    {

        $this->summaries = array();

        // Datetime is not stored in facet
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

        $osDescription = $this->osDescription[$this->context->lang] ?? $this->osDescription['en'];

        $collectionArray = array(
            'stac_version' => STAC::STAC_VERSION,
            'stac_extensions' => $this->model->stacExtensions,
            'id' => $this->id,
            'type' => 'Collection',
            'title' => $osDescription['LongName'] ?? $osDescription['ShortName'],
            'version' => $this->version ?? null,
            'description' => $osDescription['Description'],
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
                'osDescription' => $this->osDescription[$this->context->lang] ?? $this->osDescription['en'],
                'owner' => $this->owner,
                'visibility' => $this->visibility
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

        return $collectionArray;
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
     * Output collection description as an XML OpenSearch document
     */
    public function getOSDD()
    {
        return new OSDD($this->context, $this->model, $this->getSummaries(), $this);
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
    private function loadFromJSON($object, $modelName = null)
    {
        /*
         * Check that object is a valid array
         */
        if (!is_array($object)) {
            RestoLogUtil::httpError(400, 'Invalid input JSON');
        }

        /*
         * Check mandatory properties are required
         */
        $this->checkCreationMandatoryProperties($object, $modelName);

        /*
         * If OpenSearch Description object is not set, create a minimal one from $object['description']
         */
        if (!isset($object['osDescription']) || !is_array($object['osDescription']) || !isset($object['osDescription']['en']) || !is_array($object['osDescription']['en'])) {
            $object['osDescription'] = array(
                'en' => array(
                    'ShortName' => $object['title'] ?? $object['id'],
                    'Description' => $object['description'] ?? ''
                )
            );
        }
        
        /*
         * Default collection visibility is the value of RestoConstants::GROUP_DEFAULT_ID
         * [TODO] Allow to change visibility in collection
         */
        //$this->visibility = isset($object['visibility']) ? $object['visibility'] : RestoConstants::GROUP_DEFAULT_ID;
        
        /*
         * Set values
         */
        foreach (array_values(array('aliases', 'version', 'license', 'links', 'osDescription', 'providers', 'rights', 'assets', 'keywords', 'extent')) as $key) {
            if (isset($object[$key])) {
                $this->$key = $key === 'links' ? $this->cleanInputLinks($object['links']) : $object[$key];
            }
        }

        /*
         * Store every other properties to $this->properties
         *
         * [IMPORTANT] Clear properties first !
         */
        $this->properties = array();
        foreach ($object as $key => $value) {
            if (!in_array($key, $this->notInProperties)) {
                $this->properties[$key] = $value;
            }
        }

        $this->isLoaded = true;
        
        return $this;
    }

    /**
     * Check mandatory properties for collection creation
     *
     * @param array $object
     * @param string $modelName
     */
    private function checkCreationMandatoryProperties($object, $modelName)
    {
        /*
         * Check that input file is for the current collection
         */
        if (!isset($object['id']) || $this->id !== $object['id']) {
            RestoLogUtil::httpError(400, 'Property "id" and collection id differ');
        }

        /*
         * Set DefaultModel if not set
         */
        $object['model'] = isset($modelName) ? $modelName : ($object['model'] ?? 'DefaultModel');
        
        if (!class_exists($object['model']) || !is_subclass_of($object['model'], 'RestoModel')) {
            RestoLogUtil::httpError(400, 'Model "' . $object['model'] . '" is not a valid model name');
        }
        
        /*
         * Set collection model
         */
        $this->model = new $object['model'](array(
            'collectionId' => $this->id,
            'addons' => $this->context->addons
        ));
        
        /*
         * Collection owner is the current user
         */
        $this->owner = $this->user->profile['id'];
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
