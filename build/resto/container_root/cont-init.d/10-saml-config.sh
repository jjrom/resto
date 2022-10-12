#!/usr/bin/with-contenv bash

# if SAML_SP_ENABLE is set to true, then generate /etc/simplesaml/config.php from environment variables
if [[ "${SAML_SP_ENABLE}" != "true" ]]; then
    echo "[INFO] PHP SAML SP is disabled";
    rm /etc/nginx/sites-available/default.with.saml
    exit 0
fi

echo "[INFO] PHP SAML SP is enabled";
echo "[INFO] Use default.with.saml nginx configuration as default";
mv /etc/nginx/sites-available/default.with.saml /etc/nginx/sites-available/default
