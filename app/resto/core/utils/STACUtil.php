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
 * STAC utilities
 */
class STACUtil
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
     * Classifications
     */
    public $classifications = array(
        'geographical' => array(
            'continent', 'location', 'ocean', 'sea', 'river', 'bay', 'channel', 'fjord', 'gulf', 'inlet', 'lagoon', 'sound', 'strait'
        ),
        'temporal' => array(
            'season','year', 'month', 'day'
        )
    );

    /**
     * Constructor
     *
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user)
    {
        $this->context = $context;
        $this->user =$user;
    }

    /**
     * Get root links
     *
     * @param integer $minMatch
     *
     * @return array
     */
    public function getRootCatalogLinks($minMatch)
    {
        $links = array();

        /*
         * Themes are built from theme:xxxx collection keywords
         * Only displayed if at least one theme exists
         */
        if (!empty($this->getThemesRootLinks())) {
            $links[] = array(
                'rel' => 'child',
                'title' => 'Themes',
                'type' => RestoUtil::$contentTypes['json'],
                'href' => $this->context->core['baseUrl'] . '/catalogs/themes'
            );
        }

        /*
         * [STAC] Duplicate rel="data"
         */
        $collections = ($this->context->keeper->getRestoCollections($this->user)->load())->toArray();
        if (count($collections) > 0) {
            $links[] = array(
                'rel' => 'child',
                'title' => 'Collections',
                'type' => RestoUtil::$contentTypes['json'],
                'matched' =>  count($collections['collections']),
                'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_COLLECTIONS,
                'roles' => array('collections')
            );
        }

        /*
         * SOSA concepts
         * Only displayed if at least one concept exists
         */
        if (isset($this->context->addons['SOSA'])) {
            $skos = new SKOS($this->context, $this->user);
            $concepts = $skos->getConcepts($this->context->query);
            if (count($concepts['links']) > 2) {
                $links[] = array(
                    'rel' => 'child',
                    'title' => 'Concepts',
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl'] . '/catalogs/concepts'
                );
            }
        }

        /*
         * Exposed views as STAC catalogs
         * Only displayed if at least one theme exists
         */
        if (isset($this->context->addons['View'])) {
            $stacLink = (new View($this->context, $this->user))->getSTACRootLink();
            if (isset($stacLink) && $stacLink['matched'] > 0) {
                $links[] = $stacLink;
            }
        }

        $facets = $this->getFacetsCount($minMatch);

        foreach (array('catalogs', 'facets') as $key) {

            $childKeys = $facets[$key];
            if (isset($childKeys)) {
                
                // Remove the "catalogs" and "facets" childs level and directly
                // merge there respective childs to the root endpoint
                if ( $this->context->core['mergeRootCatalogLinks']) {
                    if ( !is_array($childKeys) && $key === 'catalogs' ) {
                        $childKeys = $this->getCatalogChilds();
                    }
                    foreach (array_keys($childKeys) as $childKey) {
                        $link = array(
                            'rel' => 'child',
                            'title' => isset($childKeys[$childKey]['title']) ? $childKeys[$childKey]['title'] : ucfirst($childKey),
                            'type' => RestoUtil::$contentTypes['json'],
                            'href' => $this->context->core['baseUrl'] . '/catalogs/' . rawurlencode($key) . '/' . rawurlencode($childKey)
                        );
                        if ( isset($childKeys[$childKey]['count']) ) {
                            $link['count'] = $childKeys[$childKey]['count'];
                        }
                        $links[] = $link;
                    }
                    
                }
                else {
                    $links[] = array(
                        'rel' => 'child',
                        'title' => ucfirst($key),
                        'type' => RestoUtil::$contentTypes['json'],
                        'href' => $this->context->core['baseUrl'] . '/catalogs/' . rawurlencode($key)
                    );
                }
            }
        }

        // Hashtags
        if (isset($facets['hashtags'])) {
            $links[] = array(
                'rel' => 'child',
                'title' => ucfirst('hashtags'),
                'type' => RestoUtil::$contentTypes['json'],
                'href' => $this->context->core['baseUrl'] . '/catalogs/' . rawurlencode('hashtags')
            );
        }
        
        return $links;
    }

    /**
     * Return Themes links
     *
     * @return array
     */
    public function getThemesRootLinks()
    {
        $links = array();

        // Load collections
        $collections = $this->context->keeper->getRestoCollections($this->user)->load();
        
        // Get themes
        $themes = array();

        foreach (array_values($collections->collections) as $collectionContent) {
            if (isset($collectionContent->keywords)) {
                for ($i = count($collectionContent->keywords); $i--;) {
                    $splitted = explode(':', $collectionContent->keywords[$i]);
                    if (count($splitted) > 1 && $splitted[0] === 'label' && !in_array($splitted[1], $themes)) {
                        $themes[] = $splitted[1];
                        $links[] = array(
                            'rel' => 'child',
                            'title' => $splitted[1],
                            'type' => RestoUtil::$contentTypes['json'],
                            'href' => $this->context->core['baseUrl'] . '/catalogs/themes/' . rawurlencode($splitted[1])
                        );
                    }
                }
            }
        }

        return $links;
    }

    /**
     * Return count per facet type
     *
     * @param integer $minMatch
     * @return array
     */
    public function getFacetsCount($minMatch)
    {
        $facets = array(
            'count' => 0,
            'catalogs' => array(),
            'hashtags' => array(),
            'facets' => array()
        );

        foreach ($this->classifications as $key => $value) {
            $facets['facets'][$key] = array();
        }

        try {
            $results = $this->context->dbDriver->query('SELECT split_part(type, \':\', 1) as type, sum(counter) as matched FROM ' . $this->context->dbDriver->targetSchema . '.facet WHERE pid=\'root\' GROUP BY split_part(type, \':\', 1) ORDER BY type ASC');
            
            if (!$results) {
                throw new Exception();
            }
            
            while ($result = pg_fetch_assoc($results)) {
                $matched = (integer) $result['matched'];
            
                if ($result['type'] === 'collection') {
                    $facets['count'] =  $matched;
                } else {
                    if ($matched >= $minMatch) {
                        // Catalog
                        if ($result['type'] === 'catalog' || $result['type'] === 'hashtag') {
                            $facets[$result['type'] . 's'] = $matched;
                        } else {
                            $addToFacets = true;
                            foreach ($this->classifications as $key => $value) {
                                for ($i = count($value); $i--;) {
                                    if ($result['type'] === $value[$i]) {
                                        $facets['facets'][$key][$result['type']] = $matched;
                                        $addToFacets = false;
                                        break;
                                    }
                                }
                            }
                            if ($addToFacets) {
                                $result['type'] === 'landcover' ? $facets['facets']['landcover'] = $matched : $facets['facets'][$result['type']] = $matched;
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            return $facets;
        }
        
        return $facets;
    }

    /**
     * Return catalog childs key with counts
     * 
     * @return array
     */
    private function getCatalogChilds()
    {
        $childs = array();

        try {

            $results = $this->context->dbDriver->query('SELECT id, value, pid, isleaf, sum(counter) as matched FROM resto.facet WHERE type=\'catalog\' AND pid=\'root\' GROUP BY id, value, pid, isleaf ORDER BY value ASC');
            
            if (!$results) {
                throw new Exception();
            }
            
            while ($result = pg_fetch_assoc($results)) {
                $matched = (integer) $result['matched'];
                $childs[$result['id']] = array(
                    'count' => $matched,
                    'title' => $result['value']
                );
            }
        } catch (Exception $e) {
            //
        }

        return $childs;
    }

}