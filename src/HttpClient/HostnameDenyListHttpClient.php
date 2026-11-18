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
        $this->assertUrlIsAllowed($url);

        // The prepared URL already includes the query options.
        unset($options['query'], $options['normalized_headers']);

        if (0 >= $maxRedirects = $options['max_redirects']) {
            return new AsyncResponse($this->client, $method, $url, $options);
        }

        $options['max_redirects'] = 0;
        $body = $options['body'] ?? '';
        $rewindBody = null;
        if (\is_resource($body) && stream_get_meta_data($body)['seekable'] && false !== $offset = ftell($body)) {
            $rewindBody = static fn (): bool => \is_resource($body) && 0 === @fseek($body, $offset);
            // Read through the PHP stream so each transport request sees the rewound position.
            $options['body'] = static function (int $size) use ($body): string {
                if (!\is_resource($body) || false === $chunk = @fread($body, $size)) {
                    throw new TransportException('Cannot read the HTTP request body stream.');
                }

                return $chunk;
            };
        }

        return new AsyncResponse(
            $this->client,
            $method,
            $url,
            $options,
            $this->createRedirectCallback($method, $options, $maxRedirects, $url, $rewindBody),
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
        if ($this->denyList->isEmpty()) {
            $clone->client = $this->client->withOptions($options);
        } else {
            // Keep defaults here so the transport cannot reapply them on a redirect.
            $clone->defaultOptions = self::mergeDefaultOptions($options, $this->defaultOptions);
        }

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
    private function createRedirectCallback(string $method, array $options, int $maxRedirects, string $initialUrl, ?\Closure $rewindBody): \Closure
    {
        $redirectCount = 0;
        $initialOrigin = self::getOrigin($initialUrl);
        $initialHeaders = $options['headers'];
        $authNtlm = $options['auth_ntlm'] ?? null;
        $contentHeadersToRemove = [];

        // State stays inside this response's closure so parallel responses cannot share it.
        return function (ChunkInterface $chunk, AsyncContext $context) use (&$method, &$options, $maxRedirects, $initialOrigin, &$redirectCount, $initialHeaders, $authNtlm, &$contentHeadersToRemove, $rewindBody): \Generator {
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
            $sameOrigin = $initialOrigin === self::getOrigin($redirectUrl);

            if (self::shouldDropBodyForRedirect($method, $statusCode)) {
                $method = 'HEAD' === $method ? 'HEAD' : 'GET';
                $options['body'] = '';
                $contentHeadersToRemove = ['content-length', 'content-type', 'transfer-encoding'];
            } elseif (!\is_string($options['body'] ?? '')) {
                // Stop any remaining reads on the previous request before reusing its stream.
                $context->getResponse()->cancel();
                if (null === $rewindBody || !$rewindBody()) {
                    throw new TransportException('Cannot replay the request body for an HTTP redirect.');
                }
            }

            $headersToRemove = $contentHeadersToRemove;
            if (!$sameOrigin) {
                $headersToRemove = array_merge($headersToRemove, ['host', 'authorization', 'cookie']);
            }
            $options['headers'] = self::removeHeaders($initialHeaders, $headersToRemove) + array_fill_keys($headersToRemove, []);
            $options['json'] = $options['auth_basic'] = $options['auth_bearer'] = null;
            if (null !== $authNtlm) {
                $options['auth_ntlm'] = $sameOrigin ? $authNtlm : null;
            }

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

    /** @return array{string, string, int} */
    private static function getOrigin(string $url): array
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        if (!isset($parts['host']) || !\in_array($scheme, ['http', 'https'], true)) {
            throw new TransportException('Expected an absolute HTTP request URL.');
        }

        return [$scheme, strtolower($parts['host']), $parts['port'] ?? ('https' === $scheme ? 443 : 80)];
    }

    private function assertUrlIsAllowed(string $url): void
    {
        $hostname = parse_url($url, \PHP_URL_HOST);

        if (!\is_string($hostname) || null === $blockedHostname = $this->denyList->getBlockedHostname($hostname)) {
            return;
        }

        throw new TransportException(\sprintf('Host "%s" is blocked by WALLABAG_FETCH_BLOCKED_HOSTS.', $blockedHostname));
    }
}
