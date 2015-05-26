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
 * RESTo Utilities functions
 */
class RestoUtil {

    /*
     * List of supported formats mimeTypes
     */
    public static $contentTypes = array(
        'atom' => 'application/atom+xml',
        'html' => 'text/html',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'meta4' => 'application/metalink4+xml'
    );
    
    /**
     * Encrypt a string using $algorithm
     * 
     * @param string $str
     */
    public static function encrypt($str, $algo = 'sha1') {
        return sha1($str);
    }
    
    /**
     * Format a flat JSON string to make it more human-readable
     *
     * @param array $json JSON as an array
     * 
     * @return string Indented version of the original JSON string
     */
    public static function json_format($json, $pretty = false) {

        /*
         * No pretty print - easy part
         */
        if (!$pretty) {
            return json_encode($json);
        }
        
        /*
         * Pretty print only works for PHP >= 5.4
         * Home made pretty print otherwise
         */
        if (phpversion() && phpversion() >= 5.4) {
            return json_encode($json, JSON_PRETTY_PRINT);
        }
        else {
             return RestoUtil::prettyPrintJsonString(json_encode($json));
        }
     
    }

    /**
     * Generate v5 UUID
     * 
     * Version 5 UUIDs are named based. They require a namespace (another 
     * valid UUID) and a value (the name). Given the same namespace and 
     * name, the output is always the same.
     * 
     * Note: if not set, the default namespace is a RESTo v4 UUID
     * generated at http://uuidgenerator.net/
     * 
     * @param string $name
     * @param uuid $namespace
     * 
     * @author Andrew Moore
     * @link http://www.php.net/manual/en/function.uniqid.php#94959
     */
    public static function UUIDv5($name, $namespace = '92708059-2077-45a3-a4f3-1eb428789cff') {

        if (!RestoUtil::isValidUUID($namespace)) {
            return false;
        }

        // Get hexadecimal components of namespace
        $nhex = str_replace(array('-', '{', '}'), '', $namespace);

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for ($i = 0, $ii = strlen($nhex); $i < $ii; $i+=2) {
            $nstr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
        }

        // Calculate hash value
        $hash = sha1($nstr . $name);

        return sprintf('%08s-%04s-%04x-%04x-%12s',
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
     * Check that input $uuid has a valid uuid syntax
     * @link http://tools.ietf.org/html/rfc4122
     * 
     * @param uuid $uuid
     */
    public static function isValidUUID($uuid) {
        return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
    }
    
    /**
     * Upgraded implode($glue, $arr) function that
     * do not aggregate NULL elements in result
     */
    public static function superImplode($glue, $arr) {
        $ret_str = "";
        foreach ($arr as $a) {
            $ret_str .= (is_array($a)) ? implode_r($glue, $a) : $a === NULL ? "" : strval($a) . $glue;
        }
        if (strrpos($ret_str, $glue) != strlen($glue)) {
            $ret_str = substr($ret_str, 0, -(strlen($glue)));
        }
        return $ret_str;
    }
    
    /**
     * Rewrite URL with input query parameters
     * 
     * @param string $url
     * @param array $newParams
     */
    public static function updateUrl($url, $newParams = array()) {
        $existingParams = array();
        $exploded = parse_url($url);
        if (isset($exploded['query'])) {
            $existingParams = RestoUtil::queryStringToKvps($exploded['query']);
        }
        return RestoUtil::baseUrl($exploded) . $exploded['path'] . RestoUtil::kvpsToQueryString(array_merge($existingParams, $newParams));
    }
    
    /**
     * Rewrite URL with new format
     * 
     * @param string $url
     * @param string $format
     */
    public static function updateUrlFormat($url, $format) {
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
    public static function restoUrl($baseUrl = '//', $route = '', $format = '') {
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
    public static function isISO8601($dateStr) {

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
     * 
     * Return an ISO 8601 formatted YYYY-MM-DDT00:00:00Z from
     * a valid iso8601 string
     * 
     * @param {String} $dateStr
     *    
     */
    public static function toISO8601($dateStr) {

        // Year
        if (preg_match('/^\d{4}$/i', $dateStr)) {
            return $dateStr . '-01-01T00:00:00Z';
        }
        // Month
        else if (preg_match('/^\d{4}-\d{2}$/i', $dateStr)) {
            return $dateStr . '-01T00:00:00Z';
        }
        // Day
        else if (preg_match('/^\d{4}-\d{2}-\d{2}$/i', $dateStr)) {
            return $dateStr . 'T00:00:00Z';
        }

        return $dateStr;
    }
    
    /**
     * Instantiate class with params
     * 
     * @param string $className : class name to instantiate
     * @param array $params : array of params to pass to the instantiate class
     */
    public static function instantiate($className, $params = array()) {
        
        if (!$className) {
            RestoLogUtil::httpError(500, 'Class name is not set');
        }
        
        try {
            $class = new ReflectionClass($className);
            if (!$class->isInstantiable()) {
                throw new Exception();
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, $className . ' is not instantiable');
        }
        
        switch (count($params)) {
            case 1:
                return $class->newInstance($params[0]);
            case 2:
                return $class->newInstance($params[0], $params[1]);
            case 3:
                return $class->newInstance($params[0], $params[1], $params[2]);
            default:
                return $class->newInstance();
        }
    }

    /**
     * Return an array of posted/put files or POST stream within HTTP request Body
     * 
     * @param string $uploadDirectory - Upload directory
     * 
     * @return array
     * @throws Exception
     */
    public static function readInputData($uploadDirectory) {

        /*
         * No file is posted - check HTTP request body
         */
        if (count($_FILES) === 0 || !is_array($_FILES['file'])) {
            return RestoUtil::readStream();
        }
        /*
         * A file is posted - read attachement
         */
        else {
            return RestoUtil::readFile($uploadDirectory);
        }
        
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
    public static function splitString($str) {
        
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
        
        return explode(' ', $str);
    }
    
    /**
     * Check if string starts like an url i.e. http:// or https:// or //:
     * 
     * @param {String} $str
     */
    public static function isUrl($str) {
        if (!isset($str)) {
            return false;
        }
        if (substr(trim($str), 0, 7) === 'http://' || substr(trim($str), 0, 8) === 'https://' || substr(trim($str), 0, 2) === '//') {
            return true;
        }
        return false;
    }
    
    /**
     * Compute a sha1 hash from $input,$parent truncated to 15 characters
     * 
     * @param string $input
     * @param string $parent
     * @return string
     */
    public static function getHash($input, $parent = null) {
        return substr(sha1($input . (isset($parent) ? ',' . $parent : '')), 0, 15);
    }
    
    /**
     * Sanitize input parameter to avoid code injection
     *   - remove html tags
     * 
     * @param {String or Array} $strOrArray
     */
    public static function sanitize($strOrArray) {
        
        if (!isset($strOrArray)) {
            return null;
        }
        
        if (is_array($strOrArray)) {
            $result = array();
            foreach ($strOrArray as $key => $value) {
                
                /*
                 * Remove html tags
                 */
                if (is_string($value)) {
                    $result[$key] = strip_tags($value);
                }
                /*
                 * Let value untouched
                 */
                else {
                    $result[$key] = $value;
                }
            }
            return $result;
        }
        else {

            /*
             * No Hexadecimal allowed
             */
            if (ctype_xdigit($strOrArray)) {
                return null;
            }
            /*
             * Remove html tags
             */
            else if (is_string($strOrArray)) {
                return strip_tags($strOrArray);
            }
            /*
             * Let value untouched
             */
            else {
                return $strOrArray;
            }
        }
        
    }
    
    /**
     * Format input Key/Value pairs array to query string
     * 
     * @param array $kvps
     * @return string
     */
    public static function kvpsToQueryString($kvps) {
        $paramsStr = '';
        if (!is_array($kvps)) {
            return $paramsStr;
        }
        foreach ($kvps as $key => $value) {
            if (is_array($value)) {
                for ($i = count($value); $i--;) {
                    $paramsStr .= (isset($paramsStr) ? '&' : '') . rawurlencode($key) . '[]=' . rawurlencode($value[$i]);
                }
            }
            else {
                $paramsStr .= (isset($paramsStr) ? '&' : '') . rawurlencode($key) . '=' . rawurlencode($value);
            }
        }
        return '?' . $paramsStr;
    }
    
    /**
     * Explode query string to input Key/Value pairs array
     * 
     * @param string $queryString
     * @return array
     */
    public static function queryStringToKvps($queryString) {
        $output = array();
        parse_str($queryString, $output);
        return $output;
    }
    
    /**
     * Pretty print a json string
     * Code from https://github.com/ryanuber/projects/blob/master/PHP/JSON/jsonpp.php
     * 
     * @param string $json
     */
    private static function prettyPrintJsonString($json, $istr = '   ') {
        $result = '';
        for ($p = $q = $i = 0; isset($json[$p]); $p++) {
            $json[$p] == '"' && ($p > 0 ? $json[$p - 1] : '') != '\\' && $q = !$q;
            if (!$q && strchr(" \t\n\r", $json[$p])) {
                continue;
            }
            if (strchr('}]', $json[$p]) && !$q && $i--) {
                strchr('{[', $json[$p - 1]) || $result .= "\n" . str_repeat($istr, $i);
            }
            $result .= $json[$p];
            if (strchr(',{[', $json[$p]) && !$q) {
                $i += strchr('{[', $json[$p]) === FALSE ? 0 : 1;
                strchr('}]', $json[$p + 1]) || $result .= "\n" . str_repeat($istr, $i);
            }
        }
        return $result;
    }
    
    /**
     * Construct base url from parse_url fragments
     * 
     * @param array $exploded
     */
    private static function baseUrl($exploded) {
        return (isset($exploded['scheme']) ? $exploded['scheme'] . ':' : '') . '//' .
               (isset($exploded['user']) ? $exploded['user'] . ':' . $exploded['pass'] . '@' : '') .
               $exploded['host'] . (isset($exploded['port']) ? ':' . $exploded['port'] : '');
    }
    
    /**
     * Read file content attached in POST request
     * 
     * @param string $uploadDirectory
     * @param boolean deleteAfterRead
     * @return type
     * @throws Exception
     */
    private static function readFile($uploadDirectory, $deleteAfterRead = true) {
        try {
            $fileToUpload = is_array($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'][0] : $_FILES['file']['tmp_name'];
            if (is_uploaded_file($fileToUpload)) {
                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory);
                }
                $fileName = $uploadDirectory . DIRECTORY_SEPARATOR . (substr(sha1(mt_rand() . microtime()), 0, 15));
                move_uploaded_file($fileToUpload, $fileName);
                $lines = file($fileName);
                if ($deleteAfterRead) {
                    unlink($fileName);
                }
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot upload file(s)');
        }
        
        /*
         * Assume that input data format is JSON by default
         */
        $json = json_decode(join('', $lines), true);
        
        return $json === null ? $lines : $json;
    }
    
    /**
     * Read file content within header body of POST request
     * 
     * @return type
     * @throws Exception
     */
    private static function readStream() {
        
        $content = file_get_contents('php://input');
        if (!isset($content)) {
            return null;
        }
        
        /*
         * Assume that input data format is JSON by default
         */
        $json = json_decode($content, true);
        
        return $json === null ? explode("\n", $content) : $json;
    }
    
}
