<?php

namespace Tests\Wallabag\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Wallabag\Entity\Annotation;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\AnnotationVoter;

class AnnotationVoterTest extends TestCase
{
    private $token;
    private $annotationVoter;

    protected function setUp(): void
    {
        $this->token = $this->createMock(TokenInterface::class);

        $this->annotationVoter = new AnnotationVoter();
    }

    public function testVoteReturnsAbstainForInvalidSubject(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->annotationVoter->vote($this->token, new \stdClass(), [AnnotationVoter::EDIT]));
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->annotationVoter->vote($this->token, new Annotation(new User()), ['INVALID']));
    }

    public function testVoteReturnsDeniedForUnauthenticatedEdit(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->annotationVoter->vote($this->token, new Annotation(new User()), [AnnotationVoter::EDIT]));
    }

    public function testVoteReturnsDeniedForOtherUserEdit(): void
    {
        $currentUser = new User();
        $annotationUser = new User();

        $this->token->method('getUser')->willReturn($currentUser);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->annotationVoter->vote($this->token, new Annotation($annotationUser), [AnnotationVoter::EDIT]));
    }

    public function testVoteReturnsGrantedForAnnotationUserEdit(): void
    {
        $user = new User();

        $this->token->method('getUser')->willReturn($user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->annotationVoter->vote($this->token, new Annotation($user), [AnnotationVoter::EDIT]));
    }

    public function testVoteReturnsDeniedForUnauthenticatedDelete(): void
    {
        $this->token->method('getUser')->willReturn(null);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->annotationVoter->vote($this->token, new Annotation(new User()), [AnnotationVoter::DELETE]));
    }

    public function testVoteReturnsDeniedForOtherUserDelete(): void
    {
        $currentUser = new User();
        $annotationUser = new User();

        $this->token->method('getUser')->willReturn($currentUser);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->annotationVoter->vote($this->token, new Annotation($annotationUser), [AnnotationVoter::DELETE]));
    }

    public function testVoteReturnsGrantedForAnnotationUserDelete(): void
    {
        $user = new User();

        $this->token->method('getUser')->willReturn($user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->annotationVoter->vote($this->token, new Annotation($user), [AnnotationVoter::DELETE]));
    }
}
