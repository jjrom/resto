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
 * Users API
 */
class UsersAPI
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
     * Return users
     *
     *  @OA\Get(
     *      path="/users",
     *      summary="Get users",
     *      description="Return the list of user's profiles ordered by descending user identifier. A maximum of 50 profiles are returned per page. The *lt* parameter should be used for pagination",
     *      tags={"User"},
     *      @OA\Parameter(
     *         name="lt",
     *         in="query",
     *         description="Return user profiles with identifier lower than *lt* - used for pagination",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="in",
     *         in="query",
     *         description="List of comma separated user identifiers",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="groupid",
     *         in="query",
     *         description="Return user profiles belonging to group identified by *groupid* ",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Filter by name, firstname or lastname",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="List of users profiles",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                 property="totalResults",
     *                 type="integer",
     *                 description="Total number of user profiles"
     *              ),
     *              @OA\Property(
     *                 property="exactCount",
     *                 type="boolean",
     *                 description="True if totalResults is an exact count. False if estimated."
     *              ),
     *              @OA\Property(
     *                  property="profiles",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/UserDisplayProfile")
     *              ),
     *              example={
     *                  "totalResults": 2,
     *                  "exactCount": true,
     *                  "profiles":{
     *                      {
     *                          "id": "1356771884787565573",
     *                          "picture": "https://robohash.org/d0e907f8b6f4ee74cd4c38a515e2a4de?gravatar=hashed&bgset=any&size=400x400",
     *                          "groups": {
     *                              1
     *                          },
     *                          "name": "jrom",
     *                          "followers": 185,
     *                          "followings": 144,
     *                          "firstname": "Jérôme",
     *                          "lastname": "Gasperi",
     *                          "bio": "Working on new features for the next major release of SnapPlanet",
     *                          "registrationdate": "2016-10-08T22:50:34.187217Z",
     *                          "topics":"earth,fires,geology,glaciology,volcanism",
     *                          "followed": false,
     *                          "followme": false
     *                      },
     *                      {
     *                          "id": "1381434932013827205",
     *                          "picture": "https://graph.facebook.com/410860042635946/picture?type=large",
     *                          "groups": {
     *                              "1"
     *                          },
     *                          "name": "Sergio",
     *                          "followers": 16,
     *                          "followings": 9,
     *                          "registrationdate": "2016-10-08T22:50:34.187217Z",
     *                          "followed": false,
     *                          "followme": false
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
    public function getUsersProfiles($params)
    {

        if (isset($params['lt']) && !ctype_digit($params['lt'])) {
            return RestoLogUtil::httpError(400, 'Invalid lt - should be numeric');
        }

        if (isset($params['groupid']) && !ctype_digit($params['groupid'])) {
            return RestoLogUtil::httpError(400, 'Invalid groupid');
        }

        if (isset($params['in'])) {
            $exploded = explode(',', $params['in']);
            for ($i = count($exploded);$i--;) {
                if (!ctype_digit(trim($exploded[$i]))) {
                    return RestoLogUtil::httpError(400, 'Invalid in');
                }
            }
        }

        return (new UsersFunctions($this->context->dbDriver))->getUsersProfiles(array(
            'lt' => $params['lt'] ?? null,
            'groupid' => $params['groupid'] ?? null,
            'in' => $params['in'] ?? null,
            'q' => $params['q'] ?? null
        ), !$this->user->hasGroup(Resto::GROUP_ADMIN_ID) ? $this->user->profile['id'] : null
    );

    }

    /**
     *  Get user profile
     *
     *  @OA\Get(
     *      path="/users/{userid}",
     *      summary="Get user",
     *      tags={"User"},
     *      @OA\Parameter(
     *         name="userid",
     *         in="path",
     *         required=true,
     *         description="User's identifier",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="User profile",
     *          @OA\JsonContent(ref="#/components/schemas/UserDisplayProfile")
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
    public function getUserProfile($params)
    {
        if ($this->user->profile['id'] === $params['userid']) {
            $this->user->loadProfile();
            return $this->user->profile;
        }
        
        return (new UsersFunctions($this->context->dbDriver))->getUserProfile(
            array(
                'id' => $params['userid'],
                'from' => $this->user->profile['id'],
                'partial' => true
            )
        );
    }

    /**
     *  Get user groups
     *
     *  @OA\Get(
     *      path="/users/{userid}/groups",
     *      summary="Get user's groups",
     *      tags={"User"},
     *      @OA\Parameter(
     *         name="userid",
     *         in="path",
     *         required=true,
     *         description="User's identifier",
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
     *                          "name":"Default",
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
        if ($this->user->profile['id'] === $params['userid']) {
            $this->user->loadProfile();
            return array(
                'id' => $this->user->profile['id'],
                'groups' => (new GroupsFunctions($this->context->dbDriver))->getGroups(array(
                    'in' => $this->user->profile['groups']
                ))
            );
        }

        $user = $this->getUser($params['userid']);
        return isset($user->profile['id']) ? array(
            'id' => $user->profile['id'],
            'groups' => (new GroupsFunctions($this->context->dbDriver))->getGroups(array(
                'in' => $user->profile['groups']
            ))
        ) : RestoLogUtil::httpError(404);
    }

    /**
     *  Get user rights
     *
     *  [TODO] - Write API
     */
    public function getUserRights($params)
    {
        RestoUtil::checkUser($this->user, $params['userid']);
        $result = array(
            'id' => $this->user->profile['id']
        );
        if (isset($params['collectionName'])) {
            $result['collection'] = $params['collectionName'];
        }
        if (isset($params['featureId'])) {
            $result['feature'] = $params['featureId'];
        }
        $result['rights'] = $this->user->getRights($params['collectionName'] ?? null, $params['featureId'] ?? null);
        return $result;
    }

    /**
     *  Get user signatures
     *
     *  [TODO] - Write API
     */
    public function getUserSignatures($params)
    {
        RestoUtil::checkUser($this->user, $params['userid']);
        return array(
            'id' => $this->user->profile['id'],
            'signatures' => $this->user->getSignatures()
        );
    }

    /**
     * Create user
     *
     * @OA\Post(
     *      path="/users",
     *      summary="Create user",
     *      tags={"User"},
     *      @OA\Response(
     *          response="200",
     *          description="User is created but not activated. An activation code is sent to user's email address.",
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
     *                  "message": "User john.doe@dev.null created"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad request",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="409",
     *          description="User already exist",
     *          @OA\JsonContent(ref="#/components/schemas/ConflictError")
     *      ),
     *      @OA\RequestBody(
     *         description="User information to create user account",
     *         required=true,
     *         @OA\JsonContent(
     *              required={"email", "password"},
     *              @OA\Property(
     *                  property="email",
     *                  type="string",
     *                  description="User email"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *                  description="User password - don't worry it's encrypted server side"
     *              ),
     *              @OA\Property(
     *                  property="picture",
     *                  type="string",
     *                  description="An http(s) url to the user's avatar picture"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="User display name"
     *              ),
     *              @OA\Property(
     *                  property="firstname",
     *                  type="string",
     *                  description="User firstname"
     *              ),
     *              @OA\Property(
     *                  property="lastname",
     *                  type="string",
     *                  description="User lastname"
     *              ),
     *              @OA\Property(
     *                  property="bio",
     *                  type="string",
     *                  description="User biography"
     *              ),
     *              @OA\Property(
     *                  property="country",
     *                  type="string",
     *                  description="User country code (ISO 3166-1 alpha2 code)"
     *              ),
     *              @OA\Property(
     *                  property="organization",
     *                  type="string",
     *                  description="Organization name"
     *              ),
     *              @OA\Property(
     *                  property="flags",
     *                  type="string",
     *                  description="[Unused] Comma separated list of flags"
     *              ),
     *              @OA\Property(
     *                  property="topics",
     *                  type="string",
     *                  description="Comma separated list of user's topics of interest"
     *              ),
     *              example={
     *                  "email": "john.doe@dev.null",
     *                  "password":"MySuperSecretPassword",
     *                  "picture": "https://robohash.org/d0e907f8b6f4ee74cd4c38a515e2a4de?gravatar=hashed&bgset=any&size=400x400",
     *                  "name": "jj",
     *                  "firstname": "John",
     *                  "lastname": "Doe",
     *                  "bio": "Just a user",
     *                  "country":"FR",
     *                  "organization":"My nice company",
     *                  "topics":"earth,fires,geology,glaciology,volcanism"
     *              }
     *          )
     *      )
     * )
     *
     * @param array $params
     * @param array $body
     */
    public function createUser($params, $body)
    {
        foreach (array('email', 'password') as $required) {
            if (!isset($body[$required])) {
                RestoLogUtil::httpError(400, $required . ' is not set');
            }
        }
        
        return $this->storeProfile(
            array(
                'email' => $body['email'],
                'password' => $body['password'] ?? null,
                'name'=> $body['name'] ?? trim(join(' ', array(ucfirst($body['firstname'] ?? ''), ucfirst($body['lastname'] ?? '')))),
                'firstname' => $body['firstname'] ?? null,
                'lastname' => $body['lastname'] ?? null,
                // [TODO] Check lang from request
                'lang' => 'en',
                'bio' => $body['bio'] ?? null,
                'picture' => $body['picture'] ?? null,
                'country' => $body['country'] ?? null,
                'organization' => $body['organization'] ?? null,
                'flags' => $body['flags'] ?? null,
                'topics' => $body['topics'] ?? null,
                'activated' => $this->context->core['userAutoActivation'] ? 1 : 0,
                'followers' => 0,
                'followings' => 0
            ),
            $this->context->core['storageInfo']
        );
    }

    /**
     * Update user profile
     *
     * @OA\Put(
     *      path="/users/{userid}",
     *      summary="Update user",
     *      tags={"User"},
     *      @OA\Response(
     *          response="200",
     *          description="User profile is updated",
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
     *                  "message": "Update profile for user john.doe@dev.null"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Bad request",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\RequestBody(
     *         description="User information to update",
     *         required=true,
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *                  description="User password - don't worry it's encrypted server side"
     *              ),
     *              @OA\Property(
     *                  property="picture",
     *                  type="string",
     *                  description="An http(s) url to the user's avatar picture"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="User display name"
     *              ),
     *              @OA\Property(
     *                  property="firstname",
     *                  type="string",
     *                  description="User firstname"
     *              ),
     *              @OA\Property(
     *                  property="lastname",
     *                  type="string",
     *                  description="User lastname"
     *              ),
     *              @OA\Property(
     *                  property="bio",
     *                  type="string",
     *                  description="User biography"
     *              ),
     *              @OA\Property(
     *                  property="topics",
     *                  type="string",
     *                  description="Comma separated list of user's topics of interest"
     *              ),
     *              example={
     *                  "picture": "https://robohash.org/d0e907f8b6f4ee74cd4c38a515e2a4de?gravatar=hashed&bgset=any&size=400x400",
     *                  "bio": "I just changed my picture, bio information and topics of interest list",
     *                  "topics":"earth,fires"
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
     */
    public function updateUserProfile($params, $body)
    {
        RestoUtil::checkUser($this->user, $params['userid']);

        /*
         * For normal user (i.e. non admin), some properties cannot be modified after validation
         */
        if (! $this->user->hasGroup(Resto::GROUP_ADMIN_ID)) {

           /*
            * Already validated => avoid updating administrative properties
            */
            if (isset($this->user->profile['validatedby'])) {
                unset($body['activated'], $body['validatedby'], $body['validationdate'], $body['country'], $body['organization'], $body['organizationcountry'], $body['flags']);
            }

            /*
             * These properties can only be changed by admin
             */
            unset($body['groups']);
        }

        /*
         * Ensure that user can only update its profile
         */
        $body['email'] = $this->user->profile['email'];
        (new UsersFunctions($this->context->dbDriver))->updateUserProfile(
            $body,
            $this->context->core['storageInfo']
        );

        return RestoLogUtil::success('Update profile for user ' . $this->user->profile['email']);
    }

    /**
     * Get user searches
     * 
     *  @OA\Get(
     *      path="/users/{userid}/history",
     *      summary="Get user's search history",
     *      description="Results are returned by pages with 50 results per page from most recent to oldest.",
     *      tags={"User"},
     *      @OA\Parameter(
     *         name="userid",
     *         in="path",
     *         required=true,
     *         description="User's identifier",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="querytime",
     *         in="query",
     *         description="Filter on query time. Interval of ISO8601 date (i.e. YYYY-MM-DDTHH:MM:SSZ)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="lt",
     *         in="query",
     *         description="Return logs with gid lower than *lt* - used for pagination",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="User's history",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="User identifier"
     *              ),
     *              @OA\Property(
     *                  property="logs",
     *                  type="array",
     *                  description="Array of user's log history",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(
     *                          property="gid",
     *                          type="integer",
     *                          description="Log unique idendifier"
     *                      ),
     *                      @OA\Property(
     *                          property="method",
     *                          type="string",
     *                          description="Method - one of GET, POST, PUT or DELETE"
     *                      ),
     *                      @OA\Property(
     *                          property="path",
     *                          type="string",
     *                          description="Path relative to the root endpoint"
     *                      ),
     *                      @OA\Property(
     *                          property="querytime",
     *                          type="string",
     *                          description="Date of query (in ISO 8601)"
     *                      ),
     *                      @OA\Property(
     *                          property="query",
     *                          type="string",
     *                          description="Query string"
     *                      ),
     *                      @OA\Property(
     *                          property="userid",
     *                          type="string",
     *                          description="User identifier (only display to admin user)"
     *                      ),
     *                      @OA\Property(
     *                          property="ip",
     *                          type="string",
     *                          description="Calling IP address (only display to admin user)"
     *                      )
     *                  )
     *              ),
     *              example={
     *                  "id": "1356771884787565573",
     *                  "logs":{
     *                      {
     *                          "gid": 65,
     *                          "method": "GET",
     *                          "path": "/users/202707441557308418/logs",
     *                          "querytime": "2019-01-05T07:10:38.785236Z"
     *                      },
     *                      {
     *                          "gid": 39,
     *                          "method": "GET",
     *                          "path": "/users",
     *                          "querytime": "2019-01-03T22:19:28.251167Z",
     *                          "query": "&in=202707441557308418%60"
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
     *          response="403",
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *  )
     */
    public function getUserLogs($params)
    {
       
        /*
         * [SECURITY] User is limited to its own history logs
         */
        $isAdmin = $this->user->hasGroup(Resto::GROUP_ADMIN_ID);
        if (!$isAdmin) {
            RestoUtil::checkUser($this->user, $params['userid']);
        }

        if (isset($params['lt']) && !ctype_digit($params['lt'])) {
            return RestoLogUtil::httpError(400, 'Invalid lt - should be numeric');
        }
        
        return (new LogsFunctions($this->context->dbDriver))->getLogs(array(
            'userid' => $params['userid'],
            'lt' => $params['lt'] ?? null,
            'querytime' => $params['querytime'] ?? null,
            'fullDisplay' => $isAdmin
        ));
        
    }

    /**
     * Create user object from userid
     *
     * @param string userid
     */
    private function getUser($userid)
    {
        if (!ctype_digit($userid)) {
            RestoLogUtil::httpError(400, 'Invalid userid');
        }
        return new RestoUser(array('id' => $userid), $this->context, true);
    }

    /**
     * Store user profile
     *
     * @param array $profile
     * @param array $storageInfo
     */
    private function storeProfile($profile, $storageInfo)
    {
        $userInfo = (new UsersFunctions($this->context->dbDriver))->storeUserProfile(
            $profile,
            $storageInfo
        );

        if (isset($userInfo)) {

            // Auto activation no email sent
            if ($profile['activated'] === 1) {
                return RestoLogUtil::success('User ' . $profile['email'] . ' created');
            }

            if (!(new RestoNotifier($this->context->serviceInfos))->sendMailForUserActivation($profile['email'], $this->context->core['sendmail'], array(
                'token' => $this->context->createRJWT($userInfo['id'])
            ))) {
                RestoLogUtil::httpError(500, 'Cannot send activation link');
            }
        } else {
            RestoLogUtil::httpError(500, 'Database connection error');
        }

        return RestoLogUtil::success('User ' . $profile['email'] . ' created');
    }
}
