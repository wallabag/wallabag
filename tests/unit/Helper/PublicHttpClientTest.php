<?php

namespace Wallabag\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Wallabag\Helper\PublicHttpClient;

class PublicHttpClientTest extends TestCase
{
    /** @dataProvider blockedIps */
    public function testBlocksNonPublicAddresses(string $ip): void { self::assertFalse(PublicHttpClient::isPublicIp($ip)); }
    public function blockedIps(): array
    {
        return array_map(static function ($ip) { return [$ip]; }, ['127.0.0.1', '10.0.0.1', '172.16.0.1', '192.168.0.2', '169.254.169.254', '100.100.100.200', '0.0.0.0', '198.18.0.1', '224.0.0.1', '::1', 'fc00::1', 'fe80::1', '::ffff:127.0.0.1', '2001:db8::1', '2002:7f00:1::']);
    }
    public function testPublicAddress(): void { self::assertTrue(PublicHttpClient::isPublicIp('1.1.1.1')); self::assertTrue(PublicHttpClient::isPublicIp('2606:4700:4700::1111')); }
    public function testRejectsMixedDnsAnswers(): void
    {
        $client = new class extends PublicHttpClient { protected function resolve(string $host): array { return ['1.1.1.1', '127.0.0.1']; } };
        $this->expectException(\InvalidArgumentException::class);
        $client->validate('https://example.com');
    }
    /** @dataProvider invalidUrls */
    public function testRejectsUnsafeUrls(string $url): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new PublicHttpClient())->validate($url);
    }
    public function invalidUrls(): array { return [['file:///etc/passwd'], ['http://user:password@1.1.1.1'], ['http://1.1.1.1:8080'], ["http://1.1.1.1/\r\nX-Test: yes"], ['http://[::1]/']]; }
    public function testRedirectToPrivateAddressIsNeverRequested(): void
    {
        $client = new class extends PublicHttpClient {
            public $requests = [];
            protected function request(string $url, array $ips): array {
                $this->requests[] = [$url, $ips];
                return ['body' => '', 'status' => 302, 'location' => 'http://127.0.0.1/admin'];
            }
        };
        try { $client->get('https://1.1.1.1/'); self::fail('Private redirect must fail.'); }
        catch (\InvalidArgumentException $e) { self::assertCount(1, $client->requests); self::assertSame(['1.1.1.1'], $client->requests[0][1]); }
    }

    public function testDnsIsRevalidatedAndPinnedOnEachRedirect(): void
    {
        $client = new class extends PublicHttpClient {
            public $requests = 0;
            private $resolutions = 0;
            protected function resolve(string $host): array { return ++$this->resolutions === 1 ? ['1.1.1.1'] : ['127.0.0.1']; }
            protected function request(string $url, array $ips): array {
                ++$this->requests;
                return ['body' => '', 'status' => 302, 'location' => '/rebound'];
            }
        };
        try { $client->get('https://example.com/'); self::fail('Rebinding must fail.'); }
        catch (\InvalidArgumentException $e) { self::assertSame(1, $client->requests); }
    }

}
