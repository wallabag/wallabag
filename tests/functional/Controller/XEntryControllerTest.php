<?php

namespace Wallabag\Tests\Functional\Controller;

use Wallabag\Entity\Entry;
use Wallabag\Helper\PublicHttpClient;
use Wallabag\Tests\Functional\WallabagTestCase;

class XEntryControllerTest extends WallabagTestCase
{
    public function testNormalNewEntryFormSavesPublicXSnapshot(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->disableReboot();
        $http = $this->createMock(PublicHttpClient::class);
        $http->method('get')->willReturn(['body' => '{"author_name":"lauren","html":"<blockquote><p>Public form snapshot</p></blockquote>"}']);
        $client->getContainer()->set(PublicHttpClient::class, $http);
        $crawler = $client->request('GET', '/new-entry');
        self::assertSame(200, $client->getResponse()->getStatusCode());
        $form = $crawler->filter('form[name=entry]')->form();
        $client->submit($form, ['entry[url]' => 'https://x.com/poteto/status/2069824386283319343']);
        self::assertSame(302, $client->getResponse()->getStatusCode());
        $entry = $this->getEntityManager()->getRepository(Entry::class)->findByUrlAndUserId('https://x.com/poteto/status/2069824386283319343', $this->getLoggedInUserId());
        self::assertNotFalse($entry);
        self::assertSame(206, (int) $entry->getHttpStatus());
        self::assertStringContainsString('Partial X capture', $entry->getContent());
        self::assertStringContainsString('Public form snapshot', $entry->getContent());
    }

    /** @dataProvider authorMetadata */
    public function testProviderAuthorIsRenderedAsPlainText(bool $articleReader, string $author): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->disableReboot();
        $this->getLoggedInUser()->getConfig()->setXArticleReaderEnabled($articleReader);
        $this->getEntityManager()->flush();
        $postId = '76543210987654321';
        $payload = $articleReader ? ['code' => 200, 'status' => [
            'id' => $postId,
            'author' => ['name' => $author, 'screen_name' => 'testauthor', 'protected' => false],
            'article' => ['title' => 'Author metadata regression', 'content' => ['blocks' => [
                ['type' => 'unstyled', 'text' => 'Public article body.'],
            ], 'entityMap' => []]],
        ]] : ['author_name' => $author, 'html' => '<blockquote><p>Public embed body.</p></blockquote>'];
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::once())->method('get')->willReturn(['body' => json_encode($payload)]);
        $client->getContainer()->set(PublicHttpClient::class, $http);
        $crawler = $client->request('GET', '/new-entry');
        $client->submit($crawler->filter('form[name=entry]')->form(), ['entry[url]' => 'https://x.com/testauthor/status/' . $postId]);
        self::assertSame(302, $client->getResponse()->getStatusCode());
        $entry = $this->getEntityManager()->getRepository(Entry::class)->findByUrlAndUserId('https://x.com/testauthor/status/' . $postId, $this->getLoggedInUserId());
        self::assertNotFalse($entry);
        // Metadata stays plain text, without HTML entities leaking into API/export values.
        self::assertSame([$author], $entry->getPublishedBy());
        self::assertSame($articleReader ? 200 : 206, $entry->getHttpStatus());
        $entryId = $entry->getId();
        $this->getEntityManager()->clear();
        $entry = $this->getEntityManager()->find(Entry::class, $entryId);
        self::assertSame($articleReader ? 'article' : 'post', $entry->getXCaptureType());
        $crawler = $client->request('GET', '/view/' . $entry->getId());
        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertStringContainsString($author, $crawler->filter('ul.stats')->text());
        self::assertCount(0, $crawler->filter('ul.stats img, ul.stats script, ul.stats svg, ul.stats [onerror]'));
    }

    public function authorMetadata(): array
    {
        $malicious = '<img src=x onerror=alert(1)><script>alert(2)</script><svg onload=alert(3)>';
        $plain = 'Renée O’Connor & <Friends>';

        return [
            'embed HTML' => [false, $malicious],
            'article HTML' => [true, $malicious],
            'embed plain name' => [false, $plain],
            'article plain name' => [true, $plain],
        ];
    }
}
