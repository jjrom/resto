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
     * Return rights for user i.e. aggregation of user rights including its group rights
     *
     * @param RestoUser $user
     * @param boolean $noGroups
     *
     * @return array
     * @throws exception
     */
    public function getRightsForUser($user, $noGroups = false)
    {

        $userRights = array();

        /*
         * Retrieve rights for user
         */
        if ( isset($user->profile['id']) ) {
            $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT rights FROM ' . $this->dbDriver->commonSchema . '.right WHERE userid=$1', array(
                $user->profile['id']
            )));
            if ( isset($results) && count($results) === 1 ) {
                $userRights = json_decode($results[0]['rights'], true);
            }
        }
        
        /*
         * Merge rights from user and from user's groups unless specified
         */
        return $noGroups ? $this->$userRights : $this->mergeRights(array_merge(array($userRights), $this->getRightsForGroups($user->getGroupIds())));
    }

    /**
     * Return rights for a given group
     *
     * @param string $groupId
     *
     * @return array
     * @throws exception
     */
    public function getRightsForGroup($groupId)
    {
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT rights FROM ' . $this->dbDriver->commonSchema . '.right WHERE groupid=$1', array(
            $groupId
        )));
        return isset($results) && count($results) == 1 ? json_decode($results[0]['rights'], true) : array();
    }

    /**
     * Store or update rights to database
     *
     * @param string $targetCol
     * @param string $targetValue
     * @param array rights
     *
     * @throws Exception
     */
    public function storeOrUpdateRights($targetCol, $targetValue, $rights)
    {

        $query = join(' ', array(
            'INSERT INTO ' . $this->dbDriver->commonSchema . '.right (' . $targetCol . ', rights)',
            'VALUES ($1, $2)',
            'ON CONFLICT (' . $targetCol . ')',
            'DO UPDATE SET rights = (SELECT json_object_agg(key, CASE WHEN json_typeof(existing) = \'object\' THEN json_set(existing, key, value::json) ELSE json_set(\'{"\' || key || \'":\' || value || \'}\'::json, key, value::json) END) AS merged_rights',
            'FROM json_each_text(rights) AS rights_entries',
            'JOIN json_each_text(EXCLUDED.rights) AS new_entries ON rights_entries.key = new_entries.key',
            'RETURNING rights'
        ));

        try {
            $result = pg_fetch_assoc(pg_query_params($this->dbDriver->getConnection(), $query, array(
                $targetValue,
                $rights
            )));
            if ( !$result ) {
                throw new Exception();
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot set rights');
        }

        return $result[0]['rights'];

    }

    /**
     * Return rights for groups
     *
     * @param array $groupIds
     *
     * @return array
     * @throws exception
     */
    private function getRightsForGroups($groupIds)
    {
        $groupRights = array();
        $results = $this->dbDriver->fetch($this->dbDriver->query('SELECT rights FROM ' . $this->dbDriver->commonSchema . '.right WHERE groupid IN (' . pg_escape_string($this->dbDriver->getConnection(), join(',', $groupIds)) . ')'));
        if ( isset($results) && count($results) >= 1 ) {
            for ($i = count($results); $i--;) {
                $groupRights[] = json_decode($results[$i]['rights'], true);
            }
        }
        return $groupRights;
    }

    /**
     * Merge an array of rights to an aggregate right
     *
     * Aggregation means that at least one true gives true. False in all other cases
     * 
     * @param array $rights
     */
    private function mergeRights($rights)
    {

        // Default rights allows only to delete/update things
        // that belongs to user
        $merged = array(
            'createCollection' => false,
            'deleteCollection' => true,
            'updateCollection' => true,
            'deleteAnyCollection' => false,
            'updateAnyCollection' => false,
            'createFeature' => true,
            'updateFeature' => true,
            'deleteFeature' => true,
            'createAnyFeature' => false,
            'deleteAnyFeature' => false,
            'updateAnyFeature' => false,
            'downloadFeature' => false
        );

        // [IMPORTANT] Assume only boolean otherwise it will be converted to anyway
        for ($i = count($rights); $i--;) {
            foreach ($rights[$i] as $key => $value) {

                if (array_key_exists($key, $merged)) {
                    if ( $value ) {
                        $merged[$key] = true;
                    }
                }
                else {
                    $merged[$key] = $value ?? false;
                }
            }
        }

        return $merged;

    }

}
