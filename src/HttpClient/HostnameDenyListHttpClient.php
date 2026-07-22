<?php

namespace Wallabag\HttpClient;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\HttpClientTrait;
use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Contracts\HttpClient\ChunkInterface;
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
        $url = implode('', $url);
        $initialHost = parse_url($url, \PHP_URL_HOST);
        $this->assertUrlIsAllowed($url);

        if (0 >= $maxRedirects = $options['max_redirects']) {
            return new AsyncResponse($this->client, $method, $url, $options);
        }

        $options['max_redirects'] = 0;

        return new AsyncResponse(
            $this->client,
            $method,
            $url,
            $options,
            $this->createRedirectCallback($method, $options, $maxRedirects, $initialHost),
        );
    }

    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        if ($this->denyList->isEmpty()) {
            return $this->client->stream($responses, $timeout);
        }

        if ($responses instanceof AsyncResponse) {
            $responses = [$responses];
        } elseif (!is_iterable($responses)) {
            throw new \TypeError(\sprintf('"%s()" expects parameter 1 to be an iterable of AsyncResponse objects, "%s" given.', __METHOD__, get_debug_type($responses)));
        }

        return new ResponseStream(AsyncResponse::stream($responses, $timeout, static::class));
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

    /**
     * Redirect handling is adapted from Symfony 5.4's NoPrivateNetworkHttpClient
     * because Symfony exposes no public hook that can reject a redirect before dispatch.
     */
    private function createRedirectCallback(string $method, array $options, int $maxRedirects, ?string $initialHost): \Closure
    {
        $redirectCount = 0;
        $headersWithCredentials = $options['headers'];
        $headersWithoutCredentials = self::removeHeaders(
            $headersWithCredentials,
            ['host', 'authorization', 'cookie'],
        );

        // State stays inside this response's closure so parallel responses cannot share it.
        return function (ChunkInterface $chunk, AsyncContext $context) use (&$method, &$options, $maxRedirects, $initialHost, &$redirectCount, &$headersWithCredentials, &$headersWithoutCredentials): \Generator {
            if (null !== $chunk->getError() || $chunk->isTimeout() || !$chunk->isFirst()) {
                yield $chunk;

                return;
            }

            $statusCode = $context->getStatusCode();
            $redirectUrl = $context->getInfo('redirect_url');

            if ($statusCode < 300 || 400 <= $statusCode || !\is_string($redirectUrl)) {
                $context->passthru();

                yield $chunk;

                return;
            }

            $this->assertUrlIsAllowed($redirectUrl);

            if (self::shouldDropBodyForRedirect($method, $statusCode)) {
                $method = 'HEAD' === $method ? 'HEAD' : 'GET';
                unset($options['body'], $options['json']);

                $contentHeaders = ['content-length', 'content-type', 'transfer-encoding'];
                $headersWithCredentials = self::removeHeaders($headersWithCredentials, $contentHeaders);
                $headersWithoutCredentials = self::removeHeaders($headersWithoutCredentials, $contentHeaders);
            }

            // Credentials and an explicit Host header must never cross host boundaries.
            $options['headers'] = self::selectHeadersForRedirect(
                $initialHost,
                $redirectUrl,
                $headersWithCredentials,
                $headersWithoutCredentials,
            );

            $context->setInfo('redirect_count', ++$redirectCount);
            $context->replaceRequest($method, $redirectUrl, $options);

            if ($redirectCount >= $maxRedirects) {
                $context->passthru();
            }
        };
    }

    private static function shouldDropBodyForRedirect(string $method, int $statusCode): bool
    {
        return 303 === $statusCode || ('POST' === $method && \in_array($statusCode, [301, 302], true));
    }

    /**
     * @param string[] $headers
     * @param string[] $names
     *
     * @return string[]
     */
    private static function removeHeaders(array $headers, array $names): array
    {
        return array_filter($headers, static function (string $header) use ($names): bool {
            foreach ($names as $name) {
                if (0 === stripos($header, $name . ':')) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * @param string[] $headersWithCredentials
     * @param string[] $headersWithoutCredentials
     *
     * @return string[]
     */
    private static function selectHeadersForRedirect(?string $initialHost, string $redirectUrl, array $headersWithCredentials, array $headersWithoutCredentials): array
    {
        return $initialHost === parse_url($redirectUrl, \PHP_URL_HOST)
            ? $headersWithCredentials
            : $headersWithoutCredentials;
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
