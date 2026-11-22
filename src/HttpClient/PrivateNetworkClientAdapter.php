<?php

namespace Wallabag\HttpClient;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * With max_redirects set to 0, NoPrivateNetworkHttpClient returns an AsyncResponse without a passthru callback.
 * In Symfony 5.4.56, the outer hostname decorator's stream calls getStatusCode() on that inner response.
 * This can consume the unbuffered body, so getContent(false) returns an empty string even for a 200 response.
 * PrivateNetworkResponse captures the status and headers from getInfo() when the first chunk arrives,
 * allowing the outer decorator to read them without re-entering the inner stream.
 *
 * TODO: Remove this adapter and PrivateNetworkResponse when upgrading to a minimum of symfony/http-client 6.4.19.
 * Keep the regression test that reads the complete response body through the PSR-18 client.
 *
 * @see https://github.com/symfony/symfony/issues/59574
 * @see https://github.com/symfony/symfony/pull/59689
 */
final class PrivateNetworkClientAdapter implements HttpClientInterface, LoggerAwareInterface, ResetInterface
{
    public function __construct(
        private NoPrivateNetworkHttpClient $client,
        private readonly HostnameDenyList $denyList,
    ) {
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        /** @var AsyncResponse $response */
        $response = $this->client->request($method, $url, $options);

        return $this->denyList->isEmpty() ? $response : new PrivateNetworkResponse($response);
    }

    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        if ($this->denyList->isEmpty()) {
            return $this->client->stream($responses, $timeout);
        }

        if ($responses instanceof PrivateNetworkResponse) {
            $responses = [$responses];
        } elseif (!is_iterable($responses)) {
            throw new \TypeError('Expected a private-network response or an iterable of responses.');
        }

        $innerResponses = [];
        $wrappedResponses = new \SplObjectStorage();
        foreach ($responses as $response) {
            if (!$response instanceof PrivateNetworkResponse) {
                throw new \TypeError('Expected a private-network response.');
            }

            $innerResponse = $response->getWrappedResponse();
            $innerResponses[] = $innerResponse;
            $wrappedResponses[$innerResponse] = $response;
        }

        return new ResponseStream((function () use ($innerResponses, $wrappedResponses, $timeout): \Generator {
            foreach ($this->client->stream($innerResponses, $timeout) as $innerResponse => $chunk) {
                $response = $wrappedResponses[$innerResponse];
                if (null === $chunk->getError() && $chunk->isFirst()) {
                    $response->captureHeaders();
                }

                yield $response => $chunk;
            }
        })());
    }

    public function withOptions(array $options): self
    {
        $clone = clone $this;
        $clone->client = $this->client->withOptions($options);

        return $clone;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->client->setLogger($logger);
    }

    public function reset(): void
    {
        $this->client->reset();
    }
}
