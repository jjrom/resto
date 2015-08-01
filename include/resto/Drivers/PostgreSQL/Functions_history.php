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
 * RESTo PostgreSQL history functions
 */
class Functions_history {

    private $dbDriver = null;

    /**
     * Constructor
     *
     * @param RestoDatabaseDriver $dbDriver
     */
    public function __construct($dbDriver) {
        $this->dbDriver = $dbDriver;
    }

    /**
     * Get history
     * 
     * @param array $options
     *      _order
     *      _ascordesc
     *      _offset
     *      _limit
     *      _email
     *      _service
     *      _method
     *      _collection
     *      _maxdate
     *      _mindate
     * @return array
     * @throws Exception
     */
    public function getHistory($options) {
        
        $orderBy = isset($options['_order']) ? $options['_order'] : 'querytime';
        $ascOrDesc = isset($options['_ascordesc']) ? $options['_ascordesc'] : 'DESC';
        $startIndex = isset($options['_offset']) ? $options['_offset'] : 0;
        $numberOfResults = isset($options['_limit']) ? $options['_limit'] : 50;
        $where = array();
        if (isset($options['_email'])) {
            $where[] = 'email=\'' . pg_escape_string($options['_email']) . '\'';
        }
        if (isset($options['_service'])) {
            $where[] = 'service=\'' . pg_escape_string($options['_service']) . '\'';
        }
        if (isset($options['_method'])) {
            $where[] = 'method=\'' . pg_escape_string($options['_method']) . '\'';
        }
        if (isset($options['_collection'])) {
            $where[] = 'collection=\'' . pg_escape_string($options['_collection']) . '\'';
        }
        if (isset($options['_maxdate'])) {
            $where[] = 'querytime <=\'' . pg_escape_string($options['_maxdate']) . '\'';
        }
        if (isset($options['_mindate'])) {
            $where[] = 'querytime >=\'' . pg_escape_string($options['_mindate']) . '\'';
        }
        
        $query = 'SELECT gid, email, method, service, collection, resourceid, query, querytime, url, ip FROM usermanagement.history' . (count($where) > 0 ? ' WHERE ' . join(' AND ', $where) : ' ') . ' ORDER BY ' . pg_escape_string($orderBy) . ' ' . pg_escape_string($ascOrDesc) . ' LIMIT ' . $numberOfResults . ' OFFSET ' . $startIndex;
        
        return $this->dbDriver->fetch($this->dbDriver->query($query));
        
    }

}