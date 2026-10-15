<?php

namespace Wallabag\Tests\Functional\Rss;

use Wallabag\Tests\Functional\WallabagTestCase;
use Wallabag\Entity\Entry;
use Wallabag\Entity\RssFeed;
use Wallabag\Entity\RssSeenItem;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\PublicHttpClient;
use Wallabag\Helper\TagsAssigner;
use Wallabag\Rss\FeedImporter;
use Wallabag\Rss\FeedManager;
use Wallabag\Rss\FeedParser;
use Wallabag\Entity\User;

class FeedImporterTest extends WallabagTestCase
{
    public function testAtomicClaimAndSuccessfulDeduplication(): void
    {
        $feed = $this->feed();
        $importer = $this->importer();
        self::assertTrue($importer->run($feed));
        self::assertSame(1, $feed->importedCount);
        self::assertNull($feed->lastError);
        self::assertFalse($importer->run($feed));
        $feed->nextFetchAt = new \DateTime('-1 minute');
        $this->getEntityManager()->flush();
        self::assertTrue($importer->run($feed));
        self::assertSame(1, $feed->importedCount);
        self::assertCount(1, $this->getEntityManager()->getRepository(RssSeenItem::class)->findBy(['feed' => $feed]));
    }

    public function testFailureVisibleAndRetryable(): void
    {
        $feed = $this->feed();
        self::assertTrue($this->importer(true)->run($feed));
        self::assertStringContainsString('Source unavailable', $feed->lastError);
        self::assertCount(0, $this->getEntityManager()->getRepository(RssSeenItem::class)->findBy(['feed' => $feed]));
        $feed->nextFetchAt = new \DateTime('-1 minute');
        $this->getEntityManager()->flush();
        self::assertTrue($this->importer()->run($feed));
        self::assertNull($feed->lastError);
        self::assertSame(1, $feed->importedCount);
    }

    public function testDisabledFeedCannotBeClaimed(): void
    {
        $feed = $this->feed();
        $feed->enabled = false;
        $this->getEntityManager()->flush();
        self::assertFalse($this->importer()->run($feed));
        self::assertNull($feed->lastFetchAt);
    }

    public function testExistingOwnedEntryIsNotOverwritten(): void
    {
        $feed = $this->feed();
        $entry = new Entry($feed->user);
        $entry->setUrl('https://twitter.com/example/status/9876543210123');
        $entry->setTitle('Keep me');
        $entry->setContent('Original');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
        self::assertTrue($this->importer()->run($feed));
        self::assertSame(0, $feed->importedCount);
        self::assertSame('Original', $entry->getContent());
        self::assertCount(1, $this->getEntityManager()->getRepository(RssSeenItem::class)->findBy(['feed' => $feed]));
    }

    public function testLegacyImportRollsBackAllRowsOnInvalidSecondFeed(): void
    {
        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneByUsername('admin');
        $before = \count($em->getRepository(RssFeed::class)->findBy(['user' => $user]));
        $manager = new FeedManager($em, new PublicHttpClient());
        try {
            $manager->import($user, ['feeds' => [['url' => 'https://1.1.1.1/valid'], ['url' => 'http://127.0.0.1/blocked']]]);
            self::fail('Import must fail atomically.');
        } catch (\InvalidArgumentException $e) {
            self::assertCount($before, $em->getRepository(RssFeed::class)->findBy(['user' => $user]));
        }
    }

    public function testXVariantDeduplicationNeverReusesAnotherUsersEntry(): void
    {
        $feed = $this->feed();
        $em = $this->getEntityManager();
        $other = $em->getRepository(User::class)->findOneByUsername('bob');
        $foreign = new Entry($other);
        $foreign->setUrl('https://twitter.com/example/status/9876543210123');
        $foreign->setTitle('Other account');
        $foreign->setContent('Other account content');
        $em->persist($foreign);
        $em->flush();
        self::assertTrue($this->importer()->run($feed));
        self::assertSame(1, $feed->importedCount);
        $own = $em->getRepository(Entry::class)->findByUrlAndUserId('https://x.com/example/status/9876543210123', $feed->user->getId());
        self::assertNotSame($foreign->getId(), $own->getId());
        self::assertSame($feed->user->getId(), $own->getUser()->getId());
    }

    private function feed(): RssFeed
    {
        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneByUsername('admin');
        $feed = new RssFeed($user, 'https://1.1.1.1/test-feed');
        $em->persist($feed);
        $em->flush();

        return $feed;
    }

    private function importer(bool $fail = false): FeedImporter
    {
        $em = $this->getEntityManager();
        $http = $this->createMock(PublicHttpClient::class);
        $http->method('validate')->willReturn(['1.1.1.1']);
        $http->method('get')->willReturn(['url' => 'https://1.1.1.1/test-feed', 'status' => 200, 'body' => '<rss><channel><item><link>https://x.com/example/status/9876543210123#fragment</link><category>News</category></item></channel></rss>']);
        $proxy = $this->createMock(ContentProxy::class);
        $proxy->method('updateEntry')->willReturnCallback(static function (Entry $entry, string $url) use ($fail): void {
            if ($fail) {
                throw new \RuntimeException('Source unavailable.');
            }
            $entry->setUrl($url);
            $entry->setTitle('Partial capture');
            $entry->setContent('<p>Partial X capture.</p>');
            $entry->setHttpStatus(206);
        });

        return new FeedImporter($em, $http, new FeedParser(), $proxy, $this->getTestClient()->getContainer()->get(TagsAssigner::class), $em->getRepository(Entry::class));
    }
}
