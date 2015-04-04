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
     * Reference to QueryManager
     */
    private $queryManager;
    
    /**
     * Constructor
     * 
     * @param QueryManager $queryManager
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($queryManager) {
        $this->queryManager = $queryManager;
    }

    /**
     * Process <after> "date" 
     * 
     * @param integer $startPosition
     * @return string
     */
    public function processAfter($startPosition) {
        return $this->processBeforeOrAfter($startPosition, 'time:start');
    }

    /**
     * Process <before> "date" 
     * 
     * @param integer $startPosition
     * @return string
     */
    public function processBefore($startPosition) {
        return $this->processBeforeOrAfter($startPosition, 'time:end');
    }
    
    /**
     * 
     * Process <between> "date" <and> "date"
     * 
     * @param integer $startPosition of word in the list
     */
    public function processBetween($startPosition) {
        
        /*
         * Extract first date
         */
        $firstDate = $this->extractDate($startPosition + 1, true);
        
        /*
         * Extract second date
         */
        $secondDate = $this->extractDate($firstDate['endPosition'] + 2);
        
        /*
         * Date interval is not valid
         */
        if (!$this->dateIntervalIsValid($firstDate, $secondDate)) {
            $error = QueryAnalyzer::NOT_UNDERSTOOD;
            $endPosition = $firstDate['endPosition'];
        }
  
        /*
         * Valid date interval found
         */
        else {
            if (!isset($firstDate['date']['year']) && isset($secondDate['date']['year'])) {
                $firstDate['date']['year'] = $secondDate['date']['year'];
            }
            if (!isset($firstDate['date']['month']) && isset($secondDate['date']['month'])) {
                $firstDate['date']['month'] = $secondDate['date']['month'];
            }
            $this->addToResult(array(
                'time:start' => $this->toLowestDay($firstDate['date']),
                'time:end' => $this->toGreatestDay($secondDate['date'])
            ));
            $endPosition = $secondDate['endPosition'];
        }
        
        $this->queryManager->discardPositionInterval(__METHOD__, $startPosition, $endPosition, isset($error) ? $error : null);
        
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
     * @param integer $startPosition of word in the list
     */
    public function processSince($startPosition) {
        
        /*
         * <since> duration
         */
        $duration = $this->extractDuration($startPosition + 1);
        if (isset($duration['duration']['unit'])) {
            $date = array(
                'endPosition' => $duration['endPosition'],
                'date' => $this->iso8601ToDate(date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration['duration']['value'] . $duration['duration']['unit'])))
            );
        }
        /*
         * <since> "date"
         */
        else {
           
            $date = $this->extractDate($startPosition + 1);
            
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
            $error = QueryAnalyzer::NOT_UNDERSTOOD;
        }
        else {
            $this->addToResult(array(
                'time:start' => $this->toLowestDay($date['date'])
            ));
        }
        
        $this->queryManager->discardPositionInterval(__METHOD__, $startPosition, $date['endPosition'], isset($error) ? $error : null);
        
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
     * @param integer $startPosition of word in the list
     */
    public function processLast($startPosition) {
        return $this->processLastOrNext($startPosition, 'last');
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
     * @param integer $startPosition of word in the list
     */
    public function processNext($startPosition) {
        return $this->processLastOrNext($startPosition, 'next');
    }
    
    /**
     * Process <in> "date"
     * 
     * @param integer $startPosition of word in the list
     * @param integer $delta
     */
    public function processIn($startPosition, $delta = 1) {
        
        $date = $this->extractDate($startPosition + $delta);
       
        /*
         * No date found - try a season or a duration
         */
        if (empty($date['date'])) {
            $this->processInDuration($startPosition, $delta);
        }
        
        /*
         * Date found
         */
        else {
            $this->processInDate($date, $startPosition);
        }
        
    }
    
    /**
     * Process "numeric" "units" <ago>
     * 
     * @param integer $startPosition of word in the list
     */
    public function processAgo($startPosition) {
        
        if ($this->queryManager->isValidPosition($startPosition - 2)) {
            
            $duration = $this->queryManager->dictionary->getNumber($this->queryManager->words[$startPosition - 2]['word']);
            $unit = $this->queryManager->dictionary->get(RestoDictionary::TIME_UNIT, $this->queryManager->words[$startPosition - 1]['word']);
            
            if ($duration && in_array($unit, array('days', 'months', 'years'))) {
                $this->addToResult(array(
                    'time:start' => date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration . $unit)) . 'T00:00:00Z',
                    'time:end' => date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration . $unit)) . 'T23:59:59Z'
                ));
                
                $this->queryManager->discardPositionInterval(__METHOD__, $startPosition -2, $startPosition);
                
            }
            
        }
         
    }
    
   /**
    * Process <today>
    * 
    * @param integer $position of word in the list
    */
    public function processToday($position) {
        $this->addToResult(array(
            'time:start' => date('Y-m-d\T00:00:00\Z'),
            'time:end' => date('Y-m-d\T23:59:59\Z')
        ));
        $this->queryManager->discardPosition(__METHOD__, $position);
    }
    
   /**
    * Process <tomorrow>
    * 
    * @param integer $position of word in the list
    */
    public function processTomorrow($position) {
        $time = strtotime(date('Y-m-d') . ' + 1 days');
        $this->addToResult(array(
            'time:start' => date('Y-m-d\T00:00:00\Z', $time),
            'time:end' => date('Y-m-d\T23:59:59\Z', $time)
        ));
        $this->queryManager->discardPosition(__METHOD__, $position);
    }
    
   /**
    * Process <yesterday>
    * 
    * @param integer $position of word in the list
    */
    public function processYesterday($position) {
        $time = strtotime(date('Y-m-d') . ' - 1 days');
        $this->addToResult(array(
            'time:start' => date('Y-m-d\T00:00:00\Z', $time),
            'time:end' => date('Y-m-d\T23:59:59\Z', $time)
        ));
        $this->queryManager->discardPosition(__METHOD__, $position);
    }
    
    /**
     * Process <before> or <after> "date" 
     * 
     * @param integer $startPosition
     * @return string
     */
    private function processBeforeOrAfter($startPosition, $osKey) {
        
        /*
         * Extract date
         */
        $date = $this->extractDate($startPosition + 1);
        
        /*
         * No date found - Add "Not understood" message
         */
        if (empty($date['date'])) {
            $error = QueryAnalyzer::NOT_UNDERSTOOD;
        }
        
        /*
         * Date found - add to outputFilters and remove modifier and date from words list
         */
        else {
            $this->addToResult(array(
                $osKey => $osKey === 'time:start' ? $this->toGreatestDay($date['date']) : $this->toLowestDay($date['date'])
            ));
        }
        
        $this->queryManager->discardPositionInterval(__METHOD__, $startPosition, $date['endPosition'], isset($error) ? $error : null);
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
     * @param integer $startPosition of word in the list
     */
    private function processLastOrNext($startPosition, $lastOrNext) {
        
        /*
         * Important ! Start position is one before <next>
         * to process the following
         * 
         *      "numeric" <next> "(year|month|day)"
         *      "(year|month|day)" <next>
         * 
         */
        $duration = $this->extractDuration(max(array(0, $startPosition - 1)));
        $delta = 0;
        if (isset($duration['duration']['unit'])) {
            $this->setResultForLastAndNext($this->getTimesFromDuration($duration, $lastOrNext), $duration['duration']['unit']);
            $delta = $duration['firstIsNotLast'] ? 1 : 0;
        }
        else {
            $error = QueryAnalyzer::MISSING_UNIT;
        }
        
        $this->queryManager->discardPositionInterval(__METHOD__, $startPosition - $delta, $duration['endPosition'], isset($error) ? $error : null);
 
    }
    
    /**
     * Set time:start and time:end from duration
     * @param array $times
     * @param string $unit
     */
    private function setResultForLastAndNext($times, $unit) {
        switch ($unit) {
            case 'years':
                $this->setResultForLastAndNextYear($times);
                break;
            case 'months':
                $this->setResultForLastAndNextMonth($times);
                break;
            case 'days':
                $this->setResultForLastAndNextDay($times);
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
    private function setResultForLastAndNextYear($times) {
        $this->addToResult(array(
            'time:start' => date('Y', $times['pTime']) . '-01-01' . 'T00:00:00Z',
            'time:end' => date('Y', $times['time']) . '-12-31' . 'T23:59:59Z'
        ));
    }
    
    /**
     * Set time:start and time:end from month duration
     * 
     * @param array $times
     * @param string $unit
     */
    private function setResultForLastAndNextMonth($times) {
        $this->addToResult(array(
            'time:start' => date('Y', $times['pTime']) . '-' . date('m', $times['pTime']) . '-01' . 'T00:00:00Z',
            'time:end' => date('Y', $times['time']) . '-' . date('m', $times['time']) . '-' . date('d', mktime(0, 0, 0, intval(date('m', $times['time'])) + 1, 0, intval(date('Y', $times['time'])))) . 'T23:59:59Z'
        ));
    }
    
    /**
     * Set time:start and time:end from day duration
     * 
     * @param array $times
     * @param string $unit
     */
    private function setResultForLastAndNextDay($times) {
        $this->addToResult(array(
            'time:start' => date('Y', $times['pTime']) . '-' . date('m', $times['pTime']) . '-' . date('d', $times['pTime']) . 'T00:00:00Z',
            'time:end' => date('Y', $times['time']) . '-' . date('m', $times['time']) . '-' . date('d', $times['time']) . 'T23:59:59Z'
        ));
    }
    
   /**
    * Extract duration
    * 
    * @param integer $startPosition of word in the list
    */
    private function extractDuration($startPosition) {
        
        $duration = array(
            'duration' => array(
                'value' => 1
            ),
            'endPosition' => -1,
            'firstIsNotLast' => false
        );
        for ($i = $startPosition; $i < $this->queryManager->length; $i++) {

            $duration['endPosition'] = $i;

            /*
             * <last> modifier found
             */
            if ($this->isLastOrNextPosition($i)) {
                continue;
            }

            /*
             * Exit if stop modifier is found
             */
            if ($this->queryManager->isModifierPosition($i)) {
                $duration['endPosition'] = $i - 1;
                break;
            }
    
            /*
             * Extract duration
             */
            $duration = $this->extractDurationUnitOrValue($duration, $i, $i === $startPosition);
    
        }
        
        return $duration;
        
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
     * @param integer $startPosition
     * @param boolean $between
     * 
     */
    private function extractDate($startPosition, $between = false) {
     
        /*
         * No words on the first position is an issue
         */
        if (!$this->queryManager->isValidPosition($startPosition)) {
            return array(
                'endPosition' => $startPosition
            );
        }
        
        /*
         * Today, Tomorrow and Yesterday
         */
        $date = $this->getSpecialDate($startPosition);
        if (!empty($date)) {
            return array(
                'date' => $date,
                'endPosition' => $startPosition
            );
        }
        
        return $this->getDate($startPosition, $between);
        
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
     * Get date from words starting at startPosition
     * 
     * @param type $startPosition
     * @param boolean $between - set to true when call from processBetween...
     * @return array
     */
    private function getDate($startPosition, $between) {
        
        $date = array();
        $endPosition = $this->queryManager->getEndPosition($startPosition);
        for ($i = $startPosition; $i <= $endPosition; $i++) {
            
            /*
             * Between stop modifier is 'and'
             */
            if ($between && $this->queryManager->isAndPosition($i)) {
                $endPosition = $i - 1;
                break;
            }
            
            /*
             * Check for year/month/day
             */
            $yearMonthDay = $this->getYearMonthDay($i);
            if (isset($yearMonthDay)) {
                $date = array_merge($date, $yearMonthDay);
                $realEndPosition = $i;
                continue;
            }
            
            /*
             * A non stop word breaks everything
             */
            if (!$this->queryManager->isStopWordPosition($i)) {
                break;
            }
            
        }
       
        return array(
            'date' => $date,
            'endPosition' => isset($realEndPosition) ? $realEndPosition : $endPosition
        );
    }
    
    /**
     * Return date from a single world like 'today', 'tomorrow' or 'yesterday'
     * 
     * @param string $position
     * @return array
     */
    private function getSpecialDate($position) {
        $timeModifier = $this->queryManager->dictionary->get(RestoDictionary::TIME_MODIFIER, $this->queryManager->words[$position]['word']);
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
     * Return true if word position is <last> or <next>
     * 
     * @param integer $position
     */
    private function isLastOrNextPosition($position) {
        $timeModifier = $this->queryManager->dictionary->get(RestoDictionary::TIME_MODIFIER, $this->queryManager->words[$position]['word']);
        if ($timeModifier === 'last' || $timeModifier === 'next') {
            return true;
        }
        return false;
    }
    
    /**
     * Return year, month or day from date
     * 
     * @param integer $position
     */
    private function getYearMonthDay($position) {
        
        $word = $this->queryManager->words[$position]['word'];
        
        if (preg_match('/^\d{4}$/i', $word)) {
            return array(
                'year' => $word
            );
        }
        
        $month = $this->queryManager->dictionary->get(RestoDictionary::MONTH, $word);
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
     * Return duration unit or value from position
     * 
     * @param array $duration
     * @param integer $position
     * @param boolean $firstIsNotLast
     * 
     */
    private function extractDurationUnitOrValue($duration, $position, $firstIsNotLast = false) {
        
        /*
         * Extract value or unit
         */
        $word = $this->queryManager->words[$position]['word'];
        $value = $this->queryManager->dictionary->getNumber($word);
        if (isset($value)) {
            $duration['duration'] = array_merge($duration['duration'], array('value' => $value));
        }
        else {
            $value = $this->queryManager->dictionary->get(RestoDictionary::TIME_UNIT, $word);
            if (isset($value)) {
                $duration['duration'] = array_merge($duration['duration'], array('unit' => $value));
            }
        }
        if (isset($value) && isset($firstIsNotLast)) {
            $duration['firstIsNotLast'] = true;
        }
        
        return $duration;
        
    }
    
    /**
     * Set season
     * 
     * @param integer $startPosition
     */
    private function getSeasonPosition($startPosition) {
        
        /*
         * Remove eventual stop word
         */
        if ($this->queryManager->isStopWordPosition($startPosition)) {
            $startPosition = $startPosition + 1;
        }
        
        /*
         * Check that new position exists
         */
        if (!$this->queryManager->isValidPosition($startPosition)) {
            return -1;
        }
        
        /*
         * Extract season
         */
        $season = $this->queryManager->dictionary->get(RestoDictionary::SEASON, $this->queryManager->words[$startPosition]['word']);
        if (isset($season)) {
      
            $this->addToResult(array(
                'season' => $season
            ));
            
            /*
             * Search for a year after season
             */
            if ($this->queryManager->isValidPosition($startPosition + 1) && preg_match('/^\d{4}$/i', $this->queryManager->words[$startPosition + 1]['word'])) {
                $this->addToResult(array(
                    'year' => $this->queryManager->words[$startPosition + 1]['word']
                ));
                $startPosition = $startPosition + 1;
            }
            return $startPosition;
            
        }
        return -1;
    }
    
    /**
     * Add a key to result
     * 
     * @param string $key
     * @param string $value
     */
    private function addToResult($filters) {
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'time:start':
                case 'time:end':
                    $this->result[$key] = $value;
                    break;
                default:
                    $this->result[$key] = isset($this->result[$key]) ? $this->result[$key] . '|' . $value : $value;
            }
        }
    }
    
    /**
     * Return timestamp intervals from duration
     * 
     * @param array $duration
     * @param string $lastOrNext
     * @return array
     */
    private function getTimesFromDuration($duration, $lastOrNext) {
        return $lastOrNext === 'last' ? array(
            'time' => strtotime(date('Y-m-d') . ' - 1 ' . $duration['duration']['unit']),
            'pTime' => strtotime(date('Y-m-d') . ' - ' . $duration['duration']['value'] . $duration['duration']['unit'])
                ) :
                array(
            'time' => strtotime(date('Y-m-d') . ' + ' . $duration['duration']['value'] . $duration['duration']['unit']),
            'pTime' => strtotime(date('Y-m-d') . ' + 1 ' . $duration['duration']['unit'])
        );
    }

    /**
     * Process <in> "date"
     * 
     * @param array $date
     * @param integer $startPosition
     */
    private function processInDate($date, $startPosition) {

        /*
         * Only year is specified
         */
        if (isset($date['date']['year'])) {
            $this->addToResult(
                    !isset($date['date']['month']) ?
                    array(
                        'year' => $date['date']['year']
                    ) :
                    array(
                        'time:start' => $this->toLowestDay($date['date']),
                        'time:end' => $this->toGreatestDay($date['date'])
                    )
            );
        }
        else {
            if (isset($date['date']['month'])) {
                $this->addToResult(array(
                    'month' => $date['date']['month']
                ));
            }
            if (isset($date['date']['day'])) {
                $this->addToResult(array(
                    'day' => $date['date']['day']
                ));
            }
        }

        $this->queryManager->discardPositionInterval(__METHOD__, $startPosition, $date['endPosition']);

    }
    
    /**
     * Process <in> "season" or <in> "duration"
     * 
     * @param integer $startPosition
     * @param integer $delta
     */
    private function processInDuration($startPosition, $delta) {
            
        /*
         * Season ?
         */
        $endPosition = $this->getSeasonPosition($startPosition + $delta);
        if ($endPosition !== -1) {
            $this->queryManager->discardPositionInterval(__METHOD__, $startPosition, $endPosition);
        }

        /*
         * Duration
         */
        else {
            $duration = $this->extractDuration($startPosition + $delta);
            if (isset($duration['duration']['unit'])) {
                $this->setResultForLastAndNext($this->getTimesFromDuration($duration, 'next'), $duration['duration']['unit']);
                $this->queryManager->discardPositionInterval(__METHOD__, $startPosition, $duration['endPosition']);
            }
        }
       
    }
    
    /**
     * Return true if both dates are not empty and not identical
     * 
     * @param array $firstDate
     * @param array $secondDate
     */
    private function dateIntervalIsValid($firstDate, $secondDate) {
        if (empty($firstDate['date']) && empty($secondDate['date'])) {
            return false;
        }
        $diff = array_diff($firstDate['date'], $secondDate['date']);
        
        return !empty($diff);
    }
    
}