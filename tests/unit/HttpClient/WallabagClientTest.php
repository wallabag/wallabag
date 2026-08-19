<?php

namespace Wallabag\Tests\Unit\HttpClient;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wallabag\HttpClient\Authenticator;
use Wallabag\HttpClient\WallabagClient;

class WallabagClientTest extends TestCase
{
    public function testDelegatesToInjectedClientWithDefaultTimeout(): void
    {
        $innerClient = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->assertSame('GET', $method);
            $this->assertSame('https://example.com/article', $url);
            $this->assertSame(10.0, $options['timeout']);

            return new MockResponse('article');
        });
        $client = $this->createClient($innerClient, 0);

        $this->assertSame('article', $client->request('GET', 'https://example.com/article')->getContent());
    }

    public function testCallerCanOverrideDefaultTimeout(): void
    {
        $innerClient = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->assertSame(2.5, $options['timeout']);

            return new MockResponse('article');
        });
        $client = $this->createClient($innerClient, 0);

        $this->assertSame('article', $client->request('GET', 'https://example.com/article', ['timeout' => 2.5])->getContent());
    }

    public function testWithOptionsRetainsConfiguredDefaults(): void
    {
        $innerClient = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->assertSame(4.0, $options['timeout']);
            $this->assertContains('X-Client: configured', $options['headers']);

            return new MockResponse('article');
        });
        $client = $this->createClient($innerClient, 0)->withOptions([
            'timeout' => 4,
            'headers' => ['X-Client' => 'configured'],
        ]);

        $this->assertSame('article', $client->request('GET', 'https://example.com/article')->getContent());
    }

    public function testRestrictedAccessCanRetryThroughInjectedClient(): void
    {
        $requests = 0;
        $innerClient = new MockHttpClient(static function () use (&$requests): MockResponse {
            ++$requests;

            return new MockResponse(1 === $requests ? 'login required' : 'article');
        });
        $authenticator = $this->createMock(Authenticator::class);
        $authenticator->expects($this->once())
            ->method('loginIfRequired')
            ->with('https://example.com/article')
            ->willReturn(true)
        ;
        $authenticator->expects($this->once())
            ->method('loginIfRequested')
            ->willReturn(true)
        ;
        $client = $this->createClient($innerClient, 1, $authenticator);

        $this->assertSame('article', $client->request('GET', 'https://example.com/article')->getContent());
        $this->assertSame(2, $requests);
    }

    private function createClient(MockHttpClient $httpClient, int $restrictedAccess, ?Authenticator $authenticator = null): WallabagClient
    {
        return new WallabagClient(
            $httpClient,
            $restrictedAccess,
            new HttpBrowser(new MockHttpClient()),
            $authenticator ?? $this->createMock(Authenticator::class),
            new NullLogger(),
        );
    }
}
