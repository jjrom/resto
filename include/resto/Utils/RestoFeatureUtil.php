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
        
        $searchUrl = $this->context->baseUrl . '/api/collections' . (isset($this->collection) ? '/' . $this->collection->name : '' ) . '/search.json';
        $thisUrl = isset($this->collection) ? RestoUtil::restoUrl($this->collection->getUrl(), $rawCorrectedArray['identifier']) : RestoUtil::restoUrl($this->context->baseUrl, '/collections/' . $rawCorrectedArray['collection'] . '/' . $rawCorrectedArray['identifier']);
        
        $properties = $rawCorrectedArray;
        
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
              $properties['bbox4326'],
              $properties['visible']
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
            
            if ($key === 'bbox4326') {
                $corrected[$key] = str_replace(' ', ',', substr(substr($rawFeatureArray[$key], 0, strlen($rawFeatureArray[$key]) - 1), 4));
                      
                /*
                 * Compute EPSG:3857 bbox
                 */
                $corrected['bbox3857'] = RestoGeometryUtil::bboxToMercator($rawFeatureArray[$key]);
            
            }
            else if ($key === 'totalcount') {
                $corrected[$key] = (integer) $value;
            }
            else {
                $corrected[$key] = $this->castExplicit($key, $value, isset($rawFeatureArray['collection']) ? $rawFeatureArray['collection'] : null);
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
    private function castExplicit($key, $value, $collectionName) {
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
            case 'hstore':
                return $this->hstoreToKeywords($value, $this->context->baseUrl . '/api/collections' . (isset($collectionName) ? '/' . $collectionName : '' ) . '/search.json');
            case 'array':
                return explode(',', substr($value, 1, -1));
            default:
                return $value;
        }
    }
    
    /**
     * 
     * Return keyword array assuming an input hstore $string 
     * 
     * Note : $string format is "type:name" => urlencode(json)
     *
     *      e.g. "continent:oceania"=>"%7B%22hash%22%3A%2262f4365c66c1f64%22%7D", "country:australia"=>"%7B%22hash%22%3A%228f36daace0ea948%22%2C%22parentHash%22%3A%2262f4365c66c1f64%22%2C%22value%22%3A100%7D"
     * 
     * 
     * Structure of output is 
     *      array(
     *          "id" => // Keyword id (optional)
     *          "type" => // Keyword type
     *          "value" => // Keyword value if it make sense
     *          "href" => // RESTo search url to get keyword
     *      )
     * 
     * @param string $hstore
     * @param string $url : Base url for setting href links
     * @return array
     */
    private function hstoreToKeywords($hstore, $url) {
        
        if (!isset($hstore)) {
            return null;
        }
        
        $json = json_decode('{' . str_replace('}"', '}', str_replace('\"', '"', str_replace('"{', '{', str_replace('"=>"', '":"', $hstore)))) . '}', true);
        
        if (!isset($json) || !is_array($json)) {
            return null;
        }
        
        $keywords = array();
        foreach ($json as $key => $value) {

            /*
             * $key format is "type:id"
             */
            list($type, $id) = explode(':', $key, 2);
            $hrefKey = $key;
            
            /*
             * Do not display landuse_details
             */
            if ($type === 'landuse_details') {
                continue;
            }

            /*
             * Value format is urlencode(json)
             */
            $properties = json_decode(urldecode($value), true); 
            if (!isset($properties['name'])) {
                $properties['name'] = trim($this->context->dictionary->getKeywordFromValue($id, $type));
                if (!isset($properties['name'])) {
                    $properties['name'] = ucwords($id);
                }
                $hrefKey = $properties['name'];
            }
            $keywords[] = array(
                'name' => isset($properties['name']) && $properties['name'] !== '' ? $properties['name'] : $key,
                'id' => $key,
                'href' => RestoUtil::updateUrl($url, array($this->model->searchFilters['language']['osKey'] => $this->context->dictionary->language,  $this->model->searchFilters['searchTerms']['osKey'] => count(explode(' ', $hrefKey)) > 1 ? '"'. $hrefKey . '"' : $hrefKey))
            );
            foreach (array_keys($properties) as $property) {
                if (!in_array($property, array('name', 'id'))) {
                    $keywords[count($keywords) - 1][$property] = $properties[$property];
                }
            }
        }

        return $keywords;
    }
 
}
