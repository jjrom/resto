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

class RestoATOMFeed{
    
    /*
     * Reference to XML document
     */
    private $xml;
    
    /**
     * Constructor
     * 
     * @param string $id
     * @param string $title
     * @param string $subtitle
     */
    public function __construct($id, $title, $subtitle) {
        $this->initialize($id, $title, $subtitle);
    }
    
    /**
     * Add Atom feed entry
     * 
     * @param array $feature
     * @param RestoContext $context
     */
    public function addEntry($feature, $context) {
        
        /*
         * Add entry
         */
        $this->startElement('entry');
        
        /*
         * Add entry base elements, time and geometry
         */
        $this->addEntryElements($feature);
        
        /*
         * Links
         */
        $this->addLinks($feature);
        
        /*
         * Media (i.e. Quicklook / Thumbnail / etc.)
         */
        $this->addQuicklooks($feature);
        
        /*
         * Summary
         */
        $this->addSummary($feature, $context);
        
        /*
         * entry - close element
         */
        $this->endElement(); // entry   
    }
    
    /**
     * Write attributes to current XML element
     * 
     * @param array $list
     */
    public function writeAttributes($list) {
        foreach ($list as $key => $value) {
            $this->xml->writeAttribute($key, $value);
        }
    }
    
    /**
     * Start element to the current XML document
     * 
     * @param string $name
     */
    public function startElement($name) {
        $this->xml->startElement($name);
    }
    
    /**
     * End current XML element
     */
    public function endElement() {
        $this->xml->endElement();
    }
    
    /**
     * Write element
     * 
     * @param string $name
     * @param string $value
     */
    public function writeElement($name, $value) {
        $this->xml->writeElement($name, $value);
    }
    
    /**
     * Write elements
     * 
     * @param array $list
     */
    public function writeElements($list) {
        foreach ($list as $key => $value) {
            $this->xml->writeElement($key, $value);
        }
    }
    
    /**
     * Set text to the current XML element
     * 
     * @param string $text
     */
    public function text($text) {
        $this->xml->text($text);
    }
    
    /**
     * Return stringified XML document
     */
    public function toString() {
        
        /*
         * End feed element
         */
        $this->xml->endElement();
        
        /*
         * Write result
         */
        return $this->xml->outputMemory(true);
    }
    
    /**
     * Initialize ATOM feed
     */
    private function initialize($id, $title, $subtitle) {
        
        /*
         * Create XML document
         */
        $this->xml = new XMLWriter();
        $this->xml->openMemory();
        $this->xml->setIndent(true);
        $this->xml->startDocument('1.0', 'UTF-8');
        
        /*
         * Start ATOM feed
         */
        $this->startAtomFeed($id, $title, $subtitle);
        
        /*
         * Set title, subtitle and generator
         */
        $this->setBaseElements($title, $subtitle);
        
        /*
         * Set id
         */
        $this->writeElement('id', $id);
        
    }
    
    /**
     * Start XML ATOM feed with all namespaces attributes
     *
     */
    private function startAtomFeed() {
        $this->startElement('feed');
        $this->writeAttributes(array(
            'xml:lang' => 'en',
            'xmlns' => 'http://www.w3.org/2005/Atom',
            'xmlns:time' => 'http://a9.com/-/opensearch/extensions/time/1.0/',
            'xmlns:os' => 'http://a9.com/-/spec/opensearch/1.1/',
            'xmlns:dc' => 'http://purl.org/dc/elements/1.1/',
            'xmlns:georss' => 'http://www.georss.org/georss',
            'xmlns:gml' => 'http://www.opengis.net/gml',
            'xmlns:geo' => 'http://a9.com/-/opensearch/extensions/geo/1.0/',
            'xmlns:eo' => 'http://a9.com/-/opensearch/extensions/eo/1.0/',
            'xmlns:metalink' => 'urn:ietf:params:xml:ns:metalink',
            'xmlns:xlink' => 'http://www.w3.org/1999/xlink',
            'xmlns:media' => 'http://search.yahoo.com/mrss/'
        ));
    }
    
    /**
     * Set title, subtitle and generator
     * 
     * @param string $id
     * @param string $title
     * @param string $subtitle
     */
    public function setBaseElements($title, $subtitle) {
        
        /*
         *  Title
         */
        $this->writeElement('title', $title);

        /*
         *  Subtitle
         */
        $this->startElement('subtitle');
        $this->writeAttributes(array('type' => 'html'));
        $this->text($subtitle);
        $this->endElement();

        /* 
         * Generator
         */
        $this->startElement('generator');
        $this->writeAttributes(array(
            'uri' => 'http://github.com/jjrom/resto2',
            'version' => Resto::VERSION
        ));
        $this->text('resto');
        $this->endElement();
        
        /*
         * Date of creation is now
         */
        $this->writeElement('updated', date('Y-m-dTH:i:sO'));
    }
    
    /**
     * Add summary entry
     * 
     * @param array $feature
     * @param RestoContext $context
     */
    private function addSummary($feature, $context) {
        $this->startElement('summary');
        $this->writeAttributes(array('type' => 'text'));
        $this->text((isset($feature['properties']['platform']) ? $feature['properties']['platform'] : '') . (isset($feature['properties']['platform']) && isset($feature['properties']['instrument']) ? '/' . $feature['properties']['instrument'] : '') . ' ' . $context->dictionary->translate('_acquiredOn', $feature['properties']['startDate']));
        $this->endElement(); // content
    }
    
    /**
     * Add atom entry base elements
     * 
     * @param array $feature
     */
    private function addEntryElements($feature) {
        
        /*
         * Base elements
         */
        $this->writeElements(array(
            'id' => $feature['id'], // ! THIS SHOULD BE AN ABSOLUTE UNIQUE  AND PERMANENT IDENTIFIER !!
            'dc:identifier' => $feature['id'], // Local identifier - i.e. last part of uri
            'title' => isset($feature['properties']['title']) ? $feature['properties']['title'] : '',
            'published' => $feature['properties']['published'],
            'updated' => $feature['properties']['updated'],
            /*
             * Element 'dc:date' - date of the resource is duration of acquisition following
             * the Dublin Core Collection Description on date
             * (http://www.ukoln.ac.uk/metadata/dcmi/collection-RKMS-ISO8601/)
             */
            'dc:date' => $feature['properties']['startDate'] . '/' . $feature['properties']['completionDate']
        ));
        
        /*
         * Time
         */
        $this->addGmlTime($feature['properties']['startDate'], $feature['properties']['completionDate']);
        
        /*
         * georss:polygon
         */
        $this->addGeorssPolygon($feature['geometry']['coordinates']);

    }
    
    /**
     * Add gml:validTime element
     * Element 'gml:validTime' - acquisition duration between startDate and completionDate
     * 
     * @param string $beginPosition
     * @param string $endPosition
     */
    private function addGmlTime($beginPosition, $endPosition) {
        $this->startElement('gml:validTime');
        $this->startElement('gml:TimePeriod');
        $this->writeElement('gml:beginPosition', $beginPosition);
        $this->writeElement('gml:endPosition', $endPosition);
        $this->endElement(); // gml:TimePeriod
        $this->endElement(); // gml:validTime
    }
    
    /**
     * Add georss:polygon from geojson entry
     * 
     * WARNING !
     *
     *  GeoJSON coordinates order is longitude,latitude
     *  GML coordinates order is latitude,longitude
     * 
     *  @param array $coordinates
     */
    private function addGeorssPolygon($coordinates) {
        if (isset($coordinates)) {
            $geometry = array();
            foreach ($coordinates as $key) {
                foreach ($key as $value) {
                    $geometry[] = $value[1] . ' ' . $value[0];
                }
            }
            $this->startElement('georss:where');
            $this->startElement('gml:Polygon');
            $this->startElement('gml:exterior');
            $this->startElement('gml:LinearRing');
            $this->startElement('gml:posList');
            $this->writeAttributes(array('srsDimensions' => '2'));
            $this->text(join(' ', $geometry));
            $this->endElement(); // gml:posList
            $this->endElement(); // gml:LinearRing
            $this->endElement(); // gml:exterior
            $this->endElement(); // gml:Polygon
            $this->endElement(); // georss:where
        }
    }
    
    /**
     * Add ATOM feed media element
     * 
     * @param string $type (should be THUMBNAIL or QUICKLOOK
     * @param string $url
     */
    private function addMedia($type, $url) {
        $this->startElement('media:content');
        $this->writeAttributes(array(
            'url' => $url,
            'medium' => 'image'
        ));
        $this->startElement('media:category');
        $this->writeAttributes(array('scheme' => 'http://www.opengis.net/spec/EOMPOM/1.0'));
        $this->text($type);
        $this->endElement();
        $this->endElement();
    }
    
    /**
     * Add entry links
     * 
     * @param array $feature
     */
    private function addLinks($feature) {
        
        /*
         * General links
         */
        if (is_array($feature['properties']['links'])) {
            for ($j = 0, $k = count($feature['properties']['links']); $j < $k; $j++) {
                $this->startElement('link');
                $this->writeAttributes(array(
                    'rel' => $feature['properties']['links'][$j]['rel'],
                    'type' => $feature['properties']['links'][$j]['type'],
                    'title' => $feature['properties']['links'][$j]['title'],
                    'href' => $feature['properties']['links'][$j]['href']
                ));
                $this->endElement(); // link
            }
        }
        
        /*
         * Element 'enclosure' - download product
         *  read from $feature['properties']['archive']
         */
        if (isset($feature['properties']['services']['download']['url'])) {
            $this->startElement('link');
            $this->writeAttributes(array(
                'rel' => 'enclosure',
                'type' => isset($feature['properties']['services']['download']['mimeType']) ? $feature['properties']['services']['download']['mimeType'] : 'application/unknown',
                'length' => isset($feature['properties']['services']['download']['size']) ? $feature['properties']['services']['download']['size'] : 0,
                'title' => 'File for ' . $feature['id'] . ' product',
                'metalink:priority' => 50,
                'href' => $feature['properties']['services']['download']['url']
            ));
            $this->endElement(); // link
        }
        
    }
    
    /**
     * Add entry media
     * 
     * @param array $feature
     */
    private function addQuicklooks($feature) {
        
        /*
         * Quicklook / Thumbnail
         */
        if (isset($feature['properties']['thumbnail']) || isset($feature['properties']['quicklook'])) {

            /*
             * rel=icon
             */
            if (isset($feature['properties']['quicklook'])) {
                $this->startElement('link');
                $this->writeAttributes(array(
                    'rel' => 'icon',
                    //'type' => 'TODO',
                    'title' => 'Browse image URL for ' . $feature['id'] . ' product',
                    'href' => $feature['properties']['quicklook']
                ));
                $this->endElement(); // link
            }

            /*
             * media:group
             */
            $this->startElement('media:group');
            if (isset($feature['properties']['thumbnail'])) {
                $this->addMedia('THUMNAIL', $feature['properties']['thumbnail']);
            }
            if (isset($feature['properties']['quicklook'])) {
                $this->addMedia('QUICKLOOK', $feature['properties']['quicklook']);
            }
            $this->endElement();
        }

    }
    
    
}
