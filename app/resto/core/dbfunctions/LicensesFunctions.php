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
 * RESTo PostgreSQL licenses functions
 */
class LicensesFunctions
{
    private $dbDriver = null;

    /**
     * Constructor
     *
     * @param RestoDatabaseDriver $dbDriver
     */
    public function __construct($dbDriver)
    {
        $this->dbDriver = $dbDriver;
    }

    /**
     * Return license for $licenseId
     *
     * @param string $licenseId
     *
     * @return array
     * @throws exception
     */
    public function getLicense($licenseId)
    {
        
        $license = null;
        $results = $this->dbDriver->pQuery('SELECT licenseid, grantedcountries, grantedorganizationcountries, grantedflags, viewservice, hastobesigned, signaturequota, description FROM resto.license WHERE licenseid=$1', array($licenseId));
        while ($row = pg_fetch_assoc($results)) {
            $license = array(
                'licenseId' => $row['licenseid'],
                'grantedCountries' => $row['grantedcountries'],
                'grantedOrganizationCountries' => $row['grantedorganizationcountries'],
                'grantedFlags' => $row['grantedflags'],
                'viewService' => $row['viewservice'],
                'hasToBeSigned' => $row['hastobesigned'],
                'signatureQuota' => (integer) $row['signaturequota'],
                'description' => isset($row['description']) ? json_decode($row['description'], true) : null
            );
        }

        if (! isset($license)) {
            return RestoLogUtil::httpError(404);
        }

        return $license;
    }

    /**
     * Return licenses
     *
     * @return array
     * @throws exception
     */
    public function getLicenses()
    {
        
        $licenses = array();
        
        $results = $this->dbDriver->query('SELECT licenseid, grantedcountries, grantedorganizationcountries, grantedflags, viewservice, hastobesigned, signaturequota, description FROM resto.license' . (isset($licenseId) ? ' WHERE licenseid=\'' . pg_escape_string($licenseId) . '\'' : ''));
        
        while ($row = pg_fetch_assoc($results)) {
            $licenses[$row['licenseid']] = array(
              'licenseId' => $row['licenseid'],
              'grantedCountries' => $row['grantedcountries'],
              'grantedOrganizationCountries' => $row['grantedorganizationcountries'],
              'grantedFlags' => $row['grantedflags'],
              'viewService' => $row['viewservice'],
              'hasToBeSigned' => $row['hastobesigned'],
              'signatureQuota' => (integer) $row['signaturequota'],
              'description' => isset($row['description']) ? json_decode($row['description'], true) : null
            );
        }

        return $licenses;
    }


    /**
     * Store license to database
     *
     *     array(
     *          'licenseId'      => short name
     *          'grantedCountries' => Comma separated list of isoa2 list of allowed user countries eg. "FR,US,EN"
     *          'grantedOrganizationcountries' => Comma separated list of isoa2 list of allowed user's organization countries eg. "FR,US,EN"
     *          'grantedFlags' => Comma separated list of flags allowed e.g. "PUBLIC_SERVICE, SCIENTIFIC_USAGE"
     *          'viewService' => Enumeration : 'public', 'private'
     *          'hasToBeSigned' => Enumeration : 'never', 'once', 'always'
     *          'signatureQuota' => Maximum of signatures allowed if hastobesigned = 'always' (-1 means unlimited)
     *          'description' => JSON format string with the following format.
     *              {
     *                 "en" : {
     *                      "shortName" : "xxxxx",
     *                      "url" : "https://xxxxxxx/xxx.txt"
     *                  },
     *                 "fr" : {
     *                      "shortName" : "xxxxx",
     *                      "url" : "https://xxxxxxx/xxx.txt"
     *                  }
     *             }
     *     )
     *
     * @param array $license
     * @throws Exception
     */
    public function storeLicense($license)
    {
        if (!is_array($license) || !isset($license['licenseId'])) {
            RestoLogUtil::httpError(400, 'Cannot save license - invalid license identifier');
        }
        if ($this->licenseExists($license['licenseId'])) {
            RestoLogUtil::httpError(409, 'Cannot save license - license already exist');
        }

        $values = array(
            'licenseid' => '\'' . pg_escape_string($license['licenseId']) . '\'',
            'grantedcountries' => '\'' . pg_escape_string($license['grantedCountries']) . '\'',
            'grantedorganizationcountries' => '\'' . pg_escape_string($license['grantedOrganizationCountries']) . '\'',
            'grantedflags' => '\'' . pg_escape_string($license['grantedFlags']) . '\'',
            'viewservice' => '\'' . pg_escape_string($license['viewService']) . '\'',
            'hastobesigned' => '\'' . pg_escape_string($license['hasToBeSigned']) . '\'',
            'signaturequota' => pg_escape_string($license['signatureQuota']),
            'description' => isset($license['description']) ? '\'' . pg_escape_string(json_encode($license['description'])) . '\'' : 'NULL'
        );
        $results = $this->dbDriver->query('INSERT INTO resto.license(' . join(',', array_keys($values)) . ') VALUES (' . join(',', array_values($values)) . ')  RETURNING licenseid');
        return pg_fetch_array($results);
    }

    /**
     * Remove license from database
     *
     * @param string $licenseId
     *
     * @throws Exception
     */
    public function removeLicense($licenseId)
    {
        if (!$this->licenseExists($licenseId)) {
            RestoLogUtil::httpError(400, 'Cannot delete license - '. $licenseId . ' does not exist');
        }
        try {
            $result = pg_query_params($this->dbDriver->getConnection(), 'DELETE from resto.license WHERE licenseid=$1', array($licenseId));
            if (!$result) {
                throw new Exception();
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot delete license ' . $licenseId);
        }

        return true;
    }

    /**
     * Check if license identified by $licenseId exists within database
     *
     * @param string $licenseId - license identifier
     *
     * @return boolean
     */
    public function licenseExists($licenseId)
    {
        return !empty($this->dbDriver->fetch($this->dbDriver->pQuery('SELECT 1 FROM resto.license WHERE licenseid=$1', array($licenseId))));
    }

    /**
     * Check if user signed license identified by $licenseId
     *
     * @param string $identifier
     * @param string $licenseId
     *
     * @return boolean
     */
    public function isLicenseSigned($identifier, $licenseId)
    {
        return !empty($this->dbDriver->fetch($this->dbDriver->pQuery('SELECT 1 FROM resto.signature WHERE userid=$1 AND licenseid=$2', array($identifier, $licenseId))));
    }

    /**
     * Sign license identified by $licenseId
     * If license was already signed, add 1 to the signatures counter
     *
     * @param string $identifier : user identifier
     * @param string $licenseId
     * @param integer $signatureQuota
     * @return boolean
     * @throws Exception
     */
    public function signLicense($identifier, $licenseId, $signatureQuota = -1)
    {

        /*
         * Get previous signature
         */
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT userid, counter FROM resto.signature WHERE userid=$1 AND licenseid=$2', array(
            $identifier,
            $licenseId
        )));

        /*
         * Sign license
         */
        if (count($results) === 0) {
            $this->dbDriver->pQuery('INSERT INTO resto.signature (userid, licenseid, signed, counter) VALUES ($1, $2 ,now(), 1)', array(
                $identifier,
                $licenseId
            ));
        }
        /*
         * Update signatures counter (check quota first)
         */
        else {
            if ($signatureQuota !== -1) {
                if ((integer) $results[0]['counter'] >= $signatureQuota) {
                    RestoLogUtil::httpError(403, 'Maximum signature quota exceed for this license');
                }
            }
            $this->dbDriver->pQuery('UPDATE resto.signature SET signed=now(),counter=counter+1 WHERE userid=$1 AND licenseid=$2', array(
                $identifier, $licenseId
            ));
        }

        return true;
    }

    /**
     * Return licenses signatures for user $identifier
     *
     * @param string $identifier
     * @param string $licenseId
     *
     * @return array
     * @throws Exception
     */
    public function getSignatures($identifier, $licenseId = null)
    {
        $signatures = array();
        if (isset($licenseId)) {
            $results = $this->dbDriver->pQuery('SELECT userid, licenseid, to_iso80601(signed) as signed, counter FROM resto.signature WHERE userid=$1 AND licenseid=$2', array(
                $identifier,
                $licenseId
            ));
        } else {
            $results = $this->dbDriver->pQuery('SELECT userid, licenseid, to_iso80601(signed) as signed, counter FROM resto.signature WHERE userid=$1', array(
                $identifier
            ));
        }
        while ($row = pg_fetch_assoc($results)) {
            $signatures[] = array(
                'licenseId' => $row['licenseid'],
                'lastSigned' => $row['signed'],
                'counter' => (integer) $row['counter'],

            );
        }
        return $signatures;
    }
}
