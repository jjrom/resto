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
 * RESTo WMS Utilities
 */
class RestoWMSUtil {

    /*
     * Reference to resto context
     */
    private $context;
    
    /*
     * Reference to resto user
     */
    private $user;
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user) {
        $this->context = $context;
        $this->user = $user;
    }

    /**
     * Stream WMS tiles for $feature in high resolution
     * or in low resolution if $lowResolution is set to true
     * 
     * @param type $feature
     * @param type $lowResolution
     */
    public function streamWMS($feature, $lowResolution = false) {
        
        /*
         * Easy case - no feature or no WMS
         */
        if (!isset($feature)) {
            RestoLogUtil::httpError(404);
        }
        $featureArray = $feature->toArray(); 
        if (!isset($featureArray['properties']['wmsInfos'])) {
            RestoLogUtil::httpError(404);
        }
        
        $this->stream($this->getWMSUrl($featureArray['properties']['wmsInfos'], $lowResolution));
        
    }
    
    /**
     * Generate WMS url depending on resolution
     * Stream back the result image as response of the HTTP request.
     *
     * @param String $wms
     * @param boolean $lowResolution
     */
    private function getWMSUrl($wms, $lowResolution) {
        
        /*
         * Set input query parameters to uppercase
         */
        $query = array();
        foreach ($this->context->query as $key => $value) {
            $query[strtoupper($key)] = $value;
        }
        
        /*
         * Forward only a subset of WMS service parameters
         * Other parameters are superseeded by following code
         */
        $forwardedParameters = array();
        foreach(array('SRS', 'CRS', 'BBOX', 'WIDTH', 'HEIGHT', 'VERSION') as $name) {
            if (isset($query[$name])) {
                $forwardedParameters[$name] = $query[$name];
            }
        }

        /*
         * Superseed the forwarded parameter within the original url
         */
        list($url, $kvpString) = explode('?', $lowResolution ? $this->getLowResolutionUrl($wms) : $wms, 2);
        $kvps = RestoUtil::queryStringToKvps($kvpString, true);
        foreach ($forwardedParameters as $key => $value) {
            $kvps[$key] = $value;
        }
        
        /*
         * Stream the response
         */
         return $url . RestoUtil::kvpsToQueryString($kvps);
    }

    /**
     * Return Low Resolution WMS URL from the Full resolution URL
     * 
     * !! IMPORTANT !!
     * 
     * It is supposed that WMS server provides a low resolution version
     * of the input layers.
     * 
     * The low resolution layer should be named with the following convention
     * 
     *      <fullResolutionName>_lowres
     * 
     * Example:
     * 
     *    LAYERS=mylayer for full resolution implies that low resolution
     *    layer is named LAYERS=mylayer_lowres
     *
     * @param String $wms
     */
    private function getLowResolutionUrl($wms) {
        
        /*
         * Convert full resolution WMS layer to KVP
         */
        list($url, $kvpString) = explode('?', $wms, 2);
        $kvps = RestoUtil::queryStringToKvps($kvpString, true);
        if ($kvps['LAYERS']) {
            $kvps['LAYERS'] = $kvps['LAYERS'] . '_lowres';
        }
        
        return $url . RestoUtil::kvpsToQueryString($kvps);
        
    }

    /**
     * Stream WMS url
     *
     * @return type
     */
    private function stream($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, urldecode($url));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        header('Pragma: public');
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Content-Type: image/png');
        curl_exec($curl);
        curl_close($curl);
    }
}
