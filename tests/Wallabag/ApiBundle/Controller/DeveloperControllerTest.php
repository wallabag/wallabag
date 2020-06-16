<?php

namespace Tests\Wallabag\ApiBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\ApiBundle\Entity\Client;

class DeveloperControllerTest extends WallabagCoreTestCase
{
    public function testCreateClient()
    {
        $this->logInAs('admin');
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $nbClients = $em->getRepository('WallabagApiBundle:Client')->findAll();

        $crawler = $client->request('GET', '/developer/client/create');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=client_save]')->form();

        $data = [
            'client[name]' => 'My app',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $newNbClients = $em->getRepository('WallabagApiBundle:Client')->findAll();
        $this->assertGreaterThan(\count($nbClients), \count($newNbClients));

        $this->assertGreaterThan(1, $alert = $crawler->filter('.settings table strong')->extract(['_text']));
        $this->assertStringContainsString('My app', $alert[0]);
    }

    public function testCreateToken()
    {
        $client = $this->getClient();
        $apiClient = $this->createApiClientForUser('admin');

        $client->request('POST', '/oauth/v2/token', [
            'grant_type' => 'password',
            'client_id' => $apiClient->getPublicId(),
            'client_secret' => $apiClient->getSecret(),
            'username' => 'admin',
            'password' => 'mypassword',
        ]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('refresh_token', $data);
    }

    public function testCreateTokenWithBadClientId()
    {
        $client = $this->getClient();
        $client->request('POST', '/oauth/v2/token', [
            'grant_type' => 'password',
            'client_id' => '$WALLABAG_CLIENT_ID',
            'client_secret' => 'secret',
            'username' => 'admin',
            'password' => 'mypassword',
        ]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
    }

    public function testListingClient()
    {
        $this->logInAs('admin');
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $nbClients = $em->getRepository('WallabagApiBundle:Client')->findAll();

        $crawler = $client->request('GET', '/developer');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(\count($nbClients), $crawler->filter('ul[class=collapsible] li')->count());
    }

    public function testDeveloperHowto()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/developer/howto/first-app');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testRemoveClient()
    {
        $client = $this->getClient();
        $adminApiClient = $this->createApiClientForUser('admin');
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');

        // Try to remove an admin's client with a wrong user
        $this->logInAs('bob');
        $client->request('GET', '/developer');
        $this->assertStringContainsString('no_client', $client->getResponse()->getContent());

        $this->logInAs('bob');
        $client->request('GET', '/developer/client/delete/' . $adminApiClient->getId());
        $this->assertSame(403, $client->getResponse()->getStatusCode());

        // Try to remove the admin's client with the good user
        $this->logInAs('admin');
        $crawler = $client->request('GET', '/developer');

        $link = $crawler
            ->filter('div[class=collapsible-body] p a')
            ->eq(0)
            ->link()
        ;

        $client->click($link);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $this->assertNull(
            $em->getRepository('WallabagApiBundle:Client')->find($adminApiClient->getId()),
            'The client should have been removed'
        );
    }

    /**
     * @param string $username
     * @param array  $grantTypes
     *
     * @return Client
     */
    private function createApiClientForUser($username, $grantTypes = ['password'])
    {
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $userManager = $client->getContainer()->get('fos_user.user_manager.test');
        $user = $userManager->findUserBy(['username' => $username]);
        $apiClient = new Client($user);
        $apiClient->setName('My app');
        $apiClient->setAllowedGrantTypes($grantTypes);
        $em->persist($apiClient);
        $em->flush();

        return $apiClient;
    }
}
