<?php

namespace Wallabag\Tests\Integration\HttpClient;

use Craue\ConfigBundle\Util\Config;
use Graby\Graby;
use Wallabag\HttpClient\HostnameDenyList;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class HostnameDenyListFailureTest extends WallabagKernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::getContainer()->set(HostnameDenyList::class, new HostnameDenyList(['.blocked.test']));
        $config = $this->createMock(Config::class);
        $config->method('get')
            ->with('restricted_access')
            ->willReturn(0)
        ;
        static::getContainer()->set('craue_config_default', $config);
    }

    public function testGrabyKeepsTheConfiguredFetchingError(): void
    {
        $content = static::getContainer()->get(Graby::class)->fetchContent('https://blocked.test/article');

        $this->assertSame(static::getContainer()->getParameter('wallabag.fetching_error_message_title'), $content['title']);
        $this->assertSame(static::getContainer()->getParameter('wallabag.fetching_error_message'), $content['html']);
    }
}
