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
 * RESTo PostgreSQL users functions
 */
class Functions_users {

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
     * Return encrypted user password
     * 
     * @param string $identifier : email
     * 
     * @throws Exception
     */
    public function getUserPassword($identifier) {
        $query = 'SELECT password FROM usermanagement.users WHERE email=\'' . pg_escape_string($identifier) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return count($results) === 1 ? $results[0]['password'] : null;
    }

    /**
     * Get user profile
     * 
     * @param string $identifier : can be email (or string) or integer (i.e. uid)
     * @param string $password : if set then profile is returned only if password is valid
     * @return array : this function return HTTP 404 if user is not found in database
     * @throws exception
     */
    public function getUserProfile($identifier, $password = null) {

        /*
         * Unregistered users
         */
        if (!isset($identifier) || !$identifier || $identifier === 'unregistered') {
            RestoLogUtil::httpError(404);
        }

        $query = 'SELECT userid, email, groups, username, givenname, lastname, to_char(registrationdate, \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') as registrationdate, country, organization, organizationcountry, flags, topics, activated, validatedby, to_char(validationdate, \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') as validationdate FROM usermanagement.users WHERE ' . $this->useridOrEmailFilter($identifier) . (isset($password) ? ' AND password=\'' . pg_escape_string(RestoUtil::encrypt($password)) . '\'' : '');
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));

        if (count($results) === 0) {
            RestoLogUtil::httpError(404);
        }
        
        $results[0]['activated'] = (integer) $results[0]['activated'];
        $results[0]['groups'] = substr($results[0]['groups'], 1, -1);
        
        /*
         * Add picture
         */
        $results[0]['picture'] = $this->getPicture($results[0]['email']);

        return $results[0];
    }

    /**
     * Get full profiles for all users
     * 
     * @return array
     * @throws exception
     */
    public function getUsersProfiles($data = array()) {
        
        $results = $this->dbDriver->query('SELECT userid, email, groups, username, givenname, lastname, to_char(registrationdate, \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') as registrationdate, country, organization, organizationcountry, flags, topics, activated, validatedby, to_char(validationdate, \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') as validationdate FROM usermanagement.users' . (isset($data['groupid']) ? ' WHERE \'' . pg_escape_string($data['groupid']) . '\' = any(groups)' : (isset($data['keywords']) ? ' WHERE email LIKE \'' . pg_escape_string($data['keywords']) .'\' OR username LIKE \''  . pg_escape_string($data['keywords']) .'\' OR givenname LIKE \''  . pg_escape_string($data['keywords']) .'\' OR lastname LIKE \''  . pg_escape_string($data['keywords']) .'\' OR country LIKE \''  . pg_escape_string($data['keywords']) .'\' OR organization LIKE \''  . pg_escape_string($data['keywords']) .'\'' : '')) . (isset($data['limit']) ? ' LIMIT ' . pg_escape_string($data['limit']) : '') . (isset($data['offset']) ? ' OFFSET ' . pg_escape_string($data['offset']) : '') );
        $profiles = array();
        while ($profile = pg_fetch_assoc($results)) {
            $profile['groups'] = substr($profile['groups'], 1, -1);

            $profiles[] = array(
                'userid' => $profile['userid'],
                'email' => $profile['email'],
                'picture' => $this->getPicture($profile['email']),
                'groups' => $profile['groups'],
                'username' => $profile['username'],
                'givenname' => $profile['givenname'],
                'lastname' => $profile['lastname'],
                'registrationdate' => $profile['registrationdate'],
                'country' => $profile['country'],
                'organization' => $profile['organization'],
                'organizationcountry' => $profile['organizationcountry'],
                'flags' => $profile['flags'],
                'topics' => $profile['topics'],
                'activated' => (integer) $profile['activated'],
                'validatedby' => $profile['validatedby'],
                'validationdate' => $profile['validationdate']
            );
        }
        return $profiles;
    }

    /**
     * Check if user identified by $identifier exists within database
     * 
     * @param string $email - user email
     * 
     * @return boolean
     * @throws Exception
     */
    public function userExists($email) {
        $query = 'SELECT 1 FROM usermanagement.users WHERE email=\'' . pg_escape_string($email) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        return !empty($results);
    }

    /**
     * Save user profile to database i.e. create new entry if user does not exist
     * 
     * @param array $profile
     * @return array (userid, activationcode)
     * @throws exception
     */
    public function storeUserProfile($profile) {

        if (!is_array($profile) || !isset($profile['email'])) {
            RestoLogUtil::httpError(500, 'Cannot save user profile - invalid user identifier');
        }
        if ($this->userExists($profile['email'])) {
            RestoLogUtil::httpError(500, 'Cannot save user profile - user already exist');
        }

        /*
         * Normalize email
         */
        $email = trim(strtolower($profile['email']));

        $toBeSet = array(
            'email' => '\'' . pg_escape_string($email) . '\'',
            'password' => '\'' . (isset($profile['password']) ? RestoUtil::encrypt($profile['password']) : str_repeat('*', 40)) . '\'',
            'groups' => '\'{' . (isset($profile['groups']) ? pg_escape_string($profile['groups']) : 'default') . '}\'',
            'activationcode' => '\'' . pg_escape_string(RestoUtil::encrypt($email . microtime())) . '\'',
            'activated' => $profile['activated'],
            'validatedby' => isset($profile['validatedby']) ? '\'' . $profile['validatedby'] .'\'' : 'NULL',
            'validationdate' => isset($profile['validatedby']) ? 'now()' : 'NULL',
            'registrationdate' => 'now()'
        );
        foreach (array_values(array('username', 'givenname', 'lastname', 'country', 'organization', 'topics', 'organizationcountry', 'flags')) as $field) {
            $toBeSet[$field] = (isset($profile[$field]) ? "'" . pg_escape_string($profile[$field]) . "'" : 'NULL');
        }

        return pg_fetch_array($this->dbDriver->query('INSERT INTO usermanagement.users (' . join(',', array_keys($toBeSet)) . ') VALUES (' . join(',', array_values($toBeSet)) . ') RETURNING userid, activationcode'));
    }

    /**
     * Update user profile to database
     * 
     * @param array $profile
     * @return integer (userid)
     * @throws exception
     */
    public function updateUserProfile($profile) {

        if (!is_array($profile) || !isset($profile['email'])) {
            RestoLogUtil::httpError(500, 'Cannot update user profile - invalid user identifier');
        }

        /*
         * The following parameters cannot be updated :
         *   - email
         *   - userid 
         *   - activationcode
         *   - registrationdate
         */
        $values = array();
        foreach (array_values(array('username', 'givenname', 'lastname', 'groups', 'country', 'organization', 'topics', 'organizationcountry', 'flags')) as $field) {
            if (isset($profile[$field])) {
                switch ($field) {
                    case 'password':
                        $values[] = 'password=\'' . RestoUtil::encrypt($profile['password']) . '\'';
                        break;
                    case 'activated':
                        $values[] = 'activated=' . $profile['activated'];
                        break;
                    default:
                        $values[] = $field . '=\'' . pg_escape_string($profile[$field]) . '\'';
                }
            }
        }

        $results = array();
        if (count($values) > 0) {
            $results = $this->dbDriver->fetch($this->dbDriver->query('UPDATE usermanagement.users SET ' . join(',', $values) . ' WHERE email=\'' . pg_escape_string(trim(strtolower($profile['email']))) . '\' RETURNING userid'));
        }

        return count($results) === 1 ? $results[0]['userid'] : null;
    }

    /**
     * Add groups to user $userid
     * 
     * @param integer $userid
     * @param string $groups
     * @return null
     * @throws Exception
     */
    public function storeUserGroups($userid, $groups) {
        return $this->storeOrRemoveUserGroups('store', $userid, $groups);
    }

    /**
     * Remove groups for user $userid
     * 
     * @param integer $userid
     * @param string $groups
     * @return null
     * @throws Exception
     */
    public function removeUserGroups($userid, $groups) {
        return $this->storeOrRemoveUserGroups('remove', $userid, $groups);
    }

    /**
     * Activate user
     * 
     * @param string $userid
     * @param string $activationcode
     * @param boolean $autoValidateUser
     * 
     * @throws Exception
     */
    public function activateUser($userid, $activationcode, $autoValidateUser = false) {

        $toBeSet = array(
            'activated=1'
        );

        /*
         * User is validated on activation
         */
        if ($autoValidateUser) {
            $toBeSet = array_merge($toBeSet, array(
                'validatedby=\'auto\'',
                'validationdate=now()'
            ));
        }

        $query = 'UPDATE usermanagement.users SET ' . join(',', $toBeSet) . ' WHERE userid=\'' . pg_escape_string($userid) . '\'' . (isset($activationcode) ? ' AND activationcode=\'' . pg_escape_string($activationcode) . '\'' : '') . ' RETURNING userid';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));

        return count($results) === 1 ? true : false;
    }

    /**
     * Deactivate user
     * 
     * @param string $userid
     * @throws Exception
     */
    public function deactivateUser($userid) {
        return count($this->dbDriver->fetch($this->dbDriver->query('UPDATE usermanagement.users SET activated=0 WHERE userid=\'' . pg_escape_string($userid) . '\' RETURNING userid'))) === 1 ? true : false;
    }
    
    /**
     * Validate user
     * 
     * @param string $userid
     * @param string $validatedBy
     * @return boolean
     */
    public function validateUser($userid, $validatedBy) {

        /*
         * Validate user. 
         * If user is already validate, update date and validatedby.
         */
        $toBeSet = array(
            'validatedby=\'' . $validatedBy . '\'',
            'validationdate=now()'
        );
        
        $query = 'UPDATE usermanagement.users SET ' . join(',', $toBeSet) . ' WHERE userid=\'' . pg_escape_string($userid) . '\'' . ' RETURNING userid';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));

        return count($results) === 1 ? true : false;
    }
    
    /**
     * Unvalidate user
     * 
     * @param string $userid
     * @return boolean
     */
    public function unvalidateUser($userid){
        
        $toBeSet = array(
            'validatedby=NULL',
            'validationdate=NULL'
        );
        
        return count($this->dbDriver->fetch($this->dbDriver->query('UPDATE usermanagement.users SET ' . join(',', $toBeSet) . ' WHERE userid=\'' . pg_escape_string($userid) . '\'  RETURNING userid'))) === 1 ? true : false;
    }

    /**
     * Return filter on user
     * 
     * @param string $identifier
     */
    private function useridOrEmailFilter($identifier) {
        return ctype_digit($identifier) ? 'userid=' . $identifier : 'email=\'' . pg_escape_string($identifier) . '\'';
    }

    /**
     * Store or remove groups for user $userid
     * 
     * @param string $storeOrRemove
     * @param integer $userid
     * @param string $groups
     * @return null
     * @throws Exception
     */
    private function storeOrRemoveUserGroups($storeOrRemove, $userid, $groups) {

        if (!isset($userid)) {
            RestoLogUtil::httpError(500, 'Cannot ' . $storeOrRemove . ' groups - invalid user identifier : ' . $userid);
        }
        if (empty($groups)) {
            RestoLogUtil::httpError(500, 'Cannot ' . $storeOrRemove . ' groups - empty input groups');
        }

        $profile = $this->getUserProfile($userid);
        if (!isset($profile)) {
            RestoLogUtil::httpError(500, 'Cannot ' . $storeOrRemove . ' groups - user profile not found for : ' . $userid);
        }

        /*
         * Explode existing groups into an associative array
         */
        $userGroups = !empty($profile['groups']) ? array_flip(explode(',', $profile['groups'])) : array();

        /*
         * Explode input groups
         */
        $newGroups = array();
        $rawNewGroups = explode(',', $groups);
        for ($i = 0, $ii = count($rawNewGroups); $i < $ii; $i++) {
            if ($rawNewGroups[$i] !== '') {
                $newGroups[$rawNewGroups[$i]] = 1;
            }
        }

        /*
         * Store - merge new groups with user groups
         */
        if ($storeOrRemove === 'store') {
            $newGroups = array_keys(array_merge($newGroups, $userGroups));
        }

        /*
         * Remove - note that 'default' group cannot be removed
         */ else {
            foreach (array_keys($newGroups) as $key) {
                if ($key !== 'default') {
                    unset($userGroups[$key]);
                }
            }
            $newGroups = array_keys($userGroups);
        }

        /*
         * Update user profile
         */
        $results = count($newGroups) > 0 ? implode(',', $newGroups) : null;
        $this->dbDriver->fetch($this->dbDriver->query('UPDATE usermanagement.users SET groups=' . (isset($results) ? '\'{' . pg_escape_string($results) . '}\'' : 'NULL') . ' WHERE userid=\'' . $userid . '\''));

        return $results;
    }
    
    /**
     * Return gravatar picture from $email
     * 
     * @param string $email
     */
    private function getPicture($email, $size = 200) {
        return '//www.gravatar.com/avatar/' . md5($email) . '?d=mm&s=' . $size;
    }

}
