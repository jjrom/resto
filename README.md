# resto

[![Code Climate](https://codeclimate.com/github/jjrom/resto/badges/gpa.svg)](https://codeclimate.com/github/jjrom/resto)
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/jjrom/resto.svg)](http://isitmaintained.com/project/jjrom/resto "Average time to resolve an issue")
[![Percentage of issues still open](http://isitmaintained.com/badge/open/jjrom/resto.svg)](http://isitmaintained.com/project/jjrom/resto "Percentage of issues still open")

resto is cataalog and a search engine dedicated to Earth Observation products. It's main purpose it to handle EO satellite imagery but it can be used to store any kind of geospatialized data.

resto search API is compliant with the [CEOS OpenSearch Best Practice Document](http://ceos.org/ourwork/workinggroups/wgiss/access/opensearch/) and is mentioned in ESA's "Exploitation Platform Common Core Components" as the closest implementation of a catalogue component according to the requirements specified in ESA's ["Exploitation Platform Open Architecture"](https://tep.eo.esa.int/news/-/blogs/exploitation-platforms-open-architecture-released)

## Online demo

You an try resto capability from the online [demo](http://mapshup.com/projects/rocket/) !

## Looking for support ?

**For official support to resto, please contact [jeobrowser](https://mapshup.com)**

resto have been used in several projects and supported and maintened by multiple private companies.

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

If you plan to use resto and would like to have your project added to this list, feel free to send an email to jerome[dot]gasperi[at]gmail[dot]com 

## Installation

In the following, we suppose that $RESTO_HOME is the directory where resto sources will be installed

        export RESTO_HOME=/wherever/you/want/resto

If not already done, download resto sources to $RESTO_HOME

        git clone https://github.com/jjrom/resto.git $RESTO_HOME

### Prerequesites

* Apache (v2.0+) with **mod_rewrite** support
* PHP (v5.3+) with **XMLWriter and PGConnect** extensions
* PostgreSQL (v9.3+) with json support and **unaccent** extension
* PostGIS (v2.0+)

**Note 1:** resto could work with lower version of the specified requirements.
However there is no guaranty of success and unwanted result may occurred !

**Note 2:** Apache server can be replaced by nginx server (see configuration)

### Install resto database

resto installs a PostgreSQL database named 'resto'.

The 'resto' database is created with PostGIS extension enabled within the 'public' schema.

During the installation, the 'resto' schema is created. It stores all tables needed by the application.

The user 'resto' is automatically created within this database :
* 'resto' user has READ+WRITE access to 'resto' databases

It is very important to specify strong passwords for this user.

To install resto database, launch the following script

        $RESTO_HOME/_install/installDB.sh -F -p <resto user password>

Note1 : installation script supposed that the PostgreSQL superuser name is 'postgres' (otherwise add '-s <superusername>' to the above command)
and that it has access to psql on localhost without password.

### Deploy application

Last step is to install application to the target directory. This directory will be accessed
by the web server so it could be either directly under the DocumentRoot web server directory
or in whatever directory accessed through web server Alias configuration. The latter case is preferred
(see Apache configuration part below for Alias configuration)

To install resto launch the following script

        # Note : RESTO_TARGET should not exist - it will be created by deploy.sh script
        export RESTO_TARGET=/your/installation/directory
        $RESTO_HOME/_install/deploy.sh -s $RESTO_HOME -t $RESTO_TARGET


### Install iTag

[iTag] (http://github.com/jjrom/itag) is an application to automatically tag geospatial metadata
with geographical information (such as location, landuse, etc.)

resto uses iTag during the resource ingestion process and also for the Gazetteer and the Wikipedia modules

If you want to use iTag with resto, you should install it (follow the [instructions] (http://github.com/jjrom/itag/))


## Configuration

**Note** : resto has been tested on both Apache and nginx web server.
Choose between configuration 1 and 2 depending on your configuration

### Web server option 1 : Apache

The first thing to do is to configure Apache (or whatever is your web server) to support URL rewriting.

Basically, with URLs rewriting every request sent to resto application will end up to index.php. For example,
http://localhost/resto/whatever/youwant/to/access will be rewrite as http://localhost/resto/index.php?restoURL=/whatever/youwant/to/access

**Check that mod_rewrite is installed**

For instance on MacOS X, looks for something like this in /etc/apache2/httpd.conf

        LoadModule rewrite_module libexec/apache2/mod_rewrite.so

**Configure target directory**

Set an alias to the resto directory. To make mod_rewrite works, you need to verify that both 'FollowSymLinks'
and 'AllowOverride All' are set in the apache directory configuration

For instance to access resto at http://localhost/resto (change "/directory/to/resto" by $RESTO_TARGET below):

For Apache < 2.4 :

        Alias /resto/ "/directory/to/resto/"
        <Directory "/directory/to/resto/">
            Options FollowSymLinks
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>

For Apache >= 2.4 :

        Alias /resto/ "/directory/to/resto/"
        <Directory "/directory/to/resto/">
            Options FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>

**Configure apache to support https (optional)**

resto can be accessed either in http or https. For security reason, https is preferred when
dealing with authenticated request (e.g. creation of a collection, insertion of a resource in the collection, etc.)

Thus, turning https in apache is optional to make resto work.

This document does not explain how to turn https on - but your system administrator should know how to do it !

Note: a step by step guide for installing https on Mac OS X is provided in the FAQ section below

**IMPORTANT - Configure "RewriteBase" value within $RESTO_TARGET/.htaccess**

Edit this value so it matches your alias name. If you use the same alias as in 2. (i.e. '/resto/')
there is no need to edit $RESTO_TARGET/.htaccess file

**Restart apache**

        apachectl restart

### Web server option 2 : nginx

For a comprehensive migration from apache to nginx, [you should read this article](https://www.digitalocean.com/community/tutorials/how-to-migrate-from-an-apache-web-server-to-nginx-on-an-ubuntu-vps)

The resto nginx configuration block should look like this :

        # Tell nginx to use php-fhm
        location ~ \.php$ {
                fastcgi_split_path_info ^(.+\.php)(/.+)$;
                fastcgi_pass unix:/var/run/php5-fpm.sock;
                fastcgi_index index.php;
                include fastcgi_params;
        }

        # deny access to Apache .htaccess files
        location ~ /\.ht {
                deny all;
        }

        # resto url rewrite configuration
        location /resto/ {
                if (!-e $request_filename){
                        rewrite ^/resto/(.*)$ /resto/index.php?RESToURL=$1 last; break;
                }
        }

**IMPORTANT** the previous configuration assumes that your resto installation is in the
"resto" directory within the web server document root. If it is not the case, you should
change all "/resto/" occurrences in the configuration example to match your installation

### PostgreSQL configuration

Two configurations are possible :

* A socket configuration (**preferred** if PostgreSQL and Apache are on the same server you should)
* A TCP/IP configuration

**Socket configuration**

Edit $RESTO_TARGET/include/config.php and comment the general->database->host parameter

Edit the PostgreSQL pg_hba.conf file and add the following rules :

        # Configuration for resto framework
        local  resto     resto                                        md5

Then restart postgresql (e.g. "pg_ctl restart")

**TCP/IP configuration**

Edit $RESTO_TARGET/include/config.php and uncomment the general->database->host parameter to set the right IP of the PostgreSQL server (default 'localhost')

Edit the PostgreSQL pg_hba.conf file and add the following rules :

        # Configuration for resto framework
        host   resto   resto                   127.0.0.1/32            md5
        host   resto   resto                   ::1/128                 md5

Edit the PostreSQL postgresql.conf and be sure that postgres accept tcp_ip connection.

        # Uncomment these two lines within postgesql.conf
        listen_adresses = 'localhost'
        port = 5432

Then restart postgresql (e.g. "pg_ctl restart")

Note : **Read the following if you are using Fedora, Red Hat Enterprise Linux, CentOS, Scientific Linux,
or one of the other distros that enable SELinux by default**

        #
        #  Enable the specific permission to allow Apache to issue HTTP connections.
        #
        service httpd stop
        service postgresql stop

        setsebool -P httpd_can_network_connect 1

        service httpd start
        service postgresql start

### resto configuration

All configuration parameters are defined within $RESTO_TARGET/include/config.php file

The configuration file is self explanatory. For a standard installation you should only check that :
* The restoUrl points to your resto installation webpage
* **database.password** value is **the same as the 'resto' user password set during database installation**

Create an admin user within the database

        $RESTO_HOME/_install/createAdminUser.sh -u admin -p admin

**Note : you should change the above script parameters to set a stronger password than 'admin' !!!**

**If you are using Fedora, Red Hat Enterprise Linux, CentOS, Scientific Linux, or one of the other distros that enable SELinux by default**

        #
        #  Enable sendmail
        #
        setsebool -P httpd_can_sendmail on

## Facets computation

The database table "resto.facets" stores a real count of features per :

* collection
* productType
* processingLevel
* platform
* instrument
* sensorMode
* continent
* country
* region
* state
* year
* month
* day

By default, these counts are automatically updated each time a feature is added (POST), modified (PUT) or deleted (DELETE).

**IMPORTANT** If you plan **massive parallel ingestion**, we recommand to deactivate the automatic update of the facets and to compute them manually of via a cron job by using the dedicated $RESTO_HOME/_scripts/updateFacets.php (see -h to have info on this script)

You can deactivate the automatic facets computation, by settings "'storeFacets' => false" in $RESTO_HOME/include/config.php


## Quick Start

### Create a collection

        $RESTO_HOME/_scripts/createCollection.sh -f $RESTO_HOME/_examples/collections/Example.json -u admin:admin

### Access OpenSearch Description for a collection

Only works for an existing collection (so create a collection first !)

        Open you browser to http://localhost/resto/api/collections/Example/describe.xml

###Delete a collection

        $RESTO_HOME/_scripts/deleteCollection.sh -c Example -u admin:admin

**Note** : only empty collection can be deleted this way

### List all collections

        Open your browser to http://localhost/resto/collections.json/

### Insert a resource

Only works for an existing collection (so create a collection first !)

        $RESTO_HOME/_scripts/postResource.sh -c Example -f $RESTO_HOME/_examples/resources/resource_Example.json -u admin:admin


### Search for resources in GeoJSON

Only works for an existing collection (so create a collection first !)

        Open your browser to http://localhost/resto/api/collections/Example/search.json


### See resource metadata in Atom

Only works on an existing resource (so insert resource first !)

        curl "http://localhost/resto/collections/Example/dda9cd5f-b3b9-5665-b1de-c78df8d3f1fb.atom"


### See resource metadata in GeoJSON

Only works on an existing resource (so insert resource first !)

        curl "http://localhost/resto/collections/Example/dda9cd5f-b3b9-5665-b1de-c78df8d3f1fb.json"


## Frequently Asked Questions

### What configuration parameters are important for production use ?

For production use, you should take a look at the $RESTO_TARGET/include/config.php file and do the following :

* set "debug" to false
* set a "tokenDuration" no greater than 3600 seconds (i.e. 1 hour)
* set a non obvious "passphrase" for JWT (or at least change the default one !)
* if you want to limit API access, remove 'localhost' from the corsWhiteList and explicitly add allowed domain names
* set a strong password for the database 'resto' user
* set "storeFacets" to false if you plan **massive parallel ingestion** (see chapter on Facets)


### How to force all database connection to be socket only (i.e. no http)

Edit $RESTO_TARGET/include/config.php file and comment all 'host' entries (i.e. within 'database' and in all modules options)


### How to configure Apache for https ?

For [Mac OS X] (http://blog.andyhunt.info/2011/11/26/apache-ssl-on-max-osx-lion-10-7/)

(Warning http://stackoverflow.com/questions/18251128/why-am-i-suddenly-getting-a-blocked-loading-mixed-active-content-issue-in-fire)


### My collection contains products but my collection is empty

Check if all the mandatory search terms are defined. Mandatory search terms are the OpenSearch terms
without a question mark '?' defined within the Url template of the OpenSearch Document Description (i.e. http://localhost/resto/api/collections/{collection}/describe)
