<?php

namespace Wallabag\Tests\Unit\MailImport;

use PHPUnit\Framework\TestCase;
use Wallabag\MailImport\MessageParser;

class MessageParserTest extends TestCase
{
    public function testToken(): void
    {
        $parser = new MessageParser();

        self::assertSame('abc123', $parser->getToken('wallabag@example.org', ['someone@example.org', 'Wallabag+ABC123@Example.org']));
        self::assertNull($parser->getToken('wallabag@example.org', ['wallabag@example.org', 'wallabag+abc123@example.com', 'other+abc123@example.org']));
    }

    public function testUrlsFromText(): void
    {
        $text = "(https://example.org/a?x=1&y=2#top).\nhttps://example.org/a?x=1&y=2#top\nhttps://example.org/wiki/Test_(example)\nnot-a-url";

        self::assertSame(
            ['https://example.org/a?x=1&y=2#top', 'https://example.org/wiki/Test_(example)'],
            (new MessageParser())->getUrls($text, ''),
        );
    }

    public function testUrlsFromHtmlWhenTextHasNone(): void
    {
        $html = '<a href="https://example.org/article?x=1&amp;y=2">Read</a><a href="mailto:person@example.org">Mail</a>';

        self::assertSame(['https://example.org/article?x=1&y=2'], (new MessageParser())->getUrls('Read this', $html));
        self::assertSame(['https://example.org/text'], (new MessageParser())->getUrls('https://example.org/text', $html));
    }
}
