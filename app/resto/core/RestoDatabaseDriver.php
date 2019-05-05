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

/**
 * RESTo Database
 */
class RestoDatabaseDriver
{

    /*
     * Results per page
     */
    public $resultsPerPage = 20;

    /*
     * Allowed sort columns
     * Note : the first element in array is used for sorting
     */
    public $sortKeys = array('startDate', 'published');

    /*
     * Database handler
     */
    public $dbh;

    /*
     * Database connection configuration
     */
    private $config = array(
        'dbname' => 'resto',
        'host' => 'restodb',
        'port' => 5432,
        'user' => 'resto',
        'password' => 'resto'
    );

    /**
     * Constructor
     *
     * @param array $config
     * @throws Exception
     */
    public function __construct($config)
    {

        if (isset($config)) {

            if (isset($config['resultsPerPage'])) {
                $this->resultsPerPage = $config['resultsPerPage'];
            }
            if (isset($config['sortKeys']) && is_array($config['sortKeys']) && $config['sortKeys'][0] !== '') {
                $this->sortKeys = $config['sortKeys'];
            }
            $this->config = $config;
        }
        else {
            error_log('[WARNING] No database configuration found - use default configuration');
        }
        
    }

    /**
     * Return $sentence in lowercase, without accent and with "'" character
     * replaced by a space
     *
     * This function is superseed in RestoDabaseDriver_PostgreSQL and use
     * the inner function lower(unaccent($sentence)) defined in installDB.sh
     *
     * @param string $sentence
     */
    public function normalize($sentence)
    {
        try {

            $dbh = $this->getConnection();

            if (!isset($sentence) || !$dbh) {
                throw new Exception();
            }
            
            $results = pg_query($this->getConnection(), 'SELECT lower(public.f_unaccent(\'' . pg_escape_string($sentence) . '\')) as normalized');
            if (!$results) {
                throw new Exception();
            }
            $result = pg_fetch_assoc($results);
            return str_replace('\'', ' ', $result['normalized']);
        } catch (Exception $e) {
            return $sentence ?? '';
        }
    }

    /**
     * Close database handler
     */
    public function closeDbh()
    {
        if (isset($this->dbh)) {
            pg_close($this->dbh);
        }
    }

    /**
     * Perform query on database using pg_query
     *
     * @param string $query
     * @param integer $errorCode
     * @param string $errorMessage
     * @return Database result
     * @throws Exception
     */
    public function query($query, $errorCode = 500, $errorMessage = null)
    {
        return $this->pQuery($query, null, $errorCode, $errorMessage);
    }

    /**
     * Perform query on database using pg_query_params
     *
     * @param string $query
     * @param array $params
     * @param integer $errorCode
     * @param string $errorMessage
     * @return Database result
     * @throws Exception
     */
    public function pQuery($query, $params, $errorCode = 500, $errorMessage = null)
    {
        try {
            
            $dbh = $this->getConnection();
            if (!$dbh) {
                throw new Exception('Cannot connect to database ' . ($this->config['dbname'] ?? '???') . '@' . ($this->config['host'] ?? '???') . ':' . ($this->config['port'] ?? '???'), 500);
            }
            
            $results = isset($params) ? pg_query_params($dbh, $query, $params) : pg_query($dbh, $query);
            if (!$results) {
                throw new Exception();
            }
            
            return $results;
        
        } catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode() ?? $errorCode, $e->getMessage() ?? $errorMessage);
        }
    }

    /**
     * Convert database query result into array
     *
     * @param DatabaseResult $results
     * @return array
     */
    public function fetch($results)
    {
        $output = pg_fetch_all($results);
        return $output === false ? array() : $output;
    }

    /**
     * Return PostgreSQL database handler
     */
    public function getConnection()
    {

        // Connection already initialized
        if (isset($this->dbh)) {
            return $this->dbh;
        }
        
        // Get connection
        $this->dbh = $this->getConnectionFromConfig($this->config);
        
        if (!$this->dbh) {
            return RestoLogUtil::httpError(500, 'Cannot connect to database ' . ($this->config['dbname'] ?? '???') . '@' . ($this->config['host'] ?? '???') . ':' . ($this->config['port'] ?? '???'));
        }
       
        return $this->dbh;
    }

    /**
     * Return a connection from configuration
     * 
     * @param array $config
     */
    public function getConnectionFromConfig($config) {
        
        if (! isset($config) || ! isset($config['dbname'])) {
            return false;
        }
        
        try {

            $dbInfo = array(
                'dbname=' . $config['dbname'],
                'user=' . $config['user'],
                'password=' . $config['password']
            );

            /*
             * If host is specified, then TCP/IP connection is used
             * Otherwise socket connection is used
             */
            if (isset($config['host'])) {
                $dbInfo[] = 'host=' . $config['host'];
                $dbInfo[] = 'port=' . ($config['port'] ?? '5432');
            }
            
            return @pg_connect(join(' ', $dbInfo));
            
        } catch (Exception $e) {
            return false;
        }
       
    }
}
