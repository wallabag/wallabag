<?php

namespace Wallabag\Guzzle;

use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Message\RequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Wallabag\SiteConfig\Authenticator\Factory;
use Wallabag\SiteConfig\SiteConfig;
use Wallabag\SiteConfig\SiteConfigBuilder;

class AuthenticatorSubscriber implements SubscriberInterface, LoggerAwareInterface
{
    // avoid loop when login failed which can just be a bad login/password
    // after 2 attempts, we skip the login
    public const MAX_RETRIES = 2;
    private int $retries = 0;

    /** @var SiteConfigBuilder */
    private $configBuilder;

    /** @var Factory */
    private $authenticatorFactory;

    /** @var LoggerInterface */
    private $logger;

    /**
     * AuthenticatorSubscriber constructor.
     */
    public function __construct(SiteConfigBuilder $configBuilder, Factory $authenticatorFactory)
    {
        $this->configBuilder = $configBuilder;
        $this->authenticatorFactory = $authenticatorFactory;
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function getEvents(): array
    {
        return [
            'before' => ['loginIfRequired'],
            'complete' => ['loginIfRequested'],
        ];
    }

    public function loginIfRequired(BeforeEvent $event)
    {
        $config = $this->buildSiteConfig($event->getRequest());
        if (false === $config || !$config->requiresLogin()) {
            $this->logger->debug('loginIfRequired> will not require login');

            return;
        }

        $client = $event->getClient();
        $authenticator = $this->authenticatorFactory->buildFromSiteConfig($config);

        if (!$authenticator->isLoggedIn($client)) {
            $this->logger->debug('loginIfRequired> user is not logged in, attach authenticator');

            $emitter = $client->getEmitter();
            $emitter->detach($this);
            $authenticator->login($client);
            $emitter->attach($this);
        }
    }

    public function loginIfRequested(CompleteEvent $event)
    {
        $config = $this->buildSiteConfig($event->getRequest());
        if (false === $config || !$config->requiresLogin()) {
            $this->logger->debug('loginIfRequested> will not require login');

            return;
        }

        $body = $event->getResponse()->getBody();

        if (
            null === $body
            || '' === $body->getContents()
        ) {
            $this->logger->debug('loginIfRequested> empty body, ignoring');

            return;
        }

        $authenticator = $this->authenticatorFactory->buildFromSiteConfig($config);
        $isLoginRequired = $authenticator->isLoginRequired($body);

        $this->logger->debug('loginIfRequested> retry #' . $this->retries . ' with login ' . ($isLoginRequired ? '' : 'not ') . 'required');

        if ($isLoginRequired && $this->retries < self::MAX_RETRIES) {
            $client = $event->getClient();

            $emitter = $client->getEmitter();
            $emitter->detach($this);
            $authenticator->login($client);
            $emitter->attach($this);

            $event->retry();

            ++$this->retries;
        }
    }

    /**
     * @return SiteConfig|false
     */
    private function buildSiteConfig(RequestInterface $request)
    {
        return $this->configBuilder->buildForHost($request->getHost());
    }
}
