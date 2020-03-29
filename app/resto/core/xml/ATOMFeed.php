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

class ATOMFeed extends RestoXML
{
    
    /*
     * GeoRSS Where or Simple
     */
    private $useGeoRSSSimple = true;
    
    
    /**
     * Constructor
     *
     * @param string $id
     * @param string $title
     * @param string $subtitle
     */
    public function __construct($id, $title, $subtitle)
    {
        parent::__construct();
        
        /*
         * Start ATOM feed
         */
        $this->startAtomFeed();
        
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
     * Add Atom feed entry
     *
     * @param array $feature
     */
    public function addEntry($feature)
    {
        
        /*
         * Add entry
         */
        $this->startElement('entry');
        
        /*
         * Add entry base elements, time and geometry
         */
        $this->addEntryElements($feature);
        
        /*
         * entry - close element
         */
        $this->endElement(); // entry
    }
    
    /**
     * Add Atom feed entries
     *
     * @param array $features
     */
    public function addEntries($features)
    {
        for ($i = 0, $l = count($features); $i < $l; $i++) {
            $this->addEntry($features[$i]->toArray());
        }
    }
    
    /**
     * Return stringified XML document
     */
    public function toString()
    {
        
        /*
         * End feed element
         */
        $this->endElement();
        
        /*
         * Write result
         */
        return parent::toString();
    }
    
    /**
     * Set elements for FeatureCollection
     *
     * @param array $links
     * @param array $searchMetadata
     * @param RestoModel $model
     */
    public function setCollectionElements($links, $searchMetadata, $model)
    {
        
        /*
         * Update outputFormat links except for OSDD 'search'
         */
        $this->setCollectionLinks($links);
        
        /*
         * Total results, startIndex and itemsPerpage
         */
        foreach (array('totalResults', 'startIndex', 'itemsPerPage') as $key) {
            if (isset($searchMetadata[$key])) {
                $this->writeElement('os:' . $key, $searchMetadata[$key]);
            }
        }

        /*
         * Query element
         */
        $this->setQuery($searchMetadata, $model);
    }
    
    /**
     * Start XML ATOM feed with all namespaces attributes
     *
     */
    private function startAtomFeed()
    {
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
    private function setBaseElements($title, $subtitle)
    {
        
        /*
         *  Title
         */
        $this->writeElement('title', $title ?? '');

        /*
         *  Subtitle
         */
        $this->startElement('subtitle');
        $this->writeAttributes(array('type' => 'html'));
        $this->text($subtitle ?? '');
        $this->endElement();

        /*
         * Generator
         */
        $this->startElement('generator');
        $this->writeAttributes(array(
            'uri' => 'http://github.com/jjrom/resto',
            'version' => Resto::VERSION
        ));
        $this->text('resto');
        $this->endElement();
        
        /*
         * Date of creation is now
         */
        $this->writeElement('updated', date('Y-m-d\TH:i:s\Z'));
    }
    
    /**
     * Add summary entry
     *
     * @param array $feature
     */
    private function addSummary($feature)
    {
        $this->startElement('summary');
        $this->writeAttributes(array('type' => 'text'));
        $this->text(($feature['properties']['platform'] ?? '') . (isset($feature['properties']['platform']) && isset($feature['properties']['instrument']) ? '/' . $feature['properties']['instrument'] : '') . ' acquired on ' . $feature['properties']['start_datetime']);
        $this->endElement(); // content
    }
    
    /**
     * Add atom entry base elements
     *
     * @param array $feature
     */
    private function addEntryElements($feature)
    {
        
        /*
         * General links
         */
        if (is_array($feature['links'])) {
            for ($i = 0, $ii = count($feature['links']); $i < $ii; $i++) {
                if ($feature['links'][$i]['rel'] === 'self') {
                    $explodedSelf = explode('?', RestoUtil::updateUrlFormat($feature['links'][$i]['href'], 'atom'));
                    break;
                }
            }
        }
        
        /*
         * Base elements
         */
        $this->writeElements(array(
            'title' => $feature['properties']['title'],
            'updated' => $feature['properties']['updated'],
            // IRI is self url
            'id' => is_array($explodedSelf) ? $explodedSelf[0] : $feature['id']
        ));
        
        /*
         * Summary
         */
        $this->addSummary($feature);
        
        /*
         * Add self
         */
        if (is_array($explodedSelf)) {
            $this->startElement('link');
            $this->writeAttributes(array(
                'rel' => 'self',
                'type' => RestoUtil::$contentTypes['atom'],
                'href' => join('?', $explodedSelf)
            ));
            $this->endElement(); // link
        }
        
        /*
         * Links
         */
        $this->addLinks($feature);
        
        /*
         * Media (i.e. Quicklook / Thumbnail / etc.)
         */
        $this->addQuicklooks($feature);
        
        /*
         * Base elements
         */
        $this->writeElements(array(
            'dc:identifier' => $feature['id'], // Local identifier - i.e. last part of uri
            /*
             * Element 'dc:date' - date of the resource is duration of acquisition following
             * the Dublin Core Collection Description on date
             * (http://www.ukoln.ac.uk/metadata/dcmi/collection-RKMS-ISO8601/)
             */
            'dc:date' => $feature['properties']['datetime']
        ));
        
        /*
         * Time
         */
        $this->addGmlTime($feature['properties']['start_datetime'], $feature['properties']['end_datetime']);
        
        /*
         * Add georss
         */
        $this->addGeoRSS($feature['geometry']['type'], $feature['geometry']['coordinates']);
    }
    
    /**
     * Add gml:validTime element
     * Element 'gml:validTime' - acquisition duration between start_datetime and end_datetime
     *
     * @param string $beginPosition
     * @param string $endPosition
     */
    private function addGmlTime($beginPosition, $endPosition)
    {
        $this->startElement('gml:validTime');
        $this->startElement('gml:TimePeriod');
        $this->writeElement('gml:beginPosition', $beginPosition);
        $this->writeElement('gml:endPosition', $endPosition);
        $this->endElement(); // gml:TimePeriod
        $this->endElement(); // gml:validTime
    }
    
    /**
     * Add GeoRSS element
     *
     * @param string $type
     * @param array $coordinates
     */
    private function addGeoRSS($type, $coordinates)
    {
        $geometry = array();
        switch ($type) {
            case 'Point':
                $geometry[] = $coordinates[1] . ' ' . $coordinates[0];
                break;
            case 'Polygon':
            case 'LineString':
                foreach ($coordinates as $key) {
                    foreach ($key as $value) {
                        $geometry[] = $value[1] . ' ' . $value[0];
                    }
                }
                break;
            default:
                break;
        }
        
        if (count($geometry) > 0) {
            $this->useGeoRSSSimple ? $this->addGeoRSSSimple($type, join(' ', $geometry)) : $this->addGeoRSSWhere($type, join(' ', $geometry));
        }

    }
    
    /**
     * Add georss:where from geojson entry
     *
     * WARNING !
     *
     *  GeoJSON coordinates order is longitude,latitude
     *  GML coordinates order is latitude,longitude
     *
     *  @param string $type
     *  @param string $geometry
     */
    private function addGeoRSSWhere($type, $geometry)
    {
        $this->startElement('georss:where');
        $this->startElement('gml:' . $type);
        switch ($type) {
            case 'Polygon':
                $this->startElement('gml:exterior');
                $this->startElement('gml:LinearRing');
                $this->startElement('gml:posList');
                $this->writeAttributes(array('srsDimensions' => '2'));
                $this->text($geometry);
                $this->endElement(); // gml:posList
                $this->endElement(); // gml:LinearRing
                $this->endElement(); // gml:exterior
                break;
            case 'LineString':
                $this->startElement('gml:LinearString');
                $this->startElement('gml:posList');
                $this->text($geometry);
                $this->endElement(); // gml:posList
                $this->endElement(); // gml:LineString
                break;
            case 'Point':
                $this->startElement('gml:pos');
                $this->text($geometry);
                $this->endElement(); // gml:pos
                break;
        }
        $this->endElement(); // gml:<$type>
        $this->endElement(); // georss:where
    }
    
    /**
     * Add georss simple
     *
     * WARNING !
     *
     *  GeoJSON coordinates order is longitude,latitude
     *  GML coordinates order is latitude,longitude
     *
     *  @param string $type
     *  @param string geometry
     */
    private function addGeoRSSSimple($type, $geometry)
    {
        if ($type === 'LineString') {
            $type = 'Line';
        }
        $this->startElement('georss:' . strtolower($type));
        $this->text($geometry);
        $this->endElement(); // georss:<$type>
    }
    
    /**
     * Add ATOM feed media element
     *
     * @param string $type (should be THUMBNAIL or QUICKLOOK
     * @param string $url
     */
    private function addMedia($type, $url)
    {
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
    private function addLinks($feature)
    {
        
        /*
         * General links
         */
        if (is_array($feature['links'])) {
            
            for ($i = 0, $ii = count($feature['links']); $i < $ii; $i++) {
                
                if ($feature['links'][$i]['rel'] === 'self') {
                    continue;
                }

                $this->startElement('link');

                $attributes = $feature['links'][$i];

                /*
                 * Element 'enclosure' - download product
                 * [TODO][STAC] => download is in assets not links
                 */
                if ($feature['links'][$i]['rel'] === 'download') {
                    $attributes['length'] = $feature['links'][$i]['size'] ?? 0;
                    $attributes['metalink:priority'] = 50;
                }
                
                $this->writeAttributes($attributes);
                
                $this->endElement(); // link
                
            }
        }
        
    }
    
    /**
     * Add entry media
     *
     * @param array $feature
     */
    private function addQuicklooks($feature)
    {
        
        /*
         * Thumbnail
         */
        if ( isset($feature['assets']['thumbnail']) ) {

            /*
             * rel=icon
             */
            $this->startElement('link');
            $this->writeAttributes(array(
                'rel' => 'icon',
                //'type' => 'TODO',
                'title' => 'Browse image URL for ' . $feature['id'] . ' product',
                'href' => $feature['assets']['thumbnail']['href']
            ));
            $this->endElement(); // link
            
            /*
             * media:group
             */
            $this->startElement('media:group');
            $this->addMedia('THUMBNAIL', $feature['assets']['thumbnail']['href']);
            $this->endElement();
        }
    }
    
    /**
     * Set ATOM feed links element for FeatureCollection
     *
     * @param array $links
     */
    private function setCollectionLinks($links)
    {
        if (is_array($links)) {
            for ($i = 0, $l = count($links); $i < $l; $i++) {
                $this->startElement('link');
                $this->writeAttributes(array(
                    'rel' => $links[$i]['rel']
                ));
                if ($links[$i]['type'] === 'application/opensearchdescription+xml') {
                    $this->writeAttributes(array(
                        'type' => $links[$i]['type'],
                        'href' => $links[$i]['href']
                    ));
                } else {
                    $this->writeAttributes(array(
                        'type' => RestoUtil::$contentTypes['atom'],
                        'href' => RestoUtil::updateUrlFormat($links[$i]['href'], 'atom')
                    ));
                }
                $this->endElement(); // link
            }
        }
    }
    
    /**
     * Set ATOM feed Query element from request parameters
     *
     * @param array $searchMetadata
     */
    private function setQuery($searchMetadata, $model)
    {
        $this->startElement('os:Query');
        $this->writeAttributes(array('role' => 'request'));
        if (isset($searchMetadata['query'])) {
            foreach ($searchMetadata['query']['inputFilters'] as $key => $value) {
                if ($key !== 'collection') {
                    $this->writeAttribute($model->getFilterName($key), $value);
                }
            }
        }
        $this->endElement();
    }
}
