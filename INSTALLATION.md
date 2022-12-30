# Installation

## Prerequesites
resto installation and deployment is based on docker-compose. It can run on any OS as long as the following software are up and running:

* bash
* Docker engine (i.e. docker)
* Docker compose (i.e. docker-compose)
* PostgreSQL client (i.e. psql)

## Configuration
All environment variables are defined within the [config.env](config.env) file.

For a local installation, you can leave it untouched. Otherwise, just make your own configuration. It's self explanatory (send me an email if not ;)

Note that each time you change the configuration file, you should undeploy then redeploy the service.

## Database
**[IMPORTANT] If you already deploy a resto v6.x database, you must upgrade the database to resto v7.x (see below)**

The resto service relies on a database instance. By default, the [deploy](deploy) script uses the [docker-compose.yml](docker-compose.yml) file which deploy
a "restodb" service based on [jjrom/resto-database](https://github.com/jjrom/resto-database) image.

Note that the default restodb configuration provides shm_size/shared memory values that should too high for the target host.
**If you experienced issue during installation, please check [this issue](https://github.com/jjrom/resto/issues/317#issuecomment-1258185471)**

### Using an external database
If you don't want to use the default embeded restodb service, you can use an external PostgreSQL database with the following mandatory constraints :

* PostgreSQL version must be **>= 11**
* Extension **postgis** must be installed
* Extension **postgis_topology** must be installed
* Extension **unaccent** must be installed
* Extension **uuid-ossp** must be installed
* Extension **pg_trgm** must be installed

To use an external database, you should update the [config.env](config.env) accordingly i.e. :

* set the **COMPOSE_FILE** environment variable to *docker-compose.api.yml*
* set all the **DATABASE_*** environment variables to match the external database configuration

It is important to notice that the **DATABASE_USER_NAME** should be a PostgreSQL user with `CREATE SCHEMA`  and `INSERT ON spatial_ref_sys` rights. To give a user the suitable rights, run the following sql commands on the target database:

        GRANT CREATE ON DATABASE ${DATABASE_NAME} TO ${DATABASE_USER_NAME};
        GRANT INSERT ON TABLE spatial_ref_sys TO ${DATABASE_USER_NAME};

### The DATABASE_COMMON_SCHEMA and DATABASE_TARGET_SCHEMA schemas
The resto database tables are defined in two schemas :

* The **DATABASE_COMMON_SCHEMA** schema contains the common tables, i.e. mainly user and rights
* The **DATABASE_TARGET_SCHEMA** schema contains all other tables i.e. collection, feature, etc.

By default, the **DATABASE_COMMON_SCHEMA** and **DATABASE_TARGET_SCHEMA** have the same value set to `resto`. So all tables, functions and triggers are installed in the `resto` schema.
These values can be changed in [config.env](config.env) but **YOU SHOULD NOT DO THAT UNLESS YOU HAVE A VERY GOOD REASON TO DO SO** - for instance use the same resto database instance to store independants collection from different resto api instances.

### Upgrading existing v6.x database
If you already deploy a resto v6.x database, you must upgrade the database to match resto v7.x database model. To do so, just type:

        ./scripts/upgradeDatabase_v6_to_v7.sh
        
## Building and deploying
After reviewing your [configuration](config.env) file, run the following command:

        ./deploy

You can also specify your own configuration file instead of the default [config.env](config.env)) :

        ./deploy -e config-dev.env

### Docker volumes
The following permanent docker volumes are created on first deployment:

* **resto_static_content** - contains static files uploaded to the server (e.g. user's avatar pictures)
* **resto_database_data** - contains resto database (i.e. PostgreSQL PGDATA directory). It is created only if you use the embeded "restodb" service

## Debug
The following environment variables shoud be used in development/debug context :

* **RESTO_DEBUG** should be set to 1 to disable PHP opcache
* **PHP_ENABLE_XDEBUG** should be set to 1 to enable Xdebug extension 
* **POSTGRES_LOG_MIN_DURATION_STATEMENT** should be set to 0 to log all PostgreSQL requests (only available if you use the embeded "restodb" service)

# FAQ

## How to test the STAC conformance
resto is compliant to STAC 1.0.0 to STAC-API 1.0.0-rc.1. Check with [stac-validator](https://github.com/stac-utils/stac-api-validator) tool:

        stac-api-validator \
                --root-url https://tamn.snapplanet.io --no-post \
                --conformance core \
                --conformance features \
                --conformance item-search \
                --conformance browseable \
                --conformance children \
                --conformance filter \
                --collection S2 --geometry '{"type": "Polygon", "coordinates": [[[100.0, 0.0], [101.0, 0.0], [101.0, 1.0], [100.0, 1.0], [100.0, 0.0]]]}'

## How to get the service endpoint
To get the default local endpoint :

        curl http://localhost:5252

If evertyhing runs fine, it should display the **STAC root endpoint content** of the service

## How to undeploy the service ?
Assuming that the application name is "resto" (see deploy "-p" option)

        ./undeploy

## How to check the logs of the running application ?
Assuming that the application name is "resto" (see deploy "-p" option)

        docker-compose -f docker-compose.yml logs -f

## How to build the docker images locally
Use docker-compose:

        # This will build the application server image (i.e. jjrom/resto)
        docker-compose -f docker-compose.yml build

