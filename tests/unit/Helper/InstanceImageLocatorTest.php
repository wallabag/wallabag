<?php

namespace Wallabag\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Wallabag\Helper\InstanceImageLocator;

class InstanceImageLocatorTest extends TestCase
{
    private const WALLABAG_URL = 'http://wallabag.example';

    private $projectDir;
    private $locator;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . '/wallabag-locator-' . uniqid();
        mkdir($this->projectDir . '/web/assets/images/a', 0777, true);
        touch($this->projectDir . '/web/assets/images/a/image.png');
        touch($this->projectDir . '/web/assets/images/a/file.txt');
        touch($this->projectDir . '/web/secret.png');

        $this->locator = new InstanceImageLocator(self::WALLABAG_URL . '/', $this->projectDir);
    }

    protected function tearDown(): void
    {
        unlink($this->projectDir . '/web/assets/images/a/image.png');
        unlink($this->projectDir . '/web/assets/images/a/file.txt');
        unlink($this->projectDir . '/web/secret.png');
        rmdir($this->projectDir . '/web/assets/images/a');
        rmdir($this->projectDir . '/web/assets/images');
        rmdir($this->projectDir . '/web/assets');
        rmdir($this->projectDir . '/web');
        rmdir($this->projectDir);
    }

    public function testLocate(): void
    {
        $image = $this->locator->locate(self::WALLABAG_URL . '/assets/images/a/image.png?size=2');

        $this->assertSame(realpath($this->projectDir) . '/web/assets/images/a/image.png', $image['path']);
        $this->assertSame('png', $image['extension']);
        $this->assertSame('image/png', $image['mimetype']);
    }

    /**
     * @dataProvider dataForNotLocated
     */
    public function testLocateReturnsNull(?string $url): void
    {
        $this->assertNull($this->locator->locate($url));
    }

    public static function dataForNotLocated(): iterable
    {
        yield 'no url' => [null];
        yield 'other host' => ['http://other.example/assets/images/a/image.png'];
        yield 'outside the images folder' => [self::WALLABAG_URL . '/assets/images/../../secret.png'];
        yield 'not an image' => [self::WALLABAG_URL . '/assets/images/a/file.txt'];
        yield 'missing file' => [self::WALLABAG_URL . '/assets/images/a/missing.png'];
    }
}
