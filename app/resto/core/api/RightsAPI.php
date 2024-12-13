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
 * Rights API
 */
class RightsAPI
{

    private $context;
    private $user;

    /**
     *
     * @OA\Schema(
     *  schema="Rights",
     *  type="object",
     *  description="A list of boolean rights",
     *  example={
     *    "createCollection": false,
     *    "deleteCollection": true,
     *    "updateCollection": true,
     *    "deleteAnyCollection": false,
     *    "updateAnyCollection": false,
     *    "createItem": true,
     *    "updateItem": true,
     *    "deleteItem": true,
     *    "createAnyItem": false,
     *    "deleteAnyItem": false,
     *    "updateAnyItem": false,
     *    "downloadItem": false
     *  }
     * )
     */

    /**
     * Constructor
     */
    public function __construct($context, $user)
    {
        $this->context = $context;
        $this->user = $user;
    }

    /**
     *  @OA\Get(
     *      path="/users/{username}/rights",
     *      summary="Get user rights",
     *      tags={"Rights"},
     *      @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="User name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="User rights",
     *          @OA\JsonContent(ref="#/components/schemas/Rights")
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
     *      @OA\Response(
     *          response="404",
     *          description="Resource not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *  )
     */
    public function getUserRights($params)
    {

        /*
         * [SECURITY] Only user and admin can see user rights
         */
        $isAdmin = $this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID);
        if ( !$isAdmin ) {
            RestoUtil::checkUserName($this->user, $params['username']);
        }

        $user = new RestoUser(array('username' => $params['username']), $this->context);
        return array(
            'rights' => $user->getRights()
        );

    }

    /**
     *  Get group rights
     *
     *  @OA\Get(
     *      path="/groups/{id}/rights",
     *      summary="Get group rights",
     *      tags={"Rights"},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Group identifier",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="User group rights",
     *          @OA\JsonContent(ref="#/components/schemas/Rights")
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
     *      @OA\Response(
     *          response="404",
     *          description="Resource not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *  )
     */
    public function getGroupRights($params)
    {
        return array(
            'rights' => (new RightsFunctions($this->context->dbDriver))->getRightsForGroup($params['id'])
        );
    }

    /**
     *
     * Set user rights
     *
     * @OA\Post(
     *      path="/users/{username}/rights",
     *      summary="Set rights for user",
     *      description="Set rights for a given user",
     *      tags={"Rights"},
     *      @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="User name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Rights is created or updated",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Rights updated"
     *              ),
     *              @OA\Property(
     *                  property="rights",
     *                  description="Set rights",
     *                  @OA\JsonContent(ref="#/components/schemas/Rights")
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Rights updated",
     *                  "rights": {
     *                      "createCollection": false,
     *                      "deleteCollection": true,
     *                      "updateCollection": true,
     *                      "deleteAnyCollection": false,
     *                      "updateAnyCollection": false,
     *                      "createItem": true,
     *                      "updateItem": true,
     *                      "deleteItem": true,
     *                      "createAnyItem": false,
     *                      "deleteAnyItem": false,
     *                      "updateAnyItem": false,
     *                      "downloadItem": false
     *                  }
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Rights is not set",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\RequestBody(
     *         description="Rights to create/udpated",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Rights")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     * )
     *
     * @param array $params
     * @param array $body
     *
     */
    public function setUserRights($params, $body)
    {

        /*
         * [SECURITY] Only admin can set user rights
         */
        if ( !$this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ) {
            RestoLogUtil::httpError(403);
        }

        if ( empty($body) ) {
            RestoLogUtil::httpError(400, 'No rights to set');
        }

        // Get user just to be sure that it exists !
        $user = new RestoUser(array('username' => $params['username']), $this->context);
        
        return RestoLogUtil::success('Rights set', array(
            'rights' => (new RightsFunctions($this->context->dbDriver))->storeOrUpdateRights('userid', $user->profile['id'], $body)
        ));

    }

    /**
     *
     * Set group rights
     *
     * @OA\Post(
     *      path="/groups/{id}/rights",
     *      summary="Set rights for group",
     *      description="Set rights for a given group",
     *      tags={"Rights"},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Group identifier",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Rights is created or updated",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Rights updated"
     *              ),
     *              @OA\Property(
     *                  property="rights",
     *                  description="Created/update rights",
     *                  @OA\JsonContent(ref="#/components/schemas/Rights")
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Rights updated",
     *                  "rights": {
     *                      "createCollection": false,
     *                      "deleteCollection": true,
     *                      "updateCollection": true,
     *                      "deleteAnyCollection": false,
     *                      "updateAnyCollection": false,
     *                      "createItem": true,
     *                      "updateItem": true,
     *                      "deleteItem": true,
     *                      "createAnyItem": false,
     *                      "deleteAnyItem": false,
     *                      "updateAnyItem": false,
     *                      "downloadItem": false
     *                  }
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Rights is not set",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\RequestBody(
     *         description="Rights to create/udpated",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Rights")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     * )
     *
     * @param array $params
     * @param array $body
     *
     */
    public function setGroupRights($params, $body)
    {
        /*
         * [SECURITY] Only admin can set group rights
         */
        if ( !$this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ) {
            RestoLogUtil::httpError(403);
        }

        if ( empty($body) ) {
            RestoLogUtil::httpError(400, 'No rights to set');
        }

        // Get group just to be sure that it exists !
        (new GroupsFunctions($this->context->dbDriver))->getGroups(array(
            'id' => $params['id']
        ));

        return RestoLogUtil::success('Rights set', array(
            'rights' => (new RightsFunctions($this->context->dbDriver))->storeOrUpdateRights('groupid', $params['id'], $body)
        ));

    }

}
