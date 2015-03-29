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
 * QueryAnalyzer When
 * 
 * @param array $params
 */
class WhenProcessor {

    /*
     * Process result
     */
    public $result = array();
    
    /*
     * Reference to QueryAnalyzer
     */
    private $queryAnalyzer;
    
    /**
     * Constructor
     * 
     * @param QueryAnalyzer $queryAnalyzer
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($queryAnalyzer, $context, $user) {
        $this->queryAnalyzer = $queryAnalyzer;
        $this->context = $context;
        $this->user = $user;
    }

    /**
     * Process <after> "date" 
     * 
     * @param array $words
     * @param integer $position
     * @return string
     */
    public function processAfter($words, $position) {
        return $this->processBeforeOrAfter($words, $position, 'time:start');
    }

    /**
     * Process <before> "date" 
     * 
     * @param array $words
     * @param integer $position
     * @return string
     */
    public function processBefore($words, $position) {
        return $this->processBeforeOrAfter($words, $position, 'time:end');
    }
    
    /**
     * 
     * Process <between> "date" <and> "date"
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    public function processBetween($words, $position) {
        
        $endPosition = -1;
        /*
         * Extract first date
         */
        $firstDate = $this->extractDate($words, $position + 1, true);
        
        /*
         * No first date
         */
        if (empty($firstDate['date'])) {
            $endPosition = $firstDate['endPosition'];
        }
        
        /*
         * Extract second date
         */
        $secondDate = $this->extractDate($words, $firstDate['endPosition'] + 2);
        
        /* 
         * No second date
         */
        if (empty($secondDate['date'])) {
            $endPosition = $secondDate['endPosition'];
        }
        
        /*
         * Date interval found
         */
        if ($endPosition === -1) {
            if (!isset($firstDate['date']['year']) && isset($secondDate['date']['year'])) {
                $firstDate['date']['year'] = $secondDate['date']['year'];
            }
            if (!isset($firstDate['date']['month']) && isset($secondDate['date']['month'])) {
                $firstDate['date']['month'] = $secondDate['date']['month'];
            }
            $this->result[] = array('time:start' => $this->toLowestDay($firstDate['date']));
            $this->result[] = array('time:end' => $this->toGreatestDay($secondDate['date']));
            $endPosition = $secondDate['endPosition'];
        }
        else {
            $this->queryAnalyzer->error(QueryAnalyzer::NOT_UNDERSTOOD, $this->queryAnalyzer->toSentence($words, $position, $endPosition));
        }
        array_splice($words, $position, $endPosition - $position + 1);
       
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
    public function processSince($words, $position) {
        
        /*
         * <since> duration
         */
        $duration = $this->extractDuration($words, $position + 1);
        $endPosition = $duration['endPosition'];
        if (isset($duration['duration']['unit'])) {
            $date = array(
                'endPosition' => $endPosition,
                'date' => $this->iso8601ToDate(date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration['duration']['value'] . $duration['duration']['unit'])))
            );
        }
        /*
         * <since> "date"
         */
        else {
           
            $date = $this->extractDate($words, $position + 1);
            $endPosition = $date['endPosition'];
           
            /*
             * If only a day was detected then it's an issue
             */
            if (isset($date['date']['day']) && !isset($date['date']['year']) && !isset($date['date']['month'])) {
                $date['date'] = array();
            }
            
            /*
             * If a month is specified and the month is posterior to
             * the current month then decrease by one year
             */
            if (!isset($date['date']['year']) && isset($date['date']['month']) && ((integer) $date['date']['month'] > (integer) date('m'))) {
                $date['date']['year'] = (integer) date('Y') - 1;
            }
        }
        
        if (empty($date['date'])) {
            $this->queryAnalyzer->error(QueryAnalyzer::NOT_UNDERSTOOD, $this->queryAnalyzer->toSentence($words, $position, $endPosition));
        }
        else {
            $this->result[] = array('time:start' => $this->toLowestDay($date['date']));
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
    public function processLast($words, $position) {
        return $this->processLastOrNext($words, $position, 'last');
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
    public function processNext($words, $position) {
        return $this->processLastOrNext($words, $position, 'next');
    }
    
    /**
     * Process <in> "date"
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    public function processIn($words, $position) {
        
        $date = $this->extractDate($words, $position + 1);
        
        /*
         * No date found - try <in> "location"
         */
        if (empty($date['date'])) {
            return $this->queryAnalyzer->whereProcessor->processIn($words, $position);
        }
        
        $this->result[] = array('time:start' => $this->toLowestDay($date['date']));
        $this->result[] = array('time:end' => $this->toGreatestDay($date['date']));
        array_splice($words, $position, $date['endPosition'] - $position + 1);
        
        return $words;
    }
    
    /**
     * Process "numeric" "units" <ago>
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    public function processAgo($words, $position) {
        
        if ($position - 2 >= 0 && $this->queryAnalyzer->dictionary->getNumber($words[$position - 2])) {
            
            $unit = $this->queryAnalyzer->dictionary->get(RestoDictionary::TIME_UNIT, $words[$position - 1]);
            $duration = $this->queryAnalyzer->dictionary->getNumber($words[$position - 2]);
                    
            /*
             * Known duration unit
             */
            if ($unit === 'days' || $unit === 'months' || $unit === 'years') {
                $this->result[] = array('time:start' => date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration . $unit)) . 'T00:00:00Z');
                $this->result[] = array('time:end' => date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration . $unit)) . 'T23:59:59Z');
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
    public function processToday($words, $position) {
        $this->result[] = array('time:start' => date('Y-m-d\T00:00:00\Z'));
        $this->result[] = array('time:end' => date('Y-m-d\T23:59:59\Z'));
        array_splice($words, $position, 1);
        return $words;
    }
    
   /**
    * Process <tomorrow>
    * 
    * @param array $words
    * @param integer $position of word in the list
    */
    public function processTomorrow($words, $position) {
        $time = strtotime(date('Y-m-d') . ' + 1 days');
        $this->result[] = array('time:start' => date('Y-m-d\T00:00:00\Z', $time));
        $this->result[] = array('time:end' => date('Y-m-d\T23:59:59\Z', $time));
        array_splice($words, $position, 1);
        return $words;
    }
    
   /**
    * Process <yesterday>
    * 
    * @param array $words
    * @param integer $position of word in the list
    */
    public function processYesterday($words, $position) {
        $time = strtotime(date('Y-m-d') . ' - 1 days');
        $this->result[] = array('time:start' => date('Y-m-d\T00:00:00\Z', $time));
        $this->result[] = array('time:end' => date('Y-m-d\T23:59:59\Z', $time));
        array_splice($words, $position, 1);
        return $words;
    }
    
    /**
     * Process <before> or <after> "date" 
     * 
     * @param array $words
     * @param integer $position
     * @return string
     */
    private function processBeforeOrAfter($words, $position, $osKey) {
        
        /*
         * Extract date
         */
        $date = $this->extractDate($words, $position + 1);
        
        /*
         * No date found - remove modifier only from words list
         */
        if (empty($date['date'])) {
            $this->queryAnalyzer->error(QueryAnalyzer::NOT_UNDERSTOOD, $this->queryAnalyzer->toSentence($words, $position,  $date['endPosition']));
        }
        /*
         * Date found - add to outputFilters and remove modifier and date from words list
         */
        else {
            $this->result[] = array($osKey => $osKey === 'time:start' ? $this->toGreatestDay($date['date']) : $this->toLowestDay($date['date']));
        }
        array_splice($words, $position, $date['endPosition'] - $position + 1);
        return $words;
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
    private function processLastOrNext($words, $position, $lastOrNext) {
        
        /*
         * Important ! Start position is one before <next>
         * to process the following
         * 
         *      "numeric" <next> "(year|month|day)"
         *      "(year|month|day)" <next>
         * 
         */
        $duration = $this->extractDuration($words, max(array(0, $position - 1)));
        $delta = 0;
        if (isset($duration['duration']['unit'])) {
            $times = $lastOrNext === 'last' ? array(
                'time' => strtotime(date('Y-m-d') . ' - 1 ' . $duration['duration']['unit']),
                'pTime' => strtotime(date('Y-m-d') . ' - ' . $duration['duration']['value'] . $duration['duration']['unit'])
                    ) :
                    array(
                'time' => strtotime(date('Y-m-d') . ' + ' . $duration['duration']['value'] . $duration['duration']['unit']),
                'pTime' => strtotime(date('Y-m-d') . ' + 1 ' . $duration['duration']['unit'])
            );

            $this->setWhenForLastAndNext($times, $duration['duration']['unit']);
            $delta = $duration['firstIsNotLast'] ? 1 : 0;
        }
        else {
            $this->queryAnalyzer->error(QueryAnalyzer::MISSING_UNIT, $this->queryAnalyzer->toSentence($words, $position,  $duration['endPosition']));
        }
        array_splice($words, $position - $delta, $duration['endPosition'] - $position + 1 + $delta);
        return $words;
        
    }
    
    /**
     * Set time:start and time:end from duration
     * @param array $times
     * @param string $unit
     */
    private function setWhenForLastAndNext($times, $unit) {
        switch ($unit) {
            case 'years':
                $this->setWhenForLastAndNextYear($times);
                break;
            case 'months':
                $this->setWhenForLastAndNextMonth($times);
                break;
            case 'days':
                $this->setWhenForLastAndNextDay($times);
                break;
            default:
                break;
        }
    }
    
    /**
     * Set time:start and time:end from year duration
     * 
     * @param array $times
     * @param string $unit
     */
    private function setWhenForLastAndNextYear($times) {
        $this->result[] = array('time:start' => date('Y', $times['pTime']) . '-01-01' . 'T00:00:00Z');
        $this->result[] = array('time:end' => date('Y', $times['time']) . '-12-31' . 'T23:59:59Z');
    }
    
    /**
     * Set time:start and time:end from month duration
     * 
     * @param array $times
     * @param string $unit
     */
    private function setWhenForLastAndNextMonth($times) {
        $this->result[] = array('time:start' => date('Y', $times['pTime']) . '-' . date('m', $times['pTime']) . '-01' . 'T00:00:00Z');
        $this->result[] = array('time:end' => date('Y', $times['time']) . '-' . date('m', $times['time']) . '-' . date('d', mktime(0, 0, 0, intval(date('m', $times['time'])) + 1, 0, intval(date('Y', $times['time'])))) . 'T23:59:59Z');
    }
    
    /**
     * Set time:start and time:end from day duration
     * 
     * @param array $times
     * @param string $unit
     */
    private function setWhenForLastAndNextDay($times) {
        $this->result[] = array('time:start' => date('Y', $times['pTime']) . '-' . date('m', $times['pTime']) . '-' . date('d', $times['pTime']) . 'T00:00:00Z');
        $this->result[] = array('time:end' => date('Y', $times['time']) . '-' . date('m', $times['time']) . '-' . date('d', $times['time']) . 'T23:59:59Z');
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

            $endPosition = $i;

            /*
             * <last> modifier found
             */
            if ($this->isLastOrNext($words[$i])) {
                continue;
            }

            /*
             * Exit if stop modifier is found
             */
            if ($this->queryAnalyzer->dictionary->isModifier($words[$i])) {
                $endPosition = $i - 1;
                break;
            }
    
            /*
             * Extract duration
             */
            $unitOrValue = $this->getDurationUnitOrValue($words[$i]);
            if (isset($unitOrValue)) {
                $duration = array_merge($duration, $unitOrValue);
                if ($i === $position) {
                    $firstIsNotLast = true;
                }
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
     
        /*
         * No words on the first position is an issue
         */
        if (!isset($words[$position])) {
            return array(
                'endPosition' => $position
            );
        }
        
        /*
         * Today, Tomorrow and Yesterday
         */
        $date = $this->getDateFromWord($words[$position]);
        if (!empty($date)) {
            return array(
                'date' => $date,
                'endPosition' => $position
            );
        }
        
        /*
         * Other dates
         */
        $endPosition = $this->queryAnalyzer->getEndPosition($words, $position);
        for ($i = $position; $i <= $endPosition; $i++) {
            
            /*
             * Between stop modifier is 'and'
             */
            if ($between && $this->queryAnalyzer->dictionary->get(RestoDictionary::VARIOUS_MODIFIER, $words[$i]) === 'and') {
                $endPosition = $i - 1;
                break;
            }
            
            /*
             * Check for year/month/day
             */
            $yearMonthDay = $this->getYearMonthDayFromWord($words[$i]);
            if (isset($yearMonthDay)) {
                $date = array_merge($date, $yearMonthDay);
                continue;
            }
            
            /*
             * A non stop word breaks everything
             */
            if (!$this->queryAnalyzer->dictionary->isStopWord($words[$i])) {
                break;
            }
            
            /*
             * TODO Season
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

        $date = array();
        $length = strlen($iso8601);

        /*
         * Year and month
         */
        if ($length > 6) {
            $date['year'] = substr($iso8601, 0, 4);
            $date['month'] = substr($iso8601, 5, 2);
        }
        
        /*
         * Year, month and day
         */
        if ($length > 9) {
            $date['day'] = substr($iso8601, 8, 2);
        }
        
        /*
         * Time
         */
        if ($length > 10) {
            $date['time'] = str_replace('z', '', substr($iso8601, 11, $length - 11));
        }
        
        return $date;
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
    
    /**
     * Return date from a single world like 'today', 'tomorrow' or 'yesterday'
     * 
     * @param string $word
     * @return array
     */
    private function getDateFromWord($word) {
        $timeModifier = $this->queryAnalyzer->dictionary->get(RestoDictionary::TIME_MODIFIER, $word);
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
                return array(
                    'year' => date('Y', $time),
                    'month' => date('m', $time),
                    'day' => date('d', $time)
                );
            }
        }
        return array();
    }
    
    /**
     * Return true if word is <last> or <next>
     * 
     * @param string $word
     */
    private function isLastOrNext($word) {
        $timeModifier = $this->queryAnalyzer->dictionary->get(RestoDictionary::TIME_MODIFIER, $word);
        if ($timeModifier === 'last' || $timeModifier === 'next') {
            return true;
        }
        return false;
    }
    
    /**
     * Return year, month or day from date
     * 
     * @param string $word
     */
    private function getYearMonthDayFromWord($word) {
        
        if (preg_match('/^\d{4}$/i', $word)) {
            return array(
                'year' => $word
            );
        }
        
        $month = $this->queryAnalyzer->dictionary->get(RestoDictionary::MONTH, $word);
        if (isset($month)) {
            return array(
                'month' => $month
            );
        }
        
        if (is_numeric($word)) {
            $day = intval($word);
            if ($day > 0 && $day < 31) {
                return array(
                    'day' => $day < 10 ? '0' . $day : $day
                );
            }
        }
        
        /*
         * ISO8601 date
         */
        if (RestoUtil::isISO8601($word)) {
            return $this->iso8601ToDate($word);
        }

        return null;
    }
    
    /**
     * Return duration unit or value from word
     * 
     * @param string $word
     */
    private function getDurationUnitOrValue($word) {
        
        $value = $this->queryAnalyzer->dictionary->getNumber($word);
        if ($value) {
            return array(
                'value' => $value
            );
        }

        /*
         * Extract unit
         */
        $unit = $this->queryAnalyzer->dictionary->get(RestoDictionary::TIME_UNIT, $word);
        if (isset($unit)) {
            return array(
                'unit' => $unit
            );
        }
        
        return null;
    }
    
}