<?php

namespace Tests\Wallabag\Entity\Api;

use PHPUnit\Framework\TestCase;
use Wallabag\Entity\Api\AuthCode;

/**
 * Test suite for AuthCode entity with PKCE support.
 */
class AuthCodeTest extends TestCase
{
    private AuthCode $authCode;

    protected function setUp(): void
    {
        $this->authCode = new AuthCode();
    }

    public function testSetCodeChallenge(): void
    {
        $challenge = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';

        $result = $this->authCode->setCodeChallenge($challenge);

        $this->assertSame($this->authCode, $result);
        $this->assertSame($challenge, $this->authCode->getCodeChallenge());
    }

    public function testSetCodeChallengeNull(): void
    {
        $this->authCode->setCodeChallenge('some_challenge');
        $result = $this->authCode->setCodeChallenge(null);

        $this->assertSame($this->authCode, $result);
        $this->assertNull($this->authCode->getCodeChallenge());
    }

    public function testSetCodeChallengeMethodS256(): void
    {
        $result = $this->authCode->setCodeChallengeMethod('S256');

        $this->assertSame($this->authCode, $result);
        $this->assertSame('S256', $this->authCode->getCodeChallengeMethod());
    }

    public function testSetCodeChallengeMethodPlain(): void
    {
        $result = $this->authCode->setCodeChallengeMethod('plain');

        $this->assertSame($this->authCode, $result);
        $this->assertSame('plain', $this->authCode->getCodeChallengeMethod());
    }

    public function testSetCodeChallengeMethodNull(): void
    {
        $this->authCode->setCodeChallengeMethod('S256');
        $result = $this->authCode->setCodeChallengeMethod(null);

        $this->assertSame($this->authCode, $result);
        $this->assertNull($this->authCode->getCodeChallengeMethod());
    }

    public function testSetCodeChallengeMethodInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Code challenge method must be either "S256" or "plain"');

        $this->authCode->setCodeChallengeMethod('invalid');
    }

    public function testHasPkceWithBothFields(): void
    {
        $this->authCode->setCodeChallenge('challenge');
        $this->authCode->setCodeChallengeMethod('S256');

        $this->assertTrue($this->authCode->hasPkce());
    }

    public function testHasPkceWithoutChallenge(): void
    {
        $this->authCode->setCodeChallengeMethod('S256');

        $this->assertFalse($this->authCode->hasPkce());
    }

    public function testHasPkceWithoutMethod(): void
    {
        $this->authCode->setCodeChallenge('challenge');

        $this->assertFalse($this->authCode->hasPkce());
    }

    public function testHasPkceWithNeitherField(): void
    {
        $this->assertFalse($this->authCode->hasPkce());
    }

    public function testHasPkceAfterClearingFields(): void
    {
        $this->authCode->setCodeChallenge('challenge');
        $this->authCode->setCodeChallengeMethod('S256');
        $this->assertTrue($this->authCode->hasPkce());

        $this->authCode->setCodeChallenge(null);
        $this->assertFalse($this->authCode->hasPkce());
    }

    /**
     * Test the complete PKCE workflow with AuthCode entity.
     */
    public function testCompletePkceWorkflow(): void
    {
        $challenge = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';
        $method = 'S256';

        // Initially no PKCE
        $this->assertFalse($this->authCode->hasPkce());

        // Set PKCE parameters
        $this->authCode->setCodeChallenge($challenge);
        $this->authCode->setCodeChallengeMethod($method);

        // Verify PKCE is enabled
        $this->assertTrue($this->authCode->hasPkce());
        $this->assertSame($challenge, $this->authCode->getCodeChallenge());
        $this->assertSame($method, $this->authCode->getCodeChallengeMethod());
    }
}
