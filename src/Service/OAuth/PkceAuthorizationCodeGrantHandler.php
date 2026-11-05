<?php

namespace Wallabag\Service\OAuth;

use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use OAuth2\Model\IOAuth2Client;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Wallabag\Entity\Api\AuthCode;
use Wallabag\Entity\Api\Client;
use Wallabag\Entity\User;

/**
 * PKCE-enabled Authorization Code Grant Handler.
 *
 * Extends the standard OAuth2 authorization code grant to support PKCE verification.
 * This handler validates code_verifier against the stored code_challenge.
 */
class PkceAuthorizationCodeGrantHandler implements GrantExtensionInterface
{
    private PkceService $pkceService;
    private AuthCodeManagerInterface $authCodeManager;

    /**
     * PKCE Authorization Code Grant Handler constructor.
     *
     * @param PkceService              $pkceService     Service for PKCE validation
     * @param AuthCodeManagerInterface $authCodeManager Manager for authorization codes
     */
    public function __construct(
        PkceService $pkceService,
        AuthCodeManagerInterface $authCodeManager,
    ) {
        $this->pkceService = $pkceService;
        $this->authCodeManager = $authCodeManager;
    }

    /**
     * Check if this handler supports the given grant type.
     *
     * @param IOAuth2Client $client      The OAuth client making the request
     * @param array         $inputData   Request data containing grant_type
     * @param array         $authHeaders Authentication headers
     *
     * @return bool True if this handler supports the grant type
     */
    public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders): bool
    {
        // This handler only supports authorization_code grant
        return isset($inputData['grant_type']) && 'authorization_code' === $inputData['grant_type'];
    }

    /**
     * Handle the authorization code grant with PKCE validation.
     *
     * Validates the authorization code, performs PKCE verification if required,
     * and returns the access token data.
     *
     * @param IOAuth2Client $client      The OAuth client making the request
     * @param array         $inputData   request data containing code, code_verifier, etc
     * @param array         $authHeaders Authentication headers
     *
     * @throws OAuth2ServerException When validation fails
     * @return array                 Access token data with client_id, user_id, and scope
     */
    public function getAccessTokenData(IOAuth2Client $client, array $inputData, array $authHeaders): array
    {
        // Validate required parameters
        if (!isset($inputData['code'])) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'Missing parameter: "code" is required');
        }

        $authorizationCode = $inputData['code'];
        $codeVerifier = $inputData['code_verifier'] ?? null;

        // Find the authorization code
        $authCode = $this->authCodeManager->findAuthCodeByToken($authorizationCode);

        if (!$authCode instanceof AuthCode) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'Invalid authorization code');
        }

        // Check if the authorization code has expired
        if ($authCode->hasExpired()) {
            $this->authCodeManager->deleteAuthCode($authCode);
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'The authorization code has expired');
        }

        // Verify the client
        if ($authCode->getClient()->getPublicId() !== $client->getPublicId()) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'Invalid authorization code');
        }

        $wallabagClient = $authCode->getClient();
        if (!$wallabagClient instanceof Client) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_CLIENT, 'Invalid client');
        }

        // Validate PKCE requirements
        if ($wallabagClient->requiresPkce() || $wallabagClient->isPublic()) {
            // Public clients and clients explicitly requiring PKCE must always use PKCE
            if (!$authCode->hasPkce()) {
                throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'PKCE is required for this client');
            }
            $this->validatePkce($authCode, $codeVerifier, $wallabagClient);
        } elseif ($authCode->hasPkce()) {
            // Optional PKCE - validate if present
            $this->validatePkce($authCode, $codeVerifier, $wallabagClient);
        }

        // Verify redirect URI - REQUIRED if one was stored during authorization
        if ($authCode->getRedirectUri()) {
            if (!isset($inputData['redirect_uri']) || $authCode->getRedirectUri() !== $inputData['redirect_uri']) {
                throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'Invalid redirect URI');
            }
        }

        // Generate the access token data
        $user = $authCode->getUser();
        if (!$user instanceof User) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'Invalid user in authorization code');
        }

        $tokenData = [
            'client_id' => $client->getPublicId(),
            'user_id' => $user->getId(),
            'scope' => $authCode->getScope(),
        ];

        // Delete the authorization code (single use)
        $this->authCodeManager->deleteAuthCode($authCode);

        return $tokenData;
    }

    /**
     * Validate PKCE code verifier against the stored challenge.
     */
    private function validatePkce(AuthCode $authCode, ?string $codeVerifier, Client $client): void
    {
        if (null === $codeVerifier) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'PKCE code_verifier is required for this authorization code');
        }

        $codeChallenge = $authCode->getCodeChallenge();
        $codeChallengeMethod = $authCode->getCodeChallengeMethod();

        if (!$codeChallenge || !$codeChallengeMethod) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'Invalid PKCE data in authorization code');
        }

        // Verify the code verifier
        if (!$this->pkceService->verifyCodeChallenge($codeVerifier, $codeChallenge, $codeChallengeMethod)) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'Invalid PKCE code_verifier');
        }

        // Additional security check for public clients
        if ($client->isPublic() && PkceService::METHOD_S256 !== $codeChallengeMethod) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'Public clients must use S256 code challenge method');
        }
    }
}
