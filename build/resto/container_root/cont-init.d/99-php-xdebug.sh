#!/usr/bin/with-contenv bash

if [[ ($RESTO_DEBUG_MODE = 1 || $RESTO_DEBUG_MODE = '1' || $RESTO_DEBUG_MODE = 'true') && ($PHP_ENABLE_XDEBUG = 1 || $PHP_ENABLE_XDEBUG = '1' || $PHP_ENABLE_XDEBUG = 'true') ]]
then
  echo '[debug] Enabling XDebug extension'
  sed -i 's/^;zend_extension/zend_extension/' /etc/php/${PHP_VERSION}/mods-available/xdebug.ini
  if [ -x "$(command -v phpenmod)" ]; then
    phpenmod xdebug
  fi
else
  echo '[debug] XDebug remains disabled'
fi
