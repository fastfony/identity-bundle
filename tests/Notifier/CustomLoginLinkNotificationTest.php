<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Notifier;

use Fastfony\IdentityBundle\Notifier\CustomLoginLinkNotification;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;

#[CoversClass(CustomLoginLinkNotification::class)]
class CustomLoginLinkNotificationTest extends TestCase
{
    public function testAsEmailMessageOverridesTemplate(): void
    {
        $loginLinkDetails = $this->createMock(LoginLinkDetails::class);
        $recipient = $this->createMock(EmailRecipientInterface::class);
        $recipient->expects(self::once())
            ->method('getEmail')
            ->willReturn('email@test.com');

        $subject = 'Connexion à votre compte';

        $notification = new CustomLoginLinkNotification(
            $loginLinkDetails,
            $subject,
        );

        $emailMessage = $notification->asEmailMessage($recipient);

        $this->assertInstanceOf(EmailMessage::class, $emailMessage);

        $email = $emailMessage->getMessage();
        $this->assertInstanceOf(NotificationEmail::class, $email);
        $this->assertSame(
            '@FastfonyIdentity/emails/login_link_email.html.twig',
            $email->getHtmlTemplate()
        );
    }
}
