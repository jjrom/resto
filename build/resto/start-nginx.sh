#!/usr/bin/with-contenv sh
set -e;

# Use wait for it for TCP only
#/bin/wait-for-it.sh -t 120 127.0.0.1:9000

/usr/sbin/nginx -g "daemon off;"
