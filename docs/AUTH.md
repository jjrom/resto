# Auth
The Auth class is an authentication addon that enables integration with external Single Sign-On (SSO) providers. Its main purpose is to allow users to authenticate using third-party identity providers such as Google, LinkedIn, or any service supporting OAuth 1.0 or OAuth 2.0 protocols.

Key features:

* Supports predefined providers (e.g., Google, LinkedIn) with built-in configuration.
* Allows generic OAuth2/OAuth1 authentication for custom providers.
* Handles provider-specific token exchange and user information retrieval.
* Maps external user attributes to resto's internal user model.
* Can be extended to support additional authentication flows or providers.

This class is essential for enabling federated identity and seamless user login experiences in resto deployments, especially in multi-tenant or enterprise environments.

## Configuring Authentication Providers
Providers are configured using a single string with the following format:


        providerId1|clientId1|clientSecret1|(accessTokenUrl)|(peopleApiUrl)|(openidConfigurationUrl)|(mapping);providerId2|clientId2|clientSecret2;...

* Each provider is separated by a semicolon ;.
* Each provider's fields are separated by a pipe |.
* Fields in parentheses () are optional.

### Field Descriptions

* providerId: Unique identifier for the provider (e.g., edito, theia)
* clientId: OAuth2/OpenID client ID
* clientSecret: OAuth2/OpenID client secret
* accessTokenUrl (optional): URL to obtain access tokens
* peopleApiUrl (optional): URL to fetch user info
* openidConfigurationUrl (optional): OpenID configuration endpoint
* mapping (optional): Maps IdP property names to resto property names, e.g. "email=email,firstname=given_name,lastname=family_name"

### Example

        edito|edito|secret|https://auth.dive.edito.eu/auth/realms/datalab/protocol/openid-connect/token|https://auth.dive.edito.eu/auth/realms/datalab/protocol/openid-connect/userinfo|https://auth.dive.edito.eu/auth/realms/datalab/.well-known/openid-configuration

### Example with Mapping

        theia|theia-client|secret|https://theia.example.com/token|https://theia.example.com/userinfo||email=email,firstname=given_name,lastname=family_name

### Usage

* Place the provider string in the ADDON_AUTH_PROVIDERS environment variable (or in config.env)

* Ensure all required fields for your provider are present.
* Use the mapping field to align IdP user attributes with resto's expected fields.

This format allows you to flexibly configure multiple authentication providers for your resto instance.