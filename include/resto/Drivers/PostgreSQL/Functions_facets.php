<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2013 Jérôme Gasperi <https://github.com/jjrom>
 * 
 * jerome[dot]gasperi[at]gmail[dot]com
 * 
 * 
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
 * 
 */

/**
 * RESTo PostgreSQL facets functions
 */
class Functions_facets {
    
    private $dbDriver = null;
    private $dbh = null;
    
    /**
     * Constructor
     * 
     * @param array $config
     * @param RestoCache $cache
     * @throws Exception
     */
    public function __construct($dbDriver) {
        $this->dbDriver = $dbDriver;
        $this->dbh = $dbDriver->dbh();
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
        $statistics = array();
        if (isset($facetFields) && count($facetFields) > 0) {
            $statistics = $this->getCounts($this->getFacetsPivots($collectionName, $facetFields, null));
        }
        /*
         * or for all master facet fields
         */
        else {
            $fields = array();
            foreach (array_values($this->dbDriver->facetUtil->facetCategories) as $facetCategory) {
                $fields[] = $facetCategory[0];
            }
            $statistics = $this->getCounts($this->getFacetsPivots($collectionName, $fields, null));
        }
        
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
     * Store facet within database (i.e. add 1 to the counter of facet if exist)
     * 
     * !! THIS FUNCTION IS THREAD SAFE !!
     * 
     * Input facet structure :
     *      array(
     *          array(
     *              'id' => 'instrument:PHR',
     *              'hash' => '...'
     *              'parentId' => 'platform:PHR',
     *              'parentHash' => '...'
     *          ),
     *          array(
     *              'id' => 'year:2011',
     *              'hash' => 'xxxxxx'
     *          ),
     *          ...
     *      )
     * 
     * @param array $facets
     * @param type $collectionName
     */
    public function storeFacets($facets, $collectionName) {
        
        foreach (array_values($facets) as $facetElement) {
                
            list($ptype, $pvalue) = isset($facetElement['parentId']) ? explode(':', $facetElement['parentId'],2) : array(null, null);
            list($type, $value) = explode(':', $facetElement['id'], 2);

            /*
             * Thread safe ingestion
             */
            $arr = array(
                '\'' . pg_escape_string($facetElement['hash']) . '\'',
                '\'' . pg_escape_string($value) . '\'',
                '\'' . pg_escape_string($type) . '\'',
                isset($facetElement['parentHash']) ? '\'' . pg_escape_string($facetElement['parentHash']) . '\'' : 'NULL',
                isset($pvalue) ? '\'' . pg_escape_string($pvalue) . '\'' : 'NULL',
                isset($ptype) ? '\'' . pg_escape_string($ptype) . '\'' : 'NULL',
                isset($collectionName) ? '\'' . pg_escape_string($collectionName) . '\'' : 'NULL',
                '1'
            );
            $lock = 'LOCK TABLE resto.facets IN SHARE ROW EXCLUSIVE MODE;';
            $insert = 'INSERT INTO resto.facets (uid, value, type, pid, pvalue, ptype, collection, counter) SELECT ' . join(',', $arr);
            $upsert = 'UPDATE resto.facets SET counter = counter + 1 WHERE uid = \'' . pg_escape_string($facetElement['hash']) . '\' AND collection = \'' . pg_escape_string($collectionName) . '\'';
            $this->dbDriver->query($lock . 'WITH upsert AS (' . $upsert . ' RETURNING *) ' . $insert . ' WHERE NOT EXISTS (SELECT * FROM upsert)', 500, 'Cannot insert facet for ' . $collectionName);
            
        }
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
        return !empty($this->dbDriver->fetch($this->dbDriver->query($query)));
    }
    
    /**
     * Return counts for all pivots elements
     * 
     * @param array $pivots
     * @return type
     */
    private function getCounts($pivots) {
        $statistics = array();
        foreach(array_values($pivots) as $pivot) {
            if (isset($pivot) && count($pivot) > 0) {
                for ($j = count($pivot); $j--;) {
                    if (isset($statistics[$pivot[$j]['field']][$pivot[$j]['value']])) {
                        $statistics[$pivot[$j]['field']][$pivot[$j]['value']] += (integer) $pivot[$j]['count'];
                    }
                    else {
                        $statistics[$pivot[$j]['field']][$pivot[$j]['value']] = (integer) $pivot[$j]['count'];
                    }
                }
            }
        }
        return $statistics;
    }
}
