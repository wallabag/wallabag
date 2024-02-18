<?php

namespace Tests\Wallabag\CoreBundle\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Wallabag\CoreBundle\Guzzle\AuthenticatorSubscriber;
use Wallabag\CoreBundle\SiteConfig\ArraySiteConfigBuilder;
use Wallabag\CoreBundle\SiteConfig\Authenticator\Authenticator;
use Wallabag\CoreBundle\SiteConfig\Authenticator\Factory;

class AuthenticatorSubscriberTest extends TestCase
{
    public function testGetEvents()
    {
        $subscriber = new AuthenticatorSubscriber(
            new ArraySiteConfigBuilder(),
            new Factory()
        );
        $events = $subscriber->getEvents();

        $this->assertArrayHasKey('before', $events);
        $this->assertArrayHasKey('complete', $events);
        $this->assertSame('loginIfRequired', $events['before'][0]);
        $this->assertSame('loginIfRequested', $events['complete'][0]);
    }

    public function testLoginIfRequiredNotRequired()
    {
        $builder = new ArraySiteConfigBuilder(['example.com' => []]);
        $subscriber = new AuthenticatorSubscriber($builder, new Factory());

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $request = new Request('GET', 'http://www.example.com');

        $event = $this->getMockBuilder(BeforeEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $subscriber->loginIfRequired($event);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequired> will not require login', $records[0]['message']);
    }

    public function testLoginIfRequiredWithNotLoggedInUser()
    {
        $authenticator = $this->getMockBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authenticator->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $authenticator->expects($this->once())
            ->method('login');

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory->expects($this->once())
            ->method('buildFromSiteConfig')
            ->willReturn($authenticator);

        $builder = new ArraySiteConfigBuilder(['example.com' => ['requiresLogin' => true]]);
        $subscriber = new AuthenticatorSubscriber($builder, $factory);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $response = new Response(
            200,
            ['content-type' => 'text/html'],
            Stream::factory('')
        );
        $guzzle = new Client();
        $guzzle->getEmitter()->attach(new Mock([$response]));

        $request = new Request('GET', 'http://www.example.com');

        $event = $this->getMockBuilder(BeforeEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $event->expects($this->once())
            ->method('getClient')
            ->willReturn($guzzle);

        $subscriber->loginIfRequired($event);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequired> user is not logged in, attach authenticator', $records[0]['message']);
    }

    public function testLoginIfRequestedNotRequired()
    {
        $builder = new ArraySiteConfigBuilder(['example.com' => []]);
        $subscriber = new AuthenticatorSubscriber($builder, new Factory());

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $request = new Request('GET', 'http://www.example.com');

        $event = $this->getMockBuilder(CompleteEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $subscriber->loginIfRequested($event);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequested> will not require login', $records[0]['message']);
    }

    public function testLoginIfRequestedNotRequested()
    {
        $authenticator = $this->getMockBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authenticator->expects($this->once())
            ->method('isLoginRequired')
            ->willReturn(false);

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory->expects($this->once())
            ->method('buildFromSiteConfig')
            ->willReturn($authenticator);

        $builder = new ArraySiteConfigBuilder(['example.com' => [
            'requiresLogin' => true,
            'notLoggedInXpath' => '//html',
        ]]);
        $subscriber = new AuthenticatorSubscriber($builder, $factory);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $response = new Response(
            200,
            ['content-type' => 'text/html'],
            Stream::factory('<html><body/></html>')
        );
        $request = new Request('GET', 'http://www.example.com');

        $event = $this->getMockBuilder(CompleteEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $subscriber->loginIfRequested($event);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequested> retry #0 with login not required', $records[0]['message']);
    }

    public function testLoginIfRequestedRequested()
    {
        $authenticator = $this->getMockBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authenticator->expects($this->once())
            ->method('isLoginRequired')
            ->willReturn(true);

        $authenticator->expects($this->once())
            ->method('login');

        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory->expects($this->once())
            ->method('buildFromSiteConfig')
            ->willReturn($authenticator);

        $builder = new ArraySiteConfigBuilder(['example.com' => [
            'requiresLogin' => true,
            'notLoggedInXpath' => '//html',
        ]]);
        $subscriber = new AuthenticatorSubscriber($builder, $factory);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $response = new Response(
            200,
            ['content-type' => 'text/html'],
            Stream::factory('<html><body/></html>')
        );
        $guzzle = new Client();
        $guzzle->getEmitter()->attach(new Mock([$response]));
        $request = new Request('GET', 'http://www.example.com');

        $event = $this->getMockBuilder(CompleteEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $event->expects($this->any())
            ->method('getClient')
            ->willReturn($guzzle);

        $subscriber->loginIfRequested($event);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequested> retry #0 with login required', $records[0]['message']);
    }

    public function testLoginIfRequestedRedirect()
    {
        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $builder = new ArraySiteConfigBuilder(['example.com' => [
            'requiresLogin' => true,
            'notLoggedInXpath' => '//html',
        ]]);
        $subscriber = new AuthenticatorSubscriber($builder, $factory);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $subscriber->setLogger($logger);

        $response = new Response(
            301,
            [],
            Stream::factory('')
        );
        $guzzle = new Client();
        $guzzle->getEmitter()->attach(new Mock([$response]));
        $request = new Request('GET', 'http://www.example.com');

        $event = $this->getMockBuilder(CompleteEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $event->expects($this->any())
            ->method('getClient')
            ->willReturn($guzzle);

        $subscriber->loginIfRequested($event);

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertSame('loginIfRequested> empty body, ignoring', $records[0]['message']);
    }
}
