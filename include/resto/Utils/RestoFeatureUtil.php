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
 * resto feature manipulation
 */
class RestoFeatureUtil {
   
    /*
     * Reference to resto model
     */
    private $context;
   
    /*
     * Reference to resto model
     */
    private $collection;
    
    /*
     * Search url endpoint
     */
    private $searchUrl;
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoCollection $collection
     */
    public function __construct($context, $collection) {
        $this->context = $context;
        $this->model = isset($collection) ? $collection->model : new RestoModel_default();
        $this->collection = $collection;
        $this->searchUrl = $this->context->baseUrl . '/api/collections' . (isset($this->collection) ? '/' . $this->collection->name : '' ) . '/search.json';
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
        
        $thisUrl = isset($this->collection) ? RestoUtil::restoUrl($this->collection->getUrl(), $rawCorrectedArray['identifier']) : RestoUtil::restoUrl($this->context->baseUrl, '/collections/' . $rawCorrectedArray['collection'] . '/' . $rawCorrectedArray['identifier']);
        
        $properties = $rawCorrectedArray;
        
        /*
         * Update metadata values from propertiesMapping
         */
        $this->updatePaths($properties);
        
        /*
         * Set unstored keywords - TODO
         */
        //$this->setUnstoredKeywords($properties);
        
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
        
        return $properties;
        
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
                $properties[$key] = $this->replaceInTemplate($this->collection->propertiesMapping[$key], $properties);
            }
        }
        
    }
    
    /**
     * Add keywords for dedicated filters
     * 
     * @param array $properties
     */
    private function setUnstoredKeywords(&$properties) {
        
        foreach (array_keys($this->model->searchFilters) as $key) {
            if (isset($this->model->searchFilters[$key]['keyword']) && isset($properties[$this->model->searchFilters[$key]['key']])) {
                
                /*
                 * Set multiple words within quotes 
                 */
                $name = $this->replaceInTemplate($this->model->searchFilters[$key]['keyword']['value'], $properties);
                $splitted = explode(' ', $name);
                
                if (count($splitted) > 1) {
                    $name = '"' . $name . '"';
                }
                $properties['keywords'][] = array(
                    'name' => $name,
                    'id' => $this->model->searchFilters[$key]['keyword']['type'] . ':' . $name,
                    'href' => RestoUtil::updateUrl($this->searchUrl, array($this->model->searchFilters['searchTerms']['osKey'] => $name))
                );
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
            'title' => $this->context->dictionary->translate('_htmlLink', $properties['identifier']),
            'href' => RestoUtil::updateUrl($thisUrl . '.html', array($this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
        $properties['links'][] = array(
            'rel' => 'alternate',
            'type' => RestoUtil::$contentTypes['json'],
            'title' => $this->context->dictionary->translate('_jsonLink', $properties['identifier']),
            'href' => RestoUtil::updateUrl($thisUrl . '.json', array($this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
        );
        $properties['links'][] = array(
            'rel' => 'alternate',
            'type' => RestoUtil::$contentTypes['atom'],
            'title' => $this->context->dictionary->translate('_atomLink', $properties['identifier']),
            'href' => RestoUtil::updateUrl($thisUrl . '.atom', array($this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language))
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
                
                case 'totalcount':
                    $corrected[$key] = (integer) $value;
                    break;
                
                case 'keywords':
                    $corrected[$key] = $this->correctKeywords(json_decode($value, true));
                    break;
                
                default:
                    $corrected[$key] = $this->castExplicit($key, $value);
            }
        }
        
        return $corrected;
    }
    
    /**
     * Explicitely cast $value from $model
     * 
     * @param string $key
     * @param string $value
     */
    private function castExplicit($key, $value) {
        switch($this->model->getDbType($key)) {
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
     * 
     * @return array
     */
    private function correctKeywords($keywords) {
        
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
                $this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language,
                $this->model->searchFilters['searchTerms']['osKey'] => count(explode(' ', $corrected[$key]['name'])) > 1 ? '"'. $corrected[$key]['name'] . '"' : $corrected[$key]['name']
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
