<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Manager;

use Fastfony\IdentityBundle\Entity\Identity\Role;
use Fastfony\IdentityBundle\Manager\RoleManager;
use Fastfony\IdentityBundle\Repository\RoleRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoleManager::class)]
#[CoversClass(Role::class)]
final class RoleManagerTest extends TestCase
{
    private RoleRepository&MockObject $roleRepository;
    private RoleManager $roleManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleRepository = $this->createMock(RoleRepository::class);
        $this->roleManager = new RoleManager(
            roleRepository: $this->roleRepository,
            roleClass: Role::class,
        );
    }

    public function testCreateWithNameOnly(): void
    {
        $name = 'ROLE_ADMIN';
        $role = $this->roleManager->create($name);

        $this->assertSame($name, $role->getName());
        $this->assertNull($role->getDescription());
    }

    public function testCreateWithNameAndDescription(): void
    {
        $name = 'ROLE_USER';
        $description = 'Utilisateur standard';
        $role = $this->roleManager->create($name, $description);

        $this->assertSame($name, $role->getName());
        $this->assertSame($description, $role->getDescription());
    }

    public function testFindByNameReturnsRole(): void
    {
        $name = 'ROLE_ADMIN';
        $role = new Role();
        $role->setName($name);

        $this->roleRepository
            ->expects($this->once())
            ->method('findByName')
            ->with($name)
            ->willReturn($role);

        $result = $this->roleManager->findByName($name);

        $this->assertSame($role, $result);
    }

    public function testFindByNameReturnsNull(): void
    {
        $name = 'ROLE_UNKNOWN';
        $this->roleRepository
            ->expects($this->once())
            ->method('findByName')
            ->with($name)
            ->willReturn(null);

        $result = $this->roleManager->findByName($name);

        $this->assertNull($result);
    }

    public function testGetAllReturnsRoles(): void
    {
        $roles = [new Role(), new Role()];
        $this->roleRepository
            ->expects($this->once())
            ->method('findAllOrdered')
            ->willReturn($roles);

        $result = $this->roleManager->getAll();

        $this->assertSame($roles, $result);
    }

    public function testSaveCallsRepository(): void
    {
        $role = new Role();
        $this->roleRepository
            ->expects($this->once())
            ->method('save')
            ->with($role, true);

        $this->roleManager->save($role);
    }

    public function testDeleteCallsRepository(): void
    {
        $role = new Role();
        $this->roleRepository
            ->expects($this->once())
            ->method('remove')
            ->with($role, true);

        $this->roleManager->delete($role);
    }
}
