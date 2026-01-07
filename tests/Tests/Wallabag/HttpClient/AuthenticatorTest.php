<?php

namespace Tests\Wallabag\HttpClient;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Wallabag\HttpClient\Authenticator;
use Wallabag\SiteConfig\ArraySiteConfigBuilder;
use Wallabag\SiteConfig\LoginFormAuthenticator;

class AuthenticatorTest extends TestCase
{
    public function testLoginIfRequiredNotRequired()
    {
        $authenticator = $this->getMockBuilder(LoginFormAuthenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $builder = new ArraySiteConfigBuilder(['example.com' => []]);
        $subscriber = new Authenticator($builder, $authenticator);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $login = $subscriber->loginIfRequired('http://www.example.com');

        $this->assertFalse($login);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequired> will not require login', $records[0]['message']);
    }

    public function testLoginIfRequiredWithNotLoggedInUser()
    {
        $authenticator = $this->getMockBuilder(LoginFormAuthenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authenticator->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $authenticator->expects($this->once())
            ->method('login');

        $builder = new ArraySiteConfigBuilder(['example.com' => ['requiresLogin' => true]]);
        $subscriber = new Authenticator($builder, $authenticator);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $login = $subscriber->loginIfRequired('http://www.example.com');

        $this->assertTrue($login);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequired> user is not logged in, attach authenticator', $records[0]['message']);
    }

    public function testLoginIfRequestedNotRequired()
    {
        $authenticator = $this->getMockBuilder(LoginFormAuthenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $builder = new ArraySiteConfigBuilder(['example.com' => []]);
        $subscriber = new Authenticator($builder, $authenticator);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->once())
            ->method('getInfo')
            ->with($this->equalTo('url'))
            ->willReturn('http://www.example.com');

        $login = $subscriber->loginIfRequested($response);

        $this->assertFalse($login);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequested> will not require login', $records[0]['message']);
    }

    public function testLoginIfRequestedNotRequested()
    {
        $authenticator = $this->getMockBuilder(LoginFormAuthenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authenticator->expects($this->once())
            ->method('isLoginRequired')
            ->willReturn(false);

        $builder = new ArraySiteConfigBuilder(['example.com' => [
            'requiresLogin' => true,
            'notLoggedInXpath' => '//html',
        ]]);
        $subscriber = new Authenticator($builder, $authenticator);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->once())
            ->method('getInfo')
            ->with($this->equalTo('url'))
            ->willReturn('http://www.example.com');

        $response->expects($this->once())
            ->method('getContent')
            ->willReturn('<html><body/></html>');

        $login = $subscriber->loginIfRequested($response);

        $this->assertFalse($login);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequested> retry with login not required', $records[0]['message']);
    }

    public function testLoginIfRequestedRequested()
    {
        $authenticator = $this->getMockBuilder(LoginFormAuthenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authenticator->expects($this->once())
            ->method('isLoginRequired')
            ->willReturn(true);

        $authenticator->expects($this->once())
            ->method('login');

        $builder = new ArraySiteConfigBuilder(['example.com' => [
            'requiresLogin' => true,
            'notLoggedInXpath' => '//html',
        ]]);
        $subscriber = new Authenticator($builder, $authenticator);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->once())
            ->method('getInfo')
            ->with($this->equalTo('url'))
            ->willReturn('http://www.example.com');

        $response->expects($this->once())
            ->method('getContent')
            ->willReturn('<html><body/></html>');

        $login = $subscriber->loginIfRequested($response);

        $this->assertTrue($login);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequested> retry with login required', $records[0]['message']);
    }

    public function testLoginIfRequestedRedirect()
    {
        $authenticator = $this->getMockBuilder(LoginFormAuthenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $builder = new ArraySiteConfigBuilder(['example.com' => [
            'requiresLogin' => true,
            'notLoggedInXpath' => '//html',
        ]]);
        $subscriber = new Authenticator($builder, $authenticator);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->once())
            ->method('getInfo')
            ->with($this->equalTo('url'))
            ->willReturn('http://www.example.com');

        $response->expects($this->once())
            ->method('getContent')
            ->willReturn('');

        $login = $subscriber->loginIfRequested($response);

        $this->assertFalse($login);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequested> empty body, ignoring', $records[0]['message']);
    }
}
