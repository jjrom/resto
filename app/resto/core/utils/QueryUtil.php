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
 * resto output format
 */
class QueryUtil
{
    /**
     * Prepare SQL query for intervals
     *
     *
     *      $str = n1 then returns value = n1
     *      $str = {n1,n2} then returns  value = n1 or value = n2
     *      $str = [n1,n2] then returns  n1 ≤ value ≤ n2
     *      $str = [n1,n2[ then returns  n1 ≤ value < n2
     *      $str = ]n1,n2[ then returns  n1 < value < n2
     *      $str = ]n1 then returns n1 < value
     *      $str = [n1 then returns  n1 ≤ value
     *      $str = n1[ then returns value < n2
     *      $str = n1] then returns value ≤ n2
     *
     * @param string $str
     * @param string $columnName
     * @return string
     */
    public static function intervalToQuery($str, $columnName)
    {
        $values = explode(',', $str);

        /*
         * No ',' present i.e. simple equality or non closed interval
         */
        if (count($values) === 1) {
            return QueryUtil::processSimpleInterval($columnName, trim($values[0]));
        }
        /*
         * Assume two values
         */
        return QueryUtil::processComplexInterval($columnName, $values);
    }

    /**
     * Process simple interval
     *
     * @param string $columnName
     * @param string $value
     * @return string
     */
    private static function processSimpleInterval($columnName, $value)
    {
        $quote = true;

        /*
         * A = ]n1 then returns n1 < value
         * A = n1[ then returns value < n2
         */
        $op1 = substr($value, 0, 1);
        if ($op1 === '[' || $op1 === ']') {
            return $columnName . ($op1 === '[' ? ' >= ' : ' > ') . QueryUtil::quoteMe(substr($value, 1), $quote);
        }

        /*
         * A = [n1 then returns  n1 ≤ value
         * A = n1] then returns value ≤ n2
         */
        $op2 = substr($value, -1);
        if ($op2 === '[' || $op2 === ']') {
            return $columnName . ($op2 === ']' ? ' <= ' : ' < ') . QueryUtil::quoteMe(substr($value, 0, strlen($value) - 1), $quote);
        }

        /*
         * A = n1 then returns value = n1
         */
        return $columnName . '=' . QueryUtil::quoteMe($value, $quote);
    }

    /**
     * Process complex interval
     *
     * @param string $columnName
     * @param array $values
     * @return string
     */
    private static function processComplexInterval($columnName, $values)
    {
        $quote = true;

        /*
         * First and last characters give operators
         */
        $op1 = substr(trim($values[0]), 0, 1);
        $op2 = substr(trim($values[1]), -1);

        /*
         * A = {n1,n2} then returns  = n1 or = n2
         */
        if ($op1 === '{' && $op2 === '}') {
            return '(' . $columnName . '=' . QueryUtil::quoteMe(substr($values[0], 1), $quote) . ' OR ' . $columnName . '=' . QueryUtil::quoteMe(substr($values[1], 0, strlen($values[1]) - 1), $quote) . ')';
        }

        /*
         * Other cases i.e.
         * A = [n1,n2] then returns <= n1 and <= n2
         * A = [n1,n2[ then returns <= n1 and B < n2
         * A = ]n1,n2[ then returns < n1 and B < n2
         *
         */
        if (($op1 === '[' || $op1 === ']') && ($op2 === '[' || $op2 === ']')) {
            return $columnName . ($op1 === '[' ? '>=' : '>') . QueryUtil::quoteMe(substr($values[0], 1), $quote) . ' AND ' . $columnName . ($op2 === ']' ? '<=' : '<') . QueryUtil::quoteMe(substr($values[1], 0, strlen($values[1]) - 1), $quote);
        }
    }

    /**
     * Return a escaped quoted string
     *
     * @param string $str
     * @param boolean $quote
     */
    private static function quoteMe($str, $quote)
    {
        return $quote ? '\'' . pg_escape_string($str) . '\'' : pg_escape_string($str);
    }
}
