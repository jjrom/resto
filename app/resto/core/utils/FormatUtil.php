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
 * resto output format
 */
class FormatUtil
{

    /**
     * Prepare SQL query for intervals
     *
     *  
     *      $str = n1 then returns value = n1
     *      $str = {n1,n2} then returns  value = n1 or value = n2
     *      $str = [n1,n2] then returns  n1 ≤ value ≤ n2
     *      $str = [n1,n2[ then returns  n1 ≤ value < n2
     *      $str = ]n1,n2[ then returns  n1 < value < n2
     *      $str = ]n1 then returns n1 < value
     *      $str = [n1 then returns  n1 ≤ value
     *      $str = n1[ then returns value < n2
     *      $str = n1] then returns value ≤ n2
     *
     * @param string $str
     * @param string $columnName
     * @return string
     */
    public static function intervalToQuery($str, $columnName) 
    {
        
        $values = explode(',', $str);

        /*
         * No ',' present i.e. simple equality or non closed interval
         */
        if (count($values) === 1) {
            return FormatUtil::processSimpleInterval($columnName, trim($values[0]));
        }
        /*
         * Assume two values
         */
        return FormatUtil::processComplexInterval($columnName, $values);
        
    }

    /**
     * Format facet for output
     *
     * @param array $rawFacet
     */
    public static function facet($rawFacet)
    {
        return array(
            'id' => $rawFacet['id'],
            'collection' => $rawFacet['collection'] ?? '*',
            'value' => $rawFacet['value'],
            'parentId' => $rawFacet['pid'] ?? null,
            'created' => $rawFacet['created'],
            'creator' => $rawFacet['creator'] ?? null,
            'count' => (integer) $rawFacet['counter']
        );
    }

    /**
     * Return a full formated profile info
     *
     * @param array $rawProfile
     */
    public static function fullUserProfile($rawProfile)
    {
        
        // Empty profile
        $profile = array();

        foreach ($rawProfile as $key => $value) {
            switch ($key) {

                // Never display these one
                case 'password':
                case 'resettoken':
                case 'resetexpire':
                case 'validationdate':
                case 'validatedby':
                    break;

                case 'followed':
                case 'followme':
                    $profile[$key] = $value === 't' ? true : false;
                    break;

                case 'activated':
                case 'likes':
                case 'comments':
                case 'followers':
                case 'followings':
                    $profile[$key] = (integer) $value;
                    break;

                case 'groups':
                    $profile[$key] = array_map('intval', explode(",", substr($rawProfile['groups'], 1, -1)));
                    break;

                case 'topics':
                    $profile[$key] = isset($rawProfile['topics']) ? substr($rawProfile['topics'], 1, -1) : null;
                    break;

                case 'id':
                case 'email':
                case 'name':
                case 'firstname':
                case 'lastname':
                case 'bio':
                case 'lang':
                case 'country':
                case 'organization':
                case 'organizationcountry':
                case 'flags':
                case 'owner':
                case 'picture':
                case 'registrationdate':
                    if (isset($value)) {
                        $profile[$key] = $value;
                    }
                    break;

                case 'settings':
                    $settings = isset($rawProfile['settings']) ? json_decode($rawProfile['settings'], true) : null;
                    if (isset($settings)) {
                        $profile[$key] = $settings;
                    }
                    break;

                // Additionnal profile info are in JSON
                default:
                    if (isset($value)) {
                        $profile[$key] = json_decode($value, true);
                    }

            }
        }
        
        return $profile;
    }

    /**
     * Return a partial formated profile info
     *
     * @param array $rawProfile
     */
    public static function partialUserProfile($rawProfile)
    {

        // Remove leading "{" and trailing "}" for INTEGER[] (Database returns {group1,group2,etc.})
        $groups = array_map('intval', explode(",", substr($rawProfile['groups'], 1, -1)));
        
        // [SECURITY] Never return users with only one group and group < 100
        if (count($groups) === 1 && $groups[0] < 100) {
            return null;
        }
        
        $profile = array(
            'id' => $rawProfile['id'],
            'picture' => $rawProfile['picture'],
            'groups' => $groups,
            'name' => $rawProfile['name'],
            'registrationdate' => $rawProfile['registrationdate'],
            'followers' => (integer) $rawProfile['followers'],
            'followings' => (integer) $rawProfile['followings']
        );

        foreach ($rawProfile as $key => $value) {
            switch ($key) {
                case 'settings':
                    $settings = isset($rawProfile['settings']) ? json_decode($rawProfile['settings'], true) : null;
                    if (isset($settings) && $settings['showIdentity']) {
                        $profile['firstname'] = $rawProfile['firstname'];
                        $profile['lastname'] = $rawProfile['lastname'];
                    }
                    if (isset($settings) && $settings['showTopics']) {
                        $topics = isset($rawProfile['topics']) ? substr($rawProfile['topics'], 1, -1) : null;
                        if (isset($topics)) {
                            $profile['topics'] = $topics;
                        }
                    }
                    break;

                case 'followed':
                case 'followme':
                    $profile[$key] = $value === 't' ? true : false;
                    break;
                
                case 'bio':
                    $profile[$key] = $rawProfile[$value];
                    break;
                
                default:
                    break;
            }
        }
            
        return $profile;
        
    }

    /**
     * Return a formated collection description
     * 
     * @param array $rawDescription
     */
    public static function collectionDescription($rawDescription) {
        return array(
            'id' => $rawDescription['id'],
            'version' => $rawDescription['version'] ?? null,
            'model' => $rawDescription['model'],
            'visibility' => (integer) $rawDescription['visibility'],
            'owner' => $rawDescription['owner'],
            'propertiesMapping' => json_decode($rawDescription['mapping'], true),
            'providers' => json_decode($rawDescription['providers'], true),
            'properties' => json_decode($rawDescription['properties'], true),
            'links' => json_decode($rawDescription['links'], true),
            'datetime' => array(
                'min' => $rawDescription['startdate'] ?? null,
                'max' => $rawDescription['completiondate'] ?? null
            ),
            'bbox' => RestoGeometryUtil::box2dTobbox($rawDescription['box2d']),
            'licenseId' => $rawDescription['licenseid']
        );
    }

    /**
     * Process simple interval
     *
     * @param string $columnName
     * @param string $value
     * @return string
     */
    private static function processSimpleInterval($columnName, $value)
    {

        $quote = true;

        /*
         * A = ]n1 then returns n1 < value
         * A = n1[ then returns value < n2
         */
        $op1 = substr($value, 0, 1);
        if ($op1 === '[' || $op1 === ']') {
            return $columnName . ($op1 === '[' ? ' >= ' : ' > ') . FormatUtil::quoteMe(substr($value, 1), $quote);
        }

        /*
         * A = [n1 then returns  n1 ≤ value
         * A = n1] then returns value ≤ n2
         */
        $op2 = substr($value, -1);
        if ($op2 === '[' || $op2 === ']') {
            return $columnName . ($op2 === ']' ? ' <= ' : ' < ') . FormatUtil::quoteMe(substr($value, 0, strlen($value) - 1), $quote);
        }

        /*
         * A = n1 then returns value = n1
         */
        return $columnName . '=' . FormatUtil::quoteMe($value, $quote);
    }

    /**
     * Process complex interval
     *
     * @param string $columnName
     * @param array $values
     * @return string
     */
    private static function processComplexInterval($columnName, $values)
    {

        $quote = true;

        /*
         * First and last characters give operators
         */
        $op1 = substr(trim($values[0]), 0, 1);
        $op2 = substr(trim($values[1]), -1);

        /*
         * A = {n1,n2} then returns  = n1 or = n2
         */
        if ($op1 === '{' && $op2 === '}') {
            return '(' . $columnName . '=' . FormatUtil::quoteMe(substr($values[0], 1), $quote) . ' OR ' . $columnName . '=' . FormatUtil::quoteMe(substr($values[1], 0, strlen($values[1]) - 1), $quote) . ')';
        }

        /*
         * Other cases i.e.
         * A = [n1,n2] then returns <= n1 and <= n2
         * A = [n1,n2[ then returns <= n1 and B < n2
         * A = ]n1,n2[ then returns < n1 and B < n2
         *
         */
        if (($op1 === '[' || $op1 === ']') && ($op2 === '[' || $op2 === ']')) {
            return $columnName . ($op1 === '[' ? '>=' : '>') . FormatUtil::quoteMe(substr($values[0], 1), $quote) . ' AND ' . $columnName . ($op2 === ']' ? '<=' : '<') . FormatUtil::quoteMe(substr($values[1], 0, strlen($values[1]) - 1), $quote);
        }
    }

    /**
     * Return a escaped quoted string
     * 
     * @param string $str
     * @param boolean $quote
     */
    private static function quoteMe($str, $quote) {
        return $quote ? '\'' . pg_escape_string($str) . '\'' : pg_escape_string($str);
    }

}
