<?php

namespace Tests\Wallabag\Entity\Api;

use PHPUnit\Framework\TestCase;
use Wallabag\Entity\Api\Client;
use Wallabag\Entity\User;

/**
 * Test suite for Client entity with PKCE and public client support.
 */
class ClientTest extends TestCase
{
    private Client $client;
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->client = new Client($this->user);
    }

    public function testIsPublicDefault(): void
    {
        $this->assertFalse($this->client->isPublic());
    }

    public function testSetIsPublicTrue(): void
    {
        $result = $this->client->setIsPublic(true);

        $this->assertSame($this->client, $result);
        $this->assertTrue($this->client->isPublic());
        // Public clients should automatically require PKCE
        $this->assertTrue($this->client->requiresPkce());
    }

    public function testSetIsPublicFalse(): void
    {
        $this->client->setIsPublic(true);
        $result = $this->client->setIsPublic(false);

        $this->assertSame($this->client, $result);
        $this->assertFalse($this->client->isPublic());
        // Should still require PKCE if it was set
        $this->assertTrue($this->client->requiresPkce());
    }

    public function testRequiresPkceDefault(): void
    {
        $this->assertFalse($this->client->requiresPkce());
    }

    public function testSetRequirePkce(): void
    {
        $result = $this->client->setRequirePkce(true);

        $this->assertSame($this->client, $result);
        $this->assertTrue($this->client->requiresPkce());
    }

    public function testCheckSecretForPublicClient(): void
    {
        $this->client->setIsPublic(true);

        // Public clients should accept any secret (or no secret)
        $this->assertTrue($this->client->checkSecret('any_secret'));
        $this->assertTrue($this->client->checkSecret(''));
    }

    public function testCheckSecretForConfidentialClient(): void
    {
        // Set a secret for testing
        $this->client->setSecret('correct_secret');

        // Should use parent implementation for confidential clients
        $this->assertTrue($this->client->checkSecret('correct_secret'));
        $this->assertFalse($this->client->checkSecret('wrong_secret'));
    }

    public function testIsGrantSupportedPasswordForPublicClient(): void
    {
        $this->client->setIsPublic(true);
        $this->client->setAllowedGrantTypes(['password', 'authorization_code']);

        // Public clients should not be allowed to use password grant
        $this->assertFalse($this->client->isGrantSupported('password'));
    }

    public function testIsGrantSupportedPasswordForConfidentialClient(): void
    {
        $this->client->setAllowedGrantTypes(['password', 'authorization_code']);

        // Confidential clients can use password grant if configured
        $this->assertTrue($this->client->isGrantSupported('password'));
    }

    public function testIsGrantSupportedAuthorizationCode(): void
    {
        $this->client->setAllowedGrantTypes(['authorization_code', 'refresh_token']);

        // Both public and confidential clients can use authorization_code
        $this->assertTrue($this->client->isGrantSupported('authorization_code'));

        $this->client->setIsPublic(true);
        $this->assertTrue($this->client->isGrantSupported('authorization_code'));
    }

    public function testIsGrantSupportedUnsupportedGrant(): void
    {
        $this->client->setAllowedGrantTypes(['authorization_code']);

        $this->assertFalse($this->client->isGrantSupported('unsupported_grant'));
        $this->assertFalse($this->client->isGrantSupported('password'));
    }

    /**
     * Test the complete public client workflow.
     */
    public function testPublicClientWorkflow(): void
    {
        // Start as confidential client
        $this->assertFalse($this->client->isPublic());
        $this->assertFalse($this->client->requiresPkce());

        // Configure as public client
        $this->client->setIsPublic(true);
        $this->client->setAllowedGrantTypes(['authorization_code', 'refresh_token']);

        // Verify public client properties
        $this->assertTrue($this->client->isPublic());
        $this->assertTrue($this->client->requiresPkce());

        // Verify grant restrictions
        $this->assertTrue($this->client->isGrantSupported('authorization_code'));
        $this->assertTrue($this->client->isGrantSupported('refresh_token'));
        $this->assertFalse($this->client->isGrantSupported('password'));

        // Verify secret handling
        $this->assertTrue($this->client->checkSecret('any_secret'));
    }

    /**
     * Test confidential client with optional PKCE.
     */
    public function testConfidentialClientWithOptionalPkce(): void
    {
        $this->client->setAllowedGrantTypes(['authorization_code', 'password', 'refresh_token']);
        $this->client->setRequirePkce(true);
        $this->client->setSecret('confidential_secret');

        // Verify confidential client properties
        $this->assertFalse($this->client->isPublic());
        $this->assertTrue($this->client->requiresPkce());

        // Verify all grants are supported
        $this->assertTrue($this->client->isGrantSupported('authorization_code'));
        $this->assertTrue($this->client->isGrantSupported('password'));
        $this->assertTrue($this->client->isGrantSupported('refresh_token'));

        // Verify secret is required
        $this->assertTrue($this->client->checkSecret('confidential_secret'));
        $this->assertFalse($this->client->checkSecret('wrong_secret'));
    }
}
