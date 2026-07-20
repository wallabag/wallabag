<?php

namespace Wallabag\HttpClient;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\HttpClientTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\Service\ResetInterface;

final class HostnameDenyListHttpClient implements HttpClientInterface, LoggerAwareInterface, ResetInterface
{
    use HttpClientTrait;

    private array $defaultOptions = self::OPTIONS_DEFAULTS;

    public function __construct(
        private HttpClientInterface $client,
        private readonly HostnameDenyList $denyList,
    ) {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if ($this->denyList->isEmpty()) {
            return $this->client->request($method, $url, $options);
        }

        [$url, $options] = self::prepareRequest($method, $url, $options, $this->defaultOptions, true);
        $this->assertUrlIsAllowed(implode('', $url));
        $options['max_redirects'] = 0;

        return $this->client->request($method, implode('', $url), $options);
    }

    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        return $this->client->stream($responses, $timeout);
    }

    public function withOptions(array $options): self
    {
        $clone = clone $this;
        $clone->client = $this->client->withOptions($options);
        $clone->defaultOptions = self::mergeDefaultOptions($options, $this->defaultOptions);

        return $clone;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        if ($this->client instanceof LoggerAwareInterface) {
            $this->client->setLogger($logger);
        }
    }

    public function reset(): void
    {
        if ($this->client instanceof ResetInterface) {
            $this->client->reset();
        }
    }

    private function assertUrlIsAllowed(string $url): void
    {
        $hostname = parse_url($url, \PHP_URL_HOST);

        if (!\is_string($hostname) || null === $hostname = $this->denyList->getBlockedHostname($hostname)) {
            return;
        }

        throw new TransportException(\sprintf('Host "%s" is blocked by WALLABAG_FETCH_BLOCKED_HOSTS.', $hostname));
    }
}
