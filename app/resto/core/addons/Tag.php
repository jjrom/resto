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
 * Tag add-on
 *
 * This add-on compute tags from feature.
 *
 * It requires the iTag library (https://github.com/jjrom/itag)
 *
 */
class Tag extends RestoAddOn
{
    
    /**
     * Constructor
     *
     * @param RestoContext $context
     * @param RestoContext $user
     */
    public function __construct($context, $user)
    {
        parent::__construct($context, $user);
    }

    /**
     * Compute keywords from properties array
     *
     * @param array $properties
     * @param array $geometry (GeoJSON geometry)
     * @param array $taggers
     */
    public function getKeywords($properties, $geometry, $facetCategories, $taggers)
    {
        return $taggers ? array_merge($this->keywordsFromITag($properties, $geometry, $taggers), $this->keywordsFromProperties($properties, $facetCategories)) : $this->keywordsFromProperties($properties, $facetCategories);
    }

    /**
     * Return a RESTo keywords array from an iTag Hierarchical feature
     *
     * @param array $properties
     * @param array $geometry (GeoJSON)
     * @param array $taggers
     */
    private function keywordsFromITag($properties, $geometry, $taggers)
    {
        
        /*
         * No geometry = no iTag
         * 
         * [TODO] Add support to null geometry in iTag instead
         */
        if ( ! isset($geometry) ) 
        {
            return array();
        }

        // Geometry is mandatory - other parameters optional
        $queryParams = array(
            'geometry' => RestoGeometryUtil::geoJSONGeometryToWKT($geometry),
            'taggers' => join(',', $taggers)
        );

        if (isset($properties['startDate'])) {
            $queryParams['timestamp'] = $properties['startDate'];
        }

        try {
            $curl = new Curly();
            $iTagFeature = json_decode($curl->get($this->options['iTag']['endpoint'] . '?' . http_build_query($queryParams)), true);
            $curl->close();
        } catch (Exception $e) {
            $curl->close();
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }
        
        /*
         * Return empty result
         */
        if (!isset($iTagFeature) || !isset($iTagFeature['content'])) {
            return array();
        }

        return $this->processITagKeywords($iTagFeature);
    }

    /**
     * Return all keywords from iTag process
     *
     * @param array $iTagFeature
     */
    private function processITagKeywords($iTagFeature)
    {

        /*
         * Initialize keywords array from faceted properties
         */
        $keywords = array();

        if (isset($iTagFeature['content']['political']) && isset($iTagFeature['content']['political']['continents'])) {
            $keywords = array_merge($keywords, $this->getGenericKeywords($iTagFeature['content']['political']['continents'], array(
                'defaultName' => null,
                'parentId' => null
            )));
        }

        /*
         * Physical data
         */
        if (isset($iTagFeature['content']['physical'])) {
            $keywords = array_merge($keywords, $this->getGenericKeywords($iTagFeature['content']['physical'], array(
                'defaultName' => null,
                'parentId' => null
            )));
        }

        /*
         * Landcover
         */
        if (isset($iTagFeature['content']['landcover'])) {
            $keywords = array_merge($keywords, $this->getLandCoverKeywords($iTagFeature['content']['landcover']));
        }

        /*
         * Population
         */
        if (isset($iTagFeature['content']['population'])) {
            $keywords = array_merge($keywords, $this->getPopulationKeywords($iTagFeature['content']['population']));
        }

        /*
         * Keywords
         */
        if (isset($iTagFeature['content']['keywords'])) {
            $keywords = array_merge($keywords, $this->getAlwaysKeywords($iTagFeature['content']['keywords']));
        }

        return $keywords;
    }

    /**
     * Get keywords from iTag 'keywords' property
     *
     * @param array $properties
     */
    private function getAlwaysKeywords($properties)
    {
        $keywords = array();
        foreach (array_values($properties) as $id) {
            list($type, $normalized) = explode(Resto::TAG_SEPARATOR, $id, 2);
            if (!$this->alreadyExists($keywords, $id)) {
                $keywords[] = array(
                    'id' => $id,
                    'name' => ucfirst($normalized),
                    'type' => $type
                );
            }
        }
        return $keywords;
    }

    /**
     * Get Landcover keywords
     *
     * @param array $properties
     */
    private function getLandCoverKeywords($properties)
    {
        $keywords = array();

        /*
         * Main landcover
         */
        if (isset($properties['main'])) {
            foreach (array_values($properties['main']) as $landcover) {
                $id = $landcover['id'];
                list($type) = explode(Resto::TAG_SEPARATOR, $landcover['id'], 1);
                if (!$this->alreadyExists($keywords, $id)) {
                    $keywords[] = array(
                        'id' => $id,
                        'name' => $landcover['name'],
                        'type' => $type,
                        'area' => $landcover['area'],
                        'value' => $landcover['pcover']
                    );
                }
            }
        }

        return $keywords;
    }

    /**
     * Get generic keywords
     *
     * @param array $properties
     * @param array $options
     * @return array
     */
    private function getGenericKeywords($properties, $options)
    {
        $keywords = array();
        for ($i = 0, $ii = count($properties); $i < $ii; $i++) {
            $keyword = $this->getGenericKeyword($properties[$i], $options['defaultName'], $options['parentId']);
            if (! $this->alreadyExists($keywords, $keyword['id'])) {
                $keywords[] = $keyword;
                switch ($keyword['type']) {
                    case 'continent':
                        $keywords = array_merge($keywords, $this->getGenericKeywords($properties[$i]['countries'], array(
                            'parentId' => $keyword['id'],
                            'defaultName' => null
                        )));
                        break;
                    case 'country':
                        if (isset($properties[$i]['regions'])) {
                            $keywords = array_merge($keywords, $this->getGenericKeywords($properties[$i]['regions'], array(
                                'parentId' => $keyword['id'],
                                'defaultName' => '_all'
                            )));
                        }
                        break;
                    case 'region':
                        $keywords = array_merge($keywords, $this->getGenericKeywords($properties[$i]['states'], array(
                            'parentId' => $keyword['id'],
                            'defaultName' => '_unknown'
                        )));
                        break;
                    default:
                        break;
                }
            }
        }

        return $keywords;
    }

    /**
     * Get generic keyword
     *
     * @param array $property
     * @param string $defaultName
     * @param string $parentId
     *
     */
    private function getGenericKeyword($property, $defaultName, $parentId)
    {
        $exploded = explode(Resto::TAG_SEPARATOR, $property['id']);

        $keyword = array(
            'id' => $property['id'],
            'name' => $property['name'] ?? $defaultName,
            'type' => $exploded[0]
        );
        if (isset($parentId)) {
            $keyword['parentId'] = $parentId;
        }
        if (isset($property['area'])) {
            $keyword['value'] = $property['area'];
        }
        if (isset($property['pcover'])) {
            $keyword['value'] = $property['pcover'];
        }
        /*
         * Absolute coverage of geographical entity
         */
        if (isset($property['gcover'])) {
            $keyword['gcover'] = $property['gcover'];
        }

        return $keyword;
    }

    /**
     * Return a RESTo keywords array from feature properties
     *
     * @param array $properties
     * @param array $facetCategories
     */
    private function keywordsFromProperties($properties, $facetCategories)
    {
        $keywords = array();

        /*
         * Roll over facet categories
         */
        foreach (array_values($facetCategories) as $facetCategory) {
            $keywords = array_merge($keywords, $this->keywordsFromFacets($properties, $facetCategory));
        }

        /*
         * Get date keywords
         */
        return array_merge($keywords, $this->getDateKeywords($properties));
    }

    /**
     * Process keywords for facets
     *
     * @param array $properties
     * @param array $facetCategory
     * @return array
     */
    private function keywordsFromFacets($properties, $facetCategory)
    {
        $parentId = null;
        $keywords = array();
        for ($i = 0, $ii = count($facetCategory); $i < $ii; $i++) {
            
            $value = $properties[$facetCategory[$i]] ?? null;

            if (isset($value))
            {

                // If input property is an array then split into individuals values
                // This is to support "instruments" for instance
                // [IMPORTANT] Only a leaf (i.e. the last child) can be an array with more than 1 element
                // otherwise parentId computation can be erroneous
                if ( ! is_array($value) ) {
                    $value = array($value);
                }
                
                $newParentId = null;
                for ($j = 0, $jj = count($value); $j < $jj; $j++) {
                    $keyword = array();
                    $id = $facetCategory[$i] . Resto::TAG_SEPARATOR . $value[$j];
                    if (! $this->alreadyExists($keywords, $id)) {
                        $keyword = array(
                            'id' => $id,
                            'name' => $value[$j],
                            'type' => $facetCategory[$i],
                        );
                        if (isset($parentId)) {
                            $keyword['parentId'] = $parentId;
                        }
                        $keywords[] = $keyword;
                        $newParentId = $id;
                    }

                }
                if ($newParentId) {
                    $parentId = $id;
                }
                
            }
            else
            {
                $parentId = null;
            }
        }
        return $keywords;
    }

    /**
     * Process date keywords
     *
     * @param array $properties
     * @return array
     */
    private function getDateKeywords($properties)
    {
        
        $model = new DefaultModel();

        /*
         * startDate property is not present
         */
        if (! isset($properties[$model->searchFilters['time:start']['key']])) {
            return array();
        }

        /*
         * Year
         */
        $year = substr($properties[$model->searchFilters['time:start']['key']], 0, 4);

        /*
         * Month
         */
        $month = substr($properties[$model->searchFilters['time:start']['key']], 5, 2);

        /*
         * Day
         */
        $day = substr($properties[$model->searchFilters['time:start']['key']], 8, 2);

        return array(
            array(
                'id' => 'year' . Resto::TAG_SEPARATOR . $year,
                'name' => $year,
                'type' => 'year'
            ),
            array(
                'id' =>  'month' . Resto::TAG_SEPARATOR . $month,
                'name' => $month,
                'type' => 'month',
                'parentId' => 'year' . Resto::TAG_SEPARATOR . $year
            ),
            array(
                'id' => 'day' . Resto::TAG_SEPARATOR . $day,
                'name' => $day,
                'type' => 'day',
                'parentId' => 'month' . Resto::TAG_SEPARATOR . $month
            )
        );
    }

    /**
     * Get keywords from iTag 'population' property
     *
     * @param array $populationProperty
     */
    private function getPopulationKeywords($populationProperty)
    {
        return array(
            array(
                'id' => 'other:population',
                'name' => 'Population',
                'type' => 'other',
                'count' => $populationProperty['count'],
                'densityPerSquareKm' => $populationProperty['densityPerSquareKm']
            )
        );
    }

    /**
     * Return true if id exists in keywords array
     *
     * @param array $keywords
     * @param string $identifier
     */
    private function alreadyExists($keywords, $identifier)
    {
        for ($i = count($keywords); $i--;) {
            if ($identifier === $keywords[$i]['id']) {
                return true;
            }
        }
        return false;
    }
}
