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
 * RESTo log utilities functions
 */
class RestoLogUtil {
    
    /*
     * Debug mode
     */
    public static $debug = false;
    
    /*
     * HTTP and resto codes
     */
    public static $codes = array(
        
        /*
         *  http codes
         */
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        /*
         *  resto codes
         */
        1000 => 'Cannot add item to cart because item already exist',
        1001 => 'Cannot update item in cart because item does not exist in cart',
        2000 => 'Abort create collection - schema does not exist',
        2001 => 'Abort create collection collection not created',
        2003 => 'Cannot create collection collection already exist',
        3000 => 'Cannot create user - user already exists',
        3001 => 'Cannot create user - cannot send activation code',
        3002 => 'User has to sign license',
        3003 => 'Cannot send password reset link',
        3004 => 'Cannot reset password for a non local user',
        4000 => 'Configuration file problem',
        4001 => 'Dictionary is not instantiable',
        4002 => 'Database driver does not exist',
        4003 => 'Database driver is not instantiable',
        4004 => 'Invalid input object',
        4005 => 'Invalid input array'
    );
    
    /*
     * Throw HTTP error
     */
    public static function httpError($code, $message = null) {
        $error = isset($message) ? $message : (isset(RestoLogUtil::$codes[$code]) ? RestoLogUtil::$codes[$code] : 'Unknown error');
        if (RestoLogUtil::$debug) {
            $trace = debug_backtrace();
            throw new Exception($trace[1]['function'] . ' - ' . $error, $code);
        }
        else {
            throw new Exception($error, $code);
        }
    }
    
    /**
     * Return success execution status as an array
     *  
     * @param string $message
     * @param array $additional
     */
    public static function success($message, $additional = array()) {
        return RestoLogUtil::message('success', $message, $additional);
    }
    
    /**
     * Return error execution status as an array
     *  
     * @param string $message
     * @param array $additional
     */
    public static function error($message, $additional = array()) {
        return RestoLogUtil::message('error', $message, $additional);
    }
    
    /**
     * Return output execution status as an array
     *  
     * @param string $message
     * @param array $additional
     */
    public static function message($status, $message, $additional = array()) {
        return array_merge(array(
            'status' => $status,
            'message' => $message
        ), $additional);
    }
    
    
    
}
