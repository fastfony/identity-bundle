<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Security;

use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\Security\UserChecker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(UserChecker::class)]
#[CoversClass(User::class)]
final class UserCheckerTest extends TestCase
{
    public function testCheckPreAuth(): void
    {
        $userChecker = new UserChecker();
        $user = (new User())->setEnabled(false);
        $this->expectException(CustomUserMessageAccountStatusException::class);
        $userChecker->checkPreAuth($user);

        $user = (new User())->setEnabled(true);
        // No exception should be thrown
        $userChecker->checkPreAuth($user);
    }

    public function testCheckPreAuthWithUnsupportedUser(): void
    {
        $userChecker = new UserChecker();
        $user = new class() implements UserInterface {
            public function getRoles(): array { return []; }
            public function getPassword(): ?string { return null; }
            public function getUsername(): string { return ''; }
            public function eraseCredentials(): void {}
            public function getUserIdentifier(): string { return 'user'; }
        };
        $userChecker->checkPreAuth($user);
        $this->assertTrue(true); // Dummy assertion to mark the test as passed
    }

    public function testCheckPostAuth(): void
    {
        $userChecker = new UserChecker();
        $user = (new User())->setEnabled(true);
        // No exception should be thrown
        $userChecker->checkPostAuth($user);
        $this->assertTrue(true); // Dummy assertion to mark the test as passed
    }
}

