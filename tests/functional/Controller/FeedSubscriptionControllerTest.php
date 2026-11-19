<?php

namespace Wallabag\Tests\Functional\Controller;

use Laminas\Feed\Reader\Entry\EntryInterface;
use Laminas\Feed\Reader\Exception\RuntimeException;
use PHPUnit\Framework\MockObject\MockObject;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Entity\User;
use Wallabag\Helper\FeedReader;
use Wallabag\Tests\Functional\WallabagTestCase;

class FeedSubscriptionControllerTest extends WallabagTestCase
{
    public function testSubscriptionsRequireLogin(): void
    {
        $client = $this->getTestClient();
        $client->request('GET', '/atom/list');

        self::assertTrue($client->getResponse()->isRedirect());
    }

    public function testCreatesAndListsOnlyOwnSubscriptions(): void
    {
        $this->logInAs('admin');
        $em = $this->getEntityManager();
        $bob = $em->getRepository(User::class)->findOneBy(['username' => 'bob']);
        $em->persist((new FeedUrl($bob))->setTitle('Private feed')->setUrl('https://example.org/private'));
        $em->flush();

        $this->mockReader()->expects(self::once())->method('resolve')
            ->with('https://example.org/feed')->willReturn('https://example.org/discovered');
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/atom/new');
        $client->submit($crawler->filter('#feed_save')->form(), [
            'feed[title]' => 'Example feed',
            'feed[url]' => 'https://example.org/feed',
        ]);

        self::assertTrue($client->getResponse()->isRedirect('/atom/list'));
        $crawler = $client->followRedirect();
        self::assertSame(['feeds.flash.created'], $crawler->filter('[data-controller="materialize--toast"]')->each(static fn ($toast) => $toast->text()));
        self::assertCount(1, $crawler->filter('main .card .card-title'));
        self::assertStringContainsString('Example feed', $crawler->filter('main')->text());
        self::assertCount(1, $crawler->filter('a[href="https://example.org/discovered"]'));
        self::assertStringNotContainsString('Private feed', $crawler->filter('main')->text());
    }

    /** @dataProvider invalidSubscriptions */
    public function testRejectsInvalidSubscription(string $title, string $url, string $error): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/atom/new');
        $client->submit($crawler->filter('#feed_save')->form(), [
            'feed[title]' => $title,
            'feed[url]' => $url,
        ]);

        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertStringContainsString($error, $client->getResponse()->getContent());
        self::assertSame(0, $this->getEntityManager()->getRepository(FeedUrl::class)->count([]));
    }

    public function invalidSubscriptions(): iterable
    {
        yield 'credentials' => ['Invalid feed', 'https://user:pass@example.org/feed', 'This value is not a valid URL.'];
        yield 'scheme' => ['Invalid feed', 'ftp://example.org/feed', 'This value is not a valid URL.'];
        yield 'malformed URL' => ['Invalid feed', 'https://', 'This value is not a valid URL.'];
        yield 'blank URL' => ['Invalid feed', '', 'This value should not be blank.'];
        yield 'blank title' => ['', 'https://example.org/feed', 'This value should not be blank.'];
    }

    public function testEditsOnlyOwnSubscription(): void
    {
        $this->logInAs('admin');
        $em = $this->getEntityManager();
        $bob = $em->getRepository(User::class)->findOneBy(['username' => 'bob']);
        $own = (new FeedUrl($this->getLoggedInUser()))->setTitle('Original title')->setUrl('https://example.org/feed');
        $foreign = (new FeedUrl($bob))->setTitle('Foreign feed')->setUrl('https://example.org/bob');
        $em->persist($own);
        $em->persist($foreign);
        $em->flush();

        $client = $this->getTestClient();
        $client->request('GET', '/atom/edit/' . $foreign->getId());
        self::assertSame(404, $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/atom/list');
        $editPath = '/atom/edit/' . $own->getId();
        self::assertCount(1, $crawler->filter('main .card-action a[href="' . $editPath . '"]'));
        self::assertCount(0, $crawler->filter('main .card-action a[href="/atom/edit/' . $foreign->getId() . '"]'));
        $crawler = $client->request('GET', $editPath);
        $client->submit($crawler->filter('#feed_save')->form(), ['feed[title]' => 'Updated title']);
        self::assertTrue($client->getResponse()->isRedirect('/atom/list'));
        self::assertStringContainsString('feeds.flash.updated', $client->followRedirect()->filter('[data-controller="materialize--toast"]')->text());
        self::assertSame('Updated title', $this->getEntityManager()->find(FeedUrl::class, $own->getId())->getTitle());
    }

    public function testEditingDiscoverableWebsiteStoresTheFeedUrl(): void
    {
        $this->logInAs('admin');
        $em = $this->getEntityManager();
        $feed = (new FeedUrl($this->getLoggedInUser()))->setTitle('Original')->setUrl('https://example.org/original.xml');
        $em->persist($feed);
        $em->flush();

        $reader = $this->mockReader();
        $reader->expects(self::never())->method('read');
        $reader->expects(self::once())->method('resolve')
            ->with('https://example.org/blog')->willReturn('https://example.org/blog/rss.xml');
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/atom/edit/' . $feed->getId());
        $client->submit($crawler->filter('#feed_save')->form(), ['feed[url]' => 'https://example.org/blog']);

        self::assertTrue($client->getResponse()->isRedirect('/atom/list'));
        self::assertSame('https://example.org/blog/rss.xml', $this->getEntityManager()->find(FeedUrl::class, $feed->getId())->getUrl());
    }

    public function testKeepsSubmittedUrlWhenDiscoveryFails(): void
    {
        $this->logInAs('admin');
        $reader = $this->mockReader();
        $reader->expects(self::once())->method('resolve')->willThrowException(new RuntimeException('No feed found'));
        $reader->expects(self::once())->method('read')->willThrowException(new RuntimeException('Could not read feed'));
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/atom/new');
        $client->submit($crawler->filter('#feed_save')->form(), [
            'feed[title]' => 'Unavailable feed',
            'feed[url]' => 'https://example.org/missing',
        ]);

        self::assertTrue($client->getResponse()->isRedirect('/atom/list'));
        $feed = $this->getEntityManager()->getRepository(FeedUrl::class)->findOneBy(['url' => 'https://example.org/missing']);
        self::assertNotNull($feed);
        self::assertNull($feed->getUpdatedAt());
        $crawler = $client->followRedirect();
        self::assertSame(['feeds.flash.created_refresh_error'], $crawler->filter('[data-controller="materialize--toast"]')->each(static fn ($toast) => $toast->text()));
    }

    public function testRefreshUsesProtectedPostAndShowsCollectedArticles(): void
    {
        $this->logInAs('admin');
        $feed = (new FeedUrl($this->getLoggedInUser()))->setTitle('My feed')->setUrl('https://example.org/feed');
        $em = $this->getEntityManager();
        $em->persist($feed);
        $em->flush();

        $entry = $this->createMock(EntryInterface::class);
        $entry->method('getLink')->willReturn('https://example.org/story');
        $entry->method('getTitle')->willReturn('Collected story');
        $this->mockReader()->expects(self::once())->method('read')->with($feed->getUrl())->willReturn([$entry]);

        $client = $this->getTestClient();
        $refresh = '/atom/fetch/' . $feed->getId();
        $client->request('GET', $refresh);
        self::assertSame(405, $client->getResponse()->getStatusCode());
        foreach ([[], ['token' => 'invalid'], ['token' => ['unexpected']]] as $post) {
            $client->request('POST', $refresh, $post);
            self::assertSame(400, $client->getResponse()->getStatusCode());
        }

        $crawler = $client->request('GET', '/atom/list');
        $client->submit($crawler->filter(\sprintf('form[action="%s"] button', $refresh))->form());
        self::assertTrue($client->getResponse()->isRedirect('/atom/items/' . $feed->getId()));
        $crawler = $client->followRedirect();
        self::assertStringContainsString('feeds.flash.fetched', $crawler->filter('[data-controller="materialize--toast"]')->text());
        self::assertStringContainsString('Collected story', $crawler->filter('main')->text());
    }

    /** @dataProvider protectedFeedRoutes */
    public function testProtectedFeedRoutesHideForeignResources(string $route, string $method): void
    {
        $this->logInAs('admin');
        $em = $this->getEntityManager();
        $bob = $em->getRepository(User::class)->findOneBy(['username' => 'bob']);
        $feed = (new FeedUrl($bob))->setTitle('Private feed')->setUrl('https://example.org/feed');
        $em->persist($feed);
        $item = new FeedItem($feed, 'https://example.org/article');
        $em->persist($item);
        $em->flush();

        $id = $feed->getId();
        $client = $this->getTestClient();
        $client->request($method, '/atom/' . $route . '/' . $id);

        self::assertSame(404, $client->getResponse()->getStatusCode());
        self::assertNotNull($this->getEntityManager()->find(FeedUrl::class, $feed->getId()));
        self::assertSame(1, $this->getEntityManager()->getRepository(FeedItem::class)->count(['feed' => $feed]));

        if ('POST' === $method && 'edit' !== $route) {
            $this->logInAs('bob');
            $client->request('POST', '/atom/' . $route . '/' . $id, ['token' => ['unexpected']]);

            self::assertSame(400, $client->getResponse()->getStatusCode());
            self::assertNotNull($this->getEntityManager()->find(FeedUrl::class, $feed->getId()));
            self::assertSame(1, $this->getEntityManager()->getRepository(FeedItem::class)->count(['feed' => $feed]));
        }
    }

    public function protectedFeedRoutes(): iterable
    {
        yield 'edit' => ['edit', 'POST'];
        yield 'items' => ['items', 'GET'];
        yield 'fetch' => ['fetch', 'POST'];
    }

    private function mockReader(): FeedReader&MockObject
    {
        $reader = $this->createMock(FeedReader::class);
        $client = $this->getTestClient();
        $client->disableReboot();
        $client->getContainer()->set(FeedReader::class, $reader);

        return $reader;
    }
}
