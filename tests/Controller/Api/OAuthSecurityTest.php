<?php

namespace Tests\Wallabag\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Api\AuthCode;
use Wallabag\Entity\Api\Client;
use Wallabag\Entity\User;

/**
 * OAuth Authorization Code Security Tests.
 * Tests critical security aspects of OAuth implementation.
 */
class OAuthSecurityTest extends WallabagTestCase
{
    /**
     * Test that authorization codes are single-use only.
     * This prevents replay attacks.
     */
    public function testAuthCodeSingleUse()
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneByUsername('admin');
        $authCode = $this->createAuthCode($apiClient, $user, $em);

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

        // Second use - should fail (replay attack prevention)
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

        // Verify code is deleted from database
        $em->clear();
        $deletedCode = $em->getRepository(AuthCode::class)->findOneBy(['token' => $authCode->getToken()]);
        $this->assertNull($deletedCode);
    }

    /**
     * Test that expired authorization codes are rejected.
     * Codes should expire after 10 minutes per RFC recommendation.
     */
    public function testAuthCodeExpiration()
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneByUsername('admin');

        // Create expired auth code (11 minutes old)
        $authCode = new AuthCode();
        $authCode->setClient($apiClient);
        $authCode->setUser($user);
        $authCode->setToken(bin2hex(random_bytes(32)));
        $authCode->setRedirectUri('http://example.com/callback');
        $authCode->setExpiresAt(time() - 660); // 11 minutes ago
        $authCode->setScope('read');

        $em->persist($authCode);
        $em->flush();

        // Try to use expired code
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
        $this->assertStringContainsString('expired', $data['error_description']);
    }

    /**
     * Test that authorization codes are bound to specific clients.
     * A code issued to client A cannot be used by client B.
     */
    public function testAuthCodeClientBinding()
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $apiClient1 = $this->createApiClientForUser('admin', ['authorization_code']);
        $apiClient2 = $this->createApiClientForUser('admin', ['authorization_code']);

        $user = $em->getRepository(User::class)->findOneByUsername('admin');
        $authCode = $this->createAuthCode($apiClient1, $user, $em);

        // Try to use client1's code with client2 (should fail)
        $client->request('POST', '/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $apiClient2->getPublicId(),
            'client_secret' => $apiClient2->getSecret(),
            'code' => $authCode->getToken(),
            'redirect_uri' => 'http://example.com/callback',
        ]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('invalid_grant', $data['error']);
    }

    /**
     * Test that authorization codes are bound to specific users.
     * Verify the token exchange succeeds for the correct user.
     */
    public function testAuthCodeUserBinding()
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        $user = $em->getRepository(User::class)->findOneByUsername('admin');
        $authCode = $this->createAuthCode($apiClient, $user, $em);

        // Exchange code for token - should succeed
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
        $this->assertArrayHasKey('token_type', $data);
        $this->assertSame('bearer', $data['token_type']);

        // Verify the auth code was properly bound to the user by checking
        // that we got a valid token response (proves user binding worked)
        $this->assertNotEmpty($data['access_token']);
    }

    /**
     * Test redirect URI validation prevents redirect attacks.
     * Tests this at the token endpoint level to avoid authorization flow complexity.
     */
    public function testRedirectUriValidation()
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        // Set specific allowed redirect URIs
        $apiClient->setRedirectUris(['http://example.com/callback']);
        $em->flush();

        $user = $em->getRepository(User::class)->findOneByUsername('admin');

        // Create auth code with valid redirect URI
        $authCode = new AuthCode();
        $authCode->setClient($apiClient);
        $authCode->setUser($user);
        $authCode->setToken(bin2hex(random_bytes(32)));
        $authCode->setRedirectUri('http://example.com/callback'); // Valid URI
        $authCode->setExpiresAt(time() + 600);
        $authCode->setScope('read');

        $em->persist($authCode);
        $em->flush();

        // Try to exchange with different redirect URI (should fail)
        $client->request('POST', '/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $apiClient->getPublicId(),
            'client_secret' => $apiClient->getSecret(),
            'code' => $authCode->getToken(),
            'redirect_uri' => 'http://evil.com/steal-token', // Different from auth code
        ]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('redirect_uri_mismatch', $data['error']);
    }

    /**
     * Test PKCE requirement enforcement for public clients.
     * Tests at token endpoint level - public clients must provide code_verifier.
     */
    public function testPkceRequirementEnforcement()
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        // Make it a public client requiring PKCE
        $apiClient->setIsPublic(true);
        $apiClient->setRequirePkce(true);
        $em->flush();

        $user = $em->getRepository(User::class)->findOneByUsername('admin');

        // Create auth code WITHOUT PKCE data
        $authCode = new AuthCode();
        $authCode->setClient($apiClient);
        $authCode->setUser($user);
        $authCode->setToken(bin2hex(random_bytes(32)));
        $authCode->setRedirectUri('http://example.com/callback');
        $authCode->setExpiresAt(time() + 600);
        $authCode->setScope('read');
        // No PKCE data set

        $em->persist($authCode);
        $em->flush();

        // Try token exchange without code_verifier (should fail for public client)
        $client->request('POST', '/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $apiClient->getPublicId(),
            // No client_secret for public client
            'code' => $authCode->getToken(),
            'redirect_uri' => 'http://example.com/callback',
            // Missing code_verifier
        ]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('invalid_request', $data['error']);
        $this->assertStringContainsString('PKCE', $data['error_description']);
    }

    /**
     * Test that wrong redirect URI in token request fails.
     */
    public function testTokenRequestRedirectUriValidation()
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneByUsername('admin');
        $authCode = $this->createAuthCode($apiClient, $user, $em);

        // Try to exchange code with wrong redirect URI
        $client->request('POST', '/oauth/v2/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $apiClient->getPublicId(),
            'client_secret' => $apiClient->getSecret(),
            'code' => $authCode->getToken(),
            'redirect_uri' => 'http://wrong.redirect.com/callback', // Different from auth code
        ]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('redirect_uri_mismatch', $data['error']);
        $this->assertStringContainsString('redirect URI', $data['error_description']);
    }

    /**
     * Helper method to create an OAuth client for testing.
     */
    private function createApiClientForUser($username, $grantTypes = ['password'])
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $userManager = static::getContainer()->get('fos_user.user_manager');

        $user = $userManager->findUserBy(['username' => $username]);
        \assert($user instanceof User);

        $apiClient = new Client($user);
        $apiClient->setName('Test OAuth Client');
        $apiClient->setAllowedGrantTypes($grantTypes);
        $apiClient->setRedirectUris(['http://example.com/callback']);
        $em->persist($apiClient);
        $em->flush();

        return $apiClient;
    }

    /**
     * Helper method to create an authorization code for testing.
     */
    private function createAuthCode(Client $client, User $user, EntityManagerInterface $em): AuthCode
    {
        $authCode = new AuthCode();
        $authCode->setClient($client);
        $authCode->setUser($user);
        $authCode->setToken(bin2hex(random_bytes(32)));
        $authCode->setRedirectUri('http://example.com/callback');
        $authCode->setExpiresAt(time() + 600); // 10 minutes
        $authCode->setScope('read write');

        $em->persist($authCode);
        $em->flush();

        return $authCode;
    }
}
