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
 * RESTo cache is used by database driver to store queries in cache
 */
class RestoCache
{
    
    /*
     * Cache directory - if not set, no cache !
     */
    private $directory;

    /**
     * Constructor
     *
     * @param string $directory : cache directory (must be readable+writable for Webserver user)
     */
    public function __construct($directory = '/cache')
    {
        if (isset($directory) && is_writable($directory)) {
            $this->directory = $directory;
        }
    }

    /**
     * Clear cache
     *
     * @param string $key
     */
    public function clear($key = null)
    {   
        if (isset($this->directory)) {
            if (isset($key)) {
                $file = $this->directory . DIRECTORY_SEPARATOR . crc32($key);
                if (is_file($file)) {
                    unlink($file);
                }
            }
            else {
                $files = glob($this->directory . DIRECTORY_SEPARATOR . '*');
                foreach($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }

    /**
     * Retrieve cached request result
     *
     * @param string $key
     */
    public function retrieve($key)
    {
        return isset($this->directory) && isset($key) ? $this->read(crc32($key)) : null;
    }
    
    /**
     * Store result in cache
     *
     * @param string $key
     * @param array $obj
     */
    public function store($key, $obj)
    {
        return isset($this->directory) && isset($key) ? $this->write(crc32($key), $obj) : null;
    }
    
    /**
     * Return true if fileName is in cache
     *
     * @param string $fileName
     */
    private function isInCache($fileName)
    {
        if ( file_exists($fileName) ) {
            return true;
        }
        return false;
    }
    
    /**
     * Read from cached file
     *
     * @param $key - name of the cached file key
     */
    private function read($key)
    {

        $fileName = $this->directory . DIRECTORY_SEPARATOR . $key;

        if (!$this->isInCache($fileName)) {
            return null;
        }

        $handle = fopen($fileName, 'rb');
        $obj = fread($handle, filesize($fileName));
        fclose($handle);
        return json_decode($obj, true);
    }

    /**
     * Write database result to cached file
     *
     * @param string $key - name of the cached file key
     * @param object $obj - Object to store in cache
     */
    private function write($key, $obj)
    {
        $handle = fopen($this->directory . DIRECTORY_SEPARATOR . $key, 'w');
        fwrite($handle, json_encode($obj, JSON_UNESCAPED_SLASHES));
        fclose($handle);
        return $key;
    }
    
}
