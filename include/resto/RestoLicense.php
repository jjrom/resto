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

class RestoLicense {
    
    /*
     * Context
     */
    public $context;
    
    /*
     * Description
     */
    private $description;
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param array $description : license description
     */
    public function __construct($context, $description) {
        $this->context = $context;
        $this->description = $description;
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
        
        /*
         * Always be pessimistic :)
         */
        $fulfill = false;
        
        /**
         * No license restriction (e.g. 'unlicensed' license)
         * => Every user fulfill license requirements
         */
        if (!isset($this->description['grantedCountries']) && !isset($this->description['grantedOrganizationCountries']) && !isset($this->description['grantedFlags'])) {
            return true;
        }

        /**
         * Registered user profile should be validated
         */
        if ($user->profile['userid'] !== -1 && !isset($user->profile['validatedby'])) {
            RestoLogUtil::httpError(403, 'User profile has not been validated. Please contact an administrator');
        }

        /**
         * User profile should match at least one of the license granted flags 
         */
        if (isset($this->description['grantedFlags']))  {
           
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
        if (isset($this->description['grantedCountries']) && isset($user->profile['country']))  {
            $fulfill = $fulfill || $this->matches(array_map('trim', explode(',', $this->profile['country'])), array_map('trim', explode(',', $this->description['grantedCountries'])));
        }
        if (isset($this->description['grantedOrganizationCountries']) && isset($user->profile['organizationcountry']))  {
            $fulfill = $fulfill || $this->matches(array_map('trim', explode(',', $user->profile['organizationcountry'])), array_map('trim', explode(',', $this->description['grantedOrganizationCountries'])));
        }
        
        return $fulfill;
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
     * Sign license
     * 
     *  @param array $license
     */
    public function signLicense($license) {
        return $this->context->dbDriver->execute(RestoDatabaseDriver::SIGNATURE, array(
            'email' => $this->profile['email'],
            'licenseId' => $license['licenseId'],
            'signatureQuota' => $license['signatureQuota']
        ));
    }
    
    /**
     * Return license as an array with description in the current language
     */
    public function toArray() {
        $description = array();
        if (!isset($this->description['description'][$this->context->dictionary->language])) {
            if (isset($this->description['description']['en'])) {
                $description = $this->description['description']['en'];
            }
        }
        else {
            $description = $this->description['description'][$this->context->dictionary->language];
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

