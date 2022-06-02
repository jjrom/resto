FROM postgis/postgis:14-master
LABEL maintainer="jerome.gasperi@gmail.com"

ENV BUILD_DIR=./build/resto-database \
    S6_OVERLAY_VERSION=1.22.1.0

# Add S6 supervisor (for graceful stop)
ADD https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-amd64.tar.gz /tmp
RUN tar -C / -xzf /tmp/s6-overlay-amd64.tar.gz
ENTRYPOINT [ "/init" ]

# Copy run.d configuration
COPY ${BUILD_DIR}/container_root/cont-init.d /etc/cont-init.d

RUN mkdir -p /sql/addons

# Copy resto sql sources
COPY ${BUILD_DIR}/postgresql.conf /etc/postgresql.conf
COPY ${BUILD_DIR}/sql /sql
COPY ${BUILD_DIR}/installDB.sh /docker-entrypoint-initdb.d/10-installDB.sh
