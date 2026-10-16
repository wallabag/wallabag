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
        self::assertStringNotContainsString('Article text captured from public X data via', $result['html']);
        self::assertStringNotContainsString('Partial article snapshot.', $result['html']);
        self::assertSame('article', $result['x_capture_type']);
    }

    public static function imagePost(string $url = 'https://pbs.twimg.com/media/Synthetic_1.png'): array
    {
        $post = self::post();
        $post['article']['cover_media']['media_info']['original_img_url'] = $url;
        $post['article']['media_entities'] = [['media_id' => '1', 'media_info' => ['original_img_url' => $url]]];
        $post['article']['content']['entityMap'][] = ['key' => '2', 'value' => ['type' => 'MEDIA', 'data' => ['caption' => '"><script>bad</script>', 'mediaItems' => [['mediaId' => '1']]]]];
        $post['article']['content']['blocks'][] = ['type' => 'atomic', 'text' => ' ', 'entityRanges' => [['key' => '2']]];

        return $post;
    }

    public function testCoverAndBodyImagesUseEscapedBrowserOnlyReferences(): void
    {
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::never())->method('get');
        $result = (new FxArticleReader($http))->render(self::imagePost(), 'https://x.com/i/status/12345');
        self::assertSame(200, $result['status']);
        $doc = new \DOMDocument();
        $doc->loadHTML($result['html']);
        self::assertSame(2, $doc->getElementsByTagName('img')->length);
        self::assertSame(0, $doc->getElementsByTagName('script')->length);
        foreach ($doc->getElementsByTagName('img') as $image) {
            self::assertSame('https://pbs.twimg.com/media/Synthetic_1.png', $image->getAttribute('src'));
            self::assertSame('no-referrer', $image->getAttribute('referrerpolicy'));
            self::assertSame('lazy', $image->getAttribute('loading'));
            self::assertSame(4, $image->attributes->length);
        }
        self::assertSame('"><script>bad</script>', $doc->getElementsByTagName('img')->item(1)->getAttribute('alt'));
        self::assertStringNotContainsString('image bytes are not archived', $result['html']);
    }

    public function testRejectedCoverAloneMakesTheSnapshotPartial(): void
    {
        $post = self::post();
        $post['article']['cover_media']['media_info']['original_img_url'] = 'https://evil.example/a.jpg';
        $result = (new FxArticleReader(new PublicHttpClient()))->render($post, 'https://x.com/i/status/12345');
        self::assertSame(206, $result['status']);
        self::assertStringContainsString('Cover image unavailable', $result['html']);
        self::assertStringNotContainsString('<img', $result['html']);
    }

    /** @dataProvider unsafeImageUrls */
    public function testUnsafeMediaNeverBecomesAnImageOrLink(string $url): void
    {
        $result = (new FxArticleReader(new PublicHttpClient()))->render(self::imagePost($url), 'https://x.com/i/status/12345');
        self::assertSame(206, $result['status']);
        self::assertStringNotContainsString('<img', $result['html']);
        self::assertStringNotContainsString('href="' . $url, $result['html']);
        self::assertStringContainsString('Embedded content unavailable', $result['html']);
    }

    public function unsafeImageUrls(): array
    {
        return array_map(static function ($url) { return [$url]; }, [
            'http://pbs.twimg.com/media/a.jpg',
            'https://pbs.twimg.com.evil.example/media/a.jpg',
            'https://evil.example/media/a.jpg',
            'https://127.0.0.1/media/a.jpg',
            'https://user@pbs.twimg.com/media/a.jpg',
            'https://pbs.twimg.com:443/media/a.jpg',
            'https://pbs.twimg.com/media/a.svg',
            'https://pbs.twimg.com/media/../a.jpg',
            'https://pbs.twimg.com/media/%2e%2e/a.jpg',
            'https://pbs.twimg.com/media/a.jpg?redirect=http://127.0.0.1',
            'https://pbs.twimg.com/media/a.jpg#fragment',
            'https://pbs.twimg.com/media/a.jpg" onerror="bad',
            "https://pbs.twimg.com/media/a.jpg\n",
            'data:image/png;base64,a',
            'javascript:alert(1)',
            'https://pbs.twimg.com/media/' . str_repeat('a', 2048) . '.png',
        ]);
    }

    public function testUnknownEmbedIsExplicitlyPartial(): void
    {
        $post = self::post();
        $post['article']['content']['blocks'][] = ['type' => 'atomic', 'text' => ' ', 'entityRanges' => []];
        $result = (new FxArticleReader(new PublicHttpClient()))->render($post, 'https://x.com/i/status/12345');
        self::assertSame(206, $result['status']);
        self::assertStringNotContainsString('Partial article snapshot.', $result['html']);
        self::assertStringNotContainsString('Article text captured from public X data via', $result['html']);
        self::assertStringContainsString('Embedded content unavailable', $result['html']);
        self::assertSame('article', $result['x_capture_type']);
    }

    public function testGenuineSourceParagraphsMatchingOldNoticesArePreserved(): void
    {
        $notices = [
            'Partial article snapshot. Some embedded content or formatting is unavailable.',
            'Article text captured from public X data via FxTwitter. Images load from X’s public media CDN in your browser; image bytes are not archived. Embedded posts are linked. Verify the original for later edits.',
        ];
        $post = self::post();
        foreach ($notices as $text) {
            $post['article']['content']['blocks'][] = ['type' => 'unstyled', 'text' => $text];
        }
        $result = (new FxArticleReader(new PublicHttpClient()))->render($post, 'https://x.com/i/status/12345');
        self::assertSame(200, $result['status']);
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $result['html']);
        $paragraphs = [];
        foreach ($doc->getElementsByTagName('p') as $paragraph) {
            $paragraphs[] = $paragraph->textContent;
        }
        foreach ($notices as $text) {
            self::assertSame(1, \count(array_keys($paragraphs, $text, true)));
        }
        self::assertStringContainsString('The ending paragraph.', $result['html']);
        self::assertStringContainsString('Original post and article on X', $result['html']);
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
