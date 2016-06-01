<?php

namespace Tests\Wallabag\ApiBundle\Controller;

use Tests\Wallabag\ApiBundle\WallabagApiTestCase;

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

    public function testGetStarredEntries()
    {
        $this->client->request('GET', '/api/entries', ['star' => 1, 'sort' => 'updated']);

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
            ->findOneWithTags(1);

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
            ->findOneWithTags(1);
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
        $this->client->request('DELETE', '/api/tags/'.$tag['id'].'.json');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('label', $content);
        $this->assertEquals($tag['label'], $content['label']);
        $this->assertEquals($tag['slug'], $content['slug']);

        $entries = $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findAllByTagId($this->user->getId(), $tag['id']);

        $this->assertCount(0, $entries);
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
}
