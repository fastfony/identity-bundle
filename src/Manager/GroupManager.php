<?php

namespace Fastfony\IdentityBundle\Manager;

use Fastfony\IdentityBundle\Entity\Identity\Group;
use Fastfony\IdentityBundle\Repository\GroupRepository;

class GroupManager
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
    ) {
    }

    public function create(string $name, ?string $description = null): Group
    {
        $group = new Group();
        
        $group->setName($name);
        if ($description) {
            $group->setDescription($description);
        }

        return $group;
    }

    public function findByName(string $name): ?Group
    {
        return $this->groupRepository->findByName($name);
    }

    public function getAll(): array
    {
        return $this->groupRepository->findAllOrdered();
    }

    public function save(Group $group): void
    {
        $this->groupRepository->save($group, true);
    }

    public function delete(Group $group): void
    {
        $this->groupRepository->remove($group, true);
    }
}
