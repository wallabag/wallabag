<?php

namespace Wallabag\Tests\Functional\Controller;

use Wallabag\Tests\Functional\WallabagTestCase;

class RssControllerTest extends WallabagTestCase
{
    public function testUiAndCsrfProtection(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->request('GET', '/rss/subscriptions');
        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertStringContainsString('RSS subscriptions', $client->getResponse()->getContent());
        $client->request('POST', '/rss/subscriptions', ['url' => 'https://1.1.1.1/feed', 'token' => 'invalid']);
        self::assertSame(403, $client->getResponse()->getStatusCode());
    }

    public function testAnonymousCannotManageFeeds(): void
    {
        $client = $this->getTestClient();
        $client->request('GET', '/rss/subscriptions');
        self::assertSame(302, $client->getResponse()->getStatusCode());
        $client->request('GET', '/api/rss/feeds.json');
        self::assertContains($client->getResponse()->getStatusCode(), [401, 403]);
    }

    public function testXReaderPrivacyPreferenceCanBeChangedInSettings(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $crawler = $client->request('GET', '/config');
        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertCount(1, $crawler->filter('input[name="config[x_article_reader_enabled]"]'));
        $form = $crawler->filter('button[id=config_save]')->form();
        $client->submit($form, ['config[x_article_reader_enabled]' => '1']);
        self::assertSame(302, $client->getResponse()->getStatusCode());
        self::assertTrue($this->getLoggedInUser()->getConfig()->isXArticleReaderEnabled());
    }
}
