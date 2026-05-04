<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Guards the form_login submission against missing or empty "_username" /
 * "_password" keys (HTML5 "required" bypassed by devtools or scripted clients).
 *
 * FormLoginAuthenticator::getCredentials() throws two distinct exceptions:
 *   - non-string credential (key absent)        → BadRequestHttpException (raw 400)
 *   - empty-after-trim credential (key present) → BadCredentialsException with the
 *     technical message "The key ``_username`` must be a non-empty string." which
 *     surfaces verbatim in templates that render ``error.message`` rather than
 *     ``error.messageKey``.
 *
 * The guard runs before FirewallListener (priority 8 on kernel.request) and
 * normalises both cases into a single user-facing authentication error.
 */
class EmptyCredentialsGuard implements EventSubscriberInterface
{
    private const TRANSLATION_DOMAIN = 'FastfonyIdentityBundle';
    private const ERROR_TRANSLATION_KEY = 'error.login.empty_credentials';

    public function __construct(
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Priority 9 — strictly above FirewallListener (priority 8) so the
        // guard runs before form_login authentication kicks in.
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 9]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if ('POST' !== $request->getMethod() || 'form_login' !== $request->attributes->get('_route')) {
            return;
        }

        $payload = $request->request->all();
        $username = $payload['_username'] ?? null;
        $password = $payload['_password'] ?? null;

        if ($this->isFilled($username) && $this->isFilled($password)) {
            return;
        }

        $request->getSession()->set(
            SecurityRequestAttributes::AUTHENTICATION_ERROR,
            new CustomUserMessageAuthenticationException(
                $this->translator->trans(self::ERROR_TRANSLATION_KEY, [], self::TRANSLATION_DOMAIN),
            ),
        );

        if ($this->isFilled($username)) {
            $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, trim($username));
        }

        $event->setResponse(new RedirectResponse($this->router->generate('form_login')));
    }

    /**
     * Mirrors FormLoginAuthenticator's own validation: the value must be a
     * string AND non-empty after trim.
     */
    private function isFilled(mixed $value): bool
    {
        return \is_string($value) && '' !== trim($value);
    }
}
