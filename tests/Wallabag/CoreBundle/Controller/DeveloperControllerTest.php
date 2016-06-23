<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class DeveloperControllerTest extends WallabagCoreTestCase
{
    public function testCreateClient()
    {
        $this->logInAs('admin');
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $nbClients = $em->getRepository('WallabagApiBundle:Client')->findAll();

        $crawler = $client->request('GET', '/developer/client/create');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[type=submit]')->form();

        $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $newNbClients = $em->getRepository('WallabagApiBundle:Client')->findAll();
        $this->assertGreaterThan(count($nbClients), count($newNbClients));
    }

    public function testListingClient()
    {
        $this->logInAs('admin');
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $nbClients = $em->getRepository('WallabagApiBundle:Client')->findAll();

        $crawler = $client->request('GET', '/developer');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(count($nbClients), $crawler->filter('ul[class=collapsible] li')->count());
    }

    public function testDeveloperHowto()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/developer/howto/first-app');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRemoveClient()
    {
        $this->logInAs('admin');
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $nbClients = $em->getRepository('WallabagApiBundle:Client')->findAll();

        $crawler = $client->request('GET', '/developer');

        $link = $crawler
            ->filter('div[class=collapsible-body] p a')
            ->eq(0)
            ->link()
        ;

        $client->click($link);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $newNbClients = $em->getRepository('WallabagApiBundle:Client')->findAll();
        $this->assertGreaterThan(count($newNbClients), count($nbClients));
    }
}
