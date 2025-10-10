<?php

namespace Fastfony\IdentityBundle\EventListener;

use Fastfony\IdentityBundle\Entity\User;
use Fastfony\IdentityBundle\Service\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginListener implements EventSubscriberInterface
{
    public function __construct(
        private UserManager $userManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        
        if ($user instanceof User) {
            $this->userManager->updateLastLogin($user);
            $this->userManager->saveUser($user);
        }
    }
}
