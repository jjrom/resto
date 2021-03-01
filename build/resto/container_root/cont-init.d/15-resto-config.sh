#!/usr/bin/with-contenv bash

# Generate resto config.php from environment variables

# The file /tmp/config.php.template should exist !
CONFIG_TEMPLATE_FILE=/tmp/config.php.template
if [ ! -f ${CONFIG_TEMPLATE_FILE} ]; then
    showUsage
    echo "[GRAVE] Missing ${CONFIG_TEMPLATE_FILE} file - using default resto configuration";
    exit 1
fi

# Add-ons configuration
touch /tmp/addons.template
nbOfConfig=$(ls /cfg/*.config | wc | awk '{print $1}')
addComma=1
count=1
for config in $(ls /cfg/*.config); do
    nbOfLines=`wc -l ${config} | awk '{print $1}'`
    if [[ "${nbOfLines}" != "0" ]]; then
    
        # First addon - add a comma in front since there is at leat one add already present in template i.e. iTag
        if [[ "${addComma}" == "1" ]]; then
            echo -n ","
            addComma=0
        fi

        # Add a comma at the end of each config unless it's the last one
        echo "[CONFIG] Add add-on configuration " . $config
        if [[ "${nbOfConfig}" != "${count}" ]]; then
            cat $config | awk '{print "      "$0","}' >> /tmp/addons.template
        else
            cat $config | awk '{print "      "$0}' >> /tmp/addons.template
        fi
    fi
    count=$((count + 1))
done

# Replace __ADDONS__
sed -i -e '/__ADDONS__/{' -e 'r /tmp/addons.template' -e 'd' -e '}' ${CONFIG_TEMPLATE_FILE}

# From there we use environment variables passed during container startup
mkdir -v /etc/resto

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
