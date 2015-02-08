<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagTestCase;

class EntryControllerTest extends WallabagTestCase
{
    public function testLogin()
    {
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('login', $client->getResponse()->headers->get('location'));
    }

    public function testGetNew()
    {
        $this->logIn();
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertCount(1, $crawler->filter('input[type=url]'));
        $this->assertCount(1, $crawler->filter('button[type=submit]'));
    }

    public function testPostNewEmpty()
    {
        $this->logIn();
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[type=submit]')->form();

        $crawler = $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $alert = $crawler->filter('form ul li')->extract(array('_text')));
        $this->assertEquals('This value should not be blank.', $alert[0]);
    }

    public function testPostNewOk()
    {
        $this->logIn();
        $client = $this->getClient();

        $crawler = $client->request('GET', '/new');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[type=submit]')->form();

        $data = array(
            'form[url]' => 'https://www.mailjet.com/blog/mailjet-zapier-integrations-made-easy/',
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('h2 a')->extract(array('_text')));
        $this->assertContains('Mailjet', $alert[0]);
    }

    public function testArchive()
    {
        $this->logIn();
        $client = $this->getClient();

        $crawler = $client->request('GET', '/archive');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testStarred()
    {
        $this->logIn();
        $client = $this->getClient();

        $crawler = $client->request('GET', '/starred');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testView()
    {
        $this->logIn();
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByIsArchived(false);

        if (!$content) {
            $this->markTestSkipped('No content found in db.');
        }

        $crawler = $client->request('GET', '/view/'.$content->getId());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains($content->getTitle(), $client->getResponse()->getContent());
    }
}
