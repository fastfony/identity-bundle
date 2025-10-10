<?php

namespace Fastfony\IdentityBundle\Repository;

use Fastfony\IdentityBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
abstract class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function save(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->remove($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User must be an instance of ' . User::class);
        }

        $user->setPassword($newHashedPassword);
        $this->save($user, true);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    public function findEnabledUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
