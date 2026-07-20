<?php

namespace Wallabag\Tests\Unit\HttpClient;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Wallabag\HttpClient\Authenticator;
use Wallabag\HttpClient\WallabagClient;

class WallabagClientTest extends TestCase
{
    public function testRequestBlocksPrivateNetworkTargets(): void
    {
        $innerClient = new MockHttpClient();
        $client = $this->createClient(new NoPrivateNetworkHttpClient($innerClient), 0);

        try {
            $client->request('GET', 'http://127.0.0.1');
            $this->fail('The private destination should be blocked.');
        } catch (TransportExceptionInterface $exception) {
            $this->assertStringContainsString('Host "127.0.0.1" is blocked', $exception->getMessage());
        }

        $this->assertSame(0, $innerClient->getRequestsCount());
    }

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

    private function createClient(HttpClientInterface $httpClient, int $restrictedAccess, ?Authenticator $authenticator = null): WallabagClient
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
