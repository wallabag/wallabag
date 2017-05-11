<?php

namespace Tests\Wallabag\ApiBundle\Controller;

use Tests\Wallabag\ApiBundle\WallabagApiTestCase;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Helper\ContentProxy;

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

        $this->client->request('GET', '/api/entries/'.$entry->getId().'.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($entry->getTitle(), $content['title']);
        $this->assertEquals($entry->getUrl(), $content['url']);
        $this->assertCount(count($entry->getTags()), $content['tags']);
        $this->assertEquals($entry->getUserName(), $content['user_name']);
        $this->assertEquals($entry->getUserEmail(), $content['user_email']);
        $this->assertEquals($entry->getUserId(), $content['user_id']);

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
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

        $this->client->request('GET', '/api/entries/'.$entry->getId().'/export.epub');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // epub format got the content type in the content
        $this->assertContains('application/epub', $this->client->getResponse()->getContent());
        $this->assertEquals('application/epub+zip', $this->client->getResponse()->headers->get('Content-Type'));

        // re-auth client for mobi
        $client = $this->createAuthorizedClient();
        $client->request('GET', '/api/entries/'.$entry->getId().'/export.mobi');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertEquals('application/x-mobipocket-ebook', $client->getResponse()->headers->get('Content-Type'));

        // re-auth client for pdf
        $client = $this->createAuthorizedClient();
        $client->request('GET', '/api/entries/'.$entry->getId().'/export.pdf');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertContains('PDF-', $client->getResponse()->getContent());
        $this->assertEquals('application/pdf', $client->getResponse()->headers->get('Content-Type'));

        // re-auth client for pdf
        $client = $this->createAuthorizedClient();
        $client->request('GET', '/api/entries/'.$entry->getId().'/export.txt');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertContains('text/plain', $client->getResponse()->headers->get('Content-Type'));

        // re-auth client for pdf
        $client = $this->createAuthorizedClient();
        $client->request('GET', '/api/entries/'.$entry->getId().'/export.csv');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

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

        $this->client->request('GET', '/api/entries/'.$entry->getId().'.json');

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testGetEntries()
    {
        $this->client->request('GET', '/api/entries');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertEquals(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
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
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
        $this->assertArrayHasKey('items', $content['_embedded']);
        $this->assertGreaterThanOrEqual(0, $content['total']);
        $this->assertEquals(1, $content['page']);
        $this->assertEquals(2, $content['limit']);
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
        }

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetEntriesOnPageTwo()
    {
        $this->client->request('GET', '/api/entries', [
            'page' => 2,
            'perPage' => 2,
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(0, $content['total']);
        $this->assertEquals(2, $content['page']);
        $this->assertEquals(2, $content['limit']);
    }

    public function testGetStarredEntries()
    {
        $this->client->request('GET', '/api/entries', ['starred' => 1, 'sort' => 'updated']);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertEquals(1, $content['page']);
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

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetArchiveEntries()
    {
        $this->client->request('GET', '/api/entries', ['archive' => 1]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertEquals(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertContains('archive=1', $content['_links'][$link]['href']);
        }

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetTaggedEntries()
    {
        $this->client->request('GET', '/api/entries', ['tags' => 'foo,bar']);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertEquals(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertContains('tags='.urlencode('foo,bar'), $content['_links'][$link]['href']);
        }

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetDatedEntries()
    {
        $this->client->request('GET', '/api/entries', ['since' => 1443274283]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertEquals(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertContains('since=1443274283', $content['_links'][$link]['href']);
        }

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetDatedSupEntries()
    {
        $future = new \DateTime(date('Y-m-d H:i:s'));
        $this->client->request('GET', '/api/entries', ['since' => $future->getTimestamp() + 1000]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
        $this->assertEmpty($content['_embedded']['items']);
        $this->assertEquals(0, $content['total']);
        $this->assertEquals(1, $content['page']);
        $this->assertEquals(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertContains('since='.($future->getTimestamp() + 1000), $content['_links'][$link]['href']);
        }

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
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

        $this->client->request('DELETE', '/api/entries/'.$entry->getId().'.json');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($entry->getTitle(), $content['title']);
        $this->assertEquals($entry->getUrl(), $content['url']);

        // We'll try to delete this entry again
        $this->client->request('DELETE', '/api/entries/'.$entry->getId().'.json');

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testPostEntry()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html',
            'tags' => 'google',
            'title' => 'New title for my article',
            'content' => 'my content',
            'language' => 'de_DE',
            'published_at' => '2016-09-08T11:55:58+0200',
            'authors' => 'bob,helen',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertEquals('http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html', $content['url']);
        $this->assertEquals(false, $content['is_archived']);
        $this->assertEquals(false, $content['is_starred']);
        $this->assertEquals('New title for my article', $content['title']);
        $this->assertEquals(1, $content['user_id']);
        $this->assertCount(2, $content['tags']);
        $this->assertSame('my content', $content['content']);
        $this->assertSame('de_DE', $content['language']);
        $this->assertSame('2016-09-08T11:55:58+0200', $content['published_at']);
        $this->assertCount(2, $content['published_by']);
        $this->assertContains('bob', $content['published_by']);
        $this->assertContains('helen', $content['published_by']);
    }

    public function testPostSameEntry()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html',
            'archive' => '1',
            'tags' => 'google, apple',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertEquals('http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html', $content['url']);
        $this->assertEquals(true, $content['is_archived']);
        $this->assertEquals(false, $content['is_starred']);
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

            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
            $content = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertGreaterThan(0, $content['id']);
            $this->assertEquals('http://www.example.com/', $content['url']);
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
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html',
            'archive' => '1',
            'starred' => '1',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertEquals('http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html', $content['url']);
        $this->assertEquals(true, $content['is_archived']);
        $this->assertEquals(true, $content['is_starred']);
        $this->assertEquals(1, $content['user_id']);
    }

    public function testPostArchivedAndStarredEntryWithoutQuotes()
    {
        $this->client->request('POST', '/api/entries.json', [
            'url' => 'http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html',
            'archive' => 0,
            'starred' => 1,
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertEquals('http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html', $content['url']);
        $this->assertEquals(false, $content['is_archived']);
        $this->assertEquals(true, $content['is_starred']);
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

        // hydrate the tags relations
        $nbTags = count($entry->getTags());

        $this->client->request('PATCH', '/api/entries/'.$entry->getId().'.json', [
            'title' => 'New awesome title',
            'tags' => 'new tag '.uniqid(),
            'starred' => '1',
            'archive' => '0',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($entry->getId(), $content['id']);
        $this->assertEquals($entry->getUrl(), $content['url']);
        $this->assertEquals('New awesome title', $content['title']);
        $this->assertGreaterThan($nbTags, count($content['tags']));
        $this->assertEquals(1, $content['user_id']);
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

        // hydrate the tags relations
        $nbTags = count($entry->getTags());

        $this->client->request('PATCH', '/api/entries/'.$entry->getId().'.json', [
            'title' => 'New awesome title',
            'tags' => 'new tag '.uniqid(),
            'starred' => 1,
            'archive' => 0,
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($entry->getId(), $content['id']);
        $this->assertEquals($entry->getUrl(), $content['url']);
        $this->assertEquals('New awesome title', $content['title']);
        $this->assertGreaterThan($nbTags, count($content['tags']));
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

        $this->client->request('GET', '/api/entries/'.$entry->getId().'/tags');

        $this->assertEquals(json_encode($tags, JSON_HEX_QUOT), $this->client->getResponse()->getContent());
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

        $this->client->request('POST', '/api/entries/'.$entry->getId().'/tags', ['tags' => $newTags]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('tags', $content);
        $this->assertEquals($nbTags + 3, count($content['tags']));

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

        $this->client->request('DELETE', '/api/entries/'.$entry->getId().'/tags/'.$tag->getId().'.json');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('tags', $content);
        $this->assertEquals($nbTags - 1, count($content['tags']));
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

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(true, $content['is_archived']);
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

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(true, $content['is_starred']);
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

        $this->client->request('PATCH', '/api/entries/'.$entry->getId().'.json', [
            'title' => $entry->getTitle().'++',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(true, $content['is_archived']);
    }

    public function testSaveIsStarredAfterPatch()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(['user' => 1, 'isStarred' => true]);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }
        $this->client->request('PATCH', '/api/entries/'.$entry->getId().'.json', [
            'title' => $entry->getTitle().'++',
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(true, $content['is_starred']);
    }

    public function testGetEntriesExists()
    {
        $this->client->request('GET', '/api/entries/exists?url=http://0.0.0.0/entry2');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(2, $content['exists']);
    }

    public function testGetEntriesExistsWithManyUrls()
    {
        $url1 = 'http://0.0.0.0/entry2';
        $url2 = 'http://0.0.0.0/entry10';
        $this->client->request('GET', '/api/entries/exists?urls[]='.$url1.'&urls[]='.$url2);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey($url1, $content);
        $this->assertArrayHasKey($url2, $content);
        $this->assertEquals(2, $content[$url1]);
        $this->assertEquals(false, $content[$url2]);
    }

    public function testGetEntriesExistsWhichDoesNotExists()
    {
        $this->client->request('GET', '/api/entries/exists?url=http://google.com/entry2');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(false, $content['exists']);
    }

    public function testGetEntriesExistsWithNoUrl()
    {
        $this->client->request('GET', '/api/entries/exists?url=');

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testReloadEntryErrorWhileFetching()
    {
        $entry = $this->client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId('http://0.0.0.0/entry4', 1);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('PATCH', '/api/entries/'.$entry->getId().'/reload.json');
        $this->assertEquals(304, $this->client->getResponse()->getStatusCode());
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

        $this->client->request('PATCH', '/api/entries/'.$json['id'].'/reload.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($content['title']);

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
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

        $this->client->request('POST', '/api/entries/tags/lists?list='.json_encode($list));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertInternalType('int', $content[0]['entry']);
        $this->assertEquals('http://0.0.0.0/entry4', $content[0]['url']);

        $entry = $this->client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId('http://0.0.0.0/entry4', 1);

        $tags = $entry->getTags();
        $this->assertCount(4, $tags);
    }

    public function testDeleteEntriesTagsListAction()
    {
        $entry = $this->client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId('http://0.0.0.0/entry4', 1);

        $tags = $entry->getTags();

        $this->assertCount(4, $tags);

        $list = [
            [
                'url' => 'http://0.0.0.0/entry4',
                'tags' => 'new tag 1, new tag 2',
            ],
        ];

        $this->client->request('DELETE', '/api/entries/tags/list?list='.json_encode($list));
    }

    public function testPostEntriesListAction()
    {
        $list = [
            'http://www.lemonde.fr/musiques/article/2017/04/23/loin-de-la-politique-le-printemps-de-bourges-retombe-en-enfance_5115862_1654986.html',
            'http://0.0.0.0/entry2',
        ];

        $this->client->request('POST', '/api/entries/lists?urls='.json_encode($list));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertInternalType('int', $content[0]['entry']);
        $this->assertEquals('http://www.lemonde.fr/musiques/article/2017/04/23/loin-de-la-politique-le-printemps-de-bourges-retombe-en-enfance_5115862_1654986.html', $content[0]['url']);

        $this->assertInternalType('int', $content[1]['entry']);
        $this->assertEquals('http://0.0.0.0/entry2', $content[1]['url']);
    }

    public function testDeleteEntriesListAction()
    {
        $list = [
            'http://www.lemonde.fr/musiques/article/2017/04/23/loin-de-la-politique-le-printemps-de-bourges-retombe-en-enfance_5115862_1654986.html',
            'http://0.0.0.0/entry3',
        ];

        $this->client->request('DELETE', '/api/entries/list?urls='.json_encode($list));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($content[0]['entry']);
        $this->assertEquals('http://www.lemonde.fr/musiques/article/2017/04/23/loin-de-la-politique-le-printemps-de-bourges-retombe-en-enfance_5115862_1654986.html', $content[0]['url']);

        $this->assertFalse($content[1]['entry']);
        $this->assertEquals('http://0.0.0.0/entry3', $content[1]['url']);
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

        $this->client->request('POST', '/api/entries/lists?urls='.json_encode($list));

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $this->assertContains('API limit reached', $this->client->getResponse()->getContent());
    }
}
