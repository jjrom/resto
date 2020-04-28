# resto

**[WARNING] This repository is for resto v5+. For prior resto version, please check the [resto v2.x branch](https://github.com/jjrom/resto/tree/2.x)**

[![Code Climate](https://codeclimate.com/github/jjrom/resto/badges/gpa.svg)](https://codeclimate.com/github/jjrom/resto)
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/jjrom/resto.svg)](http://isitmaintained.com/project/jjrom/resto "Average time to resolve an issue")
[![Percentage of issues still open](http://isitmaintained.com/badge/open/jjrom/resto.svg)](http://isitmaintained.com/project/jjrom/resto "Percentage of issues still open")

resto is a metadata catalog and a search engine dedicated to geospatialized data. Originally, it’s main purpose it to handle Earth Observation satellite imagery but it can be used to store any kind of metadata localized in time and space.

resto search API is compliant with the [CEOS OpenSearch Best Practice Document](http://ceos.org/ourwork/workinggroups/wgiss/access/opensearch/) and is mentioned in ESA's "Exploitation Platform Common Core Components" as the closest implementation of a catalogue component according to the requirements specified in ESA's ["Exploitation Platform Open Architecture"](https://tep.eo.esa.int/news/-/blogs/exploitation-platforms-open-architecture-released)

## Online demo

You an try resto capability from the online [demo](http://mapshup.com/projects/rocket/) !

## References

Here are some projects that use resto.

* [CREODIAS](https://creodias.eu/eo-data-finder-api-manual)
* [Rocket - The Earth in your pocket](http://mapshup.com/projects/rocket/#/home)
* [The French Sentinel Data Processing center](https://peps.cnes.fr/rocket/#/home)
* [The French Space Agency, THEIA land data center](https://theia.cnes.fr/atdistrib/rocket/#/home)
* [The Polish EO Data finder](http://finder.eocloud.eu/www/)
* [Remote Sensor Technology Center of Japan, EPIC project](http://www.geomatys.com/en/portfolio/epic.html)
* [Sentinel Australia Regional Access](https://copernicus.nci.org.au/sara.client/#/home)
* [Sinergise sentinel-hub OpenSearch API](http://sentinelhub-py.readthedocs.io/en/latest/opensearch.html)
* [ESA's Food Security Thematic Exploitation Platform](https://github.com/cgi-eoss/fstep)
* [ESA's Forestry Thematic Exploitation Platform](https://github.com/cgi-eoss/ftep)

If you plan to use resto and would like to have your project added to this list, feel free to contact [support](#support)

## Installation

### Prerequesites
resto installation and deployment is based on docker-compose. It can run on any OS as long as the following software are up and running:

* bash
* Docker engine (i.e. docker)
* Docker compose (i.e. docker-compose)
* PostgreSQL client (i.e. psql)

### Configuration
All configuration options are defined within the [config.env](https://github.com/jjrom/resto/blob/master/config.env) file.

For a local installation, you can leave it untouched. Otherwise, just make your own configuration. It's self explanatory (send me an email if not ;)

Note that each time you change the configuration file, you should undeploy then redeploy the service.

### External Database
resto can use an external PostgreSQL database (version 11+). 

Set the config.env `DATABASE_IS_EXTERNAL` parameter to `yes` to 
enable an external database.

The following extensions must be installed on the target database:
 * postgis
 * postgis_topology
 * unaccent
 * uuid-ossp
 * pg_trgm
 
A normal PG user with `create schema` rights is necessary in order for resto to operate. To give a user `create schema` rights, run the following sql command:

        grant create on database <dbname> to <dbuser>;

resto tables, functions and triggers will be installed in a `resto` schema by running [scripts/installOnExternalDB.sh](https://github.com/jjrom/resto/blob/resto-stac/scripts/installOnExternalDB.sh):

        cd scripts
        ./installOnExternalDB.sh -e <config file>

### Hardware
**[IMPORTANT]** In production mode (see below), the default configuration of the PostgreSQL server is for a 64Go RAM server. Changes this in [configuration](https://github.com/jjrom/resto/blob/master/config.env) file accordingly to your real configuration

### Building and deploying
After reviewing your [configuration](https://github.com/jjrom/resto/blob/master/config.env) file, run one of following command:

(for production)

        ./deploy prod

(for development)

        ./deploy dev

#### Docker images
On first deployment, the following images are pulled from dockerhub:

* ubuntu
* mdillon/postgis
* php

And the following images are built from local Dockerfiles:

* jjrom/resto
* jjrom/resto-database

#### Docker volumes
The following permanent docker volumes are created on first deployment:

* **resto_database_data** - contains resto database (i.e. PostgreSQL PGDATA directory)
* **resto_static_content** - contains static files uploaded to the server (e.g. user's avatar pictures)

#### Docker network
The docker network **rnet** is created on first deployment. This network is shared by the following images

* jjrom/resto
* jjrom/resto-database
* jjrom/itag (see [iTag github repository](https://github.com/jjrom/itag))

### Production vs development
The development environment differs from the production environment by the following aspects:

* The source code under /app directory is mount within the container
* The Xdebug extension is enabled
* PHP opcache is disabled
* All SQL requests are logged
* The default postgres configuration is set for a small configuration (i.e. 4Go RAM)

## FAQ

### Test the service
Resolve the endpoint provided by the deploy script

        curl http://localhost:5252

If evertyhing runs fine, it should display

        {"status":"success","message":"Hello"}

### How do i undeploy the service ?
Assuming that the application name is "resto" (see deploy "-p" option)

        ./undeploy resto

### How do i check the logs of the running application ?
Assuming that the application name is "resto" (see deploy "-p" option)

        docker-compose --project-name resto logs -f

## <a name="support"></a>Support
resto is developped and maintained by [jeobrowser](https://mapshup.com). 

For questions, support or anything related to resto feel free to contact 

        jeobrowser
        50 quai de Tounis
        31000 Toulouse
        Tel   : +33 6 19 59 17 35
        email : jerome.gasperi@gmail.com
