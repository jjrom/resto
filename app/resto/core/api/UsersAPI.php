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
     *         style="form",
     *         description="Return user profiles with identifier lower than *lt* - used for pagination",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="in",
     *         in="query",
     *         style="form",
     *         description="List of comma separated user identifiers",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="groupid",
     *         in="query",
     *         style="form",
     *         description="Return user profiles belonging to group identified by *groupid* ",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="q",
     *         in="query",
     *         style="form",
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
     *                          "username": "jrom",
     *                          "picture": "https://robohash.org/d0e907f8b6f4ee74cd4c38a515e2a4de?gravatar=hashed&bgset=any&size=400x400",
     *                          "groups": {
     *                              1
     *                          },
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
     *                          "username": "Sergio",
     *                          "picture": "https://graph.facebook.com/410860042635946/picture?type=large",
     *                          "groups": {
     *                              "1"
     *                          },
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
            RestoLogUtil::httpError(400, 'Invalid lt - should be numeric');
        }

        if (isset($params['groupid']) && !ctype_digit($params['groupid'])) {
            RestoLogUtil::httpError(400, 'Invalid groupid');
        }

        if (isset($params['in'])) {
            $exploded = explode(',', $params['in']);
            for ($i = count($exploded);$i--;) {
                if (!ctype_digit(trim($exploded[$i]))) {
                    RestoLogUtil::httpError(400, 'Invalid in');
                }
            }
        }

        return (new UsersFunctions($this->context->dbDriver))->getUsersProfiles(
            array(
                'lt' => $params['lt'] ?? null,
                'groupid' => $params['groupid'] ?? null,
                'in' => $params['in'] ?? null,
                'q' => $params['q'] ?? null
            ),
            !$this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) ? $this->user->profile['id'] : null
        );
    }

    /**
     *  Get my profile
     *
     *  @OA\Get(
     *      path="/me",
     *      summary="Get my profile",
     *      tags={"User"},
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
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *  )
     */
    public function getMyProfile() {
        $this->user->loadProfile();
        return array_merge(
            $this->user->profile,
            array(
                'in_groups' => $this->user->getGroups(),
                'owned_groups' => $this->user->getOwnedGroups()
            )
        );
    }

    /**
     *  Get user profile
     *
     *  @OA\Get(
     *      path="/users/{username}",
     *      summary="Get user",
     *      tags={"User"},
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

        if ($this->user->profile['username'] === $params['username']) {
            return $this->getMyProfile();
        }
        
        return (new UsersFunctions($this->context->dbDriver))->getUserProfile(
            'username',
            $params['username'],
            array(
                'from' => $this->user->profile['id'],
                'partial' => true
            )
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
     *      @OA\Response(
     *          response="412",
     *          description="User already exist but is not activated",
     *          @OA\JsonContent(ref="#/components/schemas/ConflictError")
     *      ),
     *      @OA\RequestBody(
     *         description="User information to create user account",
     *         required=true,
     *         @OA\JsonContent(
     *              required={"username", "email", "password"},
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
     *                  property="username",
     *                  type="string",
     *                  description="User name - must be alphanumerical only between 3 and 255 characters. (Note: will be converted to lowercase)"
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
     *                  "username": "jj",
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
        foreach (array('email', 'password', 'username') as $required) {
            if (!isset($body[$required])) {
                RestoLogUtil::httpError(400, $required . ' is not set');
            }
        }

        // Enforce alphanumerical name between 3 and 255 characters
        if ( !ctype_alnum($body['username']) || strlen($body['username']) < 3 || strlen($body['username']) > 254 ) {
            RestoLogUtil::httpError(400, 'Propety username must be an alphanumerical string between 3 and 255 characters');
        }
        
        $profile = array(
            'email' => $body['email'],
            'password' => $body['password'] ?? null,
            'username'=> strtolower($body['username']),
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
            // User created by admin is automatically activated
            'activated' => ($this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID) || $this->context->core['userAutoActivation']) ? 1 : 0,
            'followers' => 0,
            'followings' => 0
        );

        return $this->storeProfile($profile, $this->context->core['storageInfo']);

    }

    /**
     * Update user profile
     *
     * @OA\Put(
     *      path="/users/{username}",
     *      summary="Update user",
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
        RestoUtil::checkUserName($this->user, $params['username']);

        // name cannot be updated
        if ( isset($body['username']) ) {
            RestoLogUtil::httpError(400, 'Property username cannot be updated');
        }

        /*
         * For normal user (i.e. non admin), some properties cannot be modified after validation
         */
        if (! $this->user->hasGroup(RestoConstants::GROUP_ADMIN_ID)) {
            /*
             * Already validated => avoid updating administrative properties
             */
            if (isset($this->user->profile['validatedby'])) {
                unset($body['activated'], $body['validatedby'], $body['validationdate'], $body['country'], $body['organization'], $body['organizationcountry'], $body['flags']);
            }

        }

        /*
         * Ensure that user can only update its profile
         */
        $body['username'] = $this->user->profile['username'];
        (new UsersFunctions($this->context->dbDriver))->updateUserProfile(
            $body,
            $this->context->core['storageInfo']
        );

        return RestoLogUtil::success('Update profile for user ' . $this->user->profile['username']);
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
            if ($userInfo['activated'] === 1) {
                return RestoLogUtil::success('User ' . $userInfo['email'] . ' created and activated', array('profile' => $userInfo));
            }

            if (!(new RestoNotifier($this->context->servicesInfos, $this->context->lang))->sendMailForUserActivation($userInfo['email'], $this->context->core['sendmail'], array(
                'token' => $this->context->createJWT($userInfo['username'], $this->context->core['tokenDuration'])
            ))) {
                RestoLogUtil::httpError(500, 'Cannot send activation link');
            }
        } else {
            RestoLogUtil::httpError(500, 'Database connection error');
        }

    }
}
