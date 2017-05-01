<?php

namespace Tests\Wallabag\CoreBundle\GuzzleSiteAuthenticator;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use Graby\SiteConfig\SiteConfig as GrabySiteConfig;
use PHPUnit_Framework_TestCase;
use Wallabag\CoreBundle\GuzzleSiteAuthenticator\GrabySiteConfigBuilder;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class GrabySiteConfigBuilderTest extends PHPUnit_Framework_TestCase
{
    /** @var \Wallabag\CoreBundle\GuzzleSiteAuthenticator\GrabySiteConfigBuilder */
    protected $builder;

    public function testBuildConfigExists()
    {
        /* @var \Graby\SiteConfig\ConfigBuilder|\PHPUnit_Framework_MockObject_MockObject */
        $grabyConfigBuilderMock = $this->getMockBuilder('Graby\SiteConfig\ConfigBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $grabySiteConfig = new GrabySiteConfig();
        $grabySiteConfig->requires_login = true;
        $grabySiteConfig->login_uri = 'http://example.com/login';
        $grabySiteConfig->login_username_field = 'login';
        $grabySiteConfig->login_password_field = 'password';
        $grabySiteConfig->login_extra_fields = ['field=value'];
        $grabySiteConfig->not_logged_in_xpath = '//div[@class="need-login"]';

        $grabyConfigBuilderMock
            ->method('buildForHost')
            ->with('example.com')
            ->will($this->returnValue($grabySiteConfig));

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $siteCrentialRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\SiteCredentialRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $siteCrentialRepo->expects($this->once())
            ->method('findOneByHostAndUser')
            ->with('example.com', 1)
            ->willReturn(['username' => 'foo', 'password' => 'bar']);

        $user = $this->getMockBuilder('Wallabag\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $token = new UsernamePasswordToken($user, 'pass', 'provider');

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        $this->builder = new GrabySiteConfigBuilder(
            $grabyConfigBuilderMock,
            $tokenStorage,
            $siteCrentialRepo,
            $logger
        );

        $config = $this->builder->buildForHost('example.com');

        $this->assertEquals(
            new SiteConfig([
                'host' => 'example.com',
                'requiresLogin' => true,
                'loginUri' => 'http://example.com/login',
                'usernameField' => 'login',
                'passwordField' => 'password',
                'extraFields' => ['field' => 'value'],
                'notLoggedInXpath' => '//div[@class="need-login"]',
                'username' => 'foo',
                'password' => 'bar',
            ]),
            $config
        );

        $records = $handler->getRecords();

        $this->assertCount(1, $records, 'One log was recorded');
    }

    public function testBuildConfigDoesntExist()
    {
        /* @var \Graby\SiteConfig\ConfigBuilder|\PHPUnit_Framework_MockObject_MockObject */
        $grabyConfigBuilderMock = $this->getMockBuilder('\Graby\SiteConfig\ConfigBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $grabyConfigBuilderMock
            ->method('buildForHost')
            ->with('unknown.com')
            ->will($this->returnValue(new GrabySiteConfig()));

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $siteCrentialRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\SiteCredentialRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $siteCrentialRepo->expects($this->once())
            ->method('findOneByHostAndUser')
            ->with('unknown.com', 1)
            ->willReturn(null);

        $user = $this->getMockBuilder('Wallabag\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $token = new UsernamePasswordToken($user, 'pass', 'provider');

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        $this->builder = new GrabySiteConfigBuilder(
            $grabyConfigBuilderMock,
            $tokenStorage,
            $siteCrentialRepo,
            $logger
        );

        $config = $this->builder->buildForHost('unknown.com');

        $this->assertFalse($config);

        $records = $handler->getRecords();

        $this->assertCount(1, $records, 'One log was recorded');
    }
}
