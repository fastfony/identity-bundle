# Miscellaneous 

## Entity Structure

### User Entity

- `id`: Primary key
- `email`: Unique email address
- `password`: Hashed password
- `enabled`: Account status
- `roles`: Many-to-many relation with Role
- `groups`: Many-to-many relation with Group
- `createdAt`: Account creation timestamp (auto-managed by Timestampable trait)
- `updatedAt`: Last update timestamp (auto-managed by Timestampable trait)
- `lastLogin`: Last login timestamp

### Role Entity

- `id`: Primary key
- `name`: Unique role name (e.g., ROLE_ADMIN)
- `description`: Optional description
- `users`: Many-to-many relation with User
- `createdAt`: Creation timestamp (auto-managed by Timestampable trait)
- `updatedAt`: Last update timestamp (auto-managed by Timestampable trait)

### Group Entity

- `id`: Primary key
- `name`: Unique group name
- `description`: Optional description
- `users`: Many-to-many relation with User
- `roles`: Many-to-many relation with Role
- `createdAt`: Creation timestamp (auto-managed by Timestampable trait)
- `updatedAt`: Last update timestamp (auto-managed by Timestampable trait)

## Managers

The bundle provides the following manager services:

- **UserManager**: User CRUD operations and password management
    - `create()`: Create a new user
    - `save()`: Persist user to database (always flushes)
    - `delete()`: Remove user from database (always flushes)
    - `findByEmail()`: Find user by email
    - `updatePassword()`: Update user password
    - `enable()`/`disable()`: Enable/disable user account
    - `updateLastLogin()`: Update last login timestamp

- **RoleManager**: Role CRUD operations
    - `create()`: Create a new role
    - `save()`: Persist role to database (always flushes)
    - `delete()`: Remove role from database (always flushes)
    - `findByName()`: Find role by name
    - `getAll()`: Get all roles ordered by name

- **GroupManager**: Group CRUD operations
    - `create()`: Create a new group
    - `save()`: Persist group to database (always flushes)
    - `delete()`: Remove group from database (always flushes)
    - `findByName()`: Find group by name
    - `getAll()`: Get all groups ordered by name

All managers are auto-wired and can be injected into your controllers and services. The managers use the `#[Autowire]` attribute internally for configuration parameter injection.

### Example of usage for UserManager

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
        $user = $this->userManager->create(
            'user@example.com',
            'password123',
        );
        
        $this->userManager->save($user);
    }

    public function updatePassword(): void
    {
        $user = $this->userManager->findByEmail('user@example.com');
        
        if ($user) {
            $this->userManager->updatePassword($user, 'newpassword');
            $this->userManager->save($user);
        }
    }

    public function disableUser(): void
    {
        $user = $this->userManager->findByEmail('user@example.com');
        
        if ($user) {
            $this->userManager->disable($user);
            $this->userManager->save($user);
        }
    }
}
```

### Example of usage for RoleManager

```php
use Fastfony\IdentityBundle\Manager\RoleManager;

class YourController
{
    public function __construct(
        private RoleManager $roleManager
    ) {}

    public function createRole(): void
    {
        $role = $this->roleManager->create(
            'ROLE_ADMIN',
            'Administrator role'
        );
        
        $this->roleManager->save($role);
    }

    public function assignRole(): void
    {
        $role = $this->roleManager->findByName('ROLE_ADMIN');
        $user = $this->userManager->findByEmail('user@example.com');
        
        if ($role && $user) {
            $user->addRole($role);
            $this->userManager->save($user);
        }
    }
}
```

### Example of usage for GroupManager

```php
use Fastfony\IdentityBundle\Manager\GroupManager;

class YourController
{
    public function __construct(
        private GroupManager $groupManager
    ) {}

    public function createGroup(): void
    {
        $group = $this->groupManager->create(
            'Developers',
            'Development team'
        );
        
        $this->groupManager->save($group);
    }

    public function addUserToGroup(): void
    {
        $group = $this->groupManager->findByName('Developers');
        $user = $this->userManager->findByEmail('user@example.com');
        
        if ($group && $user) {
            $group->addUser($user);
            $this->groupManager->save($group);
        }
    }
}
```

## Events

The bundle listens to the following events:

- **LoginSuccessEvent**: Automatically updates the `lastLogin` timestamp when a user successfully logs in