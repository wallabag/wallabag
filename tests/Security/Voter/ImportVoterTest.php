<?php

namespace Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Wallabag\Entity\SiteCredential;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\ImportVoter;

class ImportVoterTest extends TestCase
{
    private $token;
    private $user;
    private $importVoter;

    protected function setUp(): void
    {
        $this->token = $this->createMock(TokenInterface::class);
        $this->user = new User();

        $this->importVoter = new ImportVoter();
    }

    public function testVoteReturnsAbstainForInvalidSubject(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->importVoter->vote($this->token, new \stdClass(), [ImportVoter::USE_IMPORTER]));
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->importVoter->vote($this->token, new SiteCredential(new User()), ['INVALID']));
    }

    public function testVoteReturnsDeniedForNonSiteCredentialUserEdit(): void
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->importVoter->vote($this->token, new SiteCredential(new User()), [ImportVoter::USE_IMPORTER]));
    }

    public function testVoteReturnsGrantedForSiteCredentialUserEdit(): void
    {
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->importVoter->vote($this->token, new SiteCredential($this->user), [ImportVoter::USE_IMPORTER]));
    }
}
