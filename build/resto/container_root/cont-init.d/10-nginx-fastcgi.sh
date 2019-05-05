#!/usr/bin/with-contenv bash

CONF_NGINX_SITE=/etc/nginx/sites-available/default

if [[ $NGINX_FASTCGI_BUFFERS ]]
then
  echo "[nginx-fastcgi] setting fastcgi_buffers ${NGINX_FASTCGI_BUFFERS}"
  sed -i "s/\fastcgi_buffers .*;/fastcgi_buffers ${NGINX_FASTCGI_BUFFERS};/" $CONF_NGINX_SITE
fi

if [[ $NGINX_FASTCGI_BUFFER_SIZE ]]
then
  echo "[nginx-fastcgi] setting fastcgi_buffer_size ${NGINX_FASTCGI_BUFFER_SIZE}"
  sed -i "s/\fastcgi_buffer_size .*;/fastcgi_buffer_size ${NGINX_FASTCGI_BUFFER_SIZE};/" $CONF_NGINX_SITE
fi

if [[ $NGINX_FASTCGI_BUSY_BUFFERS_SIZE ]]
then
  echo "[nginx-fastcgi] setting fastcgi_busy_buffers_size ${NGINX_FASTCGI_BUSY_BUFFERS_SIZE}"
  sed -i "s/\fastcgi_busy_buffers_size .*;/fastcgi_busy_buffers_size ${NGINX_FASTCGI_BUSY_BUFFERS_SIZE};/" $CONF_NGINX_SITE
fi

if [[ $NGINX_FASTCGI_CONNECT_TIMEOUT ]]
then
  echo "[nginx-fastcgi] setting fastcgi_connect_timeout ${NGINX_FASTCGI_CONNECT_TIMEOUT}"
  sed -i "s/\fastcgi_connect_timeout .*;/fastcgi_connect_timeout ${NGINX_FASTCGI_CONNECT_TIMEOUT};/" $CONF_NGINX_SITE
fi

if [[ $NGINX_FASTCGI_SEND_TIMEOUT ]]
then
  echo "[nginx-fastcgi] setting fastcgi_send_timeout ${NGINX_FASTCGI_SEND_TIMEOUT}"
  sed -i "s/\fastcgi_send_timeout .*;/fastcgi_send_timeout ${NGINX_FASTCGI_SEND_TIMEOUT};/" $CONF_NGINX_SITE
fi

if [[ $NGINX_FASTCGI_READ_TIMEOUT ]]
then
  echo "[nginx-fastcgi] setting fastcgi_read_timeout ${NGINX_FASTCGI_READ_TIMEOUT}"
  sed -i "s/\fastcgi_read_timeout .*;/fastcgi_read_timeout ${NGINX_FASTCGI_READ_TIMEOUT};/" $CONF_NGINX_SITE
fi
