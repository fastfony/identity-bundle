<?php

namespace Fastfony\IdentityBundle\Manager;

use Fastfony\IdentityBundle\Entity\Identity\Group;
use Fastfony\IdentityBundle\Repository\GroupRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GroupManager
{
    public function __construct(
        private GroupRepository $groupRepository,
        #[Autowire('%fastfony_identity.group.class%')]
        private string $groupClass
    ) {
    }

    public function create(string $name, ?string $description = null): Group
    {
        $group = new ($this->groupClass)();
        
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
