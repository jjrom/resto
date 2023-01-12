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
 * RESTo log utilities functions
 */
class RestoLogUtil
{
    /*
     * Timestamp mode
     */
    public static $timestamp;

    /*
     * Debug mode
     */
    public static $debug = false;

    /*
     * HTTP codes
     */
    public static $codes = array(
        200 => 'OK',
        206 => 'Partial Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        409 => 'Conflict',
        412 => 'Precondition Failed',
        500 => 'Internal Server Error'
    );

    /**
     * Throw HTTP error
     *
     * @OA\Schema(
     *      schema="GenericError",
     *      required={"ErrorCode", "ErrorMessage"},
     *      @OA\Property(
     *          property="ErrorCode",
     *          type="integer",
     *          description="HTTP status code"
     *      ),
     *      @OA\Property(
     *          property="ErrorMessage",
     *          type="string",
     *          description="Error message"
     *      )
     *  )
     *
     * @OA\Schema(
     *      schema="ConflictError",
     *      required={"ErrorCode", "ErrorMessage"},
     *      @OA\Property(
     *          property="ErrorCode",
     *          type="integer",
     *          description="HTTP status code"
     *      ),
     *      @OA\Property(
     *          property="ErrorMessage",
     *          type="string",
     *          description="Error message"
     *      ),
     *      example={
     *          "ErrorCode":409,
     *          "ErrorMessage":"Conflict"
     *      }
     *  )
     *
     * @OA\Schema(
     *      schema="ForbiddenError",
     *      required={"ErrorCode", "ErrorMessage"},
     *      @OA\Property(
     *          property="ErrorCode",
     *          type="integer",
     *          description="HTTP status code"
     *      ),
     *      @OA\Property(
     *          property="ErrorMessage",
     *          type="string",
     *          description="Error message"
     *      ),
     *      example={
     *          "ErrorCode":403,
     *          "ErrorMessage":"Forbidden"
     *      }
     *  )
     *
     *  @OA\Schema(
     *      schema="UnauthorizedError",
     *      required={"ErrorCode", "ErrorMessage"},
     *      @OA\Property(
     *          property="ErrorCode",
     *          type="integer",
     *          description="HTTP status code"
     *      ),
     *      @OA\Property(
     *          property="ErrorMessage",
     *          type="string",
     *          description="Error message"
     *      ),
     *      example={
     *          "ErrorCode":401,
     *          "ErrorMessage":"Unauthorized"
     *      }
     *  )
     *
     *  @OA\Schema(
     *      schema="BadRequestError",
     *      required={"ErrorCode", "ErrorMessage"},
     *      @OA\Property(
     *          property="ErrorCode",
     *          type="integer",
     *          description="HTTP status code"
     *      ),
     *      @OA\Property(
     *          property="ErrorMessage",
     *          type="string",
     *          description="Error message"
     *      ),
     *      example={
     *          "ErrorCode":400,
     *          "ErrorMessage":"Bad request"
     *      }
     *  )
     *
     *  @OA\Schema(
     *      schema="NotFoundError",
     *      required={"ErrorCode", "ErrorMessage"},
     *      @OA\Property(
     *          property="ErrorCode",
     *          type="integer",
     *          description="HTTP status code"
     *      ),
     *      @OA\Property(
     *          property="ErrorMessage",
     *          type="string",
     *          description="Error message"
     *      ),
     *      example={
     *          "ErrorCode":404,
     *          "ErrorMessage":"Not Found"
     *      }
     *  )
     */
    public static function httpError($code, $message = null)
    {
        $error = $message ?? (RestoLogUtil::$codes[$code] ?? 'Unknown error');
        if (RestoLogUtil::$debug) {
            $trace = debug_backtrace();
            throw new Exception($trace[1]['function'] . ' - ' . $error, $code);
        } else {
            throw new Exception($error, $code);
        }
    }

    /**
     * Return success execution status as an array
     *
     * @param string $message
     * @param array $additional
     */
    public static function success($message, $additional = array())
    {
        return array_merge(array(
            'status' => 'success',
            'message' => $message
        ), $additional);
    }

    /**
     * Log time
     *
     * @param string $message
     */
    public static function logTime($message)
    {
        echo $message . " " . (isset(RestoLogUtil::$timestamp) ? round((microtime(true) - RestoLogUtil::$timestamp) * 1000, 3) : '') . "\n";
        RestoLogUtil::$timestamp = microtime(true);
    }
}
