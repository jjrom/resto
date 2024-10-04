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
    private $collections = array();

    /**
     * Constructor
     *
     * @param RestoContext $context
     * @param RestoUser $user
     * @param array $collections
     */
    public function __construct($context, $user, $collections)
    {
        $this->context = $context;
        $this->user =$user;
        $this->collections = $collections;
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
        $collection = $this->collections[$rawFeatureArray['collection']] ?? null;
        if (!isset($collection)) {
            $collection = $this->context->keeper->getRestoCollection($rawFeatureArray['collection'], $this->user)->load();
            $this->collections[$rawFeatureArray['collection']] = $collection;
        }

        return $this->formatRawFeatureArray($rawFeatureArray, $collection);
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
     *
     * PostgreSQL output columns are treated as string
     * thus they need to be converted to their true type
     *
     * @param Array $rawFeatureArray
     * @param RestoCollection $collection
     * @return array
     */
    private function formatRawFeatureArray($rawFeatureArray, $collection)
    {
        $self = $this->context->core['baseUrl'] . RestoUtil::replaceInTemplate(RestoRouter::ROUTE_TO_FEATURE, array(
            'collectionId' => $collection->id,
            'featureId' => $rawFeatureArray['id']
        ));
        $featureArray = array(
            'type' => 'Feature',
            'id' => $rawFeatureArray['id'],
            'geometry' => null,
            'properties' => array(),
            'collection' => $collection->id,
            'links' => array(
                array(
                    'rel' => 'self',
                    'type' => RestoUtil::$contentTypes['geojson'],
                    'href' => $self
                ),
                array(
                    'rel' => 'parent',
                    'type' => RestoUtil::$contentTypes['json'],
                    'title' => $collection->id,
                    'href' => $this->context->core['baseUrl'] . RestoUtil::replaceInTemplate(RestoRouter::ROUTE_TO_COLLECTION, array(
                        'collectionId' => $collection->id
                    ))
                ),
                array(
                    'rel' => 'collection',
                    'type' => RestoUtil::$contentTypes['json'],
                    'title' => $collection->id,
                    'href' => $this->context->core['baseUrl'] . RestoUtil::replaceInTemplate(RestoRouter::ROUTE_TO_COLLECTION, array(
                        'collectionId' => $collection->id
                    ))
                ),
                array(
                    'rel' => 'root',
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl']
                )
            ),
            'assets' => array(),
            'stac_version' => STACAPI::STAC_VERSION,
            'stac_extensions' => $collection->model->stacExtensions
        );

        /*
         * Handle SKOS relations
         */
        if (isset($this->context->addons['SOSA'])) {
            $featureArray['links'] = array_merge(
                $featureArray['links'],
                array(
                    array(
                        'rel' => 'child',
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $self . '/relations/hasSample'
                    ),
                    array(
                        'rel' => 'isSampleOf',
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $self . '/relations/isSampleOf'
                    )
                )
            );
        }

        foreach ($rawFeatureArray as $key => $value) {
            if (!isset($value)) {
                continue;
            }

            switch ($key) {
                case 'collection':
                    break;

                case 'completionDate':
                    $featureArray['properties']['end_datetime'] = $rawFeatureArray[$key];
                    break;

                case 'startDate':
                    if (isset($rawFeatureArray['completionDate'])) {
                        $featureArray['properties']['start_datetime'] = $rawFeatureArray[$key];
                    }
                    else {
                        $featureArray['properties']['datetime'] = $rawFeatureArray[$key];
                    }
                    break;

                case 'assets':
                case 'geometry':
                    $featureArray[$key] = json_decode($value, true);
                    break;

                case 'links':
                    $featureArray[$key] = array_merge($featureArray[$key], $this->getLinks(json_decode($value, true), $self));
                    break;

                case 'bbox4326':
                    $featureArray['bbox'] = RestoGeometryUtil::box2dTobbox($value);
                    break;

                case 'catalogs':
                    $featureArray['properties']['resto:catalogs'] = json_decode($value, true);
                    break;

                case 'liked':
                    $featureArray['properties'][$key] = $value === 't' ? true : false;
                    break;
                
                case 'centroid':
                    $featureArray['properties'][$key] = json_decode($value, true)['coordinates'];
                    break;
                
                case 'status':
                case 'visibility':
                case 'likes':
                case 'comments':
                    $featureArray['properties'][$key] = (integer) $value;
                    break;

                case 'hashtags':
                    $featureArray['properties'][$key] = explode(',', substr($value, 1, -1));
                    break;

                case 'metadata':
                    $metadata = json_decode($value, true);
                    if (isset($metadata)) {
                        foreach (array_keys($metadata) as $metadataKey) {
                            $featureArray['properties'][$metadataKey] = $metadata[$metadataKey];
                        }
                    }
                    break;

                default:
                    $featureArray['properties'][$key] = $value;
            }
        }

        // [STAC][1.0.0-rc.3] Add preview in rel
        if (isset($featureArray['assets']) && isset($featureArray['assets']['thumbnail'])) {
            $featureArray['links'][] = array(
                'rel' => 'preview',
                'type' => $featureArray['assets']['thumbnail']['type'],
                'href' => $featureArray['assets']['thumbnail']['href']
            );
        }

        // Add planet if not already set
        if (! isset($featureArray['properties']['ssys:targets']) || ! is_array($featureArray['properties']['ssys:targets'])) {
            $featureArray['properties']['ssys:targets'] = array($collection->getPlanet());
        }

        return $featureArray;
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
        if (isset($keywords)) {
            foreach (array_keys($keywords) as $key) {
                $keywords[$key]['href'] = RestoUtil::updateUrl(
                    $this->context->core['baseUrl'] . RestoUtil::replaceInTemplate(
                        RestoRouter::ROUTE_TO_FEATURES,
                        array(
                            'collectionId' => $collection->id
                        )
                    ),
                    array(
                        $collection->model->searchFilters['language']['osKey'] => $this->context->lang,
                        
                    )
                );
            }
        }

        return $keywords;
    }

    /**
     * Add default links (i.e. self, parent and collection links) to feature links
     *
     * @param array $inputLinks
     * @return array
     */
    private function getLinks($inputLinks)
    {
        $links = array();

        for ($i = count($inputLinks); $i--;) {
            if (!in_array($inputLinks[$i]['rel'], array('self', 'parent', 'collection', 'root'))) {
                $links[] = $inputLinks[$i];
            }
        }

        return $links;
    }
}
