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

        $facets = $this->getFacets($minMatch);
        foreach (array('catalogs', 'facets', 'hashtags') as $key) {
            if (isset($facets[$key])) {
                $links[] = array(
                    'rel' => 'child',
                    'title' => ucfirst($key),
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl'] . '/catalogs/' . rawurlencode($key)
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
     * Return facets list
     *
     * @param integer $minMatch
     * @return array
     */
    public function getFacets($minMatch)
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
                            $addToOther = true;
                            foreach ($this->classifications as $key => $value) {
                                for ($i = count($value); $i--;) {
                                    if ($result['type'] === $value[$i]) {
                                        $facets['facets'][$key][$result['type']] = $matched;
                                        $addToOther = false;
                                        break;
                                    }
                                }
                            }
                            if ($addToOther) {
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
}
