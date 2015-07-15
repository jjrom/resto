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

class RestoXML {
    
    /*
     * Reference to XML document
     */
    protected $xml;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->initialize();
    }
    
    /**
     * Write attribute to current XML element
     * 
     * @param string $key
     * @param string $value
     */
    public function writeAttribute($key, $value) {
        $this->xml->writeAttribute($key, $value);
    }
    
    /**
     * Write attributes to current XML element
     * 
     * @param array $list
     */
    public function writeAttributes($list) {
        if (is_array($list)) {
            foreach ($list as $key => $value) {
                $this->xml->writeAttribute($key, $value);
            }
        }
    }
    
    /**
     * Start element to the current XML document
     * 
     * @param string $name
     */
    public function startElement($name) {
        $this->xml->startElement($name);
    }
    
    /**
     * End current XML element
     */
    public function endElement() {
        $this->xml->endElement();
    }
    
    /**
     * Write element
     * 
     * @param string $name
     * @param string $value
     */
    public function writeElement($name, $value) {
        $this->xml->writeElement($name, $value);
    }
    
    /**
     * Write elements
     * 
     * @param array $list
     */
    public function writeElements($list) {
        foreach ($list as $key => $value) {
            $this->xml->writeElement($key, $value);
        }
    }
    
    /**
     * Set text to the current XML element
     * 
     * @param string $text
     */
    public function text($text) {
        $this->xml->text($text);
    }
    
    /**
     * Return stringified XML document
     */
    public function toString() {
        
        /*
         * Write result
         */
        return $this->xml->outputMemory(true);
    }
    
    /**
     * Initialize XML document
     */
    private function initialize() {
        $this->xml = new XMLWriter();
        $this->xml->openMemory();
        $this->xml->setIndent(true);
        $this->xml->startDocument('1.0', 'UTF-8');
    }
    
}
