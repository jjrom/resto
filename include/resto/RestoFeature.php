<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2013 Jérôme Gasperi <https://github.com/jjrom>
 * 
 * jerome[dot]gasperi[at]gmail[dot]com
 * 
 * 
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
 * 
 */

/**
 * RESTo Feature
 */
class RestoFeature {

    /*
     * Feature unique identifier 
     */
    public $identifier;
    
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
     * Model
     */
    private $model;
    
    /*
     * Feature
     */
    private $feature;
    
    /*
     * Name to display in properties links
     */
    private $displayedName;
    
    /*
     * Download path on disk
     */
    private $resourceInfos;
    
    /**
     * Constructor 
     * 
     * @param array or string $featureOrIdentifier : Feature identifier or properties
     * @param RestoResto $context : Resto Context
     * @param RestoUser $user : Resto user
     * @param array $options
     * 
     */
    public function __construct($featureOrIdentifier, $context, $user, $options = array()) {
        
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            RestoLogUtil::httpError(500, 'Context is undefined or not valid');
        }
        
        $this->context = $context;
        $this->user = $user;
        $this->identifier = is_array($featureOrIdentifier) ? $featureOrIdentifier['identifier'] : $featureOrIdentifier;
        if (!RestoUtil::isValidUUID($this->identifier)) {
            RestoLogUtil::httpError(404);
        }
        
        $this->initialize($featureOrIdentifier, $options);
        
        return $this;
        
    }
    
    /*
     * Download feature product
     */
    public function download() {
        
        /*
         * Not downloadable
         */
        if (!isset($this->feature['properties']['services']) || !isset($this->feature['properties']['services']['download']))  {
            RestoLogUtil::httpError(404);;
        }
        
        /*
         * Download hosted resource with support of Range and Partial Content
         * (See http://stackoverflow.com/questions/157318/resumable-downloads-when-using-php-to-send-the-file)
         */
        if (isset($this->resourceInfos)) {
            
            if (!isset($this->resourceInfos['path']) || !is_file($this->resourceInfos['path'])) {
                RestoLogUtil::httpError(404);;
            }
           
            /*
             * Optimized download with Apache module XsendFile
             */
            if (in_array('mod_xsendfile', apache_get_modules())) {
                return $this->streamApache();
            }
            
            return $this->stream(realpath($this->resourceInfos['path']), isset($this->resourceInfos['mimeType']) ? $this->resourceInfos['mimeType'] : 'application/octet-stream');
            
        }
        /*
         * Resource is on an external url
         */
        else if (RestoUtil::isUrl($this->feature['properties']['services']['download']['url'])) {
            return $this->streamExternalUrl();
        }
        /*
         * Not Found
         */
        else {
            RestoLogUtil::httpError(404);;
        }
        
    }
    
    /**
     * Remove feature from database
     */
    public function removeFromStore() {
        $this->context->dbDriver->remove(RestoDatabaseDriver::FEATURE, array('feature' => $this));
    }
    
    /**
     * Output product description as a PHP array
     */
    public function toArray() {
        return $this->feature;
    }
    
    /**
     * Output product description as a GeoJSON Feature
     * 
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false) {
        return RestoUtil::json_format($this->feature, $pretty);
    }
    
    /**
     * Output product description as an ATOM feed
     */
    public function toATOM() {
        
        /*
         * Initialize ATOM feed
         */
        $atomFeed = new RestoATOMFeed($this->feature['id'], isset($this->description['properties']['title']) ? $this->description['properties']['title'] : '', 'TODO');
        
        /*
         * Entry for feature
         */
        $this->addAtomEntry($atomFeed);
        
        /*
         * Return ATOM result
         */
        return $atomFeed->toString();
        
    }
    
    /**
     * Add an atom entry within $xml document
     * 
     * @param RestoATOMFeed $atomFeed
     */
    public function addAtomEntry($atomFeed) {
        
        /*
         * Add entry
         */
        $atomFeed->startElement('entry');
        
        /*
         * Set base elements
         */
        $atomFeed->writeElements(array(
            'id' => $this->feature['id'], // ! THIS SHOULD BE AN ABSOLUTE UNIQUE  AND PERMANENT IDENTIFIER !!
            'dc:identifier' => $this->feature['id'], // Local identifier - i.e. last part of uri
            'title' => isset($this->feature['properties']['title']) ? $this->feature['properties']['title'] : '',
            'published' => $this->feature['properties']['published'],
            'updated' => $this->feature['properties']['updated'],
            /*
             * Element 'dc:date' - date of the resource is duration of acquisition following
             * the Dublin Core Collection Description on date
             * (http://www.ukoln.ac.uk/metadata/dcmi/collection-RKMS-ISO8601/)
             */
            'dc:date' => $this->feature['properties']['startDate'] . '/' . $this->feature['properties']['completionDate']
        ));
        
        /*
         * Time
         */
        $atomFeed->addGmlTime($this->feature['properties']['startDate'], $this->feature['properties']['completionDate']);
        
        /*
         * georss:polygon
         */
        $atomFeed->addGeorssPolygon($this->feature['geometry']['coordinates']);

        /*
         * Links
         */
        $this->addAtomLinks($atomFeed);
        
        /*
         * Media (i.e. Quicklook / Thumbnail / etc.)
         */
        $this->addAtomMedia($atomFeed);
        
        /*
         * Summary
         */
        $atomFeed->startElement('summary');
        $atomFeed->writeAttributes(array('type' => 'text'));
        $atomFeed->text((isset($this->feature['properties']['platform']) ? $this->feature['properties']['platform'] : '') . (isset($this->feature['properties']['platform']) && isset($this->feature['properties']['instrument']) ? '/' . $this->feature['properties']['instrument'] : '') . ' ' . $this->context->dictionary->translate('_acquiredOn', $this->feature['properties']['startDate']));
        $atomFeed->endElement(); // content
        
        /*
         * entry - close element
         */
        $atomFeed->endElement(); // entry
    }
    
    /**
     * Set feature either from input description or from database
     * 
     * @param string/array $featureOrIdentifier
     * @param array $options
     */
    private function initialize($featureOrIdentifier, $options) {
        
        if (isset($options['collection'])) {
            $this->collection = $options['collection'];
            $this->displayedName = isset($options['forceCollectionName']) && $options['forceCollectionName'] ? $this->collection->name : null;
            $this->model = $this->collection->model;
        }
        else {
            $this->model = new RestoModel_default($this->context, $this->user);
        }
        
        /*
         * Load from input array
         */
        if (is_array($featureOrIdentifier)) {
            $this->setFeature($featureOrIdentifier);
        }
        /*
         * ...or load from database
         */
        else {
            $this->setFeature($this->context->dbDriver->get(RestoDatabaseDriver::FEATURE_DESCRIPTION, array(
                'featureIdentifier' => $this->identifier,
                'model' => $this->model,
                'collectionName' => isset($this->collection) ? $this->collection->name : null
                ))
            );
        }
    }
    
    /**
     * Set feature properties
     */
    private function setFeature($properties) {

        /*
         * No result - throw Not Found exception
         */
        if (!$properties) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * Variables
         */
        $searchUrl = $this->context->baseUrl . '/api/collections' . (isset($this->displayedName) ? '/' . $this->displayedName : '' ) . '/search.json';
        $thisUrl = isset($this->collection) ? RestoUtil::restoUrl($this->collection->getUrl(), $this->identifier) : RestoUtil::restoUrl($this->context->baseUrl, '/collections/' . $properties['collection'] . '/' . $this->identifier);
        
        /*
         * Set collection
         */
        $this->setCollection($properties['collection']);
        
        /*
         * Update metadata values from propertiesMapping
         */
        $this->updatePaths($properties);
        
        /*
         * Set date keywords
         */
        if (isset($properties[$this->model->searchFilters['time:start']['key']])) {
            $this->setDateKeywords($properties, $searchUrl);
        }
        
        /*
         * Set other keywords
         */
        $this->setKeywords($properties, $searchUrl);
        
        /*
         * Set landuse
         */
        $this->setLanduse($properties);
        
        /*
         * Set services
         */
        $this->setServices($properties, $thisUrl);
        
        /*
         * Set links
         */
        $this->setLinks($properties, $thisUrl);
        
        /*
         * Set geometry
         */
        $geometry = isset($properties['geometry']) ? $properties['geometry'] : null;
        
        /*
         * Clean properties
         */
        $this->cleanProperties($properties);
        
        $this->feature = array(
            'type' => 'Feature',
            'id' => $this->identifier,
            'geometry' => $geometry,
            'properties' => $properties
        );
        
    }
    
    /**
     * Add ATOM feed links
     * 
     * @param RestoATOMFeed $atomFeed
     */
    private function addAtomLinks($atomFeed) {
        
        /*
         * General links
         */
        if (is_array($this->feature['properties']['links'])) {
            for ($j = 0, $k = count($this->feature['properties']['links']); $j < $k; $j++) {
                $atomFeed->startElement('link');
                $atomFeed->writeAttributes(array(
                    'rel' => $this->feature['properties']['links'][$j]['rel'],
                    'type' => $this->feature['properties']['links'][$j]['type'],
                    'title' => $this->feature['properties']['links'][$j]['title'],
                    'href' => $this->feature['properties']['links'][$j]['href']
                ));
                $atomFeed->endElement(); // link
            }
        }
        
        /*
         * Element 'enclosure' - download product
         *  read from $this->feature['properties']['archive']
         */
        if (isset($this->feature['properties']['services']['download']['url'])) {
            $atomFeed->startElement('link');
            $atomFeed->writeAttributes(array(
                'rel' => 'enclosure',
                'type' => isset($this->feature['properties']['services']['download']['mimeType']) ? $this->feature['properties']['services']['download']['mimeType'] : 'application/unknown',
                'length' => isset($this->feature['properties']['services']['download']['size']) ? $this->feature['properties']['services']['download']['size'] : 0,
                'title' => 'File for ' . $this->feature['id'] . ' product',
                'metalink:priority' => 50,
                'href' => $this->feature['properties']['services']['download']['url']
            ));
            $atomFeed->endElement(); // link
        }
        
    }
    
    /**
     * Add ATOM feed media
     * 
     * @param RestoATOMFeed $atomFeed
     */
    private function addAtomMedia($atomFeed) {
        
        /*
         * Quicklook / Thumbnail
         */
        if (isset($this->feature['properties']['thumbnail']) || isset($this->feature['properties']['quicklook'])) {

            /*
             * rel=icon
             */
            if (isset($this->feature['properties']['quicklook'])) {
                $atomFeed->startElement('link');
                $atomFeed->writeAttributes(array(
                    'rel' => 'icon',
                    //'type' => 'TODO',
                    'title' => 'Browse image URL for ' . $this->feature['id'] . ' product',
                    'href' => $this->feature['properties']['quicklook']
                ));
                $atomFeed->endElement(); // link
            }

            /*
             * media:group
             */
            $atomFeed->startElement('media:group');
            if (isset($this->feature['properties']['thumbnail'])) {
                $atomFeed->addMedia('THUMNAIL', $this->feature['properties']['thumbnail']);
            }
            if (isset($this->feature['properties']['quicklook'])) {
                $atomFeed->addMedia('QUICKLOOK', $this->feature['properties']['quicklook']);
            }
            $atomFeed->endElement();
        }

    }
    
    /**
     * 
     * Download hosted resource with support of Range and Partial Content
     * (See http://stackoverflow.com/questions/3697748/fastest-way-to-serve-a-file-using-php)
     *
     * @param string $path
     * @param string $mimeType
     * @param type $multipart
     * @return boolean
     */
    private function stream($path, $mimeType = 'application/octet-stream', $multipart = true) {

        /*
         * Open file
         */
        $file = fopen($path, 'rb');
        if (!is_resource($file)) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * Set range and headers
         */
        $range = $this->getRange($path, $multipart);
        
        /*
         * Set headers
         */
        $this->setDownloadHeaders($mimeType, $path, $range);
        
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

        fclose($file);
        
    }
    
    /**
     * Get range from HTTP_RANGE and set headers accordingly
     * 
     * In case of multiple ranges requested, only the first range is served
     * (http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt)
     * 
     * @param string $path
     * @param boolean $multipart
     * 
     */
    private function getRange($path, $multipart) {
        
        $size = sprintf('%u', filesize($path));
        
        if (!$multipart) {
            return array(0, $size - 1);
        }
        
        $range = array(0, $size - 1);
        $httpRange = filter_input(INPUT_SERVER, 'HTTP_RANGE', FILTER_SANITIZE_STRING);
        if (isset($httpRange)) {
            $range = array_map('intval', explode('-', preg_replace('~.*=([^,]*).*~', '$1', $httpRange)));

            if (empty($range[1]) === true) {
                $range[1] = $size - 1;
            }

            foreach ($range as $key => $value) {
                $range[$key] = max(0, min($value, $size - 1));
            }

            if (($range[0] > 0) || ($range[1] < ($size - 1))) {
                header(sprintf('%s %03u %s', 'HTTP/1.1', 206, 'Partial Content'), true, 206);
            }
        }
        header('Accept-Ranges: bytes');
        header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));
        
        return $range;
    }
    
    /**
     * Set HTTP headers for download
     * 
     * @param type $mimeType
     * @param type $path
     * @param type $range
     */
    private function setDownloadHeaders($mimeType, $path, $range) {
        header('Pragma: public');
        header('Cache-Control: public, no-cache');
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . sprintf('%u', $range[1] - $range[0] + 1));
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Transfer-Encoding: binary');
    }
    
    /**
     * Stream file using Apache XSendFile
     * 
     * @return type
     */
    private function streamApache() {
        header('HTTP/1.1 200 OK');
        header('Pragma: public');
        header('Expires: -1');
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
        header('X-Sendfile: ' . $this->resourceInfos['path']);
        header('Content-Type: ' . isset($this->resourceInfos['mimeType']) ? $this->resourceInfos['mimeType'] : 'application/unknown');
        header('Content-Disposition: attachment; filename="' . basename($this->resourceInfos['path']) . '"');
        header('Accept-Ranges: bytes');
    }
  
    /**
     * Stream file from external url
     * 
     * @return type
     */
    private function streamExternalUrl() {
        $handle = fopen($this->feature['properties']['services']['download']['url'], "rb");
        if ($handle === false) {
            RestoLogUtil::httpError(500, 'Resource cannot be downloaded');
        }
        header('HTTP/1.1 200 OK');
        header('Content-Disposition: attachment; filename="' . basename($this->feature['properties']['services']['download']['url']) . '"');
        header('Content-Type: ' . isset($this->feature['properties']['services']['download']['mimeType']) ? $this->feature['properties']['services']['download']['mimeType'] : 'application/unknown');
        while (!feof($handle) && (connection_status() === CONNECTION_NORMAL)) {
            echo fread($handle, 10 * 1024 * 1024);
            flush();
        }
        return fclose($handle);
    }
      
    /**
     * Retrieve collection if not set
     * 
     * @param string $collectionName
     */
    private function setCollection($collectionName) {
        if (!isset($this->collection)) {
            $this->collection = new RestoCollection($collectionName, $this->context, $this->user, array('autoload' => true));
            $this->model = $this->collection->model;
        }
    }
    
    /**
     * Update metadata values from propertiesMapping
     * 
     * @param array $properties
     */
    private function updatePaths(&$properties) {
        
        /*
         * Update dynamically metadata, quicklook and thumbnail path if required before the replaceInTemplate
         */
        if (method_exists($this->model,'generateMetadataPath')) {
            $properties['metadata'] = $this->model->generateMetadataPath($properties);
        }

        if (method_exists($this->model,'generateQuicklookPath')) {
            $properties['quicklook'] = $this->model->generateQuicklookPath($properties);
        }

        if (method_exists($this->model,'generateThumbnailPath')) {
            $properties['thumbnail'] = $this->model->generateThumbnailPath($properties);
        }
        
        /*
         * Modify properties as defined in collection propertiesMapping associative array
         */
        if (isset($this->collection->propertiesMapping)) {
            foreach (array_keys($this->collection->propertiesMapping) as $key) {
                $properties[$key] = RestoUtil::replaceInTemplate($this->collection->propertiesMapping[$key], $properties);
            }
        }
        
    }
    
    /**
     * Add keywords for dedicated filters
     * 
     * @param array $properties
     * @param string $searchUrl
     */
    private function setKeywords(&$properties, $searchUrl) {
        foreach (array_keys($this->model->searchFilters) as $key) {
            if (isset($this->model->searchFilters[$key]['keyword']) && isset($properties[$this->model->searchFilters[$key]['key']])) {
                
                /*
                 * Set multiple words within quotes 
                 */
                $name = RestoUtil::replaceInTemplate($this->model->searchFilters[$key]['keyword']['value'], $properties);
                $splitted = explode(' ', $name);
                
                if (count($splitted) > 1) {
                    $name = '"' . $name . '"';
                }
                $properties['keywords'][] = array(
                    'name' => $name,
                    'id' => $this->model->searchFilters[$key]['keyword']['type'] . ':' . $name,
                    'href' => RestoUtil::updateUrl($searchUrl, array($this->model->searchFilters['searchTerms']['osKey'] => $name))
                );
            }
        }
    }
    
    /**
     * Add a keyword for year, month and day of acquisition
     * 
     * @param array $properties
     * @param string $searchUrl
     */
    private function setDateKeywords(&$properties, $searchUrl) {
        $yearKeyword = $this->getYearKeyword($properties, $searchUrl);
        $monthKeyword = $this->getMonthKeyword($properties, $searchUrl, $yearKeyword);
        $dayKeyword = $this->getDayKeyword($properties, $searchUrl, $monthKeyword);
        $properties['keywords'][] = $yearKeyword;
        $properties['keywords'][] = $monthKeyword;
        $properties['keywords'][] = $dayKeyword;
    }
    
    /**
     * Add a keyword for year
     * 
     * @param array $properties
     * @param string $searchUrl
     */
    private function getYearKeyword($properties, $searchUrl) {
        $year = substr($properties[$this->model->searchFilters['time:start']['key']], 0, 4);
        $idYear = 'year:' . $year;
        $hashYear = RestoUtil::getHash($idYear);
        return array(
            'name' => $year,
            'id' => $idYear,
            'hash' => $hashYear,
            'href' => RestoUtil::updateUrl($searchUrl, array($this->model->searchFilters['searchTerms']['osKey'] => $year, $this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
    }
    
    /**
     * Add a keyword for month
     * 
     * @param array $properties
     * @param string $searchUrl
     */
    private function getMonthKeyword($properties, $searchUrl, $parentKeyword) {
        $month = substr($properties[$this->model->searchFilters['time:start']['key']], 0, 7);
        $idMonth = 'month:' . $month;
        $hashMonth = RestoUtil::getHash($idMonth, $parentKeyword['hash']);
        return array(
            'name' => $month,
            'id' => $idMonth,
            'hash' => $hashMonth,
            'parentId' => $parentKeyword['id'],
            'parentHash' => $parentKeyword['hash'],
            'href' => RestoUtil::updateUrl($searchUrl, array($this->model->searchFilters['searchTerms']['osKey'] => $month, $this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
    }
    
    /**
     * Add a keyword for day
     * 
     * @param array $properties
     * @param string $searchUrl
     */
    private function getDayKeyword($properties, $searchUrl, $parentKeyword) {
        $day = substr($properties[$this->model->searchFilters['time:start']['key']], 0, 10);
        $idDay = 'day:' . $day;
        return array(
            'name' => $day,
            'id' => $idDay,
            'hash' => RestoUtil::getHash($idDay),
            'parentId' => $parentKeyword['id'],
            'parentHash' => $parentKeyword['hash'],
            'href' => RestoUtil::updateUrl($searchUrl, array($this->model->searchFilters['searchTerms']['osKey'] => $day, $this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
    }
    
    /**
     * Add a keyword for year, month and day of acquisition
     * 
     * @param array $properties
     */
    private function setLanduse(&$properties) {
        $landUse = array(
            'cultivatedCover',
            'desertCover',
            'floodedCover',
            'forestCover',
            'herbaceousCover',
            'iceCover',
            'urbanCover',
            'waterCover'
        );
        if (!isset($properties['landUse']) || !is_array($properties['landUse'])) {
            $properties['landUse'] = array();
        }
        for ($i = count($landUse); $i--;) {
            if (isset($properties[$landUse[$i]])) {
                if ($properties[$landUse[$i]]) {
                    $properties['landUse'][$landUse[$i]] = $properties[$landUse[$i]];
                }
                unset($properties[$landUse[$i]]);
            }
        }
        
    }
    
    /**
     * Set services - Visualize / Download / etc.
     * 
     * @param array $properties
     * @param string $thisUrl
     */
    private function setServices(&$properties, $thisUrl) {
        
        if (!isset($properties['services'])) {
            $properties['services'] = array();
        }
            
        /*
         * Visualize
         */
        if (isset($properties['wms'])) {
            $this->setVisualizeService($properties);
        }
        
        /*
         * Download
         */
        if (isset($properties['resource'])) {
            $this->setDownloadService($properties, $thisUrl);
        }
        
    }
    
    /**
     * Set visualize service
     * 
     * @param array $properties
     */
    private function setVisualizeService(&$properties) {
        $properties['services']['browse'] = array(
            'title' => 'Display full resolution product on map',
            'layer' => array(
                'type' => 'WMS',
                'url' => $properties['wms'],
                // TODO mapshup needs layers to be set -> to be changed in mapshup
                'layers' => ''
            )
        );
    }
    
    /**
     * Set download service
     * 
     * @param array $properties
     * @param string $thisUrl
     */
    private function setDownloadService(&$properties, $thisUrl) {
        $properties['services']['download'] = array(
            'url' => RestoUtil::isUrl($properties['resource']) ? $properties['resource'] : $thisUrl. '/download'
        );
        $properties['services']['download']['mimeType'] = isset($properties['resourceMimeType']) ? $properties['resourceMimeType'] : 'application/unknown';
        if (isset($properties['resourceSize']) && $properties['resourceSize']) {
            $properties['services']['download']['size'] = $properties['resourceSize'];
        }
        if (isset($properties['resourceChecksum'])) {
            $properties['services']['download']['checksum'] = $properties['resourceChecksum'];
        }
        $this->resourceInfos = array(
            'path' => method_exists($this->model,'generateResourcePath') ? $this->model->generateResourcePath($properties) : $properties['resource'],
            'mimeType' => $properties['services']['download']['mimeType'],
            'size' => isset($properties['services']['download']['size']) ? $properties['services']['download']['size'] : null,
            'checksum' => isset($properties['services']['download']['checksum']) ? $properties['services']['download']['checksum'] : null
        );
    }
    
    /**
     * Set links
     * 
     * @param array $properties
     * @param string $thisUrl
     */
    private function setLinks(&$properties, $thisUrl) {
        
        if (!isset($properties['links']) || !is_array($properties['links'])) {
            $properties['links'] = array();
        }
        $properties['links'][] = array(
            'rel' => 'alternate',
            'type' => RestoUtil::$contentTypes['html'],
            'title' => $this->context->dictionary->translate('_htmlLink', $this->identifier),
            'href' => RestoUtil::updateUrl($thisUrl . '.html', array($this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
        $properties['links'][] = array(
            'rel' => 'alternate',
            'type' => RestoUtil::$contentTypes['json'],
            'title' => $this->context->dictionary->translate('_jsonLink', $this->identifier),
            'href' => RestoUtil::updateUrl($thisUrl . '.json', array($this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
        $properties['links'][] = array(
            'rel' => 'alternate',
            'type' => RestoUtil::$contentTypes['atom'],
            'title' => $this->context->dictionary->translate('_atomLink', $this->identifier),
            'href' => RestoUtil::updateUrl($thisUrl . '.atom', array($this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
        
        if (isset($properties['metadata'])) {
            $properties['links'][] = array(
                'rel' => 'via',
                'type' => isset($properties['metadataMimeType']) ? $properties['metadataMimeType'] : 'application/unknown',
                'title' => $this->context->dictionary->translate('_metadataLink', $this->identifier),
                'href' => $properties['metadata']
            );    
        }
        
    }
    
    /**
     * Remove redondant or unwanted properties
     * 
     * @param array $properties
     */
    private function cleanProperties(&$properties) {
        unset($properties['totalcount'],
              $properties['identifier'],
              $properties['geometry'], 
              $properties['metadata'], 
              $properties['metadataMimeType'],
              $properties['wms'],
              $properties['resource'],
              $properties['resourceMimeType'],
              $properties['resourceSize'],
              $properties['resourceChecksum'],
              $properties['bbox3857'],
              $properties['bbox4326'],
              $properties['visible']
        );
        
    }
}
