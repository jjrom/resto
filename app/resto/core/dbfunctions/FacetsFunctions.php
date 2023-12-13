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
 * RESTo PostgreSQL facets functions
 */
class FacetsFunctions
{
    /*
     * Relative an absolute coverages minimum percentage value
     */
    private $minRelCov = 20;
    private $minAbsCov = 20;
    
    private $dbDriver = null;

    /**
     * Constructor
     *
     * @param RestoDatabaseDriver $dbDriver
     * @throws Exception
     */
    public function __construct($dbDriver)
    {
        $this->dbDriver = $dbDriver;
    }

    /**
     * Format facet for output
     *
     * @param array $rawFacet
     */
    public static function format($rawFacet)
    {
        return array(
            'id' => $rawFacet['id'],
            'collection' => $rawFacet['collection'] ?? '*',
            'value' => $rawFacet['value'],
            'parentId' => $rawFacet['pid'],
            'created' => $rawFacet['created'],
            'creator' => $rawFacet['creator'] ?? null,
            'count' => (integer) $rawFacet['counter'],
            'description' => $rawFacet['description'] ?? null
        );
    }

    /**
     * Get facet from $id
     *
     * @param string $facetId
     */
    public function getFacet($facetId)
    {
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT id, collection, value, type, pid, to_iso8601(created) as created, creator, description  FROM ' . $this->dbDriver->targetSchema . '.facet WHERE normalize(id)=normalize($1) LIMIT 1', array(
            $facetId
        )));
        if (isset($results[0])) {
            return FacetsFunctions::format($results[0]);
        }
        
        return null;
    }

    /**
     * Store facet within database (i.e. add 1 to the counter of facet if exist)
     *
     * !! THIS FUNCTION IS THREAD SAFE !!
     *
     * Input facet structure :
     *      array(
     *          array(
     *              'name' =>
     *              'type' =>
     *              'id' =>
     *              'parentId' =>
     *          ),
     *          ...
     *      )
     *
     *  Or
     *      array(
     *          'id',
     *
     *      )
     *
     * @param array $facets
     * @param string $collectionId
     */
    public function storeFacets($facets, $collectionId = '*')
    {
        // Empty facets - do nothing
        if (!isset($facets) || count($facets) === 0) {
            return;
        }

        foreach (array_values($facets) as $facetElement) {
            /*
             * Support for direct hashtag (i.e. not an array)
             */
            if (!is_array($facetElement)) {
                $facetElement = array(
                    'id' => $facetElement,
                    'value' => $facetElement,
                    'type' => 'hashtag',
                    'isLeaf' => true
                );
            }

            /*
             * Thread safe ingestion using upsert - guarantees that counter is correctly incremented during concurrent transactions
             *
             * [IMPORTANT] UPSERT with check on parentId only if $facetElement['parentId'] is set
             */
            $insert = 'INSERT INTO ' . $this->dbDriver->targetSchema . '.facet (id, collection, value, type, pid, creator, description, created, counter, isleaf) SELECT $1,$2,$3,$4,$5,$6,$7,now(),$8,$9';
            $upsert = 'UPDATE ' . $this->dbDriver->targetSchema . '.facet SET counter=' .(isset($facetElement['counter']) ? 'counter' : 'counter+1') . ' WHERE normalize(id)=normalize($1) AND normalize(collection)=normalize($2)' . (isset($facetElement['parentId']) ? ' AND normalize(pid)=normalize($5)' : '');
            $this->dbDriver->pQuery('WITH upsert AS (' . $upsert . ' RETURNING *) ' . $insert . ' WHERE NOT EXISTS (SELECT * FROM upsert)', array(
                $facetElement['id'],
                $facetElement['collection'] ?? $collectionId,
                $facetElement['value'],
                $facetElement['type'],
                $facetElement['parentId'] ?? 'root',
                $facetElement['creator'] ?? null,
                $facetElement['description'] ?? null,
                // If no input counter is specified - set to 1
                isset($facetElement['counter']) ? $facetElement['counter'] : 1,
                isset($facetElement['isLeaf']) && $facetElement['isLeaf'] ? 1 : 0
            ), 500, 'Cannot insert facet ' . $facetElement['id']);
        }
    }

    /**
     * Remove facet for collection i.e. decrease by one counter
     *
     * @param string $facetId
     * @param string $collectionId
     */
    public function removeFacet($facetId, $collectionId)
    {
        $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.facet SET counter = GREATEST(0, counter - 1) WHERE normalize(id)=normalize($1) AND (normalize(collection)=normalize($2) OR normalize(collection)=\'*\')', array($facetId, $collectionId), 500, 'Cannot delete facet for ' . $collectionId);
    }

    /**
     * Return facets elements from a type for a given collection
     *
     * Returned array structure if collectionId is set
     *
     *      array(
     *          'type#' => array(
     *              'value1' => count1,
     *              'value2' => count2,
     *              'parent' => array(
     *                  'value3' => count3,
     *                  ...
     *              )
     *              ...
     *          ),
     *          'type2' => array(
     *              ...
     *          ),
     *          ...
     *      )
     *
     * Or an array of array indexed by collection id if $collectionId is null
     *
     * @param RestoCollection $collection
     * @param array $facetFields
     *
     * @return array
     */
    public function getStatistics($collection, $facetFields)
    {
        return $this->pivotsToSTACSummaries($this->getFacetsPivots($collection, $facetFields));
    }

    /**
     * Get facets from keywords
     *
     * @param array $keywords
     * @param array $facetCategories
     * @param string $collectionId
     * @param array $options
     */
    public function getFacetsFromKeywords($keywords, $facetCategories, $collectionId, $options = array())
    {
        /*
         * One facet per keyword
         */
        $facets = array();
        for ($i = count($keywords); $i--;) {
            $facetCategory = $this->getFacetCategory($facetCategories, $keywords[$i]['type']);
            if (isset($facetCategory)) {
                /*
                 * Compute  facets if relative coverage is greater than 20 %
                 * and absolute coverage is greater than 20%
                 */
                if (isset($keywords[$i]['value']) && $keywords[$i]['value'] < ($options['minRelCov'] ?? $this->minRelCov)) {
                    if (!isset($keywords[$i]['gcover']) || $keywords[$i]['gcover'] < ($options['minAbsCov'] ??  $this->minAbsCov)) {
                        continue;
                    }
                }
                
                $facets[] = array(
                    'id' => $keywords[$i]['id'],
                    'parentId' => $keywords[$i]['parentId'] ?? 'root',
                    'value' => $keywords[$i]['name'] ?? null,
                    'type' => $keywords[$i]['type'],
                    // [IMPORTANT] catalog facet are always attached to all collections
                    'collection' => $keywords[$i]['type'] === 'catalog' ? '*' : $collectionId,
                    'isLeaf' => $facetCategory['isLeaf']
                );
            }
        }

        return $facets;
    }

    /**
     * Return an array of hashtags from an array of facets
     */
    public function getHashtagsFromFacets($facets)
    {
        $hashtags = array();
        for ($i = count($facets); $i--;) {
            $hashtags[] = is_array($facets[$i]) ? $facets[$i]['id'] : $facets[$i];
        }
        return $hashtags;
    }

    /**
     * Remove feature facets from database
     *
     * @param array $hashtags
     * @param string $collectionId
     */
    public function removeFacetsFromHashtags($hashtags, $collectionId)
    {
        for ($i = count($hashtags); $i--;) {
            $this->removeFacet($hashtags[$i], $collectionId);
        }
    }

    /**
     * Return facet category
     *
     * @param array $facetCategories
     * @param string $type
     */
    private function getFacetCategory($facetCategories, $type)
    {
        if (! isset($type)) {
            return null;
        }
        for ($i = count($facetCategories); $i--;) {
            $categoryLength = count($facetCategories[$i]);
            for ($j = $categoryLength; $j--;) {
                if ($facetCategories[$i][$j] === $type) {
                    return array(
                        'category' => $facetCategories[$i],
                        'isLeaf' => $j == $categoryLength - 1
                    );
                }
            }
        }
        
        /*
         * Otherwise return $type as a new facet category
         */
        return array(
            'category' => $type,
            'isLeaf' => true
        );
    }

    /**
     * Return facets pivots
     *
     * @param RestoCollection $collection
     * @param array $fields
     * @return array
     */
    private function getFacetsPivots($collection, $fields)
    {
        $collectionId = isset($collection) ? $collection->id : null;
        $model = isset($collection) ? $collection->model : new DefaultModel();

        /*
         * [Hack] Facet with one of these type are unprefixed
         */
        $unprefixed = array(
            'hashtag',
            // The following types are from resto-addon-sosa
            'observedProperty',
            'foi',
            'sample',
            'sensor'
        );

        $pivots = array();
        
        /*
         * Build array
         */
        $where = array(
            'counter > 0'
        );

        if (isset($collectionId)) {
            $where[] = '(normalize(collection)=normalize(\'' . pg_escape_string($this->dbDriver->getConnection(), $collectionId) . '\') OR normalize(collection)=\'*\')';
        }
        if (isset($fields)) {
            $where[] = 'type IN(\'' . join('\',\'', $fields) . '\')';
        }

        /*
         * Facets for one collection
         */
        $results = $this->dbDriver->query('SELECT id,collection,value,type,pid,counter,to_iso8601(created) as created,creator FROM ' . $this->dbDriver->targetSchema . '.facet' . (count($where) > 0 ? ' WHERE ' . join(' AND ', $where): '') . ' ORDER BY type ASC, value DESC');
        
        while ($result = pg_fetch_assoc($results)) {
            $typeLen = strlen($result['type']);

            // Landcover special case
            if (strpos($result['type'], 'landcover:') === 0) {
                $type = 'landcover';
                $typeLen = strlen($type);
            }
            // [STAC] Change the type name if needed (e.g. "instrument" => "instruments")
            else {
                $type = isset($model->stacMapping[$result['type']]) ? $model->stacMapping[$result['type']]['key'] : $result['type'];
            }
            
            if (!isset($pivots[$type])) {
                $pivots[$type] = array();
            }

            $create = true;
            $const = in_array($type, $unprefixed) ? $result['id'] : substr($result['id'], $typeLen + 1);
            for ($i = count($pivots[$type]); $i--;) {
                if (isset($pivots[$type][$i]['const'])) {
                    if ($pivots[$type][$i]['const'] === $const) {
                        $pivots[$type][$i]['count'] += (integer) $result['counter'];
                        $create = false;
                        break;
                    }
                }
            }
            
            if ($create) {
                $newPivot = array(
                    'const' => $const,
                    'count' => (integer) $result['counter']
                );
                if ($result['pid'] !== 'root') {
                    $newPivot['parentId'] = $result['pid'];
                }
                if ($result['value'] !== $newPivot['const']) {
                    $newPivot['title'] = $result['value'];
                }
                $pivots[$type][] = $newPivot;
            }
        }

        return $pivots;
    }
    
    /**
     * Return STAC summaries
     *
     * @param array $fields
     * @return array
     */
    private function pivotsToSTACSummaries($pivots)
    {
        $summaries = array();

        foreach (array_keys($pivots) as $key) {
            if (count($pivots[$key]) === 1) {
                $summaries[$key] = array_merge($pivots[$key][0], array('type' => 'string'));
            } else {
                $summaries[$key] = array(
                    'type' => 'string',
                    'oneOf' => $pivots[$key]
                );
            }
        }

        return $summaries;
    }
}
