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
 * Utility class above curl
 */
class Curly
{
    public $handler;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (! extension_loaded('curl')) {
            RestoLogUtil::httpError(500, 'Curl extension not loaded');
        }

        $this->handler = curl_init();
        
        // Set default
        $this->setDefault();
    }

    /**
     * Send a GET to $url
     *
     * @param string $url
     */
    public function get($url)
    {
        curl_setopt($this->handler, CURLOPT_URL, $url);
        curl_setopt($this->handler, CURLOPT_HTTPGET, true);
        return curl_exec($this->handler);
    }

    /**
     * Send a POST to $url
     *
     * @param string $url
     */
    public function post($url, $body)
    {
        curl_setopt($this->handler, CURLOPT_URL, $url);
        curl_setopt($this->handler, CURLOPT_POST, true);
        curl_setopt($this->handler, CURLOPT_POSTFIELDS, $body);
        return curl_exec($this->handler);
    }

    /**
     * Set Headers
     *
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        curl_setopt($this->handler, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Set Options
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            curl_setopt($this->handler, $key, $value);
        }
    }

    /**
     * Close session
     */
    public function close()
    {
        if (is_resource($this->handler)) {
            curl_close($this->handler);
        }
    }

    /**
     * Set default value
     */
    private function setDefault()
    {
        // Default headers JSON
        $this->setHeaders(array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));

        curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, true);
    }
}
