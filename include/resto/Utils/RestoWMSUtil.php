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
     * Configuration
     */
    private $config = array();


    /**
     * Constructor
     */
    public function __construct() {

        /**
         * Read config.php file
         */
        $configFile = realpath(dirname(__FILE__)) . '/../../config.php';
        if (!file_exists($configFile)) {
            RestoLogUtil::httpError(4000, 'Missing mandatory configuration file');
        }
        $this->config = include($configFile);
    }


    /**
     * Generate WMS url depending on user and product license.
     * Stream back the result image as response of the HTTP request.
     *
     * @param $wms
     * @param $license
     * @param $user
     * @param $context
     */
    public function proxifyWMS($wms, $license, $user, $context)
    {
        // Get the URL
        $url = $this->getUrlWMS($wms, $license, $user, $context);

        $query = $context->query;

        // Forward only a subset of WMS service parameters and in no way the layer...
        $forwarded_param_names = array('srs','SRS','bbox','BBOX', 'width', 'WIDTH', 'height', 'HEIGHT');
        $forwarded_params = array();
        foreach($forwarded_param_names as $name) {
            if (isset($query[$name])) {
                $forwarded_params[$name] = $query[$name];
            }
        }

        // Replace the forwarded parameters to the real url
        $url_params = array();
        $url_params_pairs = explode("&", substr($url, strpos($url, "?") + 1));
        foreach($url_params_pairs as $pair) {
            $exp = explode("=", $pair);
            $url_params[$exp[0]] = $exp[1];
        }

        foreach(array_keys($url_params) as $key) {
            if (isset($forwarded_params[strtolower($key)])) {
                $url_params[$key] = $forwarded_params[strtolower($key)];
            } else if (isset($forwarded_params[strtoupper($key)])) {
                $url_params[$key] = $forwarded_params[strtoupper($key)];
            }
        }

        $url_params_pairs = array();
        foreach(array_keys($url_params) as $key) {
            $url_params_pairs[] = $key .'=' . $url_params[$key];
        }

        $url = substr($url, 0, strpos($url, "?") + 1) . implode("&", $url_params_pairs);
//error_log('real url:' . $url);

        // Stream the response
        $this->streamWMSUrl($url);
    }


    /**
     * Proxify WMS URL depending on the requesting user
     *
     * If the product has a license then
     *     If the user is authenticated then
     *        If the user is habilitated to sign the license then display WMS Full resolution
     *        else display WMS Low resolution
     *     else
     *        If the license has the public_visibility_wms = true then WMS Low resolution
     *        else don't display the WMS
     *
     * @param $wms
     * @param $license
     * @param $user
     * @param $context
     * @return string
     */
    private function getUrlWMS($wms, $license, $user, $context) {

        if (isset($wms)) {
//error_log($user->token);
            // First check if the product has a license...
            if (isset($license) && $license != null) {
                if (!isset($user->profile['email'])) {
                    // User is not authenticated
                    if ($user->isPublicVisibleWMS($license)) {
//error_log("cas1");
                        return $this->setLowResolutionWMS($wms);
                    } else {
//error_log("cas2");
                        return null;
                    }
                } else {
                    // User is authenticated
                    $habilited = false;
                    // Must use a try catch statement in order to catch Exception raised when user does not have legal info defined.
                    try {
                        $habilited =  $user->isHabilitedToSignProductLicense($license);
                    } catch (Exception $e){ }

                    if ($habilited) {
//error_log("cas3");
                        return $wms;
                    } else  {
//error_log("cas4");
                        return $this->setLowResolutionWMS($wms);
                    }
                }
            }
            else {
                // No license
                return $wms;
            }
        }
    }

    /**
     * Return Low Resolution WMS URL from the Full resolution URL
     *
     * @param $url_full
     */
    private function setLowResolutionWMS($url_full) {
        $lowResLayer = $this->config['general']['low_resolution_wms_layer'];

        $posLayer = strpos($url_full, "LAYERS=");

        // Build the first part of the URL and set the Low RES layer name
        $lowResUrl = substr($url_full, 0, $posLayer) ."LAYERS=" . $lowResLayer;

        // Append the end of the WMS URL
        $posNextParam = strpos($url_full, "&", $posLayer + 1);
        return $lowResUrl . substr($url_full, $posNextParam);
    }


    /**
     * Stream WMS url
     *
     * @return type
     */
    private function streamWMSUrl($url) {

        /**
         * Init curl
         */
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        $wmsImage = curl_exec($curl);
        curl_close($curl);

        //header("Pragma: no-cache");
        //header("Expires: 0");
        //header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        //header("Cache-Control: no-cache, must-revalidate");
        header("Content-type: image/png");

        echo $wmsImage;
    }


}
