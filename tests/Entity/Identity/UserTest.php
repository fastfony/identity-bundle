<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Entity\Identity;

use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\Entity\Identity\Role;
use Fastfony\IdentityBundle\Entity\Identity\Group;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    public function testGetRolesDefault(): void
    {
        $user = new User();
        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        $this->assertIsArray($roles);
        $this->assertCount(1, $roles);
    }

    public function testAddRoleAndRemoveRole(): void
    {
        $user = new User();
        $role = $this->createMock(Role::class);
        $role->method('getName')->willReturn('ROLE_ADMIN');

        $this->assertNotContains('ROLE_ADMIN', $user->getRoles());
        $user->addRole($role);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $user->removeRole($role);
        $this->assertNotContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testAddGroupAndRemoveGroup(): void
    {
        $user = new User();
        $group = $this->createMock(Group::class);

        $this->assertCount(0, $user->getGroups());
        $user->addGroup($group);
        $this->assertCount(1, $user->getGroups());
        $this->assertTrue($user->getGroups()->contains($group));
        $user->removeGroup($group);
        $this->assertCount(0, $user->getGroups());
        $this->assertFalse($user->getGroups()->contains($group));
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $this->assertNull($user->eraseCredentials());
    }
}

