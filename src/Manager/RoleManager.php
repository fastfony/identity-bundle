<?php

namespace Fastfony\IdentityBundle\Manager;

use Fastfony\IdentityBundle\Entity\Identity\Role;
use Fastfony\IdentityBundle\Repository\RoleRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class RoleManager
{
    public function __construct(
        private RoleRepository $roleRepository,
        #[Autowire('%fastfony_identity.role.class%')]
        private string $roleClass
    ) {
    }

    public function create(string $name, ?string $description = null): Role
    {
        $role = new ($this->roleClass)();
        
        $role->setName($name);
        if ($description) {
            $role->setDescription($description);
        }

        return $role;
    }

    public function findByName(string $name): ?Role
    {
        return $this->roleRepository->findByName($name);
    }

    public function getAll(): array
    {
        return $this->roleRepository->findAllOrdered();
    }

    public function save(Role $role): void
    {
        $this->roleRepository->save($role, true);
    }

    public function delete(Role $role): void
    {
        $this->roleRepository->remove($role, true);
    }
}
