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
 * Optical satellite model
 */
class OpticalModel extends SatelliteModel
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
         *
         * [STAC][1.0.0-rc.4] STAC extensions require at least one field to validate. Since resto cannot check this constraint all references to extensions are removed from the output
         */
        //$this->stacExtensions[] = 'https://stac-extensions.github.io/eo/v1.0.0/schema.json';

        /*
         * Extend STAC mapping
         *
         * See - https://github.com/radiantearth/stac-spec/tree/dev/extensions/eo
         */
        $this->stacMapping = array_merge($this->stacMapping, array(

            // Estimate of cloud cover as a percentage (0-100) of the entire scene. If not available the field should not be provided.
            'cloudCover' => array(
                'key' => 'eo:cloud_cover'
            )
            
        ));

        /*
         * Extend search filters
         */
        $this->searchFilters = array_merge($this->searchFilters, array(

            'eo:cloudCover' => array(
                'key' => 'cloudCover',
                'osKey' => 'cloudCover',
                'stacKey' => 'eo:cloud_cover',
                'operation' => 'interval',
                'title' => 'Cloud cover expressed in percent',
                'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$',
                'queryable' => 'eo:cloud_cover',
                '$ref' => 'https://stac-extensions.github.io/eo/v1.0.0/schema.json#/properties/eo:cloud_cover'
            ),
    
            'eo:snowCover' => array(
                'key' => 'snowCover',
                'osKey' => 'snowCover',
                'operation' => 'interval',
                'title' => 'Snow cover expressed in percent',
                'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$'
            )
        ));

        /*
         * [IMPORTANT] The table $this->schema['name'].feature_optical must exist
         * with columns 'id' and at least the columns list below
         */
        $this->tables[] = array(
            'name' => 'feature_optical',
            'columns' => array(
                'snowcover',
                'cloudcover'
            )
        );
    }
}
