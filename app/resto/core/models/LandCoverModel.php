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
 * LandCoverModel add search capabilities on features content
 * computed from iTag (i.e. land cover, population, etc.)
 */
class LandCoverModel extends DefaultModel
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
         * Add LandCover tagger
         */
        $this->tagConfig['taggers'][] = 'LandCover';

        /*
         * Extend search filters
         */
        if ( isset($this->options['addons']['Tag']) && $this->options['addons']['Tag']['options']['iTag']['addSearchFilters'] ) {
            $this->searchFilters = array_merge($this->searchFilters, array(

                'resto:cultivatedCover' => array(
                    'key' => 'cultivated',
                    'osKey' => 'cultivatedCover',
                    'operation' => 'interval',
                    'title' => 'Cultivated area expressed in percent',
                    'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$'
                ),
                
                'resto:desertCover' => array(
                    'key' => 'desert',
                    'osKey' => 'desertCover',
                    'operation' => 'interval',
                    'title' => 'Desert area expressed in percent',
                    'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$'
                ),
                
                'resto:floodedCover' => array(
                    'key' => 'flooded',
                    'osKey' => 'floodedCover',
                    'operation' => 'interval',
                    'title' => 'Flooded area expressed in percent',
                    'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$'
                ),
                
                'resto:forestCover' => array(
                    'key' => 'forest',
                    'osKey' => 'forestCover',
                    'operation' => 'interval',
                    'title' => 'Forest area expressed in percent',
                    'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$'
                ),
                
                'resto:herbaceousCover' => array(
                    'key' => 'herbaceous',
                    'osKey' => 'herbaceousCover',
                    'operation' => 'interval',
                    'title' => 'Herbaceous area expressed in percent',
                    'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$'
                ),
                
                'resto:iceCover' => array(
                    'key' => 'ice',
                    'osKey' => 'iceCover',
                    'operation' => 'interval',
                    'title' => 'Ice area expressed in percent',
                    'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$'
                ),
                
                'resto:urbanCover' => array(
                    'key' => 'urban',
                    'osKey' => 'urbanCover',
                    'operation' => 'interval',
                    'title' => 'Urban area expressed in percent',
                    'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$'
                ),
                
                'resto:waterCover' => array(
                    'key' => 'water',
                    'osKey' => 'waterCover',
                    'operation' => 'interval',
                    'title' => 'Water area expressed in percent',
                    'pattern' => '^(\[|\]|[0-9])?[0-9]+$|^[0-9]+?(\[|\])$|^(\[|\])[0-9]+,[0-9]+(\[|\])$'
                )
        
            ));
        }

        /*
         * [IMPORTANT] The table $this->schema['name'].feature_landcover must exist
         * with columns 'id' and at least the columns list below
         */
        $this->tables[] = array(
            'name' => 'feature_landcover',
            'columns' => array(
                'cultivated',
                'desert',
                'flooded',
                'forest',
                'herbaceous',
                'ice',
                'urban',
                'water',
                'population',
                'population_density'
            )
        );
    }

}
