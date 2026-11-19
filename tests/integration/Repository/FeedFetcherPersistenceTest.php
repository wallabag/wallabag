<?php

namespace Wallabag\Tests\Integration\Repository;

use Laminas\Feed\Reader\Entry\EntryInterface;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Helper\FeedFetcher;
use Wallabag\Helper\FeedReader;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class FeedFetcherPersistenceTest extends WallabagKernelTestCase
{
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
        $reader->method('read')->willReturn($entries);
        $items = $em->getRepository(FeedItem::class);
        $fetcher = new FeedFetcher($em, $items, $reader);

        self::assertSame(10, $fetcher->fetch($feed));
        self::assertCount(10, $items->findPending($feed));
        self::assertSame(1, $fetcher->fetch($feed));
        self::assertSame(0, $fetcher->fetch($feed));
        self::assertCount(11, $items->findPending($feed));
        self::assertNotNull($feed->getUpdatedAt());
    }

    private function entry(string $url): EntryInterface
    {
        $entry = $this->createMock(EntryInterface::class);
        $entry->method('getLink')->willReturn($url);

        return $entry;
    }
}
