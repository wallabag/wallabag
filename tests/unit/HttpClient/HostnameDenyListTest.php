<?php

namespace Wallabag\Tests\Unit\HttpClient;

use PHPUnit\Framework\TestCase;
use Wallabag\HttpClient\HostnameDenyList;

class HostnameDenyListTest extends TestCase
{
    public function testDefaultsToEmptyConfiguration(): void
    {
        $this->assertTrue((new HostnameDenyList())->isEmpty());
    }

    public function testEmptyConfigurationDoesNotBlockHosts(): void
    {
        $denyList = new HostnameDenyList(['', '  ', null]);

        $this->assertTrue($denyList->isEmpty());
        $this->assertNull($denyList->getBlockedHostname('example.com'));
    }

    public function testExactHostnameMatching(): void
    {
        $denyList = new HostnameDenyList(['example.com']);

        $this->assertSame('example.com', $denyList->getBlockedHostname('example.com'));
        $this->assertNull($denyList->getBlockedHostname('www.example.com'));
    }

    public function testNormalizesCaseWhitespaceAndTerminalDnsDots(): void
    {
        $denyList = new HostnameDenyList(['  ExAmPlE.Com.  ']);

        $this->assertSame('example.com', $denyList->getBlockedHostname('EXAMPLE.COM.'));
    }

    public function testNormalizesIdnsUsingUts46Ascii(): void
    {
        $denyList = new HostnameDenyList(['faß.de']);

        $this->assertSame('xn--fa-hia.de', $denyList->getBlockedHostname('xn--fa-hia.de'));
        $this->assertSame('xn--fa-hia.de', $denyList->getBlockedHostname('FAẞ.DE'));
    }

    public function testMatchesExactIpLiterals(): void
    {
        $denyList = new HostnameDenyList(['192.0.2.1', '2001:0db8:0000:0000:0000:0000:0000:0001']);

        $this->assertSame('192.0.2.1', $denyList->getBlockedHostname('192.0.2.1'));
        $this->assertSame('2001:db8::1', $denyList->getBlockedHostname('[2001:db8::1]'));
        $this->assertSame('2001:db8::1', $denyList->getBlockedHostname('[2001:0db8:0:0:0:0:0:1]'));
        $this->assertNull($denyList->getBlockedHostname('192.0.2.2'));
    }

    public function testDuplicateEntriesAreHarmless(): void
    {
        $denyList = new HostnameDenyList(['example.com', 'EXAMPLE.COM.', 'example.com']);

        $this->assertSame('example.com', $denyList->getBlockedHostname('example.com'));
    }

    /**
     * @dataProvider invalidHostnameProvider
     */
    public function testRejectsMalformedConfiguration($hostname): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new HostnameDenyList([$hostname]);
    }

    public static function invalidHostnameProvider(): iterable
    {
        yield 'non-string' => [123];
        yield 'scheme' => ['https://example.com'];
        yield 'path' => ['example.com/path'];
        yield 'userinfo' => ['user@example.com'];
        yield 'wildcard' => ['*.example.com'];
        yield 'regular expression' => ['^example\\.com$'];
        yield 'port' => ['example.com:443'];
        yield 'invalid leading dots' => ['..example.com'];
        yield 'empty hostname' => ['.'];
        yield 'invalid label' => ['bad_label.example.com'];
        yield 'bracketed IPv6' => ['[2001:db8::1]'];
    }
}
