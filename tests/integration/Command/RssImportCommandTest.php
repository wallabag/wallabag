<?php

namespace Wallabag\Tests\Integration\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Entity\RssFeed;
use Wallabag\Entity\RssSeenItem;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class RssImportCommandTest extends WallabagKernelTestCase
{
    public function testRssDeduplicationIndexesArePresentInDoctrineMapping(): void
    {
        $em = $this->getEntityManager();

        self::assertArrayHasKey('rss_owner_url', $em->getClassMetadata(RssFeed::class)->table['uniqueConstraints']);
        self::assertArrayHasKey('rss_feed_item', $em->getClassMetadata(RssSeenItem::class)->table['uniqueConstraints']);
    }

    public function testOperatorImportIsIdempotentAndConsentIsExplicit(): void
    {
        $em = $this->getEntityManager();
        $owner = $this->getUser('admin');
        $application = $this->createApplication();
        $tester = new CommandTester($application->find('wallabag:rss:import'));
        $path = tempnam(sys_get_temp_dir(), 'rss-import-');
        file_put_contents($path, json_encode(['feeds' => [['url' => 'https://1.1.1.1/feed', 'name' => 'Synthetic import', 'enabled' => false]], 'seen_items' => ['https://1.1.1.1/feed' => [['url' => 'https://1.1.1.1/story']]]]));
        try {
            self::assertSame(0, $tester->execute(['user-id' => $owner->getId(), 'file' => $path]));
            self::assertFalse($owner->getConfig()->isXArticleReaderEnabled());
            self::assertSame(0, $tester->execute(['user-id' => $owner->getId(), 'file' => $path, '--enable-x-article-reader' => true]));
            self::assertTrue($owner->getConfig()->isXArticleReaderEnabled());
            self::assertCount(1, $em->getRepository(RssFeed::class)->findBy(['user' => $owner]));
            self::assertCount(1, $em->getRepository(RssSeenItem::class)->findAll());
        } finally {
            unlink($path);
        }
    }
}
