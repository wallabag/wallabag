<?php

namespace Wallabag\Tests\Unit\SiteConfig;

use PHPUnit\Framework\TestCase;
use Wallabag\SiteConfig\SiteConfig;

class SiteConfigTest extends TestCase
{
    public function testInitSiteConfig(): void
    {
        $config = new SiteConfig([]);

        $this->assertInstanceOf(SiteConfig::class, $config);
    }

    public function testUnknownProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown property: "bad"');

        new SiteConfig(['bad' => true]);
    }

    public function testInitSiteConfigWillFullOptions(): void
    {
        $config = new SiteConfig([
            'host' => 'example.com',
            'requiresLogin' => true,
            'notLoggedInXpath' => '//all',
            'loginUri' => 'https://example.com/login',
            'usernameField' => 'username',
            'passwordField' => 'password',
            'extraFields' => [
                'action' => 'login',
                'foo' => 'bar',
            ],
            'username' => 'johndoe',
            'password' => 'unkn0wn',
            'httpHeaders' => [
                'user-agent' => 'Wallabag (Guzzle/5)',
            ],
        ]);

        $this->assertInstanceOf(SiteConfig::class, $config);
    }
}
