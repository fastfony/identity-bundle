# Configuration (if you didn't use Flex)

If you're using Symfony Flex, the configuration file will be created automatically.

Otherwise, manually do it:

Create or edit Doctrine Extensions config file in `config/packages/stof_doctrine_extensions.yaml`:

```yaml
stof_doctrine_extensions:
    default_locale: en_US
    orm:
        default:
            timestampable: true
```

Edit security settings in `config/packages/security.yaml` for add the firewall, an user provider and a new access control rule:

```yaml
security:
    # ...
    providers:
        # ...
        fastfony_identity_user_provider:
            entity:
                class: Fastfony\IdentityBundle\Entity\Identity\User
                property: email

    # ...
    firewalls:
        dev:
            # ...
        fastfony_identity:
            lazy: true
            provider: fastfony_identity_user_provider
            user_checker: Fastfony\IdentityBundle\Security\UserChecker
            form_login:
                login_path: form_login
                check_path: form_login
                enable_csrf: true
                csrf_token_id: login
                form_only: true
                default_target_path: fastfony_identity_secure_area
            login_link:
                check_route: login_check
                signature_properties: [ id, email ]
                max_uses: 3
                default_target_path: fastfony_identity_secure_area
            entry_point: Fastfony\IdentityBundle\Security\CustomEntryPoint
            remember_me:
                always_remember_me: true
                signature_properties: [ 'id', 'email', 'password' ]
            switch_user: true
            login_throttling:
                max_attempts: 3
            logout:
                path: /logout
                clear_site_data:
                    - cookies
                    - storage
        # ... (here your other firewalls)

    access_control:
        # ...
        - { path: ^/secure-area/, roles: ROLE_USER } # Adjust as needed
```

Import the bundle routing, create file `config/routes/fastfony_identity.yaml` with this content:

```yaml
fastfony_identity:
    resource: "@FastfonyIdentityBundle/config/routes/all.yaml"
```

Configure the default sender email address in `config/packages/mailer.yaml`:

```yaml
framework:
    mailer:
        dsn: '%env(MAILER_DSN)%'
        envelope:
            sender: 'noreply@your-website.com'
```

(don't forget to set the `MAILER_DSN` environment variable in your `.env` file, more info [here](https://symfony.com/doc/current/mailer.html#transport-setup) and configure send messages async or not, more info [here](https://symfony.com/doc/current/mailer.html#sending-messages-async))
