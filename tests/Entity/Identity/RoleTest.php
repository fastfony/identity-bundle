<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Entity\Identity;

use Fastfony\IdentityBundle\Entity\Identity\Role;
use Fastfony\IdentityBundle\Entity\Identity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Role::class)]
final class RoleTest extends TestCase
{
    public function testAddUser(): void
    {
        $role = new Role();
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('addRole')
            ->with($role);

        $this->assertCount(0, $role->getUsers());
        $role->addUser($user);
        $this->assertCount(1, $role->getUsers());
        $this->assertTrue($role->getUsers()->contains($user));
    }

    public function testRemoveUser(): void
    {
        $role = new Role();
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('removeRole')
            ->with($role);
        $user->expects($this->once())
            ->method('addRole')
            ->with($role);

        $role->addUser($user);
        $this->assertCount(1, $role->getUsers());
        $role->removeUser($user);
        $this->assertCount(0, $role->getUsers());
        $this->assertFalse($role->getUsers()->contains($user));
    }
}

