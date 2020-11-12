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
 *  @OA\Tag(
 *      name="User",
 *      description="Everything about user - profile, access rights, history, etc."
 *  )
 */
class RestoUser
{
    const CREATE = 'create';
    const DOWNLOAD = 'download';
    const UPDATE = 'update';
    const VISUALIZE = 'visualize';

    /**
     * User profile
     *
     * @OA\Schema(
     *  schema="UserDisplayProfile",
     *  required={"id", "picture", "groups", "name", "followers", "followings"},
     *  @OA\Property(
     *      property="id",
     *      type="string",
     *      description="Unique user identifier. Identifier is related to user's registration date i.e. the greatest the identifier value, the most recently registered the user is"
     *  ),
     *  @OA\Property(
     *      property="picture",
     *      type="string",
     *      description="An http(s) url to the user's avatar picture"
     *  ),
     *  @OA\Property(
     *      property="groups",
     *      type="array",
     *      @OA\Items(
     *          type="string"
     *      ),
     *      description="Array of group identifiers"
     *  ),
     *  @OA\Property(
     *      property="name",
     *      type="string",
     *      description="User display name"
     *  ),
     *  @OA\Property(
     *      property="followers",
     *      type="integer",
     *      description="Number of user's followers"
     *  ),
     *  @OA\Property(
     *      property="followings",
     *      type="integer",
     *      description="Number of user's followings"
     *  ),
     *  @OA\Property(
     *      property="firstname",
     *      type="string",
     *      description="User firstname"
     *  ),
     *  @OA\Property(
     *      property="lastname",
     *      type="string",
     *      description="User lastname"
     *  ),
     *  @OA\Property(
     *      property="bio",
     *      type="string",
     *      description="User biography"
     *  ),
     *  @OA\Property(
     *      property="registrationdate",
     *      type="string",
     *      description="User registration date"
     *  ),
     *  @OA\Property(
     *      property="topics",
     *      type="string",
     *      description="Comma separated list of user's topics of interest"
     *  ),
     *  @OA\Property(
     *      property="followed",
     *      type="boolean",
     *      description="True if user is followed by requesting user"
     *  ),
     *  @OA\Property(
     *      property="followme",
     *      type="string",
     *      description="True if user follows requesting user"
     *  ),
     *  example={
     *      "id": "1356771884787565573",
     *      "picture": "https://robohash.org/d0e907f8b6f4ee74cd4c38a515e2a4de?gravatar=hashed&bgset=any&size=400x400",
     *      "groups": {
     *          "1"
     *      },
     *      "name": "jrom",
     *      "followers": 185,
     *      "followings": 144,
     *      "firstname": "Jérôme",
     *      "lastname": "Gasperi",
     *      "bio": "Working on new features for the next major release of SnapPlanet",
     *      "registrationdate": "2016-10-08T22:50:34.187217Z",
     *      "topics":"earth,fires,geology,glaciology,volcanism",
     *      "followed": false,
     *      "followme": false
     *  }
     * )
     */
    public $profile;

    /*
     * Current JWT token
     */
    public $token = null;

    /*
     * Context
     */
    private $context;

    /*
     * Reference to rights object
     */
    private $rights;

    /*
     * Fallback rights if no collection is found
     */
    private $fallbackRights = array(
        'download' => 0,
        'visualize' => 0,
        'create' => 0
    );

    /*
     * Unregistered profile
     */
    private $unregistered = array(
        'id' => null,
        'email' => 'unregistered',
        'groups' => array(
            Resto::GROUP_DEFAULT_ID
        ),
        'activated' => 0
    );

    /*
     * Set to true if profile is complete i.e. loaded from database
     */
    private $isComplete = false;

    /**
     * Constructor
     *
     * @param array $profile : Profile parameters
     * @param RestoContext $context
     * @param boolean autoload
     */
    public function __construct($profile, $context, $autoload = false)
    {
        $this->context = $context;

        /*
         * Impossible
         */
        if (!isset($profile) || (!isset($profile['id']) && (!isset($profile['email']) ||  $profile['email'] == 'unregistered'))) {
            $this->profile = $this->unregistered;
        }
        /*
         * Load profile from database is autoload is set to true
         * or if no id is provided
         */
        elseif ($autoload || !isset($profile['id'])) {
            
            if (isset($profile['id'])) {
                $this->profile = (new UsersFunctions($this->context->dbDriver))->getUserProfile('id', $profile['id']);
            } 
            else {
                $this->profile = (new UsersFunctions($this->context->dbDriver))->getUserProfile('email', $profile['email'], array(
                    'password' => $profile['password'] ?? null
                ));
            }
            
            if (!$this->profile) {
                $this->profile = $this->unregistered;
            }

            $this->isComplete = true;
        } else {
            $this->profile = $profile;
        }
    }

    /**
     * Return true if user is validated by admin - false otherwise
     *
     * @return boolean
     */
    public function isValidated()
    {
        $this->loadProfile();
        if (isset($this->profile['id']) && isset($this->profile['validatedby'])) {
            return true;
        }
        return false;
    }

    /**
     * Do user has rights to :
     *   - 'download' feature,
     *   - 'view' feature,
     *   - 'create' collection,
     *   - 'update' collection (i.e. add/delete feature and/or delete collection)
     *
     * @param string $action
     * @param array $params
     * @return boolean
     */
    public function hasRightsTo($action, $params = array())
    {
        switch ($action) {
            case RestoUser::DOWNLOAD:
            case RestoUser::VISUALIZE:
                return $this->hasDownloadOrVisualizeRights($action, $params['collectionId'] ?? null, $params['featureId'] ?? null);
            case RestoUser::CREATE:
                return $this->hasCreateRights();
            case RestoUser::UPDATE:
                if (isset($params['collection'])) {
                    return $this->hasUpdateRights($params['collection']);
                }
                // no break
            default:
                break;
        }
        return false;
    }

    /**
     * Activate user
     */
    public function activate()
    {
        if ((new UsersFunctions($this->context->dbDriver))->activateUser(
                $this->profile['id'],
            $this->context->core['userAutoValidation']
        )) {
            $this->profile['activated'] = 1;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return user orders
     *
     * [IMPORTANT] This function requires Cart add-on
     */
    public function getOrders()
    {
        if (! isset($this->context->addons['Cart'])) {
            RestoLogUtil::httpError(404, 'Cart add-on not installed');
        }
        return (new CartFunctions($this->context->dbDriver))->getOrders($this->profile['id']);
    }

    /**
     * Returns rights
     *
     * @param string $collectionId
     * @param string $featureId
     */
    public function getRights($collectionId = null, $featureId = null)
    {
        $this->loadProfile();

        /*
         * Compute rights if they are not already set
         */
        if (!isset($this->rights)) {
            $this->rights = (new RightsFunctions($this->context->dbDriver))->getRightsForUser($this, null, null);
        }

        /*
         * Return specific rights for feature
         */
        if (isset($collectionId) && isset($featureId)) {
            if (isset($this->rights['features'][$featureId])) {
                return $this->rights['features'][$featureId];
            }
            return $this->getRights($collectionId);
        }

        /*
         * Return specific rights for collection
         */
        if (isset($collectionId)) {
            return $this->rights['collections'][$collectionId] ?? ($this->rights['collections']['*'] ?? $this->fallbackRights);
        }

        /*
         * Return rights for all collections/features
         */
        return $this->rights;
    }

    /**
     * Get followers
     *
     * [IMPORTANT] Requires Social add-on
     */
    public function getFollowers()
    {
        if (! isset($this->context->addons['Social'])) {
            RestoLogUtil::httpError(404, 'Social add-on not installed');
        }
        return (new SocialFunctions($this->context->dbDriver))->getFollowers(array(
            'id' => $this->profile['id']
        ));
    }

    /**
     * Get followings
     */
    public function getFollowings()
    {
        if (! isset($this->context->addons['Social'])) {
            RestoLogUtil::httpError(404, 'Social add-on not installed');
        }
        return (new SocialFunctions($this->context->dbDriver))->getFollowings(array(
            'id' => $this->profile['id']
        ));
    }

    /**
     * Set/update user rights
     *
     * @param array $rights
     * @param string $collectionId
     * @param string $featureId
     * @throws Exception
     */
    public function setRights($rights, $collectionId = null, $featureId = null)
    {
        $this->loadProfile();
        (new RightsFunctions($this->context->dbDriver))->storeOrUpdateRights($this->getRightsArray($rights, $collectionId, $featureId));
        $this->rights = (new RightsFunctions($this->context->dbDriver))->getRightsForUser($this, null, null);
        return true;
    }

    /**
     * Remove user rights
     *
     * @param array $rights
     * @param string $collectionId
     * @param string $featureId
     * @throws Exception
     */
    public function removeRights($rights, $collectionId = null, $featureId = null)
    {
        $this->loadProfile();
        (new RightsFunctions($this->context->dbDriver))->removeRights($this->getRightsArray($rights, $collectionId, $featureId));
        $this->rights = (new RightsFunctions($this->context->dbDriver))->getRightsForUser($this, null, null);
        return true;
    }

    /**
     * Return true if user is in $group
     *
     * @param string $group
     * @throws Exception
     */
    public function hasGroup($group)
    {
        $this->loadProfile();
        return in_array($group, $this->profile['groups']);
    }

    /**
     * Send reset password link to user email adress
     *
     */
    public function sendResetPasswordLink()
    {
        $this->loadProfile();

        /*
         * Only existing local user can change there password
         */
        if (! (new UsersFunctions($this->context->dbDriver))->userExists(array('email' => $this->profile['email']))) {
            RestoLogUtil::httpError(404, 'Email not Found');
        }

        /*
         * User authenticated externally (i.e. google, facebook) cannot change there password
         */
        if ((new UsersFunctions($this->context->dbDriver))->getUserPassword($this->profile['email']) === str_repeat('*', 60)) {
            RestoLogUtil::httpError(400, 'External user');
        }

        /*
         * Send email with reset link
         */
        $token = RestoUtil::encrypt(mt_rand(0, 100000) . microtime());
        
        if (! (new UsersFunctions($this->context->dbDriver))->updateResetToken(
            $this->profile['email'],
            $token
        )) {
            RestoLogUtil::httpError(500);
        }

        if (!(new RestoNotifier($this->context->servicesInfos, $this->context->lang))->sendMailForResetPassword($this->profile['email'], $this->context->core['sendmail'], array(
            'token' => $token
        ))) {
            RestoLogUtil::httpError(500, 'Cannot send reset link');
        }
        
        return RestoLogUtil::success('Reset link sent to ' . $this->profile['email']);
    }

    /**
     * Reload profile from database
     */
    public function loadProfile()
    {
        if (!$this->isComplete && isset($this->profile['id'])) {
            $this->profile = (new UsersFunctions($this->context->dbDriver))->getUserProfile('id', $this->profile['id']);
            $this->isComplete = true;
        }
    }

    /**
     * Can User download or visualize
     *
     * @param string $action
     * @param string $collectionId
     * @param string $featureId
     * @return boolean
     */
    private function hasDownloadOrVisualizeRights($action, $collectionId, $featureId = null)
    {
        $rights = $this->getRights($collectionId, $featureId);
        return $rights[$action];
    }

    /**
     * Can user create collection ?
     *
     * @return boolean
     */
    private function hasCreateRights()
    {
        $rights = $this->getRights();
        return isset($rights['collections']['*']) ? $rights['collections']['*']['create'] : 0;
    }

    /**
     * A user can update a collection if he is the owner of the collection
     * or if he is an admin
     *
     * @param RestoCollection $collection
     * @return boolean
     */
    private function hasUpdateRights($collection)
    {
        if (!$this->hasCreateRights()) {
            return false;
        }

        /*
         * Only collection owner and admin can update the collection
         */
        if (!$this->hasGroup(Resto::GROUP_ADMIN_ID) && $collection->owner !== $this->profile['id']) {
            return false;
        }

        return true;
    }

    /**
     * Return rights array for add/update/delete
     *
     * @param array $rights
     * @param string $collectionId
     * @param string $featureId
     *
     * @return string
     */
    private function getRightsArray($rights, $collectionId, $featureId)
    {

        /*
         * Check that collection/feature exists
         */
        if (isset($collectionId)) {
            if (! (new CollectionsFunctions($this->context->dbDriver))->collectionExists($collectionId)) {
                RestoLogUtil::httpError(404, 'Collection does not exist');
            }
        }
        /*
        if (isset($featureId)) {
            if (! (new FeaturesFunctions($this->context->dbDriver))->featureExists($featureId)) {
                RestoLogUtil::httpError(404, 'Feature does not exist');
            }
        }
        */
        
        return array(
            'rights' => $rights,
            'id' => $this->profile['id'],
            'groupid' => null,
            'collectionId' => $collectionId,
            'featureId' => $featureId
        );
    }

}
