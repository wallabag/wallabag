<?php

namespace Wallabag\Tests\Unit\Helper;

use Laminas\Feed\Reader\Exception\ExceptionInterface as ReaderException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Wallabag\Helper\FeedReader;

class FeedReaderTest extends TestCase
{
    /** @dataProvider formats */
    public function testRecognizesDirectFeed(string $xml): void
    {
        $url = 'https://8.8.8.8/feed';
        $client = new MockHttpClient(static function ($method, $url, $options) use ($xml): MockResponse {
            self::assertSame('GET', $method);
            self::assertSame(20.0, $options['timeout']);
            self::assertContains('User-Agent: wallabag-test', $options['headers']);
            self::assertArrayNotHasKey('authorization', $options['normalized_headers']);
            self::assertArrayNotHasKey('cookie', $options['normalized_headers']);

            return new MockResponse($xml);
        });

        self::assertSame($url, (new FeedReader('wallabag-test', $client))->resolve($url));
    }

    public function testDiscoversAtomRelativeToFinalPageBeforeRss(): void
    {
        $redirect = new MockResponse('', ['http_code' => 302, 'redirect_url' => 'https://8.8.8.8:8443/blog/']);
        $page = <<<'HTML'
            <html><head>
                <link rel="stylesheet" type="text/css" href="/site.css">
                <link rel="alternate" type="application/atom+xml" href="https://example.org:99999/atom">
                <link rel="alternate" type="application/rss+xml" href="/rss">
                <link rel="alternate" type="application/atom+xml" href="atom">
            </head></html>
            HTML;
        $reader = new FeedReader('wallabag', new MockHttpClient([$redirect, new MockResponse($page)]));

        self::assertSame('https://8.8.8.8:8443/blog/atom', $reader->resolve('https://8.8.8.8/'));
    }

    public function testRejectsUnsupportedUrlWithoutSendingARequest(): void
    {
        $client = new MockHttpClient();
        $reader = new FeedReader('wallabag', $client);

        $this->expectException(\InvalidArgumentException::class);
        try {
            $reader->resolve('ftp://example.org/feed');
        } finally {
            self::assertSame(0, $client->getRequestsCount());
        }
    }

    /** @dataProvider responsesWithoutFeeds */
    public function testRejectsResponsesWithoutAFeed(string $body): void
    {
        $reader = new FeedReader('wallabag', new MockHttpClient(new MockResponse($body)));

        $this->expectException(ReaderException::class);
        $reader->resolve('https://8.8.8.8/');
    }

    public function responsesWithoutFeeds(): iterable
    {
        yield 'empty response' => [''];
        yield 'page without alternate feed' => ['<html><head><title>Example</title></head></html>'];
        yield 'standalone Atom entry' => ['<entry xmlns="http://www.w3.org/2005/Atom"><title>Article</title></entry>'];
    }

    public function testRejectsPrivateDestination(): void
    {
        $this->expectException(TransportExceptionInterface::class);
        (new FeedReader('wallabag', new MockHttpClient()))->resolve('http://127.0.0.1/');
    }

    public function testRejectsRedirectToPrivateDestination(): void
    {
        $response = new MockResponse('', ['http_code' => 302, 'redirect_url' => 'http://127.0.0.1/']);
        $this->expectException(TransportExceptionInterface::class);
        (new FeedReader('wallabag', new MockHttpClient($response)))->resolve('https://8.8.8.8/');
    }

    public function formats(): iterable
    {
        yield 'RSS' => ['<rss version="2.0"><channel><title>Example</title></channel></rss>'];
        yield 'Atom' => ['<feed xmlns="http://www.w3.org/2005/Atom"><title>Example</title></feed>'];
        yield 'RDF' => ['<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/"><channel rdf:about="https://example.org/"><title>Example</title></channel></rdf:RDF>'];
    }
}
