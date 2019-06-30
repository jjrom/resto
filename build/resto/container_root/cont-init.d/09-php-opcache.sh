#!/usr/bin/with-contenv bash

if [[ $RESTO_DEBUG = 1 || $RESTO_DEBUG = '1' || $RESTO_DEBUG = 'true' ]]
then
  echo '[debug] Opcache WATCHING for file changes'
else
  echo '[debug] Opcache set to PERFORMANCE, NOT WATCHING for file changes'
fi
