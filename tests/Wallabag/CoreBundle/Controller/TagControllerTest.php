<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;

class TagControllerTest extends WallabagCoreTestCase
{
    public $tagName = 'opensource';
    public $caseTagName = 'OpenSource';

    public function testList()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/tag/list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testAddTagToEntry()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/foo');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $form = $crawler->filter('form[name=tag]')->form();

        $data = [
            'tag[label]' => $this->caseTagName,
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        // be sure to reload the entry
        $entry = $this->getEntityManager()->getRepository(Entry::class)->find($entry->getId());
        $this->assertCount(1, $entry->getTags());
        $this->assertContains($this->tagName, $entry->getTags());

        // tag already exists and already assigned
        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $entry = $this->getEntityManager()->getRepository(Entry::class)->find($entry->getId());
        $this->assertCount(1, $entry->getTags());

        // tag already exists but still not assigned to this entry
        $data = [
            'tag[label]' => 'foo bar',
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $entry = $this->getEntityManager()->getRepository(Entry::class)->find($entry->getId());
        $this->assertCount(2, $entry->getTags());
    }

    public function testAddMultipleTagToEntry()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId('http://0.0.0.0/entry2', $this->getLoggedInUserId());

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $form = $crawler->filter('form[name=tag]')->form();

        $data = [
            'tag[label]' => 'foo2, Bar2',
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $newEntry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry->getId());

        $tags = $newEntry->getTags()->toArray();
        foreach ($tags as $key => $tag) {
            $tags[$key] = $tag->getLabel();
        }

        $this->assertGreaterThanOrEqual(2, \count($tags));
        $this->assertNotFalse(array_search('foo2', $tags, true), 'Tag foo2 is assigned to the entry');
        $this->assertNotFalse(array_search('bar2', $tags, true), 'Tag bar2 is assigned to the entry');
    }

    public function testRemoveTagFromEntry()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $tag = new Tag();
        $tag->setLabel($this->tagName);
        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/foo');
        $entry->addTag($tag);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        // We make a first request to set an history and test redirection after tag deletion
        $client->request('GET', '/view/' . $entry->getId());
        $entryUri = $client->getRequest()->getUri();
        $client->request('GET', '/remove-tag/' . $entry->getId() . '/' . $tag->getId());

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame($entryUri, $client->getResponse()->getTargetUrl());

        // re-retrieve the entry to be sure to get fresh data from database (mostly for tags)
        $entry = $this->getEntityManager()->getRepository(Entry::class)->find($entry->getId());
        $this->assertNotContains($this->tagName, $entry->getTags());

        $client->request('GET', '/remove-tag/' . $entry->getId() . '/' . $tag->getId());

        $this->assertSame(404, $client->getResponse()->getStatusCode());

        $tag = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Tag')
            ->findOneByLabel($this->tagName);

        $this->assertNull($tag, $this->tagName . ' was removed because it begun an orphan tag');
    }

    public function testShowEntriesForTagAction()
    {
        $this->logInAs('admin');
        $client = $this->getClient();
        $em = $client->getContainer()
            ->get('doctrine.orm.entity_manager');

        $tag = new Tag();
        $tag->setLabel($this->tagName);

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId('http://0.0.0.0/entry4', $this->getLoggedInUserId());

        $tag->addEntry($entry);

        $em->persist($entry);
        $em->persist($tag);
        $em->flush();

        $tag = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Tag')
            ->findOneByEntryAndTagLabel($entry, $this->tagName);

        $crawler = $client->request('GET', '/tag/list/' . $tag->getSlug());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('[id*="entry-"]'));

        $entry->removeTag($tag);
        $em->remove($tag);
        $em->flush();
    }

    public function testRenameTagUsingTheFormInsideTagList()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $tag = new Tag();
        $tag->setLabel($this->tagName);
        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/foo');
        $entry->addTag($tag);
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        // We make a first request to set an history and test redirection after tag deletion
        $crawler = $client->request('GET', '/tag/list');
        $form = $crawler->filter('#tag-' . $tag->getId() . ' form')->form();

        $data = [
            'tag[label]' => 'specific label',
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $freshEntry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry->getId());

        $tags = $freshEntry->getTags()->toArray();
        foreach ($tags as $key => $item) {
            $tags[$key] = $item->getLabel();
        }

        $this->assertFalse(array_search($tag->getLabel(), $tags, true), 'Previous tag is not attach to entry anymore.');

        $newTag = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Tag')
            ->findOneByLabel('specific label');
        $this->assertInstanceOf(Tag::class, $newTag, 'Tag "specific label" exists.');
        $this->assertTrue($newTag->hasEntry($freshEntry), 'Tag "specific label" is assigned to the entry.');
    }

    public function testAddUnicodeTagLabel()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/tag-caché');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $form = $crawler->filter('form[name=tag]')->form();

        $data = [
            'tag[label]' => 'cache',
        ];

        $client->submit($form, $data);

        $crawler = $client->request('GET', '/view/' . $entry->getId());

        $form = $crawler->filter('form[name=tag]')->form();

        $data = [
            'tag[label]' => 'caché',
        ];

        $client->submit($form, $data);

        $newEntry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry->getId());

        $tags = $newEntry->getTags()->toArray();
        foreach ($tags as $key => $tag) {
            $tags[$key] = $tag->getLabel();
        }

        $this->assertGreaterThanOrEqual(2, \count($tags));
        $this->assertNotFalse(array_search('cache', $tags, true), 'Tag cache is assigned to the entry');
        $this->assertNotFalse(array_search('caché', $tags, true), 'Tag caché is assigned to the entry');
    }
}
