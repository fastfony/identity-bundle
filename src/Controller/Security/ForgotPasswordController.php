<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Controller\Security;

use Fastfony\IdentityBundle\Form\RequestPasswordFormType;
use Fastfony\IdentityBundle\Repository\UserRepository;
use Fastfony\IdentityBundle\Security\ResetPasswordLink;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ForgotPasswordController extends AbstractController
{
    public function __construct(
        private readonly ResetPasswordLink $resetPasswordLink,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/forgot-password', name: 'forgot_password')]
    public function __invoke(Request $request): Response
    {
        $requestPasswordForm = $this->createForm(RequestPasswordFormType::class);

        $requestPasswordForm->handleRequest($request);
        if ($requestPasswordForm->isSubmitted() && $requestPasswordForm->isValid()) {
            $email = $requestPasswordForm->get('email')->getData();
            $user = $this->userRepository->findOneBy([
                'email' => $email,
                'enabled' => true,
            ]);

            if (null !== $user) {
                if (!$this->resetPasswordLink->send($user)) {
                    $this->addFlash(
                        'error',
                        'flash.error.reset_password_not_sent',
                    );
                }
            }

            // We always redirect to the confirm message to avoid user enumeration
            return $this->render(
                '@FastfonyIdentity/forgot_password.html.twig',
                [
                    'lifetime' => $this->resetPasswordLink->getLifetime(),
                ],
                new Response(null, Response::HTTP_SEE_OTHER), // For Turbo drive compatibility
            );
        }

        return $this->render(
            '@FastfonyIdentity/forgot_password.html.twig',
            [
                'form' => $requestPasswordForm->createView(),
            ]
        );
    }
}
