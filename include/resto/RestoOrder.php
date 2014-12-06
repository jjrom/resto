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

class RestoOrder{
    
    /*
     * Context
     */
    public $context;
    
    /*
     * Owner
     */
    public $user;
    
    /*
     * Order items
     *  array(
     *      'url' //
     *      'size'
     *      'checksum'
     *      'mimeType'
     *  )
     */
    private $order = array();
    
    /**
     * Constructor
     * 
     * @param RestoUser $user
     * @param RestoContext $context
     */
    public function __construct($user, $context, $orderId){
        $this->user = $user;
        $this->context = $context;
        $this->order = $this->context->dbDriver->getOrders($this->user->profile['email'], $orderId);
    }
    
    /**
     * Return the cart as a JSON file
     * 
     * @param boolean $pretty
     */
    public function toJSON($pretty) {
        return  RestoUtil::json_format(array(
            'status' => 'success',
            'message' => 'Order ' . $this->order['orderId'] . ' for user ' . $this->user->profile['email'],
            'order' => $this->order
        ), $pretty);
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
        $items = $this->order['items'];
        foreach (array_keys($items) as $key) {
            if (!isset($items[$key]['properties']) || !isset($items[$key]['properties']['services']) || !isset($items[$key]['properties']['services']['download'])) {
                continue;
            }
            if (isset($items[$key]['properties']['services']['download']['url']) && RestoUtil::isUrl($items[$key]['properties']['services']['download']['url'])) {
                $exploded = parse_url($items[$key]['properties']['services']['download']['url']);
                $segments = explode('/', $exploded['path']);
                $last = count($segments) - 1;
                if ($last > 2) {
                    list($modifier) = explode('.', $segments[$last], 1);
                    if ($modifier !== 'download' || !$this->user->canDownload($segments[$last - 2], $segments[$last - 1])) {
                        continue;
                    }
                }
            
                $xml->startElement('file');
                if (isset($items[$key]['properties']['services']['download']['size'])) {
                    $xml->writeElement('size', $items[$key]['properties']['services']['download']['size']);
                }
                //$xml->writeElement('identity', 'TODO');
                $xml->writeElement('version', '1.0');
                $xml->writeElement('language', 'en');
                //$xml->writeElement('description', 'TODO');
                if (isset($items[$key]['properties']['services']['download']['checksum'])) {
                    list($type, $checksum) = explode('=', $items[$key]['properties']['services']['download']['checksum'], 2);
                    $xml->startElement('hash');
                    $xml->writeAttribute('type', $type);
                    $xml->text($checksum);
                    $xml->endElement(); // End hash
                }
                $xml->startElement('url');
                //$xml->writeAttribute('location', 'TODO');
                $xml->writeAttribute('priority', 1);
                $xml->text($this->context->dbDriver->createSharedLink($items[$key]['properties']['services']['download']['url']));
                $xml->endElement(); // End url
                $xml->endElement(); // End file
            }
        }
        
        $xml->endElement(); // End metalink
        
        return $xml->outputMemory(true);
        
    }
    
}
