<?php

namespace Wallabag\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Wallabag\Helper\MediumExtractor;
use Wallabag\Helper\PublicHttpClient;

class MediumExtractorTest extends TestCase
{
    public function testOnlyDirectPublicBodyIsExtractedAndMarkedUnverified(): void
    {
        $url = 'https://medium.com/@author/story-abcdef012345';
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::once())->method('get')->with($url)->willReturn(['url' => $url, 'body' => '<html><head><title>Public story</title></head><body><article><h1>Public story</h1><p>' . str_repeat('A publicly returned Medium paragraph. ', 30) . '</p><p>Public ending.</p></article></body></html>']);
        $result = (new MediumExtractor($http))->extract($url);
        self::assertSame(206, $result['status']);
        self::assertStringContainsString('completeness unverified', $result['html']);
        self::assertStringContainsString('Public ending.', $result['html']);
        self::assertStringNotContainsString('freedium', $result['url']);
    }

    public function testAccessDeniedIsAnUnavailableBookmarkWithoutMirrorRetry(): void
    {
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::once())->method('get')->willThrowException(new \RuntimeException('HTTP 403'));
        $result = (new MediumExtractor($http))->extract('https://medium.com/p/abcdef012345');
        self::assertSame(502, $result['status']);
        self::assertStringContainsString('not a captured story', $result['html']);
    }

    public function testLookalikeHostNeverTriggersPublicHttp(): void
    {
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::never())->method('get');
        $this->expectException(\InvalidArgumentException::class);
        (new MediumExtractor($http))->extract('https://medium.com.evil.example/p/abcdef012345');
    }
}
