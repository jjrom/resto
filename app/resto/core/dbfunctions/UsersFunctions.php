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
 * RESTo PostgreSQL users functions
 */
class UsersFunctions
{
    private $dbDriver = null;

    private $countLimit = 50;

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
     * Return encrypted user password
     *
     * @param string $identifier : email
     *
     * @throws Exception
     */
    public function getUserPassword($identifier)
    {
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT password FROM resto.user WHERE email=$1', array($identifier)));
        return count($results) === 1 ? $results[0]['password'] : null;
    }

    /**
     * Get user profile
     *
     * @param string $fieldName
     * @param string $fieldValue
     * @param array $params
     * @throws exception
     */
    public function getUserProfile($fieldName, $fieldValue, $params = array())
    {
        
        // Add followed and followme booleans
        $fields = 'id,email,name,firstname,lastname,bio,groups,lang,country,organization,organizationcountry,flags,topics,password,picture,to_iso8601(registrationdate),activated,followers,followings,validatedby,to_iso8601(validationdate),externalidp,settings';
        if (isset($params['from'])) {
            $fields = $fields . ',EXISTS(SELECT followerid FROM resto.follower WHERE followerid=id AND userid=' . pg_escape_string($params['from']) . ') AS followme,EXISTS(SELECT followerid FROM resto.follower WHERE userid=id AND followerid=' . pg_escape_string($params['from']) . ') AS followed';
        }
        
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT ' . $fields . ' FROM resto.user WHERE ' . $fieldName . '=$1', array(
            $fieldValue
        )));
        
        if (count($results) === 0) {
            RestoLogUtil::httpError(404, 'Unknown user');
        }

        /*
         * Check password
         */
        if (isset($params['password'])) {

            // External authentication
            if ($results[0]['password'] === str_repeat('*', 60)) {
                RestoLogUtil::httpError(400, 'External user');
            }
                
            if (!password_verify($params['password'], $results[0]['password'])) {
                RestoLogUtil::httpError(401);
            }
            
        }
        
        /*
         * Full profile if id is caller / partial otherwise
         */
        $formatedProfile = isset($params['partial']) && $params['partial'] ? FormatUtil::partialUserProfile($results[0]) : FormatUtil::fullUserProfile($results[0]);
        return isset($formatedProfile) ? $formatedProfile : RestoLogUtil::httpError(404);

    }

    /**
     * Get full profiles for all users
     *
     * @param array $params
     * @param string $userid
     *
     * @return array
     * @throws exception
     */
    public function getUsersProfiles($params, $userid)
    {
        
        // Only returns activated profiles
        $where = array(
            'activated=1'
        );

        // Paginate
        if (isset($params['lt'])) {
            $where[] = 'id < ' . $params['lt'];
        }

        if (isset($params['groupid'])) {
            $where[] =  'groups @> ARRAY[' . $params['groupid'] . ']';
        }
        
        if (isset($params['in'])) {
            $where[] = 'id in (' . pg_escape_string($params['in']) . ')';
        }

        // Search on firstname if length > 3
        if (isset($params['q'])) {
            if (strlen($params['q']) < 3 || strpos($params['q'], '%') !== false) {
                return RestoLogUtil::httpError(400);
            }
            $where[] = 'name ILIKE \'%' . pg_escape_string($params['q']). '%\'';
        }

        // Add followed and followme booleans
        $fields = 'id,email,name,firstname,lastname,bio,groups,lang,country,organization,organizationcountry,flags,topics,password,picture,to_iso8601(registrationdate),activated,followers,followings,validatedby,to_iso8601(validationdate),externalidp,settings';
        if (isset($userid)) {
            $fields = $fields . ',EXISTS(SELECT followerid FROM resto.follower WHERE followerid=id AND userid=' . pg_escape_string($userid) . ') AS followme,EXISTS(SELECT followerid FROM resto.follower WHERE userid=id AND followerid=' . pg_escape_string($userid) . ') AS followed';
        }
        
        $results = $this->dbDriver->query('SELECT ' . $fields . ' FROM resto.user WHERE ' . join(' AND ', $where) . ' ORDER BY id DESC LIMIT ' . $this->countLimit);
        
        $profiles = array();
        while ($profile = pg_fetch_assoc($results)) {
            $partial = isset($userid) ? $userid !== $profile['id'] : false;
            $profiles[] = $partial ? FormatUtil::partialUserProfile($profile) : FormatUtil::fullUserProfile($profile);
        }
        return array(
            'profiles' => $profiles
        );
    }

    /**
     * Check if user identified by email or id exists within database
     *
     * @param array $params - email or id
     *
     * @return boolean
     * @throws Exception
     */
    public function userExists($params)
    {
        $query = null;

        if (isset($params['email'])) {
            $query = 'SELECT 1 FROM resto.user WHERE email=lower(\'' . pg_escape_string($params['email']) . '\')';
        } elseif (isset($params['id']) && ctype_digit($params['id'])) {
            $query = 'SELECT 1 FROM resto.user WHERE id=' . pg_escape_string($params['id']);
        }
        
        if (! isset($query)) {
            return false;
        }

        return !empty($this->dbDriver->fetch($this->dbDriver->query($query)));
    }

    /**
     * Save user profile to database i.e. create new entry if user does not exist
     *
     * @param array $profile
     * @param array $storageInfo
     * @return array id
     * @throws exception
     */
    public function storeUserProfile($profile, $storageInfo)
    {

        if (!is_array($profile) || !isset($profile['email'])) {
            RestoLogUtil::httpError(400, 'Cannot save user profile - invalid user identifier');
        }

        if ($this->userExists(array('email' => $profile['email']))) {
            RestoLogUtil::httpError(409, 'Cannot save user profile - user already exist');
        }

        /*
         * Normalize email
         */
        $email = trim(strtolower($profile['email']));

        /*
         * Detect base64 encoded picture
         */
        $picture = $this->getPicture($profile, $storageInfo);
       
        /*
         * Store everything
         */
        $toBeSet = array(
            'email' => '\'' . pg_escape_string($email) . '\'',
            'password' => '\'' . (isset($profile['password']) ? password_hash($profile['password'], PASSWORD_BCRYPT) : str_repeat('*', 60)) . '\'',
            'groups' => '\'{' . (isset($profile['groups']) ? pg_escape_string($profile['groups']) : Resto::GROUP_DEFAULT_ID) . '}\'',
            'topics' => isset($profile['topics']) ? '\'{' . pg_escape_string($profile['topics']) . '}\'' : 'NULL',
            'picture' => '\'' . pg_escape_string($picture) . '\'',
            'bio' => isset($profile['bio']) ? '\'' . pg_escape_string($profile['bio']) . '\'' : 'NULL',
            'activated' => $profile['activated'],
            'validatedby' => isset($profile['validatedby']) ? '\'' . $profile['validatedby'] .'\'' : 'NULL',
            'validationdate' => isset($profile['validatedby']) ? 'now()' : 'NULL',
            'registrationdate' => 'now()',
            'externalidp' => isset($profile['externalidp']) ? '\'' . pg_escape_string($profile['externalidp']) . '\'' : 'NULL'
        );
        foreach (array_values(array('name', 'firstname', 'lastname', 'country', 'organization', 'organizationcountry', 'flags', 'lang')) as $field) {
            if (isset($profile[$field])) {
                $toBeSet[$field] = "'" . pg_escape_string($profile[$field]) . "'";
            }
        }

        $results =  $this->dbDriver->fetch($this->dbDriver->query('INSERT INTO resto.user (' . join(',', array_keys($toBeSet)) . ') VALUES (' . join(',', array_values($toBeSet)) . ') RETURNING *'));

        return count($results) === 1 ? FormatUtil::fullUserProfile($results[0]) : null;
    }

    /**
     * Update user profile to database
     *
     * @param array $profile
     * @param array $storageInfo
     * @return integer (userid)
     * @throws exception
     */
    public function updateUserProfile($profile, $storageInfo)
    {
        if (!is_array($profile)) {
            RestoLogUtil::httpError(400);
        }

        /*
         * Case 1 - reset password through token
         */
        if (isset($profile['token']) && isset($profile['password'])) {
            $results = $this->dbDriver->fetch($this->dbDriver->pQuery('UPDATE resto.user SET password=$1 WHERE resettoken=$2 AND resetexpire > now() RETURNING id', array(
                password_hash($profile['password'], PASSWORD_BCRYPT),
                $profile['token']
            )));
            return count($results) === 1 ? $results[0]['id'] : null;
        }

        /*
         * Normal case - update based on email
         */
        elseif (isset($profile['email'])) {

            /*
             * The following parameters cannot be updated :
             *   - id
             *   - email
             *   - resettoken
             *   - resetexpire
             *   - registrationdate
             */
            $values = array();
            foreach (array_values(array('password', 'activated', 'bio', 'name', 'firstname', 'lastname', 'groups', 'country', 'organization', 'topics', 'organizationcountry', 'flags', 'lang', 'settings', 'picture', 'externalidp')) as $field) {
                if (isset($profile[$field])) {
                    switch ($field) {
                        case 'password':
                            $values[] = 'password=\'' . password_hash($profile['password'], PASSWORD_BCRYPT) . '\'';
                            break;
                        case 'activated':
                            $values[] = 'activated=' . $profile['activated'];
                            break;
                        case 'externalidp':
                        case 'settings':
                            $jsonEncoded = json_encode($profile[$field]);
                            if (is_object(json_decode($jsonEncoded))) {
                                $values[] = $field . '=\'' . pg_escape_string($jsonEncoded) . '\'';
                            } else {
                                RestoLogUtil::httpError(400);
                            }
                            break;
                        case 'groups':
                        case 'topics':
                            $values[] = $field . '=\'{' . pg_escape_string($profile[$field]) . '}\'';
                            break;
                        case 'picture':
                            $values[] = 'picture=\'' . pg_escape_string($this->getPicture(array('picture' => $profile['picture']), $storageInfo)) . '\'';
                            break;
                        default:
                            $values[] = $field . '=\'' . pg_escape_string($profile[$field]) . '\'';
                    }
                }
            }

            $results = array();
            if (count($values) > 0) {
                $results = $this->dbDriver->fetch($this->dbDriver->query('UPDATE resto.user SET ' . join(',', $values) . ' WHERE email=\'' . pg_escape_string(trim(strtolower($profile['email']))) . '\' RETURNING id'));
            }

            return count($results) === 1 ? $results[0]['id'] : null;
        } else {
            RestoLogUtil::httpError(400);
        }
    }

    /**
     * Update user reset token
     *
     * @param string $email
     * @param string $resettoken
     * @return integer (userid)
     * @throws exception
     */
    public function updateResetToken($email, $resettoken)
    {
        if (!isset($email) || !isset($resettoken)) {
            RestoLogUtil::httpError(400);
        }

        $values = [
            'resettoken=\'' . pg_escape_string($resettoken) . '\'',
            'resetexpire=(now() + \'1 hour\'::interval)'
        ];
                    
        $results = $this->dbDriver->fetch($this->dbDriver->query('UPDATE resto.user SET ' . join(',', $values) . ' WHERE email=\'' . pg_escape_string(trim(strtolower($email))) . '\' RETURNING id'));
        
        return count($results) === 1 ? $results[0]['id'] : null;
    }

    /**
     * Add groups to user $userid
     *
     * @param integer $userid
     * @param string $groups
     * @return null
     * @throws Exception
     */
    public function storeUserGroups($userid, $groups)
    {
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
    public function removeUserGroups($userid, $groups)
    {
        return $this->storeOrRemoveUserGroups('remove', $userid, $groups);
    }

    /**
     * Activate user
     *
     * @param string $userid
     * @param boolean $autoValidateUser
     *
     * @throws Exception
     */
    public function activateUser($userid, $autoValidateUser = false)
    {
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

        $query = 'UPDATE resto.user SET ' . join(',', $toBeSet) . ' WHERE id=' . pg_escape_string($userid) . ' RETURNING id';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));

        return count($results) === 1 ? true : false;
    }

    /**
     * Deactivate user
     *
     * @param string $userid
     * @throws Exception
     */
    public function deactivateUser($userid)
    {
        return count($this->dbDriver->fetch($this->dbDriver->pQuery('UPDATE resto.user SET activated=0 WHERE id=$1 RETURNING id', array($userid)))) === 1 ? true : false;
    }

    /**
     * Validate user
     *
     * @param string $userid
     * @param string $validatedBy
     * @return boolean
     */
    public function validateUser($userid, $validatedBy)
    {

        /*
         * Validate user.
         * If user is already validate, update date and validatedby.
         */
        $toBeSet = array(
            'validatedby=\'' . $validatedBy . '\'',
            'validationdate=now()'
        );

        $query = 'UPDATE resto.user SET ' . join(',', $toBeSet) . ' WHERE id=' . pg_escape_string($userid) . ' RETURNING id';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));

        return count($results) === 1 ? true : false;
    }

    /**
     * Unvalidate user
     *
     * @param string $userid
     * @return boolean
     */
    public function unvalidateUser($userid)
    {
        $toBeSet = array(
            'validatedby=NULL',
            'validationdate=NULL'
        );

        return count($this->dbDriver->fetch($this->dbDriver->query('UPDATE resto.user SET ' . join(',', $toBeSet) . ' WHERE id=' . pg_escape_string($userid) . ' RETURNING id'))) === 1 ? true : false;
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
    private function storeOrRemoveUserGroups($storeOrRemove, $userid, $groups)
    {
        if (!isset($userid)) {
            RestoLogUtil::httpError(400, 'Cannot ' . $storeOrRemove . ' groups - invalid user identifier : ' . $userid);
        }
        if (empty($groups)) {
            RestoLogUtil::httpError(400, 'Cannot ' . $storeOrRemove . ' groups - empty input groups');
        }

        $profile = $this->getUserProfile('id', $userid);
        if (!isset($profile)) {
            RestoLogUtil::httpError(404, 'Cannot ' . $storeOrRemove . ' groups - user profile not found for : ' . $userid);
        }

        /*
         * Explode existing groups into an associative array
         */
        $userGroups = !empty($profile['groups']) ? array_flip($profile['groups']) : array();

        /*
         * Explode input groups
         */
        $newGroups = array();
        $rawNewGroups = $groups;
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
         * Remove - note that Resto::GROUP_DEFAULT_ID group cannot be removed
         */ else {
            foreach (array_keys($newGroups) as $key) {
                if ($key !== Resto::GROUP_DEFAULT_ID) {
                    unset($userGroups[$key]);
                }
            }
            $newGroups = array_keys($userGroups);
        }

        /*
         * Update user profile
         */
        $results = count($newGroups) > 0 ? implode(',', $newGroups) : null;
        $this->dbDriver->fetch($this->dbDriver->query('UPDATE resto.user SET groups=' . (isset($results) ? '\'{' . pg_escape_string($results) . '}\'' : 'NULL') . ' WHERE id=' . $userid));

        return $results;
    }

    /**
     * Return picture url
     *
     * @param array $profile
     * @param array $storageInfo
     */
    private function getPicture($profile, $storageInfo = null)
    {

        // Create picture url from email
        if (!isset($profile['picture'])) {
            return 'https://robohash.org/' . md5($profile['email']) . '?gravatar=hashed&bgset=any&size=400x400';
            //return 'https://www.gravatar.com/avatar/' . md5($email) . '?d=mm&s=' . $size;
        }

        // Return picture from input picture url
        if (substr($profile['picture'], 0, 4) === 'http') {
            return $profile['picture'];
        }

        // Create and return picture url from base64 input picture
        if (isset($storageInfo) && isset($storageInfo['path'])) {
            $outputDir = $storageInfo['path'] . '/avatars';
            if (!is_dir($outputDir)) {
                mkdir($outputDir);
            }
            $picture = RestoUtil::storeBase64File($profile['picture'], $outputDir, ['jpg', 'png', 'jpeg', 'gif']);
            if (isset($picture)) {
                return $storageInfo['endpoint'] . '/avatars/' . $picture;
            }
        }

        return RestoLogUtil::httpError(400, 'Invalid picture');
    }
}
