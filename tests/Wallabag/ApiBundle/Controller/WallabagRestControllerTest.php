<?php

namespace Tests\Wallabag\ApiBundle\Controller;

use Tests\Wallabag\ApiBundle\WallabagApiTestCase;
use Wallabag\CoreBundle\Entity\Tag;

class WallabagRestControllerTest extends WallabagApiTestCase
{
    protected static $salt;

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

        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
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

        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
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

        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
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

        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
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

        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
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

        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
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

        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
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

        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    public function testDeleteEntry()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

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
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertEquals('http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html', $content['url']);
        $this->assertEquals(false, $content['is_archived']);
        $this->assertEquals(false, $content['is_starred']);
        $this->assertEquals('New title for my article', $content['title']);
        $this->assertEquals(1, $content['user_id']);
        $this->assertCount(1, $content['tags']);
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
        $this->assertCount(2, $content['tags']);
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

    public function testGetUserTags()
    {
        $this->client->request('GET', '/api/tags.json');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content);
        $this->assertArrayHasKey('id', $content[0]);
        $this->assertArrayHasKey('label', $content[0]);

        return end($content);
    }

    /**
     * @depends testGetUserTags
     */
    public function testDeleteUserTag($tag)
    {
        $tagName = $tag['label'];

        $this->client->request('DELETE', '/api/tags/'.$tag['id'].'.json');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('label', $content);
        $this->assertEquals($tag['label'], $content['label']);
        $this->assertEquals($tag['slug'], $content['slug']);

        $entries = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findAllByTagId($this->user->getId(), $tag['id']);

        $this->assertCount(0, $entries);

        $tag = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Tag')
            ->findOneByLabel($tagName);

        $this->assertNull($tag, $tagName.' was removed because it begun an orphan tag');
    }

    public function testDeleteTagByLabel()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneWithTags($this->user->getId());

        $entry = $entry[0];

        $tag = new Tag();
        $tag->setLabel('Awesome tag for test');
        $em->persist($tag);

        $entry->addTag($tag);

        $em->persist($entry);
        $em->flush();

        $this->client->request('DELETE', '/api/tag/label.json', ['tag' => $tag->getLabel()]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('label', $content);
        $this->assertEquals($tag->getLabel(), $content['label']);
        $this->assertEquals($tag->getSlug(), $content['slug']);

        $entries = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findAllByTagId($this->user->getId(), $tag->getId());

        $this->assertCount(0, $entries);
    }

    public function testDeleteTagByLabelNotFound()
    {
        $this->client->request('DELETE', '/api/tag/label.json', ['tag' => 'does not exist']);

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteTagsByLabel()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneWithTags($this->user->getId());

        $entry = $entry[0];

        $tag = new Tag();
        $tag->setLabel('Awesome tag for tagsLabel');
        $em->persist($tag);

        $tag2 = new Tag();
        $tag2->setLabel('Awesome tag for tagsLabel 2');
        $em->persist($tag2);

        $entry->addTag($tag);
        $entry->addTag($tag2);

        $em->persist($entry);
        $em->flush();

        $this->client->request('DELETE', '/api/tags/label.json', ['tags' => $tag->getLabel().','.$tag2->getLabel()]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $content);

        $this->assertArrayHasKey('label', $content[0]);
        $this->assertEquals($tag->getLabel(), $content[0]['label']);
        $this->assertEquals($tag->getSlug(), $content[0]['slug']);

        $this->assertArrayHasKey('label', $content[1]);
        $this->assertEquals($tag2->getLabel(), $content[1]['label']);
        $this->assertEquals($tag2->getSlug(), $content[1]['slug']);

        $entries = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findAllByTagId($this->user->getId(), $tag->getId());

        $this->assertCount(0, $entries);

        $entries = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findAllByTagId($this->user->getId(), $tag2->getId());

        $this->assertCount(0, $entries);
    }

    public function testDeleteTagsByLabelNotFound()
    {
        $this->client->request('DELETE', '/api/tags/label.json', ['tags' => 'does not exist']);

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testGetVersion()
    {
        $this->client->request('GET', '/api/version');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($this->client->getContainer()->getParameter('wallabag_core.version'), $content);
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

        $this->assertEquals(true, $content['exists']);
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
}
