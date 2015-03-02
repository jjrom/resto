<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2013 Jérôme Gasperi <https://github.com/jjrom>
 * 
 * jerome[dot]gasperi[at]gmail[dot]com
 * 
 * 
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
 * 
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
     * Return extension from mimeType
     */
    public static function getExtension($mimeType) {
        if (!isset($mimeType)) {
            return '';
        }
        switch ($mimeType) {
            case 'application/zip':
                return '.zip';
            case 'application/x-gzip':
                return '.gzip';
            default:
                return '';
        }
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
        for ($i = 0; $i < strlen($nhex); $i+=2) {
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
     * Replace all occurences of a string
     * 
     *  Example :
     *      
     *      replaceInTemplate('Hello. My name is {:name:}. I live in {:location:}', array('name' => 'Jérôme', 'location' => 'Toulouse'));
     * 
     *  Will return
     * 
     *      'Hello. My name is Jérôme. I live in Toulouse
     * 
     * 
     * @param string $sentence
     * @param array $pairs
     * 
     */
    public static function replaceInTemplate($sentence, $pairs = array()) {
        
        if (!isset($sentence)) {
            return null;
        }
        
        /*
         * Extract pairs
         */
        preg_match_all("/{\:[^\\:}]*\:}/", $sentence, $matches);
        
        $replace = array();
        for ($i = count($matches[0]); $i--;)  {
            $key = substr($matches[0][$i], 2, -2);
            if (isset($pairs[$key])) {
                $replace[$matches[0][$i]] = $pairs[$key];
            }
        }
        if (count($replace) > 0) {
            return strtr($sentence, $replace);
        }
        
        return $sentence;
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
            parse_str($exploded['query'], $existingParams);
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
     * Construct base url from parse_url fragments
     * 
     * @param array $exploded
     */
    public static function baseUrl($exploded) {
        return (isset($exploded['scheme']) ? $exploded['scheme'] . ':' : '') . '//' .
               (isset($exploded['user']) ? $exploded['user'] . ':' . $exploded['pass'] . '@' : '') .
               $exploded['host'] . (isset($exploded['port']) ? ':' . $exploded['port'] : '');
    }
    
    /**
     * Write a valid RESTo URL
     * 
     * @param string $baseUrl
     * @param string $route
     * @param string $format
     */
    public static function restoUrl($baseUrl = '//', $route = '', $format = '') {
        return trim($baseUrl . (substr($baseUrl, -1) !== '/' ? '/' : '') . $route, '/') . (isset($format) && $format !== '' ? '.' . $format : '');
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

        /* Pattern for matching : YYYY */
        $pYear = '\d{4}';

        /* Pattern for matching : YYYY-MM */
        $pMonthExtend = '\d{4}-\d{2}';

        /* Pattern for matching : YYYY-MM-DD */
        $pDateExtend = '\d{4}-\d{2}-\d{2}';

        /* Pattern for matching : YYYY-MM-DDTHH:MM:SS */
        $pDateAndTimeExtend = '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}';

        /* Pattern for matching : +HH:MM or -HH:MM */
        $pTimeZoneExtend = '[\+|\-]\d{2}\:\d{2}';

        /** Pattern for matching : ,n or .n 
         *  where n is the fraction of seconds to one or more digits
         */
        $pFractionSeconds = '[,|\.]\d+';

        /* Pattern for matching : YYYYMM */
        $pMonth = '\d{4}\d{2}';

        /* Pattern for matching : YYYYMMDD */
        $pDate = '\d{4}\d{2}\d{2}';

        /* Pattern for matching : YYYYMMDDTHHMMSS */
        $pDateAndTime = '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}';

        /* Pattern for matching : +HHMM or -HHMM */
        $pTimeZone = '[\+|\-]\d{2}\d{2}';

        /**
         * Construct the regex to match all ISO 8601 format date case
         * The regex is constructed as a combination of all pattern       
         */
        $completePattern = array(
            $pYear,
            $pMonthExtend,
            $pDateExtend,
            $pDateExtend,
            $pDateAndTimeExtend . 'Z',
            $pDateAndTimeExtend . '' . $pTimeZoneExtend,
            $pDateAndTimeExtend . '' . $pFractionSeconds,
            $pDateAndTimeExtend . '' . $pFractionSeconds . 'Z',
            $pDateAndTimeExtend . '' . $pFractionSeconds . '' . $pTimeZoneExtend,
            $pMonth,
            $pDate,
            $pDateAndTime,
            $pDateAndTime . 'Z',
            $pDateAndTime . '' . $pTimeZone,
            $pDateAndTime . '' . $pTimeZone . 'Z',
            $pDateAndTime . '' . $pFractionSeconds . '' . $pTimeZone
        );
        return preg_match('/^' . join('$|^', $completePattern) .'$/i', $dateStr);
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
            throw new Exception(__METHOD__ . ' - Class name is not set', 500);
        }
        
        try {
            $class = new ReflectionClass($className);
            if (!$class->isInstantiable()) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exception(__METHOD__ . ' - ' . $className . ' is not instantiable', 500);
        }
        
        $count = count($params);
        if ($count === 1) {
            return $class->newInstance($params[0]);
        }
        else if ($count === 2) {
            return $class->newInstance($params[0], $params[1]);
        }
        else if ($count === 3) {
            return $class->newInstance($params[0], $params[1], $params[2]);
        }
        
        return $class->newInstance();
        
    }

    /**
     * Return an array of posted/put files or POST stream within HTTP request Body
     * 
     * @param array $params - query parameters
     * 
     * @return array
     * @throws Exception
     */
    public static function readInputData() {

        /*
         * True by default, False if no file is posted but data posted through parameters
         */
        $isFile = true;

        /*
         * No file is posted - check HTTP request body
         */
        if (count($_FILES) === 0 || !is_array($_FILES['file'])) {
            $body = file_get_contents('php://input');
            if (isset($body)) {
                $isFile = false;
                $tmpFiles = array($body);
            }
        }
        /*
         * A file is posted -
         * Read file assuming this is ascii file (i.e. plain text, GeoJSON, etc.)
         */
        else {
            $tmpFiles = $_FILES['file']['tmp_name'];
            if (!is_array($tmpFiles)) {
                $tmpFiles = array($tmpFiles);
            }
        }
        
        /*
         * Nothing was post - or post was empty
         */
        if (!isset($tmpFiles)) {
            return null;
        }
        if (count($tmpFiles) > 1) {
            throw new Exception('Only one file can be posted at a time', 500);
        }

        /*
         * Assume that input data format is JSON by default
         */
        try {
            $output = json_decode($isFile ? join('', file($tmpFiles[0])) : $tmpFiles[0], true);
        } catch (Exception $e) {
            throw new Exception('Invalid posted file(s)', 500);
        }
        
        /*
         * The data's format is not JSON
         */
        if ($output === null) {
            
            /*
             * Push the file content in return array.
             * The file content is transformed as array by file function
             */
            if ($isFile) {
                try {
                    $output = file($tmpFiles[0]);
                } catch (Exception $e) {
                    throw new Exception('Invalid posted file(s)', 500);
                } 
            }
            /*
             * By default, the exploding character is "\n"
             */
            else {
                $output = explode("\n", $tmpFiles[0]);
            }
        }

        return $output;
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
        
        $output = array();
        $quotted = explode('"', $str);
        
        /*
         * Search for quotted (i.e. text within " ") parts
         */
        $count = count($quotted);
        if ($count > 1 && $count % 2 === 1) {
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
        }
        else {
            $output = explode(' ', $str);
        }
        
        return $output;
    }
    
    /**
     * Quote string with " characters if needed (i.e. if 
     * the string contains a space)
     * 
     * @param string $str
     */
    public static function quoteIfNeeded($str) {
        if (strpos($str, ' ') !== FALSE) {
            return '"' . $str . '"';
        }
        return $str;
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
     * 
     * Download hosted resource with support of Range and Partial Content
     * (See http://stackoverflow.com/questions/3697748/fastest-way-to-serve-a-file-using-php)
     *
     * @param string $path
     * @param string $mimeType
     * @param type $multipart
     * @return boolean
     */
    public static function download($path, $mimeType = 'application/octet-stream', $multipart = true) {

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        /*
         * File does not exist
         */
        if (is_file($path = realpath($path)) === false) {
            RestoLogUtil::httpError(404);
        }

        /*
         * File cannot be read
         */
        $file = @fopen($path, 'rb');
        if (is_resource($file) === true) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * Avoid timeouts
         */
        set_time_limit(0);
        
        /*
         * Range support
         * 
         * In case of multiple ranges requested, only the first range is served
         * (http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt)
         */
        $size = sprintf('%u', filesize($path));
        if ($multipart === true) {
            $range = array(0, $size - 1);
            $httpRange = filter_input(INPUT_SERVER, 'HTTP_RANGE', FILTER_SANITIZE_STRING);
            if (isset($httpRange)) {
                $range = array_map('intval', explode('-', preg_replace('~.*=([^,]*).*~', '$1', $httpRange)));

                if (empty($range[1]) === true) {
                    $range[1] = $size - 1;
                }

                foreach ($range as $key => $value) {
                    $range[$key] = max(0, min($value, $size - 1));
                }

                if (($range[0] > 0) || ($range[1] < ($size - 1))) {
                    header(sprintf('%s %03u %s', 'HTTP/1.1', 206, 'Partial Content'), true, 206);
                }
            }
            header('Accept-Ranges: bytes');
            header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));
        }
        else {
            $range = array(0, $size - 1);
        }

        header('Pragma: public');
        header('Cache-Control: public, no-cache');
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . sprintf('%u', $range[1] - $range[0] + 1));
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Transfer-Encoding: binary');

        if ($range[0] > 0) {
            fseek($file, $range[0]);
        }

        while ((feof($file) !== true) && (connection_status() === CONNECTION_NORMAL)) {
            echo fread($file, 10 * 1024 * 1024);
            flush();
        }

        fclose($file);
        
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
                    $paramsStr .= (isset($paramsStr) ? '&' : '') . urlencode($key) . '[]=' . urlencode($value[$i]);
                }
            }
            else {
                $paramsStr .= (isset($paramsStr) ? '&' : '') . urlencode($key) . '=' . urlencode($value);
            }
        }
        return '?' . $paramsStr;
    }
    
    /**
     * Return PostgreSQL database handler
     * 
     * @param array $options
     * @throws Exception
     */
    public static function getPostgresHandler($options = array()) {
    
        $dbh = null;
        
        if (isset($options) && isset($options['dbname'])) {
            try {
                $dbInfo = array(
                    'dbname=' . $options['dbname'],
                    'user=' . $options['user'],
                    'password=' . $options['password']
                );
                /*
                 * If host is specified, then TCP/IP connection is used
                 * Otherwise socket connection is used
                 */
                if (isset($options['host'])) {
                    $dbInfo[] = 'host=' . $options['host'];
                    $dbInfo[] = 'port=' . (isset($options['port']) ? $options['port'] : '5432');
                }
                $dbh = pg_connect(join(' ', $dbInfo));
                if (!$dbh) {
                    throw new Exception();
                }
            } catch (Exception $e) {
                throw new Exception('Database connection error', 500);
            }
        }   

        return $dbh;
    }
    
    /**
     * Pretty print a json string
     * Code modified from https://github.com/GerHobbelt/nicejson-php
     * 
     * @param string $json
     */
    private static function prettyPrintJsonString($json) {
        
        $result = '';
        $pos = 0;               // indentation level
        $strLen = strlen($json);
        $indentStr = "\t";
        $newLine = "\n";
        $prevChar = '';
        $outOfQuotes = true;

        for ($i = 0; $i < $strLen; $i++) {
            // Grab the next character in the string
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
            }
            // If this character is the end of an element,
            // output a new line and indent the next line
            else if (($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos--;
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
            // eat all non-essential whitespace in the input as we do our own here and it would only mess up our process
            else if ($outOfQuotes && false !== strpos(" \t\r\n", $char)) {
                continue;
            }

            // Add the character to the result string
            $result .= $char;
            // always add a space after a field colon:
            if ($char == ':' && $outOfQuotes) {
                $result .= ' ';
            }

            // If the last character was the beginning of an element,
            // output a new line and indent the next line
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos++;
                }
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
            $prevChar = $char;
        }

        return $result;
    }
    
}
