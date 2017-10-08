<?php

namespace Wallabag\UserBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\AuthenticationEvents;

class AuthenticationFailureListener implements EventSubscriberInterface
{
    private $requestStack;
    private $logger;

    public function __construct(RequestStack $requestStack, LoggerInterface $logger)
    {
        $this->requestStack = $requestStack;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
        ];
    }

    /**
     * On failure, add a custom error in log so server admin can configure fail2ban to block IP from people who try to login too much.
     */
    public function onAuthenticationFailure()
    {
        $request = $this->requestStack->getMasterRequest();

        $this->logger->error('Authentication failure for user "' . $request->request->get('_username') . '", from IP "' . $request->getClientIp() . '", with UA: "' . $request->server->get('HTTP_USER_AGENT') . '".');
    }
}
