<?php

namespace Wallabag\Tests\Integration\Repository;

use Laminas\Feed\Reader\Entry\EntryInterface;
use Laminas\Feed\Reader\Exception\ExceptionInterface as ReaderException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Helper\FeedFetcher;
use Wallabag\Helper\FeedReader;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class FeedFetcherPersistenceTest extends WallabagKernelTestCase
{
    public function testDiscoveryKeepsSubscriptionIdentityAndArticleHistory(): void
    {
        $em = $this->getEntityManager();
        $website = 'https://8.8.8.8/blog';
        $feed = (new FeedUrl($this->getUser('admin')))->setTitle('Reading')->setUrl($website);
        $feed->markRefreshed();
        $lastRefresh = $feed->getUpdatedAt()->getTimestamp();
        $oldPending = new FeedItem($feed, 'https://example.org/old');
        $dismissed = new FeedItem($feed, 'https://example.org/dismissed');
        $dismissed->dismiss();
        $em->persist($feed);
        $em->persist($oldPending);
        $em->persist($dismissed);
        for ($index = 0; $index < 200; ++$index) {
            $item = new FeedItem($feed, 'https://example.org/history/' . $index);
            $item->dismiss();
            $em->persist($item);
        }
        $em->flush();
        $feedId = $feed->getId();
        $pendingId = $oldPending->getId();
        $dismissedId = $dismissed->getId();

        $page = '<html><head><link rel="alternate" type="application/rss+xml" href="/rss"></head></html>';
        $rss = <<<'XML'
            <rss version="2.0"><channel><title>Reading</title>
                <item><link>https://example.org/dismissed</link><title>Already dismissed</title></item>
                <item><link>https://example.org/new</link><title>New story</title></item>
            </channel></rss>
            XML;
        $reader = new FeedReader('wallabag-test', new MockHttpClient([
            new MockResponse($page), new MockResponse($page),
            new MockResponse($page), new MockResponse($rss),
        ]));
        $items = $em->getRepository(FeedItem::class);
        $fetcher = new FeedFetcher($em, $items, $reader);

        try {
            $fetcher->fetch($feed);
            self::fail('Expected the invalid candidate to fail discovery.');
        } catch (ReaderException) {
            $em->clear();
            $feed = $em->find(FeedUrl::class, $feedId);
            self::assertSame($website, $feed->getUrl());
            self::assertSame($lastRefresh, $feed->getUpdatedAt()->getTimestamp());
            self::assertSame(202, $items->count(['feed' => $feed]));
        }

        self::assertSame(1, $fetcher->fetch($feed));
        $em->clear();
        $feed = $em->find(FeedUrl::class, $feedId);
        self::assertSame('https://8.8.8.8/rss', $feed->getUrl());
        self::assertSame(203, $items->count(['feed' => $feed]));
        self::assertFalse($items->find($pendingId)->isTrashed());
        self::assertTrue($items->find($dismissedId)->isTrashed());
    }

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
