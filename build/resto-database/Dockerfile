FROM postgis/postgis:14-master
LABEL maintainer="jerome.gasperi@gmail.com"

ENV BUILD_DIR=./build/resto-database \
    JUST_CONTAINERS_VERSION=1.22.1.0 \
    ARCH=amd64

# Add S6 supervisor (for graceful stop)
RUN apt-get update \
    && apt-get install -y curl tzdata \
    && curl -k -L -s https://github.com/just-containers/s6-overlay/releases/download/v${JUST_CONTAINERS_VERSION}/s6-overlay-${ARCH}.tar.gz | tar xvzf - -C / && \
    && tar -xzf s6-overlay-${ARCH}.tar.gz -C / \
    && rm -rf s6-overlay-amd64.tar.gz \
    && apt-get clean \
    && rm -rf /tmp/* /var/lib/apt/lists/* /var/tmp/*
ENTRYPOINT [ "/init" ]

# Copy run.d configuration
COPY ${BUILD_DIR}/container_root/cont-init.d /etc/cont-init.d

RUN mkdir -p /sql/addons

# Copy resto sql sources
COPY ${BUILD_DIR}/postgresql.conf /etc/postgresql.conf
COPY ${BUILD_DIR}/sql /sql
COPY ${BUILD_DIR}/installDB.sh /docker-entrypoint-initdb.d/10-installDB.sh
