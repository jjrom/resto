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
 * RESTo PostgreSQL rights functions
 */
class Functions_rights {

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
     * List all groups
     * 
     * @return array
     * @throws Exception
     */
    public function getGroups($groupid = null) {
        return $this->dbDriver->fetch($this->dbDriver->query('SELECT groupid, childrens FROM usermanagement.groups' . (isset($groupid) ? ' WHERE groupid=\'' . pg_escape_string($groupid) . '\'' : '')));
    }

    /**
     * Return rights for groups
     * 
     * @param string $groups
     * @param string $targetType
     * @param string $target
     * 
     * @return array
     * @throws exception
     */
    public function getRightsForGroups($groups, $targetType = null, $target = null, $merge = true) {
        $query = 'SELECT owner, targettype, target, download, visualize, createcollection as create FROM usermanagement.rights WHERE ownertype=\'group\' AND owner IN (' . $this->quoteForIn($groups) . ')' . (isset($targetType) ? ' AND targettype=\'' . pg_escape_string($targetType) . '\'' : '') . (isset($target) ? ' AND target IN (\'' . pg_escape_string($target) . '\', \'*\')' : '');
        return $this->getRightsFromQuery($query, $merge);
    }

    /**
     * Return rights for user
     * 
     * @param string $user
     * @param string $targetType
     * @param string $target
     * 
     * @return array
     * @throws exception
     */
    public function getRightsForUser($user, $targetType = null, $target = null) {

        /*
         * Retrieve rights for user
         */
        if ($user->profile['userid'] !== -1) {
            $query = 'SELECT owner, targettype, target, download, visualize, createcollection as create FROM usermanagement.rights WHERE ownertype=\'user\' AND owner=\'' . pg_escape_string($user->profile['email']) . '\'' . (isset($targetType) ? ' AND targettype=\'' . pg_escape_string($targetType) . '\'' : '') . (isset($target) ? ' AND target IN (\'' . pg_escape_string($target) . '\', \'*\')' : '');
        }
        $userRights = $this->getRightsFromQuery(isset($query) ? $query : null, false);

        /*
         * Retrieve rights for user groups
         */
        $groupsRights = $this->getRightsForGroups($user->profile['groups'], $targetType, $target, false);

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
     * @param array  $rights
     * @param string $ownerType
     * @param string $owner
     * @param string $targetType
     * @param string $target
     * @param string $productIdentifier
     * 
     * @throws Exception
     */
    public function storeOrUpdateRights($rights, $ownerType, $owner, $targetType, $target, $productIdentifier = null) {

        /*
         * Store or update
         */
        if ($this->rightExists($ownerType, $owner, $targetType, $target)) {
            return $this->updateRights($rights, $ownerType, $owner, $targetType, $target);
        }

        return $this->storeRights($rights, $ownerType, $owner, $targetType, $target, $productIdentifier);
    }

    /**
     * Delete rights from database
     * 
     * @param string $ownerType
     * @param string $owner
     * @param string $targetType
     * @param string $target
     * 
     * @throws Exception
     */
    public function removeRights($ownerType, $owner, $targetType = null, $target = null) {
        try {
            $result = pg_query($this->dbDriver->dbh, 'DELETE from usermanagement.rights WHERE ownertype=\'' . pg_escape_string($ownerType) . '\' AND owner=\'' . pg_escape_string($owner) . '\'' . (isset($targetType) ? ' AND targettype=\'' . pg_escape_string($targetType) . '\'' : '') . (isset($target) ? ' AND target=\'' . pg_escape_string($target) . '\'' : ''));
            if (!$result) {
                throw new Exception;
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot delete rights for ' . $owner);
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
    private function getRightsFromQuery($query, $merge) {

        /*
         * No query => empty rights
         */
        if (!isset($query)) {
            return array();
        }

        /*
         * Retrieve as a simple array
         */
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));

        return $merge ? $this->mergeRights($results) : $results;
    }

    /**
     * Merge right
     * 
     * @param array $newRight
     * @param array $existingRight
     */
    private function mergeRight($newRight, $existingRight) {
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
    private function mergeRights($rights) {

        $merged = array(
            'collections' => array(),
            'features' => array()
        );

        for ($i = count($rights); $i--;) {
            if ($rights[$i]['targettype'] === 'collection') {
                $merged['collections'][$rights[$i]['target']] = $this->mergeRight($rights[$i], isset($merged['collections'][$rights[$i]['target']]) ? $merged['collections'][$rights[$i]['target']] : null);
            } else {
                $merged['features'][$rights[$i]['target']] = $this->mergeRight($rights[$i], isset($merged['features'][$rights[$i]['target']]) ? $merged['features'][$rights[$i]['target']] : null);
            }
        }

        /*
         * Update collections with '*' rights
         */
        if (isset($merged['collections']['*'])) {
            foreach (array_keys($merged['collections']) as $collectionName) {
                if ($collectionName !== '*') {
                    $merged['collections'][$collectionName] = $this->mergeRight($merged['collections'][$collectionName], $merged['collections']['*']);
                }
            }
        }
        return $merged;
    }

    /**
     * Store rights to database
     *     
     * @param array  $rights
     * @param string $ownerType
     * @param string $owner
     * @param string $targetType
     * @param string $target
     * @param string $productIdentifier
     * 
     * @throws Exception
     */
    private function storeRights($rights, $ownerType, $owner, $targetType, $target, $productIdentifier = null) {

        $values = array(
            'ownertype' => '\'' . pg_escape_string($ownerType) . '\'',
            'owner' => '\'' . pg_escape_string($owner) . '\'',
            'targettype' => '\'' . pg_escape_string($targetType) . '\'',
            'target' => '\'' . pg_escape_string($target) . '\'',
            'visualize' => isset($rights['visualize']) ? $this->integerOrZero($rights['visualize']) : 0,
            'download' => isset($rights['download']) ? $this->integerOrZero($rights['download']) : 0,
            'createcollection' => isset($rights['create']) ? $this->integerOrZero($rights['create']) : 0,
            'productIdentifier' => isset($productIdentifier) ? '\'' . pg_escape_string($productIdentifier) . '\'' : 'NULL',
        );

        try {
            $result = pg_query($this->dbDriver->dbh, 'INSERT INTO usermanagement.rights (' . join(',', array_keys($values)) . ') VALUES (' . join(',', array_values($values)) . ')');
            if (!$result) {
                throw new Exception();
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot store right');
        }
    }

    /**
     * Store new group - check if group exists before
     * 
     * @param string $groupid
     * @throws Exception
     */
    public function storeGroup($groupid) {
        $groups = $this->getGroups($groupid);
        if (empty($groups)) {
            try {
                $result = pg_query($this->dbDriver->dbh, 'INSERT INTO usermanagement.groups (groupid) VALUES (\'' . pg_escape_string($groupid) . '\')');
                if (!$result) {
                    throw new Exception();
                }
                return $this->getGroups();
            } catch (Exception $e) {
                RestoLogUtil::httpError(500, 'Cannot store group');
            }
        } else {
            RestoLogUtil::httpError(500, 'Cannot store group - groupid is missing');
        }
    }

    /**
     * Update rights to database
     * 
     * @param array  $rights
     * @param string $ownerType
     * @param string $owner
     * @param string $targetType
     * @param string $target
     * @param string $productIdentifier
     * 
     * @throws Exception
     */
    private function updateRights($rights, $ownerType, $owner, $targetType, $target) {

        $where = array(
            'ownertype=\'' . pg_escape_string($ownerType) . '\'',
            'owner=\'' . pg_escape_string($owner) . '\'',
            'targettype=\'' . pg_escape_string($targetType) . '\'',
            'target=\'' . pg_escape_string($target) . '\''
        );

        $toBeSet = array();
        foreach (array_values(array('visualize', 'download', 'create')) as $right) {
            if (isset($rights[$right])) {
                $toBeSet[] = ($right === 'create' ? 'createcollection' : $right) . "=" . $this->integerOrZero($rights[$right]);
            }
        }

        if (count($toBeSet) > 0) {
            $this->dbDriver->query('UPDATE usermanagement.rights SET ' . join(',', $toBeSet) . ' WHERE ' . join(' AND ', $where));
        }
        return true;
    }

    /**
     * Return $value or NULL
     * @param string $value
     */
    private function integerOrZero($value) {
        return isset($value) && is_int($value) && ($value === 0 || $value === 1) ? $value : 0;
    }

    /**
     * Check if right exists in database
     * 
     * @param string $ownerType
     * @param string $owner
     * @param string $targetType
     * @param string $target
     */
    private function rightExists($ownerType, $owner, $targetType, $target) {
        $query = 'SELECT 1 from usermanagement.rights WHERE ownertype=\'' . pg_escape_string($ownerType) . '\' AND owner=\'' . pg_escape_string($owner) . '\' AND targettype=\'' . pg_escape_string($targetType) . '\' AND target=\'' . pg_escape_string($target) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return !empty($results);
    }

    /**
     * Return quoted comma separated groupid for SQL IN(...) clause
     * 
     * @param string $groups - comma separated list of groups
     */
    private function quoteForIn($groups) {
        $exploded = explode(',', $groups);
        $quoted = array();
        for ($i = count($exploded); $i--;) {
            $groupid = trim($exploded[$i]);
            if ($groupid !== '') {
                $quoted[] = '\'' . pg_escape_string($groupid) . '\'';
            }
        }
        return join(',', $quoted);
    }

}
