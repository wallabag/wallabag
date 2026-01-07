<?php

namespace Tests\Wallabag\Helper;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Wallabag\Helper\CryptoProxy;

class CryptoProxyTest extends TestCase
{
    public function testCrypto()
    {
        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $crypto = new CryptoProxy(sys_get_temp_dir() . '/' . uniqid('', true) . '.txt', $logger);
        $crypted = $crypto->crypt('test');
        $decrypted = $crypto->decrypt($crypted);

        $this->assertSame('test', $decrypted);

        $records = $logHandler->getRecords();
        $this->assertCount(2, $records);
        $this->assertStringContainsString('Crypto: crypting value', $records[0]['message']);
        $this->assertStringContainsString('Crypto: decrypting value', $records[1]['message']);
    }

    public function testDecryptBadValue()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Decrypt fail');

        $crypto = new CryptoProxy(sys_get_temp_dir() . '/' . uniqid('', true) . '.txt', new NullLogger());
        $crypto->decrypt('badvalue');
    }
}
