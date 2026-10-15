<?php

namespace Wallabag\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Wallabag\Helper\PublicHttpClient;
use Wallabag\Helper\XPostExtractor;

class XPostExtractorTest extends TestCase
{
    public function testCanonicalVariants(): void
    {
        foreach (['https://x.com/poteto/status/2069824386283319343?s=20', 'https://twitter.com/poteto/status/2069824386283319343/photo/1', 'https://x.com/i/web/status/2069824386283319343'] as $url) {
            self::assertSame('https://x.com/i/status/2069824386283319343', XPostExtractor::canonicalUrl($url));
        }
        foreach (['https://x.com.evil.test/poteto/status/1', 'https://x.com@127.0.0.1/a/status/1', 'file://x.com/a/status/1', 'https://x.com:8443/a/status/1'] as $url) {
            self::assertNull(XPostExtractor::canonicalUrl($url));
        }
    }

    public function testPublicEmbedIsPartialAndSanitized(): void
    {
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::once())->method('get')->with(self::stringStartsWith('https://publish.twitter.com/oembed?'))->willReturn(['body' => json_encode(['author_name' => '<lauren>', 'html' => '<blockquote><p onclick="evil()">Text<a href="javascript:alert(1)">bad</a><a href="https://t.co/test">link</a></p><script>evil()</script><img src="http://127.0.0.1/x"></blockquote>'])]);
        $result = (new XPostExtractor($http))->extract('https://x.com/poteto/status/2069824386283319343');
        self::assertSame(206, $result['status']);
        self::assertStringContainsString('Partial X capture', $result['html']);
        self::assertStringNotContainsString('javascript:', $result['html']);
        self::assertStringNotContainsString('onclick', $result['html']);
        self::assertStringNotContainsString('script', $result['html']);
        self::assertStringNotContainsString('<img', $result['html']);
        self::assertStringContainsString('https://t.co/test', $result['html']);
    }

    public function testUnavailableDoesNotClaimCapture(): void
    {
        $http = $this->createMock(PublicHttpClient::class);
        $http->method('get')->willThrowException(new \RuntimeException('HTTP 403'));
        $result = (new XPostExtractor($http))->extract('https://x.com/poteto/status/1');
        self::assertSame(502, $result['status']);
        self::assertStringContainsString('not a captured post', $result['html']);
    }
}
