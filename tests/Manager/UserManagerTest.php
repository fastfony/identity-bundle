<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Manager;

use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\Manager\UserManager;
use Fastfony\IdentityBundle\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[CoversClass(UserManager::class)]
#[CoversClass(User::class)]
final class UserManagerTest extends TestCase
{
    private UserRepository&MockObject $userRepository;
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private UserManager $userManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->userManager = new UserManager(
            userRepository: $this->userRepository,
            passwordHasher: $this->passwordHasher,
            userClass: User::class,
        );
    }

    public function testCreateWithEmailAndPassword(): void
    {
        $email = 'foo@example.com';
        $plainPassword = 'secret';
        $hashedPassword = 'hashed_secret';

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(User::class), $plainPassword)
            ->willReturn($hashedPassword);

        $user = $this->userManager->create($email, $plainPassword);

        $this->assertSame($email, $user->getEmail());
        $this->assertSame($hashedPassword, $user->getPassword());
    }

    public function testCreateWithEmailAndNoPassword(): void
    {
        $email = 'bar@example.com';
        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(User::class), $this->isString())
            ->willReturn('hashed_random');

        $user = $this->userManager->create($email);

        $this->assertSame($email, $user->getEmail());
        $this->assertSame('hashed_random', $user->getPassword());
    }

    public function testUpdatePassword(): void
    {
        $user = new User();
        $plainPassword = 'newpass';
        $hashedPassword = 'hashed_newpass';

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->with($user, $plainPassword)
            ->willReturn($hashedPassword);

        $this->userManager->updatePassword($user, $plainPassword);

        $this->assertSame($hashedPassword, $user->getPassword());
    }

    public function testEnable(): void
    {
        $user = new User();
        $user->setEnabled(false);

        $this->userManager->enable($user);

        $this->assertTrue($user->isEnabled());
    }

    public function testDisable(): void
    {
        $user = new User();
        $user->setEnabled(true);

        $this->userManager->disable($user);

        $this->assertFalse($user->isEnabled());
    }

    public function testUpdateLastLogin(): void
    {
        $user = new User();
        $this->assertNull($user->getLastLogin());

        $this->userManager->updateLastLogin($user);

        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getLastLogin());
        $this->assertLessThanOrEqual(time(), $user->getLastLogin()->getTimestamp());
    }

    public function testFindByEmailReturnsUser(): void
    {
        $email = 'foo@example.com';
        $user = new User();
        $user->setEmail($email);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $result = $this->userManager->findByEmail($email);

        $this->assertSame($user, $result);
    }

    public function testFindByEmailReturnsNull(): void
    {
        $email = 'notfound@example.com';
        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $result = $this->userManager->findByEmail($email);

        $this->assertNull($result);
    }

    public function testSaveCallsRepository(): void
    {
        $user = new User();
        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user, true);

        $this->userManager->save($user);
    }

    public function testDeleteCallsRepository(): void
    {
        $user = new User();
        $this->userRepository
            ->expects($this->once())
            ->method('remove')
            ->with($user, true);

        $this->userManager->delete($user);
    }
}
