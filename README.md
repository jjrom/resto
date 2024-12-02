# About
[![Build](https://github.com/jjrom/resto/actions/workflows/build-image.yml/badge.svg)](https://github.com/jjrom/resto/actions/workflows/build-image.yml/badge.svg "Build")
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/jjrom/resto.svg)](http://isitmaintained.com/project/jjrom/resto "Average time to resolve an issue")
[![Percentage of issues still open](http://isitmaintained.com/badge/open/jjrom/resto.svg)](http://isitmaintained.com/project/jjrom/resto "Percentage of issues still open")

resto is a metadata catalog and a search engine dedicated to geospatialized data. Originally, it’s main purpose it to handle Earth Observation satellite imagery but it can be used to store any kind of metadata localized in time and space.

resto search API conforms to the [SpatioTemporal Asset Catalog (STAC) specification v1.0.0](https://github.com/radiantearth/stac-spec)

It is mentioned in ESA's "Exploitation Platform Common Core Components" as the closest implementation of a catalogue component according to the requirements specified in ESA's ["Exploitation Platform Open Architecture"](https://tep.eo.esa.int/news/-/blogs/exploitation-platforms-open-architecture-released)

## Demo

The [https://tamn.snapplanet.io] resto server provides up to date access to Landsat-8 and Sentinel-2 images.

You can browse it with the [rocket web client](https://rocket.snapplanet.io)

Or test the API :

* Get STAC root endpoint - https://tamn.snapplanet.io/?_pretty=1
* Get all collections - https://tamn.snapplanet.io/collections?_pretty=1
* Search Sentinel-2 products acquired on June 1st, 2021 - https://tamn.snapplanet.io/collections/S2/items?datetime=2021-05-06T00:00:00Z/2021-06-01T23:59:59Z&_pretty=1
* Get catalogs of products classified by continents - https://tamn.snapplanet.io/catalogs/classifications/geographical/continent?_pretty=1
* Get catalogs of products classified by european countries - https://tamn.snapplanet.io/catalogs/classifications/geographical/continent/continent:Europe:6255148?_pretty=1

# Installation

## TL;DR
The [INSTALLATION.md](./docs/INSTALLATION.md) file provides additional information on the installation process.

## Deploy the service
To launch a default pre-configured resto instance, just type :

    ./deploy

This will build locally the jjrom/resto image and launch a resto container exposing the resto API service at **http://localhost:5252**

To launch a default develop resto instance (i.e. with RESTO_DEBUG set to true and all database logs), just type :

    ./deploy -e config-dev.env

To launch a default develop resto instance using a connection pooling, just type :

    ./deploy -e config-dev-pgbouncer.env

### [IMPORTANT] Docker on Mac M1
If you're using docker on Mac with apple Silicon M1 chip, be sure to **turn off "Use Rosetta for x86/amd64 emulation on Apple Silicon"** in Docker Desktop > Settings > General.

When this option is turned on, every calls to PHP preg_match function (which is used by resto) leads to a segmentation fault within php fpm and an HTTP 502 Bad Gateway error in nginx. Why ? I just don't know !

## Users, groups and rights
See [./USERS.md](./docs/USERS.md)

## Collection and catalogs
See [.COLLECTIONS_AND_CATALOGS.md](./docs/COLLECTIONS_AND_CATALOGS.md)

# References

Here are some projects that use resto.

* [SnapPlanet](https://snapplanet.io)
* [European Digital Twin of the Ocean](https://www.edito.eu)
* [CREODIAS](https://creodias.eu/eo-data-finder-api-manual)
* [Rocket - The Earth in your pocket](https://rocket.snapplanet.io)
* [The French Sentinel Data Processing center](https://peps.cnes.fr/rocket/#/home)
* [The French Space Agency, THEIA land data center](https://theia.cnes.fr/atdistrib/rocket/#/home)
* [The Polish EO Data finder](http://finder.eocloud.eu/www/)
* [Remote Sensor Technology Center of Japan, EPIC project](http://www.geomatys.com/en/portfolio/epic.html)
* [Sentinel Australia Regional Access](https://copernicus.nci.org.au/sara.client/#/home)
* [ESA's Food Security Thematic Exploitation Platform](https://github.com/cgi-eoss/fstep)
* [ESA's Forestry Thematic Exploitation Platform](https://github.com/cgi-eoss/ftep)
* [CNES Kalideos platform](https://www.kalideos.fr)
* [CEOS Recovery Observatory](https://www.recovery-observatory.org)
* [EO4SD Lab - Earth observation for sustainable development](https://eo4sd-lab.net)
* [IRSTEA Thisme project - THeia and Irstea Soil MoisturE catalog](https://thisme.cines.teledetection.fr/home)

If you plan to use resto and would like to have your project added to this list, feel free to contact [support](#support)

# <a name="support"></a>Support
resto is developped and maintained by [jeobrowser](https://snapplanet.io). 

For questions, support or anything related to resto feel free to contact *jerome[dot]gasperi[at]gmail[dot]com*
