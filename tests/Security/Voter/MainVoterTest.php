<?php

namespace Tests\Wallabag\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\MainVoter;

class MainVoterTest extends TestCase
{
    private $security;
    private $token;
    private $mainVoter;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);

        $this->token = $this->createMock(TokenInterface::class);
        $this->token->method('getUser')->willReturn(new User());

        $this->mainVoter = new MainVoter($this->security);
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->mainVoter->vote($this->token, null, ['INVALID']));
    }

    public function testVoteReturnsAbstainForInvalidSubject(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->mainVoter->vote($this->token, new \stdClass(), [MainVoter::LIST_ENTRIES]));
    }

    public function testVoteReturnsDeniedForInvalidUser(): void
    {
        $this->token->method('getUser')->willReturn(new \stdClass());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::LIST_ENTRIES]));
    }

    public function testVoteReturnsDeniedForNonUserListEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::LIST_ENTRIES]));
    }

    public function testVoteReturnsGrantedForUserListEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::LIST_ENTRIES]));
    }

    public function testVoteReturnsDeniedForNonUserCreateEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::CREATE_ENTRIES]));
    }

    public function testVoteReturnsGrantedForUserCreateEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::CREATE_ENTRIES]));
    }

    public function testVoteReturnsDeniedForNonUserEditEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::EDIT_ENTRIES]));
    }

    public function testVoteReturnsGrantedForUserEditEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::EDIT_ENTRIES]));
    }

    public function testVoteReturnsDeniedForNonUserExportEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::EXPORT_ENTRIES]));
    }

    public function testVoteReturnsGrantedForUserExportEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::EXPORT_ENTRIES]));
    }

    public function testVoteReturnsDeniedForNonUserImportEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::IMPORT_ENTRIES]));
    }

    public function testVoteReturnsGrantedForUserImportEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::IMPORT_ENTRIES]));
    }

    public function testVoteReturnsDeniedForNonUserDeleteEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::DELETE_ENTRIES]));
    }

    public function testVoteReturnsGrantedForUserDeleteEntries(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::DELETE_ENTRIES]));
    }

    public function testVoteReturnsDeniedForNonUserListTags(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::LIST_TAGS]));
    }

    public function testVoteReturnsGrantedForUserListTags(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::LIST_TAGS]));
    }

    public function testVoteReturnsDeniedForNonUserCreateTags(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::CREATE_TAGS]));
    }

    public function testVoteReturnsGrantedForUserCreateTags(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::CREATE_TAGS]));
    }

    public function testVoteReturnsDeniedForNonUserDeleteTags(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::DELETE_TAGS]));
    }

    public function testVoteReturnsGrantedForUserDeleteTags(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::DELETE_TAGS]));
    }

    public function testVoteReturnsDeniedForNonUserListSiteCredentials(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::LIST_SITE_CREDENTIALS]));
    }

    public function testVoteReturnsGrantedForUserListSiteCredentials(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::LIST_SITE_CREDENTIALS]));
    }

    public function testVoteReturnsDeniedForNonUserCreateSiteCredentials(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::CREATE_SITE_CREDENTIALS]));
    }

    public function testVoteReturnsGrantedForUserCreateSiteCredentials(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::CREATE_SITE_CREDENTIALS]));
    }

    public function testVoteReturnsDeniedForNonUserEditConfig(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(false);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->mainVoter->vote($this->token, null, [MainVoter::EDIT_CONFIG]));
    }

    public function testVoteReturnsGrantedForUserEditConfig(): void
    {
        $this->security->method('isGranted')->with('ROLE_USER')->willReturn(true);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->mainVoter->vote($this->token, null, [MainVoter::EDIT_CONFIG]));
    }
}
