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
 * Extractor for QueryAnalyzer When processor
 * 
 * @param array $params
 */
class WhenExtractor {

    /*
     * Reference to QueryManager
     */
    private $queryManager;
    
    /**
     * Constructor
     * 
     * @param QueryManager $queryManager
     */
    public function __construct($queryManager) {
        $this->queryManager = $queryManager;
    }
   
   /**
    * Extract duration
    * 
    * @param integer $startPosition of word in the list
    */
    public function extractDuration($startPosition) {
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
            
            if (!isset($duration) || isset($duration['duration']['unit'])) {
                break;
            }
        }
        
        if (!isset($duration['duration']['unit'])) {
            return null;
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
    public function extractDate($startPosition, $between = false) {
     
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
    public function iso8601ToDate($iso8601) {

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
        
        return isset($value) ? $duration : null;
        
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
        
        $number = $this->queryManager->dictionary->getNumber($word);
        if (is_numeric($number)) {
            $day = intval($number);
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
    
}