<?php

namespace Wallabag\Tests\Functional\Controller;

use Laminas\Feed\Reader\Exception\RuntimeException;
use PHPUnit\Framework\MockObject\MockObject;
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

        $this->mockReader()->expects(self::once())->method('resolve')
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
        $this->mockReader()->expects(self::once())->method('resolve')->willThrowException(new RuntimeException('No feed found'));
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/atom/new');
        $client->submit($crawler->filter('#feed_save')->form(), [
            'feed[title]' => 'Unavailable feed',
            'feed[url]' => 'https://example.org/missing',
        ]);

        self::assertTrue($client->getResponse()->isRedirect('/atom/list'));
        self::assertNotNull($this->getEntityManager()->getRepository(FeedUrl::class)->findOneBy(['url' => 'https://example.org/missing']));
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
