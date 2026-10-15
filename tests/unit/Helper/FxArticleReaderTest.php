<?php

namespace Wallabag\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Wallabag\Helper\FxArticleReader;
use Wallabag\Helper\PublicHttpClient;
use Wallabag\Helper\XPostExtractor;

class FxArticleReaderTest extends TestCase
{
    public static function post(): array
    {
        return ['id' => '12345', 'author' => ['name' => 'An author', 'protected' => false], 'article' => ['title' => 'Synthetic article', 'created_at' => '2026-10-15T12:00:00Z', 'content' => ['blocks' => [
            ['type' => 'header-two', 'text' => 'A heading'],
            ['type' => 'unstyled', 'text' => '😀word <script>alert(1)</script>', 'inlineStyleRanges' => [['offset' => 2, 'length' => 4, 'style' => 'Bold']], 'entityRanges' => [['offset' => 2, 'length' => 4, 'key' => 1]]],
            ['type' => 'unstyled', 'text' => 'The ending paragraph.'],
        ], 'entityMap' => [['key' => '1', 'value' => ['type' => 'LINK', 'data' => ['url' => 'https://example.org/attribution']]]]]]];
    }

    public function testStructuredBodyIsEscapedAttributedAndPreservesUtf16Ranges(): void
    {
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::once())->method('get')->with('https://api.fxtwitter.com/2/status/12345')->willReturn(['body' => json_encode(['code' => 200, 'status' => self::post()])]);
        $result = (new FxArticleReader($http))->extract('https://x.com/i/status/12345');
        self::assertSame(200, $result['status']);
        self::assertSame('Synthetic article', $result['title']);
        self::assertStringContainsString('<strong>word</strong>', $result['html']);
        self::assertStringContainsString('😀', $result['html']);
        self::assertStringNotContainsString('<script>', $result['html']);
        self::assertStringContainsString('The ending paragraph.', $result['html']);
        self::assertStringContainsString('Original post and article on X', $result['html']);
        self::assertStringContainsString('not archived', $result['html']);
    }

    public function testUnknownEmbedIsExplicitlyPartial(): void
    {
        $post = self::post();
        $post['article']['content']['blocks'][] = ['type' => 'atomic', 'text' => ' ', 'entityRanges' => []];
        $result = (new FxArticleReader(new PublicHttpClient()))->render($post, 'https://x.com/i/status/12345');
        self::assertSame(206, $result['status']);
        self::assertStringContainsString('Partial article snapshot', $result['html']);
    }

    public function testPreviewOnlyIsNotTreatedAsArticleBody(): void
    {
        $post = self::post();
        unset($post['article']['content']);
        $post['article']['preview_text'] = 'A preview';
        $this->expectException(\RuntimeException::class);
        (new FxArticleReader(new PublicHttpClient()))->render($post, 'https://x.com/i/status/12345');
    }

    public function testMismatchedPostIsRejected(): void
    {
        $http = $this->createMock(PublicHttpClient::class);
        $http->method('get')->willReturn(['body' => json_encode(['code' => 200, 'status' => self::post()])]);
        $this->expectException(\RuntimeException::class);
        (new FxArticleReader($http))->extract('https://x.com/i/status/98765');
    }

    public function testThirdPartyIsOptInAndFailureFallsBackToPartialEmbed(): void
    {
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::exactly(2))->method('get')->willReturnOnConsecutiveCalls(['body' => '{"code":404}'], ['body' => '{"author_name":"Author","html":"<p>A public excerpt</p>"}']);
        $result = (new XPostExtractor($http))->extract('https://x.com/person/status/12345', true);
        self::assertSame(206, $result['status']);
        self::assertTrue($result['reader_failed']);
        self::assertStringContainsString('did not return a verified body', $result['html']);
    }
}
