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
 * JSON-LD additionnal metadata
 */
class JSONLDUtil
{

    public static $STAC_BROWSER_URL = 'https://radiantearth.github.io/stac-browser/#/external/';

    /**
     * Add additionnal DataCatalog JSON-LD metadata
     * 
     * See STAC discussion here - https://github.com/radiantearth/stac-spec/issues/378
     *
     * @param array $catalog
     * @return array
     */
    public static function addDataCatalogMetadata($catalog)
    {

        // Parse links
        $isPartOf = array();
        $hasPart = array();
        $dataset = array();
        $url = null;

        for ($i = 0, $ii = count($catalog['links']); $i < $ii; $i++) {
            
            if ($catalog['links'][$i]['rel'] === 'self') {
                $url = $catalog['links'][$i]['href'];
            }

            else if ($catalog['links'][$i]['rel'] === 'parent') {
                $isPartOf = array(
                    '@type' => 'DataCatalog',
                    'name' => $catalog['links'][$i]['title'] ?? '',
                    'isBaseOn' => $catalog['links'][$i]['href'],
                    'url' =>  JSONLDUtil::$STAC_BROWSER_URL . (explode('://', $catalog['links'][$i]['href'])[1])
                );
            }

            else if ($catalog['links'][$i]['rel'] === 'child') {
                $hasPart[] = array(
                    '@type' => 'DataCatalog',
                    'name' => $catalog['links'][$i]['title'] ?? '',
                    'isBaseOn' => $catalog['links'][$i]['href'],
                    'url' =>  JSONLDUtil::$STAC_BROWSER_URL . (explode('://', $catalog['links'][$i]['href'])[1])
                );
            }

            else if ($catalog['links'][$i]['rel'] === 'item') {
                $id = isset($catalog['links'][$i]['id']) ? $catalog['links'][$i]['id'] : end(explode('/', $catalog['links'][$i]['href']));
                $dataset[] = array(
                    'identifier' => $i,
                    'name' => $catalog['links'][$i]['title'] ?? $id,
                    'isBaseOn' => $catalog['links'][$i]['href'],
                    'url' =>  JSONLDUtil::$STAC_BROWSER_URL . (explode('://',$catalog['links'][$i]['href'])[1])
                );
            }

        }

        $jsonld = JSONLDUtil::getCommonMetadata($catalog, $url);

        if ( isset($catalog['extent'])) {
            
            if ( isset($catalog['extent']['spatial']['bbox']) ) {
                $jsonld['spatialCoverage'] = array(
                    '@type' => 'Place',
                    'geo' => array(
                        '@type' => 'GeoShape',
                        'box' => join(' ', $catalog['extent']['spatial']['bbox'][0])
                    )
                );
            }
            
            if ( isset($catalog['extent']['temporal']['interval']) ) {
                $jsonld['temporalCoverage'] = join('/', array($catalog['extent']['temporal']['interval'][0][0] ?? '..', $catalog['extent']['temporal']['interval'][0][1] ?? '..'));
            }

        }
        
        if ( isset($isPartOf) ) {
            $jsonld['isPartOf'] = $isPartOf;
        }

        if ( !empty($hasPart) ) {
            $jsonld['hasPart'] = $hasPart;
        }

        if ( !empty($dataset) ) {
            $jsonld['dataset'] = $dataset;
        }

        return array_merge($catalog, $jsonld);
    }


    /**
     * Add additionnal Datasets JSON-LD metadata
     *
     * @param array $item
     * @return array
     */
    public static function addDatasetsMetadata($item)
    {

        $url = null;
        $thumbnail = null;
        $includedInDataCatalog = array();
        $distribution = array();

        for ($i = 0, $ii = count($item['links']); $i < $ii; $i++) {
            if ($item['links'][$i]['rel'] === 'self') {
                $url = $item['links'][$i]['href'];
            }
            else if ($item['links'][$i]['rel'] === 'parent') {
                $includedInDataCatalog[] = array(
                    'isBaseOn' => $item['links'][$i]['href'],
                    'url' => JSONLDUtil::$STAC_BROWSER_URL . (explode('://', $item['links'][$i]['href'])[1])
                );
            }
        }

        foreach (array_keys($item['assets']) as $key) {
            
            if ( !empty($item['assets'][$key]['roles']) && in_array('thumbnail', $item['assets'][$key]['roles']) ) {
                $thumbnail = $item['assets'][$key]['href'];
            }

            $distribution[] = array(
                'contentUrl' => $item['assets'][$key]['href'],
                'fileFormat' => $item['assets'][$key]['type'] ?? null,
                'title' => $item['assets'][$key]['title'] ?? null
            );

        }

        $jsonld = JSONLDUtil::getCommonMetadata($item, $url);

        if ( isset($item['bbox'])) {
           $jsonld['spatialCoverage'] = array(
                '@type' => 'Place',
                'geo' => array(
                    '@type' => 'GeoShape',
                    'box' => join(' ', $item['bbox'])
                )
            );
        }
    
            
        if ( isset($catalog['extent']['temporal']['interval']) ) {
            $jsonld['temporalCoverage'] = join('/', array($catalog['extent']['temporal']['interval'][0][0] ?? '..', $catalog['extent']['temporal']['interval'][0][1] ?? '..'));
        }

        if ( isset($item['properties']['start_datetime']) ) {
            $jsonld['temporalCoverage'] = join('/', array($item['properties']['start_datetime'] ?? '..', $item['properties']['end_datetime'] ?? '..'));
        }
        else if ( isset($item['properties']['datetime']) ) {
            $jsonld['temporalCoverage'] = $item['properties']['datetime'];
        }

        if ( isset($thumbnail) ) {
            $jsonld['image'] = $thumbnail;
        }

        if ( !empty($distribution) ) {
            $jsonld['distribution'] = $distribution;
        }
        
        return array_merge($item, $jsonld);

    }

    /**
     * Return common catalog/item JSON-LD metadata
     * 
     * @param array $obj
     * @param string $url
     */
    private static function getCommonMetadata($obj, $url)
    {

        $jsonld = array(
            // Required
            '@context' => 'https://schema.org/',
            '@type' => 'DataCatalog',
            'name' => $obj['title'] ?? $obj['id'],
            // Recommended
            'identifier' => $obj['properties']['sci:doi'] ?? $obj['id'],
            'isBasedOn' => $url,
            'url' => JSONLDUtil::$STAC_BROWSER_URL . (explode('://', $url)[1])
        );

        if ( isset($obj['properties']['sci:citation'] ) ) {
            $jsonld['citation'] = $obj['properties']['sci:citation'];
        }
       
        if ( isset($obj['properties']['sci:publications']) ) {
            $jsonld['workExample'] = array(
                'identifier' => $obj['properties']['sci:publications']['doi'] ?? null,
                'citation' => $obj['properties']['sci:publications']['citation'] ?? null
            );
        }
  
        return $jsonld;

    } 

}
