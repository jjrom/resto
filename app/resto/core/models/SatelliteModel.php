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
 * resto model for satellite imagery
 */
class SatelliteModel extends LandCoverModel
{

    /**
     * Constructor
     * 
     * @param array $options
     */
    public function __construct($options = array())
    {
        
        parent::__construct($options);
        
        /*
         * Satellite model follows STAC EO Extension Specification
         */
        $this->stacExtensions[] = 'sat';
        
        /*
         * Extend STAC mapping
         * 
         * See - https://github.com/radiantearth/stac-spec/tree/dev/extensions/sat
         */
        $this->stacMapping = array_merge($this->stacMapping, array(
            'instrument' => 'instruments',
            'resolution' => 'eo:gsd',
            'relativeOrbitNumber' => 'sat:relative_orbit',
            'orbitDirection' => 'sat:orbit_state'
        ));

        /*
         * Extend search filters
         */
        $this->searchFilters = array_merge($this->searchFilters, array(
        
            'eo:productType' => array(
                'key' => 'normalized_hashtags',
                'osKey' => 'productType',
                'prefix' => 'productType',
                'operation' => 'keywords',
                'title' => 'A string identifying the entry type (e.g. ER02_SAR_IM__0P, MER_RR__1P, SM_SLC__1S, GES_DISC_AIRH3STD_V005)',
                'options' => 'auto'
            ),
            
            'eo:processingLevel' => array(
                'key' => 'normalized_hashtags',
                'osKey' => 'processingLevel',
                'prefix' => 'processingLevel',
                'operation' => 'keywords',
                'title' => 'A string identifying the processing level applied to the entry',
                'options' => 'auto'
            ),
            
            'eo:platform' => array(
                'key' => 'normalized_hashtags',
                'osKey' => 'platform',
                'prefix' => 'platform',
                'operation' => 'keywords',
                'title' => 'A string with the platform short name (e.g. Sentinel-1)',
                'options' => 'auto'
            ),
            
            'eo:instrument' => array(
                'key' => 'normalized_hashtags',
                'osKey' => 'instrument',
                'prefix' => 'instrument',
                'operation' => 'keywords',
                'title' => 'A string identifying the instrument (e.g. MERIS, AATSR, ASAR, HRVIR. SAR)',
                'options' => 'auto'
            ),
            
            'eo:sensorType' => array(
                'key' => 'normalized_hashtags',
                'osKey' => 'sensorType',
                'prefix' => 'sensorType',
                'operation' => 'keywords',
                'title' => 'A string identifying the sensor type. Suggested values are: OPTICAL, RADAR, ALTIMETRIC, ATMOSPHERIC, LIMB',
                'options' => 'auto'
            )
            
            /* 
             *  
             *
            'eo:resolution' => array(
                'key' => 'resolution',
                'osKey' => 'resolution',
                'operation' => 'interval',
                'title' => 'Spatial resolution expressed in meters',
                'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$'
            ),
    
            /*
             *  
             *
            'eo:orbitNumber' => array(
                'key' => 'orbitNumber',
                'osKey' => 'orbitNumber',
                'operation' => 'interval',
                'minInclusive' => 1
            ),*/
            
        ));

        /*
         * Extend facet categories
         */
        $this->facetCategories = array_merge($this->facetCategories, array(
            array(
                'productType'
            ),
            array(
                'processingLevel'
            ),
            array(
                'sensorType',
                'platform',
                'instrument'
            )
        ));
        
        /*
         * [IMPORTANT] The table $this->schema.feature_satellite must exist
         * with columns 'id' and at least the columns list below
         */
        $this->tables[] = array(
            'name' => 'feature_satellite',
            'columns' => array(
                'resolution'
            )
        );
   
    }
}
