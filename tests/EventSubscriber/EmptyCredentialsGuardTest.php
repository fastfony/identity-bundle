<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\EventSubscriber;

use Fastfony\IdentityBundle\EventSubscriber\EmptyCredentialsGuard;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(EmptyCredentialsGuard::class)]
final class EmptyCredentialsGuardTest extends TestCase
{
    private RouterInterface&MockObject $router;
    private TranslatorInterface&MockObject $translator;
    private EmptyCredentialsGuard $guard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = $this->createMock(RouterInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->guard = new EmptyCredentialsGuard($this->router, $this->translator);
    }

    public function testGetSubscribedEventsTargetsKernelRequestBeforeFirewall(): void
    {
        $events = EmptyCredentialsGuard::getSubscribedEvents();

        self::assertArrayHasKey(KernelEvents::REQUEST, $events);
        self::assertSame([['onKernelRequest', 9]], $events[KernelEvents::REQUEST]);
    }

    public function testIgnoresSubRequests(): void
    {
        $event = $this->buildEvent(
            request: Request::create('/login', 'POST'),
            requestType: HttpKernelInterface::SUB_REQUEST,
        );

        $this->guard->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testIgnoresGetRequests(): void
    {
        $request = Request::create('/login', 'GET');
        $request->attributes->set('_route', 'form_login');

        $event = $this->buildEvent($request);

        $this->guard->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testIgnoresPostsToOtherRoutes(): void
    {
        $request = Request::create('/something-else', 'POST');
        $request->attributes->set('_route', 'some_other_route');

        $event = $this->buildEvent($request);

        $this->guard->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testIgnoresWellFormedFormSubmission(): void
    {
        $request = Request::create('/login', 'POST', [
            '_username' => 'jane@example.com',
            '_password' => 'secret',
        ]);
        $request->attributes->set('_route', 'form_login');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $event = $this->buildEvent($request);

        $this->guard->onKernelRequest($event);

        self::assertNull($event->getResponse());
        self::assertFalse(
            $request->getSession()->has(SecurityRequestAttributes::AUTHENTICATION_ERROR),
        );
    }

    public function testStoresAuthenticationErrorAndRedirectsWhenUsernameMissing(): void
    {
        $request = Request::create('/login', 'POST', [
            '_password' => 'secret',
            '_csrf_token' => 'token',
        ]);
        $request->attributes->set('_route', 'form_login');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $event = $this->buildEvent($request);

        $this->translator->expects(self::once())
            ->method('trans')
            ->with('error.login.empty_credentials', [], 'FastfonyIdentityBundle')
            ->willReturn('Please fill in your email address and password.');

        $this->router->expects(self::once())
            ->method('generate')
            ->with('form_login')
            ->willReturn('/login');

        $this->guard->onKernelRequest($event);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/login', $response->getTargetUrl());

        $error = $request->getSession()->get(SecurityRequestAttributes::AUTHENTICATION_ERROR);
        self::assertInstanceOf(CustomUserMessageAuthenticationException::class, $error);
        self::assertSame('Please fill in your email address and password.', $error->getMessage());

        // No username submitted → LAST_USERNAME must not be populated.
        self::assertFalse($request->getSession()->has(SecurityRequestAttributes::LAST_USERNAME));
    }

    public function testPreservesSubmittedUsernameWhenOnlyPasswordMissing(): void
    {
        $request = Request::create('/login', 'POST', [
            '_username' => 'jane@example.com',
        ]);
        $request->attributes->set('_route', 'form_login');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $event = $this->buildEvent($request);

        $this->translator->method('trans')->willReturn('translated');
        $this->router->method('generate')->willReturn('/login');

        $this->guard->onKernelRequest($event);

        self::assertInstanceOf(RedirectResponse::class, $event->getResponse());
        self::assertSame(
            'jane@example.com',
            $request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME),
        );
    }

    public function testCatchesEmptyStringCredentials(): void
    {
        // Browser submission of a form with the HTML5 "required" attribute
        // removed: the field IS posted, but as an empty string.
        $request = Request::create('/login', 'POST', [
            '_username' => '',
            '_password' => '',
        ]);
        $request->attributes->set('_route', 'form_login');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $event = $this->buildEvent($request);

        $this->translator->method('trans')->willReturn('translated');
        $this->router->method('generate')->willReturn('/login');

        $this->guard->onKernelRequest($event);

        self::assertInstanceOf(RedirectResponse::class, $event->getResponse());
        self::assertInstanceOf(
            CustomUserMessageAuthenticationException::class,
            $request->getSession()->get(SecurityRequestAttributes::AUTHENTICATION_ERROR),
        );
        self::assertFalse($request->getSession()->has(SecurityRequestAttributes::LAST_USERNAME));
    }

    public function testCatchesWhitespaceOnlyCredentials(): void
    {
        $request = Request::create('/login', 'POST', [
            '_username' => '   ',
            '_password' => "\t",
        ]);
        $request->attributes->set('_route', 'form_login');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $event = $this->buildEvent($request);

        $this->translator->method('trans')->willReturn('translated');
        $this->router->method('generate')->willReturn('/login');

        $this->guard->onKernelRequest($event);

        self::assertInstanceOf(RedirectResponse::class, $event->getResponse());
        self::assertFalse($request->getSession()->has(SecurityRequestAttributes::LAST_USERNAME));
    }

    public function testTrimsPreservedUsername(): void
    {
        $request = Request::create('/login', 'POST', [
            '_username' => '  jane@example.com  ',
            '_password' => '',
        ]);
        $request->attributes->set('_route', 'form_login');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $event = $this->buildEvent($request);

        $this->translator->method('trans')->willReturn('translated');
        $this->router->method('generate')->willReturn('/login');

        $this->guard->onKernelRequest($event);

        self::assertSame(
            'jane@example.com',
            $request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME),
        );
    }

    public function testHandlesNonStringUsernamePayload(): void
    {
        // E.g. crafted client posts "_username[]=foo" → array, not string.
        $request = Request::create('/login', 'POST', [
            '_username' => ['foo'],
            '_password' => 'secret',
        ]);
        $request->attributes->set('_route', 'form_login');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $event = $this->buildEvent($request);

        $this->translator->method('trans')->willReturn('translated');
        $this->router->method('generate')->willReturn('/login');

        $this->guard->onKernelRequest($event);

        self::assertInstanceOf(RedirectResponse::class, $event->getResponse());
        self::assertInstanceOf(
            CustomUserMessageAuthenticationException::class,
            $request->getSession()->get(SecurityRequestAttributes::AUTHENTICATION_ERROR),
        );
        // Non-string username must not pollute LAST_USERNAME.
        self::assertFalse($request->getSession()->has(SecurityRequestAttributes::LAST_USERNAME));
    }

    private function buildEvent(
        Request $request,
        int $requestType = HttpKernelInterface::MAIN_REQUEST,
    ): RequestEvent {
        return new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $requestType,
        );
    }
}
