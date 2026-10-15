<?php

namespace Wallabag\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Wallabag\Entity\Config;
use Wallabag\Helper\SpecialGrabbers;
use Wallabag\Entity\User;

class SpecialGrabbersTest extends TestCase
{
    /** @dataProvider urls */
    public function testMediumMatchesCanonicalStoryHostsAndPaths(string $url, bool $expected): void
    {
        self::assertSame($expected, SpecialGrabbers::mediumUrl($url));
    }

    public function urls(): array
    {
        return [
            ['https://medium.com/@author/story-abcdef012345', true],
            ['https://publication.medium.com/story-abcdef012345?source=rss#section', true],
            ['https://medium.com/p/abcdef012345', true],
            ['https://notmedium.com/story-abcdef012345', false],
            ['https://medium.com.evil.example/story-abcdef012345', false],
            ['https://evil.example/medium.com/story-abcdef012345', false],
            ['https://medium.com@evil.example/story-abcdef012345', false],
            ['https://author:password@medium.com/story-abcdef012345', false],
            ['https://medium.com:443/story-abcdef012345', false],
            ['https://medium.com/search', false],
            ['file://medium.com/p/abcdef012345', false],
        ];
    }

    public function testFixedSettingsAreScopedToConfigAndRejectExecutableConfiguration(): void
    {
        $one = new Config(new User());
        $two = new Config(new User());
        $grabbers = new SpecialGrabbers();
        $grabbers->update($one, 'medium', ['enabled' => false]);
        $grabbers->update($one, 'x', ['enabled' => true, 'article_reader_enabled' => true]);
        self::assertFalse($one->isMediumGrabberEnabled());
        self::assertTrue($two->isMediumGrabberEnabled());
        self::assertFalse($two->isXArticleReaderEnabled());
        self::assertSame([10, 20], array_column($grabbers->list($one), 'priority'));
        $this->expectException(\InvalidArgumentException::class);
        $grabbers->update($one, 'medium', ['enabled' => true, 'regex' => '.*']);
    }
}
