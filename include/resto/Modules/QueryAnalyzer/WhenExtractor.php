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
        
        return $duration;
        
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
    
}