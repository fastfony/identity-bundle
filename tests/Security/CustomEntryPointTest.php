<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Security;

use Fastfony\IdentityBundle\Security\CustomEntryPoint;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

#[CoversClass(CustomEntryPoint::class)]
final class CustomEntryPointTest extends TestCase
{
    public function testStartRedirectsToLoginLinkWhenEnabled(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('request_login_link')
            ->willReturn('/login-link');

        $entryPoint = new CustomEntryPoint(
            router: $router,
            loginLinkEnabled: true,
            defaultLoginMethod: 'login_link',
        );

        $request = $this->createMock(Request::class);

        $response = $entryPoint->start($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login-link', $response->getTargetUrl());
    }

    public function testStartRedirectsToFormLoginWhenLoginLinkDisabled(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('form_login')
            ->willReturn('/login');

        $entryPoint = new CustomEntryPoint(
            router: $router,
            loginLinkEnabled: false,
            defaultLoginMethod: 'login_link',
        );

        $request = $this->createMock(Request::class);

        $response = $entryPoint->start($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());
    }

    public function testStartRedirectsToFormLoginWhenDefaultIsFormLogin(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('form_login')
            ->willReturn('/login');

        $entryPoint = new CustomEntryPoint(
            router: $router,
            loginLinkEnabled: true,
            defaultLoginMethod: 'form_login',
        );

        $request = $this->createMock(Request::class);

        $response = $entryPoint->start($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());
    }
}
