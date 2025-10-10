<?php

namespace Fastfony\IdentityBundle\Repository;

use Fastfony\IdentityBundle\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 */
abstract class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(Role $role, bool $flush = false): void
    {
        $this->getEntityManager()->persist($role);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Role $role, bool $flush = false): void
    {
        $this->getEntityManager()->remove($role);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByName(string $name): ?Role
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
