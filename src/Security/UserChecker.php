<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Security;

use Fastfony\IdentityBundle\Entity\Identity\User;
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

        // Check if the user is enabled
        if (!$user->isEnabled()) {
            throw new CustomUserMessageAccountStatusException('Inactive account');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Do nothing
    }
}
