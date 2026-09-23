<?php

namespace Tests\Wallabag\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;
use Wallabag\Controller\Api\OAuthController;
use Wallabag\Entity\Api\AuthCode;
use Wallabag\Entity\Api\Client;
use Wallabag\Entity\User;
use Wallabag\Repository\Api\ClientRepository;
use Wallabag\Service\OAuth\PkceService;

class OAuthControllerTest extends TestCase
{
    private OAuthController $controller;
    private PkceService&MockObject $pkceService;
    private EntityManagerInterface&MockObject $entityManager;
    private ClientRepository&MockObject $clientRepository;
    private TokenStorageInterface&MockObject $tokenStorage;
    private AuthorizationCheckerInterface&MockObject $authorizationChecker;
    private CsrfTokenManagerInterface&MockObject $csrfTokenManager;
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private Environment&MockObject $twig;
    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        $this->pkceService = $this->createMock(PkceService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->clientRepository = $this->createMock(ClientRepository::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->container = $this->createMock(ContainerInterface::class);

        $this->controller = new OAuthController(
            $this->pkceService,
            $this->entityManager,
            $this->clientRepository
        );
        $this->controller->setContainer($this->container);

        // Setup container services
        // Setup CSRF token manager to validate our test token
        $this->csrfTokenManager->method('isTokenValid')
            ->willReturnCallback(function ($csrfToken) {
                return 'oauth_consent' === $csrfToken->getId() && 'valid_csrf_token' === $csrfToken->getValue();
            });

        // Setup URL generator to return login route
        $this->urlGenerator->method('generate')
            ->willReturnCallback(function ($route) {
                return match ($route) {
                    'login' => '/login',
                    'fos_user_security_login' => '/fos_user_security_login',
                    default => '/' . $route,
                };
            });

        $this->container->method('get')
            ->willReturnCallback(function ($service) {
                return match ($service) {
                    'security.token_storage' => $this->tokenStorage,
                    'security.authorization_checker' => $this->authorizationChecker,
                    'security.csrf.token_manager' => $this->csrfTokenManager,
                    'router' => $this->urlGenerator,
                    'twig' => $this->twig,
                    default => null,
                };
            });

        $this->container->method('has')
            ->willReturnCallback(function ($service) {
                return \in_array($service, [
                    'security.token_storage',
                    'security.authorization_checker',
                    'security.csrf.token_manager',
                    'router',
                    'twig',
                ], true);
            });
    }

    /**
     * Test successful authorization request with PKCE parameters.
     */
    public function testAuthorizeWithValidPkceRequest(): void
    {
        $client = $this->createMockClient();
        $user = $this->createMockUser();
        $request = $this->createAuthorizationRequest($client, true);

        // Add session to request
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $this->setupAuthenticatedUser($user);
        $this->clientRepository->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($client);

        $this->pkceService->expects($this->once())
            ->method('validateCodeChallengeMethod')
            ->with('S256');

        $this->twig->expects($this->once())
            ->method('render')
            ->with('OAuth/consent.html.twig', $this->callback(function ($params) {
                return $params['client'] instanceof Client
                       && isset($params['scopes']['read'])
                       && isset($params['scopes']['write'])
                       && 'random_state' === $params['state'];
            }))
            ->willReturn('<html>Consent page</html>');

        $response = $this->controller->authorizeAction($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Consent page', $response->getContent());
    }

    /**
     * Test authorization request without PKCE for public client.
     */
    public function testAuthorizePublicClientWithoutPkce(): void
    {
        $client = $this->createMockClient(true); // Public client
        $user = $this->createMockUser();
        $request = $this->createAuthorizationRequest($client, false); // No PKCE

        $this->setupAuthenticatedUser($user);
        $this->clientRepository->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($client);

        $response = $this->controller->authorizeAction($request);

        // Should redirect with error instead of throwing exception (RFC compliant)
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('error=invalid_request', $location);
        $this->assertStringContainsString('Client+requires+PKCE+but+no+code_challenge+provided', $location);
        $this->assertStringContainsString('state=random_state', $location);
    }

    /**
     * Test authorization request with invalid client.
     */
    public function testAuthorizeWithInvalidClient(): void
    {
        $request = Request::create('/oauth/v2/authorize', 'GET', [
            'client_id' => 'invalid_client',
            'redirect_uri' => 'http://example.com/callback',
            'response_type' => 'code',
        ]);

        // For invalid client_id format, find() may be called with 0 (parsed from 'invalid_client')
        $this->clientRepository->expects($this->once())
            ->method('find')
            ->with(0)
            ->willReturn(null);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid client_id or redirect_uri');

        $this->controller->authorizeAction($request);
    }

    /**
     * Test authorization request with mismatched redirect URI.
     */
    public function testAuthorizeWithMismatchedRedirectUri(): void
    {
        $client = $this->createMockClient();
        $client->method('getRedirectUris')->willReturn(['http://example.com/callback']);

        $request = Request::create('/oauth/v2/authorize', 'GET', [
            'client_id' => '123_abc',
            'redirect_uri' => 'http://evil.com/steal',
            'response_type' => 'code',
        ]);

        $this->clientRepository->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($client);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid client_id or redirect_uri');

        $this->controller->authorizeAction($request);
    }

    /**
     * Test authorization request without authentication.
     */
    public function testAuthorizeWithoutAuthentication(): void
    {
        $client = $this->createMockClient();
        $request = $this->createAuthorizationRequest($client);

        // Add session to request
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $this->clientRepository->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($client);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null); // Not authenticated

        $response = $this->controller->authorizeAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/fos_user_security_login', $response->headers->get('Location'));
    }

    /**
     * Test consent form submission - user approves.
     */
    public function testConsentApproval(): void
    {
        $client = $this->createMockClient();
        $user = $this->createMockUser();

        $session = new Session(new MockArraySessionStorage());
        $session->set('oauth2_request', [
            'client_id' => '123_abc',
            'redirect_uri' => 'http://example.com/callback',
            'response_type' => 'code',
            'scope' => 'read write',
            'state' => 'random_state',
            'code_challenge' => 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
            'code_challenge_method' => 'S256',
        ]);

        $request = Request::create('/oauth/v2/authorize', 'POST', [
            'action' => 'allow',
            '_token' => 'valid_csrf_token',
        ]);
        $request->setSession($session);

        $this->setupAuthenticatedUser($user);
        $this->clientRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '123_abc'])
            ->willReturn($client);

        // The controller uses isCsrfTokenValid() which internally handles the token validation
        // We need to ensure the container provides the CSRF token manager
        $this->container->method('has')
            ->willReturnCallback(function ($service) {
                return \in_array($service, [
                    'security.token_storage',
                    'security.authorization_checker',
                    'security.csrf.token_manager',
                    'twig',
                ], true);
            });

        // AuthCode is created directly in controller
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(AuthCode::class));
        $this->entityManager->expects($this->once())
            ->method('flush');

        $response = $this->controller->consentAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('http://example.com/callback', $location);
        $this->assertStringContainsString('code=', $location);
        $this->assertStringContainsString('state=random_state', $location);
    }

    /**
     * Test consent form submission - user denies.
     */
    public function testConsentDenial(): void
    {
        $client = $this->createMockClient();
        $user = $this->createMockUser();

        $session = new Session(new MockArraySessionStorage());
        $session->set('oauth2_request', [
            'client_id' => '123_abc',
            'redirect_uri' => 'http://example.com/callback',
            'response_type' => 'code',
            'state' => 'random_state',
        ]);

        $request = Request::create('/oauth/v2/authorize', 'POST', [
            'action' => 'deny',
            '_token' => 'valid_csrf_token',
        ]);
        $request->setSession($session);

        $this->setupAuthenticatedUser($user);
        // Note: For denial, no client repository call is made since user denied before validation

        $response = $this->controller->consentAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('error=access_denied', $location);
        $this->assertStringContainsString('state=random_state', $location);
    }

    /**
     * Test consent form submission with CSRF token failure.
     */
    public function testConsentWithInvalidCsrfToken(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('oauth2_request', [
            'client_id' => '123_abc',
            'redirect_uri' => 'http://example.com/callback',
            'response_type' => 'code',
            'state' => 'random_state',
        ]);

        $request = Request::create('/oauth/v2/authorize', 'POST', [
            'action' => 'allow',
            '_token' => 'invalid_csrf_token',
        ]);
        $request->setSession($session);

        // Override CSRF token manager to reject invalid token
        $this->csrfTokenManager->method('isTokenValid')
            ->willReturnCallback(function ($csrfToken) {
                return 'oauth_consent' === $csrfToken->getId() && 'valid_csrf_token' === $csrfToken->getValue();
            });

        $this->expectException(\Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid CSRF token');

        $this->controller->consentAction($request);
    }

    /**
     * Test that PKCE code_challenge is properly stored for later verification.
     */
    public function testCodeChallengeStoredForVerification(): void
    {
        $client = $this->createMockClient();
        $user = $this->createMockUser();

        $session = new Session(new MockArraySessionStorage());
        $session->set('oauth2_request', [
            'client_id' => '123_abc',
            'redirect_uri' => 'http://example.com/callback',
            'response_type' => 'code',
            'scope' => 'read write',
            'state' => 'random_state',
            'code_challenge' => 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
            'code_challenge_method' => 'S256',
        ]);

        $request = Request::create('/oauth/v2/authorize', 'POST', [
            'action' => 'allow',
            '_token' => 'valid_csrf_token',
        ]);
        $request->setSession($session);

        $this->setupAuthenticatedUser($user);
        $this->clientRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '123_abc'])
            ->willReturn($client);

        // Capture the AuthCode to verify PKCE data is stored
        $capturedAuthCode = null;
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$capturedAuthCode) {
                if ($entity instanceof AuthCode) {
                    $capturedAuthCode = $entity;
                }
            });

        $this->entityManager->expects($this->once())
            ->method('flush');

        $response = $this->controller->consentAction($request);

        // Verify AuthCode has PKCE data stored for later verification
        $this->assertNotNull($capturedAuthCode);
        $this->assertSame('E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM', $capturedAuthCode->getCodeChallenge());
        $this->assertSame('S256', $capturedAuthCode->getCodeChallengeMethod());
        $this->assertTrue($capturedAuthCode->hasPkce());
    }

    /**
     * Test PKCE validation for unsupported challenge method.
     */
    public function testAuthorizeWithUnsupportedChallengeMethod(): void
    {
        $client = $this->createMockClient();
        $user = $this->createMockUser();

        $request = Request::create('/oauth/v2/authorize', 'GET', [
            'client_id' => '123_abc',
            'redirect_uri' => 'http://example.com/callback',
            'response_type' => 'code',
            'code_challenge' => 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
            'code_challenge_method' => 'MD5',
        ]);

        $this->setupAuthenticatedUser($user);
        $this->clientRepository->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($client);

        $this->pkceService->expects($this->once())
            ->method('validateCodeChallengeMethod')
            ->with('MD5')
            ->willThrowException(new \InvalidArgumentException('Unsupported code_challenge_method'));

        $response = $this->controller->authorizeAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('error=invalid_request', $location);
        $this->assertStringContainsString('Unsupported+code_challenge_method', $location);
    }

    /**
     * Test state parameter preservation.
     */
    public function testStateParameterPreservation(): void
    {
        $client = $this->createMockClient();
        $user = $this->createMockUser();
        $state = 'csrf_protection_' . bin2hex(random_bytes(16));

        $session = new Session(new MockArraySessionStorage());
        $session->set('oauth2_request', [
            'client_id' => '123_abc',
            'redirect_uri' => 'http://example.com/callback',
            'response_type' => 'code',
            'scope' => 'read write',
            'state' => $state,
            'code_challenge' => null,
            'code_challenge_method' => 'S256',
        ]);

        $request = Request::create('/oauth/v2/authorize', 'POST', [
            'action' => 'allow',
            '_token' => 'valid_csrf_token',
        ]);
        $request->setSession($session);

        $this->setupAuthenticatedUser($user);
        $this->clientRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '123_abc'])
            ->willReturn($client);

        $response = $this->controller->consentAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('state=' . $state, $location);
    }

    /**
     * Test scope validation and storage.
     */
    public function testScopeHandling(): void
    {
        $client = $this->createMockClient();
        $user = $this->createMockUser();

        $request = Request::create('/oauth/v2/authorize', 'GET', [
            'client_id' => '123_abc',
            'redirect_uri' => 'http://example.com/callback',
            'response_type' => 'code',
            'scope' => 'read write delete',
        ]);

        // Add session to request
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $this->setupAuthenticatedUser($user);
        $this->clientRepository->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($client);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('OAuth/consent.html.twig', $this->callback(function ($params) {
                return isset($params['scopes']['read'])
                       && isset($params['scopes']['write'])
                       && isset($params['scopes']['delete']);
            }))
            ->willReturn('<html>Consent page</html>');

        $response = $this->controller->authorizeAction($request);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * Test authorization code generation with correct expiration.
     */
    public function testAuthorizationCodeExpiration(): void
    {
        $client = $this->createMockClient();
        $user = $this->createMockUser();

        $session = new Session(new MockArraySessionStorage());
        $session->set('oauth2_request', [
            'client_id' => '123_abc',
            'redirect_uri' => 'http://example.com/callback',
            'response_type' => 'code',
            'scope' => 'read write',
            'state' => 'test_state',
            'code_challenge' => null,
            'code_challenge_method' => 'S256',
        ]);

        $request = Request::create('/oauth/v2/authorize', 'POST', [
            'action' => 'allow',
            '_token' => 'valid_csrf_token',
        ]);
        $request->setSession($session);

        $this->setupAuthenticatedUser($user);
        $this->clientRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '123_abc'])
            ->willReturn($client);

        $capturedAuthCode = null;
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$capturedAuthCode) {
                if ($entity instanceof AuthCode) {
                    $capturedAuthCode = $entity;
                }
            });

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->controller->consentAction($request);

        $this->assertNotNull($capturedAuthCode);
        // Verify expiration is 10 minutes in the future
        $expectedExpiration = time() + 600;
        $actualExpiration = $capturedAuthCode->getExpiresAt();
        $this->assertGreaterThanOrEqual($expectedExpiration - 5, $actualExpiration);
        $this->assertLessThanOrEqual($expectedExpiration + 5, $actualExpiration);
    }

    /**
     * Test missing required parameters.
     */
    public function testMissingRequiredParameters(): void
    {
        $request = Request::create('/oauth/v2/authorize', 'GET', [
            'client_id' => '123_abc',
            // Missing redirect_uri and response_type
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class);
        $this->expectExceptionMessage('Missing required parameter: redirect_uri');

        $this->controller->authorizeAction($request);
    }

    /**
     * Test unsupported response type.
     */
    public function testUnsupportedResponseType(): void
    {
        $request = Request::create('/oauth/v2/authorize', 'GET', [
            'client_id' => '123_abc',
            'redirect_uri' => 'http://example.com/callback',
            'response_type' => 'token', // Implicit flow not supported
        ]);

        // Verify client lookup is NOT called - controller should return early
        $this->clientRepository->expects($this->never())
            ->method('find');

        $response = $this->controller->authorizeAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('error=unsupported_response_type', $location);
    }

    /**
     * Helper method to create a mock client.
     */
    private function createMockClient(bool $isPublic = false): Client&MockObject
    {
        $client = $this->createMock(Client::class);
        $client->method('getId')->willReturn(123);
        $client->method('getPublicId')->willReturn('123_abc');
        $client->method('getRandomId')->willReturn('abc');
        $client->method('getRedirectUris')->willReturn(['http://example.com/callback']);
        $client->method('isPublic')->willReturn($isPublic);
        $client->method('requiresPkce')->willReturn($isPublic);
        $client->method('getName')->willReturn('Test Client');

        return $client;
    }

    /**
     * Helper method to create a mock user.
     */
    private function createMockUser(): User&MockObject
    {
        $user = $this->createMock(User::class);
        $user->method('getUsername')->willReturn('testuser');
        $user->method('getId')->willReturn(1);

        return $user;
    }

    /**
     * Helper method to create an authorization request.
     */
    private function createAuthorizationRequest(Client $client, bool $withPkce = false): Request
    {
        $params = [
            'client_id' => $client->getPublicId(),
            'redirect_uri' => 'http://example.com/callback',
            'response_type' => 'code',
            'scope' => 'read write',
            'state' => 'random_state',
        ];

        if ($withPkce) {
            $params['code_challenge'] = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';
            $params['code_challenge_method'] = 'S256';
        }

        return Request::create('/oauth/v2/authorize', 'GET', $params);
    }

    /**
     * Helper method to setup authenticated user.
     */
    private function setupAuthenticatedUser(User $user): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($token);
    }
}
