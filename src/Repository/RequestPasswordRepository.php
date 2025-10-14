<?php

namespace Fastfony\IdentityBundle\Repository;

use Fastfony\IdentityBundle\Entity\Identity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Fastfony\IdentityBundle\Entity\RequestPassword;

/**
 * @extends ServiceEntityRepository<Group>
 */
class RequestPasswordRepository extends ServiceEntityRepository
{
    use PersistenceTrait;

    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, RequestPassword::class);
    }
}
