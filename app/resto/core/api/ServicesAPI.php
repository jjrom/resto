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

    /**
     * Constructor
     */
    public function __construct($context, $user)
    {
        $this->context = $context;
        $this->user = $user;
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
        }
        catch (Exception $e) {
        }

        if ($content === FALSE) {
            return RestoLogUtil::httpError(404);
        }

        if ($this->context->outputFormat === 'json') {
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
            'conformsTo' => array(
                'http://www.opengis.net/spec/ogcapi-features-1/1.0/req/core',
                'http://www.opengis.net/spec/ogcapi-features-1/1.0/req/oas30',
                'http://www.opengis.net/spec/ogcapi-features-1/1.0/req/geojson'
            )
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
     *                  property="capabilities",
     *                  type="array",
     *                  @OA\Items(
     *                      type="string"
     *                  ),
     *                  description="Array of resto-addons list. Used client side to detect resto capabilities",
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
        
        return array(
            'stac_version' => STAC::STAC_VERSION,
            'id' => 'catalogs',
            'type' => 'Catalog',
            'title' => getenv('API_INFO_TITLE'),
            'description' => getenv('API_INFO_DESCRIPTION'),
            'capabilities' => array_merge(array('resto-core'), array_map('strtolower', array_keys($this->context->addons))),
            'planet' => $this->context->core['planet'],
            'links' => array_merge(
                array(
                    array(
                        'rel' => 'self',
                        'type' => RestoUtil::$contentTypes['json'],
                        'title' => getenv('API_INFO_TITLE'),
                        'href' => $this->context->core['baseUrl']
                    ),
                    array(
                        'rel' => 'service-desc',
                        'type' => RestoUtil::$contentTypes['json'],
                        'title' => 'OpenAPI 3.0 definition endpoint',
                        'href' => $this->context->core['baseUrl'] . '/api'
                    ),
                    array(
                        'rel' => 'service-doc',
                        'type' => RestoUtil::$contentTypes['html'],
                        'title' => 'OpenAPI 3.0 definition endpoint documentation',
                        'href' => $this->context->core['baseUrl'] . '/api.html'
                    ),
                    array(
                        'rel' => 'conformance',
                        'type' => RestoUtil::$contentTypes['json'],
                        'title' => 'Conformance declaration',
                        'href' => $this->context->core['baseUrl'] . '/conformance'
                    ),
                    array(
                        'rel' => 'data',
                        'type' => RestoUtil::$contentTypes['json'],
                        'title' => 'Collections',
                        'href' => $this->context->core['baseUrl'] . '/collections'
                    )
                ),
                (new STAC($this->context, $this->user))->getRootLinks()
            )
        );
            
    }

    /**
     * Return OpenSearchDescription document
     *
     *    @OA\Get(
     *      path="/services/osdd/{collectionId}",
     *      summary="Get OpenSearch Description Document for a collection",
     *      description="Returns the OpenSearch Document Description (OSDD) for the search service of collection {collectionId}",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *          name="collectionId",
     *          in="path",
     *          description="Collection identifier",
     *          required=true,
     *          @OA\Items(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="OpenSearch Document Description (OSDD)"
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Collection not found"
     *      )
     *    )
     *
     * @param array params
     */
    public function getOSDDForCollection($params)
    {
        $this->context->outputFormat = 'xml';
        return (new RestoCollection($params['collectionId'], $this->context, $this->user))->load()->getOSDD();
    }

    /**
     * Return OpenSearchDescription document
     *
     *    @OA\Get(
     *      path="/services/osdd",
     *      summary="Get OpenSearch Description Document for all collections",
     *      description="Returns the OpenSearch Document Description (OSDD) for the search service on all collections",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *          name="model",
     *          in="query",
     *          description="Limit description to collections belonging to *model* - e.g. *model=SatelliteModel* will search in all satellite collections",
     *          required=false,
     *          @OA\Items(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="OpenSearch Document Description (OSDD)"
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Collection not found"
     *      )
     *    )
     *
     * @param array params
     */
    public function getOSDD($params)
    {
        $this->context->outputFormat = 'xml';
        $model = null;
        if (isset($params['model'])) {
            if (! class_exists($params['model'])) {
                return RestoLogUtil::httpError(400, 'Unknown model ' . $params['model']);
            }
            $model = new $params['model'](array(
                'addons' => $this->context->addons
            ));
        }
        return (new RestoCollections($this->context, $this->user))->getOSDD($model);
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
            $user = new RestoUser(array('email' => strtolower($body['email'])), $this->context, true);
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

            $user = new RestoUser(array('email' => strtolower($body['email'])), $this->context, true);
        }
        
        if (!isset($user)) {
            RestoLogUtil::httpError(400, 'Missing or invalid email');
        }

        // Send activation link
        if (isset($user->profile['id']) && $user->profile['activated'] === 0) {
            if (!((new RestoNotifier($this->context->servicesInfos, $this->context->lang))->sendMailForUserActivation($body['email'], $this->context->core['sendmail'], array(
                'token' => $this->context->createRJWT($user->profile['id'])
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
     *          description="Security token",
     *          required=true,
     *          type="string",
     *          @SWG\Items(type="string")
     *      ),
     *      @SWG\Parameter(
     *          name="password",
     *          in="query",
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

}
