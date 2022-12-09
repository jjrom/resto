FROM postgis/postgis:14-master
LABEL maintainer="jerome.gasperi@gmail.com"

ENV BUILD_DIR=./build/resto-database

# Add S6 supervisor (for graceful stop)
ADD https://github.com/just-containers/s6-overlay/releases/download/v3.1.2.1/s6-overlay-noarch.tar.xz /tmp
ADD https://github.com/just-containers/s6-overlay/releases/download/v3.1.2.1/s6-overlay-x86_64.tar.xz /tmp
RUN apt-get update -y && \
	apt-get install xz-utils -y && \
	tar -C / -Jxpf /tmp/s6-overlay-noarch.tar.xz && \
	tar -C / -Jxpf /tmp/s6-overlay-x86_64.tar.xz && \
	rm -rf /tmp/*.tar.xz
ENTRYPOINT [ "/init" ]

# Copy run.d configuration
COPY ${BUILD_DIR}/container_root/cont-init.d /etc/cont-init.d

RUN mkdir -p /sql/addons

# Copy resto sql sources
COPY ${BUILD_DIR}/postgresql.conf /etc/postgresql.conf
COPY ${BUILD_DIR}/sql /sql
COPY ${BUILD_DIR}/installDB.sh /docker-entrypoint-initdb.d/10-installDB.sh

# Launch postgres as a longrun s6-overlay process
COPY ${BUILD_DIR}/start-postgres.sh /etc/services.d/postgres/run
RUN chmod 755 /etc/services.d/postgres/run
