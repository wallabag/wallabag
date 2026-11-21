<?php

namespace Wallabag\Tests\Integration\Helper;

use Symfony\Component\HttpClient\Exception\TransportException;
use Wallabag\Helper\DownloadImages;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class DownloadImagesTest extends WallabagKernelTestCase
{
    public function testTheServiceBlocksPrivateNetworkImages(): void
    {
        $download = static::getContainer()->get(DownloadImages::class);
        $client = (new \ReflectionProperty($download, 'client'))->getValue($download);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessageMatches('/is blocked/');

        $client->request('GET', 'http://127.0.0.1:9/image.png')->getStatusCode();
    }
}
