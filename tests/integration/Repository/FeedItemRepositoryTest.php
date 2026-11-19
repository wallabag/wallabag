<?php

namespace Wallabag\Tests\Integration\Repository;

use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Entity\User;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class FeedItemRepositoryTest extends WallabagKernelTestCase
{
    public function testDeletingAnAccountRemovesItsSubscriptionsAndHistory(): void
    {
        $em = $this->getEntityManager();
        $owner = new User();
        $owner->setUsername('feed-owner');
        $owner->setEmail('feed-owner@example.org');
        $owner->setPassword('test');
        $owner->setEnabled(true);
        $feed = (new FeedUrl($owner))->setTitle('Reading')->setUrl('https://example.org/feed');
        $item = new FeedItem($feed, 'https://example.org/article');
        $em->persist($owner);
        $em->persist($feed);
        $em->persist($item);
        $em->flush();
        $em->clear();

        $em->remove($em->find(User::class, $owner->getId()));
        $em->flush();

        self::assertNull($em->find(FeedUrl::class, $feed->getId()));
        self::assertNull($em->find(FeedItem::class, $item->getId()));
    }

    public function testPruningKeepsRecentAndStillPublishedItems(): void
    {
        $em = $this->getEntityManager();
        $feed = (new FeedUrl($this->getUser('admin')))->setTitle('Reading')->setUrl('https://example.org/feed');
        $other = (new FeedUrl($this->getUser('admin')))->setTitle('Other')->setUrl('https://example.org/other');
        $stillPublished = new FeedItem($feed, 'https://example.org/still-published');
        $expired = new FeedItem($feed, 'https://example.org/expired');
        $otherArticle = new FeedItem($other, $expired->getUrl());
        $recent = new FeedItem($feed, 'https://example.org/recent');
        $newest = new FeedItem($feed, 'https://example.org/newest');

        $em->persist($feed);
        $em->persist($other);
        $em->persist($stillPublished);
        $em->persist($expired);
        $em->persist($otherArticle);
        $em->persist($recent);
        $em->persist($newest);
        $em->flush();

        $repository = $em->getRepository(FeedItem::class);
        self::assertSame(1, $repository->prune($feed, [hash('sha256', $stillPublished->getUrl())], 2));
        $em->clear();

        self::assertNull($em->find(FeedItem::class, $expired->getId()));
        self::assertNotNull($em->find(FeedItem::class, $stillPublished->getId()));
        self::assertNotNull($em->find(FeedItem::class, $recent->getId()));
        self::assertNotNull($em->find(FeedItem::class, $newest->getId()));
        self::assertNotNull($em->find(FeedItem::class, $otherArticle->getId()));
    }
}
