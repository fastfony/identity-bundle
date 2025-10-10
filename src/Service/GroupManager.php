<?php

namespace Fastfony\IdentityBundle\Service;

use Fastfony\IdentityBundle\Entity\Group;
use Fastfony\IdentityBundle\Repository\GroupRepository;

class GroupManager
{
    public function __construct(
        private GroupRepository $groupRepository
    ) {
    }

    public function createGroup(string $name, ?string $description = null): Group
    {
        $groupClass = $this->groupRepository->getClassName();
        $group = new $groupClass();
        
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
