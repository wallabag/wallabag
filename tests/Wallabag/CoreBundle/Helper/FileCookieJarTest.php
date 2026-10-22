<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Wallabag\CoreBundle\Helper\FileCookieJar;

class FileCookieJarTest extends TestCase
{
    public function testMalformedCookieFileIsLoggedAndReset(): void
    {
        $filename = tempnam(sys_get_temp_dir(), 'wallabag-cookie-');
        file_put_contents($filename, '{broken json');
        $handler = new TestHandler();

        try {
            $cookies = new FileCookieJar(new Logger('test', [$handler]), $filename);

            $this->assertCount(0, $cookies);
            $this->assertTrue($handler->hasErrorRecords());
            unset($cookies);
            $this->assertSame('[]', file_get_contents($filename));
        } finally {
            unlink($filename);
        }
    }

    public function testValidCookieFileCanBeLoaded(): void
    {
        $filename = tempnam(sys_get_temp_dir(), 'wallabag-cookie-');
        file_put_contents($filename, '[{"Name":"session","Value":"valid","Domain":"example.org","Path":"/"}]');

        try {
            $cookies = new FileCookieJar(new Logger('test'), $filename);

            $this->assertCount(1, $cookies);
            $this->assertSame('valid', $cookies->getCookieByName('session')->getValue());
            unset($cookies);
        } finally {
            unlink($filename);
        }
    }
}
