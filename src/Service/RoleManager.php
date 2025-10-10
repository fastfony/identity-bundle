<?php

namespace Fastfony\IdentityBundle\Service;

use Fastfony\IdentityBundle\Entity\Role;
use Fastfony\IdentityBundle\Repository\RoleRepository;

class RoleManager
{
    public function __construct(
        private RoleRepository $roleRepository
    ) {
    }

    public function createRole(string $name, ?string $description = null): Role
    {
        $roleClass = $this->roleRepository->getClassName();
        $role = new $roleClass();
        
        $role->setName($name);
        if ($description) {
            $role->setDescription($description);
        }
        
        return $role;
    }

    public function findRoleByName(string $name): ?Role
    {
        return $this->roleRepository->findByName($name);
    }

    public function getAllRoles(): array
    {
        return $this->roleRepository->findAllOrdered();
    }

    public function saveRole(Role $role, bool $flush = true): void
    {
        $this->roleRepository->save($role, $flush);
    }

    public function deleteRole(Role $role, bool $flush = true): void
    {
        $this->roleRepository->remove($role, $flush);
    }
}
