<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Security;

use Fastfony\IdentityBundle\Entity\Identity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Check if the user is enabled (already logged in the past)
        if (!$user->isEnabled() && null !== $user->getLastLogin()) {
            throw new CustomUserMessageAccountStatusException('Inactive account');
        }

        // If user is not enabled but without last login date, we can enable it
        if (!$user->isEnabled() && null === $user->getLastLogin()) {
            $user->setEnabled(true);
        }
    }

    public function checkPostAuth(
        UserInterface $user,
        ?TokenInterface $token = null
    ): void {
        // Do nothing
    }
}
