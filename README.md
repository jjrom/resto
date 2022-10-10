# About
[![Build](https://github.com/jjrom/resto/actions/workflows/build-image.yml/badge.svg)](https://github.com/jjrom/resto/actions/workflows/build-image.yml/badge.svg "Build")
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/jjrom/resto.svg)](http://isitmaintained.com/project/jjrom/resto "Average time to resolve an issue")
[![Percentage of issues still open](http://isitmaintained.com/badge/open/jjrom/resto.svg)](http://isitmaintained.com/project/jjrom/resto "Percentage of issues still open")

resto is a metadata catalog and a search engine dedicated to geospatialized data. Originally, it’s main purpose it to handle Earth Observation satellite imagery but it can be used to store any kind of metadata localized in time and space.

resto search API conforms to the [SpatioTemporal Asset Catalog (STAC) specification v1.0.0](https://github.com/radiantearth/stac-spec) and to the [CEOS OpenSearch Best Practice Document](http://ceos.org/ourwork/workinggroups/wgiss/access/opensearch/).

It is mentioned in ESA's "Exploitation Platform Common Core Components" as the closest implementation of a catalogue component according to the requirements specified in ESA's ["Exploitation Platform Open Architecture"](https://tep.eo.esa.int/news/-/blogs/exploitation-platforms-open-architecture-released)

## Demo

The [https://tamn.snapplanet.io] resto server provides up to date access to Landsat-8 and Sentinel-2 images.

You can browse it with the [rocket web client](https://rocket.snapplanet.io)

## STAC conformance

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

# Installation
After reviewing your [configuration file](https://github.com/jjrom/resto/blob/master/config.env), run one of following command:

(for production)

        ./deploy

(for development)

        ./deploy -e config-dev.env
 
The [INSTALLATION.md](https://github.com/jjrom/resto/blob/master/INSTALLATION.md) file provides additional information on the installation process.

# Examples

* Get STAC root endpoint - https://tamn.snapplanet.io/?_pretty=1
* Get all collections - https://tamn.snapplanet.io/collections?_pretty=1
* Search Sentinel-2 products acquired on June 1st, 2021 - https://tamn.snapplanet.io/collections/S2/items?datetime=2021-05-06T00:00:00/2021-06-01T23:59:59&_pretty=1
* Get catalogs of products classified by continents - https://tamn.snapplanet.io/catalogs/classifications/geographical/continent?_pretty=1
* Get catalogs of products classified by european countries - https://tamn.snapplanet.io/catalogs/classifications/geographical/continent/continent:Europe:6255148?_pretty=1

# References

Here are some projects that use resto.

* [SnapPlanet](https://snapplanet.io)
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
resto is developped and maintained by [jeobrowser](https://mapshup.com). 

For questions, support or anything related to resto feel free to contact 

        jeobrowser
        50 quai de Tounis
        31000 Toulouse
        Tel   : +33 6 19 59 17 35
        email : jerome.gasperi@gmail.com
