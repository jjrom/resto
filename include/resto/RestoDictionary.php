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
     *      excluded => array(),
     *      modifiers => array(),
     *      units => array(),
     *      months => array(),
     *      numbers => array(),
     *      quantities => array()
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
        $keywords = $this->dbDriver->getKeywords($this->language);
        foreach (array_keys($keywords) as $type) {
            foreach ($keywords[$type] as $keyword => $value) {
                $this->dictionary[$type][$keyword] = $value;
            }
        }
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
    final public function add($dictionary = array()) {
        
        $a = 'dictionary_' . $this->language;
        
        if (is_array($dictionary) && isset($dictionary[$a])) {
            
            /*
             * Update "quantities"
             */
            if (is_array($dictionary[$a]['quantities'])) {
                foreach ($dictionary[$a]['quantities'] as $keyword => $value) {
                    $this->dictionary['quantities'][$keyword] = $value;
                }
            }
            /*
             * Update "keywords"
             */
            if (is_array($dictionary[$a]['keywords'])) {
                foreach (array_keys($dictionary[$a]['keywords']) as $type) {
                    foreach ($dictionary[$a]['keywords'][$type] as $keyword => $value) {
                        $this->dictionary['keywords'][$type][$keyword] = $value;
                    }
                }
            }
            /*
             * Update "translation"
             */
            if (is_array($dictionary[$a]['translations'])) {
                foreach ($dictionary[$a]['translations'] as $keyword => $value) {
                    $this->translations[$keyword] = $value;
                }
            }
            
        }
        
    }
    
    /**
     * Return translation array
     */
    final public function getTranslation() {
        return $this->translations;
    }
    
    /**
     * Return $property entry in dictionary identified by $name
     * 
     * @param string $property
     * @param string $name : lowered and unaccent name
     */
    final public function get($property, $name) {
        if (!is_array($this->dictionary[$property])) {
            return null;
        }
        
        return isset($this->dictionary[$property][$name]) ? $this->dictionary[$property][$name] : null;
    }
    
    /**
     * Return modifier entry in dictionary identified by $name
     * 
     * @param string $name
     */
    final public function getModifier($name) {
        return $this->get('modifiers', $name);
    }
    
    /**
     * Return unit entry in dictionary identified by $name
     * 
     * @param string $name
     */
    final public function getUnit($name) {
        return $this->get('units', $name);
    }
    
    /**
     * Return month entry in dictionary identified by $name
     * 
     * @param string $name
     */
    final public function getMonth($name) {
        return $this->get('months', $name);
    }
    
    /**
     * Return number entry in dictionary identified by $name
     * 
     * @param string $name
     */
    final public function getNumber($name) {
        return $this->get('numbers', $name);
    }
    
    /**
     * Return quantity entry in dictionary identified by $name
     * 
     * @param string $name
     */
    final public function getQuantity($name) {
        return $this->get('quantities', $name);
    }
    
    /**
     * Return instrument entry in dictionary identified by $name
     * 
     * @param string $name
     */
    final public function getInstrument($name) {
        if (!is_array($this->dictionary['keywords']) || !is_array($this->dictionary['keywords']['instrument'])) {
            return null;
        }
        return isset($this->dictionary['keywords']['instrument'][$name]) ? $this->dictionary['keywords']['instrument'][$name]['value'] : null;
    }
 
    /**
     * Return platform entry in dictionary identified by $name
     * 
     * @param string $name
     */
    final public function getPlatform($name) {
        if (!is_array($this->dictionary['keywords']) || !is_array($this->dictionary['keywords']['platform'])) {
            return null;
        }
        return isset($this->dictionary['keywords']['platform'][$name]) ? $this->dictionary['keywords']['platform'][$name]['value'] : null;
    }
    
    /**
     * Return keyword entry in dictionary identified by $name
     * 
     * @param string $name : lower(unaccent(...)))
     * @param string $type
     * @return array ('keywords', 'type')
     */
    final public function getKeyword($name, $type = null) {
        
        if (!is_array($this->dictionary['keywords'])) {
            return null;
        }
        
        /*
         * keywords entry is an array of array
         */
        foreach(array_keys($this->dictionary['keywords']) as $type) {
            if (isset($this->dictionary['keywords'][$type][$name])) {
                return array('keyword' => $this->dictionary['keywords'][$type][$name]['value'], 'type' => $type); 
            }
        }
        
        return null;
    }
    
    /**
     * Return all keywords entry in dictionary
     * 
     */
    final public function getKeywords() {
        return $this->dictionary['keywords'];
    }
    
    /**
     * Return true if $name value is present in
     * keywords array
     * 
     * @param string $value
     */
    final public function isKeywordsValue($value) {
        foreach (array_keys($this->dictionary['keywords']) as $type) {
            if (isset($this->dictionary['keywords'][$type][$value])) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Return true if $name is an excluded word
     * 
     * @param string $name : lower(unaccent(...)))
     */
    final public function isExcluded($name) {
        if (!is_array($this->dictionary['excluded'])) {
            return false;
        }
        return in_array($name, $this->dictionary['excluded']);
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
    final public function translate($sentence) {
        
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
    final public function getKeywordFromValue($inputValue, $type = null) {
        if (isset($type) && isset($this->dictionary['keywords'][$type])) {
            foreach ($this->dictionary['keywords'][$type] as $key => $value) {
                if ($inputValue === $value) {
                    return $key;
                }
            }
        }
        return ucwords($inputValue);
    }
    
    /*
     * Return the more similar dictionary keyword from input string
     * Return null if similarity is < 90%
     * 
     * @param {String} $s
     */
    final public function getSimilar($s, $percentage = 90) {
        
        $similar = null;
        foreach(array_keys($this->dictionary['keywords']) as $type) {
            foreach(array_keys($this->dictionary['keywords'][$type]) as $key) {
                $p = 0.0;
                similar_text($s, $key, $p);
                if ($p >= $percentage) {
                    $similar = array('keyword' => $this->dictionary['keywords'][$type][$key], 'type' => $type, 'similarity' => $p);
                    $percentage = $p;
                }
            }
        }
        
        return $similar;
    }
    

}
