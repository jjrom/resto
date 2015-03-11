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
        foreach ($list as $key => $value) {
            $this->xml->writeAttribute($key, $value);
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
