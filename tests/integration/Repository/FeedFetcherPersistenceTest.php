<?php

namespace Wallabag\Tests\Integration\Repository;

use Laminas\Feed\Reader\Entry\EntryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Helper\FeedFetcher;
use Wallabag\Helper\FeedReader;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class FeedFetcherPersistenceTest extends WallabagKernelTestCase
{
    public function testStoresOnlyImageEnclosures(): void
    {
        $em = $this->getEntityManager();
        $feed = (new FeedUrl($this->getUser('admin')))->setTitle('Images')->setUrl('https://example.org/feed');
        $em->persist($feed);
        $em->flush();

        $cases = [
            [['url' => 'https://example.org/preview', 'type' => 'Image/JPEG; charset=binary'], true],
            [['href' => 'https://example.org/photo.WEBP?size=64'], true],
            [['url' => 'https://example.org/photo.png', 'type' => 'application/octet-stream'], true],
            [['url' => 'https://example.org/audio.jpg', 'type' => 'audio/mpeg'], false],
            [['url' => 'https://example.org/video.jpg', 'type' => 'video/mp4'], false],
            [['url' => 'https://example.org/download?file=photo.jpg'], false],
            [['url' => 'https://example.org/audio.ogg'], false],
            [['url' => 'ftp://example.org/photo.jpg', 'type' => 'image/jpeg'], false],
        ];
        $entries = [];
        foreach ($cases as $index => [$enclosure]) {
            $entry = $this->entry('https://example.org/article/' . $index);
            $entry->method('getEnclosure')->willReturn((object) $enclosure);
            $entries[] = $entry;
        }
        $reader = $this->createMock(FeedReader::class);
        $reader->method('read')->willReturn(['url' => $feed->getUrl(), 'entries' => $entries]);
        $items = $em->getRepository(FeedItem::class);

        self::assertSame(\count($cases), (new FeedFetcher($em, $items, $reader))->fetch($feed));
        foreach ($cases as $index => [$enclosure, $isImage]) {
            self::assertSame(1, $items->count([
                'feed' => $feed,
                'url' => 'https://example.org/article/' . $index,
                'image' => $isImage ? ($enclosure['url'] ?? $enclosure['href']) : null,
            ]));
        }
    }

    public function testRefreshStoresAtMostTenNewArticlesAndRemembersTheirUrls(): void
    {
        $em = $this->getEntityManager();
        $feed = (new FeedUrl($this->getUser('admin')))->setTitle('Collection')->setUrl('https://example.org/feed');
        $em->persist($feed);
        $em->flush();

        $entries = [$this->entry('https://example.org/1'), $this->entry('ftp://example.org/invalid')];
        for ($index = 1; $index <= 11; ++$index) {
            $entries[] = $this->entry('https://example.org/' . $index);
        }

        $reader = $this->createMock(FeedReader::class);
        $reader->method('read')->willReturn(['url' => $feed->getUrl(), 'entries' => $entries]);
        $items = $em->getRepository(FeedItem::class);
        $fetcher = new FeedFetcher($em, $items, $reader);

        self::assertSame(10, $fetcher->fetch($feed));
        self::assertCount(10, $items->findPending($feed));
        self::assertSame(1, $fetcher->fetch($feed));
        self::assertSame(0, $fetcher->fetch($feed));
        self::assertCount(11, $items->findPending($feed));
        self::assertNotNull($feed->getUpdatedAt());
    }

    private function entry(string $url): EntryInterface&MockObject
    {
        $entry = $this->createMock(EntryInterface::class);
        $entry->method('getLink')->willReturn($url);

        return $entry;
    }
}
