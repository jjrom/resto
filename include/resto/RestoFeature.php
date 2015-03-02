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
            throw new Exception('Context is undefined or not valid', 500);
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
        
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');

        /*
         * feed - Start element
         */
        $xml->startElement('feed');
        $xml->writeAttribute('xml:lang', 'en');
        $xml->writeAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        $xml->writeAttribute('xmlns:time', 'http://a9.com/-/opensearch/extensions/time/1.0/');
        $xml->writeAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $xml->writeAttribute('xmlns:georss', 'http://www.georss.org/georss');
        $xml->writeAttribute('xmlns:gml', 'http://www.opengis.net/gml');
        $xml->writeAttribute('xmlns:geo', 'http://a9.com/-/opensearch/extensions/geo/1.0/');
        $xml->writeAttribute('xmlns:eo', 'http://a9.com/-/opensearch/extensions/eo/1.0/');
        $xml->writeAttribute('xmlns:metalink', 'urn:ietf:params:xml:ns:metalink');
        $xml->writeAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $xml->writeAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');

        /*
         * Element 'title' 
         *  read from $this->feature['properties']['title']
         */
        $xml->writeElement('title', isset($this->feature['properties']['title']) ? $this->feature['properties']['title'] : '');

        /*
         * Element 'subtitle'
         */
        $xml->startElement('subtitle');
        $xml->writeAttribute('type', 'html');
        $xml->text('TODO : To Be Defined');
        $xml->endElement(); // subtitle

        /*
         * Updated time is now
         */
        $xml->startElement('generator');
        $xml->writeAttribute('uri', 'http://github.com/jjrom/resto2');
        $xml->writeAttribute('version', Resto::VERSION);
        $xml->text('resto');
        $xml->endElement(); // generator
        $xml->writeElement('updated', date('Y-m-dTH:i:sO'));

        /*
         * Element 'id'
         */
        $xml->writeElement('id', $this->feature['id']);

        /*
         * Entry for feature
         */
        $this->addAtomEntry($xml);
        
        /*
         * feed - End element
         */
        $xml->endElement();

        /*
         * Return ATOM result
         */
        return $xml->outputMemory(true);
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
        $searchUrl = $this->context->baseUrl . 'api/collections' . (isset($this->displayedName) ? '/' . $this->displayedName : '' ) . '/search.json';
        $thisUrl = isset($this->collection) ? RestoUtil::restoUrl($this->collection->getUrl(), $this->identifier) : RestoUtil::restoUrl($this->context->baseUrl, 'collections/' . $properties['collection'] . '/' . $this->identifier);
        
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
         * Clean properties
         */
        $this->cleanProperties($properties);
        
        $this->feature = array(
            'type' => 'Feature',
            'id' => $this->identifier,
            'geometry' => isset($properties['geometry']) ? $properties['geometry'] : null,
            'properties' => $properties
        );
        
    }
    
    /**
     * Add an atom entry within $xml document
     * 
     * @param Document $xml
     */
    private function addAtomEntry($xml) {
        
        /*
         * entry - add element
         */
        $xml->startElement('entry');

        /*
         * Element 'id'
         *  read from $this->feature['id']
         * 
         * !! THIS SHOULD BE AN ABSOLUTE UNIQUE  AND PERMANENT IDENTIFIER !!
         * 
         */
        $xml->writeElement('id', $this->feature['id']);

        /*
         * Local identifier - i.e. last part of uri
         */
        $xml->writeElement('dc:identifier', $this->feature['id']);

        /*
         * Element 'title'
         *  read from $this->feature['properties']['title']
         */
        $xml->writeElement('title', isset($this->feature['properties']['title']) ? $this->feature['properties']['title'] : '');

        /*
         * Element 'published' - date of metadata first publication
         *  read from $this->feature['properties']['title']
         */
        $xml->writeElement('published', $this->feature['properties']['published']);

        /*
         * Element 'updated' - date of metadata last modification
         *  read from $this->feature['properties']['title']
         */
        $xml->writeElement('updated', $this->feature['properties']['updated']);

        /*
         * Element 'dc:date' - date of the resource is duration of acquisition following
         * the Dublin Core Collection Description on date
         * (http://www.ukoln.ac.uk/metadata/dcmi/collection-RKMS-ISO8601/)
         */
        $xml->writeElement('dc:date', $this->feature['properties']['startDate'] . '/' . $this->feature['properties']['completionDate']);

        /*
         * Element 'gml:validTime' - acquisition duration between startDate and completionDate
         *  read from $this->feature['properties']['startDate'] and $this->feature['properties']['completionDate']
         */
        $xml->startElement('gml:validTime');
        $xml->startElement('gml:TimePeriod');
        $xml->writeElement('gml:beginPosition', $this->feature['properties']['startDate']);
        $xml->writeElement('gml:endPosition', $this->feature['properties']['completionDate']);
        $xml->endElement(); // gml:TimePeriod
        $xml->endElement(); // gml:validTime

        /*
         * georss:polygon from geojson entry
         * 
         * WARNING !
         * 
         *      GeoJSON coordinates order is longitude,latitude
         *      GML coordinates order is latitude,longitude 
         *      
         * 
         */
        $geometry = array();
        foreach ($this->feature['geometry']['coordinates'] as $key) {
            foreach ($key as $value) {
                $geometry[] = $value[1] . ' ' . $value[0];
            }
        }
        $xml->startElement('georss:where');
        $xml->startElement('gml:Polygon');
        $xml->startElement('gml:exterior');
        $xml->startElement('gml:LinearRing');
        $xml->startElement('gml:posList');
        $xml->writeAttribute('srsDimensions', '2');
        $xml->text(join(' ', $geometry));
        $xml->endElement(); // gml:posList
        $xml->endElement(); // gml:LinearRing
        $xml->endElement(); // gml:exterior
        $xml->endElement(); // gml:Polygon
        $xml->endElement(); // georss:where

        /*
         * Links
         */
        if (is_array($this->feature['properties']['links'])) {
            for ($j = 0, $k = count($this->feature['properties']['links']); $j < $k; $j++) {
                $xml->startElement('link');
                $xml->writeAttribute('rel', $this->feature['properties']['links'][$j]['rel']);
                $xml->writeAttribute('type', $this->feature['properties']['links'][$j]['type']);
                $xml->writeAttribute('title', $this->feature['properties']['links'][$j]['title']);
                $xml->writeAttribute('href', $this->feature['properties']['links'][$j]['href']);
                $xml->endElement(); // link
            }
        }

        /*
         * Element 'enclosure' - download product
         *  read from $this->feature['properties']['archive']
         */
        if (isset($this->feature['properties']['services']['download']['url'])) {
            $xml->startElement('link');
            $xml->writeAttribute('rel', 'enclosure');
            $xml->writeAttribute('type', isset($this->feature['properties']['services']['download']['mimeType']) ? $this->feature['properties']['services']['download']['mimeType'] : 'application/unknown');
            $xml->writeAttribute('length', isset($this->feature['properties']['services']['download']['size']) ? $this->feature['properties']['services']['download']['size'] : 0);
            $xml->writeAttribute('title', 'File for ' . $this->feature['id'] . ' product');
            $xml->writeAttribute('metalink:priority', 50);
            $xml->writeAttribute('href', $this->feature['properties']['services']['download']['url']);
            $xml->endElement(); // link
        }
        
        /*
         * Quicklook / Thumbnail
         */
        if (isset($this->feature['properties']['thumbnail']) || isset($this->feature['properties']['quicklook'])) {

            /*
             * rel=icon
             */
            if (isset($this->feature['properties']['quicklook'])) {
                $xml->startElement('link');
                $xml->writeAttribute('rel', 'icon');
                //$xml->writeAttribute('type', 'TODO');
                $xml->writeAttribute('title', 'Browse image URL for ' . $this->feature['id'] . ' product');
                $xml->writeAttribute('href', $this->feature['properties']['quicklook']);
                $xml->endElement(); // link
            }

            /*
             * media:group
             */
            $xml->startElement('media:group');
            if (isset($this->feature['properties']['thumbnail'])) {
                $xml->startElement('media:content');
                $xml->writeAttribute('url', $this->feature['properties']['thumbnail']);
                $xml->writeAttribute('medium', 'image');
                $xml->startElement('media:category');
                $xml->writeAttribute('scheme', 'http://www.opengis.net/spec/EOMPOM/1.0');
                $xml->text('THUMBNAIL');
                $xml->endElement();
                $xml->endElement();
            }
            if (isset($this->feature['properties']['quicklook'])) {
                $xml->startElement('media:content');
                $xml->writeAttribute('url', $this->feature['properties']['quicklook']);
                $xml->writeAttribute('medium', 'image');
                $xml->startElement('media:category');
                $xml->writeAttribute('scheme', 'http://www.opengis.net/spec/EOMPOM/1.0');
                $xml->text('QUICKLOOK');
                $xml->endElement();
                $xml->endElement();
            }
            $xml->endElement();
        }

        /*
         * Element 'content' - HTML description
         *  construct from $this->feature['properties'][*]
         */
        /*
        $content = '<p>' . (isset($this->feature['properties']['platform']) ? $this->feature['properties']['platform'] : '') . (isset($this->feature['properties']['platform']) && isset($this->feature['properties']['instrument']) ? '/' . $this->feature['properties']['instrument'] : '') . ' ' . $this->context->dictionary->translate('_acquiredOn', $this->feature['properties']['startDate']) . '</p>';
        if ($this->feature['properties']['keywords']) {
            $keywords = array();
            foreach ($this->feature['properties']['keywords'] as $keyword => $value) {
                $keywords[] = '<a href="' . RestoUtil::updateURLFormat($value['href'], 'atom') . '">' . $keyword . '</a>';
            }
            $content .= '<p>' . $this->context->dictionary->translate('Keywords') . ' ' . join(' | ', $keywords) . '</p>';
        }
        $xml->startElement('content');
        $xml->writeAttribute('type', 'html');
        $xml->text($content);
        $xml->endElement(); // content
        */
        
        /*
         * Summary
         */
        $xml->startElement('summary');
        $xml->writeAttribute('type', 'text');
        $xml->text((isset($this->feature['properties']['platform']) ? $this->feature['properties']['platform'] : '') . (isset($this->feature['properties']['platform']) && isset($this->feature['properties']['instrument']) ? '/' . $this->feature['properties']['instrument'] : '') . ' ' . $this->context->dictionary->translate('_acquiredOn', $this->feature['properties']['startDate']));
        $xml->endElement(); // content
        
        /*
         * entry - close element
         */
        $xml->endElement(); // entry
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
         * File cannot be read
         */
        $file = fopen($path, 'rb');
        if (!is_resource($file)) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * Avoid timeouts
         */
        set_time_limit(0);
        
        /*
         * Range support
         * 
         * In case of multiple ranges requested, only the first range is served
         * (http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt)
         */
        $size = sprintf('%u', filesize($path));
        $range = $multipart === true ? $this->getRange($size) : array(0, $size - 1);

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
            flush();
        }

        fclose($file);
        
    }
    
    /**
     * Get range from HTTP_RANGE and set headers accordingly
     * 
     * @param integer $size
     */
    private function getRange($size) {
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
        $year = substr($properties[$this->model->searchFilters['time:start']['key']], 0, 4);
        $idYear = 'year:' . $year;
        $hashYear = RestoUtil::getHash($idYear);
        $month = substr($properties[$this->model->searchFilters['time:start']['key']], 0, 7);
        $idMonth = 'month:' . $month;
        $hashMonth = RestoUtil::getHash($idMonth, $hashYear);
        $day = substr($properties[$this->model->searchFilters['time:start']['key']], 0, 10);
        $idDay = 'day:' . $day;
        $properties['keywords'][] = array(
            'name' => $year,
            'id' => $idYear,
            'hash' => $hashYear,
            'href' => RestoUtil::updateUrl($searchUrl, array($this->model->searchFilters['searchTerms']['osKey'] => $year, $this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
        $properties['keywords'][] = array(
            'name' => $month,
            'id' => $idMonth,
            'hash' => $hashMonth,
            'parentId' => $idYear,
            'parentHash' => $hashYear,
            'href' => RestoUtil::updateUrl($searchUrl, array($this->model->searchFilters['searchTerms']['osKey'] => $month, $this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
        $properties['keywords'][] = array(
            'name' => $day,
            'id' => $idDay,
            'hash' => RestoUtil::getHash($idDay),
            'parentId' => $idMonth,
            'parentHash' => $hashMonth,
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
