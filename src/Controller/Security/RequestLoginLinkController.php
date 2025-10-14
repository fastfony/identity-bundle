<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Controller\Security;

use Fastfony\IdentityBundle\Form\LoginFormType;
use Fastfony\IdentityBundle\Repository\UserRepository;
use Fastfony\IdentityBundle\Security\LoginLink;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RequestLoginLinkController extends AbstractController
{
    public function __construct(
        private readonly LoginLink $loginLink,
        private readonly UserRepository $userRepository,
        #[Autowire('%fastfony_identity.login_link.enabled%')]
        private readonly bool $loginLinkEnabled,
        #[Autowire('%fastfony_identity.registration.enabled%')]
        private readonly string $registrationEnabled,
    ) {
    }

    #[Route('/request-login-link', name: 'request_login_link')]
    public function __invoke(Request $request): Response
    {
        if (!$this->loginLinkEnabled) {
            return $this->redirectToRoute('form_login');
        }

        if ($this->getUser()) {
            $this->addFlash('info', 'flash.info.already_logged_in');
        }

        $loginForm = $this->createForm(LoginFormType::class);

        $loginForm->handleRequest($request);
        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $email = $loginForm->get('email')->getData();
            $user = $this->userRepository->findOneBy([
                'email' => $email,
                'enabled' => true,
            ]);

            if (null !== $user) {
                if (!$this->loginLink->send($user)) {
                    $this->addFlash(
                        'error',
                        'flash.error.login_link_not_sent',
                    );
                }
            }

            // We always redirect to the confirm message to avoid user enumeration
            return $this->render(
                '@FastfonyIdentity/request_login_link.html.twig',
                [
                    'registration_enabled' => $this->registrationEnabled,
                    'lifetime' => $this->loginLink->getLifetime(),
                ],
                new Response(null, Response::HTTP_SEE_OTHER), // For Turbo drive compatibility
            );
        }

        return $this->render(
            '@FastfonyIdentity/request_login_link.html.twig',
            [
                'form' => $loginForm->createView(),
                'registration_enabled' => $this->registrationEnabled,
                'lifetime' => $this->loginLink->getLifetime(),
            ],
        );
    }
}
