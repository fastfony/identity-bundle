<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public function __construct(
        #[Autowire('%fastfony_identity.registration.enabled%')]
        private readonly bool $registrationEnabled,
    ) {
    }

    #[Route('/login', name: 'form_login')]
    public function __invoke(
        AuthenticationUtils $authenticationUtils,
    ): Response {
        return $this->render(
            '@FastfonyIdentity/login.html.twig',
            [
                'last_username' => $authenticationUtils->getLastUsername(),
                'error' => $authenticationUtils->getLastAuthenticationError(),
                'registration_enabled' => $this->registrationEnabled,
            ],
        );
    }
}
