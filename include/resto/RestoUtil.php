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
    
    /*
     * HTTP codes
     */
    public static $codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
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
     * Code modified from https://github.com/GerHobbelt/nicejson-php
     * 
     * @param string $json The original JSON string to process
     *        When the input is not a string it is assumed the input is RAW
     *        and should be converted to JSON first of all.
     * @return string Indented version of the original JSON string
     */
    public static function json_format($json, $pretty = false) {

        /*
         * No pretty print - easy part
         */
        if (!$pretty) {
            if (!is_string($json)) {
                return json_encode($json);
            }
            return $json;
        }

        if (!is_string($json)) {
            if (phpversion() && phpversion() >= 5.4) {
                return json_encode($json, JSON_PRETTY_PRINT);
            }
            $json = json_encode($json);
        }
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
     * Check if input object is a valid GeoJSON object
     * 
     * Valid GeoJSON Feature 
     * 
     *      array(
     *          'type' => 'Feature',
     *          'geometry' => array(...),
     *          'properties' => array(...)
     *      )
     *       
     * @param Array $object : json object
     */
    public static function isValidGeoJSONFeature($object) {
        if (!$object || !is_array($object)) {
            return false;
        }
        if (!isset($object['type']) || $object['type'] !== 'Feature') {
            return false;
        }
        if (!isset($object['geometry']) || !is_array($object['geometry'])) {
            return false;
        }
        if (!isset($object['properties']) || !is_array($object['properties'])) {
            return false;
        }
        return true;
    }
    
    /**
     * Transform input string to 7bits ascii equivalent
     * (i.e. remove accent on letters and so on)
     * 
     * @param {string} $text
     */
    public static function asciify($text) {
        return strtr(utf8_decode($text), utf8_decode('ææ̆áàâãäåāăąạắằẵÀÁÂÃÄÅĀĂĄÆəèééêëēĕėęěệÈÉÊĒĔĖĘĚıìíîïìĩīĭịÌÍÎÏÌĨĪĬİḩòóồôõöōŏőợộÒÓÔÕÖŌŎŐØùúûüũūŭůưửÙÚÛÜŨŪŬŮČÇçćĉčċøơßýÿñşšŠŞŚŒŻŽžźżœðÝŸ¥µđÐĐÑŁţğġħňĠĦ'), 'aaaaaaaaaaaaaaaAAAAAAAAAAeeeeeeeeeeeeEEEEEEEEiiiiiiiiiiIIIIIIIIIhoooooooooooOOOOOOOOOuuuuuuuuuuUUUUUUUUCCcccccoosyynssSSSOZZzzzooYYYudDDNLtgghnGH');
    }

    /**
     * Return WKT from geometry
     * @param array $geometry - GeoJSON geometry
     */
    public static function geoJSONGeometryToWKT($geometry) {
        
        $type = strtoupper($geometry['type']);
        if ($type === 'POINT') {
            $wkt = $type . RestoUtil::toPoint($geometry['coordinates']);
        }
        else if ($type === 'MULTIPOINT') {
            $points = array();
            for ($i = 0, $l = count($geometry['coordinates']); $i < $l; $i++) {
                $points[] = RestoUtil::toPoint($geometry['coordinates'][$i]);
            }
            $wkt = $type . '(' . join(',', $points) . ')';
        }
        else if ($type === 'LINESTRING') {
            $wkt = $type . RestoUtil::toLineString($geometry['coordinates']);
        }
        else if ($type === 'MULTILINESTRING') {
            $lineStrings = array();
            for ($i = 0, $l = count($geometry['coordinates']); $i < $l; $i++) {
                $lineStrings[] = RestoUtil::toLineString($geometry['coordinates'][$i]);
            }
            $wkt = $type . '(' . join(',', $lineStrings) . ')';
        }
        else if ($type === 'POLYGON') {
            $wkt = $type . RestoUtil::toPolygon($geometry['coordinates']);
        }
        else if ($type === 'MULTIPOLYGON') {
            $polygons = array();
            for ($i = 0, $l = count($geometry['coordinates']); $i < $l; $i++) {
                $polygons[] = RestoUtil::toPolygon($geometry['coordinates'][$i]);
            }
            $wkt = $type . '(' . join(',', $polygons) . ')';
        }
        return $wkt;
    }
    
    /**
     * Return POINT WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    public static function toPoint($coordinates) {
        return '(' . join(' ', $coordinates) . ')';
    }
    
    /**
     * Return LINESTRING WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    public static function toLineString($coordinates) {
        $pairs = array();
        for ($i = 0, $l = count($coordinates); $i < $l; $i++) {
            $pairs[] = join(' ', $coordinates[$i]);
        }
        return '(' . join(',', $pairs) . ')';
    }
    
    /**
     * Return POLYGON WKT from coordinates (without WKT type)
     * 
     * @param array $coordinates - GeoJSON geometry
     */
    public static function toPolygon($coordinates) {
        $rings = array();
        for ($i = 0, $l = count($coordinates); $i < $l; $i++) {
            $rings[] = RestoUtil::toLineString($coordinates[$i]);
        }
        return '(' . join(',', $rings) . ')';
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
        $paramsStr = null;
        $existingParams = array();
        $exploded = parse_url($url);
        if (isset($exploded['query'])) {
            parse_str($exploded['query'], $existingParams);
        }
        $params = array_merge($existingParams, $newParams);
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                for ($i = count($value); $i--;) {
                    $paramsStr .= (isset($paramsStr) ? '&' : '') . urlencode($key) . '[]=' . urlencode($value[$i]);
                }
            }
            else {
                $paramsStr .= (isset($paramsStr) ? '&' : '') . urlencode($key) . '=' . urlencode($value);
            }
        }
        return (isset($exploded['scheme']) ? $exploded['scheme'] . ':' : '') . '//' .
               (isset($exploded['user']) ? $exploded['user'] . ':' . $exploded['pass'] . '@' : '') .
               $exploded['host'] . (isset($exploded['port']) ? ':' . $exploded['port'] : '') .
               $exploded['path'] . (isset($paramsStr) ? '?' . $paramsStr : '');
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
        return (isset($exploded['scheme']) ? $exploded['scheme'] . ':' : '') . '//' .
               (isset($exploded['user']) ? $exploded['user'] . ':' . $exploded['pass'] . '@' : '') .
               $exploded['host'] . (isset($exploded['port']) ? ':' . $exploded['port'] : '') .
               $path . '.' . $format . (isset($exploded['query']) ? '?' . $exploded['query'] : '');
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
        $patternYear = '\d{4}';

        /* Pattern for matching : YYYY-MM */
        $patternMonthExtend = '\d{4}-\d{2}';

        /* Pattern for matching : YYYY-MM-DD */
        $patternDateExtend = '\d{4}-\d{2}-\d{2}';

        /* Pattern for matching : YYYY-MM-DDTHH:MM:SS */
        $patternDateAndTimeExtend = '\d{4}-\d{2}-\d{2}T\d{2}\:\d{2}\:\d{2}';

        /* Pattern for matching : +HH:MM or -HH:MM */
        $patternTimeZoneExtend = '[\+|\-]\d{2}\:\d{2}';

        /** Pattern for matching : ,n or .n 
         *  where n is the fraction of seconds to one or more digits
         */
        $patternFractionSeconds = '[,|\.]\d+';

        /* Pattern for matching : YYYYMM */
        $patternMonth = '\d{4}\d{2}';

        /* Pattern for matching : YYYYMMDD */
        $patternDate = '\d{4}\d{2}\d{2}';

        /* Pattern for matching : YYYYMMDDTHHMMSS */
        $patternDateAndTime = '\d{4}\d{2}\d{2}T\d{2}\d{2}\d{2}';

        /* Pattern for matching : +HHMM or -HHMM */
        $patternTimeZone = '[\+|\-]\d{2}\d{2}';

        /**
         * Construct the regex to match all ISO 8601 format date case
         * The regex is constructed as a combination of all pattern       
         */
        $completePattern = '/^'
                . $patternYear . '$|^'
                . $patternMonthExtend . '$|^'
                . $patternDateExtend . '$|^'
                . $patternDateAndTimeExtend . '$|^'
                . $patternDateAndTimeExtend . 'Z$|^'
                . $patternDateAndTimeExtend . '' . $patternTimeZoneExtend . '$|^'
                . $patternDateAndTimeExtend . '' . $patternFractionSeconds . '$|^'
                . $patternDateAndTimeExtend . '' . $patternFractionSeconds . 'Z$|^'
                . $patternDateAndTimeExtend . '' . $patternFractionSeconds . '' . $patternTimeZoneExtend . '$|^'
                . $patternMonth . '$|^'
                . $patternDate . '$|^'
                . $patternDateAndTime . '$|^'
                . $patternDateAndTime . 'Z$|^'
                . $patternDateAndTime . '' . $patternTimeZone . '$|^'
                . $patternDateAndTime . '' . $patternFractionSeconds . '$|^'
                . $patternDateAndTime . '' . $patternFractionSeconds . 'Z$|^'
                . $patternDateAndTime . '' . $patternFractionSeconds . '' . $patternTimeZone . '$/i';

        return preg_match($completePattern, $dateStr);
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
     * Return true if $str value is true, 1 or yes
     * Return false otherwise
     * 
     * @param string $str
     */
    public static function toBoolean($str) {

        if (!isset($str)) {
            return false;
        }

        if (strtolower($str) === 'true' || strtolower($str) === 'yes') {
            return true;
        }

        return false;
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

        $output = null;

        /*
         * True by default, False if no file is posted but data posted through parameters
         */
        $isFile = true;

        /*
         * No file is posted
         */
        if (count($_FILES) === 0 || !is_array($_FILES['file'])) {

            /*
             * Is data posted within HTTP request body ?
             */
            $body = file_get_contents('php://input');
            if (isset($body)) {
                $isFile = false;
                $tmpFiles = array($body);
            }
            /*
             * Nothing posted
             */
            else {
                return $output;
            }
        }
        /*
         * A file is posted
         */
        else {

            /*
             * Read file assuming this is ascii file (i.e. plain text, GeoJSON, etc.)
             */
            $tmpFiles = $_FILES['file']['tmp_name'];
            if (!is_array($tmpFiles)) {
                $tmpFiles = array($tmpFiles);
            }
        }

        if (count($tmpFiles) > 1) {
            throw new Exception('Only one file can be posted at a time', 500);
        }

        /*
         * Assume that input data format is JSON by default
         */
        try {
            /*
             * Decode json data
             */
            if ($isFile) {
                $output = json_decode(join('', file($tmpFiles[0])), true);
            }
            else {
                $output = json_decode($tmpFiles[0], true);
            }
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
                $output = file($tmpFiles[0]);
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
     * Return radius length in degrees for a radius in meters
     * at a given latitude
     * 
     * @param float $radius
     * @param float $lat
     */
    public static function radiusInDegrees($radius, $lat) {
        return ($radius * cos(deg2rad($lat))) / 111110.0;
    }

    /**
     * Read include content from a file
     * 
     * @param type $filename
     * @param Object $self
     * @return boolean
     */
    public static function get_include_contents($filename, $self) {
        if (is_file($filename)) {
            ob_start();
            include $filename;
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
        return false;
    }

    /**
     * Transform EPSG:3857 coordinate into EPSG:4326
     * 
     * @param {array} $xy : array(x, y) 
     */
    public static function inverseMercator($xy) {

        if (!is_array($xy) || count($xy) !== 2) {
            return null;
        }

        return array(
            180.0 * $xy[0] / 20037508.34,
            180.0 / M_PI * (2.0 * atan(exp(($xy[1] / 20037508.34) * M_PI)) - M_PI / 2.0)
        );
    }

    /**
     * Transform EPSG:4326 coordinate into EPSG:3857
     * 
     * @param {array} $lonlat : array(lon, lat) 
     */
    public static function forwardMercator($lonlat) {

        if (!is_array($lonlat) || count($lonlat) !== 2) {
            return null;
        }

        /*
         * Latitude limits are -85/+85 degrees
         */
        if ($lonlat[1] > 85 || $lonlat[1] < -85) {
            return null;
        }

        return array(
            $lonlat[0] * 20037508.34 / 180.0,
            max(-20037508.34, min(log(tan((90.0 + $lonlat[1]) * M_PI / 360.0)) / M_PI * 20037508.34, 20037508.34))
        );
    }

    /**
     * Transform EPSG:4326 BBOX to EPSG:3857 bbox
     * 
     * @param {String} $bbox : bbox in EPSG:4326 (i.e. lonmin,latmin,lonmax,latmax) 
     */
    public static function bboxToMercator($bbox) {

        if (!$bbox) {
            return null;
        }
        $coords = explode(',', $bbox);
        if (count($coords) !== 4) {
            return null;
        }

        /*
         * Lower left coordinate
         */
        $ll = RestoUtil::forwardMercator(array(floatval($coords[0]), floatval($coords[1])));
        if (!$ll) {
            return null;
        }

        /*
         * Upper right coordinate
         */
        $ur = RestoUtil::forwardMercator(array(floatval($coords[2]), floatval($coords[3])));
        if (!$ur) {
            return null;
        }

        return join(',', $ll) . ',' . join(',', $ur);
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
        $l = count($quotted);
        if ($l > 1 && $l % 2 === 1) {
            for ($i = 0; $i < $l; $i++) {
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
     * @param integer $speed : speed limit (in MBps)
     * @param type $multipart
     * @return boolean
     */
    public static function download($path, $mimeType = 'application/octet-stream', $speed = -1, $multipart = true) {

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (is_file($path = realpath($path)) === true) {

            $file = @fopen($path, 'rb');
            $size = sprintf('%u', filesize($path));
           
            if (is_resource($file) === true) {
                
                set_time_limit(0);

                /*
                 * Range support
                 * 
                 * In case of multiple ranges requested, only the first range is served
                 * (http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt)
                 */
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
                } else {
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
                    if ($speed !== -1) {
                        echo fread($file, $speed * 1024 * 1024);
                        flush();
                        sleep(1);
                    }
                    else {
                        echo fread($file, 10 * 1024 * 1024);
                        flush();
                    }
                }

                fclose($file);
            }
            else {
                
            }

        }
        else {
            throw new Exception('Not Found', 404);
        }

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

}
