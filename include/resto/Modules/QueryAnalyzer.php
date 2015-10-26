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
require 'QueryAnalyzer/QueryManager.php';
require 'QueryAnalyzer/WhatProcessor.php';
require 'QueryAnalyzer/WhenProcessor.php';
require 'QueryAnalyzer/WhereProcessor.php';
class QueryAnalyzer extends RestoModule {

    /*
     * Error messages
     */
    const INVALID_UNIT = 'INVALID_UNIT';
    const LOCATION_NOT_FOUND = 'LOCATION_NOT_FOUND';
    const MISSING_ARGUMENT = 'MISSING_ARGUMENT';
    const MISSING_UNIT = 'MISSING_UNIT';
    const NOT_UNDERSTOOD = 'NOT_UNDERSTOOD';
    
    /*
     * Query manager
     */
    public $queryManager = null;
    
    /*
     * Processors
     */
    public $whenProcessor = null;
    public $whereProcessor = null;
    public $whatProcessor = null;
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     * @param RestoModel $model
     */
    public function __construct($context, $user, $model = null) {
        parent::__construct($context, $user);
        
        /*
         * Patterns processor (i.e. When, What and Where)
         * Note : Where processor needs gazetteer
         */
        $this->queryManager = new QueryManager($this->context->dictionary, $model);
        $this->whenProcessor = new WhenProcessor($this->queryManager);
        $this->whatProcessor = new WhatProcessor($this->queryManager, $this->options);
        if (isset($context->modules['Gazetteer'])) {
            $this->whereProcessor = new WhereProcessor($this->queryManager, RestoUtil::instantiate($context->modules['Gazetteer']['className'], array($this->context, $this->user)));
        }
        
    }

    /**
     * Run module - this function should be called by Resto.php
     * 
     * @param array $segments : route segments
     * @param array $data : POST or PUT parameters
     * 
     * @return string : result from run process in the $context->outputFormat
     */
    public function run($segments, $data = array()) {
        
        /*
         * Only GET method on 'search' route with json outputformat is accepted
         */
        if ($this->context->method !== 'GET' || count($segments) !== 0) {
            RestoLogUtil::httpError(404);
        }
        $query = isset($this->context->query['searchTerms']) ? $this->context->query['searchTerms'] : (isset($this->context->query['q']) ? $this->context->query['q'] : null);
        
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
        
        return array(
            'query' => $query,
            'language' => $this->context->dictionary->language,
            'analyze' => $this->process($query),
            'processingTime' => microtime(true) - $startTime
        );
        
    }
    
    /**
     * Returns location from geohash/geouid
     * 
     * @param string $hashOrUid
     */
    public function whereFromGeohashOrGeouid($hashOrUid) {
        if (isset($this->context->modules['GazetteerPro'])) {
            $gazetteerPro = RestoUtil::instantiate($this->context->modules['GazetteerPro']['className'], array($this->context, $this->user));
            $location = $gazetteerPro->search(array(
                'q' => $hashOrUid,
                'wkt' => true,
                'preserve' => true,
                'tolerance' => 0.05,
                'snap' => true,
                'lang' => $this->context->dictionary->language === 'en' ? 'en' : $this->context->dictionary->language . ',en'
            ));
            return $location['results'];
        }
        return array();
    }
    
    /**
     * Return array of search terms from input query
     * 
     * @param string $query
     * @return array
     */
    private function process($query) {
        
        /*
         * Empty $query
         */
        if (empty($query)) {
            return array(
                'What' => array(),
                'When' => array(),
                'Where' => array(),
                'Errors' => array(),
                'Explained' => array()
            );
        }
        
        /*
         * Initialize QueryManager
         */
        $this->queryManager->initialize($this->queryToWords($query));
        
        /*
         * Extract type:value keywords
         */
        if ($this->queryManager->hasKeywords) {
            return $this->processKeywords();
        }
        
        /*
         * Extract (in this order !) "what", "when" and "where" elements from query
         * Suppose that query is structured (i.e. is a sentence) 
         */
        $this->processWhat(true);
        $this->processWhen(true);
        $this->processWhere(true);
        
        /*
         * Remaining words are unstructured (i.e. not a sentence)
         */
        $this->processWhat(false);
        $this->processWhen(false);
        $this->processWhere(false);
        
        /*
         * Return processing results
         */
        return array(
            'What' => $this->whatProcessor->getResult(),
            'When' => $this->whenProcessor->getResult(),
            'Where' => isset($this->whereProcessor) ? $this->whereProcessor->getResult() : array(),
            'Errors' => $this->getErrors(),
            'Explained' => $this->getExplanation()
        );
        
    }
    
    /**
     * Process "type:value" keywords
     */
    private function processKeywords() {
        $results = array();
        $where = array();
        for ($i = 0, $ii = $this->queryManager->length; $i < $ii; $i++) {
            $exploded = explode(':', $this->queryManager->words[$i]['word']);
            if (count($exploded) === 2 && !empty($exploded[0]) && !empty($exploded[1])) {
                $this->queryManager->words[$i]['processed'] = true;
                $this->queryManager->words[$i]['by'] = __METHOD__;
                $filterName = 'searchTerms';
                foreach ($this->queryManager->model->searchFilters as $key => $filter) {
                    if (strtolower($filter['osKey']) === strtolower($exploded[0])) {
                        $filterName = $key;
                        break;
                    }
                }
                if ($filterName === 'searchTerms') {
                    
                    /*
                     * Special case for geohash
                     */
                    if ($exploded[0] === 'geohash') {
                        $where = $this->whereFromGeohashOrGeouid($this->queryManager->words[$i]['word']);
                    }
                    else {
                        if (!isset($results[$filterName])) {
                            $results[$filterName] = array();
                        }
                        $results[$filterName][] = $this->queryManager->words[$i]['word'];
                    }
                }
                else {
                    $results[$filterName] = (isset($results[$filterName]) ? $results[$filterName] . ' ' : '') . $exploded[1];
                }
            }
        }
        
        return array(
            'What' => $results,
            'When' => array(),
            'Where' => $where,
            'Errors' => $this->getErrors(),
            'Explained' => $this->getExplanation()
        );
        
    }
    
    /**
     * Extract time patterns from query
     * 
     * @param boolean $fromSentence
     */
    private function processWhen($fromSentence) {
        $fromSentence ? $this->processSentence('when') : $this->processWords('when');
    }
    
    /**
     * Extract location patterns from query
     * Note: needs Gazetteer module up and running
     * 
     * @param boolean $fromSentence
     */
    private function processWhere($fromSentence) {
        if (isset($this->whereProcessor)) {
            $fromSentence ? $this->processSentence('where') : $this->processWords('where');
        }
    }
    
    /**
     * Extract what patterns from query
     * 
     * @param boolean $fromSentence
     */
    private function processWhat($fromSentence) {
        $fromSentence ? $this->processSentence('what') : $this->processWords('what');
    }
    
    /*
     * Extract What, When and Where patterns from unstructured words
     * 
     * @param string $type
     */
    private function processWords($type) {
        for ($i = 0; $i < $this->queryManager->length; $i++) {
            if ($this->queryManager->isValidPosition($i) && !$this->queryManager->isStopWordPosition($i) && !$this->queryManager->dictionary->isNoise($this->queryManager->words[$i]['word'])) {
                switch ($type) {
                    case 'what':
                        $this->whatProcessor->processWith($i, 0);
                        break;
                    case 'when':
                        $this->whenProcessor->processIn($i, 0);
                        break;
                    case 'where':
                        $this->whereProcessor->processIn($i, 0);
                        break;
                }
            }
        }
    }
    
    /*
     * Extract What, When and Where patterns from sentence
     * 
     * @param string $type
     */
    private function processSentence($type) {
        for ($i = 0; $i < $this->queryManager->length; $i++) {
            if ($this->queryManager->isValidPosition($i)) {
                switch ($type) {
                    case 'what':
                        $this->processModifier($this->context->dictionary->get(RestoDictionary::QUANTITY_MODIFIER, $this->queryManager->words[$i]['word']), $this->whatProcessor, $i);
                        break;
                    case 'when':
                        $this->processModifier($this->context->dictionary->get(RestoDictionary::TIME_MODIFIER, $this->queryManager->words[$i]['word']), $this->whenProcessor, $i);
                        break;
                    case 'where':
                        $this->processModifier($this->context->dictionary->get(RestoDictionary::LOCATION_MODIFIER, $this->queryManager->words[$i]['word']), $this->whereProcessor, $i);
                        break;
                }
            }
        }
    }
    
    /**
     * Process Modifier
     * 
     * @param string $modifier
     * @param string $processorClass
     * @param integer $position
     * @return array
     */
    private function processModifier($modifier, $processorClass, $position) {
        if (isset($modifier)) {
            $functionName = 'process' . ucfirst($modifier);
            if (method_exists($processorClass, $functionName)) {
                call_user_func_array(array($processorClass, $functionName), array($position));
            }
        }
    }
    
    /**
     * 
     * Explode query into normalized array of words
     * 
     * In order :
     *   - replace "," and ";" characters by space
     *   - transliterate query string afterward (i.e. all words in lowercase without accent)
     *   - split remaining query - split each terms with (" " character)
     *   - add a space between numeric value and '%' character
     * 
     * @param string $query
     * @return array
     */
    private function queryToWords($query) {
        $rawWords = RestoUtil::splitString($this->escapeMultiwords($this->context->dbDriver->normalize($this->removePrefixes(str_replace(array(',', ';'), ' ', $query)))));
        $words = array();
        for ($i = 0, $ii = count($rawWords); $i < $ii; $i++) {
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
     * 
     * Surround multiwords by " character
     * 
     * @param string $query
     * @return array
     */
    private function escapeMultiwords($query) {
        $query = ' ' . $query . ' ';
        for ($i = count($this->queryManager->dictionary->multiwords); $i--;) {
            $multiword = $this->queryManager->dictionary->multiwords[$i];
            $query = str_replace(' ' . $multiword . ' ', ' "' . $multiword . '" ', $query);
        }
        return trim($query);
    }
    
    /**
     * Remove prefix from query words
     * 
     * @param string $query
     * @return string
     */
    private function removePrefixes($query) {
        $splittedQuery = explode(' ', $query);
        $words = array();
        for ($i = 0, $ii = count($splittedQuery); $i < $ii; $i++) {
            $words[] = $this->queryManager->dictionary->stripPrefix($splittedQuery[$i]);
        }
        return join(' ', $words);
    }
    
    /**
     * Return comprehensive explanation of query analysis
     */
    private function getExplanation() {
        $currentBy = null;
        $explanation = array();
        for ($i = 0; $i < $this->queryManager->length; $i++) {
            $word = $this->queryManager->words[$i];
            if ($word['processed'] && !isset($word['error'])) {
                $by = $word['by'];
                if ($by !== $currentBy) {
                    $explanation[] = array(
                        'processor' => $by,
                        'word' => $word['word']
                    );
                    $currentBy = $by;
                }
                else {
                    $explanation[count($explanation) - 1]['word'] .= ' ' . $word['word'];
                }
            }
        }
        return $explanation;
    }
    
    /**
     * Return errors array from remaining words list
     */
    private function getErrors() {
        
        $inError = $this->getInErrorWords();
        $errors = array();
        $length = count($inError);
        if ($length === 0) {
            return $errors;
        }
        $error = null;
        $currentErrorType = null;
        for ($i = 0; $i <= $length; $i++) {
            
            if ($i === $length) {
                $errors[] = $error;
                break;
            }
            
            if (!isset($currentErrorType) || $currentErrorType === $inError[$i]['error']) {
                $message = (isset($error) ? $error['message'] . ' ' : '') . $inError[$i]['word'];
            }
            else {
                $errors[] = $error;
                $message = $inError[$i]['word'];
            }
            
            $currentErrorType = $inError[$i]['error'];
            $error = array(
                'error' => $currentErrorType,
                'message' => (isset($error) ? $error['message'] . ' ' : '') . $inError[$i]['word']
            );
        }
        return $errors;
    }
    
    /**
     * Get in error words removing noise and stopWords
     */
    private function getInErrorWords() {
        
        $inError = array();
        
        for ($i = 0; $i < $this->queryManager->length; $i++) {
            
            $word = $this->queryManager->words[$i];
            
            /*
             * Do not process noise or stopWords
             */
            if (!$this->queryManager->dictionary->isStopWord($word['word']) && !$this->queryManager->dictionary->isNoise($word['word'])) {
                if (isset($word['error']) || !$word['processed']) {
                    $inError[] = array(
                        'word' => $word['word'],
                        'error' => isset($word['error']) ? $word['error'] : QueryAnalyzer::NOT_UNDERSTOOD
                    );
                }
            }
        }
        return $inError;
    }
    
}