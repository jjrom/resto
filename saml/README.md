# SAML HowTo

Testing resto as Service Provider with local IdP

    docker run --name=testsamlidp_idp \
    -p 8080:8080 \
    -p 8443:8443 \
    -e SIMPLESAMLPHP_SP_ENTITY_ID=http://app.example.com \
    -e SIMPLESAMLPHP_SP_ASSERTION_CONSUMER_SERVICE=http://localhost:5252/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp \
    -e SIMPLESAMLPHP_SP_SINGLE_LOGOUT_SERVICE=http://localhost:5252/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp \
    kristophjunge/test-saml-idp
