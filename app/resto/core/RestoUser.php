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

    const CREATE_GROUP = 'createGroup';
    
    const CREATE_COLLECTION = 'createCollection';
    const DELETE_COLLECTION = 'deleteCollection';
    const UPDATE_COLLECTION = 'updateCollection';
    
    const DELETE_ANY_COLLECTION = 'deleteAnyCollection';
    const UPDATE_ANY_COLLECTION = 'updateAnyCollection';
    
    const CREATE_CATALOG = 'createCatalog';
    const DELETE_CATALOG = 'deleteCatalog';
    const UPDATE_CATALOG = 'updateCatalog';
    
    const CREATE_ANY_CATALOG = 'createAnyCatalog';
    const DELETE_ANY_CATALOG = 'deleteAnyCatalog';
    const UPDATE_ANY_CATALOG = 'updateAnyCatalog';
    
    const CREATE_ITEM = 'createFeature';
    const DELETE_ITEM = 'deleteFeature';
    const UPDATE_ITEM = 'updateFeature';
    
    const CREATE_ANY_ITEM = 'createAnyFeature';
    const DELETE_ANY_ITEM = 'deleteAnyFeature';
    const UPDATE_ANY_ITEM = 'updateAnyFeature';
    
    const DOWNLOAD_ITEM = 'downloadFeature';

    // Each user has a private group named {username}_USER_GROUP_SUFFIX
    const USER_GROUP_SUFFIX = '_private';

    /**
     * User profile
     *
     * @OA\Schema(
     *  schema="UserDisplayProfile",
     *  required={"id", "picture", "groups", "username", "followers", "followings"},
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
     *      property="username",
     *      type="string",
     *      description="User name"
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
     *      "username": "jrom",
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
     * Reference to groups object
     */
    private $groups;
    private $ownedGroups;

    /*
     * Unregistered profile
     */
    private $unregistered = array(
        'id' => null,
        'username' => null,
        'email' => 'unregistered',
        'activated' => 0,
        'myGroup' => null
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
     */
    public function __construct($profile, $context)
    {
        $this->context = $context;

        /*
         * Impossible
         */
        if (!isset($profile) || (!isset($profile['username']) && (!isset($profile['email']) ||  $profile['email'] == 'unregistered'))) {
            $this->profile = $this->unregistered;
        }
        /*
         * 
         */
        else {

            $target = isset($profile['username']) ? array('username', $profile['username']) : array('email', $profile['email']);

            if ( array_key_exists('password', $profile) ) {
                $this->profile = (new UsersFunctions($this->context->dbDriver))->getUserProfile($target[0], $target[1], array(
                    'password' => $profile['password'] ?? null
                ));
            }
            else {
                $this->profile = (new UsersFunctions($this->context->dbDriver))->getUserProfile($target[0], $target[1]);
            }
            
            if (!$this->profile) {
                $this->profile = $this->unregistered;
            }

            $this->isComplete = true;
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
     * User has explicit and implicit rights.
     * 
     * Explicit rights are defined by a boolean property :
     * 
     *  - createGroup           : if true, user can create a group
     *  - createCollection      : if true, user can create a collection under /collections
     *  - createCatalog         : if true, user can create a catalog under /catalogs/projects
     *  - createItem            : if true, user can create an item under any /collections/{collectionId}/items
     * 
     * 
     * Implicit rights are associated to every user without specific property: 
     *
     *  - User can create an item within a collection he owns
     *  - User can update any group / collection / catalog / item he owns
     *  - User can delete any group / collection / catalog / item he owns
     * 
     * 
     * 
     * 
     *  - deleteAnyCollection   : delete any collection i.e. including not owned by user
     *  - updateAnyCollection   : update any collection i.e. including not owned by user
     * 
     *  
     *
     *  - createAnyCatalog      : create a catalog anywhere except under another user private catalog
     *  - deleteAnyCatalog      : delete any catalog i.e. including not owned by user
     *  - updateAnyCatalog      : update any catalog i.e. including not owned by user
     * 
     *  - createItem            : create an item in a collection owned by user
     *  - deleteItem            : delete an item owned by user
     *  - updateItem            : update an item owned by user
     * 
     *  - createAnyItem         : create an item in any collection
     *  - deleteAnyItem         : delete any item i.e. including not owned by user
     *  - updateAnyItem         : update any item i.e. including not owned by user
     *
     * @param string $action
     * @param array $params
     * @return boolean
     */
    public function hasRightsTo($action, $params = array())
    {

        $rights = $this->getRights();
        
        /*
         * 1) Handle actions that are not known
         * and actions that do not need params to be set
         */
        /*$withParams = array(
            RestoUser::CREATE_GROUP,
            RestoUser::CREATE_CATALOG,
            RestoUser::CREATE_ITEM
        );
        if ( !in_array($action, $withParams) ) {
            return $rights[$action] ?? false;
        }*/
        
        /* 
         * Split camel case action into parts
         * The first token is the action (create, update, delete)
         * The last token is the target (collection, catalog, item)
         */
        $splittedAction = preg_split('/(?<=[a-z])(?=[A-Z])/x', $action);
        if ( count($splittedAction) === 2 ) {

            // 2) Handle "Any" cases
            $any = $splittedAction[0] . 'Any' . $splittedAction[1];
            if ( isset($rights[$any]) && $rights[$any] ) {
                return $this->handleAnyRights($action, $params);
            }

        }

        // 3) Handle action with params
        return $this->handleSimpleRights($action, $rights, $params);

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
        $cartFunctionsClassName = 'CartFunctions';
        return (new $cartFunctionsClassName($this->context->dbDriver))->getOrders($this->profile['id']);
    }

    /**
     * Returns rights
     */
    public function getRights()
    {

        $this->loadProfile();

        /*
         * Compute rights if they are not already set
         */
        if ( !isset($this->rights) ) {
            $this->rights = (new RightsFunctions($this->context->dbDriver))->getRightsForUser($this);
        }

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
        $socialFunctionsClassName = 'SocialFunctions';
        return (new $socialFunctionsClassName($this->context->dbDriver))->getFollowers(array(
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
        $socialFunctionsClassName = 'SocialFunctions';
        return (new $socialFunctionsClassName($this->context->dbDriver))->getFollowings(array(
            'id' => $this->profile['id']
        ));
    }

    /**
     * Return the list of groups the user belongs too
     *
     * @throws Exception
     */
    public function getGroups()
    {
        if ( !$this->profile['id'] ) {
            return array();
        }
        
        if ( !isset($this->groups) ) {
            $this->groups = (new GroupsFunctions($this->context->dbDriver))->getGroups(array('userid' => $this->profile['id']));
        }
        return $this->groups;
    }   

    /**
     * Return the list of groups own by the user
     *
     * @throws Exception
     */
    public function getOwnedGroups()
    {
        if ( !$this->profile['id'] ) {
            return array();
        }

        if ( !isset($this->ownedGroups) ) {
            $this->ownedGroups = (new GroupsFunctions($this->context->dbDriver))->getGroups(array('owner' => $this->profile['id']));
        }
        return $this->ownedGroups;
    }   

    /**
     * Return the list of user group ids
     *
     * @throws Exception
     */
    public function getGroupIds()
    {
        $groups = $this->getGroups();

        // Everybody is in the default RestoConstants::GROUP_DEFAULT_ID;
        $ids = [
            RestoConstants::GROUP_DEFAULT_ID
        ];

        for ($i = 0, $ii = count($groups); $i < $ii; $i++) {
            if ( $groups[$i]['id'] !== RestoConstants::GROUP_DEFAULT_ID ) {
                $ids[] = $groups[$i]['id'];
            }
        }

        return $ids;
    }

    /**
     * Return true if user is in $group
     *
     * @param string $group
     * @throws Exception
     */
    public function hasGroup($group)
    {
        return in_array($group, $this->getGroupIds());
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
        if ((new UsersFunctions($this->context->dbDriver))->userActivatedStatus(array('email' => $this->profile['email'])) !== 1) {
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
        if ( !$this->isComplete && isset($this->profile['username']) ) {
            $this->profile = (new UsersFunctions($this->context->dbDriver))->getUserProfile('username', $this->profile['username']);
            $this->isComplete = true;
        }
    }

    /**
     * Handle any rights
     * 
     * @param string $action
     * @param array $params
     * @return boolean
     */
    private function handleAnyRights($action, $params)
    {

        // Only catalog actions are special cases
        if ( !in_array($action, array(
                RestoUser::CREATE_CATALOG,
                RestoUser::DELETE_CATALOG,
                RestoUser::UPDATE_CATALOG
            ))) {
            return true;
        }

        if ( !isset($params['catalog']) ) {
            return false;
        }

        /*
         * Check forbidden paths
         */
        switch ($action) {
            case RestoUser::CREATE_CATALOG:
            case RestoUser::DELETE_CATALOG:
            case RestoUser::UPDATE_CATALOG:
                if ( in_array($params['catalog']['id'], STACUtil::RESERVED_CATALOG_PATHS) ) {
                    RestoLogUtil::httpError(400, 'You cannot create/delete/update the /catalogs/' . $params['catalog']['id'] . ' as it is a reserved path');
                }
                break;

        }

        // You cannot delete /users/* catalogs
        $exploded = explode('/', $params['catalog']['id']);
        if ( count($exploded) > 1 && $exploded[0] === 'users' ) {
            return false;
        }

        return true;

    }

    /**
     * Handle simple rights
     * 
     * @param string $action
     * @param array $rights
     * @param array $params
     * @return boolean
     */
    private function handleSimpleRights($action, $rights, $params)
    {
        switch ($action) {

            case RestoUser::CREATE_CATALOG:
                return $this->hasRightsToCreateCatalog($rights, $params);

            case RestoUser::CREATE_ITEM:

            // Owner of a collection can update/delete it
            case RestoUser::DELETE_COLLECTION:
            case RestoUser::UPDATE_COLLECTION:
                return isset($params['collection']) && $params['collection']->owner === $this->profile['id'];
            
            // Owner of a catalog can update/delete it
            case RestoUser::UPDATE_CATALOG:
                return isset($params['catalog']) && $params['catalog']['owner'] === $this->profile['id'];

            case RestoUser::DELETE_CATALOG:
                if ( !isset($params['catalog']) ) {
                    return false;
                }
                $exploded = explode('/', $params['catalog']['id']);
                if ( count($exploded) === 2 && $exploded[0] === 'users' ) {
                    return false;
                }
                return isset($params['catalog']) && $params['catalog']['owner'] === $this->profile['id'];

            // Owner of an item can update/delete it
            case RestoUser::DELETE_ITEM:
            case RestoUser::UPDATE_ITEM:
                if ( !isset($params['item']) ) {
                    return false;     
                }
                $featureArray = $params['item']->toArray();
                return isset($featureArray['properties']['owner']) && $featureArray['properties']['owner'] === $this->profile['id'];
                    
            default:
                return $rights[$action] ?? false;
        }
    }

    /**
     * User can create a catalog under /users/{username}
     * User with createCatalog can only create catalog under /catalogs/projects
     * 
     * @param array $rights
     * @param array $params
     * @return boolean
     */
    private function hasRightsToCreateCatalog($rights, $params) {
        
        if ( !isset($params['catalog']) ) {
            return false;
        }
        $exploded = explode('/', $params['catalog']['id']);
        
        // Always true under /catalogs/{username}
        if ( count($exploded) >= 3 && $exploded[0] === 'users' && $exploded[1] === $this->profile['username'] ) {
            return true;
        }

        // Always false if not under /catalogs/projects 
        if ( count($exploded) < 2 || $exploded[0] !== 'projects' ) {
            return false;
        }

        // This is /catalogs/projects/something
        if ( count($exploded) === 2 ) {
            return $rights[RestoUser::CREATE_CATALOG];
        }
        // This is /catalogs/projects/something/somethingele/...
        else if ( isset($rights['catalogs']) ) {
            foreach ($rights['catalogs'] as $key => $value) {
                if ( str_starts_with($params['catalog']['id'], $key) ) {
                    return $rights['catalogs'][$key][RestoUser::CREATE_CATALOG] ?? false;
                }
            }
        }
        
        return false;

    }

}
