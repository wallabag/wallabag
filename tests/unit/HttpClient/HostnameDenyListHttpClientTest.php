<?php

namespace Wallabag\Tests\Unit\HttpClient;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
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

    public function testBlocksRedirectBeforeInvokingRedirectedRequest(): void
    {
        $innerClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 302, 'redirect_url' => 'https://blocked.example/secret']),
            new MockResponse('must not be requested'),
        ]);
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));

        $response = $client->request('GET', 'https://allowed.example/start');

        $this->assertSame(1, $innerClient->getRequestsCount());

        try {
            $response->getHeaders(false);
            $this->fail('The blocked redirect should throw a transport exception.');
        } catch (TransportException $exception) {
            $this->assertSame('Host "blocked.example" is blocked by WALLABAG_FETCH_BLOCKED_HOSTS.', $exception->getMessage());
        }

        $this->assertSame(1, $innerClient->getRequestsCount());
    }

    public function testActivePolicyDoesNotConsumeTheResponseDuringRequest(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->never())->method('getStatusCode');
        $response->expects($this->never())->method('getHeaders');
        $response->expects($this->never())->method('getInfo');
        $response->expects($this->once())->method('cancel');
        $innerClient = $this->createMock(HttpClientInterface::class);
        $innerClient->expects($this->once())->method('request')->willReturn($response);
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));

        $decoratedResponse = $client->request('GET', 'https://allowed.example/start');

        $this->assertInstanceOf(AsyncResponse::class, $decoratedResponse);

        $decoratedResponse->cancel();
    }

    public function testFollowsAllowedRedirectChain(): void
    {
        $innerClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 302, 'redirect_url' => 'https://allowed.example/two']),
            new MockResponse('', ['http_code' => 301, 'redirect_url' => 'https://allowed.example/three']),
            new MockResponse('done'),
        ]);
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));

        $response = $client->request('GET', 'https://allowed.example/one');

        $this->assertSame('done', $response->getContent());
        $this->assertSame(3, $innerClient->getRequestsCount());
        $this->assertSame(2, $response->getInfo('redirect_count'));
    }

    public function testFollowsTheTransportResolvedRelativeRedirectUrl(): void
    {
        $requestCount = 0;
        $innerClient = new MockHttpClient(function (string $method, string $url) use (&$requestCount): MockResponse {
            ++$requestCount;

            if (1 === $requestCount) {
                $this->assertSame('https://allowed.example/path/start', $url);

                return new MockResponse('', [
                    'http_code' => 302,
                    'response_headers' => ['Location: ../result'],
                    'redirect_url' => 'https://allowed.example/result',
                ]);
            }

            $this->assertSame('https://allowed.example/result', $url);

            return new MockResponse('done');
        });
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));

        $response = $client->request('GET', 'https://allowed.example/path/start');

        $this->assertSame('done', $response->getContent());
    }

    public function testHonorsRedirectLimit(): void
    {
        $innerClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 302, 'redirect_url' => 'https://allowed.example/two']),
            new MockResponse('stopped', ['http_code' => 302, 'redirect_url' => 'https://allowed.example/three']),
            new MockResponse('must not be requested'),
        ]);
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));

        $response = $client->request('GET', 'https://allowed.example/one', ['max_redirects' => 1]);

        $this->assertSame('stopped', $response->getContent(false));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(2, $innerClient->getRequestsCount());
        $this->assertSame(1, $response->getInfo('redirect_count'));
    }

    public function testLeavesRedirectsUntouchedWhenFollowingIsDisabled(): void
    {
        $innerClient = new MockHttpClient([
            new MockResponse('stopped', ['http_code' => 302, 'redirect_url' => 'https://blocked.example/secret']),
            new MockResponse('must not be requested'),
        ]);
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));

        $response = $client->request('GET', 'https://allowed.example/start', ['max_redirects' => 0]);

        $this->assertSame('stopped', $response->getContent(false));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(1, $innerClient->getRequestsCount());
    }

    public function testStripsSensitiveHeadersWhenRedirectHostChanges(): void
    {
        $requestCount = 0;
        $innerClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$requestCount): MockResponse {
            ++$requestCount;

            if (1 === $requestCount) {
                return new MockResponse('', ['http_code' => 302, 'redirect_url' => 'https://other.example/result']);
            }

            $headers = implode("\n", $options['headers']);
            $this->assertStringNotContainsString('Authorization:', $headers);
            $this->assertStringNotContainsString('Cookie:', $headers);
            $this->assertStringNotContainsString('Host:', $headers);
            $this->assertStringContainsString('X-Safe: retained', $headers);

            return new MockResponse('safe');
        });
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));

        $response = $client->request('GET', 'https://allowed.example/start', [
            'headers' => [
                'Authorization' => 'Bearer secret',
                'Cookie' => 'session=secret',
                'Host' => 'allowed.example',
                'X-Safe' => 'retained',
            ],
        ]);

        $this->assertSame('safe', $response->getContent());
    }

    public function testStreamsDecoratedResponses(): void
    {
        $innerClient = new MockHttpClient(new MockResponse(['one', 'two']));
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));
        $response = $client->request('GET', 'https://allowed.example/stream');
        $content = '';

        foreach ($client->stream($response) as $chunk) {
            if (!$chunk->isFirst() && !$chunk->isLast()) {
                $content .= $chunk->getContent();
            }
        }

        $this->assertSame('onetwo', $content);
    }

    public function testStreamsRedirectingResponsesWithoutSharingState(): void
    {
        $innerClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 302, 'redirect_url' => 'https://allowed.example/one-final']),
            new MockResponse('', ['http_code' => 302, 'redirect_url' => 'https://allowed.example/two-final']),
            new MockResponse('one'),
            new MockResponse('two'),
        ]);
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));
        $firstResponse = $client->request('GET', 'https://allowed.example/one', ['max_redirects' => 1]);
        $secondResponse = $client->request('GET', 'https://allowed.example/two', ['max_redirects' => 1]);
        $content = [
            spl_object_id($firstResponse) => '',
            spl_object_id($secondResponse) => '',
        ];

        foreach ($client->stream([$firstResponse, $secondResponse]) as $response => $chunk) {
            if (!$chunk->isFirst() && !$chunk->isLast()) {
                $content[spl_object_id($response)] .= $chunk->getContent();
            }
        }

        $this->assertSame('one', $content[spl_object_id($firstResponse)]);
        $this->assertSame('two', $content[spl_object_id($secondResponse)]);
        $this->assertSame(1, $firstResponse->getInfo('redirect_count'));
        $this->assertSame(1, $secondResponse->getInfo('redirect_count'));
    }

    public function testEmptyConfigurationDelegatesStreaming(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(ResponseStreamInterface::class);
        $innerClient = $this->createMock(HttpClientInterface::class);
        $innerClient->expects($this->once())
            ->method('stream')
            ->with($response, 0.5)
            ->willReturn($stream)
        ;
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList([]));

        $this->assertSame($stream, $client->stream($response, 0.5));
    }

    public function testWithOptionsPreservesDefaultsAndPolicy(): void
    {
        $innerClient = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->assertContains('X-Default: retained', $options['headers']);

            return new MockResponse('configured');
        });
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));
        $configuredClient = $client->withOptions(['headers' => ['X-Default' => 'retained']]);

        $this->assertSame('configured', $configuredClient->request('GET', 'https://allowed.example')->getContent());

        $this->expectException(TransportException::class);
        $configuredClient->request('GET', 'https://blocked.example');
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

    public function testLoggerIsDelegatedToTheInnerClient(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $innerClient = $this->createMock(ScopingHttpClient::class);
        $innerClient->expects($this->once())->method('setLogger')->with($logger);
        $client = new HostnameDenyListHttpClient($innerClient, new HostnameDenyList(['blocked.example']));

        $client->setLogger($logger);
    }
}
