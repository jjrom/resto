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
 * Query Manager for QueryAnalyzer module
 */
class QueryManager {

    /*
     * Words
     */
    public $words = array();

    /*
     * Query length
     */
    public $length;
    
    /*
     * Analysis error
     */
    public $errors = array();
    
    /*
     * Dictionary
     */
    public $dictionary;
    
    /*
     * Model
     */
    public $model;
    
    /**
     * Constructor
     * 
     * @param RestoDictionary $dictionary
     * @param RestoModel $model
     */
    public function __construct($dictionary, $model) {
        $this->dictionary = $dictionary;
        $this->model = isset($model) ? $model : new RestoModel_default();
    }
    
    /**
     * Set position processed status to true
     * 
     * @param string $by
     * @param integer $position
     * @param string $error
     */
    public function discardPosition($by, $position, $error = null) {
        $this->words[$position]['processed'] = true;
        $this->words[$position]['by'] = $by;
        if (isset($error)) {
            $this->words[$position]['error'] = $error;
        }
    }
    
    /**
     * Add position to list of processed words positions
     * 
     * @param string $by
     * @param integer $startPosition
     * @param integer $endPosition
     * @param string $error
     */
    public function discardPositionInterval($by, $startPosition, $endPosition, $error = null) {
        if ($startPosition > $endPosition) {
            $this->discardPosition($by, $startPosition, $error);
        }
        else {
            for ($i = $startPosition; $i <= $endPosition; $i++) {
                $this->discardPosition($by, $i, $error);
            }
        }
        return true;
    }
    
    /**
     * Return true if position is valid i.e. word exist and is not yet processed
     * 
     * @param integer $position
     */
    public function isValidPosition($position) {
        if ($this->isNull($position)) {
            return false;
        }
        return !$this->words[$position]['processed'];
    }
    
    /**
     * Return true if word at position is 'and'
     * 
     * @param integer $position
     */
    public function isAndPosition($position) {
        if ($this->isNull($position)) {
            return false;
        }
        if ($this->dictionary->get(RestoDictionary::AND_MODIFIER, $this->words[$position]['word']) === 'and') {
            return true;
        }
        return false;
    }
     
    /**
     * Return true if word at position $position is a modifier
     * 
     * @param integer $position
     * @return boolean
     */
    public function isModifierPosition($position) {
        if ($this->isNull($position)) {
            return false;
        }
        return $this->dictionary->isModifier($this->words[$position]['word']);
    }
    
    /**
     * Return true if word at position $position is a stop word
     * 
     * @param integer $position
     * @return boolean
     */
    public function isStopWordPosition($position) {
        if ($this->isNull($position)) {
            return false;
        }
        return $this->dictionary->isStopWord($this->words[$position]['word']);
    }
    
    /**
     * Return location keyword
     * 
     * @param string $name
     * @return array
     */
    public function getLocationKeyword($name) {
        return $this->dictionary->getKeyword(RestoDictionary::LOCATION, $name);
    }
    
    /**
     * Return non location keyword
     * 
     * @param string $name
     * @return array
     */
    public function getNonLocationKeyword($name) {
        return $this->dictionary->getKeyword(RestoDictionary::NOLOCATION, $name);
    }
    
    /**
     * Get the last non processed word position of the query before a modifier
     * 
     * @param integer $position
     */
    public function getEndPosition($position) {
        $endPosition = $position;
        for ($i = $position; $i < $this->length; $i++) {
            if ($this->words[$i]['processed'] || $this->isModifierPosition($i)) {
                $endPosition = $i - 1;
                break;
            }
            $endPosition = $i;
        }
        return $endPosition;
    }
   
    /**
     * Add text to error array
     * 
     * @param array $error
     * @param array $words
     */
    public function error($error, $words) {
        $merged = $this->mergeWords($this->slice($words, 0, count($words)));
        if (!empty($merged)) {
            $this->errors[] = array(
                'error' => $error,
                'text' => $this->mergeWords($this->slice($words, 0, count($words)))
            );
        }
    }
    
    /**
     * Add words to not understood array
     * 
     * @param array $words
     */
    public function notUnderstood($words) {
        if (!is_array($words)) {
            $words = array($words);
        }
        $this->error(QueryAnalyzer::NOT_UNDERSTOOD, $words);
    }
    
    /**
     * Set array of words
     * 
     * @param array $words
     */
    public function setWords($words) {
        for ($i = 0, $ii = count($words); $i < $ii; $i++) {
            $this->words[$i] = array(
                'word' => $words[$i],
                'processed' => false
            );
        }
        $this->length = count($this->words);
    }
    
    /**
     * Return true if position is outside of $words array
     * 
     * @param type $position
     */
    private function isNull($position) {
        if ($position < 0 || $position > $this->length - 1) {
            return true;
        }
        return false;
    }
    
    /**
     * Concatenate words into sentence removing noise and stop words
     * 
     * @param array $words
     * @return array
     */
    private function mergeWords($words, $discardStopWords = false) {
        $sentence = '';
        for ($i = 0, $ii = count($words); $i < $ii; $i++) {
            if ($discardStopWords && ($this->dictionary->isStopWord($words[$i]) || $this->dictionary->isNoise($words[$i]))) {
                continue;
            }
            $sentence .= $words[$i] . ' ';
        }
        return trim($sentence);
    }
    
    
}