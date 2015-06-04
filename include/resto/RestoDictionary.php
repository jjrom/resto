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

/*
 * Dictionary class
 */
abstract class RestoDictionary {
    
    const LOCATION_MODIFIER = 'locationModifiers';
    const QUANTITY_MODIFIER = 'quantityModifiers';
    const TIME_MODIFIER = 'timeModifiers';
    const AND_MODIFIER = 'andModifiers';
    const MONTH = 'months';
    const NUMBER = 'numbers';
    const QUANTITY = 'quantities';
    const SEASON = 'seasons';
    const TIME_UNIT = 'timeUnits';
    const UNIT = 'units';
    const CONTINENT = 'continent';
    const COUNTRY = 'country';
    const REGION = 'region';
    const STATE = 'state';
    const NOLOCATION = 'nolocation';
    
    /*
     * Reference to the dictionary language
     */
    public $language;

    /*
     * Explicit liste of dictionary modifiers 
     * that need to be processed as a single word
     * in the query analysis.
     * E.g. "greater than" 
     */
    public $multiwords = array();
    
    /*
     * Dictionary Structure
     * 
     *      locationModifiers => array(),
     *      quantityModifiers => array(),
     *      timeModifiers => array(),
     *      excluded => array(),
     *      months => array(),
     *      numbers => array(),
     *      quantities => array()
     *      seasons => array(),
     *      units => array(),
     *      keywords => array() // Retrieve from database !
     */
    protected $dictionary = array();
    
    /*
     * Translations
     */
    protected $translations = array();
    
    /*
     * Database driver
     */
    private $dbDriver;
    
    /**
     * Constructor
     * 
     * @param RestoDatabaseDriver $dbDriver
     * @throws Exception
     */
    public function __construct($dbDriver) {
        $this->dbDriver = $dbDriver;
        $this->language = strtolower(substr(get_class($this), -2));
        $this->dictionary = array_merge($this->dictionary, $this->dbDriver->get(RestoDatabaseDriver::KEYWORDS, array('language' => $this->language)));
    }
    
    /**
     * Return $property entry in dictionary identified by $name
     * 
     * @param string $property
     * @param string $name : normalized name (see normalize function)
     */
    public function get($property, $name) {
        if (!is_array($this->dictionary[$property]) || !isset($name) || $name === '') {
            return null;
        }
        foreach ($this->dictionary[$property] as $key => $value) {
            for ($i = 0, $l = count($value); $i < $l; $i++) {
                if ($value[$i] === $name) {
                    return $key;
                }
            }
        }
        return null;
    }
    
    /**
     * Return number
     * 
     * @param string $property
     * @param string $name : normalized name (see normalize function)
     * @return integer
     */
    public function getNumber($name) {
        if (is_numeric($name)) {
            return $name;
        }
        $number = $this->get(RestoDictionary::NUMBER, $name);
        return isset($number) ? (integer) $number : null;
    }
    
    /**
     * Return keyword entry in dictionary identified by $name
     * 
     * @param string $name : normalized name
     * @param array $types : keyword types to search in (null means all)
     * @param float $similarity : percentage of similarity
     * @return array ('keywords', 'type')
     */
    public function getKeyword($name, $types, $similarity = 100) {
        
        /*
         * keywords entry is an array of array
         */
        foreach(array_keys($this->dictionary['keywords']) as $currentType) {
            if (isset($types)) {
                if ($types[0] === RestoDictionary::NOLOCATION) {
                    if (in_array($currentType, array(
                        RestoDictionary::CONTINENT,
                        RestoDictionary::COUNTRY,
                        RestoDictionary::REGION,
                        RestoDictionary::STATE
                        
                    ))) {
                        continue;
                    }
                }
                else {
                    if (!in_array($currentType, $types)) {
                        continue;
                    }
                }
            }
            if (isset($this->dictionary['keywords'][$currentType][$name])) {
                return $this->getKeywordFromKey($name, $currentType);
            }
        }
        
        /*
         * Nothing found ? Search for similar pattern
         */
        if ($similarity < 100) {
            return $this->getSimilar($name, $similarity);
        }
        
        return null;
    }
    
    /**
     * Return true if word is a modifier word
     */
    public function isModifier($word) {
        if ($this->get(RestoDictionary::LOCATION_MODIFIER, $word) || $this->get(RestoDictionary::TIME_MODIFIER, $word) || $this->get(RestoDictionary::QUANTITY_MODIFIER, $word)) {
            return true;
        }
        return false;
    }
    
    /**
     * Return true if $name looks like noise
     * 
     * @param string $name : normalized name
     */
    public function isNoise($name) {
        for ($i = count($this->dictionary['noise']); $i--;) {
            if (substr($this->dictionary['noise'][$i], -1) === '%') {
                if (strpos($name, trim($this->dictionary['noise'][$i], '%')) !== false) {
                    return true;
                }
            }
            else {
                if ($name === $this->dictionary['noise'][$i]) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Return true if $name is a stop word
     * 
     * @param string $name : normalized name
     */
    public function isStopWord($name) {
        return in_array($name, $this->dictionary['stopWords']);
    }
       
    /**
     * Return $keyword translation
     * 
     * Example :
     *      
     *      translation: array(
     *          'presentation' => 'Hello. My name is {a:1}. I live in {a:2}'
     *      }
     *  
     *      Call to dictionary->translate('presentation', 'Jérôme', 'Toulouse');
     *      Will return
     * 
     *           'Hello. My name is Jérôme. I live in Toulouse
     * 
     * 
     * @param string $name
     * @param string any number of optional arguments
     */
    public function translate($sentence) {
        if (isset($this->translations) && isset($this->translations[$sentence])) {
            $sentence = $this->translations[$sentence];
        }
        
        if (false !== strpos($sentence, '{a:')) {
            $replace = array();
            $args = func_get_args();
            for ($i = 1, $max = count($args); $i < $max; $i++) {
                $replace['{a:' . $i . '}'] = $args[$i];
            }

            return strtr($sentence, $replace);
        }
        return $sentence;
    }
    
    
    /**
     * Return first keyword from input value or input value if not found
     * 
     * @param string $inputValue
     */
    public function getKeywordFromValue($inputValue, $type = null) {
        if (!isset($type)) {
            return null;
        }
        if ($type === 'month') {
            return isset($this->dictionary['months'][$inputValue]) ? ucfirst($this->dictionary['months'][$inputValue][0]) : null;
        }
        if (isset($this->dictionary['keywords'][$type])) {
            foreach (array_values($this->dictionary['keywords'][$type]) as $obj) {
                if ($inputValue === $obj['value']) {
                    return $obj['name'];
                }
            }
        }
        return null;
    }
    
    /**
     * Strip unwnted prefix from word
     *  
     * @param string $word
     */
    public function stripPrefix($word) {
        for ($i = count($this->dictionary['prefixes']); $i--;) {
            $prefixLength = strlen($this->dictionary['prefixes'][$i]);
            $wordLength = strlen($word);
            if ($wordLength >= $prefixLength && substr($word, 0, $prefixLength) === $this->dictionary['prefixes'][$i]) {
                $word = substr($word, $prefixLength, $wordLength);
                break;
            }
        }
        return $word;
    }
    
    /**
     * Return the more similar dictionary keyword from input string
     * Return null if similarity is < 90%
     * 
     * @param string $s
     * @param float $similarity
     * 
     */
    private function getSimilar($s, $similarity) {
        
        $similar = null;
        foreach(array_keys($this->dictionary['keywords']) as $type) {
            foreach(array_keys($this->dictionary['keywords'][$type]) as $key) {
                $percentage = 0.0;
                similar_text($s, $key, $percentage);
                if ($percentage >= $similarity) {
                    $similar = array('keyword' => $this->dictionary['keywords'][$type][$key], 'type' => $type, 'similarity' => $percentage);
                    $similarity = $percentage;
                }
            }
        }
        
        return $similar;
    }
    
    /**
     * Return keyword 
     * 
     * @param string $name
     * @param array $type
     */
    private function getKeywordFromKey($name, $type) {
        if (!isset($this->dictionary['keywords'][$type][$name]['bbox'])) {
            return array('keyword' => $this->dictionary['keywords'][$type][$name]['value'], 'type' => $type);
        }
        else {
            return array('keyword' => $this->dictionary['keywords'][$type][$name]['value'], 'bbox' => $this->dictionary['keywords'][$type][$name]['bbox'], 'isoa2' => $this->dictionary['keywords'][$type][$name]['isoa2'], 'type' => $type);
        }
    }
    
}
