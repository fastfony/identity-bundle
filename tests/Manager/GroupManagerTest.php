<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Manager;

use Fastfony\IdentityBundle\Entity\Identity\Group;
use Fastfony\IdentityBundle\Manager\GroupManager;
use Fastfony\IdentityBundle\Repository\GroupRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(GroupManager::class)]
#[CoversClass(Group::class)]
final class GroupManagerTest extends TestCase
{
    private GroupRepository&MockObject $groupRepository;
    private GroupManager $groupManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepository::class);
        $this->groupManager = new GroupManager(
            groupRepository: $this->groupRepository,
            groupClass: Group::class,
        );
    }

    public function testCreateWithNameOnly(): void
    {
        $name = 'Admins';
        $group = $this->groupManager->create($name);

        $this->assertSame($name, $group->getName());
        $this->assertNull($group->getDescription());
    }

    public function testCreateWithNameAndDescription(): void
    {
        $name = 'Users';
        $description = 'Standard users';
        $group = $this->groupManager->create($name, $description);

        $this->assertSame($name, $group->getName());
        $this->assertSame($description, $group->getDescription());
    }

    public function testFindByNameReturnsGroup(): void
    {
        $name = 'Admins';
        $group = new Group();
        $group->setName($name);

        $this->groupRepository
            ->expects($this->once())
            ->method('findByName')
            ->with($name)
            ->willReturn($group);

        $result = $this->groupManager->findByName($name);

        $this->assertSame($group, $result);
    }

    public function testFindByNameReturnsNull(): void
    {
        $name = 'Unknown';
        $this->groupRepository
            ->expects($this->once())
            ->method('findByName')
            ->with($name)
            ->willReturn(null);

        $result = $this->groupManager->findByName($name);

        $this->assertNull($result);
    }

    public function testGetAllReturnsGroups(): void
    {
        $groups = [new Group(), new Group()];
        $this->groupRepository
            ->expects($this->once())
            ->method('findAllOrdered')
            ->willReturn($groups);

        $result = $this->groupManager->getAll();

        $this->assertSame($groups, $result);
    }

    public function testSaveCallsRepository(): void
    {
        $group = new Group();
        $this->groupRepository
            ->expects($this->once())
            ->method('save')
            ->with($group, true);

        $this->groupManager->save($group);
    }

    public function testDeleteCallsRepository(): void
    {
        $group = new Group();
        $this->groupRepository
            ->expects($this->once())
            ->method('remove')
            ->with($group, true);

        $this->groupManager->delete($group);
    }
}
