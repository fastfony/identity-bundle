# FastfonyIdentityBundle

A Symfony bundle focused on identification and the management of users, roles and groups.

## Features

- 👤 **User Management**: Complete user entity with email/username authentication
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

## Configuration

### 1. Enable the Bundle

If you're using Symfony Flex, the bundle will be automatically enabled. Otherwise, add it to `config/bundles.php`:

```php
return [
    // ...
    Fastfony\IdentityBundle\FastfonyIdentityBundle::class => ['all' => true],
];
```

### 2. Configure the Bundle

Create a configuration file `config/packages/fastfony_identity.yaml`:

```yaml
fastfony_identity:
    user:
        class: 'App\Entity\User'
        email_canonical: true
    role:
        class: 'App\Entity\Role'
        default_role: 'ROLE_USER'
    group:
        class: 'App\Entity\Group'
        enabled: true
```

### 3. Create Your Entities

The bundle provides abstract base classes. Extend them in your application:

**User Entity** (`src/Entity/User.php`):

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fastfony\IdentityBundle\Entity\Identity\User as BaseUser;
use App\Repository\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User extends BaseUser
{
    // Add custom fields here
}
```

**Role Entity** (`src/Entity/Role.php`):

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fastfony\IdentityBundle\Entity\Identity\Role as BaseRole;
use App\Repository\RoleRepository;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: 'roles')]
class Role extends BaseRole
{
    // Add custom fields here
}
```

**Group Entity** (`src/Entity/Group.php`):

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fastfony\IdentityBundle\Entity\Identity\Group as BaseGroup;
use App\Repository\GroupRepository;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: 'groups')]
class Group extends BaseGroup
{
    // Add custom fields here
}
```

### 4. Create Your Repositories

**UserRepository** (`src/Repository/UserRepository.php`):

```php
<?php

namespace App\Repository;

use App\Entity\User;
use Fastfony\IdentityBundle\Repository\UserRepository as BaseUserRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends BaseUserRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
}
```

**RoleRepository** (`src/Repository/RoleRepository.php`):

```php
<?php

namespace App\Repository;

use App\Entity\Role;
use Fastfony\IdentityBundle\Repository\RoleRepository as BaseRoleRepository;
use Doctrine\Persistence\ManagerRegistry;

class RoleRepository extends BaseRoleRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }
}
```

**GroupRepository** (`src/Repository/GroupRepository.php`):

```php
<?php

namespace App\Repository;

use App\Entity\Group;
use Fastfony\IdentityBundle\Repository\GroupRepository as BaseGroupRepository;
use Doctrine\Persistence\ManagerRegistry;

class GroupRepository extends BaseGroupRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }
}
```

### 5. Update Security Configuration

Configure Symfony Security to use your User entity in `config/packages/security.yaml`:

```yaml
security:
    password_hashers:
        App\Entity\User:
            algorithm: auto

    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email

    firewalls:
        main:
            lazy: true
            provider: app_user_provider
            # Configure your authentication methods here
```

### 6. Create Database Schema

Run migrations to create the database tables:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## Usage

### User Management

```php
use Fastfony\IdentityBundle\Manager\UserManager;

class YourController
{
    public function __construct(
        private UserManager $userManager
    ) {}

    public function createUser(): void
    {
        // Create a new user
        $user = $this->userManager->createUser(
            'user@example.com',
            'password123',
            'username'
        );
        
        $this->userManager->saveUser($user);
    }

    public function updatePassword(): void
    {
        $user = $this->userManager->findUserByEmail('user@example.com');
        
        if ($user) {
            $this->userManager->updatePassword($user, 'newpassword');
            $this->userManager->saveUser($user);
        }
    }

    public function disableUser(): void
    {
        $user = $this->userManager->findUserByEmail('user@example.com');
        
        if ($user) {
            $this->userManager->disableUser($user);
            $this->userManager->saveUser($user);
        }
    }
}
```

### Role Management

```php
use Fastfony\IdentityBundle\Manager\RoleManager;

class YourController
{
    public function __construct(
        private RoleManager $roleManager
    ) {}

    public function createRole(): void
    {
        $role = $this->roleManager->createRole(
            'ROLE_ADMIN',
            'Administrator role'
        );
        
        $this->roleManager->saveRole($role);
    }

    public function assignRole(): void
    {
        $role = $this->roleManager->findRoleByName('ROLE_ADMIN');
        $user = $this->userManager->findUserByEmail('user@example.com');
        
        if ($role && $user) {
            $user->addRole($role);
            $this->userManager->saveUser($user);
        }
    }
}
```

### Group Management

```php
use Fastfony\IdentityBundle\Manager\GroupManager;

class YourController
{
    public function __construct(
        private GroupManager $groupManager
    ) {}

    public function createGroup(): void
    {
        $group = $this->groupManager->createGroup(
            'Developers',
            'Development team'
        );
        
        $this->groupManager->saveGroup($group);
    }

    public function addUserToGroup(): void
    {
        $group = $this->groupManager->findGroupByName('Developers');
        $user = $this->userManager->findUserByEmail('user@example.com');
        
        if ($group && $user) {
            $group->addUser($user);
            $this->groupManager->saveGroup($group);
        }
    }
}
```

## Entity Structure

### User Entity

- `id`: Primary key
- `email`: Unique email address
- `username`: Optional unique username
- `password`: Hashed password
- `enabled`: Account status
- `roles`: Many-to-many relation with Role
- `groups`: Many-to-many relation with Group
- `createdAt`: Account creation timestamp
- `updatedAt`: Last update timestamp
- `lastLogin`: Last login timestamp

### Role Entity

- `id`: Primary key
- `name`: Unique role name (e.g., ROLE_ADMIN)
- `description`: Optional description
- `users`: Many-to-many relation with User
- `createdAt`: Creation timestamp

### Group Entity

- `id`: Primary key
- `name`: Unique group name
- `description`: Optional description
- `users`: Many-to-many relation with User
- `roles`: Many-to-many relation with Role
- `createdAt`: Creation timestamp

## Managers

The bundle provides the following manager services:

- **UserManager**: User CRUD operations and password management
- **RoleManager**: Role CRUD operations
- **GroupManager**: Group CRUD operations

All managers are auto-wired and can be injected into your controllers and services.

## Events

The bundle listens to the following events:

- **LoginSuccessEvent**: Automatically updates the `lastLogin` timestamp when a user successfully logs in

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/fastfony/identity-bundle).