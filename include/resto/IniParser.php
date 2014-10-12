<?php

/**
 * Parse ini file exploding '.' into arrays
 * 
 * From http://www.php.net/manual/ro/function.parse-ini-file.php
 */
class IniParser {

    /**
     *  WRITE
     */
    static function write($filename, $ini) {
        $string = '';
        foreach (array_keys($ini) as $key) {
            $string .= '[' . $key . "]\n";
            $string .= IniParser::write_get_string($ini[$key], '') . "\n";
        }
        file_put_contents($filename, $string);
    }

    /**
     *  write get string
     */
    static function write_get_string(& $ini, $prefix) {
        $string = '';
        ksort($ini);
        foreach ($ini as $key => $val) {
            if (is_array($val)) {
                $string .= IniParser::write_get_string($ini[$key], $prefix . $key . '.');
            } else {
                $string .= $prefix . $key . ' = ' . str_replace("\n", "\\\n", IniParser::set_value($val)) . "\n";
            }
        }
        return $string;
    }

    /**
     *  manage keys
     */
    static function set_value($val) {
        if ($val === true) {
            return 'true';
        } else if ($val === false) {
            return 'false';
        }
        return $val;
    }

    /**
     *  READ
     */
    static function read($filename) {
        $ini = array();
        $lines = file($filename);
        $section = 'default';
        $multi = '';
        foreach ($lines as $line) {
            if (substr($line, 0, 1) !== ';') {
                $line = str_replace("\r", "", str_replace("\n", "", $line));
                if (preg_match('/^\[(.*)\]/', $line, $m)) {
                    $section = $m[1];
                } else if ($multi === '' && preg_match('/^([a-z0-9_.\[\]-]+)\s*=\s*(.*)$/i', $line, $m)) {
                    $key = $m[1];
                    $val = $m[2];
                    if (substr($val, -1) !== "\\") {
                        $val = trim($val);
                        IniParser::manage_keys($ini[$section], $key, $val);
                        $multi = '';
                    } else {
                        $multi = substr($val, 0, -1) . "\n";
                    }
                } else if ($multi !== '') {
                    if (substr($line, -1) === "\\") {
                        $multi .= substr($line, 0, -1) . "\n";
                    } else {
                        IniParser::manage_keys($ini[$section], $key, $multi . $line);
                        $multi = '';
                    }
                }
            }
        }

        $buf = get_defined_constants(true);
        $consts = array();
        if (isset($buf['user'])) {
            foreach ($buf['user'] as $key => $val) {
                $consts['{' . $key . '}'] = $val;
            }
        }
        array_walk_recursive($ini, array('IniParser', 'replace_consts'), $consts);
        return $ini;
    }

    /**
     *  manage keys
     */
    static function get_value($val) {
        if (preg_match('/^-?[0-9]$/i', $val)) {
            return intval($val);
        } else if (strtolower($val) === 'true') {
            return true;
        } else if (strtolower($val) === 'false') {
            return false;
        } else if (preg_match('/^"(.*)"$/i', $val, $m)) {
            return $m[1];
        } else if (preg_match('/^\'(.*)\'$/i', $val, $m)) {
            return $m[1];
        }
        return $val;
    }

    /**
     *  manage keys
     */
    static function get_key($val) {
        if (preg_match('/^[0-9]$/i', $val)) {
            return intval($val);
        }
        return $val;
    }

    /**
     *  manage keys
     */
    static function manage_keys(& $ini, $key, $val) {
        if (preg_match('/^([a-z0-9_-]+)\.(.*)$/i', $key, $m)) {
            IniParser::manage_keys($ini[$m[1]], $m[2], $val);
        } else if (preg_match('/^([a-z0-9_-]+)\[(.*)\]$/i', $key, $m)) {
            if ($m[2] !== '') {
                $ini[$m[1]][IniParser::get_key($m[2])] = IniParser::get_value($val);
            } else {
                $ini[$m[1]][] = IniParser::get_value($val);
            }
        } else {
            $ini[IniParser::get_key($key)] = IniParser::get_value($val);
        }
    }

    /**
     *  replace utility
     */
    static function replace_consts(& $item, $key, $consts) {
        if (is_string($item)) {
            $item = strtr($item, $consts);
        }
    }

}