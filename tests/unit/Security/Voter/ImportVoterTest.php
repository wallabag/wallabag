<?php

namespace Wallabag\Tests\Unit\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Wallabag\Entity\User;
use Wallabag\Import\AbstractImport;
use Wallabag\Security\Voter\ImportVoter;

class ImportVoterTest extends TestCase
{
    private $token;
    private $importVoter;

    protected function setUp(): void
    {
        $this->token = $this->createMock(TokenInterface::class);
        $this->importVoter = new ImportVoter();
    }

    public function testVoteReturnsAbstainForInvalidSubject(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->importVoter->vote($this->token, new \stdClass(), [ImportVoter::USE_IMPORTER]));
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->importVoter->vote($this->token, $this->createImport(), ['INVALID']));
    }

    public function testVoteReturnsDeniedForNonUser(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->importVoter->vote($this->token, $this->createImport(), [ImportVoter::USE_IMPORTER]));
    }

    public function testVoteReturnsDeniedForDisabledImporter(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->importVoter->vote($this->token, $this->createImport(false), [ImportVoter::USE_IMPORTER]));
    }

    public function testVoteReturnsGrantedForEnabledImporter(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->importVoter->vote($this->token, $this->createImport(), [ImportVoter::USE_IMPORTER]));
    }

    private function createImport(bool $enabled = true): AbstractImport
    {
        $import = $this->createMock(AbstractImport::class);
        $import->method('isEnabled')->willReturn($enabled);

        return $import;
    }
}
