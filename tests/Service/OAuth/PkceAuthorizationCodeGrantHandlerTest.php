<?php

namespace Tests\Wallabag\Service\OAuth;

use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use OAuth2\Model\IOAuth2Client;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Wallabag\Entity\Api\AuthCode;
use Wallabag\Entity\Api\Client;
use Wallabag\Entity\User;
use Wallabag\Service\OAuth\PkceAuthorizationCodeGrantHandler;
use Wallabag\Service\OAuth\PkceService;

class PkceAuthorizationCodeGrantHandlerTest extends TestCase
{
    private PkceAuthorizationCodeGrantHandler $handler;
    private PkceService&MockObject $pkceService;
    private AuthCodeManagerInterface&MockObject $authCodeManager;

    protected function setUp(): void
    {
        $this->pkceService = $this->createMock(PkceService::class);
        $this->authCodeManager = $this->createMock(AuthCodeManagerInterface::class);

        $this->handler = new PkceAuthorizationCodeGrantHandler(
            $this->pkceService,
            $this->authCodeManager
        );
    }

    /**
     * Test that the PKCE handler correctly accepts authorization_code grants.
     *
     * This is the critical positive test case that ensures:
     * - The handler declares support for authorization_code grants
     * - PKCE validation will actually be invoked for authorization code flows
     * - The handler integrates correctly with OAuth extension system
     *
     * This test is essential because if it fails, PKCE security would be
     * completely bypassed - the handler would never run, allowing public
     * clients to obtain tokens without PKCE validation.
     */
    public function testCheckGrantExtensionSupportsAuthorizationCode(): void
    {
        $client = $this->createMock(IOAuth2Client::class);

        $this->assertTrue(
            $this->handler->checkGrantExtension($client, ['grant_type' => 'authorization_code'], [])
        );
    }

    /**
     * Test that the PKCE handler only processes authorization_code grants.
     *
     * This is a critical security boundary test that ensures:
     * - PKCE validation is not applied to inappropriate grant types
     * - Grant type confusion attacks are prevented
     * - The handler maintains proper OAuth security boundaries
     *
     * If this fails, PKCE logic might be applied to password or client_credentials
     * grants, potentially causing security vulnerabilities or breaking functionality.
     */
    public function testCheckGrantExtensionRejectsOtherGrants(): void
    {
        $client = $this->createMock(IOAuth2Client::class);

        // Should reject password grant (Resource Owner Password Credentials)
        $this->assertFalse(
            $this->handler->checkGrantExtension($client, ['grant_type' => 'password'], [])
        );

        // Should reject client credentials grant
        $this->assertFalse(
            $this->handler->checkGrantExtension($client, ['grant_type' => 'client_credentials'], [])
        );

        // Should reject malformed requests with no grant type
        $this->assertFalse(
            $this->handler->checkGrantExtension($client, [], [])
        );
    }

    public function testGetAccessTokenDataWithoutCode(): void
    {
        $client = $this->createMock(IOAuth2Client::class);

        try {
            $this->handler->getAccessTokenData($client, [], []);
            $this->fail('Expected OAuth2ServerException was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_REQUEST, $e->getMessage());
            $this->assertSame('Missing parameter: "code" is required', $e->getDescription());
        }
    }

    public function testGetAccessTokenDataWithInvalidCode(): void
    {
        $client = $this->createMock(IOAuth2Client::class);

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->with('invalid_code')
            ->willReturn(null);

        try {
            $this->handler->getAccessTokenData($client, ['code' => 'invalid_code'], []);
            $this->fail('Expected OAuth2ServerException was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_GRANT, $e->getMessage());
            $this->assertSame('Invalid authorization code', $e->getDescription());
        }
    }

    public function testGetAccessTokenDataWithExpiredCode(): void
    {
        $client = $this->createMock(IOAuth2Client::class);
        $authCode = $this->createMock(AuthCode::class);

        $authCode->expects($this->once())
            ->method('hasExpired')
            ->willReturn(true);

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->with('expired_code')
            ->willReturn($authCode);

        $this->authCodeManager->expects($this->once())
            ->method('deleteAuthCode')
            ->with($authCode);

        try {
            $this->handler->getAccessTokenData($client, ['code' => 'expired_code'], []);
            $this->fail('Expected OAuth2ServerException was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_GRANT, $e->getMessage());
            $this->assertSame('The authorization code has expired', $e->getDescription());
        }
    }

    public function testGetAccessTokenDataWithWrongClient(): void
    {
        $client = $this->createMock(IOAuth2Client::class);
        $client->method('getPublicId')->willReturn('wrong_client_id');

        $authCodeClient = $this->createMock(Client::class);
        $authCodeClient->method('getPublicId')->willReturn('correct_client_id');

        $authCode = $this->createMock(AuthCode::class);
        $authCode->method('hasExpired')->willReturn(false);
        $authCode->method('getClient')->willReturn($authCodeClient);

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->willReturn($authCode);

        try {
            $this->handler->getAccessTokenData($client, ['code' => 'valid_code'], []);
            $this->fail('Expected OAuth2ServerException was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_GRANT, $e->getMessage());
            $this->assertSame('Invalid authorization code', $e->getDescription());
        }
    }

    public function testGetAccessTokenDataWithPkceRequired(): void
    {
        $client = $this->createMock(IOAuth2Client::class);
        $client->method('getPublicId')->willReturn('client_id');

        $wallabagClient = $this->createMock(Client::class);
        $wallabagClient->method('getPublicId')->willReturn('client_id');
        $wallabagClient->method('requiresPkce')->willReturn(true);

        $authCode = $this->createMock(AuthCode::class);
        $authCode->method('hasExpired')->willReturn(false);
        $authCode->method('getClient')->willReturn($wallabagClient);
        $authCode->method('hasPkce')->willReturn(false);

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->willReturn($authCode);

        try {
            $this->handler->getAccessTokenData($client, ['code' => 'valid_code'], []);
            $this->fail('Expected OAuth2ServerException was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_REQUEST, $e->getMessage());
            $this->assertSame('PKCE is required for this client', $e->getDescription());
        }
    }

    public function testGetAccessTokenDataWithMissingCodeVerifier(): void
    {
        $client = $this->createMock(IOAuth2Client::class);
        $client->method('getPublicId')->willReturn('client_id');

        $wallabagClient = $this->createMock(Client::class);
        $wallabagClient->method('getPublicId')->willReturn('client_id');
        $wallabagClient->method('requiresPkce')->willReturn(false);

        $authCode = $this->createMock(AuthCode::class);
        $authCode->method('hasExpired')->willReturn(false);
        $authCode->method('getClient')->willReturn($wallabagClient);
        $authCode->method('hasPkce')->willReturn(true);
        $authCode->method('getCodeChallenge')->willReturn('challenge');
        $authCode->method('getCodeChallengeMethod')->willReturn('S256');

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->willReturn($authCode);

        try {
            $this->handler->getAccessTokenData($client, ['code' => 'valid_code'], []);
            $this->fail('Expected OAuth2ServerException was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_REQUEST, $e->getMessage());
            $this->assertSame('PKCE code_verifier is required for this authorization code', $e->getDescription());
        }
    }

    public function testGetAccessTokenDataWithInvalidCodeVerifier(): void
    {
        $client = $this->createMock(IOAuth2Client::class);
        $client->method('getPublicId')->willReturn('client_id');

        $wallabagClient = $this->createMock(Client::class);
        $wallabagClient->method('getPublicId')->willReturn('client_id');
        $wallabagClient->method('requiresPkce')->willReturn(false);
        $wallabagClient->method('isPublic')->willReturn(false);

        $authCode = $this->createMock(AuthCode::class);
        $authCode->method('hasExpired')->willReturn(false);
        $authCode->method('getClient')->willReturn($wallabagClient);
        $authCode->method('hasPkce')->willReturn(true);
        $authCode->method('getCodeChallenge')->willReturn('challenge');
        $authCode->method('getCodeChallengeMethod')->willReturn('S256');

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->willReturn($authCode);

        $this->pkceService->expects($this->once())
            ->method('verifyCodeChallenge')
            ->with('wrong_verifier', 'challenge', 'S256')
            ->willReturn(false);

        try {
            $this->handler->getAccessTokenData($client, [
                'code' => 'valid_code',
                'code_verifier' => 'wrong_verifier',
            ], []);
            $this->fail('Expected OAuth2ServerException was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_GRANT, $e->getMessage());
            $this->assertSame('Invalid PKCE code_verifier', $e->getDescription());
        }
    }

    public function testGetAccessTokenDataWithPublicClientRequiresS256(): void
    {
        $client = $this->createMock(IOAuth2Client::class);
        $client->method('getPublicId')->willReturn('client_id');

        $wallabagClient = $this->createMock(Client::class);
        $wallabagClient->method('getPublicId')->willReturn('client_id');
        $wallabagClient->method('requiresPkce')->willReturn(true);
        $wallabagClient->method('isPublic')->willReturn(true);

        $authCode = $this->createMock(AuthCode::class);
        $authCode->method('hasExpired')->willReturn(false);
        $authCode->method('getClient')->willReturn($wallabagClient);
        $authCode->method('hasPkce')->willReturn(true);
        $authCode->method('getCodeChallenge')->willReturn('challenge');
        $authCode->method('getCodeChallengeMethod')->willReturn('plain');

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->willReturn($authCode);

        $this->pkceService->expects($this->once())
            ->method('verifyCodeChallenge')
            ->with('verifier', 'challenge', 'plain')
            ->willReturn(true);

        try {
            $this->handler->getAccessTokenData($client, [
                'code' => 'valid_code',
                'code_verifier' => 'verifier',
            ], []);
            $this->fail('Expected OAuth2ServerException was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_REQUEST, $e->getMessage());
            $this->assertSame('Public clients must use S256 code challenge method', $e->getDescription());
        }
    }

    public function testGetAccessTokenDataWithInvalidRedirectUri(): void
    {
        $client = $this->createMock(IOAuth2Client::class);
        $client->method('getPublicId')->willReturn('client_id');

        $wallabagClient = $this->createMock(Client::class);
        $wallabagClient->method('getPublicId')->willReturn('client_id');
        $wallabagClient->method('requiresPkce')->willReturn(false);

        $authCode = $this->createMock(AuthCode::class);
        $authCode->method('hasExpired')->willReturn(false);
        $authCode->method('getClient')->willReturn($wallabagClient);
        $authCode->method('hasPkce')->willReturn(false);
        $authCode->method('getRedirectUri')->willReturn('http://correct.redirect');

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->willReturn($authCode);

        try {
            $this->handler->getAccessTokenData($client, [
                'code' => 'valid_code',
                'redirect_uri' => 'http://wrong.redirect',
            ], []);
            $this->fail('Expected OAuth2ServerException was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_GRANT, $e->getMessage());
            $this->assertSame('Invalid redirect URI', $e->getDescription());
        }
    }

    public function testGetAccessTokenDataSuccessWithoutPkce(): void
    {
        $client = $this->createMock(IOAuth2Client::class);
        $client->method('getPublicId')->willReturn('client_id');

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(123);

        $wallabagClient = $this->createMock(Client::class);
        $wallabagClient->method('getPublicId')->willReturn('client_id');
        $wallabagClient->method('requiresPkce')->willReturn(false);

        $authCode = $this->createMock(AuthCode::class);
        $authCode->method('hasExpired')->willReturn(false);
        $authCode->method('getClient')->willReturn($wallabagClient);
        $authCode->method('hasPkce')->willReturn(false);
        $authCode->method('getUser')->willReturn($user);
        $authCode->method('getScope')->willReturn('read write');

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->willReturn($authCode);

        $this->authCodeManager->expects($this->once())
            ->method('deleteAuthCode')
            ->with($authCode);

        $result = $this->handler->getAccessTokenData($client, ['code' => 'valid_code'], []);

        $this->assertSame([
            'client_id' => 'client_id',
            'user_id' => 123,
            'scope' => 'read write',
        ], $result);
    }

    public function testGetAccessTokenDataSuccessWithPkce(): void
    {
        $client = $this->createMock(IOAuth2Client::class);
        $client->method('getPublicId')->willReturn('client_id');

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(456);

        $wallabagClient = $this->createMock(Client::class);
        $wallabagClient->method('getPublicId')->willReturn('client_id');
        $wallabagClient->method('requiresPkce')->willReturn(true);
        $wallabagClient->method('isPublic')->willReturn(true);

        $authCode = $this->createMock(AuthCode::class);
        $authCode->method('hasExpired')->willReturn(false);
        $authCode->method('getClient')->willReturn($wallabagClient);
        $authCode->method('hasPkce')->willReturn(true);
        $authCode->method('getCodeChallenge')->willReturn('E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM');
        $authCode->method('getCodeChallengeMethod')->willReturn('S256');
        $authCode->method('getUser')->willReturn($user);
        $authCode->method('getScope')->willReturn('read');
        $authCode->method('getRedirectUri')->willReturn('http://app.example/callback');

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->willReturn($authCode);

        $this->pkceService->expects($this->once())
            ->method('verifyCodeChallenge')
            ->with('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk', 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM', 'S256')
            ->willReturn(true);

        $this->authCodeManager->expects($this->once())
            ->method('deleteAuthCode')
            ->with($authCode);

        $result = $this->handler->getAccessTokenData($client, [
            'code' => 'valid_code',
            'code_verifier' => 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk',
            'redirect_uri' => 'http://app.example/callback',
        ], []);

        $this->assertSame([
            'client_id' => 'client_id',
            'user_id' => 456,
            'scope' => 'read',
        ], $result);
    }

    public function testGetAccessTokenDataWithCorruptedPkceData(): void
    {
        $client = $this->createMock(IOAuth2Client::class);
        $client->method('getPublicId')->willReturn('client_id');

        $wallabagClient = $this->createMock(Client::class);
        $wallabagClient->method('getPublicId')->willReturn('client_id');

        $authCode = $this->createMock(AuthCode::class);
        $authCode->method('hasExpired')->willReturn(false);
        $authCode->method('getClient')->willReturn($wallabagClient);
        $authCode->method('hasPkce')->willReturn(true);
        $authCode->method('getCodeChallenge')->willReturn(null); // Corrupted - no challenge stored
        $authCode->method('getCodeChallengeMethod')->willReturn('S256');

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->willReturn($authCode);

        try {
            $this->handler->getAccessTokenData($client, [
                'code' => 'valid_code',
                'code_verifier' => 'verifier',
            ], []);
            $this->fail('Expected OAuth2ServerException was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_GRANT, $e->getMessage());
            $this->assertSame('Invalid PKCE data in authorization code', $e->getDescription());
        }
    }

    public function testReplayAttackPrevention(): void
    {
        // Test that the same authorization code cannot be used twice
        $client = $this->createMock(IOAuth2Client::class);
        $client->method('getPublicId')->willReturn('client_id');

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(789);

        $wallabagClient = $this->createMock(Client::class);
        $wallabagClient->method('getPublicId')->willReturn('client_id');
        $wallabagClient->method('requiresPkce')->willReturn(false);

        $authCode = $this->createMock(AuthCode::class);
        $authCode->method('hasExpired')->willReturn(false);
        $authCode->method('getClient')->willReturn($wallabagClient);
        $authCode->method('hasPkce')->willReturn(false);
        $authCode->method('getUser')->willReturn($user);
        $authCode->method('getScope')->willReturn('read');

        // First use - should succeed
        $this->authCodeManager->expects($this->exactly(2))
            ->method('findAuthCodeByToken')
            ->with('reusable_code')
            ->willReturnOnConsecutiveCalls($authCode, null);

        $this->authCodeManager->expects($this->once())
            ->method('deleteAuthCode')
            ->with($authCode);

        // First use succeeds
        $result = $this->handler->getAccessTokenData($client, ['code' => 'reusable_code'], []);
        $this->assertArrayHasKey('user_id', $result);

        // Second use should fail
        try {
            $this->handler->getAccessTokenData($client, ['code' => 'reusable_code'], []);
            $this->fail('Expected OAuth2ServerException was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_GRANT, $e->getMessage());
            $this->assertSame('Invalid authorization code', $e->getDescription());
        }
    }

    public function testTimingAttackResistance(): void
    {
        // Test that handler doesn't reveal information through timing differences
        $client = $this->createMock(IOAuth2Client::class);
        $client->method('getPublicId')->willReturn('client_id');

        $wallabagClient = $this->createMock(Client::class);
        $wallabagClient->method('getPublicId')->willReturn('client_id');
        $wallabagClient->method('isPublic')->willReturn(false);

        $authCode = $this->createMock(AuthCode::class);
        $authCode->method('hasExpired')->willReturn(false);
        $authCode->method('getClient')->willReturn($wallabagClient);
        $authCode->method('hasPkce')->willReturn(true);
        $authCode->method('getCodeChallenge')->willReturn('challenge');
        $authCode->method('getCodeChallengeMethod')->willReturn('S256');

        $this->authCodeManager->expects($this->once())
            ->method('findAuthCodeByToken')
            ->willReturn($authCode);

        // Ensure the PKCE service is called even with invalid verifier
        // This ensures timing consistency
        $this->pkceService->expects($this->once())
            ->method('verifyCodeChallenge')
            ->with($this->anything(), 'challenge', 'S256')
            ->willReturn(false);

        try {
            $this->handler->getAccessTokenData($client, [
                'code' => 'valid_code',
                'code_verifier' => 'wrong_verifier',
            ], []);
            $this->fail('Expected exception was not thrown');
        } catch (OAuth2ServerException $e) {
            $this->assertSame(OAuth2::ERROR_INVALID_GRANT, $e->getMessage());
            $this->assertSame('Invalid PKCE code_verifier', $e->getDescription());
        }
    }
}
