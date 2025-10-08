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
        $where = array(
            /*'private <> 1',
            'name NOT IN (\'admin\', \'default\')'*/
        );
        
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

        $results = $this->dbDriver->fetch($this->dbDriver->query('SELECT name, description, id, to_iso8601(created) as created, private FROM ' . $this->dbDriver->commonSchema . '.group' . (count($where) > 0 ? ' WHERE ' . join(' AND ', $where) : '') . ' ORDER BY id DESC'));

        return empty($results) ? array() : $results;

    }

    /**
     * Get group
     *
     * @return array
     * @throws Exception
     */
    public function getGroup($name)
    {
        if ( !isset($name) ) {
            RestoLogUtil::httpError(404);
        }

        $query = join(' ', array(
            'SELECT g.name, g.description, g.owner, g.id, to_iso8601(g.created) as created, g.private, COALESCE(ARRAY_REMOVE(ARRAY_AGG(u.username ORDER BY u.username), NULL), \'{}\') AS members',
            'FROM ' . $this->dbDriver->commonSchema . '.group g LEFT JOIN  ' . $this->dbDriver->commonSchema . '.group_member gm ON g.id = gm.groupid',
            'LEFT JOIN  ' . $this->dbDriver->commonSchema . '.user u ON gm.userid = u.id',
            'WHERE private <> 1 AND g.name = \'' . $this->dbDriver->escape_string($name) . '\'',
            'GROUP BY g.name,g.description,g.owner,g.id,g.created,g.private ORDER BY g.name'
        ));

        return $this->formatGroup($this->dbDriver->fetch($this->dbDriver->query($query)));
        
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
        if (! isset($params['name'])) {
            RestoLogUtil::httpError(400, 'Missing mandatory group name');
        }

        try {
            if (isset($params['owner'])) {
                $result = $this->dbDriver->query_params('DELETE FROM ' . $this->dbDriver->commonSchema . '.group WHERE name=($1) AND owner=($2)', array(
                    $params['name'],
                    $params['owner']
                ));
            } else {
                $result = $this->dbDriver->query_params('DELETE FROM ' . $this->dbDriver->commonSchema . '.group WHERE name=($1)', array(
                    $params['name']
                ));
            }
            
            if (!$result || pg_affected_rows($result) !== 1) {
                throw new Exception();
            }
            
            return array(
                'name' => $params['name']
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
    public function addUserToGroup($params, $userid, $silent = false)
    {
        if (! isset($params['id'])) {
            RestoLogUtil::httpError(400, 'Missing mandatory group identifier');
        }

        try {

            $result = $this->dbDriver->query_params('INSERT INTO ' . $this->dbDriver->commonSchema . '.group_member (groupid,userid,created) VALUES ($1,$2,now_utc()) ON CONFLICT (groupid,userid) DO NOTHING RETURNING groupid, userid', array(
                $params['id'],
                $userid
            ));
            
            if ( !isset($result) && !$silent) {
                throw new Exception();
            }
            
        } catch (Exception $e) {
            RestoLogUtil::httpError(400, 'Cannot add user to group');
        }

        return true;
    }

    /**
     * Remove user from group
     *
     * @param Array $params
     * @throws Exception
     */
    public function removeUserFromGroup($params, $userid, $silent = false)
    {
        if (! isset($params['id'])) {
            RestoLogUtil::httpError(400, 'Missing mandatory group identifier');
        }

        try {

            $result = $this->dbDriver->query_params('DELETE FROM ' . $this->dbDriver->commonSchema . '.group_member WHERE groupid=$1 AND userid=$2', array(
                $params['id'],
                $userid
            ));
            
            if ( !isset($result) && !$silent) {
                throw new Exception();
            }
            
        } catch (Exception $e) {
            RestoLogUtil::httpError(400, 'Cannot remove user from group');
        }

        return true;
    }

    /**
     * Format group  for nice output
     *
     * @param array $rawGroup Group from database
     */
    private function formatGroup($rawGroup)
    {
        if ( empty($rawGroup) ) {
            RestoLogUtil::httpError(404);
        }

        try {
            if ( isset($rawGroup[0]['owner']) ) {
                $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT username FROM ' . $this->dbDriver->commonSchema . '.user WHERE id=$1', array(
                    $rawGroup[0]['owner']
                )));
                if ( !empty($results) ) {
                    $rawGroup[0]['owner'] = $results[0]['username'];
                }
            }
            
        } catch(Exception $e) {
            // Don't break
        }
        $rawGroup[0]['private'] = (integer) $rawGroup[0]['private'];
        $rawGroup[0]['members'] = RestoUtil::SQLTextArrayToPHP($rawGroup[0]['members']);
        return $rawGroup[0];
    }
}
