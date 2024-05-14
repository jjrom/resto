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
     * These are types created by iTag 
     * Not returned from getSummaries unless explicitely requested
     * to avoid large summaries array
     */
    const TOPONYM_TYPES = array(
        'bay',
        'channel',
        'continent',
        'country',
        'fjord',
        'gulf',
        'inlet',
        'lagoon',
        'ocean',
        'region',
        'river',
        'sea',
        'sound',
        'state',
        'strait'    
    );
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
            'owner' => $rawFacet['owner'] ?? null,
            'count' => (integer) $rawFacet['counter'],
            'description' => $rawFacet['description'] ?? null,
            'isLeaf' => $rawFacet['isleaf']
        );
    }

    /**
     * Get facet from id and/or pid and/or collection)
     *
     * @param array params
     */
    public function getFacets($params = array())
    {

        $facets = array();

        $values = array();
        $where = array();
        $count = 1;
        foreach (array_keys($params) as $key ) {
            if (in_array($key, array('id', 'pid', 'collection'))) {
                $where[] = 'public.normalize(' . $key . ')=public.normalize($' . $count . ')';
                $values[] = $params[$key];
                $count++;
            }
        }

        $results = $this->dbDriver->pQuery('SELECT id, collection, value, type, pid, to_iso8601(created) as created, owner, description, counter, isleaf  FROM ' . $this->dbDriver->targetSchema . '.facet' . ( empty($where) ? '' : ' WHERE ' . join(' AND ', $where)), $values);
        while ($result = pg_fetch_assoc($results)) {
            $facets[] = FacetsFunctions::format($result);
        }

        return $facets;
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
     * @param string $userid
     * @param string $collectionId
     */
    public function storeFacets($facets, $userid, $collectionId = '*')
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
            $insert = 'INSERT INTO ' . $this->dbDriver->targetSchema . '.facet (id, collection, value, type, pid, owner, description, created, counter, isleaf) SELECT public.normalize($1),$2,$3,$4,public.normalize($5),$6,$7,now(),$8,$9';
            $upsert = 'UPDATE ' . $this->dbDriver->targetSchema . '.facet SET counter=' .(isset($facetElement['counter']) ? 'counter' : 'counter+1') . ' WHERE public.normalize(id)=public.normalize($1) AND public.normalize(collection)=public.normalize($2)' . (isset($facetElement['parentId']) ? ' AND public.normalize(pid)=public.normalize($5)' : '');
            $this->dbDriver->pQuery('WITH upsert AS (' . $upsert . ' RETURNING *) ' . $insert . ' WHERE NOT EXISTS (SELECT * FROM upsert)', array(
                $facetElement['id'],
                $facetElement['collection'] ?? $collectionId,
                $facetElement['value'],
                $facetElement['type'],
                $facetElement['parentId'] ?? 'root',
                $facetElement['owner'] ?? $userid,
                $facetElement['description'] ?? null,
                // If no input counter is specified - set to 1
                isset($facetElement['counter']) ? $facetElement['counter'] : 1,
                isset($facetElement['isLeaf']) && $facetElement['isLeaf'] ? 1 : 0
            ), 500, 'Cannot insert facet ' . $facetElement['id']);
        }
    }

    /**
     * Update facet 
     * 
     * @param array $facet
     * @return integer // number of facets updated
     */
    public function updateFacet($facet)
    {
        
        $values = array(
            $facet['id']
        );
        
        $canBeUpdated = array(
            //'collection',
            'value',
            //'type',
            //'parentId',
            'owner',
            'description',
            //'counter',
            //'isLeaf'
        );

        $set = array();
        $count = 2;
        foreach (array_keys($facet) as $key ) {
            if (in_array($key, $canBeUpdated)) {
                $set[] = $key . '=$' . $count;
                $values[] = $facet[$key];
                $count++;
            }
        }

        if ( empty($set) ) {
            return array(
                'facetsUpdated' => 0
            );
        }

        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.facet SET ' . join(',', $set) . ' WHERE public.normalize(id)=public.normalize($1) RETURNING id', $values, 500, 'Cannot update facet ' . $facet['id']));

        return array(
            'facetsUpdated' => count($results)
        );

    }

    /**
     * Remove facet from id - can only works if facet has no child
     *
     * @param string $facetId
     */
    public function removeFacet($facetId)
    {

        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('DELETE FROM ' . $this->dbDriver->targetSchema . '.facet WHERE public.normalize(id)=public.normalize($1) RETURNING id', array($facetId), 500, 'Cannot delete facet' . $facetId));
        $facetsDeleted = count($results);

        // Next remove the facet entry from all features
        $query = join(' ', array(
                'UPDATE ' . $this->dbDriver->targetSchema . '.feature SET',
                'hashtags=ARRAY_REMOVE(hashtags, $1),normalized_hashtags=ARRAY_REMOVE(normalized_hashtags,public.normalize($1)),',
                'keywords=(SELECT json_agg(e) FROM json_array_elements(keywords) AS e WHERE e->>\'id\' <> $1)',
                'WHERE normalized_hashtags @> public.normalize_array(ARRAY[$1]) RETURNING id'
            )
        );
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery($query, array($facetId), 500, 'Cannot update features' . $facetId));
        
        return array(
            'facetDeleted' => $facetsDeleted,
            'featuresUpdated' => count($results)
        );

    }

    /**
     * Return STAC Summaries from facets elements from a type for a given collection
     *
     * Returned array of array indexed by collection id
     *
     *      array(
     *          'collection1' => array(
     *                 'type#' => array(
     *                     'value1' => count1,
     *                     'value2' => count2,
     *                     'parent' => array(
     *                         'value3' => count3,
     *                         ...
     *                     )
     *                     ...
     *                 ),
     *                 'type2' => array(
     *                     ...
     *                 ),
     *                 ...
     *          ),
     *          'collection2' => array(
     *                 'type#' => array(
     *                     'value1' => count1,
     *                     'value2' => count2,
     *                     'parent' => array(
     *                         'value3' => count3,
     *                         ...
     *                     )
     *                     ...
     *                 ),
     *                 'type2' => array(
     *                     ...
     *                 ),
     *                 ...
     *          )
     *      )
     * 
     * @param array $types
     * @param string $collectionId
     * 
     * @return array
     */
    public function getSummaries($types, $collectionId)
    {
        
        $summaries = array();

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

        if ( isset($collectionId) ) {
            $where[] = 'public.normalize(collection)=public.normalize(\'' . pg_escape_string($this->dbDriver->getConnection(), $collectionId) . '\')';
        }

        if ( !empty($types) ) {
            $where[] = 'type IN(\'' . join('\',\'', $types) . '\')';
        }
        else {
            $where[] = 'type NOT IN (\'' . join('\',\'', FacetsFunctions::TOPONYM_TYPES) . '\')';
        }

        /*
         * Retrieve facets stored by collectionId
         */
        $results = $this->dbDriver->query('SELECT id,collection,value,type,pid,counter,to_iso8601(created) as created,owner FROM ' . $this->dbDriver->targetSchema . '.facet' . (count($where) > 0 ? ' WHERE ' . join(' AND ', $where): ''));
        
        while ($result = pg_fetch_assoc($results)) {

            if ( !isset($pivots[$result['collection']]) ) {
                $pivots[$result['collection']] = array();
            }
            $typeLen = strlen($result['type']);

            // Landcover special case
            if (strpos($result['type'], 'landcover:') === 0) {
                $type = 'landcover';
                $typeLen = strlen($type);
            }
            else {
                $type = $result['type'];
            }
            
            if (!isset($pivots[$result['collection']][$type])) {
                $pivots[$result['collection']][$type] = array();
            }

            $create = true;
            $const = in_array($type, $unprefixed) ? $result['id'] : substr($result['id'], $typeLen + 1);
            for ($i = count($pivots[$result['collection']][$type]); $i--;) {
                if (isset($pivots[$result['collection']][$type][$i]['const'])) {
                    if ($pivots[$result['collection']][$type][$i]['const'] === $const) {
                        $pivots[$result['collection']][$type][$i]['count'] += (integer) $result['counter'];
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
                $pivots[$result['collection']][$type][] = $newPivot;
            }
        }

        foreach (array_keys($pivots) as $collectionId) {
            if ( !isset($summaries[$collectionId]) ) {
                $summaries[$collectionId] = array();
            }
            foreach (array_keys($pivots[$collectionId]) as $key) {
                if (count($pivots[$collectionId][$key]) === 1) {
                    $summaries[$collectionId][$key] = array_merge($pivots[$collectionId][$key][0], array('type' => 'string'));
                } else {
                    $summaries[$collectionId][$key] = array(
                        'type' => 'string',
                        'oneOf' => $pivots[$collectionId][$key]
                    );
                }
            }
        }

        return $summaries;
        
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
            $this->dbDriver->pQuery('UPDATE ' . $this->dbDriver->targetSchema . '.facet SET counter = GREATEST(0, counter - 1) WHERE public.normalize(id)=public.normalize($1) AND (public.normalize(collection)=public.normalize($2) OR public.normalize(collection)=\'*\')', array($hashtags[$i], $collectionId), 500, 'Cannot delete facet for ' . $collectionId);
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

}
