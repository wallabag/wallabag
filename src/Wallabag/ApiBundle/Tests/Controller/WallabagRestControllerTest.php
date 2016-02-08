<?php

namespace Wallabag\ApiBundle\Tests\Controller;

use Wallabag\ApiBundle\Tests\WallabagApiTestCase;

class WallabagRestControllerTest extends WallabagApiTestCase
{
    protected static $salt;

    public function testGetOneEntry()
    {
        $entry = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(array('user' => 1, 'isArchived' => false));

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $this->client->request('GET', '/api/entries/'.$entry->getId().'.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($entry->getTitle(), $content['title']);
        $this->assertEquals($entry->getUrl(), $content['url']);
        $this->assertCount(count($entry->getTags()), $content['tags']);

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
            ->findOneBy(array('user' => 2, 'isArchived' => false));

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
        $this->client->request('GET', '/api/entries', array('star' => 1, 'sort' => 'updated'));

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
        $this->client->request('GET', '/api/entries', array('archive' => 1));

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
        $this->client->request('POST', '/api/entries.json', array(
            'url' => 'http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html',
            'tags' => 'google',
        ));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertEquals('http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html', $content['url']);
        $this->assertEquals(false, $content['is_archived']);
        $this->assertEquals(false, $content['is_starred']);
        $this->assertCount(1, $content['tags']);
    }

    public function testPostArchivedEntry()
    {
        $this->client->request('POST', '/api/entries.json', array(
            'url' => 'http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html',
            'archive' => true,
            'starred' => false,
        ));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertEquals('http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html', $content['url']);
        $this->assertEquals(true, $content['is_archived']);
        $this->assertEquals(false, $content['is_starred']);
    }

    public function testPostEntryWithContent()
    {
        $this->client->request('POST', '/api/entries.json', array(
            'url' => 'http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html',
            'content' => 'This is a new content for my entry',
        ));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertEquals('http://www.lemonde.fr/idees/article/2016/02/08/preserver-la-liberte-d-expression-sur-les-reseaux-sociaux_4861503_3232.html', $content['url']);
        $this->assertEquals('This is a new content for my entry', $content['content']);
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

        $this->client->request('PATCH', '/api/entries/'.$entry->getId().'.json', array(
            'title' => 'New awesome title',
            'tags' => 'new tag '.uniqid(),
            'star' => true,
            'archive' => false,
        ));

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

        $tags = array();
        foreach ($entry->getTags() as $tag) {
            $tags[] = array('id' => $tag->getId(), 'label' => $tag->getLabel(), 'slug' => $tag->getSlug());
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

        $this->client->request('POST', '/api/entries/'.$entry->getId().'/tags', array('tags' => $newTags));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('tags', $content);
        $this->assertEquals($nbTags + 3, count($content['tags']));

        $entryDB = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry->getId());

        $tagsInDB = array();
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
}
