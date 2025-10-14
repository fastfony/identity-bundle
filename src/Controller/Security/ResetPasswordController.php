<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Controller\Security;

use Fastfony\IdentityBundle\Entity\RequestPassword;
use Fastfony\IdentityBundle\Manager\UserManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly Security $security,
        #[Autowire('%fastfony_identity.request_password.redirect_route%')]
        private readonly string $redirectRoute,
    ) {
    }

    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function __invoke(
        #[MapEntity(mapping: ['token' => 'token'])]
        RequestPassword $requestPassword,
        Request $request,
    ): Response {
        if ($requestPassword->getExpireAt() < new \DateTime()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createFormBuilder()
            ->add(
                'password',
                PasswordType::class,
                [
                    'constraints' => [
                        new PasswordStrength(),
                    ],
                ]
            )
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updatePassword(
                $requestPassword->getUser(),
                $form->getData()['password']
            );

            // We connect the user and redirect to the homepage
            $this->security->login(
                $requestPassword->getUser(),
                'security.authenticator.remember_me.fastfony_identity',
                'fastfony_identity',
                [(new RememberMeBadge())->enable()],
            );

            return $this->redirectToRoute($this->redirectRoute);
        }

        return $this->render(
            '@FastfonyIdentity/reset_password.html.twig',
            [
                'form' => $form,
            ]
        );
    }
}
