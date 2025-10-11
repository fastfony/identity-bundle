<?php

namespace Fastfony\IdentityBundle\Manager;

use Fastfony\IdentityBundle\Entity\Identity\Group;
use Fastfony\IdentityBundle\Repository\GroupRepository;

class GroupManager
{
    public function __construct(
        private GroupRepository $groupRepository,
        private string $groupClass
    ) {
    }

    public function createGroup(string $name, ?string $description = null): Group
    {
        $group = new ($this->groupClass)();
        
        $group->setName($name);
        if ($description) {
            $group->setDescription($description);
        }

        return $group;
    }

    public function findGroupByName(string $name): ?Group
    {
        return $this->groupRepository->findByName($name);
    }

    public function getAllGroups(): array
    {
        return $this->groupRepository->findAllOrdered();
    }

    public function saveGroup(Group $group, bool $flush = true): void
    {
        $this->groupRepository->save($group, $flush);
    }

    public function deleteGroup(Group $group, bool $flush = true): void
    {
        $this->groupRepository->remove($group, $flush);
    }
}
