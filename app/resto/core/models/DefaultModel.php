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
 * resto Default Model
 * 
 * [IMPORTANT] Every model *MUST* extend DefaultModel
 */
class DefaultModel extends RestoModel
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
         * Extend search filters with date related filters
         */
        $this->searchFilters = array_merge($this->searchFilters, array(

            // Year
            'time:year' => array(
                'key' => 'normalized_hashtags',
                'osKey' => 'year',
                'prefix' => 'year',
                'operation' => 'keywords',
                'title' => 'Year in YYYY format',
                'pattern' => '^[0-9]{4}$',
                'options' => 'auto'
            ),

            // Month of year
            'time:month' => array(
                'key' => 'normalized_hashtags',
                'osKey' => 'month',
                'prefix' => 'month',
                'operation' => 'keywords',
                'title' => 'Month of the year in two digit (i.e. between 01 and 12)',
                'pattern' => '^[0-1][0-2]$',
                'options' => 'auto'
            ),

            // Day of month
            'time:day' => array(
                'key' => 'normalized_hashtags',
                'osKey' => 'day',
                'prefix' => 'day',
                'operation' => 'keywords',
                'title' => 'Day of month in two digit (i.e. between 1 and 31)',
                'pattern' => '^[0-3][0-9]$',
                'options' => 'auto'
            ),

            // Season (iTag needed)
            'resto:season' => array(
                'key' => 'normalized_hashtags',
                'osKey' => 'season',
                'prefix' => 'season',
                'operation' => 'keywords',
                'title' => 'Season name',
                'options' => 'auto'
            ),

            // Location (iTag needed)
            'resto:location' => array(
                'key' => 'normalized_hashtags',
                'osKey' => 'location',
                'prefix' => 'location',
                'operation' => 'keywords',
                'title' => 'Location',
                'options' => 'auto'
            ),

            // [STAC/WFS3] datetime is a mix of time:start/time:end
            'resto:datetime' => array(
                'key' => 'startDate',
                'osKey' => 'datetime',
                'title' => 'Single date+time, or a range ("/" separator) of the search query. Format should follow RFC-3339. Equivalent to OpenSearch {time:start}/{time:end}',
                'pattern' => '^[a-zA-Z0-9\-\/\.\:]+$'
            ),

            'time:start' => array(
                'key' => 'startDate',
                'osKey' => 'start',
                'operation' => '>=',
                'title' => 'Beginning of the time slice of the search query. Format should follow RFC-3339',
                'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$'
            ),
            
            'time:end' => array(
                'key' => 'startDate',
                'osKey' => 'end',
                'operation' => '<=',
                'title' => 'End of the time slice of the search query. Format should follow RFC-3339',
                'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}(T[0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?(|Z|[\+\-][0-9]{2}:[0-9]{2}))?$'
            )

        ));
    }

}
