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
 * QueryAnalyzer module
 * 
 * Extract OpenSearch EO search parameters from
 * an input string (i.e. searchTerms)
 * A typical searchTerms query can be anything :
 * 
 *      searchTerms = "spot5 images with forest in france between march 2012 and may 2012"
 * 
 * The query analyzer converts this string into comprehensive request.
 * 
 * For instance the previous string will be transformed as :
 *  
 *      eo:platform = SPOT5
 *      time:start = 2012-01-03T00:00:00Z
 *      time:end = 2012-31-05T00:00:00Z
 *      geo:box = POLYGON(( ...coordinates of France country...))
 *      searchTerms = landuse:forest
 * 
 * IMPORTANT : if a word is prefixed by 'xxx=' then QueryAnalyzer considered the string as a key=value pair
 * 
 * Some notes :
 *
 * # Dates
 * 
 * Detected dates format are :
 *      
 *      ISO8601 : see isISO8601($str) in lib/functions.php (e.g 2010-10-23)
 *      <month> <year> (e.g. may 2010)
 *      <year> <month> (e.g. 2010 may)
 *      <day> <month> <year> (e.g. 10 may 2010)
 *      <year> <month> <day> (e.g. 2010 may 10)
 * 
 * # Detected patterns
 * 
 * ## When ?
 * 
 *      <today>
 *      <tomorrow>
 *      <yesterday>
 * 
 *      <after> "date"
 *      <before> "date"
 *      
 *      <between> "date" <and> "date"
 *      <between> "month" <and> "month" (year)
 *      <between> "day" <and> "day" (month) (year)
 *      
 *      <in> "date"
 * 
 *      <last> "(year|month|day)"
 *      <last> "numeric" "(year|month|day)"
 *      "numeric" <last> "(year|month|day)"
 *      "(year|month|day)" <last>
 * 
 *      <next> "(year|month|day)"
 *      <next> "numeric" "(year|month|day)"
 *      "numeric" <next> "(year|month|day)"
 *      "(year|month|day)" <next>
 * 
 *      <since> "numeric" "(year|month|day)"
 *      <since> "month" "year"
 *      <since> "date"
 *      <since> "numeric" <last> "(year|month|day)"
 *      <since> <last> "numeric" "(year|month|day)"
 *      <since> <last> "(year|month|day)"
 *      <since> "(year|month|day)" <last>
 * 
 *      "numeric" "(year|month|day)" <ago>
 * 
 * 
 * A 'modifier' is a term which modify the way following term(s) are handled.
 * Known <modifier> and expected "terms" are :
 * 
 *      <with> "keyword"
 *      <with> "quantity"   // equivalent to "quantity" <greater> (than) 0 "unit"
 * 
 *      <without> "keyword"
 *  
 *      <without> "quantity"   // equivalent to "quantity" <equal> 0 "unit"
 * 
 *      "quantity" <lesser> (than) "numeric" "unit"
 *      "quantity" <greater> (than) "numeric" "unit"
 *      "quantity" <equal> (to) "numeric" "unit"
 *      <lesser> (than) "numeric" "unit" (of) "quantity" 
 *      <greater> (than) "numeric" "unit" (of) "quantity"
 *      <equal> (to) "numeric" "unit" (of) "quantity"
 * 
 *      
 *     
 *      <month>
 *      <season>
 * 
 * @param array $params
 */
require 'QueryAnalyzer/WhatProcessor.php';
require 'QueryAnalyzer/WhenProcessor.php';
require 'QueryAnalyzer/WhereProcessor.php';
class QueryAnalyzer extends RestoModule {

    /*
     * Error messages
     */
    const LOCATION_NOT_FOUND = 'LOCATION_NOT_FOUND';
    const NOT_UNDERSTOOD = 'NOT_UNDERSTOOD';
    const INVALID_UNIT = 'INVALID_UNIT';
    const MISSING_UNIT = 'MISSING_UNIT';
    
    /*
     * Processors
     */
    public $whenProcessor = null;
    public $whereProcessor = null;
    public $whatProcessor = null;
    
    /*
     * Reference to dictionary
     */
    public $dictionary;
    
    /*
     * Reference to model
     */
    public $model;
    
    /*
     * Analysis error
     */
    private $errors = array();
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     * @param RestoModel $model
     */
    public function __construct($context, $user, $model = null) {
        parent::__construct($context, $user);
        $this->dictionary = $this->context->dictionary;
        $this->model = isset($model) ? $model : new RestoModel_default();
        $this->whenProcessor = new WhenProcessor($this, $this->context, $this->user);
        $this->whereProcessor = new WhereProcessor($this, $this->context, $this->user);
        $this->whatProcessor = new WhatProcessor($this, $this->context, $this->user);
    }

    /**
     * Run module - this function should be called by Resto.php
     * 
     * @param array $elements : route element
     * @param array $data : POST or PUT parameters
     * 
     * @return string : result from run process in the $context->outputFormat
     */
    public function run($elements) {
        
        /*
         * Only GET method on 'search' route with json outputformat is accepted
         */
        if ($this->context->method !== 'GET' || count($elements) !== 0) {
            RestoLogUtil::httpError(404);
        }
        $query = isset($this->context->query['searchTerms']) ? $this->context->query['searchTerms'] : isset($this->context->query['q']) ? $this->context->query['q'] : null;
        
        return $this->analyze($query);
        
    }
    
    /**
     * Query analyzer process searchTerms and modify query parameters accordingly
     * 
     * @param string $query
     * @return type
     */
    public function analyze($query) {

        $startTime = microtime(true);
        
        /*
         * QueryAnalyzer only apply on searchTerms filter
         */
        if (!isset($query)) {
            RestoLogUtil::httpError(400, 'Missing mandatory searchTerms');
        }
       
        return array(
            'query' => $query,
            'language' => $this->dictionary->language,
            'analyze' => $this->process($query),
            'processingTime' => microtime(true) - $startTime
        );
        
    }
    
    /**
     * Get the last sentence position i.e. the last word position before
     * a modifier or the last word position if no modifier is found
     * @param array $words
     * @param integer $position
     */
    public function getEndPosition($words, $position) {
        $endPosition = $position;
        for ($i = $position, $ii = count($words); $i < $ii; $i++) {
            if ($this->dictionary->isModifier($words[$i])) {
                $endPosition = $i - 1;
                break;
            }
            $endPosition = $i;
        }
        return $endPosition;
    }
    
    /**
     * Add text to error array
     * 
     * @param array $error
     * @param array $words
     */
    public function error($error, $words) {
        $this->errors[] = array(
            'error' => $error,
            'text' => $this->mergeWords($this->slice($words, 0, count($words)))
        );
    }
    
    /**
     * Add words to not understood array
     * 
     * @param array $words
     */
    public function notUnderstood($words) {
        $this->error(QueryAnalyzer::NOT_UNDERSTOOD, $words);
    }
    
    /**
     * Return slice of $words array. Output array is reversed if $reverse is set to true
     * 
     * @param array $words
     * @param integer $startPosition
     * @param integer $endPosition
     * @param boolean $reverse
     * @return type
     */
    public function slice($words, $startPosition, $endPosition, $reverse = false) {
        $slicedWords = array_slice($words, $startPosition, $endPosition);
        return $reverse ? array_reverse($slicedWords) : $slicedWords;
    }
    
    /**
     * Concatenate words into sentence removing noise and stop words
     * 
     * @param array $words
     * @return array
     */
    private function mergeWords($words, $discardStopWords = false) {
        $sentence = '';
        for ($i = 0, $ii = count($words); $i < $ii; $i++) {
            if ($discardStopWords && ($this->dictionary->isStopWord($words[$i]) || $this->dictionary->isNoise($words[$i]))) {
                continue;
            }
            $sentence .= $words[$i] . ' ';
        }
        return trim($sentence);
    }
    
    /**
     * Return array of search terms from input query
     * 
     * @param string $query
     * @return array
     */
    private function process($query) {
        
        /*
         * Extract (in this order !) "what", "when" and "where" elements from query
         */
        $words = $this->processWhere($this->processWhen($this->processWhat($this->toWords($query))));
        
        /*
         * Remaining stuff
         */
        if (count($words) > 0) {
            $this->processRemainingWords($words);
        }
        
        /*
         * Return processing results
         */
        return array(
            'What' => $this->whatProcessor->result,
            'When' => $this->whenProcessor->result,
            'Where' => $this->whereProcessor->result,
            'Errors' => $this->errors
        );
        
    }
    
    /**
     * Extract time patterns from words
     * 
     * @param array $words
     */
    private function processWhen($words) {
        
        /*
         * Roll over each word to detect time pattern
         */
        for ($i = 0, $l = count($words); $i < $l; $i++) {
            $result = $this->processModifier($this->dictionary->get(RestoDictionary::TIME_MODIFIER, $words[$i]), $this->whenProcessor, $words, $i); 
            if (isset($result)) {
                return $this->processWhen($result);
            }
        }
        
        return $words;
    }
    
    /**
     * Extract time patterns from words
     * 
     * @param array $words
     */
    private function processWhere($words) {
        
        /*
         * Roll over each word to detect location pattern
         */
        for ($i = 0, $l = count($words); $i < $l; $i++) {
            $result = $this->processModifier($this->dictionary->get(RestoDictionary::LOCATION_MODIFIER, $words[$i]), $this->whereProcessor, $words, $i); 
            if (isset($result)) {
                return $this->processWhere($result);
            }
        }
        
        return $words;
    }
    
    /**
     * Extract what patterns from words
     * 
     * @param array $words
     */
    private function processWhat($words) {
        
        /*
         * Roll over each word to detect what pattern
         */
        for ($i = 0, $l = count($words); $i < $l; $i++) {
            $result = $this->processModifier($this->dictionary->get(RestoDictionary::QUANTITY_MODIFIER, $words[$i]), $this->whatProcessor, $words, $i); 
            if (isset($result)) {
                return $this->processWhat($result);
            }
        }
        
        return $words;
    }
    
    /**
     * Process non already processed words
     * 
     * @param array $words
     */
    private function processRemainingWords($words) {
        
        /*
         * Extract keywords
         */
        $remainings = $this->processRemainingWhenWhere($this->processRemainingWhat($words));
        
        /*
         * Remaining words
         */
        $this->notUnderstood($remainings, true);
        
    }
    
    /**
     * Process remaining What words
     * 
     * @param array $words
     * @return array
     */
    private function processRemainingWhat($words) {
        for ($i = count($words); $i--;) {
            $remainings = $this->whatProcessor->processWith($words, $i, array(
                'delta' => 0,
                'nullIfNotFound' => true
            ));
            if (isset($remainings)) {
                $words = $remainings;
            }
        }
        return $words;
    }
    
    /**
     * Process remaining When and Where words
     * 
     * @param array $words
     * @return array
     */
    private function processRemainingWhenWhere($words) {
        foreach (array_values(array('when', 'what')) as $processorType) { 
            for ($i = 0, $ii = count($words); $i < $ii; $i++) {
                $remainings = $processorType === 'when' ?
                        $this->whenProcessor->processIn($words, $i, array(
                            'delta' => 0,
                            'nullIfNotFound' => true
                        )) : 
                        $this->whereProcessor->processIn($words, $i, array(
                            'delta' => 0,
                            'nullIfNotFound' => true
                ));
                if (isset($remainings)) {
                    $words = $remainings;
                    break;
                }
            }
        }
        return $words;
    }
    
    /**
     * Return array of words from input string
     * In order :
     *   - replace in query ' , and ; characters by space
     *   - transliterate query string afterward (i.e. all words in lowercase without accent)
     *   - split remaining query - split each terms with (" " character)
     *   - add a space between numeric value and '%' character
     * 
     * @param string $query
     * @return array
     */
    private function toWords($query) {
        return $this->cleanRawWords(RestoUtil::splitString($this->context->dbDriver->normalize(str_replace(array('\'', ',', ';'), ' ', $query))));
    }

    /**
     * Clean raw words array i.e.
     *  - Add a space between a numeric value and '%' character
     * 
     * @param array $rawWords
     * @return array
     */
    private function cleanRawWords($rawWords) {
        $words = array();
        for ($i = 0, $l = count($rawWords); $i < $l; $i++) {
            $term = trim($rawWords[$i]);
            if ($term === '') {
                continue;
            }
            $splitted = explode('%', $term);
            if (count($splitted) === 2 && is_numeric($splitted[0])) {
                $words[] = $splitted[0];
                $words[] = '%';
            }
            else {
                $words[] = $rawWords[$i];
            }
        }
        return $words;
    }

    /**
     * Process Modifier
     * 
     * @param string $modifier
     * @param string $processorClass
     * @param array $words
     * @param integer $position
     * @return array
     */
    private function processModifier($modifier, $processorClass, $words, $position) {
        if (isset($modifier)) {
            $functionName = 'process' . ucfirst($modifier);
            if (method_exists($processorClass, $functionName)) {
                return call_user_func_array(array($processorClass, $functionName), array($words, $position));
            }
        }
        return null;
    }
    
}