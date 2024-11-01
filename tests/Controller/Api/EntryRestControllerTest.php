<?php

namespace Tests\Wallabag\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;
use Wallabag\Entity\Entry;
use Wallabag\Entity\Tag;
use Wallabag\Entity\User;
use Wallabag\Helper\ContentProxy;

class EntryRestControllerTest extends WallabagApiTestCase
{
    public function testGetOneEntry()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneBy(['user' => $this->getUserId(), 'isArchived' => false]);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('GET', '/api/entries/' . $entry->getId() . '.json');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($entry->getTitle(), $content['title']);
        $this->assertSame($entry->getUrl(), $content['url']);
        $this->assertCount(\count($entry->getTags()), $content['tags']);
        $this->assertSame($entry->getUserName(), $content['user_name']);
        $this->assertSame($entry->getUserEmail(), $content['user_email']);
        $this->assertSame($entry->getUserId(), $content['user_id']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetOneEntryWithOriginUrl()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneBy(['user' => $this->getUserId(), 'url' => 'http://0.0.0.0/entry2']);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('GET', '/api/entries/' . $entry->getId() . '.json');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($entry->getOriginUrl(), $content['origin_url']);
    }

    public function testExportEntry()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneBy(['user' => $this->getUserId(), 'isArchived' => false]);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('GET', '/api/entries/' . $entry->getId() . '/export.epub');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        // epub format got the content type in the content
        $this->assertStringContainsString('application/epub', $this->client->getResponse()->getContent());
        $this->assertSame('application/epub+zip', $this->client->getResponse()->headers->get('Content-Type'));

        // re-auth client for pdf
        $client = $this->createAuthorizedClient();
        $client->request('GET', '/api/entries/' . $entry->getId() . '/export.pdf');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString('PDF-', $client->getResponse()->getContent());
        $this->assertSame('application/pdf', $client->getResponse()->headers->get('Content-Type'));

        // re-auth client for pdf
        $client = $this->createAuthorizedClient();
        $client->request('GET', '/api/entries/' . $entry->getId() . '/export.txt');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString('text/plain', $client->getResponse()->headers->get('Content-Type'));

        // re-auth client for pdf
        $client = $this->createAuthorizedClient();
        $client->request('GET', '/api/entries/' . $entry->getId() . '/export.csv');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString('application/csv', $client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetOneEntryWrongUser()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneBy(['user' => $this->getUserId('bob'), 'isArchived' => false]);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('GET', '/api/entries/' . $entry->getId() . '.json');

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testGetEntries()
    {
        $this->client->request('GET', '/api/entries');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertNotNull($content['_embedded']['items'][0]['content']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetEntriesDetailMetadata()
    {
        $this->client->request('GET', '/api/entries?detail=metadata');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertNull($content['_embedded']['items'][0]['content']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetEntriesByDomainName()
    {
        $this->client->request('GET', '/api/entries?domain_name=domain.io');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertSame('test title entry6', $content['_embedded']['items'][0]['title']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetEntriesByHttpStatusWithMatching()
    {
        $this->client->request('GET', '/api/entries?http_status=302');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertSame(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertSame('test title entry7', $content['_embedded']['items'][0]['title']);
        $this->assertSame('302', $content['_embedded']['items'][0]['http_status']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetEntriesByHttpStatusNoMatching()
    {
        $this->client->request('GET', '/api/entries?http_status=404');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertEmpty($content['_embedded']['items']);
        $this->assertSame(0, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetEntriesWithBadHttpStatusParam()
    {
        $this->client->request('GET', '/api/entries?http_status=10000');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetEntriesWithFullOptions()
    {
        $this->client->request('GET', '/api/entries', [
            'archive' => 1,
            'starred' => 1,
            'sort' => 'updated',
            'order' => 'asc',
            'page' => 1,
            'perPage' => 2,
            'tags' => 'foo',
            'since' => 1443274283,
            'public' => 0,
            'notParsed' => 0,
            'http_status' => 200,
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertArrayHasKey('items', $content['_embedded']);
        $this->assertGreaterThanOrEqual(0, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertSame(2, $content['limit']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertStringContainsString('archive=1', $content['_links'][$link]['href']);
            $this->assertStringContainsString('starred=1', $content['_links'][$link]['href']);
            $this->assertStringContainsString('sort=updated', $content['_links'][$link]['href']);
            $this->assertStringContainsString('order=asc', $content['_links'][$link]['href']);
            $this->assertStringContainsString('tags=foo', $content['_links'][$link]['href']);
            $this->assertStringContainsString('since=1443274283', $content['_links'][$link]['href']);
            $this->assertStringContainsString('public=0', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetEntriesPublicOnly()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneByUser($this->getUserId());

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        // generate at least one public entry
        $entry->generateUid();

        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->persist($entry);
        $em->flush();

        $this->client->request('GET', '/api/entries', [
            'public' => 1,
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertArrayHasKey('items', $content['_embedded']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertSame(30, $content['limit']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertStringContainsString('public=1', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetEntriesOnPageTwo()
    {
        $this->client->request('GET', '/api/entries', [
            'page' => 2,
            'perPage' => 2,
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(0, $content['total']);
        $this->assertSame(2, $content['page']);
        $this->assertSame(2, $content['limit']);
    }

    public function testGetStarredEntriesWithBadSort()
    {
        $this->client->request('GET', '/api/entries', ['starred' => 1, 'sort' => 'updated', 'order' => 'unknown']);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetStarredEntries()
    {
        $this->client->request('GET', '/api/entries', ['starred' => 1, 'sort' => 'updated']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertStringContainsString('starred=1', $content['_links'][$link]['href']);
            $this->assertStringContainsString('sort=updated', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetArchiveEntries()
    {
        $this->client->request('GET', '/api/entries', ['archive' => 1]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertStringContainsString('archive=1', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetNotParsedEntries()
    {
        $this->client->request('GET', '/api/entries', ['notParsed' => 1]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertStringContainsString('notParsed=1', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetParsedEntries()
    {
        $this->client->request('GET', '/api/entries', ['notParsed' => 0]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertStringContainsString('notParsed=0', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetTaggedEntries()
    {
        $this->client->request('GET', '/api/entries', ['tags' => 'foo,bar']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertContains('foo', array_column($content['_embedded']['items'][0]['tags'], 'label'), 'Entries tags should have "foo" tag');
        $this->assertContains('bar', array_column($content['_embedded']['items'][0]['tags'], 'label'), 'Entries tags should have "bar" tag');

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertStringContainsString('tags=foo,bar', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetTaggedEntriesWithBadParams()
    {
        $this->client->request('GET', '/api/entries', ['tags' => ['foo', 'bar']]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testGetDatedEntries()
    {
        $this->client->request('GET', '/api/entries', ['since' => 1443274283]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertStringContainsString('since=1443274283', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetDatedSupEntries()
    {
        $future = new \DateTime(date('Y-m-d H:i:s'));
        $this->client->request('GET', '/api/entries', ['since' => $future->getTimestamp() + 1000]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertEmpty($content['_embedded']['items']);
        $this->assertSame(0, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertSame(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertStringContainsString('since=' . ($future->getTimestamp() + 1000), $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testDeleteEntry()
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $entry = new Entry($em->getReference(User::class, 1));
        $entry->setUrl('http://0.0.0.0/test-delete-entry');
        $entry->setTitle('Test delete entry');
        $em->persist($entry);
        $em->flush();

        $em->clear();

        $e = [
            'title' => $entry->getTitle(),
            'url' => $entry->getUrl(),
            'id' => $entry->getId(),
        ];

        $this->client->request('DELETE', '/api/entries/' . $e['id'] . '.json');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($e['title'], $content['title']);
        $this->assertSame($e['url'], $content['url']);
        $this->assertSame($e['id'], $content['id']);

        // We'll try to delete this entry again
        $client = $this->createAuthorizedClient();
        $client->request('DELETE', '/api/entries/' . $e['id'] . '.json');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testDeleteEntryExpectId()
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $entry = new Entry($em->getReference(User::class, 1));
        $entry->setUrl('http://0.0.0.0/test-delete-entry-id');
        $em->persist($entry);
        $em->flush();

        $em->clear();

        $id = $entry->getId();

        $this->client->request('DELETE', '/api/entries/' . $id . '.json?expect=id');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($id, $content['id']);
        $this->assertArrayNotHasKey('url', $content);

        // We'll try to delete this entry again
        $client = $this->createAuthorizedClient();
        $client->request('DELETE', '/api/entries/' . $id . '.json');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testDeleteEntryExpectBadRequest()
    {
        $this->client->request('DELETE', '/api/entries/1.json?expect=badrequest');

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
    }

    public function testBadFormatURL()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'wallabagIsAwesome',
            'tags' => 'google',
            'title' => 'New title for my article',
            'content' => 'my content',
            'language' => 'de',
            'published_at' => '2016-09-08T11:55:58+0200',
            'authors' => 'bob,helen',
            'public' => 1,
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString('The url \'"wallabagIsAwesome"\' is not a valid url', $content);
    }

    public function testPostEntry()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'https://www.20minutes.fr/sport/jo_2024/4095122-20240712-jo-paris-2024-saut-ange-bombe-comment-anne-hidalgo-va-plonger-seine-si-fait-vraiment',
            'tags' => 'google',
            'title' => 'New title for my article',
            'content' => 'my content',
            'language' => 'de',
            'published_at' => '2016-09-08T11:55:58+0200',
            'authors' => 'bob,helen',
            'public' => 1,
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertSame('https://www.20minutes.fr/sport/jo_2024/4095122-20240712-jo-paris-2024-saut-ange-bombe-comment-anne-hidalgo-va-plonger-seine-si-fait-vraiment', $content['url']);
        $this->assertSame(0, $content['is_archived']);
        $this->assertSame(0, $content['is_starred']);
        $this->assertNull($content['starred_at']);
        $this->assertNull($content['archived_at']);
        $this->assertSame('New title for my article', $content['title']);
        $this->assertSame($this->getUserId(), $content['user_id']);
        $this->assertCount(2, $content['tags']);
        $this->assertNull($content['origin_url']);
        $this->assertSame('my content', $content['content']);
        $this->assertSame('de', $content['language']);
        $this->assertSame('2016-09-08T11:55:58+0200', $content['published_at']);
        $this->assertCount(2, $content['published_by']);
        $this->assertContains('bob', $content['published_by']);
        $this->assertContains('helen', $content['published_by']);
        $this->assertTrue($content['is_public'], 'A public link has been generated for that entry');
    }

    public function testPostSameEntry()
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $entry = new Entry($em->getReference(User::class, $this->getUserId()));
        $entry->setUrl('https://www.20minutes.fr/sport/jo_2024/4095122-20240712-jo-paris-2024-saut-ange-bombe-comment-anne-hidalgo-va-plonger-seine-si-fait-vraiment');
        $entry->setArchived(true);
        $entry->addTag((new Tag())->setLabel('google'));
        $entry->addTag((new Tag())->setLabel('apple'));
        $em->persist($entry);
        $em->flush();
        $em->clear();

        $this->client->request('POST', '/api/entries.json', [
            'url' => 'https://www.20minutes.fr/sport/jo_2024/4095122-20240712-jo-paris-2024-saut-ange-bombe-comment-anne-hidalgo-va-plonger-seine-si-fait-vraiment',
            'archive' => '1',
            'tags' => 'google, apple',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertSame('https://www.20minutes.fr/sport/jo_2024/4095122-20240712-jo-paris-2024-saut-ange-bombe-comment-anne-hidalgo-va-plonger-seine-si-fait-vraiment', $content['url']);
        $this->assertSame(1, $content['is_archived']);
        $this->assertSame(0, $content['is_starred']);
        $this->assertCount(3, $content['tags']);
    }

    public function testPostEntryWhenFetchContentFails()
    {
        /** @var Container $container */
        $container = $this->client->getContainer();
        $contentProxy = $this->getMockBuilder(ContentProxy::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateEntry'])
            ->getMock();
        $contentProxy->expects($this->any())
            ->method('updateEntry')
            ->willThrowException(new \Exception('Test Fetch content fails'));
        $container->set(ContentProxy::class, $contentProxy);

        try {
            $this->client->request('POST', '/api/entries.json', [
                'url' => 'http://www.example.com/',
            ]);

            $this->assertSame(200, $this->client->getResponse()->getStatusCode());
            $content = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertGreaterThan(0, $content['id']);
            $this->assertSame('http://www.example.com/', $content['url']);
            $this->assertSame('www.example.com', $content['domain_name']);
            $this->assertSame('www.example.com', $content['title']);
        } finally {
            // Remove the created entry to avoid side effects on other tests
            if (isset($content['id'])) {
                $em = $this->client->getContainer()->get(EntityManagerInterface::class);
                $entry = $em->getReference(Entry::class, $content['id']);
                $em->remove($entry);
                $em->flush();
            }
        }
    }

    public function testPostArchivedAndStarredEntry()
    {
        $now = new \DateTime();
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'https://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html',
            'archive' => '1',
            'starred' => '1',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertSame('https://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html', $content['url']);
        $this->assertSame(1, $content['is_archived']);
        $this->assertSame(1, $content['is_starred']);
        $this->assertGreaterThanOrEqual($now->getTimestamp(), (new \DateTime($content['starred_at']))->getTimestamp());
        $this->assertGreaterThanOrEqual($now->getTimestamp(), (new \DateTime($content['archived_at']))->getTimestamp());
        $this->assertSame($this->getUserId(), $content['user_id']);
    }

    public function testPostArchivedAndStarredEntryWithoutQuotes()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'https://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html',
            'archive' => 0,
            'starred' => 1,
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertSame('https://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html', $content['url']);
        $this->assertSame(0, $content['is_archived']);
        $this->assertSame(1, $content['is_starred']);
    }

    public function testPostEntryWithOriginUrl()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'https://www.20minutes.fr/sport/jo_2024/4095122-20240712-jo-paris-2024-saut-ange-bombe-comment-anne-hidalgo-va-plonger-seine-si-fait-vraiment',
            'tags' => 'google',
            'title' => 'New title for my article',
            'content' => 'my content',
            'language' => 'de',
            'published_at' => '2016-09-08T11:55:58+0200',
            'authors' => 'bob,helen',
            'public' => 1,
            'origin_url' => 'http://mysource.tld',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertSame('https://www.20minutes.fr/sport/jo_2024/4095122-20240712-jo-paris-2024-saut-ange-bombe-comment-anne-hidalgo-va-plonger-seine-si-fait-vraiment', $content['url']);
        $this->assertSame('http://mysource.tld', $content['origin_url']);
    }

    public function testPatchEntry()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneByUser($this->getUserId());

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('PATCH', '/api/entries/' . $entry->getId() . '.json', [
            'title' => 'New awesome title',
            'tags' => 'new tag ' . uniqid(),
            'starred' => '1',
            'archive' => '0',
            'language' => 'de_AT',
            'preview_picture' => 'http://preview.io/picture.jpg',
            'authors' => 'bob,sponge',
            'content' => 'awesome',
            'public' => 0,
            'published_at' => 1488833381,
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($entry->getId(), $content['id']);
        $this->assertSame($entry->getUrl(), $content['url']);
        $this->assertSame('New awesome title', $content['title']);
        $this->assertGreaterThanOrEqual(1, \count($content['tags']), 'We force only one tag');
        $this->assertSame($this->getUserId(), $content['user_id']);
        $this->assertSame('de_AT', $content['language']);
        $this->assertSame('http://preview.io/picture.jpg', $content['preview_picture']);
        $this->assertContains('sponge', $content['published_by']);
        $this->assertContains('bob', $content['published_by']);
        $this->assertSame('awesome', $content['content']);
        $this->assertFalse($content['is_public'], 'Entry is no more shared');
        $this->assertStringContainsString('2017-03-06', $content['published_at']);
    }

    public function testPatchEntryWithoutQuotes()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneByUser($this->getUserId());

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $previousContent = $entry->getContent();
        $previousLanguage = $entry->getLanguage();

        $this->client->request('PATCH', '/api/entries/' . $entry->getId() . '.json', [
            'title' => 'New awesome title',
            'tags' => 'new tag ' . uniqid(),
            'starred' => 1,
            'archive' => 0,
            'authors' => ['bob', 'sponge'],
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($entry->getId(), $content['id']);
        $this->assertSame($entry->getUrl(), $content['url']);
        $this->assertGreaterThanOrEqual(1, \count($content['tags']), 'We force only one tag');
        $this->assertEmpty($content['published_by'], 'Authors were not saved because of an array instead of a string');
        $this->assertSame($previousContent, $content['content'], 'Ensure content has not moved');
        $this->assertSame($previousLanguage, $content['language'], 'Ensure language has not moved');
    }

    public function testPatchEntryWithOriginUrl()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneByUser($this->getUserId());

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $previousContent = $entry->getContent();
        $previousLanguage = $entry->getLanguage();

        $this->client->request('PATCH', '/api/entries/' . $entry->getId() . '.json', [
            'title' => 'Another awesome title just for profit',
            'origin_url' => 'https://myawesomesource.example.com',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($entry->getId(), $content['id']);
        $this->assertSame($entry->getUrl(), $content['url']);
        $this->assertSame('https://myawesomesource.example.com', $content['origin_url']);
        $this->assertEmpty($content['published_by'], 'Authors were not saved because of an array instead of a string');
        $this->assertSame($previousContent, $content['content'], 'Ensure content has not moved');
        $this->assertSame($previousLanguage, $content['language'], 'Ensure language has not moved');
    }

    public function testPatchEntryRemoveOriginUrl()
    {
        $entry = $this->client->getContainer()
        ->get(EntityManagerInterface::class)
        ->getRepository(Entry::class)
        ->findOneByUser($this->getUserId());

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $previousContent = $entry->getContent();
        $previousLanguage = $entry->getLanguage();
        $previousTitle = $entry->getTitle();

        $this->client->request('PATCH', '/api/entries/' . $entry->getId() . '.json', [
            'origin_url' => '',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($entry->getId(), $content['id']);
        $this->assertSame($entry->getUrl(), $content['url']);
        $this->assertEmpty($content['origin_url']);
        $this->assertEmpty($content['published_by'], 'Authors were not saved because of an array instead of a string');
        $this->assertSame($previousContent, $content['content'], 'Ensure content has not moved');
        $this->assertSame($previousLanguage, $content['language'], 'Ensure language has not moved');
        $this->assertSame($previousTitle, $content['title'], 'Ensure title has not moved');
    }

    public function testPatchEntryNullOriginUrl()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneByUser($this->getUserId());

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('PATCH', '/api/entries/' . $entry->getId() . '.json', [
            'origin_url' => null,
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNull($content['origin_url']);
    }

    public function testGetTagsEntry()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneWithTags($this->user->getId());

        $entry = $entry[0];

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $tags = [];
        foreach ($entry->getTags() as $tag) {
            $tags[] = ['id' => $tag->getId(), 'label' => $tag->getLabel(), 'slug' => $tag->getSlug()];
        }

        $this->client->request('GET', '/api/entries/' . $entry->getId() . '/tags');

        $this->assertSame(json_encode($tags, \JSON_HEX_QUOT), $this->client->getResponse()->getContent());
    }

    public function testPostTagsOnEntry()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneByUser($this->getUserId());

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $nbTags = \count($entry->getTags());

        $newTags = 'tag1,tag2,tag3';

        $this->client->request('POST', '/api/entries/' . $entry->getId() . '/tags', ['tags' => $newTags]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('tags', $content);
        $this->assertCount($nbTags + 3, $content['tags']);

        $entryDB = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->find($entry->getId());

        $tagsInDB = [];
        foreach ($entryDB->getTags()->toArray() as $tag) {
            $tagsInDB[$tag->getId()] = $tag->getLabel();
        }

        foreach (explode(',', $newTags) as $tag) {
            $this->assertContains($tag, $tagsInDB);
        }
    }

    public function testDeleteOneTagEntry()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneWithTags($this->user->getId());
        $entry = $entry[0];

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        // hydrate the tags relations
        $nbTags = \count($entry->getTags());
        $tag = $entry->getTags()[0];

        $this->client->request('DELETE', '/api/entries/' . $entry->getId() . '/tags/' . $tag->getId() . '.json');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('tags', $content);
        $this->assertCount($nbTags - 1, $content['tags']);
    }

    public function testSaveIsArchivedAfterPost()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneBy(['user' => $this->getUserId(), 'isArchived' => true]);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('POST', '/api/entries.json', [
            'url' => $entry->getUrl(),
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(1, $content['is_archived']);
    }

    public function testSaveIsStarredAfterPost()
    {
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneBy(['user' => $this->getUserId(), 'isStarred' => true]);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('POST', '/api/entries.json', [
            'url' => $entry->getUrl(),
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(1, $content['is_starred']);
    }

    public function testSaveIsArchivedAfterPatch()
    {
        $now = new \DateTime();
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneBy(['user' => $this->getUserId(), 'isArchived' => true]);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $previousTitle = $entry->getTitle();

        $this->client->request('PATCH', '/api/entries/' . $entry->getId() . '.json', [
            'title' => $entry->getTitle() . '++',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(1, $content['is_archived']);
        $this->assertSame($previousTitle . '++', $content['title']);
        $this->assertGreaterThanOrEqual((new \DateTime($content['archived_at']))->getTimestamp(), $now->getTimestamp());
    }

    public function testSaveIsStarredAfterPatch()
    {
        $now = new \DateTime();
        $entry = $this->client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneBy(['user' => $this->getUserId(), 'isStarred' => true]);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $previousTitle = $entry->getTitle();

        $this->client->request('PATCH', '/api/entries/' . $entry->getId() . '.json', [
            'title' => $entry->getTitle() . '++',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(1, $content['is_starred']);
        $this->assertSame($previousTitle . '++', $content['title']);
        $this->assertGreaterThanOrEqual((new \DateTime($content['starred_at']))->getTimestamp(), $now->getTimestamp());
    }

    public function dataForEntriesExistWithUrl()
    {
        $url = hash('sha1', 'http://0.0.0.0/entry2');

        return [
            'with_id' => [
                'url' => '/api/entries/exists?url=http://0.0.0.0/entry2&return_id=1',
                'expectedValue' => 2,
            ],
            'without_id' => [
                'url' => '/api/entries/exists?url=http://0.0.0.0/entry2',
                'expectedValue' => true,
            ],
            'hashed_url_with_id' => [
                'url' => '/api/entries/exists?hashed_url=' . $url . '&return_id=1',
                'expectedValue' => 2,
            ],
            'hashed_url_without_id' => [
                'url' => '/api/entries/exists?hashed_url=' . $url . '',
                'expectedValue' => true,
            ],
        ];
    }

    /**
     * @dataProvider dataForEntriesExistWithUrl
     */
    public function testGetEntriesExists($url, $expectedValue)
    {
        $this->client->request('GET', $url);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($expectedValue, $content['exists']);
    }

    public function testGetEntriesExistsWithManyUrls()
    {
        $url1 = 'http://0.0.0.0/entry2';
        $url2 = 'http://0.0.0.0/entry10';

        $this->client->request('GET', '/api/entries/exists?urls[]=' . $url1 . '&urls[]=' . $url2 . '&return_id=1');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey($url1, $content);
        $this->assertArrayHasKey($url2, $content);
        // it returns a database id, we don't know it, so we only check it's greater than the lowest possible value
        $this->assertGreaterThan(1, $content[$url1]);
        $this->assertNull($content[$url2]);
    }

    public function testGetEntriesExistsWithManyUrlsReturnBool()
    {
        $url1 = 'http://0.0.0.0/entry2';
        $url2 = 'http://0.0.0.0/entry10';
        $this->client->request('GET', '/api/entries/exists?urls[]=' . $url1 . '&urls[]=' . $url2);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey($url1, $content);
        $this->assertArrayHasKey($url2, $content);
        $this->assertTrue($content[$url1]);
        $this->assertFalse($content[$url2]);
    }

    public function testGetEntriesExistsWithManyUrlsHashed()
    {
        $url1 = 'http://0.0.0.0/entry2';
        $url2 = 'http://0.0.0.0/entry10';
        $this->client->request('GET', '/api/entries/exists?hashed_urls[]=' . hash('sha1', $url1) . '&hashed_urls[]=' . hash('sha1', $url2) . '&return_id=1');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey(hash('sha1', $url1), $content);
        $this->assertArrayHasKey(hash('sha1', $url2), $content);
        $this->assertSame(2, $content[hash('sha1', $url1)]);
        $this->assertNull($content[hash('sha1', $url2)]);
    }

    public function testGetEntriesExistsWithManyUrlsHashedReturnBool()
    {
        $url1 = 'http://0.0.0.0/entry2';
        $url2 = 'http://0.0.0.0/entry10';
        $this->client->request('GET', '/api/entries/exists?hashed_urls[]=' . hash('sha1', $url1) . '&hashed_urls[]=' . hash('sha1', $url2));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey(hash('sha1', $url1), $content);
        $this->assertArrayHasKey(hash('sha1', $url2), $content);
        $this->assertTrue($content[hash('sha1', $url1)]);
        $this->assertFalse($content[hash('sha1', $url2)]);
    }

    public function testGetEntriesExistsWhichDoesNotExists()
    {
        $this->client->request('GET', '/api/entries/exists?url=http://google.com/entry2');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertFalse($content['exists']);
    }

    public function testGetEntriesExistsWhichDoesNotExistsWithHashedUrl()
    {
        $this->client->request('GET', '/api/entries/exists?hashed_url=' . hash('sha1', 'http://google.com/entry2'));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertFalse($content['exists']);
    }

    public function testGetEntriesExistsWithNoUrl()
    {
        $this->client->request('GET', '/api/entries/exists?url=');

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testGetEntriesExistsWithNoHashedUrl()
    {
        $this->client->request('GET', '/api/entries/exists?hashed_url=');

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testReloadEntryErrorWhileFetching()
    {
        $entry = $this->client->getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/entry4', $this->getUserId());

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('PATCH', '/api/entries/' . $entry->getId() . '/reload.json');
        $this->assertSame(304, $this->client->getResponse()->getStatusCode());
    }

    public function testReloadEntry()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'https://www.20minutes.fr/sport/jo_2024/4095122-20240712-jo-paris-2024-saut-ange-bombe-comment-anne-hidalgo-va-plonger-seine-si-fait-vraiment',
            'archive' => '1',
            'tags' => 'google, apple',
        ]);

        $json = json_decode($this->client->getResponse()->getContent(), true);

        $this->setUp();

        $this->client->request('PATCH', '/api/entries/' . $json['id'] . '/reload.json');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($content['title']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testPostEntriesTagsListAction()
    {
        $entry = $this->client->getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/entry4', $this->getUserId());

        $tags = $entry->getTags();

        $this->assertCount(2, $tags);

        $list = [
            [
                'url' => 'http://0.0.0.0/entry4',
                'tags' => 'new tag 1, new tag 2',
            ],
        ];

        $this->client->request('POST', '/api/entries/tags/lists?list=' . json_encode($list));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsInt($content[0]['entry']);
        $this->assertSame('http://0.0.0.0/entry4', $content[0]['url']);

        $entry = $this->client->getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/entry4', $this->getUserId());

        $tags = $entry->getTags();
        $this->assertCount(4, $tags);
    }

    public function testPostEntriesTagsListActionNoList()
    {
        $this->client->request('POST', '/api/entries/tags/lists?list=' . json_encode([]));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEmpty($content);
    }

    public function testDeleteEntriesTagsListAction()
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $entry = new Entry($em->getReference(User::class, $this->getUserId()));
        $entry->setUrl('http://0.0.0.0/test-entry');
        $entry->addTag((new Tag())->setLabel('foo-tag'));
        $entry->addTag((new Tag())->setLabel('bar-tag'));
        $em->persist($entry);
        $em->flush();

        $em->clear();

        $list = [
            [
                'url' => 'http://0.0.0.0/test-entry',
                'tags' => 'foo-tag, bar-tag',
            ],
        ];

        $this->client->request('DELETE', '/api/entries/tags/list?list=' . json_encode($list));
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $entry = $em->getRepository(Entry::class)->find($entry->getId());
        $this->assertCount(0, $entry->getTags());
    }

    public function testDeleteEntriesTagsListActionNoList()
    {
        $this->client->request('DELETE', '/api/entries/tags/list?list=' . json_encode([]));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEmpty($content);
    }

    public function testPostEntriesListAction()
    {
        $list = [
            'https://www.lemonde.fr/musiques/article/2017/04/23/loin-de-la-politique-le-printemps-de-bourges-retombe-en-enfance_5115862_1654986.html',
            'http://0.0.0.0/entry2',
        ];

        $this->client->request('POST', '/api/entries/lists?urls=' . json_encode($list));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsInt($content[0]['entry']);
        $this->assertSame('https://www.lemonde.fr/musiques/article/2017/04/23/loin-de-la-politique-le-printemps-de-bourges-retombe-en-enfance_5115862_1654986.html', $content[0]['url']);

        $this->assertIsInt($content[1]['entry']);
        $this->assertSame('http://0.0.0.0/entry2', $content[1]['url']);
    }

    public function testPostEntriesListActionWithNoUrls()
    {
        $this->client->request('POST', '/api/entries/lists?urls=' . json_encode([]));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEmpty($content);
    }

    public function testDeleteEntriesListAction()
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->persist((new Entry($em->getReference(User::class, $this->getUserId())))->setUrl('http://0.0.0.0/test-entry1'));

        $em->flush();
        $em->clear();
        $list = [
            'http://0.0.0.0/test-entry1',
            'http://0.0.0.0/test-entry-not-exist',
        ];

        $this->client->request('DELETE', '/api/entries/list?urls=' . json_encode($list));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($content[0]['entry']);
        $this->assertSame('http://0.0.0.0/test-entry1', $content[0]['url']);

        $this->assertFalse($content[1]['entry']);
        $this->assertSame('http://0.0.0.0/test-entry-not-exist', $content[1]['url']);
    }

    public function testDeleteEntriesListActionWithNoUrls()
    {
        $this->client->request('DELETE', '/api/entries/list?urls=' . json_encode([]));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEmpty($content);
    }

    public function testLimitBulkAction()
    {
        $list = [
            'http://0.0.0.0/entry1',
            'http://0.0.0.0/entry1',
            'http://0.0.0.0/entry1',
            'http://0.0.0.0/entry1',
            'http://0.0.0.0/entry1',
            'http://0.0.0.0/entry1',
            'http://0.0.0.0/entry1',
            'http://0.0.0.0/entry1',
            'http://0.0.0.0/entry1',
            'http://0.0.0.0/entry1',
            'http://0.0.0.0/entry1',
        ];

        $this->client->request('POST', '/api/entries/lists?urls=' . json_encode($list));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('API limit reached', $this->client->getResponse()->getContent());
    }

    public function testRePostEntryAndReUsePublishedAt()
    {
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $entry = new Entry($em->getReference(User::class, $this->getUserId()));
        $entry->setTitle('Antoine de Caunes : « Je veux avoir le droit de tâtonner »');
        $entry->setContent('hihi');
        $entry->setUrl('https://www.lemonde.fr/m-perso/article/2017/06/25/antoine-de-caunes-je-veux-avoir-le-droit-de-tatonner_5150728_4497916.html');
        $entry->setPublishedAt(new \DateTime('2017-06-26T07:46:02+0200'));
        $em->persist($entry);
        $em->flush();
        $em->clear();

        $this->client->request('POST', '/api/entries.json', [
            'url' => 'https://www.lemonde.fr/m-perso/article/2017/06/25/antoine-de-caunes-je-veux-avoir-le-droit-de-tatonner_5150728_4497916.html',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertSame('https://www.lemonde.fr/m-perso/article/2017/06/25/antoine-de-caunes-je-veux-avoir-le-droit-de-tatonner_5150728_4497916.html', $content['url']);
    }
}
