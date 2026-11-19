<?php

namespace Wallabag\Tests\Integration\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Wallabag\Entity\FeedUrl;
use Wallabag\Helper\FeedFetcher;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class FetchRssCommandTest extends WallabagKernelTestCase
{
    public function testRefreshesSubscriptionsForAllUsersInIdOrder(): void
    {
        $em = $this->getEntityManager();
        $first = (new FeedUrl($this->getUser('bob')))->setTitle('First feed')->setUrl('https://example.org/first');
        $second = (new FeedUrl($this->getUser('admin')))->setTitle('Second feed')->setUrl('https://example.org/second');
        $em->persist($first);
        $em->persist($second);
        $em->flush();
        $expectedIds = [$first->getId(), $second->getId()];
        $em->clear();

        $refreshedIds = [];
        $fetcher = $this->createMock(FeedFetcher::class);
        $fetcher->method('fetch')->willReturnCallback(static function (FeedUrl $feed) use (&$refreshedIds): int {
            $refreshedIds[] = $feed->getId();

            return 0;
        });
        static::getContainer()->set(FeedFetcher::class, $fetcher);
        $tester = new CommandTester($this->createApplication()->find('wallabag:fetch-rss'));

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertSame($expectedIds, $refreshedIds);
    }
}
