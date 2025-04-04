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
     * Return visibility WHERE clause
     * 
     * @param RestoUser $user
     * @return string
     */
    public static function getVisibilityClause($user)
    {

        // Non authenticated user can only see DEFAULT
        if ( !isset($user) || !isset($user->profile['id']) ) {
            return 'visibility && ARRAY[' . RestoConstants::GROUP_DEFAULT_ID . '::BIGINT]';
        }

        // Admin can see everything
        if ( $user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ) {
            return null;
        }

        $groups = $user->getGroupIds();
        if ( isset($groups) && count($groups) > 0 ) {
            return '(owner = ' . $user->profile['id'] . ' OR visibility && ARRAY[' . (count($groups) === 1 ? $groups[0] : join('::BIGINT,', $groups) ). '::BIGINT])';
        }

        // This is not possible since every user is at leat in DEFAULT group but who knows !
        return 'owner = ' . $user->profile['id'];
        
    }

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
            'INSERT INTO ' . $this->dbDriver->commonSchema . '.right as r (' . $targetCol . ', rights)',
            'VALUES ($1, $2)',
            'ON CONFLICT (' . $targetCol . ')',
            'DO UPDATE SET rights = COALESCE(r.rights::jsonb || $2::jsonb) RETURNING rights'
        ));

        try {
            $result = pg_fetch_assoc($this->dbDriver->query_params($query, array(
                $targetValue,
                json_encode($rights, JSON_UNESCAPED_SLASHES)
            )));
            if ( !$result ) {
                throw new Exception();
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot set rights');
        }

        return json_decode($result['rights'], true);

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
        $results = $this->dbDriver->fetch($this->dbDriver->query('SELECT rights FROM ' . $this->dbDriver->commonSchema . '.right WHERE groupid IN (' . $this->dbDriver->escape_string( join(',', $groupIds)) . ')'));
        if ( isset($results) && count($results) >= 1 ) {
            for ($i = count($results); $i--;) {
                if ( isset($results[$i]['rights']) ) {
                    $groupRights[] = json_decode($results[$i]['rights'], true);
                }
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
            RestoUser::CREATE_COLLECTION => false,
            RestoUser::DELETE_COLLECTION => true,
            RestoUser::UPDATE_COLLECTION => true,

            RestoUser::DELETE_ANY_COLLECTION => false,
            RestoUser::UPDATE_ANY_COLLECTION => false,

            RestoUser::CREATE_CATALOG => true,
            RestoUser::DELETE_CATALOG => true,
            RestoUser::UPDATE_CATALOG => true,

            RestoUser::CREATE_ANY_CATALOG => false,
            RestoUser::DELETE_ANY_CATALOG => false,
            RestoUser::UPDATE_ANY_CATALOG => false,

            RestoUser::CREATE_ITEM => true,
            RestoUser::DELETE_ITEM => true,
            RestoUser::UPDATE_ITEM => true,
            
            RestoUser::CREATE_ANY_ITEM => false,
            RestoUser::DELETE_ANY_ITEM => false,
            RestoUser::UPDATE_ANY_ITEM => false,
            RestoUser::DOWNLOAD_ITEM => false
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
