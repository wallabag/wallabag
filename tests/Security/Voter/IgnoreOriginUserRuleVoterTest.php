<?php

namespace Tests\Wallabag\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Wallabag\Entity\Config;
use Wallabag\Entity\IgnoreOriginUserRule;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\IgnoreOriginUserRuleVoter;

class IgnoreOriginUserRuleVoterTest extends TestCase
{
    private $token;
    private $ignoreOriginUserRuleVoter;

    protected function setUp(): void
    {
        $this->token = $this->createMock(TokenInterface::class);

        $this->ignoreOriginUserRuleVoter = new IgnoreOriginUserRuleVoter();
    }

    public function testVoteReturnsAbstainForInvalidSubject(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->ignoreOriginUserRuleVoter->vote($this->token, new \stdClass(), [IgnoreOriginUserRuleVoter::EDIT]));
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->ignoreOriginUserRuleVoter->vote($this->token, new IgnoreOriginUserRule(), ['INVALID']));
    }

    public function testVoteReturnsDeniedForUnauthenticatedEdit(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->ignoreOriginUserRuleVoter->vote($this->token, new IgnoreOriginUserRule(), [IgnoreOriginUserRuleVoter::EDIT]));
    }

    public function testVoteReturnsDeniedForOtherUserEdit(): void
    {
        $currentUser = new User();

        $this->token->method('getUser')->willReturn($currentUser);

        $taggingRuleUser = new User();
        $taggingRule = new IgnoreOriginUserRule();
        $taggingRule->setConfig(new Config($taggingRuleUser));

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->ignoreOriginUserRuleVoter->vote($this->token, $taggingRule, [IgnoreOriginUserRuleVoter::EDIT]));
    }

    public function testVoteReturnsGrantedForIgnoreOriginUserRuleUserEdit(): void
    {
        $user = new User();

        $this->token->method('getUser')->willReturn($user);

        $taggingRule = new IgnoreOriginUserRule();
        $taggingRule->setConfig(new Config($user));

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->ignoreOriginUserRuleVoter->vote($this->token, $taggingRule, [IgnoreOriginUserRuleVoter::EDIT]));
    }

    public function testVoteReturnsDeniedForUnauthenticatedDelete(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->ignoreOriginUserRuleVoter->vote($this->token, new IgnoreOriginUserRule(), [IgnoreOriginUserRuleVoter::DELETE]));
    }

    public function testVoteReturnsDeniedForOtherUserDelete(): void
    {
        $currentUser = new User();

        $this->token->method('getUser')->willReturn($currentUser);

        $taggingRuleUser = new User();
        $taggingRule = new IgnoreOriginUserRule();
        $taggingRule->setConfig(new Config($taggingRuleUser));

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->ignoreOriginUserRuleVoter->vote($this->token, $taggingRule, [IgnoreOriginUserRuleVoter::DELETE]));
    }

    public function testVoteReturnsGrantedForIgnoreOriginUserRuleUserDelete(): void
    {
        $user = new User();

        $this->token->method('getUser')->willReturn($user);

        $taggingRule = new IgnoreOriginUserRule();
        $taggingRule->setConfig(new Config($user));

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->ignoreOriginUserRuleVoter->vote($this->token, $taggingRule, [IgnoreOriginUserRuleVoter::DELETE]));
    }
}
