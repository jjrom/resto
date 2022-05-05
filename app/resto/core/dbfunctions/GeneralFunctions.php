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
 * RESTo PostgreSQL general functions
 */
class GeneralFunctions
{
    private $dbDriver = null;

    /**
     * Constructor
     *
     * @param RestoDatabaseDriver $dbDriver
     * @throws Exception
     */
    public function __construct($dbDriver)
    {
        $this->dbDriver = $dbDriver;
    }

    /**
     * Check if a table exsist in database
     *
     * @param string $schemaName
     * @param string $tableName
     * @return boolean
     * @throws Exception
     */
    public function tableExists($schemaName, $tableName)
    {
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT 1 FROM information_schema.tables WHERE table_schema=$1 AND table_name=$2', array(
            $schemaName,
            $tableName
        )));
        return !empty($results);
    }

    /**
     *
     * Return keywords from database
     *
     * @param string $language : ISO A2 language code
     *
     * @return array
     * @throws Exception
     */
    public function getKeywords($language = 'en', $types = array())
    {
        $keywords = array();
        $results = $this->dbDriver->query('SELECT name, normalize(name) as normalized, type, value, location FROM ' . $this->dbDriver->schema . '.keyword WHERE ' . 'lang IN(\'' . pg_escape_string($language) . '\', \'**\')' . (count($types) > 0 ? ' AND type IN(' . join(',', $types) . ')' : ''));
        while ($result = pg_fetch_assoc($results)) {
            if (!isset($keywords[$result['type']])) {
                $keywords[$result['type']] = array();
            }
            $keywords[$result['type']][$result['normalized']] = array(
                'name' => $result['name'],
                'value' => $result['value']
            );
            if (isset($result['location'])) {
                list($isoa2, $bbox) = explode(Resto::TAG_SEPARATOR, $result['location']);
                $keywords[$result['type']][$result['normalized']]['bbox'] = $bbox;
                $keywords[$result['type']][$result['normalized']]['isoa2'] = $isoa2;
            }
        }

        return array('keywords' => $keywords);
    }


    /**
     * Returns shared link initiator email if resource is shared (checked with proof)
     * Returns false otherwise
     *
     * @param string $resourceUrl
     * @param string $token
     * @return boolean
     */
    public function getSharedLinkInitiator($resourceUrl, $token)
    {
        if (!isset($resourceUrl) || !isset($token)) {
            return false;
        }
        $results = $this->dbDriver->fetch($this->dbDriver->pQuery('SELECT userid FROM ' . $this->dbDriver->schema . '.sharedlink WHERE url=$1 AND token=$2 AND validity > now()', array($resourceUrl, $token)));
        return !empty($results) ? $results[0]['userid'] : false;
    }

    /**
     * Create a shared resource and return it
     *
     * @param string $userid
     * @param string $resourceUrl
     * @param integer $duration
     * @return array
     */
    public function createSharedLink($userid, $resourceUrl, $duration = 86400)
    {
        if (!isset($resourceUrl) || !RestoUtil::isUrl($resourceUrl)) {
            return null;
        }
        if (!is_int($duration)) {
            $duration = 86400;
        }
        $results = $this->dbDriver->fetch($this->dbDriver->query('INSERT INTO ' . $this->dbDriver->schema . '.sharedlink (url, token, userid, validity) VALUES (\'' . pg_escape_string($resourceUrl) . '\',\'' . (RestoUtil::encrypt(mt_rand(0, 100000) . microtime())) . '\',' . pg_escape_string($userid) . ',now() + ' . $duration . ' * \'1 second\'::interval) RETURNING token', 500, 'Cannot share link'));
        if (count($results) === 1) {
            return array(
                'resourceUrl' => $resourceUrl,
                'token' => $results[0]['token']
            );
        }

        return null;
    }

    /**
     * Save query to database
     *
     * @param string $userid
     * @param array $query
     * @throws Exception
     */
    public function storeQuery($userid, $query)
    {
        return $this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->schema . '.log (userid,method,path,query,ip,querytime) VALUES ($1,$2,$3,$4,$5,now())', array(
            $userid ?? null,
            $query['method'] ?? null,
            $query['path'] ?? null,
            $query['query'] ?? null,
            $this->getIp() ?? '127.0.0.1'
        ));
    }

    /**
     * Return true if token is revoked
     *
     * @param string $token
     */
    public function isTokenRevoked($token)
    {
        return !empty($this->dbDriver->fetch($this->dbDriver->pQuery('SELECT 1 FROM ' . $this->dbDriver->schema . '.revokedtoken WHERE token=$1', array($token))));
    }

    /**
     * Revoke token
     *
     * @param string $token
     * @param string $validuntil
     */
    public function revokeToken($token, $validuntil)
    {
        if (isset($token) && !$this->isTokenRevoked($token)) {
            $this->dbDriver->pQuery('INSERT INTO ' . $this->dbDriver->schema . '.revokedtoken (token, validuntil) VALUES($1, $2)', array(
                $token,
                $validuntil ?? null
            ));
        }
        return true;
    }

    /**
     * Return area of input EPSG:4326 WKT
     *
     * @param string $wkt
     * @param string $unit
     */
    public function getArea($wkt, $unit = 'deg')
    {
        // Compute area for surfaces only
        if (strrpos($wkt, 'POLYGON') === false) {
            return 0;
        }

        $result = $this->dbDriver->pQuery('SELECT st_area(' . ($unit === 'deg' ? 'st_geometryFromText($1, 4326)' : 'geography(st_geometryFromText($1, 4326)), false') . ') as area', array($wkt));

        while ($row = pg_fetch_assoc($result)) {
            return (integer) $row['area'];
        }
        return 0;
    }

    /**
     * Return topology analysis
     *
     * @param array $geometry
     * @param array $params
     */
    public function getTopologyAnalysis($geometry, $params)
    {
        $result = null;

        /*
         * Null geometry is allowed in GeoJSON
         */
        if (!isset($geometry)  || !is_array($geometry) || !isset($geometry['type']) || !isset($geometry['coordinates'])) {
            return array(
                'isValid' => true,
                'error' => 'Empty geometry'
            );
        }

        /*
         * Convert to EPSG:4326 if input SRID differs from this projection
         */
        $epsgCode = RestoGeometryUtil::geoJSONGeometryToSRID($geometry);
        $geoJsonParser = 'ST_SetSRID(ST_GeomFromGeoJSON($1), 4326)';
        if ($epsgCode !== "4326") {
            $geoJsonParser = 'ST_Transform(ST_SetSRID(ST_GeomFromGeoJSON($1), ' . $epsgCode . '), 4326)';
        }
        
        try {
            $result = pg_fetch_row(pg_query_params($this->dbDriver->getConnection(), 'WITH tmp AS (SELECT ST_Force2D(' . $geoJsonParser . ') AS geom, ST_Force2D(ST_SetSRID(' . $this->getSplitterFunction($geoJsonParser, $params) . ', 4326)) AS _geom) SELECT geom, _geom, ST_Force2D(ST_SetSRID(ST_Centroid(_geom), 4326)) AS centroid, Box2D(ST_SetSRID(_geom, 4326)) as bbox FROM tmp', array(
                json_encode(array(
                    'type' => $geometry['type'],
                    'coordinates' => $geometry['coordinates']
                ), JSON_UNESCAPED_SLASHES)
            )), 0, PGSQL_ASSOC);

        } catch (Exception $e) {
            $error = '[GEOMETRY] ' . pg_last_error($this->dbDriver->getConnection());
        }
        
        if (! $result) {
            return array(
                'isValid' => false,
                'error' => $error ?? 'Invalid geometry'
            );
        }
        
        return array(
            'isValid' => true,
            'bbox' => RestoGeometryUtil::box2dTobbox($result['bbox']),
            'geometry' => $result['geom'] === $result['_geom'] ? null : $result['geom'],
            'geom' => $result['_geom'],
            'centroid' => $result['centroid']
        );
    }

    /**
     * Get calling IP
     *
     * @return string
     */
    private function getIp()
    {

        // Try all IPs - the latest, the better
        $best = null;
        foreach (array(
            'REMOTE_ADDR',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_X_FORWARDED_FOR'
        ) as $ip) {
            if (filter_input(INPUT_SERVER, $ip, FILTER_UNSAFE_RAW) !== false && !is_null(filter_input(INPUT_SERVER, $ip, FILTER_UNSAFE_RAW))) {
                $best = filter_input(INPUT_SERVER, $ip, FILTER_UNSAFE_RAW);
            }
        }
        
        return $best;
    }

    /**
     * Return Split function
     * 
     * @param string $geom
     * @param array $params
     */
    private function getSplitterFunction($geom, $params) {

        if (!isset($params['tolerance'])) {
            return 'ST_SplitDateLine(' . $geom . ')';
        }
        
        return  'ST_SimplifyPreserveTopologyWhenTooBig(ST_SplitDateLine(' . $geom . '),' . $params['tolerance'] . (isset($params['maxpoints']) ? ',' . $params['maxpoints'] : '') . ')';

    }

}
