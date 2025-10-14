<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class CustomEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        #[Autowire('%fastfony_identity.login_link.enabled%')]
        private readonly bool            $loginLinkEnabled,
        #[Autowire('%fastfony_identity.login.default_method')]
        private readonly string          $defaultLoginMethod,
    ) {
    }

    public function start(
        Request $request,
        ?\Throwable $authException = null,
    ): Response {
        $route = match ($this->defaultLoginMethod) {
            // Only if login link is enabled, otherwise fallback to form login
            'login_link' => $this->router->generate(
                $this->loginLinkEnabled ? 'request_login_link' : 'form_login',
            ),
            default => $this->router->generate('form_login'),
        };

        return new RedirectResponse($route);
    }
}
