# Users, rights and groups
resto provide a user authentication and authorization mechanism allowing to manage access to ressources in particular to authorize CRUD operations (Create, Read, Update, Delete) on collections and features.

## Users
On the first launch of resto, one user (admin) is created with a user *{userId}* equals to **100**. This user is automatically added to the **admin group** (see chapter on groups below).

### Add a new user
The following example shows how to add a new user. When adding a new user, it will be automatically associated with default rights (see chapter on rights below).

        # Add a new user
        curl -X POST -d@examples/users/johnDoe.json "http://localhost:5252/users"

The above command should returns an HTTP 200 response including the newly create user profile

        {
                "status":"success",
                "message":"User johndoe@localhost created and activated",
                "profile":{
                        "id":"224468756040777732",
                        "email":"johndoe@localhost",
                        "name":"John Doe",
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

* An unique *id* is created for the user 
* The *activated* value set to 1. This means that the user is created and validated i.e. you can use authenticate with this user within resto. If you want to check for email address before allowing user to authenticate to resto, you have to set the **USER_AUTOVALIDATION** environment value to *false* in [config.env](./config.env). In this case, the user will receive an email including a validation link. The *activated* value will be set to 1 upon user's validation link resolution.

### Get an authorization token (optional)
To authenticate to resto endpoint, you can either provide the email/password of an existing user or an authentication token.

You can generate a bearer authentication token valid for 100 days for the above user with the following command:

        ./scripts/generateAuthToken -i 224468756040777732 -d 100

The result should be:

        {"userId":"224468756040777732","duration":100,"valid_until":"2024-07-17T09:41:49","token":"eyJzdWIiOiIyMjQ0Njg3NTYwNDA3Nzc3MzIiLCJpYXQiOjE3MTI1NjIxMDksImV4cCI6MTcyMTIwMjEwOX0.XatRV4bLbuRyvsQrL2etPAumpPPg5SK2h-7qVRrPub4"}

The token can be used to request authenticated endpoint, for instance to get the user profile:

        # Using email/password
        curl "http://johnDoe%40localhost:dummy@localhost:5252/users/224468756040777732"

        # Using bearer token
        export JOHN_DOE_BEARER=eyJzdWIiOiIyMjQ0Njg3NTYwNDA3Nzc3MzIiLCJpYXQiOjE3MTI1NjIxMDksImV4cCI6MTcyMTIwMjEwOX0.XatRV4bLbuRyvsQrL2etPAumpPPg5SK2h-7qVRrPub4
        curl -H "Authorization: Bearer ${JOHN_DOE_BEARER}" "http://localhost:5252/users/224468756040777732"

## Rights
The rights defines access to resto ressources in particular to authorize CRUD operations (Create, Read, Update, Delete) on collections and features.

rights are defined as boolean properties within a JSON object. The default user's rights are the following:

        {
                // If true the user can create a collection
                "createCollection": false,

                // If true the user can delete a collection he owns
                "deleteCollection": true,

                // If true the user can update a collection he owns
                "updateCollection": true,

                // If true the user can delete any collection whether he owns it or not
                "deleteAnyCollection": false,

                // If true the user can update any collection whether he owns it or not
                "updateAnyCollection": false,
                
                // If true the user can create a catalog
                "createCatalog": true,

                // If true the user can delete a catalog he owns
                "deleteCatalog": true,

                // If true the user can update a catalog he owns
                "updateCatalog": true,

                // If true the user can delete any catalog whether he owns it or not
                "deleteAnyCatalog": false,

                // If true the user can update any catalog whether he owns it or not
                "updateAnyCatalog": false,

                // If true the user can add a feature to a collection owns
                "createFeature": true,

                // If true the user can delete a feature he owns
                "deleteFeature": true,

                // If true the user can update a feature he owns
                "updateFeature": true,
                
                // If true the user can add a feature to any collection whether he owns it or not
                "createAnyFeature": false,

                // If true the user can delete any feature whether he owns it or not
                "deleteAnyFeature": false,

                // If true the user can update any feature whether he owns it or not
                "updateAnyFeature": false

        }

### Get user rights
To get the rights for John Doe:

        curl -H "Authorization: Bearer ${JOHN_DOE_BEARER}" "http://localhost:5252/users/224468756040777732/rights"

The result should be :

        {"rights":{"createCollection":false,"deleteCollection":true,"updateCollection":true,"deleteAnyCollection":false,"updateAnyCollection":false,"createFeature":true,"updateFeature":true,"deleteFeature":true,"createAnyFeature":false,"deleteAnyFeature":false,"updateAnyFeature":false,"downloadFeature":false}}

### Set user rights
Only a user in the **admin group** (see chapter on groups below) can set the rights of a user.

        # Allow John Doe to create collection
        curl -X POST -d@examples/users/johnDoe_rights.json "http://admin:admin@localhost:5252/users/224468756040777732/rights"

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
        curl -X POST -d@examples/users/dummyGroup.json "http://johnDoe%40localhost:dummy@localhost:5252/groups"

The result should be

        {"status":"success","message":"Group created","id":103,"name":"My first group","owner":"224468756040777732"}

### Set group rights
Only a user in the **admin group** can set the rights for a group

        # Set rights for dummy group allowing members to createAnyFeature
        curl -X POST -d@examples/users/dummyGroup_rights.json "http://admin:admin@localhost:5252/groups/103/rights"

The result should returns :

        {"status":"success","message":"Rights set","rights":{"createAnyFeature":true}}

Note that existing rights are not deleted when setting rights but are merged with input rights.

### Add user to a group
Only a user in the **admin group** or the owner of the group can add user to a group

        # Add John Doe in group dummyGroup
        curl -X POST -d@examples/users/dummyGroup_addJohnDoe.json "http://admin:admin@localhost:5252/groups/103/users"

        # Consequently, John Doe's rights now includes rights from its groups
        curl -H "Authorization: Bearer ${JOHN_DOE_BEARER}" "http://localhost:5252/users/224468756040777732/rights"

        # Result of previous request shows that John Doe can now createAnyFeature since he is in dummyGroup
        # {"rights":{"createCollection":true,"deleteCollection":true,"updateCollection":true,"deleteAnyCollection":false,"updateAnyCollection":false,"createFeature":true,"updateFeature":true,"deleteFeature":true,"createAnyFeature":true,"deleteAnyFeature":false,"updateAnyFeature":false,"downloadFeature":false}

### Remove user from a group
Only a user in the **admin group** or the owner of the group can remove a user from a group

        # Remove John Doe from dummyGroup
        curl -X DELETE "http://admin:admin@localhost:5252/groups/103/users/224468756040777732"

        # Consequently, John Doe's rights do not include anymore rights from dummyGroup
        curl -H "Authorization: Bearer ${JOHN_DOE_BEARER}" "http://localhost:5252/users/224468756040777732/rights"

        # {"rights":{"createCollection":true,"deleteCollection":true,"updateCollection":true,"deleteAnyCollection":false,"updateAnyCollection":false,"createFeature":true,"updateFeature":true,"deleteFeature":true,"createAnyFeature":false,"deleteAnyFeature":false,"updateAnyFeature":false,"downloadFeature":false}

## Ownership and visibility
