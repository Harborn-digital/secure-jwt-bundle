# Secure JWT Bundle
Symfony bundle that makes JWT more secure

## Install
Installation is not fluent and error free yet, but it is easy to work around:

```bash
composer require connectholland/secure-jwt-bundle
```

Will give error in post installation:

```
Cannot autowire service "ConnectHolland\SecureJWTBundle\EventSubscriber\LoginSubscriber": argument "$googleAuthenticator" of method "__construct()" references class "Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator" but no such service exists.
```

Configure scheb twofactor Google:

In the `scheb_two_factor.yaml` file:

```yaml
scheb_two_factor:
    security_tokens:
        - Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken
    google:
        enabled: true
        server_name: Secure Server
        issuer: Connect Holland
        digits: 6
        window: 1
```

Run

```bash
composer require connectholland/secure-jwt-bundle
```

Again to finish the installation. 

BTW1: Installation and configuration of the scheb twofactor bundle before installation of this bundle will also prevent this error.<br/> 
BTW2: of course a PR that fixes these issues is welcome :)

## Cookie storage
Tokens in local storage are insecure, so if you use tokens from a web interface you should store them somewhere else. A secure cookie is a good location. Configure cookie storage as follows:

### Let the lexik/jwt-authentication-bundle look at cookies:

In the `lexik_jwt_authentication.yaml` config file:
```yaml
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'

    token_extractors:
            # Default header auth, can be useful to allow for other auth types (for example /api)
            authorization_header:
                enabled: true

            # Make sure this is enabled
            cookie:
                enabled: true
                name:    BEARER
                set_cookies:
                    BEARER: ~
```

### Make sure the token is set as a secure cookie

In the `security.yaml` config file:
```yaml
    login:
        pattern:  ^/api/login
        stateless: true
        anonymous: true
        json_login:
            check_path:               /api/login_check
            success_handler:          lexik_jwt_authentication.handler.authentication_success
            failure_handler:          lexik_jwt_authentication.handler.authentication_failure
```

## Invalidate tokens
By default tokens are valid until they expire. This makes is impossible to really log out. You can configure token invalidatation to allow logouts:

### Create database table

In the `doctrine.yaml` file:
```yaml
doctrine:
    orm:
        mappings:
            ConnectHolland\SecureJWTBundle:
                is_bundle: true
                type: annotation
                dir: '%kernel.project_dir%/vendor/connectholland/secure-jwt-bundle/src/Entity'
                prefix: 'ConnectHolland\SecureJWTBundle\Entity'
                alias: SecureJWTBundle
```

And run migrations:
```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate -n
```

### Configure API endpoint logout

In the `api_platform.yaml` file:

```yaml
api_platform:
    mapping:
        paths: ['%kernel.project_dir%/vendor/connectholland/secure-jwt-bundle/src/Message']
```

Of course do not remove other required paths that might already be in the `paths` configuration.

There will be a `logout` endpoint in your API. This endpoint requires a message formatted like:

```json
{
  "logout": "some string"
}
```

The value of logout is not important and not used. This field is required because API platform requires at least one field in the message. (A better solution for this is welcome).

### Do not allow invalidated tokens

In the `security.yaml` file:

```yaml
    api:
        pattern: ^/api
        stateless: true
        anonymous: true
        guard:
            authenticators:
                - ConnectHolland\SecureJWTBundle\Security\Guard\JWTTokenAuthenticator
```

## Refresh token
Can be implemented after requiring the suggested package.
### Configure refresh token route
In the `routes.yaml` file:
```yaml
gesdinet_jwt_refresh_token:
  path:       /api/token/refresh
  controller: gesdinet.jwtrefreshtoken::refresh
```

In the `security.yaml` file:
```yaml
    refresh:
        pattern:  ^/token/refresh
        stateless: true
        anonymous: true

    access_control:
           - { path: ^/api/token/refresh, roles: IS_AUTHENTICATED_ANONYMOUSLY }
```

### Configure Token duration and user identity field
In the `config/packages/gesdinet_jwt_refresh_token.yaml` file:
```yaml
gesdinet_jwt_refresh_token:
  ttl: 2592000 
  user_identity_field: email 
```

## Two Factor Authentication in JWT

### Configure Google Authenticator

In the `scheb_two_factor.yaml` file:

```yaml
scheb_two_factor:
    security_tokens:
        - Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken
    google:
        enabled: true
        server_name: Secure Server
        issuer: Connect Holland
        digits: 6
        window: 1
```

### Use the two_factor_jwt security listener and provider

In the `security.yaml` file:

```yaml
    login:
        pattern:  ^/api/login
        stateless: true
        anonymous: true        
        two_factor_jwt:
            check_path:               /api/login_check
            success_handler:          ConnectHolland\SecureJWTBundle\Security\Http\Authentication\AuthenticationSuccessHandler
            failure_handler:          ConnectHolland\SecureJWTBundle\Security\Http\Authentication\AuthenticationFailureHandler
```

### Implement the right interfaces

Your User object should implement `ConnectHolland\SecureJWTBundle\Entity\TwoFactorUserInterface`.

## Using 2FA

```bash
curl -X POST http://host/api/users/authenticate -H 'Content-Type: application/json' -d '{"username": "username", "password": "password"}'
```

This will give the following response:
```json
{
  "result":"ok",
  "status":"two factor authentication required"
}
```

If 2FA is not yet setup you will receive:

```json
{
  "result":"ok",
  "message":"use provided QR code to set up two factor authentication",
  "qr":"QR code (data URL)"
}
```

In the next call add the two factor challenge:

```bash
curl -X POST http://host/api/users/authenticate -H 'Content-Type: application/json' -d '{"username": "username", "password": "password", "challenge": "123456"}'
```

If correct you'll receive:

```json
{
  "result":"ok"
}
```

The response headers will include a secure cookie containing the JWT token to allow future authenticated calls.

## Recover codes
You can retrieve recovery codes for 2FA which allow you to reset 2FA. If a valid recovery code is entered as `challenge`, 2FA will be reset and you'll get a QR code response. 
