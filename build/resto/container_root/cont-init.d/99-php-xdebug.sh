#!/command/with-contenv bash
echo '[PHP_VERSION] ' ${PHP_VERSION}

if [[ ($RESTO_DEBUG = 1 || $RESTO_DEBUG = '1' || $RESTO_DEBUG = 'true') && ($PHP_ENABLE_XDEBUG = 1 || $PHP_ENABLE_XDEBUG = '1' || $PHP_ENABLE_XDEBUG = 'true') ]]; then
  echo '[debug] Enabling XDebug extension'
  sed -i 's/^;zend_extension/zend_extension/' /etc/php/${PHP_VERSION}/mods-available/xdebug.ini
  # TODO remove once the new xdebug.ini is deployed
  sed -i 's/9009/9003/' /etc/php/${PHP_VERSION}/mods-available/xdebug.ini
  echo "" >>/etc/php/${PHP_VERSION}/mods-available/xdebug.ini
  echo "xdebug.mode = debug" >>/etc/php/${PHP_VERSION}/mods-available/xdebug.ini
  echo "xdebug.start_with_request = yes" >>/etc/php/${PHP_VERSION}/mods-available/xdebug.ini
  echo "xdebug.discover_client_host = 1" >>/etc/php/${PHP_VERSION}/mods-available/xdebug.ini

  echo ''
  if [ -x "$(command -v phpenmod)" ]; then
    phpenmod xdebug
  fi
else
  echo '[debug] XDebug remains disabled'
fi
