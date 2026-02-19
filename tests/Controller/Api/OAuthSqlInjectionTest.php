<?php

namespace Tests\Wallabag\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Api\AuthCode;
use Wallabag\Entity\Api\Client;
use Wallabag\Entity\User;

/**
 * SQL injection security tests for OAuth endpoints.
 *
 * Tests various SQL injection attack vectors against OAuth parameters
 * to ensure proper parameter sanitization and prepared statement usage.
 */
class OAuthSqlInjectionTest extends WallabagTestCase
{
    /**
     * Test SQL injection attempts in authorization code parameter.
     */
    public function testSqlInjectionInAuthorizationCode(): void
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);

        $sqlInjectionPayloads = [
            // Basic SQL injection attempts
            "'; DROP TABLE oauth2_access_tokens; --",
            "' OR '1'='1",
            "' UNION SELECT * FROM users --",
            "'; DELETE FROM oauth2_clients WHERE id=1; --",

            // Advanced SQL injection attempts
            "' OR 1=1 UNION SELECT username, password FROM users --",
            "'; INSERT INTO oauth2_access_tokens VALUES (1, 'malicious', 'token'); --",
            "' AND (SELECT COUNT(*) FROM oauth2_clients) > 0 --",
            "'; UPDATE oauth2_clients SET secret='hacked' WHERE id=1; --",

            // Encoded SQL injection attempts
            '%27%20OR%20%271%27%3D%271',
            '%27%3B%20DROP%20TABLE%20users%3B%20--',

            // Function-based SQL injection
            "'; SELECT LOAD_FILE('/etc/passwd'); --",
            "' OR SUBSTRING(password,1,1)='a' --",

            // Time-based blind SQL injection
            "' OR SLEEP(5) --",
            "'; WAITFOR DELAY '00:00:05' --",
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            $client->request('POST', '/oauth/v2/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $apiClient->getPublicId(),
                'client_secret' => $apiClient->getSecret(),
                'code' => $payload,
                'redirect_uri' => 'http://example.com/callback',
            ]);

            $response = $client->getResponse();

            // Should return proper error response, not crash or expose data
            $this->assertSame(400, $response->getStatusCode(),
                'SQL injection payload should return 400 error: ' . $payload);

            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
            $this->assertSame('invalid_grant', $data['error']);

            // Ensure no sensitive data is exposed in error message
            $this->assertArrayHasKey('error_description', $data);
            $errorDescription = strtolower($data['error_description']);
            $this->assertStringNotContainsString('password', $errorDescription);
            $this->assertStringNotContainsString('secret', $errorDescription);
            $this->assertStringNotContainsString('token', $errorDescription);
        }
    }

    /**
     * Test SQL injection attempts in client_id parameter.
     */
    public function testSqlInjectionInClientId(): void
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneByUsername('admin');
        $authCode = $this->createAuthCode($apiClient, $user);

        $sqlInjectionPayloads = [
            // Basic SQL injection attempts
            "'; DROP TABLE oauth2_clients; --",
            "' OR '1'='1",
            "' UNION SELECT secret FROM oauth2_clients --",
            "'; DELETE FROM users WHERE username='admin'; --",

            // Advanced attempts targeting client validation
            "' OR EXISTS(SELECT 1 FROM oauth2_clients WHERE secret='secret') --",
            "'; INSERT INTO oauth2_clients VALUES (999, 'evil', 'client'); --",
            "' AND (SELECT COUNT(*) FROM oauth2_access_tokens) > 0 --",

            // Encoded attempts
            '%27%20OR%20%271%27%3D%271',
            '%27%3B%20DROP%20TABLE%20oauth2_clients%3B%20--',
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            $client->request('POST', '/oauth/v2/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $payload,
                'client_secret' => $apiClient->getSecret(),
                'code' => $authCode->getToken(),
                'redirect_uri' => 'http://example.com/callback',
            ]);

            $response = $client->getResponse();

            // Should return proper error response, not crash or expose data
            $this->assertSame(400, $response->getStatusCode(),
                'SQL injection in client_id should return 400 error: ' . $payload);

            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
            // Could be invalid_client or invalid_grant depending on validation order
            $this->assertContains($data['error'], ['invalid_client', 'invalid_grant']);
        }
    }

    /**
     * Test SQL injection attempts in client_secret parameter.
     */
    public function testSqlInjectionInClientSecret(): void
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneByUsername('admin');
        $authCode = $this->createAuthCode($apiClient, $user);

        $sqlInjectionPayloads = [
            // Basic SQL injection attempts
            "'; DROP TABLE oauth2_clients; --",
            "' OR '1'='1",
            "' UNION SELECT id FROM oauth2_clients --",
            "'; UPDATE oauth2_clients SET secret='hacked'; --",

            // Advanced attempts targeting secret validation
            "' OR LENGTH(secret) > 0 --",
            "'; INSERT INTO oauth2_access_tokens VALUES (1, 'token', 'evil'); --",
            "' AND (SELECT secret FROM oauth2_clients WHERE id=" . $apiClient->getId() . ') --',

            // Encoded attempts
            '%27%20OR%20%271%27%3D%271',
            '%27%3B%20DROP%20TABLE%20users%3B%20--',
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            $client->request('POST', '/oauth/v2/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $apiClient->getPublicId(),
                'client_secret' => $payload,
                'code' => $authCode->getToken(),
                'redirect_uri' => 'http://example.com/callback',
            ]);

            $response = $client->getResponse();

            // Should return proper error response, not crash or expose data
            $this->assertSame(400, $response->getStatusCode(),
                'SQL injection in client_secret should return 400 error: ' . $payload);

            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
            $this->assertSame('invalid_client', $data['error']);
        }
    }

    /**
     * Test SQL injection attempts in redirect_uri parameter.
     */
    public function testSqlInjectionInRedirectUri(): void
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneByUsername('admin');
        $authCode = $this->createAuthCode($apiClient, $user);

        $sqlInjectionPayloads = [
            // Basic SQL injection attempts
            "http://example.com/callback'; DROP TABLE oauth2_auth_codes; --",
            "http://example.com/callback' OR '1'='1",
            "http://example.com/callback' UNION SELECT token FROM oauth2_auth_codes --",
            "http://example.com/callback'; DELETE FROM oauth2_clients; --",

            // Advanced attempts targeting redirect URI validation
            "http://example.com/callback' OR redirect_uri LIKE '%callback%' --",
            "http://example.com/callback'; INSERT INTO oauth2_access_tokens VALUES (1, 'evil'); --",
            "http://example.com/callback' AND (SELECT COUNT(*) FROM oauth2_auth_codes) > 0 --",

            // Encoded attempts
            'http://example.com/callback%27%20OR%20%271%27%3D%271',
            'http://example.com/callback%27%3B%20DROP%20TABLE%20users%3B%20--',
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            $client->request('POST', '/oauth/v2/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $apiClient->getPublicId(),
                'client_secret' => $apiClient->getSecret(),
                'code' => $authCode->getToken(),
                'redirect_uri' => $payload,
            ]);

            $response = $client->getResponse();

            // Should return proper error response, not crash or expose data
            $this->assertSame(400, $response->getStatusCode(),
                'SQL injection in redirect_uri should return 400 error: ' . $payload);

            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
            $this->assertSame('redirect_uri_mismatch', $data['error']);
        }
    }

    /**
     * Test SQL injection attempts in PKCE parameters.
     */
    public function testSqlInjectionInPkceParameters(): void
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        $apiClient->setIsPublic(true); // Force PKCE requirement

        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $em->persist($apiClient);
        $em->flush();

        $user = $em->getRepository(User::class)->findOneByUsername('admin');

        // Create auth code with PKCE
        $authCode = new AuthCode();
        $authCode->setClient($apiClient);
        $authCode->setUser($user);
        $authCode->setToken($this->generateToken());
        $authCode->setRedirectUri('http://example.com/callback');
        $authCode->setExpiresAt(time() + 600);
        $authCode->setScope('read');
        $authCode->setCodeChallenge('test_challenge');
        $authCode->setCodeChallengeMethod('S256');

        $em->persist($authCode);
        $em->flush();

        $sqlInjectionPayloads = [
            // Basic SQL injection attempts
            "'; DROP TABLE oauth2_auth_codes; --",
            "' OR '1'='1",
            "' UNION SELECT code_challenge FROM oauth2_auth_codes --",
            "'; DELETE FROM oauth2_auth_codes WHERE client_id=" . $apiClient->getId() . '; --',

            // Advanced attempts targeting PKCE validation
            "' OR LENGTH(code_challenge) > 0 --",
            "'; INSERT INTO oauth2_access_tokens VALUES (1, 'pkce_token'); --",
            "' AND code_challenge_method='S256' --",

            // Encoded attempts
            '%27%20OR%20%271%27%3D%271',
            '%27%3B%20DROP%20TABLE%20oauth2_auth_codes%3B%20--',
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            $client->request('POST', '/oauth/v2/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $apiClient->getPublicId(),
                'client_secret' => $apiClient->getSecret(),
                'code' => $authCode->getToken(),
                'redirect_uri' => 'http://example.com/callback',
                'code_verifier' => $payload,
            ]);

            $response = $client->getResponse();

            // Should return proper error response, not crash or expose data
            $this->assertSame(400, $response->getStatusCode(),
                'SQL injection in code_verifier should return 400 error: ' . $payload);

            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
            $this->assertSame('invalid_grant', $data['error']);

            // Ensure no sensitive data is exposed in error message
            $this->assertArrayHasKey('error_description', $data);
            $errorDescription = strtolower($data['error_description']);
            $this->assertStringNotContainsString('challenge', $errorDescription);
            $this->assertStringNotContainsString('secret', $errorDescription);
        }
    }

    /**
     * Test SQL injection attempts in grant_type parameter.
     */
    public function testSqlInjectionInGrantType(): void
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneByUsername('admin');
        $authCode = $this->createAuthCode($apiClient, $user);

        $sqlInjectionPayloads = [
            // Basic SQL injection attempts
            "authorization_code'; DROP TABLE oauth2_clients; --",
            "authorization_code' OR '1'='1",
            "authorization_code' UNION SELECT * FROM users --",
            "authorization_code'; DELETE FROM oauth2_access_tokens; --",

            // Advanced attempts
            "authorization_code' OR grant_type='authorization_code' --",
            "authorization_code'; INSERT INTO oauth2_access_tokens VALUES (1, 'evil'); --",

            // Encoded attempts
            'authorization_code%27%20OR%20%271%27%3D%271',
            'authorization_code%27%3B%20DROP%20TABLE%20users%3B%20--',
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            $client->request('POST', '/oauth/v2/token', [
                'grant_type' => $payload,
                'client_id' => $apiClient->getPublicId(),
                'client_secret' => $apiClient->getSecret(),
                'code' => $authCode->getToken(),
                'redirect_uri' => 'http://example.com/callback',
            ]);

            $response = $client->getResponse();

            // Should return proper error response, not crash or expose data
            $this->assertSame(400, $response->getStatusCode(),
                'SQL injection in grant_type should return 400 error: ' . $payload);

            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('error', $data);
            $this->assertSame('invalid_request', $data['error']);
        }
    }

    /**
     * Test that database queries are using prepared statements by ensuring
     * SQL injection attempts don't affect the database state.
     */
    public function testDatabaseStateIntegrityAfterSqlInjection(): void
    {
        $client = $this->getTestClient();
        $apiClient = $this->createApiClientForUser('admin', ['authorization_code']);
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        // Record initial database state
        $initialClientCount = $em->getRepository(Client::class)->count([]);
        $initialAuthCodeCount = $em->getRepository(AuthCode::class)->count([]);
        $initialUserCount = $em->getRepository(User::class)->count([]);

        // Attempt various destructive SQL injection attacks
        $destructivePayloads = [
            "'; DROP TABLE oauth2_clients; --",
            "'; DELETE FROM oauth2_auth_codes; --",
            "'; UPDATE oauth2_clients SET secret='hacked'; --",
            "'; INSERT INTO oauth2_access_tokens VALUES (999, 'evil', 'token'); --",
            "'; TRUNCATE TABLE users; --",
        ];

        foreach ($destructivePayloads as $payload) {
            $client->request('POST', '/oauth/v2/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $payload,
                'client_secret' => $apiClient->getSecret(),
                'code' => $payload,
                'redirect_uri' => 'http://example.com/callback',
            ]);

            // Clear entity manager to ensure fresh data from database
            $em->clear();
        }

        // Verify database state is unchanged
        $finalClientCount = $em->getRepository(Client::class)->count([]);
        $finalAuthCodeCount = $em->getRepository(AuthCode::class)->count([]);
        $finalUserCount = $em->getRepository(User::class)->count([]);

        $this->assertSame($initialClientCount, $finalClientCount,
            'SQL injection should not affect oauth2_clients table');
        $this->assertSame($initialAuthCodeCount, $finalAuthCodeCount,
            'SQL injection should not affect oauth2_auth_codes table');
        $this->assertSame($initialUserCount, $finalUserCount,
            'SQL injection should not affect users table');

        // Verify the test client still exists and is functional
        $testClient = $em->getRepository(Client::class)->find($apiClient->getId());
        $this->assertNotNull($testClient, 'Test client should still exist after SQL injection attempts');
        $this->assertSame($apiClient->getSecret(), $testClient->getSecret(),
            'Client secret should be unchanged after SQL injection attempts');
    }

    /**
     * Helper method to create an OAuth client for testing.
     * This follows the same pattern as OAuthSecurityTest for consistency.
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
     * Helper method to create an authorization code.
     */
    private function createAuthCode(Client $client, User $user): AuthCode
    {
        $em = $this->getTestClient()->getContainer()->get(EntityManagerInterface::class);

        $authCode = new AuthCode();
        $authCode->setClient($client);
        $authCode->setUser($user);
        $authCode->setToken($this->generateToken());
        $authCode->setRedirectUri('http://example.com/callback');
        $authCode->setExpiresAt(time() + 600); // 10 minutes
        $authCode->setScope('read write');

        $em->persist($authCode);
        $em->flush();

        return $authCode;
    }

    /**
     * Helper method to generate a secure token.
     */
    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
