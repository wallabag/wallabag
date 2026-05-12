<?php

namespace Wallabag\Tests\Integration\SiteConfig;

use Graby\SiteConfig\ConfigBuilder;
use Graby\SiteConfig\SiteConfig as GrabySiteConfig;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\Repository\SiteCredentialRepository;
use Wallabag\SiteConfig\GrabySiteConfigBuilder;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class GrabySiteConfigBuilderTest extends WallabagKernelTestCase
{
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
    public function testBuildConfigWithDbAccess($host, $expectedUsername = null, $expectedPassword = null): void
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

        $token = new UsernamePasswordToken($this->getUser('admin'), 'provider');

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $builder = new GrabySiteConfigBuilder(
            $grabyConfigBuilderMock,
            $tokenStorage,
            static::getContainer()->get(SiteCredentialRepository::class),
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
