<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Graby\HttpClient\Plugin\ServerSideRequestForgeryProtection\ServerSideRequestForgeryProtectionPlugin;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Http\Client\Common\Plugin\RedirectPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\Exception\HttpException;
use Http\Client\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Wallabag\CoreBundle\GuzzleSiteAuthenticator\GrabySiteConfigBuilder;
use Wallabag\CoreBundle\GuzzleSiteAuthenticator\SiteAuthenticator;
use Wallabag\CoreBundle\GuzzleSiteAuthenticator\SiteConfig;
use Wallabag\CoreBundle\Helper\HttpClientFactory;

class HttpClientFactoryTest extends TestCase
{
    private $configBuilder;

    protected function setUp(): void
    {
        $this->configBuilder = $this->getMockBuilder(GrabySiteConfigBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDisabledRestrictedAccessDoesNotAuthenticate(): void
    {
        $this->configBuilder->expects($this->never())->method('buildForHost');
        $history = [];
        $handler = HandlerStack::create(new MockHandler([new Response(200, [], 'article')]));
        $handler->push(Middleware::history($history));
        $client = $this->createClient($handler, '0');

        $response = $client->sendRequest(new Request('GET', 'https://example.org/article'));

        $this->assertSame('article', (string) $response->getBody());
        $this->assertCount(1, $history);
        $this->assertSame(10, $history[0]['options']['timeout']);
        $this->assertFalse($history[0]['request']->hasHeader('Cookie'));
    }

    public function testPublicArticleDoesNotLoginAndClearsOldCookies(): void
    {
        $this->configBuilder->method('buildForHost')->willReturn(false);
        $cookies = CookieJar::fromArray(['analytics' => 'old'], 'example.org');
        $handler = new MockHandler([new Response(200, [], 'article')]);
        $factory = new HttpClientFactory($cookies, '1', new NullLogger(), new SiteAuthenticator($this->configBuilder));
        $client = $factory->createClient(['handler' => $handler]);

        $this->assertSame('article', (string) $client->sendRequest(new Request('GET', 'https://example.org/article'))->getBody());
        $this->assertCount(0, $cookies);
        $this->assertFalse($handler->getLastRequest()->hasHeader('Cookie'));
    }

    public function testLoginUsesFormExpressionsAndSharesCookies(): void
    {
        $config = $this->siteConfig([
            'token' => '@=xpath(\'//input[@name="token"]\', request_html(config.getLoginUri()))',
            'nonce' => '@=preg_match(\'/nonce=([a-z]+)/\', \'nonce=abc\')',
            'remember' => 'yes',
        ]);
        $this->configBuilder->method('buildForHost')->with('example.org')->willReturn($config);
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '<input name="token" value="csrf-token">'),
            new Response(200, ['Set-Cookie' => 'session=valid; Path=/; Secure']),
            new Response(200, [], '<article>private article</article>'),
        ]));
        $handler->push(Middleware::history($history));
        $client = $this->createClient($handler);

        $response = $client->sendRequest(new Request('GET', 'https://example.org/article'));

        $this->assertCount(3, $history);
        $this->assertSame('GET', $history[0]['request']->getMethod());
        $this->assertSame('https://example.org/login', (string) $history[0]['request']->getUri());
        $this->assertSame('POST', $history[1]['request']->getMethod());
        parse_str((string) $history[1]['request']->getBody(), $fields);
        $this->assertSame([
            'username' => 'reader',
            'password' => 'secret',
            'token' => 'csrf-token',
            'nonce' => 'abc',
            'remember' => 'yes',
        ], $fields);
        $this->assertTrue($history[1]['options']['verify']);
        $this->assertSame('session=valid', $history[2]['request']->getHeaderLine('Cookie'));
        $this->assertSame('<article>private article</article>', $response->getBody()->getContents());
    }

    public function testExpiredSessionLogsInAndRetriesArticle(): void
    {
        $this->configBuilder->method('buildForHost')->willReturn($this->siteConfig());
        $cookies = CookieJar::fromArray(['session' => 'expired'], 'example.org');
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '<div class="login-required">Login</div>'),
            new Response(200, ['Set-Cookie' => 'session=renewed; Domain=example.org; Path=/; Secure']),
            new Response(200, [], 'private article'),
        ]));
        $handler->push(Middleware::history($history));
        $client = $this->createClient($handler, '1', ['cookies' => $cookies]);

        $response = $client->sendRequest(new Request('GET', 'https://example.org/article'));

        $this->assertSame('private article', $response->getBody()->getContents());
        $this->assertCount(3, $history);
        $this->assertSame('session=expired', $history[0]['request']->getHeaderLine('Cookie'));
        $this->assertSame('POST', $history[1]['request']->getMethod());
        $this->assertSame('session=renewed', $history[2]['request']->getHeaderLine('Cookie'));
    }

    public function testLoginRetriesAreBoundedAndResetForEachFetch(): void
    {
        $this->configBuilder->method('buildForHost')->willReturn($this->siteConfig());
        $responses = [];
        for ($i = 0; $i < 6; ++$i) {
            $responses[] = new Response(200);
            $responses[] = new Response(200, [], '<div class="login-required">Login</div>');
        }
        $handler = new MockHandler($responses);
        $client = $this->createClient($handler);

        for ($i = 0; $i < 2; ++$i) {
            $response = $client->sendRequest(new Request('GET', 'https://example.org/article'));
            $this->assertSame('<div class="login-required">Login</div>', $response->getBody()->getContents());
        }
        $this->assertCount(0, $handler);
    }

    public function testFailedLoginIsNotSwallowed(): void
    {
        $this->configBuilder->method('buildForHost')->willReturn($this->siteConfig());
        $client = $this->createClient(new MockHandler([new Response(403)]));

        $this->expectException(HttpException::class);
        $client->sendRequest(new Request('GET', 'https://example.org/article'));
    }

    public function testDisabledCookiesDoNotAttemptLogin(): void
    {
        $this->configBuilder->expects($this->never())->method('buildForHost');
        $client = $this->createClient(new MockHandler([new Response(200, [], 'article')]), '1', ['cookies' => false]);

        $this->assertSame('article', (string) $client->sendRequest(new Request('GET', 'https://example.org/article'))->getBody());
    }

    public function restrictedAccessProvider(): array
    {
        return [
            'disabled' => ['0'],
            'enabled' => ['1'],
        ];
    }

    /**
     * @dataProvider restrictedAccessProvider
     */
    public function testMainClientDoesNotFollowRedirects(string $restrictedAccess): void
    {
        $this->configBuilder->method('buildForHost')->willReturn(false);
        $handler = new MockHandler([
            new Response(302, ['Location' => 'http://169.254.169.254/latest/meta-data/']),
            new Response(200, [], 'private network'),
        ]);
        $client = $this->createClient(HandlerStack::create($handler), $restrictedAccess, ['allow_redirects' => true]);

        $response = $client->sendRequest(new Request('GET', 'https://example.org/article'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertCount(1, $handler);
    }

    /**
     * @dataProvider restrictedAccessProvider
     */
    public function testMainClientReturnsErrorResponses(string $restrictedAccess): void
    {
        $this->configBuilder->method('buildForHost')->willReturn(false);
        $client = $this->createClient(HandlerStack::create(new MockHandler([new Response(404, [], 'not found')])), $restrictedAccess, ['http_errors' => true]);

        $response = $client->sendRequest(new Request('GET', 'https://example.org/article'));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('not found', (string) $response->getBody());
    }

    /**
     * @dataProvider restrictedAccessProvider
     */
    public function testRedirectToPrivateNetworkIsBlocked(string $restrictedAccess): void
    {
        $this->configBuilder->method('buildForHost')->willReturn(false);
        $handler = new MockHandler([
            new Response(302, ['Location' => 'http://169.254.169.254/latest/meta-data/']),
            new Response(200, [], 'private network'),
        ]);
        $client = new PluginClient($this->createClient(HandlerStack::create($handler), $restrictedAccess), [
            new ServerSideRequestForgeryProtectionPlugin(),
            new RedirectPlugin(),
        ]);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('blacklisted');
        // A public IP avoids DNS lookups; all HTTP requests use the mock handler.
        $client->sendRequest(new Request('GET', 'https://93.184.216.34/article'));
    }

    public function testLoginCanFollowRedirectsAndKeepCookies(): void
    {
        $this->configBuilder->method('buildForHost')->willReturn($this->siteConfig());
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(302, ['Location' => '/login-complete', 'Set-Cookie' => 'session=valid; Path=/; Secure']),
            new Response(200),
            new Response(200, [], 'private article'),
        ]));
        $handler->push(Middleware::history($history));
        $client = $this->createClient($handler);

        $response = $client->sendRequest(new Request('GET', 'https://example.org/article'));

        $this->assertSame('private article', $response->getBody()->getContents());
        $this->assertCount(3, $history);
        $this->assertSame('POST', $history[0]['request']->getMethod());
        $this->assertSame('GET', $history[1]['request']->getMethod());
        $this->assertSame('https://example.org/login-complete', (string) $history[1]['request']->getUri());
        $this->assertSame('session=valid', $history[1]['request']->getHeaderLine('Cookie'));
        $this->assertSame('session=valid', $history[2]['request']->getHeaderLine('Cookie'));
    }

    public function testResponseCookiesAreOnlyExtractedOnce(): void
    {
        $this->configBuilder->method('buildForHost')->willReturn($this->siteConfig());
        $cookies = CookieJar::fromArray(['session' => 'valid'], 'example.org');
        $headers = [];
        for ($i = 0; $i < 100; ++$i) {
            $headers[] = 'tracking' . $i . '=value; Path=/';
        }
        $client = $this->createClient(HandlerStack::create(new MockHandler([
            new Response(200, ['Set-Cookie' => $headers], 'article'),
        ])), '1', ['cookies' => $cookies]);

        $client->sendRequest(new Request('GET', 'https://example.org/article'));

        // The existing session plus Guzzle's limit of 50 cookies per response.
        $this->assertCount(51, $cookies);
    }

    private function createClient($handler, $restrictedAccess = '1', array $config = [])
    {
        $factory = new HttpClientFactory(new CookieJar(), $restrictedAccess, new NullLogger(), new SiteAuthenticator($this->configBuilder));

        return $factory->createClient($config + ['handler' => $handler, 'timeout' => 10]);
    }

    private function siteConfig(array $extraFields = []): SiteConfig
    {
        return new SiteConfig([
            'host' => 'example.org',
            'requiresLogin' => true,
            'loginUri' => 'https://example.org/login',
            'usernameField' => 'username',
            'passwordField' => 'password',
            'username' => 'reader',
            'password' => 'secret',
            'extraFields' => $extraFields,
            'notLoggedInXpath' => '//div[@class="login-required"]',
        ]);
    }
}
