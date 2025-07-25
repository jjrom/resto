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
 * Services API
 */
class ServicesAPI
{
    private $context;
    private $user;

    private $title = 'STAC endpoint';
    private $description = 'This is a STAC endpoint powered by http://github.com/jjrom/resto';

    /**
     * Constructor
     */
    public function __construct($context, $user)
    {
        $this->context = $context;
        $this->user = $user;
        $this->title = getenv('STAC_ROOT_TITLE') && !empty(getenv('STAC_ROOT_TITLE')) ? getenv('STAC_ROOT_TITLE') : $this->title;
        $this->description = getenv('STAC_ROOT_DESCRIPTION') && !empty(getenv('STAC_ROOT_DESCRIPTION')) ? getenv('STAC_ROOT_DESCRIPTION') : $this->description;
    }

    /**
     * Return API server definition as OpenAPI 3.0 document
     * (see https://github.com/opengeospatial/ogcapi-features/blob/master/core/standard/17-069.adoc)
     *
     *    @OA\Get(
     *      path="/api.{format}",
     *      summary="OpenAPI definition",
     *      description="Returns the server API definition as an OpenAPI 3.0 JSON document (default) or as an HTML page (if format is specified and set to *html*)",
     *      tags={"Server"},
     *      @OA\Parameter(
     *          name="format",
     *          in="path",
     *          description="Output format - *json* or *html*",
     *          required=true,
     *          @OA\Items(
     *              type="string",
     *              enum={"json", "html"}
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="OpenAPI 3.0 definition"
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Not found"
     *      )
     *    )
     */
    public function api()
    {
        try {
            $content = @file_get_contents('/docs/resto-api.' . $this->context->outputFormat);
        } catch (Exception $e) {
            $content = false;
        }

        if ($content === false) {
            RestoLogUtil::httpError(404);
        }

        if ($this->context->outputFormat === 'json') {
            $this->context->outputFormat = 'openapi+json';
            return json_decode($content, true);
        }
        
        /*
         * Set range and headers
         */
        header('HTTP/1.1 200 OK');
        header('Content-Type: ' . RestoUtil::$contentTypes[$this->context->outputFormat]);
        echo $content;

        return null;
    }

    /**
     * Return conformance page conforms to OGC API Feature
     * (see https://github.com/opengeospatial/ogcapi-features/blob/master/core/standard/17-069.adoc)
     *
     *    @OA\Get(
     *      path="/conformance",
     *      summary="Conformance page",
     *      description="Returns the OGC API Feature conformance description as JSON document",
     *      tags={"Server"},
     *      @OA\Response(
     *          response="200",
     *          description="OGC API Feature conformance definition",
     *          @OA\JsonContent(
     *               @OA\Property(
     *                  property="conformsTo",
     *                  type="array",
     *                  description="Array of conformance specification urls",
     *                  @OA\Items(
     *                      type="string"
     *                  )
     *               )
     *          )
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Not found"
     *      )
     *    )
     */
    public function conformance()
    {
        return array(
            'conformsTo' => $this->conformsTo()
        );
    }

    /**
     * Landing page conforms to OGC API Feature
     * (see https://github.com/opengeospatial/ogcapi-features/blob/master/core/standard/17-069.adoc)
     *
     *    @OA\Get(
     *      path="/",
     *      summary="Landing page",
     *      description="Landing page for the server. Should be used by client to automatically detects endpoints to API, collections, etc.",
     *      tags={"Server"},
     *      @OA\Response(
     *          response="200",
     *          description="Server landing page",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="Server identifier.",
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  description="Server title"
     *              ),
     *              @OA\Property(
     *                  property="description",
     *                  type="string",
     *                  description="Server description"
     *              ),
     *              @OA\Property(
     *                  property="links",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/Link")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Not found"
     *      )
     *    )
     */
    public function hello()
    {
        
        $hello = array(
            'stac_version' => STACAPI::STAC_VERSION,
            'id' => 'root',
            'type' => 'Catalog',
            'title' => $this->title,
            'description' => $this->description,
            'resto:version' => RestoConstants::VERSION,
            'ssys:targets' => array($this->context->core['planet']),
            'links' => array(
                array(
                    'rel' => 'self',
                    'type' => RestoUtil::$contentTypes['json'],
                    'title' => $this->title,
                    'href' => $this->context->core['baseUrl']
                ),
                array(
                    'rel' => 'service-desc',
                    'type' => RestoUtil::$contentTypes['openapi+json'],
                    'title' => 'OpenAPI 3.0 definition endpoint',
                    'href' => !empty($this->context->core['openAPIUrl']) ? $this->context->core['openAPIUrl'] : $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_API,
                ),
                array(
                    'rel' => 'service-doc',
                    'type' => RestoUtil::$contentTypes['html'],
                    'title' => 'OpenAPI 3.0 definition endpoint documentation',
                    'href' => !empty($this->context->core['documentationUrl']) ? $this->context->core['documentationUrl'] : $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_API . '.html'
                ),
                array(
                    'rel' => 'conformance',
                    'type' => RestoUtil::$contentTypes['json'],
                    'title' => 'Conformance declaration',
                    'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_CONFORMANCE
                ),
                array(
                    'rel' => 'children',
                    'type' => RestoUtil::$contentTypes['json'],
                    'title' => 'Children',
                    'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_STAC_CHILDREN
                ),
                array(
                    'rel' => 'http://www.opengis.net/def/rel/ogc/1.0/queryables',
                    'type' => RestoUtil::$contentTypes['jsonschema'],
                    'title' => 'Queryables',
                    'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_STAC_QUERYABLES
                ),
                array(
                    'rel' => 'data',
                    'type' => RestoUtil::$contentTypes['json'],
                    'title' => 'Collections',
                    'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_COLLECTIONS
                ),
                array(
                    'rel' => 'child',
                    'title' => 'Collections',
                    'type' => RestoUtil::$contentTypes['json'],
                    'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_COLLECTIONS,
                    'roles' => array('collections')
                ),
                array(
                    'rel' => 'child',
                    'type' => RestoUtil::$contentTypes['json'],
                    'title' => 'Catalogs',
                    'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_CATALOGS
                ),
                array(
                    'rel' => 'root',
                    'type' => RestoUtil::$contentTypes['json'],
                    'title' => $this->title,
                    'href' => $this->context->core['baseUrl']
                ),
                array(
                    'rel' => 'search',
                    'type' => RestoUtil::$contentTypes['geojson'],
                    'title' => 'STAC search endpoint (GET)',
                    'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_STAC_SEARCH,
                    'method' => 'GET'
                ),
                array(
                    'rel' => 'search',
                    'type' => RestoUtil::$contentTypes['geojson'],
                    'title' => 'STAC search endpoint (POST)',
                    'href' => $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_STAC_SEARCH,
                    'method' => 'POST'
                )   
            ),
            'conformsTo' => $this->conformsTo()
        );

        // Add pinned catalogs
        $catalogsFunctions = new CatalogsFunctions($this->context->dbDriver);
        $catalogs = $catalogsFunctions->getCatalogs(array(
            'where' => 'pinned IS TRUE',
            'countCatalogs' => false,
            'noProperties' => true
        ), false);

        for ($i = 0, $ii = count($catalogs); $i < $ii; $i++) {

            if ( $catalogs[$i]['visibility'] ) {
                if ( !$catalogsFunctions->canSeeCatalog($catalogs[$i]['visibility'], $this->user) ) {
                    continue;
                }
            }
            
            $link = array(
                'id' => $catalogs[$i]['id'],
                'rel' => 'child',
                'type' => RestoUtil::$contentTypes['json'],
                'href' => $this->context->core['baseUrl'] . ( str_starts_with($catalogs[$i]['id'], 'collections/') ? '/' : '/catalogs/') . join('/', array_map('rawurlencode', explode('/', $catalogs[$i]['id'])))
            );
            if ( $catalogs[$i]['counters']['total'] > 0 ) {
                $link['matched'] = $catalogs[$i]['counters']['total'];
            }
            if ( isset($catalogs[$i]['title']) ) {
                $link['title'] = $catalogs[$i]['title'];
            }
            if ( isset($catalogs[$i]['description']) ) {
                $link['description'] = $catalogs[$i]['description'];
            }
            if ( isset($catalogs[$i]['rtype']) ) {
                $link['resto:type'] = $catalogs[$i]['rtype'];
            }
            $hello['links'][] = $link;
            
        }

        return $this->context->core['useJSONLD'] ? JSONLDUtil::addDataCatalogMetadata($hello) : $hello;
    }

    /**
     * Send a reset password link to user
     *
     *  @SWG\Get(
     *      tags={"User"},
     *      path="/services/resetPassword?email={email}",
     *      summary="Send reset password link",
     *      description="Send reset password link to the user email adress",
     *      operationId="resetPassword",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="email",
     *          in="query",
     *          style="form",
     *          description="Email",
     *          required=true,
     *          type="string",
     *          @SWG\Items(type="string")
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="Acknowledgment of email notification"
     *      ),
     *      @SWG\Response(
     *          response="400",
     *          description="Bad request"
     *      )
     *  )
     */
    public function forgotPassword($params, $body)
    {
        if (isset($body['email'])) {
            $user = new RestoUser(array('email' => strtolower($body['email'])), $this->context);
        }
        if (!isset($user)) {
            RestoLogUtil::httpError(400, 'Missing or invalid email');
        }
        return $user->sendResetPasswordLink();
    }

    /**
     * Send an activation  link to user
     *
     *  @SWG\Post(
     *      tags={"User"},
     *      path="/services/activation/send",
     *      summary="Send activation link to user",
     *      description="Send activation link to the user email adress",
     *      operationId="resetPassword",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="email",
     *          in="query",
     *          style="form",
     *          description="Email",
     *          required=true,
     *          type="string",
     *          @SWG\Items(type="string")
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="Acknowledgment of email notification"
     *      ),
     *      @SWG\Response(
     *          response="400",
     *          description="Bad request"
     *      )
     *  )
     */
    public function sendActivationLink($params, $body)
    {
        if (isset($body['email'])) {
            if (! filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
                RestoLogUtil::httpError(400, 'Email address is invalid');
            }

            $user = new RestoUser(array('email' => strtolower($body['email'])), $this->context);
        }
        
        if (!isset($user)) {
            RestoLogUtil::httpError(400, 'Missing or invalid email');
        }

        // Send activation link
        if (isset($user->profile['username']) && $user->profile['activated'] === 0) {
            if (!((new RestoNotifier($this->context->servicesInfos, $this->context->lang))->sendMailForUserActivation($body['email'], $this->context->core['sendmail'], array(
                'token' => $this->context->createJWT($user->profile['username'], $this->context->core['tokenDuration'])
            )))) {
                RestoLogUtil::httpError(500, 'Cannot send activation link');
            }
        } else {
            RestoLogUtil::httpError(400, 'User does not exist or is already activated');
        }

        return RestoLogUtil::success('Activation link sent');
    }

    /**
     *  Reset password
     *
     *  @SWG\Post(
     *      tags={"User"},
     *      path="/services/password/reset",
     *      summary="Reset password",
     *      description="Replace existing password by provided password",
     *      operationId="resetPassword",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="token",
     *          in="query",
     *          style="form",
     *          description="Security token",
     *          required=true,
     *          type="string",
     *          @SWG\Items(type="string")
     *      ),
     *      @SWG\Parameter(
     *          name="password",
     *          in="query",
     *          style="form",
     *          description="Password",
     *          required=true,
     *          type="string",
     *          @SWG\Items(type="string")
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="Acknowledgement that the password changed"
     *      ),
     *      @SWG\Response(
     *          response="400",
     *          description="Bad request"
     *      ),
     *      @SWG\Response(
     *          response="403",
     *          description="Forbidden"
     *      )
     *  )
     */
    public function resetPassword($params, $body)
    {
        if (isset($body['password']) && isset($body['token'])) {
            $userid = (new UsersFunctions($this->context->dbDriver))->updateUserPassword(
                array(
                    'token' => $body['token'],
                    'password' => $body['password']
                )
            );
            if (isset($userid)) {
                return RestoLogUtil::success('Password updated for user ' . $userid);
            }
        }
        RestoLogUtil::httpError(404);
    }

    /**
     * Return the conformance links both for STAC and OGC API
     */
    private function conformsTo()
    {
        return array_merge(STACAPI::CONFORMANCE_CLASSES, array_map(fn($str) => 'https://api.snapplanet.io/v1.0.0/' . strtolower($str), array_keys(array_merge(array('resto-core' => 1), $this->context->addons))));
    }
}
