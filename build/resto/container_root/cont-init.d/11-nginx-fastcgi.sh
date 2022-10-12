#!/usr/bin/with-contenv bash

CONF_NGINX_GLOBAL=/etc/nginx/nginx.conf
CONF_NGINX_SITE=/etc/nginx/sites-available/default


if [[ $NGINX_CLIENT_MAX_BODY_SIZE ]]
then
  echo "[nginx] setting client_max_body_size ${NGINX_CLIENT_MAX_BODY_SIZE}"
  sed -i "s/client_max_body_size .*;/client_max_body_size ${NGINX_CLIENT_MAX_BODY_SIZE};/" $CONF_NGINX_GLOBAL
  echo "[nginx] setting client_body_buffer_size ${NGINX_CLIENT_MAX_BODY_SIZE}"
  sed -i "s/client_body_buffer_size .*;/client_body_buffer_size ${NGINX_CLIENT_MAX_BODY_SIZE};/" $CONF_NGINX_GLOBAL
fi

if [[ $NGINX_CLIENT_BODY_TIMEOUT ]]
then
  echo "[nginx] setting client_body_timeout ${NGINX_CLIENT_BODY_TIMEOUT}"
  sed -i "s/client_body_timeout .*;/client_body_timeout ${NGINX_CLIENT_BODY_TIMEOUT};/" $CONF_NGINX_GLOBAL
fi

if [[ $NGINX_FASTCGI_BUFFERS ]]
then
  echo "[nginx-fastcgi] setting fastcgi_buffers ${NGINX_FASTCGI_BUFFERS}"
  sed -i "s/fastcgi_buffers .*;/fastcgi_buffers ${NGINX_FASTCGI_BUFFERS};/" $CONF_NGINX_SITE
fi

if [[ $NGINX_FASTCGI_BUFFER_SIZE ]]
then
  echo "[nginx-fastcgi] setting fastcgi_buffer_size ${NGINX_FASTCGI_BUFFER_SIZE}"
  sed -i "s/fastcgi_buffer_size .*;/fastcgi_buffer_size ${NGINX_FASTCGI_BUFFER_SIZE};/" $CONF_NGINX_SITE
fi

if [[ $NGINX_FASTCGI_BUSY_BUFFERS_SIZE ]]
then
  echo "[nginx-fastcgi] setting fastcgi_busy_buffers_size ${NGINX_FASTCGI_BUSY_BUFFERS_SIZE}"
  sed -i "s/fastcgi_busy_buffers_size .*;/fastcgi_busy_buffers_size ${NGINX_FASTCGI_BUSY_BUFFERS_SIZE};/" $CONF_NGINX_SITE
fi

if [[ $NGINX_FASTCGI_CONNECT_TIMEOUT ]]
then
  echo "[nginx-fastcgi] setting fastcgi_connect_timeout ${NGINX_FASTCGI_CONNECT_TIMEOUT}"
  sed -i "s/fastcgi_connect_timeout .*;/fastcgi_connect_timeout ${NGINX_FASTCGI_CONNECT_TIMEOUT};/" $CONF_NGINX_SITE
fi

if [[ $NGINX_FASTCGI_SEND_TIMEOUT ]]
then
  echo "[nginx-fastcgi] setting fastcgi_send_timeout ${NGINX_FASTCGI_SEND_TIMEOUT}"
  sed -i "s/fastcgi_send_timeout .*;/fastcgi_send_timeout ${NGINX_FASTCGI_SEND_TIMEOUT};/" $CONF_NGINX_SITE
fi

if [[ $NGINX_FASTCGI_READ_TIMEOUT ]]
then
  echo "[nginx-fastcgi] setting  ${NGINX_FASTCGI_READ_TIMEOUT}"
  sed -i "s/fastcgi_read_timeout .*;/fastcgi_read_timeout ${NGINX_FASTCGI_READ_TIMEOUT};/" $CONF_NGINX_SITE
fi
