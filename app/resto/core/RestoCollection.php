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
 *      schema="OutputCollection",
 *      required={"name", "visibility", "owner", "model", "licenseId", "osDescription", "statistics"},
 *      @OA\Property(
 *          property="name",
 *          type="string",
 *          description="Unique collection name. It is used as the collection identifier"
 *      ),
 *      @OA\Property(
 *          property="visibility",
 *          type="enum",
 *          enum={"public", "<group id>"},
 *          description="Visibility of this collection. *public* collections are visible to all users. Non public collections are visible to owner and member of <group id> only"
 *      ),
 *      @OA\Property(
 *          property="owner",
 *          type="string",
 *          description="Collection owner (i.e. user identifier)"
 *      ),
 *      @OA\Property(
 *          property="model",
 *          type="string",
 *          description="[For developper] Name of the collection model corresponding to the class under $SRC/include/resto/Models without *Model* suffix."
 *      ),
 *      @OA\Property(
 *          property="licenseId",
 *          type="enum",
 *          enum={"unlicensed","unlicensedwithregistration", "<license id>"},
 *          description="License for this collection. *unlicensed* collection are available for all users. *unlicensedwithregistration* collection are available for all users that are registered an authenticated"
 *      ),
 *      @OA\Property(
 *          property="osDescription",
 *          type="object",
 *          ref="#/components/schemas/OpenSearchDescription"
 *      ),
 *      @OA\Property(
 *          property="statistics",
 *          type="object",
 *          ref="#/components/schemas/Statistics"
 *      ),
 *      example={
 *          "TBD":"TBD"
 *      }
 *  )
 */
class RestoCollection
{

    /*
     * Collection name must be unique
     */
    public $name =  null;

    /*
     * Data model for this collection
     */
    public $model = null;

    /*
     * Properties mapping
     */
    public $propertiesMapping = array();

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
    public $licenseId = 'unlicensed';

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

    /*
     * Collection rights - set during creation or during update
     */
    private $rights = array();

    /**
     * Constructor
     *
     * @param array $name : collection name
     * @param RestoContext $context : RESTo context
     * @param RestoUser $user : RESTo user
     */
    public function __construct($name, $context, $user)
    {
        if (isset($name)) {

            // Collection name should be alphanumeric based only except for reserved '*' collection
            if (preg_match("/^[a-zA-Z0-9]+$/", $name) !== 1 || ctype_digit(substr($name, 0, 1))) {
                RestoLogUtil::httpError(400, 'Collection name must be an alphanumeric string [a-zA-Z0-9] and not starting with a digit');
            }

            $this->name = $name;
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
        
        $cacheKey = $this->context->path . join(',', $this->context->query);
        $collectionObject = $this->context->fromCache($cacheKey) ?? (new CollectionsFunctions($this->context->dbDriver))->getCollectionDescription($this->name);
        
        if (! isset($collectionObject)) {
            return RestoLogUtil::httpError(404);
        }
        
        foreach ($collectionObject as $key => $value) {
            $this->$key = $key === 'model' ? new $value() : $value;
        }
        
        $this->context->toCache($cacheKey, $collectionObject);

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
        return (new RestoFeatureCollection($this->context, $this->user))->load($this->model, $this);
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
     * @param boolean $setStatistics (true to return statistics)
     */
    public function toArray($setStatistics = true)
    {
        $collectionArray = array(
            'name' => $this->name,
            'model' => $this->model->getName(),
            //'lineage' => $this->model->getLineage(),
            'licenseId' => $this->licenseId,
            'osDescription' => $this->osDescription[$this->context->lang] ?? $this->osDescription['en'],
            'owner' => $this->owner
        );

        if ($this->visibility !== Resto::GROUP_DEFAULT_ID) {
            $collectionArray['visibility'] = $this->visibility;
        }
            
        if ($setStatistics) {
            $collectionArray['statistics'] = $this->getStatistics();
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
        return new OSDD($this->context, $this->model, $this->getStatistics(), $this);
    }

    /**
     * Remove collection  from RESTo database
     */
    public function removeFromStore()
    {
        return (new CollectionsFunctions($this->context->dbDriver))->removeCollection($this->name);
    }

    /**
     * Return collection statistics
     */
    public function getStatistics()
    {
        if (!isset($this->statistics)) {
            $cacheKey = 'getStatistics:' . $this->name;
            $this->statistics = $this->context->fromCache($cacheKey) ?? (new FacetsFunctions($this->context->dbDriver))->getStatistics($this, $this->model->getFacetFields());
            $this->context->toCache($cacheKey, $this->statistics);
        }
        return $this->statistics;
    }

    /**
     * Load collection parameters from input collection description
     * Collection description is a JSON file with the following structure
     *
     *      {
     *          "name": "Charter",
     *          "controller": "default",
     *          "visibility": "public",
     *          "licenseId": "license",
     *          "rights":{
     *              "download":0,
     *              "visualize":1
     *          },
     *          "osDescription": {
     *              "en": {
     *                  "ShortName": "International Charter Space and Major Disasters",
     *                  "LongName": "International Charter Space and Major Disasters catalog",
     *                  "Description": "The International Charter aims at providing a unified system of space data acquisition and delivery to those affected by natural or man-made disasters through Authorized Users. Each member agency has committed resources to support the provisions of the Charter and thus is helping to mitigate the effects of disasters on human life and property",
     *                  "Tags": "international charter space disasters",
     *                  "Developer": "J\u00e9r\u00f4me Gasperi",
     *                  "Contact": "jerome.gasperi@gmail.com",
     *                  "Query": "Cyclones in Asia in october 2013",
     *                  "Attribution": "RESTo framework. Copyright 2013, All Rights Reserved"
     *              },
     *              "fr": {
     *                  ...
     *              }
     *          },
     *          "propertiesMapping": {
     *              "id": "{a:1} will be replaced by id property value",
     *              "organisationName": "This is a constant"
     *              ...
     *          }
     *      }
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
         * Load for creation - mandatory properties are required
         */
        if (! $update) {
            $this->checkCreationMandatoryProperties($object);
        }
        /*
         * Otherwise, these are forbidden
         */
        else {
            $this->checkUpdateForbiddenProperties($object);
        }

        /*
         * Default collection visibility is the value of Resto::GROUP_DEFAULT_ID
         * [TODO] Allow to change visibility in collection
         */
        //$this->visibility = isset($object['visibility']) ? $object['visibility'] : Resto::GROUP_DEFAULT_ID;
        $this->visibility = Resto::GROUP_DEFAULT_ID;
        
        /*
         * License - set to 'unlicensed' if not specified
         */
        $this->licenseId = $object['licenseId'] ?? 'unlicensed';
        
        /*
         * Properties mapping
         */
        $this->propertiesMapping = $object['propertiesMapping'] ?? $this->propertiesMapping;
        
        /*
         * OpenSearch description
         */
        $this->osDescription = $object['osDescription'] ?? $this->osDescription;

        /*
         * Rights
         */
        $this->rights = $object['rights'] ?? $this->rights;

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
        if (!isset($object['name']) || $this->name !== $object['name']) {
            RestoLogUtil::httpError(400, 'Property "name" and collection name differ');
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
        $this->model = new $object['model']();
        

        /*
         * Collection owner is the current user
         */
        $this->owner = $this->user->profile['id'];

    }
    
    /**
     * Check forbidden properties for collection update
     *
     * @param array $object
     */
    private function checkUpdateForbiddenProperties($object)
    {
        foreach (array('name', 'model') as $key) {
            if (isset($object[$key])) {
                RestoLogUtil::httpError(400, 'Property "' . $key . '" cannot be updated');
            }
        }
    }
}
