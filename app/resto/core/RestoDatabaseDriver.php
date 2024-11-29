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
     * Default DATABASE_COMMON_SCHEMA
     */
    public $commonSchema = 'resto';

    /*
     * Default DATABASE_TARGET_SCHEMA
     */
    public $targetSchema = 'resto';

    /*
     * Use geometry_part joined table for geometrical intersection
     */
    public $useGeometryPart = false;

    /*
     * Results per page
     */
    public $resultsPerPage = 20;

    /*
     * Allowed sort columns
     * Note : the first element in array is used for sorting
     */
    public $sortKeys = array('startDate', 'created');

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
            if (isset($config['commonSchema'])) {
                $this->commonSchema = $config['commonSchema'];
            }

            if (isset($config['targetSchema'])) {
                $this->targetSchema = $config['targetSchema'];
            }

            if (isset($config['useGeometryPart'])) {
                $this->useGeometryPart = $config['useGeometryPart'];
            }

            if (isset($config['resultsPerPage'])) {
                $this->resultsPerPage = $config['resultsPerPage'];
            }

            if (isset($config['sortKeys']) && is_array($config['sortKeys']) && count($config['sortKeys']) > 0) {
                $this->sortKeys = $config['sortKeys'];
            }

            $this->config = $config;
        } else {
            error_log('[WARNING] No database configuration found - use default configuration');
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
     * @return PgSql\Result result
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
     * @return PgSql\Result result
     * @throws Exception
     */
    public function pQuery($query, $params, $errorCode = 500, $errorMessage = null)
    {
        try {
            $dbh = $this->getConnection();
            
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
     * @param PgSql\Result $results
     * @return array
     */
    public function fetch($results)
    {
        $output = pg_fetch_all($results);
        return $output === false ? array() : $output;
    }

    /**
     * Wrapper of pg_escape_string
     *
     * @param string $str
     * @return string
     */
    public function escape_string($str)
    {
        try {
            return pg_escape_string($this->getConnection(), $str);
        }
        catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Wrapper of pg_query_params
     *
     * @param string $str
     * @param array $params
     * @return PgSql\Result|false
     */
    public function query_params($str, $params)
    {
        try {
            return pg_query_params($this->getConnection(), $str, $params);
        }
        catch (Exception $e) {
            RestoLogUtil::httpError($e->getCode(), $e->getMessage());
        }
    }

    /**
     * Return PostgreSQL database handler
     */
    public function getConnection()
    {
        // Connection already initialized
        if ( !empty($this->dbh) )  {
            return $this->dbh;
        }
        
        // Get connection
        $this->dbh = $this->getConnectionFromConfig($this->config);
        
        if ( empty($this->dbh)  ) {
            throw new Exception('Cannot connect to database ' . ($this->config['dbname'] ?? '???') . '@' . ($this->config['host'] ?? '???') . ':' . ($this->config['port'] ?? '???'), 500);
        }
       
        return $this->dbh;
    }

    /**
     * Return a connection from configuration
     *
     * @param array $config
     */
    public function getConnectionFromConfig($config)
    {
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
