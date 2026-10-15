<?php

namespace Wallabag\Tests\Functional\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Wallabag\Entity\RssFeed;
use Wallabag\Entity\User;

class RssRestControllerTest extends WallabagApiTestCase
{
    public function testFeedLifecycleAndPrivateUrlRejection(): void
    {
        $this->client->request('POST', '/api/rss/feeds.json', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['url' => 'http://127.0.0.1/feed']));
        self::assertSame(400, $this->client->getResponse()->getStatusCode());
        // Literal public IP keeps this test completely offline.
        $this->client->request('POST', '/api/rss/feeds.json', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['url' => 'https://1.1.1.1/feed', 'name' => 'Example', 'max_items' => 3]));
        self::assertSame(201, $this->client->getResponse()->getStatusCode());
        $feed = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(3, $feed['max_items']);
        $id = $feed['id'];
        $this->client->request('PATCH', '/api/rss/feeds/' . $id . '.json', [], [], ['CONTENT_TYPE' => 'application/json'], '{"enabled":false}');
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        self::assertFalse(json_decode($this->client->getResponse()->getContent(), true)['enabled']);
        $this->client->request('DELETE', '/api/rss/feeds/' . $id . '.json');
        self::assertSame(204, $this->client->getResponse()->getStatusCode());
    }

    public function testOtherUserFeedIsNotReadableOrMutable(): void
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $other = $em->getRepository(User::class)->findOneByUsername('bob');
        $feed = new RssFeed($other, 'https://1.1.1.1/private');
        $em->persist($feed); $em->flush();
        foreach (['GET', 'PATCH', 'DELETE'] as $method) {
            $this->client->request($method, '/api/rss/feeds/' . $feed->id . '.json', [], [], ['CONTENT_TYPE' => 'application/json'], '{"enabled":false}');
            self::assertSame(404, $this->client->getResponse()->getStatusCode());
        }
    }

    public function testImportIsIdempotentAndPreservesSeen(): void
    {
        $body = json_encode(['feeds' => [['url' => 'https://1.1.1.1/legacy', 'enabled' => false]], 'seen_items' => ['https://1.1.1.1/legacy' => [['url' => '/one#fragment']]]]);
        for ($i = 0; $i < 2; ++$i) {
            $this->client->request('POST', '/api/rss/import.json', [], [], ['CONTENT_TYPE' => 'application/json'], $body);
            self::assertSame(200, $this->client->getResponse()->getStatusCode());
        }
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $feed = $em->getRepository(RssFeed::class)->findOneBy(['user' => $this->user, 'urlHash' => hash('sha256', 'https://1.1.1.1/legacy')]);
        self::assertNotNull($feed);
        self::assertFalse($feed->enabled);
        self::assertCount(1, $em->getRepository(\Wallabag\Entity\RssSeenItem::class)->findBy(['feed' => $feed]));
    }
}
