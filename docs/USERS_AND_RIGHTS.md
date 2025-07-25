# Users, rights and groups
resto provide a user authentication and authorization mechanism allowing to manage access to ressources in particular to authorize CRUD operations (Create, Read, Update, Delete) on collections, catalogs and items.

## Users
On the first launch of resto, one user (admin) is created. This user is automatically added to the **admin group** (see chapter on groups below).

### Add a new user
The following example shows how to add a new user. When adding a new user, it will be automatically associated with default rights (see chapter on rights below).

        # Add a new user
        curl -X POST -d@examples/users/johnDoe.json "http://localhost:5252/users"

The above command should returns an HTTP 200 response including the newly create user profile

        {
                "status":"success",
                "message":"User johndoe created and activated",
                "profile":{
                        "id":"227212962406176319",
                        "username":"johndoe",
                        "email":"johndoe@localhost",
                        "firstname":"John",
                        "lastname":"Doe",
                        "lang":"en",
                        "topics":null,
                        "picture":"https://robohash.org/a6b506f3dae99e4c35ae50ae240e8f5d?gravatar=hashed&bgset=any&size=400x400",
                        "registrationdate":"2024-04-08 07:18:19.77169",
                        "activated":1,
                        "followers":0,
                        "followings":0
                }
        }

Notes :

* The *activated* value set to 1. This means that the user is created and validated i.e. you can use authenticate with this user within resto. If you want to check for email address before allowing user to authenticate to resto, you have to set the **USER_AUTOVALIDATION** environment value to *false* in [config.env](./config.env). In this case, the user will receive an email including a validation link. The *activated* value will be set to 1 upon user's validation link resolution.

### Update user profile
Updating user profile can be done either by administrator or by the user itself:

        # Update johndoe bio with John Doe credentials
        curl -X PUT -d@examples/users/johnDoe_update.json "http://johndoe:dummy@localhost:5252/users/johndoe"

### Get user profile
First create another user Jane Doe:

        curl -X POST -d@examples/users/janeDoe.json "http://localhost:5252/users"

User profiles can only be viewed by authenticated user 
        
        # John Doe ask for Jane Doe profile 
        curl "http://johndoe:dummy@localhost:5252/users/janedoe"

User can get its own profile directly using the shorcut /me

        # John Doe get its own profile
        curl "http://johndoe:dummy@localhost:5252/me"

*Note: when requesting the profile of another user (e.g. John Doe requesting Jane Doe profile), only a limited set of the profile properties are returned. These properties are defined in the user settings*

### Get an authorization token (optional)
To authenticate to resto endpoint, you can either provide the email/password of an existing user or an authentication token.

You can generate a bearer authentication token valid for 100 days for the above user with the following command:

        ./scripts/generateAuthToken -i johndoe -d 100

The result should be:

        {"username":"johndoe","duration":100,"valid_until":"2024-07-17T09:41:49","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJqb2huZG9lIiwiaWF0IjoxNzM0MTAxNDI2LCJleHAiOjE3NDI3NDE0MjZ9.erUUOUBlbjtXswP7suvyzLmUgcR9y_qib1CfDS1m-ds"}

The token can be used to request authenticated endpoint, for instance to get the user profile:

        # Using username
        curl "http://johndoe:dummy@localhost:5252/me"

        # Using bearer token
        export JOHN_DOE_BEARER=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJqb2huZG9lIiwiaWF0IjoxNzM0MTAxNDI2LCJleHAiOjE3NDI3NDE0MjZ9.erUUOUBlbjtXswP7suvyzLmUgcR9y_qib1CfDS1m-ds
        curl -H "Authorization: Bearer ${JOHN_DOE_BEARER}" "http://localhost:5252/me"

## Rights
The rights defines access to resto ressources in particular to authorize CRUD operations (Create, Read, Update, Delete) on collections, catalogs and items.

rights are defined as boolean properties within a JSON object. The default user's rights are the following:

        {
                // If true the user can create a collection under /collections
                "createCollection": false,

                // If true the user can delete any collection whether he owns it or not
                "deleteAnyCollection": false,

                // If true the user can update any collection whether he owns it or not
                "updateAnyCollection": false,
                
                // If true the user can create a catalog under /catalogs/projects
                "createCatalog": true,

                // If true the user can create a catalog anywhere (except in another user private catalog)
                "createAnyCatalog": false,

                // If true the user can delete any catalog whether he owns it or not
                "deleteAnyCatalog": false,

                // If true the user can update any catalog whether he owns it or not
                "updateAnyCatalog": false,

                // If true the user can add an item to any collection whether he owns it or not
                "createAnyItem": false,

                // If true the user can delete any item whether he owns it or not
                "deleteAnyItem": false,

                // If true the user can update any item whether he owns it or not
                "updateAnyItem": false

        }

### Get user rights
To get the rights for John Doe:

        curl "http://johndoe:dummy@localhost:5252/users/johndoe/rights?_pretty=1"

The result should be :

        {
                "rights": {
                        "createCollection": false,
                        "deleteAnyCollection": false,
                        "updateAnyCollection": false,
                        "createCatalog": false,
                        "createAnyCatalog": false,
                        "deleteAnyCatalog": false,
                        "updateAnyCatalog": false,
                        "createFeature": false,
                        "createAnyFeature": false,
                        "deleteAnyFeature": false,
                        "updateAnyFeature": false,
                        "catalogs":{}
                }
        }

### Set user rights
Only a user in the **admin group** (see chapter on groups below) can set the rights of a user.

        # Allow John Doe to create catalog under /catalogs/projects
        curl -X POST -d@examples/users/johnDoe_rights.json "http://admin:admin@localhost:5252/users/johndoe/rights"

The result should returns :

        {"status":"success","message":"Rights set","rights":{"createCollection":true}}

Note that existing rights are not deleted when setting rights but are merged with input rights.

## Groups
groups can be used to share rights among group members.

On the first launch of resto, two groups are created :

* The *admin group* identified by id **0**.
* The *default group* identifier by id **100**

All users are automatically added to the default group

### Add a group
Any user can add a group. Note that the group name must be unique.

        # Create dummy group
        curl -X POST -d@examples/users/dummyGroup.json "http://johndoe:dummy@localhost:5252/groups"

The result should be

        {"status":"success","message":"Group created","id":1000,"name":"My first group","owner":"${JOHN_DOE_USER_ID}"}

*Note: When a user create a group, the user is automaticaly added to this group (except if the user is an admin)*

### Set group rights
Only a user in the **admin group** can set the rights for a group

        # Set rights for dummy group allowing members to createAnyItem
        curl -X POST -d@examples/users/dummyGroup_rights.json "http://admin:admin@localhost:5252/groups/My%20first%20group/rights"

The result should returns :

        {"status":"success","message":"Rights set","rights":{"createAnyItem":true,"createCollection":true}}

Note that existing rights are not deleted when setting rights but are merged with input rights.

### Add user to a group
Only a user in the **admin group** or the owner of the group can add user to a group

        # As the owner of group dummyGroup, JohnDoe can add JaneDoe in the group
        curl -X POST "http://johndoe:dummy@localhost:5252/groups/My%20first%20group/users" -d '{"username":"janedoe"}'
        
        # Consequently, Jane Doe's rights now includes rights from its groups
        curl "http://janedoe:dummy@localhost:5252/users/janedoe/rights"

        # Result of previous request shows that JaneDoe can now createAnyItem and createCollection
        {
                "rights": {
                        "createCollection": true,
                        "deleteAnyCollection": false,
                        "updateAnyCollection": false,
                        "createCatalog": false,
                        "createAnyCatalog": false,
                        "deleteAnyCatalog": false,
                        "updateAnyCatalog": false,
                        "createFeature": false,
                        "createAnyFeature": false,
                        "deleteAnyFeature": false,
                        "updateAnyFeature": false,
                        "createAnyItem": true
                }
        }

### Remove user from a group
Only a user in the **admin group** or the owner of the group can remove a user from a group

        # Remove John Doe from dummyGroup
        curl -X DELETE "http://admin:admin@localhost:5252/groups/My%20first%20group/users/johndoe"

        # Consequently, John Doe's rights do not include anymore rights from dummyGroup
        curl -H "Authorization: Bearer ${JOHN_DOE_BEARER}" "http://localhost:5252/users/johndoe/rights"

        # Results
        {
                "rights": {
                        "createCollection": false,
                        "deleteAnyCollection": false,
                        "updateAnyCollection": false,
                        "createCatalog": true,
                        "createAnyCatalog": false,
                        "deleteAnyCatalog": false,
                        "updateAnyCatalog": false,
                        "createFeature": false,
                        "createAnyFeature": false,
                        "deleteAnyFeature": false,
                        "updateAnyFeature": false,
                        "createAnyItem": false,
                        "catalogs":{}
                }
        }

## Ownership and visibility
### Ownership
The following resources have an ownership i.e. they **belong to a user**:
* item
* catalog
* collection
* group

An owned resource can only be updated and deleted by its owner or by a user with a *Any* right (e.g. updateAnyCollection or deleteAnyCatalog). See the rights section for more detailed information.

### Visibility
The following resources have a visibility status:
* item
* catalog
* collection

The visibility property is an array of group names. For a given resource, only user belonging to one of the group within the resource visibility array can see it.

Unless specified, every resource is visible by every user (i.e. its visibility property is set by default to an array containing the *default group*).

#### Set up group and user to play with visibility
First create John Doe user if not exist then create a group and add John Doe to this group:

        # John Doe register
        curl -X POST -d@examples/users/johnDoe.json "http://localhost:5252/users"

        # Admin create a dummy group
        curl -X POST -d@examples/users/dummyGroup.json "http://admin:admin@localhost:5252/groups"

        # Admin add John Doe in group dummyGroup
        curl -X POST "http://admin:admin@localhost:5252/groups/My%20first%20group/users" -d '{"username":"johndoe"}'

        # Admin allows John Doe to create collection
        curl -X POST -d@examples/users/johnDoe_rights_collection.json "http://admin:admin@localhost:5252/users/johndoe/rights"

#### Update an item to make it visible only to a group
John Doe is in group dummyGroup and has right to create a collection:

        # John Doe Create collection
        curl -X POST -d@examples/collections/JohnDoeCollection.json "http://johndoe:dummy@localhost:5252/collections"

        # And add an item to this collection
        curl -X POST -d@examples/items/johnDoeItem.json "http://johndoe:dummy@localhost:5252/collections/JohnDoeCollection/items"

        # This item is visible by everyone
        curl "http://localhost:5252/collections/JohnDoeCollection/items"

Now John Doe change the visibility of the item to dummyGroup only:

        curl -X PUT -d@examples/items/johnDoeItem_visibility.json "http://johndoe:dummy@localhost:5252/collections/JohnDoeCollection/items/JohnDoeItem/properties"

        # The item is not visible anymore to users
        curl "http://localhost:5252/collections/JohnDoeCollection/items"

        # Except to users belonging to dummyGroup (like John Doe)
        curl "http://johndoe:dummy@localhost:5252/collections/JohnDoeCollection/items"

#### Update a collection to make it visible only to a group
John Doe change the visibility of the JohnDoeCollection to dummyGroup only:

        curl -X PUT -d@examples/collections/JohnDoeCollection_update.json "http://johndoe:dummy@localhost:5252/collections/JohnDoeCollection"

        # The collection is not visible anymore to users
        curl "http://localhost:5252/collections/JohnDoeCollection"

        # Except to users belonging to dummyGroup (like John Doe)
        curl "http://johndoe:dummy@localhost:5252/collections/JohnDoeCollection"

*Note: The collection visibility can also be set during collection creation by adding the "visibility" property to the collection json description*

#### Create a catalog to make it visible only to a group
John Doe creates a catalog that is only visible by dummyGroup:

        curl -X POST -d@examples/catalogs/johnDoeCatalog.json "http://johndoe:dummy@localhost:5252/catalogs/users/johndoe"

        # The catalog is not visible to users
        curl "http://localhost:5252/catalogs/users/johndoe"

        # Except to users belonging to dummyGroup (like John Doe)
        curl "http://johndoe:dummy@localhost:5252/catalogs/users/johndoe"

#### Update a catalog to make it visible for everyone
John Doe change visibility to default group so everyone can see it:

        curl -X PUT -d@examples/catalogs/johnDoeCatalog_update.json "http://johndoe:dummy@localhost:5252/catalogs/users/johndoe/JohnDoeCatalog"

#### Allow user to create a catalog under another catalog it does not own
John Doe has "createCatalog" right, so he can create a catalog under /catalogs/projects

        curl -X POST -d@examples/catalogs/johnDoeCatalog.json "http://johndoe:dummy@localhost:5252/catalogs/projects"

Jane Doe cannot create a catalog under JohnDoeCatalog because she has no rights to do so

        # Result is {"ErrorMessage":"addCatalog - Forbidden","ErrorCode":403}
        curl -X POST -d@examples/catalogs/janeDoeCatalog.json "http://janedoe:dummy@localhost:5252/catalogs/projects/JohnDoeCatalog"
        
As the owner of JohnDoeCatalog, John Doe can give "createCatalog" rights to Jane Doe to allow to create catalog under JohnDoeCatalog:

        # Allow John Doe to create catalog under /catalogs/projects
        curl -X PUT -d@examples/users/janeDoe_johnDoeCatalog_rights.json "http://johndoe:dummy@localhost:5252/users/janedoe/rights/catalogs"
        
Now Jane Doe can create a catalog under JohnDoeCatalog

        # Result is {"ErrorMessage":"addCatalog - Forbidden","ErrorCode":403}
        curl -X POST -d@examples/catalogs/janeDoeCatalog.json "http://janedoe:dummy@localhost:5252/catalogs/projects/JohnDoeCatalog"
        