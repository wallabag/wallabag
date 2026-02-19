<?php

namespace Wallabag\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Controller\AbstractController;
use Wallabag\Entity\Api\AuthCode;
use Wallabag\Entity\Api\Client;
use Wallabag\Entity\User;
use Wallabag\Repository\Api\ClientRepository;
use Wallabag\Service\OAuth\PkceService;

/**
 * OAuth2 Authorization Controller.
 *
 * Handles the OAuth2 authorization flow with PKCE support.
 * Implements the /oauth/v2/authorize endpoint for secure OAuth2 authorization.
 */
class OAuthController extends AbstractController
{
    private PkceService $pkceService;
    private EntityManagerInterface $entityManager;
    private ClientRepository $clientRepository;

    /**
     * OAuth Controller constructor.
     *
     * @param PkceService            $pkceService      Service for PKCE validation and generation
     * @param EntityManagerInterface $entityManager    Doctrine entity manager for database operations
     * @param ClientRepository       $clientRepository Repository for OAuth client entities
     */
    public function __construct(
        PkceService $pkceService,
        EntityManagerInterface $entityManager,
        ClientRepository $clientRepository,
    ) {
        $this->pkceService = $pkceService;
        $this->entityManager = $entityManager;
        $this->clientRepository = $clientRepository;
    }

    /**
     * OAuth2 Authorization Endpoint.
     *
     * This endpoint handles the authorization request in the OAuth2 authorization code flow.
     * It validates the request parameters, checks PKCE requirements, and either shows
     * a login form or a consent screen.
     *
     * @Route("/oauth/v2/authorize", name="oauth2_authorize", methods={"GET"})
     *
     * @param Request $request The HTTP request containing OAuth2 parameters
     *
     * @throws BadRequestHttpException When required parameters are missing or invalid
     * @return Response                Either a redirect to login, consent page, or error redirect
     */
    public function authorizeAction(Request $request): Response
    {
        // Extract and validate required parameters
        $clientId = $request->query->get('client_id');
        $redirectUri = $request->query->get('redirect_uri');
        $responseType = $request->query->get('response_type', 'code');
        $scope = $request->query->get('scope', 'read');
        $state = $request->query->get('state');

        // PKCE parameters
        $codeChallenge = $request->query->get('code_challenge');
        $codeChallengeMethod = $request->query->get('code_challenge_method', 'plain');

        // Validate required parameters
        if (!$clientId) {
            throw new BadRequestHttpException('Missing required parameter: client_id');
        }

        if (!$redirectUri) {
            throw new BadRequestHttpException('Missing required parameter: redirect_uri');
        }

        if ('code' !== $responseType) {
            return $this->redirectWithError($redirectUri, 'unsupported_response_type',
                'Only "code" response type is supported', $state);
        }

        // Find and validate client
        $clientValidation = $this->findAndValidateClient($clientId, $redirectUri, $codeChallenge, $codeChallengeMethod);

        if (null === $clientValidation) {
            throw new BadRequestHttpException('Invalid client_id or redirect_uri');
        }

        if (\is_array($clientValidation)) {
            // PKCE validation failed - redirect with error (RFC compliant)
            return $this->redirectWithError($redirectUri, $clientValidation['error'], $clientValidation['description'], $state);
        }

        $client = $clientValidation;

        // Check if user is authenticated
        $user = $this->getUser();
        if (!$user instanceof User) {
            // Store request parameters in session for after login
            $session = $request->getSession();
            $session->set('oauth2_request', [
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => $scope,
                'state' => $state,
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => $codeChallengeMethod,
            ]);

            // Store the current URL to redirect back after login
            $session->set('_security.main.target_path', $request->getUri());

            // Redirect to login page
            return $this->redirectToRoute('fos_user_security_login');
        }

        // User is authenticated - check if we have stored OAuth request from session
        $session = $request->getSession();
        $storedRequest = $session->get('oauth2_request');
        if ($storedRequest) {
            // We came back from login, use stored parameters
            $clientId = $storedRequest['client_id'];
            $redirectUri = $storedRequest['redirect_uri'];
            $scope = $storedRequest['scope'];
            $state = $storedRequest['state'];
            $codeChallenge = $storedRequest['code_challenge'];
            $codeChallengeMethod = $storedRequest['code_challenge_method'];

            // Re-validate the client with stored parameters
            $client = $this->findAndValidateClient($clientId, $redirectUri, $codeChallenge, $codeChallengeMethod);
            if (!$client) {
                throw new BadRequestHttpException('Invalid client_id or redirect_uri');
            }
        } else {
            // Store current request parameters for the consent form
            $session->set('oauth2_request', [
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => $scope,
                'state' => $state,
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => $codeChallengeMethod,
            ]);
        }

        // User is authenticated, show consent page
        return $this->showConsentPage($client, $scope, $state);
    }

    /**
     * OAuth2 Authorization Consent Handler.
     *
     * Handles the user's consent decision and generates the authorization code.
     * If the user approves, creates an authorization code and redirects back to the client.
     * If denied, redirects with an access_denied error.
     *
     * @Route("/oauth/v2/authorize", name="oauth2_authorize_consent", methods={"POST"})
     * @IsGranted("ROLE_USER")
     *
     * @param Request $request The HTTP request containing consent action and CSRF token
     *
     * @throws BadRequestHttpException When CSRF token is invalid or session state is invalid
     * @return Response                Redirect to client with authorization code or error
     */
    public function consentAction(Request $request): Response
    {
        $session = $request->getSession();
        $oauthRequest = $session->get('oauth2_request');

        if (!$oauthRequest) {
            throw new BadRequestHttpException('Invalid OAuth2 session state');
        }

        // Validate CSRF token
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('oauth_consent', $token)) {
            throw new BadRequestHttpException('Invalid CSRF token');
        }

        $action = $request->request->get('action');

        if ('deny' === $action) {
            // User denied access
            $session->remove('oauth2_request');

            return $this->redirectWithError(
                $oauthRequest['redirect_uri'],
                'access_denied',
                'The user denied the request',
                $oauthRequest['state']
            );
        }

        if ('allow' !== $action) {
            throw new BadRequestHttpException('Invalid action parameter');
        }

        // User granted access, generate authorization code
        $client = $this->clientRepository->findOneBy(['id' => $oauthRequest['client_id']]);
        if (!$client) {
            throw new BadRequestHttpException('Invalid client');
        }

        $authCode = $this->generateAuthorizationCode($client, $this->getUser(), $oauthRequest);

        // Clear the session
        $session->remove('oauth2_request');

        // Redirect back to client with authorization code
        return $this->redirectWithCode(
            $oauthRequest['redirect_uri'],
            $authCode->getToken(),
            $oauthRequest['state']
        );
    }

    /**
     * Find and validate the OAuth client.
     *
     * Validates the client_id, redirect_uri, and PKCE requirements.
     * Returns the client object if valid, error array if PKCE validation fails,
     * or null if client/redirect_uri is invalid.
     *
     * @param string      $clientId            The OAuth client identifier
     * @param string      $redirectUri         The redirect URI to validate
     * @param string|null $codeChallenge       The PKCE code challenge (optional)
     * @param string      $codeChallengeMethod The PKCE challenge method
     *
     * @return Client|array|null Client object, error array for redirect, or null for invalid client/redirect_uri
     */
    private function findAndValidateClient(
        string $clientId,
        string $redirectUri,
        ?string $codeChallenge,
        string $codeChallengeMethod,
    ): Client|array|null {
        // Extract the actual client ID from the composite client_id
        $parts = explode('_', $clientId, 2);
        if (2 !== \count($parts)) {
            return null;
        }

        $client = $this->clientRepository->find((int) $parts[0]);
        if (!$client) {
            return null;
        }

        // Verify the random ID matches
        if ($client->getRandomId() !== $parts[1]) {
            return null;
        }

        // Validate redirect URI
        if (!$this->isValidRedirectUri($client, $redirectUri)) {
            return null;
        }

        // Validate PKCE requirements
        $pkceValidation = $this->validatePkceRequirements($client, $codeChallenge, $codeChallengeMethod);
        if (true !== $pkceValidation) {
            return $pkceValidation; // Return error array for PKCE failures
        }

        return $client;
    }

    /**
     * Validate that the redirect URI is allowed for this client.
     *
     * Performs exact match validation against the client's registered redirect URIs
     * for security compliance with OAuth 2.1 specifications.
     *
     * @param Client $client      The OAuth client to validate against
     * @param string $redirectUri The redirect URI to validate
     *
     * @return bool True if the redirect URI is valid for this client
     */
    private function isValidRedirectUri(Client $client, string $redirectUri): bool
    {
        $allowedUris = $client->getRedirectUris();

        // Exact match required for security
        return \in_array($redirectUri, $allowedUris, true);
    }

    /**
     * Validate PKCE requirements based on client configuration.
     *
     * @return true|array True if valid, error array if invalid
     */
    private function validatePkceRequirements(Client $client, ?string $codeChallenge, string $codeChallengeMethod): true|array
    {
        // If client requires PKCE, code_challenge must be present
        if ($client->requiresPkce() && !$codeChallenge) {
            return [
                'error' => 'invalid_request',
                'description' => 'Client requires PKCE but no code_challenge provided',
            ];
        }

        // If code_challenge is present, validate the method
        if ($codeChallenge) {
            try {
                $this->pkceService->validateCodeChallengeMethod($codeChallengeMethod);

                // For public clients, enforce S256 method for better security
                if ($client->isPublic() && $this->pkceService->shouldEnforceS256(true) && PkceService::METHOD_S256 !== $codeChallengeMethod) {
                    return [
                        'error' => 'invalid_request',
                        'description' => 'Public clients must use S256 code challenge method',
                    ];
                }

                return true;
            } catch (\InvalidArgumentException $e) {
                return [
                    'error' => 'invalid_request',
                    'description' => 'Invalid PKCE parameters: ' . $e->getMessage(),
                ];
            }
        }

        return true;
    }

    /**
     * Show the consent page to the user.
     *
     * @param Client      $client The OAuth client requesting authorization
     * @param string      $scope  The requested scope string
     * @param string|null $state  The state parameter for CSRF protection
     *
     * @return Response The rendered consent page
     */
    private function showConsentPage(Client $client, string $scope, ?string $state): Response
    {
        $scopes = $this->parseScopes($scope);

        return $this->render('OAuth/consent.html.twig', [
            'client' => $client,
            'scopes' => $scopes,
            'state' => $state,
        ]);
    }

    /**
     * Generate an authorization code for the authenticated user.
     *
     * Creates a new authorization code with PKCE data and saves it to the database.
     *
     * @param Client $client       The OAuth client
     * @param User   $user         The authenticated user
     * @param array  $oauthRequest The OAuth request data containing PKCE parameters
     *
     * @return AuthCode The created authorization code
     */
    private function generateAuthorizationCode(Client $client, User $user, array $oauthRequest): AuthCode
    {
        $authCode = new AuthCode();
        $authCode->setClient($client);
        $authCode->setUser($user);
        $authCode->setRedirectUri($oauthRequest['redirect_uri']);
        $authCode->setScope($oauthRequest['scope']);

        // Set expiration to 10 minutes (RFC recommendation)
        $authCode->setExpiresAt(time() + 600);

        // Generate a secure authorization code token
        $authCode->setToken($this->generateSecureToken());

        // Set PKCE parameters if present
        if ($oauthRequest['code_challenge']) {
            $authCode->setCodeChallenge($oauthRequest['code_challenge']);
            $authCode->setCodeChallengeMethod($oauthRequest['code_challenge_method']);
        }

        $this->entityManager->persist($authCode);
        $this->entityManager->flush();

        return $authCode;
    }

    /**
     * Generate a secure random token for authorization codes.
     */
    private function generateSecureToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Parse scope string into array of individual scopes.
     */
    private function parseScopes(string $scope): array
    {
        $scopes = array_filter(explode(' ', trim($scope)));

        // Define available scopes with descriptions
        $availableScopes = [
            'read' => 'Read your entries and account information',
            'write' => 'Create and modify entries',
            'delete' => 'Delete entries',
        ];

        $parsedScopes = [];
        foreach ($scopes as $scopeName) {
            if (isset($availableScopes[$scopeName])) {
                $parsedScopes[$scopeName] = $availableScopes[$scopeName];
            }
        }

        return $parsedScopes ?: ['read' => $availableScopes['read']];
    }

    /**
     * Redirect to client with authorization code.
     */
    private function redirectWithCode(string $redirectUri, string $code, ?string $state): RedirectResponse
    {
        $params = ['code' => $code];

        if (null !== $state) {
            $params['state'] = $state;
        }

        $separator = parse_url($redirectUri, \PHP_URL_QUERY) ? '&' : '?';
        $url = $redirectUri . $separator . http_build_query($params);

        return new RedirectResponse($url);
    }

    /**
     * Redirect to client with error.
     */
    private function redirectWithError(string $redirectUri, string $error, string $description, ?string $state): RedirectResponse
    {
        $params = [
            'error' => $error,
            'error_description' => $description,
        ];

        if (null !== $state) {
            $params['state'] = $state;
        }

        $separator = parse_url($redirectUri, \PHP_URL_QUERY) ? '&' : '?';
        $url = $redirectUri . $separator . http_build_query($params);

        return new RedirectResponse($url);
    }
}
