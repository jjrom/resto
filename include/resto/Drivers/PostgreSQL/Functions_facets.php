<?php
/*
 * Copyright 2014 Jérôme Gasperi
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
class Functions_facets {
    
    private $dbDriver = null;
    
    /**
     * Constructor
     * 
     * @param RestoDatabaseDriver $dbDriver
     * @throws Exception
     */
    public function __construct($dbDriver) {
        $this->dbDriver = $dbDriver;
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
     *              'hash' => 
     *              'parentHash' => 
     *          ),
     *          ...
     *      )
     * 
     * @param array $facets
     * @param type $collectionName
     */
    public function storeFacets($facets, $collectionName) {
        
        foreach (array_values($facets) as $facetElement) {
            
            $arr = array(
                '\'' . pg_escape_string($facetElement['hash']) . '\'',
                '\'' . pg_escape_string($facetElement['name']) . '\'',
                '\'' . pg_escape_string($facetElement['type']) . '\'',
                isset($facetElement['parentHash']) ? '\'' . pg_escape_string($facetElement['parentHash']) . '\'' : 'NULL',
                isset($collectionName) ? '\'' . pg_escape_string($collectionName) . '\'' : 'NULL',
                '1'
            );
            
            /*
             * Thread safe ingestion
             */
            $lock = 'LOCK TABLE resto.facets IN SHARE ROW EXCLUSIVE MODE;';
            $insert = 'INSERT INTO resto.facets (uid, value, type, pid, collection, counter) SELECT ' . join(',', $arr);
            $upsert = 'UPDATE resto.facets SET counter = counter + 1 WHERE uid = \'' . pg_escape_string($facetElement['hash']) . '\' AND collection = \'' . pg_escape_string($collectionName) . '\'';
            $this->dbDriver->query($lock . 'WITH upsert AS (' . $upsert . ' RETURNING *) ' . $insert . ' WHERE NOT EXISTS (SELECT * FROM upsert)', 500, 'Cannot insert facet for ' . $collectionName);
            
        }
    }
    
    /**
     * Remove facet for collection i.e. decrease by one counter
     * 
     * @param string $hash
     * @param string $collectionName
     */
    public function removeFacet($hash, $collectionName) {
        if ($this->facetExists($hash, $collectionName)) {
            $this->dbDriver->query('UPDATE resto.facets SET counter = counter - 1 WHERE uid=\'' . pg_escape_string($hash) . '\' AND collection=\'' . pg_escape_string($collectionName) . '\'', 500, 'Cannot delete facet for ' . $collectionName);
            return true;
        }
        return false;
    }
    
    /**
     * Return facets elements from a type for a given collection
     * 
     * Returned array structure if collectionName is set
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
     * Or an array of array indexed by collection name if $collectionName is null
     *  
     * @param string $collectionName
     * @param array $facetFields
     * @param string $hash
     * 
     * @return array
     */
    public function getStatistics($collectionName = null, $facetFields = null) {
        
        $cached = $this->dbDriver->cache->retrieve(array('getStatistics', $collectionName, $facetFields));
        
        if (isset($cached)) {
            return $cached;
        }
        
        /*
         * Retrieve pivot for each input facet fields
         */
        if (!isset($facetFields)) {
            $facetFields = array();
            foreach (array_values($this->dbDriver->facetUtil->facetCategories) as $facetCategory) {
                $facetFields[] = $facetCategory[0];
            }
        }
        $statistics = $this->getCounts($this->getFacetsPivots($collectionName, $facetFields, null), $collectionName);
        
        $this->dbDriver->cache->store(array('getStatistics', $collectionName, $facetFields), $statistics);
        
        return $statistics;
    }
    
    /**
     * Return facet pivots (SOLR4 like)
     * 
     * @param string $collectionName
     * @param array $fields
     * @param string $parentHash : parent hash
     * @return array
     */
    private function getFacetsPivots($collectionName, $fields, $parentHash) {
        
        $pivots = array();
        $cached = $this->dbDriver->cache->retrieve(array('getFacetsPivots', $fields, $parentHash));
        if (isset($cached)) {
            return $cached;
        }
       
        /*
         * Facets for one collection
         */
        $query = 'SELECT * FROM resto.facets WHERE counter > 0 AND ';
        if (isset($collectionName)) {
            $results = $this->dbDriver->query($query . 'collection=\'' . pg_escape_string($collectionName) . '\' AND type IN(\'' . join('\',\'', $fields) . '\')' . (isset($parentHash) ? ' AND pid=\'' . pg_escape_string($parentHash) . '\'' : '') . ' ORDER BY type ASC, value DESC');
        }
        /*
         * Facets for all collections
         */
        else {
            $results = $this->dbDriver->query($query . 'type IN(\'' . join('\',\'', $fields) . '\')' . (isset($parentHash) ? ' AND pid=\'' . pg_escape_string($parentHash) . '\'' : '') . ' ORDER BY type ASC, value DESC');
        }
        
        while ($result = pg_fetch_assoc($results)) {
            if (!isset($pivots[$result['type']])) {
                $pivots[$result['type']] = array();
            }
            $create = true;
            if (!isset($collectionName)) {
                for ($i = count($pivots[$result['type']]); $i--;) {
                    if ($pivots[$result['type']][$i]['value'] === $result['value']) {
                        $pivots[$result['type']][$i]['count'] += (integer) $result['counter'];
                        $create = false;
                        break;
                    }
                }
            }
            if ($create) {
                $pivots[$result['type']][] = array(
                    'field' => $result['type'],
                    'value' => $result['value'],
                    'count' => (integer) $result['counter'],
                    'hash' => $result['uid'],
                    'parentHash' => isset($parentHash) ? $parentHash : null
                );
            }
        }
        $this->dbDriver->cache->store(array('getFacetsPivots', $fields, $parentHash), $pivots);
       
        return $pivots;
    }

    /**
     * Check if facet exists
     * 
     * @param string $hash - facet hash
     * @param string $collectionName
     * @return boolean
     * @throws Exception
     */
    private function facetExists($hash, $collectionName) {
        $query = 'SELECT 1 FROM resto.facets WHERE uid=\'' . pg_escape_string($hash) . '\' AND collection = \'' . pg_escape_string($collectionName) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return !empty($results);
    }
    
    /**
     * Return counts for all pivots elements
     * 
     * @param array $pivots
     * @param string $collectionName
     * @return type
     */
    private function getCounts($pivots, $collectionName) {
        $facets = array();
        foreach(array_values($pivots) as $pivot) {
            if (isset($pivot) && count($pivot) > 0) {
                for ($j = count($pivot); $j--;) {
                    if (isset($facets[$pivot[$j]['field']][$pivot[$j]['value']])) {
                        $facets[$pivot[$j]['field']][$pivot[$j]['value']] += (integer) $pivot[$j]['count'];
                    }
                    else {
                        $facets[$pivot[$j]['field']][$pivot[$j]['value']] = (integer) $pivot[$j]['count'];
                    }
                }
            }
        }
        
        /*
         * Empty result
         */
        if (!isset($facets['collection'])) {
            return array(
                'count' => 0,
                'facets' => array()
            );
        }
        
        /*
         * Total count
         */
        $count = 0;
        foreach (array_values($facets['collection']) as $collectionCount) {
            $count += $collectionCount;
        }
        
        if (isset($collectionName)) {
            unset($facets['collection']);
        }
        
        return array(
            'count' => $collectionCount,
            'facets' => $facets
        );
    }
}
