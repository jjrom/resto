<?php
/*
 * Copyright 2022 Jérôme Gasperi
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

require(realpath(dirname(__FILE__)) . '/../../../vendor/lexer-1.2.3/lib/Doctrine/Common/Lexer/AbstractLexer.php');

class Lexer extends Doctrine\Common\Lexer\AbstractLexer
{
    // All tokens that are not valid identifiers must be < 100
    public const T_NONE              = 1;
    public const T_NUMBER            = 2;
    public const T_STRING            = 3;
    public const T_OPEN_PARENTHESIS  = 4;
    public const T_CLOSE_PARENTHESIS = 5;
    public const T_OPEN_BLOCK        = 6;
    public const T_CLOSE_BLOCK       = 7;
    public const T_COMMA             = 8;
    public const T_DATE              = 9;

    // Logical tokens should be >= 200 and < 300
    public const T_AND      = 200;
    public const T_OR       = 201;

    // Operator tokens should be >= 300 and < 400
    public const T_EQ       = 300;
    public const T_NE       = 301;
    public const T_LT       = 302;
    public const T_LTE      = 303;
    public const T_GT       = 304;
    public const T_GTE      = 305;
    public const T_IN       = 306;
    public const T_NI       = 307;
    public const T_NOT      = 308;

    // Functions that can be applied on value only should be >= 400
    public const T_TIMESTAMP = 400;

    // Functions that can be applied either on value or property should be >= 500
    public const T_S_INTERSECTS = 500;

    /**
     * {@inheritdoc}
     */
    protected function getCatchablePatterns()
    {
        return [
            '[a-z0-9_.:-]*',     // Identifiers
            '(?:[0-9]+)?',       // numbers
            '(?:"[^"]+")',       // Double quoted strings
            "(?:'[^']+')",       // Single quoted strings
            '>=', '<=',           // Operators
        ];
    }

    /**
     * Lexical non-catchable patterns.
     *
     * @return array
     */
    protected function getNonCatchablePatterns()
    {
        return array(
            '\s+'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getType(&$value)
    {
        $type = self::T_NONE;
        
        switch (true) {
            // Recognize numeric values
            case (is_numeric($value)):
                return self::T_NUMBER;

            // Recognize quoted strings
            case ($value[0] === '"'):
                $value = str_replace('""', '"', substr($value, 1, strlen($value) - 2));
                return $this->processQuotedString($value);

            // Recognize quoted strings
            case ($value[0] === '\''):
                $value = str_replace('\'', '', substr($value, 1, strlen($value) - 2));
                return $this->processQuotedString($value);
            
            // Recognize TIMESTAMP
            case (strpos($value, 'TIMESTAMP(') !== false):
                return self::T_TIMESTAMP;

            // Recognize identifiers, aliased or qualified names
            case (ctype_alpha($value[0])):
                $name = 'Lexer::T_' . strtoupper($value);

                if (defined($name)) {
                    $type = constant($name);

                    if ($type > 100) {
                        return $type;
                    }
                }

                return self::T_STRING;

                // Recognize symbols
            case ($value === '='):
                return self::T_EQ;

            case ($value === '<>'):
                return self::T_NE;

            case ($value === '>'):
                return self::T_GT;

            case ($value === '>='):
                return self::T_GTE;

            case ($value === '<'):
                return self::T_LT;

            case ($value === '<='):
                return self::T_LTE;

            case ($value === '('):
                return self::T_OPEN_PARENTHESIS;

            case ($value === ')'):
                return self::T_CLOSE_PARENTHESIS;

            case ($value === '['):
                return self::T_OPEN_BLOCK;

            case ($value === ']'):
                return self::T_CLOSE_BLOCK;

            case ($value === ','):
                return self::T_COMMA;

                // Default
            default:
                // Do nothing
        }

        return $type;
    }

    /**
     * Process a quoted string
     * 
     * @param string $value
     * @return int
     */
    private function processQuotedString($value)
    {
        if (is_numeric($value)) {
            // Quoted numbers are still numbers
            return self::T_NUMBER;
        }

        // See if we are a quoted date
        try {
            $ret = new \DateTime($value, new \DateTimeZone("UTC"));
            if ($ret instanceof \DateTime) {
                return self::T_DATE;
            }
        } catch (\Exception $e) {
            // It's ok when it's not a date.
        }

        return self::T_STRING;

    }
}
