<?php

namespace Wallabag\Tests\Unit\HttpClient;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Wallabag\HttpClient\Authenticator;
use Wallabag\HttpClient\WallabagClient;
use Wallabag\SiteConfig\ArraySiteConfigBuilder;
use Wallabag\SiteConfig\LoginFormAuthenticator;

class WallabagClientTest extends TestCase
{
    public function testRequestBlocksPrivateNetworkTargets(): void
    {
        $authenticator = $this->getMockBuilder(LoginFormAuthenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = new WallabagClient(
            0,
            new HttpBrowser(),
            new Authenticator(new ArraySiteConfigBuilder([]), $authenticator),
            new NullLogger(),
        );

        $this->expectException(TransportExceptionInterface::class);
        $this->expectExceptionMessage('Host "127.0.0.1" is blocked');

        $client->request('GET', 'http://127.0.0.1');
    }
}
