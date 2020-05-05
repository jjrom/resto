<?php
/*
 * Copyright 2018 Jérôme Gasperi
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

class RestoAddOn
{

    /**
     * Add-on version
     */
    public $version;

    /*
     * Resto context
     */
    protected $context;

    /*
     * Resto user
     */
    protected $user;

    /*
     * Add-on options
     */
    protected $options;

    /*
     * If set, options array is initialize from "$optionsFrom" add-on
     */
    protected $optionsFrom = null;

    /**
     * Constructor
     *
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user)
    {
        $this->context = $context;
        $this->user = $user;
        $this->options = $this->context->addons[$this->optionsFrom ?? get_class($this)]['options'] ?? array();
    }

    /**
     * Set the database handler from config.php
     *
     * @param array $dbConfig
     * @throws Exception
     */
    protected function getConnection($dbConfig)
    {
        return isset($dbConfig) ? $this->context->dbDriver->getConnectionFromConfig($dbConfig) : $this->context->dbDriver->getConnection();
    }
    
}
