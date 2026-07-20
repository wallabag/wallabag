<?php

namespace Wallabag\Tests\Unit\HttpClient;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Wallabag\HttpClient\HostnameDenyList;
use Wallabag\HttpClient\HostnameDenyListHttpClient;

class HostnameDenyListHttpClientTest extends TestCase
{
    public function testBlockedInitialRequestNeverInvokesInnerClient(): void
    {
        $innerClient = new MockHttpClient();
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['example.com']));

        try {
            $client->request('GET', 'https://user:secret@example.com/private');
            $this->fail('The blocked request should throw a transport exception.');
        } catch (TransportException $exception) {
            $this->assertSame('Host "example.com" is blocked by WALLABAG_FETCH_BLOCKED_HOSTS.', $exception->getMessage());
        }

        $this->assertSame(0, $innerClient->getRequestsCount());
    }

    public function testUrlPortsAreIgnoredWhenMatching(): void
    {
        $innerClient = new MockHttpClient();
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['example.com']));

        $this->expectException(TransportException::class);

        $client->request('GET', 'https://example.com:8443/private');
    }

    public function testAllowedRequestRetainsCallerOptions(): void
    {
        $innerClient = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->assertSame('POST', $method);
            $this->assertSame('https://allowed.example/path', $url);
            $this->assertSame('value', $options['user_data']);
            $this->assertContains('X-Test: preserved', $options['headers']);

            return new MockResponse('allowed');
        });
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));

        $response = $client->request('POST', 'https://allowed.example/path', [
            'headers' => ['X-Test' => 'preserved'],
            'user_data' => 'value',
        ]);

        $this->assertSame('allowed', $response->getContent());
    }

    public function testActivePolicyDisablesRedirectsUntilTheyCanBeValidated(): void
    {
        $innerClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 302, 'response_headers' => ['Location: https://allowed.example/next']]),
            new MockResponse('must not be requested'),
        ]);
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));

        $response = $client->request('GET', 'https://allowed.example/start');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(1, $innerClient->getRequestsCount());
    }

    public function testEmptyConfigurationDelegatesWithoutNormalizingArguments(): void
    {
        $response = new MockResponse('transparent');
        $innerClient = $this->createMock(HttpClientInterface::class);
        $innerClient->expects($this->once())
            ->method('request')
            ->with('GET', '/relative', ['extra' => 'untouched'])
            ->willReturn($response)
        ;
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList([]));

        $this->assertSame($response, $client->request('GET', '/relative', ['extra' => 'untouched']));
    }

    public function testResetIsDelegatedToTheInnerClient(): void
    {
        $innerClient = new MockHttpClient(new MockResponse('done'));
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));

        $client->request('GET', 'https://allowed.example');
        $this->assertSame(1, $innerClient->getRequestsCount());

        $client->reset();

        $this->assertSame(0, $innerClient->getRequestsCount());
    }
}
