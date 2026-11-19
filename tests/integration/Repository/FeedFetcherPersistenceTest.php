<?php

namespace Wallabag\Tests\Integration\Repository;

use Craue\ConfigBundle\Util\Config;
use Laminas\Feed\Reader\Entry\EntryInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\FeedFetcher;
use Wallabag\Helper\FeedReader;
use Wallabag\Import\FeedsImport;
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
        $fetcher = $this->createFetcher($reader);

        self::assertSame(10, $fetcher->fetch($feed));
        self::assertCount(10, $items->findPending($feed));
        self::assertSame(1, $fetcher->fetch($feed));
        self::assertSame(0, $fetcher->fetch($feed));
        self::assertCount(11, $items->findPending($feed));
        self::assertNotNull($feed->getUpdatedAt());
    }

    public function testAutomaticCollectionSavesArticleBeforeRecordingItsHistory(): void
    {
        $em = $this->getEntityManager();
        $owner = $this->getUser('admin');
        $feed = (new FeedUrl($owner))->setTitle('Automatic collection')->setUrl('https://example.org/automatic')->setAutomatic(true);
        $em->persist($feed);
        $em->flush();

        $url = 'https://example.org/automatic-article';
        $reader = $this->createMock(FeedReader::class);
        $reader->method('read')->willReturn([$this->entry($url)]);
        static::getContainer()->set(ContentProxy::class, $this->createMock(ContentProxy::class));
        $items = $em->getRepository(FeedItem::class);
        $fetcher = $this->createFetcher($reader);

        self::assertSame(1, $fetcher->fetch($feed));
        self::assertSame(0, $fetcher->fetch($feed));
        self::assertNotNull($em->getRepository(Entry::class)->findOneBy(['user' => $owner, 'url' => $url]));
        self::assertCount(0, $items->findPending($feed));
    }

    private function createFetcher(FeedReader $reader): FeedFetcher
    {
        $em = $this->getEntityManager();

        return new FeedFetcher(
            $em,
            $em->getRepository(FeedItem::class),
            $reader,
            static::getContainer()->get(FeedsImport::class),
            $this->createMock(Config::class),
            $this->createMock(ProducerInterface::class),
            $this->createMock(ProducerInterface::class),
        );
    }

    private function entry(string $url): EntryInterface
    {
        $entry = $this->createMock(EntryInterface::class);
        $entry->method('getLink')->willReturn($url);

        return $entry;
    }
}
