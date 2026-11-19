<?php

namespace Wallabag\Tests\Functional\Controller;

use Laminas\Feed\Reader\Entry\EntryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wallabag\Entity\Config;
use Wallabag\Entity\Entry;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Entity\User;
use Wallabag\Event\EntrySavedEvent;
use Wallabag\Helper\ContentProxy;
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

        $this->mockReader()->expects(self::once())->method('read')
            ->with('https://example.org/feed')->willReturn(['url' => 'https://example.org/discovered', 'entries' => []]);
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

    public function testRefreshRecoversAnInitiallyUnavailableWebsite(): void
    {
        $this->logInAs('admin');
        $websiteUrl = 'https://8.8.8.8/blog';
        $feedUrl = 'https://8.8.8.8/rss';
        $page = '<html><head><link rel="alternate" type="application/rss+xml" href="/rss"></head></html>';
        $rss = '<rss version="2.0"><channel><title>Example</title><item><title>Recovered story</title><link>https://example.org/story</link></item></channel></rss>';
        $http = new MockHttpClient([
            new MockResponse('', ['http_code' => 503]),
            new MockResponse($page),
            new MockResponse($rss),
        ]);
        $client = $this->getTestClient();
        $client->disableReboot();
        $client->getContainer()->set(FeedReader::class, new FeedReader('wallabag-test', $http));
        $crawler = $client->request('GET', '/atom/new');
        $client->submit($crawler->filter('#feed_save')->form(), [
            'feed[title]' => 'Unavailable feed',
            'feed[url]' => $websiteUrl,
        ]);

        self::assertTrue($client->getResponse()->isRedirect('/atom/list'));
        $em = $this->getEntityManager();
        $feed = $em->getRepository(FeedUrl::class)->findOneBy(['url' => $websiteUrl]);
        self::assertNotNull($feed);
        self::assertNull($feed->getUpdatedAt());
        self::assertSame(1, $http->getRequestsCount());
        $id = $feed->getId();
        $crawler = $client->followRedirect();
        self::assertSame(['feeds.flash.created_refresh_error'], $crawler->filter('[data-controller="materialize--toast"]')->each(static fn ($toast) => $toast->text()));

        $client->submit($crawler->filter('form[action="/atom/fetch/' . $id . '"] button')->form());
        $crawler = $client->followRedirect();
        self::assertStringContainsString('Recovered story', $crawler->filter('main')->text());
        self::assertSame(3, $http->getRequestsCount());
        $em->clear();
        self::assertSame($feedUrl, $em->find(FeedUrl::class, $id)->getUrl());
        self::assertNotNull($em->find(FeedUrl::class, $id)->getUpdatedAt());
    }

    public function testRefreshUsesProtectedPostAndShowsCollectedArticles(): void
    {
        $this->logInAs('admin');
        $feed = (new FeedUrl($this->getLoggedInUser()))->setTitle('My feed')->setUrl('https://example.org/feed');
        $em = $this->getEntityManager();
        $this->getLoggedInUser()->getConfig()->setDisplayThumbnails(true);
        $em->persist($feed);
        $em->flush();

        $entry = $this->createMock(EntryInterface::class);
        $entry->method('getLink')->willReturn('https://example.org/story');
        $entry->method('getTitle')->willReturn('Collected story');
        $entry->method('getDateCreated')->willReturn(new \DateTime('2025-01-02T12:00:00+00:00'));
        $entry->method('getEnclosure')->willReturn((object) ['href' => 'https://example.org/cover.jpg']);
        $withoutImage = $this->createMock(EntryInterface::class);
        $withoutImage->method('getLink')->willReturn('https://example.org/text-only');
        $this->mockReader()->expects(self::once())->method('read')->with($feed->getUrl())
            ->willReturn(['url' => $feed->getUrl(), 'entries' => [$entry, $withoutImage]]);

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
        self::assertCount(2, $crawler->filter('main .entry-card'));
        self::assertCount(1, $crawler->filter('main .entry-card img[src="https://example.org/cover.jpg"]'));
        self::assertSame('2025-01-02', $crawler->filter('main .entry-card time')->text());
    }

    public function testSwitchesBetweenCardAndListViewsUsingTheGlobalPreference(): void
    {
        $this->logInAs('admin');
        $em = $this->getEntityManager();
        $user = $this->getLoggedInUser();
        $user->getConfig()->setListMode(0)->setDisplayThumbnails(true)->setActionMarkAsRead(Config::REDIRECT_TO_HOMEPAGE);
        $feed = (new FeedUrl($user))->setTitle('Reading')->setUrl('https://example.org/feed');
        $em->persist($feed);
        $em->persist(new FeedItem($feed, 'https://example.org/story', 'Story', new \DateTime('2025-01-02T12:00:00+00:00'), 'https://example.org/cover.jpg'));
        $em->persist(new FeedItem($feed, 'https://example.org/text-only'));
        $em->flush();

        $client = $this->getTestClient();
        $path = '/atom/items/' . $feed->getId();
        $toolbar = 'main .feed-items-toolbar';
        $switch = $toolbar . ' form[action^="/config/view-mode"] button';
        $client->request('POST', '/config/view-mode', ['token' => 'invalid']);
        self::assertSame(400, $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', $path);
        self::assertCount(2, $crawler->filter('main .entry-card'));
        self::assertCount(1, $crawler->filter($toolbar . ' a[href="/atom/list"]'));
        self::assertSame('feeds.action.show_list_view', $crawler->filter($switch)->attr('aria-label'));
        $client->submit($crawler->filter($switch)->form());
        self::assertTrue($client->getResponse()->isRedirect($path));

        $crawler = $client->followRedirect();
        self::assertCount(2, $crawler->filter('main .collection .feed-list-row'));
        self::assertCount(0, $crawler->filter('main .entry-card'));
        self::assertCount(1, $crawler->filter('main .feed-list-image img[src="https://example.org/cover.jpg"]'));
        self::assertCount(1, $crawler->filter('main .feed-list-row time[datetime]'));
        self::assertSame('feeds.action.show_card_view', $crawler->filter($switch)->attr('aria-label'));
        self::assertSame(1, $this->getLoggedInUser()->getConfig()->getListMode());

        $client->submit($crawler->filter($switch)->form());
        self::assertCount(2, $client->followRedirect()->filter('main .entry-card'));
        self::assertSame(0, $this->getLoggedInUser()->getConfig()->getListMode());
    }

    public function testDismissAndPurgeRetainCollectedHistory(): void
    {
        $this->logInAs('admin');
        $em = $this->getEntityManager();
        $this->getLoggedInUser()->getConfig()->setListMode(1);
        $feed = (new FeedUrl($this->getLoggedInUser()))->setTitle('History')->setUrl('https://example.org/feed');
        $publishedAt = new \DateTime('2026-11-19 12:00:00');
        $image = 'https://example.org/cover.jpg';
        $first = new FeedItem($feed, 'https://example.org/dismissed', 'Dismissed article', $publishedAt, $image);
        $second = new FeedItem($feed, 'https://example.org/purged', 'Purged article', $publishedAt, $image);
        $em->persist($feed);
        $em->persist($first);
        $em->persist($second);
        $em->flush();

        $entry = $this->createMock(EntryInterface::class);
        $entry->method('getLink')->willReturn($first->getUrl());
        $this->mockReader()->expects(self::once())->method('read')
            ->willReturn(['url' => $feed->getUrl(), 'entries' => [$entry]]);

        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/atom/items/' . $feed->getId());
        $dismiss = $crawler->filter('form[action="/atom/item/delete/' . $first->getId() . '"] button')->form();
        $client->submit($dismiss);
        $crawler = $client->followRedirect();
        self::assertStringContainsString('feeds.flash.dismissed', $crawler->filter('[data-controller="materialize--toast"]')->text());
        self::assertCount(1, $crawler->filter('main .feed-list-row'));
        self::assertStringContainsString('Purged article', $crawler->filter('main .feed-list-row')->text());

        $client->submit($dismiss);
        self::assertStringContainsString('feeds.flash.already_processed', $client->followRedirect()->filter('[data-controller="materialize--toast"]')->text());

        $crawler = $client->request('GET', '/atom/list');
        $purge = $crawler->filter('form[action="/atom/purge/' . $feed->getId() . '"] button')->form();
        $client->submit($purge);
        self::assertStringContainsString('feeds.flash.purged', $client->followRedirect()->filter('[data-controller="materialize--toast"]')->text());
        $client->submit($purge);
        $crawler = $client->followRedirect();
        self::assertStringContainsString('feeds.flash.no_pending_articles', $crawler->filter('[data-controller="materialize--toast"]')->text());
        $this->submitAction($crawler, '/atom/fetch/' . $feed->getId());
        self::assertCount(0, $client->followRedirect()->filter('main .feed-list-row'));

        $em = $this->getEntityManager();
        $em->clear();
        $history = $em->getRepository(FeedItem::class)->findBy(['feed' => $feed], ['id' => 'ASC']);
        self::assertSame([$first->getUrl(), $second->getUrl()], array_map(static fn (FeedItem $item): string => $item->getUrl(), $history));
        foreach ($history as $item) {
            self::assertTrue($item->isTrashed());
            self::assertSame($publishedAt->getTimestamp(), $item->getPublishedAt()?->getTimestamp());
            self::assertNull($item->getTitle());
            self::assertNull($item->getImage());
        }
    }

    /** @dataProvider viewModes */
    public function testSavingFeedArticlePreservesItsTitleAndSkipsDuplicates(int $mode, string $itemSelector): void
    {
        $this->logInAs('admin');
        $em = $this->getEntityManager();
        $owner = $this->getLoggedInUser();
        $owner->getConfig()->setListMode($mode);
        $feed = (new FeedUrl($owner))->setTitle('Reading')->setUrl('https://example.org/reading');
        $otherFeed = (new FeedUrl($owner))->setTitle('Other source')->setUrl('https://example.org/other');
        $publishedAt = new \DateTime('2020-06-01 12:00:00');
        $first = new FeedItem($feed, 'https://example.org/story', 'Title from feed', $publishedAt);
        $duplicate = new FeedItem($otherFeed, $first->getUrl(), 'Same story', new \DateTime('2021-01-01 12:00:00'));
        $em->persist($feed);
        $em->persist($otherFeed);
        $em->persist($first);
        $em->persist($duplicate);
        $em->flush();

        $client = $this->getTestClient();
        $client->disableReboot();
        $content = $this->createMock(ContentProxy::class);
        $content->expects(self::once())->method('updateEntry')->willReturnCallback(static function (Entry $entry): void {
            $entry->setPublishedAt(new \DateTime('2021-06-01 12:00:00'));

            throw new \RuntimeException('Article unavailable');
        });
        $client->getContainer()->set(ContentProxy::class, $content);
        $savedId = null;
        $client->getContainer()->get('event_dispatcher')->addListener(EntrySavedEvent::NAME, static function (EntrySavedEvent $event) use (&$savedId): void {
            $savedId = $event->getEntry()->getId();
        });

        $crawler = $client->request('GET', '/atom/items/' . $feed->getId());
        $save = $crawler->filter('form[action="/atom/item/save/' . $first->getId() . '"] button')->form();
        $client->submit($save);
        $crawler = $client->followRedirect();
        self::assertStringContainsString('feeds.flash.saved', $crawler->filter('[data-controller="materialize--toast"]')->text());
        self::assertCount(0, $crawler->filter($itemSelector));

        $client->submit($save);
        self::assertStringContainsString('feeds.flash.already_processed', $client->followRedirect()->filter('[data-controller="materialize--toast"]')->text());

        $crawler = $client->request('GET', '/atom/items/' . $otherFeed->getId());
        $this->submitAction($crawler, '/atom/item/save/' . $duplicate->getId());
        $crawler = $client->followRedirect();
        self::assertStringContainsString('feeds.flash.saved', $crawler->filter('[data-controller="materialize--toast"]')->text());
        self::assertCount(0, $crawler->filter($itemSelector));

        $entries = $em->getRepository(Entry::class)->findBy(['url' => $first->getUrl(), 'user' => $owner]);
        self::assertCount(1, $entries);
        self::assertSame('Title from feed', $entries[0]->getTitle());
        self::assertSame($publishedAt->getTimestamp(), $entries[0]->getPublishedAt()->getTimestamp());
        self::assertNotSame($publishedAt->getTimestamp(), $entries[0]->getCreatedAt()->getTimestamp());
        self::assertSame($entries[0]->getId(), $savedId);
    }

    public function testDeletingSubscriptionRemovesHistoryAndRespectsOwnership(): void
    {
        $this->logInAs('admin');
        $em = $this->getEntityManager();
        $bob = $em->getRepository(User::class)->findOneBy(['username' => 'bob']);
        $feed = (new FeedUrl($this->getLoggedInUser()))->setTitle('My feed')->setUrl('https://example.org/feed');
        $foreign = (new FeedUrl($bob))->setTitle('Private feed')->setUrl('https://example.org/bob');
        $item = new FeedItem($feed, 'https://example.org/article');
        $em->persist($feed);
        $em->persist($foreign);
        $em->persist($item);
        $em->flush();

        $client = $this->getTestClient();
        $client->request('POST', '/atom/delete/' . $foreign->getId(), ['token' => 'invalid']);
        self::assertSame(404, $client->getResponse()->getStatusCode());
        $crawler = $client->request('GET', '/atom/list');
        $this->submitAction($crawler, '/atom/delete/' . $feed->getId());
        self::assertTrue($client->getResponse()->isRedirect('/atom/list'));
        self::assertStringContainsString('feeds.flash.deleted', $client->followRedirect()->filter('[data-controller="materialize--toast"]')->text());
        $em = $this->getEntityManager();
        self::assertNull($em->find(FeedUrl::class, $feed->getId()));
        self::assertNull($em->find(FeedItem::class, $item->getId()));
        self::assertNotNull($em->find(FeedUrl::class, $foreign->getId()));
    }

    public function testRejectedImportLeavesTheArticlePending(): void
    {
        $this->logInAs('admin');
        $em = $this->getEntityManager();
        $feed = (new FeedUrl($this->getLoggedInUser()))->setTitle('Reading')->setUrl('https://example.org/feed');
        $item = new FeedItem($feed, 'ftp://example.org/article');
        $em->persist($feed);
        $em->persist($item);
        $em->flush();

        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/atom/items/' . $feed->getId());
        $this->submitAction($crawler, '/atom/item/save/' . $item->getId());
        $crawler = $client->followRedirect();
        self::assertSame('feeds.flash.save_error', $crawler->filter('[data-controller="materialize--toast"]')->text());
        self::assertCount(1, $crawler->filter('main .entry-card'));
        self::assertFalse($this->getEntityManager()->find(FeedItem::class, $item->getId())->isTrashed());
    }

    public function viewModes(): iterable
    {
        yield 'cards' => [0, 'main .entry-card'];
        yield 'list' => [1, 'main .feed-list-row'];
    }

    /** @dataProvider protectedFeedRoutes */
    public function testProtectedFeedRoutesHideForeignResources(string $route, bool $isItem, string $method): void
    {
        $this->logInAs('admin');
        $em = $this->getEntityManager();
        $bob = $em->getRepository(User::class)->findOneBy(['username' => 'bob']);
        $feed = (new FeedUrl($bob))->setTitle('Private feed')->setUrl('https://example.org/feed');
        $em->persist($feed);
        $item = new FeedItem($feed, 'https://example.org/article');
        $em->persist($item);
        $em->flush();

        $id = $isItem ? $item->getId() : $feed->getId();
        $client = $this->getTestClient();
        $client->request($method, '/atom/' . $route . '/' . $id);

        self::assertSame(404, $client->getResponse()->getStatusCode());
        self::assertNotNull($this->getEntityManager()->find(FeedUrl::class, $feed->getId()));
        self::assertFalse($this->getEntityManager()->find(FeedItem::class, $item->getId())->isTrashed());

        if ('POST' === $method && 'edit' !== $route) {
            $this->logInAs('bob');
            $client->request('POST', '/atom/' . $route . '/' . $id, ['token' => ['unexpected']]);

            self::assertSame(400, $client->getResponse()->getStatusCode());
            self::assertNotNull($this->getEntityManager()->find(FeedUrl::class, $feed->getId()));
            self::assertFalse($this->getEntityManager()->find(FeedItem::class, $item->getId())->isTrashed());
        }
    }

    public function protectedFeedRoutes(): iterable
    {
        yield 'edit' => ['edit', false, 'POST'];
        yield 'items' => ['items', false, 'GET'];
        yield 'fetch' => ['fetch', false, 'POST'];
        yield 'purge' => ['purge', false, 'POST'];
        yield 'item/delete' => ['item/delete', true, 'POST'];
        yield 'item/save' => ['item/save', true, 'POST'];
        yield 'delete' => ['delete', false, 'POST'];
    }

    private function submitAction(Crawler $crawler, string $path): void
    {
        $this->getTestClient()->submit($crawler->filter(\sprintf('form[action="%s"] button', $path))->form());
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
