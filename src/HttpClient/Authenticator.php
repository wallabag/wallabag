<?php

namespace Wallabag\HttpClient;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Wallabag\SiteConfig\LoginFormAuthenticator;
use Wallabag\SiteConfig\SiteConfig;
use Wallabag\SiteConfig\SiteConfigBuilder;

class Authenticator implements LoggerAwareInterface
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        private readonly SiteConfigBuilder $configBuilder,
        private readonly LoginFormAuthenticator $authenticator,
    ) {
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function loginIfRequired(string $url): bool
    {
        $config = $this->buildSiteConfig(new Uri($url));
        if (false === $config || !$config->requiresLogin()) {
            $this->logger->debug('loginIfRequired> will not require login');

            return false;
        }

        if ($this->authenticator->isLoggedIn($config)) {
            return false;
        }

        $this->logger->debug('loginIfRequired> user is not logged in, attach authenticator');

        $this->authenticator->login($config);

        return true;
    }

    public function loginIfRequested(ResponseInterface $response): bool
    {
        $config = $this->buildSiteConfig(new Uri($response->getInfo('url')));
        if (false === $config || !$config->requiresLogin()) {
            $this->logger->debug('loginIfRequested> will not require login');

            return false;
        }

        $body = $response->getContent();

        if ('' === $body) {
            $this->logger->debug('loginIfRequested> empty body, ignoring');

            return false;
        }

        $isLoginRequired = $this->authenticator->isLoginRequired($config, $body);

        $this->logger->debug('loginIfRequested> retry with login ' . ($isLoginRequired ? '' : 'not ') . 'required');

        if (!$isLoginRequired) {
            return false;
        }

        $this->authenticator->login($config);

        return true;
    }

    /**
     * @return SiteConfig|false
     */
    private function buildSiteConfig(UriInterface $uri)
    {
        return $this->configBuilder->buildForHost($uri->getHost());
    }
}
