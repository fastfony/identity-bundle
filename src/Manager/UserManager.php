<?php

namespace Fastfony\IdentityBundle\Manager;

use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private string $userClass
    ) {
    }

    public function createUser(string $email, string $plainPassword, ?string $username = null): User
    {
        $user = new ($this->userClass)();
        
        $user->setEmail($email);
        if ($username) {
            $user->setUsername($username);
        }
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        return $user;
    }

    public function updatePassword(User $user, string $plainPassword): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $user->setUpdatedAt(new \DateTimeImmutable());
    }

    public function enableUser(User $user): void
    {
        $user->setEnabled(true);
        $user->setUpdatedAt(new \DateTimeImmutable());
    }

    public function disableUser(User $user): void
    {
        $user->setEnabled(false);
        $user->setUpdatedAt(new \DateTimeImmutable());
    }

    public function updateLastLogin(User $user): void
    {
        $user->setLastLogin(new \DateTimeImmutable());
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function findUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    public function saveUser(User $user, bool $flush = true): void
    {
        $this->userRepository->save($user, $flush);
    }

    public function deleteUser(User $user, bool $flush = true): void
    {
        $this->userRepository->remove($user, $flush);
    }
}
