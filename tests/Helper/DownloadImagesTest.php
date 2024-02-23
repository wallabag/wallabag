<?php

namespace Tests\Wallabag\Helper;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wallabag\Helper\DownloadImages;

class DownloadImagesTest extends TestCase
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
        $mockHttpClient = new MockHttpClient([new MockResponse(file_get_contents(__DIR__ . '/../fixtures/unnamed.png'), ['response_headers' => ['Content-Type: image/png']])]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);

        $res = $download->processHtml(123, $html, $url);

        // this the base path of all image (since it's calculated using the entry id: 123)
        $this->assertStringContainsString('http://wallabag.io/assets/images/9/b/9b0ead26/', $res);
    }

    public function testProcessHtmlWithBadImage()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse('', ['response_headers' => ['Content-Type: application/json']])]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processHtml(123, '<div><img src="http://i.imgur.com/T9qgcHc.jpg" /></div>', 'http://imgur.com/gallery/WxtWY');

        $this->assertStringContainsString('http://i.imgur.com/T9qgcHc.jpg', $res, 'Image were not replace because of content-type');
    }

    public function singleImage()
    {
        return [
            ['image/pjpeg', 'jpg'],
            ['image/jpeg', 'jpg'],
            ['image/png', 'png'],
            ['image/gif', 'gif'],
            ['image/webp', 'webp'],
        ];
    }

    /**
     * @dataProvider singleImage
     */
    public function testProcessSingleImage($header, $extension)
    {
        $mockHttpClient = new MockHttpClient([new MockResponse(file_get_contents(__DIR__ . '/../fixtures/unnamed.png'), ['response_headers' => ['Content-Type: ' . $header]])]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'T9qgcHc.jpg', 'http://imgur.com/gallery/WxtWY');

        $this->assertStringContainsString('/assets/images/9/b/9b0ead26/ebe60399.' . $extension, $res);
    }

    public function testProcessSingleImageWithBadUrl()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse('', ['http_code' => 404])]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'T9qgcHc.jpg', 'http://imgur.com/gallery/WxtWY');

        $this->assertFalse($res, 'Image can not be found, so it will not be replaced');
    }

    public function testProcessSingleImageWithBadImage()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse('', ['response_headers' => ['Content-Type: image/png']])]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'http://i.imgur.com/T9qgcHc.jpg', 'http://imgur.com/gallery/WxtWY');

        $this->assertFalse($res, 'Image can not be loaded, so it will not be replaced');
    }

    public function testProcessSingleImageFailAbsolute()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse(file_get_contents(__DIR__ . '/../fixtures/unnamed.png'), ['response_headers' => ['Content-Type: image/png']])]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, '/i.imgur.com/T9qgcHc.jpg', 'imgur.com/gallery/WxtWY');

        $this->assertFalse($res, 'Absolute image can not be determined, so it will not be replaced');
    }

    public function testProcessRealImage()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse(file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg'))]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);

        $res = $download->processSingleImage(
            123,
            'https://cdn.theconversation.com/files/157200/article/width926/gsj2rjp2-1487348607.jpg',
            'https://theconversation.com/conversation-avec-gerald-bronner-ce-nest-pas-la-post-verite-qui-nous-menace-mais-lextension-de-notre-credulite-73089'
        );

        $this->assertStringContainsString('http://wallabag.io/assets/images/9/b/9b0ead26/', $res, 'Content-Type was empty but data is ok for an image');
        $this->assertStringContainsString('DownloadImages: Checking extension (alternative)', $logHandler->getRecords()[3]['message']);
    }

    public function testProcessImageWithSrcset()
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')),
            new MockResponse(file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')),
            new MockResponse(file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')),
        ]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processHtml(123, '<p><img class="alignnone wp-image-1153" src="http://piketty.blog.lemonde.fr/files/2017/10/F1FR-530x375.jpg" alt="" width="628" height="444" srcset="http://piketty.blog.lemonde.fr/files/2017/10/F1FR-530x375.jpg 530w, http://piketty.blog.lemonde.fr/files/2017/10/F1FR-768x543.jpg 768w, http://piketty.blog.lemonde.fr/files/2017/10/F1FR-900x636.jpg 900w" sizes="(max-width: 628px) 100vw, 628px" /></p>', 'http://piketty.blog.lemonde.fr/2017/10/12/budget-2018-la-jeunesse-sacrifiee/');

        $this->assertStringNotContainsString('http://piketty.blog.lemonde.fr/', $res, 'Image srcset attribute were not replaced');
    }

    public function testProcessImageWithTrickySrcset()
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')),
            new MockResponse(file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')),
            new MockResponse(file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')),
        ]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processHtml(123, '<figure id="post-257260" class="align-none media-257260"><img src="https://cdn.css-tricks.com/wp-content/uploads/2017/08/the-critical-request.png" srcset="https://res.cloudinary.com/css-tricks/image/upload/c_scale,w_1000,f_auto,q_auto/v1501594717/the-critical-request_bqdfaa.png 1000w, https://res.cloudinary.com/css-tricks/image/upload/c_scale,w_200,f_auto,q_auto/v1501594717/the-critical-request_bqdfaa.png 200w" sizes="(min-width: 1850px) calc( (100vw - 555px) / 3 )
       (min-width: 1251px) calc( (100vw - 530px) / 2 )
       (min-width: 1086px) calc(100vw - 480px)
       (min-width: 626px)  calc(100vw - 335px)
                           calc(100vw - 30px)" alt="" /></figure>', 'https://css-tricks.com/the-critical-request/');

        $this->assertStringNotContainsString('f_auto,q_auto', $res, 'Image srcset attribute were not replaced');
    }

    public function testProcessImageWithNumericHtmlEntitySeparator()
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg'), ['response_headers' => ['Content-Type: image/jpeg']]),
            new MockResponse(file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg'), ['response_headers' => ['Content-Type: image/jpeg']]),
            new MockResponse(file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg'), ['response_headers' => ['Content-Type: image/jpeg']]),
        ]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        // wordpress.com sites using &#038; as an &amp; alternative
        $res = $download->processHtml(123, '<img srcset="https://example.com/20191204_133626-scaled.jpg?strip=info&#038;w=600&#038;ssl=1 600w,https://example.com/20191204_133626-scaled.jpg?strip=info&#038;w=900&#038;ssl=1 900w" src="https://example.com/20191204_133626-scaled.jpg?ssl=1"/>', 'https://example.com/about/');

        $this->assertStringNotContainsString('https://example.com', $res, 'Image srcset attribute were not replaced');
    }

    public function testProcessImageWithNullPath()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse(file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg'))]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);

        $res = $download->processSingleImage(
            123,
            null,
            'https://framablog.org/2018/06/30/engagement-atypique/'
        );
        $this->assertFalse($res);
    }

    public function testEnsureOnlyFirstOccurrenceIsReplaced()
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__ . '/../fixtures/unnamed.png'), ['response_headers' => ['Content-Type: image/png']]),
            new MockResponse(file_get_contents(__DIR__ . '/../fixtures/unnamed.png'), ['response_headers' => ['Content-Type: image/png']]),
        ]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);

        $html = '<img src="https://images.wsj.net/im-410981?width=860&height=573" srcset="https://images.wsj.net/im-410981?width=860&height=573&pixel_ratio=1.5 1290w" height="573" width="860" alt="" referrerpolicy="no-referrer">';
        $url = 'https://www.wsj.com/articles/5-interior-design-tips-to-max-out-your-basement-space-11633435201';

        $res = $download->processHtml(123, $html, $url);

        $this->assertSame('<img src="http://wallabag.io/assets/images/9/b/9b0ead26/6bef06fe.png" srcset="http://wallabag.io/assets/images/9/b/9b0ead26/43cc0123.png 1290w" height="573" width="860" alt="" referrerpolicy="no-referrer">', $res);
    }

    public function testProcessSingleImageWithSvg()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse(file_get_contents(__DIR__ . '/../fixtures/modal-content.svg'), ['response_headers' => ['Content-Type: image/svg+xml']])]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'modal-content.svg', 'http://imgur.com/gallery/WxtWY');

        $this->assertStringContainsString('/assets/images/9/b/9b0ead26/400e29f9.svg', $res);
    }

    public function testProcessSingleImageWithBadSvg()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse(file_get_contents(__DIR__ . '/../fixtures/unnamed.png'), ['response_headers' => ['Content-Type: image/svg+xml']])]);

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($mockHttpClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'modal-content.svg', 'http://imgur.com/gallery/WxtWY');

        $this->assertFalse($res);
    }
}
