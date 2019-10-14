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
 *      required={"id", "model", "osDescription"},
 *      @OA\Property(
 *          property="id",
 *          type="string",
 *          description="Collection identifier. It must be an unique alphanumeric string containing [a-zA-Z0-9] and not starting with a digit."
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
 *          description="[For developper] Name of the collection model class under $SRC/include/resto/Models."
 *      ),
 *      @OA\Property(
 *          property="licenseId",
 *          type="string",
 *          description="License for this collection as a SPDX License identifier. Alternatively, use proprietary if the license is not on the SPDX license list or various if multiple licenses apply. In these two cases links to the license texts SHOULD be added, see the license link relation type."
 *      ),
 *      @OA\Property(
 *          property="rights",
 *          type="object",
 *          description="Default collection rights settings",
 *          @OA\Property(
 *              property="download",
 *              type="enum",
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
 *          @OA\Items(ref="#/components/schemas/Links")
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
 *      example={
 *          "id": "S2",
 *          "version": "1.0",
 *          "model": "OpticalModel",
 *          "rights": {
 *              "download": 1,
 *              "visualize": 1
 *          },
 *          "visibility": 1,
 *          "licenseId": "proprietary",
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
 *          "properties": {
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
 *      required={"id", "title", "description", "license", "extent", "links"},
 *      @OA\Property(
 *          property="id",
 *          type="string",
 *          description="Collection identifier. It is an unique alphanumeric string containing [a-zA-Z0-9] and not starting with a digit."
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
 *          property="license",
 *          type="enum",
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
 *          @OA\Items(ref="#/components/schemas/Links")
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
 *                  description="Collection owner (i.e. user identifier)"
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
 *          @OA\JsonContent()
 *      ),
 *      @OA\Property(
 *          property="summaries",
 *          type="object",
 *          @OA\JsonContent(
 *              @OA\Property(
 *                  property="datetime",
 *                  type="enum",
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
 *          "properties": {
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
 *          "summaries": {
 *              "datetime": {
 *                  "min": "2019-06-11T16:11:41.808000Z",
 *                  "max": "2019-06-11T16:11:41.808000Z"
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
 *              }
 *          },
 *          "stac_version": "0.8.0",
 *          "stac_extensions": {
 *              "eo"
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
     * User
     */
    public $user = null;

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

    /*
     * Collection licenseId
     */
    public $licenseId = 'proprietary';

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
    private $statistics = null;
    
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
     *              type="enum",
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

    /**
     * Constructor
     *
     * @param array $id : collection id
     * @param RestoContext $context : RESTo context
     * @param RestoUser $user : RESTo user
     */
    public function __construct($id, $context, $user)
    {
        if (isset($id)) {

            // Collection identifier should be alphanumeric based only except for reserved '*' collection
            if (preg_match("/^[a-zA-Z0-9]+$/", $id) !== 1 || ctype_digit(substr($id, 0, 1))) {
                RestoLogUtil::httpError(400, 'Collection identifier must be an alphanumeric string [a-zA-Z0-9] and not starting with a digit');
            }

            $this->id = $id;
        }

        $this->context = $context;
        $this->user = $user;
    }

    /**
     * Load collection from database or from input data
     * Return 404 if collection is not found
     *
     * @param array $object
     * @return This object
     */
    public function load($object = null)
    {
        if (isset($object)) {
            return $this->loadFromJSON($object);
        }
        
        $cacheKey = 'collection:' . $this->id;
        $collectionObject = $this->context->fromCache($cacheKey);
    
        if (! isset($collectionObject)) {  

            $collectionObject = (new CollectionsFunctions($this->context->dbDriver))->getCollectionDescription($this->id);

            if (! isset($collectionObject)) {  
                return RestoLogUtil::httpError(404);
            }

            $this->context->toCache($cacheKey, $collectionObject);

        }
        
        foreach ($collectionObject as $key => $value) {
            $this->$key = $key === 'model' ? new $value(array(
                'collectionId' => $this->id,
                'addons' => $this->context->addons
            )) : $value;
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

        $this->loadFromJSON($object, true);
        
        return $this;
    }

    /**
     * Search features within collection
     *
     * @return array (FeatureCollection)
     */
    public function search()
    {
        return (new RestoFeatureCollection($this->context, $this->user, array($this->id => $this)))->load($this->model, $this);
    }

    /**
     * Add feature to the {collection}.features table
     *
     * @param array $data : GeoJSON file or file splitted in array
     * @param array $params : Insertion params
     */
    public function addFeature($data, $params)
    {
        return $this->model->storeFeature($this, $data, $params);
    }

    /**
     * Output collection description as an array
     *
     * @param array $options 
     */
    public function toArray($options = array())
    {
        
        $osDescription = $this->osDescription[$this->context->lang] ?? $this->osDescription['en'];

        $collectionArray = array(
            'id' => $this->id,
            'title' => $osDescription['ShortName'],
            'version' => $this->version ?? null,
            'description' => $osDescription['Description'],
            'keywords' => explode(' ', $osDescription['Tags']),
            'license' => $this->licenseId,
            'extent' => $this->getExtent(),
            'links' => array_merge(
                array(
                    array(
                        'rel' => 'self',
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $this->context->getUrl(false)
                    ),
                    array(
                        'rel' => 'root',
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $this->context->core['baseUrl']
                    ),
                    array(
                        'rel' => 'items',
                        'type' => RestoUtil::$contentTypes['geojson'],
                        'href' => $this->context->core['baseUrl'] . '/collections/' . $this->id . '/items'
                    )
                ), 
                $this->links ?? array()    
            ),
            'resto:info' => array(
                'model' => $this->model->getName(),
                'lineage' => $this->model->getLineage(),
                'osDescription' => $this->osDescription[$this->context->lang] ?? $this->osDescription['en'],
                'owner' => $this->owner
            )
        );

        foreach (array_values(array('providers', 'properties')) as $key) {
            if (isset($this->$key)) {
                $collectionArray[$key] = $this->$key;
            }
        }

        if ($this->visibility !== Resto::GROUP_DEFAULT_ID) {
            $collectionArray['visibility'] = $this->visibility;
        }
            
        if (isset($options['stats'])) {
            $collectionArray['summaries'] = $this->getSummaries($options['stats']);
            if ($options['stats']) {
                $collectionArray['summaries']['resto:stats'] = $this->statistics;
            }
        }

        return isset($this->context->addons['STAC']) ? array_merge($collectionArray, array(
            'stac_version' => STAC::STAC_VERSION,
            'stac_extensions' => $this->model->stacExtensions
        )) : $collectionArray;

    }

    /**
     * Output collection description as a JSON stream
     *
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false)
    {
        return json_encode($this->toArray(array(
            'stats' => isset($this->context->query['_stats']) ? filter_var($this->context->query['_stats'], FILTER_VALIDATE_BOOLEAN) : false
        )), $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES);
    }

    /**
     * Output collection description as an XML OpenSearch document
     */
    public function getOSDD()
    {
        return new OSDD($this->context, $this->model, $this->getStatistics($this->model->getAutoFacetFields()), $this);
    }

    /**
     * Return collection statistics
     * 
     * @param array $facetFields : Facet fields
     */
    public function getStatistics($facetFields = null)
    {
        if (!isset($this->statistics)) {
            $cacheKey = 'getStatistics:' . $this->id;
            $this->statistics = $this->context->fromCache($cacheKey);
            if (!isset($this->statistics)) {
                $this->statistics = (new FacetsFunctions($this->context->dbDriver))->getStatistics($this->id, $facetFields);
                $this->context->toCache($cacheKey, $this->statistics);
            }
        }
        return $this->statistics;
    }

    /**
     * Return STAC extent
     */
    public function getExtent()
    {
        return array(
            'spatial' => array(
                'bbox' => array(
                    $this->bbox
                ),
                'crs' => 'http://www.opengis.net/def/crs/OGC/1.3/CRS84'
            ),
            'temporal' => array(
                'interval' => array(
                    array(
                        $this->datetime['min'], $this->datetime['max']
                    )
                ),
                'trs' => 'http://www.opengis.net/def/uom/ISO-8601/0/Gregorian'
            )
        );
    }

    /**
     * Load collection parameters from input collection description
     * (See collection file example in examples/collections/S2.json)
     *
     * @param array $object : collection description as json file
     * @param boolean $update : true means update
     */
    private function loadFromJSON($object, $update = false)
    {

        /*
         * Check that object is a valid array
         */
        if (!is_array($object)) {
            RestoLogUtil::httpError(400, 'Invalid input JSON');
        }

        /*
         * This is an update - remove properties that *CANNOT BE* updated
         */
        if ($update) {
            unset($object['name'], $object['model']);
        }
        /*
         * Load for creation - mandatory properties are required
         */
        else {
            $this->checkCreationMandatoryProperties($object);
        }

        /*
         * Default collection visibility is the value of Resto::GROUP_DEFAULT_ID
         * [TODO] Allow to change visibility in collection
         */
        //$this->visibility = isset($object['visibility']) ? $object['visibility'] : Resto::GROUP_DEFAULT_ID;
        $this->visibility = Resto::GROUP_DEFAULT_ID;
        
        /*
         * Version
         */
        $this->version = $object['version'];
       
        /*
         * License - set to 'proprietary' if not specified
         */
        $this->licenseId = $object['licenseId'] ?? 'proprietary';
       
        /*
         * Set values
         */
        foreach (array_values(array('osDescription', 'providers', 'properties', 'links', 'rights')) as $key) {
            $this->$key = $object[$key] ?? array();
        }

        return $this;

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
        if (!isset($object['name']) || $this->id !== $object['name']) {
            RestoLogUtil::httpError(400, 'Property "name" and collection id differ');
        }

        /*
         * Model name must be set
         */
        if (!isset($object['model'])) {
            RestoLogUtil::httpError(400, 'Property "model" is mandatory');
        }
        
        if (!class_exists($object['model']) || !is_subclass_of($object['model'], 'RestoModel')) {
            RestoLogUtil::httpError(400, 'Model "' . $object['model'] . '" is not a valid model name');
        } 
        
        /*
         * At least an english OpenSearch Description object is mandatory
         */
        if (!isset($object['osDescription']) || !is_array($object['osDescription']) || !isset($object['osDescription']['en']) || !is_array($object['osDescription']['en'])) {
            RestoLogUtil::httpError(400, 'English OpenSearch description is mandatory');
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
     * Return STAC summaries
     * 
     * @param boolean $stats
     */
    private function getSummaries($stats = false)
    {
        $summaries = array(
            'datetime' => $this->datetime
        );
        
        /*
         * Compute statistics from facets
         */
        if (!isset($this->statistics)) {
            $this->getStatistics($this->getFacetFields($stats));
        }
        foreach ($this->statistics['facets'] as $key => $value) {
            $summaries[$this->model->stacMapping[$key] ?? $key] = array_keys($value);
        }

        return $summaries;
    }

    /**
     * Get facet fields for summaries
     * 
     * @param boolean $stats
     * @return array 
     */
    private function getFacetFields($stats)
    {
        $facetFields = array();
        if ($stats) {
            foreach (array_values($this->model->facetCategories) as $facetCategory) {
                for ($i = 0, $ii = count($facetCategory); $i < $ii; $i++)
                {
                    $facetFields[] = $facetCategory[$i];
                }
            }
        }
        else {
            $facetFields = $this->model->getAutoFacetFields();
        }
        return $facetFields;
    }
    
}
