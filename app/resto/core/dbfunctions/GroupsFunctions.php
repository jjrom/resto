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
 * RESTo PostgreSQL rights functions
 */
class GroupsFunctions
{
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
     * List all groups
     *
     * @return array
     * @throws Exception
     */
    public function getGroups($params = array())
    {
        $where = array();
        
        // Return group by id
        if (isset($params['id'])) {
            $where[] = 'id=' . $this->dbDriver->escape_string( $params['id']);
        }

        // Return all groups in
        if (isset($params['in'])) {
            $where[] = 'id IN (' . $this->dbDriver->escape_string( join(',', $params['in'])) . ')';
        }

        // Search by name
        if (isset($params['q'])) {
            $where[] = 'public.normalize(name) LIKE public.normalize(\'%' . $this->dbDriver->escape_string( $params['q']) . '%\')';
        }

        // Return groups by userid
        if (isset($params['userid'])) {
            $where[] = 'id IN (SELECT DISTINCT groupid FROM ' . $this->dbDriver->commonSchema . '.group_member WHERE userid=' . $this->dbDriver->escape_string( $params['userid']) . ')';
        }

        // Return groups by owner
        if (isset($params['owner'])) {
            $where[] = 'owner=' . $this->dbDriver->escape_string( $params['owner']);
        }
        
        return $this->formatGroups($this->dbDriver->fetch($this->dbDriver->query('SELECT id, name, description, owner, private, to_iso8601(created) as created FROM ' . $this->dbDriver->commonSchema . '.group' . (count($where) > 0 ? ' WHERE ' . join(' AND ', $where) : '') . ' ORDER BY id DESC')), $params['id'] ?? null);
    }

    /**
     * Create a new group - name must be unique
     *
     * @param Array $params
     * @throws Exception
     */
    public function createGroup($group)
    {
        if (! isset($group['name'])) {
            RestoLogUtil::httpError(400, 'Missing mandatory group name');
        }

        try {

            $result = $this->dbDriver->query_params('INSERT INTO ' . $this->dbDriver->commonSchema . '.group (name, description, owner, private, created) VALUES ($1, $2, $3, $4, now()) ON CONFLICT (name) DO NOTHING RETURNING id ', array(
                $group['name'],
                $group['description'] ?? null,
                $group['owner'],
                $group['private']
            ));

            if (!$result) {
                throw new Exception('Cannot create group', 500);
            }
            
            $row = pg_fetch_assoc($result);
            if (empty($row)) {
                throw new Exception('A group named - ' . $group['name'] . ' - already exist', 400);
            }

            return array(
                'id' => $row['id'],
                'name' => $group['name'],
                'owner' => $group['owner'],
                'private' => $group['private']
            );
            
        } catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Remove a group
     *
     * @param Array $params
     * @throws Exception
     */
    public function removeGroup($params)
    {
        if (! isset($params['id'])) {
            RestoLogUtil::httpError(400, 'Missing mandatory group identifier');
        }

        try {
            if (isset($params['owner'])) {
                $result = $this->dbDriver->query_params('DELETE FROM ' . $this->dbDriver->commonSchema . '.group WHERE id=($1) AND owner=($2)', array(
                    $params['id'],
                    $params['owner']
                ));
            } else {
                $result = $this->dbDriver->query_params('DELETE FROM ' . $this->dbDriver->commonSchema . '.group WHERE id=($1)', array(
                    $params['id']
                ));
            }
            
            if (!$result || pg_affected_rows($result) !== 1) {
                throw new Exception();
            }
            
            return array(
                'id' => $params['id']
            );
        } catch (Exception $e) {
            RestoLogUtil::httpError(403, 'Cannot delete group');
        }
    }

    /**
     * Add user to group
     *
     * @param Array $params
     * @throws Exception
     */
    public function addUserToGroup($params, $userid)
    {
        if (! isset($params['id'])) {
            RestoLogUtil::httpError(400, 'Missing mandatory group identifier');
        }

        try {

            $result = $this->dbDriver->query_params('INSERT INTO ' . $this->dbDriver->commonSchema . '.group_member (groupid,userid,created) VALUES ($1,$2,now_utc()) ON CONFLICT (groupid,userid) DO NOTHING RETURNING groupid, userid', array(
                $params['id'],
                $userid
            ));
            
            if ( !isset($result) ) {
                throw new Exception();
            }
            
        } catch (Exception $e) {
            RestoLogUtil::httpError(400, 'Cannot add user to group');
        }

        return true;
    }

    /**
     * Add user to group
     *
     * @param Array $params
     * @throws Exception
     */
    public function removeUserFromGroup($params, $userid)
    {
        if (! isset($params['id'])) {
            RestoLogUtil::httpError(400, 'Missing mandatory group identifier');
        }

        try {

            $result = $this->dbDriver->query_params('DELETE FROM ' . $this->dbDriver->commonSchema . '.group_member WHERE groupid=$1 AND userid=$2', array(
                $params['id'],
                $userid
            ));
            
            if ( !isset($result) ) {
                throw new Exception();
            }
            
        } catch (Exception $e) {
            RestoLogUtil::httpError(400, 'Cannot remove user from group');
        }

        return true;
    }

    /**
     * Format group results for nice output
     *
     * @param array $results Groups from database
     * @param string $groupId Group id
     */
    private function formatGroups($results, $groupId)
    {

        // 404 if no empty results when id is specified
        if (! isset($results) || (isset($groupId) && count($results) === 0)) {
            RestoLogUtil::httpError(404);
        }

        $length = isset($groupId) ? 1 : count($results);

        // Format groups
        for ($i = $length; $i--;) {
            $results[$i]['id'] = $results[$i]['id'];
            $results[$i]['private'] = intval($results[$i]['private']);
            if (! isset($results[$i]['owner']) ) {
                unset($results[$i]['owner']);
            }
        }

        return isset($groupId) ? $results[0] : $results;

    }
}
