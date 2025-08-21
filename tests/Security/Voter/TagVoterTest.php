<?php

namespace Tests\Wallabag\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;
use Wallabag\Entity\Tag;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\TagVoter;

class TagVoterTest extends TestCase
{
    private $security;
    private $token;
    private $tagVoter;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);

        $this->token = $this->createMock(TokenInterface::class);

        $this->tagVoter = new TagVoter($this->security);
    }

    public function testVoteReturnsAbstainForInvalidSubject(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->tagVoter->vote($this->token, new \stdClass(), [TagVoter::EDIT]));
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->tagVoter->vote($this->token, new Tag(), ['INVALID']));
    }

    public function testVoteReturnsDeniedForUnauthenticatedView(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->tagVoter->vote($this->token, new Tag(), [TagVoter::VIEW]));
    }

    public function testVoteReturnsGrantedForUnauthorizedUserView(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->tagVoter->vote($this->token, new Tag(), [TagVoter::VIEW]));
    }

    public function testVoteReturnsGrantedForAuthorizedUserView(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->tagVoter->vote($this->token, new Tag(), [TagVoter::VIEW]));
    }

    public function testVoteReturnsDeniedForUnauthenticatedEdit(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->tagVoter->vote($this->token, new Tag(), [TagVoter::EDIT]));
    }

    public function testVoteReturnsGrantedForUnauthorizedUserEdit(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->tagVoter->vote($this->token, new Tag(), [TagVoter::EDIT]));
    }

    public function testVoteReturnsGrantedForAuthorizedUserEdit(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->tagVoter->vote($this->token, new Tag(), [TagVoter::EDIT]));
    }

    public function testVoteReturnsDeniedForUnauthenticatedDelete(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->tagVoter->vote($this->token, new Tag(), [TagVoter::DELETE]));
    }

    public function testVoteReturnsGrantedForUnauthorizedUserDelete(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->tagVoter->vote($this->token, new Tag(), [TagVoter::DELETE]));
    }

    public function testVoteReturnsGrantedForAuthorizedUserDelete(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->tagVoter->vote($this->token, new Tag(), [TagVoter::DELETE]));
    }
}
