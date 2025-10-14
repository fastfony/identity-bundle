# Create Your Entities and Repositories

## 1. Create Your Entities (optional)

The bundle provides base classes. Extend them in your application if you need custom fields or methods.

**User Entity** (`src/Entity/User.php`):

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fastfony\IdentityBundle\Entity\Identity\User as BaseUser;
use App\Repository\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends BaseUser
{
    // Add custom fields here
    // Note: createdAt and updatedAt are automatically managed by the Timestampable trait
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
class Group extends BaseGroup
{
    // Add custom fields here
}
```

Configure this new entities in `config/packages/fastfony_identity.yaml` as shown above:

```yaml
fastfony_identity:
    user:
        class: 'App\Entity\User'

    role:
        class: 'App\Entity\Role'

    group:
        class: 'App\Entity\Group'
```

## 2. Create Your Repositories (optional)

Extend base repositories in your application if you need custom methods.

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
    
    // Add custom query methods here
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