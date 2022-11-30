#!/command/with-contenv bash

# Update OpenAPI documentation to match PUBLIC_ENDPOINT server url
sed -i 's|'"http://127.0.0.1:5252"'|'"${PUBLIC_ENDPOINT}"'|g' /docs/resto-api.json
sed -i 's|'"http://127.0.0.1:5252"'|'"${PUBLIC_ENDPOINT}"'|g' /docs/resto-api.html

