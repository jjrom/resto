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

class RestoCart{
    
    /*
     * User identifier
     */
    private $identifier;
    
    /*
     * Database driver
     */
    private $dbDriver;
    
    /*
     * Cart items 
     *  array(
     *      'url' //
     *      'size'
     *      'checksum'
     *      'mimeType'
     *  )
     */
    private $items = array();
    
    /**
     * Constructor
     * 
     * @param string $identifier
     * @param RestoDatabaseDriver $dbDriver
     */
    public function __construct($identifier, $dbDriver, $synchronize = false){
        $this->identifier = $identifier;
        $this->dbDriver = $dbDriver;
        if ($synchronize) {
            $this->items = $this->dbDriver->getCartItems($this->identifier);
        }
    }
    
    /**
     * Add item to cart
     * 
     * @param string $resourceUrl
     * @param boolean $synchronize : true to synchronize with database
     */
    public function add($resourceUrl, $synchronize = false) {
        if (!isset($resourceUrl)) {
            return false;
        }
        
        /*
         * Same resource cannot be added twice
         */
        foreach(array_keys($this->items) as $key) {
            if ($this->items[$key]['url'] === $resourceUrl) {
                return false;
            }
        }
        
        /*
         * Retrieve item info
         */
        $resourceInfo = $this->getResourceInfo($resourceUrl);
        if (!isset($resourceInfo)) {
            $resourceInfo = array();
        }
        $itemId = sha1(mt_rand() . microtime()); 
        if ($synchronize) {
            if (!$this->dbDriver->addToCart($this->identifier, $itemId, $resourceUrl, $resourceInfo)) {
                return false;
            }
        }
        $this->items[$itemId] = array(
            'url' => $resourceUrl,
            'size' => isset($resourceInfo['size']) ? $resourceInfo['size'] : null,
            'checksum' => isset($resourceInfo['checksum']) ? $resourceInfo['checksum'] : null,
            'mimeType' => isset($resourceInfo['mimeType']) ? $resourceInfo['mimeType'] : null
        );
        return true;
    }
    
    /**
     * Remove item from cart
     * 
     * @param string $itemId
     * @param boolean $synchronize : true to synchronize with database
     */
    public function remove($itemId, $synchronize = false) {
        if (!isset($itemId)) {
            return false;
        }
        if ($synchronize) {
            if (isset($this->items[$itemId])) {
                unset($this->items[$itemId]);
            }
            return $this->dbDriver->removeFromCart($this->identifier, $itemId);
        }
        else if (isset($this->items[$itemId])) {
            unset($this->items[$itemId]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns cart for user
     * 
     * @param string $identifier : user identifier
     */
    public function getItems() {
        return $this->items;
    }
    
    /**
     * Return the cart as a metalink XML file
     */
    public function toMETA4() {
        
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        
        $xml->startElement('metalink');
        $xml->writeAttribute('xmlns', 'urn:ietf:params:xml:ns:metalink');
        $xml->writeElement('published', date('Y-m-d\TH:i:sO'));
        
        /*
         * One metalink file per item
         */
        foreach (array_keys($this->items) as $key) {
            $xml->startElement('file');
            if (isset($this->items[$key]['size'])) {
                $xml->writeElement('size', $this->items[$key]['size']);
            }
            //$xml->writeElement('identity', 'TODO');
            $xml->writeElement('version', '1.0');
            $xml->writeElement('language', 'en');
            //$xml->writeElement('description', 'TODO');
            if (isset($this->items[$key]['checksum'])) {
                list($type, $checksum) = explode('=', $this->items[$key]['checksum'], 2);
                $xml->startElement('hash');
                $xml->writeAttribute('type', $type);
                $xml->text($checksum);
                $xml->endElement(); // End hash
            }
            $xml->startElement('url');
            //$xml->writeAttribute('location', 'TODO');
            $xml->writeAttribute('priority', 1);
            $xml->text($this->dbDriver->createSharedLink($this->items[$key]['url']));
            $xml->endElement(); // End url
            $xml->endElement(); // End file
        }
        
        $xml->endElement(); // End metalink
        
        return $xml->outputMemory(true);
        
    }
    
    /**
     * Return resource information from resource url
     * 
     * @param type $resourceUrl
     * @return array
     */
    private function getResourceInfo($resourceUrl) {
        $exploded = parse_url($resourceUrl);
        $segments = explode('/', $exploded['path']);
        $last = count($segments) - 1;
        if ($last > 2) {
            list($modifier) = explode('.', $segments[$last], 1);
            if ($modifier === 'download') {
                return $this->dbDriver->getResourceFields($segments[$last - 1], $segments[$last - 2]);
            }
        }
        return null;
    }
}
