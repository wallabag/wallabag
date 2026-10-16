<?php

namespace Wallabag\Tests\Unit\Rss;

use PHPUnit\Framework\TestCase;
use Wallabag\Rss\FeedParser;

class FeedParserTest extends TestCase
{
    public function testRssLimitNormalizationCategoriesAndDate(): void
    {
        $xml = '<rss><channel><item><title>One</title><link>/article#top</link><category>Tech</category><pubDate>Wed, 24 Jun 2026 12:00:00 GMT</pubDate></item><item><link>/two</link></item></channel></rss>';
        $items = (new FeedParser())->parse($xml, 'https://example.com/feed', 1);
        self::assertCount(1, $items);
        self::assertSame('https://example.com/article', $items[0]['url']);
        self::assertSame(['Tech'], $items[0]['tags']);
        self::assertSame('Wed, 24 Jun 2026 12:00:00 GMT', $items[0]['date']);
    }

    public function testAtomNamespaceAndInheritedBase(): void
    {
        $xml = '<feed xmlns="http://www.w3.org/2005/Atom" xml:base="https://example.org/blog/"><entry xml:base="posts/"><title>Test</title><link rel="self" href="ignore"/><link rel="alternate" href="one#section"/><category term="News"/><updated>2026-06-24T12:00:00Z</updated></entry></feed>';
        $items = (new FeedParser())->parse($xml, 'https://example.com/feed', 10);
        self::assertSame('https://example.org/blog/posts/one', $items[0]['url']);
        self::assertSame(['News'], $items[0]['tags']);
    }

    /** @dataProvider invalidXml */
    public function testInvalidXmlRestoresLibxmlState(string $xml): void
    {
        $previous = libxml_use_internal_errors(false);
        try {
            try {
                (new FeedParser())->parse($xml, 'https://example.org/feed', 10);
                self::fail('Invalid XML must be rejected.');
            } catch (\RuntimeException $e) {
                self::assertFalse(libxml_use_internal_errors());
                self::assertSame([], libxml_get_errors());
            }
            self::assertCount(0, (new FeedParser())->parse('<rss><channel/></rss>', 'https://example.org/feed', 10));
        } finally {
            libxml_use_internal_errors($previous);
        }
    }

    public function invalidXml(): array
    {
        return [[''], ['   '], ['<rss>']];
    }

    public function testDeclarationExamplesInAtomCdataCommentsAndEscapedTextAreAccepted(): void
    {
        $xml = '<feed xmlns="http://www.w3.org/2005/Atom"><!-- <!DOCTYPE example> -->'
            . '<entry><title>Literal &lt;!ENTITY example&gt;</title><link href="https://example.org/article"/>'
            . '<content type="html"><![CDATA[<pre><!DOCTYPE html> <!ENTITY x SYSTEM "file:///etc/passwd"></pre>]]></content></entry></feed>';
        $items = (new FeedParser())->parse($xml, 'https://example.org/feed', 10);
        self::assertCount(1, $items);
        self::assertSame('https://example.org/article', $items[0]['url']);
        self::assertSame('Literal <!ENTITY example>', $items[0]['title']);
    }

    /**
     * @dataProvider forbiddenDeclarations
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testActualDeclarationsAreRejectedWithoutLoadingExternalResources(string $xml): void
    {
        $loads = [];
        libxml_set_external_entity_loader(static function ($public, $system) use (&$loads) {
            $loads[] = $system;

            return null;
        });
        try {
            try {
                (new FeedParser())->parse($xml, 'https://example.org/feed', 10);
                self::fail('Document DTD/entities must be rejected.');
            } catch (\RuntimeException $e) {
                self::assertSame('Feed exceeds size limit or contains a forbidden DTD/entity.', $e->getMessage());
            }
            self::assertSame([], $loads);
        } finally {
            libxml_set_external_entity_loader(null);
        }
    }

    public function forbiddenDeclarations(): array
    {
        return [
            ['<!DOCTYPE rss><rss><channel/></rss>'],
            ['<!DOCTYPE rss SYSTEM "http://127.0.0.1/feed.dtd"><rss><channel/></rss>'],
            ['<!DOCTYPE rss SYSTEM "file:///etc/passwd"><rss><channel/></rss>'],
            ['<!DOCTYPE rss [<!ENTITY x "expanded">]><rss><channel><item><title>&x;</title><link>https://example.org/a</link></item></channel></rss>'],
            ['<!DOCTYPE rss [<!ENTITY x SYSTEM "file:///etc/passwd">]><rss><channel><item><title>&x;</title><link>https://example.org/a</link></item></channel></rss>'],
            ['<!DOCTYPE rss [<!ENTITY % external SYSTEM "http://127.0.0.1/entity.dtd">%external;]><rss><channel/></rss>'],
        ];
    }

    public function testRejectsExternalEntities(): void
    {
        $this->expectException(\RuntimeException::class);
        (new FeedParser())->parse('<!DOCTYPE rss [<!ENTITY x SYSTEM "file:///etc/passwd">]><rss><channel><item><title>&x;</title></item></channel></rss>', 'https://example.com', 10);
    }
}
