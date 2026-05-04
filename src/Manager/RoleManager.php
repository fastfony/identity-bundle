<?php

namespace Fastfony\IdentityBundle\Manager;

use Fastfony\IdentityBundle\Entity\Identity\Role;
use Fastfony\IdentityBundle\Repository\RoleRepository;

class RoleManager
{
    public function __construct(
        private readonly RoleRepository $roleRepository,
    ) {
    }

    public function create(string $name, ?string $description = null): Role
    {
        $role = new Role();
        
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
