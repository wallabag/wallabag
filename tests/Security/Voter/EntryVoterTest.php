<?php

namespace Tests\Wallabag\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Security\Voter\EntryVoter;

class EntryVoterTest extends TestCase
{
    private $token;
    private $user;
    private $entry;
    private $entryVoter;

    protected function setUp(): void
    {
        $this->token = $this->createMock(TokenInterface::class);
        $this->user = new User();
        $this->entry = new Entry($this->user);

        $this->entryVoter = new EntryVoter();
    }

    public function testVoteReturnsAbstainForInvalidSubject(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->entryVoter->vote($this->token, new \stdClass(), [EntryVoter::EDIT]));
    }

    public function testVoteReturnsAbstainForInvalidAttribute(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->entryVoter->vote($this->token, $this->entry, ['INVALID']));
    }

    public function testVoteReturnsDeniedForNonEntryUserView(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::VIEW]));
    }

    public function testVoteReturnsGrantedForEntryUserView(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::VIEW]));
    }

    public function testVoteReturnsDeniedForNonEntryUserEdit(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::EDIT]));
    }

    public function testVoteReturnsGrantedForEntryUserEdit(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::EDIT]));
    }

    public function testVoteReturnsDeniedForNonEntryUserReload(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::RELOAD]));
    }

    public function testVoteReturnsGrantedForEntryUserReload(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::RELOAD]));
    }

    public function testVoteReturnsDeniedForNonEntryUserStar(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::STAR]));
    }

    public function testVoteReturnsGrantedForEntryUserStar(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::STAR]));
    }

    public function testVoteReturnsDeniedForNonEntryUserArchive(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::ARCHIVE]));
    }

    public function testVoteReturnsGrantedForEntryUserArchive(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::ARCHIVE]));
    }

    public function testVoteReturnsDeniedForNonEntryUserShare(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::SHARE]));
    }

    public function testVoteReturnsGrantedForEntryUserShare(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::SHARE]));
    }

    public function testVoteReturnsDeniedForNonEntryUserUnshare(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::UNSHARE]));
    }

    public function testVoteReturnsGrantedForEntryUserUnshare(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::UNSHARE]));
    }

    public function testVoteReturnsDeniedForNonEntryUserExport(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::EXPORT]));
    }

    public function testVoteReturnsGrantedForEntryUserExport(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::EXPORT]));
    }

    public function testVoteReturnsDeniedForNonEntryUserDelete(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::DELETE]));
    }

    public function testVoteReturnsGrantedForEntryUserDelete(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::DELETE]));
    }

    public function testVoteReturnsDeniedForNonEntryUserListAnnotations(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::LIST_ANNOTATIONS]));
    }

    public function testVoteReturnsGrantedForEntryUserListAnnotations(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::LIST_ANNOTATIONS]));
    }

    public function testVoteReturnsDeniedForNonEntryUserCreateAnnotations(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::CREATE_ANNOTATIONS]));
    }

    public function testVoteReturnsGrantedForEntryUserCreateAnnotations(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::CREATE_ANNOTATIONS]));
    }

    public function testVoteReturnsDeniedForNonEntryUserListTags(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::LIST_TAGS]));
    }

    public function testVoteReturnsGrantedForEntryUserListTags(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::LIST_TAGS]));
    }

    public function testVoteReturnsDeniedForNonEntryUserTag(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::TAG]));
    }

    public function testVoteReturnsGrantedForEntryUserTag(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::TAG]));
    }

    public function testVoteReturnsDeniedForNonEntryUserUntag(): void
    {
        $this->token->method('getUser')->willReturn(new User());

        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::UNTAG]));
    }

    public function testVoteReturnsGrantedForEntryUserUntag(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->entryVoter->vote($this->token, $this->entry, [EntryVoter::UNTAG]));
    }
}
