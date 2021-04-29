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
        array_push($this->stacExtensions, 'https://stac-extensions.github.io/sat/v1.0.0/schema.json', 'https://stac-extensions.github.io/view/v1.0.0/schema.json');
        
        /*
         * Extend STAC mapping
         * 
         * See - https://github.com/radiantearth/stac-spec/blob/master/item-spec/common-metadata.md
         * See - https://github.com/stac-extensions/sat
         * See - https://github.com/radiantearth/stac-spec/tree/master/extensions/view
         */
        $this->stacMapping = array_merge($this->stacMapping, array(

            // Name of instrument or sensor used (e.g., MODIS, ASTER, OLI, Canon F-1).
            'instrument' => array(
                'key' => 'instruments',
                'convertTo' => 'array'
            ),

            // Ground Sample Distance at the sensor.
            'resolution' => array(
                'key' => 'gsd'
            ),

            // The absolute orbit number at the time of acquisition.
            'absoluteOrbitNumber' => array(
                'key' => 'sat:absolute_orbit'
            ),
            
            // The relative orbit number at the time of acquisition.
            'relativeOrbitNumber' => array(
                'key' => 'sat:relative_orbit'
            ),

            // The state of the orbit. Either ascending or descending for polar orbiting satellites, or geostationary for geosynchronous satellites
            'orbitDirection' => array(
                'key' => 'sat:orbit_state'
            ),
            
            // The angle from the sensor between nadir (straight down) and the scene center. Measured in degrees (0-90).
            'offNadir' => array(
                'key' => 'view:off_nadir'
            ),
            
            // The incidence angle is the angle between the vertical (normal) to the intercepting surface and the line of sight back to the satellite at the scene center. Measured in degrees (0-90).
            'incidenceAngle' => array(
                'key' => 'view:incidence_angle'
            ),
            
            // Viewing azimuth angle. The angle measured from the sub-satellite point (point on the ground below the platform) between the scene center and true north. Measured clockwise from north in degrees (0-360).
            'viewAzimuth' => array(
                'key' => 'view:azimuth'
            ),
            
            //Sun azimuth angle. From the scene center point on the ground, this is the angle between truth north and the sun. Measured clockwise in degrees (0-360).
            'sunAzimuth' => array(
                'key' => 'view:sun_azimuth'
            ),
            
            //Sun elevation angle. The angle from the tangent of the scene center point to the sun. Measured from the horizon in degrees (0-90).
            'sunElevation' => array(
                'key' => 'view:sun_elevation'
            )
        
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
         * [IMPORTANT] The table $this->schema['name'].feature_satellite must exist
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
