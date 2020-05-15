# secure-jwt
Library that makes JWT more secure

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
            success_handler:          ConnectHolland\SecureJWT\Security\Http\Authentication\AuthenticationSuccessHandler
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
            ConnectHolland\SecureJWT:
                is_bundle: false
                type: annotation
                dir: '%kernel.project_dir%/vendor/connectholland/secure-jwt/src/Entity'
                prefix: 'ConnectHolland\SecureJWT\Entity'
                alias: SecureJWT
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
        paths: ['%kernel.project_dir%/vendor/connectholland/secure-jwt/src/Message']
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
                - ConnectHolland\SecureJWT\Security\Guard\JWTTokenAuthenticator
```
