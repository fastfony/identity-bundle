<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Controller\Security;

use Fastfony\IdentityBundle\Form\RegisterFormType;
use Fastfony\IdentityBundle\Manager\UserManager;
use Fastfony\IdentityBundle\Security\LoginLink;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly LoginLink $loginLink,
        private readonly UserManager $userManager,
        #[Autowire('%fastfony_identity.registration.enabled%')]
        private readonly bool $enabled,
    ) {
    }

    #[Route('/register', name: 'register')]
    public function __invoke(
        Request $request,
    ): Response {
        if (!$this->enabled) {
            throw $this->createNotFoundException();
        }

        $registerForm = $this->createForm(RegisterFormType::class);

        if ($request->isMethod('POST')) {
            $registerForm->handleRequest($request);

            if ($registerForm->isSubmitted() && $registerForm->isValid()) {
                $email = $registerForm->get('email')->getData();
                $user = $this->userManager->findByEmail($email);

                if (null === $user) {
                    $user = $this->userManager->create($email);
                    $this->userManager->save($user);
                }

                if (!$this->loginLink->send($user)) {
                    $this->addFlash(
                        'error',
                        'flash.error.login_link_not_sent',
                    );
                }

                // We always redirect to the confirm message to avoid user enumeration
                return $this->render(
                    '@FastfonyIdentity/register.html.twig',
                    [
                        'lifetime' => $this->loginLink->getLifetime(),
                    ],
                    new Response(null, Response::HTTP_SEE_OTHER), // For Turbo drive compatibility
                );
            }
        }

        return $this->render(
            '@FastfonyIdentity/register.html.twig',
            [
                'form' => $registerForm->createView(),
                'lifetime' => $this->loginLink->getLifetime(),
            ]
        );
    }
}
