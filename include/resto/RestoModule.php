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

abstract class RestoModule{
    
    /*
     * Resto context
     */
    protected $context;
    
    /*
     * Resto user
     */
    protected $user;
    
    /*
     * Modules options
     */
    protected $options;
    
    /*
     * Translations array
     *  array(
     *      'en' => array(
     *          'key' => 'translation',
     *          ...
     *      ),
     *      'fr' => array(
     *          ...
     *      )
     *      ...
     *  )
     */
    protected $translations = array();
    
    /**
     * Constructor
     * 
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user) {
        $this->context = $context;
        $this->user = $user;
        $this->options = $this->context->modules[get_class($this)];
        if (isset($this->context)) {
            if (isset($this->translations) && isset($this->translations[$this->context->dictionary->language])) {
                $this->context->dictionary->addTranslations($this->translations[$this->context->dictionary->language]);
            }
        }
    }
    
    /**
     * Set the database handler from config.php
     * 
     * @param array $config
     * @throws Exception
     */
    protected function getDatabaseHandler() {
    
        /*
         * Set database handler from configuration
         */
        if (isset($this->options['database'])) {
            $dbh = $this->context->dbDriver->get(RestoDatabaseDriver::HANDLER, $this->options['database']);
        }

        /*
         * Get default database handler 
         */
        if (!isset($dbh)) {
            $dbh = $this->context->dbDriver->dbh;
        }
        
        return $dbh;
    }
    
    /**
     * Run module - this function should be called by Resto.php
     * 
     * @param array $params : input parameters
     * @return string : result from run process in the $context->outputFormat
     */
    abstract public function run($params);

}

