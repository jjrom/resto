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
 *  @SWG\Tag(
 *      name="license",
 *      description="License attached to a collection or a feature"
 *  )
 */
class RestoLicense {
    /*
     * Context
     */

    public $context;

    /*
     * Identifier
     */
    public $licenseId;

    /*
     * Description
     */
    private $description;

    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param string $licenseId : license identifier
     * @param boolean autoload : true to load from store
     */
    public function __construct($context, $licenseId, $autoload = true) {

        if (!isset($licenseId)) {
            RestoLogUtil::httpError(400, 'License identifier is not set');
        }

        $this->context = $context;
        $this->licenseId = $licenseId;

        if ($autoload) {
            $this->loadFromStore();
        }
    }

    /**
     * Set license description
     * 
     * @param array $description
     * @param $synchronize : true to synchronize to database
     */
    public function setDescription($description, $synchronize = true) {
        $this->description = $description;
        if ($synchronize) {
            $this->saveToStore();
        }
    }

    /**
     * Load license from database - throw exception if not found
     */
    public function loadFromStore() {
        $licenses = $this->context->dbDriver->get(RestoDatabaseDriver::LICENSES, array('licenseId' => $this->licenseId));
        if (!isset($licenses[$this->licenseId])) {
            RestoLogUtil::httpError(400, 'License ' . $this->licenseId . ' does not exist in database');
        }
        $this->description = $licenses[$this->licenseId];
    }

    /**
     * Store license to database
     */
    public function saveToStore() {
        $this->context->dbDriver->store(RestoDatabaseDriver::LICENSE, array(
            'license' => array(
                'licenseId' => $this->licenseId,
                'grantedCountries' => isset($this->description['grantedCountries']) ? $this->description['grantedCountries'] : null,
                'grantedOrganizationCountries' => isset($this->description['grantedOrganizationCountries']) ? $this->description['grantedOrganizationCountries'] : null,
                'grantedFlags' => isset($this->description['grantedFlags']) ? $this->description['grantedFlags'] : null,
                'viewService' => isset($this->description['viewService']) ? $this->description['viewService'] : null,
                'hasToBeSigned' => isset($this->description['hasToBeSigned']) ? $this->description['hasToBeSigned'] : null,
                'signatureQuota' => isset($this->description['signatureQuota']) ? $this->description['signatureQuota'] : -1,
                'description' => isset($this->description['description']) ? $this->description['description'] : null
            ))
        );
    }

    /**
     * Check if $user fulfill license requirements 
     * 
     * To be fulfilled, the user profile :
     *  - should be validated
     *  - should match at least one of the granteFlags of the license
     *  - should match at least one of the grantedCountries or the grantedOrganizationCountries of the license
     * 
     * @param RestoUser $user
     */
    public function isApplicableToUser($user) {

        /**
         * No license restriction (e.g. 'unlicensed' license)
         * => Every user fulfill license requirements
         */
        if (!isset($this->description['grantedCountries']) && !isset($this->description['grantedOrganizationCountries']) && !isset($this->description['grantedFlags'])) {
            return true;
        }

        /**
         * User profile should match at least one of the license granted flags 
         */
        if (isset($this->description['grantedFlags'])) {

            /*
             * Registered user has automatically the REGISTERED flag
             * (see 'unlicensedwithregistration' license)
             */
            $userFlags = !empty($user->profile['flags']) ? array_map('trim', explode(',', $user->profile['flags'])) : array();
            if ($user->profile['userid'] !== -1) {
                $userFlags[] = 'REGISTERED';
            }

            /*
             * No match => no fulfill
             */
            if (!$this->matches($userFlags, array_map('trim', explode(',', $this->description['grantedFlags'])))) {
                return false;
            }
        }

        /**
         * User profile should match either one of the license granted countries or organization countries
         */
        if (isset($this->description['grantedCountries']) || isset($this->description['grantedOrganizationCountries'])) {
            $fulfill = false;
            if (isset($this->description['grantedCountries']) && isset($user->profile['country'])) {
                $fulfill = $fulfill || $this->matches(array_map('trim', explode(',', $user->profile['country'])), array_map('trim', explode(',', $this->description['grantedCountries'])));
            }
            if (isset($this->description['grantedOrganizationCountries']) && isset($user->profile['organizationcountry'])) {
                $fulfill = $fulfill || $this->matches(array_map('trim', explode(',', $user->profile['organizationcountry'])), array_map('trim', explode(',', $this->description['grantedOrganizationCountries'])));
            }
            return $fulfill;
        }
        return true;
    }

    /**
     * Check if $user has to sign license
     * 
     * @param RestoUser $user
     */
    public function hasToBeSignedByUser($user) {

        /*
         * No need to sign for 'never' 
         */
        if ($this->description['hasToBeSigned'] === 'never') {
            return false;
        }

        /*
         * Always need to sign for 'always'
         */
        if ($this->description['hasToBeSigned'] === 'always') {
            return true;
        }

        /*
         * Otherwise check if license has been signed once
         */
        return !$this->context->dbDriver->check(RestoDatabaseDriver::SIGNATURE, array(
                    'email' => $user->profile['email'],
                    'licenseId' => $this->description['licenseId']
        ));
    }

    /**
     * Return license as an array with description in the current language
     */
    public function toArray() {
        $description = array();
        /*
         * Test if lang is already selected
         * 
         * 1. If not : apply lang selection
         * 2. If yes : return description without selecting lang
         * 3. If descritpion does not exist return empty array
         */
        if (isset($this->description['description']) && !isset($this->description['description']['url'])) {
            if (!isset($this->description['description'][$this->context->dictionary->language])) {
                if (isset($this->description['description']['en'])) {
                    /*
                     * Description does not exist in prefered lang, but exists in english
                     */
                    $description = $this->description['description']['en'];
                }
            } else {
                /*
                 * Description exists in prefered lang
                 */
                $description = $this->description['description'][$this->context->dictionary->language];
            }
        } else if (isset($this->description['description']) && isset($this->description['description']['url'])) {
            /*
             * Description is already in prefered language
             */
            $description = $this->description['description'];
        }

        return array(
            'licenseId' => $this->description['licenseId'],
            'hasToBeSigned' => $this->description['hasToBeSigned'],
            'grantedCountries' => $this->description['grantedCountries'],
            'grantedOrganizationCountries' => $this->description['grantedOrganizationCountries'],
            'grantedFlags' => $this->description['grantedFlags'],
            'viewService' => $this->description['viewService'],
            'signatureQuota' => $this->description['signatureQuota'],
            'description' => $description
        );
    }

    /**
     * Return true if there is at least one match between user and license grant
     * 
     * @param array $userGrant
     * @param array $licenseGrant
     * @return type
     */
    private function matches($userGrant, $licenseGrant) {
        $match = false;
        foreach (array_values($userGrant) as $grant) {
            $match = $match || (array_search($grant, $licenseGrant) !== false);
        }
        return $match;
    }

}
