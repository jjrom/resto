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

/**
 * Parser for CQL2 string
 */
class FilterParser
{

    private $lexer;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lexer = new Lexer();

        try {
            $cql2 = "collection = \"landsat8_l1tp\" AND eo:cloud_cover <= 10 AND datetime >= TIMESTAMP('2021-04-08T04:39:23Z') AND S_INTERSECTS(geometry, POLYGON((43.5845 -79.5442, 43.6079 -79.4893, 43.5677 -79.4632, 43.6129 -79.3925, 43.6223 -79.3238, 43.6576 -79.3163, 43.7945 -79.1178, 43.8144 -79.1542, 43.8555 -79.1714, 43.7509 -79.6390, 43.5845 -79.5442)))";
            echo $cql2 . "\n";
            $this->parseCQL2($cql2);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Parse a CQL2 string
     * 
     * Assuming a valid CQL2 string structure is composed of a set of triplets "property operator value"
     * separated by logical operators (AND, OR, etc)
     *
     * @param string $cql2
     * @return array
     */
    public function parseCQL2($cql2)
    {

        $this->lexer->setInput($cql2);
        $this->lexer->moveNext();

        $params = array();

        while (null !== $this->lexer->lookahead) {

            /*
            echo '"' . $this->lexer->lookahead['value'] . '"';
            echo ' is of type ' . $this->lexer->lookahead['type'] . PHP_EOL;
            $this->lexer->moveNext();
            */
            
            // Token is a logical operator - keep it and move to next token
            if ( $this->isLogicalOperator($this->lexer->lookahead['type'])) {
                //echo 'Logical operator ' . $this->lexer->lookahead['value'] . ' at position ' . $this->lexer->lookahead['position'] . "\n";
                $logicalOperator = $this->lexer->lookahead['type'];
                $this->lexer->moveNext();
            }

            $filter = $this->processTriplet();

            print_r($filter);

            // Reset logical operator and move to next token
            $logicalOperator = Lexer::T_AND;
            $this->lexer->moveNext();

        }
    }

    /**
     * Process a triplet
     * 
     * @return array
     */
    private function processTriplet()
    {

        $filter = array();

        // First token is a property
        switch ($this->lexer->lookahead['type']) {

            case Lexer::T_S_INTERSECTS:
                return $this->intersectsExpression();

            case Lexer::T_STRING:
                $filter['property'] = $this->lexer->lookahead['value'];
                $this->lexer->moveNext();
                break;
    
            default:
                throw new Exception('Invalid property');
                break;

        }

        $filter['operator'] = $this->operatorExpression();

        switch ( $filter['operator'] ) {

            case Lexer::T_IN:
            case Lexer::T_NI:
                // In and NI must be arrays
                $filter['value'] = $this->arrayExpression();
                break;

            case Lexer::T_GT:
            case Lexer::T_GTE:
            case Lexer::T_LT:
            case Lexer::T_LTE:
                // These can only be numbers or dates, not strings
                $filter['value'] = $this->numberOrDateExpression();
                break;

            case Lexer::T_EQ:
            case Lexer::T_NE:
            default:
                $filter['value'] = $this->operandExpression();
                break;
        }

        return $filter;
  
    }

    /**
     * Check type
     * 
     * @return boolean
     */
    private function mustMatch($type)
    {
        $bool = $type === $this->lexer->lookahead['type'];
        $this->lexer->moveNext();
        return $bool;
    }

    /**
     * Get current lookahead token assuming it is an oper
     * 
     * @return integer
     */
    private function operatorExpression()
    {

        if ( $this->lexer->lookahead['type'] >= 300 && $this->lexer->lookahead['type'] < 400 ) {
            $operator = $this->lexer->lookahead['type'];
        }
        else {
            throw new Exception('Invalid operator');
        }

        $this->lexer->moveNext();

        return $operator;

    }

    /**
     * Get current lookahead token assuming it is an oper
     * 
     * @return string
     */
    private function arrayExpression()
    {

        $result = 'TODO array';

        $this->lexer->moveNext();

        return $result;

    }

    /**
     * Get current lookahead token assuming it is an oper
     * 
     * @return string
     */
    private function numberOrDateExpression()
    {

        switch ($this->lexer->lookahead['type']) {

            case Lexer::T_TIMESTAMP:
                $this->lexer->moveNext();
                $this->mustMatch(Lexer::T_OPEN_PARENTHESIS);
                if ( $this->lexer->lookahead['type'] !== Lexer::T_DATE ) {
                    throw new Exception('Invalid date');
                }
                $result = $this->lexer->lookahead['value'];
                $this->mustMatch(Lexer::T_CLOSE_PARENTHESIS);
                break;

            case Lexer::T_NUMBER:
                $result = $this->lexer->lookahead['value'];
                break;
            
            default:
                throw new Exception('Invalid number or date');
                break;
        }

        $this->lexer->moveNext();

        return $result;

    }

    /**
     * Process intersects expression i.e. S_INTERSECTS(geometry, POLYGON((xxxx)))";
     * 
     * @return string
     */
    private function intersectsExpression()
    {
    
        $filter = array();

        $this->lexer->moveNext();
        $this->mustMatch(Lexer::T_OPEN_PARENTHESIS);
        $filter['property'] = $this->lexer->lookahead['value'];
        $this->lexer->moveNext();
        $this->mustMatch(Lexer::T_COMMA);

        // Next is a WKT geometry
        $lastType = 0;
        $openParenthesis = 0;
        $closeParenthesis = 0;
        $wkt = '';
        while (null !== $this->lexer->lookahead) {

            if ($this->lexer->lookahead['type'] === Lexer::T_OPEN_PARENTHESIS) {
                $openParenthesis++;
            }
            else if ($this->lexer->lookahead['type'] === Lexer::T_CLOSE_PARENTHESIS) {
                $closeParenthesis++;
            }

            if ($closeParenthesis > $openParenthesis) {
                break;
            }

            $wkt .= $this->lexer->lookahead['type'] === Lexer::T_NUMBER && $lastType === Lexer::T_NUMBER ? ' ' . $this->lexer->lookahead['value'] : $this->lexer->lookahead['value'];
            $lastType = $this->lexer->lookahead['type'];

            $this->lexer->moveNext();

        }
        
        $filter['operator'] = 'intersects';
        $filter['value'] = $wkt;

        $this->lexer->moveNext();

        return $filter;

    }

    /**
     * Get current lookahead token assuming it is an oper
     * 
     * @return string
     */
    private function operandExpression()
    {

        $result = 'TODO operand';

        $this->lexer->moveNext();

        return $result;

    }


    /**
     * Return true if the type is an operator
     * 
     * @param int $type
     */
    private function isLogicalOperator($type)
    {
        return $type >= 200 && $type < 300;
    }

}
