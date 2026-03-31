# FastfonyIdentityBundle

A Symfony bundle focused on identification and the management of users, roles and groups.

## Features

- 👤 **User Management**: Complete user entity with email authentication
- 🆔 **User Access**: Login with form or magic login link
- ⏳ **Account Activation**: Optional email verification for new users
- 🔄 **Password Management**: Secure password hashing and reset functionality
- 📧 **Email Integration**: Built-in email templates for login links and password resets
- 🕒 **Last Login Tracking**: Automatically updates last login timestamp
- 🔐 **Role-Based Access Control**: Flexible role system with many-to-many relationships
- 👥 **Group Support**: Organize users into groups with inherited roles
- 🔒 **Security Integration**: Full integration with Symfony Security component
- 📊 **Doctrine ORM**: Ready-to-use entities with Doctrine annotations
- ⚙️ **Configurable**: Easy configuration through YAML files

## Installation

Install the bundle via Composer:

```bash
composer require fastfony/identity-bundle
```

This will automatically install the required dependencies including:
- `stof/doctrine-extensions-bundle` for automatic timestamp management
- `symfony/mailer symfony/notifier symfony/twig-bundle twig/extra-bundle twig/cssinliner-extra twig/inky-extra` for email functionalities
- `symfony/rate-limiter` for login throttling
- `symfony/translation` for translations
- `symfony/uid` for unique identifiers (in the reset functionality)
- `symfony/form` for register, login and password request form view and handling

## Configuration

### 1. Enable the Bundle

If you're using Symfony Flex and you have play the recipe for the bundle, the bundle will be automatically enabled and config files are created.

Otherwise, add it to `config/bundles.php`:
```php
return [
    // ...
    Fastfony\IdentityBundle\FastfonyIdentityBundle::class => ['all' => true],
    Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true],
];
```

### 2. Configuration (if you didn't use Flex)

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
            login_link:
                check_route: login_check
                signature_properties: [ id, email ]
                max_uses: 3
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

Import the bundle routing in `config/routes/fastfony_identity.yaml`:

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

### 3. Create Database Schema

#### Option 1 - With migrations (recommended):

Generate and run migrations to create the database tables:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

#### Option 2 - Without migrations:

Alternatively, you can create the schema directly:

```bash
php bin/console doctrine:schema:update --force
```

**It's done!** 🥳 You can now start using the bundle.

### 4. Register your first user

Go to the `/register` route in order to create the first user account.

By default, routes behind /secure-area require the user logged in.

More detailed usage instructions and customizations can be found in the [Documentation](docs/index.md).

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/fastfony/identity-bundle).
