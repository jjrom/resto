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
    public function toWords($query) {
        return $this->cleanRawWords(RestoUtil::splitString($this->context->dbDriver->normalize(str_replace(array('\'', ',', ';'), ' ', $query))));
    }

    /**
     * Concatenate words into sentence
     * 
     * @param string $query
     * @return array
     */
    public function toSentence($words, $startPosition, $endPosition) {
        $sentence = '';
        for ($i = $startPosition; $i <= $endPosition; $i++) {
            $sentence .= $words[$i]. ' ';
        }
        return trim($sentence);
    }

    /**
     * 
     * Extract location from sentence
     * 
     * @param array $words
     * @param integer $position of word in the list
     */
    public function extractLocation($words, $position) {
        
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
            echo $words[$i] . "\n";
            $endPosition = $i;
            
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
            if ($this->dictionary->getNumber($words[$i])) {
                $endPosition = max(array($i, $endPosition));
                $duration['value'] = $this->dictionary->getNumber($words[$i]);
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
    public function extractDate($words, $position, $between = false) {
     
        $date = array();
        $endPosition = -1;
        
        for ($i = $position, $l = count($words); $i < $l; $i++) {
            
            $endPosition = $i;
            
            /*
             * Today, Tomorrow and Yesterday
             */
            if ($i === 0) {
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
                        $date = array(
                            'year' => date('Y', $time),
                            'month' => date('m', $time),
                            'day' => date('d', $time)
                        );
                        break;
                    }
                }
            }
            
            /*
             * Between stop modifier is 'and'
             */
            if ($between && $this->dictionary->get(RestoDictionary::VARIOUS_MODIFIER, $words[$i]) === 'and') {
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
                continue;
            }

            /*
             * Textual month
             */
            $month = $this->dictionary->get(RestoDictionary::MONTH, $words[$i]);
            if ($month) {
                $date['month'] = $month;
                continue;
            }
            
            /*
             * Day is an int value < 31
             */
            if (is_numeric($words[$i])) {
                $d = intval($words[$i]);
                if ($d > 0 && $d < 31) {
                    $date['day'] = $d < 10 ? '0' . $d : $d;
                }
                continue;
            }
            
            /*
             * ISO8601 date
             */
            if (RestoUtil::isISO8601($words[$i])) {
                $date = $this->iso8601ToDate($words[$i]);
                continue;
            }
            
            if (!$this->dictionary->isStopWord($words[$i])) {
                $endPosition = $i - 1;
                break;
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
    public function iso8601ToDate($iso8601) {

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
            if ($term === ',' || $term === ';' || $term === '') {
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
     * Return most relevant location from a set of locations
     * 
     * Order of relevance is :
     * 
     *   - Country
     *   - Capitals (i.e. PPLC toponyms)
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
                if (isset($locations[0]['fcode']) && $locations[0]['fcode'] !== 'PPLC' && $locations[0]['fcode'] !== 'PPLA') {
                    $bestPosition = $i;
                }
                break;
            }
        }
        
        $best = $locations[$bestPosition];
        array_splice($locations, $bestPosition, 1);
        return array_merge($best, array('SeeAlso' => $locations));
    }
}