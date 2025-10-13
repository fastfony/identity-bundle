<?php

namespace Fastfony\IdentityBundle\Repository;

use Fastfony\IdentityBundle\Entity\Identity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @extends ServiceEntityRepository<Group>
 */
abstract class GroupRepository extends ServiceEntityRepository
{
    use PersistenceTrait;

    public function __construct(
        ManagerRegistry $registry,
        #[Autowire('%fastfony_identity.group.class%')]
        string $entityClass
    ) {
        parent::__construct($registry, $entityClass);
    }

    public function findByName(string $name): ?Group
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
