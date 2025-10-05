<?php

namespace Tests\Wallabag\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;
use Wallabag\Entity\IgnoreOriginInstanceRule;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\IgnoreOriginInstanceRuleVoter;

class IgnoreOriginInstanceRuleVoterTest extends TestCase
{
    private $security;
    private $token;
    private $ignoreOriginInstanceRuleVoter;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);

        $this->token = $this->createMock(TokenInterface::class);
        $this->token->method('getUser')->willReturn(new User());

        $this->ignoreOriginInstanceRuleVoter = new IgnoreOriginInstanceRuleVoter($this->security);
    }

    public function testVoteReturnsAbstainForInvalidSubject(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->ignoreOriginInstanceRuleVoter->vote($this->token, new \stdClass(), [IgnoreOriginInstanceRuleVoter::EDIT]));
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->ignoreOriginInstanceRuleVoter->vote($this->token, new IgnoreOriginInstanceRule(), ['INVALID']));
    }

    public function testVoteReturnsDeniedForNonSuperAdminEdit(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->ignoreOriginInstanceRuleVoter->vote($this->token, new IgnoreOriginInstanceRule(), [IgnoreOriginInstanceRuleVoter::EDIT]));
    }

    public function testVoteReturnsGrantedForSuperAdminEdit(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->ignoreOriginInstanceRuleVoter->vote($this->token, new IgnoreOriginInstanceRule(), [IgnoreOriginInstanceRuleVoter::EDIT]));
    }

    public function testVoteReturnsDeniedForNonSuperAdminDelete(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->ignoreOriginInstanceRuleVoter->vote($this->token, new IgnoreOriginInstanceRule(), [IgnoreOriginInstanceRuleVoter::DELETE]));
    }

    public function testVoteReturnsGrantedForSuperAdminDelete(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->ignoreOriginInstanceRuleVoter->vote($this->token, new IgnoreOriginInstanceRule(), [IgnoreOriginInstanceRuleVoter::DELETE]));
    }
}
