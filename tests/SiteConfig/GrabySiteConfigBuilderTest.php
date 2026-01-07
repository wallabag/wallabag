<?php

namespace Tests\Wallabag\SiteConfig;

use Graby\SiteConfig\ConfigBuilder;
use Graby\SiteConfig\SiteConfig as GrabySiteConfig;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\User;
use Wallabag\Repository\SiteCredentialRepository;
use Wallabag\SiteConfig\GrabySiteConfigBuilder;

class GrabySiteConfigBuilderTest extends WallabagTestCase
{
    private $builder;

    public function testBuildConfigExists()
    {
        $grabyConfigBuilderMock = $this->getMockBuilder(ConfigBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $grabySiteConfig = new GrabySiteConfig();
        $grabySiteConfig->requires_login = true;
        $grabySiteConfig->login_uri = 'http://api.example.com/login';
        $grabySiteConfig->login_username_field = 'login';
        $grabySiteConfig->login_password_field = 'password';
        $grabySiteConfig->login_extra_fields = ['field=value'];
        $grabySiteConfig->not_logged_in_xpath = '//div[@class="need-login"]';

        $grabyConfigBuilderMock
            ->method('buildForHost')
            ->with('api.example.com')
            ->willReturn($grabySiteConfig);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $siteCrentialRepo = $this->getMockBuilder(SiteCredentialRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $siteCrentialRepo->expects($this->once())
            ->method('findOneByHostsAndUser')
            ->with(['api.example.com', '.example.com'], 1)
            ->willReturn(['username' => 'foo', 'password' => 'bar']);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $token = new UsernamePasswordToken($user, 'provider');

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        $builder = new GrabySiteConfigBuilder(
            $grabyConfigBuilderMock,
            $tokenStorage,
            $siteCrentialRepo,
            $logger
        );

        $config = $builder->buildForHost('api.example.com');

        $this->assertSame('api.example.com', $config->getHost());
        $this->assertTrue($config->requiresLogin());
        $this->assertSame('http://api.example.com/login', $config->getLoginUri());
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
        $grabyConfigBuilderMock = $this->getMockBuilder(ConfigBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $grabyConfigBuilderMock
            ->method('buildForHost')
            ->with('unknown.com')
            ->willReturn(new GrabySiteConfig());

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $siteCrentialRepo = $this->getMockBuilder(SiteCredentialRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $siteCrentialRepo->expects($this->once())
            ->method('findOneByHostsAndUser')
            ->with(['unknown.com', '.com'], 1)
            ->willReturn(null);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $token = new UsernamePasswordToken($user, 'provider');

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        $builder = new GrabySiteConfigBuilder(
            $grabyConfigBuilderMock,
            $tokenStorage,
            $siteCrentialRepo,
            $logger
        );

        $config = $builder->buildForHost('unknown.com');

        $this->assertFalse($config);

        $records = $handler->getRecords();

        $this->assertCount(1, $records, 'One log was recorded');
    }

    public function testBuildConfigWithBadExtraFields()
    {
        $grabyConfigBuilderMock = $this->getMockBuilder(ConfigBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $grabySiteConfig = new GrabySiteConfig();
        $grabySiteConfig->requires_login = true;
        $grabySiteConfig->login_uri = 'http://www.example.com/login';
        $grabySiteConfig->login_username_field = 'login';
        $grabySiteConfig->login_password_field = 'password';
        $grabySiteConfig->login_extra_fields = ['field'];
        $grabySiteConfig->not_logged_in_xpath = '//div[@class="need-login"]';

        $grabyConfigBuilderMock
            ->method('buildForHost')
            ->with('example.com')
            ->willReturn($grabySiteConfig);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $siteCrentialRepo = $this->getMockBuilder(SiteCredentialRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $siteCrentialRepo->expects($this->once())
            ->method('findOneByHostsAndUser')
            ->with(['example.com', '.com'], 1)
            ->willReturn(['username' => 'foo', 'password' => 'bar']);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $token = new UsernamePasswordToken($user, 'provider');

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
        $this->assertSame([], $config->getExtraFields());
        $this->assertSame('//div[@class="need-login"]', $config->getNotLoggedInXpath());
        $this->assertSame('foo', $config->getUsername());
        $this->assertSame('bar', $config->getPassword());

        $records = $handler->getRecords();

        $this->assertCount(1, $records, 'One log was recorded');
    }

    public function testBuildConfigUserNotDefined()
    {
        $grabyConfigBuilderMock = $this->getMockBuilder(ConfigBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $grabyConfigBuilderMock
            ->method('buildForHost')
            ->with('unknown.com')
            ->willReturn(new GrabySiteConfig());

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $siteCrentialRepo = $this->getMockBuilder(SiteCredentialRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage = new TokenStorage();

        $builder = new GrabySiteConfigBuilder(
            $grabyConfigBuilderMock,
            $tokenStorage,
            $siteCrentialRepo,
            $logger
        );

        $config = $builder->buildForHost('unknown.com');

        $this->assertFalse($config);
    }

    public function dataProviderCredentials()
    {
        return [
            [
                'host' => 'example.com',
            ],
            [
                'host' => 'other.example.com',
            ],
            [
                'host' => 'paywall.example.com',
                'expectedUsername' => 'paywall.example',
                'expectedPassword' => 'bar',
            ],
            [
                'host' => 'api.super.com',
                'expectedUsername' => '.super',
                'expectedPassword' => 'bar',
            ],
            [
                'host' => '.super.com',
                'expectedUsername' => '.super',
                'expectedPassword' => 'bar',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCredentials
     */
    public function testBuildConfigWithDbAccess($host, $expectedUsername = null, $expectedPassword = null)
    {
        $grabyConfigBuilderMock = $this->getMockBuilder(ConfigBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $grabySiteConfig = new GrabySiteConfig();
        $grabySiteConfig->requires_login = true;
        $grabySiteConfig->login_uri = 'http://api.example.com/login';
        $grabySiteConfig->login_username_field = 'login';
        $grabySiteConfig->login_password_field = 'password';
        $grabySiteConfig->login_extra_fields = ['field=value'];
        $grabySiteConfig->not_logged_in_xpath = '//div[@class="need-login"]';

        $grabyConfigBuilderMock
            ->method('buildForHost')
            ->with($host)
            ->willReturn($grabySiteConfig);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $token = new UsernamePasswordToken($user, 'provider');

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $builder = new GrabySiteConfigBuilder(
            $grabyConfigBuilderMock,
            $tokenStorage,
            $this->getTestClient()->getContainer()->get(SiteCredentialRepository::class),
            $logger
        );

        $config = $builder->buildForHost($host);

        if (null === $expectedUsername && null === $expectedPassword) {
            $this->assertFalse($config);

            return;
        }

        $this->assertSame($expectedUsername, $config->getUsername());
        $this->assertSame($expectedPassword, $config->getPassword());
    }
}
