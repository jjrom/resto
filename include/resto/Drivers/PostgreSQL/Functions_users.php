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
    private $dbh = null;
    
    /**
     * Constructor
     * 
     * @param array $config
     * @param RestoCache $cache
     * @throws Exception
     */
    public function __construct($dbDriver) {
        $this->dbDriver = $dbDriver;
        $this->dbh = $dbDriver->dbh;
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
     * @return array : this function should return array('userid' => -1, 'groupname' => 'unregistered')
     *                 if user is not found in database
     * @throws exception
     */
    public function getUserProfile($identifier, $password = null) {
        
        /*
         * Unregistered users
         */
        if (!isset($identifier) || !$identifier || $identifier === 'unregistered') {
            RestoLogUtil::httpError(404);
        }
        
        $query = 'SELECT userid, email, md5(email) as userhash, groupname, username, givenname, lastname, to_char(registrationdate, \'YYYY-MM-DD"T"HH24:MI:SS"Z"\'), country, organization, topics, activated, grantedvisibility FROM usermanagement.users WHERE ' . $this->useridOrEmailFilter($identifier) . (isset($password) ? ' AND password=\'' . pg_escape_string(RestoUtil::encrypt($password)). '\'' : '');

        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        
        if (count($results) === 0) {
            RestoLogUtil::httpError(404);
        }
        
        $results[0]['activated'] = (integer) $results[0]['activated'];
        
        return $results[0];
        
    }

    /**
     * Get user legal info
     *
     * @param string $identifier : can be email (or string) or integer (i.e. uid)
     * @param string $password : if set then profile is returned only if password is valid
     * @return array : this function should return array('userid' => -1, 'groupname' => 'unregistered')
     *                 if user is not found in database
     * @throws exception
     */
    public function getUserLegalInfo($identifier, $password = null) {

        /*
         * Unregistered users
         */
        if (!isset($identifier) || !$identifier || $identifier === 'unregistered') {
            RestoLogUtil::httpError(404);
        }

        $query = 'SELECT uli.email, uli.nationality, uli.organization, uli.org_nationality, uli.flags, uli.validated_by, to_char(uli.validation_date, \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') validation_date' .
            ' FROM usermanagement.userslegalinfo uli, usermanagement.users u ' .
            ' WHERE uli.email=u.email AND ';
        if (ctype_digit($identifier)) {
            $query .= 'u.userid=' . $identifier;
        } else {
            $query .= 'u.email=\'' . pg_escape_string($identifier) . '\'';
        }
        if (isset($password)) {
            $query .= ' AND u.password=\'' . pg_escape_string(RestoUtil::encrypt($password)). '\'';
        }
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));

        if (count($results) === 0) {
            RestoLogUtil::httpError(404, 'User\'s legal information not found. Please send them to the administrator to proceed.');
        }

        return $results[0];
    }

    /**
     * Remove legal info for a user
     *
     * @param $identifier
     * @throws Exception
     */
    public function deleteUserLegalInfo($identifier) {

        /*
         * Try to find the user legal info and eventually raise a 404 HTTP error.
         */
        $legalInfo = $this->getUserLegalInfo($identifier);

        try {
            $result = pg_query($this->dbh, 'DELETE from usermanagement.userslegalinfo WHERE email=\'' . pg_escape_string($legalInfo['email']) . '\'');
            if (!$result){
                throw new Exception;
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot delete legal info for userid  ' . $identifier);
        }
    }

    /**
     * Get All users legal info
     *
     * @return array : this function should return array('userid' => -1, 'groupname' => 'unregistered')
     *                 if user is not found in database
     * @throws exception
     */
    public function getAllLegalInfo() {


        $query = 'SELECT uli.email, uli.nationality, uli.organization, uli.org_nationality, uli.flags, uli.validated_by, to_char(uli.validation_date, \'YYYY-MM-DD"T"HH24:MI:SS"Z"\') validation_date, u.userid  FROM usermanagement.userslegalinfo uli JOIN usermanagement.users u ON uli.email = u.email ';

        $results = $this->dbDriver->fetch($this->dbDriver->query($query));

        if (count($results) === 0) {
            RestoLogUtil::httpError(404);
        }

        return $results;
    }

    /**
     * Check if user legal info exists in the database
     *
     * @param string $email - user email
     *
     * @return boolean
     * @throws Exception
     */
    public function userLegalInfoExists($email) {
        $query = 'SELECT 1 FROM usermanagement.userslegalinfo WHERE email=\'' . pg_escape_string($email) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query(($query)));
        return !empty($results);
    }


    /**
     * Save user profile to database i.e. create new entry if user does not exist
     *
     * @param array $legalinfo
     * @throws exception
     */
    public function storeUserLegalInfo($legalinfo) {

        if (!is_array($legalinfo) || !isset($legalinfo['email'])) {
            RestoLogUtil::httpError(500, 'Cannot save user legal info - invalid user identifier');
        }
        if ($this->userLegalInfoExists($legalinfo['email'])) {
            RestoLogUtil::httpError(500, 'Cannot save user legal info - user already exist');
        }

        $email = trim(strtolower($legalinfo['email']));
        $values = "'" . pg_escape_string($email) . "',";
        foreach (array_values(array('nationality', 'organization', 'org_nationality', 'flags')) as $field) {
            $values .= (isset($legalinfo[$field]) ? "'". $legalinfo[$field] . "'" : 'NULL') . ",";
        }
        $values .= 'NULL, NULL';

        $results = $this->dbDriver->query('INSERT INTO usermanagement.userslegalinfo(email, nationality, organization, org_nationality, flags, validated_by, validation_date) VALUES (' . $values . ')');

        pg_fetch_array($results);

        return true;
    }

    /**
     * @param $admin       admin email who has validated the user legal info
     * @param $user_email  user email for the validated legal info
     *
     * @throws Exception
     */
    public function validateUserLegalInfo($admin, $user_email) {

        if (!isset($admin) || !isset($user_email)) {
            RestoLogUtil::httpError(500, 'Cannot validate user legal info - invalid user email : '. $user_email .' or invalid admin email : ' . $admin );
        }
        if (!$this->userLegalInfoExists($user_email)) {
            RestoLogUtil::httpError(500, 'Cannot validate user legal info - user doesn\'t exist');
        }
        $results = $this->dbDriver->fetch($this->dbDriver->query('UPDATE usermanagement.userslegalinfo SET validated_by=\'' . $admin . '\', validation_date=now() WHERE email=\'' . pg_escape_string(trim(strtolower($user_email))) .'\' RETURNING email'));

        return count($results) === 1 ? $results[0]['email'] : null;
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
        $results = $this->dbDriver->fetch($this->dbDriver->query(($query)));
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
        $email = trim(strtolower($profile['email']));
        $values = "'" . pg_escape_string($email) . "',";
        $values .= "'" . (isset($profile['password']) ? RestoUtil::encrypt($profile['password']) : str_repeat('*', 40)) . "',";
        $values .= "'" . (isset($profile['groupname']) ? pg_escape_string($profile['groupname']) : 'default') . "',";
        foreach (array_values(array('username', 'givenname', 'lastname', 'country', 'organization', 'topics', 'grantedvisibility')) as $field) {
            $values .= (isset($profile[$field]) ? "'". $profile[$field] . "'" : 'NULL') . ",";
        }
        $values .= "'" . pg_escape_string(RestoUtil::encrypt($email . microtime())) . "',";
        $values .= $profile['activated'] . ',now()';
        
        // TODO change to pg_fetch_assoc ?
        $results = $this->dbDriver->query('INSERT INTO usermanagement.users (email,password,groupname,username,givenname,lastname,country,organization,topics,grantedvisibility,activationcode,activated,registrationdate) VALUES (' . $values . ') RETURNING userid, activationcode');
        return pg_fetch_array($results);
        
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
         * Only password, groupname and activated fields can be updated
         */
        $values = array();
        if (isset($profile['password'])) {
            $values[] = 'password=\'' . RestoUtil::encrypt($profile['password']) . '\'';
        }
        if (isset($profile['groupname'])) {
            $values[] = 'groupname=\'' . pg_escape_string($profile['groupname']) . '\'';
        }
        if (isset($profile['activated'])) {
            $values[] = 'activated=' . $profile['activated'];
        }

        $values[] = 'grantedvisibility=' . (isset($profile['grantedvisibility']) ?  '\'' . pg_escape_string($profile['grantedvisibility']) . '\'' : 'NULL');
        
        $results = $this->dbDriver->fetch($this->dbDriver->query('UPDATE usermanagement.users SET ' . join(',', $values) . ' WHERE email=\'' . pg_escape_string(trim(strtolower($profile['email']))) .'\' RETURNING userid'));
        
        return count($results) === 1 ? $results[0]['userid'] : null;
        
    }

    /**
     * Add granted visibility to user $userid
     * @param $userid
     * @param $visibility
     * @return null
     * @throws Exception
     */
    public function storeVisibility($userid, $visibility) {
        return $this->storeOrDeleteVisibility('store', $userid, $visibility);
    }
    
    /**
     * Remove granted visibility to user $userid
     * @param $userid
     * @param $visibility
     * @return null
     * @throws Exception
     */
    public function deleteVisibility($userid, $visibility) {
        return $this->storeOrDeleteVisibility('delete', $userid, $visibility);
    }

    /**
     * Return true if token is revoked
     * 
     * @param string $token
     */
    public function isTokenRevoked($token) {
        $query = 'SELECT 1 FROM usermanagement.revokedtokens WHERE token= \'' . pg_escape_string($token) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query(($query)));
        return !empty($results);
    }

    /**
     * Revoke token
     * 
     * @param string $token
     */
    public function revokeToken($token) {
        if (isset($token) && !$this->isTokenRevoked($token)) {
            $this->dbDriver->query('INSERT INTO usermanagement.revokedtokens (token) VALUES(\'' . pg_escape_string($token) . '\')');
        }
        return true;
    }

    /**
     * Check if user signed collection license
     * 
     * @param string $identifier
     * @param string $collectionName
     * 
     * @return boolean
     */
    public function isLicenseSigned($identifier, $collectionName) {
        $query = 'SELECT 1 FROM usermanagement.signatures WHERE email= \'' . pg_escape_string($identifier) . '\' AND collection= \'' . pg_escape_string($collectionName) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query(($query)));
        return !empty($results);
    }
    
    /**
     * Sign license for collection collectionName
     * 
     * @param string $identifier : user identifier 
     * @param string $collectionName
     * @return boolean
     * @throws Exception
     */
    public function signLicense($identifier, $collectionName) {
        
        if (!$this->dbDriver->check(RestoDatabaseDriver::COLLECTION, array(
            'collectionName' => $collectionName
        ))) {
            RestoLogUtil::httpError(500, 'Cannot sign license');
        }
        $results = $this->dbDriver->query('SELECT email FROM usermanagement.signatures WHERE email=\'' . pg_escape_string($identifier) . '\' AND collection=\'' . pg_escape_string($collectionName) . '\'');
        if (pg_fetch_assoc($results)) {
            $this->dbDriver->query('UPDATE usermanagement.signatures SET signdate=now() WHERE email=\'' . pg_escape_string($identifier) . '\' AND collection=\'' . pg_escape_string($collectionName) . '\'');
        }
        else {
            $this->dbDriver->query('INSERT INTO usermanagement.signatures (email, collection, signdate) VALUES (\'' . pg_escape_string($identifier) . '\',\'' . pg_escape_string($collectionName) . '\',now())');
        }
        return true;
    }

    /**
     * Return all user signed product licenses ordered by signature date descendant. The lastest first.
     *
     * @param $email
     * @return array
     */
    public function getLicensesProductSignatures($email) {
        $query = 'SELECT * FROM usermanagement.signatureslicense  WHERE email= \'' . pg_escape_string($email) . '\' ORDER BY signature_date DESC';
        $results = $this->dbDriver->fetch($this->dbDriver->query(($query)));

        return  $results;
    }

    /**
     * Check if user signed product license
     *
     * @param $email
     * @param $licenseid
     * @return bool
     *
     */
    public function hasToSignProductLicense($email, $licenseid) {
        $query = 'SELECT l.license_id, l.once_for_all, s.signature_date FROM usermanagement.signatureslicense s, usermanagement.licenses l WHERE l.license_id=s.license_id AND s.email= \'' . pg_escape_string($email) . '\' AND l.license_id= \'' . pg_escape_string($licenseid) . '\' ORDER BY s.signature_date DESC';
        $results = $this->dbDriver->fetch($this->dbDriver->query(($query)));

        // Required is true if the signatureslicense table doesn't contains any signature for this licence and this user.
        $required = empty($results);

        // Or if the licence has a 'once_for_all' parameter equals to false.
        $required = $required || ($results[0]['once_for_all'] == "f" && time() - strtotime($results[0]['signature_date']) > 4);

        return  $required;
    }

    /**
     * Check if the user is habilitated to sign the product license
     *
     * @param $email
     * @param $licenseid
     * @return bool
     */
    public function isHabilitedToSignProductLicense($email, $licenseid) {

        $query = 'SELECT granted_nationalities, granted_org_nationalities, restriction_flags FROM usermanagement.licenses WHERE license_id=\'' . pg_escape_string($licenseid) . '\'';
        $result = $this->dbDriver->fetch($this->dbDriver->query($query));
        $licenseRestrictions = $result[0];

        /**
         * If there is no restriction with the license, return true
         */
        if ($licenseRestrictions['granted_nationalities'] == null &&
            $licenseRestrictions['granted_org_nationalities'] == null &&
            $licenseRestrictions['restriction_flags'] == null) {
            return true;
        }

        /**
         * At least one restriction is bound to the license
         */
        $legalInfo = $this->getUserLegalInfo($email);

        /**
         * If the legal info is not set
         */
        if (!isset($legalInfo)) {
            RestoLogUtil::httpError(403, 'User legal information is missing.');
        }

        /**
         * Legal info has not been validated by an admin
         */
        if ($legalInfo['validated_by'] == null) {
            RestoLogUtil::httpError(403, 'User legal information not validated by an administrator. Please contact an administrator');
        }

        /**
         * Start with restriction flag is exist
         */
        if ($licenseRestrictions['restriction_flags'] != null)  {

            /**
             * User must fullfil at least one flag...
             */
            if ($legalInfo['flags'] == null) {
                return false;
            }

            $restriction_flags = $this->getArray(",", $licenseRestrictions['restriction_flags']);
            $habilited_flags = false;
            if ($legalInfo['flags'] != null) {
                $flags = $this->getArray(",", $legalInfo['flags']);
                foreach (array_values($flags) as $flag) {
                    $habilited_flags = $habilited_flags || (array_search($flag, $restriction_flags) !== false);
                }
            }

            /**
             * User must fullfil at least one flag...
             */
            if ($habilited_flags === false) {
                return false;
            }
        }

        $habilited = false;
        if ($licenseRestrictions['granted_nationalities'] != null && $legalInfo['nationality'] != null) {
            $granted_nationalities = $this->getArray(",", $licenseRestrictions['granted_nationalities']);
            $habilited = $habilited || (array_search($legalInfo['nationality'], $granted_nationalities) !== false);
        }

        if ($licenseRestrictions['granted_org_nationalities'] != null && $legalInfo['org_nationality'] != null) {
            $granted_org_nationalities = $this->getArray(",", $licenseRestrictions['granted_org_nationalities']);
            $habilited = $habilited || (array_search($legalInfo['org_nationality'], $granted_org_nationalities) !== false);
        }

        return $habilited;
    }

    /**
     * Transform a string of values separated by $sep into an array of trimmed values.
     *
     * @param $sep
     * @param $string
     * @return array
     */
    private function getArray($sep, $string) {
        // Explode string to an array and trim its values
        $trimmed_array = array_map('trim', explode($sep, $string));
        return $trimmed_array;
    }

    /**
     * Sign a product license
     *
     * @param $email
     * @param $licenseid
     * @return bool
     * @throws Exception
     */
    public function signProductLicense($email, $licenseid) {

        if (!$this->dbDriver->check(RestoDatabaseDriver::PRODUCT_LICENSE, array('license_id' => $licenseid))) {
            RestoLogUtil::httpError(400, 'Cannot sign license : license not found');
        }

        if (!$this->hasToSignProductLicense($email, $licenseid)) {
            RestoLogUtil::httpError(400, 'Cannot sign license : license already signed');
        }

        $values = "'" . pg_escape_string($email) . "',";
        $values .= "'" . pg_escape_string($licenseid) . "',";
        $values .= 'now()';

        $this->dbDriver->query('INSERT INTO usermanagement.signatureslicense(email, license_id, signature_date) VALUES (' . $values . ')');
        return true;
    }

    /**
     * Activate user
     * 
     * @param string $userid : can be userid or base64(email)
     * @param string $activationcode
     * 
     * @throws Exception
     */
    public function activateUser($userid, $activationcode = null) {
        $query = 'UPDATE usermanagement.users SET activated=1 WHERE userid=\'' . pg_escape_string($userid) . '\'' . (isset($activationcode) ? ' AND activationcode=\'' . pg_escape_string($activationcode) . '\'' :'') . ' RETURNING userid';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        if (count($results) === 1) {
            return true;
        }
        return false;
    }
    
    /**
     * Deactivate user
     * 
     * @param string $userid
     * @throws Exception
     */
    public function deactivateUser($userid) {
        $query = 'UPDATE usermanagement.users SET activated=0 WHERE userid=\'' . pg_escape_string($userid) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query($query));
        if (count($results) === 1) {
            return true;
        }
        return false;
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
     * Add granted visibility to user $userid
     * 
     * @param string $storeOrDelete
     * @param integer $userid
     * @param string $visibility
     * @return null
     * @throws Exception
     */
    private function storeOrDeleteVisibility($storeOrDelete, $userid, $visibility) {
        
        if (!isset($userid)) {
            RestoLogUtil::httpError(500, 'Cannot ' . $storeOrDelete . ' granted visibility - invalid user identifier:'. $userid);
        }
        if (!isset($visibility)) {
            RestoLogUtil::httpError(500, 'Cannot ' . $storeOrDelete . ' granted visibility - invalid visibility :'. $visibility);
        }

        $profile = $this->getUserProfile($userid);
        if (!isset($profile)) {
            RestoLogUtil::httpError(500, 'Cannot ' . $storeOrDelete . ' granted visibility - user profile not found for :'. $userid);
        }

        $grantedvisibility = $profile['grantedvisibility'];
        
        /*
         * Explode existing grantedvisibility into an array
         */
        $visibilities = array();
        if ($grantedvisibility) {
            $visibilities = explode(',', $grantedvisibility);
        }
        
        /*
         * Explode new visibilities (i.e. input $visibility)
         */
        $newVisibilities = explode(',', $visibility);
        
        /*
         * From input, only add non existing visibilities
         */
        $count = count($visibilities);
        for ($i = 0, $ii = count($newVisibilities); $i < $ii; $i++) {
            $new = trim($newVisibilities[$i]);
            if ($new === '') {
                continue;
            }
            $index = -1;
            for ($j = 0, $jj = $count; $j < $jj; $j++) {
                if (!isset($visibilities[$j])) {
                    continue;
                }
                $existing = trim($visibilities[$j]);
                if ($existing === $new) {
                    $index = $j;
                    break;
                }
            }
            if ($storeOrDelete === 'store' && $index === -1) {
                $visibilities[] = $new;
            }
            else if ($storeOrDelete === 'delete' && $index !== -1) {
                unset($visibilities[$index]);
            }
        }
        
        // Update user profile
        $results = $this->dbDriver->fetch($this->dbDriver->query('UPDATE usermanagement.users SET grantedvisibility=\'' . pg_escape_string(implode(',', $visibilities)) . '\' WHERE userid=\''. $userid .'\' RETURNING userid'));
        return count($results) === 1 ? $results[0]['userid'] : null;
        
    }
    
}
