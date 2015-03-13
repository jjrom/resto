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
 * resto Metalink class
 */
class RestoMetalink extends RestoXML {
    
    /*
     * Context reference
     */
    private $context;
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     */
    public function __construct($context) {
        parent::__construct();
        $this->context = $context;
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
        $this->writeAttribute('name', (isset($item['productIdentifier']) ? $item['productIdentifier'] : $item['id']) . (isset($item['properties']['services']['download']['mimeType']) ? $this->getExtension($item['properties']['services']['download']['mimeType']) : ''));
        
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
     * Return a sharable public link from input resourceUrl
     * 
     * @param string $resourceUrl
     * @return string
     */
    private function getSharedLink($resourceUrl) {
        $shared = $this->context->dbDriver->get(RestoDatabaseDriver::SHARED_LINK, array('resourceUrl' => $resourceUrl));
        return $resourceUrl . (strrpos($resourceUrl, '?') === false ? '?_tk=' : '&_tk=') . $shared['token'];       
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
        $this->text($this->getSharedLink($item['properties']['services']['download']['url']));
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
