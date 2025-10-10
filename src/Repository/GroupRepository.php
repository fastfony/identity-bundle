<?php

namespace Fastfony\IdentityBundle\Repository;

use Fastfony\IdentityBundle\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Group>
 */
abstract class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(Group $group, bool $flush = false): void
    {
        $this->getEntityManager()->persist($group);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Group $group, bool $flush = false): void
    {
        $this->getEntityManager()->remove($group);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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
