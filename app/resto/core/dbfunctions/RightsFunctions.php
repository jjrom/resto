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
class RightsFunctions
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
     * Return rights for groups
     *
     * @param array $groups
     * @param string $target
     * @param string $collectionId
     * @param string $featureId
     * @param string $target
     *
     * @return array
     * @throws exception
     */
    public function getRightsForGroups($groups, $target, $collectionId, $featureId, $merge = true)
    {
        $filter = $this->getFilterFromTarget($target, $collectionId, $featureId);
        $query = 'SELECT groupid, target, collection, featureid, download, visualize, createcollection as create FROM ' . $this->dbDriver->commonSchema . '.right WHERE groupid IN (' . join(',', $groups) . ')' . (isset($filter) ? ' AND ' . $filter : '');
        return $this->getRightsFromQuery($query, $merge);
    }

    /**
     * Return rights for user
     *
     * @param RestoUser $user
     * @param string $target
     * @param string $collectionId
     * @param string $featureId
     *
     * @return array
     * @throws exception
     */
    public function getRightsForUser($user, $target, $collectionId, $featureId)
    {
        $userRights = array();

        /*
         * Retrieve rights for user
         */
        if (isset($user->profile['id'])) {
            $filter = $this->getFilterFromTarget($target, $collectionId, $featureId);
            $query = 'SELECT userid, target, collection, featureid, download, visualize, createcollection as create FROM ' . $this->dbDriver->commonSchema . '.right WHERE userid=' . pg_escape_string($user->profile['id']) . (isset($filter) ? ' AND ' . $filter : '');
            $userRights = $this->getRightsFromQuery($query, false);
        }

        /*
         * Retrieve rights for user groups
         */
        $groupsRights = $this->getRightsForGroups($user->profile['groups'], $target, $collectionId, $featureId, false);

        /*
         * Merge rights from user and from user's groups
         */
        return $this->mergeRights(array_merge($userRights, $groupsRights));
    }

    /**
     * Store or update rights to database
     *
     *     array(
     *          'visualize' => // 0 or 1
     *          'download' => // 0 or 1
     *          'create' => // 0 or 1
     *     )
     *
     * @param array params
     *
     * @throws Exception
     */
    public function storeOrUpdateRights($params)
    {

        $rights = $params['rights'] ?? array();
        $userid = $params['id'] ?? null;
        $groupid = $params['groupid'] ?? null;
        $collectionId = $params['collectionId'] ?? null;
        $featureId = $params['featureId'] ?? null;
        $target = $params['target'] ?? null;

        /*
         * Store or update
         */
        if ($this->rightExists($userid, $groupid, $target, $collectionId, $featureId)) {
            return $this->updateRights($rights, $userid, $groupid, $target, $collectionId, $featureId);
        }

        return $this->storeRights($rights, $userid, $groupid, $target, $collectionId, $featureId);
    }

    /**
     * Delete rights from database
     *
     * @param array params
     *
     * @throws Exception
     */
    public function removeRights($params)
    {

        try {
            $filterOwner = $this->getFilterFromOwner($params['id'] ?? null, $params['groupid'] ?? null);
            if (!isset($filterOwner)) {
                throw new Exception();
            }
            $filterTarget = $this->getFilterFromTarget($params['target'] ?? null, $params['collectionId'] ?? null, $params['featureId'] ?? null);
            $result = pg_query($this->dbDriver->getConnection(), 'DELETE from ' . $this->dbDriver->commonSchema . '.right WHERE ' . (isset($filterTarget) ? ' AND ' . $filterTarget : ''));
            if (!$result) {
                throw new Exception();
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(400);
        }

    }

    /**
     * Return rights for user/group classified by collections/features
     * (if merge is set to true)
     *
     * @param string $query
     * @param boolean $merge
     *
     * @return array
     * @throws exception
     */
    private function getRightsFromQuery($query, $merge)
    {
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return $merge ? $this->mergeRights($results) : $results;
    }

    /**
     * Merge right
     *
     * @param array $newRight
     * @param array $existingRight
     */
    private function mergeRight($newRight, $existingRight)
    {
        if (isset($existingRight)) {
            return array(
                'download' => max((integer) $newRight['download'], $existingRight['download']),
                'visualize' => max((integer) $newRight['visualize'], $existingRight['visualize']),
                'create' => max((integer) $newRight['create'], $existingRight['create'])
            );
        }
        return array(
            'download' => (integer) $newRight['download'],
            'visualize' => (integer) $newRight['visualize'],
            'create' => (integer) $newRight['create']
        );
    }

    /**
     * Merge simple rights array to an associative collections/features array
     *
     * @param array $rights
     */
    private function mergeRights($rights)
    {
        $merged = array(
            'collections' => array(),
            'features' => array()
        );

        for ($i = count($rights); $i--;) {
            if (isset($rights[$i]['collection'])) {
                $merged['collections'][$rights[$i]['collection']] = $this->mergeRight($rights[$i], $merged['collections'][$rights[$i]['collection']] ?? null);
            } else {
                $merged['features'][$rights[$i]['featureid']] = $this->mergeRight($rights[$i], $merged['features'][$rights[$i]['featureid']] ?? null);
            }
        }

        /*
         * Update collections with '*' rights
         */
        if (isset($merged['collections']['*'])) {
            foreach (array_keys($merged['collections']) as $collectionId) {
                if ($collectionId !== '*') {
                    $merged['collections'][$collectionId] = $this->mergeRight($merged['collections'][$collectionId], $merged['collections']['*']);
                }
            }
        }
        return $merged;
    }

    /**
     * Store rights to database
     *
     * @param array  $rights
     * @param string $userid
     * @param string $groupid
     * @param string $target
     * @param string $collectionId
     * @param string $featureId
     *
     * @throws Exception
     */
    private function storeRights($rights, $userid, $groupid, $target, $collectionId, $featureId)
    {
        if (!isset($userid) && !isset($groupid)) {
            RestoLogUtil::httpError(400, 'Missing owner');
        }
        if (!isset($collectionId) && !isset($featureId)) {
            RestoLogUtil::httpError(400, 'Missing collectionId or featureId');
        }

        $values = array(
            'visualize' => isset($rights['visualize']) ? $this->integerOrZero($rights['visualize']) : 0,
            'download' => isset($rights['download']) ? $this->integerOrZero($rights['download']) : 0,
            'createcollection' => isset($rights['create']) ? $this->integerOrZero($rights['create']) : 0,
            'userid' => isset($userid) ? pg_escape_string($userid) : 'NULL',
            'groupid' => isset($groupid) ? pg_escape_string($groupid) : 'NULL',
            'collection' => isset($collectionId) ? '\'' . pg_escape_string($collectionId) . '\'' : 'NULL',
            'featureId' => isset($featureId) ? '\'' . pg_escape_string($featureId) . '\'' : 'NULL',
            'target' => isset($target) ? pg_escape_string($target) : 'NULL'
        );

        try {
            $result = pg_query($this->dbDriver->getConnection(), 'INSERT INTO ' . $this->dbDriver->commonSchema . '.right (' . join(',', array_keys($values)) . ') VALUES (' . join(',', array_values($values)) . ')');
            if (!$result) {
                throw new Exception();
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot store right');
        }
    }

    /**
     * Update rights to database
     *
     * @param string $userid
     * @param string $groupid
     * @param string $target
     * @param string $collectionId
     * @param string $featureId
     *
     * @throws Exception
     */
    private function updateRights($rights, $userid, $groupid, $target, $collectionId, $featureId)
    {
        if (!isset($userid) && !isset($groupid)) {
            RestoLogUtil::httpError(400, 'Missing owner');
        }
        if (!isset($collectionId) && !isset($featureId)) {
            RestoLogUtil::httpError(400, 'Missing target');
        }

        $where = array();

        if ( isset($target) ) {
            $where[] = 'target IN (\'' . pg_escape_string($this->dbDriver->targetSchema) . '\', \'*\')';
        }
        
        // Owner
        $where[] = isset($userid) ? 'userid=' . pg_escape_string($userid) : 'groupid=' . pg_escape_string($groupid);

        // Target
        $where[] = isset($collectionId) ? 'collection=\'' . pg_escape_string($collectionId) . '\'' : 'featureid=\'' . pg_escape_string($featureId) . '\'';

        $toBeSet = array();
        foreach (array_values(array('visualize', 'download', 'create')) as $right) {
            if (isset($rights[$right])) {
                $toBeSet[] = ($right === 'create' ? 'createcollection' : $right) . "=" . $this->integerOrZero($rights[$right]);
            }
        }

        if (count($toBeSet) > 0) {
            $this->dbDriver->query('UPDATE ' . $this->dbDriver->commonSchema . '.right SET ' . join(',', $toBeSet) . ' WHERE ' . join(' AND ', $where));
        }
        return true;
    }

    /**
     * Return $value or NULL
     * @param string $value
     */
    private function integerOrZero($value)
    {
        return isset($value) && is_int($value) && ($value === 0 || $value === 1) ? $value : 0;
    }

    /**
     * Check if right exists in database
     *
     * @param string $userid
     * @param string $groupid
     * @param string $target
     * @param string $collectionId
     * @param string $featureId
     */
    private function rightExists($userid, $groupid, $target, $collectionId, $featureId)
    {
        if (!isset($userid) && !isset($groupid)) {
            return false;
        }
        if (!isset($collectionId) && !isset($featureId)) {
            return false;
        }

        $where = array();

        if ( isset($target) ) {
            $where[] = 'target IN (\'' . pg_escape_string($target) . '\', \'*\')';
        }
        

        // Owner
        $where[] = isset($userid) ? 'userid=' . pg_escape_string($userid) : 'groupid=' . pg_escape_string($groupid);

        // Target
        $where[] = isset($collectionId) ? 'collection=\'' . pg_escape_string($collectionId) . '\'' : 'featureid=\'' . pg_escape_string($featureId) . '\'';

        $query = 'SELECT 1 from ' . $this->dbDriver->commonSchema . '.right WHERE ' . join(' AND ', $where);
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return !empty($results);
    }

    /**
     * Return filter WHERE from target
     *
     * @param string $target
     * @param string $collectionId
     * @param string $featureId
     */
    private function getFilterFromTarget($target, $collectionId, $featureId)
    {
        if ( !isset($collectionId) && !isset($featureId) ) {
            return null;
        }

        $where = array();
        if ( isset($target) ) {
            $where[] = 'target IN (\'' . pg_escape_string($this->dbDriver->targetSchema) . '\', \'*\')';
        }
        if (isset($collectionId)) {
            $where[] = 'collection IN (\'' . pg_escape_string($collectionId) . '\', \'*\')';
        }
        elseif (isset($featureId)) {
            $where[] = 'featureid IN (\'' . pg_escape_string($featureId) . '\', \'*\')';
        }
        return join(' AND ', $where);
    }

    /**
     * Return filter WHERE from owner
     *
     * @param string $userid
     * @param string $groupid
     */
    private function getFilterFromOwner($userid, $groupid)
    {
        if (!isset($userid) && !isset($groupid)) {
            return null;
        }
        if (isset($userid)) {
            return 'userid='. pg_escape_string($userid);
        } elseif (isset($groupid)) {
            return 'groupid=' . pg_escape_string($groupid);
        }
        return null;
    }
}
