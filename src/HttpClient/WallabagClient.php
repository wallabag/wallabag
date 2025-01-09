<?php

namespace Wallabag\HttpClient;

use Psr\Log\LoggerInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class WallabagClient implements HttpClientInterface
{
    private $restrictedAccess;
    private HttpClientInterface $httpClient;
    private HttpBrowser $browser;
    private Authenticator $authenticator;
    private LoggerInterface $logger;

    public function __construct($restrictedAccess, HttpBrowser $browser, Authenticator $authenticator, LoggerInterface $logger)
    {
        $this->restrictedAccess = $restrictedAccess;
        $this->browser = $browser;
        $this->authenticator = $authenticator;
        $this->logger = $logger;

        $this->httpClient = HttpClient::create([
            'timeout' => 10,
        ]);
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $this->logger->log('debug', 'Restricted access config enabled?', ['enabled' => (int) $this->restrictedAccess]);

        if (0 === (int) $this->restrictedAccess) {
            return $this->httpClient->request($method, $url, $options);
        }

        $login = $this->authenticator->loginIfRequired($url);

        if (!$login) {
            return $this->httpClient->request($method, $url, $options);
        }

        if (null !== $cookieHeader = $this->getCookieHeader($url)) {
            $options['headers']['cookie'] = $cookieHeader;
        }

        $response = $this->httpClient->request($method, $url, $options);

        $login = $this->authenticator->loginIfRequested($response);

        if (!$login) {
            return $response;
        }

        if (null !== $cookieHeader = $this->getCookieHeader($url)) {
            $options['headers']['cookie'] = $cookieHeader;
        }

        return $this->httpClient->request($method, $url, $options);
    }

    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->httpClient->stream($responses, $timeout);
    }

    private function getCookieHeader(string $url): ?string
    {
        $cookies = [];

        foreach ($this->browser->getCookieJar()->allRawValues($url) as $name => $value) {
            $cookies[] = $name . '=' . $value;
        }

        if ([] === $cookies) {
            return null;
        }

        return implode('; ', $cookies);
    }
}
