<?php
/*
 * Copyright 2014 Jérôme Gasperi
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
 * resto feature manipulation
 */
class RestoFeatureUtil {
   
    /*
     * Reference to resto context
     */
    private $context;
   
    /*
     * Reference to resto user
     */
    private $user;
   
    /*
     * Array of collections
     */
    private $collections;
    
    /*
     * Search url endpoint
     */
    private $searchUrl;
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     * @param RestoCollection $collection
     */
    public function __construct($context, $user, $collection) {
        $this->context = $context;
        $this->user =$user;
        if (isset($collection)) {
            $this->collections[$collection->name] = $collection;
        }
        $this->searchUrl = $this->context->baseUrl . '/api/collections' . (isset($collection) ? '/' . $collection->name : '' ) . '/search.json';
    } 
   
    /**
     * 
     * Return a featureArray array from an input rawFeatureArray.
     * A rawFeatureArray is the array format returned by a GET request
     * to the RestoDatabaseDriver::FEATURE_DESCRIPTION object
     * 
     * @param array $rawFeatureArray
     * 
     */
    public function toFeatureArray($rawFeatureArray) {
        
        /*
         * No result - throw Not Found exception
         */
        if (!isset($rawFeatureArray) || !is_array($rawFeatureArray)) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * Add collection
         */
        if (!isset($this->collections[$rawFeatureArray['collection']])) {
            $this->collections[$rawFeatureArray['collection']] = new RestoCollection($rawFeatureArray['collection'], $this->context, $this->user, array('autoload' => true));
        }
        
        /*
         * First correct types
         */
        $rawCorrectedArray = $this->correctTypes($rawFeatureArray);
        
        /*
         * Initialize featureArray
         */
        $featureArray = array(
            'type' => 'Feature',
            'id' => $rawFeatureArray['identifier'],
            'geometry' => isset($rawCorrectedArray['geometry']) ? $rawCorrectedArray['geometry'] : null,
            'properties' => $this->toProperties($rawCorrectedArray)
        );
        
        return $featureArray;
        
    }
    
    /**
     * Update feature properties
     * 
     * @param array $rawCorrectedArray
     * @param RestoCollection $collection
     * 
     */
    private function toProperties($rawCorrectedArray) {
        
        $collection = $this->collections[$rawCorrectedArray['collection']];
        $thisUrl = RestoUtil::restoUrl($collection->getUrl(), '/' . $rawCorrectedArray['identifier']);
        
        $properties = $rawCorrectedArray;
        
        /*
         * Update metadata values from propertiesMapping
         */
        $this->updatePaths($properties, $collection);
        
        /*
         * Set unstored keywords - TODO
         */
        //$this->setUnstoredKeywords($properties, $collection);
        
        /*
         * Set services
         */
        $this->setServices($properties, $thisUrl, $collection);
        
        /*
         * Set links
         */
        $this->setLinks($properties, $thisUrl, $collection);
        
        /*
         * Clean properties
         */
        $this->cleanProperties($properties);
        
        return $properties;
        
    }
    
    /**
     * Update metadata values from propertiesMapping
     * 
     * @param array $properties
     * @param RestoCollection $collection
     */
    private function updatePaths(&$properties, $collection) {
        
        /*
         * Update dynamically metadata, quicklook and thumbnail path if required before the replaceInTemplate
         */
        if (method_exists($collection->model,'generateMetadataPath')) {
            $properties['metadata'] = $collection->model->generateMetadataPath($properties);
        }

        if (method_exists($collection->model,'generateQuicklookPath')) {
            $properties['quicklook'] = $collection->model->generateQuicklookPath($properties);
        }

        if (method_exists($collection->model,'generateThumbnailPath')) {
            $properties['thumbnail'] = $collection->model->generateThumbnailPath($properties);
        }
        
        /*
         * Modify properties as defined in collection propertiesMapping associative array
         */
        if (isset($collection->propertiesMapping)) {
            $_properties = $properties;
            foreach (array_keys($collection->propertiesMapping) as $key) {
                $properties[$key] = $this->replaceInTemplate($collection->propertiesMapping[$key], $_properties);
            }
        }
        
    }
    
    /**
     * Add keywords for dedicated filters
     * 
     * @param array $properties
     * @param RestoCollection $collection
     */
    private function setUnstoredKeywords(&$properties, $collection) {
        
        foreach (array_keys($collection->model->searchFilters) as $key) {
            if (isset($collection->model->searchFilters[$key]['keyword']) && isset($properties[$collection->model->searchFilters[$key]['key']])) {
                
                /*
                 * Set multiple words within quotes 
                 */
                $name = $this->replaceInTemplate($collection->model->searchFilters[$key]['keyword']['value'], $properties);
                $splitted = explode(' ', $name);
                
                if (count($splitted) > 1) {
                    $name = '"' . $name . '"';
                }
                $properties['keywords'][] = array(
                    'name' => $name,
                    'id' => $collection->model->searchFilters[$key]['keyword']['type'] . ':' . $name,
                    'href' => RestoUtil::updateUrl($this->searchUrl, array($collection->model->searchFilters['searchTerms']['osKey'] => $name))
                );
            }
        }
    }
    
    /**
     * Set services - Visualize / Download / etc.
     * 
     * @param array $properties
     * @param string $thisUrl
     * @param RestoCollection $collection
     */
    private function setServices(&$properties, $thisUrl, $collection) {
        
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
            $this->setDownloadService($properties, $thisUrl, $collection);
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
     * @param RestoCollection $collection
     */
    private function setDownloadService(&$properties, $thisUrl, $collection) {        
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
        
        /*
         * If resource is local (i.e. not external url), set resourceInfos array
         */
        if (!RestoUtil::isUrl($properties['resource'])) {
            $properties['resourceInfos'] = array(
                'path' => method_exists($collection->model,'generateResourcePath') ? $collection->model->generateResourcePath($properties) : $properties['resource'],
                'mimeType' => $properties['services']['download']['mimeType'],
                'size' => isset($properties['services']['download']['size']) ? $properties['services']['download']['size'] : null,
                'checksum' => isset($properties['services']['download']['checksum']) ? $properties['services']['download']['checksum'] : null
            );
        }
        
    }
    
    /**
     * Set links
     * 
     * @param array $properties
     * @param string $thisUrl
     * @param RestoCollection $collection
     */
    private function setLinks(&$properties, $thisUrl, $collection) {
        
        if (!isset($properties['links']) || !is_array($properties['links'])) {
            $properties['links'] = array();
        }
        $properties['links'][] = array(
            'rel' => 'alternate',
            'type' => RestoUtil::$contentTypes['json'],
            'title' => $this->context->dictionary->translate('_jsonLink', $properties['identifier']),
            'href' => RestoUtil::updateUrl($thisUrl . '.json', array($collection->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
        $properties['links'][] = array(
            'rel' => 'alternate',
            'type' => RestoUtil::$contentTypes['atom'],
            'title' => $this->context->dictionary->translate('_atomLink', $properties['identifier']),
            'href' => RestoUtil::updateUrl($thisUrl . '.atom', array($collection->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
        
        if (isset($properties['metadata'])) {
            $properties['links'][] = array(
                'rel' => 'via',
                'type' => isset($properties['metadataMimeType']) ? $properties['metadataMimeType'] : 'application/unknown',
                'title' => $this->context->dictionary->translate('_metadataLink', $properties['identifier']),
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
              $properties['bbox4326']
        );
        
    }
    
    
    /**
     *
     * PostgreSQL output columns are treated as string
     * thus they need to be converted to their true type
     * 
     * @param Array $rawFeatureArray
     * @return array
     */
    private function correctTypes($rawFeatureArray) {
        
        $corrected = array();
        
        foreach ($rawFeatureArray as $key => $value) { 
            switch($key) {
                case 'bbox4326':
                    $corrected[$key] = str_replace(' ', ',', substr(substr($rawFeatureArray[$key], 0, strlen($rawFeatureArray[$key]) - 1), 4));
                    $corrected['bbox3857'] = RestoGeometryUtil::bboxToMercator($rawFeatureArray[$key]);
                    break;
                
                case 'keywords':
                    $corrected[$key] = $this->correctKeywords(json_decode($value, true), $this->collections[$rawFeatureArray['collection']]);
                    break;
                
                default:
                    $corrected[$key] = $this->castExplicit($key, $value, $this->collections[$rawFeatureArray['collection']]);
            }
        }
        
        return $corrected;
    }
    
    /**
     * Explicitely cast $value from $model
     * 
     * @param string $key
     * @param string $value
     * @param RestoCollection $collection
     */
    private function castExplicit($key, $value, $collection) {
        switch($collection->model->getDbType($key)) {
            case 'integer':
                return (integer) $value;
            case 'float':
                return (float) $value;
            /*
             * PostgreSQL returns ST_AsGeoJSON(geometry) 
             */
            case 'geometry':
                return json_decode($value, true);
            case 'array':
                return explode(',', substr($value, 1, -1));
            default:
                return $value;
        }
    }
    
    /**
     * 
     * Update keywords - i.e. translate name and add url endpoint
     * 
     * @param array $keywords
     * @param RestoCollection $collection
     * 
     * @return array
     */
    private function correctKeywords($keywords, $collection) {
        
        if (!isset($keywords)) {
            return null;
        }
        
        $corrected = array();
        foreach ($keywords as $key => $value) {
            
            /*
             * Do not display landuse_details
             */
            if ($value['type'] === 'landuse_details') {
                continue;
            }
            
            /*
             * Clone keyword array
             */
            $corrected[$key] = $keywords[$key];
            
            /*
             * Value format is urlencode(json)
             */
            $corrected[$key]['name'] = trim($this->context->dictionary->getKeywordFromValue(isset($value['normalized']) ? $value['normalized'] : $value['name'] , $value['type']));
            if (empty($corrected[$key]['name'])) {
                $corrected[$key]['name'] = ucwords($value['name']);
            }
            
            $corrected[$key]['href'] = RestoUtil::updateUrl($this->searchUrl, array(
                $collection->model->searchFilters['language']['osKey'] => $this->context->dictionary->language,
                $collection->model->searchFilters['searchTerms']['osKey'] => count(explode(' ', $corrected[$key]['name'])) > 1 ? '"'. $corrected[$key]['name'] . '"' : $corrected[$key]['name']
            ));
            
        }

        return $corrected;
    }
    
   /**
     * Replace all occurences of a string
     * 
     *  Example :
     *      
     *      replaceInTemplate('Hello. My name is {:name:}. I live in {:location:}', array('name' => 'Jérôme', 'location' => 'Toulouse'));
     * 
     *  Will return
     * 
     *      'Hello. My name is Jérôme. I live in Toulouse
     * 
     * 
     * @param string $sentence
     * @param array $pairs
     * 
     */
    private function replaceInTemplate($sentence, $pairs = array()) {

        if (!isset($sentence)) {
            return null;
        }

        /*
         * Extract pairs
         */
        preg_match_all("/{\:[^\\:}]*\:}/", $sentence, $matches);

        $replace = array();
        for ($i = count($matches[0]); $i--;) {
            $key = substr($matches[0][$i], 2, -2);
            if (isset($pairs[$key])) {
                $replace[$matches[0][$i]] = $pairs[$key];
            }
        }
        if (count($replace) > 0) {
            return strtr($sentence, $replace);
        }

        return $sentence;
    }
    
}
