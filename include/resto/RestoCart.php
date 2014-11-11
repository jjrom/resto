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
     * Owner of the cart
     */
    private $user;
    
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
     * @param RestoUser $user
     * @param RestoDatabaseDriver $dbDriver
     */
    public function __construct($user, $dbDriver, $synchronize = false){
        $this->user = $user;
        $this->dbDriver = $dbDriver;
        if ($synchronize) {
            $this->items = $this->dbDriver->getCartItems($this->user->profile['email']);
        }
    }
    
    /**
     * Add item to cart
     * 
     * @param array $item
     * @param boolean $synchronize : true to synchronize with database
     */
    public function add($item, $synchronize = false) {
        
        if (!is_array($item) || !isset($item['url'])) {
            return false;
        }
        
        /*
         * Same resource cannot be added twice
         */
        $itemId = sha1($this->user->profile['email'] . $item['url']);
        if (isset($this->items[$itemId])) {
            return $itemId;
        }
        
        /*
         * Add resource info to item description
         * Note : existing info ARE NOT superseeded 
         */
        $resourceInfo = $this->getResourceInfo($item['url']);
        if (isset($resourceInfo)) {
            foreach (array_keys(array('size', 'checksum', 'mimeType', 'collection', 'identifier')) as $key) {
                if (!isset($item[$key]) && isset($resourceInfo[$key])) {
                    $item[$key] = $resourceInfo[$key];
                }
            }
        }
        if ($synchronize) {
            if (!$this->dbDriver->addToCart($this->user->profile['email'], $item)) {
                return false;
            }
        }
        $this->items[$itemId] = $item;
        
        return $itemId;
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
            return $this->dbDriver->removeFromCart($this->user->profile['email'], $itemId);
        }
        else if (isset($this->items[$itemId])) {
            unset($this->items[$itemId]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns all items from cart
     */
    public function getItems() {
        return $this->items;
    }
    
    /**
     * Return the cart as a JSON file
     * 
     * @param boolean $pretty
     */
    public function toJSON($pretty) {
        return RestoUtil::json_format($this->getItems(), $pretty);
    }
    
    /**
     * Return the cart as a metalink XML file
     * 
     * Warning ! a link is created only for resource that can be downloaded by users
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
         * One metalink file per item - if user has rights to download file
         */
        foreach (array_keys($this->items) as $key) {
            if (isset($this->items[$key]['url']) && RestoUtil::isUrl($this->items[$key]['url'])) {
                $exploded = parse_url($this->items[$key]['url']);
                $segments = explode('/', $exploded['path']);
                $last = count($segments) - 1;
                if ($last > 2) {
                    list($modifier) = explode('.', $segments[$last], 1);
                    if ($modifier !== 'download' || !$this->user->canDownload($segments[$last - 2], $segments[$last - 1])) {
                        continue;
                    }
                }
            }
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
