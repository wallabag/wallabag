<?php

namespace Wallabag\Tests\Integration\HttpClient;

use Craue\ConfigBundle\Util\Config;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Wallabag\HttpClient\HostnameDenyList;
use Wallabag\HttpClient\WallabagClient;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class HostnameDenyListWiringTest extends WallabagKernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::getContainer()->set(HostnameDenyList::class, new HostnameDenyList(['.blocked.test']));
        $config = $this->createMock(Config::class);
        $config->method('get')
            ->with('restricted_access')
            ->willReturn('0')
        ;
        static::getContainer()->set('craue_config_default', $config);
    }

    /**
     * @dataProvider fetchClientServiceIdProvider
     */
    public function testEveryFetchClientUsesTheSharedHostnameBoundary(string $clientServiceId): void
    {
        $client = static::getContainer()->get($clientServiceId);
        $this->assertInstanceOf(HttpClientInterface::class, $client);

        try {
            $client->request('GET', 'https://user:secret@deep.blocked.test/private');
            $this->fail(\sprintf('Service "%s" did not reject the blocked hostname.', $clientServiceId));
        } catch (TransportExceptionInterface $exception) {
            $this->assertSame('Host "deep.blocked.test" is blocked by WALLABAG_FETCH_BLOCKED_HOSTS.', $exception->getMessage());
        }
    }

    public static function fetchClientServiceIdProvider(): iterable
    {
        yield 'default client' => [HttpClientInterface::class];
        yield 'download images client' => [HttpClientInterface::class . ' $downloadImagesClient'];
        yield 'browser client' => [HttpClientInterface::class . ' $browserClient'];
        yield 'request_html client' => [HttpClientInterface::class . ' $requestHtmlFunctionClient'];
        yield 'Wallabag client' => [WallabagClient::class];
    }

    public function testWallabagClientStillBlocksPrivateNetworkTargets(): void
    {
        $client = static::getContainer()->get(WallabagClient::class);

        $this->expectException(TransportExceptionInterface::class);
        $this->expectExceptionMessage('Host "127.0.0.1" is blocked');

        $client->request('GET', 'http://127.0.0.1');
    }

    public function testPsr18ClientUsesTheSharedHostnameBoundary(): void
    {
        $client = static::getContainer()->get('psr18.wallabag.client');
        $this->assertInstanceOf(Psr18Client::class, $client);

        try {
            $client->sendRequest(new Request('GET', 'https://deep.blocked.test/private'));
            $this->fail('The PSR-18 client did not reject the blocked hostname.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Host "deep.blocked.test" is blocked by WALLABAG_FETCH_BLOCKED_HOSTS.', $exception->getMessage());
        }
    }

    /**
     * @dataProvider allowedResponses
     */
    public function testPsr18ClientPreservesTheAllowedResponse(bool $redirect, int $status): void
    {
        $responses = [];

        if ($redirect) {
            $responses[] = new MockResponse('', [
                'http_code' => 302,
                'redirect_url' => 'https://93.184.216.34/redirected',
            ]);
        }
        $responses[] = new MockResponse(['<article>', 'Allowed content', '</article>'], [
            'http_code' => $status,
            'response_headers' => ['content-type: text/html', 'link: </one>', 'link: </two>'],
        ]);
        $transport = $this->mockArticleTransport($responses);

        $client = static::getContainer()->get('psr18.wallabag.client');
        $response = $client->sendRequest(new Request('GET', 'https://93.184.216.34/article'));

        $this->assertSame($status, $response->getStatusCode());
        $this->assertSame('text/html', $response->getHeaderLine('content-type'));
        $this->assertSame(['</one>', '</two>'], $response->getHeader('link'));
        $this->assertSame('<article>Allowed content</article>', (string) $response->getBody());
        $this->assertSame(\count($responses), $transport->getRequestsCount());
    }

    public static function allowedResponses(): iterable
    {
        yield 'direct article' => [false, 200];
        yield 'redirected article' => [true, 200];
        yield 'origin refuses the request' => [false, 403];
    }

    public function testWallabagClientPreservesTheResponseWhenRedirectsAreDisabled(): void
    {
        $transport = $this->mockArticleTransport([new MockResponse(['Moved', ' temporarily'], [
            'http_code' => 302,
            'redirect_url' => 'https://93.184.216.34/redirected',
            'response_headers' => ['location: /redirected'],
        ])]);

        $client = static::getContainer()->get(WallabagClient::class);
        $response = $client->request('GET', 'https://93.184.216.34/article', ['max_redirects' => 0]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(['/redirected'], $response->getHeaders(false)['location']);
        $this->assertSame('Moved temporarily', $response->getContent(false));
        $this->assertSame(1, $transport->getRequestsCount());
    }

    public function testWallabagClientStreamsAllowedResponsesIndependently(): void
    {
        $this->mockArticleTransport([
            new MockResponse(['first', ' article']),
            new MockResponse(['second', ' article']),
        ]);

        $client = static::getContainer()->get(WallabagClient::class);
        $first = $client->request('GET', 'https://93.184.216.34/first');
        $second = $client->request('GET', 'https://93.184.216.34/second');
        $bodies = [spl_object_id($first) => '', spl_object_id($second) => ''];

        foreach ($client->stream([$first, $second]) as $response => $chunk) {
            $bodies[spl_object_id($response)] .= $chunk->getContent();
        }

        $this->assertSame(['first article', 'second article'], array_values($bodies));
    }

    private function mockArticleTransport(array $responses): MockHttpClient
    {
        $transport = new MockHttpClient($responses);
        static::getContainer()->set('wallabag.article_fetch.private_network_client', new NoPrivateNetworkHttpClient($transport));

        return $transport;
    }
}
