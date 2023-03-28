#!/command/with-contenv bash

# Generate resto config.php from environment variables

# The file /tmp/config.php.template should exist !
CONFIG_TEMPLATE_FILE=/tmp/config.php.template
if [ ! -f ${CONFIG_TEMPLATE_FILE} ]; then
    echo "[GRAVE] Missing ${CONFIG_TEMPLATE_FILE} file - using default resto configuration";
    exit 1
fi

# Add-ons configuration
rm -Rf /tmp/addons.template
for config in $(ls /app/resto/addons/*/*.config); do
    cat $config >> /tmp/addons.template
    echo "" >> /tmp/addons.template
done

if [[ -f /tmp/addons.template ]]; then

    # Add comma at the end of each starting ) except the last one
    sed -i -e "$ ! s/^)/),/g" /tmp/addons.template

    # Add terminaning ), characters if needed
    hasAddOn=$(grep ")__ADDONS__" ${CONFIG_TEMPLATE_FILE})

    if [[ "${hasAddOn}" != "" ]]; then
        sed -i '1s/^/),\n/' /tmp/addons.template
    fi
    
    # Add a trailing tab to file
    sed -i 's/^/\t/' /tmp/addons.template

    # Replace __ADDONS__
    sed -i -e '/)__ADDONS__/{r /tmp/addons.template' -e 'd' -e '}' ${CONFIG_TEMPLATE_FILE}
else
    sed -i -e 's/__ADDONS__//' ${CONFIG_TEMPLATE_FILE}
fi

# From there we use environment variables passed during container startup
mkdir -p /etc/resto

# Add brackets around elements of comma separated lists
if [ ! -z "${SUPPORTED_LANGUAGES}" ]; then
    SUPPORTED_LANGUAGES=\'$(echo $SUPPORTED_LANGUAGES | sed s/,/\',\'/g)\'
fi
if [ ! -z "${CORS_WHITELIST}" ]; then
    CORS_WHITELIST=\'$(echo $CORS_WHITELIST | sed s/,/\',\'/g)\'
fi
if [ ! -z "${SEARCH_SORTABLE_FIELDS}" ]; then
    SEARCH_SORTABLE_FIELDS=\'$(echo $SEARCH_SORTABLE_FIELDS | sed s/,/\',\'/g)\'
fi
if [ ! -z "${ADDON_TAG_ITAG_TAGGERS}" ]; then
    ADDON_TAG_ITAG_TAGGERS=\'$(echo $ADDON_TAG_ITAG_TAGGERS | sed s/,/\',\'/g)\'
fi

# Awfull trick
eval "cat <<EOF
$(<${CONFIG_TEMPLATE_FILE})
EOF
" | sed s/\'\"/\'/g | sed s/\"\'/\'/g > /etc/resto/config.php