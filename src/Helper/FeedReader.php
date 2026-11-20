<?php

namespace Wallabag\Helper;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Laminas\Feed\Reader\Entry\EntryInterface;
use Laminas\Feed\Reader\Exception\ExceptionInterface as ReaderException;
use Laminas\Feed\Reader\Exception\RuntimeException;
use Laminas\Feed\Reader\Feed\AbstractFeed;
use Laminas\Feed\Reader\Reader;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FeedReader
{
    private readonly HttpClientInterface $client;

    public function __construct(private readonly string $userAgent, HttpClientInterface $client)
    {
        $this->client = new NoPrivateNetworkHttpClient($client);
    }

    public function resolve(string $url): string
    {
        $response = $this->request($url);
        $body = $response->getContent();

        try {
            $this->parse($body);

            return $url;
        } catch (ReaderException $failure) {
            $effectiveUrl = $response->getInfo('url') ?: $url;

            return $this->discover($body, $effectiveUrl) ?? throw $failure;
        }
    }

    /** @return iterable<EntryInterface> */
    public function read(string $url): iterable
    {
        $response = $this->request($url);
        $feed = $this->parse($response->getContent());
        $feed->setOriginalSourceUri($response->getInfo('url') ?: $url);

        return $feed;
    }

    private function request(string $url): ResponseInterface
    {
        if (!FeedUrlValidator::isValid($url)) {
            throw new \InvalidArgumentException('Feed URLs must use HTTP or HTTPS without credentials.');
        }

        return $this->client->request('GET', $url, [
            'headers' => ['User-Agent' => $this->userAgent],
            'timeout' => 20,
        ]);
    }

    private function parse(string $body): AbstractFeed
    {
        $previous = libxml_use_internal_errors();
        try {
            $feed = Reader::importString($body);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (!$feed instanceof AbstractFeed) {
            throw new RuntimeException('The response does not contain a feed.');
        }

        return $feed;
    }

    private function discover(string $body, string $effectiveUrl): ?string
    {
        $document = new \DOMDocument();
        if ('' === $body || !$document->loadHTML($body, \LIBXML_NONET | \LIBXML_NOERROR | \LIBXML_NOWARNING)) {
            return null;
        }

        $types = ['application/atom+xml', 'application/rss+xml', 'application/rdf+xml'];
        $links = [];
        foreach ($document->getElementsByTagName('link') as $link) {
            $type = strtolower($link->getAttribute('type'));
            $href = trim($link->getAttribute('href'));
            if ('alternate' !== strtolower($link->getAttribute('rel')) || '' === $href || !\in_array($type, $types, true) || isset($links[$type])) {
                continue;
            }

            try {
                $candidate = (string) UriResolver::resolve(new Uri($effectiveUrl), new Uri($href));
            } catch (\InvalidArgumentException) {
                continue;
            }
            if (FeedUrlValidator::isValid($candidate)) {
                $links[$type] = $candidate;
            }
        }

        foreach ($types as $type) {
            if (isset($links[$type])) {
                return $links[$type];
            }
        }

        return null;
    }
}
