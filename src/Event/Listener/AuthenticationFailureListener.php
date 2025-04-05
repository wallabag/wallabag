<?php

namespace Wallabag\Event\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class AuthenticationFailureListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onAuthenticationFailure',
        ];
    }

    /**
     * On failure, add a custom error in log so server admin can configure fail2ban to block IP from people who try to login too much.
     */
    public function onAuthenticationFailure()
    {
        $request = $this->requestStack->getMainRequest();

        $this->logger->error('Authentication failure for user "' . $request->request->get('_username') . '", from IP "' . $request->getClientIp() . '", with UA: "' . $request->server->get('HTTP_USER_AGENT') . '".');
    }
}
