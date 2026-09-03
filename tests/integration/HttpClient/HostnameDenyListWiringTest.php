<?php

namespace Wallabag\Tests\Integration\HttpClient;

use Craue\ConfigBundle\Util\Config;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpClient\Psr18Client;
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
            ->willReturn(0)
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
        yield 'Pocket client' => [HttpClientInterface::class . ' $pocketClient'];
        yield 'Wallabag client' => [WallabagClient::class];
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
}
