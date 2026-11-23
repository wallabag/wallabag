<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use PHPePub\Core\EPub;
use PHPUnit\Framework\TestCase;
use Wallabag\CoreBundle\Helper\DownloadImages;
use Wallabag\CoreBundle\Helper\EpubContentCleaner;
use Wallabag\CoreBundle\Helper\InstanceImageLocator;

class EpubContentCleanerTest extends TestCase
{
    public function testCleanReturnsXhtml()
    {
        $cleaner = new EpubContentCleaner($this->createMock(InstanceImageLocator::class), $this->createMock(DownloadImages::class));

        $this->assertSame(
            '<p>a<br/>b' . "\u{a0}" . 'c</p><hr/>',
            $cleaner->clean($this->createMock(EPub::class), '<!-- comment --><p>a<br>b&nbsp;c<hr></p>')
        );
    }

    public function testCleanEmbedsImagesStoredByWallabag()
    {
        $imageFilename = tempnam(sys_get_temp_dir(), 'wallabag-image-');
        file_put_contents($imageFilename, 'image data');

        $locator = $this->createMock(InstanceImageLocator::class);
        $locator->method('locate')->willReturnMap([
            ['http://wallabag.example/assets/images/image.png', ['path' => $imageFilename, 'extension' => 'png', 'mimetype' => 'image/png']],
        ]);

        $book = $this->createMock(EPub::class);
        $book->expects($this->once())
            ->method('addFile')
            ->with('images/' . sha1($imageFilename) . '.png', 'image-' . sha1($imageFilename), 'image data', 'image/png');

        $result = (new EpubContentCleaner($locator, $this->createMock(DownloadImages::class)))->clean(
            $book,
            '<img src="http://wallabag.example/assets/images/image.png" srcset="http://wallabag.example/assets/images/image.png 2x"/>'
        );

        unlink($imageFilename);

        $this->assertSame('<img src="images/' . sha1($imageFilename) . '.png"/>', $result);
    }

    public function testCleanEmbedsRemoteImagesOnce()
    {
        $downloadImages = $this->createMock(DownloadImages::class);
        $downloadImages->expects($this->once())
            ->method('fetchImage')
            ->with('http://example.org/image.png', 'http://example.org/article')
            ->willReturn(['content' => 'image data', 'extension' => 'png', 'mimetype' => 'image/png']);

        $fileName = 'images/' . sha1('http://example.org/image.png') . '.png';
        $book = $this->createMock(EPub::class);
        $book->expects($this->once())
            ->method('addFile')
            ->with($fileName, 'image-' . sha1('http://example.org/image.png'), 'image data', 'image/png');

        $result = (new EpubContentCleaner($this->createMock(InstanceImageLocator::class), $downloadImages))->clean(
            $book,
            '<img src="http://example.org/image.png" srcset="http://example.org/image.png 2x"/><img src="http://example.org/image.png"/>',
            'http://example.org/article'
        );

        $this->assertSame('<img src="' . $fileName . '"/><img src="' . $fileName . '"/>', $result);
    }

    public function testCleanKeepsImagesThatCanNotBeDownloaded()
    {
        $downloadImages = $this->createMock(DownloadImages::class);
        $downloadImages->method('fetchImage')->willReturn(null);

        $book = $this->createMock(EPub::class);
        $book->expects($this->never())->method('addFile');

        $result = (new EpubContentCleaner($this->createMock(InstanceImageLocator::class), $downloadImages))->clean($book, '<img src="http://example.org/image.png"/>');

        $this->assertSame('<img src="http://example.org/image.png"/>', $result);
    }

    public function testCleanDoesNotDownloadInlineImages()
    {
        $downloadImages = $this->createMock(DownloadImages::class);
        $downloadImages->expects($this->never())->method('fetchImage');

        (new EpubContentCleaner($this->createMock(InstanceImageLocator::class), $downloadImages))->clean($this->createMock(EPub::class), '<img src="data:image/png;base64,AAAA"/>');
    }

    public function testCleanDownloadsAtMostAGivenNumberOfImages()
    {
        $downloadImages = $this->createMock(DownloadImages::class);
        $downloadImages->expects($this->exactly(EpubContentCleaner::MAX_REMOTE_IMAGES))->method('fetchImage')->willReturn(null);

        $content = '';
        for ($i = 0; $i < EpubContentCleaner::MAX_REMOTE_IMAGES + 5; ++$i) {
            $content .= '<img src="http://example.org/' . $i . '.png"/>';
        }

        (new EpubContentCleaner($this->createMock(InstanceImageLocator::class), $downloadImages))->clean($this->createMock(EPub::class), $content);
    }
}
