FROM ubuntu:bionic
LABEL maintainer="jerome.gasperi@gmail.com"

ENV BUILD_DIR=./build/resto \
    PHP_VERSION=7.2 \
    JUST_CONTAINERS_VERSION=1.22.1.0 \
    ARCH=amd64

# Add wait-for-it
ADD https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh /bin/wait-for-it.sh
RUN chmod +x /bin/wait-for-it.sh

# Add S6 supervisor (for graceful stop)
ADD https://github.com/just-containers/s6-overlay/releases/download/v${JUST_CONTAINERS_VERSION}/s6-overlay-${ARCH}.tar.gz /tmp/
RUN tar xzf /tmp/s6-overlay-${ARCH}.tar.gz -C /
ENTRYPOINT ["/init"]
CMD []

# Add ppa, curl and syslogd
RUN apt-get update && apt-get install -y software-properties-common curl inetutils-syslogd && \
    apt-add-repository ppa:nginx/stable -y && \
    #LC_ALL=C.UTF-8 apt-add-repository ppa:ondrej/php -y && \
    apt-get update && apt-get install -y \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-json \
    php${PHP_VERSION}-pgsql \
    php-geos \
    php${PHP_VERSION}-opcache \
    php${PHP_VERSION}-mysql \
    php-gettext \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-mbstring \
    #php-ast \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-sqlite3 \
    #php${PHP_VERSION}-apcu \
    php-xdebug \
    zip \
    unzip \
    gettext-base \
    nginx && \
    phpenmod xdebug && \
    apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* && \
    mkdir -p /run/php && chmod -R 755 /run/php

# Copy NGINX service script
COPY ${BUILD_DIR}/start-nginx.sh /etc/services.d/nginx/run
RUN chmod 755 /etc/services.d/nginx/run

# Copy PHP-FPM service script
COPY ${BUILD_DIR}/start-fpm.sh /etc/services.d/php_fpm/run
RUN chmod 755 /etc/services.d/php_fpm/run

# Copy NGINX configuration
COPY ${BUILD_DIR}/container_root/etc/nginx /etc/nginx

# Copy PHP-FPM configuration
COPY ${BUILD_DIR}/container_root/etc/fpm /etc/php/${PHP_VERSION}/fpm

# Copy PHP mods available
COPY ${BUILD_DIR}/container_root/etc/php/mods-available /etc/php/${PHP_VERSION}/mods-available

# Copy run.d configuration
COPY ${BUILD_DIR}/container_root/cont-init.d /etc/cont-init.d

# Copy source code in app directory
RUN mkdir /app
COPY ./app /app

# Static directory contains static files exposed by nginx
RUN mkdir /var/www/static

# Copy template configuration file to /tmp (will be used by 15-resto-config.sh during startup to generate /etc/resto/config.php)
COPY ${BUILD_DIR}/config.php.template /tmp/config.php.template

# Create configuration directory
RUN mkdir /cfg

# Set environment variables
# opcache https://www.scalingphpbook.com/best-zend-opcache-settings-tuning-config/
ENV PHP_FPM_MAX_CHILDREN=4096 \
    PHP_FPM_START_SERVERS=20 \
    PHP_FPM_MAX_REQUESTS=1024 \
    PHP_FPM_MIN_SPARE_SERVERS=5 \
    PHP_FPM_MAX_SPARE_SERVERS=128 \
    PHP_FPM_MEMORY_LIMIT=256M \
    PHP_FPM_MAX_EXECUTION_TIME=60 \
    PHP_FPM_UPLOAD_MAX_FILESIZE=20M \
    PHP_OPCACHE_MEMORY_CONSUMPTION=128 \
    PHP_OPCACHE_INTERNED_STRINGS_BUFFER=16 \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE=5 \
    RESTO_DEBUG_MODE=1


