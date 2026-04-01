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

If you're using Symfony Flex and you have play the recipe for the bundle, the bundle will be automatically enabled and config files will be created and you can directly read the section [Use the Bundle](#use-the-bundle).

Otherwise, add it to `config/bundles.php`:
```php
return [
    // ...
    Fastfony\IdentityBundle\FastfonyIdentityBundle::class => ['all' => true],
    Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true],
];
```

### 2. Configuration

If you don't use Symfony Flex, see [Configuration](docs/configuration.md) for the configuration of the bundle.

## Use the bundle

### 1. Create Database Schema

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

### 2. Register your first user

#### Option 1 - With command:

```bash
php bin/console fastfony:user:create
```

#### Option 2 - With your browser:

Go to the `/register` route in order to create the first user account, be sure that your MAILER_DSN is correctly configure and sending messages async is disabled (or your messages consumer command is running) for receive the login link e-mail.

### 3. Usage

By default, routes behind /secure-area require the user logged in (you can change that in `config/packages/security.yaml`).

More detailed usage instructions and customizations can be found in the [Documentation](docs/index.md).

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/fastfony/identity-bundle).
