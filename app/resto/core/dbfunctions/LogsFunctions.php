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
 * Logs functions
 */
class LogsFunctions
{
    private $dbDriver = null;

    /**
     * Constructor
     *
     * @param RestoDatabaseDriver $dbDriver
     */
    public function __construct($dbDriver)
    {
        $this->dbDriver = $dbDriver;
    }

    /**
     * Get logs
     * 
     * @param array $params
     * 
     * @return array
     * @throws Exception
     */
    public function getLogs($params)
    {

        $where = array();
        
        // Paginate
        if (isset($params['lt'])) {
            $where[] = 'gid < ' . pg_escape_string($params['lt']);
        }

        if (isset($params['userid'])) {
            $where[] = 'userid=' . pg_escape_string($params['userid']);
        }
        
        if (isset($params['querytime'])) {
            $where[] = QueryUtil::intervalToQuery($params['querytime'], 'querytime');
        }

        $results = $this->dbDriver->query('SELECT gid, userid, method, to_iso8601(querytime) as querytime, path, query, ip FROM resto.log' . (count($where) > 0 ? ' WHERE ' . join(' AND ', $where) : ' ') . ' ORDER BY gid DESC LIMIT 50');
        
        $logs = array();
        while ($row = pg_fetch_assoc($results))
        {
            $log = array(
                'gid' => (integer) $row['gid'],
                'method' => $row['method'],
                'path' => $row['path'],
                'querytime' => $row['querytime']
            );
            if (!empty($row['query'])) {
                $log['query'] = $row['query'];
            }
            if ($params['fullDisplay']) {
                $log['userid'] = $row['userid'];
                $log['ip'] = $row['ip'];
            }
            $logs[] = $log;
        }
    
        return array(
            'id' => $params['userid'],
            'logs' => $logs
        );
    
    }

}
