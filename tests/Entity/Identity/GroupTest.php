<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Entity\Identity;

use Fastfony\IdentityBundle\Entity\Identity\Group;
use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\Entity\Identity\Role;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;

#[CoversClass(Group::class)]
final class GroupTest extends TestCase
{
    public function testAddUser(): void
    {
        $group = new Group();
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('addGroup')
            ->with($group);

        $this->assertCount(0, $group->getUsers());
        $group->addUser($user);
        $this->assertCount(1, $group->getUsers());
        $this->assertTrue($group->getUsers()->contains($user));
    }

    public function testRemoveUser(): void
    {
        $group = new Group();
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('removeGroup')
            ->with($group);
        $user->expects($this->once())
            ->method('addGroup')
            ->with($group);

        $group->addUser($user);
        $this->assertCount(1, $group->getUsers());
        $group->removeUser($user);
        $this->assertCount(0, $group->getUsers());
        $this->assertFalse($group->getUsers()->contains($user));
    }

    public function testAddRole(): void
    {
        $group = new Group();
        $role = $this->createMock(Role::class);
        $this->assertCount(0, $group->getRoles());
        $group->addRole($role);
        $this->assertCount(1, $group->getRoles());
        $this->assertTrue($group->getRoles()->contains($role));
    }

    public function testRemoveRole(): void
    {
        $group = new Group();
        $role = $this->createMock(Role::class);
        $group->addRole($role);
        $this->assertCount(1, $group->getRoles());
        $group->removeRole($role);
        $this->assertCount(0, $group->getRoles());
        $this->assertFalse($group->getRoles()->contains($role));
    }
}

