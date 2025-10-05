<?php

namespace Tests\Wallabag\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Wallabag\Entity\SiteCredential;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\SiteCredentialVoter;

class SiteCredentialVoterTest extends TestCase
{
    private $user;
    private $token;
    private $siteCredentialVoter;

    protected function setUp(): void
    {
        $this->user = new User();

        $this->token = $this->createMock(TokenInterface::class);
        $this->token->method('getUser')->willReturn($this->user);

        $this->siteCredentialVoter = new SiteCredentialVoter();
    }

    public function testVoteReturnsAbstainForInvalidSubject(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->siteCredentialVoter->vote($this->token, new \stdClass(), [SiteCredentialVoter::EDIT]));
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->siteCredentialVoter->vote($this->token, new SiteCredential(new User()), ['INVALID']));
    }

    public function testVoteReturnsDeniedForNonSiteCredentialUserEdit(): void
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->siteCredentialVoter->vote($this->token, new SiteCredential(new User()), [SiteCredentialVoter::EDIT]));
    }

    public function testVoteReturnsGrantedForSiteCredentialUserEdit(): void
    {
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->siteCredentialVoter->vote($this->token, new SiteCredential($this->user), [SiteCredentialVoter::EDIT]));
    }

    public function testVoteReturnsDeniedForNonSiteCredentialUserDelete(): void
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->siteCredentialVoter->vote($this->token, new SiteCredential(new User()), [SiteCredentialVoter::DELETE]));
    }

    public function testVoteReturnsGrantedForSiteCredentialUserDelete(): void
    {
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->siteCredentialVoter->vote($this->token, new SiteCredential($this->user), [SiteCredentialVoter::DELETE]));
    }
}
