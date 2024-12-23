<?php

namespace Tests\Wallabag\SiteConfig;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wallabag\ExpressionLanguage\AuthenticatorProvider;
use Wallabag\SiteConfig\LoginFormAuthenticator;
use Wallabag\SiteConfig\SiteConfig;

class LoginFormAuthenticatorTest extends TestCase
{
    public function testLoginPost()
    {
        $response = new Response(
            200,
            ['content-type' => 'text/html'],
            Stream::factory('')
        );
        $guzzle = new Client();
        $guzzle->getEmitter()->attach(new Mock([$response]));

        $mockHttpClient = new MockHttpClient([new MockResponse('', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']])]);

        $siteConfig = new SiteConfig([
            'host' => 'example.com',
            'loginUri' => 'http://example.com/login',
            'usernameField' => 'username',
            'passwordField' => 'password',
            'extraFields' => [
                'action' => 'login',
                'foo' => 'bar',
            ],
            'username' => 'johndoe',
            'password' => 'unkn0wn',
        ]);

        $authenticatorProvider = new AuthenticatorProvider($mockHttpClient);
        $auth = new LoginFormAuthenticator($authenticatorProvider);
        $res = $auth->login($siteConfig, $guzzle);

        $this->assertInstanceOf(LoginFormAuthenticator::class, $res);
    }

    public function testLoginPostWithExtraFieldsButEmptyHtml()
    {
        $response = new Response(
            200,
            ['content-type' => 'text/html'],
            Stream::factory('<html></html>')
        );
        $guzzle = new Client();
        $guzzle->getEmitter()->attach(new Mock([$response, $response]));

        $mockHttpClient = new MockHttpClient([new MockResponse('<html></html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']])]);

        $siteConfig = new SiteConfig([
            'host' => 'example.com',
            'loginUri' => 'http://example.com/login',
            'usernameField' => 'username',
            'passwordField' => 'password',
            'extraFields' => [
                'action' => 'login',
                'foo' => 'bar',
                'security' => '@=xpath(\'substring(//script[contains(text(), "security")]/text(), 112, 10)\', request_html(\'https://aoc.media/\'))',
            ],
            'username' => 'johndoe',
            'password' => 'unkn0wn',
        ]);

        $authenticatorProvider = new AuthenticatorProvider($mockHttpClient);
        $auth = new LoginFormAuthenticator($authenticatorProvider);
        $res = $auth->login($siteConfig, $guzzle);

        $this->assertInstanceOf(LoginFormAuthenticator::class, $res);
    }

    // testing preg_match
    public function testLoginPostWithExtraFieldsWithRegex()
    {
        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->any())
            ->method('getBody')
            ->willReturn(file_get_contents(__DIR__ . '/../fixtures/aoc.media.html'));

        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200);

        $mockHttpClient = new MockHttpClient([new MockResponse(file_get_contents(__DIR__ . '/../fixtures/aoc.media.html'), ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']])]);

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('post')
            ->with(
                $this->equalTo('https://aoc.media/wp-admin/admin-ajax.php'),
                $this->equalTo([
                    'body' => [
                        'nom' => 'johndoe',
                        'password' => 'unkn0wn',
                        'security' => 'c506c1b8bc',
                        'action' => 'login_user',
                    ],
                    'allow_redirects' => true,
                    'verify' => false,
                ])
            )
            ->willReturn($response);

        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->equalTo('https://aoc.media/'),
                $this->equalTo([])
            )
            ->willReturn($response);

        $siteConfig = new SiteConfig([
            'host' => 'aoc.media',
            'loginUri' => 'https://aoc.media/wp-admin/admin-ajax.php',
            'usernameField' => 'nom',
            'passwordField' => 'password',
            'extraFields' => [
                'action' => 'login_user',
                'security' => '@=preg_match(\'/security\\\":\\\"([a-z0-9]+)\\\"/si\', request_html(\'https://aoc.media/\'))',
            ],
            'username' => 'johndoe',
            'password' => 'unkn0wn',
        ]);

        $authenticatorProvider = new AuthenticatorProvider($mockHttpClient);
        $auth = new LoginFormAuthenticator($authenticatorProvider);
        $res = $auth->login($siteConfig, $client);

        $this->assertInstanceOf(LoginFormAuthenticator::class, $res);
    }

    public function testLoginPostWithExtraFieldsWithData()
    {
        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->any())
            ->method('getBody')
            ->willReturn(file_get_contents(__DIR__ . '/../fixtures/nextinpact-login.html'));

        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200);

        $mockHttpClient = new MockHttpClient([new MockResponse(file_get_contents(__DIR__ . '/../fixtures/nextinpact-login.html'), ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']])]);

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())
            ->method('post')
            ->with(
                $this->equalTo('https://compte.nextinpact.com/Account/Login'),
                $this->equalTo([
                    'body' => [
                        'UserName' => 'johndoe',
                        'Password' => 'unkn0wn',
                        '__RequestVerificationToken' => 's6x2QcnQDUL92mkKSi_JuUBXcgUYx_Plf-KyQ2eJypKAjQZIeTvaFHOsfEdTrcSXt3dt2CW39V7r9V16LUtvjszodAU1',
                        'returnUrl' => 'https://www.nextinpact.com/news/102835-pour-cour-comptes-fonctionnement-actuel-vote-par-internet-nest-pas-satisfaisant.htm',
                    ],
                    'allow_redirects' => true,
                    'verify' => false,
                ])
            )
            ->willReturn($response);

        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->equalTo('https://compte.nextinpact.com/Account/Login?http://www.nextinpact.com/'),
                $this->equalTo([
                    'headers' => [
                        'X-Requested-With' => 'XMLHttpRequest',
                    ],
                ])
            )
            ->willReturn($response);

        $siteConfig = new SiteConfig([
            'host' => 'nextinpact.com',
            'loginUri' => 'https://compte.nextinpact.com/Account/Login',
            'usernameField' => 'UserName',
            'passwordField' => 'Password',
            'extraFields' => [
                '__RequestVerificationToken' => '@=xpath(\'//form[@action="/Account/Login"]/input[@name="__RequestVerificationToken"]\', request_html(\'https://compte.nextinpact.com/Account/Login?http://www.nextinpact.com/\', {\'headers\': {\'X-Requested-With\':\'XMLHttpRequest\'}}))',
                'returnUrl' => 'https://www.nextinpact.com/news/102835-pour-cour-comptes-fonctionnement-actuel-vote-par-internet-nest-pas-satisfaisant.htm',
            ],
            'username' => 'johndoe',
            'password' => 'unkn0wn',
        ]);

        $authenticatorProvider = new AuthenticatorProvider($mockHttpClient);
        $auth = new LoginFormAuthenticator($authenticatorProvider);
        $res = $auth->login($siteConfig, $client);

        $this->assertInstanceOf(LoginFormAuthenticator::class, $res);
    }

    public function testLoginWithBadSiteConfigNotLoggedInData()
    {
        $siteConfig = new SiteConfig([
            'host' => 'nextinpact.com',
            'loginUri' => 'https://compte.nextinpact.com/Account/Login',
            'usernameField' => 'UserName',
            'username' => 'johndoe',
            'password' => 'unkn0wn',
        ]);

        $mockHttpClient = new MockHttpClient();

        $authenticatorProvider = new AuthenticatorProvider($mockHttpClient);
        $auth = new LoginFormAuthenticator($authenticatorProvider);
        $loginRequired = $auth->isLoginRequired($siteConfig, file_get_contents(__DIR__ . '/../fixtures/nextinpact-login.html'));

        $this->assertFalse($loginRequired);
    }

    public function testLoginWithGoodSiteConfigNotLoggedInData()
    {
        $siteConfig = new SiteConfig([
            'host' => 'nextinpact.com',
            'loginUri' => 'https://compte.nextinpact.com/Account/Login',
            'usernameField' => 'UserName',
            'username' => 'johndoe',
            'password' => 'unkn0wn',
            'notLoggedInXpath' => '//h2[@class="title_reserve_article"]',
        ]);

        $mockHttpClient = new MockHttpClient();

        $authenticatorProvider = new AuthenticatorProvider($mockHttpClient);
        $auth = new LoginFormAuthenticator($authenticatorProvider);
        $loginRequired = $auth->isLoginRequired($siteConfig, file_get_contents(__DIR__ . '/../fixtures/nextinpact-article.html'));

        $this->assertTrue($loginRequired);
    }
}
