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
 * RESTo cache module is used by database driver to store queries in cache
 */
class RestoCache {
    
    /*
     * Cache directory - if not set, no cache !
     */
    private $directory;

    /**
     * Constructor
     * 
     * @param string $directory : cache directory (must be readable+writable for Webserver user)
     */
    public function __construct($directory) {
        if (isset($directory) && is_writable($directory)) {
            $this->directory = $directory;
        }
    }

    /**
     * Retrieve cached request result
     * 
     * @param array $arr
     */
    public function retrieve($arr) {
        if (!$this->directory) {
            return null;
        }
        $fileName = $this->getCacheFileName($arr);
        if (!$this->isInCache($fileName)) {
            return null;
        }
        return $this->read($fileName);
    }
    
    /**
     * Store result in cache
     * 
     * @param array $arr
     * @param array $obj
     */
    public function store($arr, $obj) {
        if (!$this->directory) {
            return null;
        }
        $fileName = $this->getCacheFileName($arr);
        return $this->write($fileName, $obj);
    }
    
    /**
     * Generate a unique cache fileName from input array
     * 
     * @param array $arr
     */
    private function getCacheFileName($arr) {
        if (!isset($arr) || !is_array($arr) || count($arr) === 0) {
            return null;
        }
        return sha1(serialize($arr)) . '.cache';
    }
    
    /**
     * Return true if fileName is in cache
     * 
     * @param type $fileName
     */
    private function isInCache($fileName) {
        if (!isset($fileName) || !isset($this->directory)) {
            return false;
        }
        if (file_exists($this->directory . DIRECTORY_SEPARATOR . $fileName)) {
            return true;
        }
        return false;
    }
    
    /**
     * Read from cached file
     * 
     * @param $fileName - name of the cached file
     */
    private function read($fileName) {
        if (!$this->isInCache($fileName)) {
            return null;
        }
        $fileName = $this->directory . DIRECTORY_SEPARATOR . $fileName;
        $handle = fopen($fileName, 'rb');
        $obj = fread($handle, filesize($fileName));
        fclose($handle);
        return unserialize($obj);
    }

    /**
     * Write database result to cached file
     * 
     * @param string $fileName - name of the cached file
     * @param object $obj - Object to store in cache
     */
    private function write($fileName, $obj) {
        if (!$this->directory) {
            return null;
        }
        $handle = fopen($this->directory . DIRECTORY_SEPARATOR . $fileName, 'a');
        fwrite($handle, serialize($obj));
        fclose($handle);
        return $fileName;
    }

    /**
     * Delete cached file
     * 
     * @param $fileName - name of the cached file
     */
    private function delete($fileName) {
        unlink($this->directory . DIRECTORY_SEPARATOR . $fileName);
    }

}
