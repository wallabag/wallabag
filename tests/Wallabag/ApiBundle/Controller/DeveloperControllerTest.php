<?php

namespace Tests\Wallabag\ApiBundle\Controller;

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

        $data = [
            'client[name]' => 'My app',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $newNbClients = $em->getRepository('WallabagApiBundle:Client')->findAll();
        $this->assertGreaterThan(count($nbClients), count($newNbClients));

        $this->assertGreaterThan(1, $alert = $crawler->filter('.settings ul li strong')->extract(['_text']));
        $this->assertContains('My app', $alert[0]);
    }

    /**
     * @depends testCreateClient
     */
    public function testCreateToken()
    {
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $apiClient = $em->getRepository('WallabagApiBundle:Client')->findOneByName('My app');

        $client->request('POST', '/oauth/v2/token', [
            'grant_type' => 'password',
            'client_id' => $apiClient->getPublicId(),
            'client_secret' => $apiClient->getSecret(),
            'username' => 'admin',
            'password' => 'mypassword',
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('refresh_token', $data);
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
