<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Security;

use Fastfony\IdentityBundle\Entity\RequestPassword;
use Fastfony\IdentityBundle\Repository\RequestPasswordRepository;
use LogicException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResetPasswordLink
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly RequestPasswordRepository $requestPasswordRepository,
        private readonly TranslatorInterface $translator,
        #[Autowire('%fastfony_identity.request_password.email_subject%')]
        private readonly string $emailSubject,
        #[Autowire('%fastfony_identity.request_password.email_content%')]
        private readonly string $emailContent,
        #[Autowire('%fastfony_identity.request_password.email_action_text%')]
        private readonly string $emailActionText,
        #[Autowire('%fastfony_identity.request_password.lifetime%')]
        private readonly int $lifetime,
    ) {
    }

    public function getLifetime(): int
    {
        return $this->lifetime/60;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(UserInterface $user): bool
    {
        if (!method_exists($user, 'getEmail')) {
            throw new LogicException(
                'User must have a getEmail() method returning the email address for use login link feature.',
            );
        }

        $requestPassword = (new RequestPassword($this->lifetime))
            ->setUser($user);

        $this->requestPasswordRepository->save($requestPassword, true);

        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail()))
            ->subject($this->translator->trans($this->emailSubject))
            ->htmlTemplate('@FastfonyIdentity/emails/reset_password.html.twig')
            ->context([
                'subject' => $this->translator->trans($this->emailSubject),
                'importance' => Notification::IMPORTANCE_HIGH,
                'content' => $this->translator->trans($this->emailContent),
                'action_text' => $this->translator->trans($this->emailActionText),
                'request_password' => $requestPassword,
                'footer_text' =>
                    sprintf(
                        'This email was sent automatically to %s, please do not reply.',
                        $user->getEmail(),
                    ),
                'exception' => null,
            ])
        ;

        try {
            $this->mailer->send($email);

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
