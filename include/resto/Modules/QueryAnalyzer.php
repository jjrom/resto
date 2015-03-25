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

    /*
     * Reference to dictionary
     */
    private $dictionary;
    
    /*
     * Array of not understood part of the query
     */
    private $notUnderstood = array();
    
    private $remaining = array();
    private $explicits = array();
    
    private $keywords = array();
    
    /*
     * What, When and Where
     */
    private $when = array();
    private $where = array();
    private $what = array();
    
    /*
     * Reference to utilities class
     */
    private $utils;
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
        $this->dictionary = $this->context->dictionary;
        $this->utils = new QueryAnalyzerUtils($context, $user);
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
            'processingTime' => microtime(true) - $startTime
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
        $words = $this->utils->toWords($query);
        
        /*
         * When ?
         */
        $words = $this->processWhen($words);
        
        /*
         * Where ?
         */
        $words = $this->processWhere($words);
        
        
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
        
        return array(
            'What' => $this->what,
            'When' => $this->when,
            'Where' => $this->where,
            'NotUnderstood' => array_merge($words, $this->notUnderstood)
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
            
            switch ($this->dictionary->get(RestoDictionary::TIME_MODIFIER, $words[$i])) {
                
                /*
                 * <before> "date"
                 */
                case 'before':
                    return $this->processWhen($this->processWhenBefore($words, $i));
                
                /*
                 * <after> "date"
                 */
                case 'after':
                    return $this->processWhen($this->processWhenAfter($words, $i));
                
                /*
                 * <between> "date" <and> "date"
                 */
                case 'between':
                    return $this->processWhen($this->processWhenBetweenAnd($words, $i));

                /*
                 * <since> "date"
                 */
                case 'since':
                    return $this->processWhen($this->processWhenSince($words, $i));

                /*
                 * <last> 
                 */
                case 'last':
                    return $this->processWhen($this->processWhenLast($words, $i));

                /*
                 * <next> 
                 */
                case 'next':
                    return $this->processWhen($this->processWhenNext($words, $i));

                /*
                 * <in> "date"
                 */
                case 'in':
                    return $this->processWhen($this->processWhenIn($words, $i));

                /*
                 * "quantity" "unit" <ago>
                 */
                case 'ago':
                    return $this->processWhen($this->processWhenAgo($words, $i));

                /*
                 * Today
                 */
                case 'today':
                    return $this->processWhen($this->processWhenToday($words, $i));

                /*
                 * Tomorrow
                 */
                case 'tomorrow':
                    return $this->processWhen($this->processWhenTomorrow($words, $i));

                /*
                 * Yesterday
                 */
                case 'yesterday':
                    return $this->processWhen($this->processWhenYesterday($words, $i));

                /*
                 * Nothing found
                 */
                default:
                    continue;
                    
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
            
            switch ($this->dictionary->get(RestoDictionary::LOCATION_MODIFIER, $words[$i])) {
                
                /*
                 * <in> "location"
                 */
                case 'in':
                    return $this->processWhere($this->processWhereIn($words, $i));
                
                /*
                 * <after> "date"
                 */
                case 'between':
                    return $this->processWhere($this->processWhereBetweenAnd($words, $i));
                
                /*
                 * Nothing found
                 */
                default:
                    continue;
                    
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
         * Initialize
         */
        $results = array();
        
        /*
         * Extract locations
         */
        $location = $this->utils->extractLocation($words, $position + 1);
        for ($i = 0, $ii = count($location['location']['results']); $i < $ii; $i++) {

            $result = array(
                'name' => $location['location']['results'][$i]['name'],
                'country' => $location['location']['results'][$i]['type'] !== 'country' ? $location['location']['results'][$i]['country'] : null,
                'type' => $location['location']['results'][$i]['type']
            );

            /*
             * Toponym case
             */
            if ($location['location']['results'][$i]['type'] === 'toponym') {
                $result['geo:lon'] = $location['location']['results'][$i]['longitude'];
                $result['geo:lat'] = $location['location']['results'][$i]['latitude'];
            }
            /*
             * Other cases
             */
            else {
                $result['geo:geometry'] = $location['location']['results'][$i]['geometry'];
            }

            $results[] = $result;
        }
        
        if (count($results) > 0) {
            $SeeAlso = $results;
            array_shift($SeeAlso);
            $this->where = array_merge($results[0], array('SeeAlso' => $SeeAlso));
        }
        else {
            $this->where = array('NotFound' => $location['location']['query']);
        }
        
        array_splice($words, $position, $location['endPosition'] - $position + 1);
        
        return $words;
       
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
        $date = $this->utils->extractDate($words, $position + 1);
        
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
            $this->when[$osKey] = $osKey === 'time:start' ? $this->utils->toGreatestDay($date['date']) : $this->utils->toLowestDay($date['date']);
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
        $firstDate = $this->utils->extractDate($words, $position + 1, true);
        
        /*
         * No date found - try <between> "location" <and> "location" 
         */
        if (empty($firstDate['date'])) {
            return $this->processWhereBetween($words, $position);
        }
        
        /*
         * Date found - search for second date
         */  
        $secondDate = $this->utils->extractDate($words, $firstDate['endPosition'] + 1);

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
        $this->when['time:start'] = $this->utils->toLowestDay($firstDate['date']);
        $this->when['time:end'] = $this->utils->toGreatestDay($secondDate['date']);
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
        $date = $this->utils->extractDate($words, $position + 1);
        
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
            $duration = $this->utils->extractDuration($words, $position + 1);
            $endPosition = $duration['endPosition'];
            if (isset($duration['duration']['unit'])) {
                $date = array(
                    'endPosition' => $duration['endPosition'],
                    'date' => $this->utils->iso8601ToDate(date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration['duration']['value'] . $duration['duration']['unit'])))
                );
            }
            else {
                $this->notUnderstood[] = $this->utils->toSentence($words, $position, $endPosition);
            }
        }
        else {
            $endPosition = $date['endPosition'];
        }
        
        if (!empty($date['date'])) {
            $this->when['time:start'] = $this->utils->toLowestDay($date['date']);   
        }
        
        array_splice($words, $position, $endPosition - $position + 1);
        
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
        return $this->processWhenLastOrNext($words, $position, 'last');
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
        return $this->processWhenLastOrNext($words, $position, 'next');
    }
    
    /**
     * Process 
     *      <next> "date"
     *      <last> "date"
     * 
     * Understood structures are :
     *  
     *      <next> "(year|month|day)"
     *      <next> "numeric" "(year|month|day)"
     *      "numeric" <next> "(year|month|day)"
     *      "(year|month|day)" <next>
     * 
     *      <last> "(year|month|day)"
     *      <last> "numeric" "(year|month|day)"
     *      "numeric" <last> "(year|month|day)"
     *      "(year|month|day)" <last>
     * 
     * Example :
     *           
     *      If current date is November 2013 (i.e. 2013-11) then
     *      the "<next> 2 months" are December 2013 and January 2014
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function processWhenLastOrNext($words, $position, $lastOrNext) {
        
        /*
         * Important ! Start position is one before <next>
         * to process the following
         * 
         *      "numeric" <next> "(year|month|day)"
         *      "(year|month|day)" <next>
         * 
         */
        $duration = $this->utils->extractDuration($words, max(array(0, $position - 1)));
        $delta = 0;
        if (isset($duration['duration']['unit'])) {
        
            /*
             * <last>
             */
            if ($lastOrNext === 'last') {
                $time = strtotime(date('Y-m-d') . ' - 1 ' . $duration['duration']['unit']);
                $pTime = strtotime(date('Y-m-d') . ' - ' . $duration['duration']['value'] . $duration['duration']['unit']);
            }
            /*
             * <next>
             */
            else {
                $time = strtotime(date('Y-m-d') . ' + ' . $duration['duration']['value'] . $duration['duration']['unit']);
                $pTime = strtotime(date('Y-m-d') . ' + 1 ' . $duration['duration']['unit']);
            }

            switch ($duration['duration']['unit']) {
                case 'years':
                    $this->when['time:start'] = date('Y', $pTime) . '-01-01' . 'T00:00:00Z';
                    $this->when['time:end'] = date('Y', $time) . '-12-31' . 'T23:59:59Z';
                    break;
                case 'months':
                    $this->when['time:start'] = date('Y', $pTime) . '-' . date('m', $pTime) . '-01' . 'T00:00:00Z';
                    $this->when['time:end'] = date('Y', $time) . '-' . date('m', $time) . '-' . date('d', mktime(0, 0, 0, intval(date('m', $time)) + 1, 0, intval(date('Y', $time)))) . 'T23:59:59Z';
                    break;
                case 'days':
                    $this->when['time:start'] = date('Y', $pTime) . '-' . date('m', $pTime) . '-' . date('d', $pTime) . 'T00:00:00Z';
                    $this->when['time:end'] = date('Y', $time) . '-' . date('m', $time) . '-' . date('d', $time) . 'T23:59:59Z';
                    break;
                default:
                    break;
            }
            $delta = $duration['firstIsNotLast'] ? 1 : 0;
        }
        else {
            $this->notUnderstood[] = $this->utils->toSentence($words, $position, $duration['endPosition']);
        }
        
        array_splice($words, $position - $delta, $duration['endPosition'] - $position + 1 + $delta);
        
        return $words;
        
    }
    
    /**
     * Process <in> "date"
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    private function processWhenIn($words, $position) {
        
        $date = $this->utils->extractDate($words, $position + 1);
        
        /*
         * No date found - try <in> "location"
         */
        if (empty($date['date'])) {
            return $this->processWhereIn($words, $position);
        }
        
        $this->when['time:start'] = $this->utils->toLowestDay($date['date']);
        $this->when['time:end'] = $this->utils->toGreatestDay($date['date']);
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
        
        if ($position - 2 >= 0 && $this->dictionary->getNumber($words[$position - 2])) {
            
            $unit = $this->dictionary->get(RestoDictionary::TIME_UNIT, $words[$position - 1]);
            $duration = $this->dictionary->getNumber($words[$position - 2]);
                    
            /*
             * Known duration unit
             */
            if ($unit === 'days' || $unit === 'months' || $unit === 'years') {
                $this->when['time:start'] = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration . $unit)) . 'T00:00:00Z';
                $this->when['time:end'] = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration . $unit)) . 'T23:59:59Z';
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
        $this->when['time:start'] = date('Y-m-d\T00:00:00\Z');
        $this->when['time:end'] = date('Y-m-d\T23:59:59\Z');
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
        $this->when['time:start'] = date('Y-m-d\T00:00:00\Z', $time);
        $this->when['time:end'] = date('Y-m-d\T23:59:59\Z', $time);
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
        $this->when['time:start'] = date('Y-m-d\T00:00:00\Z', $time);
        $this->when['time:end'] = date('Y-m-d\T23:59:59\Z', $time);
        array_splice($words, $position, 1);
        return $words;
    }
    
}