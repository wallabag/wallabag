<?php

namespace Tests\Wallabag\SiteConfig;

use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Wallabag\ExpressionLanguage\AuthenticatorProvider;
use Wallabag\SiteConfig\LoginFormAuthenticator;
use Wallabag\SiteConfig\SiteConfig;

class LoginFormAuthenticatorTest extends TestCase
{
    public function testLoginPost()
    {
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

        $browserResponse = new MockResponse('<html></html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']]);
        $browserClient = new MockHttpClient([$browserResponse]);
        $browser = new HttpBrowser($browserClient);

        $requestHtmlFunctionResponse = new MockResponse('<html></html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']]);
        $requestHtmlFunctionClient = new MockHttpClient([$requestHtmlFunctionResponse]);
        $authenticatorProvider = new AuthenticatorProvider($requestHtmlFunctionClient);

        $auth = new LoginFormAuthenticator($browser, $authenticatorProvider);

        $res = $auth->login($siteConfig);

        $this->assertInstanceOf(LoginFormAuthenticator::class, $res);
    }

    public function testLoginPostWithExtraFieldsButEmptyHtml()
    {
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

        $browserResponse = new MockResponse('<html></html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']]);
        $browserClient = new MockHttpClient([$browserResponse]);
        $browser = new HttpBrowser($browserClient);

        $requestHtmlFunctionResponse = new MockResponse('<html></html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']]);
        $requestHtmlFunctionClient = new MockHttpClient([$requestHtmlFunctionResponse]);
        $authenticatorProvider = new AuthenticatorProvider($requestHtmlFunctionClient);

        $auth = new LoginFormAuthenticator($browser, $authenticatorProvider);

        $res = $auth->login($siteConfig);

        $this->assertInstanceOf(LoginFormAuthenticator::class, $res);
    }

    // testing preg_match
    public function testLoginPostWithExtraFieldsWithRegex()
    {
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

        $browserResponse = new MockResponse('<html></html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']]);
        $browserClient = new MockHttpClient([$browserResponse]);
        $browser = $this->getMockBuilder(HttpBrowser::class)
            ->setConstructorArgs([$browserClient])
            ->getMock();
        $browser->expects($this->any())
            ->method('request')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo('https://aoc.media/wp-admin/admin-ajax.php'),
                $this->equalTo([
                    'nom' => 'johndoe',
                    'password' => 'unkn0wn',
                    'security' => 'c506c1b8bc',
                    'action' => 'login_user',
                ])
            )
        ;

        $requestHtmlFunctionResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $requestHtmlFunctionResponse->expects($this->any())
            ->method('getContent')
            ->willReturn(file_get_contents(__DIR__ . '/../fixtures/aoc.media.html'))
        ;
        $requestHtmlFunctionClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $requestHtmlFunctionClient->expects($this->any())
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('https://aoc.media/'),
            )
            ->willReturn($requestHtmlFunctionResponse)
        ;
        $authenticatorProvider = new AuthenticatorProvider($requestHtmlFunctionClient);

        $auth = new LoginFormAuthenticator($browser, $authenticatorProvider);

        $res = $auth->login($siteConfig);

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

        $browserResponse = new MockResponse('<html></html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']]);
        $browserClient = new MockHttpClient([$browserResponse]);
        $browser = new HttpBrowser($browserClient);

        $requestHtmlFunctionResponse = new MockResponse('<html></html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']]);
        $requestHtmlFunctionClient = new MockHttpClient([$requestHtmlFunctionResponse]);
        $authenticatorProvider = new AuthenticatorProvider($requestHtmlFunctionClient);

        $auth = new LoginFormAuthenticator($browser, $authenticatorProvider);

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

        $browserResponse = new MockResponse('<html></html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']]);
        $browserClient = new MockHttpClient([$browserResponse]);
        $browser = new HttpBrowser($browserClient);

        $requestHtmlFunctionResponse = new MockResponse('<html></html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']]);
        $requestHtmlFunctionClient = new MockHttpClient([$requestHtmlFunctionResponse]);
        $authenticatorProvider = new AuthenticatorProvider($requestHtmlFunctionClient);

        $auth = new LoginFormAuthenticator($browser, $authenticatorProvider);

        $loginRequired = $auth->isLoginRequired($siteConfig, file_get_contents(__DIR__ . '/../fixtures/nextinpact-article.html'));

        $this->assertTrue($loginRequired);
    }

    public function testLoginPostWithUserAgentHeaderWithData()
    {
        $siteConfig = new SiteConfig([
            'host' => 'nextinpact.com',
            'loginUri' => 'https://compte.nextinpact.com/Account/Login',
            'usernameField' => 'UserName',
            'passwordField' => 'Password',
            'username' => 'johndoe',
            'password' => 'unkn0wn',
            'httpHeaders' => [
                'user-agent' => 'Wallabag (Guzzle/5)',
            ],
        ]);

        $browserResponse = new MockResponse('<html></html>', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html']]);
        $browserClient = new MockHttpClient([$browserResponse]);
        $browser = $this->getMockBuilder(HttpBrowser::class)
            ->setConstructorArgs([$browserClient])
            ->getMock();
        $browser->expects($this->any())
            ->method('request')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo('https://compte.nextinpact.com/Account/Login'),
                $this->equalTo([
                    'UserName' => 'johndoe',
                    'Password' => 'unkn0wn',
                ]),
                $this->equalTo([]),
                $this->equalTo([
                    'HTTP_user-agent' => 'Wallabag (Guzzle/5)',
                ]),
            )
        ;

        $requestHtmlFunctionResponse = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $requestHtmlFunctionResponse->expects($this->any())
            ->method('getContent')
            ->willReturn(file_get_contents(__DIR__ . '/../fixtures/nextinpact-login.html'))
        ;
        $requestHtmlFunctionClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $requestHtmlFunctionClient->expects($this->any())
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('https://nextinpact.com/'),
            )
            ->willReturn($requestHtmlFunctionResponse)
        ;
        $authenticatorProvider = new AuthenticatorProvider($requestHtmlFunctionClient);

        $auth = new LoginFormAuthenticator($browser, $authenticatorProvider);

        $res = $auth->login($siteConfig);

        $this->assertInstanceOf(LoginFormAuthenticator::class, $res);
    }
}
