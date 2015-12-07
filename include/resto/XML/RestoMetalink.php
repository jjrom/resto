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
 * resto Metalink class
 */
class RestoMetalink extends RestoXML {
    
    /*
     * Context reference
     */
    private $context;
    
    /*
     * User reference
     */
    private $user;
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     * 
     */
    public function __construct($context, $user) {
        parent::__construct();
        $this->context = $context;
        $this->user = $user;
        $this->initialize();
    }
    
    /**
     * Add link to metalink
     * 
     * @param array $item
     * @param RestoUser $user
     * 
     * @return type
     */
    public function addLink($item) {
        
        /*
         * Compute file name from productIdentifier and mimeType - identifier otherwise
         */
        $this->startElement('file');
        $this->writeAttribute('name', (isset($item['properties']['productIdentifier']) ? $item['properties']['productIdentifier'] : $item['id']) . (isset($item['properties']['services']['download']['mimeType']) ? $this->getExtension($item['properties']['services']['download']['mimeType']) : ''));
        
        if (isset($item['properties']['services']['download']['size'])) {
            $this->writeElement('size', $item['properties']['services']['download']['size']);
        }
        
        $this->writeElements(array(
            //'identity' => 'TODO',
            'version' => '1.0',
            'language' => 'en'
            //'description', 'TODO'
        ));
        
        /*
         * Checksum
         */
        $this->addChecksum($item);
        
        /*
         * Url
         */
        $this->addUrl($item);
        
        $this->endElement(); // End file
    }

    /**
     * Return stringified XML document
     */
    public function toString() {
        $this->endElement(); // End metalink
        return parent::toString();
    }
    
    /**
     * Set Metalink in META4 format
     * @return type
     */
    private function initialize() {
        $this->startElement('metalink');
        $this->writeAttributes(array(
            'xmlns' => 'urn:ietf:params:xml:ns:metalink'
        ));
        $this->writeElement('published', date('Y-m-d\TH:i:sO'));
    }
    
    /**
     * Add file checksum if available
     * 
     * @param array $item
     */
    private function addChecksum($item) {
        if (isset($item['properties']['services']['download']['checksum'])) {
            list($type, $checksum) = explode('=', $item['properties']['services']['download']['checksum'], 2);
            $this->startElement('hash');
            $this->writeAttribute('type', $type);
            $this->text($checksum);
            $this->endElement(); // End hash
        }
    }
    
    /**
     * Add file checksum if available
     * 
     * @param array $item
     */
    private function addUrl($item) {
        $this->startElement('url');
        $this->writeAttributes(array(
            //'location' => 'TODO',
            'priority' => 1
        ));
        $this->text($item['properties']['services']['download']['url']);
        $this->endElement(); // End url
    }
    
    /**
     * Return extension from mimeType
     * 
     * @param string $mimeType
     */
    private function getExtension($mimeType) {
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
    
}
