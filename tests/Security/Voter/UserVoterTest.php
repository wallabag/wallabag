<?php

namespace Tests\Wallabag\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\UserVoter;

class UserVoterTest extends TestCase
{
    private $security;
    private $token;
    private $userVoter;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);

        $this->token = $this->createMock(TokenInterface::class);
        $this->token->method('getUser')->willReturn(new User());

        $this->userVoter = new UserVoter($this->security);
    }

    public function testVoteReturnsAbstainForInvalidSubject(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->userVoter->vote($this->token, new \stdClass(), [UserVoter::EDIT]));
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->userVoter->vote($this->token, new User(), ['INVALID']));
    }

    public function testVoteReturnsDeniedForNonSuperAdminEdit(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->userVoter->vote($this->token, new User(), [UserVoter::EDIT]));
    }

    public function testVoteReturnsGrantedForSuperAdminEdit(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->userVoter->vote($this->token, new User(), [UserVoter::EDIT]));
    }

    public function testVoteReturnsDeniedForSelfDelete(): void
    {
        $user = new User();
        $this->token->method('getUser')->willReturn($user);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->userVoter->vote($this->token, $user, [UserVoter::DELETE]));
    }

    public function testVoteReturnsDeniedForNonSuperAdminDelete(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->userVoter->vote($this->token, new User(), [UserVoter::DELETE]));
    }

    public function testVoteReturnsGrantedForSuperAdminDelete(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->userVoter->vote($this->token, new User(), [UserVoter::DELETE]));
    }
}
