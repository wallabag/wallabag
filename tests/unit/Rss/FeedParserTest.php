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

    public function testRejectsExternalEntities(): void
    {
        $this->expectException(\RuntimeException::class);
        (new FeedParser())->parse('<!DOCTYPE rss [<!ENTITY x SYSTEM "file:///etc/passwd">]><rss><channel><item><title>&x;</title></item></channel></rss>', 'https://example.com', 10);
    }
}
