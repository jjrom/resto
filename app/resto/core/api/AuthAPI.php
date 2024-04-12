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
 * Authentication API
 */
class AuthAPI
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
     *  Get authentication token
     *
     *  @OA\Get(
     *      path="/auth",
     *      summary="Get an authentication token",
     *      description="Get a fresh authentication token (aka rJWT).",
     *      tags={"Authentication"},
     *      @OA\Response(
     *          response="200",
     *          description="A fresh authentication token (aka rJWT)",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  description="A rJWT token"
     *              ),
     *              @OA\Property(
     *                  property="profile",
     *                  description="User profile",
     *                  ref="#/components/schemas/UserDisplayProfile"
     *              ),
     *              example={
     *                  "token": "eyJzdWIiOiIxOTQ2NTIwMjk3MjEzNTI3MDUyIiwiaWF0IjoxNTQ2MjY2NTU3LCJleHAiOjE1NDYyNzAxNTd9.nI4q0LBqGOG0a6GCjxWvUiVA6hKndN9mJrjuT1WG1Xo",
     *                  "profile":{
     *                      "id": "1356771884787565573",
     *                      "picture": "https://robohash.org/d0e907f8b6f4ee74cd4c38a515e2a4de?gravatar=hashed&bgset=any&size=400x400",
     *                      "groups": {
     *                          "1"
     *                      },
     *                      "name": "jrom",
     *                      "followers": 185,
     *                      "followings": 144,
     *                      "firstname": "Jérôme",
     *                      "lastname": "Gasperi",
     *                      "bio": "Working on new features for the next major release of SnapPlanet",
     *                      "registrationdate": "2016-10-08T22:50:34.187217Z",
     *                      "topics":"earth,fires,geology,glaciology,volcanism",
     *                      "followed": false,
     *                      "followme": false
     *                  }
     *              }
     *         )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     * )
     *
     */
    public function getToken()
    {
        // Be sure that user profile is loaded
        $this->user->loadProfile();
        
        // User not activated
        if ($this->user->profile['activated'] !== 1) {
            return RestoLogUtil::httpError(412, 'User not activated');
        }

        return array(
            'token' => $this->context->createRJWT($this->user->profile['id'], $this->context->core['tokenDuration']),
            'profile' => $this->user->profile
        );
    }

    /**
     * Create an authentication token
     *
     *  @OA\Get(
     *      path="/create",
     *      summary="Create an authentication {token}",
     *      description="Create an authentication token (aka rJWT) for user identified by {emailOrId}",
     *      tags={"Authentication"},
     *      @OA\Parameter(
     *         name="emailOrId",
     *         in="query",
     *         required=true,
     *         description="User email or id",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="duration",
     *         in="query",
     *         required=false,
     *         description="Duration of token in days (default is 1 day)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The token is created",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="userId",
     *                  type="string",
     *                  description="User id"
     *              ),
     *              @OA\Property(
     *                  property="duration",
     *                  type="integer",
     *                  description="Duration of token in days"
     *              ),
     *              @OA\Property(
     *                  property="valid_until",
     *                  type="string",
     *                  description="Token validity"
     *              ),
     *              @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  description="Generated token"
     *              ),
     *              example={
     *                  "userId": 100,
     *                  "duration": 100,
     *                  "valid_until": "2023-05-03T11:20:13", 
     *                  "token":"eyJzdWIiOiIxMDAiLCJpYXQiOjE2NzQ0NzI4MTMsImV4cCI6MTY4MzExMjgxM30.5fdRS1jr0fuF7HMu2oXb0sXViom39ExI2IR_FI5WK7k"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     * )
     *
     *  @param array $params
     */
    public function createToken($params)
    {

        // A token can be only be created by admin 
        if ( !$this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ) {
            return RestoLogUtil::httpError(403);
        }
        
        if ( isset($params['duration']) && !ctype_digit($params['duration']) ) {
            return RestoLogUtil::httpError(400, 'Parameters duration must be a valid integer in days');
        }

        if ( !isset($params['duration']) ) {
            $params['duration'] = (integer) ($this->context->core['tokenDuration'] / 86400);
        }

        if ( !isset($params['userId']) || !ctype_digit($params['userId']) ) {
            return RestoLogUtil::httpError(400, 'Mandatory userId is not set or not valid');
        }

        $days = isset($params['duration']) ? (integer) $params['duration'] : round($this->context->core['tokenDuration'] / 86400);
        $seconds = 86400 * $days;
        return array(
            'userId' => $params['userId'],
            'duration' => $days,
            'valid_until' => date('Y-m-d\TH:i:s', time() + $seconds),
            'token' => $this->context->createRJWT($params['userId'], $seconds)
        );

    }

    /**
     * Revoke authentication token
     *
     *  @OA\Get(
     *      path="/auth/revoke/{token}",
     *      summary="Revoke an authentication {token}",
     *      description="Revoke authication token (aka rJWT). Only administrator or owne of a token can revoke it. This service should be called when user logged out from client side.",
     *      tags={"Authentication"},
     *      @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         description="JWT or rJWT",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The token is revoked",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Message information"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Token revoked"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Invalid token",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     * )
     *
     *  @param array $params
     */
    public function revokeToken($params)
    {
        $payload = $this->context->decodeJWT($params['token']);
        if (!isset($payload)) {
            return RestoLogUtil::httpError(400, 'Invalid or expired token');
        }
        
        // A token can be only be revoked by admin or by its owner
        if ($this->user->profile['id'] !== $payload['sub']) {
            if (!$this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID)) {
                return RestoLogUtil::httpError(403);
            }
        }

        (new GeneralFunctions($this->context->dbDriver))->revokeToken($params['token'], date(DateTime::ISO8601, $payload['exp']));
        
        return RestoLogUtil::success('Token revoked');
    }

    /**
     *  Check JWT token validity
     *
     *  @OA\Get(
     *      tags={"User"},
     *      path="/auth/check/{token}",
     *      summary="Check token validity",
     *      description="Check if security token associated to user is valid. Usually security token is used to temporarely replace authentication to download/visualize ressources",
     *      operationId="checkToken",
     *      @OA\Parameter(
     *          name="token",
     *          in="path",
     *          description="Security token",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Return token validity",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Token checked"
     *              ),
     *              @OA\Property(
     *                  property="isValid",
     *                  type="boolean",
     *                  description="True if valid - False if not"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Token checked",
     *                  "isValid": False
     *              }
     *          )
     *      )
     *  )
     */
    public function checkToken($params)
    {
        $payload = $this->context->decodeJWT($params['token']);

        if (!isset($payload) || (new GeneralFunctions($this->context->dbDriver))->isTokenRevoked($params['token'])) {
            return RestoLogUtil::success('Token checked', array(
                'isValid' => false
            ));
        }
        
        return RestoLogUtil::success('Token checked', array(
            'isValid' => true
        ));
    }

    /**
     * Activate user
     *
     *  @OA\Put(
     *      path="/auth/activate/{token}",
     *      summary="Activate a user",
     *      description="Activate registered user",
     *      operationId="activateUser",
     *      tags={"User"},
     *      @OA\Parameter(
     *          name="token",
     *          in="path",
     *          description="Activation token",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Activation status - user activated or not",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  description="Authentication token"
     *              ),
     *              @OA\Property(
     *                  property="profile",
     *                  description="User profile",
     *                  ref="#/components/schemas/UserDisplayProfile"
     *              ),
     *              example={
     *                  "token": "eyJzdWIiOiIxOTQ2NTIwMjk3MjEzNTI3MDUyIiwiaWF0IjoxNTQ2MjY2NTU3LCJleHAiOjE1NDYyNzAxNTd9.nI4q0LBqGOG0a6GCjxWvUiVA6hKndN9mJrjuT1WG1Xo",
     *                  "profile":{
     *                      "id": "1356771884787565573",
     *                      "picture": "https://robohash.org/d0e907f8b6f4ee74cd4c38a515e2a4de?gravatar=hashed&bgset=any&size=400x400",
     *                      "groups": {
     *                          "1"
     *                      },
     *                      "name": "jrom",
     *                      "followers": 185,
     *                      "followings": 144,
     *                      "firstname": "Jérôme",
     *                      "lastname": "Gasperi",
     *                      "bio": "Working on new features for the next major release of SnapPlanet",
     *                      "registrationdate": "2016-10-08T22:50:34.187217Z",
     *                      "topics":"earth,fires,geology,glaciology,volcanism",
     *                      "followed": false,
     *                      "followme": false
     *                  }
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad request"
     *      )
     *  )
     *
     * @param array $params
     */
    public function activateUser($params)
    {
        $payload = $this->context->decodeJWT($params['token']);

        if (!isset($payload) || !isset($payload['sub'])) {
            return RestoLogUtil::httpError(400, 'Invalid or expired token');
        }

        $user = new RestoUser(array('id' => $payload['sub']), $this->context, true);

        if (!isset($user->profile['id']) || !$user->activate()) {
            return RestoLogUtil::httpError(500, 'User not activated');
        }

        return array(
            'token' => $this->context->createRJWT($user->profile['id'], $this->context->core['tokenDuration']),
            'profile' => $user->profile
        );
    }
}
