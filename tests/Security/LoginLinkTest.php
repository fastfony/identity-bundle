<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Security;

use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\Notifier\CustomLoginLinkNotification;
use Fastfony\IdentityBundle\Security\LoginLink;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(LoginLink::class)]
final class LoginLinkTest extends TestCase
{
    private readonly NotifierInterface&MockObject $notifier;
    private readonly LoginLinkHandlerInterface&MockObject $loginLinkHandler;
    private readonly CacheItemPoolInterface&MockObject $cache;
    private readonly TranslatorInterface&MockObject $translator;
    private string $emailSubject;
    private int $maxAsk;
    private int $lifetime;
    private LoginLink $loginLink;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notifier = $this->createMock(NotifierInterface::class);
        $this->loginLinkHandler = $this->createMock(LoginLinkHandlerInterface::class);
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->emailSubject = 'subject';
        $this->maxAsk = 2;
        $this->lifetime = 600;

        $this->loginLink = new LoginLink(
            notifier: $this->notifier,
            loginLinkHandler: $this->loginLinkHandler,
            cache: $this->cache,
            translator: $this->translator,
            emailSubject: $this->emailSubject,
            maxAsk: $this->maxAsk,
            lifetime: $this->lifetime,
        );
    }

    public function testSendSuccess(): void
    {
        $user = $this->createUserMock('user@example.com');
        $this->loginLinkHandler->expects(self::once())
            ->method('createLoginLink')
            ->with($user)
            ->willReturn(
                $this->createMock(LoginLinkDetails::class)
            );
        $this->translator->expects(self::once())
            ->method('trans')
            ->with($this->emailSubject)
            ->willReturn('translated');
        $this->notifier->expects(self::once())
            ->method('send')
            ->with(
                self::callback(fn($n) => $n instanceof CustomLoginLinkNotification),
                self::callback(fn($r) => $r instanceof Recipient && $r->getEmail() === 'user@example.com')
            );

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn([]);
        $cacheItem->expects(self::once())->method('set');
        $cacheItem->expects(self::once())->method('expiresAfter')->with(60);
        $this->cache->method('getItem')->willReturn($cacheItem);
        $this->cache->expects(self::once())->method('save')->with($cacheItem);

        $result = $this->loginLink->send($user);

        self::assertTrue($result);
    }

    public function testSendTooManyRequests(): void
    {
        $user = $this->createUserMock('user@example.com');
        $this->mockCacheForAskCount($this->maxAsk);

        $result = $this->loginLink->send($user);

        self::assertFalse($result);
    }

    public function testSendThrowsIfNoGetEmail(): void
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('user3');
        // No method getEmail
        $this->mockCacheForAskCount(0);

        $this->expectException(LogicException::class);
        $this->loginLink->send($user);
    }

    public function testSendTransportException(): void
    {
        $user = $this->createUserMock('user@example.com', 'user4');
        $this->loginLinkHandler
            ->method('createLoginLink')
            ->willReturn(
                $this->createMock(LoginLinkDetails::class)
            );
        $this->translator->method('trans')->willReturn('translated');
        $this->notifier->method('send')->willThrowException($this->createMock(TransportExceptionInterface::class));
        $this->mockCacheForAskCount(0);

        $result = $this->loginLink->send($user);

        self::assertFalse($result);
    }

    public function testSendRfcComplianceException(): void
    {
        $user = $this->createUserMock('user@example.com');
        $this->loginLinkHandler
            ->method('createLoginLink')
            ->willReturn($this->createMock(LoginLinkDetails::class));
        $this->translator->method('trans')->willReturn('translated');
        $this->notifier
            ->method('send')
            ->willThrowException($this->createMock(RfcComplianceException::class));
        $this->mockCacheForAskCount(0);

        $result = $this->loginLink->send($user);

        self::assertFalse($result);
    }

    public function testGetLifetime(): void
    {
        $result = $this->loginLink->getLifetime();

        self::assertSame($this->lifetime / 60, $result);
    }

    private function createUserMock(
        string $email,
    ): UserInterface&MockObject {
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn($email);
        $user->method('getUserIdentifier')->willReturn($email);

        return $user;
    }

    private function mockCacheForAskCount(
        int $count
    ): void {
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn(array_fill(0, $count, time()));
        $this->cache->method('getItem')->with($this->anything())->willReturn($cacheItem);
    }
}
