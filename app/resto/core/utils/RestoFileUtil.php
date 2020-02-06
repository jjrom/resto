<?php
/*
 * Copyright 2018 Jérôme Gasperi
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
 * resto File Utilities functions
 */
class RestoFileUtil
{


    /**
     * Physcally remove upload files using multipart in body
     * 
     * @param {Object} $body
     */
    public static function clearUpload($body)
    {
        
        try {

            if ( isset($body) && is_array($body['files']) ) {
                for ($i = count($body['files']); $i--;) {
                    unlink($body['files'][$i]);
                }
            }
            if  ( isset($body['uploadDir']) ) {
                rmdir($body['uploadDir']);
            }

        }
        catch (Exception $e) {
            error_log('[WARNING] Error during clearUpload');
        }
        
    }
 

    /**
     * Return type and path of input array of $files
     * 
     * @param array $files
     * @return array
     */
    public static function whatIsIt($files)
    {

        $type = 'unknown';

        if ( !is_array($files) || count($files) === 0 ) {
            return array(
                'type' => $type,
                'path' => null,
                'isReadable' => false,
                'isFile' => false
            );
        }

        if ( $path = RestoFileUtil::getShapefilePath($files) ) {
            $type = 'shp';
        }

        return array(
            'type' => $type,
            'path' => $path,
            'isReadable' => isset($path) ? is_readable($path) : false,
            'isFile' => isset($path) ? is_file($path) : false
        );

    }

    /**
     * Return path from input $files if it is a shapefile - null otherwise
     * 
     * @param array $files
     * @return array
     */
    private static function getShapefilePath($files)
    {
          
        /*
         * Check shapefile is complete i.e. contains XXX.shp, XXX.dbf and XXX.shx
         */
        $path = null;
        for ($i = count($files); $i--;) {
            if ( strtolower(substr($files[$i], -3)) === 'shp') {
                $path = $files[$i];
                break;
            }
        }
        
        return  isset($path) && is_readable($path) && is_file($path) ? $path : null;

    }

}
