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
 * RESTo PostgreSQL licenses functions
 */
class Functions_licenses {
    
    private $dbDriver = null;
    private $dbh = null;

    /**
     * Constructor
     *
     * @param $dbDriver
     */
    public function __construct($dbDriver) {
        $this->dbDriver = $dbDriver;
        $this->dbh = $dbDriver->dbh;
    }

    /**
     * Return license for $license_id
     *
     * @param string $license_id
     *
     * @return array
     * @throws exception
     */
    public function getLicenses($license_id = null) {
        $query = 'SELECT license_id, max_signatures, granted_nationalities, granted_org_nationalities, restriction_flags, once_for_all, public_visibility_wms, license_info FROM usermanagement.licenses' . (isset($license_id) ? ' WHERE license_id=\'' . pg_escape_string($license_id) . '\'' : '');
        $results = $this->dbDriver->query($query);

        $licenses = array();
        while ($license = pg_fetch_assoc($results)) {
            $licenses[] = array(
                'license_id' => $license['license_id'],
                'max_signatures' => (int) $license['max_signatures'],
                'granted_nationalities' => $license['granted_nationalities'],
                'granted_org_nationalities' => $license['granted_org_nationalities'],
                'restriction_flags' => $license['restriction_flags'],
                'once_for_all' => $license['once_for_all'] == 't',
                'public_visibility_wms' => $license['public_visibility_wms'] == 't',
                'license_info' => isset($license['license_info']) ? json_decode($license['license_info'], true) : null
            );
        }

        if (count($licenses) === 0 && ($license_id != null)) {
            RestoLogUtil::httpError(404);
        }

        return $licenses;
    }

    /**
     * Store license to database
     *     
     *     array(
     *          'license_id'      => short name
     *          'max_signatures'  => maximum number of signature for the license. -1 means there is no limit.
     *          'granted_nationalities' => comma separated list of value. eg. "FR,US,EN"
     *          'granted_org_nationalities' => comma separated list of values. eg. "FR,US,EN"
     *          'restriction_flags' => comma separated list of values. "PUBLIC_SERVICE, SCIENTIFIC_USAGE"
     *          'once_for_all' => // true or false
     *          'public_visibility_wms' => // true or false
     *          'license_info' => JSON format string with the following format.
     *              {
     *                 "en" : {
     *                      "short_name" : "xxxxx",
     *                      "license_url" : "https://xxxxxxx/xxx.txt"
     *                  },
     *                 "fr" : {
     *                      "short_name" : "xxxxx",
     *                      "license_url" : "https://xxxxxxx/xxx.txt"
     *                  }
     *             }
     *     )
     * 
     * @param array $license
     * @throws Exception
     */
    public function storeLicense($license) {

        if (!is_array($license) || !isset($license['license_id'])) {
            RestoLogUtil::httpError(500, 'Cannot save license - invalid license identifier');
        }
        if ($this->licenseExists($license['license_id'])) {
            RestoLogUtil::httpError(500, 'Cannot save license - license already exist');
        }

        $values = array(
            '\'' . pg_escape_string($license['license_id']) . '\'',
            $this->valueOrNull($license['max_signatures']),
            '\'' . pg_escape_string($license['granted_nationalities']) . '\'',
            '\'' . pg_escape_string($license['granted_org_nationalities']) . '\'',
            '\'' . pg_escape_string($license['restriction_flags']) . '\'',
            '\'' . pg_escape_string($license['once_for_all'] ? 't' : 'f') . '\'',
            '\'' . pg_escape_string($license['public_visibility_wms'] ? 't' : 'f') . '\'',
            isset($license['license_info']) ? '\'' . pg_escape_string(json_encode($license['license_info'])) . '\'' : 'NULL'
        );
        $results = $this->dbDriver->query('INSERT INTO usermanagement.licenses(license_id, max_signatures, granted_nationalities, granted_org_nationalities, restriction_flags, once_for_all, public_visibility_wms, license_info) VALUES (' . join(',', $values) . ')  RETURNING license_id');
        return pg_fetch_array($results);
    }

    /**
     * Delete license from database
     * 
     * @param string $licenseid
     *
     * @throws Exception
     */
    public function deleteLicense($licenseid) {

        if (!$this->licenseExists($licenseid)) {
            RestoLogUtil::httpError(400, 'Cannot delete license - '. $licenseid . ' does not exist');
        }
        try {
            $result = pg_query($this->dbh, 'DELETE from usermanagement.licenses WHERE license_id=\'' . pg_escape_string($licenseid) . '\'');
            if (!$result){
                throw new Exception;
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot delete license ' . $licenseid);
        }
    }

    /**
     * Check if license identified by $licenseId exists within database
     *
     * @param string $licenseId - license identifier
     *
     * @return boolean
     */
    public function licenseExists($licenseId) {
        $query = 'SELECT 1 FROM usermanagement.licenses WHERE license_id=\'' . pg_escape_string($licenseId) . '\'';
        $results = $this->dbDriver->fetch($this->dbDriver->query(($query)));
        return !empty($results);
    }


    /**
     * Check if license identified by $licenseId has reach its maximum number of signatures.
     * Returns true if the maximum is not reached
     *
     * @param string $licenseId - license identifier
     *
     * @return boolean
     */
    public function checkMaximumSignatures($licenseId) {

        $query = 'SELECT max_signatures FROM usermanagement.licenses WHERE license_id=\'' . pg_escape_string($licenseId) . '\'';
        $result = $this->dbDriver->fetch($this->dbDriver->query($query));
        $maxSig = $result[0]['max_signatures'];

        if ($maxSig != -1) {
            $query = 'SELECT count(*) FROM usermanagement.signatureslicense WHERE license_id=\'' . pg_escape_string($licenseId) . '\'';
            $result = $this->dbDriver->fetch($this->dbDriver->query($query));
            $currentNumber = $result[0]['count'];

            return $maxSig > $currentNumber;
        } else {
           return true;
        }
    }

    /**
     * Return $value or NULL
     * @param string $value
     */
    private function valueOrNull($value) {
        return isset($value) ? $value : 'NULL';
    }
}
