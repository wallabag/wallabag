<?php

namespace Wallabag\Tests\Functional\Controller\Api;

use Wallabag\Tests\Unit\Helper\FxArticleReaderTest;
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
                return ['body' => json_encode(['code' => 200, 'status' => FxArticleReaderTest::post()])];
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
        $other = $this->getEntityManager()->getRepository(User::class)->findOneByUsername('bob');
        self::assertFalse($other->getConfig()->isXArticleReaderEnabled());
        $this->client->request('PATCH', '/api/config.json', [], [], ['CONTENT_TYPE' => 'application/json'], '{"x_article_reader_enabled":false}');
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->client->request('POST', '/api/entries.json', ['url' => 'https://x.com/person/status/54321']);
        self::assertSame(206, (int) json_decode($this->client->getResponse()->getContent(), true)['http_status']);
    }
}
