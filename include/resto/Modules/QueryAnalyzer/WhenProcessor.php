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
 * QueryAnalyzer When
 * 
 * @param array $params
 */
require 'WhenExtractor.php';
class WhenProcessor {

    /*
     * Process result
     */
    private $result = array();
    
    /*
     * Reference to QueryManager
     */
    private $queryManager;
    
    /*
     * Reference to WhenExtractor
     */
    private $extractor;
    
    /*
     * Seasons start and stop
     */
    private $seasons = array(
        'spring' => array(0, '03', '21', 0, '06', '20'),
        'summer' => array(0, '06', '21', 0, '09', '20'),
        'autumn' => array(0, '09', '21', 0, '12', '20'),
        'winter' => array(-1, '12', '21', 0, '03', '20')
    );
    
    /**
     * Constructor
     * 
     * @param QueryManager $queryManager
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($queryManager) {
        $this->queryManager = $queryManager;
        $this->extractor = new WhenExtractor($this->queryManager);
    }

    /**
     * Return processing result
     * 
     * @return array
     */
    public function getResult() {
       
        if (isset($this->result['year']) && strpos($this->result['year'], "|") === false) {
            
            /*
             * Translate year:xxx and season:xxx to time:start/time:stop
             * if it makes sense (i.e. "summer 2012")
             */
            if (isset($this->result['season'])) {
                $seasons = explode('|', $this->result['season']);
                for ($i = 0, $ii = count($seasons); $i < $ii; $i++) {
                    $this->seasonToInterval($seasons[$i], $this->result['year']);
                }
                unset($this->result['season'], $this->result['year']);
            }
            
            /*
             * Translate year:xxx to time:start/time:stop
             */
            else {
                $this->addToResult(array(
                    'time:start' => $this->toLowestDay(array('year' => $this->result['year'])),
                    'time:end' => $this->toGreatestDay(array('year' => $this->result['year']))
                ));
                unset($this->result['year']);
            }
            
        }
        
        return $this->result;
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
        $firstDate = $this->extractor->extractDate($startPosition + 1, true);
        
        /*
         * Extract second date
         */
        $secondDate = $this->extractor->extractDate($firstDate['endPosition'] + 2);
        
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
            $this->queryManager->discardPositionInterval(__METHOD__, $startPosition, $endPosition, isset($error) ? $error : null);
        }
        
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
        $duration = $this->extractor->extractDuration($startPosition + 1);
        if (isset($duration)) {
            $date = array(
                'endPosition' => $duration['endPosition'],
                'date' => $this->extractor->iso8601ToDate(date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $duration['duration']['value'] . $duration['duration']['unit'])))
            );
        }
        /*
         * <since> "date"
         */
        else {
           
            $date = $this->extractor->extractDate($startPosition + 1);
            
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
        
        $date = $this->extractor->extractDate($startPosition + $delta);
        
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
        $date = $this->extractor->extractDate($startPosition + 1);
        
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
        $duration = $this->extractor->extractDuration(max(array(0, $startPosition - 1)));
        if (isset($duration)) {
            if ($duration['endPosition'] === $startPosition - 1) {
                $duration['endPosition'] = $duration['endPosition'] + 1; 
            }
        }
        /*
         * <next> "(year|month|day)"
         */
        else {
            $startPosition = $startPosition + 1;
            $duration = $this->extractor->extractDuration($startPosition);
        }
        
        $delta = 0;
        
        if (isset($duration)) {
            $this->setResultForLastAndNext($this->getTimesFromDuration($duration, $lastOrNext), $duration['duration']['unit']);
            $delta = $duration['firstIsNotLast'] ? 1 : 0;
        }
        else {
            $error = QueryAnalyzer::MISSING_UNIT;
        }
        
        $this->queryManager->discardPositionInterval(__METHOD__, $startPosition - $delta, isset($duration) ? $duration['endPosition'] : $startPosition, isset($error) ? $error : null);
 
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
     * Add keys/values to result
     * 
     * @param string $key
     * @param string $value
     */
    private function addToResult($filters) {
        $time = array();
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'time:start':
                case 'time:end':
                    $time[$key] = $value;
                    break;
                default:
                    $this->result[$key] = isset($this->result[$key]) ? $this->result[$key] . '|' . $value : $value;
            }
        }
        $this->addTime($time);
    }
    
    /**
     * Add time:start/time:end interval
     * 
     * @param array $time
     */
    private function addTime($time) {
        if (!empty($time)) {
            if (!isset($this->result['times'])) {
                $this->result['times'] = array();
            }
            $this->result['times'][] = $time;
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
            $duration = $this->extractor->extractDuration($startPosition + $delta);
            if (isset($duration)) {
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
        if (empty($firstDate['date']) || empty($secondDate['date'])) {
            return false;
        }
        $diff = array_diff($firstDate['date'], $secondDate['date']);
        
        return !empty($diff);
    }
    
    /**
     * Convert season to time:start/time:stop array
     * 
     * @param string $season
     * @param integer $year
     */
    private function seasonToInterval($season, $year) {
        $magics = $this->seasons[$season];
        if (isset($magics)) {
            $this->addToResult(array(
                'time:start' => $this->toLowestDay(array(
                    'year' => $year + $magics[0],
                    'month' => $magics[1],
                    'day' => $magics[2]
                )),
                'time:end' => $this->toGreatestDay(array(
                    'year' => $year + $magics[3],
                    'month' => $magics[4],
                    'day' => $magics[5]
                )),
            ));
        }
    }
    
}