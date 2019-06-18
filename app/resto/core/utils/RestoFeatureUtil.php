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
 * resto feature manipulation
 */
class RestoFeatureUtil
{

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

    /**
     * Constructor
     *
     * @param RestoContext $context
     * @param RestoUser $user
     * @param RestoCollection $collection
     */
    public function __construct($context, $user, $collection)
    {
        $this->context = $context;
        $this->user =$user;

        /*
         * Initialize collections array with input collection
         */
        if (isset($collection)) {
            $this->collections[$collection->name] = $collection;
        }
    }

    /**
     *
     * Return a featureArray from an input rawFeatureArray.
     *
     * @param array $rawFeatureArray
     *
     */
    public function toFeatureArray($rawFeatureArray)
    {

        /*
         * No result - throw Not Found exception
         */
        if (!isset($rawFeatureArray) || !is_array($rawFeatureArray)) {
            RestoLogUtil::httpError(404);
        }

        /*
         * Retrieve collection from database
         */
        if (!isset($this->collections[$rawFeatureArray['collection']])) {
            $this->collections[$rawFeatureArray['collection']] = (new RestoCollection($rawFeatureArray['collection'], $this->context, $this->user))->load();
        }

        return $this->formatRawFeatureArray($rawFeatureArray);
    }

    /**
     * Return an array of featureArray from an input array of rawFeatureArray.
     *
     * @param array $rawFeatureArrayList
     * @return array
     */
    public function toFeatureArrayList($rawFeatureArrayList)
    {
        $featuresArray = array();
        for ($i = 0, $ii = count($rawFeatureArrayList); $i < $ii; $i++) {
            $featuresArray[] = $this->toFeatureArray($rawFeatureArrayList[$i]);
        }
        return $featuresArray;
    }

    /**
     * Update feature properties
     *
     * @param array $rawCorrectedArray
     * @param RestoCollection $collection
     *
     */
    private function toProperties($rawCorrectedArray)
    {
        $collection = $this->collections[$rawCorrectedArray['collection']];
        
        /*
         * Copy $rawCorrectedArray to fresh properties
         * [IMPORTANT] $rawCorrectedArray['metadata'][*] are moved at $properties root level
         */
        $properties = array();
        foreach (array_keys($rawCorrectedArray) as $key) {
            if ($key === 'metadata' && isset($rawCorrectedArray[$key])) {
                foreach (array_keys($rawCorrectedArray[$key]) as $metadataKey) {
                    $properties[$metadataKey] = $rawCorrectedArray[$key][$metadataKey];
                }
            } else {
                $properties[$key] = $rawCorrectedArray[$key];
            }
        }
        
        /*
         * Update resource and paths properties
         */
        $this->setPaths($properties, $collection);

        /*
         * Set links
         */
        $this->setLinks($properties, $collection, $rawCorrectedArray['id']);

        /*
         * Return properties
         */
        return $properties;
    }

    /**
     * Update metadata values from propertiesMapping
     *
     * @param array $properties
     * @param RestoCollection $collection
     */
    private function setPaths(&$properties, $collection)
    {

        /*
         * Update dynamically resource, quicklook and thumbnail path if required before the replaceInTemplate
         */
        $properties['quicklook'] = $collection->model->generateQuicklookUrl($properties);
        $properties['thumbnail'] = $collection->model->generateThumbnailUrl($properties);
        
        /*
         * Modify properties as defined in collection propertiesMapping associative array
         */
        if (isset($collection->propertiesMapping)) {
            $tmpProperties = $properties;

            /*
             * key can be a path i.e. key1.key2.key3
             */
            foreach ($collection->propertiesMapping as $key => $arr) {
                $childs = explode(Resto::MAPPING_PATH_SEPARATOR, $key);
                $property = &$properties;
                for ($i = 0, $ii = count($childs); $i < $ii; $i++) {
                    if (! isset($property[$childs[$i]])) {
                        $property[$childs[$i]] = array();
                    }
                    $property = &$property[$childs[$i]];
                    if ($i === $ii - 1) {
                        $property = RestoUtil::replaceInTemplate($arr, $tmpProperties);
                    }
                }
            }
        }
    }

    /**
     * Set links
     *
     * @param array $properties
     * @param RestoCollection $collection
     * @param string $featureId
     */
    private function setLinks(&$properties, $collection, $featureId)
    {
        if (!isset($properties['links']) || !is_array($properties['links'])) {
            $properties['links'] = array();
        }
        
        // This is always set
        $properties['links']['self'] = array(
            'type' => RestoUtil::$contentTypes['json'],
            'href' => RestoUtil::updateUrl($this->context->core['baseUrl'] . '/features/' . $featureId . '.json', array($collection->model->searchFilters['language']['osKey'] => $this->context->lang))
        );

        /*
         * If not license set, get the collection license
         */
        if (!isset($properties['links']['license'])) {
            $properties['links']['license'] = array(
                'id' => $collection->licenseId,
                'type' => 'application/json',
                'href' => $this->context->core['baseUrl'] . '/licenses/' . $collection->licenseId
            );
        }
    }

    /**
     *
     * PostgreSQL output columns are treated as string
     * thus they need to be converted to their true type
     *
     * @param Array $rawFeatureArray
     * @return array
     */
    private function formatRawFeatureArray($rawFeatureArray)
    {
        $properties = array();
        $geometry = null;
        $bbox = null;
        
        foreach ($rawFeatureArray as $key => $value) {
            if (is_null($value)) {
                $properties[$key] = null;
            } else {
                switch ($key) {

                    case 'links':
                    case 'assets':
                        break;

                    case 'geometry':
                        $geometry = json_decode($value, true);
                        break;

                    case 'bbox4326':
                        $bbox = array_map('floatval', explode(',', str_replace(' ', ',', substr(substr($rawFeatureArray[$key], 0, strlen($rawFeatureArray[$key]) - 1), 4))));
                        $properties['bbox3857'] = RestoGeometryUtil::bboxToMercator($bbox);
                        break;

                    case 'keywords':
                        $properties[$key] = $this->addKeywordsHref(json_decode($value, true), $this->collections[$rawFeatureArray['collection']]);
                        break;

                    case 'liked':
                        $properties[$key] = $value === 't' ? true : false;
                        break;
                    
                    case 'centroid':
                        $json = json_decode($value, true);
                        $properties[$key] = $json['coordinates'];
                        break;
                    
                    case 'status':
                    case 'visibility':
                    case 'likes':
                    case 'comments':
                        $properties[$key] = (integer) $value;
                        break;

                    case '_geometry':
                    case 'centroid':
                    case 'metadata':
                    case 'links':
                        $properties[$key] = json_decode($value, true);
                        break;

                    case 'hashtags':
                        $properties[$key] = explode(',', substr($value, 1, -1));
                        break;

                    default:
                        $properties[$key] = $value;

                }
            }
        }
        
        $feature = array(
            'type' => 'Feature',
            'id' => $rawFeatureArray['id'],
            'bbox' => $bbox,
            'geometry' => $geometry,
            'properties' => $this->toProperties($properties)
        );

        if (isset($rawFeatureArray['assets'])) {
            $feature['assets'] = json_decode($rawFeatureArray['assets'], true);
        }

        if (isset($rawFeatureArray['links'])) {
            $feature['links'] = json_decode($rawFeatureArray['links'], true);
        }
        
        return $feature;
    }


    /**
     *
     * Add href to keywords
     *
     * @param array $keywords
     * @param RestoCollection $collection
     *
     * @return array
     */
    private function addKeywordsHref($keywords, $collection)
    {
        if (!isset($keywords)) {
            return null;
        }
        
        foreach (array_keys($keywords) as $key) {
            $keywords[$key]['href'] = RestoUtil::updateUrl($this->context->core['baseUrl'] . '/services/search/' . $collection->name, array(
                $collection->model->searchFilters['language']['osKey'] => $this->context->lang,
                $collection->model->searchFilters['searchTerms']['osKey'] => count(explode(' ', $keywords[$key]['name'])) > 1 ? '"'. $keywords[$key]['name'] . '"' : $keywords[$key]['name']
            ));
        }

        return $keywords;
    }

    /**
     * Proxify WMS URL depending on user rights
     *
     * @param $properties
     * @param $user
     * @param $baseUrl
     * @return string relative path in the form of YYYYMMdd/thumbnail_filename with YYYYMMdd is the formated startDate parameter
     */
    private function proxifyWMSUrl($properties, $user, $baseUrl)
    {
        if (! isset($properties['resource']['browse']) || ! isset($properties['resource']['browse']['href'])) {
            return null;
        }

        $wmsUrl = RestoUtil::restoUrl($baseUrl, '/features/' . $properties['id'] . '/browse') . '?';

        if (isset($user->token)) {
            $wmsUrl .= '_bearer=' . $user->token . '&';
        }
        $wmsUrl .= substr($properties['resource']['browse']['href'], strpos($properties['wms'], '?') + 1);

        /*
         * If the feature has a license check authentication
         * [TODO] : something missing here !!!
         */
        if (isset($properties['links']['license']) && $properties['links']['license']['id'] !== 'unlicensed') {

            /*
             * Get License
             */
            $license = (new LicensesFunctions($this->context->dbDriver))->getLicense($properties['links']['license']['id']);

            /*
             * User is not authenticated
             * Returns wms url only if license has a 'public' view service
             */
            if (!isset($user->profile['id'])) {
                return $license['viewService'] === 'public' ? $wmsUrl : null;
            }
        }

        return $wmsUrl;
    }
}
