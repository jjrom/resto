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
 * Groups API
 */
class GroupAPI
{

    private $context;
    private $user;

    /**
     *
     * @OA\Schema(
     *  schema="OutputGroup",
     *  required={"id", "name", "description", "owner"},
     *  @OA\Property(
     *      property="id",
     *      type="string",
     *      description="Unique group identifier - generated by resto during group creation"
     *  ),
     *  @OA\Property(
     *      property="name",
     *      type="string",
     *      description="Unique name of the group - free text"
     *  ),
     *  @OA\Property(
     *      property="description",
     *      type="string",
     *      description="A description for this group"
     *  ),
     *  @OA\Property(
     *      property="owner",
     *      type="integer",
     *      description="Owner of the group (i.e. resto username)"
     *  ),
     *  @OA\Property(
     *      property="created",
     *      type="string",
     *      description="Date of group creation (ISO 8601 - YYYY-MM-DD-THH:MM:SSZ)"
     *  ),
     *  example={
     *      "name": "My first group",
     *      "description": "Any user can create a group.",
     *      "id": "100",
     *      "owner": "johndoe",
     *      "created": "2024-12-13T21:29:23.671111Z",
     *      "members": {
     *          "johndoe"
     *      }
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
     * Return groups
     *
     *  @OA\Get(
     *      path="/groups",
     *      summary="Get groups",
     *      description="The list is ordered by most recently created",
     *      tags={"Group"},
     *      @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Filter by group name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="List of groups",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                 property="totalResults",
     *                 type="integer",
     *                 description="Total number of groups"
     *              ),
     *              @OA\Property(
     *                  property="results",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/OutputGroup")
     *              ),
     *              example={
     *                  "totalResults": 2,
     *                  "groups":{
     *                      {
     *                          "id": 100,
     *                          "name": "My first group",
     *                          "description": "Any user can create a group.",
     *                      },
     *                      {
     *                          "id": 101,
     *                          "name": "My second group",
     *                          "description": "Any user can create a group."
     *                      }
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
     *  )
     *
     * @param array $params
     */
    public function getGroups($params)
    {
        $groups = array(
            'groups' => (new GroupsFunctions($this->context->dbDriver))->getGroups(array(
                'q' => $params['q'] ?? null
            ))
        );

        if ( empty($groups) ) {
            RestoLogUtil::httpError(400);
        }

        return $groups;
    }

    /**
     *  Get group
     *
     *  @OA\Get(
     *      path="/groups/{name}",
     *      summary="Get group",
     *      tags={"Group"},
     *      @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         description="Group name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="User group",
     *          @OA\JsonContent(ref="#/components/schemas/OutputGroup")
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
    public function getGroup($params)
    {
        return (new GroupsFunctions($this->context->dbDriver))->getGroup($params['name']);
    }

    /**
     *
     * Add group
     *
     * @OA\Post(
     *      path="/groups",
     *      summary="Create group",
     *      description="Create a group from name - the name must be unique",
     *      tags={"Group"},
     *      @OA\Response(
     *          response="200",
     *          description="Group is created and unique identifier is returned",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Group created"
     *              ),
     *              @OA\Property(
     *                  property="id",
     *                  type="integer",
     *                  description="Newly created group identifier"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Group created",
     *                  "id": 100
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="This group already exist",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\RequestBody(
     *         description="Group description",
     *         required=true,
     *         @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="Unique name for the group"
     *              ),
     *              @OA\Property(
     *                  property="description",
     *                  type="string",
     *                  description=""
     *              ),
     *              example={
     *                  "name": "My first group",
     *                  "description": "Any user can create a group."
     *              }
     *          )
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
    public function createGroup($params, $body)
    {

        // Owner of group can only be set by admin user
        if ( isset($body['owner']) && !$this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ) {
            RestoLogUtil::httpError(403, 'You are not allowed to set property "owner"');
        } 
        
        // Force owner to POSTING user
        $body['owner'] = $body['owner'] ?? $this->user->profile['id'];
        $body['private'] = 0;
        
        $group = (new GroupsFunctions($this->context->dbDriver))->createGroup($body);
        
        // When you create a group, you're in the group unless you're an admin
        if ( !$this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID ) ) {
            (new GroupsFunctions($this->context->dbDriver))->addUserToGroup(array('id' => $group['id']), $body['owner']);
        }
        
        return RestoLogUtil::success('Group created', $group);
    }

    /**
     *
     * Remove a group
     *
     *  @OA\Delete(
     *      path="/groups/{name}",
     *      summary="Delete group",
     *      description="Only administrator and owner of a group can delete it",
     *      tags={"Group"},
     *      @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         description="Group name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The group is delete",
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
     *                  "message": "Delete group xxxx"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Missing mandatory group identifier",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Group not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
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
    public function deleteGroup($params)
    {
        $group = (new GroupsFunctions($this->context->dbDriver))->removeGroup(array(
            'name' => $params['name'],
            'owner' => $this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ? null : $this->user->profile['id']
        ));

        return RestoLogUtil::success('Deleted group', array('name' => $group['name']));

    }

    /**
     *
     * Add a user to group
     *
     * @OA\Post(
     *      path="/groups/{name}/users",
     *      summary="Add a user",
     *      description="Add a user to a group",
     *      tags={"Group"},
     *      @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         description="Group name",
     *         @OA\Schema(
     *             type="name"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="User is added",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="User added"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "User added"
     *              }
     *          )
     *      ),
     *      @OA\RequestBody(
     *         description="User info",
     *         required=true,
     *         @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(
     *                  property="username",
     *                  type="string",
     *                  description="User name"
     *              ),
     *              example={
     *                  "username": "johndoe"
     *              }
     *          )
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
    public function addUser($params, $body)
    {

        $group = (new GroupsFunctions($this->context->dbDriver))->getGroup($params['name']);
        
        if ( !isset($body['username']) ) {
            RestoLogUtil::httpError(400, 'Mandatory username property is missing in message body');
        }

        $user = new RestoUser(array('username' => $body['username']), $this->context);
        
        /*
         * [SECURITY] Only user and admin can add user to group
         */
        $isAdmin = $this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID);
        if ( !$isAdmin ) {
            if ( !isset($group['owner']) ) {
                RestoLogUtil::httpError(403);
            }
            RestoUtil::checkUserId($this->user, $group['owner']);
        }

        if ( (new GroupsFunctions($this->context->dbDriver))->addUserToGroup(array('id' => $group['id']), $user->profile['id']) ) {
            return RestoLogUtil::success('User added to group');
        }

        
    }

    /**
     * Remove user from a group
     */
    public function deleteUser($params)
    {

        $group = (new GroupsFunctions($this->context->dbDriver))->getGroup($params['name']);
        $user = new RestoUser(array('username' => $params['username']), $this->context);

        /*
         * [SECURITY] Only user and admin can delete user from group
         */
        $isAdmin = $this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID);
        if ( !$isAdmin ) {
            if ( !isset($group['owner']) ) {
                RestoLogUtil::httpError(403);
            }
            RestoUtil::checkUserId($this->user, $group['owner']);
        }

        if ( (new GroupsFunctions($this->context->dbDriver))->removeUserFromGroup(array('id' => $group['id']), $user->profile['id']) ) {
            return RestoLogUtil::success('User removed from group');
        }


    }

    /**
     *  Get user groups
     *
     *  @OA\Get(
     *      path="/users/{username}/groups",
     *      summary="Get user's groups",
     *      tags={"User"},
     *      @OA\Parameter(
     *         name="username",
     *         in="path",
     *         required=true,
     *         description="User's name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="User groups",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="User identifier"
     *              ),
     *              @OA\Property(
     *                  property="groups",
     *                  type="array",
     *                  description="Array of user's groups",
     *                  @OA\Items(ref="#/components/schemas/OutputGroup")
     *              ),
     *              example={
     *                  "id": "1356771884787565573",
     *                  "groups":{
     *                      {
     *                          "id":"1",
     *                          "name":"default",
     *                          "description":"Default group"
     *                      }
     *                  }
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
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
    public function getUserGroups($params)
    {
        if ($this->user->profile['username'] === $params['username']) {
            $this->user->loadProfile();
            return array(
                'username' => $this->user->profile['username'],
                'id' => $this->user->profile['id'],
                'groups' => (new GroupsFunctions($this->context->dbDriver))->getGroups(array(
                    'in' => $this->user->getGroupIds()
                ))
            );
        }

        $user = new RestoUser(array('username' => $params['username']), $this->context);
        return isset($user->profile['username']) ? array(
            'username' => $this->user->profile['username'],
            'id' => $user->profile['id'],
            'groups' => (new GroupsFunctions($this->context->dbDriver))->getGroups(array(
                'in' => $user->getGroupIds()
            ))
        ) : RestoLogUtil::httpError(404);
    }
    
}
