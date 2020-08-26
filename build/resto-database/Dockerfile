FROM postgis/postgis:11-2.5-alpine
LABEL maintainer="jerome.gasperi@gmail.com"

ENV BUILD_DIR=./build/resto-database \
    JUST_CONTAINERS_VERSION=1.22.1.0 \
    ARCH=amd64

# Add S6 supervisor (for graceful stop)
RUN apk add --no-cache curl && \
    curl -L -s https://github.com/just-containers/s6-overlay/releases/download/v${JUST_CONTAINERS_VERSION}/s6-overlay-${ARCH}.tar.gz | tar xvzf - -C / && \
    apk del --no-cache curl
ENTRYPOINT [ "/init" ]

# Copy run.d configuration
COPY ${BUILD_DIR}/container_root/cont-init.d /etc/cont-init.d

RUN mkdir -p /sql/addons

# Copy resto sql sources
COPY ${BUILD_DIR}/postgresql.conf /etc/postgresql.conf
COPY ${BUILD_DIR}/sql /sql
COPY ${BUILD_DIR}/installDB.sh /docker-entrypoint-initdb.d/10-installDB.sh
