<?php

namespace Fastfony\IdentityBundle\EventSubscriber;

use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\Manager\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
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
            $this->userManager->save($user);
        }
    }
}
