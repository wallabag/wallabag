<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use GuzzleHttp\Psr7\Response;
use Http\Mock\Client as HttpMockClient;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Wallabag\CoreBundle\Helper\DownloadImages;

class DownloadImagesTest extends TestCase
{
    private const PUBLIC_HTTP_IP = '1.1.1.1';
    private const PUBLIC_HTTP_BASE = 'http://' . self::PUBLIC_HTTP_IP;
    private const PUBLIC_HTTPS_BASE = 'https://8.8.8.8';
    private const ENTRY_ASSET_PATH = '/assets/images/9/b/9b0ead26/';
    private const ENTRY_ASSET_PREFIX = 'http://wallabag.io' . self::ENTRY_ASSET_PATH;

    public function dataForSuccessImage()
    {
        return [
            'imgur' => [
                '<div><img src="' . self::PUBLIC_HTTP_BASE . '/T9qgcHc.jpg" /></div>',
                self::PUBLIC_HTTP_BASE . '/gallery/WxtWY',
            ],
            'image with &' => [
                '<div><img src="' . self::PUBLIC_HTTPS_BASE . '/wp-content/uploads/2017/01/screen-shot.jpg?w=640&amp;ssl=1" /></div>',
                self::PUBLIC_HTTPS_BASE . '/realdebrid-kodi-jarvis/',
            ],
        ];
    }

    /**
     * @dataProvider dataForSuccessImage
     */
    public function testProcessHtml($html, $url)
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'image/png'], file_get_contents(__DIR__ . '/../fixtures/unnamed.png')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);

        $res = $download->processHtml(123, $html, $url);

        // this the base path of all image (since it's calculated using the entry id: 123)
        $this->assertStringContainsString(self::ENTRY_ASSET_PREFIX, $res);
    }

    public function testProcessHtmlWithBadImage()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'application/json'], ''));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processHtml(123, '<div><img src="' . self::PUBLIC_HTTP_BASE . '/T9qgcHc.jpg" /></div>', self::PUBLIC_HTTP_BASE . '/gallery/WxtWY');

        $this->assertStringContainsString(self::PUBLIC_HTTP_BASE . '/T9qgcHc.jpg', $res, 'Image were not replace because of content-type');
    }

    public function singleImage()
    {
        return [
            ['image/pjpeg', 'jpeg'],
            ['image/jpeg', 'jpeg'],
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
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => $header], file_get_contents(__DIR__ . '/../fixtures/unnamed.png')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'T9qgcHc.jpg', self::PUBLIC_HTTP_BASE . '/gallery/WxtWY');

        $this->assertStringContainsString($this->assetPathFor(self::PUBLIC_HTTP_BASE . '/gallery/T9qgcHc.jpg', $extension), $res);
    }

    public function testProcessSingleImageWithBadUrl()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(404, []));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'T9qgcHc.jpg', self::PUBLIC_HTTP_BASE . '/gallery/WxtWY');

        $this->assertFalse($res, 'Image can not be found, so it will not be replaced');
    }

    public function testProcessSingleImageWithBadImage()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'image/png'], ''));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, self::PUBLIC_HTTP_BASE . '/T9qgcHc.jpg', self::PUBLIC_HTTP_BASE . '/gallery/WxtWY');

        $this->assertFalse($res, 'Image can not be loaded, so it will not be replaced');
    }

    public function testProcessSingleImageFailAbsolute()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'image/png'], file_get_contents(__DIR__ . '/../fixtures/unnamed.png')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, '/T9qgcHc.jpg', self::PUBLIC_HTTP_IP . '/gallery/WxtWY');

        $this->assertFalse($res, 'Absolute image can not be determined, so it will not be replaced');
    }

    public function testProcessRealImage()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => null], file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);

        $res = $download->processSingleImage(
            123,
            self::PUBLIC_HTTPS_BASE . '/files/article.jpg',
            self::PUBLIC_HTTPS_BASE . '/conversation'
        );

        $this->assertStringContainsString(self::ENTRY_ASSET_PREFIX, $res, 'Content-Type was empty but data is ok for an image');
        $this->assertStringContainsString('DownloadImages: Checking extension (alternative)', $logHandler->getRecords()[3]['message']);
    }

    public function testProcessImageWithSrcset()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => null], file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')));
        $httpMockClient->addResponse(new Response(200, ['content-type' => null], file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')));
        $httpMockClient->addResponse(new Response(200, ['content-type' => null], file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processHtml(123, '<p><img class="alignnone wp-image-1153" src="' . self::PUBLIC_HTTP_BASE . '/files/F1FR-530x375.jpg" alt="" width="628" height="444" srcset="' . self::PUBLIC_HTTP_BASE . '/files/F1FR-530x375.jpg 530w, ' . self::PUBLIC_HTTP_BASE . '/files/F1FR-768x543.jpg 768w, ' . self::PUBLIC_HTTP_BASE . '/files/F1FR-900x636.jpg 900w" sizes="(max-width: 628px) 100vw, 628px" /></p>', self::PUBLIC_HTTP_BASE . '/budget-2018');

        $this->assertStringNotContainsString(self::PUBLIC_HTTP_BASE . '/files/', $res, 'Image srcset attribute were not replaced');
    }

    public function testProcessImageWithTrickySrcset()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => null], file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')));
        $httpMockClient->addResponse(new Response(200, ['content-type' => null], file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')));
        $httpMockClient->addResponse(new Response(200, ['content-type' => null], file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processHtml(123, '<figure id="post-257260" class="align-none media-257260"><img src="' . self::PUBLIC_HTTPS_BASE . '/wp-content/uploads/the-critical-request.png" srcset="' . self::PUBLIC_HTTPS_BASE . '/image/upload/c_scale,w_1000,f_auto,q_auto/the-critical-request.png 1000w, ' . self::PUBLIC_HTTPS_BASE . '/image/upload/c_scale,w_200,f_auto,q_auto/the-critical-request.png 200w" sizes="(min-width: 1850px) calc( (100vw - 555px) / 3 )
       (min-width: 1251px) calc( (100vw - 530px) / 2 )
       (min-width: 1086px) calc(100vw - 480px)
       (min-width: 626px)  calc(100vw - 335px)
                           calc(100vw - 30px)" alt="" /></figure>', self::PUBLIC_HTTPS_BASE . '/the-critical-request/');

        $this->assertStringNotContainsString('f_auto,q_auto', $res, 'Image srcset attribute were not replaced');
    }

    public function testProcessImageWithNumericHtmlEntitySeparator()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'image/jpeg'], file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')));
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'image/jpeg'], file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')));
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'image/jpeg'], file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        // wordpress.com sites using &#038; as an &amp; alternative
        $res = $download->processHtml(123, '<img srcset="' . self::PUBLIC_HTTPS_BASE . '/20191204_133626-scaled.jpg?strip=info&#038;w=600&#038;ssl=1 600w,' . self::PUBLIC_HTTPS_BASE . '/20191204_133626-scaled.jpg?strip=info&#038;w=900&#038;ssl=1 900w" src="' . self::PUBLIC_HTTPS_BASE . '/20191204_133626-scaled.jpg?ssl=1"/>', self::PUBLIC_HTTPS_BASE . '/about/');

        $this->assertStringNotContainsString(self::PUBLIC_HTTPS_BASE, $res, 'Image srcset attribute were not replaced');
    }

    public function testProcessImageWithNullPath()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => null], file_get_contents(__DIR__ . '/../fixtures/image-no-content-type.jpg')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);

        $res = $download->processSingleImage(
            123,
            null,
            self::PUBLIC_HTTPS_BASE . '/engagement-atypique/'
        );
        $this->assertFalse($res);
    }

    public function provideBlockedLocalImageUrls(): array
    {
        return [
            'loopback' => ['http://127.0.0.1/image.png'],
            'private class a' => ['http://10.0.0.1/image.png'],
            'link-local metadata' => ['http://169.254.169.254/image.png'],
            'private class b' => ['http://172.16.0.1/image.png'],
            'private class c' => ['http://192.168.1.1/image.png'],
        ];
    }

    /**
     * @dataProvider provideBlockedLocalImageUrls
     */
    public function testProcessSingleImageBlocksLocalAddress($imageUrl)
    {
        $httpMockClient = new HttpMockClient();

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, $imageUrl, self::PUBLIC_HTTP_BASE . '/article');

        $this->assertFalse($res);
        $this->assertCount(0, $httpMockClient->getRequests());
        $this->assertTrue($logHandler->hasErrorThatContains('DownloadImages: Can not retrieve image, skipping.'));
    }

    public function testEnsureOnlyFirstOccurrenceIsReplaced()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'image/png'], file_get_contents(__DIR__ . '/../fixtures/unnamed.png')));
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'image/png'], file_get_contents(__DIR__ . '/../fixtures/unnamed.png')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);

        $html = '<img src="' . self::PUBLIC_HTTPS_BASE . '/im-410981?width=860&height=573" srcset="' . self::PUBLIC_HTTPS_BASE . '/im-410981?width=860&height=573&pixel_ratio=1.5 1290w" height="573" width="860" alt="" referrerpolicy="no-referrer">';
        $url = self::PUBLIC_HTTPS_BASE . '/articles/interior-design';

        $res = $download->processHtml(123, $html, $url);

        $this->assertSame(
            '<img src="' . $this->assetUrlFor(self::PUBLIC_HTTPS_BASE . '/im-410981?width=860&height=573', 'png') . '" srcset="' . $this->assetUrlFor(self::PUBLIC_HTTPS_BASE . '/im-410981?width=860&height=573&pixel_ratio=1.5', 'png') . ' 1290w" height="573" width="860" alt="" referrerpolicy="no-referrer">',
            $res
        );
    }

    public function testProcessSingleImageWithSvg()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'image/svg+xml'], file_get_contents(__DIR__ . '/../fixtures/modal-content.svg')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'modal-content.svg', self::PUBLIC_HTTP_BASE . '/gallery/WxtWY');

        $this->assertStringContainsString($this->assetPathFor(self::PUBLIC_HTTP_BASE . '/gallery/modal-content.svg', 'svg'), $res);
    }

    public function testProcessSingleImageWithBadSvg()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'image/svg+xml'], file_get_contents(__DIR__ . '/../fixtures/unnamed.png')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, 'modal-content.svg', self::PUBLIC_HTTP_BASE . '/gallery/WxtWY');

        $this->assertFalse($res);
    }

    public function testFollowRedirection()
    {
        $httpMockClient = new HttpMockClient();
        $httpMockClient->addResponse(new Response(301, ['content-type' => 'image/png', 'location' => '/final-path.png']));
        $httpMockClient->addResponse(new Response(200, ['content-type' => 'image/png'], file_get_contents(__DIR__ . '/../fixtures/unnamed.png')));

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $download = new DownloadImages($httpMockClient, sys_get_temp_dir() . '/wallabag_test', 'http://wallabag.io/', $logger);
        $res = $download->processSingleImage(123, '', self::PUBLIC_HTTPS_BASE . '/unnamed.png');

        $this->assertStringContainsString($this->assetPathFor(self::PUBLIC_HTTPS_BASE . '/unnamed.png', 'png'), $res, "Fetch client didn't follow the HTTP redirection");
    }

    private function assetUrlFor(string $absolutePath, string $extension): string
    {
        return self::ENTRY_ASSET_PREFIX . hash('crc32', $absolutePath) . '.' . $extension;
    }

    private function assetPathFor(string $absolutePath, string $extension): string
    {
        return self::ENTRY_ASSET_PATH . hash('crc32', $absolutePath) . '.' . $extension;
    }
}
