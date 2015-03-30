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
    const ALL = 'all';
    const LOCATION = 'location';
    const NOLOCATION = 'nolocation';
    
    /*
     * Reference to the dictionary language
     */
    public $language;

    /*
     * Database driver
     */
    private $dbDriver;
    
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
     * Add local dictionary to Dictionary
     * 
     * Local dictionary example :
     * 
     *  'dictionary_en' => array(
     *      'keywords' => array(
     *          'other' => array(
     *              'cyclone' => 'cyclone',
     *              'huricane' => 'cyclone'
     *          )
     *      ),
     *      'translation' => array(
     *         'oil_spill' => 'Oil Spill',
     *         'volcanic_eruption' => 'Volcanic Eruption'
     *      )
     *  )
     * 
     * @param array $dictionary
     */
    public function add($dictionary = array()) {
        
        $default = 'dictionary_' . $this->language;
        
        if (is_array($dictionary) && isset($dictionary[$default])) {
            
            /*
             * Update "quantities"
             */
            if (is_array($dictionary[$default]['quantities'])) {
                foreach ($dictionary[$default]['quantities'] as $keyword => $value) {
                    $this->dictionary['quantities'][$keyword] = $value;
                }
            }
            /*
             * Update "keywords"
             */
            if (is_array($dictionary[$default]['keywords'])) {
                foreach (array_keys($dictionary[$default]['keywords']) as $type) {
                    foreach ($dictionary[$default]['keywords'][$type] as $keyword => $value) {
                        $this->dictionary['keywords'][$type][$keyword] = $value;
                    }
                }
            }
            /*
             * Update "translation"
             */
            if (is_array($dictionary[$default]['translations'])) {
                foreach ($dictionary[$default]['translations'] as $keyword => $value) {
                    $this->translations[$keyword] = $value;
                }
            }
            
        }
        
    }
    
    /**
     * Add translations to dictionary
     * 
     * @param array $translations
     */
    public function addTranslations($translations = array()) {
        $this->translations = array_merge($this->translations, $translations);
    }
    
    /**
     * Return translation array
     */
    public function getTranslation() {
        return $this->translations;
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
     * Return true if word is a modifier word
     */
    public function isModifier($word) {
        if ($this->get(RestoDictionary::LOCATION_MODIFIER, $word) || $this->get(RestoDictionary::TIME_MODIFIER, $word) || $this->get(RestoDictionary::QUANTITY_MODIFIER, $word)) {
            return true;
        }
        return false;
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
     * @param string $type : keyword type
     * @param string $name : normalized name
     * @param float $similarity : percentage of similarity
     * @return array ('keywords', 'type')
     */
    public function getKeyword($type, $name, $similarity = 100) {
        
        if ($type === RestoDictionary::LOCATION) {
            return $this->getLocationKeyword($name, $similarity);
        }
        
        if ($type !== RestoDictionary::ALL && $type !== RestoDictionary::NOLOCATION) {
            return $this->getKeywordFromKey($type, $name);
        }
        
        /*
         * keywords entry is an array of array
         */
        foreach(array_keys($this->dictionary['keywords']) as $currentType) {
            if ($type === RestoDictionary::NOLOCATION && in_array($currentType, array(RestoDictionary::CONTINENT, RestoDictionary::COUNTRY, RestoDictionary::REGION, RestoDictionary::STATE))) {
                continue;
            }
            if (isset($this->dictionary['keywords'][$currentType][$name])) {
                return $this->getKeywordFromKey($currentType, $name);
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
     * Return all keywords entry in dictionary
     * 
     */
    public function getKeywords() {
        return $this->dictionary['keywords'];
    }
    
    /**
     * Return true if $name value is present in
     * keywords array
     * 
     * @param string $value
     */
    public function isKeywordsValue($value) {
        foreach (array_keys($this->dictionary['keywords']) as $type) {
            if (isset($this->dictionary['keywords'][$type][$value])) {
                return true;
            }
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
            if (strpos($name, $this->dictionary['noise'][$i]) !== false) {
                return true;
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
        
        if (!isset($this->translations)) {
            return $sentence;
        }
        
        /*
         * Replace additional arguments
         */
        if (isset($this->translations[$sentence])) {
            if (false !== strpos($this->translations[$sentence], '{a:')) {
                $replace = array();
                $args = func_get_args();
                for ($i = 1, $max = count($args); $i < $max; $i++) {
                    $replace['{a:' . $i . '}'] = $args[$i];
                }

                return strtr($this->translations[$sentence], $replace);
            }
        }
        return isset($this->translations[$sentence]) ? $this->translations[$sentence] : $sentence;
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
     * Return location keyword (i.e. one of continent, country, region or state)
     * 
     * @param string $name
     * @param integer $similarity
     * @return type
     */
    private function getLocationKeyword($name, $similarity) {
        $continent = $this->getKeyword(RestoDictionary::CONTINENT, $name, $similarity);
        if (isset($continent)) {
            return $continent;
        }
        $country = $this->getKeyword(RestoDictionary::COUNTRY, $name, $similarity);
        if (isset($country)) {
            return $country;
        }
        $region = $this->getKeyword(RestoDictionary::REGION, $name, $similarity);
        if (isset($region)) {
            return $region;
        }
        return $this->getKeyword(RestoDictionary::STATE, $name, $similarity);
    }
    
    /**
     * Return keyword 
     * 
     * @param array $type
     * @param string $name
     */
    private function getKeywordFromKey($type, $name) {
        if (isset($this->dictionary['keywords'][$type][$name])) {
            if (!isset($this->dictionary['keywords'][$type][$name]['bbox'])) {
                return array('keyword' => $this->dictionary['keywords'][$type][$name]['value'], 'type' => $type);
            } else {
                return array('keyword' => $this->dictionary['keywords'][$type][$name]['value'], 'bbox' => $this->dictionary['keywords'][$type][$name]['bbox'], 'isoa2' => $this->dictionary['keywords'][$type][$name]['isoa2'], 'type' => $type);
            }
        }
        return null;
    }
    
}
