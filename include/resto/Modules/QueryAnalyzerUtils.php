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
 * QueryAnalyzer useful functions
 * 
 * @param array $params
 */
class QueryAnalyzerUtils {

    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user) {
        $this->context = $context;
        $this->user = $user;
        $this->dictionary = $this->context->dictionary;
    }
    
    /**
     * Return most relevant location from a set of locations
     * 
     * Order of relevance is :
     * 
     *   - Country
     *   - Capitals (i.e. PPLC toponyms or PPLG)
     *   - First Administrative division (i.e. PPLA toponyms)
     *   - State
     *   - Other toponyms
     * 
     * @param array $locations
     */
    public function getMostRelevantLocation($locations) {
        
        $bestPosition = 0;
        for ($i = 0, $ii = count($locations); $i < $ii; $i++) {
            if ($locations[$i]['type'] === 'country') {
                $bestPosition = $i;
                break;
            }
            if ($locations[$i]['type'] === 'state') {
                if (isset($locations[0]['fcode']) && $locations[0]['fcode'] !== 'PPLC' && $locations[0]['fcode'] !== 'PPLG' && $locations[0]['fcode'] !== 'PPLA') {
                    $bestPosition = $i;
                }
                break;
            }
        }
        
        $best = $locations[$bestPosition];
        array_splice($locations, $bestPosition, 1);
        return array_merge($best, array('SeeAlso' => $locations));
    }
    
    /**
     * 
     * Extract location from sentence
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    public function extractLocation($words, $position) {
        
        /*
         * Get the last index position
         */
        $endPosition = $this->getEndPosition($words, $position);
        
        /*
         * Location modifier is a country or a state
         */
        $locationModifier = null;
        
        /*
         * Roll over each word
         */
        for ($i = $endPosition; $i >= $position; $i--) {
          
            /*
             * Search for a location modifier
             */
            $locationModifier = $this->getLocationModifier($words, $position, $i);
            
            /*
             * Break if location modifier was found
             */
            if (isset($locationModifier)) {
                break;
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
    * Extract duration
    * 
    * @param array $words
    * @param integer $position of word in the list
    */
    public function extractDuration($words, $position) {
        
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
            if ($this->dictionary->isModifier($words[$i])) {
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
    public function extractDate($words, $position, $between = false) {
     
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
        $endPosition = $this->getEndPosition($words, $position);
        for ($i = $position; $i <= $endPosition; $i++) {
            
            /*
             * Between stop modifier is 'and'
             */
            if ($between && $this->dictionary->get(RestoDictionary::VARIOUS_MODIFIER, $words[$i]) === 'and') {
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
            if (!$this->dictionary->isStopWord($words[$i])) {
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
     * Convert date (year/month/date/time) array to ISO8601 string
     * 
     * @param array $date
     * @param boolean $endOfDay
     * @return string
     */
    public function dateToISO8601($date, $endOfDay = false) {
        
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
    public function toLowestDay($date) {
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
    public function toGreatestDay($date) {
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
    public function getSearchFilter($quantity) {
        
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
    public function normalizedUnit($unit) {
        
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
     * 
     * Extract toponym
     * 
     * @param array $words
     * @param integer $position of word in the list
     * @param array $locationModifier
     */
    private function extractToponym($words, $position, $locationModifier = null) {
        
        /*
         * Initialize gazetteer
         */
        $gazetteer = new Gazetteer($this->context, $this->user, $this->context->modules['Gazetteer']);
        
        $endPosition = -1;
        
        /*
         * Roll over each word
         */
        $toponymName = '';
        for ($i = $position, $ii = count($words); $i < $ii; $i++) {
          
            $endPosition = $i;
            
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
            
        }
        
        return array(
            'endPosition' => $endPosition,
            'location' => $gazetteer->search(array(
                'q' => trim((empty($toponymName) ? '' : $toponymName . ',') . (isset($locationModifier) ? $locationModifier['keyword'] : ''), ','),
                'wkt' => true
            ))
        );
        
    }
        
    /**
     * Get the last sentence position i.e. the last word position before
     * a modifier or the last word position if no modifier is found
     * @param array $words
     * @param integer $position
     */
    private function getEndPosition($words, $position) {
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
     * Return location modifier (i.e. country or state- from words array
     * 
     * Words are parsed in reverse order to find toponym modifier
     * If input words are array('saint', 'gaudens', 'france')
     * Then keyword will be tested against : 
     *  saint, saint-gaudens, saint-gaudens-france, gaudens, gaudens-france, france
     * 
     * @param array $words
     * @param integer $startPosition
     * @param integer $endPosition
     * @return array
     */
    private function getLocationModifier($words, $startPosition, $endPosition) {
        $locationName = '';
        for ($j = $endPosition; $j >= $startPosition; $j--) {

            /*
             * Reconstruct sentence from words without stop words
             */
            if (!$this->dictionary->isStopWord($words[$j])) {
                $locationName = $words[$j] . ($locationName === '' ? '' : '-') . $locationName;
            }

            $keyword = $this->dictionary->getKeyword(RestoDictionary::LOCATION, $locationName);
            if (isset($keyword)) {
                return array(
                    'startPosition' => min(array($endPosition, $j)),
                    'endPosition' => max(array($endPosition, $j)),
                    'keyword' => $keyword['keyword'],
                    'type' => $keyword['type']
                );
            }

        }
        return null;
    }
    
    /**
     * Return date from a single world like 'today', 'tomorrow' or 'yesterday'
     * 
     * @param string $word
     * @return array
     */
    private function getDateFromWord($word) {
        $timeModifier = $this->dictionary->get(RestoDictionary::TIME_MODIFIER, $word);
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
        $timeModifier = $this->dictionary->get(RestoDictionary::TIME_MODIFIER, $word);
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
        
        $month = $this->dictionary->get(RestoDictionary::MONTH, $word);
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
        
        $value = $this->dictionary->getNumber($word);
        if ($value) {
            return array(
                'value' => $value
            );
        }

        /*
         * Extract unit
         */
        $unit = $this->dictionary->get(RestoDictionary::TIME_UNIT, $word);
        if (isset($unit)) {
            return array(
                'unit' => $unit
            );
        }
        
        return null;
    }

}