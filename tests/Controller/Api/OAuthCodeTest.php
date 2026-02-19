<?php

namespace Tests\Wallabag\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Api\AuthCode;
use Wallabag\Entity\Api\Client;
use Wallabag\Entity\User;

class OAuthCodeTest extends WallabagTestCase
{
    public function testAuthCodeSingleUse()
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneByUsername('admin');

        // Create auth code manually
        $authCode = new AuthCode();
        $authCode->setClient($apiClient);
        $authCode->setUser($user);
        $authCode->setToken(bin2hex(random_bytes(32)));
        $authCode->setRedirectUri('http://example.com/callback');
        $authCode->setExpiresAt(time() + 600);
        $authCode->setScope('read');

        $em->persist($authCode);
        $em->flush();

        // First use - should succeed
        $client->request('POST', '/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $apiClient->getPublicId(),
            'client_secret' => $apiClient->getSecret(),
            'code' => $authCode->getToken(),
            'redirect_uri' => 'http://example.com/callback',
        ]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('access_token', $data);

        // Second use - should fail (code is single-use)
        $client->request('POST', '/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $apiClient->getPublicId(),
            'client_secret' => $apiClient->getSecret(),
            'code' => $authCode->getToken(),
            'redirect_uri' => 'http://example.com/callback',
        ]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('invalid_grant', $data['error']);
    }

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
        $apiClient->setRedirectUris(['http://example.com/callback']);
        $em->persist($apiClient);
        $em->flush();

        return $apiClient;
    }
}
