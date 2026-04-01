<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Controller\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

trait AlreadyLoggedInTrait
{
    public function redirectIfAlreadyLoggedIn(): ?Response
    {
        if (null !== $this->getUser()) {
            $this->addFlash('info', 'flash.info.already_logged_in');

            return new RedirectResponse('/');
        }

        return null;
    }
}