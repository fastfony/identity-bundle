<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Security;

use Fastfony\IdentityBundle\Notifier\CustomLoginLinkNotification;
use LogicException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginLink
{
    public function __construct(
        private readonly NotifierInterface $notifier,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly CacheItemPoolInterface $cache,
        private readonly TranslatorInterface $translator,
        #[Autowire('%fastfony_identity.login_link.email_subject%')]
        private readonly string $emailSubject,
        #[Autowire('%fastfony_identity.login_link.limit_max_ask_by_minute%')]
        private readonly int $maxAsk,
        #[Autowire(param: 'fastfony_identity.login_link.lifetime')]
        private readonly int $lifetime,
    ) {
    }

    public function getLifetime(): int
    {
        return $this->lifetime/60;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function send(UserInterface $user): bool
    {
        // We limit the number of login link requests in one minute
        if ($this->hasAlreadyTooMuchAsk($user)) {
            return false;
        }

        $notification = new CustomLoginLinkNotification(
            $this->loginLinkHandler->createLoginLink($user),
            $this->translator->trans($this->emailSubject),
        );

        if (!method_exists($user, 'getEmail')) {
            throw new LogicException(
                'User must have a getEmail() method returning the email address for use login link feature.',
            );
        }

        try {
            $recipient = new Recipient($user->getEmail());
            $this->notifier->send($notification, $recipient);

            $this->saveAskTimeInCache($user);

            return true;
        } catch (RfcComplianceException|TransportExceptionInterface) {
            return false;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function hasAlreadyTooMuchAsk(UserInterface $user): bool
    {
        return count($this->getUserRecentlyAskLoginTime($user)) >= $this->maxAsk;
    }

    /**
     * @return array<int>
     *
     * @throws InvalidArgumentException
     */
    private function getUserRecentlyAskLoginTime(UserInterface $user): array
    {
        if (method_exists($this->cache, 'prune')) {
            $this->cache->prune();
        }
        $itemCache = $this->cache->getItem($this->getCacheItemUserKey($user));
        if (!$itemCache->isHit()) {
            return [];
        }

        return $itemCache->get();
    }

    private function getCacheItemUserKey(UserInterface $user): string
    {
        // Remove unauthorized characters for the Symfony cache pool
        $sanitizedIdentifier = \preg_replace('/[{}()\\/\\@:]/', '', $user->getUserIdentifier());

        return 'user_' . $sanitizedIdentifier . '_has_recently_ask_login_link';
    }

    /**
     * @throws InvalidArgumentException
     */
    private function saveAskTimeInCache(UserInterface $user): void
    {
        $itemCache = $this->cache->getItem($this->getCacheItemUserKey($user));
        $asks = $itemCache->get();
        $asks[] = time();
//        var_dump('pass');die;
        $itemCache->set($asks);
        $itemCache->expiresAfter(60); // New send login link is authorized after one minute
        $this->cache->save($itemCache);
    }
}
