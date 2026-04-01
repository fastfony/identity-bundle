<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SecureAreaController extends AbstractController
{
    #[Route(
        '/secure-area/',
        name: 'fastfony_identity_secure_area',
    )]
    public function __invoke(): Response
    {
        return $this->render('@FastfonyIdentity/secure_area/show.html.twig');
    }
}

