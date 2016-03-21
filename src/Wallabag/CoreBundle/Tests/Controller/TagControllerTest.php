<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;

class TagControllerTest extends WallabagCoreTestCase
{
    public $tagName = 'opensource';

    public function testList()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/tag/list');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAddTagToEntry()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUsernameAndNotArchived('admin');

        $crawler = $client->request('GET', '/view/'.$entry->getId());

        $form = $crawler->filter('form[name=tag]')->form();

        $data = array(
            'tag[label]' => $this->tagName,
        );

        $client->submit($form, $data);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $this->assertEquals(1, count($entry->getTags()));

        # tag already exists and already assigned
        $client->submit($form, $data);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $newEntry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry->getId());

        $this->assertEquals(1, count($newEntry->getTags()));

        # tag already exists but still not assigned to this entry
        $data = array(
            'tag[label]' => 'foo',
        );

        $client->submit($form, $data);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $newEntry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry->getId());

        $this->assertEquals(2, count($newEntry->getTags()));
    }

    public function testRemoveTagFromEntry()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUsernameAndNotArchived('admin');

        $tag = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Tag')
            ->findOneByEntryAndTagLabel($entry, $this->tagName);

        $client->request('GET', '/remove-tag/'.$entry->getId().'/'.$tag->getId());

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $this->assertNotContains($this->tagName, $entry->getTags());

        $client->request('GET', '/remove-tag/'.$entry->getId().'/'.$tag->getId());

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
