<?php

namespace Wallabag\HttpClient;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Component\HttpClient\Response\StreamableInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Exposes response metadata while the article redirect decorator reads the stream.
 *
 * @internal
 */
final class PrivateNetworkResponse implements ResponseInterface, StreamableInterface
{
    private ?int $statusCode = null;
    private array $headers = [];

    public function __construct(private readonly AsyncResponse $response)
    {
    }

    public function __destruct()
    {
        if (null !== $this->statusCode && null === $this->response->getInfo('error')) {
            // Stop Symfony 5.4 from trying to initialize this stream again during destruction.
            $this->response->cancel();
        }
    }

    public function getWrappedResponse(): AsyncResponse
    {
        return $this->response;
    }

    public function captureHeaders(): void
    {
        // Calling getStatusCode() or getHeaders() on the inner AsyncResponse here drains the stream.
        $this->statusCode = $this->response->getInfo('http_code');
        $this->headers = [];
        foreach ($this->response->getInfo('response_headers') as $line) {
            if (str_starts_with($line, 'HTTP/')) {
                $this->headers = [];
            } elseif (2 === \count($header = explode(':', $line, 2))) {
                $this->headers[strtolower($header[0])][] = ltrim($header[1]);
            }
        }
    }

    public function getStatusCode(): int
    {
        return $this->statusCode ?? $this->response->getStatusCode();
    }

    public function getHeaders(bool $throw = true): array
    {
        if (null === $this->statusCode) {
            return $this->response->getHeaders($throw);
        }

        if ($throw) {
            match (true) {
                $this->statusCode >= 500 => throw new ServerException($this),
                $this->statusCode >= 400 => throw new ClientException($this),
                $this->statusCode >= 300 => throw new RedirectionException($this),
                default => null,
            };
        }

        return $this->headers;
    }

    public function getContent(bool $throw = true): string
    {
        return $this->response->getContent($throw);
    }

    public function toArray(bool $throw = true): array
    {
        return $this->response->toArray($throw);
    }

    public function cancel(): void
    {
        $this->response->cancel();
    }

    public function getInfo(?string $type = null): mixed
    {
        return $this->response->getInfo($type);
    }

    public function toStream(bool $throw = true)
    {
        if ($throw) {
            $this->getHeaders();
        }

        return $this->response->toStream(false);
    }
}
