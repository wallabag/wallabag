<?php

namespace Tests\Wallabag\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Wallabag\Entity\Config;
use Wallabag\Entity\TaggingRule;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\TaggingRuleVoter;

class TaggingRuleVoterTest extends TestCase
{
    private $token;
    private $taggingRuleVoter;

    protected function setUp(): void
    {
        $this->token = $this->createMock(TokenInterface::class);

        $this->taggingRuleVoter = new TaggingRuleVoter();
    }

    public function testVoteReturnsAbstainForInvalidSubject(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->taggingRuleVoter->vote($this->token, new \stdClass(), [TaggingRuleVoter::EDIT]));
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->taggingRuleVoter->vote($this->token, new TaggingRule(), ['INVALID']));
    }

    public function testVoteReturnsDeniedForUnauthenticatedEdit(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->taggingRuleVoter->vote($this->token, new TaggingRule(), [TaggingRuleVoter::EDIT]));
    }

    public function testVoteReturnsDeniedForOtherUserEdit(): void
    {
        $currentUser = new User();

        $this->token->method('getUser')->willReturn($currentUser);

        $taggingRuleUser = new User();
        $taggingRule = new TaggingRule();
        $taggingRule->setConfig(new Config($taggingRuleUser));

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->taggingRuleVoter->vote($this->token, $taggingRule, [TaggingRuleVoter::EDIT]));
    }

    public function testVoteReturnsGrantedForTaggingRuleUserEdit(): void
    {
        $user = new User();

        $this->token->method('getUser')->willReturn($user);

        $taggingRule = new TaggingRule();
        $taggingRule->setConfig(new Config($user));

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->taggingRuleVoter->vote($this->token, $taggingRule, [TaggingRuleVoter::EDIT]));
    }

    public function testVoteReturnsDeniedForUnauthenticatedDelete(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->taggingRuleVoter->vote($this->token, new TaggingRule(), [TaggingRuleVoter::DELETE]));
    }

    public function testVoteReturnsDeniedForOtherUserDelete(): void
    {
        $currentUser = new User();

        $this->token->method('getUser')->willReturn($currentUser);

        $taggingRuleUser = new User();
        $taggingRule = new TaggingRule();
        $taggingRule->setConfig(new Config($taggingRuleUser));

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->taggingRuleVoter->vote($this->token, $taggingRule, [TaggingRuleVoter::DELETE]));
    }

    public function testVoteReturnsGrantedForTaggingRuleUserDelete(): void
    {
        $user = new User();

        $this->token->method('getUser')->willReturn($user);

        $taggingRule = new TaggingRule();
        $taggingRule->setConfig(new Config($user));

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->taggingRuleVoter->vote($this->token, $taggingRule, [TaggingRuleVoter::DELETE]));
    }
}
