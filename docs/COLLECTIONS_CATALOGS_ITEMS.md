# Collections, catalogs and items
The aim of resto is to store spatiotemporal *items*. These items are stored within *collections*.
Each item must belong to one and only one *collection*. Usually a *collection* is made of homogeneous *items* (e.g. the Sentinel-2 collection contains Sentinel-2 images).

However, the *collection->item* can be restrictive. Thus, items can also be linked to *catalogs*. A *catalog* is a simple, flexible JSON file of links that provides a structure to organize and browse items. For instance the "Flood in the South of France 2019-11" *catalog* could contains items that are related to this event but coming from very different *collections*

![Collections and catalogs](./stac_catalog.png)

**[IMPORTANT]** RESTO IS FOLLOWING THE OGC COLLECTION/FEATURE CONVENTION NAMING. SO ANY TIME YOU SEE "FEATURE" IN THE DOCUMENTATION OR IN THE CODE YOU CAN REPLACE IT BY "ITEM". IT'S EXACTLY THE SAME !

## Collections
**A collection must have a unique id.**

Eventually, it can contain an array of *aliases* (see [./examples/collections/L8.json](./examples/collections/L8.json#L3-L5) for instance). These aliases are alternate names to the collection id. Thus {collectionId} value in /collections/{collectionId}/* endpoints can use the original collection id or one of its aliases.

Note that id and aliases must be unique in the database. As a consequence, you cannot create a new collection or set an alias to an existing collection that as the same value of one of the aliases of an existing collection.

### Add a collection
To add a collection using the default **ADMIN_USER_NAME** and **ADMIN_USER_PASSWORD** (see [config.env](config.env)) :

        # POST a dummy collection
        curl -X POST -d@examples/collections/DummyCollection.json "http://admin:admin@localhost:5252/collections"

Then get the collections list :

        curl "http://localhost:5252/collections"

*Note: Any user with the "createCollection" right can create a collection ([see rights](./USERS.md))*

### Update a collection
To update a collection

        # UPDATE dummy collection
        curl -X PUT -d@examples/collections/DummyCollection_update.json "http://admin:admin@localhost:5252/collections/DummyCollection"

### Ingest an item
To ingest an item using the default **ADMIN_USER_NAME** and **ADMIN_USER_PASSWORD** (see [config.env](config.env)) :

        # POST a Dummy1 item inside the DummyCollection collection
        curl -X POST -d@examples/items/dummy1.json "http://admin:admin@localhost:5252/collections/DummyCollection/items"

        # Update a dummy item inside the DummyCollection collection
        curl -X PUT -d@examples/items/dummy1_update.json "http://admin:admin@localhost:5252/collections/DummyCollection/items/Dummy1"

Then get the item :

        curl "http://localhost:5252/collections/DummyCollection/items/Dummy1"

*Note: Any user with the "createItem" right can insert an item to a collection he owns ([see rights](./USERS.md))*

## Catalogs

### Add a catalog
User with catalog creation right can create a catalog i.e.:
* Any user has right to create catalog within its private space i.e. under /catalogs/users/{username}
* The "createCatalog" right allows user to create catalog under /catalogs/projects
* The "createAnyCatalog" right allows user to create catalog anywhere **except the private space of another user**

For instance, user "johndoe" has no particular right and thus can create a catalog only under its private space:

        curl -X POST -d@examples/catalogs/dummyCatalog.json "http://johndoe:dummy@localhost:5252/catalogs/users/johndoe"

Admin user has the "createAnyCatalog" right and can create a catalog anywhere:

        curl -X POST -d@examples/catalogs/dummyCatalog.json "http://admin:admin@localhost:5252/catalogs/projects"

### Reserved catalog names
The following paths are reserved and cannot be created by a user whatever its rights:
* /catalogs/collections
* /catalogs/projects
* /catalogs/users

#### Catalog /catalogs/users
No user can create a catalog directly under /catalogs/users catalog neither can create a catalog under a another user catalog.
For instance "johndoe" user cannot create a catalog under /catalog/users/janedoe even if he get the "createAnyCatalog" right

### Add a catalog under an existing catalog
**[IMPORTANT]** You cannot create a catalog with childs in links because a child cannot exist before its parent. The good way
is to create first an empty catalog (i.e. the parent) then add its childs through the POST API

        # First admin gives JohnDoe the right to createCatalog under /collections/projects
        curl -X POST -d@examples/users/johnDoe_rights.json "http://admin:admin@localhost:5252/users/johndoe/rights"

        # This will raise an error because the catalog references childs that do not exist.
        curl -X POST -d@examples/catalogs/dummyCatalogWithChilds_invalid.json "http://johndoe:dummy@localhost:5252/catalogs/projects"

        # Good way: ingest parent without childs then add childs under parent
        curl -X POST -d@examples/catalogs/dummyCatalogWithChilds_valid.json "http://admin:admin@localhost:5252/catalogs/projects"
        curl -X POST -d@examples/catalogs/dummyCatalogChild1.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalogWithChilds"
        curl -X POST -d@examples/catalogs/dummyCatalogChild2.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalogWithChilds"

### Add a collection under an existing catalog

        curl -X POST -d@examples/collections/DummyCollection.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalogWithChilds"

### Add a catalog under an existing catalog that cycle on itself

        curl -X POST -d@examples/catalogs/dummyCatalogChild1.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalog"
        curl -X POST -d@examples/catalogs/dummyCatalogChildOfChild1.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalog/dummyCatalogChild1"

        # Forbidden : dummyCatalogCycling posted under /catalogs/dummyCatalogChild1 but reference its parent as a child
        curl -X POST -d@examples/catalogs/dummyCatalogCycling.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalog/dummyCatalogChild1"

### Add a catalog with item

        # The catalog dummyCatalogWithItem is posted under /catalogs/dummyCatalogWithChilds/dummyCatalogChild1
        # It references :
        #   * a local item that is added to catalog_feature table
        #   * an external item that is kept as referenced within the links column in catalog table

        # First POST the item because it should exist before referencing it within the catalog
        curl -X POST -d@examples/items/dummySargasse.json "http://admin:admin@localhost:5252/collections/DummyCollection/items"

        curl -X POST -d@examples/catalogs/dummyCatalogWithItem.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalogWithChilds/dummyCatalogChild1"

### Add an external catalog
If you POST a catalog with a rel="root" link pointing to an external STAC href, then the resto will act as a proxy to this catalog

        # POST the CDSE STAC catalog endpoint
        curl -X POST -d@examples/catalogs/externalCatalog.json "http://admin:admin@localhost:5252/catalogs/projects"

        # Now CDSE catalogs looks like its under resto catalog
        curl "http://localhost:5252/catalogs/projects/cdse-stac"


### Update a catalog

        # Update a catalog - user with the "updateCatalog" right can update a catalog he owns

        curl -X PUT -d@examples/catalogs/dummyCatalogWithItem_update.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalogWithChilds/dummyCatalogChild1"

### Update a catalog that has already childs

        # First post 2 catalogs under dummyCatalog 
        curl -X POST -d@examples/catalogs/dummyCatalogChild1.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalog"
        curl -X POST -d@examples/catalogs/dummyCatalogChild2.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalog"
        
        # Update dummyCatalog removing one catalog in the links will return an HTTP error because it would remove existing child
        curl -X PUT -d@examples/catalogs/dummyCatalog_update.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalog"
        
        # Use _force flag to force links update
        curl -X PUT -d@examples/catalogs/dummyCatalog_update.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalog?_force=1"

### Update the "pinned" flag of a catalog
When a catalog is flagged as "pinned" (i.e. its first level pinned property is set to true), then it also appears at the root level of the STAC endpoint.
Only admin can set/unset the "pinned" flag

        curl -X PUT -d@examples/catalogs/externalCatalog_update.json "http://admin:admin@localhost:5252/catalogs/projects/cdse-stac"

### Update a catalog changing everything except links

        # If the links property is not set, then existing links will not be affected

### Delete a catalog

        # Delete a catalog - user with the "deleteCatalog" right can delete a catalog he owns
        # This will return an HTTP error because the catalog contains child and removing it would remove childs
        curl -X DELETE "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalog"

        # Use _force flag to force deletion
        curl -X DELETE "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalog?_force=1"

## Debug

        # Create a dummy collection
        curl -X POST -d@examples/collections/DummyCollection.json "http://admin:admin@localhost:5252/collections"

        # Add 4 dummy items
        curl -X POST -d@examples/items/dummy1.json "http://admin:admin@localhost:5252/collections/DummyCollection/items"
        curl -X POST -d@examples/items/dummy2.json "http://admin:admin@localhost:5252/collections/DummyCollection/items"
        curl -X POST -d@examples/items/dummy3.json "http://admin:admin@localhost:5252/collections/DummyCollection/items"
        curl -X POST -d@examples/items/dummy4.json "http://admin:admin@localhost:5252/collections/DummyCollection/items"

        # POST a dummyCatalogWithItems catalog referencing 2 dummy items
        curl -X POST -d@examples/catalogs/dummyCatalogWith2Items.json "http://admin:admin@localhost:5252/catalogs/projects"

        # PUT the catalog by referencing 4 dummy items
        curl -X PUT -d@examples/catalogs/dummyCatalogWith4Items.json "http://admin:admin@localhost:5252/catalogs/projects/dummyCatalogWithItems"



        