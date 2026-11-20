<?php

namespace Wallabag\Service\OAuth;

use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use FOS\OAuthServerBundle\Model\AuthCodeManagerInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Model\RefreshTokenManagerInterface;
use FOS\OAuthServerBundle\Storage\OAuthStorage;
use OAuth2\Model\IOAuth2AuthCode;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wallabag\Entity\Api\AuthCode;
use Wallabag\Entity\Api\Client;

/**
 * PKCE-enhanced OAuth Storage.
 *
 * Extends the default FOSOAuthServerBundle storage to add PKCE validation
 * for authorization code grants.
 */
class PkceOAuthStorage extends OAuthStorage
{
    private PkceService $pkceService;
    private RequestStack $requestStack;

    /**
     * PKCE OAuth Storage constructor.
     *
     * @param ClientManagerInterface       $clientManager       OAuth client manager
     * @param AccessTokenManagerInterface  $accessTokenManager  Access token manager
     * @param RefreshTokenManagerInterface $refreshTokenManager Refresh token manager
     * @param AuthCodeManagerInterface     $authCodeManager     Authorization code manager
     * @param UserProviderInterface        $userProvider        User provider for authentication
     * @param EncoderFactoryInterface|null $encoderFactory      Password encoder factory
     * @param PkceService                  $pkceService         Service for PKCE validation
     * @param RequestStack                 $requestStack        Symfony request stack
     */
    public function __construct(
        ClientManagerInterface $clientManager,
        AccessTokenManagerInterface $accessTokenManager,
        RefreshTokenManagerInterface $refreshTokenManager,
        AuthCodeManagerInterface $authCodeManager,
        UserProviderInterface $userProvider,
        ?EncoderFactoryInterface $encoderFactory,
        PkceService $pkceService,
        RequestStack $requestStack,
    ) {
        parent::__construct(
            $clientManager,
            $accessTokenManager,
            $refreshTokenManager,
            $authCodeManager,
            $userProvider,
            $encoderFactory
        );

        $this->pkceService = $pkceService;
        $this->requestStack = $requestStack;
    }

    /**
     * Override getAuthCode to validate PKCE during authorization code validation.
     *
     * This is called by the OAuth2 library during token exchange for validation.
     * Validates PKCE requirements and redirect URI matching for enhanced security.
     *
     * @param string $code The authorization code to validate
     *
     * @throws OAuth2ServerException When PKCE validation fails or redirect URI mismatches
     * @return IOAuth2AuthCode|null  The validated authorization code or null if invalid
     */
    public function getAuthCode($code)
    {
        // Get the authorization code from parent storage
        $authCodeEntity = parent::getAuthCode($code);

        if ($authCodeEntity instanceof AuthCode) {
            $client = $authCodeEntity->getClient();
            if ($client instanceof Client) {
                // Get code_verifier from current request
                $currentRequest = $this->requestStack->getCurrentRequest();
                $codeVerifier = null;
                $requestRedirectUri = null;

                if ($currentRequest) {
                    $codeVerifier = $currentRequest->request->get('code_verifier') ??
                                  $currentRequest->query->get('code_verifier');
                    $requestRedirectUri = $currentRequest->request->get('redirect_uri') ??
                                        $currentRequest->query->get('redirect_uri');
                }

                // Validate redirect URI if provided in request
                if (null !== $requestRedirectUri) {
                    if ($authCodeEntity->getRedirectUri() !== $requestRedirectUri) {
                        throw new OAuth2ServerException((string) Response::HTTP_BAD_REQUEST, OAuth2::ERROR_REDIRECT_URI_MISMATCH, 'The redirect URI provided does not match the one used during authorization');
                    }
                }

                // Validate PKCE requirements during code validation
                if ($client->requiresPkce() || $client->isPublic()) {
                    // Public clients and clients explicitly requiring PKCE must always use PKCE
                    if (!$authCodeEntity->hasPkce()) {
                        throw new OAuth2ServerException((string) Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'PKCE is required for this client');
                    }
                    $this->validatePkce($authCodeEntity, $codeVerifier, $client);
                } elseif ($authCodeEntity->hasPkce()) {
                    // Optional PKCE - validate if present
                    $this->validatePkce($authCodeEntity, $codeVerifier, $client);
                }
            }
        }

        // Return the validated authorization code
        return $authCodeEntity;
    }

    /**
     * Validate PKCE code verifier against the stored challenge.
     *
     * Performs RFC 7636 compliant PKCE validation including special security
     * checks for public clients (S256 method enforcement).
     *
     * @param AuthCode    $authCode     The authorization code containing PKCE data
     * @param string|null $codeVerifier The code verifier provided by the client
     * @param Client      $client       The OAuth client making the request
     *
     * @throws OAuth2ServerException When PKCE validation fails
     */
    private function validatePkce(AuthCode $authCode, ?string $codeVerifier, Client $client): void
    {
        if (null === $codeVerifier) {
            throw new OAuth2ServerException((string) Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'PKCE code_verifier is required for this authorization code');
        }

        $codeChallenge = $authCode->getCodeChallenge();
        $codeChallengeMethod = $authCode->getCodeChallengeMethod();

        if (!$codeChallenge || !$codeChallengeMethod) {
            throw new OAuth2ServerException((string) Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'Invalid PKCE data in authorization code');
        }

        // Verify the code verifier
        if (!$this->pkceService->verifyCodeChallenge($codeVerifier, $codeChallenge, $codeChallengeMethod)) {
            throw new OAuth2ServerException((string) Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'Invalid PKCE code_verifier');
        }

        // Additional security check for public clients
        if ($client->isPublic() && PkceService::METHOD_S256 !== $codeChallengeMethod) {
            throw new OAuth2ServerException((string) Response::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'Public clients must use S256 code challenge method');
        }
    }
}
