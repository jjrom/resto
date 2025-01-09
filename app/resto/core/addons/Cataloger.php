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
 * This add-on compute classification catalogs from input feature properties
 * It requires the iTag library (https://github.com/jjrom/itag)
 * 
 * [IMPORTANT][READ THIS]
 * 
 * Classification catalogs are processed :
 *    - From iTag (i.e. landcover, geographical, etc.)
 *    - From date (i.e. year/month/date and seasons)
 *    - From input property 'resto:catalogs'
 * 
 * The latter is an 'isExternal' catalog i.e. it's a user defined catalog.
 * Thus, only user with CREATE_CATALOG can create it
 * 
 * The 'properties' array is merged to the input feature properties array
 * 
 * Additionnaly, some computed catalogs are 'isNotACatalog' which means that 
 * the 'properties' are stored in final properties but do not leads to 
 * the creation of a catalog. This is the case for 'population' property
 * 
 */
class Cataloger extends RestoAddOn
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
     * Compute catalogs from properties array
     *
     * @param array $properties
     * @param array $geometry (GeoJSON geometry)
     * @param RestoCollection $collection
     * @param array $iTagParams
     */
    public function getCatalogs($properties, $geometry, $collection, $iTagParams)
    {
        $iTagHasEndpoint = isset($this->options['iTag']) && !empty($this->options['iTag']['endpoint']);
        if ( $iTagHasEndpoint ) {
            return array_merge($this->catalogsFromITag($properties, $geometry, $iTagParams), $this->catalogsFromProperties($properties, $collection, true));
        }
        return $this->catalogsFromProperties($properties, $collection, $iTagHasEndpoint);
    }

    /**
     * Extract hashtags from a text and return a catalogs list - invalid characters are discarded
     *
     * [WARNING] The leading '#' is not returned
     *
     * Example:
     *
     *    $text = "This is a #test #withA!%.badhashtag"
     *
     * returns:
     *
     *      array(
     *          array(
     *              'id' => 'hashtags/test',
     *              'title' => 'test',
     *              'description' => 'Catalog of features containing hashtag #test'  
     *          ),
     *          array(
     *              'id' => 'hashtags/withabadhashtag',
     *              'title' => 'test',
     *              'description' => 'Catalog of features containing hashtag #withabadhashtag'  
     *          )
     *      )
     *
     * @param string $text
     *
     * @return array
     */
    public function catalogsFromText($text)
    {
        $matches = null;
        $catalogs = array();
        if (isset($text)) {
            preg_match_all("/#([^ ]+)/u", RestoUtil::cleanHashtag($text), $matches);
            if ($matches) {
                $hashtagsArray = array_count_values($matches[1]);
                if (count($hashtagsArray) > 0) {
                    $catalogs = array(
                        array(
                            'id' => 'hashtags',
                            'title' => 'hashtags',
                            'description' => 'Catalog of features per hashtags'
                        )
                    );
                    foreach (array_keys($hashtagsArray) as $key) {                  
                        $catalogs[] = array(
                            'id' => 'hashtags/' . $key,
                            'title' => $key,
                            'description' => 'Catalog of features containing hashtag #' . $key,
                            'rtype' => 'hashtag'
                        );
                    }
                }
            }
        }
        return $catalogs;
    }

    /**
     * Return a RESTo catalogs array from an iTag Hierarchical feature
     *
     * @param array $properties
     * @param array $geometry (GeoJSON)
     * @param array $iTagParams
     */
    private function catalogsFromITag($properties, $geometry, $iTagParams)
    {
        
        if ( !isset($iTagParams) || empty($iTagParams['taggers']) || !isset($geometry)) {
            return array();
        }
        
        $taggerKeys = array_keys($iTagParams['taggers']);
        $queryParams = array(
            'geometry' => RestoGeometryUtil::geoJSONGeometryToWKT($geometry),
            'taggers' => join(',', $taggerKeys),
            'planet' => $iTagParams['planet'] ?? $this->context->core['planet']
        );

        if (isset($properties['startDate'])) {
            $queryParams['timestamp'] = $properties['startDate'];
        }

        /*
         * Convert taggers options to query params
         */
        for ($i = count($taggerKeys); $i--;) {
            foreach ($iTagParams['taggers'][$taggerKeys[$i]] as $optionName => $optionValue) {
                $queryParams[strtolower($taggerKeys[$i]) . '_' . $optionName] = $optionValue;
            }
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
     * Convert iTag keywords to catalogs 
     *
     * @param array $iTagFeature
     */
    private function processITagKeywords($iTagFeature)
    {

        $catalogs = array();

        /*
         * Process keywords
         */
        foreach ($iTagFeature['content'] as $key => $value) {
            switch ($key) {

                case 'political':
                    if (isset($value['continents'])) {
                        $catalogs[] = array(
                            'id' => 'continents',
                            'title' => 'Continents',
                            'description' => 'Automatic geographical classification processed by [iTag](https://github.com/jjrom/itag)'
                        );
                        $catalogs = array_merge($catalogs, $this->getCatalogsFromGeneric($value['continents'], 'continents'));
                    }
                    break;

                case 'physical':
                    $physical =  $this->getCatalogsFromPhysical($value, 'physical');
                    if (count($physical) > 0) {
                        $catalogs[] = array(
                            'id' => 'physical',
                            'title' => 'Physical',
                            'description' => 'Automatic physical classification processed by [iTag](https://github.com/jjrom/itag)'
                        );
                        $catalogs = array_merge($catalogs, $physical);
                    }
                    break;

                case 'landcover':
                    $landcover = $this->getCatalogsFromLandCover($value, 'landcover');
                    if (count($landcover) > 0) {
                        $catalogs[] = array(
                            'id' => 'landcover',
                            'title' => 'Landcover classification',
                            'description' => 'Automatic landcover classification processed by [iTag](https://github.com/jjrom/itag)'
                        );
                        $catalogs = array_merge($catalogs, $landcover);
                    }
                    break;

                case 'population':
                    $catalogs[] = array(
                        'id' => 'population',
                        'title' => 'Population',
                        'isNotACatalog' => true,
                        'properties' => array(
                            'itag:population' => array(
                                'count' => $value['count'],
                                'densityPerSquareKm' => $value['densityPerSquareKm']
                            )    
                        )
                    );
                    break;
                
                case 'keywords':
                    $catalogs = array_merge($catalogs, $this->getCatalogsFromAlways($value));
                    break;

                default:
                    if (is_array($value)) {
                        $catalogs = array_merge($catalogs, $this->getCatalogsFromGeneric($value));
                    }
            }
        }

        return $catalogs;
    }

    /**
     * Get keywords from iTag 'keywords' property
     *
     * @param array $properties
     */
    private function getCatalogsFromAlways($properties)
    {
        
        $catalogs = array();

        foreach (array_values($properties) as $typeAndId) {
            $exploded = explode(RestoConstants::ITAG_SEPARATOR, $typeAndId);
            $parentId = substr($exploded[0], -1) === 's' ? $exploded[0] : $exploded[0] . 's';
            if (!$this->alreadyExists($catalogs, $parentId)) {
                $catalogs[] = array(
                    'id' => $parentId,
                    'title' => ucfirst($parentId),
                    'description' => 'Catalogs of features per ' . $exploded[0]
                );
            }
            $id = $parentId . '/' . $exploded[1];
            if (!$this->alreadyExists($catalogs, $id)) {
                $catalogs[] = array(
                    'id' => $id,
                    'title' => ucfirst($exploded[1]),
                    'description' => 'Catalogs of features per ' . $exploded[0] . ' ' . $exploded[1],
                    'rtype' => $exploded[0]
                );
            }
        }
        return $catalogs;
    }

    /**
     * Get Landcover catalogs
     *
     * @param array $properties
     * @param string $parentId
     */
    private function getCatalogsFromLandCover($properties, $parentId)
    {
        $catalogs = array();

        /*
         * Main landcover
         */
        if (isset($properties['main'])) {
            foreach (array_values($properties['main']) as $landcover) {
                $exploded = explode(RestoConstants::ITAG_SEPARATOR, $landcover['id']);
                $id = $parentId . '/' . $exploded[1];
                if ( !$this->alreadyExists($catalogs, $id) ) {
                    $catalogs[] = array(
                        'id' => $id,
                        'title' => $landcover['name'],
                        'rtype' => $exploded[0],
                        'properties' => array(
                            'itag' . RestoConstants::ITAG_SEPARATOR . $landcover['id']  => array(
                                'area' => $landcover['area'],
                                'pcover' => $landcover['pcover']
                            )
                        )
                    );
                }
            }
        }

        return $catalogs;
    }

    /**
     * Get physical catalogs
     *
     * @param array $physicals
     * @param string $parentId
     */
    private function getCatalogsFromPhysical($physicals, $parentId)
    {
        $catalogs = array();

        /*
         * Main landcover
         */
        for ($i = 0, $ii=count($physicals); $i < $ii; $i++) {
            $exploded = explode(RestoConstants::ITAG_SEPARATOR, $physicals[$i]['id']);
            $type = $exploded[0];
            $id = $parentId . '/' . $type . 's';
            if ( !$this->alreadyExists($catalogs, $type) ) {
                $catalogs[] = array(
                    'id' => $id,
                    'title' => ucfirst($type) . 's',
                    'description' => 'Automatic ' . $type .  ' classification processed by [iTag](https://github.com/jjrom/itag)',
                    'rtype' => $type
                );
            }
            $catalogs[] = $this->getCatalogFromGeneric($physicals[$i], $id);
        }

        return $catalogs;
    }

    /**
     * Get generic keywords
     *
     * @param array $properties
     * @param array $options
     * @return array
     */
    private function getCatalogsFromGeneric($properties, $parentId = null)
    {
        $catalogs = array();
        for ($i = 0, $ii = count($properties); $i < $ii; $i++) {
            $catalog = $this->getCatalogFromGeneric($properties[$i], $parentId);
            if (! $this->alreadyExists($catalogs, $catalog['id'])) {
                $catalogs[] = $catalog;

                switch ($catalog['rtype']) {
                    
                    case 'continent':
                        $catalogs = array_merge($catalogs, $this->getCatalogsFromGeneric($properties[$i]['countries'], $catalog['id']));
                        break;
                    case 'country':
                        if (isset($properties[$i]['regions'])) {
                            $catalogs = array_merge($catalogs, $this->getCatalogsFromGeneric($properties[$i]['regions'], $catalog['id']));
                        }
                        break;
                    case 'region':
                        $catalogs = array_merge($catalogs, $this->getCatalogsFromGeneric($properties[$i]['states'], $catalog['id']));
                        break;
                    default:
                        break;
                }
            }
        }

        return $catalogs;
    }

    /**
     * Get generic keyword
     *
     * @param array $property
     * @param string $parentId
     *
     */
    private function getCatalogFromGeneric($property, $parentId)
    {
        $exploded = explode(RestoConstants::ITAG_SEPARATOR, $property['id']);

        $catalog = array(
            'id' => (isset($parentId) ? $parentId : '') . '/' . $exploded[1],
            'title' => $property['name'] ?? $exploded[1],
            'description' => 'Catalog of features for ' . ($property['name'] ?? $exploded[1]),
            'rtype' => $exploded[0]
        );

        $properties = array();

        if (isset($property['area'])) {
            $properties['area'] = $property['area'];
        }
        if (isset($property['pcover'])) {
            $properties['pcover'] = $property['pcover'];
        }

        /*
         * Absolute coverage of geographical entity
         */
        if (isset($property['gcover'])) {
            $properties['gcover'] = $property['gcover'];
        }

        if ( !empty($properties) ) {
            $catalog['properties'] = array(
                'itag' . RestoConstants::ITAG_SEPARATOR . $property['id'] => $properties
            );
        }
        
        return $catalog;
    }

    /**
     * Return a catalogs array from feature properties
     *
     * @param array $properties
     * @param RestoCollection $collection
     * @param boolean $addDateCatalogs
     */
    private function catalogsFromProperties($properties, $collection, $addDateCatalogs)
    {
        /*
         * [IMPORTANT] If input properties contains a resto:catalogs property then use it
         * [SECURITY] The isExternal is automatically set to true for these catalogs to 
         * avoid non authorized user to create a catalog if he doesn't have rights to do so
         */
        $catalogs = $properties['resto:catalogs'] ?? array();
        for ($i = count($catalogs); $i--;) {
            $exploded = explode('/', $catalogs[$i]['id']);
            $catalogs[$i]['isExternal'] = true;
            $catalogs[$i]['rtype'] = 'catalog';
        }
        
        /*
         * Roll over facet categories
         */
        $model = isset($collection) ? $collection->model : new DefaultModel();
        foreach (array_values($model->facetCategories) as $facetCategory) {
            $catalogs = array_merge($catalogs, $this->catalogsFromFacetCategory($properties, $facetCategory, $model));
        }
        
        /*
         * Compute catalogs from description
         */
        $hashtags = $this->catalogsFromText($properties['description'] ?? null);
        if ( !empty($hashtags) ) {
            $catalogs = array_merge($catalogs, $hashtags);
        }
        
        /*
         * Finnaly date catalogs
         */
        return $addDateCatalogs? array_merge($catalogs, $this->getDateCatalogs($properties, $model)) : $catalogs;
    }

    /**
     * Process catalogs from facet category
     *
     * @param array $properties
     * @param array $facetCategory
     * @param RestoModel $model
     * @return array
     */
    private function catalogsFromFacetCategory($properties, $facetCategory, $model)
    {
        $parentId = null;
        $catalogs = array();

        for ($i = 0, $ii = count($facetCategory); $i < $ii; $i++) {
            // Get value in properties for input facetCategory
            $value = $properties[$facetCategory[$i]] ?? null;
            
            // If the facetCategory is not found, try the STAC alias
            if (!isset($value) && isset($model->stacMapping[$facetCategory[$i]])) {
                $value = $properties[$model->stacMapping[$facetCategory[$i]]['key']] ?? null;
            }

            if (isset($value)) {
                // If input property is an array then split into individuals values
                // This is to support "instruments" for instance
                // [IMPORTANT] Only a leaf (i.e. the last child) can be an array with more than 1 element
                // otherwise parentId computation can be erroneous
                if (! is_array($value)) {
                    $value = array($value);
                }
                
                $newParentId = null;
                for ($j = 0, $jj = count($value); $j < $jj; $j++) {
                    // If parentId is null, create a root catalog using plural of facetCategory 
                    if ( !isset($parentId) ) {
                        $parentId = $facetCategory[$i] . (substr($facetCategory[$i], -1) === 's' ? '' : 's');
                        if (! $this->alreadyExists($catalogs, $parentId)) {
                            $catalogs[] = array(
                                'id' => $parentId,
                                'title' => $parentId,
                                'description' => 'Catalog of features per ' . $facetCategory[$i]
                            );
                        }
                    }
                    // [IMPORTANT] Remove spaces from id
                    $id = $parentId . '/' . str_replace(' ', '', $value[$j]);
                    if (! $this->alreadyExists($catalogs, $id)) {
                        $catalogs[] = array(
                            'id' => $id,
                            'title' => $value[$j],
                            'description' => 'Catalog of features for ' . $facetCategory[$i] . ' ' . $value[$j],
                            'rtype' => $facetCategory[$i]
                        );
                        $newParentId = $id;
                    }
                }
                if ($newParentId) {
                    $parentId = $id;
                }
            } else {
                $parentId = null;
            }
        }
        return $catalogs;
    }

    /**
     * Convert date keywords to catalogs
     *
     * @param array $properties
     * @param RestoModel $model
     * @return array
     */
    private function getDateCatalogs($properties, $model)
    {

        $startDate = $properties[$model->searchFilters['time:start']['key']];

        /*
         * No startDate property or both startDate and completionDate are presents => range is not supported
         */
        if (! isset($startDate) || (isset($startDate) && isset($properties['completionDate']))) {
            return array();
        }

        /*
         * Year
         */
        $year = substr($startDate, 0, 4);

        /*
         * Month
         */
        $month = substr($startDate, 5, 2);

        /*
         * Day
         */
        $day = substr($startDate, 8, 2);

        return array(
            array(
                'id' => 'years',
                'title' => 'years',
                'description' => 'Catalog of features per year'
            ),
            array(
                'id' => 'years/' . $year,
                'title' => $year,
                'rtype' => 'year'
            ),
            array(
                'id' =>  'years/' . $year . '/' . $month,
                'title' => $month,
                'rtype' => 'month'
            ),
            array(
                'id' =>  'years/' . $year . '/' . $month . '/' . $day,
                'title' => $day,
                'rtype' => 'day'
            )
        );
    }

    /**
     * Return true if id exists in keywords array
     *
     * @param array $catalogs
     * @param string $identifier
     */
    private function alreadyExists($catalogs, $identifier)
    {
        for ($i = count($catalogs); $i--;) {
            if ($identifier === $catalogs[$i]['id']) {
                return true;
            }
        }
        return false;
    }
}
