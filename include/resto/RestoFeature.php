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
    private $displayedCollectionName;
    
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
     * @param RestoCollection $collection : Parent collection
     * @param boolean $forceCollectionName : Force collection name in links
     * 
     */
    public function __construct($featureOrIdentifier, $context, $user, $collection, $forceCollectionName = true) {
        
        $this->identifier = is_array($featureOrIdentifier) ? $featureOrIdentifier['identifier'] : $featureOrIdentifier;
        $this->context = $context;
        $this->user = $user;
        
        if (isset($collection)) {
            $this->collection = $collection;
            $this->model = $this->collection->model;
        }
        else {
            $this->model = new RestoModel_default($this->context, $this->user);
        }
        
        $this->displayedCollectionName = $forceCollectionName && isset($this->collection) ? $this->collection->name : null;
        
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            throw new Exception('Context is undefined or not valid', 500);
        }
        if (!RestoUtil::isValidUUID($this->identifier)) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
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
            $this->setFeature($this->context->dbDriver->getFeatureDescription($this->identifier, $this->model, isset($this->collection) ? $this->collection->name : null));
        }
        
        return $this;
        
    }
    
    /**
     * Set feature properties
     */
    private function setFeature($properties) {

        /*
         * No result - throw Not Found exception
         */
        if (!$properties) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Not Found', 404);
        }
        
        /*
         * Retrieve collection if not set
         */
        if (!isset($this->collection)) {
            $this->collection = new RestoCollection($properties['collection'], $this->context, $this->user, array('autoload' => true));
            $this->model = $this->collection->model;
        }
        
        /*
         * Modify properties as defined in collection propertiesMapping associative array
         */
        if (isset($this->collection->propertiesMapping)) {
            foreach (array_keys($this->collection->propertiesMapping) as $key) {
                $properties[$key] = RestoUtil::replaceInTemplate($this->collection->propertiesMapping[$key], $properties);
            }
        }
        
        /*
         * Extract geometry
         */
        $geometry = isset($properties['geometry']) ? $properties['geometry'] : null;
        
        /*
         * Set search url
         */
        $searchUrl = $this->context->baseUrl . 'api/collections' . (isset($this->displayedCollectionName) ? '/' . $this->displayedCollectionName : '' ) . '/search.json';
        $thisUrl = isset($this->collection) ? RestoUtil::restoUrl($this->collection->getUrl(), $this->identifier) : RestoUtil::restoUrl($this->context->baseUrl, 'collections/' . $properties['collection'] . '/' . $this->identifier);
        
        /*
         * Add a keyword for year, month and day of acquisition
         */
        if (isset($properties[$this->model->searchFilters['time:start']['key']])) {
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
        
        /*
         * Add keywords for dedicated filters
         */
        foreach (array_keys($this->model->searchFilters) as $key) {
            if (isset($this->model->searchFilters[$key]['keyword']) && isset($properties[$this->model->searchFilters[$key]['key']])) {
                /*
                 * Set multiple words within quotes 
                 */
                $v = RestoUtil::replaceInTemplate($this->model->searchFilters[$key]['keyword']['value'], $properties);
                $splitted = explode(' ', $v);
                
                if (count($splitted) > 1) {
                    $v = '"' . $v . '"';
                }
                $properties['keywords'][] = array(
                    'name' => $v,
                    'id' => $this->model->searchFilters[$key]['keyword']['type'] . ':' . $v,
                    'href' => RestoUtil::updateUrl($searchUrl, array($this->model->searchFilters['searchTerms']['osKey'] => $v))
                );
            }
        }
        
        /*
         * LandUse
         */
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
        
        /*
         * Services - Visualize / Download / etc.
         */
        if (isset($properties['wms'])) {
            if (!isset($properties['services'])) {
                $properties['services'] = array();
            }
            $properties['services']['browse'] = array(
                'title' => 'Display full resolution product on map',
                'layer' => array(
                    'type' => 'WMS',
                    'url' => $properties['wms'],
                    // mapshup needs layers to be set -> to be changed in mapshup
                    'layers' => ''
                )
            );
        }
        // Download
        if (isset($properties['resource'])) {
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
                'path' => $properties['resource'],
                'mimeType' => $properties['services']['download']['mimeType'],
                'size' => isset($properties['services']['download']['size']) ? $properties['services']['download']['size'] : null,
                'checksum' => isset($properties['services']['download']['checksum']) ? $properties['services']['download']['checksum'] : null
            );
            
        }
        
        /*
         * Set links
         */
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
        
        /*
         * Remove redondant or unwanted properties
         */
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
              $properties['bbox4326']  
        );
        
        $this->feature = array(
            'type' => 'Feature',
            'id' => $this->identifier,
            'geometry' => $geometry,
            'properties' => $properties
        );
        
    }
    
    /**
     * Remove feature from database
     */
    public function removeFromStore() {
        $this->context->dbDriver->removeFeature($this);
    }
    
    /**
     * Add an atom entry within $xml document
     * 
     * @param Document $xml
     */
    public function addAtomEntry($xml) {
        
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
                $xml->writeAttribute('type', $this->feature['properties']['links'][$j]['rel']);
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
     * Output product description as a PHP array
     */
    public function toArray() {
        return $this->feature;
    }
    
    /**
     * Output as an HTML page
     */
    public function toHTML() {
        return RestoUtil::get_include_contents(realpath(dirname(__FILE__)) . '/../../themes/' . $this->context->config['theme'] . '/templates/feature.php', $this);
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
        $xml->writeAttribute('version', Resto::version);
        $xml->text('resto');
        $xml->endElement(); // generator
        $xml->writeElement('updated', date('Y-m-dTH:i:sO'));

        /*
         * Element 'id'
         */
        $xml->writeElement('id', $this->feature['id']);

        /*
         * Links
         */
        if (is_array($this->feature['properties']['links'])) {
            for ($i = 0, $l = count($this->description['properties']['links']); $i < $l; $i++) {
                $xml->startElement('link');
                $xml->writeAttribute('rel', $this->feature['properties']['links'][$i]['rel']);
                $xml->writeAttribute('type', RestoUtil::$contentTypes['atom']);
                $xml->writeAttribute('title', $this->feature['properties']['links'][$i]['title']);
                $xml->writeAttribute('href', RestoUtil::updateURLFormat($this->feature['properties']['links'][$i]['href'], 'atom'));
                $xml->endElement(); // link
            }
        }

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
    
    /*
     * Download feature product
     */
    public function download() {
        
        /*
         * Not downloadable
         */
        if (!isset($this->feature['properties']['services']) || !isset($this->feature['properties']['services']['download']))  {
            throw new Exception('Not Found', 404);
        }
        
        /*
         * Download hosted resource with support of Range and Partial Content
         * (See http://stackoverflow.com/questions/157318/resumable-downloads-when-using-php-to-send-the-file)
         */
        if (isset($this->resourceInfos)) {
            if (!isset($this->resourceInfos['path']) || !is_file($this->resourceInfos['path'])) {
                throw new Exception('Not Found', 404);
            }
            
            /*
             * Optimized download with Apache module XsendFile
             */
            if (in_array('mod_xsendfile', apache_get_modules())) {
                header('HTTP/1.1 200 OK');
                header('Pragma: public');
                header('Expires: -1');
                header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
                header('X-Sendfile: ' . $this->resourceInfos['path']);
                header('Content-Type: ' . isset($this->resourceInfos['mimeType']) ? $this->resourceInfos['mimeType'] : 'application/unknown');
                header('Content-Disposition: attachment; filename="' . basename($this->resourceInfos['path']) . '"');
                header('Accept-Ranges: bytes');
                return;
            }
            
            /*
             * Direct download for small files
             */
            $chunkSize = 1024 * 8;
            $fileSize = filesize($this->resourceInfos['path']);
            if ($fileSize > $chunkSize) {
                echo file_get_contents($this->resourceInfos['path']);
                return true;
            }
            
            /*
             * Read file
             */
            $file = @fopen($this->resourceInfos['path'], "rb");
            if (isset($file)) {
                
                /*
                 * Default headers with prevent caching
                 */
                header('HTTP/1.1 200 OK');
                header('Pragma: public');
                header('Expires: -1');
                header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
                header('Content-Disposition: attachment; filename="' . basename($this->resourceInfos['path']) . '"');
                header('Content-Type: ' . isset($this->resourceInfos['mimeType']) ? $this->resourceInfos['mimeType'] : 'application/unknown');
                header('Accept-Ranges: bytes');
                
                /*
                 * Range support
                 * 
                 * In case of multiple ranges requested, only the first range is served
                 * (http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt)
                 */
                $range = '';
                if (isset($_SERVER['HTTP_RANGE'])) {
                    $splitted = explode('=', $_SERVER['HTTP_RANGE'], 2);
                    if ($splitted[0] === 'bytes') {
                        list($range, $unusued) = explode(',', $splitted[1], 2);
                    }
                    else {
                        $range = '';
                        header('HTTP/1.1 416 Requested Range Not Satisfiable');
                        exit;
                    }
                }

                /*
                 * Partial download based on range
                 */
                $bounds = explode('-', $range, 2);
                $seekEnd = empty($bounds[1]) ? ($fileSize - 1) : min(abs(intval($bounds[1])), ($fileSize - 1));
                $seekStart = (empty($bounds[0]) || $seekEnd < abs(intval($bounds[0]))) ? 0 : max(abs(intval($bounds[0])), 0);
                
                /*
                 * Only send partial content header if downloading a piece of the file
                 * (IE workaround)
                 */
                if ($seekStart > 0 || $seekEnd < ($fileSize - 1)) {
                    header('HTTP/1.1 206 Partial Content');
                    header('Content-Range: bytes ' . $seekStart . '-' . $seekEnd . '/' . $fileSize);
                    header('Content-Length: ' . ($seekEnd - $seekStart + 1));
                }
                else {
                    header('Content-Length: ' . $fileSize);
                }
                
                /*
                 * Output without buffering to support large file 
                 * downloads
                 */
                set_time_limit(0);
                fseek($file, $seekStart);
                while (!feof($file)) {
                    $buffer = @fread($file, $chunkSize);
                    echo $buffer;
                    ob_flush();
                    flush();
                    if (connection_status() != 0) {
                        return fclose($file);
                    }
                }
                return fclose($file);
            }
            else {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Resource cannot be downloaded', 500);
            }
        }
        /*
         * Resource is on an external url
         */
        else if (RestoUtil::isUrl($this->feature['properties']['services']['download']['url'])) {
            $handle = fopen($this->feature['properties']['services']['download']['url'], "rb");
            if ($handle === false) {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Resource cannot be downloaded', 500);
            }
            header('HTTP/1.1 200 OK');
            header('Content-Disposition: attachment; filename="' . basename($this->feature['properties']['services']['download']['url']) . '"');
            header('Content-Type: ' . isset($this->feature['properties']['services']['download']['mimeType']) ? $this->feature['properties']['services']['download']['mimeType'] : 'application/unknown');
            while (!feof($handle)) {
                $buffer = @fread($handle, 1024 * 8);
                echo $buffer;
                ob_flush();
                flush();
                if (connection_status() != 0) {
                    return fclose($file);
                }
            }
            return fclose($file);
        }
        /*
         * Not Found
         */
        else {
            throw new Exception('Not Found', 404);
        }
        
    }
    
}
