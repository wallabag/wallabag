<?php

namespace Wallabag\Service\OAuth;

/**
 * PKCE (Proof Key for Code Exchange) service implementation.
 *
 * This service handles the generation and verification of PKCE challenges
 * according to RFC 7636 OAuth 2.0 Extension.
 *
 * @see https://tools.ietf.org/html/rfc7636
 */
class PkceService
{
    public const METHOD_PLAIN = 'plain';
    public const METHOD_S256 = 'S256';

    // RFC 7636 specifications
    public const MIN_VERIFIER_LENGTH = 43;
    public const MAX_VERIFIER_LENGTH = 128;
    public const VERIFIER_CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';

    /**
     * Generate a cryptographically secure code verifier.
     *
     * The code verifier is a cryptographically random string using the characters
     * [A-Z] / [a-z] / [0-9] / "-" / "." / "_" / "~", with a minimum length of 43
     * characters and a maximum length of 128 characters.
     */
    public function generateCodeVerifier(): string
    {
        $length = random_int(self::MIN_VERIFIER_LENGTH, self::MAX_VERIFIER_LENGTH);
        $charactersLength = \strlen(self::VERIFIER_CHARACTERS);
        $verifier = '';

        for ($i = 0; $i < $length; ++$i) {
            $verifier .= self::VERIFIER_CHARACTERS[random_int(0, $charactersLength - 1)];
        }

        return $verifier;
    }

    /**
     * Generate a code challenge from a code verifier using the specified method.
     *
     * @param string $codeVerifier The code verifier string
     * @param string $method       Either 'plain' or 'S256' (default: 'S256')
     *
     * @throws \InvalidArgumentException If the method is not supported or verifier is invalid
     */
    public function generateCodeChallenge(string $codeVerifier, string $method = self::METHOD_S256): string
    {
        $this->validateCodeVerifier($codeVerifier);

        switch ($method) {
            case self::METHOD_PLAIN:
                return $codeVerifier;
            case self::METHOD_S256:
                return $this->base64UrlEncode(hash('sha256', $codeVerifier, true));
            default:
                throw new \InvalidArgumentException(\sprintf('Unsupported code challenge method "%s". Supported methods are: %s, %s', $method, self::METHOD_PLAIN, self::METHOD_S256));
        }
    }

    /**
     * Verify that a code verifier matches the stored code challenge.
     *
     * @param string $codeVerifier  The code verifier provided by the client
     * @param string $codeChallenge The stored code challenge
     * @param string $method        The method used to generate the challenge
     *
     * @return bool True if the verifier is valid, false otherwise
     */
    public function verifyCodeChallenge(string $codeVerifier, string $codeChallenge, string $method): bool
    {
        try {
            $this->validateCodeVerifier($codeVerifier);
            $expectedChallenge = $this->generateCodeChallenge($codeVerifier, $method);

            // Use hash_equals to prevent timing attacks
            return hash_equals($codeChallenge, $expectedChallenge);
        } catch (\InvalidArgumentException $e) {
            // Invalid verifier or method
            return false;
        }
    }

    /**
     * Validate that a code verifier meets RFC 7636 requirements.
     *
     * @throws \InvalidArgumentException If the verifier is invalid
     */
    public function validateCodeVerifier(string $codeVerifier): void
    {
        $length = \strlen($codeVerifier);

        if ($length < self::MIN_VERIFIER_LENGTH) {
            throw new \InvalidArgumentException(\sprintf('Code verifier must be at least %d characters long, got %d', self::MIN_VERIFIER_LENGTH, $length));
        }

        if ($length > self::MAX_VERIFIER_LENGTH) {
            throw new \InvalidArgumentException(\sprintf('Code verifier must be at most %d characters long, got %d', self::MAX_VERIFIER_LENGTH, $length));
        }

        // Verify that all characters are from the allowed set
        if (!preg_match('/^[A-Za-z0-9\-._~]+$/', $codeVerifier)) {
            throw new \InvalidArgumentException('Code verifier contains invalid characters. Only A-Z, a-z, 0-9, -, ., _, ~ are allowed');
        }
    }

    /**
     * Validate that a code challenge method is supported.
     *
     * @throws \InvalidArgumentException If the method is not supported
     */
    public function validateCodeChallengeMethod(string $method): void
    {
        if (!\in_array($method, [self::METHOD_PLAIN, self::METHOD_S256], true)) {
            throw new \InvalidArgumentException(\sprintf('Unsupported code challenge method "%s". Supported methods are: %s, %s', $method, self::METHOD_PLAIN, self::METHOD_S256));
        }
    }

    /**
     * Get the list of supported code challenge methods.
     *
     * @return string[] Array of supported method names
     */
    public function getSupportedMethods(): array
    {
        return [self::METHOD_PLAIN, self::METHOD_S256];
    }

    /**
     * Check if S256 method should be enforced for security.
     *
     * For production environments and public clients, S256 should be required.
     * Plain method should only be used when S256 is not feasible.
     */
    public function shouldEnforceS256(bool $isPublicClient = false): bool
    {
        // Always enforce S256 for public clients
        return $isPublicClient;
    }

    /**
     * Base64-URL encode a string (RFC 4648 Section 5).
     *
     * This is standard base64 encoding with URL-safe characters and no padding.
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
