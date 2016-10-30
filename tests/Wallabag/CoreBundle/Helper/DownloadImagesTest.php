<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Wallabag\CoreBundle\Helper\DownloadImages;
use Psr\Log\NullLogger;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class DownloadImagesTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessHtml()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['content-type' => 'image/png'], Stream::factory(file_get_contents(__DIR__.'/../fixtures/unnamed.png'))),
        ]);

        $client->getEmitter()->attach($mock);

        $logHandler = new TestHandler();
        $logger = new Logger('test', array($logHandler));

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', $logger);
        $res = $download->processHtml('<div><img src="http://i.imgur.com/T9qgcHc.jpg" /></div>', 'http://imgur.com/gallery/WxtWY');

        $this->assertContains('/assets/images/4/2/4258f71e/c638b4c2.png', $res);
    }

    public function testProcessHtmlWithBadImage()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['content-type' => 'application/json'], Stream::factory('')),
        ]);

        $client->getEmitter()->attach($mock);

        $logHandler = new TestHandler();
        $logger = new Logger('test', array($logHandler));

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', $logger);
        $res = $download->processHtml('<div><img src="http://i.imgur.com/T9qgcHc.jpg" /></div>', 'http://imgur.com/gallery/WxtWY');

        $this->assertContains('http://i.imgur.com/T9qgcHc.jpg', $res, 'Image were not replace because of content-type');
    }

    public function singleImage()
    {
        return [
            ['image/pjpeg', 'jpeg'],
            ['image/jpeg', 'jpeg'],
            ['image/png', 'png'],
            ['image/gif', 'gif'],
        ];
    }

    /**
     * @dataProvider singleImage
     */
    public function testProcessSingleImage($header, $extension)
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['content-type' => $header], Stream::factory(file_get_contents(__DIR__.'/../fixtures/unnamed.png'))),
        ]);

        $client->getEmitter()->attach($mock);

        $logHandler = new TestHandler();
        $logger = new Logger('test', array($logHandler));

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', $logger);
        $res = $download->processSingleImage('T9qgcHc.jpg', 'http://imgur.com/gallery/WxtWY');

        $this->assertContains('/assets/images/4/2/4258f71e/ebe60399.'.$extension, $res);
    }

    public function testProcessSingleImageWithBadImage()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['content-type' => 'image/png'], Stream::factory('')),
        ]);

        $client->getEmitter()->attach($mock);

        $logHandler = new TestHandler();
        $logger = new Logger('test', array($logHandler));

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', $logger);
        $res = $download->processSingleImage('http://i.imgur.com/T9qgcHc.jpg', 'http://imgur.com/gallery/WxtWY');

        $this->assertFalse($res, 'Image can not be loaded, so it will not be replaced');
    }

    public function testProcessSingleImageFailAbsolute()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['content-type' => 'image/png'], Stream::factory(file_get_contents(__DIR__.'/../fixtures/unnamed.png'))),
        ]);

        $client->getEmitter()->attach($mock);

        $logHandler = new TestHandler();
        $logger = new Logger('test', array($logHandler));

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', $logger);
        $res = $download->processSingleImage('/i.imgur.com/T9qgcHc.jpg', 'imgur.com/gallery/WxtWY');

        $this->assertFalse($res, 'Absolute image can not be determined, so it will not be replaced');
    }
}
