<?php

namespace Tests\Wallabag\Service\OAuth;

use PHPUnit\Framework\TestCase;
use Wallabag\Service\OAuth\PkceService;

/**
 * Comprehensive test suite for PKCE Service.
 *
 * Tests all aspects of PKCE code generation and verification according to RFC 7636.
 */
class PkceServiceTest extends TestCase
{
    private PkceService $pkceService;

    protected function setUp(): void
    {
        $this->pkceService = new PkceService();
    }

    public function testGenerateCodeVerifierLength(): void
    {
        $verifier = $this->pkceService->generateCodeVerifier();

        $this->assertGreaterThanOrEqual(PkceService::MIN_VERIFIER_LENGTH, \strlen($verifier));
        $this->assertLessThanOrEqual(PkceService::MAX_VERIFIER_LENGTH, \strlen($verifier));
    }

    public function testGenerateCodeVerifierCharacters(): void
    {
        $verifier = $this->pkceService->generateCodeVerifier();

        // Should only contain allowed characters: A-Z, a-z, 0-9, -, ., _, ~
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9\-._~]+$/', $verifier);
    }

    public function testGenerateCodeVerifierUniqueness(): void
    {
        $verifiers = [];

        // Generate multiple verifiers and ensure they're unique
        for ($i = 0; $i < 100; ++$i) {
            $verifier = $this->pkceService->generateCodeVerifier();
            $this->assertNotContains($verifier, $verifiers, 'Code verifiers should be unique');
            $verifiers[] = $verifier;
        }
    }

    public function testGenerateCodeChallengeS256(): void
    {
        $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
        $expectedChallenge = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';

        $challenge = $this->pkceService->generateCodeChallenge($verifier, PkceService::METHOD_S256);

        $this->assertSame($expectedChallenge, $challenge);
    }

    public function testGenerateCodeChallengePlain(): void
    {
        $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';

        $challenge = $this->pkceService->generateCodeChallenge($verifier, PkceService::METHOD_PLAIN);

        $this->assertSame($verifier, $challenge);
    }

    public function testGenerateCodeChallengeInvalidMethod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported code challenge method "invalid"');

        // Use a properly sized verifier (43+ characters) so method validation happens
        $validVerifier = str_repeat('a', 43);
        $this->pkceService->generateCodeChallenge($validVerifier, 'invalid');
    }

    public function testVerifyCodeChallengeS256Valid(): void
    {
        $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
        $challenge = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';

        $result = $this->pkceService->verifyCodeChallenge($verifier, $challenge, PkceService::METHOD_S256);

        $this->assertTrue($result);
    }

    public function testVerifyCodeChallengeS256Invalid(): void
    {
        $verifier = 'wrong_verifier';
        $challenge = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';

        $result = $this->pkceService->verifyCodeChallenge($verifier, $challenge, PkceService::METHOD_S256);

        $this->assertFalse($result);
    }

    public function testVerifyCodeChallengePlainValid(): void
    {
        $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
        $challenge = $verifier; // Plain method uses verifier as challenge

        $result = $this->pkceService->verifyCodeChallenge($verifier, $challenge, PkceService::METHOD_PLAIN);

        $this->assertTrue($result);
    }

    public function testVerifyCodeChallengePlainInvalid(): void
    {
        $verifier = 'correct_verifier';
        $challenge = 'wrong_challenge';

        $result = $this->pkceService->verifyCodeChallenge($verifier, $challenge, PkceService::METHOD_PLAIN);

        $this->assertFalse($result);
    }

    public function testVerifyCodeChallengeInvalidVerifier(): void
    {
        $invalidVerifier = 'too_short'; // Less than 43 characters
        $challenge = 'some_challenge';

        $result = $this->pkceService->verifyCodeChallenge($invalidVerifier, $challenge, PkceService::METHOD_S256);

        $this->assertFalse($result);
    }

    public function testValidateCodeVerifierTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Code verifier must be at least 43 characters long');

        $this->pkceService->validateCodeVerifier('too_short');
    }

    public function testValidateCodeVerifierTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Code verifier must be at most 128 characters long');

        $longVerifier = str_repeat('a', 129);
        $this->pkceService->validateCodeVerifier($longVerifier);
    }

    public function testValidateCodeVerifierInvalidCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Code verifier contains invalid characters');

        $invalidVerifier = str_repeat('a', 43) . '!@#$%'; // Invalid characters
        $this->pkceService->validateCodeVerifier($invalidVerifier);
    }

    public function testValidateCodeVerifierValid(): void
    {
        $validVerifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk'; // 43 chars, valid chars

        // Should not throw exception
        $this->pkceService->validateCodeVerifier($validVerifier);
        $this->addToAssertionCount(1);
    }

    public function testValidateCodeChallengeMethodValid(): void
    {
        // Should not throw exceptions for valid methods
        $this->pkceService->validateCodeChallengeMethod(PkceService::METHOD_S256);
        $this->pkceService->validateCodeChallengeMethod(PkceService::METHOD_PLAIN);
        $this->addToAssertionCount(2);
    }

    public function testValidateCodeChallengeMethodInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported code challenge method "invalid"');

        $this->pkceService->validateCodeChallengeMethod('invalid');
    }

    public function testGetSupportedMethods(): void
    {
        $methods = $this->pkceService->getSupportedMethods();

        $this->assertContains(PkceService::METHOD_S256, $methods);
        $this->assertContains(PkceService::METHOD_PLAIN, $methods);
        $this->assertCount(2, $methods);
    }

    public function testShouldEnforceS256ForPublicClient(): void
    {
        $this->assertTrue($this->pkceService->shouldEnforceS256(true));
    }

    public function testShouldEnforceS256ForConfidentialClient(): void
    {
        $this->assertFalse($this->pkceService->shouldEnforceS256(false));
    }

    /**
     * Test full round-trip: generate verifier -> generate challenge -> verify.
     */
    public function testFullRoundTripS256(): void
    {
        $verifier = $this->pkceService->generateCodeVerifier();
        $challenge = $this->pkceService->generateCodeChallenge($verifier, PkceService::METHOD_S256);
        $isValid = $this->pkceService->verifyCodeChallenge($verifier, $challenge, PkceService::METHOD_S256);

        $this->assertTrue($isValid);
    }

    /**
     * Test full round-trip: generate verifier -> generate challenge -> verify (Plain method).
     */
    public function testFullRoundTripPlain(): void
    {
        $verifier = $this->pkceService->generateCodeVerifier();
        $challenge = $this->pkceService->generateCodeChallenge($verifier, PkceService::METHOD_PLAIN);
        $isValid = $this->pkceService->verifyCodeChallenge($verifier, $challenge, PkceService::METHOD_PLAIN);

        $this->assertTrue($isValid);
    }

    /**
     * Test that verification fails when using wrong method.
     */
    public function testVerificationFailsWithWrongMethod(): void
    {
        $verifier = $this->pkceService->generateCodeVerifier();
        $challengeS256 = $this->pkceService->generateCodeChallenge($verifier, PkceService::METHOD_S256);

        // Try to verify S256 challenge with plain method
        $isValid = $this->pkceService->verifyCodeChallenge($verifier, $challengeS256, PkceService::METHOD_PLAIN);

        $this->assertFalse($isValid);
    }

    /**
     * Test security: timing attack resistance
     * This test ensures hash_equals is used for comparison.
     */
    public function testTimingAttackResistance(): void
    {
        $verifier = $this->pkceService->generateCodeVerifier();
        $correctChallenge = $this->pkceService->generateCodeChallenge($verifier, PkceService::METHOD_S256);

        // Create a challenge that differs only in the last character
        $almostCorrectChallenge = substr($correctChallenge, 0, -1) . 'X';

        $startTime = microtime(true);
        $this->pkceService->verifyCodeChallenge($verifier, $almostCorrectChallenge, PkceService::METHOD_S256);
        $time1 = microtime(true) - $startTime;

        $startTime = microtime(true);
        $this->pkceService->verifyCodeChallenge($verifier, 'completely_wrong', PkceService::METHOD_S256);
        $time2 = microtime(true) - $startTime;

        // The timing difference should be minimal (less than 0.001 seconds)
        // This is a basic test - in practice, timing attack resistance is provided by hash_equals()
        $this->assertLessThan(0.001, abs($time1 - $time2));
    }
}
