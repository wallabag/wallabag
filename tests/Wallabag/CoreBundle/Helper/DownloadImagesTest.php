<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Wallabag\CoreBundle\Helper\DownloadImages;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class DownloadImagesTest extends \PHPUnit_Framework_TestCase
{
    public function dataForSuccessImage()
    {
        return [
            'imgur' => [
                '<div><img src="http://i.imgur.com/T9qgcHc.jpg" /></div>',
                'http://imgur.com/gallery/WxtWY',
            ],
            'image with &' => [
                '<div><img src="https://i2.wp.com/www.tvaddons.ag/wp-content/uploads/2017/01/Screen-Shot-2017-01-07-at-10.17.40-PM.jpg?w=640&amp;ssl=1" /></div>',
                'https://www.tvaddons.ag/realdebrid-kodi-jarvis/',
            ],
        ];
    }

    /**
     * @dataProvider dataForSuccessImage
     */
    public function testProcessHtml($html, $url)
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['content-type' => 'image/png'], Stream::factory(file_get_contents(__DIR__.'/../fixtures/unnamed.png'))),
        ]);

        $client->getEmitter()->attach($mock);

        $logHandler = new TestHandler();
        $logger = new Logger('test', array($logHandler));

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', 'http://wallabag.io/', $logger);

        $res = $download->processHtml(123, $html, $url);

        // this the base path of all image (since it's calculated using the entry id: 123)
        $this->assertContains('http://wallabag.io/assets/images/9/b/9b0ead26/', $res);
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

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processHtml(123, '<div><img src="http://i.imgur.com/T9qgcHc.jpg" /></div>', 'http://imgur.com/gallery/WxtWY');

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

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'T9qgcHc.jpg', 'http://imgur.com/gallery/WxtWY');

        $this->assertContains('/assets/images/9/b/9b0ead26/ebe60399.'.$extension, $res);
    }

    public function testProcessSingleImageWithBadUrl()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(404, []),
        ]);

        $client->getEmitter()->attach($mock);

        $logHandler = new TestHandler();
        $logger = new Logger('test', array($logHandler));

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'T9qgcHc.jpg', 'http://imgur.com/gallery/WxtWY');

        $this->assertFalse($res, 'Image can not be found, so it will not be replaced');
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

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'http://i.imgur.com/T9qgcHc.jpg', 'http://imgur.com/gallery/WxtWY');

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

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, '/i.imgur.com/T9qgcHc.jpg', 'imgur.com/gallery/WxtWY');

        $this->assertFalse($res, 'Absolute image can not be determined, so it will not be replaced');
    }

    public function testProcessRealImage()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['content-type' => null], Stream::factory(file_get_contents(__DIR__.'/../fixtures/image-no-content-type.jpg'))),
        ]);

        $client->getEmitter()->attach($mock);

        $logHandler = new TestHandler();
        $logger = new Logger('test', array($logHandler));

        $download = new DownloadImages($client, sys_get_temp_dir().'/wallabag_test', 'http://wallabag.io/', $logger);

        $res = $download->processSingleImage(
            123,
            'https://cdn.theconversation.com/files/157200/article/width926/gsj2rjp2-1487348607.jpg',
            'https://theconversation.com/conversation-avec-gerald-bronner-ce-nest-pas-la-post-verite-qui-nous-menace-mais-lextension-de-notre-credulite-73089'
        );

        $this->assertContains('http://wallabag.io/assets/images/9/b/9b0ead26/', $res, 'Content-Type was empty but data is ok for an image');
        $this->assertContains('DownloadImages: Checking extension (alternative)', $logHandler->getRecords()[3]['message']);
    }
}
