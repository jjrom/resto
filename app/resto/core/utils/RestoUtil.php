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
 * RESTo Utilities functions
 */
class RestoUtil
{

    /*
     * List of supported formats mimeTypes
     */
    public static $contentTypes = array(
        'atom' => 'application/atom+xml',
        'cog' => 'image/tiff; application=geotiff; profile=cloud-optimized',
        'csv' => 'text/csv',
        'geojson' => 'application/geo+json',
        'geopackage' => 'application/geopackage+sqlite3',
        'geotiff' => 'image/tiff; application=geotiff',
        'hdf' => 'application/x-hdf',
        'hdf5' => 'application/x-hdf5',
        'html' => 'text/html',
        'jpeg' => 'image/jpeg',
        'jpeg2000' => 'image/jp2',
        'jp2' => 'image/jp2',
        'json' => 'application/json',
        'meta4' => 'application/metalink4+xml',
        'mvt' => 'application/vnd.mapbox-vector-tile',
        'pbf' => 'application/vnd.mapbox-vector-tile',
        'png' => 'image/png',
        'xml' => 'application/xml',
        'zip' => 'application/zip'
    );

    /**
     * Clean associative array i.e. remove empty or null keys
     * 
     * @param array $associativeArray
     * @return array
     */
    public static function cleanAssociativeArray($associativeArray)
    {

        // Output
        $cleanArray = array();

        // Eventually unset all empty properties and array
        foreach (array_keys($associativeArray) as $key) {
            if (!isset($associativeArray[$key]) || (is_array($associativeArray[$key]) && count($associativeArray[$key]) === 0)) {
                continue;
            }
            $cleanArray[$key] = $associativeArray[$key];
        }
        
        return $cleanArray;

    }

    /**
     * Extract hashtags from a string (i.e. #something or -#something)
     *
     * @param string $str
     * 
     * @return array
     */
    public static function extractHashtags($str)
    {
        preg_match_all("/(#|-#)([^ ]+)/u", $str, $matches);
        if ($matches && count($matches[0]) > 0) {
            return $matches[0];
        }
        return array();
    }

    /**
     * Clean a hashtag string i.e. disacard characters !, $, %, ^, &, *, +, ., ", { ,}, /, \
     *
     * Example:
     *
     *    $str = #bad!hasHtag$%".veryBad
     *
     * returns:
     *    
     *    #badhasHtagveryBad
     * 
     * @param string $str
     * 
     * @return string
     */
    public static function cleanHashtag($str)
    {
        $bad = array('!', '$', '%', '^', '&', '*', '+', '.', '"', '{', '}', '/', '\\');
        return str_replace($bad, '', $str);
    }

    /**
     * Encrypt a string using $algorithm
     *
     * @param string $str
     */
    public static function encrypt($str)
    {
        return sha1($str);
    }

    /**
     * Generate v5 UUID
     *
     * Version 5 UUIDs are named based. They require a namespace (another
     * valid UUID) and a value (the name). Given the same namespace and
     * name, the output is always the same.
     *
     * @param string $str
     *
     * @author Andrew Moore
     * @link http://www.php.net/manual/en/function.uniqid.php#94959
     */
    public static function toUUID($str)
    {

        // Get hexadecimal components of namespace (Note: use a dummy uuid)
        $nhex = str_replace(array('-', '{', '}'), '', '92708059-2077-45a3-a4f3-1eb428789cff');

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for ($i = 0, $ii = strlen($nhex); $i < $ii; $i+=2) {
            $nstr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
        }

        // Calculate hash value
        $hash = sha1($nstr . $str);

        return sprintf(
            '%08s-%04s-%04x-%04x-%12s',
                // 32 bits for "time_low"
                substr($hash, 0, 8),
                // 16 bits for "time_mid"
                substr($hash, 8, 4),
                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 5
                (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,
                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
                // 48 bits for "node"
                substr($hash, 20, 12)
        );
    }

    /**
     * Rewrite URL with input query parameters
     *
     * @param string $url
     * @param array $newParams
     */
    public static function updateUrl($url, $newParams = array())
    {
        $existingParams = array();
        $exploded = parse_url($url);
        if (isset($exploded['query'])) {
            $existingParams = RestoUtil::queryStringToKvps($exploded['query']);
        }
        $queryString = RestoUtil::kvpsToQueryString(array_merge($existingParams, $newParams));
        return RestoUtil::baseUrl($exploded) . $exploded['path'] . ($queryString ? '?' . $queryString : '');
    }

    /**
     * Rewrite URL with new format
     *
     * @param string $url
     * @param string $format
     */
    public static function updateUrlFormat($url, $format)
    {
        $exploded = parse_url($url);
        $path = $exploded['path'];
        $splitted = explode('.', $path);
        if (count($splitted) > 1) {
            array_pop($splitted);
            $path = join('.', $splitted);
        }
        return RestoUtil::baseUrl($exploded) . $path . '.' . $format . (isset($exploded['query']) ? '?' . $exploded['query'] : '');
    }

    /**
     * Write a valid RESTo URL
     *
     * @param string $baseUrl
     * @param string $route
     * @param string $format
     */
    public static function restoUrl($baseUrl = '//', $route = '', $format = '')
    {
        return trim($baseUrl . $route, '/') . (isset($format) && $format !== '' ? '.' . $format : '');
    }

    /**
     *
     * Return true if input date string is ISO 8601 formatted
     * i.e. one in the following form :
     *
     *      YYYY
     *      YYYY-MM
     *      YYYY-MM-DD
     *      YYYY-MM-DDTHH:MM:SS
     *      YYYY-MM-DDTHH:MM:SSZ
     *      YYYY-MM-DDTHH:MM:SS.sssss
     *      YYYY-MM-DDTHH:MM:SS.sssssZ
     *      YYYY-MM-DDTHH:MM:SS+HHMM
     *      YYYY-MM-DDTHH:MM:SS-HHMM
     *      YYYY-MM-DDTHH:MM:SS.sssss+HHMM
     *      YYYY-MM-DDTHH:MM:SS.sssss-HHMM
     *
     * @param {String} $dateStr
     *
     */
    public static function isISO8601($dateStr)
    {

        /**
         * Construct the regex to match all ISO 8601 format date case
         * The regex is constructed as a combination of all pattern
         */
        return preg_match('/^' . join('$|^', array(
                    '\d{4}', // YYYY
                    '\d{4}-\d{2}', // YYYY-MM
                    '\d{4}-\d{2}-\d{2}', // YYYY-MM-DD
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}', // YYYY-MM-DDTHH:MM:SS
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}Z', // YYYY-MM-DDTHH:MM:SSZ
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}' . '' . '[\+|\-]\d{2}\:\d{2}', // YYYY-MM-DDTHH:MM:SS +HH:MM or -HH:MM
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}' . '' . '[,|\.]\d+', // YYYY-MM-DDTHH:MM:SS(. or ,)n
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}' . '' . '[,|\.]\d+' . 'Z', // YYYY-MM-DDTHH:MM:SS(. or ,)nZ
                    '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}' . '' . '[,|\.]\d+' . '' . '[\+|\-]\d{2}\:\d{2}', // // YYYY-MM-DDTHH:MM:SS(. or ,)n +HH:MM or -HH:MM
                    '\d{4}\d{2}', // YYYYMM
                    '\d{4}\d{2}\d{2}', // YYYYMMDD
                    '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}', // YYYYMMDDTHHMMSS
                    '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}' . 'Z', // YYYYMMDDTHHMMSSZ
                    '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}' . '' . '[\+|\-]\d{2}\d{2}', // YYYYMMDDTHHMMSSZ +HHMM or -HHMM
                    '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}' . '' . '[\+|\-]\d{2}\d{2}' . 'Z', // // YYYYMMDDTHHMMSSZ(. or ,)nZ
                    '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}' . '' . '[,|\.]\d+' . '' . '[\+|\-]\d{2}\d{2}' // YYYYMMDDTHHMMSSZ(. or ,)n +HHMM or -HHMM
                )) . '$/i', $dateStr);
    }

    /**
     * Split a string on space character into an array of words
     *
     * Note: if parts of the input string are inside quotes (i.e. " character"),
     * the content of the quotes is considered as a single word
     *
     *
     * @param string $str
     * @return array
     */
    public static function splitString($str)
    {
        $quotted = explode('"', $str);

        /*
         * Search for quotted (i.e. text within " ") parts
         */
        $count = count($quotted);
        if ($count > 1 && $count % 2 === 1) {
            $output = array();
            for ($i = 0; $i < $count; $i++) {
                if ($quotted[$i]) {
                    // Inside the quote
                    if ($i % 2 === 1) {
                        $output[] = $quotted[$i];
                    }
                    // Outside the quote - split on space character
                    else {
                        $exploded = explode(' ', $quotted[$i]);
                        for ($j = 0, $m = count($exploded); $j < $m; $j++) {
                            if ($exploded[$j]) {
                                $output[] = $exploded[$j];
                            }
                        }
                    }
                }
            }

            return $output;
        }

        return explode(' ', str_replace('"', '', $str));
    }

    /**
     * Check if string starts like an url i.e. http:// or https:// or //:
     *
     * @param {String} $str
     */
    public static function isUrl($str)
    {
        if (!isset($str)) {
            return false;
        }
        if (substr(trim($str), 0, 7) === 'http://' || substr(trim($str), 0, 8) === 'https://' || substr(trim($str), 0, 2) === '//') {
            return true;
        }
        return false;
    }

    /**
     * Check that input $uuid has a valid uuid syntax
     * @link http://tools.ietf.org/html/rfc4122
     *
     * @param uuid $uuid
     */
    public static function isValidUUID($uuid) {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }

    /**
     * Sanitize input parameter to avoid code injection
     *   - remove html tags
     *
     * @param {String or Array} $strOrArray
     */
    public static function sanitize($strOrArray)
    {   
        
        if (!isset($strOrArray)) {
            return null;
        }

        if (is_array($strOrArray)) {
            $result = array();
            foreach ($strOrArray as $key => $value) {
                $result[$key] = RestoUtil::sanitizeString($value);
            }
            return $result;
        }
        
        return RestoUtil::sanitizeString($strOrArray);
    }

    /**
     * Format input Key/Value pairs array to query string
     *
     * @param array $kvps
     * @return string
     */
    public static function kvpsToQueryString($kvps)
    {
        $paramsStr = '';
        if (!is_array($kvps)) {
            return $paramsStr;
        }
        foreach ($kvps as $key => $value) {
            if ($value === null) {
                continue;
            }
            if (is_array($value)) {
                for ($i = count($value); $i--;) {
                    $paramsStr .= (isset($paramsStr) ? '&' : '') . rawurlencode($key) . '[]=' . rawurlencode($value[$i]);
                }
            } else {
                $paramsStr .= (isset($paramsStr) ? '&' : '') . rawurlencode($key) . '=' . rawurlencode($value);
            }
        }
        return $paramsStr;
    }

    /**
     * Explode query string to input Key/Value pairs array
     *
     * @param string $queryString
     * @return array
     */
    public static function queryStringToKvps($queryString)
    {
        $output = array();
        parse_str($queryString, $output);
        return $output;
    }

    /**
     * Replace all occurences of a string
     *
     *  Example :
     *
     *      replaceInTemplate('Hello. My name is {:name:}. I live in {:location:}', array('name' => 'Jérôme', 'location' => 'Toulouse'));
     *
     *  Will return
     *
     *      Hello. My name is Jérôme. I live in Toulouse
     *
     * [IMPORTANT]
     *
     *      {:xxx:} value without a xxx pair defined in pairs is replace by empty string
     *      In the previous example, if 'name' => 'Jérôme' is not provided, the return sentence
     *      would be
     *
     *      Hello. My name is . I live in Toulouse
     *
     *
     * @param string $sentence
     * @param array $pairs
     *
     */
    public static function replaceInTemplate($sentence, $pairs = array())
    {
        if (!isset($sentence)) {
            return null;
        }

        /*
         * Extract pairs
         */
        preg_match_all("/{\:[^\\:}]*\:}/", $sentence, $matches);

        $replace = array();
        for ($i = count($matches[0]); $i--;) {
            $replace[$matches[0][$i]] = $pairs[substr($matches[0][$i], 2, -2)] ?? '';
        }
        if (count($replace) > 0) {
            return strtr($sentence, $replace);
        }

        return $sentence;
    }

    /**
     * Store file within outputDir
     *
     * @param string $data Base64 encoded file
     * @param string $outputDir Directory where to store the file
     * @param array $allowedTypes File types allowed to be uploaded
     * @return string The name of the stored file (without basename)
     */
    public static function storeBase64File($data, $outputDir, $allowedTypes = array())
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]);
            
                if (!in_array($type, $allowedTypes)) {
                    throw new Exception('This file type is not allowed');
                }
            
                // Compute file md5
                $fileName = md5($data) . '.' . $type;

                // Decode data
                $data = base64_decode($data);
            
                if ($data === false) {
                    throw new Exception('File decoding failed');
                }
            } else {
                throw new Exception('Invalid file');
            }
            
            file_put_contents($outputDir . '/' . $fileName, $data);

            return $fileName;
        } catch (Exception $e) {
            RestoLogUtil::httpError(400, $e->getMessage());
        }
    }

    /**
     * Check that userid is the caller
     *
     * @param string userid
     */
    public static function checkUser($user, $userid)
    {
        if (!ctype_digit($userid)) {
            RestoLogUtil::httpError(400, 'Invalid userid');
        }
        if (! $user || $user->profile['id'] !== $userid) {
            RestoLogUtil::httpError(403);
        }
    }

    /**
     * Construct base url from parse_url fragments
     *
     * @param array $exploded
     */
    private static function baseUrl($exploded)
    {
        return (isset($exploded['scheme']) ? $exploded['scheme'] . ':' : '') . '//' .
               (isset($exploded['user']) ? $exploded['user'] . ':' . $exploded['pass'] . '@' : '') .
               $exploded['host'] . (isset($exploded['port']) ? ':' . $exploded['port'] : '');
    }

    /**
     * Sanitize string
     *
     * @param string $str
     * @return string
     */
    private static function sanitizeString($str)
    {
        
        /*
         * Remove html tags and NULL (i.e. \0)
         */
        if (is_string($str)) {

            /*
             * No Hexadecimal allowed i.e. nothing that starts with 0x
             */
            if (strlen($str) > 1 && substr($str, 0, 2) === '0x') {
                return null;
            }

            return strip_tags(str_replace(chr(0), '', $str));
            
        }
        
        /*
         * Let value untouched
         */
        return $str;

    }
}
