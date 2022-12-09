FROM jjrom/nginx-fpm:8.1-1
LABEL maintainer="jerome.gasperi@gmail.com"

ENV BUILD_DIR=./build/resto \
    PHP_VERSION=8.1 \
    RESTO_DEBUG=1

# SimpleSAMLphp
ARG SIMPLESAMLPHP_VERSION=1.19.6
ADD https://github.com/simplesamlphp/simplesamlphp/releases/download/v${SIMPLESAMLPHP_VERSION}/simplesamlphp-${SIMPLESAMLPHP_VERSION}.tar.gz /tmp/
RUN tar xzf /tmp/simplesamlphp-${SIMPLESAMLPHP_VERSION}.tar.gz -C /tmp && \
    rm -f /tmp/simplesamlphp-${SIMPLESAMLPHP_VERSION}.tar.gz && \
    mv /tmp/simplesamlphp-${SIMPLESAMLPHP_VERSION} /var/www/simplesamlphp
COPY saml/config/config.php /var/www/simplesamlphp/config
COPY saml/config/authsources.php /var/www/simplesamlphp/config
COPY saml/config/saml20-idp-remote.php /var/www/simplesamlphp/metadata
COPY saml/cert/* /var/www/simplesamlphp/cert/

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

# Cache directory
RUN mkdir -p /cache

# Static directory contains static files exposed by nginx
RUN mkdir /var/www/static

# Copy template configuration file to /tmp (will be used by 15-resto-config.sh during startup to generate /etc/resto/config.php)
COPY ${BUILD_DIR}/config.php.template /tmp/config.php.template

# Create configuration directory
RUN mkdir /cfg

# Docs directory (for API)
RUN mkdir -p /docs
COPY ./docs /docs
