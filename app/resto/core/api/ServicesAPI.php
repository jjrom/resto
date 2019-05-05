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
     * Hello
     */
    public function hello()
    {
        return RestoLogUtil::success('Hello');
    }

    /**
     * Return OpenSearchDescription document
     *
     *    @OA\Get(
     *      path="/services/osdd/{collectionName}",
     *      summary="Get OpenSearch Description Document for a collection",
     *      description="Returns the OpenSearch Document Description (OSDD) for the search service of collection {collectionName}",
     *      tags={"Collection"},
     *      @OA\Parameter(
     *          name="collectionName",
     *          in="query",
     *          description="Collection name",
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
        return (new RestoCollection($params['collectionName'], $this->context, $this->user))->load()->getOSDD();
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
     *          in="path",
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
            $model = new $params['model']();
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
            if (!((new RestoNotifier($this->context->serviceInfos))->sendMailForUserActivation($body['email'], $this->context->core['sendmail'], array(
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

    /**
     * Get hashtags
     *
     * @SWG\Get(
     *      tags={"hashtags"},
     *      path="/services/search/hashtags/{q}",
     *      summary="Return hashtags",
     *      description="Return hashtags",
     *      produces={"application/json"},
     *      @SWG\Response(
     *          response="200",
     *          description="Hashtags list"
     *      )
     *  )
     *
     * @param array params
     *
     */
    public function getHashtags($params)
    {
        return (new FeaturesFunctions($this->context->dbDriver))->searchHashtags(array(
            'q' => $params['q'] ?? null,
            'id' => $params['id'] ?? null,
            'user' => $this->user
        ));
    }
}
