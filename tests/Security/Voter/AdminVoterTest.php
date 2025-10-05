<?php

namespace Tests\Wallabag\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\AdminVoter;

class AdminVoterTest extends TestCase
{
    private $security;
    private $token;
    private $adminVoter;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);

        $this->token = $this->createMock(TokenInterface::class);
        $this->token->method('getUser')->willReturn(new User());

        $this->adminVoter = new AdminVoter($this->security);
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->adminVoter->vote($this->token, null, ['INVALID']));
    }

    public function testVoteReturnsDeniedForInvalidUser(): void
    {
        $this->token->method('getUser')->willReturn(new \stdClass());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->adminVoter->vote($this->token, null, [AdminVoter::LIST_USERS]));
    }

    public function testVoteReturnsDeniedForNonSuperAdminListUsers(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->adminVoter->vote($this->token, null, [AdminVoter::LIST_USERS]));
    }

    public function testVoteReturnsGrantedForSuperAdminListUsers(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->adminVoter->vote($this->token, null, [AdminVoter::LIST_USERS]));
    }

    public function testVoteReturnsDeniedForNonSuperAdminCreateUsers(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->adminVoter->vote($this->token, null, [AdminVoter::CREATE_USERS]));
    }

    public function testVoteReturnsGrantedForSuperAdminCreateUsers(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->adminVoter->vote($this->token, null, [AdminVoter::CREATE_USERS]));
    }

    public function testVoteReturnsDeniedForNonSuperAdminListIgnoreOriginInstanceRules(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->adminVoter->vote($this->token, null, [AdminVoter::LIST_IGNORE_ORIGIN_INSTANCE_RULES]));
    }

    public function testVoteReturnsGrantedForSuperAdminListIgnoreOriginInstanceRules(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->adminVoter->vote($this->token, null, [AdminVoter::LIST_IGNORE_ORIGIN_INSTANCE_RULES]));
    }

    public function testVoteReturnsDeniedForNonSuperAdminCreateIgnoreOriginInstanceRules(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->adminVoter->vote($this->token, null, [AdminVoter::CREATE_IGNORE_ORIGIN_INSTANCE_RULES]));
    }

    public function testVoteReturnsGrantedForSuperAdminCreateIgnoreOriginInstanceRules(): void
    {
        $this->security->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->adminVoter->vote($this->token, null, [AdminVoter::CREATE_IGNORE_ORIGIN_INSTANCE_RULES]));
    }
}
