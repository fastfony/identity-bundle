<?php

namespace Fastfony\IdentityBundle\Manager;

use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserManager
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire('%fastfony_identity.user.class%')]
        private readonly string $userClass
    ) {
    }

    public function create(
        string $email,
        ?string $plainPassword = null,
    ): User {
        $user = new ($this->userClass)();
        
        $user->setEmail($email);

        if (null === $plainPassword) {
            // Generate a random password if none is provided
            $plainPassword = bin2hex(random_bytes(10));
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        return $user;
    }

    public function updatePassword(
        PasswordAuthenticatedUserInterface $user,
        string $plainPassword
    ): void {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
    }

    public function enable(User $user): void
    {
        $user->setEnabled(true);
    }

    public function disable(User $user): void
    {
        $user->setEnabled(false);
    }

    public function updateLastLogin(User $user): void
    {
        $user->setLastLogin(new \DateTimeImmutable());
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function save(User $user): void
    {
        $this->userRepository->save($user, true);
    }

    public function delete(User $user): void
    {
        $this->userRepository->remove($user, true);
    }
}
