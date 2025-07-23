EDITO user profiles are stored within the EDITO keecloak. The profile includes a unique "username", the list of projects the user belongs to and the subsequent rights of this user

The EDITO catalog is synchronized with the keecloak profile meaning : 

[OK] When a user register on EDITO, a dedicated private catalog is created within the catalog (i.e. /catalogs/users/{username})

When a project is created within the EDITO datalab, a catalog is created (i.e. /catalogs/projects/{projectname})

When a user is added/removed from a project, its subsequent rights access in the EDITO catalog are synchronized

[OK] Each user can POST/PUT/DELETE its  catalogs/items under its private catalog

[OK] By default, catalogs/items within the private catalog is only visible to owner

Only user belonging to a project can POST/PUT/DELETE catalog/item within the project catalog.

Copernicus Marine Service and EMODNET are projects. Thus, the paths to their corresponding catalog are located under /catalogs/projects (e.g. /catalogs/projects/emodnet and /catalogs/projects/cmems)

[OK] Catalog can be "pinned" by EDITO administrator. A pinned catalog also appears directly at the root of the EDITO catalog endpoint. This allows a viewer to display these catalogs first and not mixed with the other catalogs

EMODNET and Copernicus Marine Services catalogs are pinned catalogs

We discard all existing collections. Only one collection named "edito" remains. Every items of all catalogs will be under "edito" collection

Additionally, to the users and projects catalog, the catalog API provides access to "virtual" catalogs. A virtual catalog is a catalog that is created on the fly and contains items based on common properties. For instance, a geographical virtual catalog named "Pacific Ocean" would provide access to all items which footprint intersects the Pacific Ocean. 

The catalog API provides at least one virtual catalog : the "Variable Families" catalog. This catalog provides access to product per 'human readable" variable (e.g. "Temperature", "Current", "Biology", etc.)

The virtual catalogs are created from item properties. In particular we could use the STAC CF extension (or another more generic extension) to associate item to a Variable Family name based on the CF standard name. Additionally, EDITO will provide a set of controlled keywords to be passed as item property to ease association of this item, or an IA process would be used to automatically detect from its properties/keywords to which Variable Family an item should be associated. 

The curation of EDITO catalog products is of paramount importance. A quality control API should be put in place in front of the catalog API or as part of the catalog API to check for item compliance. In particular,  this quality control API should ensure that all assets listed in an item exist. This will avoid posting item with non existing asset. The asset format is also to be discussed. Do we allow any data format or do we enforce only ARCO / OGC format to ensure the EDITO viewer can display the product. In the latter case, EDITO will provide a set of tools to "ARCO-ify" assets. The Quality Control API/process is part of EDITO 2 WP3

The EDITO catalog API will also provide a brokering mechanism that allow to reference an external STAC catalog as a EDITO catalog. Basically, this will "proxify" the external catalog through the EDITO catalog API, providing a seamless navigation through the exernal catalog as if it was an EDITO catalog

(Not related to catalog API - but as a reminder) On the viewer, we would provide a way for users to warn EDITO administrator about invalid/not in the right category/etc. Product. A kind of data curation from the community. 