<?php

namespace Tests\Wallabag\CoreBundle\GuzzleSiteAuthenticator;

use Graby\SiteConfig\SiteConfig as GrabySiteConfig;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\CoreBundle\GuzzleSiteAuthenticator\GrabySiteConfigBuilder;

class GrabySiteConfigBuilderTest extends TestCase
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
        $grabySiteConfig->login_uri = 'http://www.example.com/login';
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

        $config = $this->builder->buildForHost('www.example.com');

        $this->assertSame('example.com', $config->getHost());
        $this->assertTrue($config->requiresLogin());
        $this->assertSame('http://www.example.com/login', $config->getLoginUri());
        $this->assertSame('login', $config->getUsernameField());
        $this->assertSame('password', $config->getPasswordField());
        $this->assertSame(['field' => 'value'], $config->getExtraFields());
        $this->assertSame('//div[@class="need-login"]', $config->getNotLoggedInXpath());
        $this->assertSame('foo', $config->getUsername());
        $this->assertSame('bar', $config->getPassword());

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
