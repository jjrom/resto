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
     * Resto core routes
     */
    const ROUTE_TO_API = '/api';
    const ROUTE_TO_ASSETS = '/assets';
    const ROUTE_TO_AUTH = '/auth';
    const ROUTE_TO_CATALOGS = '/catalogs';
    const ROUTE_TO_COLLECTIONS = '/collections';
    const ROUTE_TO_COLLECTION = RestoRouter::ROUTE_TO_COLLECTIONS . '/{collectionId}';
    const ROUTE_TO_CONFORMANCE = '/conformance';
    const ROUTE_TO_FEATURES = RestoRouter::ROUTE_TO_COLLECTION . '/items';
    const ROUTE_TO_FEATURE = RestoRouter::ROUTE_TO_FEATURES . '/{featureId}';
    const ROUTE_TO_FORGOT_PASSWORD = '/services/password/forgot';
    const ROUTE_TO_OSDD = '/services/osdd';
    const ROUTE_TO_RESET_PASSWORD = '/services/password/reset';
    const ROUTE_TO_SEND_ACTIVATION_LINK = '/services/activation/send';
    const ROUTE_TO_STAC_CHILDREN = '/children';
    const ROUTE_TO_STAC_QUERYABLES = '/queryables';
    const ROUTE_TO_STAC_SEARCH = '/search';
    const ROUTE_TO_USERS = '/users';
    const ROUTE_TO_USER = RestoRouter::ROUTE_TO_USERS . '/{userid}';
    const ROUTE_TO_USER_RIGHTS = RestoRouter::ROUTE_TO_USERS . '/rights';
    
    /*
     * Default routes
     */
    private $defaultRoutes = array(

        // Landing page and conformance (see WFS 3.0)
        array('GET',    '/', false, 'ServicesAPI::hello'),                                                                                  // Landing page
        array('GET',    RestoRouter::ROUTE_TO_API, false, 'ServicesAPI::api'),                                                                                 // API page
        array('GET',    RestoRouter::ROUTE_TO_CONFORMANCE, false, 'ServicesAPI::conformance'),                                                                 // Conformance page
        
        // API for users
        array('GET',    RestoRouter::ROUTE_TO_USERS, true, 'UsersAPI::getUsersProfiles'),                                                                      // List users profiles
        array('POST',   RestoRouter::ROUTE_TO_USERS, false, 'UsersAPI::createUser'),                                                                           // Create user
        array('GET',    RestoRouter::ROUTE_TO_USER, true, 'UsersAPI::getUserProfile'),                                                               // Show user profile
        array('PUT',    RestoRouter::ROUTE_TO_USER, true, 'UsersAPI::updateUserProfile'),                                                            // Update :userid profile
        array('GET',    RestoRouter::ROUTE_TO_USER . '/logs', true, 'UsersAPI::getUserLogs'),                                                             // Show user logs
        array('GET',    RestoRouter::ROUTE_TO_USER_RIGHTS, true, 'UsersAPI::getUserRights'),                                                         // Show user rights
        array('GET',    RestoRouter::ROUTE_TO_USER_RIGHTS . '/{collectionId}', true, 'UsersAPI::getUserRights'),                                          // Show user rights for :collectionId
        array('GET',    RestoRouter::ROUTE_TO_USER_RIGHTS . '/{collectionId}/{featureId}', true, 'UsersAPI::getUserRights'),                              // Show user rights for :featureId
        
        // API for collections
        array('GET',    RestoRouter::ROUTE_TO_COLLECTIONS, false, 'CollectionsAPI::getCollections'),                                                           // List all collections
        array('POST',   RestoRouter::ROUTE_TO_COLLECTIONS, true, 'CollectionsAPI::createCollection'),                                                          // Create collection
        array('GET',    RestoRouter::ROUTE_TO_COLLECTION, false, 'CollectionsAPI::getCollection'),                                             // Get :collectionId description
        array('PUT',    RestoRouter::ROUTE_TO_COLLECTION, true, 'CollectionsAPI::updateCollection'),                                           // Update :collectionId
        array('DELETE', RestoRouter::ROUTE_TO_COLLECTION, true, 'CollectionsAPI::deleteCollection'),                                           // Delete :collectionId
        array('GET',    RestoRouter::ROUTE_TO_COLLECTION . '/queryables', false, 'STAC::getQueryables'),                                            // OAFeature API - Queryables

        // API for features
        array('GET',    RestoRouter::ROUTE_TO_FEATURES, false, 'FeaturesAPI::getFeaturesInCollection'),                                // Search features in :collectionId
        array('POST',   RestoRouter::ROUTE_TO_FEATURES, array('auth' => true, 'upload' => 'files'), 'CollectionsAPI::insertFeatures'), // Insert feature(s)
        array('GET',    RestoRouter::ROUTE_TO_FEATURE, false, 'FeaturesAPI::getFeature'),                                 // Get feature :featureId
        array('PUT',    RestoRouter::ROUTE_TO_FEATURE, true, 'FeaturesAPI::updateFeature'),                               // Update feature :featureId
        array('DELETE', RestoRouter::ROUTE_TO_FEATURE, true, 'FeaturesAPI::deleteFeature'),                               // Delete :featureId
        array('PUT',    RestoRouter::ROUTE_TO_FEATURE . '/properties/{property}', true, 'FeaturesAPI::updateFeatureProperty'), // Update feature :featureId single property
        
        // API for authentication (token based)
        array('GET',    RestoRouter::ROUTE_TO_AUTH, true, 'AuthAPI::getToken'),                                                                                // Return a valid auth token
        array('GET',    RestoRouter::ROUTE_TO_AUTH . '/check/{token}', false, 'AuthAPI::checkToken'),                                                               // Check auth token validity
        array('DELETE', RestoRouter::ROUTE_TO_AUTH . '/revoke/{token}', true, 'AuthAPI::revokeToken'),                                                              // Revoke auth token
        array('PUT',    RestoRouter::ROUTE_TO_AUTH . '/activate/{token}', false, 'AuthAPI::activateUser'),                                                          // Activate owner of the token

        // API for services
        array('GET',    RestoRouter::ROUTE_TO_OSDD, false, 'ServicesAPI::getOSDD'),                                                                   // Opensearch service description at collections level
        array('GET',    RestoRouter::ROUTE_TO_OSDD . '/{collectionId}', false, 'ServicesAPI::getOSDDForCollection'),                                       // Opensearch service description for products on {collection}
        array('POST',   RestoRouter::ROUTE_TO_SEND_ACTIVATION_LINK, false, 'ServicesAPI::sendActivationLink'),                                             // Send activation link
        array('POST',   RestoRouter::ROUTE_TO_FORGOT_PASSWORD, 'ServicesAPI::forgotPassword'),                                                 // Send reset password link
        array('POST',   RestoRouter::ROUTE_TO_RESET_PASSWORD, false, 'ServicesAPI::resetPassword'),                                                   // Reset password

        // STAC
        array('GET',    RestoRouter::ROUTE_TO_ASSETS . '/{urlInBase64}', false, 'STAC::getAsset'),                                                                  // Get an asset using HTTP 301 permanent redirect
        array('GET',    RestoRouter::ROUTE_TO_CATALOGS . '/*', false, 'STAC::getCatalogs'),                                                                         // Get catalogs
        array('GET',    RestoRouter::ROUTE_TO_STAC_CHILDREN, false, 'STAC::getChildren'),                                                                           // STAC API - Children
        array('GET',    RestoRouter::ROUTE_TO_STAC_QUERYABLES, false, 'STAC::getQueryables'),                                                                       // STAC/OAFeature API - Queryables
        array('GET',    RestoRouter::ROUTE_TO_STAC_SEARCH, false, 'STAC::search')                                                                                   // STAC API - core search
    );

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

        /*
         * Add default routes
         */
        $this->addRoutes(isset($this->context->core['defaultRoutes']) && count($this->context->core['defaultRoutes']) > 0 ? $this->context->core['defaultRoutes'] : $this->defaultRoutes);

        /*
         * Add add-ons routes
         */
        foreach (array_keys($this->context->addons) as $addonName) {
            if (isset($this->context->addons[$addonName]['routes'])) {
                $this->addRoutes($this->context->addons[$addonName]['routes']);
            }
        }
    }

    /**
     * Add a route to routes list
     *
     * @param array $route // Mandatory format is (HTTP Verb, path, need authentication ?, Class name::Method name)
     *                     // e.g. ('GET', '/collections/{collectionId}', false, 'RestoRouteGet::getFeatures')
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
     *      /collections/{collectionId}, myFunction::myClass
     *
     * Will call function myFunction($params) from class myClass with $params = array('collectionId' => // The value of collectionId in path)
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
                    // Break only if the method is valid - otherwise continue
                    if (isset($validRoute) && isset($validRoute[1][$method])) {
                        break;
                    }
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
        if (!isset($validRoute)) {
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
         * In resto 5.x first element of route is an "authenticationIsRequired" boolean
         * In restto >=6.x first element of route can also be an array
         */
        if (is_bool($validRoute[0])) {
            $validRoute[0] = array(
                'auth' => $validRoute[0]
            );
        }

        /*
         * Authentication is required
         */
        if (isset($validRoute[0]['auth']) && $validRoute[0]['auth'] && !isset($this->user->profile['id'])) {
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
            /*
             * File upload is allowed - upload files and populate data with file paths...
             * In this case, the target $className->$methodName is responsible of the uploaded files
             */
            if (isset($validRoute[0]['upload']) && isset($_FILES[$validRoute[0]['upload']]) && is_array($_FILES[$validRoute[0]['upload']])) {
                $data = $this->uploadFiles($_FILES[$validRoute[0]['upload']]);
            }
            /*
             * ...or read the input body content and directly populate data with it
             */
            else {
                $data = $this->readStream();
            }
        }
        
        return (new $className($this->context, $this->user))->$methodName(
            $params,
            $data
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
     * Upload files locally and return array of file paths
     *
     * @param array $files
     * @return array
     * @throws Exception
     */
    private function uploadFiles($files)
    {
        $filePaths = [];

        // All files will be uploaded within a dedicated directory with a random name
        $uploadDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . (substr(sha1(mt_rand(0, 100000) . microtime()), 0, 15));

        try {
            for ($i = count($files['tmp_name']); $i--;) {
                $fileToUpload = $files['tmp_name'][$i];

                if (is_uploaded_file($fileToUpload)) {
                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0777, true);
                    }

                    $fileName = $uploadDirectory . DIRECTORY_SEPARATOR . $files['name'][$i];
                    move_uploaded_file($fileToUpload, $fileName);

                    $filePaths[] = $fileName;
                }
            }
        } catch (Exception $e) {
            RestoLogUtil::httpError(500, 'Cannot upload file(s)');
        }

        return array(
            'uploadDir' => $uploadDirectory,
            'files' => $filePaths
        );
    }
}
