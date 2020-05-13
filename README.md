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
