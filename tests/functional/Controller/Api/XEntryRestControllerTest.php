<?php

namespace Wallabag\Tests\Functional\Controller\Api;

use Wallabag\Helper\PublicHttpClient;

class XEntryRestControllerTest extends WallabagApiTestCase
{
    public function testOrdinaryEntryApiCapturesPartialXContentAndDeduplicatesVariants(): void
    {
        $this->client->disableReboot();
        $http = $this->createMock(PublicHttpClient::class);
        $http->method('get')->willReturn(['body' => '{"author_name":"lauren","html":"<blockquote><p>Visible public text</p></blockquote>"}']);
        $this->client->getContainer()->set(PublicHttpClient::class, $http);
        $this->client->request('POST', '/api/entries.json', ['url' => 'https://x.com/poteto/status/2069824386283319343']);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $first = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(206, (int) $first['http_status']);
        self::assertStringContainsString('Partial X capture', $first['content']);
        self::assertStringContainsString('Visible public text', $first['content']);
        $this->client->request('POST', '/api/entries.json', ['url' => 'https://twitter.com/poteto/status/2069824386283319343?s=20']);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        self::assertSame($first['id'], json_decode($this->client->getResponse()->getContent(), true)['id']);
    }
}
