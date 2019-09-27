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
 *              property="quicklook",
 *              type="string",
 *              description="Url to the feature quicklook"
 *          ),
 *          @OA\Property(
 *              property="thumbnail",
 *              type="string",
 *              description="Url to the feature thumbnail"
 *          ),
 *          @OA\Property(
 *              property="udpated",
 *              type="string",
 *              description="The date when the feature was updated (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ)"
 *          ),
 *          @OA\Property(
 *              property="published",
 *              type="string",
 *              description="The date when the feature was published (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ)"
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
 *              @OA\Items(
 *                  type="object",
 *                  @OA\Property(
 *                      property="rel",
 *                      type="string",
 *                      description="Relationship between the feature and the linked document/resource"
 *                  ),
 *                  @OA\Property(
 *                      property="type",
 *                      type="string",
 *                      description="Mimetype of the resource"
 *                  ),
 *                  @OA\Property(
 *                      property="title",
 *                      type="string",
 *                      description="Title of the resource"
 *                  ),
 *                  @OA\Property(
 *                      property="href",
 *                      type="string",
 *                      description="Url to the resource"
 *                  )
 *              ),
 *              description="Additional resources linked to the feature"
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
 *                      "href": "https://ds.snapplanet.io/2.0/collections/S2/items/b9eeaf68-5127-53e5-97ff-ddf44984ef56?&collectionName=S2.json&lang=en"
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
 *              property="quicklook",
 *              type="string",
 *              description="Url to the feature quicklook"
 *          ),
 *          @OA\Property(
 *              property="thumbnail",
 *              type="string",
 *              description="Url to the feature thumbnail"
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
     * Parent collection name
     */
    public $collectionName;

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

    /*
     * Download feature product
     *
     *  - 'path' should be a non public url to the local file (e.g. 'file://data/images/abcd.tif')
     *  - 'href' should be a public url (e.g. 'http://my.server/images/abcd.tif)
     *
     * [IMPORTANT] If both 'path' and 'href' are provided, 'path' is used first
     *
     */
    public function download()
    {
 
        /*
         * Not downloadable
         */
        if (! isset($this->featureArray['properties']['links']['download'])) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * Default mimeType
         */
        $defaultMimetype = 'application/octet-stream';

        /*
         * Get pointer to download object
         */
        $downloadInfo = $this->featureArray['properties']['links']['download'];

        /*
         * Download hosted resource with support of Range and Partial Content
         * (See http://stackoverflow.com/questions/157318/resumable-downloads-when-using-php-to-send-the-file)
         */
        if (isset($downloadInfo['path'])) {
            if (! is_file($downloadInfo['path'])) {
                RestoLogUtil::httpError(404);
            }

            return $this->streamLocalUrl(realpath($downloadInfo['path']), $downloadInfo['type'] ?? $defaultMimetype);
        }
        /*
         * Resource is on an external url
         */
        elseif (RestoUtil::isUrl($downloadInfo['href'])) {
            return $this->streamExternalUrl($downloadInfo['href'], $downloadInfo['type'] ?? $defaultMimetype);
        }
        /*
         * Not Found
         */
        else {
            RestoLogUtil::httpError(404);
        }
    }

    /**
     * Remove feature from database
     */
    public function removeFromStore()
    {
        return (new FeaturesFunctions($this->context->dbDriver))->removeFeature($this);
    }

    /**
     * Output product description as a PHP array
     *
     * @param boolean publicOutput
     */
    public function toArray($publicOutput = false)
    {
        
        /*
         * For API output, links are changed from associative array to array
         * with $key set as "rel" element
         *
         * For security reason, "realpath" property is removed from output
         */
        if ($publicOutput) {
            $feature = $this->featureArray;

            // Clean properties
            $feature['properties'] = $this->cleanProperties($feature['properties'], array(
                'id',
                'visibility',
                'owner'
            ));

            // Correct links
            $links = array();
            foreach (array_keys($feature['properties']['links']) as $key) {
                if (isset($feature['properties']['links'][$key]['realpath'])) {
                    unset($feature['properties']['links'][$key]['realpath']);
                }
                $links[] = array_merge(array('rel' => $key), $feature['properties']['links'][$key]);
            }
            $feature['properties']['links'] = $links;
            
            return $feature;
        }

        return $this->featureArray;
    }

    /**
     * Output product description as a GeoJSON Feature
     *
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false)
    {
        return json_encode($this->toArray(true), $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES);
    }

    /**
     * Output product description as an ATOM feed
     */
    public function toATOM()
    {

        /*
         * Initialize ATOM feed
         */
        $atomFeed = new ATOMFeed($this->featureArray['id'], $this->featureArray['properties']['title'], 'resto feature', isset($this->collection) ? $this->collection->model : new DefaultModel());

        /*
         * Entry for feature
         */
        $atomFeed->addEntry($this->featureArray, $this->context);

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
        if (empty($this->featureArray) || (isset($options['collectionName']) && $options['collectionName'] !== $this->featureArray['properties']['collection'])) {
            $this->id = null;
            return;
        } 
        
        $this->id = $this->featureArray['id'];
        $this->collectionName = $this->featureArray['properties']['collection'];
        
    }

    /**
     * Stream local file either from PHP or from Apache/Nginx
     *
     * @param string $path
     * @param string $mimeType
     * @param boolean $multipart
     */
    private function streamLocalUrl($path, $mimeType, $multipart = true)
    {
        switch ($this->context->core['streamMethod']) {

           /*
            * Optimized download with Apache module XsendFile
            */
            case 'apache':
                return $this->streamApache($path, $mimeType);

           /*
            * Optimized download with Apache module XsendFile
            */
            case 'nginx':
                return $this->streamNginx($path, $mimeType);

           /*
            * Slower but generic PHP stream
            */
            default:
                return $this->streamPHP($path, $mimeType, $multipart);

        }
    }

    /**
     *
     * Download hosted resource with support of Range and Partial Content
     * (See http://stackoverflow.com/questions/3697748/fastest-way-to-serve-a-file-using-php)
     *
     * @param string $path
     * @param string $mimeType
     * @param boolean $multipart
     * @return boolean
     */
    private function streamPHP($path, $mimeType, $multipart)
    {

        /*
         * Open file
         */
        $file = fopen($path, 'rb');
        if (!is_resource($file)) {
            RestoLogUtil::httpError(404);
        }

        /*
         * Compute file size
         */
        $size = sprintf('%u', filesize($path));
        $range = $multipart ? $this->getMultipartRange($size, filter_input(INPUT_SERVER, 'HTTP_RANGE', FILTER_SANITIZE_STRING)) : $this->getSimpleRange($size);

        /*
         * Set range and headers
         */
        header('HTTP/1.1 ' . (($range[0] > 0) || ($range[1] < ($size - 1)) ?  '206 Partial Content' : '200 OK'));
        $this->setDownloadHeaders($path, $mimeType);
        header('Content-Length: ' . sprintf('%u', $range[1] - $range[0] + 1));
        header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));

        /*
         * Read file
         */
        $this->readFile($file, $range);

        fclose($file);
    }

    /**
     * Flush result
     *
     * @param File $file
     * @param array $range
     */
    private function readFile($file, $range)
    {

        /*
         * Multipart case
         */
        if ($range[0] > 0) {
            fseek($file, $range[0]);
        }

        /*
         * Stream result
         */
        while ((feof($file) !== true) && (connection_status() === CONNECTION_NORMAL)) {
            echo fread($file, 10 * 1024 * 1024);
            set_time_limit(0);
            flush();
        }
    }

    /**
     * Get range from HTTP_RANGE and set headers accordingly
     *
     * In case of multiple ranges requested, only the first range is served
     * (http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt)
     *
     * @param integer $size
     *
     */
    private function getSimpleRange($size)
    {
        return array(0, $size - 1);
    }

    /**
     * Get range from HTTP_RANGE and set headers accordingly
     *
     * In case of multiple ranges requested, only the first range is served
     * (http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt)
     *
     * @param integer $size
     * @param string $httpRange
     *
     */
    private function getMultipartRange($size, $httpRange)
    {
        $range = array(0, $size - 1);
        if (isset($httpRange)) {
            $range = array_map('intval', explode('-', preg_replace('~.*=([^,]*).*~', '$1', $httpRange)));
            if (empty($range[1]) === true) {
                $range[1] = $size - 1;
            }
            foreach ($range as $key => $value) {
                $range[$key] = max(0, min($value, $size - 1));
            }
        }
        return $range;
    }

    /**
     * Set HTTP headers for download
     *
     * @param string $path
     * @param string $mimeType
     */
    private function setDownloadHeaders($path, $mimeType)
    {
        header('Pragma: public');
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
    }

    /**
     * Stream file using Apache XSendFile
     *
     * @param string $path
     * @param string $mimeType
     */
    private function streamApache($path, $mimeType)
    {
        $this->setDownloadHeaders($path, $mimeType);
        header('X-Sendfile: ' . $path);
    }

    /**
     * Stream file using Nginx X-Accel-Redirect
     *
     * @param string $path
     * @param string $mimeType
     */
    private function streamNginx($path, $mimeType)
    {
        $this->setDownloadHeaders($path, $mimeType);
        header('X-Accel-Redirect: ' . $path);
    }

    /**
     * Stream file from external url
     *
     * @param string $href
     * @param string $mimeType
     * @return boolean
     */
    private function streamExternalUrl($href, $mimeType)
    {
        $handle = fopen($href, "rb");
        if ($handle === false) {
            RestoLogUtil::httpError(500, 'Resource cannot be downloaded');
        }
        header('HTTP/1.1 200 OK');
        header('Content-Disposition: attachment; filename="' . basename($href) . '"');
        header('Content-Type: ' . $mimeType);
        while (!feof($handle) && (connection_status() === CONNECTION_NORMAL)) {
            echo fread($handle, 10 * 1024 * 1024);
            flush();
        }
        return fclose($handle);
    }

    /**
     * Remove redondant or unwanted properties
     *
     * @param array $properties
     */
    private function cleanProperties($properties, $discard)
    {
        $output = array();
        
        foreach (array_keys($properties) as $key) {

            // Remove null properties
            if (! isset($properties[$key]) || in_array($key, $discard)) {
                continue;
            }
            
            $output[$key] = $properties[$key];
        }

        return $output;
    }
}
