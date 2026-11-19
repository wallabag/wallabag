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
}
