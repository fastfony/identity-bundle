<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\EventSubscriber;

use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\EventSubscriber\LoginSubscriber;
use Fastfony\IdentityBundle\Manager\UserManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[CoversClass(LoginSubscriber::class)]
final class LoginSubscriberTest extends TestCase
{
    private readonly UserManager&MockObject $userManager;
    private readonly LoginSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userManager = $this->createMock(UserManager::class);
        $this->subscriber = new LoginSubscriber(
            $this->userManager
        );
    }

    public function testOnLoginSuccessWithUser(): void
    {
        $user = $this->createMock(User::class);
        $event = $this->createMock(LoginSuccessEvent::class);
        $event->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->userManager->expects(self::once())
            ->method('updateLastLogin')
            ->with($user);
        $this->userManager->expects(self::once())
            ->method('save')
            ->with($user);

        $this->subscriber->onLoginSuccess($event);
    }

    public function testOnLoginSuccessWithNonUser(): void
    {
        $notAUser = $this->createMock(UserInterface::class);
        $event = $this->createMock(LoginSuccessEvent::class);
        $event->expects(self::once())
            ->method('getUser')
            ->willReturn($notAUser);

        $this->userManager->expects(self::never())
            ->method('updateLastLogin');
        $this->userManager->expects(self::never())
            ->method('save');

        $this->subscriber->onLoginSuccess($event);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = LoginSubscriber::getSubscribedEvents();
        self::assertArrayHasKey(LoginSuccessEvent::class, $events);
        self::assertSame('onLoginSuccess', $events[LoginSuccessEvent::class]);
    }
}
