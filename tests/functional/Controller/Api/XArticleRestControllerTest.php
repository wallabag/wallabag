<?php

namespace Wallabag\Tests\Functional\Controller\Api;

use Wallabag\Tests\Unit\Helper\FxArticleReaderTest;
use Wallabag\Entity\Entry;
use Wallabag\Helper\PublicHttpClient;
use Wallabag\Entity\User;

class XArticleRestControllerTest extends RssApiTestCase
{
    public function testAuthenticatedOptInCapturesBodyAndDoesNotChangeOtherUsers(): void
    {
        $this->client->disableReboot();
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::exactly(2))->method('get')->willReturnCallback(static function (string $url): array {
            if ('https://api.fxtwitter.com/2/status/12345' === $url) {
                return ['body' => json_encode(['code' => 200, 'status' => FxArticleReaderTest::imagePost()])];
            }
            if (0 === strpos($url, 'https://publish.twitter.com/oembed?')) {
                return ['body' => '{"author_name":"Author","html":"<p>A partial embed</p>"}'];
            }
            throw new \RuntimeException('Unexpected provider request.');
        });
        $this->client->getContainer()->set(PublicHttpClient::class, $http);
        $this->client->request('PATCH', '/api/config.json', [], [], ['CONTENT_TYPE' => 'application/json'], '{"x_article_reader_enabled":true}');
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->client->request('POST', '/api/entries.json', ['url' => 'https://x.com/person/status/12345']);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $entry = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Synthetic article', $entry['title']);
        self::assertSame(200, (int) $entry['http_status']);
        self::assertStringContainsString('The ending paragraph.', $entry['content']);
        self::assertStringNotContainsString('<script>', $entry['content']);
        $doc = new \DOMDocument();
        $doc->loadHTML($entry['content']);
        self::assertSame(2, $doc->getElementsByTagName('img')->length);
        self::assertSame('https://pbs.twimg.com/media/Synthetic_1.png', $doc->getElementsByTagName('img')->item(0)->getAttribute('src'));
        $this->logInAs('admin');
        $this->client->request('GET', '/view/' . $entry['id']);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        self::assertSame(2, $this->client->getCrawler()->filter('img[src="https://pbs.twimg.com/media/Synthetic_1.png"]')->count());
        $other = $this->getEntityManager()->getRepository(User::class)->findOneByUsername('bob');
        self::assertFalse($other->getConfig()->isXArticleReaderEnabled());
        $this->client->request('PATCH', '/api/config.json', [], [], ['CONTENT_TYPE' => 'application/json'], '{"x_article_reader_enabled":false}');
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->client->request('POST', '/api/entries.json', ['url' => 'https://x.com/person/status/54321']);
        self::assertSame(206, (int) json_decode($this->client->getResponse()->getContent(), true)['http_status']);
    }

    public function testDuplicateApiPostPreservesArticleProvenanceForFailedReload(): void
    {
        $this->client->disableReboot();
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::exactly(3))->method('get')->willReturnOnConsecutiveCalls(
            ['body' => json_encode(['code' => 200, 'status' => FxArticleReaderTest::post()])],
            ['body' => '{"code":404}'],
            ['body' => '{"author_name":"Embed author","html":"<p>Short public excerpt</p>"}']
        );
        $this->client->getContainer()->set(PublicHttpClient::class, $http);
        $this->client->request('PATCH', '/api/config.json', [], [], ['CONTENT_TYPE' => 'application/json'], '{"x_article_reader_enabled":true}');
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $url = 'https://x.com/person/status/12345';
        $this->client->request('POST', '/api/entries.json', ['url' => $url]);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $original = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(200, (int) $original['http_status']);
        self::assertStringContainsString('The ending paragraph.', $original['content']);
        $this->client->request('POST', '/api/entries.json', ['url' => $url]);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $duplicate = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame($original['id'], $duplicate['id']);
        self::assertSame($original['content'], $duplicate['content']);
        self::assertSame($original['published_by'], $duplicate['published_by']);
        $em = $this->getEntityManager();
        $em->clear();
        $entry = $em->find(Entry::class, $original['id']);
        self::assertSame('article', $entry->getXCaptureType());
        $this->client->request('PATCH', '/api/entries/' . $original['id'] . '/reload.json');
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $reloaded = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(502, (int) $reloaded['http_status']);
        self::assertSame($original['content'], $reloaded['content']);
        self::assertSame($original['title'], $reloaded['title']);
        self::assertSame($original['published_by'], $reloaded['published_by']);
        $em->clear();
        self::assertSame('article', $em->find(Entry::class, $original['id'])->getXCaptureType());
    }
}
