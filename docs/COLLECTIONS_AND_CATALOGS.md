# Collections and catalogs
The aim of resto is to store spatiotemporal *items* (aka features). These items are stored within *collections*.
Each item must belong to one and only one *collection*. Usually a *collection* is made of homogeneous *items* (e.g. the Sentinel-2 collection contains Sentinel-2 images).

However, the *collection->item* can be restrictive. Thus, items can also be linked to *catalogs*. A *catalog* is a simple, flexible JSON file of links that provides a structure to organize and browse items. For instance the "Flood in the South of France 2019-11" *catalog* could contains items that are related to this event but coming from very different *collections*

![Collections and catalogs](./stac_catalog.png)

## Collections
**A collection must have a unique id.**

Eventually, it can contain an array of *aliases* (see [./examples/collections/L8.json](./examples/collections/L8.json#L3-L5) for instance). These aliases are alternate names to the collection id. Thus {collectionId} value in /collections/{collectionId}/* endpoints can use the original collection id or one of its aliases.

Note that id and aliases must be unique in the database. As a consequence, you cannot create a new collection or set an alias to an existing collection that as the same value of one of the aliases of an existing collection.

### Ingest a collection
To ingest a collection using the default **ADMIN_USER_NAME** and **ADMIN_USER_PASSWORD** (see [config.env](config.env)) :

        # POST a S2 dummy collection
        curl -X POST -d@examples/collections/S2.json "http://admin:admin@localhost:5252/collections"

Then get the collections list :

        curl "http://localhost:5252/collections"

*Note: Any user with the "createCollection" right can create a collection ([see rights](./USERS.md))*

### Ingest an item (aka feature)
To ingest a feature using the default **ADMIN_USER_NAME** and **ADMIN_USER_PASSWORD** (see [config.env](config.env)) :

        # POST a dummy feature inside the S2 collection
        curl -X POST -d@examples/features/S2A_MSIL1C_20190611T160901_N0207_R140_T23XMD_20190611T193040.json "http://admin:admin@localhost:5252/collections/S2/items"

        # Update a dummy feature inside the S2 collection
        curl -X PUT -d@examples/features/S2A_MSIL1C_20190611T160901_N0207_R140_T23XMD_20190611T193040_update.json "http://admin:admin@localhost:5252/collections/S2/items/S2A_MSIL1C_20190611T160901_N0207_R140_T23XMD_20190611T193040"

Then get the feature :

        curl "http://localhost:5252/collections/S2/items/S2A_MSIL1C_20190611T160901_N0207_R140_T23XMD_20190611T193040"

*Note: Any user with the "createFeature" right can insert a feature to a collection he owns ([see rights](./USERS.md))*

## Catalogs

### Create a catalog

        # Create a catalog - user with the "createCatalog" right can create a catalog 
        curl -X POST -d@examples/catalogs/dummyCatalog.json "http://admin:admin@localhost:5252/catalogs"

### Create a catalog with childs

        # This will raise an error because catalog' childs does not exist. They must be created first
        curl -X POST -d@examples/catalogs/dummyCatalogWithChilds.json "http://admin:admin@localhost:5252/catalogs"

        # Good way : ingest childs then parent
        curl -X POST -d@examples/catalogs/dummyCatalogChild1.json "http://admin:admin@localhost:5252/catalogs"
        curl -X POST -d@examples/catalogs/dummyCatalogChild2.json "http://admin:admin@localhost:5252/catalogs"
        curl -X POST -d@examples/catalogs/dummyCatalogWithChilds.json "http://admin:admin@localhost:5252/catalogs"


### Update a catalog
Only "title", "description" and "owner" properties can be updated

        # Update a catalog - user with the "updateCatalog" right can update a catalog he owns
        curl -X PUT -d@examples/catalogs/dummyCatalog_update.json "http://admin:admin@localhost:5252/catalogs/dummyCatalog"

### Delete a catalog

        # Delete a catalog - user with the "deleteCatalog" right can delete a catalog he owns
        curl -X DELETE "http://admin:admin@localhost:5252/catalogs/dummyCatalog"