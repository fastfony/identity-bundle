<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Security;

use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\Security\ResetPasswordLink;
use Fastfony\IdentityBundle\Entity\RequestPassword;
use Fastfony\IdentityBundle\Repository\RequestPasswordRepository;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[CoversClass(ResetPasswordLink::class)]
#[CoversClass(RequestPassword::class)]
#[CoversClass(User::class)]
final class ResetPasswordLinkTest extends TestCase
{
    private MailerInterface $mailer;
    private RequestPasswordRepository $requestPasswordRepository;
    private TranslatorInterface $translator;
    private ResetPasswordLink $resetPasswordLink;
    private string $emailSubject = 'subject';
    private string $emailContent = 'content';
    private string $emailActionText = 'action';
    private int $lifetime = 3600;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->requestPasswordRepository = $this->createMock(RequestPasswordRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->resetPasswordLink = new ResetPasswordLink(
            $this->mailer,
            $this->requestPasswordRepository,
            $this->translator,
            $this->emailSubject,
            $this->emailContent,
            $this->emailActionText,
            $this->lifetime
        );
    }

    public function testGetLifetime(): void
    {
        $this->assertSame(60, $this->resetPasswordLink->getLifetime());
    }

    public function testSendSuccess(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('user@example.com');
        $this->requestPasswordRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(RequestPassword::class), true);
        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(TemplatedEmail::class));
        $this->translator->method('trans')->willReturnArgument(0);

        $result = $this->resetPasswordLink->send($user);

        $this->assertTrue($result);
    }

    public function testSendThrowsLogicExceptionIfNoGetEmail(): void
    {
        $user = new class implements UserInterface {
            public function getRoles(): array { return []; }
            public function getPassword(): ?string { return null; }
            public function getUsername(): string { return 'user'; }
            public function eraseCredentials(): void {}
            public function getUserIdentifier(): string { return 'user';}
        };
        $this->expectException(LogicException::class);
        $this->resetPasswordLink->send($user);
    }

    public function testSendReturnsFalseOnMailerException(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('user@example.com');
        $this->requestPasswordRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(RequestPassword::class), true);
        $this->mailer->expects($this->once())
            ->method('send')
            ->willThrowException(new \Exception('Mailer failed'));
        $this->translator->method('trans')->willReturnArgument(0);

        $result = $this->resetPasswordLink->send($user);

        $this->assertFalse($result);
    }
}
