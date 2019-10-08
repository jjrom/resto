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
 * resto REST router
 */
class RestoRouter
{

    /*
     * RestoContext
     */
    private $context;

    /*
     * RestoUser
     */
    private $user;

    /*
     * Temporary upload directory for POST/PUT input data
     */
    private $uploadDirectory = '/tmp/resto_uploads';

    /*
     * Registered routes sorted by method
     */
    private $routes = array();

    /**
     * Constructor
     */
    public function __construct($context, $user)
    {
        $this->context = $context;
        $this->user = $user;
    }

    /**
     * Add a route to routes list
     *
     * @param array $route // Mandatory format is (HTTP Verb, path, need authentication ?, Class name::Method name)
     *                     // e.g. ('GET', '/collections/{collectionName}', false, 'RestoRouteGet::getFeatures')
     */
    public function addRoute($route)
    {
        if (!is_array($route) || count($route) !== 4) {
            return false;
        }
        if (!isset($this->routes[$route[1]])) {
            $this->routes[$route[1]] = array();
        }
        
        $this->routes[$route[1]][$route[0]] = array($route[2], $route[3]);
        
        return true;
        
    }

    /**
     * Add routes to routes list
     *
     * @param array $routes // array of route
     */
    public function addRoutes($routes)
    {
        if (!is_array($routes)) {
            return false;
        }
        for ($i = 0, $ii = count($routes); $i < $ii; $i++) {
            $this->addRoute($routes[$i]);
        }
        return true;
    }

    /**
     * Process route
     *
     * Route structure is :
     *  array('path', 'isAuthenticated', 'className::methodName')
     * 
     * Some path examples:
     * 
     *      /collections/{collectionName}, myFunction::myClass
     * 
     * Will call function myFunction($params) from class myClass with $params = array('collectionName' => // The value of collectionName in path)
     * 
     *      /anyroute/isvalidafter/*, myFunction::myClass
     * 
     * Will call function function myFunction($params) from class myClass with $params = array('segments' => array('a', 'b', 'c', etc.))
     * Where (a, b, c, etc.) are the values of everything after '/anyroute/isvalidafter/' splitted by '/' character
     *
     * @param string $method // One of GET, POST, PUT or DELETE
     * @param string $path // url to process
     * @param array $query // queryParams array
     */
    public function process($method, $path, $query)
    {
        $segments = explode('/', $path);
        $workingRoutes = $this->getWorkingRoutes(count($segments));
        $nbOfRoutes = count($workingRoutes);
        $workIndex = 0;
        $params = array();
        $validRoute = null;
        
        // Look through route segments
        while ($workIndex < $nbOfRoutes) {
            $routeSegments = explode('/', $workingRoutes[$workIndex][0]);

            // Compare segments one by one to match a valid route
            for ($i = 1, $ii = count($segments); $i < $ii; $i++) {

                // Segments match - keep the path and continue to match against following segments
                if ($routeSegments[$i] === $segments[$i]) {
                    $validRoute = $workingRoutes[$workIndex];
                    continue;
                }

                // Non constraint route case i.e. '*'
                if ($routeSegments[$i] === '*') {
                    $params = array(
                        'segments' => array_slice($segments, $i)
                    );
                    break;
                }

                // Parameter case - add an entry to params and jump to next segment
                if (substr($routeSegments[$i], 0, 1) === '{') {
                    $validRoute = $workingRoutes[$workIndex];
                    $params[substr($routeSegments[$i], 1, strlen($routeSegments[$i]) - 2)] = RestoUtil::sanitize($segments[$i]);
                    continue;
                }

                // If we reach this point, reset everything
                $validRoute = null;
                $params = array();
                break;
            }

            // Iterate if nothing matched
            if (isset($validRoute)) {
                break;
            }
            $workIndex++;
        }

        // No route found
        if ( !isset($validRoute) ) {
            RestoLogUtil::httpError(404);
        }

        return isset($validRoute[1][$method]) ? $this->instantiateRoute($validRoute[1][$method], $method, array_merge($query, $params)) : RestoLogUtil::httpError(405);

    }

    /**
     * Instantiate a valid route
     *
     * @param array $validRoute
     * @param string $method
     * @param array $params
     */
    private function instantiateRoute($validRoute, $method, $params)
    {

        /*
         * Authentication is required
         */
        if ($validRoute[0] && !isset($this->user->profile['id'])) {
            return RestoLogUtil::httpError(401);
        }

        /*
         * Instantiates route class and calls method
         */
        list($className, $methodName) = explode('::', $validRoute[1]);

        /*
         * Read input data
         */
        $data = null;
        if ($method === 'POST' || $method === 'PUT') {
            $data = count($_FILES) === 0 ? $this->readStream() : $this->readFile();
        }
        
        return (new $className($this->context, $this->user))->$methodName(
            $params,
            $data ?? null
        );

    }

    /**
     * Return an array of potentially valid routes i.e.
     *  - routes with exactly the same number of segments as input path
     *  - routes with last segment equal to '*'
     *
     * @param integer $length Number of segments in input path
     */
    private function getWorkingRoutes($length)
    {
        $workingRoutes = array();
        foreach ($this->routes as $key => $value) {
            $segments = explode('/', $key);
            $count = count($segments);
            if ($count === $length || ($segments[$count - 1] === '*' && $count < $length)) {
                $workingRoutes[] = array($key, $value);
            }
        }

        return $workingRoutes;
    }

    /**
     * Read file content within header body of POST request
     *
     * @return array
     * @throws Exception
     */
    private static function readStream()
    {
        $content = file_get_contents('php://input');
        if (!isset($content)) {
            return null;
        }

        /*
         * Assume that input data format is JSON by default
         */
        $json = json_decode($content, true);

        return $json === null ? explode("\n", $content) : $json;
    }

    /**
     * Read file content attached in POST request
     *
     * @return array
     * @throws Exception
     */
    private function readFile()
    {
        if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
            RestoLogUtil::httpError(500, 'Cannot upload file(s)');
        }
        try {
            $fileToUpload = is_array($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'][0] : $_FILES['file']['tmp_name'];
            if (is_uploaded_file($fileToUpload)) {
                if (!is_dir($this->uploadDirectory)) {
                    mkdir($this->uploadDirectory);
                }
                $fileName = $this->uploadDirectory . DIRECTORY_SEPARATOR . (substr(sha1(mt_rand(0, 100000) . microtime()), 0, 15));
                move_uploaded_file($fileToUpload, $fileName);
                $lines = file($fileName);
                // Delete after read
                unlink($fileName);
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot upload file(s)');
        }

        /*
         * Assume that input data format is JSON by default
         */
        $json = json_decode(join('', $lines), true);

        return $json === null ? $lines : $json;
    }

}
