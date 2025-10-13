<?php

namespace Fastfony\IdentityBundle\Repository;

use Fastfony\IdentityBundle\Entity\Identity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @extends ServiceEntityRepository<Role>
 */
abstract class RoleRepository extends ServiceEntityRepository
{
    use PersistenceTrait;

    public function __construct(
        ManagerRegistry $registry,
        #[Autowire('%fastfony_identity.role.class%')]
        string $entityClass
    ) {
        parent::__construct($registry, $entityClass);
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
