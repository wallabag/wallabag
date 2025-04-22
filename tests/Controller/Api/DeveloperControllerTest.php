<?php

namespace Tests\Wallabag\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Api\Client;
use Wallabag\Entity\User;

class DeveloperControllerTest extends WallabagTestCase
{
    public function testCreateClient()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $nbClients = $em->getRepository(Client::class)->findAll();

        $crawler = $client->request('GET', '/developer/client/create');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=client_save]')->form();

        $data = [
            'client[name]' => 'My app',
        ];

        $crawler = $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $newNbClients = $em->getRepository(Client::class)->findAll();
        $this->assertGreaterThan(\count($nbClients), \count($newNbClients));

        $this->assertGreaterThan(1, $alert = $crawler->filter('.settings table strong')->extract(['_text']));
        $this->assertStringContainsString('My app', $alert[0]);
    }

    public function testCreateToken()
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin');

        $client->request('POST', '/oauth/v2/token', [
            'grant_type' => 'password',
            'client_id' => $apiClient->getPublicId(),
            'client_secret' => $apiClient->getSecret(),
            'username' => 'admin',
            'password' => 'mypassword',
        ]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $data = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('expires_in', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('refresh_token', $data);
    }

    public function testCreateTokenWithBadClientId()
    {
        $client = $this->getTestClient();
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
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $nbClients = $em->getRepository(Client::class)->findAll();

        $crawler = $client->request('GET', '/developer');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(\count($nbClients), $crawler->filter('ul[class=collapsible] li')->count());
    }

    public function testDeveloperHowto()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/developer/howto/first-app');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testRemoveClient()
    {
        $client = $this->getTestClient();
        $adminApiClient = $this->createApiClientForUser('admin');
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        // Try to remove an admin's client with a wrong user
        $this->logInAs('bob');
        $client->request('GET', '/developer');
        $this->assertStringContainsString('no_client', $client->getResponse()->getContent());

        $this->logInAs('bob');
        $client->request('POST', '/developer/client/delete/' . $adminApiClient->getId());
        $this->assertSame(400, $client->getResponse()->getStatusCode());

        // Try to remove the admin's client with the good user
        $this->logInAs('admin');
        $crawler = $client->request('GET', '/developer');

        $form = $crawler->filter('form[name=delete-client]')->form();

        $client->submit($form);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $this->assertNull(
            $em->getRepository(Client::class)->find($adminApiClient->getId()),
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
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $userManager = static::getContainer()->get('fos_user.user_manager');

        $user = $userManager->findUserBy(['username' => $username]);
        \assert($user instanceof User);

        $apiClient = new Client($user);
        $apiClient->setName('My app');
        $apiClient->setAllowedGrantTypes($grantTypes);
        $em->persist($apiClient);
        $em->flush();

        return $apiClient;
    }
}
