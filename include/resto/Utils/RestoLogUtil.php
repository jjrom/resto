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
