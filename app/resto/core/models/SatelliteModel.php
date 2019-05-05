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

    /*
     * Array of facet categories
     *
     * [IMPORTANT]
     *   - Facet categories is an array of facet category name
     *   - Each facet category name is indexed as a hashtag "<facetCategoryName>:value"
     *   - Hierarchy within a facet categories is the order of the array (first element is the parent)
     */
    private $extendedFacetCategories = array(
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
    );

    /**
     * Extended search filters for satellite collections
     */
    private $extendedSearchFilters = array(
        
        'eo:productType' => array(
            'key' => 'productType',
            'osKey' => 'productType',
            'operation' => 'keywords',
            'title' => 'A string identifying the entry type (e.g. ER02_SAR_IM__0P, MER_RR__1P, SM_SLC__1S, GES_DISC_AIRH3STD_V005)',
            'options' => 'auto'
        ),
        
        'eo:processingLevel' => array(
            'key' => 'processingLevel',
            'osKey' => 'processingLevel',
            'operation' => 'keywords',
            'title' => 'A string identifying the processing level applied to the entry',
            'options' => 'auto'
        ),
        
        'eo:platform' => array(
            'key' => 'platform',
            'osKey' => 'platform',
            'operation' => 'keywords',
            'title' => 'A string with the platform short name (e.g. Sentinel-1)',
            'options' => 'auto'
        ),
        
        'eo:instrument' => array(
            'key' => 'instrument',
            'osKey' => 'instrument',
            'operation' => 'keywords',
            'title' => 'A string identifying the instrument (e.g. MERIS, AATSR, ASAR, HRVIR. SAR)',
            'options' => 'auto'
        ),
        
        'eo:sensorType' => array(
            'key' => 'sensorType',
            'osKey' => 'sensorType',
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
            'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
            'quantity' => array(
                'value' => 'resolution',
                'unit' => 'm'
            )
        ),

        /*
         *  
         *
        'eo:orbitNumber' => array(
            'key' => 'orbitNumber',
            'osKey' => 'orbitNumber',
            'operation' => 'interval',
            'minInclusive' => 1,
            'quantity' => array(
                'value' => 'orbit'
            )
        ),*/
        
    );


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->addSearchFilters($this->extendedSearchFilters);
        $this->addFacetCategories($this->extendedFacetCategories);

        /*
         * [IMPORTANT] The table resto.feature_satellite must exist
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
