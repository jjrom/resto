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
 * RESTo Feature
 */
class RestoFeature {

    /*
     * Feature unique identifier 
     */
    public $identifier;
    
    /*
     * Context
     */
    public $context;
    
    /*
     * User
     */
    public $user;
    
    /*
     * Parent collection
     */
    public $collection;
    
    /*
     * Feature array
     */
    private $featureArray;
    
    /*
     * Download path on disk
     */
    private $resourceInfos;
    
    /**
     * Constructor 
     * 
     * @param RestoResto $context : Resto Context
     * @param RestoUser $user : Resto user
     * @param array $options : array(
     *                              'featureIdentifier':// string 
     *                              'featureArray':// array()
     *                              'collection' : // RestoCollection
     *                         )
     * 
     * Note that 'featureArray' should only be called by RestoFeatureCollection
     */
    public function __construct($context, $user, $options) {
        $this->context = $context;
        $this->user = $user;
        $this->initialize($options);
    }
    
    /*
     * Download feature product
     */
    public function download() {
        
        /*
         * Not downloadable
         */
        if (!isset($this->featureArray['properties']['services']) || !isset($this->featureArray['properties']['services']['download']))  {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * Download hosted resource with support of Range and Partial Content
         * (See http://stackoverflow.com/questions/157318/resumable-downloads-when-using-php-to-send-the-file)
         */
        if (isset($this->resourceInfos)) {
            
            if (!isset($this->resourceInfos['path']) || !is_file($this->resourceInfos['path'])) {
                RestoLogUtil::httpError(404);;
            }
           
            /*
             * Optimized download with Apache module XsendFile
             */
            if (in_array('mod_xsendfile', apache_get_modules())) {
                return $this->streamApache();
            }
            
            return $this->stream(realpath($this->resourceInfos['path']), isset($this->resourceInfos['mimeType']) ? $this->resourceInfos['mimeType'] : 'application/octet-stream');
            
        }
        /*
         * Resource is on an external url
         */
        else if (RestoUtil::isUrl($this->featureArray['properties']['services']['download']['url'])) {
            return $this->streamExternalUrl();
        }
        /*
         * Not Found
         */
        else {
            RestoLogUtil::httpError(404);;
        }
        
    }
    
    /**
     * Remove feature from database
     */
    public function removeFromStore() {
        $this->context->dbDriver->remove(RestoDatabaseDriver::FEATURE, array('feature' => $this));
    }
    
    /**
     * Output product description as a PHP array
     */
    public function toArray() {
        return $this->featureArray;
    }
    
    /**
     * Output product description as a GeoJSON Feature
     * 
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false) {
        return RestoUtil::json_format($this->featureArray, $pretty);
    }
    
    /**
     * Output product description as an ATOM feed
     */
    public function toATOM() {
        
        /*
         * Initialize ATOM feed
         */
        $atomFeed = new RestoATOMFeed($this->featureArray['id'], isset($this->description['properties']['title']) ? $this->description['properties']['title'] : '', 'TODO');
        
        /*
         * Entry for feature
         */
        $atomFeed->addEntry($this->featureArray, $this->context);
        
        /*
         * Return ATOM result
         */
        return $atomFeed->toString();
        
    }
    
    /**
     * Set feature either from input description or from database
     * 
     * @param array $options
     */
    private function initialize($options) {
        
        if (isset($options['collection'])) {
            $this->collection = $options['collection'];
        }
        
        /*
         * Load from input array
         */
        if (isset($options['featureIdentifier'])) {
            $this->featureArray = $this->context->dbDriver->get(RestoDatabaseDriver::FEATURE_DESCRIPTION, array(
                'context' => $this->context,
                'featureIdentifier' => $options['featureIdentifier'],
                'collection' => isset($this->collection) ? $this->collection : null
            ));
        }
        /*
         * ...or load from database
         */
        else {
            $this->featureArray = $options['featureArray'];
        }
        
        $this->identifier = $this->featureArray['id'];
        $this->setCollection($this->featureArray['properties']['collection']);
        
    }
    
    /**
     * 
     * Download hosted resource with support of Range and Partial Content
     * (See http://stackoverflow.com/questions/3697748/fastest-way-to-serve-a-file-using-php)
     *
     * @param string $path
     * @param string $mimeType
     * @param type $multipart
     * @return boolean
     */
    private function stream($path, $mimeType = 'application/octet-stream', $multipart = true) {

        /*
         * Open file
         */
        $file = fopen($path, 'rb');
        if (!is_resource($file)) {
            RestoLogUtil::httpError(404);
        }
        
        /*
         * Set range and headers
         */
        $size = sprintf('%u', filesize($path));
        $range = $multipart ? $this->getMultipartRange($size, filter_input(INPUT_SERVER, 'HTTP_RANGE', FILTER_SANITIZE_STRING)) : $this->getSimpleRange($size);
        $this->setDownloadHeaders($mimeType, $path, $range);
        
        /*
         * Read file
         */
        $this->readFile($file, $range);
        
        fclose($file);
        
    }
    
    /**
     * Flush result
     * 
     * @param File $file
     * @param array $range
     */
    private function readFile($file, $range) {
        
        /*
         * Multipart case
         */
        if ($range[0] > 0) {
            fseek($file, $range[0]);
        }

        /*
         * Stream result
         */
        while ((feof($file) !== true) && (connection_status() === CONNECTION_NORMAL)) {
            echo fread($file, 10 * 1024 * 1024);
            set_time_limit(0);
            flush();
        }
        
    }
    
    /**
     * Get range from HTTP_RANGE and set headers accordingly
     * 
     * In case of multiple ranges requested, only the first range is served
     * (http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt)
     * 
     * @param integer $size
     * 
     */
    private function getSimpleRange($size) {
        return array(0, $size - 1);
    }
    
    /**
     * Get range from HTTP_RANGE and set headers accordingly
     * 
     * In case of multiple ranges requested, only the first range is served
     * (http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt)
     * 
     * @param integer $size
     * @param string $httpRange
     * 
     */
    private function getMultipartRange($size, $httpRange) {
        $range = array(0, $size - 1);
        if (isset($httpRange)) {
            $range = array_map('intval', explode('-', preg_replace('~.*=([^,]*).*~', '$1', $httpRange)));
            if (empty($range[1]) === true) {
                $range[1] = $size - 1;
            }
            foreach ($range as $key => $value) {
                $range[$key] = max(0, min($value, $size - 1));
            }
            if (($range[0] > 0) || ($range[1] < ($size - 1))) {
                header(sprintf('%s %03u %s', 'HTTP/1.1', 206, 'Partial Content'), true, 206);
            }
        }
        header('Accept-Ranges: bytes');
        header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));
        return $range;
    }
    
    /**
     * Set HTTP headers for download
     * 
     * @param type $mimeType
     * @param type $path
     * @param type $range
     */
    private function setDownloadHeaders($mimeType, $path, $range) {
        header('Pragma: public');
        header('Cache-Control: public, no-cache');
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . sprintf('%u', $range[1] - $range[0] + 1));
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Transfer-Encoding: binary');
    }
    
    /**
     * Stream file using Apache XSendFile
     * 
     * @return type
     */
    private function streamApache() {
        header('HTTP/1.1 200 OK');
        header('Pragma: public');
        header('Expires: -1');
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
        header('X-Sendfile: ' . $this->resourceInfos['path']);
        header('Content-Type: ' . isset($this->resourceInfos['mimeType']) ? $this->resourceInfos['mimeType'] : 'application/unknown');
        header('Content-Disposition: attachment; filename="' . basename($this->resourceInfos['path']) . '"');
        header('Accept-Ranges: bytes');
    }
  
    /**
     * Stream file from external url
     * 
     * @return type
     */
    private function streamExternalUrl() {
        $handle = fopen($this->featureArray['properties']['services']['download']['url'], "rb");
        if ($handle === false) {
            RestoLogUtil::httpError(500, 'Resource cannot be downloaded');
        }
        header('HTTP/1.1 200 OK');
        header('Content-Disposition: attachment; filename="' . basename($this->featureArray['properties']['services']['download']['url']) . '"');
        header('Content-Type: ' . isset($this->featureArray['properties']['services']['download']['mimeType']) ? $this->featureArray['properties']['services']['download']['mimeType'] : 'application/unknown');
        while (!feof($handle) && (connection_status() === CONNECTION_NORMAL)) {
            echo fread($handle, 10 * 1024 * 1024);
            flush();
        }
        return fclose($handle);
    }
      
    /**
     * Retrieve collection if not set
     * 
     * @param string $collectionName
     */
    private function setCollection($collectionName) {
        if (!isset($this->collection)) {
            $this->collection = new RestoCollection($collectionName, $this->context, $this->user, array('autoload' => true));
        }
    }
  
}
