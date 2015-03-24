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
class QueryAnalyzer extends RestoModule {

    private $dictionary;
    private $unProcessed = array();
    private $remaining = array();
    private $explicits = array();
    
    private $outputFilters = array();
    
    private $keywords = array();
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
        $this->dictionary = $this->context->dictionary;
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
        
        return $this->analyze(isset($this->context->query['searchTerms']) ? $this->context->query['searchTerms'] : null, new RestoModel_default());
        
    }
    
    /**
     * Query analyzer process searchTerms and modify query parameters accordingly
     * 
     * @param string $query
     * @param RestoModel $model
     * @return type
     */
    public function analyze($query, $model) {

        $this->model = $model;
        
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
            'unProcessed' => $this->unProcessed,
            'remaining' => implode(' ', $this->remaining),
            'queryAnalyzeProcessingTime' => microtime(true) - $startTime
        );
        
    }
    
    /**
     * Return array of search terms from input query
     * 
     * @param string $query
     * @return array
     */
    private function process($query) {
        
        /*
         * Get searchTerms array
         */
        $words = $this->toWords($query);
        print_r($words);
        
        /*
         * When ?
         */
        $words = $this->processWhen($words);
        print_r($words);
        
        /*
         * Where ?
         */
        $words = $this->processWhere($words);
        print_r($words);
        
        
        /*
         * Remove excluded words and words with less than 4 characters that are not in dictionary
         */
        //$searchTerms = $this->extractExcluded($searchTerms);
        
        /*
         * Extract Platform and Instrument
         */
        //$searchTerms = $this->extractPlaformAndInstrument($searchTerms, $params);
        
        /*
         * At this stage remaining terms are 
         *  - numeric values
         *  - modifiers
         *  - non excluded terms with 4 or more characters in length that
         */
        //$this->extractModifiers($searchTerms);
        
        /*
         * Extract dates alone
         */
        //$this->extractDates($searchTerms, $inputParams);
        
        /*
         * Extract keywords
         */
        //$this->extractKeywordsAndLocation($searchTerms, $params);
        
        /*
         * Merge computed searchTerms with explicits keywords
         */
        /*
        if (count($this->explicits) > 0) {
            $params['searchTerms'] = trim($params['searchTerms'] . ' ' . join(' ', array_keys($this->explicits)));
        }
        */
        
        return $this->outputFilters;
        
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
            
            $modifier = $this->dictionary->get(RestoDictionary::TIME_MODIFIER, $words[$i]);
           
            if (!isset($modifier)) {
                continue;
            }
            
            /*
             * <before> "date"
             */
            if ($modifier === 'before') {
                return $this->processWhen($this->processWhenBefore($words, $i));
            }
            
            /*
             * <after> "date"
             */
            if ($modifier === 'after') {
                return $this->processWhen($this->processWhenAfter($words, $i));
            }
            
            /*
             * <between> "date" <and> "date"
             */
            if ($modifier === 'between') {
                return $this->processWhen($this->processWhenBetweenAnd($words, $i));
            }
            
            /*
             * <since> "date"
             */
            if ($modifier === 'since') {
                return $this->processWhen($this->processWhenSince($words, $i));
            }
            
            /*
             * <last> 
             */
            if ($modifier === 'last') {
                return $this->processWhen($this->processWhenLast($words, $i));
            }
            
            /*
             * <next> 
             */
            if ($modifier === 'next') {
                return $this->processWhen($this->processWhenNext($words, $i));
            }
            
            /*
             * <in> "date"
             */
            if ($modifier === 'in') {
                return $this->processWhen($this->processWhenIn($words, $i));
            }
            
            /*
             * "quantity" "unit" <ago>
             */
            if ($modifier === 'ago') {
                return $this->processWhen($this->processWhenAgo($words, $i));
            }
            
            /*
             * Today
             */
            if ($modifier === 'today') {
                return $this->processWhen($this->processWhenToday($words, $i));
            }
            
            /*
             * Tomorrow
             */
            if ($modifier === 'tomorrow') {
                return $this->processWhen($this->processWhenTomorrow($words, $i));
            }
            
            /*
             * Yesterday
             */
            if ($modifier === 'yesterday') {
                return $this->processWhen($this->processWhenYesterday($words, $i));
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
            
            $modifier = $this->dictionary->get(RestoDictionary::LOCATION_MODIFIER, $words[$i]);
           
            if (!isset($modifier)) {
                continue;
            }
            
            /*
             * <in> "location"
             */
            if ($modifier === 'in') {
                return $this->processWhere($this->processWhereIn($words, $i));
            }
            
            /*
             * <between> "location" <and> "location"
             */
            if ($modifier === 'between') {
                return $this->processWhere($this->processWhereBetweenAnd($words, $i));
            }
            
        }
        
        return $words;
    }
    
    /**
     * 
     * Process <in> "location"
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function processWhereIn($words, $position) {
        
        /*
         * Extract location
         */
        $location = $this->extractLocation($words, $position + 1);
        
        /*
         * No location found
         */
        if ($location['endPosition'] === -1) {
            array_splice($words, $position, 1);
        }
        else {
            $this->outputFilters['location'] = $location['locations'];
            array_splice($words, $position, $location['endPosition'] - $position + 1);
        }
        
        return $words;
       
    }
    
    /**
     * 
     * Extract location from sentence
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function extractLocation($words, $position) {
        
        $endPosition = -1;
        
        /*
         * Get the last index position
         */
        for ($i = $position, $ii = count($words); $i < $ii; $i++) {
            if ($this->dictionary->isModifier($words[$i])) {
                $endPosition = $i - 1;
                break;
            }
            $endPosition = $i;
        }
        
        /*
         * Roll over each word
         */
        $locationModifier = null;
        for ($i = $endPosition; $i >= $position; $i--) {
          
            /*
             * Do not process if location modifier was already found
             */
            if (isset($locationModifier)) {
                continue;
            }
            
            /*
             * Parse words in reverse order to find toponym modifier
             * If input words are array('saint', 'gaudens', 'france')
             * Then keyword will be tested against : 
             *  saint, saint-gaudens, saint-gaudens-france, gaudens, gaudens-france, france
             */
            $locationName = '';
            for ($j = $i; $j >= $position; $j--) {
                
                /*
                 * Reconstruct sentence from words without stop words
                 */
                if (!$this->dictionary->isStopWord($words[$j])) {
                    $locationName = $words[$j] . ($locationName === '' ? '' : '-') . $locationName;
                }
                
                $keyword = $this->dictionary->getKeyword(RestoDictionary::LOCATION, $locationName);
                if (isset($keyword)) {
                    $locationModifier = array(
                        'startPosition' => min(array($i, $j)),
                        'endPosition' => max(array($i, $j)),
                        'keyword' => $keyword['keyword'],
                        'type' => $keyword['type']
                    );
                    break;
                }
                
            }
            
        }
        
        /*
         * Search toponym in gazetteer
         */
        if (isset($this->context->modules['Gazetteer'])) {
            return $this->extractToponym($words, $position, $locationModifier);
        }
        /*
         * Return location modifier
         */
        else {
            return array(
                'endPosition' => $endPosition,
                'location' => $locationModifier
            );
        }
        
    }
    
    /**
     * 
     * Extract toponym
     * 
     * @param array $words
     * @param integer $position of word in the list
     * @param array $locationModifier
     */
    private function extractToponym($words, $position, $locationModifier = null) {
        
        $endPosition = -1;
        
        /*
         * Roll over each word
         */
        $toponymName = '';
        for ($i = $position, $ii = count($words); $i < $ii; $i++) {
          
            /*
             * Exit if stop modifier is found
             */
            if ($this->dictionary->isModifier($words[$i])) {
                $endPosition = $i - 1;
                break;
            }
            
            /*
             * Discard locationModifier
             */
            if (isset($locationModifier)) {
                if ($i >= $locationModifier['startPosition'] && $i <= $locationModifier['endPosition']) {
                    continue;
                }
            }
            
            /*
             * Reconstruct sentence from words without stop words
             */
            if (!$this->dictionary->isStopWord($words[$i])) {
                $toponymName .= ($toponymName === '' ? '' : '-') . $words[$i];
            }
            
            $endPosition = $i;
        }
        
        /*
         * No toponym
         */
        if (empty($toponymName)) {
            return array(
                'endPosition' => $endPosition,
                'locations' => $locationModifier
            );
        }
        
        /*
         * Search in gazetteer
         */
        $gazetteer = new Gazetteer($this->context, $this->user, $this->context->modules['Gazetteer']);
        $locations = $gazetteer->search(array(
            'q' => $toponymName . (isset($locationModifier) ? ',' . $locationModifier['keyword'] : '')
        ));
        
        return array(
            'endPosition' => $endPosition,
            'locations' => $locations
        );
        
    }
    
    /**
     * Process <without> "quantity" 
     * 
     * @param array $searchTerms
     * @param integer $i
     * @param integer $l
     * @return string
     */
    private function processWithout($searchTerms, $i, $l) {
        
        /*
         * <without> "quantity" means quantity = 0
         */ 
        if ($i + 1 < $l) {
            $quantity = $this->dictionary->getQuantity($searchTerms[$i + 1]);
            if (isset($quantity)) {
                $searchFilter = $this->getSearchFilter($quantity);
                if (isset($searchFilter)) {
                    $this->outputFilters[$searchFilter['key']] = 0;
                }
            }
            else {
                $this->unProcessed[] = $searchTerms[$i] . ' ' .  $searchTerms[$i + 1];
            }
            array_splice($searchTerms, $i, 2);
        }
        else {
            $this->unProcessed[] = $searchTerms[$i];
            array_splice($searchTerms, $i, 1);
        }
        return $searchTerms;
    }
    
    /**
     * Process <after> "date" 
     * 
     * @param array $words
     * @param integer $position
     * @return string
     */
    private function processWhenAfter($words, $position) {
        return $this->processWhenBeforeOrAfter($words, $position, 'time:start');
    }

    /**
     * Process <before> "date" 
     * 
     * @param array $words
     * @param integer $position
     * @return string
     */
    private function processWhenBefore($words, $position) {
        return $this->processWhenBeforeOrAfter($words, $position, 'time:end');
    }
    
    /**
     * Process <before> or <after> "date" 
     * 
     * @param array $words
     * @param integer $position
     * @return string
     */
    private function processWhenBeforeOrAfter($words, $position, $osKey) {
        
        /*
         * Extract date
         */
        $date = $this->extractDate($words, $position + 1);
        
        /*
         * No date found - remove modifier only from words list
         */
        if ($date['endPosition'] === -1) {
            array_splice($words, $position, 1);
        }
        /*
         * Date found - add to outputFilters and remove modifier and date from words list
         */
        else {
            $this->outputFilters[$osKey] = $osKey === 'time:start' ? $this->toGreatestDay($date['date']) : $this->toLowestDay($date['date']);
            array_splice($words, $position, $date['endPosition'] - $position + 1);
        }
        return $words;
    }

    /**
     * 
     * Process <between> "date" <and> "date"
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function processWhenBetweenAnd($words, $position) {
        
        /*
         * Extract first date
         */
        $firstDate = $this->extractDate($words, $position + 1, true);
        
        /*
         * No date found - try <between> "location" <and> "location" 
         */
        if (empty($firstDate['date'])) {
            return $this->processWhereBetween($words, $position);
        }
        
        /*
         * Date found - search for second date
         */  
        $secondDate = $this->extractDate($words, $firstDate['endPosition'] + 1);

        /*
         * No date found - try <between> "location" <and> "location" 
         */
        if (empty($secondDate['date'])) {
            return $this->processWhereBetween($words, $position);
        }

        /*
         * Date found - compute time:start and time:end
         */
        if (!isset($firstDate['date']['year']) &&isset($secondDate['date']['year'])) {
            $firstDate['date']['year'] = $secondDate['date']['year'];
        }
        if (!isset($firstDate['date']['month']) && isset($secondDate['date']['month'])) {
            $firstDate['date']['month'] = $secondDate['date']['month'];
        }
        $this->outputFilters['time:start'] = $this->toLowestDay($firstDate['date']);
        $this->outputFilters['time:end'] = $this->toGreatestDay($secondDate['date']);
        array_splice($words, $position, $secondDate['endPosition'] - $position + 1);
       
        return $words;
        
    }
    
    /**
     * Process <since> "date"
     * 
     * Understood structures are :
     *  
     *      <since> "numeric" "(year|month|day)"
     *      <since> "month" "year"
     *      <since> "date"
     *      <since> "numeric" <last> "(year|month|day)"
     *      <since> <last> "numeric" "(year|month|day)"
     *      <since> <last> "(year|month|day)"
     *      <since> "(year|month|day)" <last>
     *      
     * 
     * Example :
     *           
     *      If current date is 12 November 2013 (i.e. 2013-11) then
     *      the "<since> 2 months" are from 12 septembre 2013 and 12 november 2013
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function processWhenSince($words, $position) {
        
        /*
         * <since> "date"
         */
        $date = $this->extractDate($words, $position + 1);
        
        /*
         * If a month is specified and the month is posterior to
         * the current month then decrease by one year
         */
        if (!isset($date['date']['year']) && isset($date['date']['month']) && ((integer) $date['date']['month'] > (integer) date('m'))) {
            $date['date']['year'] = (integer) date('Y') - 1;
        }
        
        /*
         * <since> "numeric" (year|month|day)
         * <since> <last> "numeric" "(year|month|day)"
         * <since> <last> "numeric" "(year|month|day)"
         * <since> <last> "(year|month|day)"
         * <since> "(year|month|day)" <last>
         */
        if (empty($date['date'])) {
            $duration = $this->extractDuration($words, $position + 1);
            if (!empty($duration['duration'])) {
                $date = array(
                    'endPosition' => $duration['endPosition'],
                    'date' => $this->iso8601ToDate(date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration['duration']['value'] . $duration['duration']['unit'])))
                );
            }
        }
        
        if (!empty($date['date'])) {
            $this->outputFilters['time:start'] = $this->toLowestDay($date['date']);
            array_splice($words, $position, $date['endPosition'] - $position + 1);
        }
        else {
            array_splice($words, $position, 1);
        }
        
        return $words;
        
    }
    
    /**
     * Process <last> "date"
     * 
     * Understood structures are :
     *  
     *      <last> "(year|month|day)"
     *      <last> "numeric" "(year|month|day)"
     *      "numeric" <last> "(year|month|day)"
     *      "(year|month|day)" <last>
     * 
     * Example :
     *           
     *      If current date is November 2013 (i.e. 2013-11) then
     *      the "<last> 2 months" are September and October 2013
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function processWhenLast($words, $position) {
        
        /*
         * Important ! Start position is one before <last>
         * to process the following
         * 
         *      "numeric" <last> "(year|month|day)"
         *      "(year|month|day)" <last>
         * 
         */
        $duration = $this->extractDuration($words, max(array(0, $position - 1)));
        if (!empty($duration['duration'])) {
            $year = date('Y', strtotime(date('Y-m-d') . ' - 1 ' . $duration['duration']['unit']));
            $pYear = date('Y', strtotime(date('Y-m-d') . ' - ' . $duration['duration']['value'] . $duration['duration']['unit']));
            $month = date('m', strtotime(date('Y-m-d') . ' - 1 ' . $duration['duration']['unit']));
            $pMonth = date('m', strtotime(date('Y-m-d') . ' - ' . $duration['duration']['value'] . $duration['duration']['unit']));
            $day = date('d', strtotime(date('Y-m-d') . ' - 1 ' . $duration['duration']['unit']));
            $pDay = date('d', strtotime(date('Y-m-d') . ' - ' . $duration['duration']['value'] . $duration['duration']['unit']));
            
            switch ($duration['duration']['unit']) {
                case 'years':
                    $this->outputFilters['time:start'] = $pYear . '-01-01' . 'T00:00:00Z';
                    $this->outputFilters['time:end'] = $year . '-12-31' . 'T23:59:59Z';
                    break;
                case 'months':
                    $this->outputFilters['time:start'] = $pYear . '-' . $pMonth . '-01' . 'T00:00:00Z';
                    $this->outputFilters['time:end'] = $year . '-' . $month . '-' . date('d', mktime(0, 0, 0, intval($month) + 1, 0, intval($year))) . 'T23:59:59Z';
                    break;
                case 'days':
                    $this->outputFilters['time:start'] = $pYear . '-' . $pMonth . '-' . $pDay . 'T00:00:00Z';
                    $this->outputFilters['time:end'] = $year . '-' . $month . '-' . $day . 'T23:59:59Z';
                    break;
                default:
                    break;
            }
            $delta = $duration['firstIsNotLast'] ? 1 : 0;
            array_splice($words, $position - $delta, $duration['endPosition'] - $position + 1 + $delta);
        }
        else {
            array_splice($words, $position, 1);
        }
        
        return $words;
        
    }
    
    /**
     * Process <next> "date"
     * 
     * Understood structures are :
     *  
     *      <next> "(year|month|day)"
     *      <next> "numeric" "(year|month|day)"
     *      "numeric" <next> "(year|month|day)"
     *      "(year|month|day)" <next>
     * 
     * Example :
     *           
     *      If current date is November 2013 (i.e. 2013-11) then
     *      the "<next> 2 months" are December 2013 and January 2014
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function processWhenNext($words, $position) {
        
        /*
         * Important ! Start position is one before <next>
         * to process the following
         * 
         *      "numeric" <next> "(year|month|day)"
         *      "(year|month|day)" <next>
         * 
         */
        $duration = $this->extractDuration($words, max(array(0, $position - 1)));
        
        if (!empty($duration['duration'])) {
            $pYear = date('Y', strtotime(date('Y-m-d') . ' + 1 ' . $duration['duration']['unit']));
            $year = date('Y', strtotime(date('Y-m-d') . ' + ' . $duration['duration']['value'] . $duration['duration']['unit']));
            $pMonth = date('m', strtotime(date('Y-m-d') . ' + 1 ' . $duration['duration']['unit']));
            $month = date('m', strtotime(date('Y-m-d') . ' + ' . $duration['duration']['value'] . $duration['duration']['unit']));
            $pDay = date('d', strtotime(date('Y-m-d') . ' + 1 ' . $duration['duration']['unit']));
            $day = date('d', strtotime(date('Y-m-d') . ' + ' . $duration['duration']['value'] . $duration['duration']['unit']));
            
            switch ($duration['duration']['unit']) {
                case 'years':
                    $this->outputFilters['time:start'] = $pYear . '-01-01' . 'T00:00:00Z';
                    $this->outputFilters['time:end'] = $year . '-12-31' . 'T23:59:59Z';
                    break;
                case 'months':
                    $this->outputFilters['time:start'] = $pYear . '-' . $pMonth . '-01' . 'T00:00:00Z';
                    $this->outputFilters['time:end'] = $year . '-' . $month . '-' . date('d', mktime(0, 0, 0, intval($month) + 1, 0, intval($year))) . 'T23:59:59Z';
                    break;
                case 'days':
                    $this->outputFilters['time:start'] = $pYear . '-' . $pMonth . '-' . $pDay . 'T00:00:00Z';
                    $this->outputFilters['time:end'] = $year . '-' . $month . '-' . $day . 'T23:59:59Z';
                    break;
                default:
                    break;
            }
            $delta = $duration['firstIsNotLast'] ? 1 : 0;
            array_splice($words, $position - $delta, $duration['endPosition'] - $position + 1 + $delta);
        }
        else {
            array_splice($words, $position, 1);
        }
        
        return $words;
        
    }
    
    /**
     * Process <in> "date"
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function processWhenIn($words, $position) {
        
        $date = $this->extractDate($words, $position + 1);
        
        /*
         * No date found - try <in> "location"
         */
        if (empty($date['date'])) {
            return $this->processWhereIn($words, $position);
        }
        
        $this->outputFilters['time:start'] = $this->toLowestDay($date['date']);
        $this->outputFilters['time:end'] = $this->toGreatestDay($date['date']);
        array_splice($words, $position, $date['endPosition'] - $position + 1);
        
        return $words;
    }
    
    /**
     * Process "numeric" "units" <ago>
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function processWhenAgo($words, $position) {
        
        if ($position - 2 >= 0 && $this->isNumeric($words[$position - 2])) {
            
            $unit = $this->dictionary->get(RestoDictionary::TIME_UNIT, $words[$position - 1]);
            $duration = $this->toNumeric($words[$position - 2]);
            
            /*
             * Known duration unit
             */
            if ($unit === 'days' || $unit === 'months' || $unit === 'years') {
                $this->outputFilters['time:start'] = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration . $unit)) . 'T00:00:00Z';
                $this->outputFilters['time:end'] = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration . $unit)) . 'T23:59:59Z';
                array_splice($words, $position - 2, 3);
                return $words;
            }
        }
        
        /*
         * Invalid processing
         */
        array_splice($words, $position, 1);
        return $words;
    }
    
   /**
    * Process <today>
    * 
    * @param array $words
    * @param integer $position of word in the list
    */
    private function processWhenToday($words, $position) {
        $this->outputFilters['time:start'] = date('Y-m-d\T00:00:00\Z');
        $this->outputFilters['time:end'] = date('Y-m-d\T23:59:59\Z');
        array_splice($words, $position, 1);
        return $words;
    }
    
   /**
    * Process <tomorrow>
    * 
    * @param array $words
    * @param integer $position of word in the list
    */
    private function processWhenTomorrow($words, $position) {
        $time = strtotime(date('Y-m-d') . ' + 1 days');
        $this->outputFilters['time:start'] = date('Y-m-d\T00:00:00\Z', $time);
        $this->outputFilters['time:end'] = date('Y-m-d\T23:59:59\Z', $time);
        array_splice($words, $position, 1);
        return $words;
    }
    
   /**
    * Process <yesterday>
    * 
    * @param array $words
    * @param integer $position of word in the list
    */
    private function processWhenYesterday($words, $position) {
        $time = strtotime(date('Y-m-d') . ' - 1 days');
        $this->outputFilters['time:start'] = date('Y-m-d\T00:00:00\Z', $time);
        $this->outputFilters['time:end'] = date('Y-m-d\T23:59:59\Z', $time);
        array_splice($words, $position, 1);
        return $words;
    }
    
   /**
    * Extract duration
    * 
    * @param array $words
    * @param integer $position of word in the list
    */
    private function extractDuration($words, $position) {
        
        $duration = array(
            'value' => 1
        );
        $endPosition = -1;
        $firstIsNotLast = false;
        
        for ($i = $position, $l = count($words); $i < $l; $i++) {
            
            /*
             * <last> modifier found
             */
            $timeModifier = $this->dictionary->get(RestoDictionary::TIME_MODIFIER, $words[$i]);
            if ($timeModifier === 'last' || $timeModifier === 'next') {
                continue;
            }
            
            /*
             * Exit if stop modifier is found
             */
            if ($this->dictionary->isModifier($words[$i])) {
                $endPosition = $i - 1;
                break;
            }
            
            /*
             * Extract duration
             */
            if ($this->isNumeric($words[$i])) {
                $duration['value'] = $this->toNumeric($words[$i]);
                $endPosition = max(array($i, $endPosition));
                if ($i === $position) {
                    $firstIsNotLast = true;
                }
                continue;
            }
            
            /*  
             * Extract unit
             */           
            $unit = $this->dictionary->get(RestoDictionary::TIME_UNIT, $words[$i]);
            if (isset($unit)) {
                $duration['unit'] = $unit;
                $endPosition = max(array($i, $endPosition));
                if ($i === $position) {
                    $firstIsNotLast = true;
                }
                continue;
            }
        }
        
        return array(
            'duration' => $duration,
            'endPosition' => $endPosition,
            'firstIsNotLast' => $firstIsNotLast
        );
        
    }
    
    /**
     * Extract date from an array words starting analysis at $position
     * Valid patterns are :
     * 
     *      - ISO 8601 date (i.e. "2015-05-01T12:23:34")
     *      - year (i.e. "2015")
     *      - month (i.e. "may")
     *      - month year (i.e. "may 2015")
     *      - "today", "yesterday" or "tomorrow"
     * 
     * @param array $words
     * @param integer $position
     * @param boolean $between
     * 
     */
    private function extractDate($words, $position, $between = false) {
     
        $date = array();
        $endPosition = -1;
        
        for ($i = $position, $l = count($words); $i < $l; $i++) {
            
            /*
             * Today, Tomorrow and Yesterday
             */
            $timeModifier = $this->dictionary->get(RestoDictionary::TIME_MODIFIER, $words[$i]);
            if (isset($timeModifier)) {
                $time = null;
                if ($timeModifier === 'today') {
                    $time = strtotime(date('Y-m-d'));
                }
                else if (isset($timeModifier) && $timeModifier === 'tomorrow') {
                    $time = strtotime(date('Y-m-d') . ' + 1 days');
                }
                else if (isset($timeModifier) && $timeModifier === 'yesterday') {
                    $time = strtotime(date('Y-m-d') . ' - 1 days');
                } 
                if (isset($time)) {
                    $endPosition = $i;
                    $date = array(
                        'year' => date('Y', $time),
                        'month' => date('m', $time),
                        'day' => date('d', $time)
                    );
                    break;
                }
            }
            
            /*
             * Between stop modifier is 'and'
             */
            if ($between && $this->dictionary->get(RestoDictionary::VARIOUS_MODIFIER, $words[$i]) === 'and') {
                $endPosition = $i;
                break;
            }
            
            /*
             * Exit if stop modifier is found
             */
            if ($this->dictionary->isModifier($words[$i])) {
                $endPosition = $i - 1;
                break;
            }

            /*
             * Year
             */
            if (preg_match('/^\d{4}$/i', $words[$i])) {
                $date['year'] = $words[$i];
                $endPosition = max(array($i, $endPosition));
                continue;
            }

            /*
             * Textual month
             */
            $month = $this->dictionary->get(RestoDictionary::MONTH, $words[$i]);
            if ($month) {
                $date['month'] = $month;
                $endPosition = max(array($i, $endPosition));
                continue;
            }
            
            /*
             * Day is an int value < 31
             */
            if (is_numeric($words[$i])) {
                $d = intval($words[$i]);
                if ($d > 0 && $d < 31) {
                    $date['day'] = $d < 10 ? '0' . $d : $d;
                    $endPosition = max(array($i, $endPosition));
                }
                continue;
            }
            
            /*
             * ISO8601 date
             */
            if (RestoUtil::isISO8601($words[$i])) {
                $date = $this->iso8601ToDate($words[$i]);
                $endPosition = max(array($i, $endPosition));
                continue;
            }
            
            /*
             * TODO Season
             *
            if (($season = $this->dictionary->getSeason($searchTerms[$i])) !== null) {
                switch($season) {
                    case 'winter':
                        $this->explicits['month:01|02|03'] = true;
                        break;
                    case 'spring':
                        $this->explicits['month:04|05|06'] = true;
                        break;
                    case 'summer':
                        $this->explicits['month:07|08|09'] = true;
                        break;
                    case 'automn':
                        $this->explicits['month:10|11|12'] = true;
                        break;
                    default:
                        break;
                }
            }
             * 
             */
        }
        
        return array(
            'date' => $date,
            'endPosition' => $endPosition
        );
    }

    /**
     * Convert ISO8601 string to year/month/date/time array
     * @param string $iso8601
     * @return array
     */
    private function iso8601ToDate($iso8601) {

        $length = strlen($iso8601);

        /*
         * Year and month
         */
        if ($length === 7) {
            return array(
                'year' => substr($iso8601, 0, 4),
                'month' => substr($iso8601, 5, 2)
            );
        }
        
        /*
         * Year, month and day
         */
        if ($length === 10) {
            return array(
                'year' => substr($iso8601, 0, 4),
                'month' => substr($iso8601, 5, 2),
                'day' => substr($iso8601, 8, 2)
            );
        }
        
        return array(
            'year' => substr($iso8601, 0, 4),
            'month' => substr($iso8601, 5, 2),
            'day' => substr($iso8601, 8, 2),
            'time' => str_replace('z', '', substr($iso8601, 11, $length - 11))
        );
    }
    
    /**
     * Convert date (year/month/date/time) array to ISO8601 string
     * 
     * @param array $date
     * @param boolean $endOfDay
     * @return string
     */
    private function dateToISO8601($date, $endOfDay = false) {
        
        /*
         * Set current year if not set
         */
        if (!isset($date['year'])) {
            $date['year'] = date('Y');
        }
        
        /*
         * Set current month if not set
         */
        if (!isset($date['month'])) {
            $date['month'] = date('m');
        }
        
        /*
         * Set current day if not set
         */
        if (!isset($date['day'])) {
            $date['day'] = date('d');
        }
        
        /*
         * Set current time if not set
         */
        if (!isset($date['time'])) {
            $date['time'] = $endOfDay ? '23:59:59' : '00:00:00';
        }
        
        return $date['year'] . '-' . $date['month'] . '-' . $date['day'] . 'T' . $date['time'] . 'Z';
        
    }
    
    /**
     * Return the lowest day of the given date as ISO 8601
     * Exemple :
     *      "2015" would return "2015-01-01T00:00:00"
     *      "may 2015" would return "2015-05-01T00:00:00"
     *  
     * @param type $date
     */
    private function toLowestDay($date) {
        if (!isset($date['month'])) {
            $date['month'] = '01';
        }
        if (!isset($date['day'])) {
            $date['day'] = '01';
        }
        return $this->dateToISO8601($date);
    }
    
    /**
     * Return the greatest day of the given date as ISO 8601
     * Exemple :
     *      "2015" would return "2015-12-31T23:59:59"
     *      "may 2015" would return "2015-05-31T23:59:59"
     *  
     * @param type $date
     */
    private function toGreatestDay($date) {
        if (!isset($date['year'])) {
            $date['year'] = date('Y');
        }
        if (!isset($date['month'])) {
            $date['month'] = '12';
        }
        if (!isset($date['day'])) {
            $date['day'] = date('d', mktime(0, 0, 0, intval($date['month']) + 1, 0, intval($date['year'])));
        }
        return $this->dateToISO8601($date, true);
    }

    /*
     * Return true if the entry is a numeric value wich is
     * the case if value is really numeric or if value is
     * a string within the numbers dictionary
     * 
     * @param String $str
     */
    private function isNumeric($str) {
        
        if (is_numeric($str)) {
            return true;
        }
        
        if ($this->dictionary->get(RestoDictionary::NUMBER, $str) !== null) {
            return true;
        }
        
        return false;
    }
    
    /*
     * Return numeric value of input $str
     * 
     * @param String $str
     */
    private function toNumeric($str) {
        
        if (is_numeric($str)) {
            return $str;
        }
        
        return $this->dictionary->get(RestoDictionary::NUMBER, $str);
    }
    
    /**
     * Return filter name associated to $quantity
     * 
     * A valid quantity should be defined with searchFilters as
     *      'quantity' => array(
     *          'value' => // name of the quantity (i.e. an existing entry in "quantities" dictionary array)
     *          'unit' => // unit of the quantity (i.e. an existing entry in "units" dictionnary array)
     *      )
     * 
     * @param String $quantity
     */
    private function getSearchFilter($quantity) {
        
        if (!$quantity) {
            return null;
        }
        
        foreach(array_keys($this->model->searchFilters) as $key) {
            if (isset($this->model->searchFilters[$key]['quantity']) && is_array($this->model->searchFilters[$key]['quantity']) && $this->model->searchFilters[$key]['quantity']['value'] === $quantity) {
                return array('key' => $key, 'unit' => $this->model->searchFilters[$key]['quantity']['unit']);
            }
        }
        
        return null;
    }
    
    /**
     * Return normalized unit from $unit
     * e.g. if $unit = 'km', returned value is 
     *      array(
     *          'unit' => 'm',
     *          'factor' => 1000
     *      )
     * 
     * @param string $unit
     */
    private function normalizedUnit($unit) {
        
        if (!$unit) {
            return null;
        }
        
        $factor = 1.0;
        switch ($unit) {
            case 'km':
                $unit = 'm';
                $factor = 1000.0;
                break;
            default:
                break;
        }
        
        return array(
            'unit' => $unit,
            'factor' => $factor
        );
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
     *  - replace spaces by minus sign
     * 
     * @param array $rawWords
     * @return array
     */
    private function cleanRawWords($rawWords) {
        $words = array();
        for ($i = 0, $l = count($rawWords); $i < $l; $i++) {
            $term = trim($rawWords[$i]);
            if ($term === ',' || $term === ';' || $term === '') {
                continue;
            }
            $splitted = explode('%', $term);
            if (count($splitted) === 2 && is_numeric($splitted[0])) {
                $words[] = $splitted[0];
                $words[] = '%';
            }
            else {
                $words[] = str_replace(' ', '-', $rawWords[$i]);
            }
        }
        return $words;
    }

}