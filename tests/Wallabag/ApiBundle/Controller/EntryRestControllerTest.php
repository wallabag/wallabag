<?php

namespace Tests\Wallabag\ApiBundle\Controller;

use Tests\Wallabag\ApiBundle\WallabagApiTestCase;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Wallabag\UserBundle\Entity\User;

class EntryRestControllerTest extends WallabagApiTestCase
{
    public function testGetOneEntry()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(['user' => 1, 'isArchived' => false]);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('GET', '/api/entries/' . $entry->getId() . '.json');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($entry->getTitle(), $content['title']);
        $this->assertSame($entry->getUrl(), $content['url']);
        $this->assertCount(count($entry->getTags()), $content['tags']);
        $this->assertSame($entry->getUserName(), $content['user_name']);
        $this->assertSame($entry->getUserEmail(), $content['user_email']);
        $this->assertSame($entry->getUserId(), $content['user_id']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetOneEntryWithOriginUrl()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(['user' => 1, 'url' => 'http://0.0.0.0/entry2']);

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
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(['user' => 1, 'isArchived' => false]);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('GET', '/api/entries/' . $entry->getId() . '/export.epub');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        // epub format got the content type in the content
        $this->assertContains('application/epub', $this->client->getResponse()->getContent());
        $this->assertSame('application/epub+zip', $this->client->getResponse()->headers->get('Content-Type'));

        // re-auth client for mobi
        $client = $this->createAuthorizedClient();
        $client->request('GET', '/api/entries/' . $entry->getId() . '/export.mobi');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertSame('application/x-mobipocket-ebook', $client->getResponse()->headers->get('Content-Type'));

        // re-auth client for pdf
        $client = $this->createAuthorizedClient();
        $client->request('GET', '/api/entries/' . $entry->getId() . '/export.pdf');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertContains('PDF-', $client->getResponse()->getContent());
        $this->assertSame('application/pdf', $client->getResponse()->headers->get('Content-Type'));

        // re-auth client for pdf
        $client = $this->createAuthorizedClient();
        $client->request('GET', '/api/entries/' . $entry->getId() . '/export.txt');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertContains('text/plain', $client->getResponse()->headers->get('Content-Type'));

        // re-auth client for pdf
        $client = $this->createAuthorizedClient();
        $client->request('GET', '/api/entries/' . $entry->getId() . '/export.csv');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertContains('application/csv', $client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetOneEntryWrongUser()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(['user' => 2, 'isArchived' => false]);

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

        $this->assertGreaterThanOrEqual(1, count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
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
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
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
            $this->assertContains('archive=1', $content['_links'][$link]['href']);
            $this->assertContains('starred=1', $content['_links'][$link]['href']);
            $this->assertContains('sort=updated', $content['_links'][$link]['href']);
            $this->assertContains('order=asc', $content['_links'][$link]['href']);
            $this->assertContains('tags=foo', $content['_links'][$link]['href']);
            $this->assertContains('since=1443274283', $content['_links'][$link]['href']);
            $this->assertContains('public=0', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetEntriesPublicOnly()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        // generate at least one public entry
        $entry->generateUid();

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $em->persist($entry);
        $em->flush();

        $this->client->request('GET', '/api/entries', [
            'public' => 1,
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
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
            $this->assertContains('public=1', $content['_links'][$link]['href']);
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

    public function testGetStarredEntries()
    {
        $this->client->request('GET', '/api/entries', ['starred' => 1, 'sort' => 'updated']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
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
            $this->assertContains('starred=1', $content['_links'][$link]['href']);
            $this->assertContains('sort=updated', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetArchiveEntries()
    {
        $this->client->request('GET', '/api/entries', ['archive' => 1]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
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
            $this->assertContains('archive=1', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetTaggedEntries()
    {
        $this->client->request('GET', '/api/entries', ['tags' => 'foo,bar']);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
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
            $this->assertContains('tags=' . urlencode('foo,bar'), $content['_links'][$link]['href']);
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

        $this->assertGreaterThanOrEqual(1, count($content));
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
            $this->assertContains('since=1443274283', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetDatedSupEntries()
    {
        $future = new \DateTime(date('Y-m-d H:i:s'));
        $this->client->request('GET', '/api/entries', ['since' => $future->getTimestamp() + 1000]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
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
            $this->assertContains('since=' . ($future->getTimestamp() + 1000), $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testDeleteEntry()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1, ['id' => 'asc']);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('DELETE', '/api/entries/' . $entry->getId() . '.json');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($entry->getTitle(), $content['title']);
        $this->assertSame($entry->getUrl(), $content['url']);

        // We'll try to delete this entry again
        $this->client->request('DELETE', '/api/entries/' . $entry->getId() . '.json');

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    public function testPostEntry()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html',
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
        $this->assertSame('http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html', $content['url']);
        $this->assertSame(0, $content['is_archived']);
        $this->assertSame(0, $content['is_starred']);
        $this->assertNull($content['starred_at']);
        $this->assertSame('New title for my article', $content['title']);
        $this->assertSame(1, $content['user_id']);
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
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $entry = new Entry($em->getReference(User::class, 1));
        $entry->setUrl('http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html');
        $entry->setArchived(true);
        $entry->addTag((new Tag())->setLabel('google'));
        $entry->addTag((new Tag())->setLabel('apple'));
        $em->persist($entry);
        $em->flush();
        $em->clear();

        $this->client->request('POST', '/api/entries.json', [
            'url' => 'http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html',
            'archive' => '1',
            'tags' => 'google, apple',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertSame('http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html', $content['url']);
        $this->assertSame(1, $content['is_archived']);
        $this->assertSame(0, $content['is_starred']);
        $this->assertCount(3, $content['tags']);
    }

    public function testPostEntryWhenFetchContentFails()
    {
        /** @var \Symfony\Component\DependencyInjection\Container $container */
        $container = $this->client->getContainer();
        $contentProxy = $this->getMockBuilder(ContentProxy::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateEntry'])
            ->getMock();
        $contentProxy->expects($this->any())
            ->method('updateEntry')
            ->willThrowException(new \Exception('Test Fetch content fails'));
        $container->set('wallabag_core.content_proxy', $contentProxy);

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
                $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
                $entry = $em->getReference('WallabagCoreBundle:Entry', $content['id']);
                $em->remove($entry);
                $em->flush();
            }
        }
    }

    public function testPostArchivedAndStarredEntry()
    {
        $now = new \DateTime();
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html',
            'archive' => '1',
            'starred' => '1',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertSame('http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html', $content['url']);
        $this->assertSame(1, $content['is_archived']);
        $this->assertSame(1, $content['is_starred']);
        $this->assertGreaterThanOrEqual($now->getTimestamp(), (new \DateTime($content['starred_at']))->getTimestamp());
        $this->assertSame(1, $content['user_id']);
    }

    public function testPostArchivedAndStarredEntryWithoutQuotes()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html',
            'archive' => 0,
            'starred' => 1,
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertSame('http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html', $content['url']);
        $this->assertSame(0, $content['is_archived']);
        $this->assertSame(1, $content['is_starred']);
    }

    public function testPostEntryWithOriginUrl()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html',
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
        $this->assertSame('http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html', $content['url']);
        $this->assertSame('http://mysource.tld', $content['origin_url']);
    }

    public function testPatchEntry()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

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
        $this->assertGreaterThanOrEqual(1, count($content['tags']), 'We force only one tag');
        $this->assertSame(1, $content['user_id']);
        $this->assertSame('de_AT', $content['language']);
        $this->assertSame('http://preview.io/picture.jpg', $content['preview_picture']);
        $this->assertContains('sponge', $content['published_by']);
        $this->assertContains('bob', $content['published_by']);
        $this->assertSame('awesome', $content['content']);
        $this->assertFalse($content['is_public'], 'Entry is no more shared');
        $this->assertContains('2017-03-06', $content['published_at']);
    }

    public function testPatchEntryWithoutQuotes()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

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
        $this->assertGreaterThanOrEqual(1, count($content['tags']), 'We force only one tag');
        $this->assertEmpty($content['published_by'], 'Authors were not saved because of an array instead of a string');
        $this->assertSame($previousContent, $content['content'], 'Ensure content has not moved');
        $this->assertSame($previousLanguage, $content['language'], 'Ensure language has not moved');
    }

    public function testPatchEntryWithOriginUrl()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

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
        ->get('doctrine.orm.entity_manager')
        ->getRepository('WallabagCoreBundle:Entry')
        ->findOneByUser(1);

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
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

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
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
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

        $this->assertSame(json_encode($tags, JSON_HEX_QUOT), $this->client->getResponse()->getContent());
    }

    public function testPostTagsOnEntry()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $nbTags = count($entry->getTags());

        $newTags = 'tag1,tag2,tag3';

        $this->client->request('POST', '/api/entries/' . $entry->getId() . '/tags', ['tags' => $newTags]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('tags', $content);
        $this->assertSame($nbTags + 3, count($content['tags']));

        $entryDB = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
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
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneWithTags($this->user->getId());
        $entry = $entry[0];

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        // hydrate the tags relations
        $nbTags = count($entry->getTags());
        $tag = $entry->getTags()[0];

        $this->client->request('DELETE', '/api/entries/' . $entry->getId() . '/tags/' . $tag->getId() . '.json');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('tags', $content);
        $this->assertSame($nbTags - 1, count($content['tags']));
    }

    public function testSaveIsArchivedAfterPost()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(['user' => 1, 'isArchived' => true]);

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
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(['user' => 1, 'isStarred' => true]);

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
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(['user' => 1, 'isArchived' => true]);

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
    }

    public function testSaveIsStarredAfterPatch()
    {
        $now = new \DateTime();
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(['user' => 1, 'isStarred' => true]);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }
        $this->client->request('PATCH', '/api/entries/' . $entry->getId() . '.json', [
            'title' => $entry->getTitle() . '++',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(1, $content['is_starred']);
        $this->assertGreaterThanOrEqual($now->getTimestamp(), (new \DateTime($content['starred_at']))->getTimestamp());
    }

    public function dataForEntriesExistWithUrl()
    {
        return [
            'with_id' => [
                'url' => '/api/entries/exists?url=http://0.0.0.0/entry2&return_id=1',
                'expectedValue' => 2,
            ],
            'without_id' => [
                'url' => '/api/entries/exists?url=http://0.0.0.0/entry2',
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
        $this->assertSame(2, $content[$url1]);
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

    public function testGetEntriesExistsWhichDoesNotExists()
    {
        $this->client->request('GET', '/api/entries/exists?url=http://google.com/entry2');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertFalse($content['exists']);
    }

    public function testGetEntriesExistsWithNoUrl()
    {
        $this->client->request('GET', '/api/entries/exists?url=');

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testReloadEntryErrorWhileFetching()
    {
        $entry = $this->client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId('http://0.0.0.0/entry4', 1);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('PATCH', '/api/entries/' . $entry->getId() . '/reload.json');
        $this->assertSame(304, $this->client->getResponse()->getStatusCode());
    }

    public function testReloadEntry()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html',
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
        $entry = $this->client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId('http://0.0.0.0/entry4', 1);

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

        $this->assertInternalType('int', $content[0]['entry']);
        $this->assertSame('http://0.0.0.0/entry4', $content[0]['url']);

        $entry = $this->client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId('http://0.0.0.0/entry4', 1);

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
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $entry = new Entry($em->getReference(User::class, 1));
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

        $entry = $em->getRepository('WallabagCoreBundle:Entry')->find($entry->getId());
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
            'http://www.lemonde.fr/musiques/article/2017/04/23/loin-de-la-politique-le-printemps-de-bourges-retombe-en-enfance_5115862_1654986.html',
            'http://0.0.0.0/entry2',
        ];

        $this->client->request('POST', '/api/entries/lists?urls=' . json_encode($list));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertInternalType('int', $content[0]['entry']);
        $this->assertSame('http://www.lemonde.fr/musiques/article/2017/04/23/loin-de-la-politique-le-printemps-de-bourges-retombe-en-enfance_5115862_1654986.html', $content[0]['url']);

        $this->assertInternalType('int', $content[1]['entry']);
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
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $em->persist((new Entry($em->getReference(User::class, 1)))->setUrl('http://0.0.0.0/test-entry1'));

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
        $this->assertContains('API limit reached', $this->client->getResponse()->getContent());
    }

    public function testRePostEntryAndReUsePublishedAt()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $entry = new Entry($em->getReference(User::class, 1));
        $entry->setTitle('Antoine de Caunes : « Je veux avoir le droit de tâtonner »');
        $entry->setContent('hihi');
        $entry->setUrl('http://www.lemonde.fr/m-perso/article/2017/06/25/antoine-de-caunes-je-veux-avoir-le-droit-de-tatonner_5150728_4497916.html');
        $entry->setPublishedAt(new \DateTime('2017-06-26T07:46:02+0200'));
        $em->persist($entry);
        $em->flush();
        $em->clear();

        $this->client->request('POST', '/api/entries.json', [
            'url' => 'http://www.lemonde.fr/m-perso/article/2017/06/25/antoine-de-caunes-je-veux-avoir-le-droit-de-tatonner_5150728_4497916.html',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertSame('http://www.lemonde.fr/m-perso/article/2017/06/25/antoine-de-caunes-je-veux-avoir-le-droit-de-tatonner_5150728_4497916.html', $content['url']);
    }
}
