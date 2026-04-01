<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Graby\HttpClient\Plugin\ServerSideRequestForgeryProtection\ServerSideRequestForgeryProtectionPlugin;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use Http\Client\Common\PluginClient;
use Http\Client\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Wallabag\CoreBundle\Helper\HttpClientFactory;

class HttpClientFactoryTest extends TestCase
{
    public function testCreateClientWrapsClientWithSsrfProtectionWhenRestrictedAccessIsDisabled()
    {
        $factory = new HttpClientFactory(new CookieJar(), 0, new NullLogger());

        $client = $factory->createClient();

        $this->assertInstanceOf(PluginClient::class, $client);
        $this->assertContainsOnlyInstancesOf(
            ServerSideRequestForgeryProtectionPlugin::class,
            $this->getPlugins($client)
        );
        $this->assertNull($this->getInnerGuzzleClient($client)->getDefaultOption('cookies'));
    }

    public function testCreateClientWrapsGuzzleAdapterWithSsrfProtectionWhenRestrictedAccessIsEnabled()
    {
        $cookieJar = new CookieJar();
        $factory = new HttpClientFactory($cookieJar, 1, new NullLogger());

        $client = $factory->createClient();

        $this->assertInstanceOf(PluginClient::class, $client);
        $this->assertContainsOnlyInstancesOf(
            ServerSideRequestForgeryProtectionPlugin::class,
            $this->getPlugins($client)
        );
        $this->assertSame($cookieJar, $this->getInnerGuzzleClient($client)->getDefaultOption('cookies'));
    }

    /**
     * @dataProvider provideBlockedLocalUrls
     */
    public function testCreateClientBlocksRequestsToLocalAddresses(string $url)
    {
        $factory = new HttpClientFactory(new CookieJar(), 0, new NullLogger());

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('matches a blacklisted value');

        $factory->createClient()->sendRequest(new Request('GET', $url));
    }

    public function provideBlockedLocalUrls(): array
    {
        return [
            'localhost loopback' => ['http://127.0.0.1/'],
            'cloud metadata' => ['http://169.254.169.254/latest/meta-data/'],
        ];
    }

    private function getPlugins(PluginClient $client): array
    {
        $property = (new \ReflectionObject($client))->getProperty('plugins');
        $property->setAccessible(true);

        return $property->getValue($client);
    }

    private function getInnerGuzzleClient(PluginClient $client): GuzzleClient
    {
        $property = (new \ReflectionObject($client))->getProperty('client');
        $property->setAccessible(true);
        $emulatedAsyncClient = $property->getValue($client);

        $property = (new \ReflectionObject($emulatedAsyncClient))->getProperty('httpClient');
        $property->setAccessible(true);
        $guzzleAdapter = $property->getValue($emulatedAsyncClient);

        $property = (new \ReflectionObject($guzzleAdapter))->getProperty('client');
        $property->setAccessible(true);

        return $property->getValue($guzzleAdapter);
    }
}
